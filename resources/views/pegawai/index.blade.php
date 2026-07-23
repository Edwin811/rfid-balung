@extends('layouts.app')

@section('title', 'Kelola Aparatur Desa & Kartu RFID')

@section('content')
<div x-data="{ addModal: false, editModal: false, editData: {} }">

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
                <h3 class="font-extrabold text-slate-900 text-lg">Kelola Data Aparatur Desa & Pemetaan Kartu RFID</h3>
                <p class="text-xs text-slate-500 font-medium">Ubah nama aparatur, jabatan, ID Mesin, dan nomor kartu RFID. Anda juga dapat mengunggah (*push*) data nama langsung ke memori fisik mesin biometrik ZKTeco.</p>
            </div>

            <div class="flex items-center space-x-3 self-end md:self-auto">
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

        <form action="{{ route('pegawai.index') }}" method="GET" class="mt-5 pt-4 border-t border-slate-100 flex items-center gap-3">
            <div class="flex-1">
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama aparatur, jabatan, ID Mesin, atau nomor kartu RFID..."
                    class="w-full px-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
            </div>
            <button type="submit" class="px-5 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-xl text-xs transition border border-slate-200">
                Cari
            </button>
        </form>
    </div>

    <!-- Table Aparatur Desa -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-600 uppercase tracking-wider bg-slate-100 font-bold">
                        <th class="p-4">No</th>
                        <th class="p-4">ID Mesin</th>
                        <th class="p-4">Nama Lengkap Aparatur Desa</th>
                        <th class="p-4">Jabatan Official</th>
                        <th class="p-4">UID Kartu RFID (10-Digit)</th>
                        <th class="p-4">Biometrik Sidik Jari</th>
                        <th class="p-4 text-center">Status</th>
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
                        <td class="p-4 font-mono font-bold text-emerald-700">
                            {{ $p->uid_kartu ? str_pad($p->uid_kartu, 10, '0', STR_PAD_LEFT) : '-' }}
                        </td>
                        <td class="p-4 font-semibold">
                            @if(in_array((string)$p->id_mesin, ['1', '2']))
                            <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">
                                <span>👆 1 Jari (Aktif)</span>
                            </span>
                            @else
                            <span class="inline-flex items-center space-x-1 px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-slate-100 text-slate-500 border border-slate-300">
                                <span>Belum Ada</span>
                            </span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            @if($p->is_active)
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">Aktif</span>
                            @else
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-slate-100 text-slate-600 border border-slate-300">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center space-x-2">
                                <button @click="editModal = true; editData = @js($p)"
                                    class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-lg text-xs transition border border-slate-300">
                                    Edit
                                </button>

                                <form action="{{ route('pegawai.destroy', $p->id) }}" method="POST"
                                    data-confirm="Apakah Anda yakin ingin menghapus aparatur desa '{{ $p->nama_lengkap }}'? Data presensi historis di masa lalu tetap tersimpan aman 100%."
                                    data-confirm-title="Hapus Aparatur Desa"
                                    data-confirm-btn="Ya, Hapus Aparatur"
                                    data-confirm-theme="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 font-bold rounded-lg text-xs transition border border-red-200">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-slate-400">
                            Belum ada data aparatur desa. Silakan tambahkan data baru.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 bg-slate-50 border-t border-slate-200">
            {{ $pegawaiList->links() }}
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

    <!-- Modal Edit Aparatur -->
    <div x-show="editModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-slate-200" @click.away="editModal = false">
            <div class="border-b border-slate-100 pb-3 mb-4">
                <h3 class="font-bold text-slate-800 text-base">Edit Data Aparatur Desa</h3>
                <p class="text-xs text-slate-500">Ubah nama, jabatan, ID Mesin, dan nomor kartu RFID.</p>
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
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">UID Kartu RFID (10-Digit)</label>
                    <input type="text" name="uid_kartu" x-model="editData.uid_kartu" placeholder="Contoh: 0012345678"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status Keaktifan</label>
                    <select name="is_active" x-model="editData.is_active" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                        <option :value="1">Aktif</option>
                        <option :value="0">Non-Aktif</option>
                    </select>
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
