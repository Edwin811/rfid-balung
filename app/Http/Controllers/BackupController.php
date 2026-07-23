<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function index()
    {
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $files = File::files($backupDir);
        $backups = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'sql') {
                $backups[] = [
                    'filename' => $file->getFilename(),
                    'path' => $file->getPathname(),
                    'size' => round($file->getSize() / 1024, 2) . ' KB',
                    'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                ];
            }
        }

        // Sort descending by date
        usort($backups, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));

        return view('backup.index', compact('backups'));
    }

    public function store()
    {
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }

        $filename = 'backup_presensi_balunglor_' . date('Ymd_His') . '.sql';
        $filepath = $backupDir . '/' . $filename;

        $tables = ['users', 'pegawai', 'presensi', 'perizinan', 'audit_logs', 'device_logs'];
        $sqlContent = "-- Backup Presensi Desa Balung Lor\n-- Tanggal: " . date('Y-m-d H:i:s') . "\n\n";

        foreach ($tables as $table) {
            $sqlContent .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $createTableSql = DB::select("SHOW CREATE TABLE `{$table}`");
            if (!empty($createTableSql)) {
                $createAttr = 'Create Table';
                $sqlContent .= $createTableSql[0]->$createAttr . ";\n\n";
            }

            $rows = DB::table($table)->get();
            foreach ($rows as $row) {
                $rowArray = (array) $row;
                $keys = array_keys($rowArray);
                $values = array_map(function ($val) {
                    if (is_null($val)) return 'NULL';
                    if (is_numeric($val)) return $val;
                    return "'" . addslashes($val) . "'";
                }, array_values($rowArray));

                $sqlContent .= "INSERT INTO `{$table}` (`" . implode('`, `', $keys) . "`) VALUES (" . implode(', ', $values) . ");\n";
            }
            $sqlContent .= "\n";
        }

        File::put($filepath, $sqlContent);

        AuditLogger::log('backup_database', null, null, null, ['filename' => $filename]);

        return back()->with('success', "Database berhasil di-backup: {$filename}");
    }

    public function download($filename)
    {
        $filepath = storage_path('app/backups/' . basename($filename));
        if (!File::exists($filepath)) {
            return back()->with('error', 'File backup tidak ditemukan.');
        }

        return response()->download($filepath);
    }
}
