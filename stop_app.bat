@echo off
title Presensi Desa Balunglor - Stop Server
cd /d "%~dp0"

echo ===============================================================
echo   MEMATIKAN WEB SERVER PRESENSI DESA BALUNGLOR
echo ===============================================================
echo.

taskkill /F /IM httpd.exe >nul 2>&1
taskkill /F /IM php.exe >nul 2>&1

echo [OK] Seluruh proses server presensi (Apache & PHP) berhasil dihentikan!
echo.
pause
exit
