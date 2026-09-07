@echo off
title Presensi Biometrik Desa Balunglor - Server Runner
cd /d "%~dp0"

echo ===============================================================
echo     SISTEM PRESENSI BIOMETRIK DAN RFID DESA BALUNGLOR
echo ===============================================================
echo.

:: 1. Auto-Start MySQL jika belum aktif (Tanpa perlu buka aplikasi Laragon)
powershell -Command "$c = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue; if (!$c) { exit 1 }" >nul 2>&1
if %errorLevel% neq 0 (
    echo [*] Mengaktifkan database MySQL di background...
    net start mysql >nul 2>&1
    if %errorLevel% neq 0 (
        for /d %%D in (C:\laragon\bin\mysql\mysql-*) do (
            if exist "%%D\bin\mysqld.exe" (
                start "" /B "%%D\bin\mysqld.exe" --console >nul 2>&1
            )
        )
    )
    timeout /t 2 /nobreak >nul 2>&1
)

:: 2. Cek dan Deteksi PHP di Laragon atau XAMPP jika belum ada di PATH
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
