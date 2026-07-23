<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Presensi;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        $search = $request->input('search');

        $pegawaiList = Pegawai::where('is_active', true)->orderBy('nama_lengkap')->get();

        $query = Presensi::with('pegawai', 'updatedBy')
            ->whereDate('tanggal', $tanggal);

        if ($search) {
            $query->whereHas('pegawai', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        $presensiList = $query->get();

        $presensiFormattedList = $presensiList->map(function ($p, $index) {
            return [
                'no' => $index + 1,
                'id' => $p->id,
                'nama' => $p->pegawai->nama_lengkap ?? '-',
                'id_mesin' => $p->pegawai->id_mesin ?? '-',
                'jabatan' => $p->pegawai->jabatan ?? '-',
                'jam_masuk' => $p->jam_masuk ?? '-',
                'jam_pulang' => $p->jam_pulang ?? '-',
                'tipe_masuk' => $p->tipe_verifikasi_masuk ?? 'fingerprint',
                'tipe_pulang' => $p->tipe_verifikasi_pulang ?? 'fingerprint',
                'status' => $p->status,
                'keterangan' => $p->keterangan ?? '-',
            ];
        })->values()->toArray();

        return view('presensi.index', compact('tanggal', 'search', 'pegawaiList', 'presensiList', 'presensiFormattedList'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isOperator()) {
            return back()->with('error', 'Hanya Admin dan Operator yang berhak mengubah data presensi.');
        }

        $request->validate([
            'status' => ['required', 'in:hadir,terlambat,tidak_hadir,sakit,izin,dispen'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $presensi = Presensi::findOrFail($id);
        $dataLama = $presensi->toArray();

        $presensi->status = $request->input('status');
        $presensi->keterangan = $request->input('keterangan');
        $presensi->updated_by = Auth::id();
        $presensi->save();

        AuditLogger::log('update_presensi', 'Presensi', $presensi->id, $dataLama, $presensi->toArray());

        return back()->with('success', 'Data presensi berhasil diperbarui.');
    }

    public function syncNow()
    {
        $pythonScript = base_path('scripts/fetch_presensi.py');
        $pythonBin = env('PYTHON_BIN', 'python');
        $command = escapeshellcmd("{$pythonBin} {$pythonScript}") . " 2>&1";
        $output = shell_exec($command);

        AuditLogger::log('sync_mesin_manual', null, null, null, ['output' => $output]);

        return back()->with('success', 'Proses Sync Log Presensi dari Mesin Biometrik Selesai!');
    }

    public function harianRealtimeApi(Request $request)
    {
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        $search = $request->input('search');

        $query = Presensi::with('pegawai', 'updatedBy')
            ->whereDate('tanggal', $tanggal);

        if ($search) {
            $query->whereHas('pegawai', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        $presensiList = $query->get();

        return response()->json([
            'status' => 'success',
            'timestamp' => now()->toTimeString(),
            'data' => $presensiList->map(function ($p, $index) {
                return [
                    'no' => $index + 1,
                    'id' => $p->id,
                    'nama' => $p->pegawai->nama_lengkap ?? '-',
                    'id_mesin' => $p->pegawai->id_mesin ?? '-',
                    'jabatan' => $p->pegawai->jabatan ?? '-',
                    'jam_masuk' => $p->jam_masuk ?? '-',
                    'jam_pulang' => $p->jam_pulang ?? '-',
                    'tipe_masuk' => $p->tipe_verifikasi_masuk ?? 'fingerprint',
                    'tipe_pulang' => $p->tipe_verifikasi_pulang ?? 'fingerprint',
                    'status' => $p->status,
                    'keterangan' => $p->keterangan ?? '-',
                ];
            }),
        ]);
    }
}
