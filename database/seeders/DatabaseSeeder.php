<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed Users (4 Roles)
        $users = [
            [
                'name' => 'Administrator System',
                'username' => 'admin',
                'email' => 'admin@balunglor.desa.id',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ],
            [
                'name' => 'IMAM MUSTOFA (Kepala Desa)',
                'username' => 'kades',
                'email' => 'kades@balunglor.desa.id',
                'password' => Hash::make('password123'),
                'role' => 'kepala_desa',
                'is_active' => true,
            ],
            [
                'name' => 'MOH ANSORI (Sekretaris Desa)',
                'username' => 'wakilkades',
                'email' => 'sekdes@balunglor.desa.id',
                'password' => Hash::make('password123'),
                'role' => 'wakil_kepala_desa',
                'is_active' => true,
            ],
            [
                'name' => 'Operator Presensi Desa',
                'username' => 'operator',
                'email' => 'operator@balunglor.desa.id',
                'password' => Hash::make('password123'),
                'role' => 'operator',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(['username' => $userData['username']], $userData);
        }

        // 2. Seed Data Resmi Perangkat Desa Balunglor (Sesuai Portal PPID Resmi)
        $aparatur = [
            ['id_mesin' => '101', 'uid_kartu' => 'RF1001', 'nama_lengkap' => 'IMAM MUSTOFA', 'jabatan' => 'KEPALA DESA BALUNGLOR'],
            ['id_mesin' => '102', 'uid_kartu' => 'RF1002', 'nama_lengkap' => 'MOH ANSORI', 'jabatan' => 'SEKRETARIS DESA'],
            ['id_mesin' => '103', 'uid_kartu' => 'RF1003', 'nama_lengkap' => 'SUTOMO', 'jabatan' => 'KAUR UMUM & TU'],
            ['id_mesin' => '104', 'uid_kartu' => 'RF1004', 'nama_lengkap' => 'DIAN CANDRA DINATA', 'jabatan' => 'KAUR KEUANGAN'],
            ['id_mesin' => '105', 'uid_kartu' => 'RF1005', 'nama_lengkap' => 'HUSNAN SUBANDI', 'jabatan' => 'KAUR PERENCANAAN'],
            ['id_mesin' => '106', 'uid_kartu' => 'RF1006', 'nama_lengkap' => 'SHOLEH A. R', 'jabatan' => 'KASI PEMERINTAHAN'],
            ['id_mesin' => '107', 'uid_kartu' => 'RF1007', 'nama_lengkap' => 'SITI AMINAH', 'jabatan' => 'KASI KESEJAHTERAAN'],
            ['id_mesin' => '108', 'uid_kartu' => 'RF1008', 'nama_lengkap' => 'KHOIRUL ANAM', 'jabatan' => 'KASI PELAYANAN'],
            ['id_mesin' => '109', 'uid_kartu' => 'RF1009', 'nama_lengkap' => 'AGUS WARSITO', 'jabatan' => 'KASUN BALUNGLOR'],
            ['id_mesin' => '110', 'uid_kartu' => 'RF1010', 'nama_lengkap' => 'MOH. SUKUR', 'jabatan' => 'KASUN KEBON'],
            ['id_mesin' => '111', 'uid_kartu' => 'RF1011', 'nama_lengkap' => 'AGUS YULIONO', 'jabatan' => 'KASUN PURWOASRI'],
            ['id_mesin' => '112', 'uid_kartu' => 'RF1012', 'nama_lengkap' => 'INAMAH', 'jabatan' => 'STAF PELAYANAN'],
            ['id_mesin' => '113', 'uid_kartu' => 'RF1013', 'nama_lengkap' => 'ABDUL KHARIS', 'jabatan' => 'STAF PEMERINTAHAN'],
            ['id_mesin' => '114', 'uid_kartu' => 'RF1014', 'nama_lengkap' => 'YULIYATIN', 'jabatan' => 'STAF KEUANGAN'],
            ['id_mesin' => '115', 'uid_kartu' => 'RF1015', 'nama_lengkap' => 'YULI PRAHMITA PUTRI', 'jabatan' => 'STAF PERENCANAAN'],
            ['id_mesin' => '116', 'uid_kartu' => 'RF1016', 'nama_lengkap' => 'HERIK MA\'ARIFIN', 'jabatan' => 'STAF IT & OPERATOR'],
        ];

        foreach ($aparatur as $pegawaiData) {
            Pegawai::updateOrCreate(['id_mesin' => $pegawaiData['id_mesin']], $pegawaiData);
        }
    }
}
