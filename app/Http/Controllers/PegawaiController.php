<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $query = Pegawai::query()->orderBy('id_mesin', 'asc');

        if ($search) {
            $query->where('nama_lengkap', 'like', "%{$search}%")
                ->orWhere('jabatan', 'like', "%{$search}%")
                ->orWhere('id_mesin', 'like', "%{$search}%")
                ->orWhere('uid_kartu', 'like', "%{$search}%");
        }

        $pegawaiList = $query->paginate(20);

        return view('pegawai.index', compact('pegawaiList', 'search'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_mesin' => 'required|string|max:50|unique:pegawai,id_mesin',
            'nama_lengkap' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'uid_kartu' => 'nullable|string|max:100',
        ]);

        $pegawai = Pegawai::create([
            'id_mesin' => $validated['id_mesin'],
            'nama_lengkap' => strtoupper($validated['nama_lengkap']),
            'jabatan' => strtoupper($validated['jabatan']),
            'uid_kartu' => $validated['uid_kartu'] ?? null,
            'is_active' => true,
        ]);

        AuditLogger::log('tambah_pegawai', 'pegawai', $pegawai->id, null, $pegawai->toArray());

        // Otomatis push perubahan ke mesin fisik ZKTeco di background
        $this->triggerAutoMachineSync();

        return back()->with('success', 'Data Aparatur Desa berhasil ditambahkan & otomatis tersinkron ke mesin biometrik!');
    }

    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);

        $validated = $request->validate([
            'id_mesin' => 'required|string|max:50|unique:pegawai,id_mesin,' . $id,
            'nama_lengkap' => 'required|string|max:255',
            'jabatan' => 'required|string|max:255',
            'uid_kartu' => 'nullable|string|max:100',
            'is_active' => 'required|boolean',
        ]);

        $oldData = $pegawai->toArray();
        $uidKartu = isset($validated['uid_kartu']) && trim($validated['uid_kartu']) !== '' ? trim($validated['uid_kartu']) : null;

        $pegawai->update([
            'id_mesin' => $validated['id_mesin'],
            'nama_lengkap' => strtoupper($validated['nama_lengkap']),
            'jabatan' => strtoupper($validated['jabatan']),
            'uid_kartu' => $uidKartu,
            'is_active' => $validated['is_active'],
        ]);

        AuditLogger::log('update_pegawai', 'pegawai', $pegawai->id, $oldData, $pegawai->toArray());

        // Otomatis push perubahan ke mesin fisik ZKTeco di background
        $this->triggerAutoMachineSync();

        return back()->with('success', 'Data Aparatur Desa berhasil diperbarui & otomatis tersinkron ke mesin biometrik!');
    }

    public function destroy($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $oldData = $pegawai->toArray();
        $pegawai->delete();

        AuditLogger::log('hapus_pegawai', 'pegawai', $id, $oldData, null);

        // Otomatis push perubahan ke mesin fisik ZKTeco di background
        $this->triggerAutoMachineSync();

        return back()->with('success', 'Data Aparatur Desa berhasil dihapus & otomatis tersinkron ke mesin biometrik!');
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
