@extends('layouts.app')

@section('title', 'Data Presensi Harian Aparatur')

@section('content')
<div x-data="presensiHarian()" x-init="startAutoRefresh()">

    <!-- Banner Aturan Presensi -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 mb-5 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-slate-700">
        <div class="flex items-start sm:items-center space-x-3">
            <div class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h4 class="font-bold text-sm text-slate-800">Aturan Jam Kerja & Presensi</h4>
                <p class="text-xs text-slate-500 mt-0.5">
                    <span class="font-semibold text-slate-700">Jam Masuk:</span> Maks. 08:00 WIB (Tepat Waktu) &nbsp;•&nbsp;
                    <span class="font-semibold text-slate-700">Jam Pulang:</span> Mulai 16:00 WIB &nbsp;•&nbsp;
                    <span class="text-slate-400">Pulang sebelum 16:00 WIB wajib memiliki perizinan resmi.</span>
                </p>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white border border-slate-200 rounded-xl p-4 mb-6 shadow-sm flex flex-col md:flex-row items-stretch md:items-center justify-between gap-4">
        <form action="{{ route('presensi.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <!-- Pilih Tanggal -->
            <div class="flex items-center space-x-2">
                <input type="date" id="tanggal" name="tanggal" value="{{ $tanggal }}" x-model="tanggal" @change="fetchData()"
                    class="px-3 py-2 rounded-xl bg-white border border-slate-300 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-red-600/30 focus:border-red-600 font-medium shadow-sm transition">
            </div>

            <!-- Search Input Lebih Mencolok & Icon Search -->
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" id="search" name="search" value="{{ $search }}" x-model="search" @input.debounce.300ms="fetchData()" placeholder="Cari aparatur / jabatan..."
                    class="w-full pl-9 pr-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-600/30 focus:border-red-600 bg-white font-medium shadow-sm transition placeholder-slate-400">
            </div>

            <button type="submit" class="px-4 py-2 bg-red-700 hover:bg-red-800 text-white font-bold rounded-xl text-xs sm:text-sm transition shadow-sm">
                Tampilkan
            </button>
        </form>

        <!-- Status Konektivitas Mesin -->
        <div class="flex items-center space-x-2 text-xs font-medium px-3.5 py-2 rounded-xl border transition-all duration-300 shadow-sm flex-shrink-0 self-start sm:self-auto"
            :class="deviceOnline ? 'bg-emerald-50 text-emerald-800 border-emerald-200' : 'bg-slate-100 text-slate-500 border-slate-200'">
            <span class="relative flex h-2.5 w-2.5">
                <span x-show="deviceOnline" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5" :class="deviceOnline ? 'bg-emerald-500' : 'bg-slate-400'"></span>
            </span>
            <span class="font-semibold" x-text="deviceOnline ? 'Terhubung' : 'Tidak Terhubung'">
                {{ $isDeviceOnline ? 'Terhubung' : 'Tidak Terhubung' }}
            </span>
            <span class="text-[11px] font-mono pl-1.5 border-l"
                  :class="deviceOnline ? 'border-emerald-300 text-emerald-700' : 'border-slate-300 text-slate-400'"
                  x-text="deviceOnline ? lastUpdated : '--:--:--'">
                {{ $isDeviceOnline ? date('H:i:s') : '--:--:--' }}
            </span>
        </div>
    </div>

    <!-- Presensi Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-600 uppercase tracking-wider bg-slate-100 font-bold">
                        <th class="p-4 w-12 text-center">No</th>
                        <th class="p-4">ID Mesin</th>
                        <th class="p-4">Nama Aparatur Desa</th>
                        <th class="p-4">Jabatan</th>
                        <th class="p-4">Jam Masuk</th>
                        <th class="p-4">Jam Pulang</th>
                        <th class="p-4">Status Kehadiran</th>
                        <th class="p-4">Keterangan</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <template x-for="(row, idx) in presensiList" :key="row.id">
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-4 text-center text-slate-400 font-mono" x-text="idx + 1"></td>
                            <td class="p-4 font-mono font-bold text-slate-700" x-text="row.id_mesin"></td>
                            <td class="p-4">
                                <div class="font-bold text-slate-900 text-sm" x-text="row.nama"></div>
                            </td>
                            <td class="p-4 text-slate-600" x-text="row.jabatan"></td>
                            <td class="p-4">
                                <div class="font-mono font-bold text-slate-800 text-sm" x-text="row.jam_masuk"></div>
                                <template x-if="row.jam_masuk && row.jam_masuk !== '-'">
                                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase mt-1"
                                        :class="row.tipe_masuk === 'rfid' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200'">
                                        <span x-text="row.tipe_masuk === 'rfid' ? 'RFID' : 'Biometrik'"></span>
                                    </span>
                                </template>
                            </td>
                            <td class="p-4">
                                <div class="font-mono font-bold text-slate-800 text-sm" x-text="row.jam_pulang"></div>
                                <template x-if="row.jam_pulang && row.jam_pulang !== '-'">
                                    <span class="inline-flex items-center space-x-1 px-2 py-0.5 rounded text-[10px] font-bold uppercase mt-1"
                                        :class="row.tipe_pulang === 'rfid' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200'">
                                        <span x-text="row.tipe_pulang === 'rfid' ? 'RFID' : 'Biometrik'"></span>
                                    </span>
                                </template>
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase tracking-wider"
                                    :class="{
                                        'bg-emerald-100 text-emerald-800 border border-emerald-300': row.status === 'hadir',
                                        'bg-red-100 text-red-800 border border-red-300': row.status === 'terlambat',
                                        'bg-amber-100 text-amber-800 border border-amber-300': row.status === 'sakit',
                                        'bg-blue-100 text-blue-800 border border-blue-300': row.status === 'izin' || row.status === 'dispen',
                                        'bg-purple-100 text-purple-800 border border-purple-300': row.status === 'skorsing',
                                        'bg-slate-100 text-slate-700 border border-slate-300': row.status === 'tidak_hadir'
                                    }"
                                    x-text="row.status">
                                </span>
                            </td>
                            <td class="p-4 text-slate-600" x-text="row.keterangan"></td>
                            <td class="p-4 text-center">
                                @if(Auth::user()->hasRole(['admin', 'operator']))
                                <button @click="editModal = true; selectedRecord = { id: row.id, nama: row.nama, status: row.status, keterangan: row.keterangan }"
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-lg text-xs transition border border-slate-300">
                                    Edit Status
                                </button>
                                @else
                                <span class="text-slate-300">-</span>
                                @endif
                            </td>
                        </tr>
                    </template>

                    @if($presensiList->isEmpty())
                    <tr x-show="presensiList.length === 0">
                        <td colspan="9" class="p-8 text-center text-slate-400">
                            Tidak ada record presensi ditemukan pada tanggal ini.
                        </td>
                    </tr>
                    @endif
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
                        <option value="skorsing">Skorsing (Hukuman Disiplin)</option>
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

