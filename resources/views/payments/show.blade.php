@extends('layouts.app')

@section('title', 'Tagihan Pembayaran ' . $payment->merchant_ref . ' — VIRA')

@section('content')
<div class="max-w-3xl mx-auto px-3.5 sm:px-6 lg:px-8 py-8 sm:py-12">
    <!-- Payment Status Badge Card -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl sm:rounded-3xl p-4 sm:p-8 shadow-2xl mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-5 sm:pb-6 border-b border-slate-800">
            <div>
                <span class="text-xs uppercase font-bold tracking-wider text-slate-400">Kode Tagihan Pembayaran</span>
                <h1 class="text-xl sm:text-2xl font-extrabold text-white font-mono-num mt-0.5">{{ $payment->merchant_ref }}</h1>
            </div>

            <div>
                @if($payment->isPaid())
                    <span class="px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs font-extrabold tracking-wider uppercase bg-emerald-500/20 text-emerald-300 border border-emerald-500/40 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></span>
                        <span>PEMBAYARAN LUNAS (PAID)</span>
                    </span>
                @else
                    <span class="px-3.5 py-1.5 sm:px-4 sm:py-2 rounded-xl text-xs font-extrabold tracking-wider uppercase bg-amber-500/20 text-amber-300 border border-amber-500/40 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                        <span>MENUNGGU PEMBAYARAN</span>
                    </span>
                @endif
            </div>
        </div>

        <!-- Success Notification -->
        @if($payment->isPaid())
            @if($payment->registration->event_id && $payment->registration->event && $payment->registration->bib_number)
                <!-- Event Registration e-BIB Success -->
                <div class="mt-6 p-4 sm:p-6 rounded-2xl bg-gradient-to-r from-emerald-950/80 via-slate-900 to-slate-900 border border-emerald-500/40 text-center">
                    <span class="text-xs uppercase tracking-widest font-extrabold text-emerald-400 block mb-1">Nomor e-BIB Resmi Anda:</span>
                    <div class="font-athletic text-4xl sm:text-6xl text-white tracking-widest text-[#FF5500]">
                        {{ $payment->registration->bib_number }}
                    </div>
                    <p class="text-xs text-slate-300 mt-2">
                        Gunakan nomor e-BIB ini untuk mencatat hasil lari di portal universal kapan saja.
                    </p>

                    <div class="mt-5 flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3">
                        <a href="{{ route('participant.download.bib', $payment->registration->bib_number) }}" class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-[#FF5500] hover:bg-[#FF6600] transition shadow-lg shadow-orange-950/50 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Unduh Kartu e-BIB (.PNG)</span>
                        </a>
                        <a href="{{ route('submit.index', ['bib' => $payment->registration->bib_number]) }}" class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-[#00E5FF] to-[#0099FF] hover:from-[#00B4D8] hover:to-[#0077B6] transition shadow-lg shadow-cyan-950/50 flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Buka Portal Submit Lari</span>
                        </a>
                    </div>
                </div>
            @else
                <!-- Standalone Merchandise Success -->
                <div class="mt-6 p-6 rounded-2xl bg-gradient-to-r from-emerald-950/80 via-slate-900 to-slate-900 border border-emerald-500/40 text-center">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center text-2xl mx-auto mb-3">
                        ✓
                    </div>
                    <span class="text-xs uppercase tracking-widest font-extrabold text-emerald-400 block mb-1">Pembayaran Terverifikasi</span>
                    <h2 class="text-lg sm:text-xl font-black text-white mb-2">Pesanan Merchandise Anda Sedang Diproses!</h2>
                    <p class="text-xs text-slate-300 max-w-md mx-auto mb-5 leading-relaxed">
                        Terima kasih! Pembayaran Anda telah kami terima. Paket pesanan merchandise saat ini sedang disiapkan oleh tim logistik kami untuk pengiriman via <strong>SPX Express</strong>.
                    </p>
                    <div class="flex items-center justify-center gap-3">
                        <a href="{{ route('shop.index') }}" class="px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-lg shadow-orange-950/50 flex items-center gap-2">
                            <span>🛍️ Belanja Merchandise Lainnya &rarr;</span>
                        </a>
                    </div>
                </div>
            @endif
        @else
            <!-- QRIS / Payment Box -->
            <div class="mt-6 p-4 sm:p-6 rounded-2xl bg-slate-950 border border-slate-800 text-center">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Total Tagihan Pembayaran</div>
                <div class="text-3xl sm:text-4xl font-extrabold font-mono-num text-white mb-4 text-[#FF5500]">
                    Rp {{ number_format($payment->total_amount, 0, ',', '.') }}
                </div>

                @if($payment->qr_code_url)
                    <!-- QRIS Live dari Tripay -->
                    <div class="max-w-[240px] sm:max-w-[280px] mx-auto bg-white p-3 sm:p-4 rounded-2xl shadow-xl flex flex-col items-center justify-center mb-4">
                        <img src="{{ $payment->qr_code_url }}" alt="QRIS Tripay" class="w-full h-auto rounded-lg">
                        <span class="text-[9px] sm:text-[10px] text-slate-600 font-bold uppercase tracking-wider mt-2">Scan dengan BCA, Mandiri, BRI, GoPay, OVO, Dana</span>
                    </div>
                @elseif($payment->pay_code)
                    <!-- Virtual Account / Retail Code with One-Tap Copy -->
                    <div class="max-w-md mx-auto p-4 rounded-xl bg-slate-900 border border-slate-800 mb-4 text-center">
                        <span class="text-xs text-slate-400 block font-bold uppercase">Kode Pembayaran / Virtual Account</span>
                        <div class="text-xl sm:text-2xl font-mono-num font-bold text-white tracking-widest my-2 select-all">{{ $payment->pay_code }}</div>
                        <button type="button" 
                                onclick="navigator.clipboard.writeText('{{ $payment->pay_code }}'); this.textContent = '✓ Tersalin!'; setTimeout(() => this.textContent = 'Salin Kode Pembayaran', 2000)" 
                                class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-xs font-bold text-cyan-300 border border-cyan-800/40 transition">
                            Salin Kode Pembayaran
                        </button>
                        <span class="text-[11px] text-slate-500 block mt-2">Gunakan kode di atas pada menu transfer atau ATM</span>
                    </div>
                @else
                    <!-- QRIS Mockup / Fallback -->
                    <div class="w-48 h-48 sm:w-56 sm:h-56 mx-auto bg-white p-3 rounded-2xl shadow-xl flex flex-col items-center justify-center mb-4">
                        <div class="w-full h-full bg-slate-100 border-2 border-dashed border-slate-300 rounded-xl flex flex-col items-center justify-center p-2 text-slate-800">
                            <span class="text-3xl mb-1">📱</span>
                            <span class="font-extrabold text-xs">QRIS TRIPAY READY</span>
                            <span class="text-[9px] text-slate-500 text-center mt-1">Scan via BCA Mobile, GoPay, OVO, Dana, ShopeePay</span>
                        </div>
                    </div>
                @endif

                @if($payment->checkout_url)
                    <div class="mt-4 mb-4">
                        <a href="{{ $payment->checkout_url }}" target="_blank" rel="noopener noreferrer" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-lg shadow-orange-950/50 glow-orange">
                            <span>Buka Halaman Checkout Tripay</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>
                    </div>
                @endif

                <p class="text-xs text-slate-400">
                    Batas waktu pembayaran: <strong class="text-amber-300">{{ $payment->expired_at ? $payment->expired_at->format('d M Y, H:i') : '24 Jam' }} WIB</strong>
                </p>

                <!-- Simulasi Sandbox Payment Button -->
                <form action="{{ route('payment.simulate', $payment->merchant_ref) }}" method="POST" class="mt-6 pt-6 border-t border-slate-800/80">
                    @csrf
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-slate-800 hover:bg-slate-700 border border-slate-700 transition flex items-center justify-center gap-2 mx-auto">
                        <span>⚡ Simulasikan Pembayaran Sukses (Mode Sandbox)</span>
                    </button>
                    <p class="text-[10px] text-slate-400 mt-2">
                        @if($payment->registration->event_id)
                            Tombol di atas memicu konfirmasi pembayaran &amp; penerbitan e-BIB serta kirim email Mailketing secara instan.
                        @else
                            Tombol di atas memicu konfirmasi pembayaran lunas untuk pesanan merchandise ini secara instan.
                        @endif
                    </p>
                </form>
            </div>
        @endif


        <!-- Rincian Pesanan -->
        <div class="mt-8 pt-6 border-t border-slate-800">
            <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-4">
                {{ $payment->registration->event ? 'Rincian Event & Peserta' : 'Rincian Pesanan Merchandise & Pembeli' }}
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80">
                    @if($payment->registration->event)
                        <span class="text-slate-400 block font-bold mb-1">Event:</span>
                        <span class="text-white font-semibold text-sm">{{ $payment->registration->event->title }}</span>
                        @if($payment->registration->category)
                            <span class="text-slate-400 block mt-1">Kategori: {{ $payment->registration->category->name }} ({{ number_format($payment->registration->category->target_distance_km, 1) }} KM)</span>
                        @endif
                        @if($payment->registration->package)
                            <span class="text-slate-400 block">Paket: {{ $payment->registration->package->name }}</span>
                        @endif
                    @else
                        <span class="text-[#FF5500] block font-bold mb-1">🛍️ Official Store:</span>
                        <span class="text-white font-semibold text-sm">Pembelian Merchandise VIRA</span>
                        <span class="text-slate-400 block mt-1">Status: Pesanan Langsung (Tanpa Tiket Event)</span>
                    @endif
                </div>

                <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80">
                    <span class="text-slate-400 block font-bold mb-1">Data Pemesan:</span>
                    <span class="text-white font-semibold text-sm">{{ $payment->registration->participant->full_name }}</span>
                    <span class="text-slate-400 block mt-1">Email: {{ $payment->registration->participant->email }}</span>
                    <span class="text-slate-400 block">WhatsApp: {{ $payment->registration->participant->phone_number }}</span>
                </div>
            </div>

            <!-- Add-ons jika ada -->
            @if($payment->registration->registrationAddOns->count() > 0)
                <div class="mt-4 p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 text-xs">
                    <span class="text-slate-400 block font-bold mb-2">Merchandise Dipesan:</span>
                    <ul class="space-y-1 text-slate-300">
                        @foreach($payment->registration->registrationAddOns as $item)
                            <li class="flex justify-between">
                                <span>{{ $item->quantity }}x {{ $item->addOn->name }} {{ $item->variant ? '(' . $item->variant->variant_name . ')' : '' }}</span>
                                <span class="font-mono-num font-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Pengiriman SPX jika ada -->
            @if($payment->registration->shippingAddress)
                <div class="mt-4 p-4 rounded-xl bg-slate-950/60 border border-slate-800/80 text-xs">
                    <span class="text-slate-400 block font-bold mb-1">Pengiriman Fisik (SPX Express):</span>
                    <div class="text-slate-300">
                        <span class="font-semibold">{{ $payment->registration->shippingAddress->recipient_name }}</span> ({{ $payment->registration->shippingAddress->recipient_phone }})<br>
                        {{ $payment->registration->shippingAddress->address_detail }}, {{ $payment->registration->shippingAddress->district }}, {{ $payment->registration->shippingAddress->city }}, {{ $payment->registration->shippingAddress->province }} {{ $payment->registration->shippingAddress->postal_code }}
                    </div>
                    <div class="mt-2 text-cyan-400 font-semibold flex items-center justify-between">
                        <span>Layanan: {{ $payment->registration->shippingAddress->courier_name }} ({{ $payment->registration->shippingAddress->total_weight_grams }}g)</span>
                        <span class="font-mono-num font-bold">Rp {{ number_format($payment->shipping_cost, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@if($payment->registration->event && $payment->registration->event->meta_pixel_id)
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
fbq('init', '{{ $payment->registration->event->meta_pixel_id }}');
fbq('track', 'PageView');

@if($payment->isPaid())
// Event Purchase (Hanya saat status pembayaran PAID / Terbayar)
fbq('track', 'Purchase', {
    content_name: '{{ addslashes($payment->registration->event->title) }} - {{ addslashes($payment->registration->category->name) }}',
    content_type: 'product',
    currency: 'IDR',
    value: {{ (float) $payment->total_amount }},
    order_id: '{{ $payment->merchant_ref }}',
    num_items: 1
}, { eventID: '{{ $payment->merchant_ref }}' });
@else
// Event AddPaymentInfo (Saat tagihan/QRIS/VA pembayaran terbuka)
fbq('track', 'AddPaymentInfo', {
    content_name: '{{ addslashes($payment->registration->event->title) }} - {{ addslashes($payment->registration->category->name) }}',
    content_type: 'product',
    currency: 'IDR',
    value: {{ (float) $payment->total_amount }},
    order_id: '{{ $payment->merchant_ref }}'
});
@endif
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id={{ $payment->registration->event->meta_pixel_id }}&ev=PageView&noscript=1"
/></noscript>
@endpush
@endif

