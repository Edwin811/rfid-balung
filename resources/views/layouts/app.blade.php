<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Sistem Presensi Biometrik Desa Balunglor</title>
    <!-- Favicon Image logo.png -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js CDN -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'sans-serif'],
                    },
                    colors: {
                        jember: {
                            50: '#fff1f2',
                            100: '#ffe4e6',
                            600: '#e11d48',
                            700: '#be123c',
                            800: '#9f1239',
                            900: '#881337',
                            dark: '#580a22',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans antialiased min-h-screen flex flex-col" x-data="{ sidebarOpen: false }">

    <!-- Top Bar Portal -->
    <div class="bg-slate-950 text-slate-300 text-xs px-4 py-2 flex items-center justify-between border-b border-slate-800/80">
        <div class="flex items-center space-x-3">
            <span class="bg-jember-800 text-white font-extrabold px-2.5 py-0.5 rounded text-[10px] tracking-wider uppercase shadow-sm">PORTAL BIOMETRIK OFF-LINE</span>
            <span class="hidden sm:inline text-slate-400 font-medium">Pemerintah Kabupaten Jember &bull; Kecamatan Balung &bull; Kode Desa: 35.09.10.2005</span>
        </div>
        <div class="flex items-center space-x-4">
            <span class="text-slate-300 font-medium">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}</span>
            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping" title="Koneksi Jaringan Lokal OK"></span>
        </div>
    </div>

    <!-- Main Header Banner (Red Pemkab Jember Theme with Official Logo) -->
    <header class="bg-gradient-to-r from-jember-dark via-jember-900 to-jember-800 text-white shadow-lg relative overflow-hidden">
        <!-- Subtle Decorative Background Overlay -->
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-red-500/10 via-transparent to-transparent pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between relative z-10">
            <div class="flex items-center space-x-4">
                <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-white hover:text-red-200 focus:outline-none p-1">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                
                <!-- Official Logo Badge -->
                <div class="w-14 h-14 rounded-2xl bg-white p-2 flex items-center justify-center shadow-xl border-2 border-amber-300 flex-shrink-0 transition transform hover:scale-105">
                    <img src="{{ asset('logo.png') }}" alt="Logo Desa Balunglor" class="w-full h-full object-contain">
                </div>

                <div>
                    <div class="flex items-center space-x-2.5">
                        <h1 class="text-xl sm:text-2xl font-extrabold tracking-tight drop-shadow-sm">Desa Balunglor</h1>
                        <span class="bg-gradient-to-r from-amber-400 to-amber-500 text-slate-950 font-black text-[10px] px-2.5 py-0.5 rounded-full uppercase tracking-widest shadow-sm">PROSENDI 2026</span>
                    </div>
                    <p class="text-xs text-red-100 font-medium mt-0.5">Sistem Informasi & Presensi Biometrik Terintegrasi Aparatur Desa</p>
                </div>
            </div>

            <!-- User Status Card & Logout -->
            <div class="hidden md:flex items-center space-x-4 bg-jember-dark/70 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-red-500/30 shadow-inner">
                <div class="text-right">
                    <div class="font-extrabold text-sm text-white leading-tight">{{ Auth::user()->name }}</div>
                    <div class="text-[10px] text-amber-300 font-black uppercase tracking-widest mt-0.5">{{ Auth::user()->role }}</div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2.5 rounded-xl bg-red-800/80 hover:bg-red-700 text-red-100 hover:text-white transition shadow-sm border border-red-600/40" title="Keluar dari Sistem">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>

        <!-- Navigation Bar (Tabs Bar) -->
        <div class="bg-jember-dark/95 border-t border-red-900/90 px-4 sm:px-6 shadow-inner">
            <div class="max-w-7xl mx-auto flex items-center overflow-x-auto space-x-1.5 py-1.5 text-xs font-bold scrollbar-none">
                <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-xl whitespace-nowrap transition flex items-center space-x-2 {{ request()->routeIs('dashboard') ? 'bg-white text-jember-900 font-extrabold shadow-md' : 'text-red-100 hover:bg-red-900/70' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Beranda Dashboard</span>
                </a>

                <a href="{{ route('presensi.index') }}" class="px-4 py-2.5 rounded-xl whitespace-nowrap transition flex items-center space-x-2 {{ request()->routeIs('presensi.*') ? 'bg-white text-jember-900 font-extrabold shadow-md' : 'text-red-100 hover:bg-red-900/70' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Data Presensi Harian</span>
                </a>

                <a href="{{ route('perizinan.index') }}" class="px-4 py-2.5 rounded-xl whitespace-nowrap transition flex items-center space-x-2 {{ request()->routeIs('perizinan.*') ? 'bg-white text-jember-900 font-extrabold shadow-md' : 'text-red-100 hover:bg-red-900/70' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Kelola Perizinan</span>
                </a>

                <a href="{{ route('laporan.index') }}" class="px-4 py-2.5 rounded-xl whitespace-nowrap transition flex items-center space-x-2 {{ request()->routeIs('laporan.*') ? 'bg-white text-jember-900 font-extrabold shadow-md' : 'text-red-100 hover:bg-red-900/70' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Laporan & Export</span>
                </a>

                @if(Auth::user()->hasRole(['admin', 'kepala_desa', 'wakil_kepala_desa']))
                <a href="{{ route('audit_log.index') }}" class="px-4 py-2.5 rounded-xl whitespace-nowrap transition flex items-center space-x-2 {{ request()->routeIs('audit_log.*') ? 'bg-white text-jember-900 font-extrabold shadow-md' : 'text-red-100 hover:bg-red-900/70' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Audit Log</span>
                </a>
                @endif

                @if(Auth::user()->isAdmin())
                <a href="{{ route('users.index') }}" class="px-4 py-2.5 rounded-xl whitespace-nowrap transition flex items-center space-x-2 {{ request()->routeIs('users.*') ? 'bg-white text-jember-900 font-extrabold shadow-md' : 'text-red-100 hover:bg-red-900/70' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Pengguna</span>
                </a>

                <a href="{{ route('backup.index') }}" class="px-4 py-2.5 rounded-xl whitespace-nowrap transition flex items-center space-x-2 {{ request()->routeIs('backup.*') ? 'bg-white text-jember-900 font-extrabold shadow-md' : 'text-red-100 hover:bg-red-900/70' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                    <span>Backup Database</span>
                </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Page Body Container -->
    <main class="max-w-7xl w-full mx-auto px-4 sm:px-6 py-6 flex-1">
        @if(session('success'))
        <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-300 text-emerald-900 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full bg-emerald-600 text-white flex items-center justify-center font-bold">✓</div>
                <p class="text-sm font-semibold">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mb-5 p-4 rounded-xl bg-red-50 border border-red-300 text-red-900 flex items-center justify-between shadow-sm">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center font-bold">!</div>
                <p class="text-sm font-semibold">{{ session('error') }}</p>
            </div>
        </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer Official Style -->
    <footer class="bg-slate-900 text-slate-400 text-xs py-6 border-t border-slate-800 mt-auto">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="flex items-center space-x-3">
                <img src="{{ asset('logo.png') }}" alt="Logo Desa Balunglor" class="h-8 w-auto">
                <div>
                    <p class="font-bold text-slate-200">Pemerintah Desa Balunglor &copy; 2026</p>
                    <p class="text-slate-500 mt-0.5">Sistem Presensi Biometrik Terintegrasi (PROSENDI 2026 &bull; Universitas Jember)</p>
                </div>
            </div>
            <div class="text-right text-slate-500 font-medium">
                <span>Mesin Absensi: Magic MP340 (IP: 169.254.176.250 / 192.168.1.200)</span>
            </div>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
