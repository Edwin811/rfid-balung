<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pegawai', 'jumlah_jari')) {
            Schema::table('pegawai', function (Blueprint $table) {
                $table->integer('jumlah_jari')->default(0)->after('uid_kartu');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pegawai', 'jumlah_jari')) {
            Schema::table('pegawai', function (Blueprint $table) {
                $table->dropColumn('jumlah_jari');
            });
        }
    }
};
