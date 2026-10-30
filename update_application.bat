@echo off
title Presensi Desa Balunglor - Safe Updater
cd /d "%~dp0"
setlocal EnableExtensions EnableDelayedExpansion

echo ===============================================================
echo   PEMBARUAN AMAN SISTEM PRESENSI DESA BALUNGLOR
echo ===============================================================
echo.
echo Skrip ini TIDAK membuat APP_KEY baru, TIDAK menjalankan seeder,
echo dan TIDAK menghapus database maupun file .env.
echo.

if not exist ".env" (
    echo [ERROR] File .env tidak ditemukan. Salin .env dari instalasi lama terlebih dahulu.
    pause
    exit /b 1
)

where php >nul 2>&1
if errorlevel 1 (
    for %%D in (C D E F) do (
        for /d %%P in (%%D:\laragon\bin\php\php-*) do (
            if exist "%%P\php.exe" set "PATH=%%P;!PATH!"
        )
    )
)

where php >nul 2>&1
if errorlevel 1 (
    echo [ERROR] PHP Laragon tidak ditemukan.
    pause
    exit /b 1
)

set "PYTHON_EXEC="
for /f "usebackq tokens=1,* delims==" %%A in (".env") do (
    if /I "%%A"=="PYTHON_BIN" set "PYTHON_EXEC=%%B"
)
set "PYTHON_EXEC=!PYTHON_EXEC:"=!"

if not defined PYTHON_EXEC (
    echo [ERROR] PYTHON_BIN belum diisi di file .env.
    pause
    exit /b 1
)

if not exist "!PYTHON_EXEC!" (
    echo [ERROR] PYTHON_BIN tidak ditemukan: !PYTHON_EXEC!
    echo Periksa versi/path Python Laragon lalu perbarui .env.
    pause
    exit /b 1
)

echo [1/4] Memeriksa Python: !PYTHON_EXEC!
"!PYTHON_EXEC!" -c "import requests, zk; print('OK: requests dan pyzk tersedia')"
if errorlevel 1 (
    echo [*] Memasang dependensi Python yang belum tersedia...
    "!PYTHON_EXEC!" -m pip install -r requirements.txt
    if errorlevel 1 (
        echo [ERROR] Gagal memasang dependensi Python.
        pause
        exit /b 1
    )
)

echo [2/4] Menjalankan migrasi database yang aman...
php artisan migrate --force
if errorlevel 1 (
    echo [ERROR] Migrasi database gagal. Aplikasi tidak dijalankan ulang.
    pause
    exit /b 1
)

echo [3/4] Memperbarui cache Laravel...
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo [4/4] Menyalakan ulang layanan presensi...
powershell -Command "Get-CimInstance Win32_Process | Where-Object { $_.CommandLine -match 'fetch_presensi.py|artisan (serve|schedule:work)' } | ForEach-Object { Stop-Process -Id $_.ProcessId -Force }" >nul 2>&1
wscript "%~dp0run_background.vbs"

echo.
echo [OK] Update selesai. Tunggu sekitar 15 detik, lalu buka aplikasi dan uji satu scan presensi.
pause
