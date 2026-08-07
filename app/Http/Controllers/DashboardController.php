<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\DeviceLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $lastSync = \Illuminate\Support\Facades\Cache::get('last_rfid_sync_time');
        if (!$lastSync || (time() - (int)$lastSync) >= 10) {
            try {
                \Illuminate\Support\Facades\Artisan::call('rfid:sync');
                \Illuminate\Support\Facades\Cache::put('last_rfid_sync_time', time(), 60);
            } catch (\Throwable $e) {
                // ignore if device offline
            }
        }

        $today = Carbon::today()->toDateString();
        $totalPegawai = Pegawai::where('is_active', true)->count();

        // Ambil data presensi hari ini
        $presensiToday = Presensi::with('pegawai')
            ->whereDate('tanggal', $today)
            ->get();

        $countHadir = $presensiToday->where('status', 'hadir')->count();
        $countTerlambat = $presensiToday->where('status', 'terlambat')->count();
        $countSakit = $presensiToday->where('status', 'sakit')->count();
        $countIzin = $presensiToday->where('status', 'izin')->count();
        $countDispen = $presensiToday->where('status', 'dispen')->count();
        $countAbsenRecorded = $presensiToday->count();
        $countTidakHadir = max(0, $totalPegawai - $countAbsenRecorded);

        // Alert pegawai yang belum absen setelah pukul 08:00
        $alreadyRecordedPegawaiIds = $presensiToday->pluck('pegawai_id')->toArray();
        $unCheckedInPegawai = Pegawai::where('is_active', true)
            ->whereNotIn('id', $alreadyRecordedPegawaiIds)
            ->orderBy('id_mesin')
            ->get();

        $unCheckedInList = $unCheckedInPegawai->map(function ($p, $index) {
            return [
                'no' => $index + 1,
                'id' => $p->id,
                'id_mesin' => $p->id_mesin ?? '-',
                'nama' => $p->nama_lengkap,
                'jabatan' => $p->jabatan,
            ];
        })->values()->toArray();

        $showLateAlert = (now()->format('H:i') >= '08:01') && ($unCheckedInPegawai->count() > 0);

        // Chart 7 hari terakhir
        $chartData = $this->get7DayChartData();

        // Device Connection Status (online if device log received in last 10 minutes)
        $lastLog = DeviceLog::latest('timestamp_absen')->first();
        $isDeviceOnline = $lastLog ? $lastLog->timestamp_absen->gt(now()->subMinutes(10)) : false;

        $presensiTodayList = $presensiToday->map(function ($p) {
            return [
                'id' => $p->id,
                'nama' => $p->pegawai->nama_lengkap ?? '-',
                'jabatan' => $p->pegawai->jabatan ?? '-',
                'jam_masuk' => $p->jam_masuk ?? '-',
                'jam_pulang' => $p->jam_pulang ?? '-',
                'tipe_masuk' => $p->tipe_verifikasi_masuk ?? 'fingerprint',
                'tipe_pulang' => $p->tipe_verifikasi_pulang ?? 'fingerprint',
                'status' => $p->status,
                'keterangan' => $p->keterangan ?? '-',
            ];
        })->values()->toArray();

        return view('dashboard.index', compact(
            'totalPegawai',
            'countHadir',
            'countTerlambat',
            'countSakit',
            'countIzin',
            'countDispen',
            'countTidakHadir',
            'presensiToday',
            'presensiTodayList',
            'unCheckedInPegawai',
            'unCheckedInList',
            'showLateAlert',
            'chartData',
            'isDeviceOnline',
            'lastLog'
        ));
    }

    public function realtimeApi(): JsonResponse
    {
        $lastSync = \Illuminate\Support\Facades\Cache::get('last_rfid_sync_time');
        if (!$lastSync || (time() - (int)$lastSync) >= 10) {
            try {
                \Illuminate\Support\Facades\Artisan::call('rfid:sync');
                \Illuminate\Support\Facades\Cache::put('last_rfid_sync_time', time(), 60);
            } catch (\Throwable $e) {
                // ignore if device offline
            }
        }

        $today = Carbon::today()->toDateString();
        $totalPegawai = Pegawai::where('is_active', true)->count();

        $presensiToday = Presensi::with('pegawai')
            ->whereDate('tanggal', $today)
            ->get();

        $alreadyRecordedPegawaiIds = $presensiToday->pluck('pegawai_id')->toArray();
        $unCheckedInPegawai = Pegawai::where('is_active', true)
            ->whereNotIn('id', $alreadyRecordedPegawaiIds)
            ->orderBy('id_mesin')
            ->get();

        $unCheckedInList = $unCheckedInPegawai->map(function ($p, $index) {
            return [
                'no' => $index + 1,
                'id' => $p->id,
                'id_mesin' => $p->id_mesin ?? '-',
                'nama' => $p->nama_lengkap,
                'jabatan' => $p->jabatan,
            ];
        })->values()->toArray();

        $countHadir = $presensiToday->where('status', 'hadir')->count();
        $countTerlambat = $presensiToday->where('status', 'terlambat')->count();
        $countSakit = $presensiToday->where('status', 'sakit')->count();
        $countIzin = $presensiToday->where('status', 'izin')->count();
        $countDispen = $presensiToday->where('status', 'dispen')->count();
        $countTidakHadir = max(0, $totalPegawai - $presensiToday->count());

        $lastLog = DeviceLog::latest('timestamp_absen')->first();
        $isDeviceOnline = $lastLog ? $lastLog->timestamp_absen->gt(now()->subMinutes(10)) : false;

        return response()->json([
            'status' => 'success',
            'timestamp' => now()->toTimeString(),
            'counts' => [
                'total' => $totalPegawai,
                'hadir' => $countHadir,
                'terlambat' => $countTerlambat,
                'sakit' => $countSakit,
                'izin' => $countIzin,
                'dispen' => $countDispen,
                'tidak_hadir' => $countTidakHadir,
            ],
            'device_online' => $isDeviceOnline,
            'last_log_time' => $lastLog ? $lastLog->timestamp_absen->format('Y-m-d H:i:s') : null,
            'data' => $presensiToday->map(function ($p) {
                return [
                    'id' => $p->id,
                    'nama' => $p->pegawai->nama_lengkap ?? '-',
                    'jabatan' => $p->pegawai->jabatan ?? '-',
                    'jam_masuk' => $p->jam_masuk ?? '-',
                    'jam_pulang' => $p->jam_pulang ?? '-',
                    'tipe_masuk' => $p->tipe_verifikasi_masuk ?? 'fingerprint',
                    'tipe_pulang' => $p->tipe_verifikasi_pulang ?? 'fingerprint',
                    'status' => $p->status,
                    'keterangan' => $p->keterangan ?? '-',
                ];
            }),
            'unchecked' => $unCheckedInList,
        ]);
    }

    private function get7DayChartData(): array
    {
        $dates = [];
        $hadir = [];
        $terlambat = [];
        $izin = [];

        for ($i = 6; $i >= 0; $i--) {
            $dt = Carbon::today()->subDays($i);
            $dateStr = $dt->toDateString();
            $dates[] = $dt->format('d M');

            $records = Presensi::whereDate('tanggal', $dateStr)->get();
            $hadir[] = $records->where('status', 'hadir')->count();
            $terlambat[] = $records->where('status', 'terlambat')->count();
            $izin[] = $records->whereIn('status', ['sakit', 'izin', 'dispen'])->count();
        }

        return [
            'labels' => $dates,
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'izin' => $izin,
        ];
    }
}
