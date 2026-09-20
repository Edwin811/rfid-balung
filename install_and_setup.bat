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
echo   Anda hanya perlu menunggu sampai selesai.
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

:: Cari di XAMPP (Drive C, D, E)
for %%L in (C D E) do (
    if exist "%%L:\xampp\php\php.exe" (
        set "PATH=%%L:\xampp\php;!PATH!"
        echo       [OK] PHP ditemukan di: %%L:\xampp\php
        echo [%date% %time%]   [OK] PHP ditemukan di: %%L:\xampp\php >> "%LOGFILE%"
        set "XAMPP_DRIVE=%%L"
        goto PHP_FIX_EXTENSIONS
    )
)

:: Cari di Laragon (Drive C, D, E)
for %%L in (C D E) do (
    for /d %%D in (%%L:\laragon\bin\php\php-*) do (
        if exist "%%D\php.exe" (
            set "PATH=%%D;!PATH!"
            echo       [OK] PHP ditemukan di: %%D
            echo [%date% %time%]   [OK] PHP ditemukan di: %%D >> "%LOGFILE%"
            goto PHP_READY
        )
    )
)

:: PHP tidak ditemukan sama sekali
echo.
echo       ============================================================
echo       [ERROR] PHP TIDAK DITEMUKAN DI KOMPUTER INI!
echo       ============================================================
echo.
echo       Silakan install salah satu dari:
echo       - Laragon (Disarankan): https://laragon.org/download/
echo       - XAMPP: https://www.apachefriends.org/download.html
echo.
echo       Setelah terpasang, jalankan kembali file ini.
echo.
echo [%date% %time%]   [ERROR] PHP TIDAK DITEMUKAN! >> "%LOGFILE%"
set /a INSTALL_ERRORS+=1
goto SHOW_SUMMARY

:PHP_FIX_EXTENSIONS
:: Auto-aktifkan ekstensi PHP yang dibutuhkan di php.ini XAMPP (gunakan drive yang terdeteksi)
echo       [*] Mengaktifkan ekstensi PHP yang dibutuhkan...
set "PHPINI=!XAMPP_DRIVE!:\xampp\php\php.ini"
echo [%date% %time%]   [*] Mengaktifkan ekstensi di: !PHPINI! >> "%LOGFILE%"
if exist "!PHPINI!" (
    powershell -Command "$c = Get-Content '!PHPINI!'; $c = $c -replace '^;extension=zip', 'extension=zip'; $c = $c -replace '^;extension=openssl', 'extension=openssl'; $c = $c -replace '^;extension=mbstring', 'extension=mbstring'; $c = $c -replace '^;extension=fileinfo', 'extension=fileinfo'; $c = $c -replace '^;extension=pdo_mysql', 'extension=pdo_mysql'; $c = $c -replace '^;extension=curl', 'extension=curl'; Set-Content '!PHPINI!' $c" >> "%LOGFILE%" 2>&1
    echo       [OK] Ekstensi PHP berhasil diaktifkan.
) else (
    echo       [NOTE] File php.ini tidak ditemukan di !PHPINI!
    echo [%date% %time%]   [NOTE] php.ini tidak ditemukan di !PHPINI! >> "%LOGFILE%"
)

:PHP_READY
:: Verifikasi PHP bisa jalan
php -v >nul 2>&1
if not "%errorLevel%"=="0" (
    echo       [ERROR] PHP ditemukan tapi gagal dijalankan.
    echo       Detail error:
    php -v 2>&1
    echo [%date% %time%]   [ERROR] PHP gagal dijalankan >> "%LOGFILE%"
    php -v >> "%LOGFILE%" 2>&1
    set /a INSTALL_ERRORS+=1
    goto SHOW_SUMMARY
)

:: Validasi versi PHP >= 8.2
echo [%date% %time%]   [*] Memeriksa versi PHP... >> "%LOGFILE%"
for /f "tokens=2 delims= " %%V in ('php -r "echo PHP_VERSION;"') do set "PHP_VER=%%V"
php -r "echo PHP_VERSION;" > "%TEMP%\php_ver.txt" 2>&1
set /p PHP_VER=<"%TEMP%\php_ver.txt"
del "%TEMP%\php_ver.txt" >nul 2>&1

