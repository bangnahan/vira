# Panduan Deployment VIRA ke Server Produksi Menggunakan HestiaCP

Panduan lengkap ini menjelaskan langkah demi langkah cara men-deploy dan menjalankan (*go-live*) aplikasi **VIRA** (*Virtual Run, Ride, & Walk Platform*) pada VPS berbasis control panel **HestiaCP**.

---

## 1. Persyaratan Server & Dependensi

Pastikan server VPS dengan HestiaCP sudah terpasang dependensi berikut:

- **PHP 8.3** (atau 8.4) beserta ekstensi wajib:
  ```bash
  sudo apt update
  sudo apt install -y php8.3-fpm php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl \
                      php8.3-zip php8.3-bcmath php8.3-intl php8.3-gd libfreetype6-dev
  ```
  > [!IMPORTANT]
  > Ekstensi **`php8.3-gd`** dan library **FreeType** mutlak diperlukan oleh modul **CanvasRenderService** di VIRA untuk men-generate gambar e-BIB dan E-Certificate ber-font atletik secara dinamis.

- **Composer** (v2.x):
  ```bash
  composer --version
  ```
- **Node.js (v18+ / v20+) & NPM**:
  ```bash
  node -v
  npm -v
  ```
- **MySQL / MariaDB** (disediakan langsung oleh HestiaCP).

---

## 2. Konfigurasi Domain di Panel HestiaCP

1. Login ke panel HestiaCP Anda (`https://IP-SERVER:8083`).
2. Masuk ke menu **WEB** -> klik **Add Web Domain** (atau gunakan akun user non-admin yang Anda buat, misal `bangnahan`).
3. Masukkan nama domain Anda, misalnya: `vira.my.id` atau `event.domainanda.com`.
4. Buka opsi **Advanced Options**:
   - Centang **Custom document root**.
   - Arahkan folder dokumen ke:
     ```text
     /home/<USER>/web/<DOMAIN>/public_html/public
     ```
     *(Ganti `<USER>` dengan username HestiaCP dan `<DOMAIN>` dengan nama domain Anda)*.
     > Hal ini penting karena Laravel melayani request publik dari folder `/public`.
5. Klik **Save**.

---

## 3. Aktifkan SSL Gratis (Let's Encrypt)

1. Pada menu **WEB**, arahkan kursor ke domain Anda lalu klik ikon **Edit (Pensil)**.
2. Centang **Enable SSL for this domain**.
3. Centang **Use Let's Encrypt to obtain SSL certificate**.
4. Centang **Enable Automatic HTTP-to-HTTPS redirection**.
5. Klik **Save** (tunggu 10-30 detik hingga sertifikat berhasil diterbitkan).

---

## 4. Buat Database MySQL di HestiaCP

1. Di panel HestiaCP, klik tab menu **DB** -> klik **Add Database**.
2. Masukkan informasi database:
   - **Database**: `vira` (nama lengkap otomatis menjadi `<USER>_vira`, misal `admin_vira`).
   - **User**: `vira_user` (nama lengkap otomatis menjadi `<USER>_vira_user`).
   - **Password**: Buat password yang kuat dan catat.
3. Klik **Save**.

---

## 5. Clone Repository & Setup Proyek via SSH

Buka terminal SSH server VPS Anda (`ssh root@ip-server` atau sebagai user Hestia):

```bash
# Pindah ke direktori domain
cd /home/<USER>/web/<DOMAIN>/public_html

# Bersihkan file default HestiaCP jika ada (misal index.html dan robots.txt bawaan)
rm -rf index.html robots.txt

# Clone repository VIRA menggunakan Personal Access Token Anda
git clone https://<USERNAME>:<GITHUB_TOKEN>@github.com/bangnahan/vira.git temp_repo

# Pindahkan semua file ke root public_html
shopt -s dotglob
mv temp_repo/* .
rm -rf temp_repo

# Install dependensi PHP untuk produksi
composer install --no-dev --optimize-autoloader

# Install dependensi frontend dan build assets
npm install
npm run build
```

---

## 6. Konfigurasi Environment (`.env`)

Salin file contoh ke file `.env`:

```bash
cp .env.example .env
nano .env
```

Sesuaikan variabel-variabel kunci berikut:

```env
APP_NAME="VIRA - Virtual Race"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://domainanda.com

# Koneksi Database MySQL HestiaCP
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=admin_vira
DB_USERNAME=admin_vira_user
DB_PASSWORD=password_database_anda

# Tripay Payment Gateway (Ganti ke Production jika sudah live)
TRIPAY_API_KEY=KODE_API_TRIPAY_PRODUKSI
TRIPAY_PRIVATE_KEY=PRIVATE_KEY_TRIPAY_PRODUKSI
TRIPAY_MERCHANT_CODE=KODE_MERCHANT_PRODUKSI
TRIPAY_SANDBOX=false
TRIPAY_DEFAULT_CHANNEL=QRIS2

# Mailketing Transactional Email
MAILKETING_API_TOKEN=308b31d3313311776744479fa8fd7eb3
MAILKETING_SENDER_EMAIL=hi@jelatix.com
MAILKETING_SENDER_NAME="VIRA Virtual Sport"

# Meta Conversions API (Opsional / Jika Diaktifkan)
META_CAPI_ACCESS_TOKEN=
META_CAPI_TEST_CODE=
```

Setelah menyimpan file `.env`, generate Application Key:

```bash
php artisan key:generate
```

---

## 7. Migrasi Database & Buat User Admin

Jalankan perintah migrasi dan link storage publik:

```bash
# 1. Jalankan migrasi tabel
php artisan migrate --force

# 2. Buat symlink storage agar upload gambar e-BIB, jersey, & banner dapat diakses publik
php artisan storage:link

# 3. Jalankan seeder bawaan (opsional, jika ingin template dan event sample)
php artisan db:seed --force
```

### Membuat User Admin Panel:
Jalankan tinker untuk memastikan akun admin Anda aktif:

```bash
php artisan tinker --execute '
$user = App\Models\User::firstOrNew(["email" => "bangnahan@gmail.com"]);
$user->name = "Admin Bangnahan";
$user->password = bcrypt("pekanbaru12");
$user->is_admin = true;
$user->save();
echo "Admin siap digunakan!\n";
'
```

---

## 8. Hak Akses Folder (Permissions)

Pastikan web server (`www-data` dan user HestiaCP) dapat menulis file log dan cache:

```bash
cd /home/<USER>/web/<DOMAIN>/public_html

# Berikan kepemilikan ke user Hestia
chown -R <USER>:<USER> .

# Berikan hak akses tulis ke storage dan bootstrap cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

## 9. Konfigurasi Cron Job (Laravel Scheduler) di HestiaCP

Laravel memerlukan cron job setiap menit untuk memproses expired order, notifikasi progres lari, dll.

1. Di panel HestiaCP, klik tab **CRON**.
2. Klik **Add Cron Job**.
3. Masukkan konfigurasi:
   - **Minute**: `*`
   - **Hour**: `*`
   - **Day**: `*`
   - **Month**: `*`
   - **Day of week**: `*`
   - **Command**:
     ```bash
     /usr/bin/php8.3 /home/<USER>/web/<DOMAIN>/public_html/artisan schedule:run >> /dev/null 2>&1
     ```
4. Klik **Save**.

---

## 10. Konfigurasi Webhook di Dashboard Tripay

Agar status pembayaran peserta otomatis berubah menjadi **PAID** dan e-BIB langsung terbit serta email terkirim:

1. Login ke [Dashboard Tripay Merchant](https://tripay.co.id/).
2. Masuk ke menu **Pengaturan** -> **Webhook**.
3. Masukkan URL Webhook:
   ```text
   https://domainanda.com/api/tripay/webhook
   ```
4. Simpan pengaturan.

---

## 11. Optimasi Performa Produksi (Caching)

Setelah semua berjalan normal, jalankan perintah caching Laravel agar website merespons super cepat:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

> [!TIP]
> Jika di kemudian hari Anda melakukan perubahan pada file konfigurasi `.env` atau routes, jalankan:
> ```bash
> php artisan config:clear
> php artisan route:clear
> php artisan view:clear
> ```
> lalu ulangi perintah cache di atas.

---

## 12. Troubleshooting Singkat

| Masalah | Penyebab Umum | Solusi |
| :--- | :--- | :--- |
| **Error 500 (White Screen)** | Permission folder `storage` atau `.env` belum dikonfigurasi. | Cek `storage/logs/laravel.log`. Jalankan `chmod -R 775 storage bootstrap/cache`. |
| **Halaman 404 pada URL selain Home** | Konfigurasi Nginx belum me-rewrite URL ke `index.php`. | Di HestiaCP Web Domain -> Web Template pastikan menggunakan template standard Laravel atau tambahkan directive `try_files $uri $uri/ /index.php?$query_string;`. |
| **Gambar e-BIB / Banner tidak muncul** | Symlink storage belum terpasang. | Jalankan `php artisan storage:link`. |
| **Error saat render e-BIB / Sertifikat** | Ekstensi PHP GD atau FreeType belum terinstall. | Install dengan `sudo apt install php8.3-gd libfreetype6-dev` lalu restart PHP: `sudo systemctl restart php8.3-fpm`. |
