@echo off
title Presensi Desa Balunglor - Setup Auto-Start Windows
cd /d "%~dp0"

set "SHORTCUT_PATH=%APPDATA%\Microsoft\Windows\Start Menu\Programs\Startup\PresensiBalunglor.lnk"

echo ===============================================================
echo     PEMASANGAN AUTO-START WINDOWS PRESENSI BALUNGLOR
echo ===============================================================
echo.
echo 1. Pasang Auto-Start (Berjalan Otomatis saat Windows Dinyalakan)
echo 2. Hapus Auto-Start (Matikan Otomatisasi saat Booting)
echo.
set /p choice="Pilih Opsi (1/2): "

if "%choice%"=="1" goto DO_INSTALL
if "%choice%"=="2" goto DO_REMOVE
echo Pilihan tidak valid.
goto END

:DO_INSTALL
powershell -ExecutionPolicy Bypass -File "%~dp0scripts\create_startup_shortcut.ps1"
echo.
echo [OK] BERHASIL! Aplikasi Presensi Balunglor kini akan OTOMATIS BERJALAN di background setiap kali komputer Windows dinyalakan!
echo [INFO] Alamat Server: http://localhost:8000
goto END

:DO_REMOVE
if exist "%SHORTCUT_PATH%" (
    del "%SHORTCUT_PATH%"
    echo.
    echo [OK] Auto-Start berhasil dihapus.
) else (
    echo.
    echo [NOTE] Shortcut Auto-Start tidak ditemukan.
)
goto END

:END
echo.
pause
