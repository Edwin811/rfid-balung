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

:: 1. Hentikan PHP built-in server lama agar port 8000 tidak bentrok
powershell -Command "Get-Process -Name php -ErrorAction SilentlyContinue | Where-Object { $_.CommandLine -like '*artisan serve*' -or $_.CommandLine -like '*0.0.0.0:8000*' } | Stop-Process -Force" >nul 2>&1

:: 2. Auto-Start / Cek MySQL Lokal dengan retry loop
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
    net start MySQL >nul 2>&1
    if !errorLevel! neq 0 net start mysql >nul 2>&1
    powershell -Command "$c = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue; if ($c) { exit 0 } else { exit 1 }" >nul 2>&1
    if "!errorLevel!"=="0" set "MYSQL_READY=1"

    if "!MYSQL_READY!"=="0" (
        for %%L in (C D E F) do (
            for /d %%D in (%%L:\laragon\bin\mysql\mysql-*) do (
                if exist "%%D\bin\mysqld.exe" (
                    echo [%date% %time%] Ditemukan mysqld: %%D\bin\mysqld.exe >> "%LOGFILE%"
                    if exist "%%D\my.ini" (
                        "%%D\bin\mysqld.exe" --install MySQL --defaults-file="%%D\my.ini" >nul 2>&1
                    ) else (
                        "%%D\bin\mysqld.exe" --install MySQL >nul 2>&1
                    )
                    net start MySQL >nul 2>&1
                    powershell -Command "$c = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue; if ($c) { exit 0 } else { exit 1 }" >nul 2>&1
                    if "!errorLevel!" neq "0" (
                        if exist "%%D\my.ini" (
                            powershell -Command "Start-Process '%%D\bin\mysqld.exe' -ArgumentList '--defaults-file=\"\"%%D\my.ini\"\"' -WindowStyle Hidden" >nul 2>&1
                        ) else (
                            powershell -Command "Start-Process '%%D\bin\mysqld.exe' -ArgumentList '--console' -WindowStyle Hidden" >nul 2>&1
                        )
                    )
                )
            )
        )
    )

    :: Retry loop - tunggu MySQL siap (max 16 detik)
    for /L %%I in (1,1,8) do (
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
    echo [WARNING] MySQL di port 3306 belum terdeteksi.
    echo           Jika database belum aktif, silakan klik tombol Start All di Laragon.
    echo [%date% %time%] [WARNING] MySQL belum terdeteksi. >> "%LOGFILE%"
)

:: 3. Cek dan Deteksi PHP di Laragon atau XAMPP jika belum ada di PATH
where php >nul 2>&1
if %errorLevel% neq 0 (
    for %%L in (C D E F) do (
        for /d %%D in (%%L:\laragon\bin\php\php-*) do (
            if exist "%%D\php.exe" (
                set "PATH=%%D;!PATH!"
                echo [%date% %time%] PHP ditemukan: %%D >> "%LOGFILE%"
            )
        )
    )
    for %%L in (C D E F) do (
        if exist "%%L:\xampp\php\php.exe" (
            set "PATH=%%L:\xampp\php;!PATH!"
            echo [%date% %time%] PHP ditemukan: %%L:\xampp\php >> "%LOGFILE%"
        )
    )
)

where php >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] PHP tidak ditemukan di sistem!
    echo         Pastikan Laragon sudah terpasang.
    echo [%date% %time%] [ERROR] PHP tidak ditemukan! >> "%LOGFILE%"
    pause
    exit /b 1
)
echo [OK] PHP siap.

:: 4. Deteksi IP Lokal Komputer untuk Akses dari HP/Laptop Lain
set "LOCAL_IP="
for /f "tokens=*" %%I in ('powershell -Command "(Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.InterfaceAlias -notlike '*Loopback*' -and $_.IPAddress -notlike '169.254*' -and $_.IPAddress -notlike '127.*' } | Select-Object -First 1).IPAddress" 2^>nul') do (
    set "LOCAL_IP=%%I"
)
if not defined LOCAL_IP set "LOCAL_IP=127.0.0.1"

:: 5. Optimasi Cache Laravel
echo [*] Mengoptimalkan cache aplikasi...
php artisan config:cache >> "%LOGFILE%" 2>&1
php artisan route:cache >> "%LOGFILE%" 2>&1
php artisan view:cache >> "%LOGFILE%" 2>&1
echo [OK] Cache siap.

:: 6. Mulai Layanan Auto-Sync Mesin ZKTeco di Background
echo [*] Memulai Layanan Auto-Sync Mesin ZKTeco di background...
echo [%date% %time%] Memulai ZK Daemon via start_zk_daemon.vbs >> "%LOGFILE%"
if exist "%~dp0scripts\start_zk_daemon.vbs" (
    wscript "%~dp0scripts\start_zk_daemon.vbs" >nul 2>&1
)

:: 7. Tampilkan Alamat dan Jalankan Server di Port 8000
echo.
echo ===============================================================
echo [BERHASIL] SERVER PRESENSI DESA BALUNGLOR AKTIF!
echo ===============================================================
echo  Akses dari PC Ini:
echo    - http://localhost:8000
echo    - http://127.0.0.1:8000
echo.
echo  Akses dari HP / Laptop Lain (Satu Wi-Fi):
echo    - http://!LOCAL_IP!:8000
echo ===============================================================
echo.
echo Server sedang berjalan...
echo (Jangan tutup jendela ini agar website tetap aktif)
echo (Untuk mematikan server, Anda bisa menutup jendela ini atau jalankan stop_app.bat)
echo.

php artisan serve --host=0.0.0.0 --port=8000
if not "%errorLevel%"=="0" (
    echo.
    echo [NOTE] Menjalankan server alternatif php -S...
    php -S 0.0.0.0:8000 -t public
)

echo.
echo Server terhenti.
pause
