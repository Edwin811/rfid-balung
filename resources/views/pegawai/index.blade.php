@extends('layouts.app')

@section('title', 'Kelola Aparatur Desa, RFID & Histori Perangkat')

@section('content')
<div x-data="{ addModal: false, editModal: false, editData: {}, activeTab: '{{ $tab }}' }">

    <!-- Flash Alert -->
    @if(session('success'))
    <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl mb-6 shadow-sm flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span class="text-xs font-bold">{{ session('success') }}</span>
        </div>
        <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800 text-sm font-bold">&times;</button>
    </div>
    @endif

    <!-- Header & Action Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-6 shadow-sm">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="font-extrabold text-slate-900 text-lg">Kelola Data Aparatur Desa & Histori Perangkat Presensi</h3>
                <p class="text-xs text-slate-500 font-medium">Ubah nama aparatur, status pensiun/mutasi, ID Mesin, dan nomor kartu RFID.</p>
            </div>

            <div class="flex items-center space-x-3 self-end md:self-auto">
                <form action="{{ route('pegawai.pull_machine') }}" method="POST"
                    data-confirm="Apakah Anda yakin ingin menarik (pull) seluruh data nama & nomor kartu RFID dari memori mesin biometrik ke website?"
                    data-confirm-title="Pull Data dari Mesin Biometrik"
                    data-confirm-btn="Ya, Pull Sekarang"
                    data-confirm-theme="warning">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-xs flex items-center space-x-2 transition border border-slate-300 shadow-sm">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                        <span>Pull Data dari Mesin</span>
                    </button>
                </form>

                <form action="{{ route('pegawai.sync_machine') }}" method="POST"
                    data-confirm="Apakah Anda yakin ingin mengunggah (push) seluruh data nama & nomor kartu RFID di website ke memori fisik mesin biometrik ZKTeco?"
                    data-confirm-title="Push Data ke Mesin Biometrik"
                    data-confirm-btn="Ya, Push Sekarang"
                    data-confirm-theme="warning">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs flex items-center space-x-2 transition shadow">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <span>Push Data ke Mesin Biometrik</span>
                    </button>
                </form>

                <button @click="addModal = true" class="px-4 py-2.5 bg-red-700 hover:bg-red-800 text-white font-bold rounded-xl text-xs flex items-center space-x-2 transition shadow">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Tambah Aparatur Baru</span>
                </button>
            </div>
        </div>

        <!-- Navigation Tabs Bar -->
        <div class="mt-6 border-b border-slate-200 flex items-center space-x-2 overflow-x-auto scrollbar-none">
            <button @click="activeTab = 'aktif'"
                :class="activeTab === 'aktif' ? 'border-red-700 text-red-700 font-extrabold bg-red-50/50' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-5 py-3 border-b-2 text-xs transition flex items-center space-x-2 rounded-t-xl">
                <span>Aparatur Aktif</span>
            </button>

            <button @click="activeTab = 'arsip'"
                :class="activeTab === 'arsip' ? 'border-amber-600 text-amber-700 font-extrabold bg-amber-50/50' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-5 py-3 border-b-2 text-xs transition flex items-center space-x-2 rounded-t-xl">
                <span>Arsip Pensiun & Mutasi</span>
            </button>

            <button @click="activeTab = 'histori'"
                :class="activeTab === 'histori' ? 'border-blue-700 text-blue-700 font-extrabold bg-blue-50/50' : 'border-transparent text-slate-500 hover:text-slate-800 font-medium'"
                class="px-5 py-3 border-b-2 text-xs transition flex items-center space-x-2 rounded-t-xl">
                <span>Histori Perangkat & Mutasi</span>
            </button>
        </div>

        <!-- Search Bar -->
        <form action="{{ route('pegawai.index') }}" method="GET" class="mt-4 flex items-center gap-3">
            <input type="hidden" name="tab" :value="activeTab">
            <div class="flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama aparatur, jabatan, ID Mesin, atau nomor kartu RFID..."
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-xs transition border border-slate-200">
                Cari
            </button>
        </form>
    </div>

    <!-- TAB 1: TABEL APARATUR DESA AKTIF -->
    <div x-show="activeTab === 'aktif'" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-600 uppercase tracking-wider bg-slate-100 font-bold">
                        <th class="p-4">No</th>
                        <th class="p-4">ID Mesin</th>
                        <th class="p-4">Nama Lengkap Aparatur Desa</th>
                        <th class="p-4">Jabatan</th>
                        <th class="p-4 text-center">Status Kepegawaian</th>
                        <th class="p-4">UID Kartu RFID (10-Digit)</th>
                        <th class="p-4">Biometrik Sidik Jari</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($pegawaiList as $index => $p)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 text-slate-400 font-mono">{{ $pegawaiList->firstItem() + $index }}</td>
                        <td class="p-4 font-mono font-extrabold text-slate-900 text-sm">{{ $p->id_mesin }}</td>
                        <td class="p-4 font-bold text-slate-800 text-sm">{{ $p->nama_lengkap }}</td>
                        <td class="p-4 text-slate-600 font-semibold">{{ $p->jabatan }}</td>
                        <td class="p-4 text-center">
                            @if($p->status_kepegawaian === 'aktif')
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">Aktif</span>
                            @elseif($p->status_kepegawaian === 'pensiun')
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 border border-amber-300">Pensiun</span>
                            @elseif($p->status_kepegawaian === 'mutasi')
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-blue-100 text-blue-800 border border-blue-300">Mutasi</span>
                            @else
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-slate-100 text-slate-600 border border-slate-300">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="p-4 font-mono font-bold text-emerald-700">
                            {{ $p->uid_kartu ? str_pad($p->uid_kartu, 10, '0', STR_PAD_LEFT) : '-' }}
                        </td>
                        <td class="p-4 font-semibold">
                            @if(in_array((string)$p->id_mesin, ['1', '2']))
                            <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <span>1 Jari (Aktif)</span>
                            </span>
                            @else
                            <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-slate-100 text-slate-500 border border-slate-300">
                                <span>Belum Ada</span>
                            </span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <button @click="editModal = true; editData = @js($p)"
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-lg text-xs transition border border-slate-300">
                                    Edit / Ganti Kartu
                                </button>

                                <form action="{{ route('pegawai.destroy', $p->id) }}" method="POST"
                                    data-confirm="Apakah Anda yakin ingin mengarsipkan / me-nonaktifkan aparatur '{{ $p->nama_lengkap }}'? Data presensi historis tetap aman 100%."
                                    data-confirm-title="Arsip Aparatur Desa"
                                    data-confirm-btn="Ya, Arsipkan"
                                    data-confirm-theme="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 font-bold rounded-lg text-xs transition border border-red-200">
                                        Pensiun / Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-400">
                            Belum ada data aparatur desa aktif. Silakan tambahkan data baru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-200">
            {{ $pegawaiList->appends(['tab' => 'aktif'])->links() }}
        </div>
    </div>

    <!-- TAB 2: TABEL ARSIP PENSIUN & MUTASI APARATUR -->
    <div x-show="activeTab === 'arsip'" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 bg-amber-50 border-b border-amber-200 flex items-center justify-between">
            <h4 class="font-extrabold text-amber-900 text-xs uppercase tracking-wide">
                ARSIP PENSIUN, MUTASI, & SOFT-DELETED APARATUR (DATA HISTORIS)
            </h4>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-600 uppercase tracking-wider bg-slate-100 font-bold">
                        <th class="p-4">No</th>
                        <th class="p-4">ID Mesin</th>
                        <th class="p-4">Nama Aparatur Desa</th>
                        <th class="p-4">Jabatan Terakhir</th>
                        <th class="p-4 text-center">Status Arsip</th>
                        <th class="p-4">UID Kartu Bekas</th>
                        <th class="p-4 text-center">Keamanan Presensi</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($arsipList as $index => $a)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 text-slate-400 font-mono">{{ $arsipList->firstItem() + $index }}</td>
                        <td class="p-4 font-mono font-extrabold text-slate-900">{{ $a->id_mesin }}</td>
                        <td class="p-4 font-bold text-slate-800 text-sm">{{ $a->nama_lengkap }}</td>
                        <td class="p-4 text-slate-600 font-semibold">{{ $a->jabatan }}</td>
                        <td class="p-4 text-center">
                            @if($a->status_kepegawaian === 'pensiun')
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 border border-amber-300">👴 Pensiun</span>
                            @elseif($a->status_kepegawaian === 'mutasi')
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-blue-100 text-blue-800 border border-blue-300">🔁 Mutasi</span>
                            @else
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-slate-200 text-slate-700 border border-slate-300">📁 Terhapus (Arsip)</span>
                            @endif
                        </td>
                        <td class="p-4 font-mono text-slate-500">
                            {{ $a->uid_kartu ? str_pad($a->uid_kartu, 10, '0', STR_PAD_LEFT) : '-' }}
                        </td>
                        <td class="p-4 text-center">
                            <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded text-[10px] font-extrabold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span>✓ Riwayat Terjaga 100%</span>
                            </span>
                        </td>
                        <td class="p-4 text-center">
                            @if($a->trashed())
                            <form action="{{ route('pegawai.restore', $a->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs transition shadow">
                                    Pulihkan (Aktifkan)
                                </button>
                            </form>
                            @else
                            <button @click="editModal = true; editData = @js($a)"
                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-lg text-xs transition border border-slate-300">
                                Ubah Status
                            </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-400">
                            Belum ada arsip pegawai pensiun atau mutasi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-200">
            {{ $arsipList->appends(['tab' => 'arsip'])->links() }}
        </div>
    </div>

    <!-- TAB 3: TABEL HISTORI PERANGKAT & MUTASI KARTU RFID -->
    <div x-show="activeTab === 'histori'" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="p-4 bg-blue-50 border-b border-blue-200 flex items-center justify-between">
            <h4 class="font-extrabold text-blue-900 text-xs uppercase tracking-wide">
                HISTORI MUTASI PERANGKAT, PERGANTIAN KARTU RFID & SINKRONISASI MESIN
            </h4>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-600 uppercase tracking-wider bg-slate-100 font-bold">
                        <th class="p-4">Waktu & Tanggal</th>
                        <th class="p-4">Operator (Aktor)</th>
                        <th class="p-4">Peristiwa (Aksi)</th>
                        <th class="p-4">Detail Mutasi / Perubahan Perangkat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($historiPerangkat as $h)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-mono text-slate-500 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($h->created_at)->format('d/m/Y H:i:s') }}
                        </td>
                        <td class="p-4 font-bold text-slate-800">
                            {{ $h->user->name ?? 'System' }}
                        </td>
                        <td class="p-4">
                            @if($h->aksi === 'ganti_kartu_rfid')
                            <span class="px-2.5 py-1 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800 border border-amber-300">💳 Ganti Kartu RFID</span>
                            @elseif($h->aksi === 'pensiun_pegawai')
                            <span class="px-2.5 py-1 rounded text-[10px] font-black uppercase bg-amber-100 text-amber-800 border border-amber-300">👴 Aparatur Pensiun</span>
                            @elseif($h->aksi === 'mutasi_pegawai')
                            <span class="px-2.5 py-1 rounded text-[10px] font-black uppercase bg-blue-100 text-blue-800 border border-blue-300">🔁 Mutasi Jabatan</span>
                            @elseif($h->aksi === 'tambah_pegawai')
                            <span class="px-2.5 py-1 rounded text-[10px] font-black uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">➕ Registrasi Aparatur</span>
                            @elseif($h->aksi === 'sync_users_to_machine')
                            <span class="px-2.5 py-1 rounded text-[10px] font-black uppercase bg-slate-800 text-white">📡 Push Mesin Biometrik</span>
                            @else
                            <span class="px-2.5 py-1 rounded text-[10px] font-black uppercase bg-slate-100 text-slate-700 border border-slate-300">{{ $h->aksi }}</span>
                            @endif
                        </td>
                        <td class="p-4 text-slate-700 font-mono text-[11px]">
                            @if($h->aksi === 'ganti_kartu_rfid')
                            <div>Aparatur: <strong>{{ $h->data_baru['nama'] ?? '-' }}</strong></div>
                            <div>Nomor Kartu Lama: <span class="text-red-600">{{ $h->data_lama['uid_lama'] ?? '-' }}</span> &rarr; Kartu Baru: <span class="text-emerald-700 font-bold">{{ $h->data_baru['uid_baru'] ?? '-' }}</span></div>
                            @elseif($h->aksi === 'pensiun_pegawai' || $h->aksi === 'mutasi_pegawai')
                            <div>Aparatur: <strong>{{ $h->data_baru['nama'] ?? '-' }}</strong></div>
                            <div>Status Berubah: <span class="text-slate-500 uppercase">{{ $h->data_lama['status_lama'] ?? '-' }}</span> &rarr; <span class="text-amber-700 uppercase font-bold">{{ $h->data_baru['status_baru'] ?? '-' }}</span></div>
                            @else
                            <div>{{ json_encode($h->data_baru ?: $h->data_lama) }}</div>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400">
                            Belum ada riwayat mutasi perangkat atau kartu RFID tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-200">
            {{ $historiPerangkat->appends(['tab' => 'histori'])->links() }}
        </div>
    </div>

    <!-- Modal Tambah Aparatur Baru -->
    <div x-show="addModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-slate-200" @click.away="addModal = false">
            <div class="border-b border-slate-100 pb-3 mb-4">
                <h3 class="font-bold text-slate-800 text-base">Tambah Aparatur Desa Baru</h3>
                <p class="text-xs text-slate-500">Daftarkan ID Mesin, nama lengkap, jabatan, dan nomor kartu RFID.</p>
            </div>

            <form action="{{ route('pegawai.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">ID Mesin (User ID ZKTeco)</label>
                    <input type="text" name="id_mesin" required placeholder="Contoh: 101, 1, 2"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nama Lengkap Aparatur</label>
                    <input type="text" name="nama_lengkap" required placeholder="Contoh: IMAM MUSTOFA"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Jabatan Official</label>
                    <input type="text" name="jabatan" required placeholder="Contoh: KEPALA DESA BALUNGLOR"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status Kepegawaian</label>
                    <select name="status_kepegawaian" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                        <option value="aktif">Aktif</option>
                        <option value="pensiun">Pensiun</option>
                        <option value="mutasi">Mutasi</option>
                        <option value="non_aktif">Non-Aktif</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">UID Kartu RFID (10-Digit)</label>
                    <input type="text" name="uid_kartu" placeholder="Contoh: 0012345678"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="addModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-900 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white font-bold text-xs rounded-xl shadow transition">
                        Simpan Aparatur
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Aparatur & Ganti Kartu RFID -->
    <div x-show="editModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-slate-200" @click.away="editModal = false">
            <div class="border-b border-slate-100 pb-3 mb-4">
                <h3 class="font-bold text-slate-800 text-base">Edit Aparatur & Ganti Kartu RFID</h3>
                <p class="text-xs text-slate-500">Ubah nama, jabatan, status pensiun/mutasi, atau pergantian nomor kartu RFID baru.</p>
            </div>

            <form :action="'/pegawai/' + editData.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">ID Mesin (User ID ZKTeco)</label>
                    <input type="text" name="id_mesin" x-model="editData.id_mesin" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nama Lengkap Aparatur</label>
                    <input type="text" name="nama_lengkap" x-model="editData.nama_lengkap" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Jabatan Official</label>
                    <input type="text" name="jabatan" x-model="editData.jabatan" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status Kepegawaian</label>
                    <select name="status_kepegawaian" x-model="editData.status_kepegawaian" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                        <option value="aktif">Aktif</option>
                        <option value="pensiun">Pensiun</option>
                        <option value="mutasi">Mutasi</option>
                        <option value="non_aktif">Non-Aktif</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">UID Kartu RFID (10-Digit)</label>
                    <input type="text" name="uid_kartu" x-model="editData.uid_kartu" placeholder="Contoh: 0012345678"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                    <p class="text-[10px] text-slate-400 mt-1">Jika mengganti kartu RFID baru, riwayat pergantian kartu akan otomatis tercatat di Histori Perangkat.</p>
                </div>

                <input type="hidden" name="is_active" :value="editData.status_kepegawaian === 'aktif' ? 1 : 0">

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
