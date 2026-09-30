<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'VIRA — Virtual Run, Ride & Walk Platform')</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #0B0F19;
            color: #F8FAFC;
        }
        .font-athletic {
            font-family: 'Bebas Neue', cursive, sans-serif;
            letter-spacing: 0.05em;
        }
        .font-mono-num {
            font-family: 'Space Grotesk', monospace;
        }
        .glow-orange {
            box-shadow: 0 0 25px rgba(255, 85, 0, 0.35);
        }
        .glow-cyan {
            box-shadow: 0 0 25px rgba(0, 229, 255, 0.3);
        }
        /* Mobile horizontal scroll without visible scrollbar */
        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-[#0B0F19] text-slate-100 selection:bg-[#FF5500] selection:text-white">

    <!-- Header & Navigation -->
    <header class="sticky top-0 z-50 backdrop-blur-xl bg-[#0B0F19]/90 border-b border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 sm:h-20 flex items-center justify-between">
            @if(request()->is('admin*'))
                <!-- ================= ADMIN HEADER ================= -->
                <!-- Brand Logo (Admin Panel) -->
                <a href="{{ Auth::check() ? route('admin.events.index') : route('home') }}" class="flex items-center gap-2.5 sm:gap-3 group">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-tr from-rose-600 to-[#FF5500] flex items-center justify-center font-athletic text-2xl text-white shadow-lg shadow-rose-950/40 group-hover:scale-105 transition-transform">
                        V
                    </div>
                    <div>
                        <span class="font-athletic text-2xl sm:text-3xl tracking-wider text-white">VIRA</span>
                        <span class="text-[10px] uppercase font-bold tracking-widest px-2 py-0.5 ml-1.5 rounded-full bg-rose-500/20 text-rose-400 border border-rose-500/30">Panel Admin</span>
                    </div>
                </a>

                @if(Auth::check() && !request()->routeIs('admin.login*'))
                    <!-- Desktop Navigation Links (Admin) -->
                    <nav class="hidden md:flex items-center gap-1.5 lg:gap-2">
                        <a href="{{ route('admin.events.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.events.*') || request()->routeIs('admin.designer.*') ? 'text-white bg-slate-800/80 border border-slate-700' : 'text-slate-300 hover:text-white hover:bg-slate-800/40' }} transition flex items-center gap-1.5">
                            <span>Kelola Event</span>
                            <span class="text-[9px] bg-rose-500/20 text-rose-400 font-bold px-1.5 py-0.5 rounded border border-rose-500/30">CAPI</span>
                        </a>
                        <a href="{{ route('admin.addons.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.addons.*') ? 'text-white bg-slate-800/80 border border-slate-700' : 'text-slate-300 hover:text-white hover:bg-slate-800/40' }} transition flex items-center gap-1.5">
                            <span>Item Add-ons</span>
                            <span class="text-[9px] bg-orange-500/20 text-[#FF5500] font-bold px-1.5 py-0.5 rounded border border-orange-500/30">Merch</span>
                        </a>
                        <a href="{{ route('admin.registrations.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.registrations.*') ? 'text-[#00E5FF] bg-slate-800/80 border border-slate-700' : 'text-slate-300 hover:text-white hover:bg-slate-800/40' }} transition flex items-center gap-1.5">
                            <span>Data Peserta</span>
                            <span class="text-[9px] bg-cyan-500/20 text-[#00E5FF] font-bold px-1.5 py-0.5 rounded border border-cyan-500/30">Live</span>
                        </a>
                        <a href="{{ route('admin.shipping-settings.index') }}" class="px-3.5 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('admin.shipping-settings.*') ? 'text-[#FF5500] bg-slate-800/80 border border-slate-700' : 'text-slate-300 hover:text-white hover:bg-slate-800/40' }} transition flex items-center gap-1.5">
                            <span>Ekspedisi SPX</span>
                            <span class="text-[9px] bg-orange-500/20 text-[#FF5500] font-bold px-1.5 py-0.5 rounded border border-orange-500/30">Ongkir</span>
                        </a>
                    </nav>

                    <!-- Admin Action & Profile & Logout -->
                    <div class="flex items-center gap-2.5 sm:gap-3">
                        <!-- Admin Profile Pill -->
                        <div class="hidden xl:flex items-center gap-2 pl-3 pr-2 py-1 rounded-xl bg-slate-900 border border-slate-800 text-xs">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span class="font-bold text-slate-200">{{ Auth::user()->name }}</span>
                            <span class="text-[10px] font-mono px-1.5 py-0.5 rounded bg-rose-500/15 text-rose-400 border border-rose-500/20 font-bold uppercase">{{ Auth::user()->role === 'SUPER_ADMIN' ? 'Super Admin' : 'Admin' }}</span>
                        </div>

                        <a href="{{ route('home') }}" target="_blank" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-bold text-slate-300 hover:text-white bg-slate-900 border border-slate-800 hover:border-slate-700 transition shadow-sm" title="Buka Halaman Publik">
                            <svg class="w-3.5 h-3.5 text-[#FF5500]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            <span>Lihat Website</span>
                        </a>

                        <!-- Logout Button -->
                        <form action="{{ route('admin.logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="px-3 py-2 rounded-xl text-xs font-bold text-rose-300 hover:text-white bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 hover:border-rose-500/50 transition flex items-center gap-1.5 shadow-sm"
                                    title="Keluar dari sesi admin">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                <span class="hidden sm:inline">Keluar</span>
                            </button>
                        </form>

                        <!-- Hamburger Button (Mobile Admin) -->
                        <button type="button" 
                                id="mobileMenuToggle" 
                                class="md:hidden p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:bg-slate-800 transition focus:outline-none"
                                aria-label="Toggle Navigation Menu">
                            <svg id="menuIconOpen" class="w-6 h-6 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                            <svg id="menuIconClose" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                @else
                    <div class="flex items-center gap-3">
                        <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-slate-300 hover:text-white bg-slate-900 border border-slate-700 hover:border-slate-600 transition shadow-sm">
                            <svg class="w-3.5 h-3.5 text-[#FF5500]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            <span>Kembali ke Website</span>
                        </a>
                    </div>
                @endif
            @else
                <!-- ================= PUBLIC HEADER ================= -->
                <!-- Brand Logo (Public) -->
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 sm:gap-3 group">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-tr from-[#FF5500] to-[#FF8800] flex items-center justify-center font-athletic text-2xl text-white shadow-lg shadow-[#FF5500]/30 group-hover:scale-105 transition-transform">
                        V
                    </div>
                    <div>
                        <span class="font-athletic text-2xl sm:text-3xl tracking-wider text-white">VIRA</span>
                        <span class="hidden sm:inline-block text-[10px] uppercase font-bold tracking-widest px-2 py-0.5 ml-1.5 rounded-full bg-slate-800 text-[#00E5FF] border border-[#00E5FF]/20">Virtual Sport</span>
                    </div>
                </a>

                <!-- Desktop Navigation Links (Public: HANYA Daftar Event, Official Shop, Submit Hasil Lari) -->
                <nav class="hidden md:flex items-center gap-2 lg:gap-3">
                    <a href="{{ route('home') }}" class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('home') || request()->routeIs('events.*') ? 'text-[#FF5500] bg-slate-800/60' : 'text-slate-300 hover:text-white hover:bg-slate-800/40' }} transition">
                        Daftar Event
                    </a>
                    <a href="{{ route('shop.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold {{ request()->routeIs('etalase.*') || request()->routeIs('shop.*') ? 'text-[#FF5500] bg-slate-800/60' : 'text-slate-300 hover:text-white hover:bg-slate-800/40' }} transition flex items-center gap-1.5">
                        <span>Official Shop</span>
                        <span class="text-[10px] bg-[#FF5500]/20 text-[#FF5500] font-bold px-1.5 py-0.5 rounded border border-[#FF5500]/30">Store</span>
                    </a>
                    <a href="{{ route('submit.index') }}" class="ml-2 px-4 py-2 rounded-lg text-sm font-bold text-white bg-gradient-to-r from-[#00B4D8] to-[#0077B6] hover:from-[#00E5FF] hover:to-[#00B4D8] transition shadow-md shadow-cyan-900/30 flex items-center gap-2">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Submit Hasil Lari</span>
                    </a>
                </nav>

                <!-- Mobile Quick Actions & Hamburger Toggle (Public) -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('submit.index') }}" class="md:hidden px-3 py-1.5 rounded-lg bg-cyan-500/10 text-[#00E5FF] border border-cyan-500/30 text-xs font-bold flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Submit</span>
                    </a>

                    <!-- Hamburger Button (Mobile Public) -->
                    <button type="button" 
                            id="mobileMenuToggle" 
                            class="md:hidden p-2 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white hover:bg-slate-800 transition focus:outline-none"
                            aria-label="Toggle Navigation Menu">
                        <svg id="menuIconOpen" class="w-6 h-6 block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg id="menuIconClose" class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            @endif
        </div>

        <!-- Mobile Drawer Navigation (Slide Down) -->
        <div id="mobileMenu" class="hidden md:hidden border-t border-slate-800 bg-[#0B0F19]/95 backdrop-blur-2xl px-4 py-5 space-y-2 shadow-2xl transition-all">
            @if(request()->is('admin*') && Auth::check() && !request()->routeIs('admin.login*'))
                <!-- Mobile Admin Profile & Navigation Links -->
                <div class="p-3 mb-2 rounded-xl bg-slate-900/90 border border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-rose-500/20 border border-rose-500/30 flex items-center justify-center font-bold text-rose-300 text-xs font-athletic">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <div>
                            <div class="text-xs font-bold text-white leading-tight">{{ Auth::user()->name }}</div>
                            <div class="text-[10px] text-slate-400 leading-tight">{{ Auth::user()->email }}</div>
                        </div>
                    </div>
                    <span class="text-[9px] font-mono font-bold uppercase px-2 py-0.5 rounded bg-rose-500/20 text-rose-300 border border-rose-500/30">
                        {{ Auth::user()->role === 'SUPER_ADMIN' ? 'Super Admin' : 'Admin' }}
                    </span>
                </div>

                <div class="px-2 pb-1 text-[11px] font-bold uppercase tracking-wider text-rose-400">
                    Menu Panel Admin
                </div>
                <a href="{{ route('admin.events.index') }}" class="flex items-center justify-between p-3 rounded-xl text-sm font-semibold {{ request()->routeIs('admin.events.*') ? 'bg-[#FF5500]/10 text-[#FF5500] border border-[#FF5500]/30' : 'text-slate-200 hover:bg-slate-800/60' }} transition">
                    <span class="flex items-center gap-3">
                        <span class="text-base">⚙️</span>
                        <span>Kelola Event &amp; CAPI</span>
                    </span>
                    <span class="text-[10px] bg-rose-500/20 text-rose-400 font-bold px-2 py-0.5 rounded border border-rose-500/30">CAPI</span>
                </a>
                <a href="{{ route('admin.addons.index') }}" class="flex items-center justify-between p-3 rounded-xl text-sm font-semibold {{ request()->routeIs('admin.addons.*') ? 'bg-[#FF5500]/10 text-[#FF5500] border border-[#FF5500]/30' : 'text-slate-200 hover:bg-slate-800/60' }} transition">
                    <span class="flex items-center gap-3">
                        <span class="text-base">🛍️</span>
                        <span>Pengaturan Item Add-ons</span>
                    </span>
                    <span class="text-[10px] bg-orange-500/20 text-[#FF5500] font-bold px-2 py-0.5 rounded border border-orange-500/30">Merch</span>
                </a>
                <a href="{{ route('admin.registrations.index') }}" class="flex items-center justify-between p-3 rounded-xl text-sm font-semibold {{ request()->routeIs('admin.registrations.*') ? 'bg-cyan-500/10 text-[#00E5FF] border border-cyan-500/30' : 'text-slate-200 hover:bg-slate-800/60' }} transition">
                    <span class="flex items-center gap-3">
                        <span class="text-base">👥</span>
                        <span>Data Peserta &amp; Tagihan</span>
                    </span>
                    <span class="text-[10px] bg-cyan-500/20 text-[#00E5FF] font-bold px-2 py-0.5 rounded border border-cyan-500/30">Data</span>
                </a>
                <a href="{{ route('admin.shipping-settings.index') }}" class="flex items-center justify-between p-3 rounded-xl text-sm font-semibold {{ request()->routeIs('admin.shipping-settings.*') ? 'bg-[#FF5500]/10 text-[#FF5500] border border-[#FF5500]/30' : 'text-slate-200 hover:bg-slate-800/60' }} transition">
                    <span class="flex items-center gap-3">
                        <span class="text-base">🚚</span>
                        <span>Pengaturan Ekspedisi SPX</span>
                    </span>
                    <span class="text-[10px] bg-orange-500/20 text-[#FF5500] font-bold px-2 py-0.5 rounded border border-orange-500/30">Ongkir</span>
                </a>
                <a href="{{ route('home') }}" target="_blank" class="flex items-center justify-between p-3 rounded-xl text-sm font-bold text-slate-300 hover:text-white bg-slate-900 border border-slate-700 transition mt-2">
                    <span class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-[#FF5500]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>Lihat Website Publik</span>
                    </span>
                    <span class="text-xs">&rarr;</span>
                </a>
                <form action="{{ route('admin.logout') }}" method="POST" class="pt-2">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center gap-2 p-3 rounded-xl text-sm font-bold text-rose-300 bg-rose-500/10 hover:bg-rose-500/20 border border-rose-500/30 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        <span>Keluar dari Sesi Admin</span>
                    </button>
                </form>
            @elseif(!request()->is('admin*'))
                <!-- Mobile Public Navigation Links (HANYA Daftar Event, Official Shop, Submit Hasil Lari) -->
                <a href="{{ route('home') }}" class="flex items-center justify-between p-3 rounded-xl text-sm font-semibold {{ request()->routeIs('home') ? 'bg-[#FF5500]/10 text-[#FF5500] border border-[#FF5500]/30' : 'text-slate-200 hover:bg-slate-800/60' }} transition">
                    <span class="flex items-center gap-3">
                        <span class="text-base">🏃</span>
                        <span>Daftar Event</span>
                    </span>
                    <span class="text-xs text-slate-500">&rarr;</span>
                </a>

                <a href="{{ route('shop.index') }}" class="flex items-center justify-between p-3 rounded-xl text-sm font-semibold {{ request()->routeIs('etalase.*') || request()->routeIs('shop.*') ? 'bg-[#FF5500]/10 text-[#FF5500] border border-[#FF5500]/30' : 'text-slate-200 hover:bg-slate-800/60' }} transition">
                    <span class="flex items-center gap-3">
                        <span class="text-base">🛍️</span>
                        <span>Official Shop</span>
                    </span>
                    <span class="text-[10px] bg-[#FF5500]/20 text-[#FF5500] font-bold px-2 py-0.5 rounded border border-[#FF5500]/30">Store</span>
                </a>

                <a href="{{ route('submit.index') }}" class="flex items-center justify-between p-3 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-[#00B4D8] to-[#0077B6] hover:from-[#00E5FF] hover:to-[#00B4D8] shadow-lg shadow-cyan-950/40 transition mt-2">
                    <span class="flex items-center gap-2.5">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        <span>Submit Hasil Lari</span>
                    </span>
                    <span class="text-xs">&rarr;</span>
                </a>
            @endif
        </div>
    </header>

    <!-- Flash Notifications -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full mt-4">
        @if(session('success'))
            <div class="bg-emerald-950/80 border border-emerald-500/50 text-emerald-200 px-4 py-3.5 rounded-xl flex items-center justify-between shadow-lg shadow-emerald-950/50 backdrop-blur-md mb-4">
                <div class="flex items-center gap-3">
                    <span class="text-xl">✅</span>
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">&times;</button>
            </div>
        @endif

        @if(session('error'))
            <div class="bg-rose-950/80 border border-rose-500/50 text-rose-200 px-4 py-3.5 rounded-xl flex items-center justify-between shadow-lg shadow-rose-950/50 backdrop-blur-md mb-4">
                <div class="flex items-center gap-3">
                    <span class="text-xl">⚠️</span>
                    <p class="text-sm font-medium">{{ session('error') }}</p>
                </div>
                <button onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white">&times;</button>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-grow">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-slate-950 border-t border-slate-800/80 mt-20 pt-14 pb-10 text-slate-400">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-10 pb-12 border-b border-slate-800">
                <div class="md:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-[#FF5500] to-[#FF8800] flex items-center justify-center font-athletic text-xl text-white">V</div>
                        <span class="font-athletic text-2xl text-white tracking-wider">VIRA PLATFORM</span>
                    </div>
                    <p class="text-sm text-slate-400 max-w-md leading-relaxed">
                        Platform pendaftaran dan pencatatan virtual sport (Run, Ride, & Walk) nomor satu di Indonesia. Nikmati penomoran e-BIB otomatis, akumulasi jarak fleksibel, dan pengiriman race pack via SPX Express.
                    </p>
                    <div class="flex items-center gap-3 pt-2">
                        <span class="text-xs px-2.5 py-1 rounded bg-slate-900 border border-slate-800 text-slate-300">⚡ Instant e-BIB</span>
                        <span class="text-xs px-2.5 py-1 rounded bg-slate-900 border border-slate-800 text-slate-300">📦 SPX 7.100+ Kecamatan</span>
                        <span class="text-xs px-2.5 py-1 rounded bg-slate-900 border border-slate-800 text-slate-300">💳 Tripay Payment</span>
                    </div>
                </div>

                <div>
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-4">Navigasi Utama</h4>
                    <ul class="space-y-2.5 text-sm">
                        <li><a href="{{ route('home') }}" class="hover:text-[#FF5500] transition">Semua Event</a></li>
                        <li><a href="{{ route('submit.index') }}" class="hover:text-[#00E5FF] transition">Universal Submit Portal</a></li>
                        <li><a href="{{ route('etalase.index') }}" class="hover:text-[#FF5500] transition">Etalase Add-ons & Merchandise</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-bold text-sm uppercase tracking-wider mb-4">Metode & Logistik</h4>
                    <p class="text-xs text-slate-400 leading-relaxed mb-3">
                        Pengiriman paket fisik dari <strong>Kab. Tangerang, Banten</strong> ke seluruh Indonesia melalui <strong>SPX Express</strong> (Hemat & Regular).
                    </p>
                    <div class="text-xs text-slate-500">
                        Pembayaran aman terintegrasi otomatis QRIS, Virtual Account, & E-Wallet via Tripay.
                    </div>
                </div>
            </div>

            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>&copy; {{ date('Y') }} VIRA — Virtual Sport Ecosystem. All rights reserved.</p>
                <p>Designed for Athletes & Fun Runners Everywhere.</p>
            </div>
        </div>
    </footer>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('mobileMenuToggle');
        const mobileMenu = document.getElementById('mobileMenu');
        const iconOpen = document.getElementById('menuIconOpen');
        const iconClose = document.getElementById('menuIconClose');

        if (toggleBtn && mobileMenu) {
            toggleBtn.addEventListener('click', function() {
                const isHidden = mobileMenu.classList.contains('hidden');
                if (isHidden) {
                    mobileMenu.classList.remove('hidden');
                    if (iconOpen) iconOpen.classList.add('hidden');
                    if (iconClose) iconClose.classList.remove('hidden');
                } else {
                    mobileMenu.classList.add('hidden');
                    if (iconOpen) iconOpen.classList.remove('hidden');
                    if (iconClose) iconClose.classList.add('hidden');
                }
            });

            // Close mobile menu on resize to desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768 && !mobileMenu.classList.contains('hidden')) {
                    mobileMenu.classList.add('hidden');
                    if (iconOpen) iconOpen.classList.remove('hidden');
                    if (iconClose) iconClose.classList.add('hidden');
                }
            });
        }
    });
    </script>

    @stack('scripts')
</body>
</html>
