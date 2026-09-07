@echo off
title Presensi Desa Balunglor - Installer dan Setup Otomatis
cd /d "%~dp0"

echo ===============================================================
echo   INSTALLER DAN SETUP OTOMATIS PRESENSI DESA BALUNGLOR
echo ===============================================================
echo.

:: 1. Cek dan Deteksi PHP di Laragon atau XAMPP atau PATH
where php >nul 2>&1
if %errorLevel% neq 0 (
    echo [*] Mencari PHP dari instalasi Laragon atau XAMPP...
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
    echo Silakan install atau buka Laragon atau XAMPP terlebih dahulu.
    echo.
    pause
    exit /b 1
)

:: 2. Cek dan Deteksi Composer / Auto-Download composer.phar
if not exist "vendor\autoload.php" (
    echo [*] Memeriksa pustaka vendor Laravel...
    where composer >nul 2>&1
    if %errorLevel% neq 0 (
        if exist "C:\laragon\bin\composer\composer.bat" (
            set "PATH=C:\laragon\bin\composer;%PATH%"
        ) else if exist "C:\ProgramData\ComposerSetup\bin\composer.bat" (
            set "PATH=C:\ProgramData\ComposerSetup\bin;%PATH%"
        )
    )

    where composer >nul 2>&1
    if %errorLevel% eq 0 (
        echo [*] Menjalankan composer install...
        call composer install --no-interaction --prefer-dist --ignore-platform-reqs
    ) else (
        echo [*] Composer sistem belum terpasang. Mengunduh portable composer.phar...
        php -r "copy('https://getcomposer.org/download/latest-stable/composer.phar', 'composer.phar');"
        if exist "composer.phar" (
            echo [*] Menjalankan instalasi dependensi via portable composer...
            php composer.phar install --no-interaction --prefer-dist --ignore-platform-reqs
        ) else (
            echo.
            echo [ERROR] Gagal mengunduh Composer otomatis.
            echo Silakan unduh dan pasang Composer di: https://getcomposer.org/Composer-Setup.exe
            echo.
            pause
            exit /b 1
        )
    )
) else (
    echo [OK] Pustaka vendor sudah tersedia. Melewati proses composer install.
)

:: 4. Buat File .env jika belum ada
if not exist ".env" (
    echo [*] Menyiapkan file konfigurasi .env...
    copy ".env.example" ".env"
)

:: 5. Generate APP_KEY
echo [*] Menghasilkan kunci keamanan aplikasi APP_KEY...
call php artisan key:generate

:: 6. Jalankan Migrasi Database dan Seeder
echo [*] Menyiapkan struktur database dan 15 data aparatur desa...
call php artisan migrate:fresh --seed
if %errorLevel% neq 0 (
    echo.
    echo [NOTE] Jika migrasi gagal, pastikan MySQL di Laragon atau XAMPP sudah berjalan.
    echo.
)

echo.
echo ===============================================================
echo [OK] SETUP APLIKASI SELESAI!
echo ===============================================================
echo.
echo Apakah Anda ingin memasang AUTO-START agar aplikasi ini 
echo OTOMATIS NYALA di background setiap kali komputer dinyalakan?
echo (Tanpa perlu buka Laragon dan Tanpa perlu ketik perintah apapun)
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
