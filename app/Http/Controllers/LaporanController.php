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
        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));

        $rekapPegawai = $this->getMonthlyReportData($bulan, $tahun);

        return view('laporan.index', compact('bulan', 'tahun', 'rekapPegawai'));
    }

    public function exportPdf(Request $request)
    {
        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));

        $rekapPegawai = $this->getMonthlyReportData($bulan, $tahun);
        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->isoFormat('MMMM');

        AuditLogger::log('export_pdf_laporan', null, null, null, ['bulan' => $bulan, 'tahun' => $tahun]);

        // HTML Stream / DomPDF view
        if (class_exists(Pdf::class)) {
            $pdf = Pdf::loadView('laporan.pdf', compact('bulan', 'tahun', 'namaBulan', 'rekapPegawai'))
                ->setPaper('a4', 'landscape');
            return $pdf->download("Laporan_Presensi_BalungLor_{$namaBulan}_{$tahun}.pdf");
        }

        return view('laporan.pdf', compact('bulan', 'tahun', 'namaBulan', 'rekapPegawai'));
    }

    public function exportExcel(Request $request)
    {
        $bulan = (int) $request->input('bulan', date('m'));
        $tahun = (int) $request->input('tahun', date('Y'));
        $namaBulan = Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->isoFormat('MMMM');

        $rekapPegawai = $this->getMonthlyReportData($bulan, $tahun);

        AuditLogger::log('export_excel_laporan', null, null, null, ['bulan' => $bulan, 'tahun' => $tahun]);

        $fileName = "Laporan_Presensi_BalungLor_{$namaBulan}_{$tahun}.csv";
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$fileName}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($rekapPegawai) {
            $file = fopen('php://output', 'w');
            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF))); // UTF-8 BOM
            fputcsv($file, ['No', 'ID Mesin', 'Nama Lengkap', 'Jabatan', 'Hadir', 'Terlambat', 'Sakit', 'Izin', 'Dispen', 'Alfa / Tidak Hadir']);

            foreach ($rekapPegawai as $index => $item) {
                fputcsv($file, [
                    $index + 1,
                    $item['pegawai']->id_mesin,
                    $item['pegawai']->nama_lengkap,
                    $item['pegawai']->jabatan,
                    $item['hadir'],
                    $item['terlambat'],
                    $item['sakit'],
                    $item['izin'],
                    $item['dispen'],
                    $item['tidak_hadir'],
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getMonthlyReportData(int $bulan, int $tahun): array
    {
        $pegawaiList = Pegawai::where('is_active', true)->orderBy('nama_lengkap')->get();
        $daysInMonth = Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;

        // Calculate working days in month (excluding weekends)
        $workDaysCount = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $date = Carbon::createFromDate($tahun, $bulan, $d);
            if (!$date->isWeekend()) {
                $workDaysCount++;
            }
        }

        $result = [];
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
            $recordedCount = $presensi->count();
            $tidakHadir = max(0, $workDaysCount - $recordedCount);

            $result[] = [
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

        return $result;
    }
}
