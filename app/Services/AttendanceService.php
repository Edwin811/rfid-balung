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

        // BR-03: Hari kerja (Senin - Jumat). Sabtu (6) dan Minggu (0) diabaikan.
        if ($dt->isWeekend()) {
            Log::info("Absen diabaikan karena hari libur akhir pekan: {$timestampAbsen} (id_mesin: {$idMesinDevice})");
            return null;
        }

        // Cari data pegawai berdasarkan id_mesin
        $pegawai = Pegawai::where('id_mesin', $idMesinDevice)->where('is_active', true)->first();
        if (!$pegawai) {
            Log::warning("Pegawai dengan ID Mesin {$idMesinDevice} tidak ditemukan / non-aktif.");
            return null;
        }

        // BR-05: Cek prioritas perizinan aktif pada tanggal tersebut
        $izinAktif = Perizinan::where('pegawai_id', $pegawai->id)
            ->whereDate('tanggal_mulai', '<=', $tanggal)
            ->whereDate('tanggal_selesai', '>=', $tanggal)
            ->first();

        $presensi = Presensi::firstOrNew([
            'pegawai_id' => $pegawai->id,
            'tanggal' => $tanggal,
        ]);

        if ($izinAktif) {
            // Jika ada izin aktif, status otomatis mengikuti jenis izin (sakit, izin, dispen)
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
            // BR-02: Jam Pulang dicatat jika antara 16:00 dan 21:00
            if ($waktu >= '16:00:00' && $waktu <= '21:00:00') {
                $presensi->jam_pulang = $waktu;
                $presensi->tipe_verifikasi_pulang = $tipeVerifikasi ?? 'fingerprint';
            }
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
            if ($date->isWeekend()) {
                continue;
            }

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
