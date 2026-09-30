@extends('layouts.app')

@section('title', 'VIRA — Virtual Run, Ride & Walk Platform')

@section('content')
<!-- Hero Section -->
<div class="relative overflow-hidden bg-gradient-to-b from-[#0B0F19] via-[#10172A] to-[#0B0F19] pt-12 pb-20 border-b border-slate-800/60">
    <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#FF5500_1px,transparent_1px)] [background-size:24px_24px]"></div>
    
    <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <!-- Highlight Pill -->
        <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-slate-800/80 border border-slate-700/80 text-xs text-slate-300 mb-6 backdrop-blur-md">
            <span class="w-2 h-2 rounded-full bg-[#FF5500]"></span>
            <span>Platform Virtual Sport Terpadu dengan Ongkir SPX Real-Time</span>
        </div>

        <!-- Big Athletic Heading -->
        <h1 class="text-3xl sm:text-5xl lg:text-7xl font-extrabold tracking-tight text-white uppercase max-w-5xl mx-auto leading-tight sm:leading-none">
            Taklukkan Jarak <span class="font-athletic text-[#FF5500] text-4xl sm:text-7xl lg:text-8xl tracking-wider block sm:inline">Kapan Saja</span>, Di Mana Saja
        </h1>

        <p class="mt-4 sm:mt-6 text-sm sm:text-lg md:text-xl text-slate-400 max-w-3xl mx-auto leading-relaxed px-2">
            Daftar instan tanpa ribet, dapatkan <strong class="text-slate-200">Nomor e-BIB otomatis</strong>, catat lari dengan link Strava/GDrive, dan kumpulkan jarak hingga raih <strong class="text-[#FFD700]">Finisher E-Certificate & Medali Fisik</strong>.
        </p>

        <!-- CTA Buttons -->
        <div class="mt-8 sm:mt-10 flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3 sm:gap-4 max-w-md sm:max-w-none mx-auto">
            <a href="#events" class="w-full sm:w-auto px-7 py-3.5 sm:py-4 rounded-xl font-bold text-sm sm:text-base text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-lg shadow-orange-950/50 glow-orange flex items-center justify-center gap-2">
                <span>Pilih &amp; Ikuti Event</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
            <a href="{{ route('submit.index') }}" class="w-full sm:w-auto px-7 py-3.5 sm:py-4 rounded-xl font-bold text-sm sm:text-base text-slate-200 bg-slate-800/90 hover:bg-slate-700/90 border border-slate-700 hover:border-slate-600 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5 text-[#00E5FF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Input / Submit Hasil Lari</span>
            </a>
        </div>

        <!-- Featured Event Hero Spotlight (Paling Atas) -->
        @if($featuredEvent = $events->first())
            <div class="mt-10 sm:mt-12 max-w-5xl mx-auto text-left">
                <a href="{{ route('events.show', $featuredEvent->slug) }}" class="block relative rounded-2xl sm:rounded-3xl overflow-hidden border border-slate-800 shadow-2xl group bg-slate-950">
                    <div class="relative min-h-[260px] sm:min-h-[320px] md:h-[400px] w-full overflow-hidden flex flex-col justify-between p-4 sm:p-6 md:p-8">
                        <img src="{{ $featuredEvent->banner_url }}" 
                             alt="{{ $featuredEvent->title }}" 
                             class="absolute inset-0 w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700">
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/60 to-slate-950/20"></div>

                        <!-- Top Floating Badges -->
                        <div class="relative z-10 flex flex-wrap gap-2">
                            <span class="px-2.5 sm:px-3.5 py-1 rounded-md text-[10px] sm:text-xs font-black uppercase tracking-wider bg-[#FF5500] text-white shadow-lg">
                                ★ Event Unggulan
                            </span>
                            <span class="px-2.5 sm:px-3.5 py-1 rounded-md text-[10px] sm:text-xs font-extrabold uppercase tracking-wider bg-slate-900/90 text-cyan-300 border border-cyan-800 backdrop-blur-md">
                                {{ $featuredEvent->activity_type }}
                            </span>
                            <span class="hidden sm:inline-block px-3 py-1 rounded-md text-xs font-bold uppercase tracking-wider bg-slate-900/90 text-emerald-400 border border-emerald-800 backdrop-blur-md">
                                Pendaftaran Aktif
                            </span>
                        </div>

                        <!-- Bottom Content -->
                        <div class="relative z-10 mt-16 sm:mt-0 flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                            <div>
                                <span class="text-[11px] sm:text-xs font-bold uppercase tracking-wider text-slate-300 block mb-1">Virtual Challenge Terkini</span>
                                <h3 class="text-xl sm:text-3xl md:text-4xl font-black text-white group-hover:text-[#FF5500] transition drop-shadow-md">
                                    {{ $featuredEvent->title }}
                                </h3>
                                <p class="text-xs sm:text-sm text-slate-300 mt-1 line-clamp-2 max-w-xl">
                                    {{ strip_tags($featuredEvent->description) }}
                                </p>
                            </div>
                            <div class="shrink-0 w-full sm:w-auto">
                                <span class="inline-flex w-full sm:w-auto items-center justify-center gap-2 px-5 sm:px-6 py-2.5 sm:py-3 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] group-hover:from-[#FF6600] group-hover:to-[#FF8800] transition shadow-lg shadow-orange-950/50">
                                    <span>Lihat Detail Event</span>
                                    <span>&rarr;</span>
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endif

        <!-- Key Feature Badges -->
        <div class="mt-10 sm:mt-14 grid grid-cols-2 md:grid-cols-4 gap-2.5 sm:gap-4 max-w-4xl mx-auto text-left">
            <div class="p-3 sm:p-4 rounded-xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
                <div class="text-[#FF5500] text-sm sm:text-xl font-athletic">01. GUEST REGISTRATION</div>
                <div class="text-[10px] sm:text-xs text-slate-400 mt-0.5 sm:mt-1">Daftar langsung tanpa repot kata sandi.</div>
            </div>
            <div class="p-3 sm:p-4 rounded-xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
                <div class="text-[#00E5FF] text-sm sm:text-xl font-athletic">02. AUTO e-BIB &amp; DESIGN</div>
                <div class="text-[10px] sm:text-xs text-slate-400 mt-0.5 sm:mt-1">Unduh kartu visual e-BIB untuk sosmed.</div>
            </div>
            <div class="p-3 sm:p-4 rounded-xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
                <div class="text-[#FFD700] text-sm sm:text-xl font-athletic">03. 1 LINK PORTAL</div>
                <div class="text-[10px] sm:text-xs text-slate-400 mt-0.5 sm:mt-1">Satu link submit (/submit) semua event.</div>
            </div>
            <div class="p-3 sm:p-4 rounded-xl bg-slate-900/60 border border-slate-800/80 backdrop-blur-sm">
                <div class="text-emerald-400 text-sm sm:text-xl font-athletic">04. SPX LOGISTICS</div>
                <div class="text-[10px] sm:text-xs text-slate-400 mt-0.5 sm:mt-1">Tarif resmi SPX 7.100+ kecamatan.</div>
            </div>
        </div>
    </div>
