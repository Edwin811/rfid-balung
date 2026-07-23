<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceLog;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AdmsController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function handshake(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'ADMS Endpoint Active',
            'timestamp' => now()->toDateTimeString(),
        ]);
    }

    public function handleAttendance(Request $request): JsonResponse
    {
        $secretKey = $request->input('secret_key') ?? $request->header('X-ADMS-KEY');
        $expectedKey = config('app.adms_secret_key', env('ADMS_SECRET_KEY', 'balunglor_secret_2026'));

        if ($secretKey !== $expectedKey) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized key'], 401);
        }

        $logs = $request->input('logs');

        // Allow single log or batch logs
        if (!$logs && $request->has('user_id')) {
            $logs = [$request->all()];
        }

        if (empty($logs) || !is_array($logs)) {
            return response()->json(['status' => 'error', 'message' => 'No logs provided'], 400);
        }

        $savedCount = 0;
        foreach ($logs as $logData) {
            $userId = $logData['user_id'] ?? $logData['id_mesin'] ?? null;
            $timestamp = $logData['timestamp'] ?? $logData['timestamp_absen'] ?? null;
            $status = $logData['status'] ?? null;
            $punch = $logData['punch'] ?? null;

            if ($userId && $timestamp) {
                $tipeVerifikasi = isset($logData['tipe_verifikasi']) ? $logData['tipe_verifikasi'] : 'fingerprint';

                // Cegah duplikasi penumpukan baris di database
                $exists = DeviceLog::where('id_mesin_device', (string) $userId)
                    ->where('timestamp_absen', $timestamp)
                    ->exists();

                if (!$exists) {
                    DeviceLog::create([
                        'id_mesin_device' => (string) $userId,
                        'timestamp_absen' => $timestamp,
                        'tipe_verifikasi' => $tipeVerifikasi,
                        'raw_data' => $logData,
                        'is_processed' => true,
                        'created_at' => now(),
                    ]);

                    // Process log into presensi table
                    $this->attendanceService->processLog(
                        (string) $userId,
                        $timestamp,
                        $tipeVerifikasi,
                        $logData
                    );

                    $savedCount++;
                }
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => "Successfully processed {$savedCount} logs",
            'received' => count($logs),
            'processed' => $savedCount,
        ]);
    }

    public function getPegawaiList(): JsonResponse
    {
        $pegawai = \App\Models\Pegawai::where('is_active', true)->orderBy('id_mesin')->get();
        return response()->json([
            'status' => 'success',
            'data' => $pegawai,
        ]);
    }
}
