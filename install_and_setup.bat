@echo off
title Presensi Desa Balunglor - Installer & Setup Otomatis
cd /d "%~dp0"

echo ===============================================================
echo   INSTALLER & SETUP OTOMATIS PRESENSI BIOMETRIK DESA BALUNGLOR
echo ===============================================================
echo.

if not exist ".env" (
    echo [*] Membuat file konfigurasi .env...
    copy ".env.example" ".env"
    call php artisan key:generate
)

echo [*] Menyiapkan struktur database & 15 data aparatur desa...
call php artisan migrate:fresh --seed

echo.
echo ===============================================================
echo [OK] SETUP APLIKASI SELESAI!
echo ===============================================================
echo.
echo Apakah Anda ingin memasang AUTO-START agar aplikasi ini 
echo OTOMATIS NYALA di background setiap kali komputer dinyalakan?
echo (Tanpa perlu buka Laragon & Tanpa perlu ketik perintah apapun)
echo.
set /p autostart_choice="Pasang Auto-Start sekarang? (Y/N): "

if /i "%autostart_choice%"=="Y" goto DO_AUTOSTART
goto START_APP

:DO_AUTOSTART
powershell -ExecutionPolicy Bypass -File "%~dp0scripts\create_startup_shortcut.ps1"
echo.
echo [OK] BERHASIL! Aplikasi kini OTOMATIS AKTIF di background setiap kali Windows menyala!

:START_APP
echo.
echo [*] Memulai server sekarang...
start http://localhost:8000
call start_app.bat
