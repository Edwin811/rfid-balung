<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = new \Illuminate\Http\Request(['jenis_laporan' => 'bulanan', 'bulan' => 7, 'tahun' => 2026]);
$controller = app(\App\Http\Controllers\LaporanController::class);

$res = $controller->exportPdf($request);
echo "Response class: " . get_class($res) . "\n";
echo "Content-Type: " . $res->headers->get('content-type') . "\n";
echo "Content-Disposition: " . $res->headers->get('content-disposition') . "\n";
