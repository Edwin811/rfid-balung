@echo off
title Presensi Desa Balunglor - Setup Custom Local Domain
cd /d "%~dp0"

echo ===============================================================
echo   PEMASANGAN DOMAIN LOKAL PRESENSI DESA BALUNGLOR
echo   Domain Default: http://presensi.balunglor.desa.id
echo ===============================================================
echo.

set "HOSTS_FILE=%WINDIR%\System32\drivers\etc\hosts"

:: Check for Administrative Privileges
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [NOTE] Meminta izin Administrator Windows...
    powershell -Command "Start-Process cmd.exe -ArgumentList '/k `\"\"%~f0\"`\"' -Verb RunAs"
    exit /b
)

findstr /C:"presensi.balunglor.desa.id" "%HOSTS_FILE%" >nul 2>&1
if %errorLevel% equ 0 (
    echo [OK] Domain http://presensi.balunglor.desa.id SUDAH TERPASANG di Windows Hosts!
) else (
    echo. >> "%HOSTS_FILE%"
    echo 127.0.0.1    presensi.balunglor.desa.id >> "%HOSTS_FILE%"
    echo 127.0.0.1    presensi.local >> "%HOSTS_FILE%"
    echo [OK] BERHASIL! Domain http://presensi.balunglor.desa.id berhasil didaftarkan!
)

echo.
echo ===============================================================
echo Alamat domain lokal Anda kini resmi aktif:
echo - http://presensi.balunglor.desa.id:8000
echo - http://presensi.local:8000
echo ===============================================================
echo.
pause
exit
