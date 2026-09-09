@echo off
title Presensi Desa Balunglor - Setup SSL HTTPS Otomatis
cd /d "%~dp0"

echo ===============================================================
echo   PEMASANGAN SERTIFIKAT SSL HTTPS PRESENSI DESA BALUNGLOR
echo   Domain: https://presensi.balunglor.desa.id
echo ===============================================================
echo.

:: 1. Meminta Izin Administrator Windows
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [*] Meminta izin Administrator Windows untuk mendaftarkan sertifikat...
    powershell -Command "Start-Process cmd.exe -ArgumentList '/k `\"\"%~f0\"`\"' -Verb RunAs"
    exit /b
)

:: 2. Matikan proses web server lama agar tidak bentrok
taskkill /F /IM php.exe >nul 2>&1
taskkill /F /IM httpd.exe >nul 2>&1

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

:: 4. Generate Sertifikat SSL & Masukkan ke Windows Trust Store
echo [*] Memasang sertifikat SSL terpercaya ke Windows...
powershell -ExecutionPolicy Bypass -File "%~dp0scripts\generate_ssl.ps1"
if %errorLevel% neq 0 (
    echo [ERROR] Gagal memasang sertifikat SSL.
    pause
    exit /b 1
)

:: 5. Pasang Konfigurasi VirtualHost Apache Laragon
echo [*] Memasang konfigurasi VirtualHost SSL di Apache Laragon...
if not exist "C:\laragon\etc\apache2\sites-enabled" mkdir "C:\laragon\etc\apache2\sites-enabled" >nul 2>&1
set "VHOST_FILE=C:\laragon\etc\apache2\sites-enabled\presensi.balunglor.desa.id.conf"

set "PROJ_PATH=%~dp0"
set "PROJ_PATH=%PROJ_PATH:\=/%"
set "PROJ_PUBLIC=%PROJ_PATH%public"

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
echo     SSLCertificateFile "C:/laragon/etc/ssl/laragon.crt"
echo     SSLCertificateKeyFile "C:/laragon/etc/ssl/laragon.key"
echo     SSLCertificateChainFile "C:/laragon/etc/ssl/ca.crt"
echo ^</VirtualHost^>
) > "%VHOST_FILE%"

echo [OK] Konfigurasi Apache berhasil dipasang!

:: 6. Deteksi Apache dan Jalankan
set "APACHE_DIR="
for /d %%A in (C:\laragon\bin\apache\httpd-*) do (
    if exist "%%A\bin\httpd.exe" (
        set "APACHE_DIR=%%A"
    )
)

if defined APACHE_DIR (
    set "APACHE_EXE=%APACHE_DIR%\bin\httpd.exe"
    
    :: Pastikan nghttp2.dll Apache sinkron dengan PHP
    for /d %%P in (C:\laragon\bin\php\php-*) do (
        if exist "%%P\nghttp2.dll" (
            copy /y "%%P\nghttp2.dll" "%APACHE_DIR%\bin\nghttp2.dll" >nul 2>&1
        )
    )

    echo [*] Mendaftarkan Apache sebagai Windows Service (Auto-Start Background)...
    "%APACHE_EXE%" -k install -n "LaragonApache" >nul 2>&1
    net start LaragonApache >nul 2>&1
    if %errorLevel% eq 0 (
        echo [OK] Service Apache berhasil dinyalakan!
    ) else (
        echo [*] Menjalankan Apache secara background process...
        start "" /B "%APACHE_EXE%" -d "%APACHE_DIR%"
    )
)

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
pause
exit
