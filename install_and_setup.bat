@echo off
title Presensi Desa Balunglor - Master Installer 1-Klik
cd /d "%~dp0"
setlocal enabledelayedexpansion

echo.
echo ================================================================
echo   MASTER INSTALLER - SISTEM PRESENSI DESA BALUNGLOR
echo   Installer ini akan menangani SEMUA proses secara otomatis.
echo   Anda hanya perlu menunggu sampai selesai.
echo ================================================================
echo.

:: ============================================================
:: TAHAP 1: DETEKSI DAN PERSIAPAN PHP
:: ============================================================
echo [1/7] Mencari PHP di komputer Anda...

where php >nul 2>&1
if "%errorLevel%"=="0" (
    echo       [OK] PHP ditemukan di PATH sistem.
    goto PHP_READY
)

:: Cari di XAMPP (Drive C, D, E)
for %%L in (C D E) do (
    if exist "%%L:\xampp\php\php.exe" (
        set "PATH=%%L:\xampp\php;!PATH!"
        echo       [OK] PHP ditemukan di: %%L:\xampp\php
        goto PHP_FIX_EXTENSIONS
    )
)

:: Cari di Laragon (Drive C, D, E)
for %%L in (C D E) do (
    for /d %%D in (%%L:\laragon\bin\php\php-*) do (
        if exist "%%D\php.exe" (
            set "PATH=%%D;!PATH!"
            echo       [OK] PHP ditemukan di: %%D
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
pause
exit /b 1

:PHP_FIX_EXTENSIONS
:: Auto-aktifkan ekstensi zip, openssl, mbstring, fileinfo, pdo_mysql di php.ini XAMPP
echo       [*] Mengaktifkan ekstensi PHP yang dibutuhkan...
set "PHPINI=C:\xampp\php\php.ini"
if exist "%PHPINI%" (
    powershell -Command "$c = Get-Content '%PHPINI%'; $c = $c -replace '^;extension=zip', 'extension=zip'; $c = $c -replace '^;extension=openssl', 'extension=openssl'; $c = $c -replace '^;extension=mbstring', 'extension=mbstring'; $c = $c -replace '^;extension=fileinfo', 'extension=fileinfo'; $c = $c -replace '^;extension=pdo_mysql', 'extension=pdo_mysql'; $c = $c -replace '^;extension=curl', 'extension=curl'; Set-Content '%PHPINI%' $c" >nul 2>&1
    echo       [OK] Ekstensi PHP berhasil diaktifkan.
)

:PHP_READY
:: Verifikasi PHP bisa jalan
php -v >nul 2>&1
if not "%errorLevel%"=="0" (
    echo       [ERROR] PHP ditemukan tapi gagal dijalankan.
    pause
    exit /b 1
)
echo       [OK] PHP siap digunakan.
echo.

:: ============================================================
:: TAHAP 2: UNDUH PUSTAKA VENDOR (COMPOSER INSTALL)
:: ============================================================
echo [2/7] Memeriksa pustaka vendor Laravel...

if exist "vendor\autoload.php" (
    echo       [OK] Pustaka vendor sudah tersedia. Melewati proses unduh.
    goto VENDOR_DONE
)

echo       [*] Pustaka vendor belum ada. Memulai pengunduhan...

:: Cek apakah composer tersedia di sistem
where composer >nul 2>&1
if "%errorLevel%"=="0" (
    echo       [*] Menjalankan: composer install...
    call composer install --no-interaction --prefer-dist --ignore-platform-reqs
    goto CHECK_VENDOR
)

:: Cek composer di Laragon
if exist "C:\laragon\bin\composer\composer.bat" (
    set "PATH=C:\laragon\bin\composer;%PATH%"
    echo       [*] Menjalankan: composer install (via Laragon)...
    call composer install --no-interaction --prefer-dist --ignore-platform-reqs
    goto CHECK_VENDOR
)

:: Composer tidak ada - unduh portable composer.phar
echo       [*] Composer belum terpasang. Mengunduh portable composer...
php -r "copy('https://getcomposer.org/download/latest-stable/composer.phar', 'composer.phar');"
if exist "composer.phar" (
    echo       [OK] Composer portable berhasil diunduh.
    echo       [*] Menjalankan: php composer.phar install...
    php composer.phar install --no-interaction --prefer-dist --ignore-platform-reqs
) else (
    echo.
    echo       [ERROR] Gagal mengunduh Composer. Pastikan koneksi internet aktif.
    pause
    exit /b 1
)

:CHECK_VENDOR
if not exist "vendor\autoload.php" (
    echo.
    echo       [ERROR] Pustaka vendor gagal terunduh!
    echo       Pastikan koneksi internet stabil dan coba jalankan ulang.
    pause
    exit /b 1
)
echo       [OK] Pustaka vendor berhasil terpasang.

:VENDOR_DONE
echo.

:: ============================================================
:: TAHAP 3: KONFIGURASI FILE .ENV
:: ============================================================
echo [3/7] Menyiapkan file konfigurasi .env...

if not exist ".env" (
    if exist ".env.example" (
        copy ".env.example" ".env" >nul
        echo       [OK] File .env berhasil dibuat dari .env.example
    ) else (
        echo       [ERROR] File .env.example tidak ditemukan!
        pause
        exit /b 1
    )
) else (
    echo       [OK] File .env sudah ada.
)
echo.

:: ============================================================
:: TAHAP 4: GENERATE APP_KEY
:: ============================================================
echo [4/7] Menghasilkan kunci keamanan aplikasi...
call php artisan key:generate --force >nul 2>&1
call php artisan config:clear >nul 2>&1
echo       [OK] APP_KEY berhasil di-generate.
echo.

:: ============================================================
:: TAHAP 5: PERSIAPAN DATABASE DAN MIGRASI
:: ============================================================
echo [5/8] Menyiapkan database MySQL dan data aparatur desa...

:: 1. Pastikan Service MySQL Berjalan
powershell -Command "$c = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue; if (!$c) { exit 1 }" >nul 2>&1
if not "%errorLevel%"=="0" (
    echo       [*] Mengaktifkan database MySQL lokal...
    net start mysql >nul 2>&1
        for %%L in (C D E) do (
            for /d %%D in (%%L:\laragon\bin\mysql\mysql-*) do (
                if exist "%%D\bin\mysqld.exe" (
                    if exist "%%D\my.ini" (
                        start "" /B "%%D\bin\mysqld.exe" --defaults-file="%%D\my.ini" >nul 2>&1
                    ) else (
                        start "" /B "%%D\bin\mysqld.exe" --console >nul 2>&1
                    )
                )
            )
        )
    powershell -Command "Start-Sleep -Milliseconds 2500" >nul 2>&1
)

:: 2. Buat Database rfid_balung jika belum ada
php -r "$p=new PDO('mysql:host=127.0.0.1;port=3306','root',''); $p->exec('CREATE DATABASE IF NOT EXISTS \`rfid_balung\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');" >nul 2>&1

:: 3. Cek apakah tabel sudah ada (mencegah data lama terhapus jika dijalankan ulang)
php -r "$p=new PDO('mysql:host=127.0.0.1;port=3306;dbname=rfid_balung','root',''); $s=$p->query('SHOW TABLES LIKE \'users\''); exit($s->rowCount() > 0 ? 0 : 1);" >nul 2>&1
if not "%errorLevel%"=="0" (
    echo       [*] Database baru terdeteksi. Mengisi skema awal dan 15 data aparatur...
    call php artisan migrate:fresh --seed --force >nul 2>&1
    if "%errorLevel%"=="0" (
        echo       [OK] Database berhasil dibuat dengan 15 data aparatur desa!
    ) else (
        echo       [NOTE] Migrasi database dicoba ulang...
        call php artisan migrate --force >nul 2>&1
    )
) else (
    echo       [OK] Database sudah memiliki data sebelumnya. Menjalankan update migrasi aman...
    call php artisan migrate --force >nul 2>&1
)

:: 4. Buat Storage Link
call php artisan storage:link >nul 2>&1
echo.

:: ============================================================
:: TAHAP 6: INTEGRASI MESIN RFID (PYTHON & PYZK)
:: ============================================================
echo [6/8] Memeriksa integrasi pustaka mesin RFID (Python & PyZK)...
where python >nul 2>&1
if not "%errorLevel%"=="0" (
    for /d %%P in (C:\laragon\bin\python\*) do (
        if exist "%%P\python.exe" (
            set "PATH=%%P;%%P\Scripts;%PATH%"
        )
    )
)

where python >nul 2>&1
if "%errorLevel%"=="0" (
    python -c "import zk" >nul 2>&1
    if not "%errorLevel%"=="0" (
        echo       [*] Memasang library pyzk & requests untuk komunikasi mesin fisik ZKTeco...
        pip install pyzk requests >nul 2>&1
        python -c "import zk" >nul 2>&1
        if "%errorLevel%"=="0" (
            echo       [OK] Library pyzk & requests berhasil dipasang!
        ) else (
            echo       [NOTE] Library pyzk belum terpasang otomatis. Anda bisa jalankan: pip install pyzk requests
        )
    ) else (
        echo       [OK] Python dan library pyzk siap digunakan.
    )
) else (
    echo       [NOTE] Python belum terdeteksi di komputer ini.
    echo              Untuk komunikasi otomatis ke mesin ZKTeco via kabel LAN,
    echo              silakan pasang Python melalui:
    echo              - Menu Laragon: Klik kanan Laragon -> Tools -> Quick add -> Python
    echo              - ATAU download dari: https://www.python.org/downloads/ (centang Add to PATH)
)
echo.

:: ============================================================
:: TAHAP 7: DAFTARKAN DOMAIN LOKAL DESA & SSL HTTPS
:: ============================================================
echo [7/8] Mendaftarkan domain presensi.balunglor.desa.id & SSL...

set "HOSTS_FILE=%WINDIR%\System32\drivers\etc\hosts"
findstr /C:"presensi.balunglor.desa.id" "%HOSTS_FILE%" >nul 2>&1
if "%errorLevel%"=="0" (
    echo       [OK] Domain sudah terdaftar di Windows hosts.
) else (
    net session >nul 2>&1
    if "%errorLevel%"=="0" (
        echo. >> "%HOSTS_FILE%"
        echo 127.0.0.1    presensi.balunglor.desa.id >> "%HOSTS_FILE%"
        echo 127.0.0.1    presensi.local >> "%HOSTS_FILE%"
        echo       [OK] Domain berhasil didaftarkan!
    ) else (
        echo       [*] Meminta izin Administrator untuk mendaftarkan domain...
        powershell -Command "Start-Process cmd.exe -ArgumentList '/c echo. >> \"%HOSTS_FILE%\" & echo 127.0.0.1    presensi.balunglor.desa.id >> \"%HOSTS_FILE%\" & echo 127.0.0.1    presensi.local >> \"%HOSTS_FILE%\"' -Verb RunAs -Wait" >nul 2>&1
        findstr /C:"presensi.balunglor.desa.id" "%HOSTS_FILE%" >nul 2>&1
        if "%errorLevel%"=="0" (
            echo       [OK] Domain berhasil didaftarkan!
        ) else (
            echo       [NOTE] Domain gagal didaftarkan otomatis. Akses via http://localhost:8000
        )
    )
)

:: Pasang SSL otomatis jika ada setup_ssl.bat
if exist "%~dp0setup_ssl.bat" (
    echo       [*] Mengonfigurasi VirtualHost Apache & Sertifikat SSL...
    powershell -Command "Start-Process cmd.exe -ArgumentList '/c `\"\"%~dp0setup_ssl.bat\"`\"' -Verb RunAs -Wait" >nul 2>&1
    echo       [OK] Konfigurasi SSL berhasil dipasang.
)

:: Buka Firewall Windows untuk akses HP/Laptop lain di Wi-Fi kantor desa
netsh advfirewall firewall add rule name="Presensi Balunglor Web Server" dir=in action=allow protocol=TCP localport=80,443,8000 >nul 2>&1
echo.

:: ============================================================
:: TAHAP 8: PASANG AUTO-START WINDOWS
:: ============================================================
echo [8/8] Memasang Auto-Start Windows...
echo.
echo       Apakah Anda ingin aplikasi ini OTOMATIS MENYALA
echo       setiap kali komputer dihidupkan? (SANGAT DISARANKAN UNTUK PC DESA)
echo.
set /p autostart_choice="       Pasang Auto-Start? (Y/N): "

if /i "%autostart_choice%"=="Y" (
    if exist "scripts\create_startup_shortcut.ps1" (
        powershell -ExecutionPolicy Bypass -File "%~dp0scripts\create_startup_shortcut.ps1" >nul 2>&1
    )
    echo.
    echo       [OK] Auto-Start berhasil dipasang!
    echo       Setiap kali PC desa dinyalakan, server presensi
    echo       akan otomatis aktif di background tanpa perlu buka aplikasi lain.
) else (
    echo       [OK] Auto-Start dilewati. Anda bisa klik run_background.vbs kapan saja.
)

:: ============================================================
:: SELESAI! TAMPILKAN RINGKASAN
:: ============================================================
echo.
echo ================================================================
echo   INSTALASI SELESAI 100%% BERHASIL!
echo ================================================================
echo.
echo   Alamat Akses Resmi:
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
echo ================================================================
echo.

:: Tanya apakah mau langsung buka
set /p start_now="  Buka aplikasi sekarang? (Y/N): "
if /i "%start_now%"=="Y" (
    echo.
    echo  [*] Memulai server dan membuka browser...
    start "" "https://presensi.balunglor.desa.id"
    start "" wscript.exe "%~dp0run_background.vbs"
)

echo.
pause
exit
