@extends('layouts.app')

@section('title', 'Laporan & Rekapitulasi Presensi')

@section('content')
<div>

    <!-- Filter & Action Bar -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 mb-6 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <form action="{{ route('laporan.index') }}" method="GET" class="flex items-center space-x-3 w-full md:w-auto">
            <div>
                <label for="bulan" class="block text-[11px] font-semibold uppercase text-slate-400 mb-1">Bulan</label>
                <select id="bulan" name="bulan" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @for($m=1; $m<=12; $m++)
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::createFromDate(2026, $m, 1)->locale('id')->isoFormat('MMMM') }}
                    </option>
                    @endfor
                </select>
            </div>

            <div>
                <label for="tahun" class="block text-[11px] font-semibold uppercase text-slate-400 mb-1">Tahun</label>
                <select id="tahun" name="tahun" class="px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    @for($y=2024; $y<=2030; $y++)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            <div class="self-end">
                <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-white font-semibold rounded-xl text-sm transition">
                    Tampilkan Rekap
                </button>
            </div>
        </form>

        <!-- Export Buttons -->
        <div class="flex items-center space-x-3 w-full md:w-auto justify-end">
            <a href="{{ route('laporan.excel', ['bulan' => $bulan, 'tahun' => $tahun]) }}"
                class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl text-xs flex items-center space-x-2 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Export Excel (.xlsx)</span>
            </a>

            <a href="{{ route('laporan.pdf', ['bulan' => $bulan, 'tahun' => $tahun]) }}" target="_blank"
                class="px-4 py-2.5 bg-rose-600 hover:bg-rose-500 text-white font-semibold rounded-xl text-xs flex items-center space-x-2 transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                <span>Export PDF (.pdf)</span>
            </a>
        </div>
    </div>

    <!-- Rekapitulasi Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-base">
                Rekap Kehadiran Aparatur — {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->isoFormat('MMMM YYYY') }}
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase tracking-wider bg-slate-50">
                        <th class="p-4">No</th>
                        <th class="p-4">Aparatur Desa</th>
                        <th class="p-4 text-center">Hadir Tepat</th>
                        <th class="p-4 text-center">Terlambat</th>
                        <th class="p-4 text-center">Sakit</th>
                        <th class="p-4 text-center">Izin</th>
                        <th class="p-4 text-center">Dispen</th>
                        <th class="p-4 text-center">Tidak Hadir</th>
                        <th class="p-4 text-center">Total Hari Kerja</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($rekapPegawai as $index => $r)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 text-slate-400 font-mono">{{ $index + 1 }}</td>
                        <td class="p-4">
                            <div class="font-bold text-slate-800">{{ $r['pegawai']->nama_lengkap }}</div>
                            <div class="text-[11px] text-slate-400">ID Mesin: {{ $r['pegawai']->id_mesin }} | {{ $r['pegawai']->jabatan }}</div>
                        </td>
                        <td class="p-4 text-center font-bold text-emerald-600 font-mono">{{ $r['hadir'] }}</td>
                        <td class="p-4 text-center font-bold text-rose-600 font-mono">{{ $r['terlambat'] }}</td>
                        <td class="p-4 text-center font-bold text-amber-600 font-mono">{{ $r['sakit'] }}</td>
                        <td class="p-4 text-center font-bold text-blue-600 font-mono">{{ $r['izin'] }}</td>
                        <td class="p-4 text-center font-bold text-indigo-600 font-mono">{{ $r['dispen'] }}</td>
                        <td class="p-4 text-center font-bold text-slate-500 font-mono">{{ $r['tidak_hadir'] }}</td>
                        <td class="p-4 text-center font-bold text-slate-800 font-mono">{{ $r['total_kerja'] }} Hari</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
