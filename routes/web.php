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

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/realtime', [DashboardController::class, 'realtimeApi'])->name('dashboard.realtime');

    // Presensi
    Route::get('/presensi', [PresensiController::class, 'index'])->name('presensi.index');
    Route::get('/presensi/sync-now', [PresensiController::class, 'syncNow'])->name('presensi.sync_now');
    Route::get('/api/presensi/harian-realtime', [PresensiController::class, 'harianRealtimeApi'])->name('presensi.harian_realtime');
    Route::post('/presensi/{id}', [PresensiController::class, 'update'])->name('presensi.update');

    // Kelola Aparatur Desa & RFID
    Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai.index');
    Route::post('/pegawai', [PegawaiController::class, 'store'])->name('pegawai.store');
    Route::post('/pegawai/sync-machine', [PegawaiController::class, 'syncToMachine'])->name('pegawai.sync_machine');
    Route::post('/pegawai/{id}', [PegawaiController::class, 'update'])->name('pegawai.update');
    Route::delete('/pegawai/{id}', [PegawaiController::class, 'destroy'])->name('pegawai.destroy');

    // Perizinan
    Route::get('/perizinan', [PerizinanController::class, 'index'])->name('perizinan.index');
    Route::post('/perizinan', [PerizinanController::class, 'store'])->name('perizinan.store');

    // Laporan & Export
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/pdf', [LaporanController::class, 'exportPdf'])->name('laporan.pdf');
    Route::get('/laporan/excel', [LaporanController::class, 'exportExcel'])->name('laporan.excel');

    // Audit Log (Admin, Kepala Desa, Wakil Kepala Desa)
    Route::middleware('role:admin,kepala_desa,wakil_kepala_desa')->group(function () {
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit_log.index');
    });

    // Backup Database & User Management (Khusus Admin)
    Route::middleware('role:admin')->group(function () {
        Route::get('/backup', [BackupController::class, 'index'])->name('backup.index');
        Route::post('/backup', [BackupController::class, 'store'])->name('backup.store');
        Route::post('/backup/csv', [BackupController::class, 'exportCsv'])->name('backup.csv');
        Route::post('/backup/clean-duplicates', [BackupController::class, 'cleanDuplicates'])->name('backup.clean_duplicates');
        Route::get('/backup/download/{filename}', [BackupController::class, 'download'])->name('backup.download');

        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::post('/users/{id}', [UserController::class, 'update'])->name('users.update');
    });
});
