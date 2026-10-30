<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Presensi Bulanan - Pemkab Jember (Desa Balunglor)</title>
    <!-- Favicon Image logo.png -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">
    <style>
        @page { size: 216mm 330mm; margin: 10mm 12mm 16mm 12mm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #000; margin: 0; padding: 0; background: #fff; }
        
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border-bottom: 3px double #000; }
        .header-table td { border: none !important; }
        
        .meta-info { width: 100%; margin-bottom: 10px; font-size: 12px; font-weight: bold; font-family: 'Helvetica', 'Arial', sans-serif; }
        
        table.matrix-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 10px; page-break-inside: auto; }
        table.matrix-table tr { page-break-inside: avoid; page-break-after: auto; }
        table.matrix-table th, table.matrix-table td { border: 1px solid #000; padding: 7px 3px; text-align: center; vertical-align: middle; word-wrap: normal; word-break: normal; font-size: 10px; }
        table.matrix-table th { background-color: #e2e8f0; color: #000; font-weight: bold; padding: 7px 3px; }
        
        .cell-time { font-size: 9px; font-family: monospace; font-weight: bold; color: #000; line-height: 1.25; }
        .cell-code { font-size: 10px; font-weight: bold; margin-top: 3px; }

        /* Full Column Cell Background Colors High-Contrast (Pemkab Jember Format) */
        .cell-green { background-color: #4ade80 !important; color: #000; font-weight: bold; } /* Hadir OK */
        .cell-yellow { background-color: #facc15 !important; color: #000; font-weight: bold; } /* Terlambat / PSW */
        .cell-blue { background-color: #38bdf8 !important; color: #000; font-weight: bold; } /* Izin / IT / Cuti / Sakit */
        .cell-red { background-color: #f87171 !important; color: #000; font-weight: bold; } /* TK / Tidak Hadir */
        .cell-libur { background-color: #e2e8f0 !important; color: #475569; } /* Libur */

        .pegawai-info { text-align: left; padding: 7px 8px; font-size: 10px; line-height: 1.35; background-color: #fff !important; word-wrap: normal !important; word-break: normal !important; overflow: hidden; }
        .pegawai-nama { font-weight: bold; color: #000; font-size: 11px; word-wrap: normal !important; word-break: normal !important; white-space: normal; }
        .pegawai-nip { font-size: 9px; color: #475569; }
        .pegawai-jabatan { font-size: 9px; color: #1e293b; font-style: italic; }

        .legend-box { border: 1px solid #000; padding: 8px 12px; background: #fff; margin-top: 12px; font-size: 10px; page-break-inside: avoid; }
        .legend-item { display: inline-block; margin-right: 15px; margin-bottom: 2px; }
        .legend-color { display: inline-block; width: 12px; height: 12px; border: 1px solid #000; vertical-align: middle; margin-right: 4px; }

        .footer-sig { width: 100%; margin-top: 15px; page-break-inside: avoid; }
        .sig-box { float: right; width: 270px; text-align: center; font-size: 11px; }
        .sig-space { height: 45px; }

        .print-footer-left {
            position: fixed;
            bottom: 3mm;
            left: 10mm;
            font-size: 10px;
            color: #333;
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-weight: bold;
        }

        .employee-report { page-break-before: always; }
        .employee-report.first { page-break-before: auto; }
        .employee-name { font-size: 16px; font-weight: bold; text-transform: uppercase; margin: 10px 0 3px; }
        .employee-detail { font-size: 11px; margin-bottom: 10px; color: #1e293b; }
        .monthly-table th, .monthly-table td { font-size: 11px; padding: 8px 7px; }
        .monthly-table th { background: #dbeafe; }
        .monthly-table .date-cell { font-weight: bold; text-align: left; }
        .monthly-table .status-cell { font-weight: bold; text-transform: uppercase; }
        .monthly-table .note-cell { text-align: left; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <script type="text/php">
        if (isset($pdf)) {
            $text = "Halaman {PAGE_NUM} dari {PAGE_COUNT}";
            $timestamp = "Dicetak pada: " . date('d/m/Y H:i:s') . " WIB";
            $font = $fontMetrics->get_font("Helvetica", "bold");
            $size = 10;
            $color = array(0.2, 0.2, 0.2);
            
            // Bottom Left: Timestamp
            $pdf->page_text(28, 910, $timestamp, $font, $size, $color);
            
            // Bottom Right: Page Numbers
            $pdf->page_text(475, 910, $text, $font, $size, $color);
        }
    </script>

    @if($jenisLaporan !== 'bulanan')
    <!-- Header Kop Surat Pemkab Jember dengan Logo Resmi -->
    <table class="header-table">
        <tr>
            <td style="width: 80px; text-align: center; vertical-align: middle; padding-bottom: 6px;">
                @if(!empty($logoBase64))
                <img src="{{ $logoBase64 }}" style="width: 65px; height: 65px; object-fit: contain;">
                @else
                <img src="{{ asset('logo.png') }}" style="width: 65px; height: 65px; object-fit: contain;">
                @endif
            </td>
            <td style="text-align: center; vertical-align: middle; padding-bottom: 6px; padding-right: 80px;">
                <div style="font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.2;">PEMERINTAH KABUPATEN JEMBER</div>
                <div style="font-size: 16px; font-weight: bold; text-transform: uppercase; line-height: 1.2; margin-top: 2px;">PEMERINTAH DESA BALUNGLOR</div>
                <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; line-height: 1.2; color: #1e293b; margin-top: 1px;">KECAMATAN BALUNG</div>
            </td>
        </tr>
    </table>

    <!-- Meta Info Bar -->
    <div class="meta-info">
        <div>Bulan : {{ strtoupper($namaBulan) }} {{ $tahun }}</div>
        <div>Instansi : PEMERINTAH DESA BALUNGLOR</div>
    </div>
    @endif

    @if($jenisLaporan === 'bulanan')
    @foreach($dataLaporan['items'] as $item)
    <?php $p = $item['pegawai']; ?>
    <section class="employee-report {{ $loop->first ? 'first' : '' }}">
        <table class="header-table">
            <tr>
                <td style="width: 70px; text-align: center; vertical-align: middle; padding-bottom: 5px;">
                    @if(!empty($logoBase64))
                    <img src="{{ $logoBase64 }}" style="width: 56px; height: 56px; object-fit: contain;">
                    @else
                    <img src="{{ asset('logo.png') }}" style="width: 56px; height: 56px; object-fit: contain;">
                    @endif
                </td>
                <td style="text-align: center; vertical-align: middle; padding-bottom: 5px; padding-right: 70px;">
                    <div style="font-size: 12px; font-weight: bold; text-transform: uppercase; line-height: 1.2;">PEMERINTAH KABUPATEN JEMBER</div>
                    <div style="font-size: 14px; font-weight: bold; text-transform: uppercase; line-height: 1.2; margin-top: 2px;">PEMERINTAH DESA BALUNGLOR</div>
                    <div style="font-size: 10px; font-weight: bold; text-transform: uppercase; line-height: 1.2; color: #1e293b;">KECAMATAN BALUNG</div>
                </td>
            </tr>
        </table>
        <div class="meta-info">Laporan Presensi Bulan: {{ strtoupper($namaBulan) }} {{ $tahun }}</div>
        <div class="employee-name">{{ $p->nama_lengkap }}</div>
        <div class="employee-detail">Jabatan: {{ $p->jabatan }}<br>Total jam kerja tercatat: <strong>{{ $item['total_hours'] }} jam {{ $item['total_mins'] }} menit</strong></div>
        <table class="matrix-table monthly-table">
            <thead><tr><th style="width: 28%;">Tanggal</th><th style="width: 16%;">Masuk</th><th style="width: 16%;">Pulang</th><th style="width: 18%;">Status</th><th>Keterangan</th></tr></thead>
            <tbody>
            @foreach($item['matrix'] as $dayData)
                <?php
                    $rec = $dayData['record'];
                    $status = $dayData['is_weekend'] ? 'Libur' : ($rec ? strtoupper(str_replace('_', ' ', $rec->status)) : 'Tidak Hadir');
                    $class = $dayData['is_weekend'] ? 'cell-libur' : (!$rec ? 'cell-red' : ($rec->status === 'hadir' ? 'cell-green' : (in_array($rec->status, ['terlambat']) ? 'cell-yellow' : 'cell-blue')));
                ?>
                <tr>
                    <td class="date-cell">{{ $dayData['day_name'] }}, {{ $dayData['day_num'] }} {{ $namaBulan }} {{ $tahun }}</td>
                    <td>{{ $rec && $rec->jam_masuk ? substr($rec->jam_masuk, 0, 5) : '-' }}</td>
                    <td>{{ $rec && $rec->jam_pulang ? substr($rec->jam_pulang, 0, 5) : '-' }}</td>
                    <td class="status-cell {{ $class }}">{{ $status }}</td>
                    <td class="note-cell">{{ $rec->keterangan ?? '-' }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <div class="footer-sig" style="margin-top: 8px;">
            <div class="sig-box">
                <p>Jember, {{ $currentDateIndo ?? \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY') }}</p>
                <p><strong>Kepala Desa Balunglor</strong></p>
                <div class="sig-space"></div>
                <p><u><strong>IMAM MUSTOFA</strong></u></p>
                <p>Kepala Desa Balunglor</p>
            </div>
        </div>
    </section>
    @endforeach
    @else
    <!-- Mode Harian / Rentang -->
    <table class="matrix-table">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 95px;">Tanggal</th>
                <th style="width: 190px;">Nama Aparatur Desa</th>
                <th style="width: 165px;">Jabatan</th>
                <th style="width: 85px;">Jam Masuk</th>
                <th style="width: 85px;">Jam Pulang</th>
                <th style="width: 90px;">Status</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dataLaporan['items'] as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                <td class="pegawai-info"><strong>{{ $row->pegawai->nama_lengkap ?? '-' }}</strong></td>
                <td class="pegawai-info">{{ $row->pegawai->jabatan ?? '-' }}</td>
                <td class="cell-time">{{ $row->jam_masuk ?? '-' }}</td>
                <td class="cell-time">{{ $row->jam_pulang ?? '-' }}</td>
                <td><strong>{{ strtoupper($row->status) }}</strong></td>
                <td style="text-align: left;">{{ $row->keterangan ?? '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8">Tidak ada record presensi ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @endif

    @if($jenisLaporan !== 'bulanan')
    <!-- Signature Section -->
    <div class="footer-sig">
        <div class="sig-box">
            <p>Jember, {{ $currentDateIndo ?? \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY') }}</p>
            <p><strong>Kepala Desa Balunglor</strong></p>
            <div class="sig-space"></div>
            <p><u><strong>IMAM MUSTOFA</strong></u></p>
            <p>Kepala Desa Balunglor</p>
        </div>
    </div>
    @endif

    <!-- Print Footer for Browser Print Mode (Bottom Left Timestamp) -->
    <div class="print-footer-left">
        Dicetak pada: {{ $printTimestamp ?? \Carbon\Carbon::now()->locale('id')->isoFormat('DD/MM/YYYY HH:mm:ss') . ' WIB' }}
    </div>

</body>
</html>
