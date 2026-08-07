<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    \Illuminate\Support\Facades\DB::statement("ALTER TABLE perizinan MODIFY COLUMN jenis_izin ENUM('sakit', 'izin', 'cuti', 'dispen', 'skorsing') NOT NULL");
    echo "SUCCESS: Updated perizinan.jenis_izin enum to include 'skorsing'\n";
} catch (\Throwable $e) {
    echo "NOTICE: perizinan alter: " . $e->getMessage() . "\n";
}

try {
    \Illuminate\Support\Facades\DB::statement("ALTER TABLE presensi MODIFY COLUMN status ENUM('hadir', 'terlambat', 'tidak_hadir', 'sakit', 'izin', 'dispen', 'skorsing') NOT NULL DEFAULT 'tidak_hadir'");
    echo "SUCCESS: Updated presensi.status enum to include 'skorsing'\n";
} catch (\Throwable $e) {
    echo "NOTICE: presensi alter: " . $e->getMessage() . "\n";
}
