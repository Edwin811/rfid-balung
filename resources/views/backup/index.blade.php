@extends('layouts.app')

@section('title', 'Backup Database System')

@section('content')
<div class="max-w-4xl mx-auto">

    <!-- Action Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-6 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-slate-800 text-lg mb-1">Backup Database Manual</h3>
            <p class="text-xs text-slate-500">Membuat salinan cadangan lengkap database MySQL dalam format <code>.sql</code> di folder lokal server.</p>
        </div>

        <form action="{{ route('backup.store') }}" method="POST">
            @csrf
            <button type="submit" class="px-5 py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl text-sm transition shadow-lg shadow-emerald-600/20 flex items-center space-x-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                <span>Backup Sekarang</span>
            </button>
        </form>
    </div>

    <!-- Backup Files Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h4 class="font-bold text-slate-800 text-base">Riwayat File Backup Database</h4>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase tracking-wider bg-slate-50">
                        <th class="p-4">Nama File</th>
                        <th class="p-4">Ukuran File</th>
                        <th class="p-4">Tanggal Pembuatan</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($backups as $b)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-mono font-bold text-slate-800">
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
                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition inline-flex items-center space-x-1">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                                <span>Unduh</span>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400">
                            Belum ada file backup database tersedia. Klik "Backup Sekarang" untuk membuat backup pertama.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
