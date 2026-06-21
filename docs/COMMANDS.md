# Commands — Daftar Perintah Penting

Daftar lengkap command yang tersedia di project SIIMUT, dikelompokkan per kategori.

---

## 🐘 Artisan Commands

### Informasi & Utility

| Command | Fungsi |
|---|---|
| `php artisan about` | Informasi aplikasi (env, driver, versi) |
| `php artisan list` | Daftar semua Artisan commands |
| `php artisan help <command>` | Help detail untuk command tertentu |
| `php artisan optimize:clear` | Clear semua cache |

### Database

| Command | Fungsi |
|---|---|
| `php artisan migrate` | Jalankan migrasi database |
| `php artisan migrate:fresh` | Rollback + migrasi ulang semua tabel |
| `php artisan migrate:status` | Cek status migrasi |
| `php artisan db:seed` | Jalankan database seeder |
| `php artisan db:show` | Lihat status database (tabel, ukuran) |
| `php artisan db:monitor` | Monitor koneksi database |
| `php artisan make:migration` | Buat file migration baru |

### Shield (Permission)

| Command | Fungsi |
|---|---|
| `php artisan shield:generate --all` | Generate semua permissions |
| `php artisan shield:generate --all --panel=admin` | Generate permissions untuk admin panel |
| `php artisan shield:super-admin --user=1` | Set user ID 1 sebagai super admin |
| `php artisan permission:show` | Lihat daftar permissions |

### Queue

| Command | Fungsi |
|---|---|
| `php artisan queue:listen` | Jalankan queue worker (foreground) |
| `php artisan queue:listen --tries=1` | Worker dengan 1 retry |
| `php artisan queue:work` | Jalankan queue worker (production) |
| `php artisan queue:monitor` | Monitor antrian queue |
| `php artisan queue:restart` | Restart semua worker |
| `php artisan queue:failed` | Lihat failed jobs |
| `php artisan queue:retry all` | Retry semua failed jobs |
| `php artisan queue:flush` | Hapus semua failed jobs |

### Storage & Cache

| Command | Fungsi |
|---|---|
| `php artisan storage:link` | Buat symlink public/storage → storage/app/public |
| `php artisan cache:clear` | Clear aplikasi cache |
| `php artisan config:clear` | Clear config cache |
| `php artisan config:cache` | Cache config untuk production |
| `php artisan view:clear` | Clear compiled Blade views |
| `php artisan view:cache` | Pre-compile Blade views |
| `php artisan route:clear` | Clear route cache |
| `php artisan route:cache` | Cache routes untuk production |
| `php artisan route:list` | Lihat daftar routes |

### Development

| Command | Fungsi |
|---|---|
| `php artisan serve` | Jalankan development server (port 8000) |
| `php artisan pail` | Log viewer real-time (Laravel Pail) |
| `php artisan schedule:work` | Jalankan scheduler (foreground) |
| `php artisan schedule:run` | Jalankan scheduled tasks sekali |
| `php artisan tinker` | Interactive PHP REPL |
| `php artisan make:model` | Buat model baru |
| `php artisan make:controller` | Buat controller baru |
| `php artisan make:livewire` | Buat Livewire component |
| `php artisan key:generate` | Generate APP_KEY |

### Module (nwidart/laravel-modules)

| Command | Fungsi |
|---|---|
| `php artisan module:list` | Daftar modul & statusnya |
| `php artisan module:make <Nama>` | Buat modul baru |
| `php artisan module:enable <Nama>` | Aktifkan modul |
| `php artisan module:disable <Nama>` | Nonaktifkan modul |
| `php artisan module:migrate` | Jalankan migrasi semua modul |
| `php artisan module:seed` | Seed semua modul |

### IAM / SSO (nexaid-client)

| Command | Fungsi |
|---|---|
| `php artisan iam:login` | Login via IAM SSO |
| `php artisan iam:logout` | Logout dari session IAM |
| `php artisan iam:status` | Status koneksi IAM |

### Backup (filament-backup)

| Command | Fungsi |
|---|---|
| `php artisan backup:run` | Jalankan backup manual |
| `php artisan backup:list` | Lihat daftar backup |
| `php artisan backup:clean` | Hapus backup lama sesuai konfigurasi |
| `php artisan backup:monitor` | Monitor status backup |

### Kustom (Project-specific)

