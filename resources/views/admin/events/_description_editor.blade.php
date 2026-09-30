<div class="space-y-2" id="richEditorWrapper">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-1">
        <label class="block text-xs font-bold uppercase text-slate-400">
            Deskripsi Lengkap Event <span class="text-xs font-normal text-slate-500">(Mendukung Headline, Bold, Garis Miring, Tipis &amp; Upload Gambar)</span>
        </label>
        <div class="flex items-center gap-2">
            <button type="button" id="btnInsertTemplate" class="text-[11px] font-bold text-[#FF5500] hover:text-orange-400 bg-orange-500/10 hover:bg-orange-500/20 px-2.5 py-1 rounded-lg border border-orange-500/30 transition flex items-center gap-1">
                <span>⚡ Sisipkan Template Lengkap</span>
            </button>
            <button type="button" id="btnToggleSource" class="text-[11px] font-bold text-slate-400 hover:text-white bg-slate-800 hover:bg-slate-700 px-2.5 py-1 rounded-lg border border-slate-700 transition">
                &lt;/&gt; HTML Source
            </button>
        </div>
    </div>

    <!-- WYSIWYG Toolbar -->
    <div class="bg-slate-900 border border-slate-700/80 rounded-t-2xl p-2 sm:p-2.5 flex flex-wrap items-center gap-1.5 select-none" id="editorToolbar">
        <!-- Headings Dropdown -->
        <select id="headingSelect" class="bg-slate-950 border border-slate-700 text-slate-200 text-xs rounded-lg px-2.5 py-1.5 focus:border-[#FF5500] focus:ring-0">
            <option value="p">Normal (Paragraf)</option>
            <option value="h1">Headline 1 (Besar)</option>
            <option value="h2">Headline 2 (Sub-Judul)</option>
            <option value="h3">Headline 3 (Bagian)</option>
            <option value="blockquote">Kutipan / Box</option>
        </select>

        <div class="h-5 w-px bg-slate-800 mx-0.5"></div>

        <!-- Basic Formats -->
        <button type="button" data-cmd="bold" class="editor-btn p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition font-bold text-sm w-8 h-8 flex items-center justify-center" title="Tebal (Bold)">
            <b>B</b>
        </button>
        <button type="button" data-cmd="italic" class="editor-btn p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition italic text-sm w-8 h-8 flex items-center justify-center" title="Garis Miring (Italic)">
            <i>I</i>
        </button>
        <button type="button" id="btnThinText" class="editor-btn px-2 py-1 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800 transition text-xs font-light" title="Tulisan Tipis (Light / Subtitle)">
            Tipis
        </button>
        <button type="button" data-cmd="underline" class="editor-btn p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition underline text-sm w-8 h-8 flex items-center justify-center" title="Garis Bawah (Underline)">
            <u>U</u>
        </button>
        <button type="button" data-cmd="strikeThrough" class="editor-btn p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition line-through text-sm w-8 h-8 flex items-center justify-center" title="Coret (Strikethrough)">
            <s>S</s>
        </button>

        <div class="h-5 w-px bg-slate-800 mx-0.5"></div>

        <!-- Highlight Colors -->
        <div class="flex items-center gap-1">
            <button type="button" class="color-btn w-6 h-6 rounded-md bg-[#FF5500] border border-orange-400/40 hover:scale-110 transition shadow" data-color="#FF5500" title="Warna Oranye VIRA"></button>
            <button type="button" class="color-btn w-6 h-6 rounded-md bg-[#00E5FF] border border-cyan-400/40 hover:scale-110 transition shadow" data-color="#00E5FF" title="Warna Cyan"></button>
            <button type="button" class="color-btn w-6 h-6 rounded-md bg-[#FFD700] border border-amber-400/40 hover:scale-110 transition shadow" data-color="#FFD700" title="Warna Emas / Kuning"></button>
            <button type="button" class="color-btn w-6 h-6 rounded-md bg-slate-200 border border-slate-400 hover:scale-110 transition shadow" data-color="#F8FAFC" title="Warna Putih / Reset"></button>
        </div>

        <div class="h-5 w-px bg-slate-800 mx-0.5"></div>

        <!-- Lists -->
        <button type="button" data-cmd="insertUnorderedList" class="editor-btn p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition text-xs flex items-center justify-center w-8 h-8" title="Bullet List">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16M2 6h.01M2 12h.01M2 18h.01"/></svg>
        </button>
        <button type="button" data-cmd="insertOrderedList" class="editor-btn p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition text-xs flex items-center justify-center w-8 h-8" title="Numbered List">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 6h13M7 12h13M7 18h13M3 6h1v4H3m0 4h2a1 1 0 011 1v1a1 1 0 01-1 1H3m0 0h3"/></svg>
        </button>

        <div class="h-5 w-px bg-slate-800 mx-0.5"></div>

        <!-- Alignment -->
        <button type="button" data-cmd="justifyLeft" class="editor-btn p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition text-xs w-8 h-8 flex items-center justify-center" title="Rata Kiri">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h10M4 18h14"/></svg>
        </button>
        <button type="button" data-cmd="justifyCenter" class="editor-btn p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition text-xs w-8 h-8 flex items-center justify-center" title="Rata Tengah">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M7 12h10M5 18h14"/></svg>
        </button>

        <div class="h-5 w-px bg-slate-800 mx-0.5"></div>

        <!-- Link -->
        <button type="button" id="btnInsertLink" class="editor-btn p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition text-xs w-8 h-8 flex items-center justify-center" title="Sisipkan Link">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
        </button>

        <!-- Divider Line -->
        <button type="button" data-cmd="insertHorizontalRule" class="editor-btn p-1.5 rounded-lg text-slate-300 hover:text-white hover:bg-slate-800 transition text-xs w-8 h-8 flex items-center justify-center" title="Garis Pemisah (Horizontal Rule)">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 12h16"/></svg>
        </button>

        <!-- Image Upload Buttons -->
        <div class="flex items-center gap-1.5 ml-auto">
            <!-- Hidden file input for uploading images -->
            <input type="file" id="imageFileInput" accept="image/jpeg,image/png,image/webp,image/gif" class="hidden">
            
            <button type="button" id="btnUploadImage" class="px-2.5 py-1.5 rounded-lg bg-[#FF5500] hover:bg-[#FF6600] text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-orange-950/40">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span>+ Upload Gambar</span>
            </button>
        </div>
    </div>

    <!-- Upload Progress Indicator -->
    <div id="imageUploadProgress" class="hidden px-4 py-2 bg-orange-950/60 border-x border-orange-500/40 text-orange-300 text-xs flex items-center gap-2">
        <svg class="animate-spin h-4 w-4 text-orange-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
        <span>Sedang mengunggah gambar ke server... Mohon tunggu.</span>
    </div>

    <!-- Contenteditable Visual Editor -->
    <div id="visualEditor" 
         contenteditable="true" 
         class="w-full bg-slate-950 border border-slate-700/80 rounded-b-2xl p-4 sm:p-6 min-h-[280px] max-h-[640px] overflow-y-auto text-slate-200 text-sm leading-relaxed focus:outline-none focus:border-[#FF5500] prose prose-invert max-w-none">
        {!! $initialContent ?? '' !!}
    </div>

    <!-- Raw HTML Textarea (Hidden by default, shown in HTML mode) -->
    <textarea name="description" 
              id="hiddenDescriptionInput" 
              class="hidden w-full bg-slate-950 border border-slate-700/80 rounded-b-2xl p-4 font-mono text-xs text-cyan-300 min-h-[280px] max-h-[640px] focus:outline-none focus:border-[#FF5500]">{!! $initialContent ?? '' !!}</textarea>

    <div class="flex items-center justify-between text-[11px] text-slate-500 px-1 pt-1">
        <span>Gunakan format headline, bold, teks tipis, list, dan upload gambar untuk memperkaya halaman event.</span>
        <span id="charCountDisplay">0 karakter</span>
    </div>
