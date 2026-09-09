@echo off
title Presensi Biometrik Desa Balunglor - Server Runner
cd /d "%~dp0"

echo ===============================================================
echo     SISTEM PRESENSI BIOMETRIK DAN RFID DESA BALUNGLOR
echo ===============================================================
echo.

:: Bersihkan proses sebelumnya agar port 80, 443, dan 8000 tidak bentrok
taskkill /F /IM httpd.exe >nul 2>&1
taskkill /F /IM php.exe >nul 2>&1

:: 1. Auto-Start MySQL Lokal jika belum aktif
powershell -Command "$c = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue; if (!$c) { exit 1 }" >nul 2>&1
if %errorLevel% neq 0 (
    echo [*] Mengaktifkan database MySQL di background...
    net start mysql >nul 2>&1
    if %errorLevel% neq 0 (
        for /d %%D in (C:\laragon\bin\mysql\mysql-*) do (
            if exist "%%D\bin\mysqld.exe" (
                if exist "%%D\my.ini" (
                    start "" /B "%%D\bin\mysqld.exe" --defaults-file="%%D\my.ini" >nul 2>&1
                ) else (
                    start "" /B "%%D\bin\mysqld.exe" --console >nul 2>&1
                )
            )
        )
    )
    powershell -Command "Start-Sleep -Milliseconds 1500" >nul 2>&1
)

:: 2. Cek dan Deteksi PHP di Laragon atau XAMPP jika belum ada di PATH
where php >nul 2>&1
if %errorLevel% neq 0 (
    for /d %%D in (C:\laragon\bin\php\php-*) do (
        if exist "%%D\php.exe" (
            set "PATH=%%D;%PATH%"
        )
    )
    if exist "C:\xampp\php\php.exe" (
        set "PATH=C:\xampp\php;%PATH%"
    )
)

where php >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] PHP tidak ditemukan di sistem!
    pause
    exit /b 1
)

:: 3. Optimasi Cache Laravel
echo [*] Mengoptimalkan cache aplikasi...
php artisan config:cache >nul 2>&1
php artisan route:cache >nul 2>&1
php artisan view:cache >nul 2>&1

:: 4. Cari dan Jalankan Apache Laragon dengan SSL
set "APACHE_DIR="
for /d %%A in (C:\laragon\bin\apache\httpd-*) do (
    if exist "%%A\bin\httpd.exe" (
        set "APACHE_DIR=%%A"
    )
)

if not defined APACHE_DIR goto RUN_PHP_FALLBACK
set "APACHE_EXE=%APACHE_DIR%\bin\httpd.exe"

:: Pastikan nghttp2.dll di Apache sinkron dengan PHP agar tidak terjadi error DLL Entry Point Not Found
for /d %%P in (C:\laragon\bin\php\php-*) do (
    if exist "%%P\nghttp2.dll" (
        copy /y "%%P\nghttp2.dll" "%APACHE_DIR%\bin\nghttp2.dll" >nul 2>&1
    )
)

echo.
echo ===============================================================
echo [OK] Web Server Apache (HTTPS dan HTTP) BERHASIL DIAKTIFKAN!
echo ===============================================================
echo Alamat Akses Resmi:
echo  - https://presensi.balunglor.desa.id  (Aman dengan Gembok SSL)
echo  - http://presensi.balunglor.desa.id:8000
echo  - http://localhost:8000
echo ===============================================================
echo [*] Memulai Layanan Auto-Sync Mesin ZKTeco di background...
wscript "%~dp0scripts\start_zk_daemon.vbs" >nul 2>&1

echo.
echo Server sedang berjalan...
echo (Jangan tutup jendela ini agar website tetap aktif)
echo (Untuk mematikan server, Anda bisa menutup jendela ini atau jalankan stop_app.bat)
echo.
"%APACHE_EXE%" -d "%APACHE_DIR%"
exit /b

:RUN_PHP_FALLBACK
echo [NOTE] Apache Laragon tidak ditemukan. Menjalankan PHP Built-in Server...
wscript "%~dp0scripts\start_zk_daemon.vbs" >nul 2>&1
set PHP_CLI_SERVER_WORKERS=4
php -S 0.0.0.0:8000 -t public
