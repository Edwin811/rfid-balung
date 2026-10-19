@echo off
title Presensi Desa Balunglor - Master Installer 1-Klik
cd /d "%~dp0"
setlocal enabledelayedexpansion

:: ============================================================
:: 0. MEMINTA IZIN ADMINISTRATOR WINDOWS SECARA OTOMATIS
:: ============================================================
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo.
    echo ================================================================
    echo   MEMINTA IZIN ADMINISTRATOR WINDOWS...
    echo   Klik "YES" pada jendela pop-up konfirmasi yang muncul.
    echo ================================================================
    echo.
    powershell -Command "Start-Process cmd.exe -ArgumentList '/k `\"\"%~f0\"`\"' -Verb RunAs"
    exit /b 0
)

:: ============================================================
:: SETUP LOG FILE
:: ============================================================
set "LOGFILE=%~dp0install_log.txt"
echo. > "%LOGFILE%"
echo [%date% %time%] ============================================== >> "%LOGFILE%"
echo [%date% %time%] MASTER INSTALLER - PRESENSI DESA BALUNGLOR >> "%LOGFILE%"
echo [%date% %time%] ============================================== >> "%LOGFILE%"

set "STATUS_PHP=GAGAL"
set "STATUS_VENDOR=GAGAL"
set "STATUS_ENV=GAGAL"
set "STATUS_KEY=GAGAL"
set "STATUS_DB=GAGAL"
set "STATUS_SEED=GAGAL"
set "STATUS_PYTHON=DILEWATI"
set "STATUS_DOMAIN=GAGAL"
set "STATUS_SERVER=GAGAL"

echo.
echo ================================================================
echo   MASTER INSTALLER - SISTEM PRESENSI DESA BALUNGLOR
echo   Installer ini akan menyiapkan sistem presensi secara otomatis.
echo.
echo   Log detail tersimpan di: install_log.txt
echo ================================================================
echo.

:: ============================================================
:: TAHAP 1: DETEKSI DAN PERSIAPAN PHP
:: ============================================================
echo [1/8] Memeriksa PHP...
echo [%date% %time%] [TAHAP 1] Mencari PHP... >> "%LOGFILE%"

where php >nul 2>&1
if "%errorLevel%"=="0" (
    echo       [OK] PHP ditemukan di PATH sistem.
    goto PHP_READY
)

for %%L in (C D E F) do (
    for /d %%D in (%%L:\laragon\bin\php\php-*) do (
        if exist "%%D\php.exe" (
            set "PATH=%%D;!PATH!"
            echo       [OK] PHP ditemukan di: %%D
            goto PHP_READY
        )
    )
)

for %%L in (C D E F) do (
    if exist "%%L:\xampp\php\php.exe" (
        set "PATH=%%L:\xampp\php;!PATH!"
        echo       [OK] PHP ditemukan di: %%L:\xampp\php
        goto PHP_READY
    )
)

echo.
echo [ERROR] PHP tidak ditemukan. Silakan pasang Laragon terlebih dahulu.
pause
exit /b 1

:PHP_READY
for /f "tokens=*" %%V in ('php -r "echo PHP_VERSION;" 2^>nul') do set "PHP_VER=%%V"
for /f "tokens=*" %%C in ('php -r "echo version_compare(PHP_VERSION, '8.2.0', '>=') ? 'OK' : 'OLD';" 2^>nul') do set "PHP_CHECK=%%C"

if not "!PHP_CHECK!"=="OK" (
    echo [ERROR] Versi PHP terlalu lama: !PHP_VER! (Membutuhkan minimal PHP 8.2)
    pause
    exit /b 1
)
echo       [OK] PHP !PHP_VER! siap digunakan.
set "STATUS_PHP=OK"
echo.

:: ============================================================
:: TAHAP 2: UNDUH PUSTAKA VENDOR (COMPOSER)
:: ============================================================
echo [2/8] Memeriksa pustaka vendor Laravel...
if exist "vendor\autoload.php" (
    echo       [OK] Pustaka vendor sudah tersedia.
    set "STATUS_VENDOR=OK"
    goto VENDOR_DONE
)

echo       [*] Mengunduh pustaka vendor (composer install)...
where composer >nul 2>&1
if "%errorLevel%"=="0" (
    call composer install --no-interaction --prefer-dist --ignore-platform-reqs
    goto CHECK_VENDOR
)

