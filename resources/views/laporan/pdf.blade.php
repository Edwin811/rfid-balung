<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Presensi Aparatur Desa Balunglor</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 15px; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 10px; margin-bottom: 15px; position: relative; }
        .header h3 { margin: 0; font-size: 13px; text-transform: uppercase; letter-spacing: 1px; }
        .header h2 { margin: 2px 0; font-size: 16px; text-transform: uppercase; font-weight: bold; }
        .header p { margin: 0; font-size: 10px; color: #475569; }
        .title-box { text-align: center; margin-bottom: 15px; }
        .title-box h4 { margin: 0; font-size: 13px; text-transform: uppercase; text-decoration: underline; }
        .title-box p { margin: 2px 0; font-size: 10px; color: #64748b; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        table th, table td { border: 1px solid #cbd5e1; padding: 5px 7px; text-align: left; font-size: 10px; }
        table th { background-color: #9f1239; color: #ffffff; font-size: 10px; text-transform: uppercase; font-weight: bold; text-align: center; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .footer-sig { width: 100%; margin-top: 25px; }
        .sig-box { float: right; width: 230px; text-align: center; }
        .sig-space { height: 50px; }
    </style>
</head>
<body>

    <!-- Header Kop Surat Desa -->
    <div class="header">
        <h3>Pemerintah Kabupaten Jember</h3>
        <h3>Kecamatan Balung</h3>
        <h2>Pemerintah Desa Balunglor</h2>
        <p>Jl. Raya Balung Lor No. 01 Balung Lor, Kec. Balung, Kab. Jember — Kode Pos 68161</p>
    </div>

    <!-- Title & Period -->
    <div class="title-box">
        <h4>Laporan Presensi Aparatur Desa ({{ strtoupper($jenisLaporan) }})</h4>
        @if($jenisLaporan === 'bulanan')
        <p>Periode: <strong>{{ $namaBulan }} {{ $tahun }}</strong></p>
        @else
        <p>Periode Tanggal: <strong>{{ \Carbon\Carbon::parse($tanggalMulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tanggalSelesai)->format('d/m/Y') }}</strong></p>
        @endif
        @if($selectedPegawai)
        <p>Aparatur Desa: <strong>{{ $selectedPegawai->nama_lengkap }} ({{ $selectedPegawai->jabatan }})</strong></p>
        @endif
    </div>

    @if($jenisLaporan === 'bulanan')
    <!-- Mode Bulanan Table -->
    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th width="65">ID Mesin</th>
                <th>Nama Aparatur Desa</th>
                <th>Jabatan</th>
                <th width="45">Hadir</th>
                <th width="50">Terlambat</th>
                <th width="40">Sakit</th>
                <th width="40">Izin</th>
                <th width="45">Dispen</th>
                <th width="60">Tidak Hadir</th>
                <th width="65">Total Kerja</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataLaporan['items'] as $index => $r)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ $r['pegawai']->id_mesin }}</td>
                <td><strong>{{ $r['pegawai']->nama_lengkap }}</strong></td>
                <td>{{ $r['pegawai']->jabatan }}</td>
                <td class="text-center font-bold">{{ $r['hadir'] }}</td>
                <td class="text-center font-bold">{{ $r['terlambat'] }}</td>
                <td class="text-center font-bold">{{ $r['sakit'] }}</td>
                <td class="text-center font-bold">{{ $r['izin'] }}</td>
                <td class="text-center font-bold">{{ $r['dispen'] }}</td>
                <td class="text-center font-bold">{{ $r['tidak_hadir'] }}</td>
                <td class="text-center font-bold">{{ $r['total_kerja'] }} Hari</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <!-- Mode Harian / Rentang / Individu Table -->
    <table>
        <thead>
            <tr>
                <th width="30">No</th>
                <th width="75">Tanggal</th>
                <th>Nama Aparatur Desa</th>
                <th>Jabatan</th>
                <th width="65">Jam Masuk</th>
                <th width="65">Jam Pulang</th>
                <th width="70">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataLaporan['items'] as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                <td><strong>{{ $row->pegawai->nama_lengkap ?? '-' }}</strong></td>
                <td>{{ $row->pegawai->jabatan ?? '-' }}</td>
                <td class="text-center font-bold">{{ $row->jam_masuk ?? '-' }}</td>
                <td class="text-center font-bold">{{ $row->jam_pulang ?? '-' }}</td>
                <td class="text-center font-bold">{{ strtoupper($row->status) }}</td>
                <td>{{ $row->keterangan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center">Tidak ada record presensi ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endif

    <!-- Signature Section -->
    <div class="footer-sig">
        <div class="sig-box">
            <p>Balung Lor, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY') }}</p>
            <p><strong>Kepala Desa Balunglor</strong></p>
            <div class="sig-space"></div>
            <p><u><strong>IMAM MUSTOFA</strong></u></p>
            <p>Kepala Desa Balunglor</p>
        </div>
    </div>

</body>
</html>
