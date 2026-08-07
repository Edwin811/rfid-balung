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
                <div class="font-extrabold text-xs uppercase tracking-wider text-amber-200">⚠️ PERINGATAN PRESENSI TERLAMBAT</div>
                <div class="font-bold text-base mt-0.5 text-white" x-text="lateToast.nama"></div>
                <div class="text-xs text-red-100 font-medium mt-0.5" x-text="lateToast.jabatan + ' • Scan Masuk: ' + lateToast.jam_masuk + ' WIB'"></div>
            </div>
            <button @click="lateToast = null" class="text-white/80 hover:text-white font-bold text-sm p-1">✕</button>
        </div>
    </template>

    <!-- Alert Un-Checked In Employees (> 08:00) -->
    @if($showLateAlert)
    <div class="mb-6 p-4 rounded-xl bg-amber-50 border-l-4 border-amber-500 text-amber-900 shadow-sm flex items-start space-x-3">
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
    @endif

    <!-- 5 Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        <!-- Hadir -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm border-t-4 border-t-emerald-600">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Hadir Tepat</span>
                <span class="p-2 rounded-lg bg-emerald-50 text-emerald-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="counts.hadir">{{ $countHadir }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Presensi &le; 08:00 WIB</p>
        </div>

        <!-- Terlambat -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm border-t-4 border-t-red-600">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Terlambat</span>
                <span class="p-2 rounded-lg bg-red-50 text-red-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="counts.terlambat">{{ $countTerlambat }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Presensi &gt; 08:00 WIB</p>
        </div>

        <!-- Sakit -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm border-t-4 border-t-amber-500">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Sakit</span>
                <span class="p-2 rounded-lg bg-amber-50 text-amber-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="counts.sakit">{{ $countSakit }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Surat Keterangan</p>
        </div>

        <!-- Izin / Dispen -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm border-t-4 border-t-blue-600">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Izin / Dispen</span>
                <span class="p-2 rounded-lg bg-blue-50 text-blue-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="counts.izin + counts.dispen">{{ $countIzin + $countDispen }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Tercatat Perizinan</p>
        </div>

        <!-- Belum / Tidak Hadir -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm border-t-4 border-t-slate-400">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Belum / Absen</span>
                <span class="p-2 rounded-lg bg-slate-100 text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="counts.tidak_hadir">{{ $countTidakHadir }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Dari {{ $totalPegawai }} Aparatur</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Real-time Attendance Table (2 Columns) -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
            <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wide">Presensi Real-Time Aparatur (Hari Ini)</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar presensi aparatur desa yang tercatat hari ini</p>
                </div>
                <div class="flex items-center space-x-2">
                    <input type="text" x-model="searchAttendance" placeholder="Cari aparatur / jabatan..."
                        class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-red-600 bg-white font-medium shadow-sm">
                    <a href="{{ route('presensi.sync_now') }}" class="px-3 py-1.5 bg-red-700 hover:bg-red-800 text-white font-bold rounded-lg text-xs flex items-center space-x-1.5 shadow transition">
                        <svg class="w-3.5 h-3.5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        <span>Sync Mesin</span>
                    </a>
                    <div class="hidden md:flex items-center space-x-2 text-xs font-medium text-slate-600 bg-white px-3 py-1.5 rounded-lg border border-slate-200">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                        <span>Sync: <strong x-text="lastUpdated">{{ date('H:i:s') }}</strong></span>
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
        <div class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wide mb-1">Grafik Kehadiran (7 Hari)</h3>
                <p class="text-xs text-slate-500 mb-4">Tren kehadiran aparatur Desa Balunglor seminggu terakhir</p>
                <div class="relative h-64">
                    <canvas id="attendance7DayChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 text-center text-[11px] text-slate-500 font-medium">
                Pemerintah Desa Balunglor &bull; Kecamatan Balung
            </div>
        </div>
    </div>

    <!-- Tabel Aparatur Belum Presensi (Hari Ini) - Card Baru -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden mb-6">
        <div class="p-4 bg-slate-50 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center space-x-2">
                    <h3 class="font-bold text-slate-800 text-sm uppercase tracking-wide">Daftar Aparatur Belum Presensi (Hari Ini)</h3>
                    <span class="px-2 py-0.5 rounded-full bg-slate-700 text-white font-extrabold text-[10px]" x-text="unCheckedList.length"></span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">Daftar aparatur yang belum mencatatkan presensi hari ini</p>
            </div>
            <div class="flex items-center space-x-2">
                <input type="text" x-model="searchUnchecked" placeholder="Cari nama / jabatan / ID..."
                    class="px-3 py-1.5 rounded-lg border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-red-600 bg-white font-medium shadow-sm">
                <div class="hidden sm:flex items-center space-x-2 text-xs font-medium text-slate-600 bg-white px-3 py-1.5 rounded-lg border border-slate-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Belum Presensi Hari Ini</span>
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
                                    Belum Absen
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
            lastUpdated: '{{ date('H:i:s') }}',
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
                    fetch('{{ route('dashboard.realtime') }}')
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
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
                                this.lastUpdated = data.timestamp;
                            }
                        })
                        .catch(err => console.error('Auto refresh error:', err));
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
