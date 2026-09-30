# Product Requirement Document (PRD) — VIRA (Virtual Run, Ride, & Walk)

---

## 1. Executive Summary & Product Overview

### 1.1 Nama Produk
**VIRA** (*Virtual Run, Ride, & Walk Platform*)

### 1.2 Ringkasan Visi
VIRA adalah platform web berbasis **Laravel** yang memfasilitasi penyelenggaraan event virtual sport (lari, bersepeda, jalan santai) dengan alur pendaftaran cepat (*guest direct registration*), penomoran **e-BIB otomatis**, modul **Visual Designer (e-BIB & E-Certificate)** berbasis pengaturan koordinat & tipografi, pencatatan aktivitas fleksibel (single session maupun multi-akumulasi jarak) melalui input manual dengan lampiran link bukti (Strava/Google Drive/dll), **Etalase Add-ons & Cross-selling Merchandise** (Jersey finisher, gantungan kunci, dll), pembayaran otomatis via **Tripay**, serta pengiriman notifikasi transaksional via **Mailketing**.

### 1.3 Nilai Utama (Key Value Propositions)
1. **Zero-Friction Registration**: Peserta tidak perlu mengingat kata sandi atau membuat akun rumit. Cukup isi form pendaftaran, pilih add-ons, bayar, dan terima e-BIB.
2. **Visual Designer Canvas (e-BIB & E-Sertifikat)**: Admin bebas mengunggah background e-BIB & sertifikat, lalu menyesuaikan letak posisi koordinat (X, Y), ukuran huruf, jenis font, warna, dan perataan teks secara presisi.
3. **Etalase Add-on & Cross-Selling**: Meningkatkan pendapatan event organizer (*average order value*) dengan menawarkan produk pelengkap (Finisher Jersey, gantungan kunci, medali ekstra) langsung di alur pendaftaran maupun etalase mandiri.
4. **Dynamic Race Mechanism**: Fleksibilitas tipe event (Single-session target atau Cumulative / cicil jarak berkali-kali).
5. **Automated Digital Rewards**: Visual e-BIB interaktif dan E-Certificate otomatis setelah status Finisher tercapai.
6. **Community & Competitive Leaderboard**: Live ranking publik untuk menyemarakkan persaingan sehat antar peserta.
7. **Seamless Operations**: Otomasi pembayaran Tripay, pengiriman email Mailketing, dan modul review admin untuk potensi pemenang (*potential winner inspection*).

---

## 2. User Personas & Use Cases

| Persona | Peran | Kebutuhan Utama |
| :--- | :--- | :--- |
| **Peserta (Participant)** | Pelari, pesepeda, pejalan kaki | Mendaftar cepat, memilih add-on merchandise (jersey/gantungan kunci), dapat e-BIB kartu digital untuk pamer sosmed, submit waktu & jarak lari dengan mudah, pantau progres target, dan unduh sertifikat Finisher yang proporsional dan rapi. |
| **Race Admin / Organizer** | Pengelola event & verifikator | Mengatur letak koordinat teks di e-BIB & sertifikat (Designer), mengelola etalase add-ons dan varian stok, memantau pembayaran, memverifikasi bukti submission potensi juara, mengelola resi pengiriman race pack, dan mengedit data peserta jika ada keliru input. |
| **Super Admin** | Penanggung jawab teknis sistem | Konfigurasi API Tripay, Mailketing, template generator sertifikat & e-BIB, serta manajemen role admin. |

---

## 3. Fitur Utama & Kebutuhan Fungsional (Functional Requirements)

### 3.1 Modul Event Management & Kategori (Admin)
1. **CRUD Event Virtual**:
   - Judul event, slug URL, deskripsi, banner gambar, dan syarat ketentuan (Rules).
   - Tipe Aktivitas: `RUN`, `RIDE`, `WALK`.
   - Periode Pendaftaran: Tanggal buka s/d tanggal tutup.
   - Periode Pelaksanaan (Race Period): Tanggal mulai lari s/d batas akhir submit.
   - Sifat Event:
     - `Mode Submission`: **Single Activity** (harus selesai dalam 1 sesi) atau **Cumulative** (bisa dicicil berkali-kali).
     - `Karakter Event`: **Fun / Challenge** (tanpa juara, fokus target finisher) atau **Race / Lomba** (ada podium/juara).
