@echo off
title Presensi Biometrik Desa Balunglor - Server Runner
cd /d "%~dp0"

echo ===============================================================
echo     SISTEM PRESENSI BIOMETRIK & RFID DESA BALUNGLOR
echo ===============================================================
echo.

:: Cek & Deteksi PHP di Laragon / XAMPP jika belum ada di PATH
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
    echo [ERROR] PHP tidak ditemukan di sistem! Silakan install Laragon/XAMPP.
    pause
    exit /b 1
)

echo [*] Memulai Web Server Presensi Desa Balunglor...
echo [*] Alamat: http://localhost:8000
echo ===============================================================

php -S 0.0.0.0:8000 -t public
pause