@push('scripts')
<script>
    function presensiHarian() {
        return {
            editModal: false,
            selectedRecord: {},
            tanggal: '{{ $tanggal }}',
            search: '{{ $search }}',
            lastUpdated: '{{ $isDeviceOnline ? date('H:i:s') : '--:--:--' }}',
            deviceOnline: {{ $isDeviceOnline ? 'true' : 'false' }},
            presensiList: @json($presensiFormattedList),

            fetchData() {
                const params = new URLSearchParams({
                    tanggal: this.tanggal,
                    search: this.search
                });
                fetch('{{ route('presensi.harian_realtime') }}?' + params.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(res => {
                        if (!res.ok) throw new Error('Network response not ok');
                        return res.json();
                    })
                    .then(data => {
                        if (data.status === 'success') {
                            this.deviceOnline = !!data.device_online;
                            this.presensiList = data.data;
                            if (this.deviceOnline) {
                                this.lastUpdated = data.timestamp;
                            }
                        }
                    })
                    .catch(err => {
                        console.error('Presensi realtime error:', err);
                        this.deviceOnline = false;
                    });
            },

            startAutoRefresh() {
                // Realtime Auto Fetch Every 1.5 Seconds
                setInterval(() => {
                    this.fetchData();
                }, 1500);
            }
        }
    }
</script>
@endpush
