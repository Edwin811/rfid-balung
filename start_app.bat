@echo off
title Presensi Biometrik Desa Balunglor - Server Runner
cd /d "%~dp0"
setlocal enabledelayedexpansion

:: Setup log file
set "LOGFILE=%~dp0server_log.txt"
echo [%date% %time%] =============================================== >> "%LOGFILE%"
echo [%date% %time%] Server Runner dimulai >> "%LOGFILE%"
echo [%date% %time%] =============================================== >> "%LOGFILE%"

echo ===============================================================
echo     SISTEM PRESENSI BIOMETRIK DAN RFID DESA BALUNGLOR
echo ===============================================================
echo.

:: Bersihkan proses sebelumnya agar port 80, 443, dan 8000 tidak bentrok
taskkill /F /IM httpd.exe >nul 2>&1
taskkill /F /IM php.exe >nul 2>&1
echo [%date% %time%] Proses httpd dan php sebelumnya dihentikan. >> "%LOGFILE%"

:: 1. Auto-Start MySQL Lokal dengan retry loop
echo [*] Memeriksa database MySQL...
set "MYSQL_READY=0"

powershell -Command "$c = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue; if ($c) { exit 0 } else { exit 1 }" >nul 2>&1
if "%errorLevel%"=="0" (
    set "MYSQL_READY=1"
    echo [OK] MySQL sudah aktif.
    echo [%date% %time%] MySQL sudah aktif di port 3306. >> "%LOGFILE%"
)

if "%MYSQL_READY%"=="0" (
    echo [*] Mengaktifkan database MySQL di background...
    echo [%date% %time%] Mencoba menyalakan MySQL... >> "%LOGFILE%"
    net start mysql >nul 2>&1
    if !errorLevel! neq 0 (
        for %%L in (C D E) do (
            for /d %%D in (%%L:\laragon\bin\mysql\mysql-*) do (
                if exist "%%D\bin\mysqld.exe" (
                    echo [%date% %time%] Ditemukan mysqld: %%D\bin\mysqld.exe >> "%LOGFILE%"
                    if exist "%%D\my.ini" (
                        start "" /B "%%D\bin\mysqld.exe" --defaults-file="%%D\my.ini" >nul 2>&1
                    ) else (
                        start "" /B "%%D\bin\mysqld.exe" --console >nul 2>&1
                    )
                )
            )
        )
    )

    :: Retry loop - tunggu MySQL siap (max 20 detik)
    for /L %%I in (1,1,10) do (
        if "!MYSQL_READY!"=="0" (
            powershell -Command "Start-Sleep -Seconds 2" >nul 2>&1
            powershell -Command "$c = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue; if ($c) { exit 0 } else { exit 1 }" >nul 2>&1
            if "!errorLevel!"=="0" (
                set "MYSQL_READY=1"
                echo [OK] MySQL siap setelah %%I percobaan.
                echo [%date% %time%] MySQL siap setelah %%I percobaan. >> "%LOGFILE%"
            )
        )
    )
)

if "%MYSQL_READY%"=="0" (
    echo [ERROR] MySQL gagal diaktifkan! Pastikan Laragon sudah Start.
    echo [%date% %time%] [ERROR] MySQL gagal aktif setelah 10 percobaan! >> "%LOGFILE%"
    pause
    exit /b 1
)

:: 2. Cek dan Deteksi PHP di Laragon atau XAMPP jika belum ada di PATH
where php >nul 2>&1
if %errorLevel% neq 0 (
    for %%L in (C D E) do (
        for /d %%D in (%%L:\laragon\bin\php\php-*) do (
            if exist "%%D\php.exe" (
                set "PATH=%%D;%PATH%"
                echo [%date% %time%] PHP ditemukan: %%D >> "%LOGFILE%"
            )
        )
    )
    for %%L in (C D E) do (
        if exist "%%L:\xampp\php\php.exe" (
            set "PATH=%%L:\xampp\php;%PATH%"
            echo [%date% %time%] PHP ditemukan: %%L:\xampp\php >> "%LOGFILE%"
        )
    )
)

where php >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] PHP tidak ditemukan di sistem!
    echo [%date% %time%] [ERROR] PHP tidak ditemukan! >> "%LOGFILE%"
    pause
    exit /b 1
)
echo [OK] PHP siap.

:: 3. Optimasi Cache Laravel
echo [*] Mengoptimalkan cache aplikasi...
php artisan config:cache >> "%LOGFILE%" 2>&1
php artisan route:cache >> "%LOGFILE%" 2>&1
php artisan view:cache >> "%LOGFILE%" 2>&1
echo [%date% %time%] Cache dioptimalkan. >> "%LOGFILE%"

:: 4. Cari dan Jalankan Apache Laragon dengan SSL
set "APACHE_DIR="
for %%L in (C D E) do (
    for /d %%A in (%%L:\laragon\bin\apache\httpd-*) do (
        if exist "%%A\bin\httpd.exe" (
            set "APACHE_DIR=%%A"
        )
    )
)

if not defined APACHE_DIR goto RUN_PHP_FALLBACK
set "APACHE_EXE=%APACHE_DIR%\bin\httpd.exe"

:: Pastikan nghttp2.dll di Apache sinkron dengan PHP
for %%L in (C D E) do (
    for /d %%P in (%%L:\laragon\bin\php\php-*) do (
        if exist "%%P\nghttp2.dll" (
            copy /y "%%P\nghttp2.dll" "%APACHE_DIR%\bin\nghttp2.dll" >nul 2>&1
        )
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
echo [%date% %time%] Apache dimulai: %APACHE_EXE% >> "%LOGFILE%"

echo [*] Memulai Layanan Auto-Sync Mesin ZKTeco di background...
echo [%date% %time%] Memulai ZK Daemon via start_zk_daemon.vbs >> "%LOGFILE%"
wscript "%~dp0scripts\start_zk_daemon.vbs" >nul 2>&1

echo.
echo Server sedang berjalan...
echo (Jangan tutup jendela ini agar website tetap aktif)
echo (Untuk mematikan server, Anda bisa menutup jendela ini atau jalankan stop_app.bat)
echo (Log server: server_log.txt)
echo.
"%APACHE_EXE%" -d "%APACHE_DIR%" 2>> "%LOGFILE%"
echo [NOTE] Apache terhenti. Mencoba menyalakan server PHP built-in...
echo [%date% %time%] Apache terhenti, fallback ke PHP built-in. >> "%LOGFILE%"

:RUN_PHP_FALLBACK
echo [NOTE] Menjalankan PHP Built-in Server...
echo [%date% %time%] Memulai PHP Built-in Server di port 8000. >> "%LOGFILE%"

echo [*] Memulai Layanan Auto-Sync Mesin ZKTeco di background...
wscript "%~dp0scripts\start_zk_daemon.vbs" >nul 2>&1

php artisan serve --host=0.0.0.0 --port=8000 2>> "%LOGFILE%"
if not "%errorLevel%"=="0" (
    echo [NOTE] Menjalankan php built-in server fallback...
    echo [%date% %time%] artisan serve gagal, fallback ke php -S. >> "%LOGFILE%"
    set PHP_CLI_SERVER_WORKERS=4
    php -S 0.0.0.0:8000 -t public 2>> "%LOGFILE%"
)
echo.
echo Server terhenti.
echo [%date% %time%] Server terhenti. >> "%LOGFILE%"
pause
