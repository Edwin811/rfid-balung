<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

\Illuminate\Support\Facades\DB::statement("ALTER TABLE perizinan MODIFY COLUMN jenis_izin ENUM('sakit', 'izin', 'cuti', 'dispen') NOT NULL");
echo "Updated jenis_izin enum in perizinan table to include 'cuti'!\n";
