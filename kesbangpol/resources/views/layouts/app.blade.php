<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @auth
        <meta name="vapid-public-key" content="{{ config('webpush.vapid.public_key') }}">
    @endauth
    <title>@yield('title', 'Dashboard') - Kesbangpol Kota Palembang</title>
    
    <!-- Google Fonts: Inter & Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        gov: {
                            50: '#f0f5fa',
                            100: '#e1ebf5',
                            200: '#c7d9eb',
                            500: '#1e5388',
                            600: '#163a5f', // Deep Navy / Primary
                            700: '#112e4d',
                            800: '#0d233c',
                            900: '#081729',
                        }
                    },
                    fontFamily: {
                        sans: ['"Inter"', '"Plus Jakarta Sans"', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Hotwire Turbo for SPA Seamless Page Navigation & Form Submissions without full reloads -->
    <script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@7.3.0/dist/turbo.es2017-umd.js"></script>

    <script>
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    <style>
        [x-cloak] { display: none !important; }
        .turbo-progress-bar {
            height: 3px !important;
            background-color: #163a5f !important;
            background-image: linear-gradient(to right, #1e5388, #f59e0b) !important;
        }
    </style>
</head>
<body class="h-full bg-[#F8FAFC] dark:bg-slate-900 text-slate-800 dark:text-slate-100 font-sans transition-colors duration-200 flex flex-col min-h-screen antialiased">

    <!-- Government Header Banner / Topbar -->
    <header class="bg-gov-600 dark:bg-slate-950 text-white text-xs py-1.5 px-4 border-b border-gov-700 dark:border-slate-800">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center gap-2 font-medium text-slate-200">
                <i class="fa-solid fa-building-columns text-slate-300"></i>
                <span>PORTAL RESMI BADAN KESATUAN BANGSA DAN POLITIK KOTA PALEMBANG</span>
            </div>
            <div class="hidden sm:flex items-center gap-4 text-slate-300">
                <span><i class="fa-solid fa-phone me-1 text-slate-400"></i> Layanan Informasi Ormas</span>
            </div>
        </div>
    </header>

    <!-- Main Navigation Bar -->
    <nav x-data="{ mobileOpen: false, darkMode: localStorage.getItem('theme') === 'dark' }" class="bg-white dark:bg-slate-900 border-b border-slate-200 dark:border-slate-800 sticky top-0 z-40 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                
                <!-- Logo & Brand Title -->
                <div class="flex items-center space-x-3">
                    <a href="{{ route('home') }}" class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gov-600 dark:bg-gov-500 text-white flex items-center justify-center font-bold text-lg shadow-sm">
                            <i class="fa-solid fa-shield"></i>
                        </div>
                        <div>
                            <span class="font-extrabold text-base sm:text-lg tracking-tight text-gov-600 dark:text-white block leading-tight">
                                KESBANGPOL PALEMBANG
                            </span>
                            <span class="block text-[11px] font-medium text-slate-500 dark:text-slate-400">Sistem Informasi Pengelolaan Ormas</span>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation Links -->
                <div class="hidden md:flex md:items-center md:space-x-1">
                    @auth
                        @if(auth()->user()->isUser())
                            <a href="{{ route('user.dashboard') }}" class="px-3 py-2 rounded-lg text-xs font-bold transition-colors {{ request()->routeIs('user.dashboard') ? 'bg-gov-50 text-gov-600 dark:bg-slate-800 dark:text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('user.history') }}" class="px-3 py-2 rounded-lg text-xs font-bold transition-colors {{ request()->routeIs('user.history*') ? 'bg-gov-50 text-gov-600 dark:bg-slate-800 dark:text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                Semua Berkas
                            </a>
                            <a href="{{ route('user.chat') }}" data-turbo="false" class="px-3 py-2 rounded-lg text-xs font-bold transition-colors relative {{ request()->routeIs('user.chat') ? 'bg-gov-50 text-gov-600 dark:bg-slate-800 dark:text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                Chat & Konsultasi
                                <span id="user-chat-badge" class="hidden absolute -top-1 -right-1 px-1.5 py-0.5 text-[10px] font-extrabold text-white bg-emerald-600 rounded-full animate-pulse">0</span>
                            </a>
                            <a href="{{ route('user.notifications') }}" title="Semua history notifikasi akan terhapus otomatis dalam jangka waktu maksimal 30 hari" class="px-3 py-2 rounded-lg text-xs font-bold transition-colors relative {{ request()->routeIs('user.notifications*') ? 'bg-gov-50 text-gov-600 dark:bg-slate-800 dark:text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                Notifikasi
                                <span id="user-notif-badge" class="hidden absolute -top-1 -right-1 px-1.5 py-0.5 text-[10px] font-extrabold text-white bg-rose-600 rounded-full animate-pulse">0</span>
                            </a>
                            <a href="{{ route('user.profile') }}" class="px-3 py-2 rounded-lg text-xs font-bold transition-colors {{ request()->routeIs('user.profile') ? 'bg-gov-50 text-gov-600 dark:bg-slate-800 dark:text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                Profile
                            </a>
                        @elseif(auth()->user()->isAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="px-2.5 py-2 rounded-lg text-xs font-bold transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-gov-50 text-gov-600 dark:bg-slate-800 dark:text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                Dashboard
                            </a>
                            <a href="{{ route('admin.submissions.index') }}" class="px-2.5 py-2 rounded-lg text-xs font-bold transition-colors {{ request()->routeIs('admin.submissions*') ? 'bg-gov-50 text-gov-600 dark:bg-slate-800 dark:text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                Berkas Masuk
                            </a>
                            <a href="{{ route('admin.organizations') }}" class="px-2.5 py-2 rounded-lg text-xs font-bold transition-colors {{ request()->routeIs('admin.organizations*') ? 'bg-gov-50 text-gov-600 dark:bg-slate-800 dark:text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                Data Organisasi
                            </a>
                            <a href="{{ route('admin.users') }}" class="px-2.5 py-2 rounded-lg text-xs font-bold transition-colors {{ request()->routeIs('admin.users*') ? 'bg-gov-50 text-gov-600 dark:bg-slate-800 dark:text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                Data User
                            </a>
                            <a href="{{ route('admin.chat.index') }}" data-turbo="false" class="px-2.5 py-2 rounded-lg text-xs font-bold transition-colors relative {{ request()->routeIs('admin.chat*') ? 'bg-gov-50 text-gov-600 dark:bg-slate-800 dark:text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                Chat Ormas
                                <span id="admin-chat-badge" class="hidden absolute -top-1 -right-1 px-1.5 py-0.5 text-[10px] font-extrabold text-white bg-emerald-600 rounded-full animate-pulse">0</span>
                            </a>
                            <a href="{{ route('admin.notifications') }}" title="Semua history notifikasi akan terhapus otomatis dalam jangka waktu maksimal 30 hari" class="px-2.5 py-2 rounded-lg text-xs font-bold transition-colors relative {{ request()->routeIs('admin.notifications*') ? 'bg-gov-50 text-gov-600 dark:bg-slate-800 dark:text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                Notifikasi
                            </a>

                            <a href="{{ route('admin.activity_logs') }}" class="px-2.5 py-2 rounded-lg text-xs font-bold transition-colors {{ request()->routeIs('admin.activity_logs*') ? 'bg-gov-50 text-gov-600 dark:bg-slate-800 dark:text-white' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800' }}">
                                Aktivitas
                            </a>
                        @endif
                    @endauth

                    <!-- Dark Mode Toggle Button -->
                    <button @click="darkMode = !darkMode; if (darkMode) { document.documentElement.classList.add('dark'); localStorage.setItem('theme', 'dark'); } else { document.documentElement.classList.remove('dark'); localStorage.setItem('theme', 'light'); }"
                            type="button" class="p-2 text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 rounded-lg transition-colors ms-2" title="Toggle Dark/Light Mode">
                        <i x-show="!darkMode" class="fa-solid fa-moon text-slate-600"></i>
                        <i x-show="darkMode" class="fa-solid fa-sun text-amber-400" x-cloak></i>
                    </button>

                    <!-- User Identity Badge in Header -->
                    @auth
                        <div class="ps-3 ms-2 border-l border-slate-200 dark:border-slate-700 flex items-center gap-3">
                            <div class="text-right">
                                <span class="block text-xs font-extrabold text-slate-900 dark:text-white">
                                    {{ auth()->user()->name }}
                                </span>
                                <span class="block text-[11px] font-medium text-slate-500 dark:text-slate-400">
                                    {{ auth()->user()->organization?->name ?: (auth()->user()->isAdmin() ? auth()->user()->position : 'Pengurus Ormas') }}
                                </span>
                            </div>

                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" title="Logout">
                                    Logout
                                </button>
                            </form>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="ms-3 px-4 py-2 text-xs font-bold rounded-lg bg-gov-600 hover:bg-gov-700 text-white transition-colors">
                            Masuk Akun
                        </a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <div class="flex items-center md:hidden space-x-2">
                    <button @click="darkMode = !darkMode; if (darkMode) { document.documentElement.classList.add('dark'); localStorage.setItem('theme', 'dark'); } else { document.documentElement.classList.remove('dark'); localStorage.setItem('theme', 'light'); }"
                            type="button" class="p-2 text-slate-500 dark:text-slate-400">
                        <i x-show="!darkMode" class="fa-solid fa-moon text-slate-600"></i>
                        <i x-show="darkMode" class="fa-solid fa-sun text-amber-400" x-cloak></i>
                    </button>
                    <button @click="mobileOpen = !mobileOpen" type="button" class="p-2 text-slate-600 dark:text-slate-300 focus:outline-none">
                        <i class="fa-solid" :class="mobileOpen ? 'fa-xmark text-lg' : 'fa-bars text-lg'"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Nav Menu -->
        <div x-show="mobileOpen" x-cloak @click.away="mobileOpen = false" class="md:hidden border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 px-4 pt-2 pb-4 space-y-1 text-xs font-semibold">
            @auth
                <div class="py-2 border-b border-slate-100 dark:border-slate-800 mb-2">
                    <span class="block font-bold text-slate-900 dark:text-white">{{ auth()->user()->name }}</span>
                    <span class="block text-[11px] text-slate-500">{{ auth()->user()->organization?->name ?: auth()->user()->position }}</span>
                </div>

                @if(auth()->user()->isUser())
                    <a href="{{ route('user.dashboard') }}" class="block px-3 py-2 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Dashboard
                    </a>
                    <a href="{{ route('user.history') }}" class="block px-3 py-2 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Semua Berkas
                    </a>
                    <a href="{{ route('user.chat') }}" data-turbo="false" class="block px-3 py-2 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Chat & Konsultasi
                    </a>
                    <a href="{{ route('user.notifications') }}" class="block px-3 py-2 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Notifikasi
                    </a>
                    <a href="{{ route('user.profile') }}" class="block px-3 py-2 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Profile
                    </a>
                @elseif(auth()->user()->isAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="block px-3 py-2 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Dashboard Admin
                    </a>
                    <a href="{{ route('admin.submissions.index') }}" class="block px-3 py-2 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Berkas Masuk
                    </a>
                    <a href="{{ route('admin.organizations') }}" class="block px-3 py-2 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Data Organisasi
                    </a>
                    <a href="{{ route('admin.users') }}" class="block px-3 py-2 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Data User
                    </a>
                    <a href="{{ route('admin.chat.index') }}" data-turbo="false" class="block px-3 py-2 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Chat Ormas
                    </a>
                    <a href="{{ route('admin.notifications') }}" class="block px-3 py-2 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Notifikasi
                    </a>

                    <a href="{{ route('admin.activity_logs') }}" class="block px-3 py-2 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Aktivitas
                    </a>
                    <a href="{{ route('admin.profile') }}" class="block px-3 py-2 rounded-md text-slate-700 dark:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800">
                        Profile Admin
                    </a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="pt-2">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 text-rose-600 font-bold">
                        Logout
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="block w-full text-center px-4 py-2 bg-gov-600 text-white rounded-md font-bold">
                    Masuk Akun
                </a>
            @endauth
        </div>
    </nav>

    <!-- Main Body Container -->
    <main class="flex-grow">
        @if(session('success'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="p-3.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 text-emerald-900 dark:text-emerald-200 text-xs font-semibold flex items-center justify-between shadow-sm">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        @endif


        @if(session('error'))
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-4">
                <div x-data="{ show: true }" x-show="show" class="p-3.5 rounded-lg bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 text-rose-900 dark:text-rose-200 text-xs font-semibold flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-rose-500 hover:text-rose-700">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-slate-900 border-t border-slate-200 dark:border-slate-800 py-6 mt-12 transition-colors">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-3 text-xs text-slate-500 dark:text-slate-400">
                <div class="flex items-center gap-2">
                    <span class="font-bold text-slate-700 dark:text-slate-300">Badan Kesatuan Bangsa dan Politik Kota Palembang</span>
                </div>
                <div>
                    &copy; {{ date('Y') }} Kesbangpol Kota Palembang. Permendagri No. 57 Tahun 2017.
                </div>
            </div>
        </div>
    </footer>

    @auth
    <script>
        function checkGlobalBadges() {
            if (document.hidden) return;

            @if(auth()->user()->isUser())
                // Fetch User Chat Unread
                fetch("{{ route('user.chat.unread_count') }}")
                    .then(r => r.json())
                    .then(data => {
                        const badge = document.getElementById('user-chat-badge');
                        if (badge) {
                            if (data.unread_count > 0) {
                                badge.innerText = data.unread_count;
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                    }).catch(() => {});

                // Fetch User Notification Unread
                fetch("{{ route('user.notifications.fetch_unread') }}")
                    .then(r => r.json())
                    .then(data => {
                        const badge = document.getElementById('user-notif-badge');
                        if (badge) {
                            if (data.unread_count > 0) {
                                badge.innerText = data.unread_count;
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                    }).catch(() => {});
            @elseif(auth()->user()->isAdmin())
                // Fetch Admin Chat Unread
                fetch("{{ route('admin.chat.unread_count') }}")
                    .then(r => r.json())
                    .then(data => {
                        const badge = document.getElementById('admin-chat-badge');
                        if (badge) {
                            if (data.unread_count > 0) {
                                badge.innerText = data.unread_count;
                                badge.classList.remove('hidden');
                            } else {
                                badge.classList.add('hidden');
                            }
                        }
                    }).catch(() => {});
            @endif
        }

        document.addEventListener('DOMContentLoaded', () => {
            checkGlobalBadges();
            if (!window.globalBadgeInterval) {
                window.globalBadgeInterval = setInterval(checkGlobalBadges, 15000);
            }
        });

        document.addEventListener('turbo:load', () => {
            checkGlobalBadges();
        });
    </script>
    <script src="{{ asset('js/webpush.js') }}"></script>
    @endauth

    @stack('scripts')
</body>
</html>
