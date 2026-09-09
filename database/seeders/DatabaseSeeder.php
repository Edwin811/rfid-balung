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
                'password' => 'password123',
                'role' => 'admin',
                'is_active' => true,
            ],
            [
                'name' => 'IMAM MUSTOFA (Kepala Desa)',
                'username' => 'kades',
                'email' => 'kades@balunglor.desa.id',
                'password' => 'password123',
                'role' => 'kepala_desa',
                'is_active' => true,
            ],
            [
                'name' => 'MOH ANSORI (Sekretaris Desa)',
                'username' => 'wakilkades',
                'email' => 'sekdes@balunglor.desa.id',
                'password' => 'password123',
                'role' => 'wakil_kepala_desa',
                'is_active' => true,
            ],
            [
                'name' => 'Operator Presensi Desa',
                'username' => 'operator',
                'email' => 'operator@balunglor.desa.id',
                'password' => 'password123',
                'role' => 'operator',
                'is_active' => true,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(['username' => $userData['username']], $userData);
        }

        $aparatur = [
            ['id_mesin' => '1', 'uid_kartu' => '1003', 'nama_lengkap' => 'IMAM MUSTOFA', 'jabatan' => 'KEPALA DESA'],
            ['id_mesin' => '2', 'uid_kartu' => '1004', 'nama_lengkap' => 'MOH. ANSORI', 'jabatan' => 'SEKRETARIS DESA'],
            ['id_mesin' => '3', 'uid_kartu' => '1005', 'nama_lengkap' => 'SHOLEH A R', 'jabatan' => 'KASI PEMERINTAHAN'],
            ['id_mesin' => '4', 'uid_kartu' => '1006', 'nama_lengkap' => 'SITI AMINAH', 'jabatan' => 'KASI KESRA'],
            ['id_mesin' => '5', 'uid_kartu' => '1007', 'nama_lengkap' => 'KHOIRUL ANAM', 'jabatan' => 'KASI PELAYANAN'],
            ['id_mesin' => '6', 'uid_kartu' => '0002745299', 'nama_lengkap' => 'DIAN CANDRA DINATA', 'jabatan' => 'KAUR KEUANGAN'],
            ['id_mesin' => '7', 'uid_kartu' => '0002659221', 'nama_lengkap' => 'HUSNAN SUBANDI', 'jabatan' => 'KAUR PERENCANAAN'],
            ['id_mesin' => '8', 'uid_kartu' => '0002641926', 'nama_lengkap' => 'SUTOMO', 'jabatan' => 'KAUR TU DAN UMUM'],
            ['id_mesin' => '9', 'uid_kartu' => '0002681592', 'nama_lengkap' => 'ASMAD', 'jabatan' => 'KASUN KARANG ANYAR'],
            ['id_mesin' => '10', 'uid_kartu' => '0002893203', 'nama_lengkap' => 'NUR ROHMAT', 'jabatan' => 'KASUN KRAJAN'],
            ['id_mesin' => '11', 'uid_kartu' => '0002645639', 'nama_lengkap' => 'NURHOLIK', 'jabatan' => 'KASUN KEBONSARI'],
            ['id_mesin' => '12', 'uid_kartu' => '0002622742', 'nama_lengkap' => 'SUGENG RAWUH', 'jabatan' => 'KASUN WETAN KALI'],
            ['id_mesin' => '13', 'uid_kartu' => '0002679422', 'nama_lengkap' => 'ANANG SETIAWAN', 'jabatan' => 'STAF KAUR KEUANGAN'],
            ['id_mesin' => '14', 'uid_kartu' => '0002665748', 'nama_lengkap' => 'ABDUL WAHIT', 'jabatan' => 'OPERATOR DESA'],
            ['id_mesin' => '15', 'uid_kartu' => '0008811783', 'nama_lengkap' => 'Superadmin', 'jabatan' => 'DEVELOPER SYSTEM'],
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
