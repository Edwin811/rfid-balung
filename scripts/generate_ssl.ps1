# Script Pemasangan Sertifikat SSL Otomatis untuk Presensi Desa Balunglor
# Mendukung Laragon di drive manapun (C, D, E, F)

param(
    [string]$LaragonDir = ""
)

$ErrorActionPreference = "Stop"

# 1. Deteksi Laragon secara dinamis jika tidak diberikan parameter
if (-not $LaragonDir -or -not (Test-Path $LaragonDir)) {
    $LaragonDir = ""
    foreach ($drive in @("C", "D", "E", "F")) {
        $candidate = "${drive}:\laragon"
        if (Test-Path $candidate) {
            $LaragonDir = $candidate
            Write-Host "[OK] Laragon ditemukan di: $LaragonDir" -ForegroundColor Green
            break
        }
    }
    if (-not $LaragonDir) {
        Write-Host "[ERROR] Laragon tidak ditemukan di Drive C, D, E, atau F!" -ForegroundColor Red
        exit 1
    }
}

$laragonSslDir = Join-Path $LaragonDir "etc\ssl"

# 2. Cari OpenSSL: Apache Laragon -> Git -> System PATH
$opensslExe = ""

# Cari di Apache Laragon
$apacheDir = Get-ChildItem -Path (Join-Path $LaragonDir "bin\apache\httpd-*") -Directory -ErrorAction SilentlyContinue | Select-Object -First 1
if ($apacheDir) {
    $candidate = Join-Path $apacheDir.FullName "bin\openssl.exe"
    if (Test-Path $candidate) {
        $opensslExe = $candidate
        Write-Host "[OK] OpenSSL ditemukan di Apache: $opensslExe" -ForegroundColor Green
    }
}

# Fallback: Git OpenSSL
if (-not $opensslExe) {
    $gitOpenssl = "C:\Program Files\Git\usr\bin\openssl.exe"
    if (Test-Path $gitOpenssl) {
        $opensslExe = $gitOpenssl
        Write-Host "[OK] OpenSSL ditemukan di Git: $opensslExe" -ForegroundColor Green
    }
}

# Fallback: System PATH
if (-not $opensslExe) {
    $pathOpenssl = Get-Command openssl -ErrorAction SilentlyContinue
    if ($pathOpenssl) {
        $opensslExe = $pathOpenssl.Source
        Write-Host "[OK] OpenSSL ditemukan di PATH: $opensslExe" -ForegroundColor Green
    }
}

if (-not $opensslExe) {
    Write-Host "[ERROR] OpenSSL tidak ditemukan!" -ForegroundColor Red
    Write-Host "        Pastikan Apache Laragon terinstall atau Git for Windows tersedia." -ForegroundColor Yellow
    exit 1
}

# 3. Buat direktori SSL jika belum ada
if (-not (Test-Path $laragonSslDir)) {
    New-Item -ItemType Directory -Path $laragonSslDir -Force | Out-Null
    Write-Host "[OK] Direktori SSL dibuat: $laragonSslDir" -ForegroundColor Green
}

$caKey = Join-Path $laragonSslDir "ca.key"
$caCrt = Join-Path $laragonSslDir "ca.crt"
$caCnf = Join-Path $laragonSslDir "ca.cnf"

$serverKey = Join-Path $laragonSslDir "laragon.key"
$serverCsr = Join-Path $laragonSslDir "laragon.csr"
$serverCrt = Join-Path $laragonSslDir "laragon.crt"
$serverCnf = Join-Path $laragonSslDir "server.cnf"

# 4. Konfigurasi dan Pembuatan Root CA (Masa Aktif 10 Tahun)
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

# Set OPENSSL_CONF agar Apache OpenSSL tidak error mencari config default
$env:OPENSSL_CONF = $caCnf
$sslErrorLog = Join-Path $laragonSslDir "ssl_error.log"

# Generate Root CA key + cert
$sslOutput = & $opensslExe req -x509 -new -nodes -newkey rsa:2048 -keyout $caKey -out $caCrt -days 3650 -config $caCnf 2>&1
$sslOutput | Out-File -FilePath $sslErrorLog -Encoding ASCII

if (-not (Test-Path $caCrt) -or (Get-Item $caCrt).Length -eq 0) {
    Write-Host "[ERROR] Gagal membuat Root CA!" -ForegroundColor Red
    Write-Host "        Detail error:" -ForegroundColor Yellow
    if (Test-Path $sslErrorLog) {
        Get-Content $sslErrorLog | ForEach-Object { Write-Host "        $_" -ForegroundColor Yellow }
    }
    Write-Host "        OpenSSL: $opensslExe" -ForegroundColor Yellow
    Write-Host "        Config : $caCnf" -ForegroundColor Yellow
    exit 1
}
Write-Host "[OK] Root CA berhasil dibuat." -ForegroundColor Green

# 5. Konfigurasi Server Certificate (Masa Aktif 365 Hari - Standar Chrome/Chromium)
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

# Set OPENSSL_CONF ke server.cnf
$env:OPENSSL_CONF = $serverCnf

# Generate Server Private Key & CSR
$sslOutput = & $opensslExe req -new -nodes -newkey rsa:2048 -keyout $serverKey -out $serverCsr -config $serverCnf 2>&1
$sslOutput | Out-File -FilePath $sslErrorLog -Encoding ASCII -Append

# Sign Server Certificate menggunakan Root CA (365 hari, standar Chromium)
$sslOutput = & $opensslExe x509 -req -in $serverCsr -CA $caCrt -CAkey $caKey -CAcreateserial -out $serverCrt -days 365 -extfile $serverCnf -extensions server_ext -sha256 2>&1
$sslOutput | Out-File -FilePath $sslErrorLog -Encoding ASCII -Append

if (-not (Test-Path $serverCrt) -or -not (Test-Path $serverKey)) {
    Write-Host "[ERROR] Gagal membuat file sertifikat SSL server!" -ForegroundColor Red
    Write-Host "        Detail error:" -ForegroundColor Yellow
    if (Test-Path $sslErrorLog) {
        Get-Content $sslErrorLog | ForEach-Object { Write-Host "        $_" -ForegroundColor Yellow }
    }
    Write-Host "        File yang diharapkan:" -ForegroundColor Yellow
    Write-Host "        - $serverCrt" -ForegroundColor Yellow
    Write-Host "        - $serverKey" -ForegroundColor Yellow
    exit 1
}

Write-Host "[OK] Sertifikat SSL server berhasil dibuat:" -ForegroundColor Green
Write-Host "     - $serverCrt"
Write-Host "     - $serverKey"

# 6. Daftarkan Root CA ke Windows Trusted Root Certification Authorities
Write-Host "[*] Mendaftarkan Balunglor Local Root CA ke Windows Trusted Root Store..." -ForegroundColor Cyan
& certutil -addstore -f "ROOT" $caCrt

if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] SUKSES! Sertifikat SSL kini dipercaya oleh Windows, Google Chrome, dan Edge!" -ForegroundColor Green
} else {
    Write-Host "[WARNING] Pendaftaran ke Trust Store mungkin gagal." -ForegroundColor Yellow
    Write-Host "          Browser mungkin menampilkan peringatan 'Not Secure'." -ForegroundColor Yellow
    Write-Host "          Coba jalankan script ini dengan Run as Administrator." -ForegroundColor Yellow
}
