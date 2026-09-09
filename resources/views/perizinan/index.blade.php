@extends('layouts.app')

@section('title', 'Kelola Perizinan Aparatur Desa')

@section('content')
<div x-data="{ editModal: false, editData: {} }" class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Form Input Perizinan (1 Column) -->
    @if(Auth::user()->hasRole(['admin', 'operator']))
    <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm h-fit">
        <h3 class="font-bold text-slate-800 text-base mb-1">Tambah Data Perizinan</h3>
        <p class="text-xs text-slate-500 mb-5">Status presensi otomatis diperbarui sesuai tanggal perizinan yang diinputkan.</p>

        <form action="{{ route('perizinan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            <!-- Pass Pegawai Data Safely to JS -->
            <script>
                window.pegawaiListPerizinan = @json($pegawaiList);
            </script>

            <!-- Searchable Dropdown Pilih Aparatur Desa -->
            <div x-data="{
                open: false,
                search: '',
                selectedId: '{{ old('pegawai_id', '') }}',
                items: window.pegawaiListPerizinan || [],

                get selectedLabel() {
                    if (!this.selectedId) return '-- Pilih Aparatur Desa --';
                    const found = this.items.find(i => i.id == this.selectedId);
                    return found ? (found.nama_lengkap + ' (' + found.jabatan + ')') : '-- Pilih Aparatur Desa --';
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
                <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Aparatur Desa</label>
                <input type="hidden" name="pegawai_id" :value="selectedId" required>

                <!-- Trigger Button (Tampilan Select Box Standar) -->
                <button type="button" @click="toggle()"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm bg-slate-50 text-left font-medium flex items-center justify-between focus:outline-none focus:ring-2 focus:ring-red-600 shadow-sm transition">
                    <span x-text="selectedLabel" :class="selectedId ? 'text-slate-900 font-bold' : 'text-slate-400'"></span>
                    <svg class="w-4 h-4 text-slate-400 transition-transform duration-200" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown Box (Tertutup secara default, baru terbuka jika diklik) -->
                <div x-show="open" @click.away="open = false" x-cloak
                    class="absolute left-0 right-0 top-full mt-1 bg-white border border-slate-200 rounded-xl shadow-2xl z-50 p-2 max-h-64 overflow-y-auto">

                    <!-- Search Input Di Dalam Dropdown -->
                    <div class="sticky top-0 bg-white pb-2 border-b border-slate-100">
                        <input type="text" x-ref="searchInput" x-model="search" placeholder="Cari nama / jabatan..."
                            class="w-full px-3 py-2 text-xs border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-600 font-medium bg-slate-50">
                    </div>

                    <!-- List Options -->
                    <div class="divide-y divide-slate-100 mt-1">
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

            <!-- Jenis Perizinan -->
            <div>
                <label for="jenis_izin" class="block text-xs font-bold uppercase text-slate-600 mb-1">Jenis Perizinan</label>
                <select id="jenis_izin" name="jenis_izin" required
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                    <option value="sakit">Sakit (Surat Dokter)</option>
                    <option value="izin">Izin Kepentingan Pribadi</option>
                    <option value="cuti">Cuti (Tahunan / Alasan Penting)</option>
                    <option value="dispen">Dispensasi Tugas Luar</option>
                    <option value="skorsing">Skorsing / Hukuman Disiplin</option>
                </select>
            </div>

            <!-- Periode Tanggal -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="tanggal_mulai" class="block text-xs font-bold uppercase text-slate-600 mb-1">Tanggal Mulai</label>
                    <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="{{ date('Y-m-d') }}" required
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>
                <div>
                    <label for="tanggal_selesai" class="block text-xs font-bold uppercase text-slate-600 mb-1">Tanggal Selesai</label>
                    <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="{{ date('Y-m-d') }}" required
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>
            </div>

            <!-- Keterangan / Alasan -->
            <div>
                <label for="keterangan" class="block text-xs font-bold uppercase text-slate-600 mb-1">Keterangan / Alasan</label>
                <textarea id="keterangan" name="keterangan" rows="2"
                    class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium"
                    placeholder="Contoh: Mengikuti dinas luar di kantor kecamatan"></textarea>
            </div>

            <!-- Upload Surat (PDF / Gambar) -->
            <div>
                <label for="file_surat" class="block text-xs font-bold uppercase text-slate-600 mb-1">Upload Surat / Bukti (PDF / Gambar)</label>
                <input type="file" id="file_surat" name="file_surat" accept=".pdf,.jpg,.jpeg,.png,.webp"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-red-700 file:text-white hover:file:bg-red-800 cursor-pointer">
                <p class="text-[10px] text-slate-400 mt-1">Format yang didukung: PDF, JPG, PNG, WEBP (Maks: 5MB)</p>
            </div>

            <button type="submit" class="w-full py-3 bg-green-700 hover:bg-green-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow">
                Simpan
            </button>
        </form>
    </div>
    @endif

    <!-- Riwayat Perizinan (2 Columns) -->
    <div class="{{ Auth::user()->hasRole(['admin', 'operator']) ? 'lg:col-span-2' : 'lg:col-span-3' }} bg-white border border-slate-200 rounded-xl p-6 shadow-sm">
        <h3 class="font-bold text-slate-800 text-base mb-1">Riwayat Perizinan Aparatur</h3>
        <p class="text-xs text-slate-500 mb-4">Daftar riwayat izin yang telah tersimpan di sistem</p>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-600 uppercase tracking-wider bg-slate-100 font-bold">
                        <th class="p-3">Aparatur</th>
                        <th class="p-3">Jenis Izin</th>
                        <th class="p-3">Periode Tanggal</th>
                        <th class="p-3">Keterangan</th>
                        <th class="p-3">Dokumen Surat</th>
                        <th class="p-3">Audit Input</th>
                        @if(auth()->user()->isAdmin() || auth()->user()->isOperator())
                        <th class="p-3 text-center">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @forelse($perizinanList as $p)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-3">
                            <div class="font-bold text-slate-800 text-sm">{{ $p->pegawai->nama_lengkap ?? '-' }}</div>
                            <div class="text-[11px] text-slate-500 font-medium">{{ $p->pegawai->jabatan ?? '-' }}</div>
                        </td>
                        <td class="p-3">
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase tracking-wider
                                @if($p->jenis_izin === 'sakit') bg-amber-100 text-amber-800 border border-amber-300
                                @elseif($p->jenis_izin === 'izin') bg-blue-100 text-blue-800 border border-blue-300
                                @elseif($p->jenis_izin === 'cuti') bg-emerald-100 text-emerald-800 border border-emerald-300
                                @elseif($p->jenis_izin === 'skorsing') bg-purple-100 text-purple-800 border border-purple-300
                                @else bg-indigo-100 text-indigo-800 border border-indigo-300 @endif">
                                {{ $p->jenis_izin }}
                            </span>
                        </td>
                        <td class="p-3 text-slate-700 font-mono">
                            {{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($p->tanggal_selesai)->format('d/m/Y') }}
                        </td>
                        <td class="p-3 text-slate-600">{{ $p->keterangan ?? '-' }}</td>
                        <td class="p-3">
                            @if($p->file_surat)
                            <a href="{{ asset($p->file_surat) }}" target="_blank"
                                class="inline-flex items-center space-x-1.5 px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-lg border border-slate-300 text-[10px] transition">
                                <svg class="w-3.5 h-3.5 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                <span>Lihat File</span>
                            </a>
                            @else
                            <span class="text-slate-400 italic text-[11px]">-</span>
                            @endif
                        </td>
                        <td class="p-3 text-slate-500 text-[11px]">
                            <div class="font-bold text-slate-700">{{ $p->pembuat->name ?? 'System' }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $p->created_at->format('d/m/Y H:i') }} WIB</div>
                        </td>
                        @if(auth()->user()->isAdmin() || auth()->user()->isOperator())
                        <td class="p-3 text-center">
                            <div class="flex items-center justify-center space-x-1.5">
                                <button type="button" @click="editModal = true; editData = @js([
                                    'id' => $p->id,
                                    'pegawai_id' => $p->pegawai_id,
                                    'jenis_izin' => $p->jenis_izin,
                                    'tanggal_mulai' => \Carbon\Carbon::parse($p->tanggal_mulai)->format('Y-m-d'),
                                    'tanggal_selesai' => \Carbon\Carbon::parse($p->tanggal_selesai)->format('Y-m-d'),
                                    'keterangan' => $p->keterangan ?? ''
                                ])"
                                    class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-lg text-xs transition border border-slate-300 inline-flex items-center space-x-1" title="Edit Perizinan">
                                    <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Edit</span>
                                </button>

                                <form action="{{ route('perizinan.destroy', $p->id) }}" method="POST"
                                    data-confirm="Apakah Anda yakin ingin menghapus data perizinan {{ strtoupper($p->jenis_izin) }} milik aparatur '{{ $p->pegawai->nama_lengkap ?? 'Aparatur' }}' ({{ \Carbon\Carbon::parse($p->tanggal_mulai)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($p->tanggal_selesai)->format('d/m/Y') }})? Status presensi akan dipulihkan secara otomatis."
                                    data-confirm-title="Hapus Riwayat Perizinan"
                                    data-confirm-btn="Ya, Hapus Sekarang"
                                    data-confirm-theme="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 font-bold rounded-lg text-xs transition border border-red-200 inline-flex items-center space-x-1" title="Hapus Riwayat Perizinan">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()->isAdmin() || auth()->user()->isOperator() ? 7 : 6 }}" class="p-8 text-center text-slate-400">
                            Belum ada riwayat perizinan tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $perizinanList->links() }}
        </div>
    </div>

    <!-- Modal Edit Perizinan -->
    <div x-show="editModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-slate-200 text-left" @click.away="editModal = false">
            <div class="flex items-center justify-between mb-4 border-b border-slate-100 pb-3">
                <h3 class="font-extrabold text-slate-900 text-base">Edit Data Perizinan</h3>
                <button type="button" @click="editModal = false" class="text-slate-400 hover:text-slate-700 text-lg font-bold">&times;</button>
            </div>

            <form :action="'{{ url('/perizinan') }}/' + editData.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Aparatur Desa</label>
                    <select name="pegawai_id" x-model="editData.pegawai_id" required
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                        @foreach($pegawaiList as $peg)
                        <option value="{{ $peg->id }}">{{ $peg->nama_lengkap }} ({{ $peg->jabatan }})</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Jenis Perizinan</label>
                    <select name="jenis_izin" x-model="editData.jenis_izin" required
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                        <option value="sakit">Sakit (Surat Dokter)</option>
                        <option value="izin">Izin Kepentingan Pribadi</option>
                        <option value="cuti">Cuti (Tahunan / Alasan Penting)</option>
                        <option value="dispen">Dispensasi Tugas Luar</option>
                        <option value="skorsing">Skorsing / Hukuman Disiplin</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" x-model="editData.tanggal_mulai" required
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" x-model="editData.tanggal_selesai" required
                            class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Keterangan / Alasan</label>
                    <textarea name="keterangan" x-model="editData.keterangan" rows="2"
                        class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Ganti Surat / Bukti (Opsional)</label>
                    <input type="file" name="file_surat" accept=".pdf,.jpg,.jpeg,.png,.webp"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-slate-50 text-slate-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-red-700 file:text-white hover:file:bg-red-800 cursor-pointer">
                    <p class="text-[10px] text-slate-400 mt-1">Kosongkan jika tidak ingin mengganti file lama.</p>
                </div>

                <div class="flex items-center space-x-3 pt-3">
                    <button type="button" @click="editModal = false" class="w-1/2 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-xl text-xs transition">
                        Batal
                    </button>
                    <button type="submit" class="w-1/2 py-2.5 bg-red-700 hover:bg-red-800 text-white font-bold rounded-xl text-xs transition shadow">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
