@echo off
title Presensi Desa Balunglor - Stop Server
cd /d "%~dp0"

echo ===============================================================
echo   MEMATIKAN WEB SERVER PRESENSI DESA BALUNGLOR
echo ===============================================================
echo.

taskkill /F /IM php.exe >nul 2>&1
if %errorLevel% eq 0 (
    echo [OK] Seluruh proses server presensi di background berhasil dihentikan!
) else (
    echo [NOTE] Tidak ada server PHP yang sedang berjalan di background.
)

echo.
pause
