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
        $pegawaiList = Pegawai::where('is_active', true)->orderByRaw('CAST(id_mesin AS UNSIGNED) ASC')->get();
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
            'jenis_izin' => ['required', 'in:sakit,izin,cuti,dispen,skorsing'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['nullable', 'string'],
            'file_surat' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $filePath = null;
        if ($request->hasFile('file_surat')) {
            $file = $request->file('file_surat');
            $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $file->move(public_path('uploads/perizinan'), $filename);
            $filePath = 'uploads/perizinan/' . $filename;
        }

        $perizinan = Perizinan::create([
            'pegawai_id' => $request->input('pegawai_id'),
            'jenis_izin' => $request->input('jenis_izin'),
            'tanggal_mulai' => $request->input('tanggal_mulai'),
            'tanggal_selesai' => $request->input('tanggal_selesai'),
            'keterangan' => $request->input('keterangan'),
            'file_surat' => $filePath,
            'dibuat_oleh' => Auth::id(),
        ]);

        // Auto-sync into presensi table
        $this->attendanceService->syncLeaveToAttendance($perizinan);

        AuditLogger::log('tambah_perizinan', 'Perizinan', $perizinan->id, null, $perizinan->toArray());

        return back()->with('success', 'Data perizinan berhasil disimpan dan status presensi diperbarui.');
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isOperator()) {
            return back()->with('error', 'Hanya Admin dan Operator yang berhak mengedit data perizinan.');
        }

        $perizinan = Perizinan::findOrFail($id);

        $request->validate([
            'pegawai_id' => ['required', 'exists:pegawai,id'],
            'jenis_izin' => ['required', 'in:sakit,izin,cuti,dispen,skorsing'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'keterangan' => ['nullable', 'string'],
            'file_surat' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ]);

        $oldData = $perizinan->toArray();

        // 1. Memulihkan presensi lama sebelum memperbarui ke tanggal/jenis izin baru
        $this->attendanceService->removeLeaveFromAttendance($perizinan);

        $filePath = $perizinan->file_surat;
        if ($request->hasFile('file_surat')) {
            if (!empty($filePath) && file_exists(public_path($filePath))) {
                @unlink(public_path($filePath));
            }
            $file = $request->file('file_surat');
            $filename = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
            $file->move(public_path('uploads/perizinan'), $filename);
            $filePath = 'uploads/perizinan/' . $filename;
        }

        $perizinan->update([
            'pegawai_id' => $request->input('pegawai_id'),
            'jenis_izin' => $request->input('jenis_izin'),
            'tanggal_mulai' => $request->input('tanggal_mulai'),
            'tanggal_selesai' => $request->input('tanggal_selesai'),
            'keterangan' => $request->input('keterangan'),
            'file_surat' => $filePath,
        ]);

        // 2. Terapkan presensi baru berdasarkan izin yang diperbarui
        $this->attendanceService->syncLeaveToAttendance($perizinan);

        AuditLogger::log('update_perizinan', 'Perizinan', $perizinan->id, $oldData, $perizinan->toArray());

        return back()->with('success', 'Data perizinan berhasil diperbarui dan status presensi disesuaikan.');
    }

    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isOperator()) {
            return back()->with('error', 'Hanya Admin dan Operator yang berhak menghapus riwayat perizinan.');
        }

        $perizinan = Perizinan::with('pegawai')->findOrFail($id);
        $oldData = $perizinan->toArray();

        // 1. Memulihkan status presensi harian aparatur pada rentang tanggal izin tersebut
        $this->attendanceService->removeLeaveFromAttendance($perizinan);

        // 2. Hapus berkas lampiran jika tersimpan di disk
        if (!empty($perizinan->file_surat) && file_exists(public_path($perizinan->file_surat))) {
            @unlink(public_path($perizinan->file_surat));
        }

        // 3. Hapus data perizinan dari database
        $perizinan->delete();

        // 4. Catat ke Audit Log
        AuditLogger::log('hapus_perizinan', 'Perizinan', $id, $oldData, null);

        return back()->with('success', 'Data riwayat perizinan berhasil dihapus dan status presensi dipulihkan.');
    }
}
