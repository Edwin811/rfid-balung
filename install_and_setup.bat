@echo off
title Presensi Desa Balunglor - Installer & Setup Otomatis
cd /d "%~dp0"

echo ===============================================================
echo   INSTALLER & SETUP OTOMATIS PRESENSI BIOMETRIK DESA BALUNGLOR
echo ===============================================================
echo.

:: 1. Cek & Deteksi PHP di Laragon / XAMPP / PATH
where php >nul 2>&1
if %errorLevel% neq 0 (
    echo [*] Mencari PHP dari instalasi Laragon/XAMPP...
    for /d %%D in (C:\laragon\bin\php\php-*) do (
        if exist "%%D\php.exe" (
            set "PATH=%%D;%PATH%"
            echo [OK] Ditemukan PHP di: %%D
        )
    )
    if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;%PATH%"
        echo [OK] Ditemukan PHP di: C:\xampp\php
    )
)

:: Verifikasi apakah PHP tersedia
where php >nul 2>&1
if %errorLevel% neq 0 (
    echo.
    echo [ERROR] PHP tidak ditemukan di sistem ini!
    echo Silakan install atau buka Laragon / XAMPP terlebih dahulu.
    echo.
    pause
    exit /b 1
)

:: 2. Cek & Deteksi Composer
where composer >nul 2>&1
if %errorLevel% neq 0 (
    if exist "C:\laragon\bin\composer\composer.bat" (
        set "PATH=C:\laragon\bin\composer;%PATH%"
    )
    if exist "C:\ProgramData\ComposerSetup\bin\composer.bat" (
        set "PATH=C:\ProgramData\ComposerSetup\bin;%PATH%"
    )
)

:: 3. Jalankan Composer Install jika vendor belum ada
if not exist "vendor\autoload.php" (
    echo [*] Mengunduh pustaka Laravel (composer install)...
    call composer install
    if not exist "vendor\autoload.php" (
        echo.
        echo [ERROR] Pustaka vendor gagal terunduh.
        pause
        exit /b 1
    )
)

:: 4. Buat File .env jika belum ada
if not exist ".env" (
    echo [*] Menyiapkan file konfigurasi .env...
    copy ".env.example" ".env"
)

:: 5. Generate APP_KEY
echo [*] Menghasilkan kunci keamanan aplikasi (APP_KEY)...
call php artisan key:generate

:: 6. Jalankan Migrasi Database & Seeder
echo [*] Menyiapkan struktur database & 15 data aparatur desa...
call php artisan migrate:fresh --seed
if %errorLevel% neq 0 (
    echo.
    echo [NOTE] Jika migrasi gagal, pastikan MySQL di Laragon / XAMPP sudah berjalan (Start All).
    echo.
)

echo.
echo ===============================================================
echo [OK] SETUP APLIKASI SELESAI!
echo ===============================================================
echo.
echo Apakah Anda ingin memasang AUTO-START agar aplikasi ini 
echo OTOMATIS NYALA di background setiap kali komputer dinyalakan?
echo (Tanpa perlu buka Laragon & Tanpa perlu ketik perintah apapun)
echo.
set /p autostart_choice="Pasang Auto-Start sekarang? (Y/N): "

if /i "%autostart_choice%"=="Y" goto DO_AUTOSTART
goto START_APP

:DO_AUTOSTART
powershell -ExecutionPolicy Bypass -File "%~dp0scripts\create_startup_shortcut.ps1"
echo.
echo [OK] BERHASIL! Aplikasi kini OTOMATIS AKTIF di background setiap kali Windows menyala!

:START_APP
echo.
echo [*] Memulai server sekarang...
start http://localhost:8000
call start_app.bat
pause
