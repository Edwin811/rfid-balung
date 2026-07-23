<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Presensi Bulanan - Pemkab Jember (Desa Balunglor)</title>
    <style>
        @page { size: A3 landscape; margin: 10mm; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 8.5px; color: #000; margin: 0; padding: 10px; background: #fff; }
        
        .header-pemkab { text-align: center; font-weight: bold; line-height: 1.3; margin-bottom: 12px; }
        .header-pemkab h2 { margin: 0; font-size: 15px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header-pemkab h3 { margin: 1px 0; font-size: 13px; text-transform: uppercase; }
        .header-pemkab h4 { margin: 1px 0; font-size: 12px; font-weight: bold; text-transform: uppercase; color: #1e293b; }
        
        .meta-info { width: 100%; margin-bottom: 8px; font-size: 10px; font-weight: bold; }
        
        table.matrix-table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-bottom: 12px; }
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

        .legend-box { border: 1px solid #000; padding: 8px 12px; background: #fff; margin-top: 10px; font-size: 9px; }
        .legend-item { display: inline-block; margin-right: 18px; margin-bottom: 4px; }
        .legend-color { display: inline-block; width: 14px; height: 14px; border: 1px solid #000; vertical-align: middle; margin-right: 5px; }

        .footer-sig { width: 100%; margin-top: 15px; page-break-inside: avoid; }
        .sig-box { float: right; width: 250px; text-align: center; font-size: 10px; }
        .sig-space { height: 45px; }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <!-- Floating Action Header -->
    <div class="no-print" style="position: sticky; top: 0; background: #881337; color: white; padding: 12px 20px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px; border-radius: 12px; box-shadow: 0 8px 25px rgba(0,0,0,0.25); font-family: sans-serif; z-index: 9999;">
        <div style="display: flex; align-items: center; gap: 10px;">
            <div style="width: 10px; height: 10px; background: #f59e0b; border-radius: 50%;"></div>
            <div style="font-weight: 800; font-size: 13px;">FORMAT RESMI PEMKAB JEMBER — WARNA FULL KOLOM & LEGENDA STATUS</div>
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="window.print()" style="background: #ffffff; color: #881337; border: none; padding: 8px 18px; border-radius: 10px; font-weight: 800; font-size: 12px; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 2px 5px rgba(0,0,0,0.15);">
                📥 CETAK / SIMPAN KE PDF (SAVE AS PDF)
            </button>
            <button onclick="window.close()" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.4); padding: 8px 14px; border-radius: 10px; font-weight: 700; font-size: 12px; cursor: pointer;">
                Tutup
            </button>
        </div>
    </div>

    <!-- Header Kop Surat Pemkab Jember -->
    <div class="header-pemkab">
        <h2>PEMERINTAH KABUPATEN JEMBER</h2>
        <h3>PEMERINTAH DESA BALUNGLOR</h3>
        <h4>JEMBER</h4>
    </div>

    <!-- Meta Info Bar -->
    <div class="meta-info">
        <div>Bulan : {{ $namaBulan }} {{ $tahun }}</div>
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
        <div style="font-weight: bold; margin-bottom: 5px; text-transform: uppercase;">KETERANGAN WARNA KOLOM & KODE STATUS:</div>
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
            <p>Balung Lor, {{ \Carbon\Carbon::now()->locale('id')->isoFormat('D MMMM YYYY') }}</p>
            <p><strong>Kepala Desa Balunglor</strong></p>
            <div class="sig-space"></div>
            <p><u><strong>IMAM MUSTOFA</strong></u></p>
            <p>Kepala Desa Balunglor</p>
        </div>
    </div>

</body>
</html>
