<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PresensiController;
use App\Http\Controllers\PerizinanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PegawaiController;

// Guest Routes (Dilindungi Throttle Rate Limiter 5 attempt/min)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

// Authenticated Routes (Semua rute wajib login)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard (Read-Only)
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/realtime', [DashboardController::class, 'realtimeApi'])->name('dashboard.realtime');

    // Read-Only Views (Semua User Login: Admin, Operator, Kades, Wakil Kades)
    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::get('/presensi/sync-now', [PresensiController::class, 'syncNow'])->name('presensi.sync_now');
    Route::get('/api/presensi/harian-realtime', [PresensiController::class, 'harianRealtimeApi'])->name('presensi.harian_realtime');
    
    Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai.index');
    Route::get('/perizinan', [PerizinanController::class, 'index'])->name('perizinan.index');
    
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('/laporan/excel', [LaporanController::class, 'exportExcel'])->name('laporan.excel');

    // Audit Log (Admin, Kades, Wakil Kades)
    Route::middleware('role:admin,kepala_desa,wakil_kepala_desa')->group(function () {
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit_log.index');
    });

    // Aksi Operasional (Khusus Admin & Operator)
    Route::middleware('role:admin,operator')->group(function () {
        // Edit Status Presensi
        Route::post('/presensi/{id}', [PresensiController::class, 'update'])->name('presensi.update');

        // Kelola Aparatur Desa
        Route::post('/pegawai', [PegawaiController::class, 'store'])->name('pegawai.store');
        Route::match(['post', 'put'], '/pegawai/{id}', [PegawaiController::class, 'update'])->name('pegawai.update');
        Route::post('/pegawai/{id}/restore', [PegawaiController::class, 'restore'])->name('pegawai.restore');
        Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy'])->name('pegawai.destroy');
        Route::post('/pegawai/{id}/delete-fingerprint', [PegawaiController::class, 'deleteFingerprint'])->name('pegawai.delete_fingerprint');

        // Kelola Perizinan
        Route::post('/perizinan', [PerizinanController::class, 'store'])->name('perizinan.store');
        Route::match(['post', 'put'], '/perizinan/{id}', [PerizinanController::class, 'update'])->name('perizinan.update');
        Route::delete('/perizinan/{id}', [PerizinanController::class, 'destroy'])->name('perizinan.destroy');
    });

    // Aksi Administrator Sistem (Khusus Admin 100%)
    Route::middleware('role:admin')->group(function () {
        // Sync & Pull Mesin Fisik
        Route::post('/pegawai/sync-machine', [PegawaiController::class, 'syncToMachine'])->name('pegawai.sync_machine');
        Route::post('/pegawai/pull-machine', [PegawaiController::class, 'pullFromMachine'])->name('pegawai.pull_machine');

        // Backup Database & Clean Log
        Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
        Route::post('/backup', [BackupController::class, 'store'])->name('backup.store');
        Route::post('/backup/csv', [BackupController::class, 'exportCsv'])->name('backup.csv');
        Route::post('/backup/clean-duplicates', [BackupController::class, 'cleanDuplicates'])->name('backup.clean_duplicates');
        Route::get('/backup/download/{filename}', [BackupController::class, 'download'])->name('backup.download');

        // Manajemen Pengguna Sistem
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
    });
});
