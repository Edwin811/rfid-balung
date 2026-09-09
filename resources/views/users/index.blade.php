@extends('layouts.app')

@section('title', 'Manajemen Pengguna Sistem')

@section('content')
<div x-data="{ addModal: false, editModal: false, passwordModal: false, selectedUser: {} }">

    <!-- Header & Add Button -->
    <div class="bg-white border border-slate-200 rounded-xl p-5 mb-6 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="font-bold text-slate-800 text-base">
                @if(auth()->user()->isAdmin())
                Kelola Akun Pengguna
                @else
                Pengaturan Akun Pengguna
                @endif
            </h3>
            <p class="text-xs text-slate-500">
                @if(auth()->user()->isAdmin())
                Pengaturan akun dan peran pengguna (Admin, Kepala Desa, Wakil Kepala Desa, Operator)
                @else
                Pengaturan profil dan kata sandi untuk akun Anda ({{ auth()->user()->name }})
                @endif
            </p>
        </div>

        @if(auth()->user()->isAdmin())
        <button @click="addModal = true" class="px-4 py-2.5 bg-red-700 hover:bg-red-800 text-white font-bold rounded-xl text-xs uppercase tracking-wider transition shadow self-start sm:self-auto">
            + Tambah Pengguna Baru
        </button>
        @endif
    </div>

    <!-- Users Table -->
    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-600 uppercase tracking-wider bg-slate-100 font-bold">
                        <th class="p-4">Nama Lengkap</th>
                        <th class="p-4">Username</th>
                        <th class="p-4">Email</th>
                        <th class="p-4">Role</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Terakhir Login</th>
                        <th class="p-4 text-center whitespace-nowrap min-w-[220px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($users as $u)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-bold text-slate-800 text-sm">{{ $u->name }}</td>
                        <td class="p-4 font-mono text-slate-700 font-bold">{{ $u->username }}</td>
                        <td class="p-4 text-slate-500">{{ $u->email ?? '-' }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase tracking-wider bg-slate-900 text-amber-300">
                                {{ $u->role }}
                            </span>
                        </td>
                        <td class="p-4">
                            @if($u->is_active)
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-emerald-100 text-emerald-800 border border-emerald-300">Aktif</span>
                            @else
                            <span class="px-2.5 py-1 rounded text-[10px] font-extrabold uppercase bg-red-100 text-red-800 border border-red-300">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="p-4 font-mono text-slate-500">
                            {{ $u->last_login_at ? \Carbon\Carbon::parse($u->last_login_at)->diffForHumans() : 'Belum Pernah' }}
                        </td>
                        <td class="p-4 text-center">
                            <div class="flex items-center justify-center space-x-1.5 flex-nowrap">
                                <button @click="editModal = true; selectedUser = @js([
                                    'id' => $u->id,
                                    'name' => $u->name,
                                    'username' => $u->username,
                                    'email' => $u->email ?? '',
                                    'role' => $u->role,
                                    'is_active' => $u->is_active ? 1 : 0
                                ])"
                                    class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded-lg text-xs transition border border-slate-300 flex items-center space-x-1 whitespace-nowrap"
                                    title="Edit Profil Pengguna">
                                    <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    <span>Edit</span>
                                </button>

                                <button @click="passwordModal = true; selectedUser = @js([
                                    'id' => $u->id,
                                    'name' => $u->name,
                                    'username' => $u->username
                                ])"
                                    class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs transition border border-slate-300 flex items-center space-x-1 whitespace-nowrap"
                                    title="Ganti Password Akun">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    <span>Ganti Password</span>
                                </button>

                                @if(auth()->user()->isAdmin() && auth()->id() !== $u->id)
                                <form action="{{ route('users.destroy', $u->id) }}" method="POST"
                                    data-confirm="Apakah Anda yakin ingin menghapus akun pengguna '{{ addslashes($u->name) }}' (Username: {{ $u->username }}) dari sistem?"
                                    data-confirm-title="Hapus Akun Pengguna"
                                    data-confirm-btn="Ya, Hapus User"
                                    data-confirm-theme="danger">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-2.5 py-1.5 bg-red-50 hover:bg-red-100 text-red-700 font-bold rounded-lg text-xs transition border border-red-200 flex items-center space-x-1 whitespace-nowrap" title="Hapus User">
                                        <svg class="w-3.5 h-3.5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Hapus</span>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah User -->
    <div x-show="addModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-slate-200" @click.away="addModal = false">
            <h3 class="font-bold text-slate-800 text-base mb-4">Tambah Akun Pengguna Baru</h3>
            <form action="{{ route('users.store') }}" method="POST"
                data-confirm="Apakah Anda yakin ingin menambahkan akun pengguna baru ini ke dalam sistem?"
                data-confirm-title="Tambah Akun Pengguna Baru"
                data-confirm-btn="Ya, Simpan User"
                data-confirm-theme="warning" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Username</label>
                    <input type="text" name="username" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email (Opsional)</label>
                    <input type="email" name="email" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Password</label>
                    <input type="password" name="password" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Role / Peran</label>
                    <select name="role" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                        <option value="operator">Operator Absen</option>
                        <option value="kepala_desa">Kepala Desa</option>
                        <option value="wakil_kepala_desa">Wakil Kepala Desa</option>
                        <option value="admin">Administrator System</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="addModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-900 font-bold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white font-bold text-xs rounded-xl shadow">Simpan User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div x-show="editModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-slate-200" @click.away="editModal = false">
            <h3 class="font-bold text-slate-800 text-base mb-4">Edit Akun Pengguna</h3>
            <form :action="'/users/' + selectedUser.id" method="POST"
                data-confirm="Apakah Anda yakin ingin menyimpan perubahan pada akun pengguna ini?"
                data-confirm-title="Simpan Perubahan User"
                data-confirm-btn="Ya, Simpan Perubahan"
                data-confirm-theme="warning" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" name="name" x-model="selectedUser.name" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Username</label>
                    <input type="text" name="username" x-model="selectedUser.username" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Email</label>
                    <input type="email" name="email" x-model="selectedUser.email" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>
                @if(auth()->user()->isAdmin())
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Role / Peran</label>
                    <select name="role" x-model="selectedUser.role" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                        <option value="operator">Operator Absen</option>
                        <option value="kepala_desa">Kepala Desa</option>
                        <option value="wakil_kepala_desa">Wakil Kepala Desa</option>
                        <option value="admin">Administrator System</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Status Akun</label>
                    <select name="is_active" x-model="selectedUser.is_active" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                        <option :value="1">Aktif</option>
                        <option :value="0">Non-Aktif</option>
                    </select>
                </div>
                @endif
                <div class="flex justify-end space-x-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="editModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-900 font-bold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white font-bold text-xs rounded-xl shadow">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Ganti Password -->
    <div x-show="passwordModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl border border-slate-200" @click.away="passwordModal = false">
            <h3 class="font-bold text-slate-800 text-base mb-1">Ganti Password Akun</h3>
            <p class="text-xs text-slate-500 mb-4">Pengguna: <strong class="text-slate-800" x-text="selectedUser.name"></strong> (<span class="font-mono text-slate-600" x-text="selectedUser.username"></span>)</p>

            <form :action="'/users/' + selectedUser.id + '/password'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Password Saat Ini (Lama)</label>
                    <input type="password" name="password_lama" required placeholder="Masukkan password saat ini..."
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Password Baru</label>
                    <input type="password" name="password_baru" required minlength="6" placeholder="Minimal 6 karakter..."
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="password_baru_confirmation" required minlength="6" placeholder="Ketik ulang password baru..."
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 font-medium">
                </div>

                <div class="flex justify-end space-x-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="passwordModal = false" class="px-4 py-2 text-slate-600 hover:text-slate-900 font-bold text-xs">Batal</button>
                    <button type="submit" class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white font-bold text-xs rounded-xl shadow">Simpan Password</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
