# Script Pemasangan Sertifikat SSL Otomatis untuk Presensi Desa Balunglor
$ErrorActionPreference = "Stop"

$laragonSslDir = "C:\laragon\etc\ssl"

# Cari Apache bin secara dinamis di C:\laragon\bin\apache
$apacheDir = Get-ChildItem -Path "C:\laragon\bin\apache\httpd-*" -Directory -ErrorAction SilentlyContinue | Select-Object -First 1
if ($apacheDir) {
    $apacheBinDir = Join-Path $apacheDir.FullName "bin"
} else {
    $apacheBinDir = "C:\laragon\bin\apache\httpd-2.4.54-win64-VS16\bin"
}
$opensslExe = Join-Path $apacheBinDir "openssl.exe"

if (-not (Test-Path $opensslExe)) {
    $gitOpenssl = "C:\Program Files\Git\usr\bin\openssl.exe"
    if (Test-Path $gitOpenssl) {
        $opensslExe = $gitOpenssl
    } else {
        Write-Host "[ERROR] OpenSSL tidak ditemukan di $opensslExe!" -ForegroundColor Red
        exit 1
    }
}

if (-not (Test-Path $laragonSslDir)) {
    New-Item -ItemType Directory -Path $laragonSslDir -Force | Out-Null
}

$caKey = Join-Path $laragonSslDir "ca.key"
$caCrt = Join-Path $laragonSslDir "ca.crt"
$caCnf = Join-Path $laragonSslDir "ca.cnf"

$serverKey = Join-Path $laragonSslDir "laragon.key"
$serverCsr = Join-Path $laragonSslDir "laragon.csr"
$serverCrt = Join-Path $laragonSslDir "laragon.crt"
$serverCnf = Join-Path $laragonSslDir "server.cnf"

# 1. Konfigurasi dan Pembuatan Root CA (Masa Aktif 10 Tahun)
Write-Host "[*] Menyiapkan Root Certificate Authority (CA) Lokal Desa Balunglor..." -ForegroundColor Cyan

$caCnfContent = @"
[req]
default_bits = 2048
prompt = no
default_md = sha256
distinguished_name = ca_dn
x509_extensions = ca_ext

[ca_dn]
C = ID
ST = Jawa Timur
L = Jember
O = Pemerintah Desa Balunglor
OU = Otoritas Sertifikat Presensi
CN = Balunglor Local Root CA

[ca_ext]
basicConstraints = critical, CA:TRUE
keyUsage = critical, keyCertSign, cRLSign
subjectKeyIdentifier = hash
"@

Set-Content -Path $caCnf -Value $caCnfContent -Encoding ASCII
& $opensslExe req -x509 -new -nodes -keyout $caKey -out $caCrt -days 3650 -config $caCnf

# 2. Konfigurasi Server Certificate (Masa Aktif 365 Hari - Wajib Standar Chrome/Chromium)
Write-Host "[*] Menyiapkan Sertifikat SSL Server dengan EKU serverAuth & SAN..." -ForegroundColor Cyan

$serverCnfContent = @"
[req]
default_bits = 2048
prompt = no
default_md = sha256
distinguished_name = server_dn
req_extensions = req_ext

[server_dn]
C = ID
ST = Jawa Timur
L = Jember
O = Pemerintah Desa Balunglor
OU = Pelayanan & Presensi Aparatur Desa
CN = presensi.balunglor.desa.id

[req_ext]
subjectAltName = @alt_names

[server_ext]
basicConstraints = CA:FALSE
keyUsage = critical, digitalSignature, keyEncipherment
extendedKeyUsage = serverAuth
subjectAltName = @alt_names
subjectKeyIdentifier = hash
authorityKeyIdentifier = keyid,issuer

[alt_names]
DNS.1 = presensi.balunglor.desa.id
DNS.2 = *.balunglor.desa.id
DNS.3 = presensi.local
DNS.4 = localhost
IP.1 = 127.0.0.1
"@

Set-Content -Path $serverCnf -Value $serverCnfContent -Encoding ASCII

# Generate Server Private Key & CSR
& $opensslExe req -new -nodes -keyout $serverKey -out $serverCsr -config $serverCnf

# Sign Server Certificate menggunakan Root CA (Masa aktif 365 hari, memenuhi standar Chromium)
& $opensslExe x509 -req -in $serverCsr -CA $caCrt -CAkey $caKey -CAcreateserial -out $serverCrt -days 365 -extfile $serverCnf -extensions server_ext -sha256

if (-not (Test-Path $serverCrt) -or -not (Test-Path $serverKey)) {
    Write-Host "[ERROR] Gagal membuat file sertifikat SSL server!" -ForegroundColor Red
    exit 1
}

Write-Host "[OK] Sertifikat SSL server berhasil dibuat:" -ForegroundColor Green
Write-Host "     - $serverCrt"
Write-Host "     - $serverKey"

# 3. Daftarkan Root CA ke Windows Trusted Root Certification Authorities
Write-Host "[*] Mendaftarkan Balunglor Local Root CA ke Windows Trusted Root Store..." -ForegroundColor Cyan
& certutil -addstore -f "ROOT" $caCrt

Write-Host "[OK] SUKSES! Sertifikat SSL kini dipercaya oleh Windows, Google Chrome, dan Edge!" -ForegroundColor Green