2. **Kategori Jarak (Race Categories)**:
   - Contoh: 5K, 10K, 21K (Half Marathon), 42K, atau Custom Distance (misal 50K cumulative).
   - Target jarak dalam desimal kilometer.
   - Prefix e-BIB per kategori (misal: `VR10-001`).
3. **Paket Pendaftaran Dasar (Registration Packages)**:
   - **Digital Only**: E-BIB, E-Certificate, Digital Finisher Badge.
   - **With Physical Race Pack**: Termasuk Jersey standar, Medali Fisik. Memerlukan input ukuran jersey dan alamat pengiriman.

---

### 3.2 Modul Visual Designer (E-BIB & E-Certificate Builder)
Fitur unggulan untuk menyesuaikan letak elemen teks di atas background gambar e-BIB dan E-Sertifikat:
1. **Upload Background Canvas**:
   - Upload background template e-BIB (PNG/JPG) dan background template E-Sertifikat (Horizontal A4 / High-Res PNG).
2. **Pengaturan Koordinat & Tipografi Elemen (Visual / Coordinates Editor)**:
   - Admin dapat menentukan posisi koordinat `X` dan `Y` (dalam pixel atau persentase canvas).
   - Pilihan Jenis Font, Ukuran Huruf (*Font Size* pt/px), Ketebalan (*Font Weight*), Warna Teks (*Hex Color Picker*), dan Alignment (*Left, Center, Right*).
3. **Daftar Elemen Dinamis yang Dapat Disesuaikan**:
   - **Pada E-BIB**:
     - `bib_number` (Nomor BIB peserta)
     - `participant_name` (Nama lengkap peserta)
     - `category_name` (Nama kategori, misal: 10K Run)
     - `event_name` (Judul event)
     - `qr_code` (Posisi X, Y, dan ukuran dimensi QR code verifikasi)
   - **Pada E-Certificate**:
     - `participant_name` (Nama peserta finisher)
     - `bib_number` (Nomor BIB)
     - `category_name` (Kategori)
     - `total_distance` (Total jarak tempuh, misal: "10.00 KM")
     - `total_duration` (Catatan waktu tempuh, misal: "00:54:12")
     - `average_pace` (Pace rata-rata, misal: "5'25\" /km")
     - `finish_date` (Tanggal selesai / pencapaian finisher)
     - `qr_code` (QR Code keaslian sertifikat)
4. **Live Interactive Preview**:
   - Tampilan preview langsung (*live canvas rendering*) dengan data *dummy* sehingga admin bisa melihat letak presisi sebelum disimpan.

---

### 3.3 Modul Etalase Add-ons & Cross-Selling Merchandise
1. **Katalog Produk Add-on (Admin)**:
   - Admin dapat menambahkan item add-on yang ditautkan ke event tertentu atau berlaku global:
     - Contoh: Jersey Finisher Tambahan, Medali Fisik Tambahan, Gantungan Kunci VIRA, Topi Lari, Kaos Kaki, Gel Pack.
   - Informasi Produk: Nama, Gambar Produk, Deskripsi, Harga Satuan, Berat Barang (*gram* - untuk kalkulasi ongkir).
   - Pengaturan Varian: Mendukung opsi ukuran (S, M, L, XL, XXL) atau warna.
   - Manajemen Stok / Kuota per item dan per varian.
2. **Integrasi Add-on pada Form Pendaftaran**:
   - Pada langkah pemilihan paket, peserta disajikan **Etalase Rekomendasi (Cross-sell Showcase)**.
   - Peserta dapat mencentang dan memilih kuantiti serta varian add-on yang diinginkan.
   - Subtotal add-on otomatis terakumulasi ke total tagihan dan menambah berat paket pada kalkulasi ongkir.
3. **Halaman Etalase Mandiri (Stand-alone Storefront)**:
   - Peserta yang sudah pernah mendaftar dapat mengunjungi halaman etalase untuk membeli merchandise tambahan dengan menginput nomor e-BIB mereka (sehingga pesanan dapat digabungkan atau dikirim terpisah).

---

### 3.4 Modul Pendaftaran & Pembayaran (Front-End)
1. **Form Pendaftaran Guest**:
   - Data Peserta: Nama Lengkap, Email, Nomor WhatsApp, Jenis Kelamin, Tanggal Lahir, Golongan Darah, Kontak Darurat.
   - Pemilihan Kategori Event & Paket Utama.
   - Pilihan Add-ons dari Etalase.
   - Jika paket/add-on memerlukan pengiriman fisik: Input alamat lengkap (Provinsi, Kota/Kabupaten, Kecamatan, Kode Pos, Detail Alamat).
   - Penghitungan Ongkir melalui sistem API Ongkir internal berdasarkan total berat (berat paket + total berat add-on).
