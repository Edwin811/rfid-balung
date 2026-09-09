@extends('layouts.app')

@section('title', 'Kelola Aparatur Desa & Kartu RFID')

@section('content')
<div x-data="{ addModal: false, editModal: false, editData: {}, archiveModal: false, archiveData: {}, archiveKategori: 'pensiun', activeTab: '{{ $tab }}' }">


    <!-- Header & Action Card -->
    <div class="bg-white border border-slate-200 rounded-2xl p-6 mb-6 shadow-sm">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="font-extrabold text-slate-900 text-lg">Kelola Data Aparatur Desa & Kartu RFID Presensi</h3>
                <p class="text-xs text-slate-500 font-medium">Ubah nama aparatur, status pensiun/mutasi/arsip, ID Mesin, dan nomor kartu RFID.</p>
            </div>

            <div class="flex items-center space-x-3 self-end md:self-auto">
                <form action="{{ route('pegawai.pull_machine') }}" method="POST"
                    data-confirm="Apakah Anda yakin ingin menarik (pull) nomor kartu RFID & data biometrik sidik jari dari mesin fisik ke website? Data nama dan jabatan di website tetap aman terjaga."
                    data-confirm-title="Tarik Data Biometrik & Kartu dari Mesin"
                    data-confirm-btn="Ya, Tarik Sekarang"
                    data-confirm-theme="warning">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-xs flex items-center space-x-2 transition border border-slate-300 shadow-sm">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/></svg>
                        <span>Mengambil data dari Mesin</span>
                    </button>
                </form>

                <form action="{{ route('pegawai.sync_machine') }}" method="POST"
                    data-confirm="Apakah Anda yakin ingin mengunggah (push) seluruh data nama & nomor kartu RFID di website ke memori fisik mesin biometrik ZKTeco?"
                    data-confirm-title="Mengunggah Data ke Mesin Biometrik"
                    data-confirm-btn="Ya, Push Sekarang"
                    data-confirm-theme="warning">
                    @csrf
                    <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white font-bold rounded-xl text-xs flex items-center space-x-2 transition shadow">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <span>Mengunggah Data ke Mesin Biometrik</span>
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
        </div>

        <!-- Search Bar Ter-highlight -->
        <form action="{{ route('pegawai.index') }}" method="GET" class="mt-4 flex flex-col sm:flex-row items-center gap-3">
            <input type="hidden" name="tab" :value="activeTab">
            <div class="relative flex-1 w-full">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama aparatur, jabatan, ID Mesin, atau nomor kartu RFID..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-white border border-slate-300 text-xs sm:text-sm font-medium shadow-sm transition placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-red-600/30 focus:border-red-600">
            </div>
            <div class="flex items-center space-x-2 w-full sm:w-auto">
                <button type="submit" class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white font-bold rounded-xl text-xs sm:text-sm transition shadow-sm flex items-center justify-center space-x-1.5 flex-1 sm:flex-none">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Cari</span>
                </button>
                @if($search)
                <a href="{{ route('pegawai.index', ['tab' => $tab]) }}" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs sm:text-sm transition border border-slate-300 text-center">
                    Reset
                </a>
                @endif
            </div>
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
                            @if(($p->jumlah_jari ?? 0) > 0)
                            <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <span>{{ $p->jumlah_jari }} Jari (Aktif)</span>
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

                                @if(in_array(auth()->user()->role, ['admin', 'operator']))
                                <form action="{{ route('pegawai.delete_fingerprint', $p->id) }}" method="POST"
                                    data-confirm="Apakah Anda yakin ingin menghapus seluruh template sidik jari biometrik milik aparatur '{{ $p->nama_lengkap }}' (ID Mesin: {{ $p->id_mesin }}) dari mesin fisik ZKTeco?"
                                    data-confirm-title="Hapus Sidik Jari Biometrik"
                                    data-confirm-btn="Ya, Hapus Sidik Jari"
                                    data-confirm-theme="warning">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold rounded-lg text-xs transition border border-amber-300 flex items-center space-x-1" title="Hapus Sidik Jari dari Mesin">
                                        <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457-.39-2.823-1.07-4"/></svg>
                                        <span>Hapus Jari</span>
                                    </button>
                                </form>
                                @endif

                                <button type="button" @click="archiveModal = true; archiveData = @js($p); archiveKategori = 'pensiun'"
                                    class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 font-bold rounded-lg text-xs transition border border-red-200 flex items-center space-x-1"
                                    title="Pensiun / Mutasi / Arsipkan Aparatur">
                                    <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    <span>Pensiun / Arsipkan</span>
                                </button>
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
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-amber-100 text-amber-800 border border-amber-300">Pensiun</span>
                            @elseif($a->status_kepegawaian === 'mutasi')
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-blue-100 text-blue-800 border border-blue-300">Mutasi</span>
                            @else
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-slate-200 text-slate-700 border border-slate-300">Diarsipkan</span>
                            @endif
                        </td>
                        <td class="p-4 font-mono text-slate-500">
                            {{ $a->uid_kartu ? str_pad($a->uid_kartu, 10, '0', STR_PAD_LEFT) : '-' }}
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
                        <td colspan="7" class="p-8 text-center text-slate-400">
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
                    <p class="text-[10px] text-slate-400 mt-1">Nomor kartu RFID 10-digit yang terdaftar pada kartu fisik aparatur.</p>
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

    <!-- Modal Pilihan Kategori Pensiun / Mutasi / Arsip -->
    <div x-show="archiveModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl border border-slate-200" @click.away="archiveModal = false">
            <div class="border-b border-slate-100 pb-3 mb-4">
                <div class="flex items-center space-x-2.5">
                    <div class="w-9 h-9 rounded-xl bg-red-100 text-red-700 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                    </div>
                    <div>
                        <h3 class="font-extrabold text-slate-800 text-base">Arsipkan Aparatur Desa</h3>
                        <p class="text-xs text-slate-500">Pilih kategori penonaktifan untuk aparatur terpilih.</p>
                    </div>
                </div>
            </div>

            <form :action="'/pegawai/' + archiveData.id" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')

                <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                    <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Aparatur Terpilih:</div>
                    <div class="font-extrabold text-slate-900 text-sm mt-0.5" x-text="archiveData.nama_lengkap"></div>
                    <div class="text-xs text-slate-600 font-medium" x-text="(archiveData.jabatan || '-') + ' • ID Mesin: ' + (archiveData.id_mesin || '-')"></div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Pilih Kategori Status:</label>
                    <div class="space-y-2">
                        <!-- Opsi 1: Pensiun -->
                        <label class="flex items-start p-3.5 rounded-xl border cursor-pointer transition"
                            :class="archiveKategori === 'pensiun' ? 'bg-amber-50 border-amber-400 ring-2 ring-amber-200' : 'bg-white border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="kategori" value="pensiun" x-model="archiveKategori" class="mt-0.5 text-amber-600 focus:ring-amber-500">
                            <div class="ml-3">
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-xs text-slate-900">Pensiun (Purna Tugas)</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-amber-100 text-amber-800 border border-amber-300 uppercase">Pensiun</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">Aparatur telah mencapai batas usia kerja atau purna masa tugas resmi desa.</p>
                            </div>
                        </label>

                        <!-- Opsi 2: Mutasi -->
                        <label class="flex items-start p-3.5 rounded-xl border cursor-pointer transition"
                            :class="archiveKategori === 'mutasi' ? 'bg-blue-50 border-blue-400 ring-2 ring-blue-200' : 'bg-white border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="kategori" value="mutasi" x-model="archiveKategori" class="mt-0.5 text-blue-600 focus:ring-blue-500">
                            <div class="ml-3">
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-xs text-slate-900">Mutasi Tugas / Jabatan</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-blue-100 text-blue-800 border border-blue-300 uppercase">Mutasi</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">Aparatur berpindah dinas, alih tugas ke instansi lain, atau mutasi jabatan.</p>
                            </div>
                        </label>

                        <!-- Opsi 3: Non-Aktif / Diarsipkan -->
                        <label class="flex items-start p-3.5 rounded-xl border cursor-pointer transition"
                            :class="archiveKategori === 'non_aktif' ? 'bg-slate-100 border-slate-400 ring-2 ring-slate-200' : 'bg-white border-slate-200 hover:bg-slate-50'">
                            <input type="radio" name="kategori" value="non_aktif" x-model="archiveKategori" class="mt-0.5 text-slate-700 focus:ring-slate-500">
                            <div class="ml-3">
                                <div class="flex items-center space-x-2">
                                    <span class="font-bold text-xs text-slate-900">Diarsipkan (Non-Aktif Sementara)</span>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold bg-slate-200 text-slate-700 border border-slate-300 uppercase">Diarsipkan</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-0.5">Aparatur dinonaktifkan sementara dari kepengurusan administratif.</p>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 flex items-center space-x-2 text-emerald-800 text-[11px] font-medium">
                    <svg class="w-4 h-4 flex-shrink-0 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span>Seluruh riwayat presensi masa lalu aparatur tetap terjaga 100% dan dapat dilihat di tab Arsip.</span>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="archiveModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-900 font-bold text-xs">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white font-bold text-xs rounded-xl shadow transition flex items-center space-x-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        <span>Simpan & Arsipkan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
