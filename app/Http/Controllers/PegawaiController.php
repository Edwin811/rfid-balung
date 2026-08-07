<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Pegawai;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $tab = $request->input('tab', 'aktif'); // 'aktif', 'arsip', 'histori'

        // 1. Data Aparatur Aktif
        $query = Pegawai::query()->orderByRaw('CAST(id_mesin AS UNSIGNED) ASC');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%")
                  ->orWhere('id_mesin', 'like', "%{$search}%")
                  ->orWhere('uid_kartu', 'like', "%{$search}%");
            });
        }
        $pegawaiList = $query->paginate(20, ['*'], 'page_aktif');

        // 2. Data Arsip Pensiun / Mutasi / Soft-Deleted
        $arsipQuery = Pegawai::withTrashed()
            ->where(function($q) {
                $q->whereNotNull('deleted_at')
                  ->orWhereIn('status_kepegawaian', ['pensiun', 'mutasi', 'non_aktif']);
            })
            ->orderBy('updated_at', 'desc');

        if ($search) {
            $arsipQuery->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%")
                  ->orWhere('id_mesin', 'like', "%{$search}%")
                  ->orWhere('uid_kartu', 'like', "%{$search}%");
            });
        }
        $arsipList = $arsipQuery->paginate(20, ['*'], 'page_arsip');

        // 3. Log Histori Perangkat & Mutasi RFID/Aparatur
        $historiQuery = AuditLog::with('user')
            ->whereIn('aksi', [
                'tambah_pegawai', 'update_pegawai', 'hapus_pegawai', 
                'sync_users_to_machine', 'ganti_kartu_rfid', 'pensiun_pegawai', 'mutasi_pegawai'
            ])
            ->orderBy('created_at', 'desc');

        $historiPerangkat = $historiQuery->paginate(20, ['*'], 'page_histori');

        return view('pegawai.index', compact('pegawaiList', 'arsipList', 'historiPerangkat', 'search', 'tab'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_mesin' => 'required|string|max:50|unique:pegawai,id_mesin',
            'nama_lengkap' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'status_kepegawaian' => 'required|in:aktif,pensiun,mutasi,non_aktif',
            'uid_kartu' => 'nullable|string|max:100',
        ]);

        $pegawai = Pegawai::create([
            'id_mesin' => $validated['id_mesin'],
            'nama_lengkap' => strtoupper($validated['nama_lengkap']),
            'jabatan' => strtoupper($validated['jabatan']),
            'status_kepegawaian' => $validated['status_kepegawaian'],
            'uid_kartu' => $validated['uid_kartu'] ?? null,
            'is_active' => $validated['status_kepegawaian'] === 'aktif',
        ]);

        AuditLogger::log('tambah_pegawai', 'pegawai', $pegawai->id, null, $pegawai->toArray());

        // Otomatis push perubahan ke mesin fisik ZKTeco di background
        $this->triggerAutoMachineSync();

        return back()->with('success', 'Data Aparatur Desa berhasil ditambahkan & otomatis tersinkron ke mesin biometrik!');
    }

    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::withTrashed()->findOrFail($id);

        $validated = $request->validate([
            'id_mesin' => 'required|string|max:50|unique:pegawai,id_mesin,' . $id,
            'nama_lengkap' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'status_kepegawaian' => 'required|in:aktif,pensiun,mutasi,non_aktif',
            'uid_kartu' => 'nullable|string|max:100',
            'is_active' => 'required|boolean',
        ]);

        $oldData = $pegawai->toArray();
        $uidKartu = isset($validated['uid_kartu']) && trim($validated['uid_kartu']) !== '' ? trim($validated['uid_kartu']) : null;
        $isActive = ($validated['status_kepegawaian'] === 'aktif') ? $validated['is_active'] : false;

        $pegawai->update([
            'id_mesin' => $validated['id_mesin'],
            'nama_lengkap' => strtoupper($validated['nama_lengkap']),
            'jabatan' => strtoupper($validated['jabatan']),
            'status_kepegawaian' => $validated['status_kepegawaian'],
            'uid_kartu' => $uidKartu,
            'is_active' => $isActive,
        ]);

        // Audit Log spesifik pergantian kartu & status kepegawaian
        if ($oldData['uid_kartu'] !== $uidKartu) {
            AuditLogger::log('ganti_kartu_rfid', 'pegawai', $pegawai->id, ['uid_lama' => $oldData['uid_kartu']], ['uid_baru' => $uidKartu, 'nama' => $pegawai->nama_lengkap]);
        }

        if ($oldData['status_kepegawaian'] !== $validated['status_kepegawaian']) {
            $actionLog = $validated['status_kepegawaian'] === 'pensiun' ? 'pensiun_pegawai' : ($validated['status_kepegawaian'] === 'mutasi' ? 'mutasi_pegawai' : 'update_pegawai');
            AuditLogger::log($actionLog, 'pegawai', $pegawai->id, ['status_lama' => $oldData['status_kepegawaian']], ['status_baru' => $validated['status_kepegawaian'], 'nama' => $pegawai->nama_lengkap]);
        }

        AuditLogger::log('update_pegawai', 'pegawai', $pegawai->id, $oldData, $pegawai->toArray());

        // Otomatis push perubahan ke mesin fisik ZKTeco di background
        $this->triggerAutoMachineSync();

        return back()->with('success', 'Data Aparatur Desa & Kartu RFID berhasil diperbarui dan tersinkron!');
    }

    public function destroy($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $oldData = $pegawai->toArray();
        $pegawai->update(['status_kepegawaian' => 'pensiun', 'is_active' => false]);
        $pegawai->delete();

        AuditLogger::log('hapus_pegawai', 'pegawai', $id, $oldData, null);

        // Otomatis push perubahan ke mesin fisik ZKTeco di background
        $this->triggerAutoMachineSync();

        return back()->with('success', 'Aparatur Desa berhasil diarsipkan (Pensiun / Soft Delete). Seluruh data presensi historis tetap tersimpan 100%!');
    }

    public function restore($id)
    {
        $pegawai = Pegawai::onlyTrashed()->findOrFail($id);
        $pegawai->restore();
        $pegawai->update(['status_kepegawaian' => 'aktif', 'is_active' => true]);

        AuditLogger::log('restore_pegawai', 'pegawai', $id, null, $pegawai->toArray());

        $this->triggerAutoMachineSync();

        return back()->with('success', 'Akun Aparatur Desa berhasil dipulihkan kembali ke status Aktif!');
    }

    public function syncToMachine()
    {
        $pythonScript = base_path('scripts/push_users_to_machine.py');
        $pythonBin = env('PYTHON_BIN', 'python');
        $command = escapeshellcmd("{$pythonBin} {$pythonScript}") . " 2>&1";
        $output = shell_exec($command);

        AuditLogger::log('sync_users_to_machine', null, null, null, ['output' => $output]);

        return back()->with('success', 'Perintah push nama & kartu RFID ke mesin ZKTeco berhasil dijalankan! ' . $output);
    }

    public function pullFromMachine()
    {
        $pythonScript = base_path('scripts/pull_users_from_machine.py');
        $pythonBin = env('PYTHON_BIN', 'python');
        $command = escapeshellcmd("{$pythonBin} {$pythonScript}") . " 2>&1";
        $rawOutput = shell_exec($command);

        if (!$rawOutput) {
            return back()->with('error', 'Gagal mengeksekusi script pull data dari mesin.');
        }

        $result = json_decode($rawOutput, true);

        if (!$result || ($result['status'] ?? '') !== 'success') {
            return back()->with('error', 'Gagal menarik data dari mesin: ' . ($result['message'] ?? $rawOutput));
        }

        $users = $result['data'] ?? [];
        $pulledCount = 0;
        $updatedCount = 0;

        foreach ($users as $u) {
            $idMesin = (string) $u['user_id'];
            $nama = !empty($u['nama']) ? $u['nama'] : "Aparatur ID {$idMesin}";
            $card = !empty($u['card']) ? (string) $u['card'] : null;

            $pegawai = Pegawai::where('id_mesin', $idMesin)->first();

            if ($pegawai) {
                $updateData = [];
                if ($nama && $nama !== "Aparatur ID {$idMesin}" && $pegawai->nama_lengkap !== $nama) {
                    $updateData['nama_lengkap'] = $nama;
                }
                if ($card && $pegawai->uid_kartu !== $card) {
                    $updateData['uid_kartu'] = $card;
                }
                if (!empty($updateData)) {
                    $pegawai->update($updateData);
                    $updatedCount++;
                }
            } else {
                Pegawai::create([
                    'id_mesin' => $idMesin,
                    'nama_lengkap' => $nama,
                    'uid_kartu' => $card,
                    'jabatan' => 'Aparatur Desa',
                    'is_active' => true,
                ]);
                $pulledCount++;
            }
        }

        AuditLogger::log('pull_users_from_machine', null, null, null, [
            'total_from_machine' => count($users),
            'new_created' => $pulledCount,
            'updated' => $updatedCount,
        ]);

        return back()->with('success', "Proses Pull Selesai! Ditemukan " . count($users) . " aparatur di mesin. ({$pulledCount} baru ditambahkan, {$updatedCount} diperbarui)");
    }

    private function triggerAutoMachineSync(): void
    {
        try {
            $pythonScript = base_path('scripts/push_users_to_machine.py');
            $pythonBin = env('PYTHON_BIN', 'python');
            $command = escapeshellcmd("{$pythonBin} {$pythonScript}") . " > /dev/null 2>&1 &";
            exec($command);
        } catch (\Throwable $e) {
            Log::warning("Auto-sync machine error: " . $e->getMessage());
        }
    }
}
