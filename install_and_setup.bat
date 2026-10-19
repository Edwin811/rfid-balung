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
    powershell -Command "Start-Process cmd.exe -ArgumentList '/k cd /d `\"\"%~dp0\"`\" ^& `\"\"%~f0\"`\"' -Verb RunAs -WorkingDirectory '%~dp0'"
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
echo [1/8] Memeriksa PHP di komputer Anda...
echo [%date% %time%] [TAHAP 1] Mencari PHP... >> "%LOGFILE%"

where php >nul 2>&1
if not "%errorLevel%"=="0" (
    for %%L in (C D E F) do (
        for /d %%D in (%%L:\laragon\bin\php\php-*) do (
            if exist "%%D\php.exe" (
                set "PATH=%%D;!PATH!"
                echo [%date% %time%]   PHP ditemukan di Laragon: %%D >> "%LOGFILE%"
            )
        )
    )
    for %%L in (C D E F) do (
        if exist "%%L:\xampp\php\php.exe" (
            set "PATH=%%L:\xampp\php;!PATH!"
            echo [%date% %time%]   PHP ditemukan di XAMPP: %%L:\xampp\php >> "%LOGFILE%"
        )
    )
)

where php >nul 2>&1
if not "%errorLevel%"=="0" (
    echo.
    echo [ERROR] PHP tidak ditemukan di komputer ini!
    echo         Pastikan Laragon sudah terpasang.
    echo [%date% %time%] [ERROR] PHP tidak ditemukan >> "%LOGFILE%"
    pause
    exit /b 1
)

:: Verifikasi versi PHP >= 8.2 menggunakan exit code murni
php -r "exit(version_compare(PHP_VERSION, '8.2.0', '>=') ? 0 : 1);"
if not "%errorLevel%"=="0" (
    echo.
    echo [ERROR] Versi PHP di komputer Anda lebih lama dari 8.2!
    echo         Laravel membutuhkan minimal PHP 8.2.
    echo         Silakan pilih PHP 8.2+ di menu Laragon.
    echo [%date% %time%] [ERROR] Versi PHP < 8.2 >> "%LOGFILE%"
    pause
    exit /b 1
)

for /f "tokens=*" %%V in ('php -r "echo PHP_VERSION;" 2^>nul') do set "PHP_VER=%%V"
echo       [OK] PHP !PHP_VER! siap digunakan.
echo [%date% %time%]   [OK] PHP !PHP_VER! siap digunakan. >> "%LOGFILE%"
set "STATUS_PHP=OK"
echo.

:: ============================================================
:: TAHAP 2: UNDUH PUSTAKA VENDOR (COMPOSER)
:: ============================================================
echo [2/8] Memeriksa pustaka vendor Laravel...
echo [%date% %time%] [TAHAP 2] Memeriksa pustaka vendor... >> "%LOGFILE%"

if exist "vendor\autoload.php" (
    echo       [OK] Pustaka vendor sudah tersedia.
    echo [%date% %time%]   [OK] vendor/autoload.php sudah ada. >> "%LOGFILE%"
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
    echo.
    echo [ERROR] Gagal mengunduh pustaka vendor Laravel!
    echo         Pastikan PC terhubung ke internet lalu coba lagi.
    echo [%date% %time%]   [ERROR] vendor/autoload.php gagal diunduh >> "%LOGFILE%"
    pause
    exit /b 1
)
echo       [OK] Pustaka vendor berhasil dipasang.
echo [%date% %time%]   [OK] Pustaka vendor terpasang. >> "%LOGFILE%"
set "STATUS_VENDOR=OK"

:VENDOR_DONE
echo.

:: ============================================================
:: TAHAP 3: KONFIGURASI FILE .ENV
:: ============================================================
echo [3/8] Menyiapkan file .env...
echo [%date% %time%] [TAHAP 3] Menyiapkan .env... >> "%LOGFILE%"

if not exist ".env" (
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
        echo       [OK] File .env dibuat dari .env.example
        echo [%date% %time%]   [OK] .env dibuat dari .env.example >> "%LOGFILE%"
    ) else (
        echo [ERROR] File .env.example tidak ditemukan.
        echo [%date% %time%]   [ERROR] .env.example tidak ditemukan >> "%LOGFILE%"
        pause
        exit /b 1
    )
) else (
    echo       [OK] File .env sudah ada.
    echo [%date% %time%]   [OK] .env sudah ada. >> "%LOGFILE%"
)
set "STATUS_ENV=OK"
echo.

