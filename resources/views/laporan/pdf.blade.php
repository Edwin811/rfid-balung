<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Presensi Bulanan - Pemkab Jember (Desa Balunglor)</title>
    <style>
        @page { size: A3 landscape; margin: 10mm 10mm 15mm 10mm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 8.5px; color: #000; margin: 0; padding: 5px; background: #fff; }
        
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 8px; border-bottom: 3px double #000; }
        .header-table td { border: none !important; }
        
        .meta-info { width: 100%; margin-bottom: 8px; font-size: 10px; font-weight: bold; font-family: 'Helvetica', 'Arial', sans-serif; }
        
        table.matrix-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 10px; }
        table.matrix-table th, table.matrix-table td { border: 1px solid #000; padding: 3px 1px; text-align: center; vertical-align: middle; word-wrap: break-word; font-size: 8px; }
        table.matrix-table th { background-color: #e2e8f0; color: #000; font-weight: bold; }
        
        .cell-time { font-size: 7.5px; font-family: monospace; font-weight: bold; color: #000; line-height: 1.1; }
        .cell-code { font-size: 8px; font-weight: bold; margin-top: 1px; }

        /* Full Column Cell Background Colors High-Contrast (Pemkab Jember Format) */
        .cell-green { background-color: #4ade80 !important; color: #000; font-weight: bold; } /* Hadir OK */
        .cell-yellow { background-color: #facc15 !important; color: #000; font-weight: bold; } /* Terlambat / PSW */
        .cell-blue { background-color: #38bdf8 !important; color: #000; font-weight: bold; } /* Izin / IT / Cuti / Sakit */
        .cell-red { background-color: #f87171 !important; color: #000; font-weight: bold; } /* TK / Tidak Hadir */
        .cell-libur { background-color: #e2e8f0 !important; color: #475569; } /* Libur */

        .pegawai-info { text-align: left; padding-left: 5px; font-size: 8.5px; line-height: 1.2; background-color: #fff !important; }
        .pegawai-nama { font-weight: bold; color: #000; font-size: 9px; }
        .pegawai-nip { font-size: 7.5px; color: #475569; }
        .pegawai-jabatan { font-size: 7.5px; color: #1e293b; font-style: italic; }

        .legend-box { border: 1px solid #000; padding: 6px 10px; background: #fff; margin-top: 8px; font-size: 8.5px; }
        .legend-item { display: inline-block; margin-right: 15px; margin-bottom: 2px; }
        .legend-color { display: inline-block; width: 12px; height: 12px; border: 1px solid #000; vertical-align: middle; margin-right: 4px; }

        .footer-sig { width: 100%; margin-top: 15px; page-break-inside: avoid; }
        .sig-box { float: right; width: 250px; text-align: center; font-size: 10px; }
        .sig-space { height: 45px; }

        .print-footer-left {
            position: fixed;
            bottom: 3mm;
            left: 10mm;
            font-size: 8.5px;
            color: #333;
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-weight: bold;
        }

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
            $size = 8;
            $color = array(0.2, 0.2, 0.2);
            
            // Bottom Left: Timestamp
            $pdf->page_text(30, 815, $timestamp, $font, $size, $color);
            
            // Bottom Right: Page Numbers
            $pdf->page_text(1110, 815, $text, $font, $size, $color);
        }
    </script>

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

    @if($jenisLaporan === 'bulanan')
    <!-- Matriks 31 Hari Presensi Style Pemkab Jember (Warna Full Kolom Cell) -->
    <table class="matrix-table">
        <thead>
            <tr>
                <th style="width: 160px;">Pegawai</th>
                @foreach($dataLaporan['days'] as $day)
                <th style="width: 28px;">
                    {{ $day['day_num'] }}<br>
                    <span style="font-size: 7px; font-weight: normal;">{{ $day['day_name'] }}</span>
                </th>
                @endforeach
                <th style="width: 65px;">Jumlah Jam</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataLaporan['items'] as $item)
            <?php $p = $item['pegawai']; ?>
            <tr>
                <td class="pegawai-info">
                    <div class="pegawai-nama">{{ $p->nama_lengkap }}</div>
                    <div class="pegawai-nip">NIP/ID : {{ $p->id_mesin }}</div>
                    <div class="pegawai-jabatan">{{ $p->jabatan }}</div>
                </td>

                @foreach($item['matrix'] as $dayData)
                <?php
                    $cellClass = 'cell-libur';
                    if (!$dayData['is_weekend']) {
                        if ($dayData['record']) {
                            $st = $dayData['record']->status;
                            if ($st === 'hadir') $cellClass = 'cell-green';
                            elseif ($st === 'terlambat') $cellClass = 'cell-yellow';
                            elseif (in_array($st, ['sakit', 'izin', 'dispen'])) $cellClass = 'cell-blue';
                            else $cellClass = 'cell-green';
                        } else {
                            $cellClass = 'cell-red';
                        }
                    }
                ?>
                <td class="{{ $cellClass }}">
                    @if($dayData['is_weekend'])
                    <span>Libur</span>
                    @elseif($dayData['record'])
                        <?php $rec = $dayData['record']; ?>
                        <div class="cell-time">{{ $rec->jam_masuk ? substr($rec->jam_masuk, 0, 5) : '-' }}</div>
                        <div class="cell-time">{{ $rec->jam_pulang ? substr($rec->jam_pulang, 0, 5) : '-' }}</div>
                        <div class="cell-code">
                            @if($rec->status === 'hadir') OK-OK
                            @elseif($rec->status === 'terlambat') TL1-OK
                            @elseif($rec->status === 'sakit') SK
                            @elseif($rec->status === 'izin') IT
                            @elseif($rec->status === 'dispen') IT
                            @else OK
                            @endif
                        </div>
                    @else
                    <div class="cell-code">TK</div>
                    @endif
                </td>
                @endforeach

                <td style="font-weight: bold; font-size: 8px; background-color: #fff !important;">
                    {{ $item['total_hours'] }} jam<br>{{ $item['total_mins'] }} menit
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Keterangan Warna & Kode Status (Legenda Presensi Pemkab) -->
    <div class="legend-box">
        <div style="font-weight: bold; margin-bottom: 4px; text-transform: uppercase;">KETERANGAN WARNA KOLOM & KODE STATUS:</div>
        <div class="legend-item"><span class="legend-color" style="background-color: #4ade80;"></span><strong>HIJAU (OK / OK-OK)</strong> : Hadir Tepat Waktu</div>
        <div class="legend-item"><span class="legend-color" style="background-color: #facc15;"></span><strong>KUNING (TL1 / PSW)</strong> : Terlambat / Pulang Sebelum Waktu</div>
        <div class="legend-item"><span class="legend-color" style="background-color: #38bdf8;"></span><strong>BIRU (IT / SK / CT)</strong> : Izin Tidak Masuk / Sakit / Cuti</div>
        <div class="legend-item"><span class="legend-color" style="background-color: #f87171;"></span><strong>MERAH (TK)</strong> : Tanpa Keterangan / Tidak Hadir</div>
        <div class="legend-item"><span class="legend-color" style="background-color: #e2e8f0;"></span><strong>ABU-ABU (LIBUR)</strong> : Akhir Pekan (Sabtu & Minggu)</div>
    </div>
    @else
    <!-- Mode Harian / Rentang -->
    <table class="matrix-table">
        <thead>
            <tr>
                <th style="width: 30px;">No</th>
                <th style="width: 70px;">Tanggal</th>
                <th style="width: 150px;">Nama Aparatur Desa</th>
                <th style="width: 130px;">Jabatan</th>
                <th style="width: 60px;">Jam Masuk</th>
                <th style="width: 60px;">Jam Pulang</th>
                <th style="width: 70px;">Status</th>
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

    <!-- Signature Section -->
    <div class="footer-sig">
        <div class="sig-box">
            <p>Balung Lor, {{ $currentDateIndo ?? \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY') }}</p>
            <p><strong>Kepala Desa Balunglor</strong></p>
            <div class="sig-space"></div>
            <p><u><strong>IMAM MUSTOFA</strong></u></p>
            <p>Kepala Desa Balunglor</p>
        </div>
    </div>

    <!-- Print Footer for Browser Print Mode (Bottom Left Timestamp) -->
    <div class="print-footer-left">
        Dicetak pada: {{ $printTimestamp ?? \Carbon\Carbon::now()->locale('id')->isoFormat('DD/MM/YYYY HH:mm:ss') . ' WIB' }}
    </div>

</body>
</html>
