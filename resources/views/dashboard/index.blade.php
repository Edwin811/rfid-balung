@extends('layouts.app')

@section('title', 'Dashboard Monitoring Presensi')

@section('content')
<div x-data="dashboardRealtime()" x-init="startAutoRefresh()">

    <!-- Alert Un-Checked In Employees (> 08:00) -->
    @if($showLateAlert)
    <div class="mb-6 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 shadow-sm flex items-start space-x-3">
        <svg class="w-6 h-6 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        <div class="flex-1">
            <h4 class="font-bold text-sm">Pemberitahuan Presensi Masuk (> 08:00)</h4>
            <p class="text-xs text-amber-800 mt-1">
                Terdapat <strong>{{ $unCheckedInPegawai->count() }} aparatur desa</strong> yang belum melakukan presensi masuk hingga jam 08:00 WIB:
            </p>
            <div class="mt-2 flex flex-wrap gap-1.5">
                @foreach($unCheckedInPegawai as $p)
                <span class="inline-block px-2 py-0.5 bg-amber-100 border border-amber-300 rounded text-[11px] font-medium text-amber-900">
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
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Hadir Tepat Waktu</span>
                <span class="p-2 rounded-lg bg-emerald-50 text-emerald-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="counts.hadir">{{ $countHadir }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Presensi ≤ 08:00 WIB</p>
        </div>

        <!-- Terlambat -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Terlambat</span>
                <span class="p-2 rounded-lg bg-rose-50 text-rose-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="counts.terlambat">{{ $countTerlambat }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Presensi > 08:00 WIB</p>
        </div>

        <!-- Sakit -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sakit</span>
                <span class="p-2 rounded-lg bg-amber-50 text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="counts.sakit">{{ $countSakit }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Surat Dokter</p>
        </div>

        <!-- Izin / Dispen -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Izin / Dispen</span>
                <span class="p-2 rounded-lg bg-blue-50 text-blue-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="counts.izin + counts.dispen">{{ $countIzin + $countDispen }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Tercatat di Perizinan</p>
        </div>

        <!-- Tidak Hadir / belum absen -->
        <div class="bg-white border border-slate-200 rounded-xl p-4 shadow-sm">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">Belum / Tidak Hadir</span>
                <span class="p-2 rounded-lg bg-slate-100 text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                </span>
            </div>
            <p class="text-3xl font-extrabold text-slate-900 mt-2" x-text="counts.tidak_hadir">{{ $countTidakHadir }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Dari {{ $totalPegawai }} Aparatur</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Real-time Attendance Table (2 Columns) -->
        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Presensi Real-time Hari Ini</h3>
                    <p class="text-xs text-slate-500">Otomatis memperbarui data tiap 30 detik</p>
                </div>
                <div class="flex items-center space-x-2 text-xs text-slate-400">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    <span>Update: <strong x-text="lastUpdated">{{ date('H:i:s') }}</strong></span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-slate-500 uppercase tracking-wider bg-slate-50">
                            <th class="p-3">Aparatur Desa</th>
                            <th class="p-3">Jam Masuk</th>
                            <th class="p-3">Jam Pulang</th>
                            <th class="p-3">Status</th>
                            <th class="p-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium">
                        <template x-for="row in attendanceList" :key="row.id">
                            <tr class="hover:bg-slate-50 transition">
                                <td class="p-3">
                                    <div class="font-bold text-slate-800" x-text="row.nama"></div>
                                    <div class="text-[11px] text-slate-400" x-text="row.jabatan"></div>
                                </td>
                                <td class="p-3 font-mono text-slate-700" x-text="row.jam_masuk"></td>
                                <td class="p-3 font-mono text-slate-700" x-text="row.jam_pulang"></td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold uppercase tracking-wider"
                                        :class="{
                                            'bg-emerald-100 text-emerald-700': row.status === 'hadir',
                                            'bg-rose-100 text-rose-700': row.status === 'terlambat',
                                            'bg-amber-100 text-amber-700': row.status === 'sakit',
                                            'bg-blue-100 text-blue-700': row.status === 'izin' || row.status === 'dispen',
                                            'bg-slate-100 text-slate-600': row.status === 'tidak_hadir'
                                        }"
                                        x-text="row.status">
                                    </span>
                                </td>
                                <td class="p-3 text-slate-500" x-text="row.keterangan"></td>
                            </tr>
                        </template>

                        @if($presensiToday->isEmpty())
                        <tr x-show="attendanceList.length === 0">
                            <td colspan="5" class="p-8 text-center text-slate-400">
                                Belum ada log presensi masuk tercatat hari ini.
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 7-Day Chart (1 Column) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-slate-800 text-base mb-1">Grafik Kehadiran (7 Hari)</h3>
                <p class="text-xs text-slate-500 mb-4">Tren kedisiplinan aparatur desa seminggu terakhir</p>
                <div class="relative h-64">
                    <canvas id="attendance7DayChart"></canvas>
                </div>
            </div>
            <div class="mt-4 pt-3 border-t border-slate-100 text-center text-[11px] text-slate-400">
                Sistem Presensi Offline Jaringan Kantor Desa Balung Lor
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    function dashboardRealtime() {
        return {
            lastUpdated: '{{ date('H:i:s') }}',
            counts: {
                hadir: {{ $countHadir }},
                terlambat: {{ $countTerlambat }},
                sakit: {{ $countSakit }},
                izin: {{ $countIzin }},
                dispen: {{ $countDispen }},
                tidak_hadir: {{ $countTidakHadir }}
            },
            attendanceList: @json($presensiToday->map(fn($p) => [
                'id' => $p->id,
                'nama' => $p->pegawai->nama_lengkap ?? '-',
                'jabatan' => $p->pegawai->jabatan ?? '-',
                'jam_masuk' => $p->jam_masuk ?? '-',
                'jam_pulang' => $p->jam_pulang ?? '-',
                'status' => $p->status,
                'keterangan' => $p->keterangan ?? '-',
            ])),
            startAutoRefresh() {
                setInterval(() => {
                    fetch('{{ route('dashboard.realtime') }}')
                        .then(res => res.json())
                        .then(data => {
                            if (data.status === 'success') {
                                this.counts = data.counts;
                                this.attendanceList = data.data;
                                this.lastUpdated = data.timestamp;
                            }
                        })
                        .catch(err => console.error('Auto refresh error:', err));
                }, 30000); // 30 detik
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
                        backgroundColor: '#22c55e',
                    },
                    {
                        label: 'Terlambat',
                        data: @json($chartData['terlambat']),
                        backgroundColor: '#f43f5e',
                    },
                    {
                        label: 'Izin/Sakit',
                        data: @json($chartData['izin']),
                        backgroundColor: '#3b82f6',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
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
