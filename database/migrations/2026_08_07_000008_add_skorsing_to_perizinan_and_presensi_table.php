<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE perizinan MODIFY COLUMN jenis_izin ENUM('sakit', 'izin', 'cuti', 'dispen', 'skorsing') NOT NULL");
            DB::statement("ALTER TABLE presensi MODIFY COLUMN status ENUM('hadir', 'terlambat', 'tidak_hadir', 'sakit', 'izin', 'dispen', 'skorsing') NOT NULL DEFAULT 'tidak_hadir'");
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE perizinan MODIFY COLUMN jenis_izin ENUM('sakit', 'izin', 'cuti', 'dispen') NOT NULL");
            DB::statement("ALTER TABLE presensi MODIFY COLUMN status ENUM('hadir', 'terlambat', 'tidak_hadir', 'sakit', 'izin', 'dispen') NOT NULL DEFAULT 'tidak_hadir'");
        }
    }
};
