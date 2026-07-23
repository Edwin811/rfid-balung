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
                'name' => 'Kepala Desa Balung Lor',
                'username' => 'kades',
                'email' => 'kades@balunglor.desa.id',
                'password' => Hash::make('password123'),
                'role' => 'kepala_desa',
                'is_active' => true,
            ],
            [
                'name' => 'Wakil Kepala Desa Balung Lor',
                'username' => 'wakilkades',
                'email' => 'wakilkades@balunglor.desa.id',
                'password' => Hash::make('password123'),
                'role' => 'wakil_kepala_desa',
                'is_active' => true,
            ],
            [
                'name' => 'Operator Presensi',
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

        // 2. Seed 16 Aparatur Desa Balung Lor
        $aparatur = [
            ['id_mesin' => '101', 'uid_kartu' => 'RF1001', 'nama_lengkap' => 'Budi Santoso, S.STP', 'jabatan' => 'Kepala Desa'],
            ['id_mesin' => '102', 'uid_kartu' => 'RF1002', 'nama_lengkap' => 'Siti Aminah, S.E.', 'jabatan' => 'Sekretaris Desa'],
            ['id_mesin' => '103', 'uid_kartu' => 'RF1003', 'nama_lengkap' => 'Ahmad Fauzi, S.IP', 'jabatan' => 'Kasi Pemerintahan'],
            ['id_mesin' => '104', 'uid_kartu' => 'RF1004', 'nama_lengkap' => 'Dewi Lestari, S.Sos', 'jabatan' => 'Kasi Kesejahteraan'],
            ['id_mesin' => '105', 'uid_kartu' => 'RF1005', 'nama_lengkap' => 'Hendra Wijaya', 'jabatan' => 'Kasi Pelayanan'],
            ['id_mesin' => '106', 'uid_kartu' => 'RF1006', 'nama_lengkap' => 'Rina Kartika, A.Md.', 'jabatan' => 'Kaur Keuangan'],
            ['id_mesin' => '107', 'uid_kartu' => 'RF1007', 'nama_lengkap' => 'Eko Prasetyo, S.T.', 'jabatan' => 'Kaur Perencanaan'],
            ['id_mesin' => '108', 'uid_kartu' => 'RF1008', 'nama_lengkap' => 'Sri Wahyuni', 'jabatan' => 'Kaur Umum & TU'],
            ['id_mesin' => '109', 'uid_kartu' => 'RF1009', 'nama_lengkap' => 'Bambang Supriyanto', 'jabatan' => 'Kasun Balung Lor'],
            ['id_mesin' => '110', 'uid_kartu' => 'RF1010', 'nama_lengkap' => 'Suryadi', 'jabatan' => 'Kasun Kebon'],
            ['id_mesin' => '111', 'uid_kartu' => 'RF1011', 'nama_lengkap' => 'M. Rizky Pratama', 'jabatan' => 'Kasun Purwoasri'],
            ['id_mesin' => '112', 'uid_kartu' => 'RF1012', 'nama_lengkap' => 'Nurul Hidayah', 'jabatan' => 'Staf Pelayanan'],
            ['id_mesin' => '113', 'uid_kartu' => 'RF1013', 'nama_lengkap' => 'Agus Setiawan', 'jabatan' => 'Staf Administrasi'],
            ['id_mesin' => '114', 'uid_kartu' => 'RF1014', 'nama_lengkap' => 'Dwi Handoko', 'jabatan' => 'Staf IT & Operator'],
            ['id_mesin' => '115', 'uid_kartu' => 'RF1015', 'nama_lengkap' => 'Lilik Rahmawati', 'jabatan' => 'Staf Keuangan'],
            ['id_mesin' => '116', 'uid_kartu' => 'RF1016', 'nama_lengkap' => 'Hadi Sucipto', 'jabatan' => 'Staf Umum'],
        ];

        foreach ($aparatur as $pegawaiData) {
            Pegawai::updateOrCreate(['id_mesin' => $pegawaiData['id_mesin']], $pegawaiData);
        }
    }
}