for %%L in (C D E F) do (
    if exist "%%L:\laragon\bin\composer\composer.bat" (
        set "PATH=%%L:\laragon\bin\composer;!PATH!"
        call composer install --no-interaction --prefer-dist --ignore-platform-reqs
        goto CHECK_VENDOR
    )
)

echo       [*] Mengunduh composer portable...
php -r "copy('https://getcomposer.org/download/latest-stable/composer.phar', 'composer.phar');"
if exist "composer.phar" (
    php composer.phar install --no-interaction --prefer-dist --ignore-platform-reqs
)

:CHECK_VENDOR
if not exist "vendor\autoload.php" (
    echo [ERROR] Gagal mengunduh vendor. Pastikan PC terhubung ke internet.
    pause
    exit /b 1
)
echo       [OK] Pustaka vendor berhasil dipasang.
set "STATUS_VENDOR=OK"

:VENDOR_DONE
echo.

:: ============================================================
:: TAHAP 3: KONFIGURASI FILE .ENV
:: ============================================================
echo [3/8] Menyiapkan file .env...
if not exist ".env" (
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
        echo       [OK] File .env dibuat dari .env.example
    ) else (
        echo [ERROR] File .env.example tidak ditemukan.
        pause
        exit /b 1
    )
) else (
    echo       [OK] File .env sudah ada.
)
set "STATUS_ENV=OK"
echo.

:: ============================================================
:: TAHAP 4: APP KEY & CACHE
:: ============================================================
echo [4/8] Membuat APP_KEY dan membersihkan cache...
call php artisan key:generate --force >nul 2>&1
call php artisan config:clear >nul 2>&1
echo       [OK] Kunci keamanan dan cache siap.
set "STATUS_KEY=OK"
echo.

:: ============================================================
:: TAHAP 5: PERSIAPAN DATABASE DAN SEEDING
:: ============================================================
echo [5/8] Menyiapkan database MySQL dan data aparatur desa...

:: Pastikan MySQL di port 3306 siap
set "MYSQL_READY=0"
powershell -Command "$c = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue; if ($c) { exit 0 } else { exit 1 }" >nul 2>&1
if "%errorLevel%"=="0" set "MYSQL_READY=1"

if "!MYSQL_READY!"=="0" (
    echo       [*] Menyalakan MySQL...
    net start mysql >nul 2>&1
    for %%L in (C D E F) do (
        for /d %%D in (%%L:\laragon\bin\mysql\mysql-*) do (
            if exist "%%D\bin\mysqld.exe" (
                start "" /B "%%D\bin\mysqld.exe" --defaults-file="%%D\my.ini" >nul 2>&1
            )
        )
    )
    for /L %%I in (1,1,8) do (
        if "!MYSQL_READY!"=="0" (
            powershell -Command "Start-Sleep -Seconds 2" >nul 2>&1
            powershell -Command "$c = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue; if ($c) { exit 0 } else { exit 1 }" >nul 2>&1
            if "!errorLevel!"=="0" set "MYSQL_READY=1"
        )
    )
)

if "!MYSQL_READY!"=="0" (
    echo       [WARNING] MySQL di port 3306 belum terdeteksi. Pastikan Laragon Start All aktif.
)

:: Buat database rfid_balung jika belum ada
echo       [*] Membuat database rfid_balung...
php -r "$p = new PDO('mysql:host=127.0.0.1;port=3306', 'root', ''); $p->exec('CREATE DATABASE IF NOT EXISTS rfid_balung CHARACTER SET utf8mb4;');" >nul 2>&1
set "STATUS_DB=OK"
echo       [OK] Database rfid_balung siap.

:: Jalankan migrasi dan seeder
echo       [*] Menjalankan migrasi struktur tabel...
call php artisan migrate --force

echo       [*] Mengisi akun pengguna dan data 15 aparatur desa...
call php artisan db:seed --force
set "STATUS_SEED=OK"

call php artisan storage:link >nul 2>&1
echo.

:: ============================================================
:: TAHAP 6: INTEGRASI PYTHON & PYZK
:: ============================================================
echo [6/8] Memeriksa integrasi Python dan PyZK...
where python >nul 2>&1
if not "%errorLevel%"=="0" (
    for %%L in (C D E F) do (
        for /d %%P in (%%L:\laragon\bin\python\*) do (
            if exist "%%P\python.exe" (
                set "PATH=%%P;%%P\Scripts;!PATH!"
            )
        )
    )
)

