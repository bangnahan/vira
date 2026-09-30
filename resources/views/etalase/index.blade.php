@extends('layouts.app')

@section('title', 'Official Store: Merchandise & Sport Gear — VIRA')

@section('content')
<div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Header -->
    <div class="text-center max-w-3xl mx-auto mb-8 sm:mb-12">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#FF5500]/10 border border-[#FF5500]/30 text-[#FF5500] text-xs font-bold uppercase tracking-wider mb-3">
            <span>🛍️ VIRA Official Store</span>
            <span>•</span>
            <span>Beli Mandiri</span>
        </div>
        <h1 class="text-3xl sm:text-5xl font-extrabold text-white font-athletic tracking-wide">
            Katalog Merchandise &amp; Official Gear
        </h1>
        <p class="text-xs sm:text-base text-slate-400 mt-2.5 sm:mt-3 leading-relaxed">
            Dapatkan jersey lari dry-fit, medali finisher ekstra, topi breathable, dan apparel resmi VIRA secara langsung <strong>tanpa harus mendaftar event</strong>. Pengiriman cepat ke seluruh Indonesia via SPX Express.
        </p>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm flex items-center justify-between shadow-lg max-w-4xl mx-auto">
            <div class="flex items-center gap-3">
                <span class="text-xl">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white">&times;</button>
        </div>
    @endif

    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center justify-between shadow-lg max-w-4xl mx-auto">
            <div class="flex items-center gap-3">
                <span class="text-xl">✅</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">&times;</button>
        </div>
    @endif

    <!-- Filter & Search Bar -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-5 mb-8 shadow-xl max-w-4xl mx-auto">
        <form action="{{ route('etalase.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <!-- Search -->
            <div class="sm:col-span-7 relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Cari jersey, topi, medali, atau merchandise..." 
                       class="w-full bg-slate-950 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-[#FF5500] focus:ring-1 focus:ring-[#FF5500]">
            </div>

            <!-- Filter Kategori Event -->
            <div class="sm:col-span-3">
                <select name="event_id" onchange="this.form.submit()" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3 py-2.5 text-xs text-white focus:border-[#FF5500]">
                    <option value="all" {{ $eventFilter === 'all' ? 'selected' : '' }}>— Semua Kategori —</option>
                    <option value="global" {{ $eventFilter === 'global' ? 'selected' : '' }}>🌐 Global Merchandise</option>
                    @foreach($events as $ev)
                        <option value="{{ $ev->id }}" {{ (string)$eventFilter === (string)$ev->id ? 'selected' : '' }}>
                            {{ $ev->event_code }} — {{ Str::limit($ev->title, 20) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Tombol Submit -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 border border-slate-700">
                    <span>Cari</span>
                </button>
                @if($search !== '' || $eventFilter !== 'all')
                    <a href="{{ route('etalase.index') }}" class="p-2.5 rounded-xl bg-slate-800 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 border border-slate-700 transition" title="Reset">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Products Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 sm:gap-8">
        @forelse($addOns as $item)
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl sm:rounded-3xl p-5 sm:p-6 flex flex-col justify-between hover:border-slate-700 transition shadow-xl group product-card"
                 data-id="{{ $item->id }}"
                 data-name="{{ $item->name }}"
                 data-price="{{ $item->price }}"
                 data-weight="{{ $item->weight_grams }}"
                 data-stock="{{ $item->total_stock }}"
                 data-has-variants="{{ $item->has_variants ? '1' : '0' }}">
                <div>
                    <!-- Product Graphic / Photo -->
                    <div class="h-56 rounded-2xl bg-gradient-to-tr from-slate-950 to-slate-800 flex items-center justify-center mb-5 border border-slate-800/80 group-hover:scale-[1.02] transition-transform overflow-hidden relative" style="height: 224px; max-height: 224px;">
                        @if($item->image_url)
                            <img src="{{ $item->image_url }}" alt="{{ $item->name }}" class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <span class="text-6xl">
                                @if(str_contains(strtolower($item->name), 'jersey'))
                                    🎽
                                @elseif(str_contains(strtolower($item->name), 'kunci') || str_contains(strtolower($item->name), 'medali'))
                                    🏅
                                @else
                                    🧢
                                @endif
                            </span>
                        @endif

                        <!-- Floating Badges -->
                        <div class="absolute top-3 left-3 flex flex-col gap-1">
                            @if($item->event)
                                <span class="text-[10px] font-bold uppercase tracking-wider text-cyan-300 bg-cyan-950/90 px-2.5 py-0.5 rounded-full border border-cyan-700/60 backdrop-blur-sm">
                                    {{ $item->event->event_code }}
                                </span>
                            @else
                                <span class="text-[10px] font-bold uppercase tracking-wider text-[#FF5500] bg-orange-950/90 px-2.5 py-0.5 rounded-full border border-orange-700/60 backdrop-blur-sm">
                                    🌐 Global Gear
                                </span>
                            @endif
                        </div>

                        <div class="absolute top-3 right-3">
                            <span class="text-[10px] font-mono-num font-bold text-slate-300 bg-slate-950/90 px-2 py-0.5 rounded-full border border-slate-800 backdrop-blur-sm">
                                {{ $item->weight_grams }}g
                            </span>
                        </div>
                    </div>

                    <h3 class="font-extrabold text-lg text-white group-hover:text-[#FF5500] transition line-clamp-1" title="{{ $item->name }}">
                        {{ $item->name }}
                    </h3>
                    <p class="text-xs text-slate-400 mt-1.5 leading-relaxed line-clamp-2">{{ $item->description }}</p>

                    <!-- Variants Dropdown -->
                    @if($item->has_variants && $item->variants->count() > 0)
                        <div class="mt-4 pt-3 border-t border-slate-800">
                            <label class="text-[11px] font-bold text-slate-300 block mb-1.5 uppercase tracking-wider">Pilih Ukuran / Varian:</label>
                            <select class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white focus:border-[#FF5500] variant-selector">
                                @foreach($item->variants as $v)
                                    <option value="{{ $v->id }}" 
                                            data-name="{{ $v->variant_name }}" 
                                            data-addprice="{{ $v->additional_price }}" 
                                            data-stock="{{ $v->stock }}">
                                        {{ $v->variant_name }} 
                                        @if($v->additional_price > 0) (+Rp {{ number_format($v->additional_price, 0, ',', '.') }}) @endif
                                        (Sisa: {{ $v->stock }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @else
                        <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between text-xs text-slate-400">
                            <span>Ketersediaan:</span>
                            <span class="font-mono-num font-bold {{ $item->stock <= 10 ? 'text-rose-400' : 'text-emerald-400' }}">
                                {{ $item->stock }} unit tersedia
                            </span>
                        </div>
                    @endif
                </div>

                <!-- Price & Action Section -->
                <div class="mt-6 pt-4 border-t border-slate-800">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] text-slate-400 font-bold uppercase">Harga Satuan</span>
                        <span class="font-mono-num font-extrabold text-xl text-white">
                            Rp {{ number_format($item->price, 0, ',', '.') }}
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <!-- Qty Selector -->
                        <div class="flex items-center bg-slate-950 border border-slate-700 rounded-xl overflow-hidden">
                            <button type="button" class="btn-qty-minus px-3 py-2 text-slate-400 hover:text-white hover:bg-slate-800 transition">-</button>
                            <input type="number" min="1" max="{{ min(20, $item->total_stock) }}" value="1" class="w-12 bg-transparent text-center text-xs font-mono-num text-white font-bold item-qty-input focus:outline-none">
                            <button type="button" class="btn-qty-plus px-3 py-2 text-slate-400 hover:text-white hover:bg-slate-800 transition">+</button>
                        </div>

                        <!-- Add to Cart / Buy Button -->
                        <button type="button" 
                                class="btn-add-cart flex-1 py-2.5 px-4 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-lg shadow-orange-950/40 glow-orange flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                            <span>Beli Sekarang</span>
                        </button>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 py-16 text-center text-slate-400 bg-slate-900/40 rounded-3xl border border-slate-800">
                <span class="text-4xl block mb-2">🛍️</span>
                <p class="text-base font-bold text-white mb-1">Belum ada merchandise yang sesuai dengan pencarian Anda</p>
                <p class="text-xs text-slate-500">Coba ubah kata kunci pencarian atau reset filter kategori di atas.</p>
            </div>
        @endforelse
    </div>
</div>

<!-- ========================================== -->
<!-- SLIDE-OVER CHECKOUT MODAL / SHOPPING CART -->
<!-- ========================================== -->
<div id="checkoutModal" class="fixed inset-0 z-50 overflow-hidden hidden" aria-labelledby="slide-over-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div id="modalBackdrop" class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity"></div>

    <div class="fixed inset-y-0 right-0 max-w-full flex pl-6 sm:pl-10">
        <div class="w-screen max-w-xl bg-slate-900 border-l border-slate-800 flex flex-col shadow-2xl">
            <!-- Header Drawer -->
            <div class="p-5 sm:p-6 border-b border-slate-800 flex items-center justify-between bg-slate-950/60">
                <div class="flex items-center gap-2.5">
                    <span class="text-2xl">🛍️</span>
                    <div>
                        <h2 class="text-lg font-bold text-white font-athletic tracking-wide">Checkout Pembelian Merchandise</h2>
                        <p class="text-xs text-slate-400">Pengiriman SPX Express &amp; Pembayaran Otomatis</p>
                    </div>
                </div>
                <button type="button" id="btnCloseModal" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body Drawer (Form) -->
            <form action="{{ route('shop.order.store') }}" method="POST" id="checkoutForm" class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-6 no-scrollbar">
                @csrf

                <!-- 1. Ringkasan Keranjang Belanja -->
                <div class="bg-slate-950/60 border border-slate-800 rounded-2xl p-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Barang Yang Dipesan</span>
                        <span id="cartCountBadge" class="text-[11px] font-mono-num font-bold text-[#FF5500] bg-orange-950 px-2 py-0.5 rounded-full border border-orange-800/60">0 item</span>
                    </div>

                    <div id="cartItemsList" class="space-y-3 divide-y divide-slate-800/80">
                        <!-- Populated dynamically by JS -->
                    </div>

                    <!-- Hidden Inputs for Submission -->
                    <div id="cartHiddenInputs"></div>

                    <div class="mt-4 pt-3 border-t border-slate-800 flex items-center justify-between text-xs">
                        <span class="text-slate-400">Total Berat: <strong id="cartTotalWeight" class="text-white">0</strong> gram</span>
                        <span class="text-slate-400">Subtotal Produk: <strong id="cartSubtotalText" class="text-white font-mono-num text-sm">Rp 0</strong></span>
                    </div>
                </div>

                <!-- 2. Data Pemesan / Penerima -->
                <div class="bg-slate-950/60 border border-slate-800 rounded-2xl p-4 space-y-3.5">
                    <span class="text-xs font-bold text-slate-300 uppercase tracking-wider block">Data Penerima Paket</span>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Nama Lengkap <span class="text-rose-500">*</span></label>
                        <input type="text" name="full_name" required placeholder="Nama penerima paket" class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs text-white focus:border-[#FF5500]">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Email <span class="text-rose-500">*</span></label>
                            <input type="email" name="email" required placeholder="email@anda.com" class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs text-white focus:border-[#FF5500]">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">No. WhatsApp <span class="text-rose-500">*</span></label>
                            <input type="tel" name="phone_number" required placeholder="0812xxxxxxxx" class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs text-white focus:border-[#FF5500]">
                        </div>
                    </div>
                </div>

                <!-- 3. Alamat Pengiriman (SPX Express) -->
                <div class="bg-slate-950/60 border border-slate-800 rounded-2xl p-4 space-y-3.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Alamat Pengiriman (SPX)</span>
                        <span class="text-[10px] text-cyan-400 font-bold bg-cyan-950 px-2 py-0.5 rounded border border-cyan-800">SPX Logistics</span>
                    </div>

                    <!-- Autocomplete Kota/Kabupaten -->
                    <div class="relative">
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kota / Kabupaten Tujuan <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <input type="text" id="shopCityInput" name="destination_city" required autocomplete="off" placeholder="Ketik minimal 3 huruf nama kota..." class="w-full bg-slate-900 border border-slate-700/80 rounded-xl pl-3.5 pr-8 py-2 text-xs text-white focus:border-[#FF5500]">
                            <button type="button" id="btnClearShopCity" class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-500 hover:text-white hidden text-base leading-none">&times;</button>
                        </div>
                        <div id="shopCityDropdown" class="absolute left-0 right-0 mt-1 bg-slate-950 border border-slate-700 rounded-xl shadow-2xl max-h-48 overflow-y-auto hidden z-20"></div>
                    </div>

                    <!-- Kecamatan -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kecamatan Tujuan <span class="text-rose-500">*</span></label>
                        <select id="shopDistrictSelect" name="destination_district" required disabled class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3 py-2 text-xs text-white focus:border-[#FF5500]">
                            <option value="">-- Pilih Kota Terlebih Dahulu --</option>
                        </select>
                    </div>

                    <!-- Alamat Lengkap & Kode Pos -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Alamat Jalan, No Rumah, RT/RW <span class="text-rose-500">*</span></label>
                        <textarea name="address_detail" required rows="2" placeholder="Jl. Sudirman No. 123, RT 01/RW 02..." class="w-full bg-slate-900 border border-slate-700/80 rounded-xl p-3 text-xs text-white focus:border-[#FF5500]"></textarea>
                    </div>

                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1">Kode Pos</label>
                        <input type="text" name="postal_code" placeholder="15xxx" class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2 text-xs text-white focus:border-[#FF5500]">
                    </div>

                    <!-- Pilihan Layanan SPX -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-400 uppercase tracking-wider mb-1.5">Layanan Kurir SPX</label>
                        <div id="shopSpxServices" class="space-y-2">
                            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-center text-xs text-slate-500">
                                Pilih Kota &amp; Kecamatan untuk memuat tarif ongkir SPX.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. Pilihan Saluran Pembayaran (Tripay) -->
                <div class="bg-slate-950/60 border border-slate-800 rounded-2xl p-4 space-y-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1" for="paymentChannelSelect">
                        Metode Pembayaran (Tripay) <span class="text-rose-500">*</span>
                    </label>
                    <select name="payment_channel" 
                            id="paymentChannelSelect"
                            required 
                            class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-[#FF5500] focus:ring-1 focus:ring-[#FF5500]">
                        @if(isset($groupedPaymentChannels) && $groupedPaymentChannels->count() > 0)
                            @foreach($groupedPaymentChannels as $groupName => $channels)
                                <optgroup label="— {{ strtoupper($groupName) }} —" class="bg-slate-950 text-slate-400 font-bold">
                                    @foreach($channels as $ch)
                                        <option value="{{ $ch['code'] }}" 
                                                class="bg-slate-900 text-white font-normal py-1"
                                                {{ $ch['code'] === 'QRIS2' ? 'selected' : '' }}>
                                            {{ $ch['name'] }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        @else
                            <optgroup label="— E-WALLET &amp; QRIS —" class="bg-slate-950 text-slate-400 font-bold">
                                <option value="QRIS2" selected>QRIS (Semua E-Wallet &amp; Mobile Banking)</option>
                                <option value="OVO">OVO</option>
                                <option value="SHOPEEPAY">ShopeePay</option>
                                <option value="DANA">DANA</option>
                            </optgroup>
                            <optgroup label="— VIRTUAL ACCOUNT —" class="bg-slate-950 text-slate-400 font-bold">
                                <option value="BCAVA">BCA Virtual Account</option>
                                <option value="BRIVA">BRI Virtual Account</option>
                                <option value="BNIVA">BNI Virtual Account</option>
                                <option value="MANDIRIVA">Mandiri Virtual Account</option>
                                <option value="PERMATAVA">Permata Virtual Account</option>
                                <option value="CIMBVA">CIMB Niaga Virtual Account</option>
                                <option value="BSIVA">BSI (Bank Syariah Indonesia) Virtual Account</option>
                                <option value="DANAMONVA">Danamon Virtual Account</option>
                                <option value="BNCVA">Bank Neo Commerce (BNC) Virtual Account</option>
                                <option value="MUAMALATVA">Muamalat Virtual Account</option>
                            </optgroup>
                            <optgroup label="— GERAI RETAIL / MINIMARKET —" class="bg-slate-950 text-slate-400 font-bold">
                                <option value="ALFAMART">Alfamart</option>
                                <option value="INDOMARET">Indomaret</option>
                                <option value="ALFAMIDI">Alfamidi</option>
                            </optgroup>
                        @endif
                    </select>
                    <span class="text-[11px] text-slate-500 block">Pilih saluran pembayaran yang Anda kehendaki dari daftar di atas.</span>
                </div>

                <!-- 5. Rincian Total Akhir -->
                <div class="p-4 rounded-2xl bg-gradient-to-br from-slate-950 to-slate-900 border border-slate-800 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-400">
                        <span>Subtotal Barang:</span>
                        <span id="finalItemsSubtotal" class="font-mono-num text-white">Rp 0</span>
                    </div>
                    <div class="flex justify-between text-slate-400">
                        <span>Ongkos Kirim SPX:</span>
                        <span id="finalShippingCost" class="font-mono-num text-cyan-400">Rp 0</span>
                    </div>
                    <div class="pt-2 border-t border-slate-800 flex justify-between items-center text-sm font-bold">
                        <span class="text-white">Total Tagihan:</span>
                        <span id="finalGrandTotal" class="text-lg font-mono-num text-[#FF5500] font-extrabold">Rp 0</span>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" 
                        id="btnSubmitOrder"
                        disabled
                        class="w-full py-3.5 px-4 rounded-xl font-bold text-sm uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] disabled:opacity-50 disabled:cursor-not-allowed transition shadow-lg shadow-orange-950/50 glow-orange flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <span>Bayar Sekarang via Tripay &rarr;</span>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Sticky Bottom Cart Bar (Muncul jika ada item di cart) -->
<div id="stickyCartBar" class="fixed bottom-0 inset-x-0 bg-slate-900/95 border-t border-slate-800 p-4 backdrop-blur-xl z-40 transform translate-y-full transition-transform duration-300 shadow-2xl">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-[#FF5500]/20 text-[#FF5500] flex items-center justify-center text-xl font-bold">
                🛍️
            </div>
            <div>
                <span id="stickyItemCount" class="text-xs font-bold text-white block">0 Produk Dipilih</span>
                <span id="stickySubtotal" class="text-sm font-mono-num font-extrabold text-[#FF5500]">Rp 0</span>
            </div>
        </div>

        <button type="button" id="btnOpenCheckoutFromSticky" class="px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-lg shadow-orange-950/50 glow-orange flex items-center gap-2">
            <span>Checkout Sekarang &rarr;</span>
        </button>
    </div>
</div>

<!-- Script Keranjang Belanja, Autocomplete SPX & Checkout -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    // State cart
    let cart = []; // [{ id, name, variant_id, variant_name, price, weight, quantity }]
    let currentShippingCost = 0;

    // Elements
    const checkoutModal = document.getElementById('checkoutModal');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const btnCloseModal = document.getElementById('btnCloseModal');
    const cartItemsList = document.getElementById('cartItemsList');
    const cartHiddenInputs = document.getElementById('cartHiddenInputs');
    const cartCountBadge = document.getElementById('cartCountBadge');
    const cartTotalWeight = document.getElementById('cartTotalWeight');
    const cartSubtotalText = document.getElementById('cartSubtotalText');
    const finalItemsSubtotal = document.getElementById('finalItemsSubtotal');
    const finalShippingCost = document.getElementById('finalShippingCost');
    const finalGrandTotal = document.getElementById('finalGrandTotal');
    const btnSubmitOrder = document.getElementById('btnSubmitOrder');

    // Sticky bar
    const stickyCartBar = document.getElementById('stickyCartBar');
    const stickyItemCount = document.getElementById('stickyItemCount');
    const stickySubtotal = document.getElementById('stickySubtotal');
    const btnOpenCheckoutFromSticky = document.getElementById('btnOpenCheckoutFromSticky');

    // SPX Autocomplete Elements
    const cityInput = document.getElementById('shopCityInput');
    const cityDropdown = document.getElementById('shopCityDropdown');
    const btnClearShopCity = document.getElementById('btnClearShopCity');
    const districtSelect = document.getElementById('shopDistrictSelect');
    const spxServicesContainer = document.getElementById('shopSpxServices');

    function formatRupiah(num) {
        return 'Rp ' + Number(num).toLocaleString('id-ID');
    }

    // Modal Controls
    function openCheckout() {
        if (cart.length === 0) return;
        renderCart();
        checkoutModal.classList.remove('hidden');
    }

    function closeCheckout() {
        checkoutModal.classList.add('hidden');
    }

    btnCloseModal.addEventListener('click', closeCheckout);
    modalBackdrop.addEventListener('click', closeCheckout);
    btnOpenCheckoutFromSticky.addEventListener('click', openCheckout);

    // Quantity buttons in product cards
    document.querySelectorAll('.product-card').forEach(card => {
        const qtyInput = card.querySelector('.item-qty-input');
        const btnMinus = card.querySelector('.btn-qty-minus');
        const btnPlus = card.querySelector('.btn-qty-plus');
        const btnAdd = card.querySelector('.btn-add-cart');

        btnMinus.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            if (val > 1) qtyInput.value = val - 1;
        });

        btnPlus.addEventListener('click', () => {
            let val = parseInt(qtyInput.value) || 1;
            let max = parseInt(qtyInput.max) || 20;
            if (val < max) qtyInput.value = val + 1;
        });

        btnAdd.addEventListener('click', () => {
            const id = parseInt(card.dataset.id);
            const name = card.dataset.name;
            const basePrice = parseFloat(card.dataset.price);
            const weight = parseInt(card.dataset.weight);
            const qty = parseInt(qtyInput.value) || 1;

            let variantId = null;
            let variantName = null;
            let finalPrice = basePrice;

            const varSelect = card.querySelector('.variant-selector');
            if (varSelect && varSelect.value) {
                variantId = parseInt(varSelect.value);
                const opt = varSelect.selectedOptions[0];
                variantName = opt.dataset.name;
                finalPrice += parseFloat(opt.dataset.addprice || 0);
            }

            // Check if already in cart
            const existingIndex = cart.findIndex(c => c.id === id && c.variant_id === variantId);
            if (existingIndex > -1) {
                cart[existingIndex].quantity += qty;
            } else {
                cart.push({
                    id: id,
                    name: name,
                    variant_id: variantId,
                    variant_name: variantName,
                    price: finalPrice,
                    weight: weight,
                    quantity: qty
                });
            }

            updateCartUI();
            openCheckout();
        });
    });

    // Update Cart UI (Sticky bar and Modal content)
    function updateCartUI() {
        const totalItems = cart.reduce((acc, c) => acc + c.quantity, 0);
        const subtotal = cart.reduce((acc, c) => acc + (c.price * c.quantity), 0);
        const totalWeight = cart.reduce((acc, c) => acc + (c.weight * c.quantity), 0);

        if (totalItems > 0) {
            stickyCartBar.classList.remove('translate-y-full');
            stickyItemCount.textContent = `${totalItems} Produk (${totalWeight}g)`;
            stickySubtotal.textContent = formatRupiah(subtotal);
        } else {
            stickyCartBar.classList.add('translate-y-full');
            closeCheckout();
        }

        renderCart();
    }

    function renderCart() {
        const totalItems = cart.reduce((acc, c) => acc + c.quantity, 0);
        const subtotal = cart.reduce((acc, c) => acc + (c.price * c.quantity), 0);
        const totalWeight = cart.reduce((acc, c) => acc + (c.weight * c.quantity), 0);

        cartCountBadge.textContent = `${totalItems} item`;
        cartTotalWeight.textContent = totalWeight;
        cartSubtotalText.textContent = formatRupiah(subtotal);
        finalItemsSubtotal.textContent = formatRupiah(subtotal);

        // Render Items List in Modal
        if (cart.length === 0) {
            cartItemsList.innerHTML = '<div class="py-4 text-center text-xs text-slate-500">Keranjang masih kosong.</div>';
            cartHiddenInputs.innerHTML = '';
            btnSubmitOrder.disabled = true;
            return;
        }

        let itemsHtml = '';
        let inputsHtml = '';

        cart.forEach((item, index) => {
            const lineTotal = item.price * item.quantity;
            itemsHtml += `
                <div class="pt-2.5 pb-2 flex items-center justify-between text-xs text-slate-200">
                    <div>
                        <span class="font-bold text-white block">${item.name}</span>
                        <span class="text-[11px] text-slate-400">${item.variant_name ? '(' + item.variant_name + ') ' : ''}${item.quantity} x ${formatRupiah(item.price)}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <span class="font-mono-num font-bold text-white">${formatRupiah(lineTotal)}</span>
                        <button type="button" class="btn-remove-cart-item text-slate-500 hover:text-rose-400 p-1" data-index="${index}" title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            `;

            inputsHtml += `
                <input type="hidden" name="items[${index}][id]" value="${item.id}">
                ${item.variant_id ? `<input type="hidden" name="items[${index}][variant_id]" value="${item.variant_id}">` : ''}
                <input type="hidden" name="items[${index}][quantity]" value="${item.quantity}">
            `;
        });

        cartItemsList.innerHTML = itemsHtml;
        cartHiddenInputs.innerHTML = inputsHtml;

        // Delegate remove button
        document.querySelectorAll('.btn-remove-cart-item').forEach(btn => {
            btn.addEventListener('click', function() {
                const idx = parseInt(this.dataset.index);
                cart.splice(idx, 1);
                updateCartUI();
                recalculateShippingIfReady();
            });
        });

        recalculateShippingIfReady();
    }

    // SPX Autocomplete & Dropdown
    let cityDebounce;

    if (btnClearShopCity) {
        btnClearShopCity.addEventListener('click', () => {
            cityInput.value = '';
            btnClearShopCity.classList.add('hidden');
            cityDropdown.classList.add('hidden');
            districtSelect.innerHTML = '<option value="">-- Pilih Kota Terlebih Dahulu --</option>';
            districtSelect.disabled = true;
            spxServicesContainer.innerHTML = '<div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-center text-xs text-slate-500">Pilih Kota &amp; Kecamatan untuk memuat tarif ongkir SPX.</div>';
            currentShippingCost = 0;
            updateTotals();
            cityInput.focus();
        });
    }

    cityInput.addEventListener('input', () => {
        const query = cityInput.value.trim();
        clearTimeout(cityDebounce);

        if (btnClearShopCity) {
            if (query.length > 0) {
                btnClearShopCity.classList.remove('hidden');
            } else {
                btnClearShopCity.classList.add('hidden');
            }
        }

        if (query.length < 3) {
            cityDropdown.classList.add('hidden');
            return;
        }

        cityDebounce = setTimeout(() => {
            fetch(`/api/shipping/cities?q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(res => {
                    if (res.status === 'success' && res.data.length > 0) {
                        let html = '';
                        res.data.forEach(city => {
                            html += `
                                <div class="px-3.5 py-2.5 text-xs text-slate-300 hover:bg-slate-800 hover:text-[#FF5500] cursor-pointer shop-city-item transition flex items-center gap-2 border-b border-slate-900 last:border-b-0" data-city="${city}">
                                    <span class="text-slate-500">📍</span>
                                    <span>${city}</span>
                                </div>
                            `;
                        });
                        cityDropdown.innerHTML = html;
                        cityDropdown.classList.remove('hidden');

                        document.querySelectorAll('.shop-city-item').forEach(item => {
                            item.addEventListener('click', () => {
                                const c = item.dataset.city;
                                cityInput.value = c;
                                if (btnClearShopCity) btnClearShopCity.classList.remove('hidden');
                                cityDropdown.classList.add('hidden');
                                loadDistricts(c);
                            });
                        });
                    } else {
                        cityDropdown.innerHTML = '<div class="p-3 text-xs text-slate-500 text-center">Kota tidak ditemukan.</div>';
                        cityDropdown.classList.remove('hidden');
                    }
                })
                .catch(() => {
                    cityDropdown.classList.add('hidden');
                });
        }, 250);
    });

    document.addEventListener('click', (e) => {
        if (!cityInput.contains(e.target) && !cityDropdown.contains(e.target)) {
            cityDropdown.classList.add('hidden');
        }
    });

    function loadDistricts(city) {
        districtSelect.innerHTML = '<option value="">Memuat kecamatan...</option>';
        districtSelect.disabled = true;
        spxServicesContainer.innerHTML = '<div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-center text-xs text-slate-500">Memuat rute SPX untuk kota ini...</div>';
        currentShippingCost = 0;
        updateTotals();

        fetch(`/api/shipping/districts?city=${encodeURIComponent(city)}`)
            .then(r => r.json())
            .then(res => {
                if (res.status === 'success' && res.data.length > 0) {
                    let html = '<option value="">-- Pilih Kecamatan --</option>';
                    res.data.forEach(d => {
                        html += `<option value="${d}">${d}</option>`;
                    });
                    districtSelect.innerHTML = html;
                    districtSelect.disabled = false;
                    spxServicesContainer.innerHTML = '<div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-center text-xs text-slate-500">Pilih Kecamatan untuk menghitung ongkir SPX.</div>';
                } else {
                    districtSelect.innerHTML = '<option value="">Kecamatan tidak ditemukan</option>';
                    districtSelect.disabled = true;
                    spxServicesContainer.innerHTML = '<div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-center text-xs text-rose-400">Tidak ada kecamatan terdaftar untuk kota ini.</div>';
                }
            })
            .catch(() => {
                districtSelect.innerHTML = '<option value="">Gagal memuat kecamatan</option>';
                districtSelect.disabled = true;
                spxServicesContainer.innerHTML = '<div class="p-3 rounded-xl bg-slate-900 border border-rose-900/50 text-center text-xs text-rose-400">Gagal memuat data kecamatan.</div>';
            });
    }

    districtSelect.addEventListener('change', () => {
        recalculateShippingIfReady();
    });

    function recalculateShippingIfReady() {
        const city = cityInput.value.trim();
        const district = districtSelect.value.trim();
        const totalWeight = cart.reduce((acc, c) => acc + (c.weight * c.quantity), 0);

        if (!city || !district || totalWeight <= 0) {
            currentShippingCost = 0;
            if (!city || !district) {
                spxServicesContainer.innerHTML = '<div class="p-3 rounded-xl bg-slate-900 border border-slate-800 text-center text-xs text-slate-500">Pilih Kota &amp; Kecamatan untuk memuat tarif ongkir SPX.</div>';
            }
            updateTotals();
            return;
        }

        spxServicesContainer.innerHTML = `
            <div class="p-3.5 rounded-xl bg-slate-900/80 border border-slate-800 text-center text-xs text-slate-300 flex items-center justify-center gap-2.5">
                <svg class="animate-spin h-4 w-4 text-[#FF5500]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                </svg>
                <span>Menghitung estimasi ongkir SPX (${totalWeight}g)...</span>
            </div>
        `;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

        fetch('/api/shipping/calculate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                destination_city: city,
                destination_district: district,
                city: city,
                district: district,
                weight_grams: totalWeight
            })
        })
        .then(r => r.json())
        .then(res => {
            if (res.status === 'success' && res.data && res.data.services && res.data.services.length > 0) {
                let servicesHtml = '';
                res.data.services.forEach((s, idx) => {
                    const isChecked = idx === 0 ? 'checked' : '';
                    servicesHtml += `
                        <label class="flex items-center justify-between p-3 rounded-xl border border-slate-700 bg-slate-900/90 cursor-pointer hover:border-[#FF5500] transition">
                            <div class="flex items-center gap-2.5">
                                <input type="radio" name="spx_service" value="${s.service_code}" ${isChecked} data-cost="${s.total_cost}" class="spx-radio text-[#FF5500] focus:ring-[#FF5500]">
                                <div>
                                    <span class="text-xs font-bold text-white block">${s.service_name}</span>
                                    <span class="text-[10px] text-slate-400">Estimasi tiba: ${s.etd_days} hari</span>
                                </div>
                            </div>
                            <span class="font-mono-num font-bold text-xs text-cyan-400">${formatRupiah(s.total_cost)}</span>
                        </label>
                    `;
                });

                spxServicesContainer.innerHTML = servicesHtml;
                currentShippingCost = parseFloat(res.data.services[0].total_cost);

                document.querySelectorAll('.spx-radio').forEach(r => {
                    r.addEventListener('change', () => {
                        currentShippingCost = parseFloat(r.dataset.cost);
                        updateTotals();
                    });
                });

                updateTotals();
            } else {
                spxServicesContainer.innerHTML = '<div class="p-3 rounded-xl bg-slate-900 border border-rose-900/50 text-center text-xs text-rose-400">Rute pengiriman SPX belum tersedia untuk kecamatan ini.</div>';
                currentShippingCost = 0;
                updateTotals();
            }
        })
        .catch(() => {
            spxServicesContainer.innerHTML = '<div class="p-3 rounded-xl bg-slate-900 border border-rose-900/50 text-center text-xs text-rose-400">Gagal menghitung ongkir SPX. Silakan coba lagi.</div>';
            currentShippingCost = 0;
            updateTotals();
        });
    }

    function updateTotals() {
        const subtotal = cart.reduce((acc, c) => acc + (c.price * c.quantity), 0);
        const grandTotal = subtotal + currentShippingCost;

        finalItemsSubtotal.textContent = formatRupiah(subtotal);
        finalShippingCost.textContent = formatRupiah(currentShippingCost);
        finalGrandTotal.textContent = formatRupiah(grandTotal);

        if (cart.length > 0 && currentShippingCost > 0 && districtSelect.value && cityInput.value) {
            btnSubmitOrder.disabled = false;
        } else {
            btnSubmitOrder.disabled = true;
        }
    }
});
</script>
@endsection
