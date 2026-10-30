<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Presensi;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $jenisLaporan = $request->input('jenis_laporan', 'bulanan'); // bulanan, harian, individu
        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));
        $tanggalMulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggalSelesai = $request->input('tanggal_selesai', date('Y-m-d'));
        $pegawaiId = $request->input('pegawai_id');
        $status = $request->input('status');

        $pegawaiList = Pegawai::where('is_active', true)->orderByRaw('CAST(id_mesin AS UNSIGNED) ASC')->get();

        $dataLaporan = $this->getFilteredReportData(
            $jenisLaporan, $bulan, $tahun, $tanggalMulai, $tanggalSelesai, $pegawaiId, $status
        );

        return view('laporan.index', compact(
            'jenisLaporan', 'bulan', 'tahun', 'tanggalMulai', 'tanggalSelesai',
            'pegawaiId', 'status', 'pegawaiList', 'dataLaporan'
        ));
    }

    public function exportPdf(Request $request)
    {
        $jenisLaporan = $request->input('jenis_laporan', 'bulanan');
        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));
        $tanggalMulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggalSelesai = $request->input('tanggal_selesai', date('Y-m-d'));
        $pegawaiId = $request->input('pegawai_id');
        $status = $request->input('status');

        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->isoFormat('MMMM');
        $dataLaporan = $this->getFilteredReportData(
            $jenisLaporan, $bulan, $tahun, $tanggalMulai, $tanggalSelesai, $pegawaiId, $status
        );

        $selectedPegawai = $pegawaiId ? Pegawai::find($pegawaiId) : null;

        $logoPath = public_path('logo.png');
        $logoBase64 = file_exists($logoPath) ? 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath)) : '';
        $printTimestamp = Carbon::now()->locale('id')->isoFormat('DD/MM/YYYY HH:mm:ss') . ' WIB';
        $currentDateIndo = Carbon::now()->locale('id')->isoFormat('D MMMM YYYY');

        AuditLogger::log('export_pdf_laporan', null, null, null, [
            'jenis' => $jenisLaporan, 'bulan' => $bulan, 'tahun' => $tahun, 'pegawai_id' => $pegawaiId
        ]);

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pdf', compact(
                'jenisLaporan', 'bulan', 'tahun', 'namaBulan', 'tanggalMulai', 'tanggalSelesai',
                'selectedPegawai', 'status', 'dataLaporan', 'logoBase64', 'printTimestamp', 'currentDateIndo'
            // F4 portrait memberi ruang cukup untuk 31 hari satu aparatur dalam satu lembar.
            ))->setPaper([0, 0, 612, 936]);

            return $pdf->stream("Laporan_Presensi_Pemkab_{$jenisLaporan}_BalungLor.pdf");
        }

        return view('laporan.pdf', compact(
            'jenisLaporan', 'bulan', 'tahun', 'namaBulan', 'tanggalMulai', 'tanggalSelesai',
            'selectedPegawai', 'status', 'dataLaporan', 'logoBase64', 'printTimestamp', 'currentDateIndo'
        ));
    }

    public function exportExcel(Request $request)
    {
        $jenisLaporan = $request->input('jenis_laporan', 'bulanan');
        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));
        $tanggalMulai = $request->input('tanggal_mulai', date('Y-m-01'));
        $tanggalSelesai = $request->input('tanggal_selesai', date('Y-m-d'));
        $pegawaiId = $request->input('pegawai_id');
        $status = $request->input('status');

        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->isoFormat('MMMM');
        $dataLaporan = $this->getFilteredReportData(
            $jenisLaporan, $bulan, $tahun, $tanggalMulai, $tanggalSelesai, $pegawaiId, $status
        );
        $selectedPegawai = $pegawaiId ? Pegawai::find($pegawaiId) : null;

        $logoUrl = asset('logo.png');
        $printTimestamp = Carbon::now()->locale('id')->isoFormat('DD/MM/YYYY HH:mm:ss') . ' WIB';
        $currentDateIndo = Carbon::now()->locale('id')->isoFormat('D MMMM YYYY');

        AuditLogger::log('export_excel_laporan', null, null, null, [
            'jenis' => $jenisLaporan, 'bulan' => $bulan, 'tahun' => $tahun, 'pegawai_id' => $pegawaiId
        ]);

        $fileName = "Laporan_Presensi_{$jenisLaporan}_BalungLor.xls";

        // HTML Excel Spreadsheet Export (Matriks Pemkab Style)
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        $html .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Laporan Presensi</x:Name><x:WorksheetOptions><x:Zoom>125</x:Zoom><x:PageSetup><x:Layout x:Orientation="Landscape"/><x:FitToPage/></x:PageSetup><x:FitToPage/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        $html .= '<style>';
        $html .= 'body { font-family: Calibri, Arial, sans-serif; font-size: 14pt; }';
        $html .= '.header-title { font-size: 21pt; font-weight: bold; text-align: center; color: #9f1239; }';
        $html .= '.header-sub { font-size: 17pt; font-weight: bold; text-align: center; color: #1e293b; }';
        $html .= '.header-period { font-size: 14pt; text-align: center; font-style: italic; color: #475569; }';
        $html .= 'table { border-collapse: collapse; width: 100%; font-size: 13pt; }';
        $html .= 'th { background-color: #9f1239; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000000; padding: 9px; }';
        $html .= 'td { border: 1px solid #000000; padding: 9px; vertical-align: middle; text-align: center; }';
        $html .= '.left { text-align: left; }';
        $html .= '.bold { font-weight: bold; }';
        $html .= '.bg-libur { background-color: #e2e8f0; color: #475569; }';
        $html .= '.bg-green { background-color: #4ade80; color: #000; }';
        $html .= '.bg-yellow { background-color: #facc15; color: #000; }';
        $html .= '.bg-blue { background-color: #38bdf8; color: #000; }';
        $html .= '.bg-red { background-color: #f87171; color: #000; }';
        $html .= '.no-border { border: none !important; }';
        $html .= '</style></head><body>';

        // Title Header with Logo Pemkab Jember
        $html .= '<table style="border:none; margin-bottom: 15px;">';
        $html .= '<tr>';
        $html .= '<td style="border:none; width:80px; text-align:center; vertical-align:middle;"><img src="' . $logoUrl . '" width="60" height="60" alt="Logo Pemkab Jember"></td>';
        $html .= '<td style="border:none; text-align:center; vertical-align:middle;" colspan="33">';
        $html .= '<div class="header-sub">PEMERINTAH KABUPATEN JEMBER</div>';
        $html .= '<div class="header-title">PEMERINTAH DESA BALUNGLOR</div>';
        $html .= '<div class="header-sub" style="font-size:11pt; font-weight:bold;">KECAMATAN BALUNG</div>';
        $html .= '</td></tr>';
        $html .= '<tr><td colspan="35" style="border:none;" class="header-period">BULAN : ' . strtoupper($namaBulan) . ' ' . $tahun . ' | INSTANSI : PEMERINTAH DESA BALUNGLOR</td></tr>';
        $html .= '<tr><td colspan="35" style="border:none;">&nbsp;</td></tr>';
        $html .= '</table>';

        if ($jenisLaporan === 'bulanan') {
            // Excel mempertahankan format matriks ringkas untuk analisis data.
            $days = $dataLaporan['days'];
            $html .= '<table><thead><tr>';
            $html .= '<th style="width:250px;">Pegawai</th>';
            foreach ($days as $day) {
                $html .= '<th>' . $day['day_num'] . '<br><small>' . $day['day_name'] . '</small></th>';
            }
            $html .= '<th style="width:120px;">Jumlah Jam</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($dataLaporan['items'] as $item) {
                $p = $item['pegawai'];
                $html .= '<tr>';
                $html .= '<td class="left"><b>' . $p->nama_lengkap . '</b><br><small>ID: ' . $p->id_mesin . ' | ' . $p->jabatan . '</small></td>';

                foreach ($item['matrix'] as $dayData) {
                    $bg = 'bg-libur';
                    if (!$dayData['is_weekend']) {
                        if ($dayData['record']) {
                            $st = $dayData['record']->status;
                            if ($st === 'hadir') $bg = 'bg-green';
                            elseif ($st === 'terlambat') $bg = 'bg-yellow';
                            elseif (in_array($st, ['sakit', 'izin', 'dispen', 'skorsing'])) $bg = 'bg-blue';
                            else $bg = 'bg-green';
                        } else {
                            $bg = 'bg-red';
                        }
                    }
                    $html .= '<td class="' . $bg . '">';
                    if ($dayData['is_weekend']) {
                        $html .= 'Libur';
                    } elseif ($dayData['record']) {
                        $rec = $dayData['record'];
                        $masuk = $rec->jam_masuk ? substr($rec->jam_masuk, 0, 5) : '-';
                        $pulang = $rec->jam_pulang ? substr($rec->jam_pulang, 0, 5) : '-';
                        $html .= $masuk . '<br>' . $pulang . '<br><b>' . strtoupper($rec->status) . '</b>';
                    } else {
                        $html .= 'TK';
                    }
                    $html .= '</td>';
                }

                $html .= '<td><b>' . $item['total_hours'] . ' jam ' . $item['total_mins'] . ' menit</b></td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';

            $html .= '<br>';
            $html .= '<table style="border: 1px solid #000000; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; font-size: 11pt;">';
            $html .= '<tr><td colspan="10" style="border: 1px solid #000000; background-color: #f8fafc; font-weight: bold; padding: 8px 10px; text-align: left;">KETERANGAN WARNA KOLOM &amp; KODE STATUS:</td></tr>';
            $html .= '<tr>';
            $html .= '<td style="width: 25px; background-color: #4ade80; border: 1px solid #000000; text-align: center;">&nbsp;</td><td style="border: 1px solid #000000; padding: 6px 8px; text-align: left;"><b>HIJAU</b>: Hadir Tepat Waktu</td>';
            $html .= '<td style="width: 25px; background-color: #facc15; border: 1px solid #000000; text-align: center;">&nbsp;</td><td style="border: 1px solid #000000; padding: 6px 8px; text-align: left;"><b>KUNING</b>: Terlambat</td>';
            $html .= '<td style="width: 25px; background-color: #38bdf8; border: 1px solid #000000; text-align: center;">&nbsp;</td><td style="border: 1px solid #000000; padding: 6px 8px; text-align: left;"><b>BIRU</b>: Izin / Sakit / Dispensasi</td>';
            $html .= '<td style="width: 25px; background-color: #f87171; border: 1px solid #000000; text-align: center;">&nbsp;</td><td style="border: 1px solid #000000; padding: 6px 8px; text-align: left;"><b>MERAH</b>: Tidak Hadir</td>';
            $html .= '</tr></table>';
        } else {
            $html .= '<table><thead><tr>';
            $html .= '<th>NO</th><th>TANGGAL</th><th>NAMA APARATUR</th><th>JABATAN</th><th>JAM MASUK</th><th>JAM PULANG</th><th>STATUS</th><th>KETERANGAN</th>';
            $html .= '</tr></thead><tbody>';
            foreach ($dataLaporan['items'] as $index => $row) {
                $html .= '<tr>';
                $html .= '<td>' . ($index + 1) . '</td>';
                $html .= '<td>' . Carbon::parse($row->tanggal)->format('d/m/Y') . '</td>';
                $html .= '<td class="left bold">' . ($row->pegawai->nama_lengkap ?? '-') . '</td>';
                $html .= '<td class="left">' . ($row->pegawai->jabatan ?? '-') . '</td>';
                $html .= '<td>' . ($row->jam_masuk ?? '-') . '</td>';
                $html .= '<td>' . ($row->jam_pulang ?? '-') . '</td>';
                $html .= '<td class="bold">' . strtoupper($row->status) . '</td>';
                $html .= '<td class="left">' . ($row->keterangan ?? '-') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }

        // Tanda Tangan & Time Stamp Footer (Excel Format Seperti PDF)
        $html .= '<br><br>';
        $html .= '<table style="border:none; width:100%;">';
        $html .= '<tr>';
        $html .= '<td colspan="15" style="border:none; text-align:left; vertical-align:top; font-size:9pt; color:#475569;">';
        $html .= '<b>Dicetak pada:</b> ' . $printTimestamp . '<br>';
        $html .= '<i>Sistem Informasi Presensi Biometrik & RFID Desa Balunglor</i>';
        $html .= '</td>';
        $html .= '<td colspan="20" style="border:none; text-align:center; vertical-align:top; font-size:10pt;">';
        $html .= 'Jember, ' . $currentDateIndo . '<br>';
        $html .= '<b>Kepala Desa Balunglor</b><br><br><br><br>';
        $html .= '<u><b>IMAM MUSTOFA</b></u><br>';
        $html .= 'Kepala Desa Balunglor';
        $html .= '</td>';
        $html .= '</tr>';
        $html .= '</table>';

        $html .= '</body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=utf-8',
            'Content-Disposition' => "attachment; filename={$fileName}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ]);
    }

    private function getFilteredReportData($jenis, $bulan, $tahun, $tglMulai, $tglSelesai, $pegawaiId, $status): array
    {
        if ($jenis === 'bulanan') {
            $pegawaiQuery = Pegawai::where('is_active', true)->orderByRaw('CAST(id_mesin AS UNSIGNED) ASC');
            if ($pegawaiId) {
                $pegawaiQuery->where('id', $pegawaiId);
            }
            $pegawaiList = $pegawaiQuery->get();

            $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
            $workDaysCount = 0;
            $days = [];
            $dayNamesMap = [
                'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
            ];

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dt = Carbon::createFromDate($tahun, $bulan, $d);
                if (!$dt->isWeekend()) {
                    $workDaysCount++;
                }
                $days[] = [
                    'day_num' => $d,
                    'date_str' => $dt->toDateString(),
                    'day_name' => $dayNamesMap[$dt->format('l')] ?? $dt->format('l'),
                    'is_weekend' => $dt->isWeekend(),
                ];
            }

            $items = [];
            foreach ($pegawaiList as $pegawai) {
                $presensiRecords = Presensi::where('pegawai_id', $pegawai->id)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan)
                    ->get()
                    ->keyBy(function($item) {
                        return Carbon::parse($item->tanggal)->toDateString();
                    });

                $matrix = [];
                $totalMinutesWorked = 0;
                $countHadir = 0;
                $countTerlambat = 0;
                $countSakit = 0;
                $countIzin = 0;
                $countDispen = 0;
                $countSkorsing = 0;

                foreach ($days as $day) {
                    $dateKey = $day['date_str'];
                    $record = $presensiRecords->get($dateKey);

                    if ($record && $record->jam_masuk && $record->jam_pulang) {
                        try {
                            $t1 = Carbon::parse($record->tanggal->format('Y-m-d') . ' ' . $record->jam_masuk);
                            $t2 = Carbon::parse($record->tanggal->format('Y-m-d') . ' ' . $record->jam_pulang);
                            if ($t2->gt($t1)) {
                                $totalMinutesWorked += $t2->diffInMinutes($t1);
                            }
                        } catch (\Throwable $e) {}
                    }

                    if ($record) {
                        if ($record->status === 'hadir') $countHadir++;
                        elseif ($record->status === 'terlambat') $countTerlambat++;
                        elseif ($record->status === 'sakit') $countSakit++;
                        elseif ($record->status === 'izin') $countIzin++;
                        elseif ($record->status === 'dispen') $countDispen++;
                        elseif ($record->status === 'skorsing') $countSkorsing++;
                    }

                    $matrix[] = [
                        'day_num' => $day['day_num'],
                        'day_name' => $day['day_name'],
                        'is_weekend' => $day['is_weekend'],
                        'record' => $record,
                    ];
                }

                $totalKehadiranRecorded = $countHadir + $countTerlambat + $countSakit + $countIzin + $countDispen + $countSkorsing;
                $countTidakHadir = max(0, $workDaysCount - $totalKehadiranRecorded);

                $items[] = [
                    'pegawai' => $pegawai,
                    'matrix' => $matrix,
                    'hadir' => $countHadir,
                    'terlambat' => $countTerlambat,
                    'sakit' => $countSakit,
                    'izin' => $countIzin,
                    'dispen' => $countDispen,
                    'skorsing' => $countSkorsing,
                    'tidak_hadir' => $countTidakHadir,
                    'total_kerja' => $workDaysCount,
                    'total_hours' => floor($totalMinutesWorked / 60),
                    'total_mins' => $totalMinutesWorked % 60,
                ];
            }

            if ($status && $status !== 'all') {
                $items = array_values(array_filter($items, function($item) use ($status) {
                    return isset($item[$status]) && $item[$status] > 0;
                }));
            }

            return [
                'days' => $days,
                'items' => $items,
            ];
        } else {
            // Mode Harian / Rentang / Individu
            $query = Presensi::with('pegawai')
                ->whereBetween('tanggal', [$tglMulai, $tglSelesai])
                ->join('pegawai', 'presensi.pegawai_id', '=', 'pegawai.id')
                ->orderBy('presensi.tanggal', 'desc')
                ->orderByRaw('CAST(pegawai.id_mesin AS UNSIGNED) ASC')
                ->select('presensi.*');

            if ($pegawaiId) {
                $query->where('pegawai_id', $pegawaiId);
            }

            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }

            $items = $query->get();

            return [
                'items' => $items,
                'summary' => [
                    'hadir' => $items->where('status', 'hadir')->count(),
                    'terlambat' => $items->where('status', 'terlambat')->count(),
                    'sakit' => $items->where('status', 'sakit')->count(),
                    'izin' => $items->where('status', 'izin')->count(),
                    'dispen' => $items->where('status', 'dispen')->count(),
                    'skorsing' => $items->where('status', 'skorsing')->count(),
                    'tidak_hadir' => $items->where('status', 'tidak_hadir')->count(),
                ]
            ];
        }
    }
}
