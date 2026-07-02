# Panduan Deployment

Panduan untuk mendeploy SIIMUT ke lingkungan production.

---

## Prasyarat

### Server Requirements

| Komponen | Spesifikasi Minimal |
|---|---|
| CPU | 2 core |
| RAM | 4 GB |
| Storage | 20 GB + storage media |
| OS | Ubuntu 22.04+ / Debian 12+ |

### Software

- PHP 8.3+ dengan ekstensi yang diperlukan
- Composer 2.x
- MySQL 8.0+ atau MariaDB 10.6+
- Nginx atau Apache
- Redis (opsional, untuk cache/queue)
- Supervisor (untuk queue worker)
- Node.js 22.x (untuk build frontend)

---

## Alur Deployment

### 1. Build Frontend

```bash
npm install
npm run build
```

Ini menghasilkan file di `public/build/`.

### 2. Setup Environment Production

```bash
cp .env.example .env
# Edit .env:
#   APP_ENV=production
#   APP_DEBUG=false
#   APP_URL=https://your-domain.com
#   DB_HOST, DB_USERNAME, DB_PASSWORD sesuaikan
#   FILESYSTEM_DISK=s3
#   QUEUE_CONNECTION=database (atau redis)
php artisan key:generate
```

### 3. Migration & Seed

```bash
php artisan migrate --force
php artisan db:seed --force
```

### 4. Generate Permission

```bash
php artisan shield:generate --all --panel=admin
php artisan shield:super-admin --user=1
```

### 5. Optimize Laravel

```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### 6. Storage

```bash
php artisan storage:link
```

### 7. Setup Queue Worker (Supervisor)

Buat file `/etc/supervisor/conf.d/siimut-worker.conf`:

```ini
[program:siimut-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/siimut/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/siimut/storage/logs/queue-worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start siimut-queue:*
```

### 8. Setup Scheduler (Cron)

Tambahkan ke crontab:

```bash
* * * * * cd /path/to/siimut && php artisan schedule:run >> /dev/null 2>&1
```

### 9. Setup Nginx

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/siimut/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## Deployment via Script

Project menyediakan script deployment:

### Production Setup

```bash
composer run prod-setup
```

> ⚠️ Script ini melakukan `db:wipe` (menghapus semua data) — **jangan jalankan di server production yang sudah aktif**.

### Production Update

```bash
composer run prod-only
```

Script ini menjalankan:
1. Hapus debugbar cache.
2. Migrasi database.
3. Seed database.
4. Generate permission.
5. Set super admin.
6. Git clean.

---

## Service yang Perlu Berjalan

| Service | Fungsi | Cara Jalankan |
|---|---|---|
| Web Server | Serve aplikasi | Nginx / Apache |
| PHP-FPM | Process PHP | `php-fpm` |
| MySQL | Database | `mysqld` (Docker/manual) |
| Queue Worker | Proses job | Supervisor / `php artisan queue:work` |
| Scheduler | Task terjadwal | Cron (`* * * * *`) |
| Redis (opsional) | Cache/queue | `redis-server` |

---

## Validasi Setelah Deployment

```bash
# Cek halaman utama (harus 200)
curl -I https://your-domain.com

# Cek health check
php artisan about

# Cek koneksi database
php artisan db:show

# Cek queue worker
php artisan queue:monitor

# Cek cache
php artisan cache:table
```

---

## Rollback

### Rollback Database

```bash
# Rollback 1 step
php artisan migrate:rollback --step=1 --force

# Rollback semua
php artisan migrate:reset --force
```

### Rollback Code

```bash
git revert HEAD
# atau
git reset --hard <previous-tag>
```

### Rollback Frontend

```bash
# Kembalikan build sebelumnya
# (simpan backup public/build/ sebelum deploy)
mv public/build public/build-new
mv public/build-backup public/build
```

---

## Checklist Sebelum Deployment

- [ ] Semua test lulus: `php artisan test`
- [ ] Build frontend berhasil: `npm run build`
- [ ] `.env` production sudah benar (APP_DEBUG=false)
- [ ] APP_KEY sudah di-generate
- [ ] Storage link sudah dibuat
- [ ] Queue worker berjalan
- [ ] Scheduler aktif (cron)
- [ ] Permission storage sudah benar (775)
- [ ] SSL/TLS terkonfigurasi
- [ ] Backup database sudah dibuat
- [ ] Debugbar non-aktif di production
- [ ] Changelog sudah update

---

## TODO

- [ ] Deployment via Docker Swarm / Kubernetes
- [ ] Backup otomatis database
- [ ] Monitoring (Laravel Pulse / Sentry)
- [ ] CI/CD pipeline (GitHub Actions)
- [ ] Blue-green deployment strategy
