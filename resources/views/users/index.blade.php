@extends('layouts.app')

@section('title', 'Manajemen Pengguna Sistem')

@section('content')
<div x-data="{ addModal: false, editModal: false, selectedUser: {} }">

    <!-- Header & Add Button -->
    <div class="bg-white border border-slate-200 rounded-2xl p-5 mb-6 shadow-sm flex items-center justify-between">
        <div>
            <h3 class="font-bold text-slate-800 text-base">Daftar Akun Pengguna</h3>
            <p class="text-xs text-slate-500">Kelola akun pengguna dan hak akses peran (Admin, Kepala Desa, Wakil Kepala Desa, Operator)</p>
        </div>

        <button @click="addModal = true" class="px-4 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl text-xs transition shadow-sm">
            + Tambah User Baru
        </button>
    </div>

    <!-- Users Table -->
    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="border-b border-slate-200 text-slate-500 uppercase tracking-wider bg-slate-50">
                        <th class="p-4">Nama Lengkap</th>
                        <th class="p-4">Username</th>
                        <th class="p-4">Email</th>
                        <th class="p-4">Role</th>
                        <th class="p-4">Status</th>
                        <th class="p-4">Terakhir Login</th>
                        <th class="p-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium">
                    @foreach($users as $u)
                    <tr class="hover:bg-slate-50 transition">
                        <td class="p-4 font-bold text-slate-800">{{ $u->name }}</td>
                        <td class="p-4 font-mono text-slate-600">{{ $u->username }}</td>
                        <td class="p-4 text-slate-500">{{ $u->email ?? '-' }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded text-[11px] font-semibold uppercase tracking-wider bg-slate-900 text-emerald-400">
                                {{ $u->role }}
                            </span>
                        </td>
                        <td class="p-4">
                            @if($u->is_active)
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-100 text-emerald-700">Aktif</span>
                            @else
                            <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-rose-100 text-rose-700">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="p-4 font-mono text-slate-400">
                            {{ $u->last_login_at ? \Carbon\Carbon::parse($u->last_login_at)->diffForHumans() : 'Belum Pernah' }}
                        </td>
                        <td class="p-4 text-center">
                            <button @click="editModal = true; selectedUser = { id: {{ $u->id }}, name: '{{ addslashes($u->name) }}', username: '{{ addslashes($u->username) }}', email: '{{ addslashes($u->email ?? '') }}', role: '{{ $u->role }}', is_active: {{ $u->is_active ? 1 : 0 }} }"
                                class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-lg text-xs transition">
                                Edit Akun
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah User -->
    <div x-show="addModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl" @click.away="addModal = false">
            <h3 class="font-bold text-slate-800 text-lg mb-4">Tambah Akun Pengguna Baru</h3>
            <form action="{{ route('users.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Username</label>
                    <input type="text" name="username" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email (Opsional)</label>
                    <input type="email" name="email" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Password</label>
                    <input type="password" name="password" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Role / Peran</label>
                    <select name="role" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="operator">Operator Absen</option>
                        <option value="kepala_desa">Kepala Desa</option>
                        <option value="wakil_kepala_desa">Wakil Kepala Desa</option>
                        <option value="admin">Administrator System</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="addModal = false" class="px-4 py-2 text-slate-500 hover:text-slate-800 font-semibold text-sm">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white font-semibold text-sm rounded-xl">Simpan User</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit User -->
    <div x-show="editModal" x-cloak class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl" @click.away="editModal = false">
            <h3 class="font-bold text-slate-800 text-lg mb-4">Edit Akun Pengguna</h3>
            <form :action="'/users/' + selectedUser.id" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Nama Lengkap</label>
                    <input type="text" name="name" x-model="selectedUser.name" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Username</label>
                    <input type="text" name="username" x-model="selectedUser.username" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Email</label>
                    <input type="email" name="email" x-model="selectedUser.email" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Password Baru (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Role / Peran</label>
                    <select name="role" x-model="selectedUser.role" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="operator">Operator Absen</option>
                        <option value="kepala_desa">Kepala Desa</option>
                        <option value="wakil_kepala_desa">Wakil Kepala Desa</option>
                        <option value="admin">Administrator System</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Status Akun</label>
                    <select name="is_active" x-model="selectedUser.is_active" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option :value="1">Aktif</option>
                        <option :value="0">Non-Aktif</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-3 pt-3 border-t border-slate-100">
                    <button type="button" @click="editModal = false" class="px-4 py-2 text-slate-500 hover:text-slate-800 font-semibold text-sm">Batal</button>
                    <button type="submit" class="px-4 py-2 bg-emerald-600 text-white font-semibold text-sm rounded-xl">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
