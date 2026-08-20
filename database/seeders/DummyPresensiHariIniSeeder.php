<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\Perizinan;
use App\Models\User;
use Carbon\Carbon;

class DummyPresensiHariIniSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $today = Carbon::today()->toDateString();
        $admin = User::first();
        $adminId = $admin ? $admin->id : 1;

        // Bersihkan data presensi hari ini terlebih dahulu agar fresh
        Presensi::whereDate('tanggal', $today)->delete();
        Perizinan::whereDate('tanggal_mulai', '<=', $today)
            ->whereDate('tanggal_selesai', '>=', $today)
            ->delete();

        // Ambil seluruh 15 aparatur desa
        $pegawaiList = Pegawai::orderBy('id_mesin')->get();

        if ($pegawaiList->isEmpty()) {
            $this->command->warn('Belum ada data pegawai. Silakan jalankan DatabaseSeeder terlebih dahulu.');
            return;
        }

        // Pemetaan Presensi Hari Ini:
        // Ada yang hadir lengkap (masuk & pulang), hadir masuk saja (belum pulang), terlambat, izin, sakit, dan BELUM ABSEN (null)
        $dummyMap = [
            1  => ['masuk' => '07:42:15', 'pulang' => '16:05:30', 'tipe_m' => 'rfid', 'tipe_p' => 'rfid', 'status' => 'hadir', 'ket' => 'Hadir Tepat Waktu'],
            2  => ['masuk' => '07:35:10', 'pulang' => '16:12:00', 'tipe_m' => 'fingerprint', 'tipe_p' => 'fingerprint', 'status' => 'hadir', 'ket' => 'Hadir Tepat Waktu'],
            3  => ['masuk' => '07:50:22', 'pulang' => '16:02:18', 'tipe_m' => 'rfid', 'tipe_p' => 'rfid', 'status' => 'hadir', 'ket' => 'Hadir Tepat Waktu'],
            4  => ['masuk' => '08:14:45', 'pulang' => '16:08:10', 'tipe_m' => 'fingerprint', 'tipe_p' => 'fingerprint', 'status' => 'terlambat', 'ket' => 'Terlambat 14 Menit'],
            5  => ['masuk' => '07:48:00', 'pulang' => '16:15:00', 'tipe_m' => 'rfid', 'tipe_p' => 'rfid', 'status' => 'hadir', 'ket' => 'Hadir Tepat Waktu'],
            6  => ['masuk' => '07:28:40', 'pulang' => '16:30:10', 'tipe_m' => 'rfid', 'tipe_p' => 'rfid', 'status' => 'hadir', 'ket' => 'Hadir Tepat Waktu'],
            7  => ['masuk' => null, 'pulang' => null, 'tipe_m' => null, 'tipe_p' => null, 'status' => 'izin', 'ket' => 'Izin Rapat Koordinasi Kecamatan'],
            8  => ['masuk' => '07:45:00', 'pulang' => '16:04:20', 'tipe_m' => 'fingerprint', 'tipe_p' => 'fingerprint', 'status' => 'hadir', 'ket' => 'Hadir Tepat Waktu'],
            9  => ['masuk' => '07:52:11', 'pulang' => null, 'tipe_m' => 'rfid', 'tipe_p' => null, 'status' => 'hadir', 'ket' => 'Hadir Masuk (Sedang Bekerja)'],
            10 => ['masuk' => null, 'pulang' => null, 'tipe_m' => null, 'tipe_p' => null, 'status' => 'sakit', 'ket' => 'Sakit Demam (Surat Keterangan Dokter)'],
            11 => ['masuk' => '08:08:20', 'pulang' => null, 'tipe_m' => 'fingerprint', 'tipe_p' => null, 'status' => 'terlambat', 'ket' => 'Terlambat 8 Menit (Belum Pulang)'],
            12 => null, // BELUM ABSEN (Tidak Hadir / Belum Tap Masuk)
            13 => ['masuk' => '07:55:00', 'pulang' => null, 'tipe_m' => 'rfid', 'tipe_p' => null, 'status' => 'hadir', 'ket' => 'Hadir Masuk (Sedang Bekerja)'],
            14 => null, // BELUM ABSEN (Tidak Hadir / Belum Tap Masuk)
            15 => null, // BELUM ABSEN (Tidak Hadir / Belum Tap Masuk)
        ];

        foreach ($pegawaiList as $p) {
            $idMesin = intval($p->id_mesin);
            $conf = $dummyMap[$idMesin] ?? null;

            // Jika diset null, pegawai ini dianggap BELUM ABSEN (Tidak ada record hari ini)
            if (!$conf) {
                continue;
            }

            // Jika izin atau sakit, buat juga record perizinan-nya
            if (in_array($conf['status'], ['izin', 'sakit'])) {
                Perizinan::create([
                    'pegawai_id' => $p->id,
                    'jenis_izin' => $conf['status'],
                    'tanggal_mulai' => $today,
                    'tanggal_selesai' => $today,
                    'keterangan' => $conf['ket'],
                    'dibuat_oleh' => $adminId,
                ]);
            }

            // Simpan data presensi hari ini
            Presensi::create([
                'pegawai_id' => $p->id,
                'tanggal' => $today,
                'jam_masuk' => $conf['masuk'],
                'jam_pulang' => $conf['pulang'],
                'tipe_verifikasi_masuk' => $conf['tipe_m'],
                'tipe_verifikasi_pulang' => $conf['tipe_p'],
                'status' => $conf['status'],
                'keterangan' => $conf['ket'],
                'updated_by' => $adminId,
            ]);
        }

        $this->command->info("Data presensi bervariasi hari ini ($today) BERHASIL dibuat!");
        $this->command->info("- Hadir Lengkap : 6 orang");
        $this->command->info("- Hadir (Belum Pulang) : 2 orang");
        $this->command->info("- Terlambat : 2 orang");
        $this->command->info("- Izin : 1 orang");
        $this->command->info("- Sakit : 1 orang");
        $this->command->info("- Belum Absen (Tidak Hadir) : 3 orang");
    }
}
