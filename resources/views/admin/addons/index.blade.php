@extends('layouts.app')

@section('title', 'Admin Panel: Pengaturan Item Add-ons & Merchandise — VIRA')

@section('content')
<div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-6 sm:py-10">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 sm:mb-8">
        <div>
            <div class="flex items-center gap-2">
                <span class="px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-orange-500/20 text-[#FF5500] border border-orange-500/30">Panel Admin</span>
                <span class="text-xs text-slate-400">Merchandise &amp; Up-selling</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white font-athletic tracking-wide mt-1">Pengaturan Item Add-ons &amp; Merchandise</h1>
            <p class="text-xs text-slate-400">Kelola katalog barang tambahan, stok merchandise, varian ukuran/warna, dan harga add-on saat peserta mendaftar.</p>
        </div>

        <div class="flex flex-wrap items-center gap-2.5">
            <a href="{{ route('admin.events.index') }}" class="px-3.5 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-slate-600 transition flex items-center gap-1.5">
                <span>🏃 Kelola Event</span>
            </a>
            <a href="{{ route('admin.registrations.index') }}" class="px-3.5 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-300 bg-slate-800 hover:bg-slate-700 border border-slate-700 hover:border-slate-600 transition flex items-center gap-1.5">
                <span>👥 Data Peserta</span>
            </a>
            <a href="{{ route('admin.addons.create') }}" class="px-4 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-lg shadow-orange-950/50 glow-orange flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Tambah Add-on</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifikasi -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-sm flex items-center justify-between shadow-lg">
            <div class="flex items-center gap-3">
                <span class="text-xl">✅</span>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-white">&times;</button>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-sm flex items-center justify-between shadow-lg">
            <div class="flex items-center gap-3">
                <span class="text-xl">⚠️</span>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-white">&times;</button>
        </div>
    @endif

    <!-- Quick Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Total Item</span>
                <span class="text-lg">🛍️</span>
            </div>
            <div class="mt-2 text-2xl sm:text-3xl font-extrabold text-white font-mono-num">{{ number_format($totalItems) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Katalog Merchandise</span>
        </div>

        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Item Aktif</span>
                <span class="text-lg">⚡</span>
            </div>
            <div class="mt-2 text-2xl sm:text-3xl font-extrabold text-emerald-400 font-mono-num">{{ number_format($activeItems) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Tampil di Registrasi</span>
        </div>

        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Global Item</span>
                <span class="text-lg">🌐</span>
            </div>
            <div class="mt-2 text-2xl sm:text-3xl font-extrabold text-[#00E5FF] font-mono-num">{{ number_format($globalItems) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Tersedia Semua Event</span>
        </div>

        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <span class="text-xs text-slate-400 font-bold uppercase tracking-wider">Stok Dasar</span>
                <span class="text-lg">📦</span>
            </div>
            <div class="mt-2 text-2xl sm:text-3xl font-extrabold text-[#FF5500] font-mono-num">{{ number_format($totalBaseStock) }}</div>
            <span class="text-[11px] text-slate-500 mt-1 block">Total Unit Tersedia</span>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 sm:p-5 mb-6 shadow-xl">
        <form action="{{ route('admin.addons.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-center">
            <!-- Search -->
            <div class="sm:col-span-5 relative">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                <input type="text" 
                       name="search" 
                       value="{{ $search }}" 
                       placeholder="Cari nama item, slug, atau deskripsi..." 
                       class="w-full bg-slate-950 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-[#FF5500] focus:ring-1 focus:ring-[#FF5500]">
            </div>

            <!-- Filter Event -->
            <div class="sm:col-span-3">
                <select name="event_id" onchange="this.form.submit()" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3 py-2.5 text-xs text-white focus:border-[#FF5500]">
                    <option value="all" {{ $eventFilter === 'all' ? 'selected' : '' }}>— Semua Event —</option>
                    <option value="global" {{ $eventFilter === 'global' ? 'selected' : '' }}>🌐 Global (Semua Event)</option>
                    @foreach($events as $ev)
                        <option value="{{ $ev->id }}" {{ (string)$eventFilter === (string)$ev->id ? 'selected' : '' }}>
                            {{ $ev->event_code }} — {{ Str::limit($ev->title, 25) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Filter Status -->
            <div class="sm:col-span-2">
                <select name="status" onchange="this.form.submit()" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3 py-2.5 text-xs text-white focus:border-[#FF5500]">
                    <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Nonaktif</option>
                </select>
            </div>

            <!-- Tombol Aksi -->
            <div class="sm:col-span-2 flex items-center gap-2">
                <button type="submit" class="w-full py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold transition flex items-center justify-center gap-1.5 border border-slate-700">
                    <span>Filter</span>
                </button>
                @if($search !== '' || $eventFilter !== 'all' || $statusFilter !== 'all')
                    <a href="{{ route('admin.addons.index') }}" class="p-2.5 rounded-xl bg-slate-800 hover:bg-rose-500/20 text-slate-400 hover:text-rose-400 border border-slate-700 transition" title="Reset Filter">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Addons Table Card -->
    <div class="bg-slate-900/90 border border-slate-800 rounded-2xl sm:rounded-3xl overflow-hidden shadow-2xl">
        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full min-w-[760px] text-left text-sm text-slate-300">
                <thead class="bg-slate-950/80 text-[11px] uppercase tracking-wider text-slate-400 font-bold border-b border-slate-800">
                    <tr>
                        <th class="py-4 px-6">Produk / Add-on</th>
                        <th class="py-4 px-4">Event Terkait</th>
                        <th class="py-4 px-4">Harga Satuan</th>
                        <th class="py-4 px-4">Berat</th>
                        <th class="py-4 px-4">Stok &amp; Varian</th>
                        <th class="py-4 px-4 text-center">Terjual</th>
                        <th class="py-4 px-4 text-center">Status</th>
                        <th class="py-4 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800/60 font-sans">
                    @forelse($addOns as $addon)
                        <tr class="hover:bg-slate-800/30 transition">
                            <!-- Produk Info -->
                            <td class="py-4 px-6">
                                <div class="flex items-center gap-3.5">
                                    <div class="w-12 h-12 rounded-xl bg-slate-950 border border-slate-800 flex-shrink-0 flex items-center justify-center overflow-hidden shadow-inner" style="width: 48px; height: 48px; min-width: 48px; min-height: 48px; max-width: 48px; max-height: 48px;">
                                        @if($addon->image_url)
                                            <img src="{{ $addon->image_url }}" alt="{{ $addon->name }}" class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover;">
                                        @else
                                            <span class="text-xl">
                                                @if(str_contains(strtolower($addon->name), 'jersey'))
                                                    🎽
                                                @elseif(str_contains(strtolower($addon->name), 'kunci') || str_contains(strtolower($addon->name), 'medali'))
                                                    🏅
                                                @else
                                                    🧢
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                    <div>
                                        <a href="{{ route('admin.addons.edit', $addon) }}" class="font-bold text-white hover:text-[#FF5500] transition">
                                            {{ $addon->name }}
                                        </a>
                                        <div class="text-[11px] font-mono text-slate-500 mt-0.5">
                                            slug: {{ $addon->slug }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Event Terkait -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if($addon->event)
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-cyan-500/10 text-cyan-400 border border-cyan-500/30 font-mono">
                                            {{ $addon->event->event_code }}
                                        </span>
                                        <span class="text-xs text-slate-300 font-medium truncate max-w-[140px]" title="{{ $addon->event->title }}">
                                            {{ $addon->event->title }}
                                        </span>
                                    </div>
                                @else
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-orange-500/10 text-[#FF5500] border border-orange-500/30">
                                        🌐 Semua Event
                                    </span>
                                @endif
                            </td>

                            <!-- Harga -->
                            <td class="py-4 px-4 whitespace-nowrap font-mono-num font-bold text-white">
                                Rp {{ number_format($addon->price, 0, ',', '.') }}
                            </td>

                            <!-- Berat -->
                            <td class="py-4 px-4 whitespace-nowrap text-xs text-slate-300 font-mono-num">
                                {{ number_format($addon->weight_grams) }} gr
                            </td>

                            <!-- Stok & Varian -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="flex flex-col gap-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-mono-num font-bold {{ $addon->total_stock <= 10 ? 'text-rose-400' : 'text-emerald-400' }} text-xs">
                                            {{ number_format($addon->total_stock) }} unit
                                        </span>
                                        @if($addon->total_stock <= 10)
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-rose-500/20 text-rose-400 border border-rose-500/30">Menipis</span>
                                        @endif
                                    </div>

                                    @if($addon->has_variants && $addon->variants->count() > 0)
                                        <div class="flex items-center gap-1">
                                            <span class="text-[10px] text-slate-400 bg-slate-800 px-1.5 py-0.5 rounded border border-slate-700">
                                                {{ $addon->variants->count() }} Varian ({{ $addon->variants->pluck('variant_name')->take(3)->implode(', ') }}{{ $addon->variants->count() > 3 ? '...' : '' }})
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-[10px] text-slate-500">Tanpa Varian</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Terjual -->
                            <td class="py-4 px-4 text-center whitespace-nowrap font-mono-num text-xs font-bold text-slate-300">
                                {{ number_format($addon->registration_add_ons_count ?? 0) }}x
                            </td>

                            <!-- Status -->
                            <td class="py-4 px-4 text-center whitespace-nowrap">
                                <form action="{{ route('admin.addons.toggle-status', $addon) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider transition cursor-pointer {{ $addon->is_active ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/20' : 'bg-slate-800 text-slate-400 border border-slate-700 hover:bg-slate-700 hover:text-slate-200' }}"
                                            title="Klik untuk {{ $addon->is_active ? 'sembunyikan dari etalase & event' : 'aktifkan kembali' }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $addon->is_active ? 'bg-emerald-400 animate-pulse' : 'bg-slate-500' }}"></span>
                                        <span>{{ $addon->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                    </button>
                                </form>
                            </td>

                            <!-- Aksi -->
                            <td class="py-4 px-6 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.addons.edit', $addon) }}" 
                                       class="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white transition text-xs font-bold flex items-center gap-1 border border-slate-700" 
                                       title="Edit Item & Varian">
                                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                        <span class="hidden md:inline">Edit</span>
                                    </a>

                                    <form action="{{ route('admin.addons.destroy', $addon) }}" 
                                          method="POST" 
                                          class="inline" 
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus item add-on \'{{ addslashes($addon->name) }}\'?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="p-2 rounded-lg bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 transition text-xs font-bold flex items-center gap-1" 
                                                title="Hapus Add-on">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-500">
                                <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-800/80 flex items-center justify-center text-3xl mb-3 border border-slate-700">
                                    🛍️
                                </div>
                                <p class="text-base font-bold text-white mb-1">Belum ada item add-on yang sesuai</p>
                                <p class="text-xs text-slate-400 max-w-sm mx-auto mb-4">Tambahkan item merchandise seperti jersey, medali ekstra, topi lari, atau aksesori lainnya untuk meningkatkan pendapatan event.</p>
                                <a href="{{ route('admin.addons.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#FF5500] hover:bg-[#FF6600] text-white text-xs font-bold transition shadow-lg shadow-orange-950/40">
                                    <span>+ Buat Item Add-on Pertama</span>
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($addOns->hasPages())
            <div class="p-4 border-t border-slate-800 bg-slate-950/60">
                {{ $addOns->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
