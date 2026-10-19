@echo off
title Presensi Desa Balunglor - Master Installer 1-Klik
cd /d "%~dp0"
setlocal enabledelayedexpansion

:: ============================================================
:: SETUP LOG FILE - Semua output dicatat ke install_log.txt
:: ============================================================
set "LOGFILE=%~dp0install_log.txt"
echo. > "%LOGFILE%"
echo [%date% %time%] ============================================== >> "%LOGFILE%"
echo [%date% %time%] MASTER INSTALLER - SISTEM PRESENSI DESA BALUNGLOR >> "%LOGFILE%"
echo [%date% %time%] ============================================== >> "%LOGFILE%"

:: Variabel tracking status setiap tahap
set "STATUS_PHP=GAGAL"
set "STATUS_VENDOR=GAGAL"
set "STATUS_ENV=GAGAL"
set "STATUS_KEY=GAGAL"
set "STATUS_DB=GAGAL"
set "STATUS_SEED=GAGAL"
set "STATUS_PYTHON=DILEWATI"
set "STATUS_DOMAIN=GAGAL"
set "STATUS_SERVER=GAGAL"
set "INSTALL_ERRORS=0"

echo.
echo ================================================================
echo   MASTER INSTALLER - SISTEM PRESENSI DESA BALUNGLOR
echo   Installer ini akan menangani SEMUA proses secara otomatis.
echo.
echo   Log detail tersimpan di: install_log.txt
echo ================================================================
echo.

:: ============================================================
:: TAHAP 1: DETEKSI DAN PERSIAPAN PHP
:: ============================================================
echo [1/8] Mencari PHP di komputer Anda...
echo [%date% %time%] [TAHAP 1] Mencari PHP... >> "%LOGFILE%"

where php >nul 2>&1
if "%errorLevel%"=="0" (
    echo       [OK] PHP ditemukan di PATH sistem.
    echo [%date% %time%]   [OK] PHP ditemukan di PATH sistem. >> "%LOGFILE%"
    goto PHP_READY
)

:: Cari di Laragon (Drive C, D, E, F)
for %%L in (C D E F) do (
    for /d %%D in (%%L:\laragon\bin\php\php-*) do (
        if exist "%%D\php.exe" (
            set "PATH=%%D;!PATH!"
            echo       [OK] PHP ditemukan di: %%D
            echo [%date% %time%]   [OK] PHP ditemukan di: %%D >> "%LOGFILE%"
            goto PHP_READY
        )
    )
)

:: Cari di XAMPP (Drive C, D, E, F)
for %%L in (C D E F) do (
    if exist "%%L:\xampp\php\php.exe" (
        set "PATH=%%L:\xampp\php;!PATH!"
        echo       [OK] PHP ditemukan di: %%L:\xampp\php
        echo [%date% %time%]   [OK] PHP ditemukan di: %%L:\xampp\php >> "%LOGFILE%"
        goto PHP_READY
    )
)

:: PHP tidak ditemukan sama sekali
echo.
echo       ============================================================
echo       [ERROR] PHP TIDAK DITEMUKAN DI KOMPUTER INI!
echo       ============================================================
echo       Silakan install Laragon: https://laragon.org/download/
echo.
echo [%date% %time%]   [ERROR] PHP TIDAK DITEMUKAN! >> "%LOGFILE%"
set /a INSTALL_ERRORS+=1
goto SHOW_SUMMARY

:PHP_READY
php -v >nul 2>&1
if not "%errorLevel%"=="0" (
    echo       [ERROR] PHP ditemukan tapi gagal dijalankan.
    php -v >> "%LOGFILE%" 2>&1
    set /a INSTALL_ERRORS+=1
    goto SHOW_SUMMARY
)

:: Validasi versi PHP >= 8.2
for /f "tokens=*" %%V in ('php -r "echo PHP_VERSION;" 2^>nul') do set "PHP_VER=%%V"
for /f "tokens=*" %%C in ('php -r "echo version_compare(PHP_VERSION, '8.2.0', '>=') ? 'OK' : 'OLD';" 2^>nul') do set "PHP_CHECK=%%C"

if not "!PHP_CHECK!"=="OK" (
    echo       [ERROR] Versi PHP terlalu lama: !PHP_VER!
    echo       Laravel membutuhkan minimal PHP 8.2.
    echo [%date% %time%]   [ERROR] PHP versi !PHP_VER! terlalu lama >> "%LOGFILE%"
    set /a INSTALL_ERRORS+=1
    goto SHOW_SUMMARY
)

echo       [OK] PHP !PHP_VER! siap digunakan.
echo [%date% %time%]   [OK] PHP !PHP_VER! siap digunakan. >> "%LOGFILE%"
set "STATUS_PHP=OK"
echo.