</div>

<!-- Event List Section -->
<div id="events" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-8 sm:mb-10 gap-4">
        <div>
            <div class="text-xs font-bold uppercase tracking-widest text-[#FF5500] mb-1">Pilihan Lomba &amp; Tantangan</div>
            <h2 class="text-2xl sm:text-4xl font-extrabold text-white">Event Virtual Tersedia</h2>
        </div>

        <!-- Filter Tabs (Horizontal touch scroll on mobile) -->
        <div class="flex items-center gap-1.5 sm:gap-2 bg-slate-900 p-1 sm:p-1.5 rounded-xl border border-slate-800 overflow-x-auto no-scrollbar scroll-smooth">
            <a href="{{ route('home') }}" class="px-3 sm:px-3.5 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap {{ !$activityFilter ? 'bg-[#FF5500] text-white' : 'text-slate-400 hover:text-white' }}">
                Semua Event
            </a>
            <a href="{{ route('home', ['type' => 'RUN']) }}" class="px-3 sm:px-3.5 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap {{ $activityFilter === 'RUN' ? 'bg-[#FF5500] text-white' : 'text-slate-400 hover:text-white' }}">
                🏃 Run
            </a>
            <a href="{{ route('home', ['type' => 'RIDE']) }}" class="px-3 sm:px-3.5 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap {{ $activityFilter === 'RIDE' ? 'bg-[#FF5500] text-white' : 'text-slate-400 hover:text-white' }}">
                🚴 Ride
            </a>
            <a href="{{ route('home', ['type' => 'WALK']) }}" class="px-3 sm:px-3.5 py-1.5 rounded-lg text-xs font-bold transition whitespace-nowrap {{ $activityFilter === 'WALK' ? 'bg-[#FF5500] text-white' : 'text-slate-400 hover:text-white' }}">
                🚶 Walk
            </a>
        </div>
    </div>

    <!-- Event Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        @forelse($events as $event)
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl overflow-hidden hover:border-slate-700 transition flex flex-col group shadow-xl">
                <!-- Banner Image Hero -->
                <a href="{{ route('events.show', $event->slug) }}" class="relative h-52 bg-slate-950 flex items-center justify-center overflow-hidden block">
                    <img src="{{ $event->banner_url }}" 
                         alt="{{ $event->title }}" 
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/30 to-transparent"></div>
                    
                    <!-- Badges -->
                    <div class="absolute top-4 left-4 flex gap-2">
                        <span class="px-2.5 py-1 rounded-md text-[11px] font-extrabold uppercase tracking-wider bg-[#FF5500] text-white shadow-md">
                            {{ $event->activity_type }}
                        </span>
                        <span class="px-2.5 py-1 rounded-md text-[11px] font-extrabold uppercase tracking-wider bg-slate-900/90 text-cyan-300 border border-cyan-800/50">
                            {{ $event->submission_mode === 'CUMULATIVE' ? 'Akumulasi' : 'Single Run' }}
                        </span>
                    </div>

                    <div class="absolute top-4 right-4">
                        <span class="px-2 py-0.5 rounded text-[10px] font-mono-num font-bold bg-slate-900/80 text-slate-300 border border-slate-700">
                            {{ $event->event_code }}
                        </span>
                    </div>

                    <div class="absolute bottom-3 left-4 right-4">
                        <h3 class="text-xl font-extrabold text-white leading-tight group-hover:text-[#FF5500] transition">
                            {{ $event->title }}
                        </h3>
                    </div>
                </a>

                <!-- Card Body -->
                <div class="p-5 flex-grow flex flex-col justify-between space-y-4">
                    <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed">
                        {{ strip_tags($event->description) }}
                    </p>

                    <!-- Categories Pill -->
                    <div>
                        <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-2">Pilihan Kategori:</div>
                        <div class="flex flex-wrap gap-1.5">
                            @foreach($event->categories as $cat)
                                <span class="px-2.5 py-1 rounded-lg bg-slate-800 text-xs font-bold text-slate-200 border border-slate-700/60">
                                    {{ $cat->name }} ({{ number_format($cat->target_distance_km, 0) }}K)
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Timeline & Status -->
                    <div class="pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                        <div>
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Periode Lari:</span>
                            <span class="text-slate-200 font-semibold">{{ $event->race_start->format('d M') }} - {{ $event->race_end->format('d M Y') }}</span>
                        </div>
                        <div class="text-right">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold">Mulai Dari:</span>
                            <span class="text-emerald-400 font-bold font-mono-num text-sm">
                                Rp {{ number_format($event->packages->min('price'), 0, ',', '.') }}
                            </span>
                        </div>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-2">
                        <a href="{{ route('events.register', $event->slug) }}" class="w-full py-3 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition flex items-center justify-center gap-2 shadow-md shadow-orange-950/40">
                            <span>Daftar Sekarang</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 py-16 text-center text-slate-400 bg-slate-900/40 rounded-2xl border border-slate-800">
                <p class="text-lg">Tidak ada event aktif pada kategori ini.</p>
                <a href="{{ route('home') }}" class="text-[#FF5500] text-sm font-bold mt-2 inline-block">Lihat Semua Event &rarr;</a>
            </div>
        @endforelse
    </div>
