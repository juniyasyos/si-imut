# Troubleshooting

Daftar masalah umum dan solusinya.

---

## Masalah: Aplikasi tidak berjalan

### Gejala
- Halaman putih atau error 500.
- `Whoops!` atau error page Laravel.

### Penyebab Kemungkinan
- `.env` tidak dikonfigurasi dengan benar.
- APP_KEY tidak di-generate.
- Storage permission salah.
- Extension PHP tidak terinstall.

### Cara Cek
```bash
# Cek error log
tail -f storage/logs/laravel.log

# Cek status aplikasi
php artisan about
```

### Solusi
```bash
# Generate key
php artisan key:generate

# Fix permission
chmod -R 775 storage bootstrap/cache

# Cek extension
php -m | grep -E "pdo|mysql|zip|gd|intl|pcntl"
```

---

## Masalah: Database connection refused

### Gejala
```
SQLSTATE[HY000] [2002] Connection refused
```

### Penyebab Kemungkinan
- MySQL/MariaDB tidak berjalan.
- Kredensial database di `.env` salah.
- Port database salah.
- Host database salah (terutama di Docker).

### Cara Cek
```bash
# Cek service MySQL
sudo systemctl status mysql
# atau
docker ps | grep mysql

# Cek koneksi
mysql -h 127.0.0.1 -u root -p -e "SELECT 1"
```

### Solusi
```bash
# Start MySQL
sudo systemctl start mysql

# Atau di Docker
make up

# Verifikasi .env
# Pastikan DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD benar
```

---

## Masalah: Vite manifest not found

### Gejala
```
Vite manifest not found at: public/build/manifest.json
```

### Penyebab Kemungkinan
- Frontend belum di-build.
- `npm install` belum dijalankan.

### Solusi
```bash
npm install
npm run build
```

---

## Masalah: Permission denied (storage)

### Gejala
```
The stream or file "..." could not be opened in append mode
```

### Penyebab Kemungkinan
- Permission direktori `storage/` salah.
- Ownership file salah (apache/www-data vs user).

### Solusi
```bash
# Fix permission
chmod -R 775 storage bootstrap/cache

# Fix ownership (Ubuntu/Debian)
sudo chown -R www-data:www-data storage bootstrap/cache

# Atau beri akses grup
sudo usermod -a -G www-data $USER
```

---

## Masalah: Queue worker tidak berjalan

### Gejala
- Job tidak diproses.
- Export/download tidak selesai.
- Laporan tidak ter-generate.

### Cara Cek
```bash
# Cek tabel jobs
php artisan queue:monitor

# Cek failed jobs
php artisan queue:failed

# Cek worker berjalan
ps aux | grep queue:work
```

### Solusi
```bash
# Jalankan worker manual
php artisan queue:work --tries=3

# Atau via Supervisor
sudo supervisorctl status siimut-queue:*
sudo supervisorctl restart siimut-queue:*
```

---

## Masalah: Halaman login tidak muncul

### Gejala
- Redirect terus-menerus.
- Error 419 (CSRF token mismatch).

### Penyebab Kemungkinan
- Session driver tidak sesuai.
- Cache session perlu dibersihkan.
- `.env` SESSION_DRIVER salah.

### Solusi
```bash
# Session driver harus database
# SESSION_DRIVER=database

# Bersihkan cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Pastikan session table ada
php artisan session:table
php artisan migrate
```

---

## Masalah: File upload gagal

### Gejala
- Error saat upload gambar/dokumen.
- File tidak muncul di media manager.

### Penyebab Kemungkinan
- Storage disk tidak terkonfigurasi.
- S3/MinIO credentials salah.
- Folder tujuan tidak ada.

### Solusi
```bash
# Cek storage link
php artisan storage:link

# Cek filesystem config
php artisan about | grep Filesystem

# Cek MinIO/S3
# Pastikan AWS_* di .env sudah benar
```

---

## Masalah: Debugbar muncul di production

### Gejala
- Toolbar debugbar terlihat di halaman production.

### Solusi
```bash
# Pastikan di .env:
APP_DEBUG=false

# Hapus cache config
php artisan config:clear
php artisan config:cache
```

---

## Masalah: Permission / RBAC error

### Gejala
- "Unauthorized" atau "This action is unauthorized."
- User tidak bisa mengakses resource.

### Solusi
```bash
# Generate ulang permission
php artisan shield:generate --all --panel=admin

# Assign ulang role ke user
php artisan shield:super-admin --user=1
```

---

## Masalah: Class not found

### Gejala
```
Class "App\Modules\XXX" not found
```

### Solusi
```bash
# Dump autoload
composer dump-autoload

# Clear cache
php artisan optimize:clear

# Cek apakah modul terdaftar
php artisan module:list
```

---

## Masalah: Laravel Pail (log viewer) tidak berfungsi

### Gejala
```
pail: No content yet
```

### Solusi
```bash
# Pastikan ada log
tail -f storage/logs/laravel.log

# Jalankan pail dengan timeout
php artisan pail --timeout=0
```

---

## Masalah: PWA / Service Worker

### Gejala
- Aplikasi tidak bisa diakses offline.
- Cache tidak ter-update.

### Solusi
```bash
# Regenerate cache PWA
bash generate-cache-pwa.sh

# Clear service worker di browser
# Buka DevTools → Application → Service Workers → Unregister
```

---

## Mendapatkan Bantuan

Jika masalah tidak terselesaikan:

1. Cek log: `storage/logs/laravel.log`
2. Cek failed jobs: `php artisan queue:failed`
3. Cek debugbar toolbar (jika aktif)
4. Buka issue di [GitHub](https://github.com/juniyasyos/si-imut/issues)

---

## TODO

- [ ] Troubleshooting untuk MinIO/S3 connection
- [ ] Troubleshooting untuk SSO login
- [ ] Troubleshooting untuk performance issue