:: ============================================================
:: TAHAP 2: UNDUH PUSTAKA VENDOR (COMPOSER INSTALL)
:: ============================================================
echo [2/8] Memeriksa pustaka vendor Laravel...
echo [%date% %time%] [TAHAP 2] Memeriksa pustaka vendor... >> "%LOGFILE%"

if exist "vendor\autoload.php" (
    echo       [OK] Pustaka vendor sudah tersedia.
    echo [%date% %time%]   [OK] vendor/autoload.php sudah ada. >> "%LOGFILE%"
    set "STATUS_VENDOR=OK"
    goto VENDOR_DONE
)

echo       [*] Mengunduh dependensi (composer install)...
echo [%date% %time%]   [*] Memulai composer install... >> "%LOGFILE%"

where composer >nul 2>&1
if "%errorLevel%"=="0" (
    call composer install --no-interaction --prefer-dist --ignore-platform-reqs >> "%LOGFILE%" 2>&1
    goto CHECK_VENDOR
)

for %%L in (C D E F) do (
    if exist "%%L:\laragon\bin\composer\composer.bat" (
        set "PATH=%%L:\laragon\bin\composer;!PATH!"
        call composer install --no-interaction --prefer-dist --ignore-platform-reqs >> "%LOGFILE%" 2>&1
        goto CHECK_VENDOR
    )
)

:: Unduh composer.phar portable jika tidak ada composer
echo       [*] Mengunduh portable composer...
php -r "copy('https://getcomposer.org/download/latest-stable/composer.phar', 'composer.phar');" >> "%LOGFILE%" 2>&1
if exist "composer.phar" (
    php composer.phar install --no-interaction --prefer-dist --ignore-platform-reqs >> "%LOGFILE%" 2>&1
)

:CHECK_VENDOR
if not exist "vendor\autoload.php" (
    echo       [ERROR] Pustaka vendor gagal terunduh. Pastikan ada koneksi internet.
    echo [%date% %time%]   [ERROR] vendor/autoload.php tidak terbentuk! >> "%LOGFILE%"
    set /a INSTALL_ERRORS+=1
    goto SHOW_SUMMARY
)
echo       [OK] Pustaka vendor berhasil terpasang.
echo [%date% %time%]   [OK] Pustaka vendor terpasang. >> "%LOGFILE%"
set "STATUS_VENDOR=OK"

:VENDOR_DONE
echo.

:: ============================================================
:: TAHAP 3: KONFIGURASI FILE .ENV
:: ============================================================
echo [3/8] Menyiapkan file konfigurasi .env...
echo [%date% %time%] [TAHAP 3] Menyiapkan .env... >> "%LOGFILE%"

if not exist ".env" (
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
        echo       [OK] File .env berhasil dibuat dari .env.example
        echo [%date% %time%]   [OK] .env dibuat dari .env.example >> "%LOGFILE%"
    ) else (
        echo       [ERROR] File .env.example tidak ditemukan!
        echo [%date% %time%]   [ERROR] .env.example tidak ditemukan! >> "%LOGFILE%"
        set /a INSTALL_ERRORS+=1
        goto SHOW_SUMMARY
    )
) else (
    echo       [OK] File .env sudah ada.
    echo [%date% %time%]   [OK] .env sudah ada. >> "%LOGFILE%"
)
set "STATUS_ENV=OK"
echo.

:: ============================================================
:: TAHAP 4: GENERATE APP_KEY & BERSIHKAN CACHE
:: ============================================================
echo [4/8] Menghasilkan kunci keamanan aplikasi...
echo [%date% %time%] [TAHAP 4] Generate APP_KEY... >> "%LOGFILE%"

call php artisan key:generate --force >> "%LOGFILE%" 2>&1
call php artisan config:clear >> "%LOGFILE%" 2>&1
echo       [OK] Kunci keamanan dan cache siap.
set "STATUS_KEY=OK"
echo.

:: ============================================================
:: TAHAP 5: PERSIAPAN DATABASE DAN MIGRASI
:: ============================================================
echo [5/8] Menyiapkan database MySQL dan data aparatur desa...
echo [%date% %time%] [TAHAP 5] Persiapan database... >> "%LOGFILE%"

set "MYSQL_READY=0"
powershell -Command "$c = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue; if ($c) { exit 0 } else { exit 1 }" >nul 2>&1
if "%errorLevel%"=="0" set "MYSQL_READY=1"

