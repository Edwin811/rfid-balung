<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Presensi Desa Balung Lor</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 font-sans min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-slate-800 border border-slate-700 rounded-2xl p-8 shadow-2xl">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 bg-emerald-500 rounded-2xl flex items-center justify-center text-slate-900 font-bold text-3xl mx-auto mb-4 shadow-lg shadow-emerald-500/30">
                BL
            </div>
            <h1 class="text-2xl font-bold text-slate-100">Sistem Presensi Biometrik</h1>
            <p class="text-sm text-slate-400 mt-1">Kantor Desa Balung Lor — Jember</p>
        </div>

        @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-950/60 border border-rose-800 text-rose-300 text-sm">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('login') }}" method="POST" class="space-y-5">
            @csrf

            <div>
                <label for="username" class="block text-sm font-medium text-slate-300 mb-2">Username</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                    class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                    placeholder="Masukkan username Anda">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Password</label>
                <input type="password" id="password" name="password" required
                    class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-slate-700 text-slate-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                    placeholder="••••••••">
            </div>

            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded bg-slate-900 border-slate-700 text-emerald-500 focus:ring-emerald-500">
                    <span class="text-slate-400">Ingat Saya</span>
                </label>
            </div>

            <button type="submit"
                class="w-full py-3.5 px-4 bg-emerald-600 hover:bg-emerald-500 text-white font-semibold rounded-xl shadow-lg shadow-emerald-600/30 hover:shadow-emerald-500/40 transition duration-200">
                Masuk ke Sistem
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-700/60 text-center text-xs text-slate-500">
            PROSENDI 2026 — Universitas Jember & Desa Balung Lor
        </div>
    </div>

</body>
</html>
