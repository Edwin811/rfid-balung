<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username'],
            'email' => ['nullable', 'email', 'max:100', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', 'in:admin,kepala_desa,wakil_kepala_desa,operator'],
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'username' => $request->input('username'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'role' => $request->input('role'),
            'is_active' => true,
        ]);

        AuditLogger::log('tambah_user', 'User', $user->id, null, ['username' => $user->username, 'role' => $user->role]);

        return back()->with('success', 'Akun pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $dataLama = $user->toArray();

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'unique:users,username,' . $id],
            'email' => ['nullable', 'email', 'max:100', 'unique:users,email,' . $id],
            'password' => ['nullable', 'string', 'min:6'],
            'role' => ['required', 'in:admin,kepala_desa,wakil_kepala_desa,operator'],
            'is_active' => ['required', 'boolean'],
        ]);

        $user->name = $request->input('name');
        $user->username = $request->input('username');
        $user->email = $request->input('email');
        $user->role = $request->input('role');
        $user->is_active = $request->boolean('is_active');

        if ($request->filled('password')) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        AuditLogger::log('edit_user', 'User', $user->id, $dataLama, $user->toArray());

        return back()->with('success', 'Akun pengguna berhasil diperbarui.');
    }
}