2. **Integrasi Payment Gateway Tripay**:
   - Mendukung channel pembayaran: QRIS, Virtual Account (BCA, Mandiri, BRI, BNI, BSI), E-Wallet (OVO, Dana, ShopeePay), Gerai Retail (Indomaret/Alfamart).
   - Generate Kode Pembayaran / QR Code / Virtual Account dan countdown kadaluarsa tagihan.
   - **Webhook Callback Tripay**:
     - Status `PAID`: Mengubah status registrasi menjadi `CONFIRMED`, men-generate nomor e-BIB, membuat kartu e-BIB sesuai koordinat designer, dan mentrigger email Mailketing.
     - Status `EXPIRED` / `FAILED`: Membatalkan tagihan dan mengembalikan stok add-on & kuota kategori.

---

### 3.5 Modul Submission Aktivitas & Universal Submission Portal (Satu Link untuk Semua Event)
1. **Universal Submission Portal (`/submit`)**:
   - Sistem VIRA beroperasi secara multi-event (menjalankan banyak event sekaligus).
   - **Satu Link Global Tunggal**: Semua peserta dari event apa pun (Virtual Run, Ride, Walk) mengakses link submit yang **sama** di `/submit` (tidak perlu link berbeda per event).
   - **Global Unique e-BIB Resolution**: Nomor e-BIB dibuat unik di seluruh sistem dan mengikat langsung ke event dan kategori tertentu (Format: `[EVENT_CODE]-[CAT_CODE]-[NOMOR_URUT]`, contoh: `MVR26-10K-0012` atau `JVR26-40K-0045`).
   - Ketika peserta memasukkan nomor e-BIB di halaman `/submit`:
     - Sistem langsung mendeteksi event yang diikuti, tema event, kategori jarak, target waktu, dan mode event (Single atau Cumulative).
     - Menampilkan identitas peserta, progres kilometer saat ini, riwayat lari, dan form submit aktivitas yang relevan.
2. **Form Input Aktivitas**:
   - Tanggal & Waktu Aktivitas dilakukan.
   - Jarak Tempuh (Kilometer, format desimal misal: `5.25` km).
   - Waktu Tempuh (Jam, Menit, Detik, misal: `00:28:45`).
   - Link Bukti Rekam Jejak (Public Strava Activity URL, Google Drive link, Garmin Connect link, Apple Fitness, dll).
   - Catatan Tambahan (opsional).
3. **Aturan Akumulasi Jarak**:
   - **Mode Cumulative**: Setiap submission sukses akan menambah `total_distance` dan `total_moving_time`. Sistem menampilkan persentase progress bar (contoh: `15.5 km / 21.0 km - 73%`).
   - **Mode Single Activity**: Submission harus mencukupi atau melebihi target jarak kategori dalam satu catatan waktu.
4. **Pencapaian Finisher & Auto-Locked**:
   - Ketika total jarak mencapai atau melampaui target kategori, status peserta otomatis ter-update menjadi `FINISHED`.
   - Tombol unduh E-Certificate aktif.

---

### 3.6 Modul E-Certificate Finisher
1. **Auto-Generate Sesuai Koordinat Designer**:
   - Di-generate secara otomatis menggunakan koordinat dan gaya huruf yang telah diatur admin pada modul Designer.
   - Menampilkan: Nama Peserta, Kategori Event, Tanggal Selesai, Total Jarak Tempuh, Catatan Waktu Tempuh, Pace Rata-rata, dan Nomor e-BIB.
   - Format unduhan: Gambar High-Resolution (PNG) dan dokumen PDF.

---

### 3.7 Modul Leaderboard Publik
1. **Tampilan Live Ranking per Event**:
   - Filter berdasarkan Kategori Jarak dan Jenis Kelamin.
   - Sorting Parameter:
     - Untuk Event Lomba / Single: Ranking berdasarkan `moving_time` tercepat untuk jarak target.
     - Untuk Event Challenge / Cumulative: Ranking berdasarkan waktu tercepat mencapai target jarak, atau akumulasi jarak terjauh.
   - Kolom Leaderboard: Peringkat, Nomor BIB, Nama Peserta, Jarak Tempuh, Total Durasi, Pace Rata-rata, Status (Ongoing / Finisher), dan Link Bukti.

