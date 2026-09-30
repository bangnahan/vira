@extends('layouts.app')

@section('title', 'Pendaftaran ' . $event->title . ' — VIRA')

@section('content')
<div class="max-w-4xl mx-auto px-3.5 sm:px-6 lg:px-8 py-6 sm:py-10">
    <!-- Top Event Hero Banner -->
    <div class="relative rounded-2xl sm:rounded-3xl overflow-hidden mb-6 sm:mb-8 border border-slate-800 shadow-2xl bg-slate-950 flex items-end">
        <div class="relative w-full h-44 sm:h-64">
            <img src="{{ $event->banner_url }}" alt="{{ $event->title }}" class="absolute inset-0 w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-slate-950 via-slate-950/50 to-transparent"></div>
            
            <div class="absolute top-4 left-4 flex gap-2">
                <a href="{{ route('events.show', $event->slug) }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold text-slate-300 bg-slate-900/80 hover:text-white border border-slate-700 backdrop-blur-md transition">
                    &larr; Detail Event
                </a>
            </div>

            <div class="absolute bottom-3 left-3 right-3 sm:bottom-6 sm:left-6 sm:right-6">
                <span class="px-2.5 py-1 rounded-md text-[10px] sm:text-[11px] font-extrabold uppercase tracking-wider bg-[#FF5500] text-white">
                    Form Pendaftaran Guest &bull; {{ $event->activity_type }}
                </span>
                <h1 class="text-xl sm:text-3xl font-black text-white mt-1 sm:mt-1.5 leading-tight">{{ $event->title }}</h1>
                <p class="text-[11px] sm:text-xs text-slate-300 mt-0.5 sm:mt-1">Daftar instan tanpa password. Cukup lengkapi data diri, pilih paket, dan selesaikan pembayaran.</p>
            </div>
        </div>
    </div>

    <!-- Flash Error / Warning / Validation Alerts -->
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/50 flex items-start gap-3 text-rose-300 shadow-lg">
            <svg class="w-5 h-5 text-rose-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div class="text-sm font-medium">{{ session('error') }}</div>
        </div>
    @endif

    @if(session('warning'))
        <div class="mb-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/50 flex items-start gap-3 text-amber-300 shadow-lg">
            <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <div class="text-sm font-medium">{{ session('warning') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-rose-500/10 border border-rose-500/50 text-rose-300 shadow-lg">
            <div class="flex items-center gap-2 font-bold text-sm mb-2 text-rose-400">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Mohon lengkapi formulir Anda:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-1 text-rose-300/90 pl-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('events.register.store', $event->slug) }}" method="POST" id="regForm" class="space-y-6 sm:space-y-8">
        @csrf

        <!-- STEP 1: Pilih Kategori Jarak -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-6 md:p-8 shadow-xl">
            <div class="flex items-center gap-3 mb-6">
                <span class="w-8 h-8 rounded-lg bg-[#FF5500]/20 text-[#FF5500] font-athletic text-xl flex items-center justify-center">1</span>
                <div>
                    <h3 class="text-lg font-bold text-white">Pilih Kategori Jarak</h3>
                    <p class="text-xs text-slate-400">Tentukan target lari yang ingin Anda capai selama periode event.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($event->categories as $cat)
                    <label class="relative flex flex-col p-4 rounded-xl border border-slate-800 bg-slate-950/60 cursor-pointer hover:border-slate-600 transition has-[:checked]:border-[#FF5500] has-[:checked]:bg-[#FF5500]/5">
                        <input type="radio" name="category_id" value="{{ $cat->id }}" class="sr-only" {{ ($loop->first || old('category_id') == $cat->id) ? 'checked' : '' }}>
                        <span class="font-athletic text-3xl text-white">{{ $cat->name }}</span>
                        <span class="text-xs font-bold text-[#FF5500] mt-1">Target: {{ number_format($cat->target_distance_km, 1) }} KM</span>
                    </label>
                @endforeach
            </div>
            @error('category_id') <p class="text-rose-400 text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        <!-- STEP 2: Pilih Paket Pendaftaran -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-6 md:p-8 shadow-xl">
            <div class="flex items-center gap-3 mb-6">
                <span class="w-8 h-8 rounded-lg bg-[#00E5FF]/20 text-[#00E5FF] font-athletic text-xl flex items-center justify-center">2</span>
                <div>
                    <h3 class="text-lg font-bold text-white">Pilih Paket Pendaftaran</h3>
                    <p class="text-xs text-slate-400">Pilih antara paket digital finisher atau race pack fisik lengkap medali & jersey.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @foreach($event->packages as $pkg)
                    <label class="relative flex flex-col justify-between p-5 rounded-xl border border-slate-800 bg-slate-950/60 cursor-pointer hover:border-slate-600 transition has-[:checked]:border-[#00E5FF] has-[:checked]:bg-[#00E5FF]/5"
                           data-requires-shipping="{{ $pkg->requires_shipping ? '1' : '0' }}"
                           data-base-weight="{{ $pkg->base_weight_grams }}"
                           data-price="{{ $pkg->price }}">
                        <input type="radio" name="package_id" value="{{ $pkg->id }}" class="sr-only package-radio" {{ ($loop->first || old('package_id') == $pkg->id) ? 'checked' : '' }}>
                        <div>
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-white text-base">{{ $pkg->name }}</span>
                                <span class="font-mono-num font-bold text-sm text-emerald-400">Rp {{ number_format($pkg->price, 0, ',', '.') }}</span>
                            </div>
                            <p class="text-xs text-slate-400 mt-2 leading-relaxed">{{ $pkg->description }}</p>
                        </div>

                        <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between text-[11px]">
                            @if($pkg->requires_shipping)
                                <span class="inline-flex items-center gap-1.5 text-cyan-400 font-medium">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                                    Termasuk Pengiriman Fisik (SPX)
                                </span>
                                <span class="text-slate-400 font-mono-num">{{ $pkg->base_weight_grams }}g</span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-emerald-400 font-medium">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    Paket Digital (Bebas Ongkir)
                                </span>
                                <span class="text-slate-500">Instant Access</span>
                            @endif
                        </div>
                    </label>
                @endforeach
            </div>
            @error('package_id') <p class="text-rose-400 text-xs mt-2">{{ $message }}</p> @enderror
        </div>

        <!-- STEP 3: Add-ons & Merchandise Opsional -->
        @if($addOns->count() > 0)
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-6 md:p-8 shadow-xl">
            <div class="flex items-center gap-3 mb-6">
                <span class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 font-athletic text-xl flex items-center justify-center">3</span>
                <div>
                    <h3 class="text-lg font-bold text-white">Tambahan Add-ons & Merchandise</h3>
                    <p class="text-xs text-slate-400">Pilih aksesoris atau merchandise resmi lomba (opsional).</p>
                </div>
            </div>

            <div class="space-y-4">
                @foreach($addOns as $index => $addon)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 rounded-xl border border-slate-800 bg-slate-950/40 gap-4"
                         data-addon-price="{{ $addon->price }}"
                         data-addon-weight="{{ $addon->weight_grams }}">
                        <div class="flex items-center gap-4">
                            @if($addon->image_url)
                                <div class="w-16 h-16 rounded-xl bg-slate-900 border border-slate-800 flex-shrink-0 overflow-hidden flex items-center justify-center shadow-sm" style="width: 64px; height: 64px; min-width: 64px; min-height: 64px; max-width: 64px; max-height: 64px;">
                                    <img src="{{ $addon->image_url }}" alt="{{ $addon->name }}" class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover;">
                                </div>
                            @endif
                            <div>
                                <h4 class="font-bold text-sm text-white">{{ $addon->name }}</h4>
                                <span class="font-mono-num text-xs text-emerald-400 font-bold block mt-0.5">Rp {{ number_format($addon->price, 0, ',', '.') }}</span>
                                <span class="text-[11px] text-slate-500 block">Berat: {{ $addon->weight_grams }}g • Stok: {{ $addon->stock }}</span>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 self-end sm:self-center">
                            @if($addon->variants->count() > 0)
                                <select name="addons[{{ $index }}][variant_id]" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-1.5 text-xs text-white">
                                    <option value="">-- Pilih Varian --</option>
                                    @foreach($addon->variants as $variant)
                                        <option value="{{ $variant->id }}">
                                            {{ $variant->variant_name }} 
                                            @if($variant->additional_price > 0) (+Rp {{ number_format($variant->additional_price, 0, ',', '.') }}) @endif
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            <input type="hidden" name="addons[{{ $index }}][id]" value="{{ $addon->id }}">
                            <div class="flex items-center">
                                <label class="text-xs text-slate-400 mr-2">Qty:</label>
                                <input type="number" name="addons[{{ $index }}][quantity]" min="0" max="{{ min(10, $addon->stock) }}" value="{{ old('addons.'.$index.'.quantity', 0) }}"
                                       class="w-16 bg-slate-900 border border-slate-700 rounded-lg px-2.5 py-1.5 text-xs text-white text-center addon-qty-input focus:border-[#FF5500]">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- STEP 4: Data Pribadi Peserta -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-6 md:p-8 shadow-xl">
            <div class="flex items-center gap-3 mb-6">
                <span class="w-8 h-8 rounded-lg bg-purple-500/20 text-purple-400 font-athletic text-xl flex items-center justify-center">4</span>
                <div>
                    <h3 class="text-lg font-bold text-white">Data Pribadi Peserta</h3>
                    <p class="text-xs text-slate-400">Data digunakan untuk pembuatan e-BIB resmi dan verifikasi peserta.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nama Lengkap Sesuai KTP / ID <span class="text-rose-400">*</span></label>
                    <input type="text" name="full_name" value="{{ old('full_name') }}" required placeholder="Contoh: Aditya Pratama"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                    @error('full_name') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Email Aktif (Pengiriman e-BIB & Tagihan) <span class="text-rose-400">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="aditya@example.com"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                    @error('email') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">No. Handphone / WhatsApp <span class="text-rose-400">*</span></label>
                    <input type="tel" name="phone_number" value="{{ old('phone_number') }}" required placeholder="08123456789"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                    @error('phone_number') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Ukuran Jersey (Jika Memilih Paket Fisik / Jersey)</label>
                    <select name="jersey_size" class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                        <option value="">-- Pilih Ukuran Jersey --</option>
                        @foreach(['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'] as $size)
                            <option value="{{ $size }}" {{ old('jersey_size') === $size ? 'selected' : '' }}>{{ $size }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- STEP 5: Pengiriman SPX (Dinamis: Wajib untuk Paket Fisik, Bebas Ongkir untuk Digital) -->
        <div id="shippingSection" class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-6 md:p-8 shadow-xl transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-orange-500/20 text-[#FF5500] font-athletic text-xl flex items-center justify-center">5</span>
                    <div>
                        <h3 class="text-lg font-bold text-white">Alamat Pengiriman (SPX Express)</h3>
                        <p class="text-xs text-slate-400">Pengiriman langsung dari <strong>Kab. Tangerang, Banten</strong> ke seluruh Indonesia.</p>
                    </div>
                </div>
                <span class="text-xs font-mono-num px-2.5 py-1 rounded bg-slate-800 text-slate-300" id="totalWeightDisplay">
                    Total Berat: 0g
                </span>
            </div>

            <!-- Notice jika memilih Paket Digital (Bebas Ongkir) -->
            <div id="noShippingNotice" class="hidden p-4 rounded-xl border border-cyan-500/30 bg-cyan-950/20 text-xs text-cyan-300 flex items-center gap-3 mb-2">
                <span class="text-2xl">⚡</span>
                <div>
                    <span class="font-bold text-white block text-sm">Paket Digital Finisher — Bebas Ongkos Kirim</span>
                    <span>Paket yang Anda pilih tidak memerlukan pengiriman fisik (e-BIB, tracking, dan e-Sertifikat dikirim instan secara digital). Anda tidak perlu melengkapi alamat pengiriman.</span>
                </div>
            </div>

            <!-- Kontainer Input Pengiriman Fisik -->
            <div id="shippingInputsContainer" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nama Penerima Paket</label>
                    <input type="text" id="recipientNameInput" name="recipient_name" value="{{ old('recipient_name') }}" placeholder="Nama penerima paket"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">No. Telepon Penerima</label>
                    <input type="tel" id="recipientPhoneInput" name="recipient_phone" value="{{ old('recipient_phone') }}" placeholder="081234567890"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                </div>

                <!-- Input Live Search Autocomplete Kota / Kabupaten SPX -->
                <div class="relative">
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">
                        Kota / Kabupaten Tujuan (SPX) <span class="text-rose-400" id="cityRequiredStar">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" 
                               id="citySearchInput" 
                               name="destination_city" 
                               value="{{ old('destination_city') }}" 
                               autocomplete="off" 
                               placeholder="Ketik nama Kota / Kab (cth: Tangerang, Bandung, Surabaya)..."
                               class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 pl-10 pr-10 text-sm text-white focus:border-[#FF5500] focus:ring-0">
                        
                        <!-- Search Icon -->
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>

                        <!-- Clear Button -->
                        <button type="button" id="clearCityBtn" class="hidden absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-white transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <!-- Live Suggestions Dropdown -->
                    <div id="citySuggestionsDropdown" class="hidden absolute z-50 left-0 right-0 mt-1 max-h-60 overflow-y-auto bg-slate-900 border border-slate-700 rounded-xl shadow-2xl divide-y divide-slate-800/80"></div>
                    <p class="text-[11px] text-slate-500 mt-1">Ketik untuk mencari dari 500+ kota/kabupaten di seluruh Indonesia.</p>
                </div>

                <!-- Dropdown Kecamatan Tujuan SPX -->
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">
                        Kecamatan Tujuan <span class="text-rose-400" id="districtRequiredStar">*</span>
                    </label>
                    <select name="destination_district" id="districtSelect" disabled class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500] disabled:opacity-50">
                        <option value="">-- Ketik &amp; Pilih Kota Terlebih Dahulu --</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Alamat Lengkap (Jalan, No Rumah, RT/RW, Patokan)</label>
                    <textarea name="address_detail" id="addressDetailInput" rows="2" placeholder="Jl. Melati No. 12 RT 01/RW 02..."
                              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">{{ old('address_detail') }}</textarea>
                </div>

                <!-- Pilihan Layanan SPX -->
                <div class="sm:col-span-2" id="serviceOptionsContainer">
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-2">Pilihan Layanan Ongkir SPX:</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="spxServicesGrid">
                        <div class="p-4 rounded-xl border border-slate-800 bg-slate-950/60 text-xs text-slate-500 text-center col-span-2">
                            Pilih Kota dan Kecamatan untuk melihat tarif ongkir SPX.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 6: Pilih Metode Pembayaran (Tripay Payment Gateway) -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-6 md:p-8 shadow-xl">
            <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 font-athletic text-xl flex items-center justify-center">6</span>
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-white">Metode Pembayaran (Tripay)</h3>
                        <p class="text-xs text-slate-400">Verifikasi otomatis 24 jam real-time. Pilih metode pembayaran yang Anda inginkan:</p>
                    </div>
                </div>
                <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/30">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Otomatis Terverifikasi
                </span>
            </div>

            <div class="space-y-2">
                <label for="paymentChannelSelect" class="block text-xs font-bold text-slate-300 uppercase tracking-wider">
                    Pilih Saluran Pembayaran <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <select name="payment_channel" 
                            id="paymentChannelSelect"
                            required 
                            class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500] focus:ring-1 focus:ring-[#FF5500] transition cursor-pointer appearance-none pr-10">
                        @if(isset($groupedPaymentChannels) && $groupedPaymentChannels->count() > 0)
                            @foreach($groupedPaymentChannels as $groupName => $channels)
                                <optgroup label="— {{ strtoupper($groupName) }} —" class="bg-slate-950 text-slate-400 font-bold py-1">
                                    @foreach($channels as $ch)
                                        @php
                                            $isQris = ($ch['code'] === 'QRIS2' || str_contains(strtoupper($ch['code']), 'QRIS'));
                                            $isSelected = old('payment_channel') ? (old('payment_channel') === $ch['code']) : $isQris;
                                        @endphp
                                        <option value="{{ $ch['code'] }}" 
                                                data-channel-name="{{ $ch['name'] }}"
                                                class="bg-slate-900 text-white font-normal py-1.5"
                                                {{ $isSelected ? 'selected' : '' }}>
                                            {{ $ch['name'] }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        @else
                            <optgroup label="— E-WALLET &amp; QRIS —" class="bg-slate-950 text-slate-400 font-bold py-1">
                                <option value="QRIS2" data-channel-name="QRIS (Semua Bank &amp; E-Wallet)" selected>QRIS (Semua Bank &amp; E-Wallet)</option>
                                <option value="OVO" data-channel-name="OVO">OVO</option>
                                <option value="SHOPEEPAY" data-channel-name="ShopeePay">ShopeePay</option>
                                <option value="DANA" data-channel-name="DANA">DANA</option>
                            </optgroup>
                            <optgroup label="— VIRTUAL ACCOUNT —" class="bg-slate-950 text-slate-400 font-bold py-1">
                                <option value="BCAVA" data-channel-name="BCA Virtual Account">BCA Virtual Account</option>
                                <option value="BRIVA" data-channel-name="BRI Virtual Account">BRI Virtual Account</option>
                                <option value="BNIVA" data-channel-name="BNI Virtual Account">BNI Virtual Account</option>
                                <option value="MANDIRIVA" data-channel-name="Mandiri Virtual Account">Mandiri Virtual Account</option>
                                <option value="PERMATAVA" data-channel-name="Permata Virtual Account">Permata Virtual Account</option>
                                <option value="CIMBVA" data-channel-name="CIMB Niaga Virtual Account">CIMB Niaga Virtual Account</option>
                                <option value="BSIVA" data-channel-name="BSI (Bank Syariah Indonesia) Virtual Account">BSI (Bank Syariah Indonesia) Virtual Account</option>
                                <option value="DANAMONVA" data-channel-name="Danamon Virtual Account">Danamon Virtual Account</option>
                                <option value="BNCVA" data-channel-name="Bank Neo Commerce (BNC) Virtual Account">Bank Neo Commerce (BNC) Virtual Account</option>
                                <option value="MUAMALATVA" data-channel-name="Muamalat Virtual Account">Muamalat Virtual Account</option>
                            </optgroup>
                            <optgroup label="— GERAI RETAIL / MINIMARKET —" class="bg-slate-950 text-slate-400 font-bold py-1">
                                <option value="ALFAMART" data-channel-name="Alfamart">Alfamart</option>
                                <option value="INDOMARET" data-channel-name="Indomaret">Indomaret</option>
                                <option value="ALFAMIDI" data-channel-name="Alfamidi">Alfamidi</option>
                            </optgroup>
                        @endif
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3.5 text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>
                <p class="text-[11px] text-slate-400 mt-1">
                    Pilih metode yang Anda kehendaki. Kode QRIS atau nomor Virtual Account akan langsung diterbitkan setelah pendaftaran dikirim.
                </p>
                @error('payment_channel') <p class="text-rose-400 text-xs mt-1.5">{{ $message }}</p> @enderror
            </div>
        </div>

        <!-- STEP 7: Ringkasan Total & Tombol Checkout Tripay -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-900 to-slate-950 border border-slate-800 rounded-2xl p-4 sm:p-6 md:p-8 shadow-2xl">
            <h3 class="text-base sm:text-lg font-bold text-white mb-4">Ringkasan Pembayaran</h3>

            <div class="space-y-2.5 text-xs sm:text-sm pb-4 border-b border-slate-800">
                <div class="flex justify-between text-slate-300">
                    <span>Biaya Paket Pendaftaran</span>
                    <span class="font-mono-num font-bold text-white" id="summaryPackagePrice">Rp 0</span>
                </div>
                <div class="flex justify-between text-slate-300">
                    <span>Tambahan Add-ons &amp; Merchandise</span>
                    <span class="font-mono-num font-bold text-white" id="summaryAddonsPrice">Rp 0</span>
                </div>
                <div class="flex justify-between text-slate-300">
                    <span>Ongkos Kirim SPX (Asal Kab. Tangerang)</span>
                    <span class="font-mono-num font-bold text-cyan-400" id="summaryShippingPrice">Rp 0</span>
                </div>
                <div class="flex justify-between text-slate-300 pt-1">
                    <span>Metode Pembayaran Dipilih</span>
                    <span class="font-bold text-emerald-400 text-xs" id="summarySelectedMethod">QRIS (Semua Bank &amp; E-Wallet)</span>
                </div>
            </div>

            <div class="pt-5 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
                <div>
                    <span class="text-xs text-slate-400 block font-bold uppercase tracking-wider">Total Tagihan Pembayaran</span>
                    <span class="text-2xl sm:text-4xl font-extrabold text-white font-mono-num" id="summaryGrandTotal">Rp 0</span>
                </div>

                <button type="submit" 
                        id="submitBtn" 
                        class="w-full sm:w-auto px-8 sm:px-10 py-3.5 sm:py-4 rounded-xl font-bold uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-lg shadow-orange-950/60 glow-orange flex items-center justify-center gap-3 cursor-pointer">
                    <span id="submitBtnNormal" class="flex items-center gap-2">
                        <span>Lanjut Bayar via Tripay</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </span>
                    <span id="submitBtnLoading" class="hidden flex items-center gap-2">
                        <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        <span>Menghubungkan ke Tripay...</span>
                    </span>
                </button>
            </div>
            
            <div class="mt-4 pt-4 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between text-[11px] text-slate-500 gap-2">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    Transaksi Terenkripsi SSL 256-Bit • Tripay Payment Gateway Resmi
                </span>
                <span>e-BIB dan invoice otomatis terbit setelah pembayaran lunas</span>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const packageRadios = document.querySelectorAll('.package-radio');
    const addonQtyInputs = document.querySelectorAll('.addon-qty-input');
    
    // Shipping Elements
    const shippingSection = document.getElementById('shippingSection');
    const noShippingNotice = document.getElementById('noShippingNotice');
    const shippingInputsContainer = document.getElementById('shippingInputsContainer');
    const citySearchInput = document.getElementById('citySearchInput');
    const citySuggestionsDropdown = document.getElementById('citySuggestionsDropdown');
    const clearCityBtn = document.getElementById('clearCityBtn');
    const districtSelect = document.getElementById('districtSelect');
    const spxServicesGrid = document.getElementById('spxServicesGrid');
    const totalWeightDisplay = document.getElementById('totalWeightDisplay');
    const cityRequiredStar = document.getElementById('cityRequiredStar');
    const districtRequiredStar = document.getElementById('districtRequiredStar');

    // Summary Elements
    const summaryPackagePrice = document.getElementById('summaryPackagePrice');
    const summaryAddonsPrice = document.getElementById('summaryAddonsPrice');
    const summaryShippingPrice = document.getElementById('summaryShippingPrice');
    const summaryGrandTotal = document.getElementById('summaryGrandTotal');
    const summarySelectedMethod = document.getElementById('summarySelectedMethod');

    // Submit Button
    const regForm = document.getElementById('regForm');
    const submitBtn = document.getElementById('submitBtn');
    const submitBtnNormal = document.getElementById('submitBtnNormal');
    const submitBtnLoading = document.getElementById('submitBtnLoading');

    let currentShippingCost = 0;

    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID');
    }

    function checkRequiresShipping() {
        const checkedPkg = document.querySelector('.package-radio:checked');
        const pkgRequires = checkedPkg ? (checkedPkg.closest('label').dataset.requiresShipping === '1') : false;
        
        let addonsRequire = false;
        addonQtyInputs.forEach(input => {
            if ((parseInt(input.value) || 0) > 0) {
                addonsRequire = true;
            }
        });

        return (pkgRequires || addonsRequire);
    }

    function calculateTotalWeight() {
        let weight = 0;
        const checkedPkg = document.querySelector('.package-radio:checked');
        if (checkedPkg) {
            const parent = checkedPkg.closest('label');
            weight += parseInt(parent.dataset.baseWeight || 0);
        }

        addonQtyInputs.forEach(input => {
            const qty = parseInt(input.value) || 0;
            if (qty > 0) {
                const parent = input.closest('[data-addon-weight]');
                weight += (parseInt(parent.dataset.addonWeight || 0) * qty);
            }
        });

        return weight;
    }

    function syncShippingVisibility() {
        const requires = checkRequiresShipping();

        if (requires) {
            noShippingNotice.classList.add('hidden');
            shippingInputsContainer.classList.remove('opacity-40', 'pointer-events-none');
            citySearchInput.setAttribute('required', 'required');
            districtSelect.removeAttribute('disabled');
            cityRequiredStar.classList.remove('hidden');
            districtRequiredStar.classList.remove('hidden');
        } else {
            noShippingNotice.classList.remove('hidden');
            shippingInputsContainer.classList.add('opacity-40', 'pointer-events-none');
            citySearchInput.removeAttribute('required');
            districtSelect.removeAttribute('required');
            cityRequiredStar.classList.add('hidden');
            districtRequiredStar.classList.add('hidden');
            currentShippingCost = 0;
        }
    }

    function updateSummary() {
        let pkgPrice = 0;
        let addonsPrice = 0;

        const checkedPkg = document.querySelector('.package-radio:checked');
        if (checkedPkg) {
            const parent = checkedPkg.closest('label');
            pkgPrice = parseFloat(parent.dataset.price || 0);
        }

        addonQtyInputs.forEach(input => {
            const qty = parseInt(input.value) || 0;
            if (qty > 0) {
                const parent = input.closest('[data-addon-price]');
                addonsPrice += (parseFloat(parent.dataset.addonPrice || 0) * qty);
            }
        });

        const requiresShipping = checkRequiresShipping();
        const effectiveShipping = requiresShipping ? currentShippingCost : 0;
        const totalWeight = calculateTotalWeight();
        
        totalWeightDisplay.textContent = `Total Berat: ${totalWeight}g (${Math.max(1, Math.ceil(totalWeight / 1000))} kg)`;

        summaryPackagePrice.textContent = formatRupiah(pkgPrice);
        summaryAddonsPrice.textContent = formatRupiah(addonsPrice);
        summaryShippingPrice.textContent = formatRupiah(effectiveShipping);
        summaryGrandTotal.textContent = formatRupiah(pkgPrice + addonsPrice + effectiveShipping);
    }

    packageRadios.forEach(r => r.addEventListener('change', () => {
        syncShippingVisibility();
        updateSummary();
        if (checkRequiresShipping() && citySearchInput.value && districtSelect.value) {
            fetchSpxRates();
        }
    }));

    addonQtyInputs.forEach(i => i.addEventListener('input', () => {
        syncShippingVisibility();
        updateSummary();
        if (checkRequiresShipping() && citySearchInput.value && districtSelect.value) {
            fetchSpxRates();
        }
    }));

    // Payment Channel Dropdown Interaction
    const paymentChannelSelect = document.getElementById('paymentChannelSelect');
    function updateSelectedPaymentChannel() {
        if (!paymentChannelSelect || !summarySelectedMethod) return;
        const selectedOption = paymentChannelSelect.options[paymentChannelSelect.selectedIndex];
        if (selectedOption) {
            summarySelectedMethod.textContent = selectedOption.dataset.channelName || selectedOption.text.trim();
        }
    }

    if (paymentChannelSelect) {
        paymentChannelSelect.addEventListener('change', updateSelectedPaymentChannel);
        updateSelectedPaymentChannel();
    }

    // Live Search Autocomplete Kota / Kabupaten SPX
    let cityDebounceTimer = null;

    citySearchInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(cityDebounceTimer);

        if (query.length > 0) {
            clearCityBtn.classList.remove('hidden');
        } else {
            clearCityBtn.classList.add('hidden');
            citySuggestionsDropdown.classList.add('hidden');
            resetDistricts();
            return;
        }

        cityDebounceTimer = setTimeout(() => {
            citySuggestionsDropdown.innerHTML = '<div class="p-3 text-xs text-slate-400 text-center flex items-center justify-center gap-2"><svg class="animate-spin h-3.5 w-3.5 text-[#FF5500]" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Mencari kota/kabupaten...</div>';
            citySuggestionsDropdown.classList.remove('hidden');

            fetch(`/api/shipping/cities?q=${encodeURIComponent(query)}`)
                .then(res => res.json())
                .then(res => {
                    if (res.status === 'success' && res.data && res.data.length > 0) {
                        let html = '';
                        res.data.forEach(city => {
                            html += `
                                <div class="px-4 py-3 hover:bg-slate-800 cursor-pointer text-xs text-slate-200 hover:text-white transition flex items-center justify-between group city-item" data-city="${city}">
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-500 group-hover:text-[#FF5500]">📍</span>
                                        <span class="font-bold">${city}</span>
                                    </div>
                                    <span class="text-[10px] text-slate-500 uppercase tracking-wider group-hover:text-[#FF5500]">Pilih &rarr;</span>
                                </div>
                            `;
                        });
                        citySuggestionsDropdown.innerHTML = html;

                        document.querySelectorAll('.city-item').forEach(item => {
                            item.addEventListener('click', function() {
                                const selectedCity = this.dataset.city;
                                citySearchInput.value = selectedCity;
                                citySuggestionsDropdown.classList.add('hidden');
                                loadDistrictsForCity(selectedCity);
                            });
                        });
                    } else {
                        citySuggestionsDropdown.innerHTML = '<div class="p-3 text-xs text-slate-500 text-center">Kota/Kabupaten tidak ditemukan. Coba ketik nama lain.</div>';
                    }
                })
                .catch(() => {
                    citySuggestionsDropdown.innerHTML = '<div class="p-3 text-xs text-rose-400 text-center">Gagal mencari kota. Coba lagi.</div>';
                });
        }, 200);
    });

    clearCityBtn.addEventListener('click', function() {
        citySearchInput.value = '';
        this.classList.add('hidden');
        citySuggestionsDropdown.classList.add('hidden');
        resetDistricts();
        citySearchInput.focus();
    });

    document.addEventListener('click', function(e) {
        if (!citySearchInput.contains(e.target) && !citySuggestionsDropdown.contains(e.target)) {
            citySuggestionsDropdown.classList.add('hidden');
        }
    });

    function resetDistricts() {
        districtSelect.innerHTML = '<option value="">-- Ketik &amp; Pilih Kota Terlebih Dahulu --</option>';
        districtSelect.disabled = true;
        spxServicesGrid.innerHTML = '<div class="p-4 rounded-xl border border-slate-800 bg-slate-950/60 text-xs text-slate-500 text-center col-span-2">Pilih Kota dan Kecamatan untuk melihat tarif ongkir SPX.</div>';
        currentShippingCost = 0;
        updateSummary();
    }

    function loadDistrictsForCity(city) {
        districtSelect.innerHTML = '<option value="">Memuat kecamatan di ' + city + '...</option>';
        districtSelect.disabled = true;

        fetch(`/api/shipping/districts?city=${encodeURIComponent(city)}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.data && data.data.length > 0) {
                    let html = '<option value="">-- Pilih Kecamatan di ' + city + ' --</option>';
                    data.data.forEach(d => {
                        html += `<option value="${d}">${d}</option>`;
                    });
                    districtSelect.innerHTML = html;
                    districtSelect.disabled = false;
                    districtSelect.focus();
                } else {
                    districtSelect.innerHTML = '<option value="">Kecamatan tidak ditemukan</option>';
                }
            })
            .catch(() => {
                districtSelect.innerHTML = '<option value="">Gagal memuat kecamatan</option>';
            });
    }

    districtSelect.addEventListener('change', fetchSpxRates);

    function fetchSpxRates() {
        const city = citySearchInput.value.trim();
        const district = districtSelect.value;
        const weight = calculateTotalWeight();

        if (!city || !district) {
            return;
        }

        spxServicesGrid.innerHTML = '<div class="p-4 rounded-xl border border-slate-800 bg-slate-950/60 text-xs text-slate-400 text-center col-span-2">Menghitung tarif SPX...</div>';

        fetch('/api/shipping/calculate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                destination_city: city,
                destination_district: district,
                weight_grams: weight
            })
        })
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success') {
                let html = '';
                const services = res.data.services;
                services.forEach((s, idx) => {
                    const isChecked = (idx === 0) ? 'checked' : '';
                    if (idx === 0) {
                        currentShippingCost = s.total_cost;
                    }
                    html += `
                        <label class="flex items-center justify-between p-4 rounded-xl border border-slate-800 bg-slate-950 cursor-pointer hover:border-slate-700 transition has-[:checked]:border-cyan-400 has-[:checked]:bg-cyan-500/5">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="spx_service" value="${s.service_code}" class="sr-only spx-service-radio" ${isChecked} data-cost="${s.total_cost}">
                                <div>
                                    <span class="font-bold text-sm text-white block">${s.service_name}</span>
                                    <span class="text-[11px] text-slate-400">Estimasi Tiba: ${s.etd_text}</span>
                                </div>
                            </div>
                            <span class="font-mono-num font-bold text-sm text-emerald-400">${formatRupiah(s.total_cost)}</span>
                        </label>
                    `;
                });
                spxServicesGrid.innerHTML = html;

                document.querySelectorAll('.spx-service-radio').forEach(radio => {
                    radio.addEventListener('change', function() {
                        currentShippingCost = parseFloat(this.dataset.cost);
                        updateSummary();
                    });
                });

                updateSummary();
            } else {
                spxServicesGrid.innerHTML = `<div class="p-4 text-xs text-rose-400 text-center col-span-2">${res.message}</div>`;
            }
        })
        .catch(() => {
            spxServicesGrid.innerHTML = '<div class="p-4 text-xs text-rose-400 text-center col-span-2">Gagal memuat tarif SPX.</div>';
        });
    }

    // Submit handler with user validation & loading state
    regForm.addEventListener('submit', function(e) {
        const requiresShipping = checkRequiresShipping();

        if (requiresShipping) {
            const city = citySearchInput.value.trim();
            const district = districtSelect.value.trim();

            if (!city) {
                e.preventDefault();
                alert('Silakan ketik dan pilih Kota / Kabupaten pengiriman terlebih dahulu.');
                citySearchInput.focus();
                citySearchInput.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }

            if (!district) {
                e.preventDefault();
                alert('Silakan pilih Kecamatan tujuan pengiriman Anda.');
                districtSelect.focus();
                districtSelect.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
        }

        // Nonaktifkan field add-on yang quantity-nya 0 agar form submission bersih
        addonQtyInputs.forEach(input => {
            const qty = parseInt(input.value) || 0;
            if (qty <= 0) {
                const parentRow = input.closest('[data-addon-price]');
                if (parentRow) {
                    parentRow.querySelectorAll('input, select').forEach(elem => {
                        elem.disabled = true;
                    });
                }
            }
        });

        // Active loading state on button
        submitBtn.disabled = true;
        submitBtnNormal.classList.add('hidden');
        submitBtnLoading.classList.remove('hidden');
    });

    // Inisialisasi awal
    syncShippingVisibility();
    updateSummary();
});
</script>

@if($event->meta_pixel_id)
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
fbq('track', 'InitiateCheckout', {
    content_name: '{{ addslashes($event->title) }}',
    content_type: 'product',
});

// Event AddToCart saat memilih paket
document.querySelectorAll('.package-radio').forEach(function(radio) {
    radio.addEventListener('change', function() {
        if (window.fbq) {
            fbq('track', 'AddToCart', {
                content_name: '{{ addslashes($event->title) }} (Paket Dipilih)',
                content_type: 'product',
                currency: 'IDR'
            });
        }
    });
});

// Event AddPaymentInfo saat formulir pendaftaran disubmit untuk pembayaran
var regFormElem = document.getElementById('regForm');
if (regFormElem) {
    regFormElem.addEventListener('submit', function() {
        if (window.fbq) {
            fbq('track', 'AddPaymentInfo', {
                content_name: '{{ addslashes($event->title) }}',
                content_type: 'product',
                currency: 'IDR'
            });
        }
    });
}
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={{ $event->meta_pixel_id }}&ev=PageView&noscript=1"
/></noscript>
@endif
@endpush
