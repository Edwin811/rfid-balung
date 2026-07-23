<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Presensi;
use App\Services\AuditLogger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PresensiController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', Carbon::today()->toDateString());
        $search = $request->input('search');

        $pegawaiList = Pegawai::where('is_active', true)->orderBy('nama_lengkap')->get();

        $query = Presensi::with('pegawai', 'updatedBy')
            ->whereDate('tanggal', $tanggal);

        if ($search) {
            $query->whereHas('pegawai', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('jabatan', 'like', "%{$search}%");
            });
        }

        $presensiList = $query->get();

        return view('presensi.index', compact('tanggal', 'search', 'pegawaiList', 'presensiList'));
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->isAdmin() && !$user->isOperator()) {
            return back()->with('error', 'Hanya Admin dan Operator yang berhak mengubah data presensi.');
        }

        $request->validate([
            'status' => ['required', 'in:hadir,terlambat,tidak_hadir,sakit,izin,dispen'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $presensi = Presensi::findOrFail($id);
        $dataLama = $presensi->toArray();

        $presensi->status = $request->input('status');
        $presensi->keterangan = $request->input('keterangan');
        $presensi->updated_by = Auth::id();
        $presensi->save();

        AuditLogger::log('update_presensi', 'Presensi', $presensi->id, $dataLama, $presensi->toArray());

        return back()->with('success', 'Data presensi berhasil diperbarui.');
    }
}
