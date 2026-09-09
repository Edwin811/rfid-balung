<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DeviceLog;
use App\Models\Presensi;

echo "Total DeviceLog in DB: " . DeviceLog::count() . PHP_EOL;
echo "Total Presensi in DB: " . Presensi::count() . PHP_EOL;

echo "\n--- 5 Terakhir di DeviceLog ---\n";
foreach (DeviceLog::latest('timestamp_absen')->take(5)->get() as $log) {
    echo "ID: {$log->id_mesin_device} | Waktu: {$log->timestamp_absen} | Tipe: {$log->tipe_verifikasi} | Processed: " . ($log->is_processed ? 'YES' : 'NO') . PHP_EOL;
}

echo "\n--- Presensi Hari Ini ---\n";
foreach (Presensi::whereDate('tanggal', date('Y-m-d'))->get() as $p) {
    echo "Pegawai ID: {$p->pegawai_id} | Tanggal: {$p->tanggal} | Masuk: {$p->jam_masuk} | Pulang: {$p->jam_pulang} | Status: {$p->status}" . PHP_EOL;
}
