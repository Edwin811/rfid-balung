@echo off
title Presensi Biometrik Desa Balunglor - Server Runner
cd /d "%~dp0"

echo ===============================================================
echo     SISTEM PRESENSI BIOMETRIK DAN RFID DESA BALUNGLOR
echo ===============================================================
echo.

:: Bersihkan proses PHP zombie sebelumnya agar port 8000 tidak bentrok
taskkill /F /IM php.exe >nul 2>&1

:: 1. Cek dan Deteksi PHP di Laragon atau XAMPP jika belum ada di PATH
where php >nul 2>&1
if %errorLevel% neq 0 (
    for /d %%D in (C:\laragon\bin\php\php-*) do (
        if exist "%%D\php.exe" (
            set "PATH=%%D;%PATH%"
        )
    )
    if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;%PATH%"
    )
)

where php >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] PHP tidak ditemukan di sistem!
    pause
    exit /b 1
)

:: Aktifkan cache untuk performa optimal
echo [*] Mengoptimalkan cache aplikasi...
php artisan config:cache >nul 2>&1
php artisan route:cache >nul 2>&1
php artisan view:cache >nul 2>&1

echo [*] Memulai Web Server Presensi Desa Balunglor...
echo [*] Alamat: http://localhost:8000
echo ===============================================================

php -S 0.0.0.0:8000 -t public
pause
