@echo off
title Presensi Biometrik Desa Balunglor - Server Runner
cd /d "%~dp0"

echo ===============================================================
echo     SISTEM PRESENSI BIOMETRIK & RFID DESA BALUNGLOR
echo ===============================================================
echo [*] Memulai Web Server Presensi Desa Balunglor...
echo [*] Domain Lokal: http://presensi.balunglor.desa.id
echo [*] Alamat Lokal: http://localhost:8000
echo ===============================================================

php -S 0.0.0.0:80 -t public >nul 2>&1
if %errorLevel% neq 0 (
    php -S 0.0.0.0:8000 -t public
)
