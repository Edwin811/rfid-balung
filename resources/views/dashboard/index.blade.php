@extends('layouts.app')

@section('title', 'Beranda Dashboard Presensi')

@section('content')
<div x-data="dashboardRealtime()" x-init="startAutoRefresh()">

    <!-- Late Notification Toast Banner (Realtime Audio & Visual Popup) -->
    <template x-if="lateToast">
        <div class="fixed top-20 right-6 z-[99999] max-w-md w-full bg-red-600 text-white rounded-2xl shadow-2xl p-4 border-2 border-white flex items-start space-x-3 transition-all duration-300">
            <div class="p-2.5 bg-white/20 rounded-xl shrink-0">
                <svg class="w-7 h-7 text-amber-300 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            </div>
            <div class="flex-1">
                <div class="font-extrabold text-xs uppercase tracking-wider text-amber-200">Peringatan Presensi Terlambat</div>
                <div class="font-bold text-base mt-0.5 text-white" x-text="lateToast.nama"></div>
                <div class="text-xs text-red-100 font-medium mt-0.5" x-text="lateToast.jabatan + ' • Scan Masuk: ' + lateToast.jam_masuk + ' WIB'"></div>
            </div>
            <button @click="lateToast = null" class="text-white/80 hover:text-white p-1 rounded transition" title="Tutup">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </template>

    <!-- Alert Un-Checked In Employees (> 08:00) -->
    @if($showLateAlert)
    <div x-data="{ showLateAlertCard: true }" x-show="showLateAlertCard" x-transition class="mb-6 p-4 rounded-xl bg-amber-50 border-l-4 border-amber-500 text-amber-900 shadow-sm flex items-start justify-between space-x-3">
        <div class="flex items-start space-x-3 flex-1">
            <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div class="flex-1">
                <h4 class="font-bold text-sm">Pemberitahuan Presensi Masuk (> 08:00 WIB)</h4>
                <p class="text-xs text-amber-800 mt-0.5">
                    Terdapat <strong>{{ $unCheckedInPegawai->count() }} aparatur desa</strong> yang belum melakukan presensi masuk hingga jam 08:00 WIB:
                </p>
                <div class="mt-2 flex flex-wrap gap-1.5">
                    @foreach($unCheckedInPegawai as $p)
                    <span class="inline-block px-2.5 py-1 bg-amber-100 border border-amber-300 rounded text-[11px] font-bold text-amber-900">
                        {{ $p->nama_lengkap }} ({{ $p->jabatan }})
                    </span>
                    @endforeach
                </div>
            </div>
        </div>
        <button @click="showLateAlertCard = false" type="button" class="text-amber-500 hover:text-amber-800 p-1 rounded-lg hover:bg-amber-100 transition flex-shrink-0" title="Tutup Pemberitahuan">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
    @endif

    <!-- 5 Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <!-- Hadir -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm border-t-4 border-t-emerald-600">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-600">Hadir Tepat Waktu</span>
                <span class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="counts.hadir">{{ $countHadir }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Presensi &le; 08:00 WIB</p>
        </div>

        <!-- Terlambat -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm border-t-4 border-t-red-600">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-600">Terlambat</span>
                <span class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-red-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="counts.terlambat">{{ $countTerlambat }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Presensi &gt; 08:00 WIB</p>
        </div>

        <!-- Sakit -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm border-t-4 border-t-amber-500">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-600">Sakit</span>
                <span class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="counts.sakit">{{ $countSakit }}</p>
        </div>

        <!-- Izin / Dispen -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm border-t-4 border-t-blue-600">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-600">Izin / Dispen</span>
                <span class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 9v7.5" /></svg>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="counts.izin + counts.dispen">{{ $countIzin + $countDispen }}</p>
        </div>

        <!-- Belum Presensi -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm border-t-4 border-t-slate-400">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold text-slate-600">Belum Presensi</span>
                <span class="w-8 h-8 rounded-lg border border-slate-200 flex items-center justify-center text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="counts.tidak_hadir">{{ $countTidakHadir }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Real-time Attendance Table (2 Columns) -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Daftar Presensi Aparatur Desa yang Tercatat Hari Ini</h3>
                </div>
                <div class="flex items-center space-x-2.5">
                    <!-- Search Input Lebih Mencolok & Icon Search -->
                    <div class="relative w-full sm:w-64">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <input type="text" x-model="searchAttendance" placeholder="Cari aparatur / jabatan..."
                            class="w-full pl-9 pr-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-600/30 focus:border-red-600 bg-white font-medium shadow-sm transition placeholder-slate-400">
                    </div>

                    <!-- Status Konektivitas Mesin -->
                    <div class="flex items-center space-x-2 text-xs font-medium px-3.5 py-2 rounded-xl border transition-all duration-300 shadow-sm flex-shrink-0"
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
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-600 uppercase tracking-wider bg-slate-100 font-bold">
                            <th class="p-3">Nama Aparatur Desa</th>
                            <th class="p-3">Jam Masuk</th>
                            <th class="p-3">Jam Pulang</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-for="row in filteredAttendance" :key="row.id">
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3">
                                    <div class="font-bold text-slate-800" x-text="row.nama"></div>
                                    <div class="text-[11px] text-slate-500" x-text="row.jabatan"></div>
                                </td>
                                <td class="p-3">
                                    <div class="font-mono text-slate-800 font-bold" x-text="row.jam_masuk"></div>
                                    <template x-if="row.jam_masuk && row.jam_masuk !== '-'">
                                        <span class="inline-flex items-center space-x-1 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase mt-0.5"
                                            :class="row.tipe_masuk === 'rfid' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200'">
                                            <span x-text="row.tipe_masuk === 'rfid' ? 'RFID' : 'Biometrik'"></span>
                                        </span>
                                    </template>
                                </td>
                                <td class="p-3">
                                    <div class="font-mono text-slate-800 font-bold" x-text="row.jam_pulang"></div>
                                    <template x-if="row.jam_pulang && row.jam_pulang !== '-'">
                                        <span class="inline-flex items-center space-x-1 px-1.5 py-0.5 rounded text-[9px] font-bold uppercase mt-0.5"
                                            :class="row.tipe_pulang === 'rfid' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200'">
                                            <span x-text="row.tipe_pulang === 'rfid' ? 'RFID' : 'Biometrik'"></span>
                                        </span>
                                    </template>
                                </td>
                                <td class="p-3">
                                    <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase tracking-wider"
                                        :class="{
                                            'bg-emerald-100 text-emerald-800 border border-emerald-300': row.status === 'hadir',
                                            'bg-red-100 text-red-800 border border-red-300': row.status === 'terlambat',
                                            'bg-amber-100 text-amber-800 border border-amber-300': row.status === 'sakit',
                                            'bg-blue-100 text-blue-800 border border-blue-300': row.status === 'izin' || row.status === 'dispen',
                                            'bg-slate-100 text-slate-700 border border-slate-300': row.status === 'tidak_hadir'
                                        }"
                                        x-text="row.status">
                                    </span>
                                </td>
                                <td class="p-3 text-slate-600" x-text="row.keterangan"></td>
                            </tr>
                        </template>

                        <tr x-show="filteredAttendance.length === 0">
                            <td colspan="5" class="p-8 text-center text-slate-400 font-medium">
                                Tidak ada data presensi yang sesuai pencarian.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 7-Day Chart (1 Column) -->
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm">
            <div>
                <h3 class="font-bold text-slate-800 text-sm sm:text-base mb-1">Grafik Kehadiran (7 Hari)</h3>
                <p class="text-xs text-slate-500 mb-4">Tren kehadiran aparatur Desa Balunglor seminggu terakhir</p>
                <div class="relative h-64">
                    <canvas id="attendance7DayChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Aparatur Belum Presensi (Hari Ini) -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center space-x-2.5">
                <h3 class="font-bold text-slate-800 text-sm">Daftar Aparatur yang Belum Melakukan Presensi Hari Ini</h3>
                <span class="px-2.5 py-0.5 rounded-full bg-slate-700 text-white font-extrabold text-xs" x-text="unCheckedList.length"></span>
            </div>
            <div class="flex items-center">
                <!-- Search Input Lebih Mencolok & Icon Search -->
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input type="text" x-model="searchUnchecked" placeholder="Cari nama / jabatan / ID..."
                        class="w-full pl-9 pr-3.5 py-2 text-xs sm:text-sm rounded-xl border border-slate-300 focus:outline-none focus:ring-2 focus:ring-red-600/30 focus:border-red-600 bg-white font-medium shadow-sm transition placeholder-slate-400">
                </div>
            </div>
        </div>

        <div class="overflow-x-auto max-h-96 overflow-y-auto">
            <table class="w-full text-left text-xs">
                <thead class="sticky top-0 bg-slate-100 border-b border-slate-200 text-slate-600 uppercase tracking-wider font-bold z-10">
                    <tr>
                        <th class="p-3 w-12 text-center">No</th>
                        <th class="p-3">ID Mesin</th>
                        <th class="p-3">Nama Aparatur Desa</th>
                        <th class="p-3">Jabatan</th>
                        <th class="p-3 text-center">Status Presensi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    <template x-for="(row, idx) in filteredUnchecked" :key="row.id">
                        <tr class="hover:bg-slate-50 transition">
                            <td class="p-3 text-center text-slate-400 font-mono" x-text="idx + 1"></td>
                            <td class="p-3 font-mono font-bold text-slate-700" x-text="row.id_mesin"></td>
                            <td class="p-3">
                                <div class="font-bold text-slate-900 text-sm" x-text="row.nama"></div>
                            </td>
                            <td class="p-3 text-slate-600" x-text="row.jabatan"></td>
                            <td class="p-3 text-center">
                                <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase tracking-wider bg-slate-100 text-slate-700 border border-slate-300">
                                    Belum Presensi
                                </span>
                            </td>
                        </tr>
                    </template>

                    <tr x-show="filteredUnchecked.length === 0">
                        <td colspan="5" class="p-8 text-center text-slate-500 font-medium bg-slate-50">
                            Tidak ada aparatur yang sesuai kriteria pencarian / seluruh aparatur telah melakukan presensi.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function dashboardRealtime() {
        return {
            lastUpdated: '{{ $isDeviceOnline ? date('H:i:s') : '--:--:--' }}',
            deviceOnline: {{ $isDeviceOnline ? 'true' : 'false' }},
            searchAttendance: '',
            searchUnchecked: '',
            counts: {
                hadir: {{ $countHadir }},
                terlambat: {{ $countTerlambat }},
                sakit: {{ $countSakit }},
                izin: {{ $countIzin }},
                dispen: {{ $countDispen }},
                tidak_hadir: {{ $countTidakHadir }}
            },
            attendanceList: @json($presensiTodayList),
            unCheckedList: @json($unCheckedInList),
            lateToast: null,
            knownLateIds: new Set((@json($presensiTodayList)).filter(i => i.status === 'terlambat').map(i => i.id)),

            get filteredAttendance() {
                if (!this.searchAttendance.trim()) return this.attendanceList;
                const q = this.searchAttendance.toLowerCase();
                return this.attendanceList.filter(item =>
                    (item.nama && item.nama.toLowerCase().includes(q)) ||
                    (item.jabatan && item.jabatan.toLowerCase().includes(q))
                );
            },

            get filteredUnchecked() {
                if (!this.searchUnchecked.trim()) return this.unCheckedList;
                const q = this.searchUnchecked.toLowerCase();
                return this.unCheckedList.filter(item =>
                    (item.nama && item.nama.toLowerCase().includes(q)) ||
                    (item.jabatan && item.jabatan.toLowerCase().includes(q)) ||
                    (item.id_mesin && String(item.id_mesin).includes(q))
                );
            },

            triggerLateAlert(item) {
                this.lateToast = item;
                if ('speechSynthesis' in window) {
                    window.speechSynthesis.cancel();
                    const text = `Peringatan! Aparatur ${item.nama}, ${item.jabatan}, absen terlambat pada pukul ${item.jam_masuk}!`;
                    const utterance = new SpeechSynthesisUtterance(text);
                    utterance.lang = 'id-ID';
                    utterance.rate = 0.95;
                    window.speechSynthesis.speak(utterance);
                }
                setTimeout(() => {
                    if (this.lateToast && this.lateToast.id === item.id) {
                        this.lateToast = null;
                    }
                }, 8000);
            },

            startAutoRefresh() {
                setInterval(() => {
                    fetch('{{ route('dashboard.realtime') }}', {
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

                                // Deteksi entri terlambat baru
                                if (data.data) {
                                    data.data.forEach(item => {
                                        if (item.status === 'terlambat' && !this.knownLateIds.has(item.id)) {
                                            this.knownLateIds.add(item.id);
                                            this.triggerLateAlert(item);
                                        }
                                    });
                                }

                                this.counts = data.counts;
                                this.attendanceList = data.data;
                                this.unCheckedList = data.unchecked || [];
                                if (this.deviceOnline) {
                                    this.lastUpdated = data.timestamp;
                                }
                            }
                        })
                        .catch(err => {
                            console.error('Auto refresh error:', err);
                            this.deviceOnline = false;
                        });
                }, 1500); // Ultra-fast 1.5-second instant UI refresh
            }
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('attendance7DayChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartData['labels']),
                datasets: [
                    {
                        label: 'Hadir',
                        data: @json($chartData['hadir']),
                        backgroundColor: '#16a34a',
                    },
                    {
                        label: 'Terlambat',
                        data: @json($chartData['terlambat']),
                        backgroundColor: '#dc2626',
                    },
                    {
                        label: 'Izin/Sakit',
                        data: @json($chartData['izin']),
                        backgroundColor: '#2563eb',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11, family: 'Plus Jakarta Sans' } } }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });
    });
</script>
@endpush
