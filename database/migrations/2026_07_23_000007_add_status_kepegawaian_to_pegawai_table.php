<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->enum('status_kepegawaian', ['aktif', 'pensiun', 'mutasi', 'non_aktif'])->default('aktif')->after('jabatan');
        });
    }

    public function down(): void
    {
        Schema::table('pegawai', function (Blueprint $table) {
            $table->dropColumn('status_kepegawaian');
        });
    }
};
