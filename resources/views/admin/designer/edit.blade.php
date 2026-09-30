@extends('layouts.app')

@section('title', 'Visual Designer (' . $type . ') — ' . $event->title . ' — VIRA')

@section('content')
<div class="max-w-7xl mx-auto px-3.5 sm:px-6 lg:px-8 py-6 sm:py-8">
    <!-- Header Ringkas -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <a href="{{ route('admin.events.index') }}" class="text-xs text-slate-400 hover:text-white transition flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    <span>Kembali ke Event</span>
                </a>
                <span class="text-slate-600">/</span>
                <span class="text-xs font-bold uppercase tracking-wider text-[#FF5500]">Drag &amp; Drop Designer</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white font-athletic tracking-wide">
                Desainer {{ $type === 'BIB' ? 'Kartu e-BIB' : 'E-Sertifikat Finisher' }}
            </h1>
            <p class="text-xs text-slate-400 mt-1">
                Tinggal <strong>klik dan geser (drag &amp; drop)</strong> teks langsung di atas gambar template. Posisi langsung terlihat nyata.
            </p>
        </div>

        <!-- Switch Tab BIB vs CERTIFICATE -->
        <div class="flex items-center gap-1.5 bg-slate-900 p-1.5 rounded-2xl border border-slate-800">
            <a href="{{ route('admin.designer.edit', ['event' => $event->id, 'type' => 'BIB']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $type === 'BIB' ? 'bg-[#FF5500] text-white shadow-md' : 'text-slate-400 hover:text-white' }}">
                🎽 e-BIB
            </a>
            <a href="{{ route('admin.designer.edit', ['event' => $event->id, 'type' => 'CERTIFICATE']) }}" 
               class="px-4 py-2 rounded-xl text-xs font-bold transition {{ $type === 'CERTIFICATE' ? 'bg-cyan-500 text-slate-950 font-extrabold shadow-md' : 'text-slate-400 hover:text-white' }}">
                🏅 E-Sertifikat
            </a>
        </div>
    </div>

    <!-- Main Form & Canvas Grid -->
    <form action="{{ route('admin.designer.update', ['event' => $event->id, 'type' => $type]) }}" 
          method="POST" 
          enctype="multipart/form-data" 
          id="simpleDesignerForm" 
          class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        @csrf

        <!-- KIRI: Kanvas Interaktif Drag & Drop (7 Cols) -->
        <div class="lg:col-span-7 space-y-3 lg:sticky lg:top-20">
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 shadow-xl">
                
                <!-- Info Status Bar -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3 text-xs">
                    <span class="font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Live Preview ({{ $template->canvas_width }} &times; {{ $template->canvas_height }} px)</span>
                    </span>

                    <!-- Tab Switcher: Mode Geser vs Mode Hasil Cetak -->
                    <div class="flex items-center gap-1 bg-slate-950 p-1 rounded-xl border border-slate-800">
                        <button type="button" id="tabDesignMode" 
                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition bg-[#FF5500] text-white shadow-sm flex items-center gap-1"
                                title="Mode Atur Posisi: Kanvas bersih hanya dengan teks yang bisa digeser">
                            <span>🖐️ Geser Posisi</span>
                        </button>
                        <button type="button" id="tabFinalPreview" 
                                class="px-2.5 py-1 rounded-lg text-[11px] font-bold transition text-slate-400 hover:text-white flex items-center gap-1"
                                title="Lihat hasil render final server yang akan diterima peserta">
                            <span>👁️ Hasil Cetak Akhir</span>
                        </button>
                    </div>
                </div>

                <!-- Canvas Stage Container -->
                <div class="relative bg-slate-950 rounded-xl overflow-hidden border border-slate-800 shadow-inner flex items-center justify-center p-1 select-none">
                    
                    <div id="simpleCanvasStage" 
                         class="relative w-full overflow-hidden rounded-lg cursor-default select-none"
                         style="aspect-ratio: {{ $template->canvas_width }} / {{ $template->canvas_height }};">
                        
                        <!-- Background Template Image (Resampled 100% presisi pixel) -->
                        <img id="templateBgImage" 
                             src="{{ route('admin.designer.preview', ['event' => $event->id, 'type' => $type, 'blank' => 1]) }}&t={{ time() }}" 
                             data-blank-url="{{ route('admin.designer.preview', ['event' => $event->id, 'type' => $type, 'blank' => 1]) }}"
                             data-full-url="{{ route('admin.designer.preview', ['event' => $event->id, 'type' => $type]) }}"
                             alt="Template Background" 
                             class="absolute inset-0 w-full h-full object-fill pointer-events-none select-none z-0">

                        <!-- Lapisan Teks Interaktif Drag & Drop -->
                        <div id="dragLayer" class="absolute inset-0 z-10 pointer-events-auto">
                            @foreach($elements as $i => $el)
                                @php
                                    $sampleText = match($el['key']) {
                                        'bib_number' => ($type === 'BIB' ? '1001' : 'BIB: 1001'),
                                        'participant_name' => 'BUDI PRATAMA',
                                        'category_name' => '10K CHALLENGE',
                                        'event_name' => $event->title,
                                        'total_distance' => '10.00 KM',
                                        'total_duration' => '00:52:18',
                                        'average_pace' => "5'13\" /km",
                                        'finish_date' => date('d F Y'),
                                        'qr_code' => 'QR CODE',
                                        default => $el['label'] ?? $el['key']
                                    };

                                    $align = $el['align'] ?? 'center';
                                    $translateX = match($align) {
                                        'left' => '0%',
                                        'right' => '-100%',
                                        default => '-50%'
                                    };

                                    $leftPct = ($template->canvas_width > 0) ? ($el['x'] / $template->canvas_width) * 100 : 50;
                                    $topPct = ($template->canvas_height > 0) ? ($el['y'] / $template->canvas_height) * 100 : 50;
                                    $isVisible = !empty($el['visible']);
                                @endphp

                                <div id="drag-item-{{ $el['key'] }}"
                                     data-key="{{ $el['key'] }}"
                                     data-index="{{ $i }}"
                                     data-font-size="{{ $el['font_size'] ?? 36 }}"
                                     class="drag-text-item absolute cursor-grab active:cursor-grabbing select-none whitespace-nowrap touch-none {{ $isVisible ? '' : 'hidden' }}"
                                     style="left: {{ $leftPct }}%; top: {{ $topPct }}%; transform: translate({{ $translateX }}, -50%); z-index: 10;">
                                    
                                    <!-- Label Badge Ringkas (Muncul saat hover / aktif) -->
                                    <div class="el-badge opacity-0 pointer-events-none transition-opacity duration-150 absolute -top-5 left-1/2 -translate-x-1/2 px-1.5 py-0.5 rounded bg-slate-900/95 border border-cyan-500/80 text-[9px] font-bold text-cyan-300 whitespace-nowrap shadow-xl z-50">
                                        {{ $el['label'] ?? $el['key'] }}
                                    </div>

                                    <!-- Teks Asli yang Ditampilkan & Bisa Digeser -->
                                    <div class="el-box px-2 py-0.5 rounded border border-transparent hover:border-cyan-400/80 hover:bg-cyan-500/10 transition-colors font-bold tracking-wide {{ $el['key'] === 'bib_number' ? 'font-athletic tracking-wider' : '' }}"
                                         style="color: {{ $el['color'] ?? '#FFFFFF' }};">
                                        @if(($el['key'] ?? '') === 'qr_code')
                                            <div class="w-12 h-12 border-2 border-dashed border-cyan-400 bg-black/60 rounded flex items-center justify-center text-[10px] text-cyan-300 font-mono shadow-md">
                                                QR CODE
                                            </div>
                                        @else
                                            <span class="preview-text-content pointer-events-none select-none inline-block leading-none">{{ $sampleText }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mt-2 flex items-center justify-between text-[11px] text-slate-400 px-1">
                    <span>💡 <strong>Tip:</strong> Klik teks di kanvas dan geser langsung, atau ubah angka koordinat X &amp; Y di sidebar.</span>
                    <button type="button" id="refreshBtn" class="text-cyan-400 hover:text-cyan-300 font-semibold flex items-center gap-1">
                        <span>🔄 Segarkan Gambar</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- KANAN: Pengaturan Elemen Sederhana (5 Cols) -->
        <div class="lg:col-span-5 space-y-4">
            
            <!-- Upload Background -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 shadow-xl">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider mb-1 flex items-center gap-2">
                    <span class="text-[#FF5500]">1.</span> Ganti Gambar Background
                </h3>
                <p class="text-[11px] text-slate-400 mb-2">
                    Upload file template desain resmi Anda (PNG/JPG {{ $type === 'BIB' ? '1200x800' : '1920x1080' }} px).
                </p>

                <input type="file" name="background_image" accept="image/png,image/jpeg"
                       class="w-full text-xs text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-[#FF5500] file:text-white hover:file:bg-[#FF6600] cursor-pointer bg-slate-950 rounded-xl border border-slate-800 p-1.5">

                @if($template->background_image_path)
                    <div class="mt-1.5 text-[10px] text-emerald-400 truncate">
                        ✓ Background aktif: {{ basename($template->background_image_path) }}
                    </div>
                @endif
            </div>

            <!-- List Pengaturan Elemen -->
            <div class="bg-slate-900/90 border border-slate-800 rounded-2xl p-4 shadow-xl space-y-3">
                <div class="flex items-center justify-between pb-2 border-b border-slate-800">
                    <h3 class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                        <span class="text-[#00E5FF]">2.</span> Daftar Elemen &amp; Koordinat
                    </h3>
                    <span class="text-[11px] text-slate-400">{{ count($elements) }} Item</span>
                </div>

                <div class="space-y-2.5 max-h-[520px] overflow-y-auto pr-1 no-scrollbar" id="elementsSidebarList">
                    @foreach($elements as $i => $el)
                        <div class="element-card p-3 rounded-xl bg-slate-950 border border-slate-800/80 space-y-2.5 transition hover:border-slate-700"
                             data-key="{{ $el['key'] }}">
                            
                            <!-- Header Item: Checkbox & Nama -->
                            <div class="flex items-center justify-between">
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" 
                                           name="elements[{{ $i }}][visible]" 
                                           value="1" 
                                           data-key="{{ $el['key'] }}"
                                           {{ !empty($el['visible']) ? 'checked' : '' }}
                                           class="el-visible-chk rounded bg-slate-900 border-slate-700 text-[#FF5500] focus:ring-0">
                                    <span class="font-bold text-xs text-white">{{ $el['label'] ?? $el['key'] }}</span>
                                </label>
                                
                                <span class="text-[9px] font-mono font-bold text-cyan-400 bg-cyan-950/60 border border-cyan-800/50 px-1.5 py-0.5 rounded tracking-wider uppercase">
                                    {{ $el['key'] }}
                                </span>

                                <input type="hidden" name="elements[{{ $i }}][key]" value="{{ $el['key'] }}">
                                <input type="hidden" name="elements[{{ $i }}][label]" value="{{ $el['label'] ?? $el['key'] }}">
                                <input type="hidden" name="elements[{{ $i }}][align]" value="{{ $el['align'] ?? 'center' }}">
                            </div>

                            <!-- Input Koordinat Presisi X & Y -->
                            <div class="grid grid-cols-2 gap-2 text-xs pt-0.5">
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Posisi X (px)</label>
                                    <input type="number" 
                                           name="elements[{{ $i }}][x]" 
                                           value="{{ $el['x'] ?? 0 }}" 
                                           data-key="{{ $el['key'] }}" 
                                           data-axis="x"
                                           min="0"
                                           max="{{ $template->canvas_width }}"
                                           class="coord-input w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-white text-xs font-mono focus:border-cyan-400 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Posisi Y (px)</label>
                                    <input type="number" 
                                           name="elements[{{ $i }}][y]" 
                                           value="{{ $el['y'] ?? 0 }}" 
                                           data-key="{{ $el['key'] }}" 
                                           data-axis="y"
                                           min="0"
                                           max="{{ $template->canvas_height }}"
                                           class="coord-input w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-white text-xs font-mono focus:border-cyan-400 focus:outline-none">
                                </div>
                            </div>

                            @if(($el['key'] ?? '') !== 'qr_code')
                                <!-- Opsi Ukuran Font & Warna -->
                                <div class="grid grid-cols-2 gap-2 text-xs pt-0.5">
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Ukuran Font</label>
                                        <input type="number" 
                                               name="elements[{{ $i }}][font_size]" 
                                               value="{{ $el['font_size'] ?? 36 }}" 
                                               min="12" 
                                               max="150"
                                               data-key="{{ $el['key'] }}"
                                               class="el-fontsize-input w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-white text-xs font-mono focus:border-cyan-400 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Warna Teks</label>
                                        <div class="flex items-center gap-1.5">
                                            <input type="color" 
                                                   value="{{ $el['color'] ?? '#FFFFFF' }}" 
                                                   data-key="{{ $el['key'] }}"
                                                   class="el-color-input w-6 h-6 rounded border-0 bg-transparent cursor-pointer">
                                            <input type="text" 
                                                   name="elements[{{ $i }}][color]" 
                                                   value="{{ $el['color'] ?? '#FFFFFF' }}" 
                                                   data-key="{{ $el['key'] }}"
                                                   class="el-colortext-input w-full bg-slate-900 border border-slate-700 rounded-lg px-1.5 py-1 text-white font-mono text-[10px] uppercase focus:border-cyan-400 focus:outline-none">
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="pt-0.5">
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Ukuran QR Code (px)</label>
                                    <input type="number" 
                                           name="elements[{{ $i }}][size]" 
                                           value="{{ $el['size'] ?? 100 }}" 
                                           min="40" 
                                           max="400"
                                           class="w-full bg-slate-900 border border-slate-700 rounded-lg px-2 py-1 text-white text-xs font-mono focus:border-cyan-400 focus:outline-none">
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                <!-- Tombol Simpan -->
                <div class="pt-3 border-t border-slate-800">
                    <button type="submit" 
                            id="saveBtn"
                            class="w-full py-3 px-4 rounded-xl font-bold uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-lg shadow-orange-950/60 glow-orange flex items-center justify-center gap-2 text-xs">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span id="saveBtnText">Simpan Posisi Desain</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Notification Toast -->
<div id="simpleToast" class="fixed bottom-6 right-6 z-50 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
    <div class="px-4 py-2.5 rounded-xl bg-emerald-950/95 border border-emerald-500/50 text-emerald-200 text-xs font-bold shadow-xl flex items-center gap-2">
        <span>✓ Posisi desain berhasil disimpan!</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const canvasWidth = {{ $template->canvas_width }};
    const canvasHeight = {{ $template->canvas_height }};
    
    const stage = document.getElementById('simpleCanvasStage');
    const bgImg = document.getElementById('templateBgImage');
    const refreshBtn = document.getElementById('refreshBtn');
    const form = document.getElementById('simpleDesignerForm');
    const saveBtn = document.getElementById('saveBtn');
    const saveBtnText = document.getElementById('saveBtnText');
    const toast = document.getElementById('simpleToast');

    const dragItems = document.querySelectorAll('.drag-text-item');

    // Sesuaikan ukuran font preview agar proporsional dengan skala kanvas di layar
    function scalePreviewFonts() {
        const stageWidth = stage.clientWidth;
        if (stageWidth <= 0) return;
        const scale = stageWidth / canvasWidth;

        dragItems.forEach(item => {
            const baseFontSize = parseInt(item.dataset.fontSize, 10) || 36;
            const renderedFontSize = Math.max(10, Math.round(baseFontSize * scale));
            item.style.fontSize = renderedFontSize + 'px';
            item.style.lineHeight = '1';

            const textContent = item.querySelector('.preview-text-content');
            if (textContent) {
                textContent.style.fontSize = renderedFontSize + 'px';
                textContent.style.lineHeight = '1';
            }
        });
    }

    scalePreviewFonts();
    window.addEventListener('resize', scalePreviewFonts);

    // Tab Switcher Logic: Mode Desain (Polos) vs Mode Hasil Cetak Akhir
    const tabDesignMode = document.getElementById('tabDesignMode');
    const tabFinalPreview = document.getElementById('tabFinalPreview');
    const dragLayer = document.getElementById('dragLayer');
    let isFinalMode = false;

    function refreshCanvasBg() {
        if (isFinalMode) {
            bgImg.src = bgImg.dataset.fullUrl + '?t=' + Date.now();
        } else {
            bgImg.src = bgImg.dataset.blankUrl + '&t=' + Date.now();
        }
    }

    if (tabDesignMode) {
        tabDesignMode.addEventListener('click', function() {
            isFinalMode = false;
            tabDesignMode.className = 'px-2.5 py-1 rounded-lg text-[11px] font-bold transition bg-[#FF5500] text-white shadow-sm flex items-center gap-1';
            tabFinalPreview.className = 'px-2.5 py-1 rounded-lg text-[11px] font-bold transition text-slate-400 hover:text-white flex items-center gap-1';
            if (dragLayer) dragLayer.classList.remove('hidden');
            refreshCanvasBg();
        });
    }

    if (tabFinalPreview) {
        tabFinalPreview.addEventListener('click', function() {
            isFinalMode = true;
            tabFinalPreview.className = 'px-2.5 py-1 rounded-lg text-[11px] font-bold transition bg-cyan-500 text-slate-950 font-extrabold shadow-sm flex items-center gap-1';
            tabDesignMode.className = 'px-2.5 py-1 rounded-lg text-[11px] font-bold transition text-slate-400 hover:text-white flex items-center gap-1';
            if (dragLayer) dragLayer.classList.add('hidden');
            refreshCanvasBg();
        });
    }

    // Refresh Background Preview Image
    if (refreshBtn) {
        refreshBtn.addEventListener('click', refreshCanvasBg);
    }

    // SISTEM SELEKSI ELEMEN & Z-INDEX MANAGEMENT
    let selectedKey = null;

    function selectElement(key) {
        selectedKey = key;

        dragItems.forEach(item => {
            const isSel = item.dataset.key === key;
            const box = item.querySelector('.el-box');
            const badge = item.querySelector('.el-badge');

            if (isSel) {
                item.style.zIndex = '50';
                if (box) {
                    box.classList.add('ring-2', 'ring-cyan-400', 'bg-cyan-500/20');
                }
                if (badge) {
                    badge.classList.remove('opacity-0');
                }
            } else {
                item.style.zIndex = '10';
                if (box) {
                    box.classList.remove('ring-2', 'ring-cyan-400', 'bg-cyan-500/20');
                }
                if (badge) {
                    badge.classList.add('opacity-0');
                }
            }
        });

        document.querySelectorAll('.element-card').forEach(card => {
            if (card.dataset.key === key) {
                card.classList.add('ring-2', 'ring-[#00E5FF]/70', 'border-[#00E5FF]/50');
            } else {
                card.classList.remove('ring-2', 'ring-[#00E5FF]/70', 'border-[#00E5FF]/50');
            }
        });
    }

    // Klik kartu di sidebar untuk langsung memilih dan menaikkan elemen di kanvas
    document.querySelectorAll('.element-card').forEach(card => {
        card.addEventListener('click', function(e) {
            if (e.target.closest('input') || e.target.closest('button')) return;
            selectElement(this.dataset.key);
        });
    });

    // SISTEM DRAG & DROP DENGAN POINTER EVENTS & POINTER CAPTURE
    let activeItem = null;
    let isDragging = false;
    let startX = 0;
    let startY = 0;
    let startLeftPct = 0;
    let startTopPct = 0;

    dragItems.forEach(item => {
        item.addEventListener('pointerdown', function(e) {
            if (e.button !== 0 && e.pointerType === 'mouse') return;

            activeItem = this;
            isDragging = true;
            try {
                activeItem.setPointerCapture(e.pointerId);
            } catch (_) {}

            selectElement(activeItem.dataset.key);

            startX = e.clientX;
            startY = e.clientY;

            startLeftPct = parseFloat(activeItem.style.left);
            if (isNaN(startLeftPct)) startLeftPct = 50;

            startTopPct = parseFloat(activeItem.style.top);
            if (isNaN(startTopPct)) startTopPct = 50;

            e.preventDefault();
        });

        item.addEventListener('pointermove', function(e) {
            if (!isDragging || activeItem !== this) return;

            const stageRect = stage.getBoundingClientRect();
            if (stageRect.width <= 0 || stageRect.height <= 0) return;

            const deltaPxX = e.clientX - startX;
            const deltaPxY = e.clientY - startY;

            const deltaPctX = (deltaPxX / stageRect.width) * 100;
            const deltaPctY = (deltaPxY / stageRect.height) * 100;

            let newLeftPct = Math.max(0, Math.min(100, startLeftPct + deltaPctX));
            let newTopPct = Math.max(0, Math.min(100, startTopPct + deltaPctY));

            activeItem.style.left = newLeftPct + '%';
            activeItem.style.top = newTopPct + '%';

            const canvasX = Math.round((newLeftPct / 100) * canvasWidth);
            const canvasY = Math.round((newTopPct / 100) * canvasHeight);
            const key = activeItem.dataset.key;

            // Live sync ke input koordinat di sidebar
            const inputX = document.querySelector(`.coord-input[data-key="${key}"][data-axis="x"]`);
            const inputY = document.querySelector(`.coord-input[data-key="${key}"][data-axis="y"]`);
            if (inputX) inputX.value = canvasX;
            if (inputY) inputY.value = canvasY;

            if (e.cancelable) e.preventDefault();
        });

        function endDrag(e) {
            if (activeItem === this) {
                try {
                    this.releasePointerCapture(e.pointerId);
                } catch (_) {}
                isDragging = false;
                activeItem = null;
            }
        }

        item.addEventListener('pointerup', endDrag);
        item.addEventListener('pointercancel', endDrag);
    });

    // SINKRONISASI DUA ARAH: Edit angka koordinat X & Y di sidebar langsung menggeser kanvas
    document.querySelectorAll('.coord-input').forEach(input => {
        input.addEventListener('input', function() {
            const key = this.dataset.key;
            const axis = this.dataset.axis;
            const val = parseInt(this.value, 10);
            if (isNaN(val)) return;

            const item = document.getElementById('drag-item-' + key);
            if (!item) return;

            selectElement(key);

            if (axis === 'x') {
                const leftPct = (val / canvasWidth) * 100;
                item.style.left = Math.max(0, Math.min(100, leftPct)) + '%';
            } else if (axis === 'y') {
                const topPct = (val / canvasHeight) * 100;
                item.style.top = Math.max(0, Math.min(100, topPct)) + '%';
            }
        });
    });

    // 1. Tampilkan / Sembunyikan elemen
    document.querySelectorAll('.el-visible-chk').forEach(chk => {
        chk.addEventListener('change', function() {
            const key = this.dataset.key;
            const item = document.getElementById('drag-item-' + key);
            if (item) {
                if (this.checked) {
                    item.classList.remove('hidden');
                    selectElement(key);
                } else {
                    item.classList.add('hidden');
                }
            }
        });
    });

    // 2. Warna Teks Langsung
    document.querySelectorAll('.el-color-input').forEach(colorPicker => {
        colorPicker.addEventListener('input', function() {
            const key = this.dataset.key;
            const val = this.value;
            const textInput = document.querySelector(`.el-colortext-input[data-key="${key}"]`);
            if (textInput) textInput.value = val.toUpperCase();

            const item = document.getElementById('drag-item-' + key);
            if (item) {
                const textElem = item.querySelector('.el-box');
                if (textElem) textElem.style.color = val;
            }
        });
    });

    document.querySelectorAll('.el-colortext-input').forEach(textInput => {
        textInput.addEventListener('input', function() {
            const key = this.dataset.key;
            const val = this.value;
            const colorPicker = document.querySelector(`.el-color-input[data-key="${key}"]`);
            if (colorPicker && /^#[0-9A-Fa-f]{6}$/.test(val)) colorPicker.value = val;

            const item = document.getElementById('drag-item-' + key);
            if (item) {
                const textElem = item.querySelector('.el-box');
                if (textElem) textElem.style.color = val;
            }
        });
    });

    // 3. Ukuran Font Langsung
    document.querySelectorAll('.el-fontsize-input').forEach(input => {
        input.addEventListener('input', function() {
            const key = this.dataset.key;
            const size = parseInt(this.value, 10) || 36;
            const item = document.getElementById('drag-item-' + key);
            if (item) {
                item.dataset.fontSize = size;
                scalePreviewFonts();
            }
        });
    });

    // AJAX SAVE CEPAT
    form.addEventListener('submit', async function(e) {
        const bgInput = document.querySelector('input[name="background_image"]');
        if (bgInput && bgInput.files && bgInput.files.length > 0) {
            return true; // Submit normal untuk upload file gambar
        }

        e.preventDefault();
        saveBtn.disabled = true;
        saveBtnText.textContent = 'Menyimpan...';

        try {
            const formData = new FormData(form);
            const res = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
                body: formData
            });

            if (res.ok) {
                toast.classList.remove('translate-y-20', 'opacity-0');
                setTimeout(() => toast.classList.add('translate-y-20', 'opacity-0'), 2500);

                // Refresh background render
                refreshCanvasBg();
            } else {
                form.submit();
            }
        } catch (err) {
            form.submit();
        } finally {
            saveBtn.disabled = false;
            saveBtnText.textContent = 'Simpan Posisi Desain';
        }
    });
});
</script>
@endpush
