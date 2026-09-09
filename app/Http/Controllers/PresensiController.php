<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Presensi;
use App\Services\AuditLogger;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        $search = $request->input('search');

        $pegawaiList = Pegawai::where('is_active', true)->orderByRaw('CAST(id_mesin AS UNSIGNED) ASC')->get();

        $query = Presensi::with('pegawai', 'updatedBy')
            ->whereDate('tanggal', $tanggal)
            ->join('pegawai', 'presensi.pegawai_id', '=', 'pegawai.id')
            ->orderByRaw('CAST(pegawai.id_mesin AS UNSIGNED) ASC')
            ->select('presensi.*');

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

        $isDeviceOnline = AttendanceService::isDeviceOnline();

        return view('presensi.index', compact('tanggal', 'search', 'pegawaiList', 'presensiList', 'presensiFormattedList', 'isDeviceOnline'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || !($user->isAdmin() || $user->isOperator())) {
            abort(403, 'Anda tidak memiliki hak akses untuk memperbarui data presensi.');
        }

        $request->validate([
            'status' => ['required', 'in:hadir,terlambat,tidak_hadir,sakit,izin,dispen,skorsing'],
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
        try {
            \Illuminate\Support\Facades\Artisan::call('rfid:sync');
            $output = trim(\Illuminate\Support\Facades\Artisan::output());
            return redirect()->back(fallback: route('presensi.index'))->with('success', 'Sinkronisasi mesin selesai! ' . $output);
        } catch (\Throwable $e) {
            return redirect()->back(fallback: route('presensi.index'))->with('error', 'Gagal menarik data dari mesin: ' . $e->getMessage());
        }
    }

    public function harianRealtimeApi(Request $request)
    {
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        $search = $request->input('search');

        $isDeviceOnline = AttendanceService::isDeviceOnline();

        // Auto-sync cerdas: Jika mesin online dan belum sync dalam 30 detik terakhir, tarik data otomatis
        if ($isDeviceOnline && \Illuminate\Support\Facades\Cache::add('auto_rfid_sync_throttle', true, 30)) {
            try {
                \Illuminate\Support\Facades\Artisan::call('rfid:sync');
            } catch (\Throwable $e) {
                // Abaikan error agar respon realtime tetap instan
            }
        }

        $query = Presensi::with('pegawai', 'updatedBy')
            ->whereDate('tanggal', $tanggal)
            ->join('pegawai', 'presensi.pegawai_id', '=', 'pegawai.id')
            ->orderByRaw('CAST(pegawai.id_mesin AS UNSIGNED) ASC')
            ->select('presensi.*');

        if ($search) {
            $query->whereHas('pegawai', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        $presensiList = $query->get();
        $isDeviceOnline = AttendanceService::isDeviceOnline();

        return response()->json([
            'status' => 'success',
            'timestamp' => now()->toTimeString(),
            'device_online' => $isDeviceOnline,
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
