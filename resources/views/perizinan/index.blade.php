@extends('layouts.app')

@section('title', 'Kelola Perizinan Aparatur Desa')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Form Input Perizinan (1 Column) -->
    @if(Auth::user()->hasRole(['admin', 'operator']))
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm h-fit">
        <h3 class="font-bold text-slate-800 text-base mb-1">Tambah Data Perizinan</h3>
        <p class="text-xs text-slate-500 mb-5">Status presensi otomatis diperbarui sesuai tanggal perizinan yang diinputkan.</p>

        <form action="{{ route('perizinan.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label for="pegawai_id" class="block text-xs font-bold uppercase text-slate-600 mb-1">Aparatur Desa</label>
                <select id="pegawai_id" name="pegawai_id" required
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                    <option value="">-- Pilih Aparatur --</option>
                    @foreach($pegawaiList as $p)
                    <option value="{{ $p->id }}">{{ $p->nama_lengkap }} ({{ $p->jabatan }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="jenis_izin" class="block text-xs font-bold uppercase text-slate-600 mb-1">Jenis Perizinan</label>
                <select id="jenis_izin" name="jenis_izin" required
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                    <option value="sakit">Sakit (Surat Dokter)</option>
                    <option value="izin">Izin Kepentingan Pribadi</option>
                    <option value="dispen">Dispensasi Tugas Luar</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="tanggal_mulai" class="block text-xs font-bold uppercase text-slate-600 mb-1">Tanggal Mulai</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ date('Y-m-d') }}" required
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>
                <div>
                    <label for="tanggal_selesai" class="block text-xs font-bold uppercase text-slate-600 mb-1">Tanggal Selesai</label>
                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ date('Y-m-d') }}" required
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>
            </div>

            <div>
                <label for="keterangan" class="block text-xs font-bold uppercase text-slate-600 mb-1">Keterangan / Alasan</label>
                <textarea id="keterangan" name="keterangan" rows="3"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium"
                    placeholder="Contoh: Mengikuti dinas luar di kantor kecamatan"></textarea>
            </div>

            <button type="submit" class="w-full py-3 bg-red-700 hover:bg-red-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow">
                Simpan & Sync Presensi
            </button>
        </form>
    </div>
    @endif

    <!-- Riwayat Perizinan (2 Columns) -->
    <div class="{{ Auth::user()->hasRole(['admin', 'operator']) ? 'lg:col-span-2' : 'lg:col-span-3' }} bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        <h3 class="font-bold text-slate-800 text-base mb-1">Riwayat Perizinan Aparatur</h3>
        <p class="text-xs text-slate-500 mb-4">Daftar riwayat izin yang telah tersimpan di sistem</p>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-600 uppercase tracking-wider bg-slate-100 font-bold">
                        <th class="p-3">Aparatur</th>
                        <th class="p-3">Jenis Izin</th>
                        <th class="p-3">Periode Tanggal</th>
                        <th class="p-3">Keterangan</th>
                        <th class="p-3">Input By</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($perizinanList as $p)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-3">
                            <div class="font-bold text-slate-800 text-sm">{{ $p->pegawai->nama_lengkap ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500 font-medium">{{ $p->pegawai->jabatan ?? '-' }}</div>
                        </td>
                        <td class="p-3">
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase tracking-wider
                                @if($p->jenis_izin === 'sakit') bg-amber-100 text-amber-800 border border-amber-300
                                @elseif($p->jenis_izin === 'izin') bg-blue-100 text-blue-800 border border-blue-300
                                @else bg-indigo-100 text-indigo-800 border border-indigo-300 @endif">
                                {{ $p->jenis_izin }}
                            </span>
                        </td>
                        <td class="p-3 text-slate-700 font-mono">
                            {{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($p->tanggal_selesai)->format('d/m/Y') }}
                        </td>
                        <td class="p-3 text-slate-600">{{ $p->keterangan ?? '-' }}</td>
                        <td class="p-3 text-slate-400 font-mono">{{ $p->pembuat->name ?? 'System' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-slate-400">
                            Belum ada riwayat perizinan tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $perizinanList->links() }}
        </div>
    </div>

</div>
@endsection
