@extends('layouts.app')

@section('title', 'Audit Log Activity System')

@section('content')
<div>
    <!-- Header & Search -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 mb-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-slate-800 text-base">Audit Log System (Append-Only)</h3>
            <p class="text-xs text-slate-500">Log permanen seluruh aktivitas pengguna (Login, Edit Presensi, Perizinan, Export, dan Backup).</p>
        </div>

        <form action="{{ route('audit_log.index') }}" method="GET" class="w-full md:w-auto">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari aksi / user / IP..."
                class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
        </form>
    </div>

    <!-- Audit Logs Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-600 uppercase tracking-wider bg-slate-100 font-bold">
                        <th class="p-4">Waktu</th>
                        <th class="p-4">Pengguna</th>
                        <th class="p-4">Aksi</th>
                        <th class="p-4">Model & ID</th>
                        <th class="p-4">IP Address</th>
                        <th class="p-4">Detail Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($logs as $log)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 text-slate-500 font-mono">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800 text-sm">{{ $log->user->name ?? 'System' }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">{{ $log->user->username ?? '-' }} ({{ $log->user->role ?? 'system' }})</div>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase tracking-wider bg-slate-900 text-white">
                                {{ $log->aksi }}
                            </span>
                        </td>
                        <td class="p-4 font-mono text-slate-700 font-bold">
                            {{ $log->model ? $log->model . ' #' . $log->model_id : '-' }}
                        </td>
                        <td class="p-4 font-mono text-slate-500">
                            {{ $log->ip_address ?? '-' }}
                        </td>
                        <td class="p-4 text-slate-600">
                            @if($log->data_baru)
                            <details class="cursor-pointer">
                                <summary class="text-xs font-bold text-red-700 hover:underline">Lihat JSON Data</summary>
                                <pre class="mt-2 p-2 bg-slate-900 text-slate-200 rounded text-[10px] overflow-x-auto max-w-xs font-mono">{{ json_encode($log->data_baru, JSON_PRETTY_PRINT) }}</pre>
                            </details>
                            @else
                            <span class="text-slate-300">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-slate-400">
                            Belum ada aktivitas audit log tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
