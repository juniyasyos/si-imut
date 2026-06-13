# Konfigurasi

Dokumentasi konfigurasi lingkungan dan file penting project SIIMUT.

---

## Environment Variables

File `.env` adalah sumber utama konfigurasi aplikasi. Salin dari `.env.example`:

```bash
cp .env.example .env
```

### Variabel Penting

| Variable | Contoh Default | Fungsi | Wajib |
|---|---|---|---|
| `APP_NAME` | `SI-IMUT` | Nama aplikasi | Ya |
| `APP_ENV` | `production` | Environment (`local`, `production`) | Ya |
| `APP_KEY` | `base64:...` | Key enkripsi aplikasi | Ya |
| `APP_DEBUG` | `false` | Mode debug (true saat development) | Ya |
| `APP_URL` | `http://127.0.0.1:8000` | URL aplikasi | Ya |
| `APP_TIMEZONE` | `Asia/Jakarta` | Timezone aplikasi | Ya |
| `APP_VERSION` | `1.0.0` | Versi aplikasi | Ya |
| `DB_CONNECTION` | `mysql` | Driver database | Ya |
| `DB_HOST` | `127.0.0.1` | Host database | Ya |
| `DB_PORT` | `3306` | Port database | Ya |
| `DB_DATABASE` | `siimut` | Nama database | Ya |
| `DB_USERNAME` | `root` | User database | Ya |
| `DB_PASSWORD` | - | Password database | Ya |
| `SESSION_DRIVER` | `database` | Driver session (`database` wajib untuk IAM) | Ya |
| `SESSION_LIFETIME` | `120` | Lifetime session (menit) | Ya |
| `FILESYSTEM_DISK` | `s3` | Default filesystem disk | Ya |
| `QUEUE_CONNECTION` | `database` | Driver queue | Ya |
| `CACHE_STORE` | `database` | Driver cache | Ya |
| `MAIL_MAILER` | `smtp` | Mail driver | Opsional |
| `MAIL_HOST` | `smtp.mailtrap.io` | Mail host | Opsional |
| `REDIS_HOST` | `127.0.0.1` | Host Redis | Opsional |

### MinIO / S3 Storage

```ini
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=siimut
AWS_ENDPOINT=http://127.0.0.1:9000  # MinIO endpoint
AWS_USE_PATH_STYLE_ENDPOINT=true
```

### IAM / SSO

```ini
# IAM Client
IAM_BASE_URL=
IAM_CLIENT_ID=
IAM_CLIENT_SECRET=
IAM_MODE_DISABLE=false

# Socialite
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
```

### Queue

```ini
QUEUE_CONNECTION=database
# atau
QUEUE_CONNECTION=redis
```

---

## File Konfigurasi Penting

### `config/app.php`

Konfigurasi dasar Laravel: timezone, locale, service providers, aliases.

```php
'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta'),
'locale' => env('APP_LOCALE', 'id'),
```

### `config/database.php`

Koneksi database default. Saat ini menggunakan MySQL via env.

### `config/filament.php`

Konfigurasi panel admin Filament:
- Path: `/admin`
- Brand name
- Theme: `dash-stack-theme-juniyasyos`
- Middleware: auth, permission check

### `config/permission.php`

Konfigurasi Spatie Laravel Permission:
- Models
- Cache settings
- Column names

### `config/filesystems.php`

Filesystem disks:
- `local` — storage lokal
- `s3` — S3/MinIO untuk production
- Media library disk

### `config/media-library.php`

Konfigurasi Spatie Media Library:
- Disk driver
- Image conversions
- Path generator (custom: `FolderPathGenerator`)

### `config/iam.php`

Konfigurasi IAM/SSO integration.

### `config/filament-shield.php`

Konfigurasi Filament Shield untuk RBAC:
- Panel assignment
- Resource permissions
- Role configuration

---

## Perbedaan Environment

| Aspek | Local (.env) | Docker (.env.example.docker) | Production |
|---|---|---|---|
| `APP_ENV` | `local` | `local` / `production` | `production` |
| `APP_DEBUG` | `true` | `false` | `false` |
| `DB_HOST` | `127.0.0.1` | `db` (service name) | Host production |
| `SESSION_DRIVER` | `database` | `database` | `database` |
| `FILESYSTEM_DISK` | `local` (atau `s3` via MinIO) | `s3` | `s3` |
| `QUEUE_CONNECTION` | `database` | `database` | `database` (atau `redis`) |
| `CACHE_STORE` | `database` | `database` | `database` / `redis` |

---

## Konfigurasi Khusus

### Session

Session menggunakan **database driver** untuk kompatibilitas dengan IAM client:

```ini
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
```

### Queue

Queue menggunakan **database driver** sebagai default:

```ini
QUEUE_CONNECTION=database
```

Ada script shell yang tersedia untuk beralih auth mode:

```bash
./switch-auth-mode.sh
```

---

## Keamanan

### Jangan commit secret ke repository

- **File `.env` sudah di `.gitignore`** — jangan dipaksa commit.
- **Jangan tulis password/token asli** di dokumentasi atau file publik.
- **Regenerate APP_KEY** jika terlanjur terekspos: `php artisan key:generate`.

### File yang Dilindungi .gitignore

```txt
.env
.storage
node_modules/
vendor/
public/hot
public/build
*.log
```

### Best Practice

1. Gunakan `.env.example` sebagai template — isi dengan nilai dummy.
2. Jangan pernah menyimpan secret di file konfigurasi yang di-commit.
3. Rotasi key secara berkala.
4. Untuk production, gunakan environment variables langsung (bukan file .env).

---

## TODO

- [ ] Dokumentasi konfigurasi Filament theme (`dash-stack-theme-juniyasyos`)
- [ ] Dokumentasi konfigurasi Filament PWA
- [ ] Dokumentasi konfigurasi Socialite provider
- [ ] Dokumentasi konfigurasi backup (`laravel-backup`)
