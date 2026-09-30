@extends('layouts.app')

@section('title', 'Data Peserta & Status Pembayaran — Admin VIRA')

@section('content')
<div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-6 sm:py-10">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 sm:mb-8">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-cyan-500/20 text-[#00E5FF] border border-cyan-500/30">Monitoring Peserta</span>
                <span class="text-xs text-slate-400">Backoffice VIRA</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white font-athletic tracking-wide mt-1">Data Peserta &amp; Status Pembayaran</h1>
            <p class="text-xs text-slate-400">Pantau transaksi pembayaran Tripay, penomoran e-BIB, alamat pengiriman SPX, dan progres capaian lari.</p>
        </div>

        <div class="flex items-center gap-3">
            <!-- Tombol Buka Modal Export CSV -->
            <button type="button" 
                    onclick="openExportModal()"
                    class="w-full sm:w-auto px-5 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 transition shadow-lg shadow-emerald-950/50 flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>📥 Export Data (CSV / Excel)</span>
            </button>
            <a href="{{ route('admin.addons.index') }}" 
               class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-300 bg-slate-800 hover:bg-slate-700 hover:text-white transition border border-slate-700">
                Item Add-ons
            </a>
            <a href="{{ route('admin.events.index') }}" 
               class="px-4 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-300 bg-slate-800 hover:bg-slate-700 hover:text-white transition border border-slate-700">
                Kelola Event
            </a>
        </div>
    </div>

    <!-- Navigation Tabs (Data Peserta vs Rekap Jersey) -->
    <div class="flex items-center gap-2 border-b border-slate-800 mb-6 sm:mb-8">
        <a href="{{ route('admin.registrations.index') }}" 
           class="px-4 py-3 rounded-t-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 text-white bg-slate-900 border-b-2 border-[#00E5FF] shadow-sm">
            <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            <span>Daftar Peserta &amp; Tagihan</span>
        </a>

        <a href="{{ route('admin.registrations.jersey-orders') }}" 
           class="px-4 py-3 rounded-t-xl text-xs sm:text-sm font-bold transition flex items-center gap-2 text-slate-400 hover:text-white hover:bg-slate-900/60 border-b-2 border-transparent">
            <span class="text-base">👕</span>
            <span>Rekap Order Jersey &amp; Produksi</span>
            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30">Siap Cetak</span>
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-950/80 border border-emerald-500/50 text-emerald-200 text-xs flex items-center gap-3 shadow-xl">
            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('info'))
        <div class="mb-6 p-4 rounded-2xl bg-cyan-950/80 border border-cyan-500/50 text-cyan-200 text-xs flex items-center gap-3 shadow-xl">
            <svg class="w-5 h-5 text-cyan-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span class="font-medium">{{ session('info') }}</span>
        </div>
    @endif

    <!-- Stat Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3.5 sm:gap-5 mb-6 sm:mb-8">
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-5">
            <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-slate-400 block mb-1">Total Pendaftar</span>
            <div class="font-athletic text-2xl sm:text-3xl text-white font-mono-num">{{ number_format($totalRegistrations) }}</div>
            <span class="text-[10px] text-slate-500 mt-1 block">Seluruh data pendaftaran</span>
        </div>

        <div class="bg-slate-900/90 border border-emerald-500/30 rounded-2xl p-4 sm:p-5 relative overflow-hidden">
            <div class="flex items-center justify-between">
                <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-emerald-400 block mb-1">Sudah Bayar (PAID)</span>
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
            </div>
            <div class="font-athletic text-2xl sm:text-3xl text-emerald-300 font-mono-num">{{ number_format($totalPaid) }}</div>
            <span class="text-[11px] font-mono-num text-emerald-400/80 font-bold mt-1 block">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</span>
        </div>

        <div class="bg-slate-900/90 border border-amber-500/30 rounded-2xl p-4 sm:p-5">
            <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-amber-400 block mb-1">Menunggu Bayar</span>
            <div class="font-athletic text-2xl sm:text-3xl text-amber-300 font-mono-num">{{ number_format($totalUnpaid) }}</div>
            <span class="text-[10px] text-slate-500 mt-1 block">Pending / Dalam batas waktu</span>
        </div>

        <div class="bg-slate-900/90 border border-cyan-500/30 rounded-2xl p-4 sm:p-5">
            <span class="text-[10px] sm:text-[11px] font-bold uppercase tracking-wider text-[#00E5FF] block mb-1">Finisher Resmi</span>
            <div class="font-athletic text-2xl sm:text-3xl text-cyan-300 font-mono-num">{{ number_format($totalFinished) }}</div>
            <span class="text-[10px] text-slate-500 mt-1 block">Target jarak 100% tuntas</span>
        </div>
    </div>

    <!-- Filter & Search Toolbar Card -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-6 mb-6 shadow-xl">
        <form action="{{ route('admin.registrations.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 items-end">
            <!-- Search Input -->
            <div class="lg:col-span-2">
                <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Cari Peserta / BIB / Invoice</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Ketik Nama, Email, No. WA, BIB, atau Ref..."
                           class="w-full bg-slate-950 border border-slate-700 rounded-xl pl-9 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-[#FF5500]">
                    <svg class="w-4 h-4 text-slate-500 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Filter Event -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Pilih Event</label>
                <select name="event_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:border-[#FF5500]">
                    <option value="">-- Semua Event --</option>
                    @foreach($events as $ev)
                        <option value="{{ $ev->id }}" {{ request('event_id') == $ev->id ? 'selected' : '' }}>
                            {{ $ev->title }} ({{ $ev->event_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status Pembayaran -->
            <div>
                <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Status Pembayaran</label>
                <select name="payment_status" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3 py-2.5 text-xs text-white focus:border-[#FF5500]">
                    <option value="ALL">Semua Status</option>
                    <option value="PAID" {{ request('payment_status') === 'PAID' ? 'selected' : '' }}>PAID (Lunas)</option>
                    <option value="UNPAID" {{ request('payment_status') === 'UNPAID' ? 'selected' : '' }}>UNPAID (Pending)</option>
                    <option value="EXPIRED" {{ request('payment_status') === 'EXPIRED' ? 'selected' : '' }}>EXPIRED (Kedaluwarsa)</option>
                    <option value="FAILED" {{ request('payment_status') === 'FAILED' ? 'selected' : '' }}>FAILED (Gagal)</option>
                </select>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2">
                <button type="submit" class="w-full px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-[#FF5500] hover:bg-[#FF6600] transition shadow">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'event_id', 'payment_status', 'finisher_status']))
                    <a href="{{ route('admin.registrations.index') }}" class="px-3 py-2.5 rounded-xl text-xs font-bold text-slate-400 hover:text-white bg-slate-800 transition" title="Reset Filter">
                        ✕
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Data Table Card -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full min-w-[980px] text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 text-[11px] uppercase tracking-wider text-slate-400 font-bold border-b border-slate-800">
                    <tr>
                        <th class="py-4 px-5">Peserta</th>
                        <th class="py-4 px-4">Event &amp; Kategori</th>
                        <th class="py-4 px-4">Paket &amp; Pengiriman</th>
                        <th class="py-4 px-4">Tagihan &amp; Pembayaran</th>
                        <th class="py-4 px-4">Progres Lari</th>
                        <th class="py-4 px-5 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans text-xs">
                    @forelse($registrations as $reg)
                        @php
                            $p = $reg->participant;
                            $pay = $reg->payment;
                            $ship = $reg->shippingAddress;
                            $isPaid = $reg->payment_status === 'PAID';
                        @endphp
                        <tr class="hover:bg-slate-800/30 transition">
                            <!-- Kolom Peserta -->
                            <td class="py-4 px-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl {{ $isPaid ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : 'bg-slate-800 text-slate-400 border border-slate-700' }} flex items-center justify-center font-bold text-xs uppercase flex-shrink-0">
                                        {{ substr($p->full_name ?? 'P', 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-white text-sm hover:text-[#00E5FF] transition">
                                            {{ $p->full_name ?? '-' }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5">
                                            {{ $p->email ?? '-' }}
                                        </div>
                                        <div class="text-[11px] font-mono-num text-slate-500 mt-0.5">
                                            📱 {{ $p->phone_number ?? '-' }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Kolom Event & Kategori -->
                            <td class="py-4 px-4">
                                @if($reg->event)
                                    <div class="font-bold text-white text-xs">
                                        {{ $reg->event->title }}
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-1">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-cyan-950 text-cyan-300 border border-cyan-800">
                                            {{ $reg->category?->name ?? '-' }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 font-mono-num">
                                            ({{ (float) ($reg->category?->target_distance_km ?? 0) }} KM)
                                        </span>
                                    </div>
                                    <div class="mt-1.5">
                                        @if($reg->bib_number)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded font-mono-num text-[11px] font-bold bg-slate-950 text-[#FF5500] border border-orange-500/30">
                                                BIB: {{ $reg->bib_number }}
                                            </span>
                                        @else
                                            <span class="text-[10px] text-slate-500 italic">Belum terbit</span>
                                        @endif
                                    </div>
                                @else
                                    <div class="font-bold text-[#FF5500] text-xs flex items-center gap-1">
                                        <span>🛍️</span>
                                        <span>Official Shop</span>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-orange-950 text-orange-300 border border-orange-800/80 inline-block mt-1">
                                        Merchandise Mandiri
                                    </span>
                                @endif
                            </td>

                            <!-- Kolom Paket & Pengiriman -->
                            <td class="py-4 px-4">
                                <div class="font-medium text-slate-200">
                                    {{ $reg->package?->name ?? '-' }}
                                </div>
                                @if($ship?->jersey_size)
                                    <div class="text-[11px] text-purple-300 mt-0.5">
                                        Ukuran Jersey: <strong>{{ $ship->jersey_size }}</strong>
                                    </div>
                                @endif
                                @if($ship?->district || $ship?->destination_district)
                                    @php
                                        $districtName = $ship->district ?? $ship->destination_district;
                                        $cityName = $ship->city ?? $ship->destination_city;
                                    @endphp
                                    <div class="text-[10px] text-slate-400 mt-1 max-w-[200px] truncate" title="{{ $districtName }}, {{ $cityName }}">
                                        📦 SPX: {{ $districtName }}, {{ $cityName }}
                                    </div>
                                @endif
                                @if($reg->registrationAddOns->count() > 0)
                                    <div class="text-[10px] text-emerald-400 mt-1">
                                        +{{ $reg->registrationAddOns->count() }} Add-on
                                    </div>
                                @endif
                            </td>

                            <!-- Kolom Tagihan & Pembayaran -->
                            <td class="py-4 px-4">
                                <div class="font-mono-num font-bold text-sm text-white">
                                    Rp {{ number_format($pay?->total_amount ?? 0, 0, ',', '.') }}
                                </div>
                                <div class="mt-1">
                                    @if($reg->payment_status === 'PAID')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                            PAID (Lunas)
                                        </span>
                                    @elseif($reg->payment_status === 'UNPAID')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                            UNPAID (Pending)
                                        </span>
                                    @elseif($reg->payment_status === 'EXPIRED')
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-rose-500/20 text-rose-300 border border-rose-500/30">
                                            EXPIRED
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700">
                                            {{ $reg->payment_status }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-slate-500 font-mono-num mt-1">
                                    {{ $pay?->payment_method ?? 'TRIPAY' }} • {{ $pay?->merchant_ref ?? '-' }}
                                </div>
                            </td>

                            <!-- Kolom Progres Lari & Finisher -->
                            <td class="py-4 px-4">
                                @php
                                    $targetKm = (float) ($reg->category?->target_distance_km ?? 1);
                                    $currentKm = (float) $reg->total_distance_km;
                                    $percent = $targetKm > 0 ? min(100, round(($currentKm / $targetKm) * 100)) : 0;
                                @endphp
                                <div class="flex items-center justify-between text-[11px] mb-1 font-mono-num">
                                    <span class="text-white font-bold">{{ number_format($currentKm, 1) }} KM</span>
                                    <span class="{{ $percent >= 100 ? 'text-emerald-400 font-bold' : 'text-slate-400' }}">{{ $percent }}%</span>
                                </div>
                                <div class="w-28 bg-slate-950 rounded-full h-1.5 overflow-hidden border border-slate-800">
                                    <div class="h-full rounded-full {{ $percent >= 100 ? 'bg-emerald-400' : 'bg-gradient-to-r from-orange-500 to-cyan-400' }}" style="width: {{ $percent }}%"></div>
                                </div>
                                <div class="mt-1">
                                    @if($reg->finisher_status === 'FINISHED')
                                        <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider">🏆 FINISHER</span>
                                    @else
                                        <span class="text-[10px] text-slate-500">In Progress</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Kolom Aksi -->
                            <td class="py-4 px-5 text-right">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    @if($pay?->merchant_ref)
                                        <a href="{{ route('payment.show', $pay->merchant_ref) }}" 
                                           target="_blank" 
                                           class="p-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition text-xs" 
                                           title="Lihat Invoice Tagihan">
                                            💳 Invoice
                                        </a>
                                    @endif
                                    @if(! $isPaid)
                                        <form action="{{ route('admin.registrations.mark-as-paid', $reg) }}" method="POST" class="inline" onsubmit="return confirm('Konfirmasi pelunasan manual untuk {{ addslashes($p?->full_name ?? 'peserta ini') }}?\n\nStatus akan diubah menjadi PAID, nomor e-BIB resmi akan diterbitkan, dan email konfirmasi akan dikirimkan.');">
                                            @csrf
                                            <button type="submit" 
                                                    class="p-1.5 rounded-lg bg-emerald-950/80 hover:bg-emerald-900 text-emerald-400 hover:text-emerald-300 border border-emerald-500/40 transition text-xs font-bold flex items-center gap-1 shadow" 
                                                    title="Konfirmasi Lunas Manual (Terbitkan e-BIB)">
                                                <span>✅ Tandai Lunas</span>
                                            </button>
                                        </form>
                                    @endif
                                    @if($isPaid && $reg->bib_number)
                                        <a href="{{ route('participant.download.bib', $reg->access_token) }}" 
                                           target="_blank" 
                                           class="p-1.5 rounded-lg bg-orange-950/60 hover:bg-orange-900/60 text-[#FF5500] hover:text-orange-400 border border-orange-500/30 transition text-xs font-bold" 
                                           title="Unduh e-BIB Digital">
                                            🎽 e-BIB
                                        </a>
                                    @endif
                                    @if($reg->finisher_status === 'FINISHED')
                                        <a href="{{ route('participant.download.certificate', $reg->access_token) }}" 
                                           target="_blank" 
                                           class="p-1.5 rounded-lg bg-cyan-950/60 hover:bg-cyan-900/60 text-[#00E5FF] hover:text-cyan-300 border border-cyan-500/30 transition text-xs font-bold" 
                                           title="Unduh E-Sertifikat Finisher">
                                            🎓 Sertifikat
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500">
                                Tidak ada data pendaftar yang cocok dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($registrations->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/60">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal Export Data Peserta (CSV / Excel) -->
<div id="exportModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <!-- Backdrop Blur -->
    <div class="fixed inset-0 bg-black/80 backdrop-blur-sm transition-opacity" onclick="closeExportModal()"></div>

    <div class="min-h-screen px-4 text-center flex items-center justify-center p-0">
        <div class="inline-block w-full max-w-lg p-6 sm:p-8 my-8 overflow-hidden text-left align-middle transition-all transform bg-slate-900 border border-slate-800 rounded-3xl shadow-2xl relative z-10">
            <!-- Modal Header -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-800 mb-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-lg border border-emerald-500/30">
                        📥
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-white leading-tight">Export Data Peserta</h3>
                        <p class="text-xs text-slate-400">Unduh arsip spreadsheet CSV / Excel sesuai kebutuhan Anda.</p>
                    </div>
                </div>
                <button type="button" onclick="closeExportModal()" class="text-slate-500 hover:text-white transition p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('admin.registrations.export') }}" method="GET" class="space-y-4">
                <!-- Pilihan Event -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                        Pilih Target Event
                    </label>
                    <select name="event_id" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-[#FF5500]">
                        <option value="">-- Semua Event VIRA (Seluruh Database) --</option>
                        @foreach($events as $ev)
                            <option value="{{ $ev->id }}" {{ request('event_id') == $ev->id ? 'selected' : '' }}>
                                {{ $ev->title }} ({{ $ev->event_code }})
                            </option>
                        @endforeach
                    </select>
                    <span class="text-[10px] text-slate-500 mt-1 block">Pilih salah satu event untuk mengekspor data event tersebut saja.</span>
                </div>

                <!-- Pilihan Status Pembayaran -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-1.5">
                        Filter Status Pembayaran
                    </label>
                    <select name="payment_status" class="w-full bg-slate-950 border border-slate-700 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-[#FF5500]">
                        <option value="PAID" selected>Hanya yang Lunas (PAID) — Disarankan untuk Logistik / Finisher</option>
                        <option value="ALL">Semua Status (PAID, UNPAID, EXPIRED)</option>
                        <option value="UNPAID">Hanya yang Belum Bayar (UNPAID) — Untuk Follow-up</option>
                    </select>
                </div>

                <!-- Pilihan Format Preset Kolom -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-300 mb-2">
                        Pilihan Preset Format Data
                    </label>
                    <div class="space-y-2.5">
                        <label class="flex items-start gap-3 p-3 rounded-xl bg-slate-950/70 border border-slate-800 hover:border-slate-700 cursor-pointer transition select-none">
                            <input type="radio" name="preset" value="logistics" checked class="mt-0.5 text-emerald-500 focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-bold text-white block">📦 Khusus Logistik &amp; Pengiriman SPX Express</span>
                                <span class="text-[11px] text-slate-400 block mt-0.5">Nama Penerima, No. WA, Paket, Ukuran Jersey, Add-on, Ekspedisi, dan Alamat Lengkap untuk cetak resi.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-xl bg-slate-950/70 border border-slate-800 hover:border-slate-700 cursor-pointer transition select-none">
                            <input type="radio" name="preset" value="full" class="mt-0.5 text-emerald-500 focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-bold text-white block">📑 Master Data Lengkap (Semua 39 Kolom)</span>
                                <span class="text-[11px] text-slate-400 block mt-0.5">Seluruh informasi: biodata, e-BIB, rincian biaya tiket, ongkir SPX, detail alamat, dan status finisher.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-3 p-3 rounded-xl bg-slate-950/70 border border-slate-800 hover:border-slate-700 cursor-pointer transition select-none">
                            <input type="radio" name="preset" value="finance" class="mt-0.5 text-emerald-500 focus:ring-emerald-500">
                            <div>
                                <span class="text-xs font-bold text-white block">💰 Rekap Keuangan &amp; Transaksi Tripay</span>
                                <span class="text-[11px] text-slate-400 block mt-0.5">Hanya invoice merchant ref, metode bayar, biaya paket, ongkir, admin fee, total tagihan, dan waktu lunas.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Footer Buttons -->
                <div class="pt-4 border-t border-slate-800 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeExportModal()" class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 transition">
                        Batal
                    </button>
                    <button type="submit" onclick="setTimeout(closeExportModal, 1000)" class="px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 transition shadow-lg shadow-emerald-950/50 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Unduh File CSV</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function openExportModal() {
        const modal = document.getElementById('exportModal');
        if (modal) {
            modal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeExportModal() {
        const modal = document.getElementById('exportModal');
        if (modal) {
            modal.classList.add('hidden');
            document.body.style.overflow = '';
        }
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeExportModal();
        }
    });
</script>
@endpush
@endsection
