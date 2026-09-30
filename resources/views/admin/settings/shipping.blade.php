@extends('layouts.app')

@section('title', 'Pengaturan Ekspedisi SPX Express — Admin VIRA')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">
                <a href="{{ route('admin.events.index') }}" class="hover:text-white transition">Admin</a>
                <span>/</span>
                <span class="text-[#FF5500]">Pengaturan Ekspedisi SPX</span>
            </div>
            <h1 class="text-3xl font-extrabold text-white font-athletic tracking-wide">Pengaturan Ekspedisi SPX Express</h1>
            <p class="text-xs text-slate-400">Atur ketersediaan opsi pengiriman SPX Hemat dan Reguler untuk pendaftaran event dan pembelian merchandise.</p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('admin.registrations.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-300 bg-slate-800 hover:bg-slate-700 transition border border-slate-700">
                Data Peserta
            </a>
            <a href="{{ route('admin.events.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-300 bg-slate-800 hover:bg-slate-700 transition border border-slate-700">
                Kelola Event
            </a>
        </div>
    </div>

    <!-- Flash Alert -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-200 text-xs flex items-center gap-3 shadow-xl">
            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span class="font-medium text-sm">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Origin & Master Data Stats Card -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
        <div class="p-5 rounded-2xl bg-slate-900/90 border border-slate-800">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Asal Gudang Pengiriman (Origin)</span>
            <div class="text-lg font-bold text-white flex items-center gap-2">
                <span>📍</span>
                <span>KAB. TANGERANG</span>
            </div>
            <span class="text-[11px] text-slate-500 mt-1 block">Banten, Indonesia (Official Hub SPX VIRA)</span>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/90 border border-slate-800">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Master Tarif Terpasang</span>
            <div class="text-2xl font-bold font-athletic text-[#FF5500] font-mono-num">{{ number_format($totalRates) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Baris tarif ongkir resmi SPX Express</span>
        </div>

        <div class="p-5 rounded-2xl bg-slate-900/90 border border-slate-800">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Cakupan Wilayah Tujuan</span>
            <div class="text-2xl font-bold font-athletic text-[#00E5FF] font-mono-num">{{ number_format($totalCities) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Kota / Kabupaten &amp; seluruh Kecamatan se-Indonesia</span>
        </div>
    </div>

    <!-- Form Pengaturan Layanan Aktif -->
    <form action="{{ route('admin.shipping-settings.update') }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="p-6 sm:p-8 rounded-3xl bg-slate-900/90 border border-slate-800 shadow-xl space-y-6">
            <div>
                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                    <span class="text-xl">🚚</span>
                    <span>Pilihan Metode Layanan SPX yang Diaktifkan</span>
                </h3>
                <p class="text-xs text-slate-400 mt-1">
                    Tentukan apakah kedua opsi layanan (Hemat &amp; Reguler) diaktifkan bersamaan, atau hanya salah satu saja yang dapat dipilih oleh peserta lomba.
                </p>
            </div>

            <!-- Radio Options -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Option 1: ALL (Keduanya Aktif) -->
                <label class="relative flex flex-col p-5 rounded-2xl border-2 cursor-pointer transition {{ $activeMode === 'ALL' ? 'border-[#FF5500] bg-orange-500/10 shadow-lg shadow-orange-950/40' : 'border-slate-800 bg-slate-950/60 hover:border-slate-700' }} group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-orange-500/20 text-[#FF5500] border border-orange-500/30">
                            Paling Fleksibel
                        </span>
                        <input type="radio" name="spx_active_services" value="ALL" {{ $activeMode === 'ALL' ? 'checked' : '' }} class="w-4 h-4 text-[#FF5500] focus:ring-0 bg-slate-900 border-slate-700">
                    </div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="text-xl">✨</span>
                        <h4 class="font-bold text-white text-sm">Semua Aktif (Hemat &amp; Reguler)</h4>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed flex-grow">
                        Peserta bebas memilih antara biaya termurah (<strong>SPX Hemat</strong>) atau pengiriman lebih cepat (<strong>SPX Reguler</strong>).
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center gap-1.5 text-[11px] text-emerald-400 font-semibold">
                        <span>✓ Rekomendasi Resmi VIRA</span>
                    </div>
                </label>

                <!-- Option 2: SPX_REGULAR Only -->
                <label class="relative flex flex-col p-5 rounded-2xl border-2 cursor-pointer transition {{ $activeMode === 'SPX_REGULAR' ? 'border-[#00E5FF] bg-cyan-500/10 shadow-lg shadow-cyan-950/40' : 'border-slate-800 bg-slate-950/60 hover:border-slate-700' }} group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-cyan-500/20 text-[#00E5FF] border border-cyan-500/30">
                            Prioritas Kecepatan
                        </span>
                        <input type="radio" name="spx_active_services" value="SPX_REGULAR" {{ $activeMode === 'SPX_REGULAR' ? 'checked' : '' }} class="w-4 h-4 text-[#00E5FF] focus:ring-0 bg-slate-900 border-slate-700">
                    </div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="text-xl">🚀</span>
                        <h4 class="font-bold text-white text-sm">Hanya SPX Reguler (Standar)</h4>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed flex-grow">
                        Hanya opsi <strong>SPX Reguler</strong> yang muncul. Opsi Hemat disembunyikan. Estimasi pengiriman lebih cepat <strong>(2–4 hari kerja)</strong>.
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center gap-1.5 text-[11px] text-cyan-300 font-semibold">
                        <span>⚡ SLA 2–4 Hari Kerja</span>
                    </div>
                </label>

                <!-- Option 3: SPX_HEMAT Only -->
                <label class="relative flex flex-col p-5 rounded-2xl border-2 cursor-pointer transition {{ $activeMode === 'SPX_HEMAT' ? 'border-amber-500 bg-amber-500/10 shadow-lg shadow-amber-950/40' : 'border-slate-800 bg-slate-950/60 hover:border-slate-700' }} group">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                            Prioritas Biaya
                        </span>
                        <input type="radio" name="spx_active_services" value="SPX_HEMAT" {{ $activeMode === 'SPX_HEMAT' ? 'checked' : '' }} class="w-4 h-4 text-amber-500 focus:ring-0 bg-slate-900 border-slate-700">
                    </div>
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="text-xl">💰</span>
                        <h4 class="font-bold text-white text-sm">Hanya SPX Hemat (Ekonomi)</h4>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed flex-grow">
                        Hanya opsi <strong>SPX Hemat</strong> yang muncul. Memberikan biaya ongkir termurah bagi peserta, estimasi <strong>(3–7 hari kerja)</strong>.
                    </p>
                    <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center gap-1.5 text-[11px] text-amber-300 font-semibold">
                        <span>🏷️ Ongkir Termurah</span>
                    </div>
                </label>
            </div>

            <!-- Preview Card -->
            <div class="p-5 rounded-2xl bg-slate-950/90 border border-slate-800">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-2 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span>Simulasi Tampilan di Form Registrasi Peserta (Contoh Tujuan: Jakarta Selatan / Cilandak - 1 KG):</span>
                </span>

                @if($sampleRate && !empty($sampleRate['services']))
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-3">
                        @foreach($sampleRate['services'] as $svc)
                            <div class="p-3.5 rounded-xl border border-slate-800 bg-slate-900 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <span class="text-lg">{{ $svc['service_code'] === 'SPX_HEMAT' ? '💰' : '🚀' }}</span>
                                    <div>
                                        <span class="text-xs font-bold text-white block">{{ $svc['service_name'] }}</span>
                                        <span class="text-[11px] text-slate-400">Estimasi Tiba: {{ $svc['etd_text'] }}</span>
                                    </div>
                                </div>
                                <span class="font-mono-num font-bold text-sm text-emerald-400">Rp {{ number_format($svc['total_cost'], 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-xs text-slate-500 italic">Data tarif simulasi belum termuat.</p>
                @endif
            </div>

            <!-- Submit Button -->
            <div class="pt-2 flex justify-end">
                <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-lg shadow-orange-950/50 glow-orange flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>Simpan Pengaturan Ekspedisi SPX</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
