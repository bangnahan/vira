@extends('layouts.app')

@section('title', 'Admin Panel: Edit Item Add-on — ' . $addon->name . ' — VIRA')

@section('content')
<div class="max-w-4xl mx-auto px-3.5 sm:px-6 lg:px-8 py-6 sm:py-10">
    <!-- Breadcrumb & Back -->
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-2 text-xs text-slate-400">
            <a href="{{ route('admin.addons.index') }}" class="hover:text-white transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                <span>Kembali ke Katalog Add-ons</span>
            </a>
            <span>/</span>
            <span class="text-[#FF5500] font-semibold">Edit Add-on</span>
        </div>

        <form action="{{ route('admin.addons.destroy', $addon) }}" 
              method="POST" 
              onsubmit="return confirm('PERINGATAN: Apakah Anda yakin ingin menghapus item add-on \'{{ addslashes($addon->name) }}\'?');">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="px-3 py-1.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 border border-rose-500/30 text-xs font-bold transition flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                <span>Hapus Item</span>
            </button>
        </form>
    </div>

    <!-- Header Card -->
    <div class="mb-8">
        <div class="flex items-center gap-2">
            <span class="px-2.5 py-0.5 rounded text-[11px] font-bold uppercase tracking-wider bg-orange-500/20 text-[#FF5500] border border-orange-500/30">Merchandise Setup</span>
            <span class="text-xs text-slate-400">Backoffice VIRA</span>
        </div>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white font-athletic tracking-wide mt-1">Edit Item: {{ $addon->name }}</h1>
        <p class="text-xs text-slate-400">Perbarui spesifikasi produk, harga, bobot pengiriman SPX, stok, atau varian ukuran/warna.</p>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs shadow-lg">
            <div class="font-bold mb-1 text-sm flex items-center gap-2">
                <span>⚠️</span> Terjadi kendala validasi data:
            </div>
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.addons.update', $addon) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- 1. Informasi Utama Produk -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl sm:rounded-3xl p-5 sm:p-7 shadow-xl">
            <h2 class="text-base sm:text-lg font-bold text-white mb-1 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-[#FF5500]/20 text-[#FF5500] flex items-center justify-center text-xs font-mono font-bold">1</span>
                <span>Informasi Dasar Add-on</span>
            </h2>
            <p class="text-xs text-slate-400 mb-6">Tentukan nama barang, event terkait, dan deskripsi produk.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <!-- Nama Item -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                        Nama Item Add-on <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="name" 
                           value="{{ old('name', $addon->name) }}" 
                           required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white placeholder-slate-500 focus:border-[#FF5500] focus:ring-1 focus:ring-[#FF5500]">
                </div>

                <!-- Slug URL -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                        Slug URL
                    </label>
                    <input type="text" 
                           name="slug" 
                           value="{{ old('slug', $addon->slug) }}" 
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-xs text-white placeholder-slate-500 focus:border-[#FF5500]">
                </div>

                <!-- Event Terkait -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                        Cakupan Event <span class="text-rose-500">*</span>
                    </label>
                    <select name="event_id" class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-3 py-2.5 text-xs text-white focus:border-[#FF5500]">
                        <option value="" {{ old('event_id', $addon->event_id) === null ? 'selected' : '' }}>🌐 Semua Event (Global Merchandise)</option>
                        @foreach($events as $ev)
                            <option value="{{ $ev->id }}" {{ (string)old('event_id', $addon->event_id) === (string)$ev->id ? 'selected' : '' }}>
                                [{{ $ev->event_code }}] {{ $ev->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Deskripsi -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                        Deskripsi Produk
                    </label>
                    <textarea name="description" 
                              rows="3" 
                              class="w-full bg-slate-950 border border-slate-700/80 rounded-xl p-3 text-xs text-white placeholder-slate-500 focus:border-[#FF5500]">{{ old('description', $addon->description) }}</textarea>
                </div>

                <!-- Upload Foto Produk -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                        Foto / Thumbnail Produk
                    </label>
                    <div class="p-4 sm:p-5 rounded-2xl bg-slate-950/80 border border-slate-800">
                        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                            <!-- Bounded Thumbnail Box (Rigidly locked at 128x128px max) -->
                            <div class="flex-shrink-0 text-center">
                                <div id="imagePreviewContainer" 
                                     class="w-32 h-32 rounded-2xl bg-slate-900 border-2 {{ $addon->image_url ? 'border-solid border-slate-700/80' : 'border-dashed border-slate-700/80' }} flex items-center justify-center overflow-hidden shadow-inner relative group"
                                     style="width: 128px; height: 128px; min-width: 128px; min-height: 128px; max-width: 128px; max-height: 128px;">
                                    @if($addon->image_url)
                                        <img id="currentPhotoImg" 
                                             src="{{ $addon->image_url }}" 
                                             alt="{{ $addon->name }}" 
                                             class="w-full h-full object-cover rounded-xl"
                                             style="width: 100%; height: 100%; max-width: 100%; max-height: 100%; object-fit: cover;">
                                    @else
                                        <div id="previewPlaceholder" class="text-center p-2">
                                            <span class="text-3xl block mb-1">🛍️</span>
                                            <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Belum Ada Foto</span>
                                        </div>
                                    @endif
                                </div>
                                <span class="text-[10px] font-mono text-slate-500 block mt-1.5">Max: 128 &times; 128 px</span>
                            </div>

                            <!-- Upload Controls & Guidelines -->
                            <div class="flex-1 w-full text-center sm:text-left">
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                                    <label for="imageInput" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-bold border border-slate-700 hover:border-slate-600 transition cursor-pointer shadow-sm">
                                        <svg class="w-4 h-4 text-[#FF5500]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span id="btnSelectLabel">{{ $addon->image_url ? 'Ganti Foto Produk' : 'Pilih Foto Produk' }}</span>
                                    </label>
                                    <input type="file" 
                                           name="image" 
                                           id="imageInput"
                                           accept="image/png,image/jpeg,image/webp" 
                                           class="hidden">

                                    <button type="button" 
                                            id="btnResetImage" 
                                            class="hidden inline-flex items-center gap-1.5 px-3 py-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 text-xs font-bold border border-rose-500/30 transition">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        <span>Batal</span>
                                    </button>
                                </div>

                                <div class="mt-3">
                                    <div id="imageFileInfo" class="text-xs text-emerald-400 font-mono hidden mb-1.5 flex items-center justify-center sm:justify-start gap-1.5">
                                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span id="imageFileName"></span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">
                                        Format: <span class="text-slate-300 font-semibold">JPG, PNG, atau WEBP</span>. Ukuran file maksimal <span class="text-slate-300 font-semibold">4 MB</span>.
                                    </p>
                                    <p class="text-[11px] text-slate-500 mt-0.5">
                                        Preview foto terkunci secara proporsional dan tidak akan meluap memenuhi layar.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Harga, Berat & Stok -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl sm:rounded-3xl p-5 sm:p-7 shadow-xl">
            <h2 class="text-base sm:text-lg font-bold text-white mb-1 flex items-center gap-2">
                <span class="w-6 h-6 rounded-lg bg-[#FF5500]/20 text-[#FF5500] flex items-center justify-center text-xs font-mono font-bold">2</span>
                <span>Harga Satuan, Berat &amp; Stok</span>
            </h2>
            <p class="text-xs text-slate-400 mb-6">Berat digunakan untuk kalkulasi otomatis tarif ongkos kirim SPX &amp; kurir pengiriman race pack.</p>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <!-- Harga -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                        Harga Satuan (Rp) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-xs text-slate-500 font-bold">Rp</span>
                        <input type="number" 
                               name="price" 
                               min="0" 
                               step="1000"
                               value="{{ old('price', (int)$addon->price) }}" 
                               required
                               class="w-full bg-slate-950 border border-slate-700/80 rounded-xl pl-10 pr-4 py-2.5 text-sm text-white font-mono-num font-bold focus:border-[#FF5500]">
                    </div>
                </div>

                <!-- Berat (Gram) -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                        Berat Paket (Gram) <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" 
                               name="weight_grams" 
                               min="0" 
                               value="{{ old('weight_grams', $addon->weight_grams) }}" 
                               required
                               class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white font-mono-num focus:border-[#FF5500]">
                        <span class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-xs text-slate-500">gram</span>
                    </div>
                </div>

                <!-- Stok -->
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                        Stok Total Unit <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" 
                           name="stock" 
                           id="baseStockInput"
                           min="0" 
                           value="{{ old('stock', $addon->stock) }}" 
                           required
                           class="w-full bg-slate-950 border border-slate-700/80 rounded-xl px-4 py-2.5 text-sm text-white font-mono-num focus:border-[#FF5500]">
                </div>

                <!-- Status Aktif -->
                <div class="sm:col-span-3 pt-2 border-t border-slate-800">
                    <input type="hidden" name="is_active" value="0">
                    <label class="inline-flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" 
                               name="is_active" 
                               value="1" 
                               {{ old('is_active', $addon->is_active) ? 'checked' : '' }}
                               class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-[#FF5500] focus:ring-[#FF5500]">
                        <div>
                            <span class="text-xs font-bold text-white block">Tampilkan di Pendaftaran &amp; Etalase</span>
                            <span class="text-[11px] text-slate-400">Jika di-uncheck (nonaktif), produk ini otomatis disembunyikan dari etalase &amp; formulir pendaftaran.</span>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        <!-- 3. Pengaturan Varian (Ukuran / Warna) -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-2xl sm:rounded-3xl p-5 sm:p-7 shadow-xl">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-base sm:text-lg font-bold text-white mb-1 flex items-center gap-2">
                        <span class="w-6 h-6 rounded-lg bg-[#FF5500]/20 text-[#FF5500] flex items-center justify-center text-xs font-mono font-bold">3</span>
                        <span>Varian Produk (Pilihan Ukuran / Warna)</span>
                    </h2>
                    <p class="text-xs text-slate-400">Aktifkan opsi ukuran seperti XS, S, M, L, XL, 2XL, 3XL, 4XL, 5XL atau varian lainnya.</p>
                </div>
            </div>

            <!-- Toggle Checkbox -->
            <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 mb-5">
                <label class="inline-flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" 
                           name="has_variants" 
                           id="hasVariantsToggle"
                           value="1" 
                           {{ old('has_variants', $addon->has_variants) ? 'checked' : '' }}
                           class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-[#FF5500] focus:ring-[#FF5500]">
                    <div>
                        <span class="text-xs font-bold text-white block">Aktifkan Varian untuk Add-on Ini</span>
                        <span class="text-[11px] text-slate-400">Peserta dapat memilih varian saat memesan item ini.</span>
                    </div>
                </label>
            </div>

            <!-- Varian Editor Section -->
            <div id="variantsContainer" class="{{ old('has_variants', $addon->has_variants) ? '' : 'hidden' }} space-y-4">
                <!-- Action Buttons: Preset & Add Manual -->
                <div class="flex flex-wrap items-center justify-between gap-2 p-3 rounded-xl bg-slate-950/60 border border-slate-800/80">
                    <span class="text-xs font-bold text-slate-300">Daftar Varian Produk:</span>
                    <div class="flex items-center gap-2">
                        <button type="button" 
                                id="btnPresetJersey" 
                                class="px-3 py-1.5 rounded-lg bg-cyan-500/10 hover:bg-cyan-500/20 text-[#00E5FF] border border-cyan-500/30 text-xs font-bold transition flex items-center gap-1.5"
                                title="Isi otomatis varian ukuran jersey standar XS sampai 5XL">
                            <span>⚡ Isi Preset Ukuran (XS - 5XL)</span>
                        </button>
                        <button type="button" 
                                id="btnAddVariantRow" 
                                class="px-3 py-1.5 rounded-lg bg-[#FF5500]/10 hover:bg-[#FF5500]/20 text-[#FF5500] border border-[#FF5500]/30 text-xs font-bold transition flex items-center gap-1.5">
                            <span>+ Tambah Varian</span>
                        </button>
                    </div>
                </div>

                <!-- Table Header for Variants -->
                <div class="overflow-x-auto no-scrollbar">
                    <table class="w-full text-left text-xs text-slate-300" id="variantsTable">
                        <thead class="bg-slate-950 text-[11px] uppercase tracking-wider text-slate-400 font-bold border-b border-slate-800">
                            <tr>
                                <th class="py-2.5 px-3">Nama Varian (Ukuran / Opsi)</th>
                                <th class="py-2.5 px-3 w-40">Tambahan Biaya (Rp)</th>
                                <th class="py-2.5 px-3 w-32">Stok Unit</th>
                                <th class="py-2.5 px-3 w-16 text-center">Hapus</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60" id="variantsTableBody">
                            @php
                                $displayVariants = old('variants', $addon->variants);
                            @endphp
                            @foreach($displayVariants as $idx => $v)
                                @php
                                    $vId = is_array($v) ? ($v['id'] ?? '') : $v->id;
                                    $vName = is_array($v) ? ($v['variant_name'] ?? '') : $v->variant_name;
                                    $vPrice = is_array($v) ? ($v['additional_price'] ?? 0) : (int)$v->additional_price;
                                    $vStock = is_array($v) ? ($v['stock'] ?? 0) : $v->stock;
                                @endphp
                                <tr class="variant-row">
                                    <td class="py-2.5 px-3">
                                        <input type="hidden" name="variants[{{ $idx }}][id]" value="{{ $vId }}">
                                        <input type="text" 
                                               name="variants[{{ $idx }}][variant_name]" 
                                               value="{{ $vName }}" 
                                               placeholder="Contoh: Ukuran M" 
                                               required
                                               class="w-full bg-slate-950 border border-slate-700/80 rounded-lg px-3 py-1.5 text-xs text-white focus:border-[#FF5500]">
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input type="number" 
                                               name="variants[{{ $idx }}][additional_price]" 
                                               value="{{ $vPrice }}" 
                                               min="0" 
                                               step="1000"
                                               class="w-full bg-slate-950 border border-slate-700/80 rounded-lg px-3 py-1.5 text-xs text-white font-mono-num focus:border-[#FF5500]">
                                    </td>
                                    <td class="py-2.5 px-3">
                                        <input type="number" 
                                               name="variants[{{ $idx }}][stock]" 
                                               value="{{ $vStock }}" 
                                               min="0" 
                                               class="w-full bg-slate-950 border border-slate-700/80 rounded-lg px-3 py-1.5 text-xs text-white font-mono-num var-stock-input focus:border-[#FF5500]">
                                    </td>
                                    <td class="py-2.5 px-3 text-center">
                                        <button type="button" class="btn-remove-variant text-slate-500 hover:text-rose-400 p-1 transition" title="Hapus varian">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="flex items-center justify-between text-xs text-slate-400 px-2 pt-2 border-t border-slate-800">
                    <span>* Tambahan biaya di atas akan otomatis ditambahkan ke harga dasar add-on jika dipilih.</span>
                    <span id="variantStockTotal" class="font-mono-num font-bold text-white"></span>
                </div>
            </div>
        </div>

        <!-- Tombol Submit -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.addons.index') }}" class="px-5 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-slate-300 bg-slate-800 hover:bg-slate-700 transition">
                Batal
            </a>
            <button type="submit" class="px-6 py-2.5 rounded-xl font-bold text-xs uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-lg shadow-orange-950/50 glow-orange flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>Simpan Perubahan</span>
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const hasVariantsToggle = document.getElementById('hasVariantsToggle');
    const variantsContainer = document.getElementById('variantsContainer');
    const variantsTableBody = document.getElementById('variantsTableBody');
    const btnAddVariantRow = document.getElementById('btnAddVariantRow');
    const btnPresetJersey = document.getElementById('btnPresetJersey');
    const baseStockInput = document.getElementById('baseStockInput');
    const variantStockTotal = document.getElementById('variantStockTotal');
    const imageInput = document.getElementById('imageInput');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');

    let variantCounter = {{ count($displayVariants) }};

    // Toggle container
    hasVariantsToggle.addEventListener('change', () => {
        if (hasVariantsToggle.checked) {
            variantsContainer.classList.remove('hidden');
            if (variantsTableBody.children.length === 0) {
                addVariantRow('Ukuran S', 0, 50);
                addVariantRow('Ukuran M', 0, 50);
                addVariantRow('Ukuran L', 0, 50);
            }
        } else {
            variantsContainer.classList.add('hidden');
        }
        recalculateTotalStock();
    });

    // Helper: add variant row
    function addVariantRow(name = '', additionalPrice = 0, stock = 50) {
        const tr = document.createElement('tr');
        tr.className = 'variant-row';
        tr.innerHTML = `
            <td class="py-2.5 px-3">
                <input type="text" 
                       name="variants[${variantCounter}][variant_name]" 
                       value="${name}" 
                       placeholder="Contoh: Ukuran XL" 
                       required
                       class="w-full bg-slate-950 border border-slate-700/80 rounded-lg px-3 py-1.5 text-xs text-white focus:border-[#FF5500]">
            </td>
            <td class="py-2.5 px-3">
                <input type="number" 
                       name="variants[${variantCounter}][additional_price]" 
                       value="${additionalPrice}" 
                       min="0" 
                       step="1000"
                       class="w-full bg-slate-950 border border-slate-700/80 rounded-lg px-3 py-1.5 text-xs text-white font-mono-num focus:border-[#FF5500]">
            </td>
            <td class="py-2.5 px-3">
                <input type="number" 
                       name="variants[${variantCounter}][stock]" 
                       value="${stock}" 
                       min="0" 
                       class="w-full bg-slate-950 border border-slate-700/80 rounded-lg px-3 py-1.5 text-xs text-white font-mono-num var-stock-input focus:border-[#FF5500]">
            </td>
            <td class="py-2.5 px-3 text-center">
                <button type="button" class="btn-remove-variant text-slate-500 hover:text-rose-400 p-1 transition" title="Hapus varian">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </td>
        `;
        variantsTableBody.appendChild(tr);
        variantCounter++;
        recalculateTotalStock();
    }

    // Add manual row
    btnAddVariantRow.addEventListener('click', () => {
        addVariantRow('', 0, 50);
    });

    // Preset standard jersey sizes: XS, S, M, L, XL, 2XL, 3XL, 4XL, 5XL
    btnPresetJersey.addEventListener('click', () => {
        if (variantsTableBody.children.length > 0 && !confirm('Ganti varian saat ini dengan preset ukuran standar XS - 5XL?')) {
            return;
        }
        variantsTableBody.innerHTML = '';
        const sizes = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', '4XL', '5XL'];
        sizes.forEach(size => {
            const addPrice = (['3XL', '4XL', '5XL'].includes(size)) ? 10000 : 0;
            addVariantRow(`Ukuran ${size}`, addPrice, 50);
        });
        hasVariantsToggle.checked = true;
        variantsContainer.classList.remove('hidden');
    });

    // Remove row delegation
    variantsTableBody.addEventListener('click', (e) => {
        const removeBtn = e.target.closest('.btn-remove-variant');
        if (removeBtn) {
            removeBtn.closest('tr').remove();
            recalculateTotalStock();
        }
    });

    // Recalculate stock total
    variantsTableBody.addEventListener('input', (e) => {
        if (e.target.classList.contains('var-stock-input')) {
            recalculateTotalStock();
        }
    });

    function recalculateTotalStock() {
        if (!hasVariantsToggle.checked) {
            variantStockTotal.textContent = '';
            return;
        }
        let total = 0;
        document.querySelectorAll('.var-stock-input').forEach(input => {
            total += (parseInt(input.value) || 0);
        });
        variantStockTotal.textContent = `Total Stok Varian: ${total} unit`;
        baseStockInput.value = total;
    }

    // Image preview with rigid bounds
    const imageInput = document.getElementById('imageInput');
    const imagePreviewContainer = document.getElementById('imagePreviewContainer');
    const btnResetImage = document.getElementById('btnResetImage');
    const btnSelectLabel = document.getElementById('btnSelectLabel');
    const imageFileInfo = document.getElementById('imageFileInfo');
    const imageFileName = document.getElementById('imageFileName');

    @if($addon->image_url)
    const originalImageHtml = `
        <img id="currentPhotoImg" 
             src="{{ $addon->image_url }}" 
             alt="{{ $addon->name }}" 
             class="w-full h-full object-cover rounded-xl"
             style="width: 100%; height: 100%; max-width: 100%; max-height: 100%; object-fit: cover;">
    `;
    const defaultSelectLabel = 'Ganti Foto Produk';
    @else
    const originalImageHtml = `
        <div id="previewPlaceholder" class="text-center p-2">
            <span class="text-3xl block mb-1">🛍️</span>
            <span class="text-[10px] text-slate-500 font-semibold uppercase tracking-wider block">Belum Ada Foto</span>
        </div>
    `;
    const defaultSelectLabel = 'Pilih Foto Produk';
    @endif

    imageInput.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 4 * 1024 * 1024) {
                alert('Ukuran file foto maksimal 4 MB.');
                imageInput.value = '';
                return;
            }
            const reader = new FileReader();
            reader.onload = (event) => {
                imagePreviewContainer.innerHTML = `
                    <img src="${event.target.result}" 
                         alt="Preview Produk Baru" 
                         class="w-full h-full object-cover rounded-xl"
                         style="width: 100%; height: 100%; max-width: 100%; max-height: 100%; object-fit: cover;">
                `;
                imagePreviewContainer.classList.remove('border-dashed');
                imagePreviewContainer.classList.add('border-solid', 'border-[#FF5500]/50');

                if (imageFileName && imageFileInfo) {
                    const sizeKb = (file.size / 1024).toFixed(0);
                    imageFileName.textContent = `${file.name} (${sizeKb} KB)`;
                    imageFileInfo.classList.remove('hidden');
                }
                if (btnResetImage) btnResetImage.classList.remove('hidden');
                if (btnSelectLabel) btnSelectLabel.textContent = 'Ganti Foto';
            };
            reader.readAsDataURL(file);
        }
    });

    if (btnResetImage) {
        btnResetImage.addEventListener('click', () => {
            imageInput.value = '';
            imagePreviewContainer.innerHTML = originalImageHtml;
            @if($addon->image_url)
                imagePreviewContainer.classList.remove('border-dashed', 'border-[#FF5500]/50');
                imagePreviewContainer.classList.add('border-solid');
            @else
                imagePreviewContainer.classList.add('border-dashed');
                imagePreviewContainer.classList.remove('border-solid', 'border-[#FF5500]/50');
            @endif
            btnResetImage.classList.add('hidden');
            if (imageFileInfo) imageFileInfo.classList.add('hidden');
            if (btnSelectLabel) btnSelectLabel.textContent = defaultSelectLabel;
        });
    }

    recalculateTotalStock();
});
</script>
@endsection
