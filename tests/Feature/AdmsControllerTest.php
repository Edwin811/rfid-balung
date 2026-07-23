<?php

namespace Tests\Feature;

use App\Models\Pegawai;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdmsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Pegawai::create([
            'id_mesin' => '101',
            'uid_kartu' => 'RF1001',
            'nama_lengkap' => 'Budi Santoso',
            'jabatan' => 'Kepala Desa',
            'is_active' => true,
        ]);
    }

    public function test_push_adms_attendance_success()
    {
        $response = $this->postJson('/api/adms/attendance', [
            'secret_key' => 'balunglor_secret_2026',
            'logs' => [
                [
                    'user_id' => '101',
                    'timestamp' => '2026-07-27 07:55:00',
                    'status' => 0,
                    'punch' => 0,
                ]
            ]
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'processed' => 1
        ]);

        $this->assertDatabaseHas('device_logs', [
            'id_mesin_device' => '101',
            'is_processed' => true,
        ]);

        $this->assertDatabaseHas('presensi', [
            'status' => 'hadir',
            'jam_masuk' => '07:55:00',
        ]);
    }
}
