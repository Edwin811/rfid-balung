<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use App\Models\Presensi;
use App\Models\Perizinan;
use App\Services\AttendanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AttendanceService $service;
    protected Pegawai $pegawai;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new AttendanceService();

        $this->pegawai = Pegawai::create([
            'id_mesin' => '101',
            'uid_kartu' => 'RF1001',
            'nama_lengkap' => 'Budi Santoso',
            'jabatan' => 'Kepala Desa',
            'is_active' => true,
        ]);
    }

    public function test_absen_masuk_tepat_waktu_status_hadir()
    {
        // Monday 2026-07-27 at 07:45:00
        $presensi = $this->service->processLog('101', '2026-07-27 07:45:00', 'fingerprint');

        $this->assertNotNull($presensi);
        $this->assertEquals('hadir', $presensi->status);
        $this->assertEquals('07:45:00', $presensi->jam_masuk);
    }

    public function test_absen_masuk_setelah_jam_8_status_terlambat()
    {
        // Monday 2026-07-27 at 08:15:00
        $presensi = $this->service->processLog('101', '2026-07-27 08:15:00', 'fingerprint');

        $this->assertNotNull($presensi);
        $this->assertEquals('terlambat', $presensi->status);
        $this->assertEquals('08:15:00', $presensi->jam_masuk);
    }

    public function test_absen_pulang_di_jam_kerja()
    {
        // First scan (masuk)
        $this->service->processLog('101', '2026-07-27 07:50:00', 'fingerprint');
        // Second scan (pulang at 16:30)
        $presensi = $this->service->processLog('101', '2026-07-27 16:30:00', 'fingerprint');

        $this->assertEquals('07:50:00', $presensi->jam_masuk);
        $this->assertEquals('16:30:00', $presensi->jam_pulang);
    }

    public function test_absen_akhir_pekan_diabaikan()
    {
        // Sunday 2026-07-26
        $presensi = $this->service->processLog('101', '2026-07-26 08:00:00', 'fingerprint');

        $this->assertNull($presensi);
        $this->assertDatabaseCount('presensi', 0);
    }

    public function test_prioritas_perizinan_override_absen()
    {
        // Add leave for 2026-07-27
        Perizinan::create([
            'pegawai_id' => $this->pegawai->id,
            'jenis_izin' => 'sakit',
            'tanggal_mulai' => '2026-07-27',
            'tanggal_selesai' => '2026-07-27',
            'keterangan' => 'Sakit Demam',
            'dibuat_oleh' => 1,
        ]);

        $presensi = $this->service->processLog('101', '2026-07-27 08:00:00', 'fingerprint');

        $this->assertNotNull($presensi);
        $this->assertEquals('sakit', $presensi->status);
    }
}
