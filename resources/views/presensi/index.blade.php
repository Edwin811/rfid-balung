@extends('layouts.app')

@section('title', 'Data Presensi Harian Aparatur')

@section('content')
<div x-data="{ editModal: false, selectedRecord: {} }">

    <!-- Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 mb-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="{{ route('presensi.index') }}" method="GET" class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
            <div>
                <label for="tanggal" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Pilih Tanggal</label>
                <input type="date" id="tanggal" name="tanggal" value="{{ $tanggal }}"
                    class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
            </div>

            <div>
                <label for="search" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Cari Nama / Jabatan</label>
                <input type="text" id="search" name="search" value="{{ $search }}" placeholder="Nama aparatur desa..."
                    class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
            </div>

            <div class="sm:self-end">
                <button type="submit" class="px-5 py-2 bg-red-700 hover:bg-red-800 text-white font-bold rounded-xl text-sm transition shadow">
                    Tampilkan Presensi
                </button>
            </div>
        </form>

        <div class="text-xs text-slate-500 text-right">
            Menampilkan data tanggal: <strong class="text-slate-900 font-bold">{{ \Carbon\Carbon::parse($tanggal)->locale('id')->isoFormat('D MMMM YYYY') }}</strong>
        </div>
    </div>

    <!-- Presensi Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-600 uppercase tracking-wider bg-slate-100 font-bold">
                        <th class="p-4">No</th>
                        <th class="p-4">Aparatur Desa</th>
                        <th class="p-4">Jam Masuk</th>
                        <th class="p-4">Jam Pulang</th>
                        <th class="p-4">Status Kehadiran</th>
                        <th class="p-4">Keterangan</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($presensiList as $index => $row)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 text-slate-400 font-mono">{{ $index + 1 }}</td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800 text-sm">{{ $row->pegawai->nama_lengkap ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500 font-medium">ID Mesin: {{ $row->pegawai->id_mesin ?? '-' }} | {{ $row->pegawai->jabatan ?? '-' }}</div>
                        </td>
                        <td class="p-4 font-mono font-bold text-slate-700">{{ $row->jam_masuk ?? '-' }}</td>
                        <td class="p-4 font-mono font-bold text-slate-700">{{ $row->jam_pulang ?? '-' }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase tracking-wider
                                @if($row->status === 'hadir') bg-emerald-100 text-emerald-800 border border-emerald-300
                                @elseif($row->status === 'terlambat') bg-red-100 text-red-800 border border-red-300
                                @elseif($row->status === 'sakit') bg-amber-100 text-amber-800 border border-amber-300
                                @elseif(in_array($row->status, ['izin', 'dispen'])) bg-blue-100 text-blue-800 border border-blue-300
                                @else bg-slate-100 text-slate-700 border border-slate-300 @endif">
                                {{ $row->status }}
                            </span>
                        </td>
                        <td class="p-4 text-slate-600">{{ $row->keterangan ?? '-' }}</td>
                        <td class="p-4 text-center">
                            @if(Auth::user()->hasRole(['admin', 'operator']))
                            <button @click="editModal = true; selectedRecord = { id: {{ $row->id }}, nama: '{{ addslashes($row->pegawai->nama_lengkap ?? '') }}', status: '{{ $row->status }}', keterangan: '{{ addslashes($row->keterangan ?? '') }}' }"
                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-lg text-xs transition border border-slate-300">
                                Edit Status
                            </button>
                            @else
                            <span class="text-slate-300">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-slate-400">
                            Tidak ada record presensi ditemukan pada tanggal ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Status Modal -->
    <div x-show="editModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-slate-200" @click.away="editModal = false">
            <div class="border-b border-slate-100 pb-3 mb-4">
                <h3 class="font-bold text-slate-800 text-base">Edit Status Presensi Manual</h3>
                <p class="text-xs text-slate-500">Aparatur: <strong x-text="selectedRecord.nama" class="text-red-700"></strong></p>
            </div>

            <form :action="'/presensi/' + selectedRecord.id" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status Kehadiran</label>
                    <select name="status" x-model="selectedRecord.status" required
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                        <option value="hadir">Hadir (Tepat Waktu)</option>
                        <option value="terlambat">Terlambat</option>
                        <option value="tidak_hadir">Tidak Hadir (Alfa)</option>
                        <option value="sakit">Sakit</option>
                        <option value="izin">Izin</option>
                        <option value="dispen">Dispensasi Tugas</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Keterangan / Alasan</label>
                    <textarea name="keterangan" x-model="selectedRecord.keterangan" rows="3"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium"
                        placeholder="Masukkan keterangan tambahan..."></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="editModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-900 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white font-bold text-xs rounded-xl shadow transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
