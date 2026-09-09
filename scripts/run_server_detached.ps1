# Matikan proses httpd lama jika ada
Stop-Process -Name httpd -Force -ErrorAction SilentlyContinue
Stop-Process -Name php -Force -ErrorAction SilentlyContinue

# 1. Pastikan MySQL Aktif
$mysqlConn = Test-NetConnection -ComputerName 127.0.0.1 -Port 3306 -InformationLevel Quiet -WarningAction SilentlyContinue
if (-not $mysqlConn) {
    Start-Service mysql -ErrorAction SilentlyContinue
    $mysqld = Get-ChildItem -Path "C:\laragon\bin\mysql\mysql-*\bin\mysqld.exe" -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($mysqld) {
        Start-Process -FilePath $mysqld.FullName -ArgumentList "--console" -WindowStyle Hidden
        Start-Sleep -Seconds 2
    }
}

# 2. Sinkronkan nghttp2.dll
$apacheDir = Get-ChildItem -Path "C:\laragon\bin\apache\httpd-*" -Directory -ErrorAction SilentlyContinue | Select-Object -First 1
$phpDir = Get-ChildItem -Path "C:\laragon\bin\php\php-*" -Directory -ErrorAction SilentlyContinue | Select-Object -First 1
if ($apacheDir -and $phpDir) {
    $srcDll = Join-Path $phpDir.FullName "nghttp2.dll"
    $dstDll = Join-Path $apacheDir.FullName "bin\nghttp2.dll"
    if (Test-Path $srcDll) {
        Copy-Item -Path $srcDll -Destination $dstDll -Force -ErrorAction SilentlyContinue
    }
}

# 3. Jalankan Apache di background secara independen (detached)
$apacheExe = Join-Path $apacheDir.FullName "bin\httpd.exe"
Start-Process -FilePath $apacheExe -ArgumentList "-d `"$($apacheDir.FullName)`"" -WindowStyle Hidden

Start-Sleep -Seconds 2

# Verifikasi koneksi
$httpd = Get-Process httpd -ErrorAction SilentlyContinue
if ($httpd) {
    Write-Host "[OK] Server Apache BERHASIL BERJALAN di background (PID: $($httpd.Id -join ', '))"
} else {
    Write-Host "[ERROR] Server Apache gagal berjalan."
}