| Command | Fungsi |
|---|---|
| `php artisan sync:form-template-dates` | Sinkronisasi tanggal form template |
| `php artisan test:ttd-url-resolver` | Test TTD URL resolver |
| `php artisan ide-helper:generate` | Generate IDE helper |
| `php artisan ide-helper:models --nowrite` | Generate model helper tanpa overwrite |

---

## 📦 Composer Commands

### Development Server

| Command | Fungsi |
|---|---|
| `composer run dev` | Full stack: serve + queue + schedule + logs + Vite |
| `composer run dev-lara` | Laravel only: serve + queue + schedule + logs |
| `composer run iam` | IAM mode: serve (port 8088) + queue + logs |

### Utility

| Command | Fungsi |
|---|---|
| `composer install` | Install PHP dependencies |
| `composer update` | Update PHP dependencies |
| `composer dump-autoload` | Regenerate autoload files |
| `composer pint` | Jalankan Laravel Pint (code style fixer) |
| `composer test` | Jalankan semua tests (Pest) |
| `composer licenses` | Lihat lisensi dependencies |

---

## 🐳 Docker / Makefile Commands

| Command | Fungsi |
|---|---|
| `make up` | Start Docker containers (background) |
| `make down` | Stop dan hapus containers |
| `make restart` | Restart containers |
| `make build` | Build atau rebuild Docker images |
| `make logs` | Tail logs dari semua containers |
| `make shell:app` | SSH / bash ke container PHP |
| `make shell:db` | SSH ke container MySQL |
| `make shell:nginx` | SSH ke container Nginx |
| `make ps` | Status containers |
| `make test` | Jalankan tests di container |
| `make fresh` | Reset database di container |
| `make fresh:seed` | Reset + seed database di container |

---

## 📦 NPM Commands

| Command | Fungsi |
|---|---|
| `npm install` | Install Node.js dependencies |
| `npm run build` | Build frontend assets (production) |
| `npm run dev` | Vite dev server dengan HMR |
| `npm run lint` | Lint frontend code |

---

## 🧠 RAG (contexta) Commands

### Setup
Karena menggunakan `bun`, pastikan `bun` sudah terinstal:
```bash
# Instalasi di dalam folder plugin contexta
cd /home/juni/projects/plugin/contexta
bun install
```

### Scan & Query

| Command | Fungsi |
|---|---|
| `bunx contexta scan` | Scan project dan buat graph RAG |
| `bunx contexta graph stats` | Lihat statistik graph RAG |
| `bunx contexta inspect <node_id>` | Lihat relasi spesifik dari sebuah node |
| `bunx contexta impact <node_id>` | Lihat analisis dampak perubahan dari sebuah node |
| `bunx contexta query --intent <intent> --entity <entitas>` | Melakukan query/pencarian intent arsitektural |

### Contoh Penggunaan

```bash
# Scan/Update Graph
bunx contexta scan

# Mengecek siapa yang memanggil User Model
bunx contexta inspect model-user

# Mencari controller untuk entitas ImutData
bunx contexta query --intent service_lookup --entity ImutData
```

---

## 🔧 Utility Scripts

| Script | Fungsi |
|---|---|
| `setup.sh` | Setup environment (git clean + install + build) |
| `generate-cache-pwa.sh` | Generate PWA cache manifest |
| `scripts/` | Utility scripts (lihat folder scripts/) |

---

## 📋 Ringkasan Command Terpopuler

| Yang Ingin Dilakukan | Command |
|---|---|
| Jalankan app (full stack) | `composer run dev` |
| Jalankan migrasi | `php artisan migrate` |
| Generate permission | `php artisan shield:generate --all --panel=admin` |
| Seed database | `php artisan db:seed` |
| Cek routes | `php artisan route:list` |
| Jalankan queue worker | `php artisan queue:listen` |
| Jalankan scheduler | `php artisan schedule:work` |
| Backup database | `php artisan backup:run` |
| Cek log real-time | `php artisan pail` |
| Docker up | `make up` |
| Test app | `composer test` |
| Query knowledge base | `bunx contexta inspect <entitas>` |

---

## RAG Metadata

## CMD-001 - sync:form-template-dates

Type: Command
Status: Active
Area: Form Template
Related Modules:
- MOD-002
Related Services:
- Needs Verification
Source:
- commit: 4bb5bd7

Summary:
Sinkronisasi tanggal form template.

## CMD-002 - test:ttd-url-resolver

Type: Command
Status: Active
Area: Reporting
Related Modules:
- MOD-003
Source:
- commit: 2fa6f26

Summary:
Test TTD URL resolver.