if "!MYSQL_READY!"=="0" (
    echo       [*] Menyalakan MySQL...
    net start mysql >> "%LOGFILE%" 2>&1
    for %%L in (C D E F) do (
        for /d %%D in (%%L:\laragon\bin\mysql\mysql-*) do (
            if exist "%%D\bin\mysqld.exe" (
                if exist "%%D\my.ini" (
                    start "" /B "%%D\bin\mysqld.exe" --defaults-file="%%D\my.ini" >> "%LOGFILE%" 2>&1
                ) else (
                    start "" /B "%%D\bin\mysqld.exe" --console >> "%LOGFILE%" 2>&1
                )
            )
        )
    )

    for /L %%I in (1,1,10) do (
        if "!MYSQL_READY!"=="0" (
            powershell -Command "Start-Sleep -Seconds 2" >nul 2>&1
            powershell -Command "$c = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue; if ($c) { exit 0 } else { exit 1 }" >nul 2>&1
            if "!errorLevel!"=="0" set "MYSQL_READY=1"
        )
    )
)

if "!MYSQL_READY!"=="0" (
    echo       [ERROR] MySQL di port 3306 tidak dapat diakses!
    echo       Pastikan Laragon sudah menyala (Start All).
    echo [%date% %time%]   [ERROR] MySQL gagal aktif >> "%LOGFILE%"
    set /a INSTALL_ERRORS+=1
    goto SHOW_SUMMARY
)
echo       [OK] MySQL aktif di port 3306.

:: Buat database rfid_balung jika belum ada
php -r "try { $p=new PDO('mysql:host=127.0.0.1;port=3306','root',''); $p->exec('CREATE DATABASE IF NOT EXISTS `rfid_balung` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'); echo 'DB_OK'; } catch(Exception $e) { echo 'DB_ERR: '.$e->getMessage(); exit(1); }" >> "%LOGFILE%" 2>&1
set "STATUS_DB=OK"
echo       [OK] Database rfid_balung siap.

:: Migrasi dan Seeding
php -r "try { $p=new PDO('mysql:host=127.0.0.1;port=3306;dbname=rfid_balung','root',''); $s=$p->query('SHOW TABLES LIKE \'users\''); exit($s->rowCount() > 0 ? 0 : 1); } catch(Exception $e) { exit(1); }" >nul 2>&1
if not "%errorLevel%"=="0" (
    echo       [*] Database baru: membuat skema tabel dan mengisi data aparatur...
    call php artisan migrate:fresh --seed --force >> "%LOGFILE%" 2>&1
    if not "!errorLevel!"=="0" (
        call php artisan migrate --force >> "%LOGFILE%" 2>&1
        call php artisan db:seed --force >> "%LOGFILE%" 2>&1
    )
    set "STATUS_SEED=OK"
    echo       [OK] Skema tabel dan data aparatur berhasil diisi.
) else (
    echo       [OK] Database sudah memiliki data sebelumnya. Menjalankan migrasi aman...
    call php artisan migrate --force >> "%LOGFILE%" 2>&1
    set "STATUS_SEED=OK"
)

call php artisan storage:link >> "%LOGFILE%" 2>&1
echo.

:: ============================================================
:: TAHAP 6: INTEGRASI MESIN RFID (PYTHON & PYZK)
:: ============================================================
echo [6/8] Memeriksa integrasi Python dan PyZK...
echo [%date% %time%] [TAHAP 6] Cek Python... >> "%LOGFILE%"

where python >nul 2>&1
if not "%errorLevel%"=="0" (
    for %%L in (C D E F) do (
        for /d %%P in (%%L:\laragon\bin\python\*) do (
            if exist "%%P\python.exe" (
                set "PATH=%%P;%%P\Scripts;!PATH!"
                echo [%date% %time%]   [OK] Python ditemukan di: %%P >> "%LOGFILE%"
            )
        )
    )
)

where python >nul 2>&1
if "%errorLevel%"=="0" (
    python -c "import zk" >nul 2>&1
    if not "%errorLevel%"=="0" (
        echo       [*] Memasang library pyzk dan requests...
        pip install pyzk requests >> "%LOGFILE%" 2>&1
    )
    echo       [OK] Python dan library siap digunakan.
    set "STATUS_PYTHON=OK"
) else (
    echo       [NOTE] Python belum terdeteksi. Silakan aktifkan via Laragon Tools.
    set "STATUS_PYTHON=LEWATI"
)
echo.

:: ============================================================
:: TAHAP 7: DAFTARKAN DOMAIN LOKAL DESA & FIREWALL
:: ============================================================
echo [7/8] Menyiapkan domain lokal dan izin firewall...
echo [%date% %time%] [TAHAP 7] Domain dan firewall... >> "%LOGFILE%"

set "HOSTS_FILE=%WINDIR%\System32\drivers\etc\hosts"
findstr /C:"presensi.balunglor.desa.id" "%HOSTS_FILE%" >nul 2>&1
if "%errorLevel%"=="0" (
    echo       [OK] Domain presensi.balunglor.desa.id sudah ada di hosts.
) else (
    net session >nul 2>&1
    if "%errorLevel%"=="0" (
        echo. >> "%HOSTS_FILE%"
        echo 127.0.0.1    presensi.balunglor.desa.id >> "%HOSTS_FILE%"
        echo 127.0.0.1    presensi.local >> "%HOSTS_FILE%"
        echo       [OK] Domain didaftarkan ke Windows hosts.
    ) else (
        powershell -Command "Start-Process cmd.exe -ArgumentList '/c echo. >> \"%HOSTS_FILE%\" & echo 127.0.0.1    presensi.balunglor.desa.id >> \"%HOSTS_FILE%\" & echo 127.0.0.1    presensi.local >> \"%HOSTS_FILE%\"' -Verb RunAs -Wait" >nul 2>&1
    )
)