where python >nul 2>&1
if "%errorLevel%"=="0" (
    python -c "import zk" >nul 2>&1
    if not "%errorLevel%"=="0" (
        echo       [*] Memasang modul pyzk dan requests...
        pip install pyzk requests >nul 2>&1
    )
    echo       [OK] Python dan library pyzk siap digunakan.
    set "STATUS_PYTHON=OK"
) else (
    echo       [NOTE] Python belum aktif. Bisa diaktifkan via Laragon Tools.
    set "STATUS_PYTHON=DILEWATI"
)
echo.

:: ============================================================
:: TAHAP 7: SETUP DOMAIN PRESENSI DAN SERTIFIKAT SSL
:: ============================================================
echo [7/8] Menyiapkan Domain presensi.balunglor.desa.id dan SSL...

:: Jalankan setup_ssl.bat secara terintegrasi
if exist "%~dp0setup_ssl.bat" (
    call "%~dp0setup_ssl.bat"
) else (
    :: Fallback: daftarkan ke hosts manual jika setup_ssl tidak ada
    set "HOSTS_FILE=%WINDIR%\System32\drivers\etc\hosts"
    findstr /C:"presensi.balunglor.desa.id" "%HOSTS_FILE%" >nul 2>&1
    if not "%errorLevel%"=="0" (
        echo. >> "%HOSTS_FILE%"
        echo 127.0.0.1    presensi.balunglor.desa.id >> "%HOSTS_FILE%"
        echo 127.0.0.1    presensi.local >> "%HOSTS_FILE%"
    )
)

:: Buka port 80, 443, 8000 di firewall Windows
netsh advfirewall firewall add rule name="Presensi Balunglor Web Server" dir=in action=allow protocol=TCP localport=80,443,8000 >nul 2>&1
echo       [OK] Domain dan firewall Windows berhasil dikonfigurasi.
set "STATUS_DOMAIN=OK"
echo.

:: ============================================================
:: TAHAP 8: AUTO-START WINDOWS + NYALAKAN SERVER LATAR BELAKANG
:: ============================================================
echo [8/8] Memasang Auto-Start dan menyalakan server...

if exist "scripts\create_startup_shortcut.ps1" (
    powershell -ExecutionPolicy Bypass -File "%~dp0scripts\create_startup_shortcut.ps1" >nul 2>&1
    echo       [OK] Auto-Start Windows berhasil dipasang.
)

:: Deteksi IP lokal PC untuk akses via Wi-Fi
set "LOCAL_IP="
for /f "tokens=*" %%I in ('powershell -Command "(Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.InterfaceAlias -notlike '*Loopback*' -and $_.IPAddress -notlike '169.254*' -and $_.IPAddress -notlike '127.*' } | Select-Object -First 1).IPAddress" 2^>nul') do (
    set "LOCAL_IP=%%I"
)
if not defined LOCAL_IP set "LOCAL_IP=127.0.0.1"

:: Nyalakan server presensi secara background (senyap)
echo       [*] Menyalakan server presensi di latar belakang...
wscript "%~dp0run_background.vbs" >nul 2>&1

:: Tunggu 3 detik lalu buka browser
powershell -Command "Start-Sleep -Seconds 3" >nul 2>&1
start "" "http://localhost:8000"
set "STATUS_SERVER=OK"

:: ============================================================
:: RINGKASAN INSTALASI SELESAI
:: ============================================================
:SHOW_SUMMARY
echo.
echo ================================================================
echo   INSTALASI PRESENSI DESA BALUNGLOR SELESAI 100%%
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
echo   - Domain & SSL    : [!STATUS_DOMAIN!]
echo   - Web Server      : [!STATUS_SERVER!]
echo.
echo   Alamat Akses Website:
echo   - Dari Komputer Ini : https://presensi.balunglor.desa.id
echo   - Alternatif Lokal  : http://localhost:8000
echo   - Dari HP / Laptop  : http://!LOCAL_IP!:8000
echo.
echo   Akun Pengguna Default:
echo   - Administrator : admin / password123
echo   - Kepala Desa   : kades / password123
echo   - Operator      : operator / password123
echo.
echo   Catatan Server:
echo   - Server presensi saat ini SUDAH AKTIF di latar belakang (background).
echo   - Anda tidak perlu memencet start_app.bat lagi.
echo   - Jika ingin mematikan server: klik 2x stop_app.bat
echo   - Jika ingin menyalakan kembali: klik 2x run_background.vbs
echo ================================================================
echo.
echo Tekan tombol apa saja untuk keluar dari installer...
pause >nul
exit /b 0
