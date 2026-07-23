<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Pegawai;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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

        // 2. Seed Data Resmi Perangkat Desa Balunglor (Eksak Presisi dengan Mesin ZKTeco Real)
        $aparatur = [
            ['id_mesin' => '1', 'uid_kartu' => '0008811783', 'nama_lengkap' => 'Tes', 'jabatan' => 'KEPALA DESA BALUNGLOR'],
            ['id_mesin' => '2', 'uid_kartu' => '0002660909', 'nama_lengkap' => 'Edwin', 'jabatan' => 'SEKRETARIS DESA'],
            ['id_mesin' => '3', 'uid_kartu' => 'RF1003', 'nama_lengkap' => 'SUTOMO', 'jabatan' => 'KAUR UMUM & TU'],
            ['id_mesin' => '4', 'uid_kartu' => 'RF1004', 'nama_lengkap' => 'DIAN CANDRA DINATA', 'jabatan' => 'KAUR KEUANGAN'],
            ['id_mesin' => '5', 'uid_kartu' => 'RF1005', 'nama_lengkap' => 'HUSNAN SUBANDI', 'jabatan' => 'KAUR PERENCANAAN'],
            ['id_mesin' => '6', 'uid_kartu' => 'RF1006', 'nama_lengkap' => 'SHOLEH A. R', 'jabatan' => 'KASI PEMERINTAHAN'],
            ['id_mesin' => '7', 'uid_kartu' => 'RF1007', 'nama_lengkap' => 'SITI AMINAH', 'jabatan' => 'KASI KESEJAHTERAAN'],
            ['id_mesin' => '8', 'uid_kartu' => 'RF1008', 'nama_lengkap' => 'KHOIRUL ANAM', 'jabatan' => 'KASI PELAYANAN'],
            ['id_mesin' => '9', 'uid_kartu' => 'RF1009', 'nama_lengkap' => 'AGUS WARSITO', 'jabatan' => 'KASUN BALUNGLOR'],
            ['id_mesin' => '10', 'uid_kartu' => 'RF1010', 'nama_lengkap' => 'MOH. SUKUR', 'jabatan' => 'KASUN KEBON'],
            ['id_mesin' => '11', 'uid_kartu' => 'RF1011', 'nama_lengkap' => 'AGUS YULIONO', 'jabatan' => 'KASUN PURWOASRI'],
            ['id_mesin' => '12', 'uid_kartu' => 'RF1012', 'nama_lengkap' => 'INAMAH', 'jabatan' => 'STAF PELAYANAN'],
            ['id_mesin' => '13', 'uid_kartu' => 'RF1013', 'nama_lengkap' => 'ABDUL KHARIS', 'jabatan' => 'STAF PEMERINTAHAN'],
            ['id_mesin' => '14', 'uid_kartu' => 'RF1014', 'nama_lengkap' => 'YULIYATIN', 'jabatan' => 'STAF KEUANGAN'],
            ['id_mesin' => '15', 'uid_kartu' => 'RF1015', 'nama_lengkap' => 'YULI PRAHMITA PUTRI', 'jabatan' => 'STAF PERENCANAAN'],
            ['id_mesin' => '16', 'uid_kartu' => 'RF1016', 'nama_lengkap' => 'HERIK MA\'ARIFIN', 'jabatan' => 'STAF IT & OPERATOR'],
        ];

        // Bersihkan entri lama agar presisi
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Pegawai::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($aparatur as $pegawaiData) {
            Pegawai::create(array_merge($pegawaiData, ['is_active' => true]));
        }
    }
}
