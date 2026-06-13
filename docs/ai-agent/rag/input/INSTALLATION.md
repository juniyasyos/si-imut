# Panduan Instalasi

Panduan ini mencakup instalasi SIIMUT di lingkungan **local development** dan **Docker**.

---

## Prasyarat

| Tools | Versi Minimal | Catatan |
|---|---|---|
| PHP | 8.3+ | 8.4 direkomendasikan (lihat `.docker/php/Dockerfile`) |
| Composer | 2.x | Dependency manager PHP |
| Node.js | 22.x | Build frontend assets |
| NPM | 10+ | Atau Yarn |
| MySQL | 8.0+ | Atau MariaDB 10.6+ |
| Git | 2.x | Version control |
| Redis | Opsional | Untuk cache/queue alternatif |
| Docker | Opsional | Untuk lingkungan kontainer |

### Ekstensi PHP yang Dibutuhkan

- `pdo_mysql`, `pdo_pgsql`
- `zip`, `gd`, `intl`
- `pcntl` (untuk queue worker)
- `redis` (opsional)
- `pcov` (untuk code coverage)

> Lihat [`.docker/php/Dockerfile`](../.docker/php/Dockerfile) untuk referensi lengkap paket yang dibutuhkan.

---

## Instalasi Manual (Local)

### 1. Clone Repository

```bash
git clone https://github.com/juniyasyos/si-imut.git SIIMUT
cd SIIMUT
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Frontend Dependencies

```bash
npm install
```

### 4. Setup Environment

```bash
# Copy environment file
cp .env.example .env

# Generate app key
php artisan key:generate

# (Opsional) Sesuaikan .env untuk kebutuhan lokal
```

### 5. Konfigurasi Database

Buat database MySQL:

```sql
CREATE DATABASE siimut CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Sesuaikan `.env`:

```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=siimut
DB_USERNAME=root
DB_PASSWORD=
```

### 6. Migrasi Database

```bash
php artisan migrate --seed
```

### 7. Generate Permission

```bash
php artisan shield:generate --all --panel=admin
php artisan shield:super-admin --user=1
```

### 8. Storage Link

```bash
php artisan storage:link
```

### 9. Jalankan Aplikasi

```bash
composer run dev
```

Akses di `http://127.0.0.1:8000`.

---

## Instalasi Docker

### 1. Clone & Setup

```bash
git clone https://github.com/juniyasyos/si-imut.git SIIMUT
cd SIIMUT

# Copy Docker environment
cp .env.example.docker .env
```

### 2. Build & Jalankan Container

```bash
make up
```

Atau langsung:

```bash
docker compose --env-file ./.env --profile app --profile administration -f docker-compose.yml up -d --remove-orphans
```

### 3. Install Dependencies (dalam container)

```bash
make shell:app
# Di dalam container:
composer install
npm install
php artisan key:generate
```

### 4. Migrasi Database

```bash
make command:app "php artisan migrate --seed"
make command:app "php artisan shield:generate --all --panel=admin"
make command:app "php artisan shield:super-admin --user=1"
```

### 5. Akses Aplikasi

- **Aplikasi**: `http://localhost:80`
- **phpMyAdmin**: `http://localhost:8080`

---

## Setup Cepat (Script)

Untuk development cepat, project menyediakan script `setup.sh`:

```bash
chmod +x setup.sh
./setup.sh
```

> ⚠️ Baca script terlebih dahulu sebelum menjalankan — script ini melakukan operasi git clean dan reset.

---

## Verifikasi Instalasi

Setelah instalasi, pastikan:

```bash
# Cek versi PHP
php -v

# Cek konfigurasi Laravel
php artisan about

# Cek koneksi database
php artisan db:show

# Cek queue
php artisan queue:monitor

# Cek storage link
php artisan storage:link
```

Akses halaman dashboard di browser. Jika halaman login Filament muncul, instalasi berhasil.

---

## Troubleshooting Instalasi

| Masalah | Solusi |
|---|---|
| `Class "..." not found` | Jalankan `composer dump-autoload` |
| `No application key` | Jalankan `php artisan key:generate` |
| `Connection refused (MySQL)` | Pastikan MySQL berjalan, cek kredensial di `.env` |
| `Vite manifest not found` | Jalankan `npm run build` |
| `Permission denied storage/` | `chmod -R 775 storage bootstrap/cache` |

> Untuk masalah lain, lihat [TROUBLESHOOTING.md](TROUBLESHOOTING.md).

---

## TODO

- [ ] Panduan instalasi untuk production (server bare-metal)
- [ ] Panduan setup MinIO / S3 storage
- [ ] Panduan setup SSO / IAM