php -r "if (version_compare(PHP_VERSION, '8.2.0', '<')) { echo 'OLD'; exit(1); } echo 'OK';" > "%TEMP%\php_check.txt" 2>&1
set /p PHP_CHECK=<"%TEMP%\php_check.txt"
del "%TEMP%\php_check.txt" >nul 2>&1

if "%PHP_CHECK%"=="OLD" (
    echo.
    echo       [ERROR] Versi PHP terlalu lama: %PHP_VER%
    echo       Laravel membutuhkan minimal PHP 8.2!
    echo       Silakan update PHP Anda melalui Laragon atau XAMPP terbaru.
    echo [%date% %time%]   [ERROR] PHP versi %PHP_VER% terlalu lama, butuh >= 8.2 >> "%LOGFILE%"
    set /a INSTALL_ERRORS+=1
    goto SHOW_SUMMARY
)

echo       [OK] PHP %PHP_VER% siap digunakan.
echo [%date% %time%]   [OK] PHP %PHP_VER% siap digunakan. >> "%LOGFILE%"
set "STATUS_PHP=OK"
echo.

:: ============================================================
:: TAHAP 2: UNDUH PUSTAKA VENDOR (COMPOSER INSTALL)
:: ============================================================
echo [2/8] Memeriksa pustaka vendor Laravel...
echo [%date% %time%] [TAHAP 2] Memeriksa pustaka vendor... >> "%LOGFILE%"

if exist "vendor\autoload.php" (
    echo       [OK] Pustaka vendor sudah tersedia. Melewati proses unduh.
    echo [%date% %time%]   [OK] vendor/autoload.php sudah ada. >> "%LOGFILE%"
    set "STATUS_VENDOR=OK"
    goto VENDOR_DONE
)

echo       [*] Pustaka vendor belum ada. Memulai pengunduhan...
echo [%date% %time%]   [*] Memulai composer install... >> "%LOGFILE%"

:: Cek apakah composer tersedia di sistem
where composer >nul 2>&1
if "%errorLevel%"=="0" (
    echo       [*] Menjalankan: composer install...
    call composer install --no-interaction --prefer-dist --ignore-platform-reqs >> "%LOGFILE%" 2>&1
    if "%errorLevel%"=="0" (
        echo       [OK] Composer install selesai.
    ) else (
        echo       [NOTE] Composer install mengembalikan error, memeriksa vendor...
    )
    goto CHECK_VENDOR
)

:: Cek composer di Laragon
for %%L in (C D E) do (
    if exist "%%L:\laragon\bin\composer\composer.bat" (
        set "PATH=%%L:\laragon\bin\composer;%PATH%"
        echo       [*] Menjalankan: composer install (via Laragon)...
        call composer install --no-interaction --prefer-dist --ignore-platform-reqs >> "%LOGFILE%" 2>&1
        goto CHECK_VENDOR
    )
)

:: Composer tidak ada - unduh portable composer.phar
echo       [*] Composer belum terpasang. Mengunduh portable composer...
echo [%date% %time%]   [*] Mengunduh composer.phar... >> "%LOGFILE%"
php -r "copy('https://getcomposer.org/download/latest-stable/composer.phar', 'composer.phar');"
if exist "composer.phar" (
    echo       [OK] Composer portable berhasil diunduh.
    echo       [*] Menjalankan: php composer.phar install...
    php composer.phar install --no-interaction --prefer-dist --ignore-platform-reqs >> "%LOGFILE%" 2>&1
) else (
    echo.
    echo       [ERROR] Gagal mengunduh Composer. Pastikan koneksi internet aktif.
    echo [%date% %time%]   [ERROR] Gagal mengunduh composer.phar >> "%LOGFILE%"
    set /a INSTALL_ERRORS+=1
    goto SHOW_SUMMARY
)