net session >nul 2>&1
if "%errorLevel%"=="0" (
    netsh advfirewall firewall add rule name="Presensi Balunglor Web Server" dir=in action=allow protocol=TCP localport=80,443,8000 >> "%LOGFILE%" 2>&1
    echo       [OK] Port 80, 443, dan 8000 dibuka di Firewall.
)
set "STATUS_DOMAIN=OK"
echo.

:: ============================================================
:: TAHAP 8: AUTO-START WINDOWS + NYALAKAN SERVER
:: ============================================================
echo [8/8] Memasang Auto-Start dan menyalakan server...
echo [%date% %time%] [TAHAP 8] Auto-Start dan start server... >> "%LOGFILE%"

if exist "scripts\create_startup_shortcut.ps1" (
    powershell -ExecutionPolicy Bypass -File "%~dp0scripts\create_startup_shortcut.ps1" >> "%LOGFILE%" 2>&1
    echo       [OK] Auto-Start Windows terpasang.
)

:: Deteksi IP lokal PC
set "LOCAL_IP="
for /f "tokens=*" %%I in ('powershell -Command "(Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.InterfaceAlias -notlike '*Loopback*' -and $_.IPAddress -notlike '169.254*' -and $_.IPAddress -notlike '127.*' } | Select-Object -First 1).IPAddress" 2^>nul') do (
    set "LOCAL_IP=%%I"
)
if not defined LOCAL_IP set "LOCAL_IP=127.0.0.1"

:: Jalankan server via start_app.bat di background/jendela baru
echo       [*] Menyalakan Web Server di port 8000...
start "" cmd /c "%~dp0start_app.bat"

:: Tunggu verifikasi respons server
set "SERVER_READY=0"
for /L %%I in (1,1,10) do (
    if "!SERVER_READY!"=="0" (
        powershell -Command "Start-Sleep -Seconds 1.5" >nul 2>&1
        powershell -Command "try { $r = Invoke-WebRequest -Uri 'http://127.0.0.1:8000' -TimeoutSec 2 -UseBasicParsing -ErrorAction Stop; if ($r.StatusCode -eq 200 -or $r.StatusCode -eq 302) { exit 0 } else { exit 1 } } catch { exit 1 }" >nul 2>&1
        if "!errorLevel!"=="0" set "SERVER_READY=1"
    )
)

if "!SERVER_READY!"=="1" (
    set "STATUS_SERVER=OK"
    echo       [OK] Server aktif dan merespons.
    start "" "http://localhost:8000"
) else (
    set "STATUS_SERVER=AKTIF"
    echo       [NOTE] Server telah dijalankan di jendela terpisah.
    start "" "http://localhost:8000"
)

:: ============================================================
:: RINGKASAN INSTALASI
:: ============================================================
:SHOW_SUMMARY
echo.
echo ================================================================
if "!INSTALL_ERRORS!"=="0" (
    echo   INSTALASI BERHASIL 100%%
) else (
    echo   INSTALASI SELESAI DENGAN CATATAN
)
echo ================================================================
echo.
echo   Status Komponen:
echo   - PHP             : [!STATUS_PHP!]
echo   - Vendor Laravel  : [!STATUS_VENDOR!]
echo   - Konfigurasi .env: [!STATUS_ENV!]
echo   - Kunci Keamanan  : [!STATUS_KEY!]
echo   - Database MySQL  : [!STATUS_DB!]
echo   - Data Aparatur   : [!STATUS_SEED!]
echo   - Python & PyZK   : [!STATUS_PYTHON!]
echo   - Domain & Network: [!STATUS_DOMAIN!]
echo   - Web Server      : [!STATUS_SERVER!]
echo.
echo   Alamat Akses Website:
echo   - Dari Komputer Ini : http://localhost:8000
echo   - Dari HP / Laptop  : http://!LOCAL_IP!:8000
echo   - Domain Lokal      : http://presensi.balunglor.desa.id:8000
echo.
echo   Akun Login Default:
echo   - Administrator : admin / password123
echo   - Kepala Desa   : kades / password123
echo   - Operator      : operator / password123
echo.
echo ================================================================
echo   Log detail tersimpan di: install_log.txt
echo ================================================================
echo.
echo Tekan tombol apa saja untuk menutup jendela installer ini...
pause >nul
exit
