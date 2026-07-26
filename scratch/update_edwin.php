<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$affected = \Illuminate\Support\Facades\DB::table('pegawai')
    ->where('nama_lengkap', 'like', '%Edwin%')
    ->orWhere('id_mesin', 2)
    ->update(['jabatan' => 'DEVELOPER SYSTEM']);

echo "Successfully updated {$affected} row(s) in database!\n";