</div>

<!-- Etalase Cross-Selling Showcase -->
@if($featuredAddons->count() > 0)
<div class="bg-slate-900/60 border-y border-slate-800 py-16">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between mb-8 gap-4">
            <div>
                <span class="text-xs font-bold uppercase tracking-widest text-[#00E5FF]">Official Merchandise & Extras</span>
                <h2 class="text-2xl sm:text-3xl font-extrabold text-white">Etalase Finisher & Add-ons</h2>
            </div>
            <a href="{{ route('etalase.index') }}" class="text-xs font-bold text-[#FF5500] hover:underline flex items-center gap-1">
                <span>Lihat Seluruh Etalase</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>

        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
            @foreach($featuredAddons as $item)
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-3 sm:p-4 flex flex-col justify-between hover:border-slate-700 transition">
                    <div>
                        <div class="h-36 rounded-lg bg-slate-800 flex items-center justify-center text-4xl mb-3 overflow-hidden" style="height: 144px; max-height: 144px;">
                            @if($item->image_url)
                                <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover;">
                            @else
                                @if(str_contains(strtolower($item->name), 'jersey'))
                                    🎽
                                @elseif(str_contains(strtolower($item->name), 'kunci') || str_contains(strtolower($item->name), 'medali'))
                                    🏅
                                @else
                                    🧢
                                @endif
                            @endif
                        </div>
                        <h4 class="font-bold text-sm text-white leading-snug">{{ $item->name }}</h4>
                        <p class="text-xs text-slate-400 mt-1 line-clamp-2">{{ $item->description }}</p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between">
                        <span class="text-sm font-bold font-mono-num text-[#FF5500]">
                            Rp {{ number_format($item->price, 0, ',', '.') }}
                        </span>
                        <span class="text-[10px] text-slate-400 bg-slate-800 px-2 py-0.5 rounded">
                            {{ $item->weight_grams }}g
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<!-- How It Works Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
    <div class="text-xs font-bold uppercase tracking-widest text-[#FF5500] mb-2">Simpel & Praktis</div>
    <h2 class="text-3xl sm:text-4xl font-extrabold text-white mb-12">Cara Kerja Mengikuti Event di VIRA</h2>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="p-8 rounded-2xl bg-slate-900/60 border border-slate-800 text-center relative">
            <div class="w-14 h-14 rounded-2xl bg-[#FF5500]/10 border border-[#FF5500]/30 text-[#FF5500] font-athletic text-2xl flex items-center justify-center mx-auto mb-5">
                01
            </div>
            <h3 class="text-lg font-bold text-white mb-2">Daftar Guest & Pilih Paket</h3>
            <p class="text-sm text-slate-400 leading-relaxed">
                Pilih kategori jarak, opsi paket digital atau dengan medali fisik, serta tambahkan merchandise etalase. Bayar mudah dengan QRIS / VA Tripay.
            </p>
        </div>

        <div class="p-8 rounded-2xl bg-slate-900/60 border border-slate-800 text-center relative">
            <div class="w-14 h-14 rounded-2xl bg-[#00E5FF]/10 border border-[#00E5FF]/30 text-[#00E5FF] font-athletic text-2xl flex items-center justify-center mx-auto mb-5">
                02
            </div>
            <h3 class="text-lg font-bold text-white mb-2">Dapatkan e-BIB Visual</h3>
            <p class="text-sm text-slate-400 leading-relaxed">
                Nomor e-BIB unik otomatis terbit saat pembayaran lunas. Unduh kartu e-BIB grafis resolusi tinggi untuk kamu pamerkan ke media sosial.
            </p>
        </div>

        <div class="p-8 rounded-2xl bg-slate-900/60 border border-slate-800 text-center relative">
            <div class="w-14 h-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 font-athletic text-2xl flex items-center justify-center mx-auto mb-5">
                03
            </div>
            <h3 class="text-lg font-bold text-white mb-2">Lari & Submit di /submit</h3>
            <p class="text-sm text-slate-400 leading-relaxed">
                Gunakan satu link universal untuk submit hasil lari manual dan lampirkan link Strava/GDrive. Kumpulkan jaraknya hingga Finisher!
            </p>
        </div>
    </div>
</div>
@endsection