</div>

@push('styles')
<style>
/* Styling inside the visual editor */
#visualEditor {
    min-height: 280px;
}
#visualEditor:empty:before {
    content: "Tuliskan deskripsi lengkap event di sini... Anda bisa mengetik judul, teks tebal, garis miring, atau klik '+ Upload Gambar' untuk menyisipkan banner / foto medali / jersey.";
    color: #64748b;
    pointer-events: none;
    display: block;
}
#visualEditor h1 {
    font-size: 1.85rem;
    font-weight: 800;
    color: #ffffff;
    margin-top: 1.25rem;
    margin-bottom: 0.5rem;
    line-height: 1.2;
}
#visualEditor h2 {
    font-size: 1.5rem;
    font-weight: 700;
    color: #ffffff;
    margin-top: 1rem;
    margin-bottom: 0.5rem;
    line-height: 1.3;
}
#visualEditor h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #f1f5f9;
    margin-top: 0.75rem;
    margin-bottom: 0.25rem;
}
#visualEditor p {
    margin-bottom: 0.75rem;
    line-height: 1.6;
}
#visualEditor ul {
    list-style-type: disc;
    margin-left: 1.5rem;
    margin-bottom: 0.75rem;
}
#visualEditor ol {
    list-style-type: decimal;
    margin-left: 1.5rem;
    margin-bottom: 0.75rem;
}
#visualEditor li {
    margin-bottom: 0.25rem;
}
#visualEditor blockquote {
    border-left: 4px solid #FF5500;
    padding-left: 1rem;
    margin: 1rem 0;
    font-style: italic;
    color: #cbd5e1;
    background: rgba(15, 23, 42, 0.6);
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    border-radius: 0 0.5rem 0.5rem 0;
}
#visualEditor img {
    max-width: 100%;
    height: auto;
    border-radius: 1rem;
    margin: 1rem 0;
    border: 1px solid #334155;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
}
#visualEditor a {
    color: #00E5FF;
    text-decoration: underline;
}
#visualEditor hr {
    border-color: #334155;
    margin: 1.5rem 0;
}
.font-light-sub {
    font-weight: 300 !important;
    color: #94a3b8 !important;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const visualEditor = document.getElementById('visualEditor');
    const hiddenInput = document.getElementById('hiddenDescriptionInput');
    const headingSelect = document.getElementById('headingSelect');
    const imageFileInput = document.getElementById('imageFileInput');
    const btnUploadImage = document.getElementById('btnUploadImage');
    const btnInsertLink = document.getElementById('btnInsertLink');
    const btnThinText = document.getElementById('btnThinText');
    const btnToggleSource = document.getElementById('btnToggleSource');
    const btnInsertTemplate = document.getElementById('btnInsertTemplate');
    const uploadProgress = document.getElementById('imageUploadProgress');
    const charCountDisplay = document.getElementById('charCountDisplay');

    let isSourceMode = false;

    // Sinkronisasi data ke hidden textarea
    function syncToHidden() {
        if (!isSourceMode) {
            hiddenInput.value = visualEditor.innerHTML;
        } else {
            visualEditor.innerHTML = hiddenInput.value;
        }
        updateCharCount();
    }

    function updateCharCount() {
        const text = visualEditor.innerText || '';
        charCountDisplay.textContent = text.trim().length + ' karakter';
    }

    visualEditor.addEventListener('input', syncToHidden);
    visualEditor.addEventListener('keyup', syncToHidden);
    visualEditor.addEventListener('paste', function() {
        setTimeout(syncToHidden, 50);
    });

    // Formatting Toolbar standard commands
    document.querySelectorAll('#editorToolbar .editor-btn[data-cmd]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const cmd = this.dataset.cmd;
            document.execCommand(cmd, false, null);
            visualEditor.focus();
            syncToHidden();
        });
    });

    // Headings dropdown
    headingSelect.addEventListener('change', function() {
        const val = this.value;
        if (val === 'p') {
            document.execCommand('formatBlock', false, '<p>');
        } else if (val === 'blockquote') {
            document.execCommand('formatBlock', false, '<blockquote>');
        } else {
            document.execCommand('formatBlock', false, `<${val}>`);
        }
        visualEditor.focus();
        syncToHidden();
        this.value = 'p';
    });

    // Tulisan Tipis (Light / Subtitle)
    if (btnThinText) {
        btnThinText.addEventListener('click', function(e) {
            e.preventDefault();
            const selection = window.getSelection();
            if (!selection.rangeCount || selection.isCollapsed) {
                alert('Silakan blok/sorot tulisan yang ingin dijadikan tulisan tipis terlebih dahulu.');
                return;
            }
            const range = selection.getRangeAt(0);
            const span = document.createElement('span');
            span.className = 'font-light-sub';
            span.appendChild(range.extractContents());
            range.insertNode(span);
            visualEditor.focus();
            syncToHidden();
        });
    }

    // Color highlights
    document.querySelectorAll('.color-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const color = this.dataset.color;
            document.execCommand('foreColor', false, color);
            visualEditor.focus();
            syncToHidden();
        });
    });

    // Link insert
    if (btnInsertLink) {
        btnInsertLink.addEventListener('click', function(e) {
            e.preventDefault();
            const url = prompt('Masukkan URL Link (contoh: https://instagram.com/namakomunitas):');
            if (url) {
                document.execCommand('createLink', false, url);
                visualEditor.focus();
                syncToHidden();
            }
        });
    }

    // Upload Image from Computer / Phone
    if (btnUploadImage && imageFileInput) {
        btnUploadImage.addEventListener('click', function() {
            imageFileInput.click();
        });

        imageFileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            // Validasi ukuran < 5MB
            if (file.size > 5 * 1024 * 1024) {
                alert('Ukuran file gambar maksimal 5MB.');
                this.value = '';
                return;
            }

            const formData = new FormData();
            formData.append('image', file);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

            uploadProgress.classList.remove('hidden');

            fetch('{{ route("admin.events.upload-description-image") }}', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                uploadProgress.classList.add('hidden');
                if (data.status === 'success' && data.url) {
                    insertImageHtml(data.url, file.name);
                } else {
                    alert('Gagal mengunggah gambar: ' + (data.message || 'Terjadi kesalahan sistem.'));
                }
                imageFileInput.value = '';
            })
            .catch(err => {
                uploadProgress.classList.add('hidden');
                alert('Gagal mengunggah gambar. Pastikan format file sesuai.');
                imageFileInput.value = '';
            });
        });
    }

    function insertImageHtml(url, altText) {
        visualEditor.focus();
        const imgHtml = `<p><img src="${url}" alt="${altText || 'Gambar Event'}" class="rounded-2xl max-w-full h-auto my-4 border border-slate-800 shadow-xl" /></p><p><br></p>`;
        document.execCommand('insertHTML', false, imgHtml);
        syncToHidden();
    }

    // Toggle Source Code HTML
    if (btnToggleSource) {
        btnToggleSource.addEventListener('click', function(e) {
            e.preventDefault();
            isSourceMode = !isSourceMode;
            if (isSourceMode) {
                hiddenInput.value = visualEditor.innerHTML;
                visualEditor.classList.add('hidden');
                hiddenInput.classList.remove('hidden');
                this.textContent = '👁️ Mode Visual';
                this.classList.add('text-[#FF5500]', 'border-orange-500/50');
            } else {
                visualEditor.innerHTML = hiddenInput.value;
                hiddenInput.classList.add('hidden');
                visualEditor.classList.remove('hidden');
                this.textContent = '</> HTML Source';
                this.classList.remove('text-[#FF5500]', 'border-orange-500/50');
            }
        });
    }

    // Template Cepat Deskripsi Lomba
    if (btnInsertTemplate) {
        btnInsertTemplate.addEventListener('click', function(e) {
            e.preventDefault();
            if (visualEditor.innerText.trim().length > 10 && !confirm('Ganti konten saat ini dengan Template Lengkap Informasi Event?')) {
                return;
            }

            const template = `
                <h2>Tentang Event</h2>
                <p>Selamat datang di ajang virtual sport resmi persembahan VIRA! Tantang batas kemampuan larimu kapan saja dan di mana saja selama periode race berlangsung.</p>
                
                <h3>Ketentuan Lomba &amp; Pencatatan Jarak</h3>
                <ul>
                    <li><strong>Mode Lari:</strong> Bebas di outdoor (jalanan/taman) atau indoor menggunakan treadmill.</li>
                    <li><strong>Aplikasi Tracking:</strong> Gunakan Strava, Garmin, Polar, Apple Health, atau foto display treadmill.</li>
                    <li><strong>Submit Hasil:</strong> Cukup buka menu <em>/submit</em> dan masukkan nomor e-BIB resmi Anda.</li>
                </ul>

                <blockquote>
                    <strong>Tips Atlet:</strong> Jangan lupa untuk selalu melakukan pemanasan sebelum berlari dan jaga hidrasi tubuh Anda.
                </blockquote>

                <h3>Benefit &amp; Finisher Race Pack</h3>
                <ul>
                    <li>Nomor e-BIB Digital Otomatis instan setelah registrasi</li>
                    <li>Finisher E-Certificate resolusi tinggi dengan nama &amp; catatan waktu resmi</li>
                    <li>Medali Fisik Berkualitas Logam Cor (Bagi pemilih paket Finisher Pack / Komplit)</li>
                    <li>Jersey Dry-Fit Premium nyaman dan menyerap keringat</li>
                </ul>

                <p><span class="font-light-sub">Pengiriman race pack fisik akan diproses melalui SPX Express langsung ke alamat Anda setelah periode lomba berakhir.</span></p>
            `;

            visualEditor.innerHTML = template;
            syncToHidden();
        });
    }

    // Initial sync
    syncToHidden();

    // Ensure hidden input is synced on form submit
    const parentForm = visualEditor.closest('form');
    if (parentForm) {
        parentForm.addEventListener('submit', function() {
            syncToHidden();
        });
    }
});
</script>
@endpush