:CHECK_VENDOR
if not exist "vendor\autoload.php" (
    echo.
    echo       [ERROR] Pustaka vendor gagal terunduh!
    echo       Pastikan koneksi internet stabil dan coba jalankan ulang.
    echo       Lihat detail error di: install_log.txt
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
if "%errorLevel%"=="0" (
    echo       [OK] APP_KEY berhasil di-generate.
    echo [%date% %time%]   [OK] APP_KEY berhasil di-generate. >> "%LOGFILE%"
) else (
    echo       [WARNING] APP_KEY mungkin gagal di-generate. Lihat install_log.txt
    echo [%date% %time%]   [WARNING] APP_KEY gagal di-generate >> "%LOGFILE%"
)

call php artisan config:clear >> "%LOGFILE%" 2>&1
echo [%date% %time%]   [OK] Config cache dibersihkan. >> "%LOGFILE%"
set "STATUS_KEY=OK"
echo.

:: ============================================================
:: TAHAP 5: PERSIAPAN DATABASE DAN MIGRASI
:: ============================================================
echo [5/8] Menyiapkan database MySQL dan data aparatur desa...
echo [%date% %time%] [TAHAP 5] Persiapan database... >> "%LOGFILE%"

:: 1. Pastikan Service MySQL Berjalan dengan retry loop
echo       [*] Memeriksa koneksi MySQL...
echo [%date% %time%]   [*] Memeriksa koneksi MySQL di port 3306... >> "%LOGFILE%"

set "MYSQL_READY=0"

:: Cek apakah MySQL sudah aktif
powershell -Command "$c = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue; if ($c) { exit 0 } else { exit 1 }" >nul 2>&1
if "%errorLevel%"=="0" (
    set "MYSQL_READY=1"
    echo       [OK] MySQL sudah aktif.
    echo [%date% %time%]   [OK] MySQL sudah aktif di port 3306. >> "%LOGFILE%"
)

:: Jika MySQL belum aktif, coba start
if "%MYSQL_READY%"=="0" (
    echo       [*] MySQL belum aktif. Mencoba menyalakan...
    echo [%date% %time%]   [*] Mencoba menyalakan MySQL... >> "%LOGFILE%"

    :: Coba via service
    net start mysql >> "%LOGFILE%" 2>&1

    :: Coba via Laragon mysqld
    for %%L in (C D E) do (
        for /d %%D in (%%L:\laragon\bin\mysql\mysql-*) do (
            if exist "%%D\bin\mysqld.exe" (
                echo [%date% %time%]   [*] Ditemukan mysqld: %%D\bin\mysqld.exe >> "%LOGFILE%"
                if exist "%%D\my.ini" (
                    start "" /B "%%D\bin\mysqld.exe" --defaults-file="%%D\my.ini" >> "%LOGFILE%" 2>&1
                ) else (
                    start "" /B "%%D\bin\mysqld.exe" --console >> "%LOGFILE%" 2>&1
                )
            )
        )
    )

    :: Retry loop - tunggu MySQL siap (max 20 detik, cek setiap 2 detik)
    echo       [*] Menunggu MySQL siap menerima koneksi...
    for /L %%I in (1,1,10) do (
        if "!MYSQL_READY!"=="0" (
            powershell -Command "Start-Sleep -Seconds 2" >nul 2>&1
            powershell -Command "$c = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue; if ($c) { exit 0 } else { exit 1 }" >nul 2>&1
            if "!errorLevel!"=="0" (
                set "MYSQL_READY=1"
                echo       [OK] MySQL siap setelah %%I percobaan.
                echo [%date% %time%]   [OK] MySQL siap setelah %%I percobaan. >> "%LOGFILE%"
            ) else (
                echo       [*] Percobaan %%I/10 - MySQL belum siap...
            )
        )
    )
)

if "%MYSQL_READY%"=="0" (
    echo.
    echo       ============================================================
    echo       [ERROR] MySQL GAGAL DIAKTIFKAN!
    echo       ============================================================
    echo       Pastikan MySQL/MariaDB sudah terinstall di Laragon atau XAMPP.
    echo       Buka Laragon dan pastikan MySQL sudah Start.
    echo.
    echo [%date% %time%]   [ERROR] MySQL gagal aktif setelah 10 percobaan! >> "%LOGFILE%"
    set /a INSTALL_ERRORS+=1
    goto SHOW_SUMMARY
)

:: 2. Buat Database rfid_balung jika belum ada
echo       [*] Membuat database rfid_balung...
echo [%date% %time%]   [*] Membuat database rfid_balung... >> "%LOGFILE%"

php -r "try { $p=new PDO('mysql:host=127.0.0.1;port=3306','root',''); $p->exec('CREATE DATABASE IF NOT EXISTS `rfid_balung` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;'); echo 'DB_OK'; } catch(Exception $e) { echo 'DB_ERROR: '.$e->getMessage(); exit(1); }" > "%TEMP%\db_result.txt" 2>&1
set /p DB_RESULT=<"%TEMP%\db_result.txt"
del "%TEMP%\db_result.txt" >nul 2>&1

echo [%date% %time%]   Hasil create database: %DB_RESULT% >> "%LOGFILE%"

echo %DB_RESULT% | findstr /C:"DB_ERROR" >nul 2>&1
if "%errorLevel%"=="0" (
    echo       [ERROR] Gagal membuat database!
    echo       Detail: %DB_RESULT%
    echo       Kemungkinan penyebab:
    echo       - User MySQL bukan 'root' atau ada password
    echo       - MySQL belum benar-benar siap
    echo.
    echo [%date% %time%]   [ERROR] %DB_RESULT% >> "%LOGFILE%"
    set /a INSTALL_ERRORS+=1
    goto SHOW_SUMMARY
)
echo       [OK] Database rfid_balung siap.
set "STATUS_DB=OK"

:: 3. Cek apakah tabel sudah ada (mencegah data lama terhapus jika dijalankan ulang)
echo [%date% %time%]   [*] Memeriksa apakah tabel users sudah ada... >> "%LOGFILE%"

php -r "try { $p=new PDO('mysql:host=127.0.0.1;port=3306;dbname=rfid_balung','root',''); $s=$p->query('SHOW TABLES LIKE \'users\''); exit($s->rowCount() > 0 ? 0 : 1); } catch(Exception $e) { exit(1); }" >nul 2>&1
if not "%errorLevel%"=="0" (
    echo       [*] Database baru terdeteksi. Membuat skema dan mengisi 15 data aparatur...
    echo [%date% %time%]   [*] Database baru. Menjalankan migrate:fresh --seed... >> "%LOGFILE%"

    :: Jalankan migrate:fresh --seed dengan output penuh
    call php artisan migrate:fresh --seed --force >> "%LOGFILE%" 2>&1
    if "!errorLevel!"=="0" (
        echo       [OK] Database berhasil dibuat dengan data aparatur desa!
        echo [%date% %time%]   [OK] migrate:fresh --seed berhasil! >> "%LOGFILE%"
        set "STATUS_SEED=OK"
    ) else (
        echo       [WARNING] migrate:fresh --seed gagal. Mencoba langkah alternatif...
        echo [%date% %time%]   [WARNING] migrate:fresh --seed gagal, mencoba fallback... >> "%LOGFILE%"

        :: Fallback 1: migrate saja
        call php artisan migrate --force >> "%LOGFILE%" 2>&1
        echo [%date% %time%]   Fallback migrate selesai. >> "%LOGFILE%"

        :: Fallback 2: JALANKAN SEEDER TERPISAH (fix masalah utama!)
        echo       [*] Menjalankan seeder database secara terpisah...
        echo [%date% %time%]   [*] Menjalankan db:seed --force... >> "%LOGFILE%"
        call php artisan db:seed --force >> "%LOGFILE%" 2>&1
        if "!errorLevel!"=="0" (
            echo       [OK] Seeder berhasil dijalankan!
            echo [%date% %time%]   [OK] db:seed berhasil! >> "%LOGFILE%"
            set "STATUS_SEED=OK"
        ) else (
            echo       [ERROR] Seeder gagal! Akun login mungkin tidak tersedia.
            echo       Lihat detail error di: install_log.txt
            echo [%date% %time%]   [ERROR] db:seed gagal! >> "%LOGFILE%"
            set /a INSTALL_ERRORS+=1
        )
    )
) else (
    echo       [OK] Database sudah memiliki data sebelumnya. Menjalankan update migrasi aman...
    echo [%date% %time%]   [OK] Tabel users ada. Menjalankan migrate saja... >> "%LOGFILE%"
    call php artisan migrate --force >> "%LOGFILE%" 2>&1
    set "STATUS_SEED=OK"
)

:: 4. Validasi database: pastikan ada user untuk login
echo [%date% %time%]   [*] Validasi data user di database... >> "%LOGFILE%"

php -r "try { $p=new PDO('mysql:host=127.0.0.1;port=3306;dbname=rfid_balung','root',''); $s=$p->query('SELECT COUNT(*) as c FROM users'); $r=$s->fetch(); if($r['c']>0){echo 'USERS_OK:'.$r['c']; exit(0);} else {echo 'USERS_EMPTY'; exit(1);} } catch(Exception $e) { echo 'CHECK_ERROR:'.$e->getMessage(); exit(1); }" > "%TEMP%\user_check.txt" 2>&1
set /p USER_CHECK=<"%TEMP%\user_check.txt"
del "%TEMP%\user_check.txt" >nul 2>&1
echo [%date% %time%]   Hasil validasi users: %USER_CHECK% >> "%LOGFILE%"

echo %USER_CHECK% | findstr /C:"USERS_OK" >nul 2>&1
if not "%errorLevel%"=="0" (
    echo.
    echo       ============================================================
    echo       [ERROR] DATABASE TIDAK MEMILIKI AKUN LOGIN!
    echo       ============================================================
    echo       Seeder gagal membuat akun pengguna.
    echo       Mencoba menjalankan seeder sekali lagi...
    echo.
    echo [%date% %time%]   [ERROR] Tabel users kosong! Mencoba seed ulang... >> "%LOGFILE%"

    call php artisan db:seed --force >> "%LOGFILE%" 2>&1
    if "!errorLevel!"=="0" (
        echo       [OK] Seeder berhasil dijalankan pada percobaan kedua!
        echo [%date% %time%]   [OK] db:seed percobaan kedua berhasil! >> "%LOGFILE%"
        set "STATUS_SEED=OK"
    ) else (
        echo       [ERROR] Seeder tetap gagal. Lihat install_log.txt untuk detail error.
        echo [%date% %time%]   [ERROR] db:seed percobaan kedua juga gagal! >> "%LOGFILE%"
        set /a INSTALL_ERRORS+=1
    )
) else (
    echo       [OK] Data user tervalidasi: akun login tersedia.
)

:: 5. Buat Storage Link
call php artisan storage:link >> "%LOGFILE%" 2>&1
echo.

:: ============================================================
:: TAHAP 6: INTEGRASI MESIN RFID (PYTHON & PYZK)
:: ============================================================
echo [6/8] Memeriksa integrasi mesin RFID (Python dan PyZK)...
echo [%date% %time%] [TAHAP 6] Cek Python dan PyZK... >> "%LOGFILE%"

where python >nul 2>&1
if not "%errorLevel%"=="0" (
    for %%L in (C D E) do (
        for /d %%P in (%%L:\laragon\bin\python\*) do (
            if exist "%%P\python.exe" (
                set "PATH=%%P;%%P\Scripts;%PATH%"
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
        echo [%date% %time%]   [*] pip install pyzk requests... >> "%LOGFILE%"
        pip install pyzk requests >> "%LOGFILE%" 2>&1
        python -c "import zk" >nul 2>&1
        if "%errorLevel%"=="0" (
            echo       [OK] Library pyzk dan requests berhasil dipasang!
            echo [%date% %time%]   [OK] pyzk berhasil dipasang. >> "%LOGFILE%"
            set "STATUS_PYTHON=OK"
        ) else (
            echo       [WARNING] Library pyzk belum terpasang otomatis.
            echo       Coba jalankan manual: pip install pyzk requests
            echo [%date% %time%]   [WARNING] pyzk gagal dipasang otomatis. >> "%LOGFILE%"
            set "STATUS_PYTHON=WARNING"
        )
    ) else (
        echo       [OK] Python dan library pyzk siap digunakan.
        echo [%date% %time%]   [OK] Python dan pyzk sudah siap. >> "%LOGFILE%"
        set "STATUS_PYTHON=OK"
    )
) else (
    echo       [NOTE] Python belum terdeteksi di komputer ini.
    echo              Untuk komunikasi otomatis ke mesin ZKTeco via kabel LAN,
    echo              silakan pasang Python melalui:
    echo              - Menu Laragon: Klik kanan Laragon -^> Tools -^> Quick add -^> Python
    echo              - ATAU download dari: https://www.python.org/downloads/ (centang Add to PATH)
    echo [%date% %time%]   [NOTE] Python tidak ditemukan. >> "%LOGFILE%"
    set "STATUS_PYTHON=TIDAK ADA"
)
echo.

:: ============================================================
:: TAHAP 7: DAFTARKAN DOMAIN LOKAL DESA & SSL HTTPS
:: ============================================================
echo [7/8] Mendaftarkan domain presensi.balunglor.desa.id dan SSL...
echo [%date% %time%] [TAHAP 7] Domain dan SSL... >> "%LOGFILE%"

set "HOSTS_FILE=%WINDIR%\System32\drivers\etc\hosts"
findstr /C:"presensi.balunglor.desa.id" "%HOSTS_FILE%" >nul 2>&1
if "%errorLevel%"=="0" (
    echo       [OK] Domain sudah terdaftar di Windows hosts.
    echo [%date% %time%]   [OK] Domain sudah ada di hosts file. >> "%LOGFILE%"
) else (
    net session >nul 2>&1
    if "%errorLevel%"=="0" (
        echo. >> "%HOSTS_FILE%"
        echo 127.0.0.1    presensi.balunglor.desa.id >> "%HOSTS_FILE%"
        echo 127.0.0.1    presensi.local >> "%HOSTS_FILE%"
        echo       [OK] Domain berhasil didaftarkan!
        echo [%date% %time%]   [OK] Domain didaftarkan langsung (admin). >> "%LOGFILE%"
    ) else (
        echo       [*] Meminta izin Administrator untuk mendaftarkan domain...
        echo [%date% %time%]   [*] Meminta UAC untuk hosts file... >> "%LOGFILE%"
        powershell -Command "Start-Process cmd.exe -ArgumentList '/c echo. >> \"%HOSTS_FILE%\" & echo 127.0.0.1    presensi.balunglor.desa.id >> \"%HOSTS_FILE%\" & echo 127.0.0.1    presensi.local >> \"%HOSTS_FILE%\"' -Verb RunAs -Wait" >nul 2>&1
        findstr /C:"presensi.balunglor.desa.id" "%HOSTS_FILE%" >nul 2>&1
        if "%errorLevel%"=="0" (
            echo       [OK] Domain berhasil didaftarkan!
            echo [%date% %time%]   [OK] Domain didaftarkan via UAC. >> "%LOGFILE%"
        ) else (
            echo       [NOTE] Domain gagal didaftarkan otomatis. Akses via http://localhost:8000
            echo [%date% %time%]   [NOTE] Domain gagal didaftarkan. >> "%LOGFILE%"
        )
    )
)

:: Pasang SSL otomatis jika ada setup_ssl.bat
if exist "%~dp0setup_ssl.bat" (
    echo       [*] Mengonfigurasi VirtualHost Apache dan Sertifikat SSL...
    echo [%date% %time%]   [*] Menjalankan setup_ssl.bat... >> "%LOGFILE%"
    powershell -Command "Start-Process cmd.exe -ArgumentList '/c `\"\"%~dp0setup_ssl.bat\"`\"' -Verb RunAs -Wait" >> "%LOGFILE%" 2>&1
    echo       [OK] Konfigurasi SSL selesai diproses.
    echo [%date% %time%]   [OK] setup_ssl.bat selesai. >> "%LOGFILE%"
)

:: Buka Firewall Windows untuk akses HP/Laptop lain di Wi-Fi kantor desa
net session >nul 2>&1
if "%errorLevel%"=="0" (
    netsh advfirewall firewall add rule name="Presensi Balunglor Web Server" dir=in action=allow protocol=TCP localport=80,443,8000 >> "%LOGFILE%" 2>&1
    echo       [OK] Firewall Windows dibuka untuk port 80, 443, 8000.
    echo [%date% %time%]   [OK] Firewall rule ditambahkan. >> "%LOGFILE%"
) else (
    echo       [NOTE] Firewall belum dibuka (butuh hak Administrator).
    echo              Jalankan installer dengan klik kanan -^> Run as Administrator.
    echo [%date% %time%]   [NOTE] Firewall gagal - bukan admin. >> "%LOGFILE%"
)
set "STATUS_DOMAIN=OK"
echo.

:: ============================================================
:: TAHAP 8: AUTO-START WINDOWS + LANGSUNG NYALAKAN SERVER
:: ============================================================
echo [8/8] Memasang Auto-Start dan menyalakan server...
echo [%date% %time%] [TAHAP 8] Auto-Start dan start server... >> "%LOGFILE%"

:: Pasang Auto-Start otomatis (PC desa harus selalu aktif)
if exist "scripts\create_startup_shortcut.ps1" (
    powershell -ExecutionPolicy Bypass -File "%~dp0scripts\create_startup_shortcut.ps1" >> "%LOGFILE%" 2>&1
    echo       [OK] Auto-Start berhasil dipasang!
    echo       Setiap kali PC desa dinyalakan, server presensi
    echo       akan otomatis aktif di background.
    echo [%date% %time%]   [OK] Auto-Start shortcut dibuat. >> "%LOGFILE%"
) else (
    echo       [NOTE] Script Auto-Start tidak ditemukan.
    echo [%date% %time%]   [NOTE] create_startup_shortcut.ps1 tidak ditemukan. >> "%LOGFILE%"
)
echo.

:: ============================================================
:: LANGSUNG NYALAKAN SERVER + ZK DAEMON (TANPA TANYA!)
:: ============================================================
echo  [*] Menyalakan web server dan daemon sinkronisasi mesin...
echo [%date% %time%]   [*] Menyalakan server via run_background.vbs... >> "%LOGFILE%"

:: Jalankan server + ZK daemon di background
if exist "%~dp0run_background.vbs" (
    start "" wscript.exe "%~dp0run_background.vbs"
    echo       [OK] Server berhasil dinyalakan di background.
    echo [%date% %time%]   [OK] run_background.vbs diluncurkan. >> "%LOGFILE%"
) else (
    echo       [WARNING] run_background.vbs tidak ditemukan!
    echo       Mencoba start manual...
    echo [%date% %time%]   [WARNING] run_background.vbs tidak ada, mencoba start_app.bat... >> "%LOGFILE%"
    if exist "%~dp0start_app.bat" (
        start "" cmd /c "%~dp0start_app.bat"
        echo       [OK] Server dinyalakan via start_app.bat.
    ) else (
        echo       [ERROR] Tidak ada file starter yang ditemukan!
        set /a INSTALL_ERRORS+=1
    )
)

:: Tunggu server siap (max 15 detik)
echo       [*] Menunggu server siap...
set "SERVER_READY=0"
for /L %%I in (1,1,10) do (
    if "!SERVER_READY!"=="0" (
        powershell -Command "Start-Sleep -Seconds 1.5" >nul 2>&1
        powershell -Command "try { $r = Invoke-WebRequest -Uri 'http://127.0.0.1:8000' -TimeoutSec 2 -UseBasicParsing -ErrorAction Stop; if ($r.StatusCode -eq 200 -or $r.StatusCode -eq 302) { exit 0 } else { exit 1 } } catch { exit 1 }" >nul 2>&1
        if "!errorLevel!"=="0" (
            set "SERVER_READY=1"
            echo       [OK] Web server aktif dan dapat diakses!
            echo [%date% %time%]   [OK] Web server merespons setelah percobaan %%I >> "%LOGFILE%"
        )
    )
)

if "%SERVER_READY%"=="1" (
    set "STATUS_SERVER=OK"
    :: Buka browser otomatis
    echo       [*] Membuka browser...
    start "" "http://localhost:8000"
) else (
    echo       [WARNING] Server belum merespons setelah 15 detik.
    echo       Server mungkin masih dalam proses loading.
    echo       Coba buka manual: http://localhost:8000
    echo [%date% %time%]   [WARNING] Server belum merespons setelah 15 detik. >> "%LOGFILE%"
    set "STATUS_SERVER=MENUNGGU"
    :: Tetap buka browser, mungkin akan ready sebentar lagi
    start "" "http://localhost:8000"
)

:: ============================================================
:: RINGKASAN INSTALASI
:: ============================================================
:SHOW_SUMMARY
echo.
echo ================================================================
if "%INSTALL_ERRORS%"=="0" (
    echo   INSTALASI SELESAI 100%% BERHASIL!
) else (
    echo   INSTALASI SELESAI DENGAN %INSTALL_ERRORS% MASALAH
)
echo ================================================================
echo.
echo   Status Setiap Tahap:
echo   [%STATUS_PHP%] PHP
echo   [%STATUS_VENDOR%] Pustaka Vendor
echo   [%STATUS_ENV%] File .env
echo   [%STATUS_KEY%] APP_KEY
echo   [%STATUS_DB%] Database MySQL
echo   [%STATUS_SEED%] Data Aparatur ^& Akun Login
echo   [%STATUS_PYTHON%] Python ^& PyZK
echo   [%STATUS_DOMAIN%] Domain ^& SSL
echo   [%STATUS_SERVER%] Web Server
echo.

if "%INSTALL_ERRORS%"=="0" (
    echo   Alamat Akses:
    echo   - https://presensi.balunglor.desa.id  (Aman dengan Gembok SSL)
    echo   - http://presensi.balunglor.desa.id:8000
    echo   - http://localhost:8000
    echo.
    echo   Akun Pengguna Default:
    echo   1. Administrator:
    echo      Username : admin
    echo      Password : password123
    echo   2. Kepala Desa:
    echo      Username : kades
    echo      Password : password123
    echo   3. Sekretaris Desa:
    echo      Username : wakilkades
    echo      Password : password123
    echo   4. Operator Presensi:
    echo      Username : operator
    echo      Password : password123
    echo.
    echo   File Penting:
    echo   - run_background.vbs  = Nyalakan server secara senyap (1 klik)
    echo   - stop_app.bat        = Matikan server
    echo   - start_app.bat       = Nyalakan server dengan jendela log
    echo.
) else (
    echo   [!] Ada masalah selama instalasi.
    echo   Lihat detail error di file: install_log.txt
    echo   Lokasi: %LOGFILE%
    echo.
    echo   Setelah masalah diperbaiki, jalankan installer ini kembali.
    echo.
)

echo ================================================================
echo   Log detail instalasi tersimpan di: install_log.txt
echo ================================================================
echo.
echo [%date% %time%] ============================================== >> "%LOGFILE%"
echo [%date% %time%] INSTALASI SELESAI. Error: %INSTALL_ERRORS% >> "%LOGFILE%"
echo [%date% %time%] Status: PHP=%STATUS_PHP% VENDOR=%STATUS_VENDOR% ENV=%STATUS_ENV% KEY=%STATUS_KEY% DB=%STATUS_DB% SEED=%STATUS_SEED% PYTHON=%STATUS_PYTHON% DOMAIN=%STATUS_DOMAIN% SERVER=%STATUS_SERVER% >> "%LOGFILE%"
echo [%date% %time%] ============================================== >> "%LOGFILE%"

pause
exit
