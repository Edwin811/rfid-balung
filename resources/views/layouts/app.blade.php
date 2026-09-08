<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Presensi Biometrik & RFID Desa Balunglor</title>
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
                        <span class="bg-white/15 text-white border border-white/20 font-bold text-[10px] px-2.5 py-0.5 rounded-full uppercase tracking-widest">PROSENDI UNEJ 2026</span>
                    </div>
                    <p class="text-xs text-red-100 font-medium mt-0.5">Sistem Informasi Presensi Biometrik & RFID Terintegrasi Aparatur Desa</p>
                </div>
            </div>

            <!-- User Status Card & Logout -->
            <div class="hidden md:flex items-center space-x-4 bg-jember-dark/70 backdrop-blur-md px-4 py-2.5 rounded-2xl border border-red-500/30 shadow-inner">
                <div class="text-right">
                    <div class="font-extrabold text-sm text-white leading-tight">{{ Auth::user()->name }}</div>
                    <div class="text-[10px] text-amber-300 font-black uppercase tracking-widest mt-0.5">{{ Auth::user()->role }}</div>
                </div>

                <button type="button" @click="$dispatch('open-logout-modal')"
                    class="p-2.5 rounded-xl bg-red-800/80 hover:bg-red-700 text-red-100 hover:text-white transition shadow-sm border border-red-600/40" title="Keluar dari Sistem">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </div>
        </div>

        <!-- Navigation Bar (Tabs Bar) -->
        <div class="bg-jember-dark/95 border-t border-red-900/90 px-4 sm:px-6 shadow-inner">
            <div class="max-w-7xl mx-auto flex items-center overflow-x-auto space-x-1.5 py-1.5 text-xs font-bold scrollbar-none">
                <!-- 1. Beranda -->
                <a href="{{ route('dashboard') }}" class="px-4 py-2.5 rounded-xl whitespace-nowrap transition flex items-center space-x-2 {{ request()->routeIs('dashboard') ? 'bg-white text-jember-900 font-extrabold shadow-md' : 'text-red-100 hover:bg-red-900/70' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>Beranda</span>
                </a>

                <!-- 2. Data Presensi Harian -->
                <a href="{{ route('presensi.index') }}" class="px-4 py-2.5 rounded-xl whitespace-nowrap transition flex items-center space-x-2 {{ request()->routeIs('presensi.*') ? 'bg-white text-jember-900 font-extrabold shadow-md' : 'text-red-100 hover:bg-red-900/70' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span>Data Presensi Harian</span>
                </a>

                <!-- 3. Laporan dan Export -->
                <a href="{{ route('laporan.index') }}" class="px-4 py-2.5 rounded-xl whitespace-nowrap transition flex items-center space-x-2 {{ request()->routeIs('laporan.*') ? 'bg-white text-jember-900 font-extrabold shadow-md' : 'text-red-100 hover:bg-red-900/70' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Laporan & Export</span>
                </a>

                <!-- 4. Aparatur Desa & Perangkat Presensi -->
                <a href="{{ route('pegawai.index') }}" class="px-4 py-2.5 rounded-xl whitespace-nowrap transition flex items-center space-x-2 {{ request()->routeIs('pegawai.*') ? 'bg-white text-jember-900 font-extrabold shadow-md' : 'text-red-100 hover:bg-red-900/70' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V4a2 2 0 012-2h2a2 2 0 012 2v2m-6 0h6"/></svg>
                    <span>Aparatur Desa & Perangkat Presensi</span>
                </a>

                <!-- 5. Kelola Perizinan Aparatur Desa -->
                <a href="{{ route('perizinan.index') }}" class="px-4 py-2.5 rounded-xl whitespace-nowrap transition flex items-center space-x-2 {{ request()->routeIs('perizinan.*') ? 'bg-white text-jember-900 font-extrabold shadow-md' : 'text-red-100 hover:bg-red-900/70' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Kelola Perizinan Aparatur Desa</span>
                </a>

                <!-- 6. Pengguna (Bisa diakses oleh semua pengguna untuk akunnya sendiri, admin mengelola semua) -->
                <a href="{{ route('users.index') }}" class="px-4 py-2.5 rounded-xl whitespace-nowrap transition flex items-center space-x-2 {{ request()->routeIs('users.*') ? 'bg-white text-jember-900 font-extrabold shadow-md' : 'text-red-100 hover:bg-red-900/70' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Pengguna</span>
                </a>

                @if(Auth::user()->isAdmin())
                <!-- 7. Backup Database (Khusus Admin) -->
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
        <!-- Global Flash Alert Success (Modern Dismissible Style) -->
        @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-transition class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-2xl mb-6 shadow-sm flex items-center justify-between transition-all duration-300">
            <div class="flex items-center space-x-3 pr-4">
                <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-xs font-bold leading-relaxed">{{ session('success') }}</span>
            </div>
            <button @click="show = false" type="button" class="text-emerald-500 hover:text-emerald-800 hover:bg-emerald-100/80 p-1.5 rounded-xl transition flex items-center justify-center shrink-0" title="Tutup Notifikasi">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        @endif

        <!-- Global Flash Alert Error (Modern Dismissible Style) -->
        @if(session('error'))
        <div x-data="{ show: true }" x-show="show" x-transition class="bg-rose-50 border border-rose-200 text-rose-800 px-5 py-4 rounded-2xl mb-6 shadow-sm flex items-center justify-between transition-all duration-300">
            <div class="flex items-center space-x-3 pr-4">
                <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-xs font-bold leading-relaxed">{{ session('error') }}</span>
            </div>
            <button @click="show = false" type="button" class="text-rose-500 hover:text-rose-800 hover:bg-rose-100/80 p-1.5 rounded-xl transition flex items-center justify-center shrink-0" title="Tutup Notifikasi">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
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
                    <p class="text-slate-500 mt-0.5">Sistem Informasi Presensi Biometrik & RFID Terintegrasi</p>
                </div>
            </div>
            <div class="text-right text-slate-500 font-medium">
                <span>(PROSENDI 2026 | FASILKOM X TEKNIK ELEKTRO UNIVERSITAS JEMBER)</span>
            </div>
        </div>
    </footer>

    <!-- Global Custom Confirmation Modal (Menggantikan Dialog Bawaan Browser) -->
    <div x-data="{
        open: false,
        title: 'Konfirmasi Tindakan',
        message: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
        confirmText: 'Ya, Lanjutkan',
        theme: 'danger',
        targetForm: null,

        confirmAction(detail) {
            this.title = detail.title || 'Konfirmasi Tindakan';
            this.message = detail.message || 'Apakah Anda yakin ingin melanjutkan?';
            this.confirmText = detail.confirmText || 'Ya, Lanjutkan';
            this.theme = detail.theme || 'danger';
            this.targetForm = detail.form;
            this.open = true;
        },

        execute() {
            if (this.targetForm) {
                this.targetForm.dataset.confirmed = 'true';
                this.targetForm.submit();
            }
            this.open = false;
        }
    }"
    @trigger-confirm.window="confirmAction($event.detail)"
    x-show="open"
    x-cloak
    class="fixed inset-0 bg-slate-950/70 backdrop-blur-md flex items-center justify-center p-4 z-[999999] transition-all duration-200">

        <div class="bg-white rounded-3xl p-6 sm:p-7 max-w-md w-full shadow-2xl border border-slate-200 transform transition-all scale-100 animate-in fade-in zoom-in duration-200" @click.away="open = false">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center shadow-inner flex-shrink-0"
                    :class="theme === 'danger' ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-amber-100 text-amber-800 border border-amber-200'">
                    <template x-if="theme === 'danger'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </template>
                    <template x-if="theme !== 'danger'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    </template>
                </div>
                <div>
                    <span class="text-[10px] font-black uppercase tracking-widest px-2.5 py-0.5 rounded-full"
                        :class="theme === 'danger' ? 'bg-red-100 text-red-800 border border-red-200' : 'bg-amber-100 text-amber-800 border border-amber-200'">KONFIRMASI SISTEM</span>
                    <h4 class="font-extrabold text-slate-900 text-base mt-1" x-text="title"></h4>
                </div>
            </div>

            <p class="text-xs text-slate-600 font-medium leading-relaxed mb-6" x-text="message"></p>

            <div class="flex items-center justify-end space-x-3 pt-4 border-t border-slate-100">
                <button type="button" @click="open = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-extrabold text-xs transition border border-slate-300">
                    Batal
                </button>
                <button type="button" @click="execute()"
                    class="px-5 py-2.5 rounded-xl font-extrabold text-xs text-white shadow-md transition flex items-center space-x-2"
                    :class="theme === 'danger' ? 'bg-red-700 hover:bg-red-800' : 'bg-slate-900 hover:bg-slate-800'">
                    <span x-text="confirmText"></span>
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('submit', function(e) {
            const form = e.target;
            const confirmMsg = form.getAttribute('data-confirm') || form.dataset.confirm;

            if (confirmMsg && !form.dataset.confirmed) {
                e.preventDefault();
                const title = form.getAttribute('data-confirm-title') || 'Konfirmasi Tindakan';
                const btnText = form.getAttribute('data-confirm-btn') || 'Ya, Lanjutkan';
                const theme = form.getAttribute('data-confirm-theme') || 'danger';

                window.dispatchEvent(new CustomEvent('trigger-confirm', {
                    detail: {
                        title: title,
                        message: confirmMsg,
                        form: form,
                        confirmText: btnText,
                        theme: theme
                    }
                }));
            }
        });
    </script>

    <!-- Modal Konfirmasi Logout Global (Selalu Presisi di Tengah Layar) -->
    <div x-data="{ openLogout: false }" @open-logout-modal.window="openLogout = true">
        <div x-show="openLogout" x-cloak class="fixed inset-0 z-[99999] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
            <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl border border-slate-200 text-left transform transition-all" @click.away="openLogout = false">
                <div class="text-center mb-5">
                    <div class="w-12 h-12 bg-red-100 text-red-700 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </div>
                    <h3 class="font-extrabold text-slate-800 text-lg">Konfirmasi Keluar</h3>
                    <p class="text-xs text-slate-500 mt-1 font-medium">Apakah Anda yakin ingin keluar dari Sistem Presensi Desa Balunglor?</p>
                </div>

                <div class="flex items-center justify-center space-x-3">
                    <button type="button" @click="openLogout = false" class="w-1/2 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition border border-slate-200 text-center">
                        Batal
                    </button>
                    <form action="{{ route('logout') }}" method="POST" class="w-1/2">
                        @csrf
                        <button type="submit" class="w-full py-2.5 bg-red-700 hover:bg-red-800 text-white font-bold text-xs rounded-xl shadow transition text-center">
                            Ya, Keluar
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
