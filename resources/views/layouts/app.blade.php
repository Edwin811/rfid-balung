<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - Presensi Desa Balung Lor</title>
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
                    colors: {
                        brand: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
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
<body class="bg-slate-50 text-slate-800 font-sans antialiased min-h-screen flex flex-col" x-data="{ sidebarOpen: false }">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-white flex-shrink-0 flex flex-col justify-between hidden md:flex">
            <div>
                <!-- Brand Header -->
                <div class="p-5 border-b border-slate-800 flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500 flex items-center justify-center text-slate-900 font-bold text-xl shadow-lg shadow-emerald-500/20">
                        BL
                    </div>
                    <div>
                        <h1 class="font-bold text-sm leading-tight text-slate-100">Desa Balung Lor</h1>
                        <p class="text-xs text-slate-400">Presensi Biometrik</p>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="p-4 space-y-1 text-sm font-medium">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>

                    <a href="{{ route('presensi.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('presensi.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                        Data Presensi
                    </a>

                    <a href="{{ route('perizinan.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('perizinan.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Kelola Perizinan
                    </a>

                    <a href="{{ route('laporan.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('laporan.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Laporan & Export
                    </a>

                    @if(Auth::user()->hasRole(['admin', 'kepala_desa', 'wakil_kepala_desa']))
                    <a href="{{ route('audit_log.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('audit_log.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Audit Log Activity
                    </a>
                    @endif

                    @if(Auth::user()->isAdmin())
                    <div class="pt-4 pb-1 text-xs font-semibold text-slate-500 uppercase tracking-wider px-3">
                        Administrasi
                    </div>

                    <a href="{{ route('users.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('users.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        Manajemen User
                    </a>

                    <a href="{{ route('backup.index') }}" class="flex items-center px-3 py-2.5 rounded-lg {{ request()->routeIs('backup.*') ? 'bg-emerald-600 text-white font-semibold' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                        Backup Database
                    </a>
                    @endif
                </nav>
            </div>

            <!-- Footer User Info -->
            <div class="p-4 border-t border-slate-800">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-slate-200">{{ Auth::user()->name }}</p>
                        <span class="inline-block text-[11px] px-2 py-0.5 rounded bg-emerald-950 text-emerald-400 font-mono border border-emerald-800 uppercase">
                            {{ Auth::user()->role }}
                        </span>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-slate-400 hover:text-rose-400 p-1.5 transition" title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="bg-white border-b border-slate-200 px-6 py-4 flex items-center justify-between sticky top-0 z-10 shadow-sm">
                <div class="flex items-center space-x-4">
                    <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-slate-600 hover:text-slate-900 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <h2 class="text-lg font-bold text-slate-800">@yield('title', 'Dashboard')</h2>
                </div>

                <div class="flex items-center space-x-4 text-xs font-medium">
                    <!-- Status Indikator Mesin -->
                    <div class="flex items-center space-x-2 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-slate-700">Mesin Magic MP340: <strong class="text-emerald-600">Aktif (169.254.176.250)</strong></span>
                    </div>

                    <div class="text-slate-500 hidden sm:block">
                        {{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}
                    </div>
                </div>
            </header>

            <!-- Page Body -->
            <main class="p-6 flex-1 max-w-7xl w-full mx-auto">
                @if(session('success'))
                <div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
                @endif

                @if(session('error'))
                <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-3">
                        <svg class="w-5 h-5 text-rose-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
