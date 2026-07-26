<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

if (!Schema::hasColumn('perizinan', 'file_surat')) {
    Schema::table('perizinan', function (Blueprint $table) {
        $table->string('file_surat')->nullable()->after('keterangan');
    });
    echo "Added file_surat column to perizinan table successfully!\n";
} else {
    echo "file_surat column already exists in perizinan table.\n";
}
