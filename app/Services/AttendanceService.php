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
            // Absen Pulang: Hanya dicatat jika scan dilakukan pada atau setelah jam 16:00 (sebelum jam 16:00 belum jam pulang)
            if ($waktu >= '16:00:00') {
                $presensi->jam_pulang = $waktu;
                $presensi->tipe_verifikasi_pulang = $tipeVerifikasi ?? 'fingerprint';
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
}
