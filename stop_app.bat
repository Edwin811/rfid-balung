@echo off
title Presensi Desa Balunglor - Stop Server
cd /d "%~dp0"

echo ===============================================================
echo   MEMATIKAN WEB SERVER PRESENSI DESA BALUNGLOR
echo ===============================================================
echo.

taskkill /F /IM httpd.exe >nul 2>&1
taskkill /F /IM php.exe >nul 2>&1
powershell -Command "Get-CimInstance Win32_Process | Where-Object { $_.CommandLine -like '*fetch_presensi.py*' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force }" >nul 2>&1

echo [OK] Seluruh proses server presensi (Apache, PHP, dan Layanan Sinkron Mesin) berhasil dihentikan!
echo.
pause
exit
