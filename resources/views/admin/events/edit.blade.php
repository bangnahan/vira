@extends('layouts.app')

@section('title', 'Edit Event & Konfigurasi Meta Pixel CAPI: ' . $event->title . ' — VIRA Admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Breadcrumb & Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">
                <a href="{{ route('admin.events.index') }}" class="hover:text-white transition">Admin Events</a>
                <span>/</span>
                <span class="text-[#FF5500]">Edit: {{ $event->title }}</span>
            </div>
            <h1 class="text-3xl font-extrabold text-white font-athletic tracking-wide">Edit Event &amp; Meta Pixel CAPI</h1>
            <p class="text-xs text-slate-400">Kode Event: <strong class="text-cyan-400 font-mono-num">{{ $event->event_code }}</strong> • Terdaftar {{ $event->categories->count() }} Kategori &amp; {{ $event->packages->count() }} Paket</p>
        </div>

        <!-- Quick Designer Links -->
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.designer.edit', ['event' => $event->id, 'type' => 'BIB']) }}" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-cyan-300 bg-cyan-950/60 border border-cyan-800/80 hover:bg-cyan-900/60 transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/></svg>
                <span>e-BIB Designer</span>
            </a>
            <a href="{{ route('admin.designer.edit', ['event' => $event->id, 'type' => 'CERTIFICATE']) }}" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-amber-300 bg-amber-950/60 border border-amber-800/80 hover:bg-amber-900/60 transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Sertifikat Designer</span>
            </a>
        </div>
    </div>

    <!-- Test CAPI Card (Hanya muncul jika Pixel ID & Token sudah diisi) -->
    @if($event->meta_pixel_id && $event->meta_capi_token)
        <div class="mb-8 p-6 rounded-3xl bg-gradient-to-r from-indigo-950/80 via-slate-900 to-slate-900 border border-indigo-500/40 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <h4 class="text-sm font-bold text-white uppercase tracking-wider">Uji Coba Pengiriman Meta Conversions API (CAPI)</h4>
                    </div>
                    <p class="text-xs text-slate-300 mt-1">
                        Kirim data simulasi <strong>Purchase</strong> langsung ke Meta Events Manager untuk memvalidasi integrasi Pixel ID <strong>{{ $event->meta_pixel_id }}</strong>.
                    </p>
                </div>

                <form action="{{ route('admin.events.test-capi', $event) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="test_event_code" value="{{ $event->meta_test_code }}" placeholder="Test Event Code (e.g. TEST12345)"
                           class="bg-slate-950 border border-slate-700 rounded-xl px-3 py-2 text-xs text-white font-mono-num w-44 focus:border-indigo-400">
                    <button type="submit" class="px-4 py-2 rounded-xl text-xs font-bold uppercase tracking-wider text-white bg-indigo-600 hover:bg-indigo-500 transition shadow-md whitespace-nowrap">
                        🚀 Kirim Test Event
                    </button>
                </form>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-950/80 border border-rose-500/50 text-rose-200 text-xs space-y-1.5 shadow-xl">
            <div class="flex items-center gap-2 font-bold text-rose-300 text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Gagal Memperbarui Event - Periksa Input Berikut:</span>
            </div>
            @foreach($errors->all() as $error)
                <p class="pl-7 text-rose-300/90">• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.events.update', $event) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf
        @method('PUT')

        <!-- SECTION 1: Informasi Dasar Event -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-800">
                <span class="w-8 h-8 rounded-lg bg-orange-500/20 text-[#FF5500] font-athletic text-xl flex items-center justify-center">1</span>
                <div>
                    <h3 class="text-lg font-bold text-white">Informasi Dasar Event</h3>
                    <p class="text-xs text-slate-400">Judul, kode identifikasi, dan aturan mode pencatatan jarak.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Judul Event <span class="text-rose-400">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $event->title) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                    @error('title') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Kode Event Unik (Prefix BIB) <span class="text-rose-400">*</span></label>
                    <input type="text" name="event_code" value="{{ old('event_code', $event->event_code) }}" required uppercase maxlength="10"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white font-mono-num font-bold focus:border-[#FF5500]">
                    @error('event_code') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Slug URL <span class="text-rose-400">*</span></label>
                    <input type="text" name="slug" value="{{ old('slug', $event->slug) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                    @error('slug') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Jenis Olahraga (Activity Type) <span class="text-rose-400">*</span></label>
                    <select name="activity_type" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                        <option value="RUN" {{ old('activity_type', $event->activity_type) === 'RUN' ? 'selected' : '' }}>Lari (Virtual Run)</option>
                        <option value="RIDE" {{ old('activity_type', $event->activity_type) === 'RIDE' ? 'selected' : '' }}>Sepeda (Virtual Ride)</option>
                        <option value="WALK" {{ old('activity_type', $event->activity_type) === 'WALK' ? 'selected' : '' }}>Jalan Santai (Virtual Walk)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Metode Submission Hasil <span class="text-rose-400">*</span></label>
                    <select name="submission_mode" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                        <option value="CUMULATIVE" {{ old('submission_mode', $event->submission_mode) === 'CUMULATIVE' ? 'selected' : '' }}>Akumulasi Jarak (Cicil Berkali-kali)</option>
                        <option value="SINGLE" {{ old('submission_mode', $event->submission_mode) === 'SINGLE' ? 'selected' : '' }}>Satu Sesi Tunggal (Single Activity)</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Tipe Event</label>
                    <select name="race_type" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                        <option value="CHALLENGE" {{ old('race_type', $event->race_type) === 'CHALLENGE' ? 'selected' : '' }}>Tantangan Target (Challenge / Finisher)</option>
                        <option value="RACE" {{ old('race_type', $event->race_type) === 'RACE' ? 'selected' : '' }}>Kompetisi Kecepatan (Pace Ranking)</option>
                    </select>
                </div>

                <!-- Hero Image Upload & URL Section -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">
                        Hero Banner Gambar Event <span class="text-xs font-normal text-slate-500">(Upload File)</span>
                    </label>

                    <div class="space-y-3">
                        @php
                            $hasBanner = !empty($event->banner_image);
                        @endphp

                        <!-- Dropzone File Upload -->
                        <div id="dropzoneContainer" 
                             class="relative border-2 border-dashed border-slate-700 hover:border-[#FF5500] rounded-2xl p-6 text-center bg-slate-950/60 transition cursor-pointer group">
                            
                            <input type="file" 
                                   id="heroImageInput" 
                                   name="hero_image" 
                                   accept="image/png,image/jpeg,image/webp" 
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                            <!-- Empty State Display -->
                            <div id="dropzoneEmpty" class="{{ $hasBanner ? 'hidden' : 'flex' }} flex-col items-center justify-center py-4">
                                <div class="w-14 h-14 rounded-2xl bg-orange-500/10 text-[#FF5500] flex items-center justify-center mb-3 group-hover:scale-110 transition duration-200">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <span class="text-sm font-bold text-white block">Klik atau Seret &amp; Lepas File Hero Image di sini</span>
                                <span class="text-xs text-slate-400 mt-1">Format: JPG, PNG, atau WebP (Maksimal 5MB). Rekomendasi: 1920 &times; 800 px (16:9).</span>
                            </div>

                            <!-- Image Preview Display -->
                            <div id="dropzonePreview" class="{{ $hasBanner ? 'flex' : 'hidden' }} relative rounded-xl overflow-hidden max-h-72 border border-slate-800 bg-slate-900 items-center justify-center">
                                <img id="previewImg" src="{{ $event->banner_url }}" alt="Hero Image Preview" class="w-full h-auto max-h-72 object-cover rounded-xl">
                                <div class="absolute inset-0 bg-slate-950/60 opacity-0 hover:opacity-100 transition flex items-center justify-center gap-3">
                                    <span class="text-xs text-white font-bold bg-[#FF5500] px-3 py-1.5 rounded-lg shadow">Ganti Gambar</span>
                                    <button type="button" id="removeImgBtn" class="text-xs text-white font-bold bg-rose-600 hover:bg-rose-700 px-3 py-1.5 rounded-lg shadow z-20">Hapus</button>
                                </div>
                            </div>
                        </div>
                        @error('hero_image') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="sm:col-span-2">
                    @include('admin.events._description_editor', ['initialContent' => old('description', $event->description)])
                    @error('description') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Aturan & Ketentuan Lomba -->
                <div class="sm:col-span-2">
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-bold uppercase text-slate-400">
                            Aturan &amp; Ketentuan Lomba <span class="text-xs font-normal text-slate-500">(Ditampilkan di section khusus pada halaman event)</span>
                        </label>
                        <button type="button" 
                                onclick="const t = document.getElementById('rulesTermsInput'); if(!t.value.trim() || confirm('Isi dengan template ketentuan lomba default?')) { t.value = '1. Aktivitas lari/olahraga dapat dilakukan di mana saja (outdoor/indoor treadmill) secara mandiri.\n2. Catatan jarak dapat dicicil berkali-kali selama periode race berlangsung.\n3. Submit bukti aktivitas (screenshot Strava, Garmin, Polar, atau foto treadmill) melalui portal /submit.\n4. E-Certificate Finisher resmi otomatis terbit begitu progres target jarak mencapai 100%.\n5. Paket medali & jersey fisik dikirim via SPX Express ke alamat peserta setelah event berakhir.'; }"
                                class="text-[11px] font-bold text-[#00E5FF] hover:text-cyan-300 bg-cyan-950/40 hover:bg-cyan-900/40 px-2.5 py-1 rounded-lg border border-cyan-800/50 transition">
                            ⚡ Sisipkan Aturan Default
                        </button>
                    </div>
                    <textarea name="rules_and_terms" 
                              id="rulesTermsInput"
                              rows="5" 
                              placeholder="Tuliskan poin-poin aturan lomba, tata cara cicil jarak, dan ketentuan finisher..."
                              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 focus:border-[#00E5FF] leading-relaxed font-mono text-xs">{{ old('rules_and_terms', $event->rules_and_terms) }}</textarea>
                    @error('rules_and_terms') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <!-- SECTION 2: Jadwal & Periode -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-800">
                <span class="w-8 h-8 rounded-lg bg-cyan-500/20 text-[#00E5FF] font-athletic text-xl flex items-center justify-center">2</span>
                <div>
                    <h3 class="text-lg font-bold text-white">Jadwal Pendaftaran &amp; Periode Race</h3>
                    <p class="text-xs text-slate-400">Tentukan rentang tanggal buka pendaftaran dan pelaksanaan lari.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Buka Pendaftaran <span class="text-rose-400">*</span></label>
                    <input type="datetime-local" name="registration_start" value="{{ old('registration_start', $event->registration_start?->format('Y-m-d\TH:i')) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Tutup Pendaftaran <span class="text-rose-400">*</span></label>
                    <input type="datetime-local" name="registration_end" value="{{ old('registration_end', $event->registration_end?->format('Y-m-d\TH:i')) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Mulai Periode Lari (Race Start) <span class="text-rose-400">*</span></label>
                    <input type="datetime-local" name="race_start" value="{{ old('race_start', $event->race_start?->format('Y-m-d\TH:i')) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Selesai Periode Lari (Race End) <span class="text-rose-400">*</span></label>
                    <input type="datetime-local" name="race_end" value="{{ old('race_end', $event->race_end?->format('Y-m-d\TH:i')) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                </div>
            </div>
        </div>

        <!-- SECTION 3: Konfigurasi Meta Pixel & Conversions API (CAPI) -->
        <div class="bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950/40 border-2 border-indigo-500/40 rounded-3xl p-6 sm:p-8 shadow-2xl relative overflow-hidden">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-indigo-500/20">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-indigo-500/20 text-indigo-400 font-athletic text-xl flex items-center justify-center">3</span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-lg font-bold text-white">Konfigurasi Meta Pixel &amp; CAPI</h3>
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-indigo-500/20 text-indigo-300 border border-indigo-500/40">Facebook / Instagram Ads</span>
                        </div>
                        <p class="text-xs text-slate-400">Lacak konversi penjualan tiket secara akurat tanpa hambatan ad-blocker.</p>
                    </div>
                </div>

                <!-- Toggle Aktifkan CAPI -->
                <label class="inline-flex items-center cursor-pointer select-none">
                    <input type="checkbox" name="is_meta_capi_enabled" value="1" {{ old('is_meta_capi_enabled', $event->is_meta_capi_enabled) ? 'checked' : '' }} class="sr-only peer">
                    <div class="relative w-11 h-6 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600"></div>
                    <span class="ms-3 text-xs font-bold text-slate-300 uppercase tracking-wider">Aktifkan CAPI</span>
                </label>
            </div>

            <!-- Notice Box -->
            <div class="p-4 rounded-2xl bg-indigo-950/50 border border-indigo-500/30 text-xs text-indigo-200 leading-relaxed mb-6 flex items-start gap-3">
                <span class="text-xl">🎯</span>
                <div>
                    <strong class="text-white block mb-0.5">Aturan Pengiriman Event "Purchase":</strong>
                    Event <strong>Purchase</strong> server-side CAPI <u>HANYA</u> dikirim saat status pembayaran diverifikasi terbayar lunas (<strong>PAID</strong>) oleh payment gateway Tripay. Nilai nominal, ID referensi pembayaran, serta data peserta terenkripsi (SHA-256) otomatis dikirim ke Meta Events Manager.
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Meta Pixel ID</label>
                    <input type="text" name="meta_pixel_id" value="{{ old('meta_pixel_id', $event->meta_pixel_id) }}" placeholder="Contoh: 123456789012345"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white font-mono-num focus:border-indigo-500">
                    <p class="text-[11px] text-slate-500 mt-1">ID Kumpulan Data dari Events Manager Meta.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Conversions API Access Token (CAPI)</label>
                    <textarea name="meta_capi_token" rows="3" placeholder="Contoh: EAAG..."
                              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white font-mono-num focus:border-indigo-500">{{ old('meta_capi_token', $event->meta_capi_token) }}</textarea>
                    <p class="text-[11px] text-slate-500 mt-1">Token akses yang di-generate dari menu Konversi API.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Kode Test Event (Test Event Code - Opsional)</label>
                    <input type="text" name="meta_test_code" value="{{ old('meta_test_code', $event->meta_test_code) }}" placeholder="Contoh: TEST12345"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white font-mono-num focus:border-indigo-500">
                    <p class="text-[11px] text-slate-500 mt-1">Isi kode dari tab "Test Events" di Events Manager untuk melihat event langsung saat pengujian.</p>
                </div>
        </div>

        <!-- SECTION 4: Pengaturan Kategori Jarak -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 font-athletic text-xl flex items-center justify-center">4</span>
                    <div>
                        <h3 class="text-lg font-bold text-white">Pengaturan Kategori Jarak</h3>
                        <p class="text-xs text-slate-400">Kelola nama kategori, target jarak tempuh (KM), prefix nomor e-BIB, dan batasan kuota.</p>
                    </div>
                </div>
                <button type="button" id="btnAddCategory" class="px-3.5 py-2 rounded-xl text-xs font-bold text-emerald-400 bg-emerald-500/10 hover:bg-emerald-500/20 border border-emerald-500/30 transition flex items-center gap-1.5 self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Tambah Kategori Baru</span>
                </button>
            </div>

            <div id="categoriesContainer" class="space-y-4">
                @foreach($event->categories as $idx => $cat)
                    <div class="category-row p-4 sm:p-5 rounded-2xl bg-slate-950/80 border border-slate-800 relative transition hover:border-slate-700" data-index="{{ $idx }}">
                        <input type="hidden" name="categories[{{ $idx }}][id]" value="{{ $cat->id }}">
                        <input type="hidden" name="categories[{{ $idx }}][is_deleted]" value="0" class="is-deleted-input">

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3 pb-2 border-b border-slate-900">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                Kategori #{{ $loop->iteration }}
                                @if($cat->registered_count > 0)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-300 border border-emerald-500/20">
                                        {{ $cat->registered_count }} Peserta Terdaftar
                                    </span>
                                @endif
                            </span>
                            @if($cat->registrations()->count() === 0)
                                <button type="button" class="btn-remove-row text-xs text-rose-400 hover:text-rose-300 hover:underline flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Hapus Kategori</span>
                                </button>
                            @else
                                <span class="text-[11px] text-slate-500 italic">Sudah ada peserta (tidak dapat dihapus)</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Nama Kategori <span class="text-rose-400">*</span></label>
                                <input type="text" name="categories[{{ $idx }}][name]" value="{{ old('categories.'.$idx.'.name', $cat->name) }}" required placeholder="cth: 10K Challenge"
                                       class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-[#FF5500]">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Target Jarak (KM) <span class="text-rose-400">*</span></label>
                                <input type="number" step="0.1" name="categories[{{ $idx }}][target_distance_km]" value="{{ old('categories.'.$idx.'.target_distance_km', $cat->target_distance_km) }}" required placeholder="10.0"
                                       class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono-num focus:border-[#FF5500]">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Prefix BIB <span class="text-rose-400">*</span></label>
                                <input type="text" name="categories[{{ $idx }}][bib_prefix]" value="{{ old('categories.'.$idx.'.bib_prefix', $cat->bib_prefix) }}" required maxlength="10" placeholder="10K"
                                       class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono-num uppercase focus:border-[#FF5500]">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Kuota Peserta <span class="text-slate-500 font-normal">(Opsional)</span></label>
                                <input type="number" name="categories[{{ $idx }}][quota]" value="{{ old('categories.'.$idx.'.quota', $cat->quota) }}" placeholder="Tanpa Batas"
                                       class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono-num focus:border-[#FF5500]">
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- SECTION 5: Pengaturan Paket Pendaftaran & Race Pack -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6 pb-4 border-b border-slate-800">
                <div class="flex items-center gap-3">
                    <span class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 font-athletic text-xl flex items-center justify-center">5</span>
                    <div>
                        <h3 class="text-lg font-bold text-white">Pengaturan Paket Pendaftaran</h3>
                        <p class="text-xs text-slate-400">Atur harga tiket, berat fisik paket SPX Express, serta benefit medali &amp; jersey.</p>
                    </div>
                </div>
                <button type="button" id="btnAddPackage" class="px-3.5 py-2 rounded-xl text-xs font-bold text-amber-400 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-500/30 transition flex items-center gap-1.5 self-start sm:self-auto">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Tambah Paket Baru</span>
                </button>
            </div>

            <div id="packagesContainer" class="space-y-4">
                @foreach($event->packages as $pIdx => $pkg)
                    <div class="package-row p-4 sm:p-5 rounded-2xl bg-slate-950/80 border border-slate-800 relative transition hover:border-slate-700" data-index="{{ $pIdx }}">
                        <input type="hidden" name="packages[{{ $pIdx }}][id]" value="{{ $pkg->id }}">
                        <input type="hidden" name="packages[{{ $pIdx }}][is_deleted]" value="0" class="is-deleted-input">

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3 pb-2 border-b border-slate-900">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                Paket #{{ $loop->iteration }}
                            </span>
                            @if($pkg->registrations()->count() === 0)
                                <button type="button" class="btn-remove-row text-xs text-rose-400 hover:text-rose-300 hover:underline flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    <span>Hapus Paket</span>
                                </button>
                            @else
                                <span class="text-[11px] text-slate-500 italic">Sudah ada peserta (tidak dapat dihapus)</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mb-3">
                            <div class="sm:col-span-1">
                                <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Nama Paket <span class="text-rose-400">*</span></label>
                                <input type="text" name="packages[{{ $pIdx }}][name]" value="{{ old('packages.'.$pIdx.'.name', $pkg->name) }}" required placeholder="cth: Reguler (Medali Finisher)"
                                       class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-[#FF5500]">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Harga Tiket (Rp) <span class="text-rose-400">*</span></label>
                                <input type="number" step="1000" name="packages[{{ $pIdx }}][price]" value="{{ old('packages.'.$pIdx.'.price', (int)$pkg->price) }}" required placeholder="150000"
                                       class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono-num focus:border-[#FF5500]">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Berat Fisik SPX (Gram)</label>
                                <input type="number" name="packages[{{ $pIdx }}][base_weight_grams]" value="{{ old('packages.'.$pIdx.'.base_weight_grams', $pkg->base_weight_grams) }}" placeholder="300"
                                       class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono-num focus:border-[#FF5500]">
                            </div>
                        </div>

                        <!-- Isi & Benefit Paket (Custom Items) -->
                        <div class="mb-3">
                            <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">
                                Isi &amp; Benefit Paket (Item yang Didapat Peserta)
                                <span class="text-xs font-normal text-slate-500">(Bisa diketik custom, cth: e-BIB, E-Sertifikat Finisher, Jersey Event, Jersey Finisher, Medali)</span>
                            </label>
                            <input type="text" name="packages[{{ $pIdx }}][description]" value="{{ old('packages.'.$pIdx.'.description', $pkg->description) }}" placeholder="cth: e-BIB, E-Sertifikat Finisher, Jersey Event, Jersey Finisher, Medali"
                                   class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-[#FF5500]">
                        </div>

                        <!-- Checkbox Options -->
                        <div class="flex flex-wrap items-center gap-6 pt-2">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="packages[{{ $pIdx }}][requires_shipping]" value="1" {{ old('packages.'.$pIdx.'.requires_shipping', $pkg->requires_shipping) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-[#FF5500] focus:ring-0 bg-slate-900 border-slate-700">
                                <span class="text-xs text-slate-300">Wajib Pengiriman Fisik (SPX Express)</span>
                            </label>

                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="packages[{{ $pIdx }}][includes_medal]" value="1" {{ old('packages.'.$pIdx.'.includes_medal', $pkg->includes_medal) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-[#FF5500] focus:ring-0 bg-slate-900 border-slate-700">
                                <span class="text-xs text-slate-300">Termasuk Medali Finisher</span>
                            </label>

                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" name="packages[{{ $pIdx }}][includes_jersey]" value="1" {{ old('packages.'.$pIdx.'.includes_jersey', $pkg->includes_jersey) ? 'checked' : '' }}
                                       class="w-4 h-4 rounded text-[#FF5500] focus:ring-0 bg-slate-900 border-slate-700">
                                <span class="text-xs text-slate-300">Termasuk Jersey Dry-Fit</span>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-between pt-4">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $event->is_active) ? 'checked' : '' }} class="w-4 h-4 rounded text-[#FF5500] focus:ring-0 bg-slate-950 border-slate-800">
                <span class="text-xs text-slate-300 font-bold uppercase tracking-wider">Status Event Aktif</span>
            </label>

            <button type="submit" class="px-8 py-3.5 rounded-xl font-bold uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-lg shadow-orange-950/50 glow-orange flex items-center gap-2">
                <span>Perbarui Event &amp; Simpan CAPI</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            </button>
        </div>
    </form>

    <!-- Danger Zone: Hapus Event -->
    <div class="mt-12 p-6 rounded-3xl bg-rose-950/20 border border-rose-900/50 shadow-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h4 class="text-sm font-bold text-rose-400 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Zona Berbahaya: Hapus Event</span>
                </h4>
                <p class="text-xs text-slate-400 mt-1 max-w-xl">
                    Menghapus event ini akan menghapus seluruh data pendaftaran peserta, kategori jarak, paket, dan desain template terkait secara permanen.
                </p>
            </div>

            <form action="{{ route('admin.events.destroy', $event) }}" method="POST" onsubmit="return confirm('PERINGATAN: Apakah Anda benar-benar yakin ingin menghapus event \'{{ addslashes($event->title) }}\'?\n\nTindakan ini permanen dan seluruh data terkait akan ikut terhapus.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-xs font-bold uppercase tracking-wider text-white bg-rose-600 hover:bg-rose-700 transition shadow-lg shadow-rose-950/60 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>Hapus Event Ini</span>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const heroImageInput = document.getElementById('heroImageInput');
    const dropzoneEmpty = document.getElementById('dropzoneEmpty');
    const dropzonePreview = document.getElementById('dropzonePreview');
    const previewImg = document.getElementById('previewImg');
    const removeImgBtn = document.getElementById('removeImgBtn');

    if (heroImageInput) {
        heroImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(evt) {
                    previewImg.src = evt.target.result;
                    dropzoneEmpty.classList.add('hidden');
                    dropzoneEmpty.classList.remove('flex');
                    dropzonePreview.classList.remove('hidden');
                    dropzonePreview.classList.add('flex');
                };
                reader.readAsDataURL(file);
            }
        });
    }

    if (removeImgBtn) {
        removeImgBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            heroImageInput.value = '';
            previewImg.src = '#';
            dropzonePreview.classList.add('hidden');
            dropzonePreview.classList.remove('flex');
            dropzoneEmpty.classList.remove('hidden');
            dropzoneEmpty.classList.add('flex');
        });
    }

    // Dynamic Categories & Packages Handler
    const categoriesContainer = document.getElementById('categoriesContainer');
    const packagesContainer = document.getElementById('packagesContainer');
    const btnAddCategory = document.getElementById('btnAddCategory');
    const btnAddPackage = document.getElementById('btnAddPackage');

    let catIndex = document.querySelectorAll('.category-row').length + 10;
    let pkgIndex = document.querySelectorAll('.package-row').length + 10;

    // Remove row handler (delegated)
    document.addEventListener('click', function(e) {
        const btnRemove = e.target.closest('.btn-remove-row');
        if (btnRemove) {
            e.preventDefault();
            const row = btnRemove.closest('.category-row, .package-row');
            if (row) {
                if (confirm('Apakah Anda yakin ingin menghapus baris ini?')) {
                    const isDeletedInput = row.querySelector('.is-deleted-input');
                    if (isDeletedInput) {
                        isDeletedInput.value = '1';
                        row.classList.add('hidden');
                    } else {
                        row.remove();
                    }
                }
            }
        }
    });

    if (btnAddCategory && categoriesContainer) {
        btnAddCategory.addEventListener('click', function() {
            catIndex++;
            const newCatHtml = `
                <div class="category-row p-4 sm:p-5 rounded-2xl bg-slate-950/80 border border-emerald-500/40 relative transition" data-index="${catIndex}">
                    <input type="hidden" name="categories[${catIndex}][id]" value="">
                    <input type="hidden" name="categories[${catIndex}][is_deleted]" value="0" class="is-deleted-input">

                    <div class="flex items-center justify-between gap-2 mb-3 pb-2 border-b border-slate-900">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-400 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                            Kategori Baru
                        </span>
                        <button type="button" class="btn-remove-row text-xs text-rose-400 hover:text-rose-300 hover:underline flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Batal / Hapus</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3.5">
                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Nama Kategori <span class="text-rose-400">*</span></label>
                            <input type="text" name="categories[${catIndex}][name]" required placeholder="cth: 21K Half Marathon"
                                   class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-[#FF5500]">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Target Jarak (KM) <span class="text-rose-400">*</span></label>
                            <input type="number" step="0.1" name="categories[${catIndex}][target_distance_km]" required placeholder="21.1"
                                   class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono-num focus:border-[#FF5500]">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Prefix BIB <span class="text-rose-400">*</span></label>
                            <input type="text" name="categories[${catIndex}][bib_prefix]" required maxlength="10" placeholder="21K"
                                   class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono-num uppercase focus:border-[#FF5500]">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Kuota Peserta <span class="text-slate-500 font-normal">(Opsional)</span></label>
                            <input type="number" name="categories[${catIndex}][quota]" placeholder="Tanpa Batas"
                                   class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono-num focus:border-[#FF5500]">
                        </div>
                    </div>
                </div>
            `;
            categoriesContainer.insertAdjacentHTML('beforeend', newCatHtml);
        });
    }

    if (btnAddPackage && packagesContainer) {
        btnAddPackage.addEventListener('click', function() {
            pkgIndex++;
            const newPkgHtml = `
                <div class="package-row p-4 sm:p-5 rounded-2xl bg-slate-950/80 border border-amber-500/40 relative transition" data-index="${pkgIndex}">
                    <input type="hidden" name="packages[${pkgIndex}][id]" value="">
                    <input type="hidden" name="packages[${pkgIndex}][is_deleted]" value="0" class="is-deleted-input">

                    <div class="flex items-center justify-between gap-2 mb-3 pb-2 border-b border-slate-900">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-400 flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                            Paket Baru
                        </span>
                        <button type="button" class="btn-remove-row text-xs text-rose-400 hover:text-rose-300 hover:underline flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Batal / Hapus</span>
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3.5 mb-3">
                        <div class="sm:col-span-1">
                            <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Nama Paket <span class="text-rose-400">*</span></label>
                            <input type="text" name="packages[${pkgIndex}][name]" required placeholder="cth: Finisher Jersey Only"
                                   class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-[#FF5500]">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Harga Tiket (Rp) <span class="text-rose-400">*</span></label>
                            <input type="number" step="1000" name="packages[${pkgIndex}][price]" required placeholder="125000"
                                   class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono-num focus:border-[#FF5500]">
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">Berat Fisik SPX (Gram)</label>
                            <input type="number" name="packages[${pkgIndex}][base_weight_grams]" value="250" placeholder="250"
                                   class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white font-mono-num focus:border-[#FF5500]">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="block text-[11px] font-bold uppercase text-slate-400 mb-1">
                            Isi &amp; Benefit Paket (Item yang Didapat Peserta)
                            <span class="text-xs font-normal text-slate-500">(Bisa diketik custom)</span>
                        </label>
                        <input type="text" name="packages[${pkgIndex}][description]" value="e-BIB Digital, E-Sertifikat Finisher, Jersey Event, Medali" placeholder="cth: e-BIB, E-Sertifikat Finisher, Jersey Event, Jersey Finisher, Medali"
                               class="w-full bg-slate-900 border border-slate-700/80 rounded-xl px-3.5 py-2.5 text-xs text-white focus:border-[#FF5500]">
                    </div>

                    <div class="flex flex-wrap items-center gap-6 pt-2">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="packages[${pkgIndex}][requires_shipping]" value="1" checked
                                   class="w-4 h-4 rounded text-[#FF5500] focus:ring-0 bg-slate-900 border-slate-700">
                            <span class="text-xs text-slate-300">Wajib Pengiriman Fisik (SPX Express)</span>
                        </label>

                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="packages[${pkgIndex}][includes_medal]" value="1"
                                   class="w-4 h-4 rounded text-[#FF5500] focus:ring-0 bg-slate-900 border-slate-700">
                            <span class="text-xs text-slate-300">Termasuk Medali Finisher</span>
                        </label>

                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="packages[${pkgIndex}][includes_jersey]" value="1" checked
                                   class="w-4 h-4 rounded text-[#FF5500] focus:ring-0 bg-slate-900 border-slate-700">
                            <span class="text-xs text-slate-300">Termasuk Jersey Dry-Fit</span>
                        </label>
                    </div>
                </div>
            `;
            packagesContainer.insertAdjacentHTML('beforeend', newPkgHtml);
        });
    }
});
</script>
@endpush

