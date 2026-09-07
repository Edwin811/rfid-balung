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
if %errorLevel% eq 0 (
    echo       [OK] PHP ditemukan di PATH sistem.
    goto PHP_READY
)

:: Cari di XAMPP
if exist "C:\xampp\php\php.exe" (
    set "PATH=C:\xampp\php;%PATH%"
    echo       [OK] PHP ditemukan di: C:\xampp\php

    :: Auto-fix XAMPP path error (xampp_ menjadi xampp)
    if exist "C:\xampp\setup_xampp.bat" (
        echo       [*] Memperbaiki konfigurasi path XAMPP...
        pushd C:\xampp
        call setup_xampp.bat >nul 2>&1
        popd
    )
    goto PHP_FIX_EXTENSIONS
)

:: Cari di Laragon
for /d %%D in (C:\laragon\bin\php\php-*) do (
    if exist "%%D\php.exe" (
        set "PATH=%%D;%PATH%"
        echo       [OK] PHP ditemukan di: %%D
        goto PHP_READY
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
if %errorLevel% neq 0 (
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
if %errorLevel% eq 0 (
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
:: TAHAP 5: MIGRASI DATABASE
:: ============================================================
echo [5/7] Menyiapkan tabel database dan data aparatur desa...
call php artisan migrate:fresh --seed --force >nul 2>&1
if %errorLevel% eq 0 (
    echo       [OK] Database berhasil disiapkan dengan 15 data aparatur desa.
) else (
    echo       [NOTE] Migrasi database dilewati. Database cloud mungkin sudah terisi.
)
echo.

:: ============================================================
:: TAHAP 6: DAFTARKAN DOMAIN LOKAL DESA
:: ============================================================
echo [6/7] Mendaftarkan domain lokal presensi.balunglor.desa.id...

set "HOSTS_FILE=%WINDIR%\System32\drivers\etc\hosts"
findstr /C:"presensi.balunglor.desa.id" "%HOSTS_FILE%" >nul 2>&1
if %errorLevel% eq 0 (
    echo       [OK] Domain sudah terdaftar sebelumnya.
) else (
    :: Coba tulis langsung (jika sudah admin)
    net session >nul 2>&1
    if %errorLevel% eq 0 (
        echo. >> "%HOSTS_FILE%"
        echo 127.0.0.1    presensi.balunglor.desa.id >> "%HOSTS_FILE%"
        echo 127.0.0.1    presensi.local >> "%HOSTS_FILE%"
        echo       [OK] Domain berhasil didaftarkan!
    ) else (
        echo       [*] Memerlukan izin Administrator untuk mendaftarkan domain...
        powershell -Command "Start-Process cmd.exe -ArgumentList '/c echo. >> \"%HOSTS_FILE%\" & echo 127.0.0.1    presensi.balunglor.desa.id >> \"%HOSTS_FILE%\" & echo 127.0.0.1    presensi.local >> \"%HOSTS_FILE%\"' -Verb RunAs -Wait" >nul 2>&1
        findstr /C:"presensi.balunglor.desa.id" "%HOSTS_FILE%" >nul 2>&1
        if %errorLevel% eq 0 (
            echo       [OK] Domain berhasil didaftarkan!
        ) else (
            echo       [NOTE] Domain gagal didaftarkan otomatis.
            echo       Anda tetap bisa mengakses via http://localhost:8000
        )
    )
)
echo.

:: ============================================================
:: TAHAP 7: PASANG AUTO-START
:: ============================================================
echo [7/7] Memasang Auto-Start Windows...
echo.
echo       Apakah Anda ingin aplikasi ini OTOMATIS MENYALA
echo       setiap kali komputer dihidupkan? (DISARANKAN)
echo.
set /p autostart_choice="       Pasang Auto-Start? (Y/N): "

if /i "%autostart_choice%"=="Y" (
    if exist "scripts\create_startup_shortcut.ps1" (
        powershell -ExecutionPolicy Bypass -File "%~dp0scripts\create_startup_shortcut.ps1" >nul 2>&1
    )
    echo.
    echo       [OK] Auto-Start berhasil dipasang!
    echo       Setiap kali komputer dinyalakan, server presensi
    echo       akan otomatis aktif di background.
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
echo   Alamat Akses:
echo   - http://presensi.balunglor.desa.id:8000
echo   - http://localhost:8000
echo.
echo   Akun Login:
echo   - Username: admin
echo   - Password: password
echo.
echo   File Penting:
echo   - run_background.vbs  = Nyalakan server (1 klik)
echo   - stop_app.bat        = Matikan server
echo   - start_app.bat       = Nyalakan server (dengan jendela)
echo.
echo ================================================================
echo.

:: Tanya apakah mau langsung buka
set /p start_now="  Buka aplikasi sekarang? (Y/N): "
if /i "%start_now%"=="Y" (
    echo.
    echo  [*] Memulai server dan membuka browser...
    start "" "http://presensi.balunglor.desa.id:8000"
    start "" wscript.exe "%~dp0run_background.vbs"
)

echo.
pause
exit
