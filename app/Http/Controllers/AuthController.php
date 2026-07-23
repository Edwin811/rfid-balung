<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt(['username' => $credentials['username'], 'password' => $credentials['password'], 'is_active' => true], $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();
            $user->last_login_at = now();
            $user->save();

            AuditLogger::log('login', 'User', $user->id, null, ['username' => $user->username, 'role' => $user->role]);

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'username' => 'Kredensial yang Anda masukkan salah atau akun Anda tidak aktif.',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        if (Auth::check()) {
            AuditLogger::log('logout', 'User', Auth::id());
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
