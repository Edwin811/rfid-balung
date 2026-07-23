<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Presensi Aparatur Desa Balung Lor - {{ $namaBulan }} {{ $tahun }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 20px; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 20px; position: relative; }
        .header h3 { margin: 0; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
        .header h2 { margin: 2px 0; font-size: 16px; text-transform: uppercase; font-weight: bold; }
        .header p { margin: 0; font-size: 10px; color: #475569; }
        .title-box { text-align: center; margin-bottom: 15px; }
        .title-box h4 { margin: 0; font-size: 13px; text-transform: uppercase; text-decoration: underline; }
        .title-box p { margin: 2px 0; font-size: 11px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        table th, table td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        table th { background-color: #f1f5f9; font-size: 10px; text-transform: uppercase; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
        .footer-sig { width: 100%; margin-top: 30px; }
        .sig-box { float: right; width: 250px; text-align: center; }
        .sig-space { height: 60px; }
    </style>
</head>
<body>

    <!-- Header Kop Surat Desa -->
    <div class="header">
        <h3>Pemerintah Kabupaten Jember</h3>
        <h3>Kecamatan Balung</h3>
        <h2>Pemerintah Desa Balung Lor</h2>
        <p>Jl. Raya Balung Lor No. 01 Balung Lor, Kec. Balung, Kab. Jember — Kode Pos 68161</p>
    </div>

    <!-- Title -->
    <div class="title-box">
        <h4>Laporan Rekapitulasi Presensi Aparatur Desa</h4>
        <p>Periode: <strong>{{ $namaBulan }} {{ $tahun }}</strong></p>
    </div>

    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th width="70">ID Mesin</th>
                <th>Nama Aparatur Desa</th>
                <th>Jabatan</th>
                <th width="45">Hadir</th>
                <th width="50">Terlambat</th>
                <th width="45">Sakit</th>
                <th width="45">Izin</th>
                <th width="45">Dispen</th>
                <th width="60">Tidak Hadir</th>
                <th width="65">Total Kerja</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekapPegawai as $index => $r)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $r['pegawai']->id_mesin }}</td>
                <td><strong>{{ $r['pegawai']->nama_lengkap }}</strong></td>
                <td>{{ $r['pegawai']->jabatan }}</td>
                <td class="text-center">{{ $r['hadir'] }}</td>
                <td class="text-center">{{ $r['terlambat'] }}</td>
                <td class="text-center">{{ $r['sakit'] }}</td>
                <td class="text-center">{{ $r['izin'] }}</td>
                <td class="text-center">{{ $r['dispen'] }}</td>
                <td class="text-center">{{ $r['tidak_hadir'] }}</td>
                <td class="text-center">{{ $r['total_kerja'] }} Hari</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Signature Section -->
    <div class="footer-sig">
        <div class="sig-box">
            <p>Balung Lor, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY') }}</p>
            <p><strong>Kepala Desa Balung Lor</strong></p>
            <div class="sig-space"></div>
            <p><u><strong>IMAM MUSTOFA</strong></u></p>
            <p>Kepala Desa Balunglor</p>
        </div>
    </div>

</body>
</html>
