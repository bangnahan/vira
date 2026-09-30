@extends('layouts.app')

@section('title', 'Buat Event Baru & Konfigurasi Meta Pixel CAPI — VIRA Admin')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <!-- Breadcrumb & Title -->
    <div class="mb-8">
        <div class="flex items-center gap-2 text-xs text-slate-400 mb-2">
            <a href="{{ route('admin.events.index') }}" class="hover:text-white transition">Admin Events</a>
            <span>/</span>
            <span class="text-[#FF5500]">Buat Event Baru</span>
        </div>
        <h1 class="text-3xl font-extrabold text-white font-athletic tracking-wide">Buat Event Baru</h1>
        <p class="text-xs text-slate-400">Atur rincian event olahraga virtual dan integrasikan server-side Meta Conversions API (CAPI).</p>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-2xl bg-rose-950/80 border border-rose-500/50 text-rose-200 text-xs space-y-1.5 shadow-xl">
            <div class="flex items-center gap-2 font-bold text-rose-300 text-sm">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Gagal Menyimpan Event - Periksa Input Berikut:</span>
            </div>
            @foreach($errors->all() as $error)
                <p class="pl-7 text-rose-300/90">• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.events.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
        @csrf

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
                    <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Bandung Ultra Virtual Run 2026"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                    @error('title') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Kode Event Unik (Prefix BIB) <span class="text-rose-400">*</span></label>
                    <input type="text" name="event_code" value="{{ old('event_code') }}" required placeholder="Contoh: BVR26" uppercase maxlength="10"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white font-mono-num font-bold focus:border-[#FF5500]">
                    <p class="text-[11px] text-slate-500 mt-1">Digunakan untuk nomor e-BIB resmi (e.g. BVR26-10K-0001).</p>
                    @error('event_code') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Custom Slug URL (Opsional)</label>
                    <input type="text" name="slug" value="{{ old('slug') }}" placeholder="bandung-ultra-virtual-run-2026"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                    @error('slug') <p class="text-rose-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Jenis Olahraga (Activity Type) <span class="text-rose-400">*</span></label>
                    <select name="activity_type" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                        <option value="RUN" {{ old('activity_type') === 'RUN' ? 'selected' : '' }}>Lari (Virtual Run)</option>
                        <option value="RIDE" {{ old('activity_type') === 'RIDE' ? 'selected' : '' }}>Sepeda (Virtual Ride)</option>
                        <option value="WALK" {{ old('activity_type') === 'WALK' ? 'selected' : '' }}>Jalan Santai (Virtual Walk)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Metode Submission Hasil <span class="text-rose-400">*</span></label>
                    <select name="submission_mode" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                        <option value="CUMULATIVE" {{ old('submission_mode') === 'CUMULATIVE' ? 'selected' : '' }}>Akumulasi Jarak (Cicil Berkali-kali)</option>
                        <option value="SINGLE" {{ old('submission_mode') === 'SINGLE' ? 'selected' : '' }}>Satu Sesi Tunggal (Single Activity)</option>
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Tipe Event</label>
                    <select name="race_type" required class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                        <option value="CHALLENGE" {{ old('race_type') === 'CHALLENGE' ? 'selected' : '' }}>Tantangan Target (Challenge / Finisher)</option>
                        <option value="RACE" {{ old('race_type') === 'RACE' ? 'selected' : '' }}>Kompetisi Kecepatan (Pace Ranking)</option>
                    </select>
                </div>

                <!-- Hero Image Upload & URL Section -->
                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">
                        Hero Banner Gambar Event <span class="text-xs font-normal text-slate-500">(Upload File)</span>
                    </label>

                    <div class="space-y-3">
                        <!-- Dropzone File Upload -->
                        <div id="dropzoneContainer" 
                             class="relative border-2 border-dashed border-slate-700 hover:border-[#FF5500] rounded-2xl p-6 text-center bg-slate-950/60 transition cursor-pointer group">
                            
                            <input type="file" 
                                   id="heroImageInput" 
                                   name="hero_image" 
                                   accept="image/png,image/jpeg,image/webp" 
                                   class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">

                            <!-- Empty State Display -->
                            <div id="dropzoneEmpty" class="flex flex-col items-center justify-center py-4">
                                <div class="w-14 h-14 rounded-2xl bg-orange-500/10 text-[#FF5500] flex items-center justify-center mb-3 group-hover:scale-110 transition duration-200">
                                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                                <span class="text-sm font-bold text-white block">Klik atau Seret &amp; Lepas File Hero Image di sini</span>
                                <span class="text-xs text-slate-400 mt-1">Format: JPG, PNG, atau WebP (Maksimal 5MB). Rekomendasi: 1920 &times; 800 px (16:9).</span>
                            </div>

                            <!-- Image Preview Display (Hidden initially) -->
                            <div id="dropzonePreview" class="hidden relative rounded-xl overflow-hidden max-h-72 border border-slate-800 bg-slate-900 flex items-center justify-center">
                                <img id="previewImg" src="#" alt="Hero Image Preview" class="w-full h-auto max-h-72 object-cover rounded-xl">
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
                    @include('admin.events._description_editor', ['initialContent' => old('description')])
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
                              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-slate-200 focus:border-[#00E5FF] leading-relaxed font-mono text-xs">{{ old('rules_and_terms') }}</textarea>
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
                    <input type="datetime-local" name="registration_start" value="{{ old('registration_start', now()->format('Y-m-d\TH:i')) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Tutup Pendaftaran <span class="text-rose-400">*</span></label>
                    <input type="datetime-local" name="registration_end" value="{{ old('registration_end', now()->addDays(30)->format('Y-m-d\TH:i')) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Mulai Periode Lari (Race Start) <span class="text-rose-400">*</span></label>
                    <input type="datetime-local" name="race_start" value="{{ old('race_start', now()->format('Y-m-d\TH:i')) }}" required
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Selesai Periode Lari (Race End) <span class="text-rose-400">*</span></label>
                    <input type="datetime-local" name="race_end" value="{{ old('race_end', now()->addDays(30)->format('Y-m-d\TH:i')) }}" required
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
                    <input type="checkbox" name="is_meta_capi_enabled" value="1" {{ old('is_meta_capi_enabled', true) ? 'checked' : '' }} class="sr-only peer">
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
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Meta Pixel ID <span class="text-indigo-400">*</span></label>
                    <input type="text" name="meta_pixel_id" value="{{ old('meta_pixel_id') }}" placeholder="Contoh: 123456789012345"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white font-mono-num focus:border-indigo-500">
                    <p class="text-[11px] text-slate-500 mt-1">Ditemukan di Meta Business Suite &gt; Events Manager &gt; Pengaturan Pixel &gt; ID Kumpulan Data.</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Conversions API Access Token (CAPI) <span class="text-indigo-400">*</span></label>
                    <textarea name="meta_capi_token" rows="3" placeholder="Contoh: EAAG..."
                              class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-xs text-white font-mono-num focus:border-indigo-500">{{ old('meta_capi_token') }}</textarea>
                    <p class="text-[11px] text-slate-500 mt-1">Generate di Events Manager &gt; Pengaturan &gt; Konversi API &gt; "Buat Token Akses".</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Kode Test Event (Test Event Code - Opsional)</label>
                    <input type="text" name="meta_test_code" value="{{ old('meta_test_code') }}" placeholder="Contoh: TEST12345"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white font-mono-num focus:border-indigo-500">
                    <p class="text-[11px] text-slate-500 mt-1">Gunakan kode ini saat melakukan pengujian di tab "Uji Peristiwa" (Test Events) di Meta Events Manager. Kosongkan saat live campaign.</p>
                </div>
            </div>
        </div>

        <!-- SECTION 4: Kategori & Paket Awal -->
        <div class="bg-slate-900/90 border border-slate-800 rounded-3xl p-6 sm:p-8 shadow-xl">
            <div class="flex items-center gap-3 mb-6 pb-4 border-b border-slate-800">
                <span class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 font-athletic text-xl flex items-center justify-center">4</span>
                <div>
                    <h3 class="text-lg font-bold text-white">Kategori Jarak &amp; Paket Pendaftaran Awal</h3>
                    <p class="text-xs text-slate-400">Kategori dan paket default yang langsung aktif untuk event ini.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nama Kategori Jarak</label>
                    <input type="text" name="category_name" value="{{ old('category_name', '10K Challenge') }}" placeholder="10K Challenge"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Target Jarak (KM)</label>
                    <input type="number" step="0.1" name="target_distance_km" value="{{ old('target_distance_km', '10.0') }}" placeholder="10.0"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white font-mono-num focus:border-[#FF5500]">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Prefix Nomor BIB</label>
                    <input type="text" name="bib_prefix" value="{{ old('bib_prefix', '10K') }}" placeholder="10K / 5K / 21K"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white font-mono-num uppercase focus:border-[#FF5500]">
                    <p class="text-[11px] text-slate-500 mt-1">Format BIB: KODE-PREFIX-0001 (cth: YGY-10K-0001).</p>
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Nama Paket Pendaftaran</label>
                    <input type="text" name="package_name" value="{{ old('package_name', 'Reguler (Medali Finisher)') }}" placeholder="Reguler"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">Harga Tiket Paket (Rp)</label>
                    <input type="number" step="1000" name="package_price" value="{{ old('package_price', '150000') }}" placeholder="150000"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white font-mono-num focus:border-[#FF5500]">
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-xs font-bold uppercase text-slate-400 mb-1.5">
                        Isi &amp; Benefit Paket (Item yang Didapat Peserta)
                        <span class="text-xs font-normal text-slate-500">(Bisa diketik custom)</span>
                    </label>
                    <input type="text" name="package_description" value="{{ old('package_description', 'e-BIB Digital, E-Sertifikat Finisher, Medali Logam Cor') }}" placeholder="cth: e-BIB, E-Sertifikat Finisher, Jersey Event, Jersey Finisher, Medali"
                           class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-3 text-sm text-white focus:border-[#FF5500]">
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex items-center justify-between pt-4">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }} class="w-4 h-4 rounded text-[#FF5500] focus:ring-0 bg-slate-950 border-slate-800">
                <span class="text-xs text-slate-300 font-bold uppercase tracking-wider">Langsung Publikasikan Event (Aktif)</span>
            </label>

            <button type="submit" class="px-8 py-3.5 rounded-xl font-bold uppercase tracking-wider text-white bg-gradient-to-r from-[#FF5500] to-[#FF7700] hover:from-[#FF6600] hover:to-[#FF8800] transition shadow-lg shadow-orange-950/50 glow-orange flex items-center gap-2">
                <span>Simpan Event &amp; Aktifkan CAPI</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </div>
    </form>
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
                    dropzonePreview.classList.remove('hidden');
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
            dropzoneEmpty.classList.remove('hidden');
        });
    }
});
</script>
@endpush