---

### 3.8 Modul Integrasi Email (Mailketing) & 5-Stage Progressive Motivation
1. **Daftar Email Transaksional**:
   - **Email Invoice & Tagihan**: Rincian pendaftaran, add-ons yang dibeli, dan instruksi bayar Tripay.
   - **Email Konfirmasi & e-BIB**: Nomor e-BIB, lampiran kartu e-BIB hasil render designer, dan link akses submit lari.
   - **Email Finisher Congratulations**: Ucapan selamat dan link unduh E-Certificate saat 100% tuntas.

2. **Sistem Notifikasi Progres Lari Berjenjang (Maksimal 5 Email Progresif / Tiap 20% Milestone)**:
   Untuk menghindari *email spamming* saat peserta sering mencicil jarak kecil, sistem mengirimkan email progres hanya saat akumulasi jarak peserta menembus milestone kelipatan 20% dari target event. Setiap milestone dilengkapi narasi dan kata-kata motivasi unik:

   | Milestone | Threshold Jarak | Tema Motivasi | Contoh Subjek & Pesan Motivasi |
   | :---: | :---: | :--- | :--- |
   | **Stage 1** | **≥ 20% Target** | *Awal yang Kuat (The Spark)* | **Subjek**: *"Langkah Awal yang Luar Biasa! Kamu sudah menuntaskan 20% target 🚀"*<br>**Pesan**: *"Perjalanan ribuan kilometer selalu dimulai dari satu langkah pertama. Komitmenmu sudah terbukti hari ini. Jaga ritme, nikmati setiap ayunan langkah, dan bangun konsistensimu!"* |
   | **Stage 2** | **≥ 40% Target** | *Membangun Momentum (Building Rhythm)* | **Subjek**: *"Momentummu tak terbendung! 40% target telah tercapai 🔥"*<br>**Pesan**: *"Konsistensi adalah ciri sejati seorang finisher. Kamu sudah hampir mencapai separuh jalan. Napasmu kian teratur, tekadmu kian membaja. Jangan kasih kendor!"* |
   | **Stage 3** | **≥ 60% Target** | *Melewati Titik Balik (The Turning Point)* | **Subjek**: *"Lebih dari separuh jalan! Kamu sudah menembus 60% target 💪"*<br>**Pesan**: *"Kamu telah menaklukkan bagian terberat dan kini berada di paruh akhir. Garis finish semakin dekat dan nyata. Ingat alasan kamu memulai tantangan ini, teruslah melangkah maju!"* |
   | **Stage 4** | **≥ 80% Target** | *Garis Finish di Depan Mata (The Final Push)* | **Subjek**: *"Tinggal sedikit lagi! 80% target telah kamu taklukkan ⚡"*<br>**Pesan**: *"Hanya tersisa 20% jarak terakhir! Di sinilah mental pemenang diuji. Kumpulkan sisa energimu, nikmati setiap meter perjuangannya, dan bersiaplah menyambut medali kebanggaanmu!"* |
   | **Stage 5** | **100% Target (Finisher)** | *Kemenangan & Apresiasi (The Triumph)* | **Subjek**: *"LUAR BIASA! Target Tuntas 100% — Selamat Finisher [Nama Event]! 🏆"*<br>**Pesan**: *"DEDIKASI, KERINGAT, DAN KONSISTENSIMU TERBAYAR LUNAS! Kamu resmi menjadi Finisher. Unduh E-Certificate kebanggaanmu sekarang, pamerkan ke teman-temanmu, dan rayakan pencapaian hebat ini!"* |

---

### 3.9 Modul Admin Backoffice (Review & Operasional)
1. **Dashboard Metrik**: Total peserta, pendapatan tiket & add-ons, total km lari terakumulasi, rasio finisher.
2. **Manajemen Submission & Moderasi**:
   - Tabel catatan lari dengan link bukti Strava/Gdrive.
   - Fitur Edit Data Submission: Admin dapat mengoreksi angka jarak/waktu jika peserta salah input.
   - Fitur Pembatalan / Reject jika bukti tidak sah.
   - **Flag Potential Winner**: Filter verifikasi top finisher pada event lomba.
3. **Pengelolaan Pengiriman Race Pack & Add-ons**:
   - Daftar pengiriman berisi paket fisik dan rincian add-ons yang dibeli (ukuran jersey, jenis merchandise).
   - Input nomor resi pengiriman (tracking number) & status pengiriman.
