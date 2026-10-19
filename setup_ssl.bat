@echo off
title Presensi Desa Balunglor - Setup SSL HTTPS Otomatis
cd /d "%~dp0"
setlocal enabledelayedexpansion

echo ===============================================================
echo   PEMASANGAN SERTIFIKAT SSL HTTPS PRESENSI DESA BALUNGLOR
echo   Domain: https://presensi.balunglor.desa.id
echo ===============================================================
echo.

:: 1. Meminta Izin Administrator Windows (skip jika dipanggil dari installer)
if not defined CALLED_FROM_INSTALLER (
    net session >nul 2>&1
    if %errorLevel% neq 0 (
        echo [*] Meminta izin Administrator Windows untuk mendaftarkan sertifikat...
        powershell -Command "Start-Process cmd.exe -ArgumentList '/k `\"\"%~f0\"`\"' -Verb RunAs"
        exit /b
    )
)

:: 2. Matikan proses web server lama agar tidak bentrok (skip jika dari installer)
if not defined CALLED_FROM_INSTALLER (
    taskkill /F /IM php.exe >nul 2>&1
    taskkill /F /IM httpd.exe >nul 2>&1
)

:: 3. Daftarkan Domain ke Windows hosts
set "HOSTS_FILE=%WINDIR%\System32\drivers\etc\hosts"
findstr /C:"presensi.balunglor.desa.id" "%HOSTS_FILE%" >nul 2>&1
if %errorLevel% neq 0 (
    echo. >> "%HOSTS_FILE%"
    echo 127.0.0.1    presensi.balunglor.desa.id >> "%HOSTS_FILE%"
    echo 127.0.0.1    presensi.local >> "%HOSTS_FILE%"
    echo [OK] Domain presensi.balunglor.desa.id berhasil didaftarkan ke hosts!
) else (
    echo [OK] Domain presensi.balunglor.desa.id sudah ada di hosts.
)

:: 4. Deteksi Laragon di semua drive (C, D, E, F)
set "LARAGON_DIR="
for %%L in (C D E F) do (
    if exist "%%L:\laragon" (
        set "LARAGON_DIR=%%L:\laragon"
        echo [OK] Laragon ditemukan di: %%L:\laragon
        goto LARAGON_FOUND
    )
)

echo [ERROR] Laragon tidak ditemukan di Drive C, D, E, atau F!
echo         SSL membutuhkan Apache dari Laragon.
echo         Silakan install Laragon terlebih dahulu.
exit /b 1

:LARAGON_FOUND

:: 5. Generate Sertifikat SSL & Masukkan ke Windows Trust Store
echo [*] Memasang sertifikat SSL terpercaya ke Windows...
powershell -ExecutionPolicy Bypass -File "%~dp0scripts\generate_ssl.ps1" -LaragonDir "!LARAGON_DIR!"
if %errorLevel% neq 0 (
    echo [ERROR] Gagal memasang sertifikat SSL.
    echo         Lihat pesan error di atas untuk detail.
    exit /b 1
)

:: 6. Pasang Konfigurasi VirtualHost Apache Laragon
echo [*] Memasang konfigurasi VirtualHost SSL di Apache Laragon...
set "SITES_DIR=!LARAGON_DIR!\etc\apache2\sites-enabled"
if not exist "!SITES_DIR!" mkdir "!SITES_DIR!" >nul 2>&1
set "VHOST_FILE=!SITES_DIR!\presensi.balunglor.desa.id.conf"

set "PROJ_PATH=%~dp0"
set "PROJ_PATH=%PROJ_PATH:\=/%"
set "PROJ_PUBLIC=%PROJ_PATH%public"

set "SSL_DIR=!LARAGON_DIR!\etc\ssl"
set "SSL_DIR_FWD=!SSL_DIR:\=/!"

(
echo Listen 8000
echo.
echo ^<VirtualHost *:80 *:8000^>
echo     DocumentRoot "%PROJ_PUBLIC%"
echo     ServerName presensi.balunglor.desa.id
echo     ServerAlias *.balunglor.desa.id presensi.local localhost 127.0.0.1
echo     ^<Directory "%PROJ_PUBLIC%"^>
echo         AllowOverride All
echo         Require all granted
echo     ^</Directory^>
echo ^</VirtualHost^>
echo.
echo ^<VirtualHost *:443^>
echo     DocumentRoot "%PROJ_PUBLIC%"
echo     ServerName presensi.balunglor.desa.id
echo     ServerAlias *.balunglor.desa.id presensi.local localhost 127.0.0.1
echo     ^<Directory "%PROJ_PUBLIC%"^>
echo         AllowOverride All
echo         Require all granted
echo     ^</Directory^>
echo.
echo     SSLEngine on
echo     SSLCertificateFile "!SSL_DIR_FWD!/laragon.crt"
echo     SSLCertificateKeyFile "!SSL_DIR_FWD!/laragon.key"
echo     SSLCertificateChainFile "!SSL_DIR_FWD!/ca.crt"
echo ^</VirtualHost^>
) > "!VHOST_FILE!"

echo [OK] Konfigurasi Apache VirtualHost berhasil dipasang!
echo       File: !VHOST_FILE!

:: 7. Deteksi Apache dan Jalankan
set "APACHE_DIR="
for /d %%A in (!LARAGON_DIR!\bin\apache\httpd-*) do (
    if exist "%%A\bin\httpd.exe" (
        set "APACHE_DIR=%%A"
    )
)

if not defined APACHE_DIR (
    echo [WARNING] Apache tidak ditemukan di Laragon.
    echo           SSL hanya berfungsi dengan Apache, bukan PHP built-in server.
    echo           App tetap bisa diakses via http://localhost:8000
    goto SSL_DONE
)

set "APACHE_EXE=!APACHE_DIR!\bin\httpd.exe"

:: Pastikan nghttp2.dll Apache sinkron dengan PHP
for /d %%P in (!LARAGON_DIR!\bin\php\php-*) do (
    if exist "%%P\nghttp2.dll" (
        copy /y "%%P\nghttp2.dll" "!APACHE_DIR!\bin\nghttp2.dll" >nul 2>&1
    )
)

echo [*] Mendaftarkan Apache sebagai Windows Service (Auto-Start Background)...
"!APACHE_EXE!" -k install -n "LaragonApache" >nul 2>&1
net start LaragonApache >nul 2>&1
if "!errorLevel!"=="0" (
    echo [OK] Service Apache berhasil dinyalakan!
) else (
    echo [*] Menjalankan Apache secara background process...
    start "" /B "!APACHE_EXE!" -d "!APACHE_DIR!"
)

:SSL_DONE
echo.
echo ===============================================================
echo [BERHASIL 100%%] PEMASANGAN SSL HTTPS SELESAI!
echo ===============================================================
echo Anda TIDAK PERLU lagi membuka aplikasi Laragon setiap hari!
echo Server HTTPS kini berjalan otomatis di latar belakang.
echo.
echo Silakan buka browser Anda di:
echo  - https://presensi.balunglor.desa.id  (Aman dengan Gembok SSL)
echo  - http://presensi.balunglor.desa.id:8000
echo ===============================================================
echo.
exit /b 0
