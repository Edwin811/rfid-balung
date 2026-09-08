@extends('layouts.app')

@section('title', 'Laporan & Rekapitulasi Presensi Lengkap')

@section('content')
<div x-data="{ mode: '{{ $jenisLaporan }}' }">

    <!-- Filter Bar Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-6 shadow-sm">
        <div class="border-b border-slate-100 pb-4 mb-5 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="font-extrabold text-slate-900 text-lg">Filter & Generator Laporan Presensi</h3>
                <p class="text-xs text-slate-500 font-medium">Filter laporan presensi berdasarkan periode bulan, rentang tanggal harian, aparatur desa, dan status kehadiran.</p>
            </div>

            <!-- Mode Selector Tabs (Hanya 2 Tab: Rekap Bulanan & Rincian Harian) -->
            <div class="flex items-center bg-slate-100 p-1 rounded-xl text-xs font-bold border border-slate-200">
                <button type="button" @click="mode = 'bulanan'" :class="mode === 'bulanan' ? 'bg-red-700 text-white shadow' : 'text-slate-600 hover:text-slate-900'" class="px-4 py-2 rounded-lg transition">
                    Rekap Bulanan
                </button>
                <button type="button" @click="mode = 'harian'" :class="mode === 'harian' ? 'bg-red-700 text-white shadow' : 'text-slate-600 hover:text-slate-900'" class="px-4 py-2 rounded-lg transition">
                    Rincian Harian / Rentang
                </button>
            </div>
        </div>

        <form action="{{ route('laporan.index') }}" method="GET" class="space-y-4">
            <input type="hidden" name="jenis_laporan" :value="mode">

            <script>
                window.pegawaiListLaporan = @json($pegawaiList);
            </script>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <!-- Filter Bulan & Tahun (Bila Mode Bulanan) -->
                <div x-show="mode === 'bulanan'">
                    <label for="bulan" class="block text-xs font-bold uppercase text-slate-600 mb-1">Bulan</label>
                    <select id="bulan" name="bulan" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                        @for($m=1; $m<=12; $m++)
                        <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromDate(2026, $m, 1)->locale('id')->isoFormat('MMMM') }}
                        </option>
                        @endfor
                    </select>
                </div>

                <div x-show="mode === 'bulanan'">
                    <label for="tahun" class="block text-xs font-bold uppercase text-slate-600 mb-1">Tahun</label>
                    <select id="tahun" name="tahun" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                        @for($y=2024; $y<=2030; $y++)
                        <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>

                <!-- Filter Tanggal Mulai & Selesai (Bila Mode Harian / Individu) -->
                <div x-show="mode !== 'bulanan'">
                    <label for="tanggal_mulai" class="block text-xs font-bold uppercase text-slate-600 mb-1">Tanggal Mulai</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ $tanggalMulai }}"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>

                <div x-show="mode !== 'bulanan'">
                    <label for="tanggal_selesai" class="block text-xs font-bold uppercase text-slate-600 mb-1">Tanggal Selesai</label>
                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ $tanggalSelesai }}"
                        class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>

                <!-- Filter Aparatur Desa Searchable Dropdown -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Filter Aparatur Desa</label>
                    <div x-data="{
                        open: false,
                        search: '',
                        selectedId: '{{ $pegawaiId ?? '' }}',
                        items: window.pegawaiListLaporan || [],

                        get selectedLabel() {
                            if (!this.selectedId) return '-- Semua Aparatur Desa --';
                            const found = this.items.find(i => i.id == this.selectedId);
                            return found ? (found.nama_lengkap + ' (' + found.jabatan + ')') : '-- Semua Aparatur Desa --';
                        },

                        get filteredItems() {
                            if (!this.search.trim()) return this.items;
                            const q = this.search.toLowerCase();
                            return this.items.filter(i =>
                                (i.nama_lengkap && i.nama_lengkap.toLowerCase().includes(q)) ||
                                (i.jabatan && i.jabatan.toLowerCase().includes(q))
                            );
                        },

                        toggle() {
                            this.open = !this.open;
                            if (this.open) {
                                this.$nextTick(() => {
                                    if (this.$refs.searchInput) this.$refs.searchInput.focus();
                                });
                            }
                        },

                        select(id) {
                            this.selectedId = id;
                            this.open = false;
                            this.search = '';
                        }
                    }" class="relative">
                        <input type="hidden" name="pegawai_id" :value="selectedId">

                        <!-- Trigger Button -->
                        <button type="button" @click="toggle()"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm bg-slate-50 text-left font-medium flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-red-600 shadow-sm transition">
                            <span x-text="selectedLabel" :class="selectedId ? 'text-slate-900 font-bold' : 'text-slate-600'"></span>
                            <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <!-- Dropdown Box (Hidden until clicked) -->
                        <div x-show="open" @click.away="open = false" x-cloak
                            class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 p-2 max-h-64 overflow-y-auto">

                            <!-- Search Input INSIDE Dropdown -->
                            <div class="sticky top-0 bg-white pb-2 border-b border-slate-100">
                                <input type="text" x-ref="searchInput" x-model="search" placeholder="Cari nama / jabatan..."
                                    class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600 font-medium bg-slate-50">
                            </div>

                            <!-- List Items -->
                            <div class="divide-y divide-slate-100 mt-1">
                                <div @click="select('')" class="p-2.5 hover:bg-slate-50 rounded-lg cursor-pointer transition text-xs font-bold text-slate-700">
                                    -- Semua Aparatur Desa --
                                </div>
                                <template x-for="item in filteredItems" :key="item.id">
                                    <div @click="select(item.id)"
                                        class="p-2.5 hover:bg-slate-50 rounded-lg cursor-pointer transition text-xs flex items-center justify-between"
                                        :class="selectedId == item.id ? 'bg-red-50 text-red-900 font-bold' : 'text-slate-800'">
                                        <div>
                                            <div class="font-bold" x-text="item.nama_lengkap"></div>
                                            <div class="text-[11px] text-slate-500 font-normal" x-text="item.jabatan"></div>
                                        </div>
                                        <span x-show="selectedId == item.id" class="text-red-700 font-bold text-xs">✓</span>
                                    </div>
                                </template>

                                <div x-show="filteredItems.length === 0" class="p-4 text-center text-xs text-slate-400">
                                    Aparatur tidak ditemukan.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Status Kehadiran (Tampil di Semua Tab) -->
                <div>
                    <label for="status" class="block text-xs font-bold uppercase text-slate-600 mb-1">Filter Status</label>
                    <select id="status" name="status" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                        <option value="">-- Semua Status --</option>
                        <option value="hadir" {{ $status == 'hadir' ? 'selected' : '' }}>Hadir Tepat</option>
                        <option value="terlambat" {{ $status == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="sakit" {{ $status == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="izin" {{ $status == 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="dispen" {{ $status == 'dispen' ? 'selected' : '' }}>Dispensasi</option>
                        <option value="skorsing" {{ $status == 'skorsing' ? 'selected' : '' }}>Skorsing</option>
                        <option value="tidak_hadir" {{ $status == 'tidak_hadir' ? 'selected' : '' }}>Tidak Hadir</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <div class="text-xs text-slate-500 font-medium">
                    Status Filter: <strong class="text-slate-800 uppercase" x-text="mode"></strong>
                </div>

                <div class="flex items-center space-x-3">
                    <button type="submit" class="px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow">
                        Terapkan Filter
                    </button>
                    @if(request()->anyFilled(['bulan', 'tahun', 'tanggal_mulai', 'tanggal_selesai', 'pegawai_id', 'status']))
                    <a href="{{ route('laporan.index') }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs uppercase tracking-wider transition border border-slate-300">
                        Reset Filter
                    </a>
                    @endif

                    <!-- Export Buttons (With All Active Query Parameters) -->
                    <a href="{{ route('laporan.excel', request()->all()) }}"
                        class="px-4 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Export Excel</span>
                    </a>

                    <a href="{{ route('laporan.pdf', request()->all()) }}" target="_blank"
                        class="px-4 py-2.5 bg-red-700 hover:bg-red-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow flex items-center space-x-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                        <span>Cetak PDF</span>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Mode 1: Table Rekap Bulanan (Kembali ke Tampilan Ringkas Website) -->
    @if($jenisLaporan === 'bulanan')
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-6">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 text-sm uppercase tracking-wide">
                Rekapitulasi Bulanan Aparatur Desa &bull; {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->locale('id')->isoFormat('MMMM YYYY') }}
            </h3>
            <span class="text-xs text-slate-500 font-medium"></span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-600 uppercase tracking-wider bg-slate-100 font-bold">
                        <th class="p-4">No</th>
                        <th class="p-4">ID Mesin</th>
                        <th class="p-4">Nama Aparatur Desa</th>
                        <th class="p-4">Jabatan</th>
                        <th class="p-4 text-center">Hadir</th>
                        <th class="p-4 text-center">Terlambat</th>
                        <th class="p-4 text-center">Sakit</th>
                        <th class="p-4 text-center">Izin</th>
                        <th class="p-4 text-center">Dispen</th>
                        <th class="p-4 text-center">Skorsing</th>
                        <th class="p-4 text-center">Tidak Hadir</th>
                        <th class="p-4 text-center">Total Hari Kerja</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($dataLaporan['items'] as $index => $r)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 text-slate-400 font-mono">{{ $index + 1 }}</td>
                        <td class="p-4 font-mono font-bold text-slate-700">{{ $r['pegawai']->id_mesin }}</td>
                        <td class="p-4 font-bold text-slate-800 text-sm">{{ $r['pegawai']->nama_lengkap }}</td>
                        <td class="p-4 text-slate-600 font-semibold">{{ $r['pegawai']->jabatan }}</td>
                        <td class="p-4 text-center font-extrabold text-emerald-700 font-mono text-sm">{{ $r['hadir'] }}</td>
                        <td class="p-4 text-center font-extrabold text-red-700 font-mono text-sm">{{ $r['terlambat'] }}</td>
                        <td class="p-4 text-center font-extrabold text-amber-700 font-mono text-sm">{{ $r['sakit'] }}</td>
                        <td class="p-4 text-center font-extrabold text-blue-700 font-mono text-sm">{{ $r['izin'] }}</td>
                        <td class="p-4 text-center font-extrabold text-indigo-700 font-mono text-sm">{{ $r['dispen'] }}</td>
                        <td class="p-4 text-center font-extrabold text-purple-700 font-mono text-sm">{{ $r['skorsing'] }}</td>
                        <td class="p-4 text-center font-extrabold text-slate-600 font-mono text-sm">{{ $r['tidak_hadir'] }}</td>
                        <td class="p-4 text-center font-extrabold text-slate-900 font-mono">{{ $r['total_kerja'] }} Hari</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Keterangan Warna Kolom & Kode Status (Persis Seperti di PDF) -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 mb-6 shadow-sm">
        <div class="text-xs font-bold text-slate-800 uppercase tracking-wide mb-2.5">
            Keterangan Warna Kolom &amp; Kode Status:
        </div>
        <div class="flex flex-wrap items-center gap-4 text-xs text-slate-700">
            <div class="flex items-center space-x-1.5">
                <span class="w-3.5 h-3.5 rounded bg-[#4ade80] border border-slate-300 inline-block flex-shrink-0"></span>
                <span><strong>HIJAU (OK / OK-OK)</strong>: Hadir Tepat Waktu</span>
            </div>
            <div class="flex items-center space-x-1.5">
                <span class="w-3.5 h-3.5 rounded bg-[#facc15] border border-slate-300 inline-block flex-shrink-0"></span>
                <span><strong>KUNING (TL1 / PSW)</strong>: Terlambat / Pulang Sebelum Waktu</span>
            </div>
            <div class="flex items-center space-x-1.5">
                <span class="w-3.5 h-3.5 rounded bg-[#38bdf8] border border-slate-300 inline-block flex-shrink-0"></span>
                <span><strong>BIRU (IT / SK / CT)</strong>: Izin Tidak Masuk / Sakit / Cuti</span>
            </div>
            <div class="flex items-center space-x-1.5">
                <span class="w-3.5 h-3.5 rounded bg-[#f87171] border border-slate-300 inline-block flex-shrink-0"></span>
                <span><strong>MERAH (TK)</strong>: Tanpa Keterangan / Tidak Hadir</span>
            </div>
            <div class="flex items-center space-x-1.5">
                <span class="w-3.5 h-3.5 rounded bg-[#e2e8f0] border border-slate-300 inline-block flex-shrink-0"></span>
                <span><strong>ABU-ABU (LIBUR)</strong>: Akhir Pekan (Sabtu &amp; Minggu)</span>
            </div>
        </div>
    </div>
    @else
    <!-- Mode 2 & 3: Rincian Harian & Individu Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-extrabold text-slate-800 text-sm uppercase tracking-wide">
                Rincian Log Presensi Harian / Per Aparatur &bull; ({{ \Carbon\Carbon::parse($tanggalMulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($tanggalSelesai)->format('d/m/Y') }})
            </h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-600 uppercase tracking-wider bg-slate-100 font-bold">
                        <th class="p-4">No</th>
                        <th class="p-4">Tanggal</th>
                        <th class="p-4">Nama Aparatur Desa</th>
                        <th class="p-4">Jabatan</th>
                        <th class="p-4 text-center">Jam Masuk</th>
                        <th class="p-4 text-center">Jam Pulang</th>
                        <th class="p-4 text-center">Status</th>
                        <th class="p-4">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($dataLaporan['items'] as $index => $row)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 text-slate-400 font-mono">{{ $index + 1 }}</td>
                        <td class="p-4 font-mono font-bold text-slate-700">{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y') }}</td>
                        <td class="p-4 font-bold text-slate-800 text-sm">{{ $row->pegawai->nama_lengkap ?? '-' }}</td>
                        <td class="p-4 text-slate-600 font-semibold">{{ $row->pegawai->jabatan ?? '-' }}</td>
                        <td class="p-4 text-center font-mono font-extrabold text-slate-800">{{ $row->jam_masuk ?? '-' }}</td>
                        <td class="p-4 text-center font-mono font-extrabold text-slate-800">{{ $row->jam_pulang ?? '-' }}</td>
                        <td class="p-4 text-center">
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase tracking-wider
                                @if($row->status === 'hadir') bg-emerald-100 text-emerald-800 border border-emerald-300
                                @elseif($row->status === 'terlambat') bg-red-100 text-red-800 border border-red-300
                                @elseif($row->status === 'sakit') bg-amber-100 text-amber-800 border border-amber-300
                                @elseif(in_array($row->status, ['izin', 'dispen'])) bg-blue-100 text-blue-800 border border-blue-300
                                @elseif($row->status === 'skorsing') bg-purple-100 text-purple-800 border border-purple-300
                                @else bg-slate-100 text-slate-700 border border-slate-300 @endif">
                                {{ $row->status }}
                            </span>
                        </td>
                        <td class="p-4 text-slate-600">{{ $row->keterangan ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-400">
                            Tidak ada record presensi ditemukan sesuai filter yang dipilih.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>
@endsection
