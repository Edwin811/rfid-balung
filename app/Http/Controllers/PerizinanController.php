<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Perizinan;
use App\Services\AttendanceService;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerizinanController extends Controller
{
    protected AttendanceService $attendanceService;

    public function __construct(AttendanceService $attendanceService)
    {
        $this->attendanceService = $attendanceService;
    }

    public function index()
    {
        $pegawaiList = Pegawai::where('is_active', true)->orderBy('nama_lengkap')->get();
        $perizinanList = Perizinan::with('pegawai', 'pembuat')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('perizinan.index', compact('pegawaiList', 'perizinanList'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isOperator()) {
            return back()->with('error', 'Hanya Admin dan Operator yang berhak menambah perizinan.');
        }

        $request->validate([
            'pegawai_id' => ['required', 'exists:pegawai,id'],
            'jenis_izin' => ['required', 'in:sakit,izin,dispen'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $perizinan = Perizinan::create([
            'pegawai_id' => $request->input('pegawai_id'),
            'jenis_izin' => $request->input('jenis_izin'),
            'tanggal_mulai' => $request->input('tanggal_mulai'),
            'tanggal_selesai' => $request->input('tanggal_selesai'),
            'keterangan' => $request->input('keterangan'),
            'dibuat_oleh' => Auth::id(),
        ]);

        // Auto-sync into presensi table
        $this->attendanceService->syncLeaveToAttendance($perizinan);

        AuditLogger::log('tambah_perizinan', 'Perizinan', $perizinan->id, null, $perizinan->toArray());

        return back()->with('success', 'Data perizinan berhasil disimpan dan status presensi diperbarui.');
    }
}
