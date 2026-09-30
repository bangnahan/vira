@extends('layouts.app')

@section('title', 'Rekap Order Jersey & Produksi — Admin VIRA')

@push('styles')
<style>
    @media print {
        header, footer, .no-print, #mobileMenu, #exportModal {
            display: none !important;
        }
        body {
            background: white !important;
            color: black !important;
        }
        .print-card {
            border: 1px solid #ddd !important;
            background: white !important;
            color: black !important;
            box-shadow: none !important;
        }
        .print-text-dark {
            color: black !important;
        }
        .print-table {
            border-collapse: collapse !important;
            width: 100% !important;
        }
        .print-table th, .print-table td {
            border: 1px solid #666 !important;
            padding: 6px 8px !important;
            color: black !important;
        }
    }
</style>
@endpush

@section('content')
<div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-6 sm:py-10">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 sm:mb-8 no-print">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Logistik &amp; Produksi</span>
                <span class="text-xs text-slate-400">Backoffice VIRA</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white font-athletic tracking-wide mt-1">Rekapitulasi Order Jersey Siap Produksi</h1>
            <p class="text-xs text-slate-400">Pantau akumulasi size jersey lunas (PAID) dari paket pendaftaran dan add-ons untuk vendor konveksi secara real-time.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <!-- Tombol Export CSV Vendor -->
            <a href="{{ route('admin.registrations.jersey-orders.export', request()->query()) }}" 
               class="px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 transition shadow-lg shadow-emerald-950/50 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>📥 Export SPK Vendor (CSV)</span>
            </a>

            <!-- Tombol Cetak SPK -->
            <button type="button" 
                    onclick="window.print()" 
                    class="px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-200 bg-slate-800 hover:bg-slate-700 hover:text-white transition border border-slate-700 flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>🖨️ Cetak Lembar SPK</span>
            </button>
        </div>
    </div>

    <!-- Navigation Tabs (Data Peserta vs Rekap Jersey) -->
    <div class="flex items-center gap-2 border-b border-slate-800 mb-6 sm:mb-8 no-print">
        <a href="{{ route('admin.registrations.index') }}" 
           class="px-4 py-3 rounded-t-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 text-slate-400 hover:text-white hover:bg-slate-900/60 border-b-2 border-transparent">
            <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span>Daftar Peserta &amp; Tagihan</span>
        </a>

        <a href="{{ route('admin.registrations.jersey-orders') }}" 
           class="px-4 py-3 rounded-t-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 text-white bg-slate-900 border-b-2 border-[#00E5FF] shadow-sm">
            <span class="text-base">👕</span>
            <span>Rekap Order Jersey &amp; Produksi</span>
            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Siap Cetak</span>
        </a>
    </div>

    <!-- Print Header Only Visible on Paper / PDF -->
    <div class="hidden print:block mb-6">
        <div class="border-b-2 border-black pb-3">
            <h1 class="text-xl font-bold text-black uppercase">SURAT PERINTAH KERJA (SPK) — REKAPITULASI PRODUKSI JERSEY</h1>
            <p class="text-xs text-gray-700">Platform: VIRA Virtual Sport • Tanggal Cetak: {{ date('d F Y, H:i') }} WIB • Status: {{ $paymentStatus === 'PAID' ? 'HANYA YANG LUNAS (PAID)' : 'SEMUA TRANSAKSI' }}</p>
        </div>
    </div>

    <!-- Stat Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 sm:gap-5 mb-6 sm:mb-8">
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-5 print-card">
            <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Jersey Paket Lomba</span>
            <div class="font-athletic text-2xl sm:text-3xl text-white font-mono-num print-text-dark">{{ number_format($totalPackageJerseys) }}</div>
            <span class="text-[10px] text-slate-500 mt-1 block">Dari paket pendaftaran resmi</span>
        </div>

        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-5 print-card">
            <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Jersey Tambahan Add-ons</span>
            <div class="font-athletic text-2xl sm:text-3xl text-white font-mono-num print-text-dark">{{ number_format($totalAddonJerseys) }}</div>
            <span class="text-[10px] text-slate-500 mt-1 block">Pesanan ekstra lewat Add-ons</span>
        </div>

        <div class="bg-slate-900/90 border border-emerald-500/40 rounded-2xl p-4 sm:p-5 relative overflow-hidden print-card">
            <div class="flex items-center justify-between">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-emerald-400 block mb-1">Total Siap Produksi</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse no-print"></span>
            </div>
            <div class="font-athletic text-2xl sm:text-3xl text-emerald-300 font-mono-num print-text-dark">{{ number_format($grandTotalJerseys) }} Pcs</div>
            <span class="text-[10px] text-emerald-400/80 font-bold mt-1 block">Status Lunas (PAID) Siap SPK</span>
        </div>

        <div class="bg-slate-900/90 border border-amber-500/30 rounded-2xl p-4 sm:p-5 print-card">
            <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-amber-400 block mb-1">Estimasi Pending (UNPAID)</span>
            <div class="font-athletic text-2xl sm:text-3xl text-amber-300 font-mono-num print-text-dark">{{ number_format($totalUnpaidForecast) }} Pcs</div>
            <span class="text-[10px] text-slate-500 mt-1 block">Buffer perkiraan bahan vendor</span>
        </div>
    </div>

    <!-- Filter Toolbar (Hanya di layar, tersembunyi saat dicetak) -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-6 mb-6 sm:mb-8 shadow-xl no-print">
        <form action="{{ route('admin.registrations.jersey-orders') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-4 gap-4 items-end">
            <!-- Filter Event -->
            <div class="sm:col-span-2">
                <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1.5">Pilih Event Spesifik</label>
                <select name="event_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-[#FF5500]">
                    <option value="">-- Semua Event VIRA (Akumulasi Global) --</option>
                    @foreach($events as $ev)
                        <option value="{{ $ev->id }}" {{ (string)$selectedEventId === (string)$ev->id ? 'selected' : '' }}>
                            {{ $ev->title }} ({{ $ev->event_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Pembayaran -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1.5">Status Pembayaran</label>
                <select name="payment_status" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-[#FF5500]">
                    <option value="PAID" {{ $paymentStatus === 'PAID' ? 'selected' : '' }}>Hanya Lunas (PAID) — Siap Cetak</option>
                    <option value="ALL" {{ $paymentStatus === 'ALL' ? 'selected' : '' }}>Semua Status (Lunas + Pending)</option>
                    <option value="UNPAID" {{ $paymentStatus === 'UNPAID' ? 'selected' : '' }}>Hanya Belum Bayar (UNPAID Forecast)</option>
                </select>
            </div>

            <!-- Tombol Terapkan -->
            <div>
                <button type="submit" 
                        class="w-full py-2.5 px-4 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-md shadow-orange-950/40 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                    <span>Filter Data</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Bagian 1: Matriks Jersey dari Paket Pendaftaran -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl mb-8 print-card">
        <div class="px-5 py-4 bg-slate-950/80 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-base">🎽</span>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-white print-text-dark">1. Rekapitulasi Jersey dari Paket Pendaftaran</h3>
                    <p class="text-[11px] text-slate-400">Pengelompokan otomatis berdasarkan pilihan paket (Lengan Pendek, Lengan Panjang, dll) x Ukuran Size.</p>
                </div>
            </div>
            <span class="text-xs font-mono font-bold text-[#00E5FF] px-2.5 py-1 rounded bg-cyan-500/10 border border-cyan-500/20">
                Subtotal: {{ number_format($totalPackageJerseys) }} Pcs
            </span>
        </div>

        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full min-w-[750px] text-left text-xs sm:text-sm text-slate-300 print-table">
                <thead class="bg-slate-950/90 text-[11px] uppercase tracking-wider text-slate-400 font-bold border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-4">Nama Paket Pendaftaran</th>
                        <th class="py-3.5 px-3">Event</th>
                        @foreach($standardSizes as $size)
                            <th class="py-3.5 px-2.5 text-center">{{ $size }}</th>
                        @endforeach
                        <th class="py-3.5 px-2.5 text-center">Lainnya</th>
                        <th class="py-3.5 px-4 text-right">Total Pcs</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80 font-mono-num">
                    @forelse($packageMatrix as $pkg)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-4 font-bold text-white font-sans print-text-dark">
                                {{ $pkg['package_name'] }}
                            </td>
                            <td class="py-3 px-3 text-xs text-slate-400 font-sans print-text-dark">
                                {{ $pkg['event_title'] }}
                            </td>
                            @foreach($standardSizes as $size)
                                <td class="py-3 px-2.5 text-center {{ $pkg['sizes'][$size] > 0 ? 'text-[#00E5FF] font-bold' : 'text-slate-600' }} print-text-dark">
                                    {{ $pkg['sizes'][$size] ?: '-' }}
                                </td>
                            @endforeach
                            <td class="py-3 px-2.5 text-center {{ $pkg['sizes']['Lainnya'] > 0 ? 'text-amber-400 font-bold' : 'text-slate-600' }} print-text-dark">
                                {{ $pkg['sizes']['Lainnya'] ?: '-' }}
                            </td>
                            <td class="py-3 px-4 text-right font-bold text-emerald-400 text-sm print-text-dark">
                                {{ number_format($pkg['total']) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($standardSizes) + 4 }}" class="py-8 text-center text-slate-500 font-sans">
                                Belum ada pesanan jersey dari paket pendaftaran untuk filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- Footer Baris Total -->
                <tfoot class="bg-slate-950 font-bold text-xs uppercase tracking-wider text-slate-200 border-t-2 border-slate-700">
                    <tr>
                        <td colspan="2" class="py-3.5 px-4 text-white print-text-dark font-sans">
                            TOTAL JERSEY PAKET
                        </td>
                        @foreach($standardSizes as $size)
                            <td class="py-3.5 px-2.5 text-center text-[#00E5FF] font-mono-num print-text-dark">
                                {{ number_format($packageSizeTotals[$size]) }}
                            </td>
                        @endforeach
                        <td class="py-3.5 px-2.5 text-center text-amber-400 font-mono-num print-text-dark">
                            {{ number_format($packageSizeTotals['Lainnya']) }}
                        </td>
                        <td class="py-3.5 px-4 text-right text-emerald-400 font-mono-num text-sm print-text-dark">
                            {{ number_format($totalPackageJerseys) }} Pcs
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Bagian 2: Rekapitulasi Jersey Tambahan dari Item Add-ons -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl mb-8 print-card">
        <div class="px-5 py-4 bg-slate-950/80 border-b border-slate-800 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <span class="text-base">🛍️</span>
                <div>
                    <h3 class="text-sm sm:text-base font-bold text-white print-text-dark">2. Rekapitulasi Jersey &amp; Apparel Tambahan dari Fitur Add-ons</h3>
                    <p class="text-[11px] text-slate-400">Pembelian jersey ekstra atau finisher tambahan yang dibeli peserta saat checkout.</p>
                </div>
            </div>
            <span class="text-xs font-mono font-bold text-orange-400 px-2.5 py-1 rounded bg-orange-500/10 border border-orange-500/20">
                Subtotal: {{ number_format($totalAddonJerseys) }} Pcs
            </span>
        </div>

        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left text-xs sm:text-sm text-slate-300 print-table">
                <thead class="bg-slate-950/90 text-[11px] uppercase tracking-wider text-slate-400 font-bold border-b border-slate-800">
                    <tr>
                        <th class="py-3.5 px-5">Nama Item Add-on / Merchandise</th>
                        <th class="py-3.5 px-4">Event Terkait</th>
                        <th class="py-3.5 px-4">Varian / Ukuran Size</th>
                        <th class="py-3.5 px-5 text-right">Jumlah Order (Pcs)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/80">
                    @forelse($addonRaw as $addon)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-3 px-5 font-bold text-white print-text-dark">
                                {{ $addon->item_name }}
                            </td>
                            <td class="py-3 px-4 text-xs text-slate-400 print-text-dark">
                                {{ $addon->event_title }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-slate-800 text-[#00E5FF] border border-slate-700 print-text-dark">
                                    {{ $addon->variant_name ?: 'Standar (Tanpa Varian)' }}
                                </span>
                            </td>
                            <td class="py-3 px-5 text-right font-mono-num font-bold text-emerald-400 text-sm print-text-dark">
                                {{ number_format($addon->total_qty) }} Pcs
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500">
                                Belum ada pembelian jersey/merchandise tambahan dari add-ons untuk filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <!-- Footer Baris Total Addons -->
                <tfoot class="bg-slate-950 font-bold text-xs uppercase tracking-wider text-slate-200 border-t-2 border-slate-700">
                    <tr>
                        <td colspan="3" class="py-3.5 px-5 text-white print-text-dark font-sans">
                            TOTAL MERCHANDISE ADD-ONS
                        </td>
                        <td class="py-3.5 px-5 text-right text-emerald-400 font-mono-num text-sm print-text-dark">
                            {{ number_format($totalAddonJerseys) }} Pcs
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Bagian 3: Ringkasan Final SPK Konveksi (Surat Perintah Kerja) -->
    <div class="bg-gradient-to-r from-slate-900 to-slate-950 border border-emerald-500/30 rounded-2xl sm:rounded-3xl p-6 sm:p-8 shadow-2xl print-card">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-slate-800">
            <div>
                <span class="px-2.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Ringkasan Pabrik Konveksi</span>
                <h3 class="text-xl font-extrabold text-white font-athletic tracking-wide mt-1 print-text-dark">GRAND TOTAL KEBUTUHAN PRODUKSI JERSEY</h3>
                <p class="text-xs text-slate-400">Total akumulasi bersih semua jersey (Paket Pendaftaran + Tambahan Add-ons) siap naik meja potong &amp; sablon.</p>
            </div>
            
            <div class="text-right">
                <span class="text-xs uppercase text-slate-400 block font-semibold">Total Unit Siap Produksi</span>
                <div class="font-athletic text-4xl sm:text-5xl text-emerald-400 font-mono-num print-text-dark">
                    {{ number_format($grandTotalJerseys) }} <span class="text-xl sm:text-2xl text-slate-300">PCS</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-6 text-xs">
            <div class="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 print-card">
                <span class="text-slate-400 block mb-1 font-semibold">Total dari Paket Lomba:</span>
                <span class="text-base font-bold text-white font-mono-num print-text-dark">{{ number_format($totalPackageJerseys) }} Pcs</span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 print-card">
                <span class="text-slate-400 block mb-1 font-semibold">Total dari Add-on Tambahan:</span>
                <span class="text-base font-bold text-white font-mono-num print-text-dark">{{ number_format($totalAddonJerseys) }} Pcs</span>
            </div>
            <div class="p-3.5 rounded-xl bg-slate-950/70 border border-slate-800 print-card">
                <span class="text-slate-400 block mb-1 font-semibold">Estimasi Pending (Belum Bayar):</span>
                <span class="text-base font-bold text-amber-400 font-mono-num print-text-dark">{{ number_format($totalUnpaidForecast) }} Pcs</span>
            </div>
        </div>

        <div class="mt-6 pt-5 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Data tersinkronisasi otomatis dengan status lunas payment gateway Tripay.</span>
            </div>
            <div class="no-print">
                <a href="{{ route('admin.registrations.jersey-orders.export', request()->query()) }}" class="text-emerald-400 hover:text-emerald-300 font-bold underline">
                    Unduh Rekap CSV Vendor Ini &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
