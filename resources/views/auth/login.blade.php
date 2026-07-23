<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Presensi Desa Balunglor</title>
    <!-- Favicon Image logo.png -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        jember: {
                            800: '#9f1239',
                            900: '#881337',
                            dark: '#580a22',
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-100 text-slate-800 font-sans min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md bg-white border border-slate-200 rounded-3xl shadow-2xl overflow-hidden">
        <!-- Header Banner Merah Pemkab Jember / Desa Balunglor -->
        <div class="bg-gradient-to-r from-jember-dark via-jember-900 to-jember-800 text-white p-7 text-center relative">
            <div class="w-20 h-20 bg-white p-2.5 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl border-2 border-amber-300 transition transform hover:scale-105">
                <img src="{{ asset('logo.png') }}" alt="Logo Desa Balunglor" class="w-full h-full object-contain">
            </div>
            <span class="inline-block bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 font-black text-[10px] px-3 py-0.5 rounded-full uppercase tracking-widest mb-2 shadow-sm">PPID DESA BALUNGLOR</span>
            <h1 class="text-xl font-extrabold tracking-tight">Sistem Presensi Biometrik</h1>
            <p class="text-xs text-red-100 font-medium mt-1">Pemerintah Desa Balunglor, Kecamatan Balung, Jember</p>
        </div>

        <div class="p-6 sm:p-8">
            @if($errors->any())
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs">
                <ul class="list-disc list-inside space-y-1 font-medium">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="username" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Username</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}" required autofocus
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 focus:bg-white transition font-medium"
                        placeholder="Masukkan username anda">
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-slate-800 text-sm focus:outline-none focus:ring-2 focus:ring-red-600 focus:bg-white transition font-medium"
                        placeholder="••••••••">
                </div>

                <div class="flex items-center justify-between text-xs">
                    <label class="flex items-center space-x-2 cursor-pointer font-medium text-slate-600">
                        <input type="checkbox" name="remember" class="rounded border-slate-300 text-red-700 focus:ring-red-600">
                        <span>Ingat Saya</span>
                    </label>
                </div>

                <button type="submit"
                    class="w-full py-3.5 px-4 bg-red-700 hover:bg-red-800 text-white font-bold rounded-xl shadow-lg shadow-red-700/25 transition duration-150 text-sm tracking-wide">
                    MASUK SISTEM
                </button>
            </form>
        </div>

        <div class="bg-slate-50 px-6 py-4 border-t border-slate-100 text-center text-[11px] text-slate-500 font-medium">
            PROSENDI 2026 &bull; Universitas Jember & Pemerintah Desa Balunglor
        </div>
    </div>

</body>
</html>
