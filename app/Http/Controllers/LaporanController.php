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

        $pegawaiList = Pegawai::where('is_active', true)->orderBy('id_mesin')->get();

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

        AuditLogger::log('export_pdf_laporan', null, null, null, [
            'jenis' => $jenisLaporan, 'bulan' => $bulan, 'tahun' => $tahun, 'pegawai_id' => $pegawaiId
        ]);

        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('laporan.pdf', compact(
                'jenisLaporan', 'bulan', 'tahun', 'namaBulan', 'tanggalMulai', 'tanggalSelesai',
                'selectedPegawai', 'status', 'dataLaporan'
            ))->setPaper('a4', 'landscape');

            return $pdf->download("Laporan_Presensi_{$jenisLaporan}_BalungLor.pdf");
        }

        return view('laporan.pdf', compact(
            'jenisLaporan', 'bulan', 'tahun', 'namaBulan', 'tanggalMulai', 'tanggalSelesai',
            'selectedPegawai', 'status', 'dataLaporan'
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

        AuditLogger::log('export_excel_laporan', null, null, null, [
            'jenis' => $jenisLaporan, 'bulan' => $bulan, 'tahun' => $tahun, 'pegawai_id' => $pegawaiId
        ]);

        $fileName = "Laporan_Presensi_{$jenisLaporan}_BalungLor.xls";

        // HTML Excel Spreadsheet Export
        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">';
        $html .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>Laporan Presensi</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        $html .= '<style>';
        $html .= 'body { font-family: Calibri, Arial, sans-serif; font-size: 12pt; }';
        $html .= '.header-title { font-size: 16pt; font-weight: bold; text-align: center; color: #9f1239; }';
        $html .= '.header-sub { font-size: 13pt; font-weight: bold; text-align: center; color: #1e293b; }';
        $html .= '.header-period { font-size: 12pt; text-align: center; font-style: italic; color: #475569; }';
        $html .= 'table { border-collapse: collapse; width: 100%; font-size: 12pt; }';
        $html .= 'th { background-color: #9f1239; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000000; padding: 10px; }';
        $html .= 'td { border: 1px solid #000000; padding: 8px; vertical-align: middle; }';
        $html .= '.center { text-align: center; }';
        $html .= '.bold { font-weight: bold; }';
        $html .= '.even-row { background-color: #f8fafc; }';
        $html .= '.odd-row { background-color: #ffffff; }';
        $html .= '.sig-box { text-align: center; font-weight: bold; font-size: 12pt; }';
        $html .= '</style></head><body>';

        // Header Title
        $html .= '<table>';
        $html .= '<tr><td colspan="9" class="header-sub">PEMERINTAH KABUPATEN JEMBER - KECAMATAN BALUNG</td></tr>';
        $html .= '<tr><td colspan="9" class="header-title">PEMERINTAH DESA BALUNGLOR</td></tr>';
        $html .= '<tr><td colspan="9" class="header-sub">LAPORAN PRESENSI APARATUR DESA (' . strtoupper($jenisLaporan) . ')</td></tr>';
        if ($jenisLaporan === 'bulanan') {
            $html .= '<tr><td colspan="9" class="header-period">PERIODE: ' . strtoupper($namaBulan) . ' ' . $tahun . '</td></tr>';
        } else {
            $html .= '<tr><td colspan="9" class="header-period">PERIODE: ' . $tanggalMulai . ' S/D ' . $tanggalSelesai . '</td></tr>';
        }
        if ($selectedPegawai) {
            $html .= '<tr><td colspan="9" class="header-period">APARATUR: ' . strtoupper($selectedPegawai->nama_lengkap) . ' (' . strtoupper($selectedPegawai->jabatan) . ')</td></tr>';
        }
        $html .= '<tr><td colspan="9">&nbsp;</td></tr>';
        $html .= '</table>';

        if ($jenisLaporan === 'bulanan') {
            $html .= '<table><thead><tr>';
            $html .= '<th style="width:40px;">NO</th>';
            $html .= '<th style="width:90px;">ID MESIN</th>';
            $html .= '<th style="width:250px;">NAMA APARATUR DESA</th>';
            $html .= '<th style="width:180px;">JABATAN</th>';
            $html .= '<th style="width:80px;">HADIR</th>';
            $html .= '<th style="width:100px;">TERLAMBAT</th>';
            $html .= '<th style="width:80px;">SAKIT</th>';
            $html .= '<th style="width:80px;">IZIN</th>';
            $html .= '<th style="width:80px;">DISPEN</th>';
            $html .= '<th style="width:110px;">TIDAK HADIR</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($dataLaporan['items'] as $index => $item) {
                $rowClass = ($index % 2 === 0) ? 'even-row' : 'odd-row';
                $html .= '<tr class="' . $rowClass . '">';
                $html .= '<td class="center">' . ($index + 1) . '</td>';
                $html .= '<td class="center font-mono">' . $item['pegawai']->id_mesin . '</td>';
                $html .= '<td class="bold">' . $item['pegawai']->nama_lengkap . '</td>';
                $html .= '<td>' . $item['pegawai']->jabatan . '</td>';
                $html .= '<td class="center bold" style="color:#16a34a;">' . $item['hadir'] . '</td>';
                $html .= '<td class="center bold" style="color:#dc2626;">' . $item['terlambat'] . '</td>';
                $html .= '<td class="center bold" style="color:#d97706;">' . $item['sakit'] . '</td>';
                $html .= '<td class="center bold" style="color:#2563eb;">' . $item['izin'] . '</td>';
                $html .= '<td class="center bold" style="color:#4f46e5;">' . $item['dispen'] . '</td>';
                $html .= '<td class="center bold" style="color:#475569;">' . $item['tidak_hadir'] . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';

        } else { // Harian / Individu
            $html .= '<table><thead><tr>';
            $html .= '<th style="width:40px;">NO</th>';
            $html .= '<th style="width:110px;">TANGGAL</th>';
            $html .= '<th style="width:230px;">NAMA APARATUR</th>';
            $html .= '<th style="width:180px;">JABATAN</th>';
            $html .= '<th style="width:90px;">JAM MASUK</th>';
            $html .= '<th style="width:90px;">JAM PULANG</th>';
            $html .= '<th style="width:110px;">STATUS</th>';
            $html .= '<th style="width:200px;">KETERANGAN</th>';
            $html .= '</tr></thead><tbody>';

            foreach ($dataLaporan['items'] as $index => $row) {
                $rowClass = ($index % 2 === 0) ? 'even-row' : 'odd-row';
                $html .= '<tr class="' . $rowClass . '">';
                $html .= '<td class="center">' . ($index + 1) . '</td>';
                $html .= '<td class="center">' . Carbon::parse($row->tanggal)->format('d/m/Y') . '</td>';
                $html .= '<td class="bold">' . ($row->pegawai->nama_lengkap ?? '-') . '</td>';
                $html .= '<td>' . ($row->pegawai->jabatan ?? '-') . '</td>';
                $html .= '<td class="center bold">' . ($row->jam_masuk ?? '-') . '</td>';
                $html .= '<td class="center bold">' . ($row->jam_pulang ?? '-') . '</td>';
                $html .= '<td class="center bold">' . strtoupper($row->status) . '</td>';
                $html .= '<td>' . ($row->keterangan ?? '-') . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }

        // Tanda tangan
        $html .= '<table>';
        $html .= '<tr><td colspan="9">&nbsp;</td></tr>';
        $html .= '<tr><td colspan="6"></td><td colspan="3" class="sig-box">Balung Lor, ' . Carbon::now()->locale('id')->isoFormat('D MMMM YYYY') . '</td></tr>';
        $html .= '<tr><td colspan="6"></td><td colspan="3" class="sig-box">Kepala Desa Balunglor</td></tr>';
        $html .= '<tr><td colspan="9">&nbsp;</td></tr>';
        $html .= '<tr><td colspan="9">&nbsp;</td></tr>';
        $html .= '<tr><td colspan="6"></td><td colspan="3" class="sig-box" style="text-decoration:underline;">IMAM MUSTOFA</td></tr>';
        $html .= '</table></body></html>';

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
            $pegawaiQuery = Pegawai::where('is_active', true)->orderBy('id_mesin');
            if ($pegawaiId) {
                $pegawaiQuery->where('id', $pegawaiId);
            }
            $pegawaiList = $pegawaiQuery->get();

            $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
            $workDaysCount = 0;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $date = Carbon::createFromDate($tahun, $bulan, $d);
                if (!$date->isWeekend()) {
                    $workDaysCount++;
                }
            }

            $items = [];
            foreach ($pegawaiList as $pegawai) {
                $presensi = Presensi::where('pegawai_id', $pegawai->id)
                    ->whereYear('tanggal', $tahun)
                    ->whereMonth('tanggal', $bulan)
                    ->get();

                $hadir = $presensi->where('status', 'hadir')->count();
                $terlambat = $presensi->where('status', 'terlambat')->count();
                $sakit = $presensi->where('status', 'sakit')->count();
                $izin = $presensi->where('status', 'izin')->count();
                $dispen = $presensi->where('status', 'dispen')->count();
                $tidakHadir = max(0, $workDaysCount - $presensi->count());

                $items[] = [
                    'pegawai' => $pegawai,
                    'hadir' => $hadir,
                    'terlambat' => $terlambat,
                    'sakit' => $sakit,
                    'izin' => $izin,
                    'dispen' => $dispen,
                    'tidak_hadir' => $tidakHadir,
                    'total_kerja' => $workDaysCount,
                ];
            }

            return ['items' => $items];
        } else {
            // Mode Harian / Rentang / Per Orang
            $query = Presensi::with('pegawai')
                ->whereBetween('tanggal', [$tglMulai, $tglSelesai])
                ->orderBy('tanggal', 'desc')
                ->orderBy('pegawai_id', 'asc');

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
                    'tidak_hadir' => $items->where('status', 'tidak_hadir')->count(),
                ]
            ];
        }
    }
}
