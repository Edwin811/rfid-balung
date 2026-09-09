<?php

namespace App\Services;

use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\Perizinan;
use App\Models\DeviceLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AttendanceService
{
    /**
     * Fast availability check with 4s caching
     * Mendukung deteksi mesin baik via ICMP ping maupun TCP/UDP socket.
     */
    public static function isDeviceOnline(): bool
    {
        return \Illuminate\Support\Facades\Cache::remember('zk_device_online_status', 4, function () {
            $ip = env('ZKT_IP', '192.168.1.23');
            $port = intval(env('ZKT_PORT', 4370));

            // 1. Coba ping cepat (200ms) - cocok untuk mesin dengan protokol UDP
            if (PHP_OS_FAMILY === 'Windows') {
                $pingCmd = "ping -n 1 -w 200 " . escapeshellarg($ip);
            } else {
                $pingCmd = "ping -c 1 -W 1 " . escapeshellarg($ip);
            }
            @exec($pingCmd, $output, $resultCode);
            if ($resultCode === 0) {
                return true;
            }

            // 2. Coba TCP socket (300ms) - cocok untuk mesin dengan protokol TCP
            $fp = @fsockopen($ip, $port, $errno, $errstr, 0.3);
            if ($fp) {
                fclose($fp);
                return true;
            }

            return false;
        });
    }

    /**
     * Process a raw log record into presensi table
     */
    public function processLog(string $idMesinDevice, string $timestampAbsen, ?string $tipeVerifikasi = 'fingerprint', ?array $rawData = null): ?Presensi
    {
        $dt = Carbon::parse($timestampAbsen);
        $tanggal = $dt->toDateString();
        $waktu = $dt->toTimeString();

        // Cari data pegawai berdasarkan id_mesin
        $pegawai = Pegawai::where('id_mesin', (string) $idMesinDevice)->first();
        if (!$pegawai) {
            // Auto-create pegawai agar data dari mesin fisik tidak pernah terbuang
            $pegawai = Pegawai::create([
                'id_mesin' => (string) $idMesinDevice,
                'nama_lengkap' => 'Aparatur ID ' . $idMesinDevice,
                'jabatan' => 'Aparatur Desa',
                'is_active' => true,
            ]);
            Log::info("Auto-created Pegawai untuk ID Mesin {$idMesinDevice}");
        }

        // BR-03: Hari kerja (Senin - Jumat). Sabtu & Minggu tetap dapat dicatat bila di-override.
        $presensi = Presensi::firstOrNew([
            'pegawai_id' => $pegawai->id,
            'tanggal' => $tanggal,
        ]);

        // BR-05: Cek prioritas perizinan aktif pada tanggal tersebut
        $izinAktif = Perizinan::where('pegawai_id', $pegawai->id)
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->first();

        if ($izinAktif) {
            $presensi->status = $izinAktif->jenis_izin;
            $presensi->keterangan = $izinAktif->keterangan ?? "Perizinan: " . ucfirst($izinAktif->jenis_izin);
            $presensi->save();
            return $presensi;
        }

        // BR-04 & BR-01: Absen Pertama = Jam Masuk
        if (!$presensi->exists || is_null($presensi->jam_masuk)) {
            $presensi->jam_masuk = $waktu;
            $presensi->tipe_verifikasi_masuk = $tipeVerifikasi ?? 'fingerprint';

            // BR-01: Jam masuk <= 08:00 -> Hadir, > 08:00 -> Terlambat
            if ($waktu <= '08:00:59') {
                $presensi->status = 'hadir';
            } else {
                $presensi->status = 'terlambat';
            }
        } else {
            // Absen Pulang (Scan Kedua atau Lanjutan)
            if ($waktu >= '16:00:00') {
                // Pulang Reguler (Mulai jam 16:00:00 WIB ke atas)
                $presensi->jam_pulang = $waktu;
                $presensi->tipe_verifikasi_pulang = $tipeVerifikasi ?? 'fingerprint';
            } else {
                // Scan Sebelum Jam 16:00 WIB:
                // HANYA dicatat sebagai Jam Pulang jika aparatur SUDAH MEMILIKI PERIZINAN RESMI (Izin/Dispen/Cuti) di sistem
                $hasLeave = Perizinan::where('pegawai_id', $pegawai->id)
                    ->whereDate('tanggal_mulai', '<=', $tanggal)
                    ->whereDate('tanggal_selesai', '>=', $tanggal)
                    ->exists();

                if ($hasLeave) {
                    $selisihMenit = Carbon::parse($presensi->jam_masuk)->diffInMinutes(Carbon::parse($waktu));
                    if ($selisihMenit >= 10) {
                        $presensi->jam_pulang = $waktu;
                        $presensi->tipe_verifikasi_pulang = $tipeVerifikasi ?? 'fingerprint';
                    }
                }
            }
        }

        // Always ensure verification type is updated if provided
        if ($presensi->jam_masuk && $waktu === $presensi->jam_masuk) {
            $presensi->tipe_verifikasi_masuk = $tipeVerifikasi ?? 'fingerprint';
        }
        if ($presensi->jam_pulang && $waktu === $presensi->jam_pulang) {
            $presensi->tipe_verifikasi_pulang = $tipeVerifikasi ?? 'fingerprint';
        }

        $presensi->save();
        return $presensi;
    }

    /**
     * Batch process raw device logs
     */
    public function processUnprocessedDeviceLogs(): int
    {
        $logs = DeviceLog::where('is_processed', false)->orderBy('timestamp_absen', 'asc')->get();
        $processedCount = 0;

        foreach ($logs as $deviceLog) {
            $this->processLog(
                $deviceLog->id_mesin_device,
                $deviceLog->timestamp_absen->toDateTimeString(),
                $deviceLog->tipe_verifikasi ?? 'fingerprint',
                $deviceLog->raw_data
            );
            $deviceLog->is_processed = true;
            $deviceLog->save();
            $processedCount++;
        }

        return $processedCount;
    }

    /**
     * Sync attendance for a date range when leave is added
     */
    public function syncLeaveToAttendance(Perizinan $perizinan): void
    {
        $start = Carbon::parse($perizinan->tanggal_mulai);
        $end = Carbon::parse($perizinan->tanggal_selesai);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $tanggalStr = $date->toDateString();
            $presensi = Presensi::firstOrNew([
                'pegawai_id' => $perizinan->pegawai_id,
                'tanggal' => $tanggalStr,
            ]);

            $presensi->status = $perizinan->jenis_izin;
            $presensi->keterangan = "Perizinan: " . ucfirst($perizinan->jenis_izin) . " (" . ($perizinan->keterangan ?? '-') . ")";
            $presensi->save();
        }
    }

    /**
     * Remove leave and recalculate attendance for a date range when leave is deleted
     */
    public function removeLeaveFromAttendance(Perizinan $perizinan): void
    {
        $start = Carbon::parse($perizinan->tanggal_mulai);
        $end = Carbon::parse($perizinan->tanggal_selesai);

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $tanggalStr = $date->toDateString();
            $presensi = Presensi::where('pegawai_id', $perizinan->pegawai_id)
                ->where('tanggal', $tanggalStr)
                ->first();

            if ($presensi) {
                if ($presensi->jam_masuk) {
                    $presensi->status = ($presensi->jam_masuk <= '08:00:59') ? 'hadir' : 'terlambat';
                    $presensi->keterangan = null;
                    $presensi->save();
                } else {
                    $presensi->delete();
                }
            }
        }
    }
}
