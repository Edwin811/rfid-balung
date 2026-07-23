@extends('layouts.app')

@section('title', 'Backup Database System')

@section('content')
<div class="max-w-4xl mx-auto">

    <!-- Action Card -->
    <div class="bg-white border border-slate-200 rounded-xl p-6 mb-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-slate-800 text-base mb-1">Backup Data Presensi (.SQL)</h3>
            <p class="text-xs text-slate-500">Membuat cadangan khusus data inti presensi, aparatur desa, dan perizinan tanpa menyertakan log mentah mesin.</p>
        </div>

        <div class="flex items-center space-x-3">
            <form action="{{ route('backup.clean_duplicates') }}" method="POST"
                data-confirm="Apakah Anda yakin ingin menghapus semua baris data log duplikat dari database? Data presensi resmi tidak akan terpengaruh."
                data-confirm-title="Bersihkan Sampah Log Duplikat"
                data-confirm-btn="Ya, Bersihkan Sampah"
                data-confirm-theme="warning">
                @csrf
                <button type="submit" class="px-4 py-3 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow flex items-center space-x-2">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Bersihkan Sampah Log</span>
                </button>
            </form>

            <form action="{{ route('backup.store') }}" method="POST">
                @csrf
                <button type="submit" class="px-5 py-3 bg-red-700 hover:bg-red-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow flex items-center space-x-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    <span>Backup Sekarang</span>
                </button>
            </form>
        </div>
    </div>

    <!-- Backup Files Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200">
            <h4 class="font-bold text-slate-800 text-sm uppercase tracking-wide">Riwayat File Backup Database</h4>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-600 uppercase tracking-wider bg-slate-100 font-bold">
                        <th class="p-4">Nama File Backup</th>
                        <th class="p-4">Ukuran File</th>
                        <th class="p-4">Waktu Pembuatan</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($backups as $b)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-mono font-bold text-slate-800 text-xs">
                            {{ $b['filename'] }}
                        </td>
                        <td class="p-4 font-mono text-slate-600">
                            {{ $b['size'] }}
                        </td>
                        <td class="p-4 text-slate-600">
                            {{ $b['created_at'] }}
                        </td>
                        <td class="p-4 text-center">
                            <a href="{{ route('backup.download', ['filename' => $b['filename']]) }}"
                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-lg text-xs transition inline-flex items-center space-x-1 border border-slate-300">
                                <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Unduh</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400">
                            Belum ada file backup database tersedia. Klik "Backup Sekarang" untuk membuat file backup baru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