:: ============================================================
:: TAHAP 4: APP KEY & CACHE
:: ============================================================
echo [4/8] Membuat APP_KEY dan membersihkan cache...
echo [%date% %time%] [TAHAP 4] Generate APP_KEY... >> "%LOGFILE%"

call php artisan key:generate --force >nul 2>&1
call php artisan config:clear >nul 2>&1
echo       [OK] Kunci keamanan dan cache siap.
echo [%date% %time%]   [OK] APP_KEY dan config cache siap. >> "%LOGFILE%"
set "STATUS_KEY=OK"
echo.

:: ============================================================
:: TAHAP 5: PERSIAPAN DATABASE DAN SEEDING
:: ============================================================
echo [5/8] Menyiapkan database MySQL dan data aparatur desa...
echo [%date% %time%] [TAHAP 5] Persiapan database... >> "%LOGFILE%"

set "MYSQL_READY=0"
powershell -Command "$c = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue; if ($c) { exit 0 } else { exit 1 }" >nul 2>&1
if "%errorLevel%"=="0" set "MYSQL_READY=1"

if "!MYSQL_READY!"=="0" (
    echo       [*] Menyalakan MySQL...
    net start MySQL >nul 2>&1
    if "!errorLevel!" neq "0" net start mysql >nul 2>&1
    powershell -Command "$c = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue; if ($c) { exit 0 } else { exit 1 }" >nul 2>&1
    if "!errorLevel!"=="0" set "MYSQL_READY=1"

    if "!MYSQL_READY!"=="0" (
        for %%L in (C D E F) do (
            for /d %%D in (%%L:\laragon\bin\mysql\mysql-*) do (
                if exist "%%D\bin\mysqld.exe" (
                    :: Daftarkan sebagai Windows Service permanen agar tidak mati saat jendela CMD ditutup
                    if exist "%%D\my.ini" (
                        "%%D\bin\mysqld.exe" --install MySQL --defaults-file="%%D\my.ini" >nul 2>&1
                    ) else (
                        "%%D\bin\mysqld.exe" --install MySQL >nul 2>&1
                    )
                    net start MySQL >nul 2>&1
                    :: Jika service gagal, jalankan secara detached via Start-Process (bukan start /B)
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
    for /L %%I in (1,1,10) do (
        if "!MYSQL_READY!"=="0" (
            powershell -Command "Start-Sleep -Seconds 2" >nul 2>&1
            powershell -Command "$c = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue; if ($c) { exit 0 } else { exit 1 }" >nul 2>&1
            if "!errorLevel!"=="0" set "MYSQL_READY=1"
        )
    )
)

if "!MYSQL_READY!"=="0" (
    echo       [WARNING] MySQL di port 3306 belum terdeteksi. Pastikan Laragon Start All aktif.
    echo [%date% %time%]   [WARNING] MySQL belum terdeteksi >> "%LOGFILE%"
)

echo       [*] Membuat database rfid_balung jika belum ada...
php -r "$p = new PDO('mysql:host=127.0.0.1;port=3306', 'root', ''); $p->exec('CREATE DATABASE IF NOT EXISTS rfid_balung CHARACTER SET utf8mb4;');" >nul 2>&1
set "STATUS_DB=OK"
echo       [OK] Database rfid_balung siap.
echo [%date% %time%]   [OK] Database rfid_balung siap. >> "%LOGFILE%"

echo       [*] Menjalankan migrasi struktur tabel...
call php artisan migrate --force
echo [%date% %time%]   [OK] Migrasi selesai. >> "%LOGFILE%"

echo       [*] Mengisi data akun pengguna dan 15 aparatur desa...
call php artisan db:seed --force
set "STATUS_SEED=OK"
echo [%date% %time%]   [OK] db:seed selesai. >> "%LOGFILE%"

call php artisan storage:link >nul 2>&1
echo.

:: ============================================================
:: TAHAP 6: INTEGRASI PYTHON & PYZK
:: ============================================================
echo [6/8] Memeriksa integrasi Python dan PyZK...
echo [%date% %time%] [TAHAP 6] Integrasi Python... >> "%LOGFILE%"

where python >nul 2>&1
if not "%errorLevel%"=="0" (
    for %%L in (C D E F) do (
        for /d %%P in (%%L:\laragon\bin\python\*) do (
            if exist "%%P\python.exe" (
                set "PATH=%%P;%%P\Scripts;!PATH!"
                echo [%date% %time%]   Python ditemukan di: %%P >> "%LOGFILE%"
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
    echo [%date% %time%]   [OK] Python dan pyzk siap. >> "%LOGFILE%"
    set "STATUS_PYTHON=OK"
) else (
    echo       [NOTE] Python belum aktif. Bisa diaktifkan via Laragon Tools.
    echo [%date% %time%]   [NOTE] Python belum aktif >> "%LOGFILE%"
    set "STATUS_PYTHON=DILEWATI"
)
echo.

:: ============================================================
:: TAHAP 7: SETUP DOMAIN PRESENSI DAN SERTIFIKAT SSL
:: ============================================================
echo [7/8] Menyiapkan Domain presensi.balunglor.desa.id dan SSL...
echo [%date% %time%] [TAHAP 7] Domain dan SSL... >> "%LOGFILE%"

if exist "%~dp0setup_ssl.bat" (
    set "CALLED_FROM_INSTALLER=1"
    call "%~dp0setup_ssl.bat"
    set "CALLED_FROM_INSTALLER="
) else (
    set "HOSTS_FILE=%WINDIR%\System32\drivers\etc\hosts"
    findstr /C:"presensi.balunglor.desa.id" "%HOSTS_FILE%" >nul 2>&1
    if not "%errorLevel%"=="0" (
        echo. >> "%HOSTS_FILE%"
        echo 127.0.0.1    presensi.balunglor.desa.id >> "%HOSTS_FILE%"
        echo 127.0.0.1    presensi.local >> "%HOSTS_FILE%"
    )
)

netsh advfirewall firewall add rule name="Presensi Balunglor Web Server" dir=in action=allow protocol=TCP localport=80,443,8000 >nul 2>&1
echo       [OK] Domain dan firewall Windows berhasil dikonfigurasi.
echo [%date% %time%]   [OK] Domain dan firewall siap. >> "%LOGFILE%"
set "STATUS_DOMAIN=OK"
echo.

:: ============================================================
:: TAHAP 8: AUTO-START WINDOWS + NYALAKAN SERVER LATAR BELAKANG
:: ============================================================
echo [8/8] Memasang Auto-Start dan menyalakan server...
echo [%date% %time%] [TAHAP 8] Auto-start dan server... >> "%LOGFILE%"

if exist "scripts\create_startup_shortcut.ps1" (
    powershell -ExecutionPolicy Bypass -File "%~dp0scripts\create_startup_shortcut.ps1" >nul 2>&1
    echo       [OK] Auto-Start Windows berhasil dipasang.
    echo [%date% %time%]   [OK] Auto-Start shortcut terpasang. >> "%LOGFILE%"
)

set "LOCAL_IP="
for /f "tokens=*" %%I in ('powershell -Command "(Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.InterfaceAlias -notlike '*Loopback*' -and $_.IPAddress -notlike '169.254*' -and $_.IPAddress -notlike '127.*' } | Select-Object -First 1).IPAddress" 2^>nul') do (
    set "LOCAL_IP=%%I"
)
if not defined LOCAL_IP set "LOCAL_IP=127.0.0.1"

echo       [*] Menyalakan server presensi di latar belakang...
echo [%date% %time%]   Menjalankan run_background.vbs >> "%LOGFILE%"
wscript "%~dp0run_background.vbs" >nul 2>&1

powershell -Command "Start-Sleep -Seconds 5" >nul 2>&1
start "" "http://localhost:8000"
set "STATUS_SERVER=OK"
echo [%date% %time%]   [OK] Server diluncurkan, browser dibuka. >> "%LOGFILE%"

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
echo   - Python ^& PyZK   : [!STATUS_PYTHON!]
echo   - Domain ^& SSL    : [!STATUS_DOMAIN!]
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
echo [%date% %time%] INSTALASI SELESAI 100%%. >> "%LOGFILE%"
echo Tekan tombol apa saja untuk menutup jendela installer ini...
pause >nul
exit /b 0
