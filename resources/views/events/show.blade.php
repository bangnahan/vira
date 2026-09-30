@extends('layouts.app')

@section('title', $event->title . ' — VIRA')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Breadcrumb -->
    <nav class="flex text-xs font-semibold text-slate-400 mb-6 gap-2">
        <a href="{{ route('home') }}" class="hover:text-white transition">Beranda</a>
        <span>/</span>
        <span class="text-[#FF5500]">{{ $event->title }}</span>
    </nav>

    <!-- Dedicated Clean Hero Image Section (Tanpa Overlay Teks/Info) -->
    <div class="rounded-2xl sm:rounded-3xl overflow-hidden border border-slate-800 shadow-2xl bg-slate-950 mb-6 sm:mb-8">
        <img src="{{ $event->banner_url }}" 
             alt="{{ $event->title }}" 
             class="w-full h-auto max-h-[300px] sm:max-h-[440px] md:max-h-[560px] object-cover object-center block">
    </div>

    <!-- Event Header & Info Card (Judul & Detail di Bawah Gambar) -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl sm:rounded-3xl overflow-hidden shadow-xl mb-8 sm:mb-10">
        <div class="p-5 sm:p-8 border-b border-slate-800/80">
            <div class="flex flex-wrap items-center gap-2 mb-3">
                <span class="px-2.5 sm:px-3 py-1 rounded-md text-[10px] sm:text-xs font-black uppercase tracking-wider bg-[#FF5500] text-white shadow-md">
                    {{ $event->activity_type }}
                </span>
                <span class="px-2.5 sm:px-3 py-1 rounded-md text-[10px] sm:text-xs font-extrabold uppercase tracking-wider bg-slate-800 text-cyan-300 border border-cyan-800">
                    {{ $event->submission_mode === 'CUMULATIVE' ? 'Akumulasi Jarak' : 'Single Session' }}
                </span>
                <span class="px-2.5 sm:px-3 py-1 rounded-md text-[10px] sm:text-xs font-mono-num font-bold bg-slate-950 text-slate-300 border border-slate-700">
                    KODE: {{ $event->event_code }}
                </span>
            </div>
            <h1 class="text-2xl sm:text-4xl md:text-5xl font-black text-white leading-tight">
                {{ $event->title }}
            </h1>
        </div>

        <!-- Event Quick Info Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 divide-x divide-y md:divide-y-0 divide-slate-800 bg-slate-950/80 text-center">
            <div class="p-3.5 sm:p-5">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase text-slate-400 block mb-1">Periode Pendaftaran</span>
                <span class="text-xs sm:text-sm font-semibold text-slate-200">
                    {{ $event->registration_start->format('d M') }} - {{ $event->registration_end->format('d M Y') }}
                </span>
            </div>
            <div class="p-3.5 sm:p-5">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase text-slate-400 block mb-1">Periode Lari (Race)</span>
                <span class="text-xs sm:text-sm font-semibold text-slate-200">
                    {{ $event->race_start->format('d M') }} - {{ $event->race_end->format('d M Y') }}
                </span>
            </div>
            <div class="p-3.5 sm:p-5">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase text-slate-400 block mb-1">Logistik Pengiriman</span>
                <span class="text-xs sm:text-sm font-semibold text-emerald-400">
                    SPX Express (7.100 Kec.)
                </span>
            </div>
            <div class="p-3.5 sm:p-5 flex items-center justify-center">
                @if($event->isRegistrationOpen())
                    <a href="{{ route('events.register', $event->slug) }}" class="w-full py-3 sm:py-2.5 px-3 sm:px-4 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-lg shadow-orange-950/40 text-center">
                        Daftar Sekarang &rarr;
                    </a>
                @elseif(now()->lt($event->registration_start))
                    <span class="w-full py-3 sm:py-2.5 px-3 sm:px-4 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-400 bg-slate-800/80 border border-slate-700/80 text-center block cursor-not-allowed">
                        Segera Dibuka
                    </span>
                @else
                    <span class="w-full py-3 sm:py-2.5 px-3 sm:px-4 rounded-xl font-bold text-xs uppercase tracking-wider text-rose-300 bg-rose-950/40 border border-rose-900/60 text-center block cursor-not-allowed">
                        Pendaftaran Ditutup
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Description & Rules Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <div class="lg:col-span-2 space-y-8">
            <!-- Deskripsi -->
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6 sm:p-8">
                <h3 class="text-lg font-bold text-white mb-5 flex items-center gap-2 pb-3 border-b border-slate-800">
                    <span class="text-[#FF5500]">■</span> Deskripsi &amp; Informasi Event
                </h3>
                <div class="event-prose text-slate-300 leading-relaxed text-sm sm:text-base">
                    {!! $event->sanitized_description !!}
                </div>
            </div>

            <!-- Syarat & Ketentuan -->
            @if(!empty($event->rules_and_terms))
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6 sm:p-8">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <span class="text-[#00E5FF]">■</span> Aturan &amp; Ketentuan Lomba
                </h3>
                <div class="text-sm text-slate-300 leading-relaxed whitespace-pre-line bg-slate-950/80 p-5 rounded-xl border border-slate-800/80 font-mono text-xs">
                    {{ $event->rules_and_terms }}
                </div>
            </div>
            @endif

            <!-- Kategori Jarak -->
            <div class="bg-slate-900/60 border border-slate-800 rounded-2xl p-6 sm:p-8">
                <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                    <span class="text-[#FFD700]">■</span> Kategori Jarak
                </h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @foreach($event->categories as $cat)
                        <div class="bg-slate-950 p-4 rounded-xl border border-slate-800 text-center">
                            <div class="font-athletic text-3xl text-white">{{ $cat->name }}</div>
                            <div class="text-xs font-bold text-[#FF5500] mt-1">{{ number_format($cat->target_distance_km, 1) }} KM</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar: Paket & CTA -->
        <div class="space-y-6">
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-6 sticky top-28">
                <h3 class="text-base font-bold text-white mb-4">Pilihan Paket Pendaftaran</h3>

                <div class="space-y-3 mb-6">
                    @foreach($event->packages as $pkg)
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold text-sm text-white">{{ $pkg->name }}</span>
                                <span class="font-mono-num font-bold text-xs text-emerald-400">
                                    Rp {{ number_format($pkg->price, 0, ',', '.') }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-400">{{ $pkg->description }}</p>
                            @if($pkg->requires_shipping)
                                <div class="mt-2 text-[10px] text-cyan-400 flex items-center gap-1">
                                    <span>📦 Termasuk pengiriman fisik via SPX</span>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <a href="{{ route('events.register', $event->slug) }}" 
                   onclick="if(window.fbq) { fbq('track', 'AddToCart', { content_name: '{{ addslashes($event->title) }}', content_type: 'product', value: {{ (float) ($event->packages->min('price') ?? 0) }}, currency: 'IDR' }); }"
                   class="w-full py-4 rounded-xl font-bold text-center text-sm uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition block shadow-lg shadow-orange-950/50 glow-orange">
                    Lanjut ke Form Pendaftaran &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.event-prose h1 {
    font-size: 1.75rem;
    font-weight: 800;
    color: #ffffff;
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    line-height: 1.25;
}
.event-prose h2 {
    font-size: 1.35rem;
    font-weight: 700;
    color: #ffffff;
    margin-top: 1.25rem;
    margin-bottom: 0.5rem;
    line-height: 1.3;
}
.event-prose h3 {
    font-size: 1.15rem;
    font-weight: 700;
    color: #f8fafc;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
}
.event-prose p {
    margin-bottom: 0.85rem;
    line-height: 1.7;
}
.event-prose strong, .event-prose b {
    font-weight: 700;
    color: #ffffff;
}
.event-prose em, .event-prose i {
    font-style: italic;
    color: #e2e8f0;
}
.event-prose .font-light-sub {
    font-weight: 300 !important;
    color: #94a3b8 !important;
}
.event-prose ul {
    list-style-type: disc;
    margin-left: 1.5rem;
    margin-bottom: 1rem;
    space-y: 0.25rem;
}
.event-prose ol {
    list-style-type: decimal;
    margin-left: 1.5rem;
    margin-bottom: 1rem;
    space-y: 0.25rem;
}
.event-prose li {
    margin-bottom: 0.35rem;
    color: #cbd5e1;
}
.event-prose blockquote {
    border-left: 4px solid #FF5500;
    padding-left: 1.25rem;
    margin: 1.25rem 0;
    font-style: italic;
    color: #e2e8f0;
    background: rgba(15, 23, 42, 0.7);
    padding-top: 0.75rem;
    padding-bottom: 0.75rem;
    border-radius: 0 0.75rem 0.75rem 0;
}
.event-prose img {
    max-width: 100%;
    height: auto;
    border-radius: 1.25rem;
    margin: 1.5rem 0;
    border: 1px solid #334155;
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5), 0 8px 10px -6px rgba(0, 0, 0, 0.5);
}
.event-prose a {
    color: #00E5FF;
    text-decoration: underline;
    font-weight: 600;
}
.event-prose a:hover {
    color: #67e8f9;
}
.event-prose hr {
    border-color: #1e293b;
    margin: 1.75rem 0;
}
</style>
@endpush

@if($event->meta_pixel_id)
@push('scripts')
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{{ $event->meta_pixel_id }}');
fbq('track', 'PageView');
fbq('track', 'ViewContent', {
    content_name: '{{ addslashes($event->title) }}',
    content_category: '{{ $event->activity_type }}',
    content_ids: ['{{ $event->id }}'],
    content_type: 'product',
    value: {{ (float) ($event->packages->min('price') ?? 0) }},
    currency: 'IDR'
});
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={{ $event->meta_pixel_id }}&ev=PageView&noscript=1"
/></noscript>
@endpush
@endif


