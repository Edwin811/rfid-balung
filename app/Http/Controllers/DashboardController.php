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
            ->get();

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
            'showLateAlert',
            'chartData',
            'isDeviceOnline',
            'lastLog'
        ));
    }

    public function realtimeApi(): JsonResponse
    {
        $today = Carbon::today()->toDateString();
        $totalPegawai = Pegawai::where('is_active', true)->count();

        $presensiToday = Presensi::with('pegawai')
            ->whereDate('tanggal', $today)
            ->get();

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
                    'status' => $p->status,
                    'keterangan' => $p->keterangan ?? '-',
                ];
            }),
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
