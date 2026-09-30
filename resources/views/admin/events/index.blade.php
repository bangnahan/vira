@extends('layouts.app')

@section('title', 'Admin Panel: Manajemen Event & Meta CAPI — VIRA')

@section('content')
<div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-6 sm:py-10">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 sm:mb-8">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-rose-500/20 text-rose-400 border border-rose-500/30">Panel Admin</span>
                <span class="text-xs text-slate-400">Backoffice VIRA</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white font-athletic tracking-wide mt-1">Daftar Event &amp; Meta Pixel CAPI</h1>
            <p class="text-xs text-slate-400">Kelola event virtual sport, integrasi Meta Conversions API (CAPI), dan desain e-BIB / E-Sertifikat.</p>
        </div>

        <div class="flex items-center gap-3">
            <a href="{{ route('admin.addons.index') }}" class="w-full sm:w-auto px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-200 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-slate-600 transition flex items-center justify-center gap-2">
                <span>🛍️ Item Add-ons</span>
            </a>
            <a href="{{ route('admin.registrations.index') }}" class="w-full sm:w-auto px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-200 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-slate-600 transition flex items-center justify-center gap-2">
                <svg class="w-4 h-4 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Data Peserta</span>
            </a>
            <a href="{{ route('admin.events.create') }}" class="w-full sm:w-auto px-5 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-lg shadow-orange-950/50 glow-orange flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Buat Event Baru</span>
            </a>
        </div>
    </div>

    <!-- Events Table Card -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full min-w-[650px] text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 text-[11px] uppercase tracking-wider text-slate-400 font-bold border-b border-slate-800">
                    <tr>
                        <th class="py-4 px-6">Event &amp; Kode</th>
                        <th class="py-4 px-4">Tipe &amp; Mode</th>
                        <th class="py-4 px-4">Periode Race</th>
                        <th class="py-4 px-4">Meta Pixel / CAPI</th>
                        <th class="py-4 px-4">Status</th>
                        <th class="py-4 px-6 text-right">Aksi &amp; Alat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans">
                    @forelse($events as $ev)
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-slate-800 flex items-center justify-center font-athletic text-lg text-white font-bold border border-slate-700">
                                        {{ substr($ev->event_code, 0, 3) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.events.edit', $ev) }}" class="font-bold text-white hover:text-[#FF5500] transition">
                                            {{ $ev->title }}
                                        </a>
                                        <div class="flex items-center gap-2 text-xs text-slate-400 mt-0.5">
                                            <span class="font-mono-num font-bold text-cyan-400">Kode: {{ $ev->event_code }}</span>
                                            <span>•</span>
                                            <span>{{ $ev->categories_count }} Kategori</span>
                                            <span>•</span>
                                            <span>{{ $ev->packages_count }} Paket</span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 px-4">
                                <div class="flex flex-col gap-1 items-start">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-200 border border-slate-700">
                                        {{ $ev->activity_type }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">
                                        {{ $ev->submission_mode === 'CUMULATIVE' ? 'Akumulasi Jarak' : 'Satu Sesi Tunggal' }}
                                    </span>
                                </div>
                            </td>
                            <td class="py-4 px-4 text-xs">
                                <span class="text-white block font-medium">{{ $ev->race_start ? $ev->race_start->format('d M Y') : '-' }}</span>
                                <span class="text-slate-500">s/d {{ $ev->race_end ? $ev->race_end->format('d M Y') : '-' }}</span>
                            </td>
                            <td class="py-4 px-4">
                                @if($ev->meta_pixel_id)
                                    <div class="flex items-center gap-1.5">
                                        @if($ev->is_meta_capi_enabled)
                                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                            <span class="text-xs font-bold text-emerald-400">CAPI AKTIF</span>
                                        @else
                                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                            <span class="text-xs font-bold text-amber-400">CAPI MATI</span>
                                        @endif
                                    </div>
                                    <span class="text-[11px] font-mono-num text-slate-400 block mt-0.5">ID: {{ $ev->meta_pixel_id }}</span>
                                @else
                                    <span class="text-xs text-slate-500 italic">Belum dikonfigurasi</span>
                                @endif
                            </td>
                            <td class="py-4 px-4">
                                @if($ev->is_active)
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-slate-800 text-slate-400 border border-slate-700">
                                        Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    <a href="{{ route('admin.registrations.index', ['event_id' => $ev->id]) }}" class="p-2 rounded-lg bg-cyan-950/60 hover:bg-cyan-900/80 text-cyan-300 border border-cyan-800/40 transition text-xs font-bold flex items-center gap-1" title="Lihat Data Peserta Event Ini">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                        <span>Peserta</span>
                                    </a>
                                    <a href="{{ route('admin.events.edit', $ev) }}" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white transition text-xs font-bold flex items-center gap-1" title="Edit Event & Meta Pixel CAPI">
                                        <svg class="w-3.5 h-3.5 text-[#FF5500]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        <span>Edit &amp; CAPI</span>
                                    </a>
                                    <a href="{{ route('admin.designer.edit', ['event' => $ev->id, 'type' => 'BIB']) }}" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-cyan-300 transition text-xs font-bold flex items-center gap-1" title="e-BIB Designer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                                        <span>e-BIB</span>
                                    </a>
                                    <a href="{{ route('admin.designer.edit', ['event' => $ev->id, 'type' => 'CERTIFICATE']) }}" class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-amber-300 transition text-xs font-bold flex items-center gap-1" title="E-Sertifikat Designer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        <span>Sertifikat</span>
                                    </a>
                                    <a href="{{ route('events.show', $ev->slug) }}" target="_blank" class="p-2 rounded-lg bg-slate-800/80 hover:bg-slate-700 text-slate-400 hover:text-white transition" title="Lihat Halaman Publik">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                    </a>
                                    <form action="{{ route('admin.events.destroy', $ev) }}" method="POST" class="inline" onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus event \'{{ addslashes($ev->title) }}\'?\n\nSeluruh kategori, paket, dan data pendaftaran terkait akan ikut terhapus secara permanen.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 rounded-lg bg-rose-950/60 hover:bg-rose-900/80 text-rose-400 hover:text-rose-200 border border-rose-800/40 transition text-xs font-bold flex items-center gap-1" title="Hapus Event">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            <span class="sr-only sm:not-sr-only">Hapus</span>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-slate-500">
                                Belum ada event terdaftar. Klik tombol <strong>Buat Event Baru</strong> untuk memulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
