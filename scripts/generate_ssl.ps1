# Script Pemasangan Sertifikat SSL Otomatis untuk Presensi Desa Balunglor
$ErrorActionPreference = "Stop"

$laragonSslDir = "C:\laragon\etc\ssl"
$apacheBinDir = "C:\laragon\bin\apache\httpd-2.4.54-win64-VS16\bin"
$opensslExe = Join-Path $apacheBinDir "openssl.exe"

if (-not (Test-Path $opensslExe)) {
    Write-Host "[ERROR] OpenSSL tidak ditemukan di $opensslExe!" -ForegroundColor Red
    exit 1
}

if (-not (Test-Path $laragonSslDir)) {
    New-Item -ItemType Directory -Path $laragonSslDir -Force | Out-Null
}

$keyPath = Join-Path $laragonSslDir "laragon.key"
$crtPath = Join-Path $laragonSslDir "laragon.crt"
$cnfPath = Join-Path $laragonSslDir "openssl_san.cnf"

Write-Host "[*] Menyiapkan konfigurasi OpenSSL dengan SAN (Subject Alternative Names)..." -ForegroundColor Cyan

$cnfContent = @"
[req]
default_bits = 2048
prompt = no
default_md = sha256
distinguished_name = dn
x509_extensions = v3_req

[dn]
C = ID
ST = Jawa Timur
L = Jember
O = Pemerintah Desa Balunglor
OU = Pelayanan & Presensi Aparatur Desa
CN = presensi.balunglor.desa.id

[v3_req]
subjectAltName = @alt_names
basicConstraints = CA:TRUE
keyUsage = digitalSignature, keyEncipherment, keyCertSign

[alt_names]
DNS.1 = presensi.balunglor.desa.id
DNS.2 = *.balunglor.desa.id
DNS.3 = presensi.local
DNS.4 = localhost
IP.1 = 127.0.0.1
"@

Set-Content -Path $cnfPath -Value $cnfContent -Encoding ASCII

Write-Host "[*] Membuat Private Key & Sertifikat SSL X.509 (Masa aktif: 100 Tahun)..." -ForegroundColor Cyan

& $opensslExe req -x509 -new -nodes -keyout $keyPath -out $crtPath -days 36500 -config $cnfPath

if (-not (Test-Path $crtPath) -or -not (Test-Path $keyPath)) {
    Write-Host "[ERROR] Gagal membuat file sertifikat SSL!" -ForegroundColor Red
    exit 1
}

Write-Host "[OK] Sertifikat SSL berhasil dibuat:" -ForegroundColor Green
Write-Host "     - $crtPath"
Write-Host "     - $keyPath"

Write-Host "[*] Mendaftarkan sertifikat ke Windows Trusted Root Certification Authorities..." -ForegroundColor Cyan
& certutil -addstore -f "ROOT" $crtPath

Write-Host "[OK] SUKSES! Sertifikat SSL kini dipercaya oleh Windows, Google Chrome, dan Edge!" -ForegroundColor Green
