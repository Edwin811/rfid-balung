<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AttendanceService;
use App\Models\DeviceLog;
use Illuminate\Support\Facades\Log;

class SyncRfidAttendance extends Command
{
    protected $signature = 'rfid:sync';
    protected $description = 'Sync attendance logs from ZKTeco RFID machine into Laravel database';

    public function handle(AttendanceService $attendanceService)
    {
        $this->info('Starting attendance sync from ZKTeco RFID machine...');
        
        $pythonScript = base_path('scripts/fetch_presensi.py');
        $pythonBin = env('PYTHON_BIN', 'python');

        $command = escapeshellcmd("{$pythonBin} {$pythonScript}") . " 2>&1";
        $rawOutput = shell_exec($command);

        if (!$rawOutput) {
            $this->error('Failed to execute python fetch script.');
            return 1;
        }

        $result = json_decode($rawOutput, true);

        if (!$result || ($result['status'] ?? '') !== 'success') {
            $this->error('Failed to fetch data from machine: ' . ($result['message'] ?? $rawOutput));
            return 1;
        }

        $logs = $result['data'] ?? [];
        $this->info("Successfully fetched " . count($logs) . " records from machine.");

        $importedCount = 0;
        foreach ($logs as $log) {
            // Check if device log already recorded
            $idMesin = (string) $log['user_id'];
            $timestamp = $log['timestamp'];
            $tipeVerifikasi = $log['tipe_verifikasi'] ?? (($log['status'] ?? 0) == 4 ? 'rfid' : 'fingerprint');

            $deviceLog = DeviceLog::where('id_mesin_device', $idMesin)
                ->where('timestamp_absen', $timestamp)
                ->first();

            if (!$deviceLog) {
                DeviceLog::create([
                    'id_mesin_device' => $idMesin,
                    'timestamp_absen' => $timestamp,
                    'tipe_verifikasi' => $tipeVerifikasi,
                    'raw_data' => $log,
                    'is_processed' => false,
                    'created_at' => now(),
                ]);
                $importedCount++;
            } else {
                if ($deviceLog->tipe_verifikasi !== $tipeVerifikasi) {
                    $deviceLog->tipe_verifikasi = $tipeVerifikasi;
                    $deviceLog->is_processed = false;
                    $deviceLog->save();
                }
            }
        }

        $this->info("Inserted {$importedCount} new raw logs.");

        // Process all unprocessed device logs into presensi table
        $processedCount = $attendanceService->processUnprocessedDeviceLogs();
        $this->info("Processed {$processedCount} presensi records into database!");

        return 0;
    }
}
