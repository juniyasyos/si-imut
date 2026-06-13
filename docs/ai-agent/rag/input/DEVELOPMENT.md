# Panduan Development

Panduan untuk developer yang berkontribusi pada project SIIMUT.

---

## Setup Development

### Prasyarat

Lihat [INSTALLATION.md](INSTALLATION.md) untuk panduan instalasi lengkap.

### Tools yang Direkomendasikan

| Tools | Fungsi |
|---|---|
| **VS Code** | Editor utama |
| **Laravel IDE Helper** | Auto-completion untuk Facades, Models |
| **Laravel Pint** | Code style fixer (otomatis) |
| **Pest** | Testing framework |
| **Laravel Debugbar** | Debugging toolbar |
| **Laravel Pail** | Log viewer real-time |
| **TablePlus / Sequel Ace** | Database GUI |

### Setup IDE Helper

```bash
php artisan ide-helper:generate
php artisan ide-helper:models --nowrite
```

---

## Menjalankan Project

### Mode Development (dengan Hot Reload)

```bash
composer run dev
```

Ini menjalankan 5 proses sekaligus:
- **Server**: `php artisan serve` (port 8000)
- **Queue**: Worker background job
- **Schedule**: Task scheduler
- **Logs**: Laravel Pail (real-time log)
- **Vite**: HMR untuk frontend

### Mode Development (Laravel only)

```bash
composer run dev-lara
```

Tanpa Vite — cocok jika frontend sudah di-build.

---

## Coding Convention

### PHP (Laravel + Filament)

- **PHP**: Ikuti standar PSR-12 (dijaga otomatis oleh Laravel Pint).
- **Model naming**: Singular (`User`, `ImutData`, `DailyReportEntry`).
- **Table naming**: Plural snake_case (`imut_data`, `daily_report_entries`).
- **Controller naming**: Singular (`UserController`).
- **Service naming**: Deskriptif (`ImutDataPermissionService`).
- **Route naming**: snake_case (`imut-data.index`).

### Database

- Migration: `YYYY_MM_DD_HHMMSS_create_xxx_table.php`.
- Gunakan `foreignId()` untuk relasi.
- SoftDeletes untuk data penting.
- Timestamps wajib (created_at, updated_at, created_by, updated_by).

### Frontend (Blade + Livewire + Alpine.js)

- Blade: lowercase dengan dot notation (`filament.resources.index`).
- Livewire: PascalCase component, kebab-case view.
- Alpine.js: Gunakan store untuk state kompleks (`x-data` untuk lokal).
- CSS: Tailwind utility classes.

### Javascript

- Format: Prettier / standard.
- Module: ES modules.
- Hindari jQuery — gunakan Alpine.js atau vanilla JS.

---

## Branching & Versioning

### Branch Strategy

```txt
main              → Stabil, production-ready
├── develop       → Integration branch (modular-monolist)
│   ├── feature/* → Fitur baru
│   ├── fix/*     → Bug fix
│   └── docs/*    → Perubahan dokumentasi
```

Aturan:
- `main` hanya menerima merge dari `develop`.
- Fitur baru di branch `feature/nama-fitur`.
- Bug fix di branch `fix/deskripsi-bug`.
- Dokumentasi di branch `docs/deskripsi`.

### Versioning

Lihat [VERSIONING.md](VERSIONING.md) untuk aturan lengkap.

---

## Cara Menambah Fitur Baru

### 1. Buat Branch

```bash
git checkout modular-monolist
git pull
git checkout -b feature/nama-fitur
```

### 2. Identifikasi Modul

Tentukan modul yang tepat di `app/Modules/`:

| Modul | Cocok untuk |
|---|---|
| `Authorization` | Manajemen user, role, permission, SSO |
| `DailyReport` | Laporan harian dan entri data |
| `FormEngine` | Form builder, template, schema |
| `ImutMaster` | Master data indikator mutu |
| `Laporan` | Laporan periodik |
| `Reporting` | Report/export service |
| `Benchmarking` | Benchmarking indikator |

Atau buat service di `app/Services/` jika logic lintas modul.

### 3. Buat Migration

```bash
# Migration global
php artisan make:migration create_xxx_table

# Migration modul
# Buat manual di app/Modules/{Module}/Database/Migrations/
```

### 4. Buat Model

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YourModel extends Model
{
    protected $fillable = [];
    // Relasi, casts, boot()
}
```

### 5. Buat Filament Resource

```bash
php artisan make:filament-resource YourModel
```

### 6. Tambahkan Service / Logic

- Service class di `app/Services/` atau `app/Modules/{Module}/Services/`.
- Policy di `app/Policies/`.
- Query Builder di `app/QueryBuilders/`.

### 7. Testing

```bash
php artisan make:test YourFeatureTest --pest
# atau
php artisan make:test --unit YourUnitTest --pest
```

### 8. Update Dokumentasi

Jika fitur mengubah:
- Command / cara penggunaan → update `docs/USAGE.md`.
- Konfigurasi → update `docs/CONFIGURATION.md`.
- Struktur folder → update `docs/PROJECT_STRUCTURE.md`.
- Catatan changelog di `CHANGELOG.md`.

### 9. Commit & Push

```bash
git add .
git commit -m "feat: menambahkan fitur xxx"
git push origin feature/nama-fitur
```

Buat Pull Request ke branch `modular-monolist`.

---

## Menambah Dokumentasi

Saat fitur berubah, periksa checklist berikut:

- [ ] Apakah ada command/perintah baru? → Update `docs/USAGE.md`
- [ ] Apakah ada konfigurasi baru? → Update `docs/CONFIGURATION.md`
- [ ] Apakah struktur folder berubah? → Update `docs/PROJECT_STRUCTURE.md`
- [ ] Apakah cara install berubah? → Update `docs/INSTALLATION.md`
- [ ] Apakah ada masalah potensial? → Update `docs/TROUBLESHOOTING.md`
- [ ] Catat di `CHANGELOG.md`

---

## Checklist Sebelum Commit

- [ ] Kode sudah dites secara lokal.
- [ ] Tidak ada secret yang ikut ter-commit (cek `.env` tidak ikut).
- [ ] Dokumentasi diperbarui jika ada perubahan fitur.
- [ ] CHANGELOG diperbarui jika ada perubahan penting.
- [ ] Tidak ada `dd()`, `dump()`, `ray()` yang tertinggal.
- [ ] Migration sudah dicek bisa rollback.
- [ ] Factory/Seeder sudah update jika model berubah.

---

## Perintah Testing

```bash
# Jalankan semua test
php artisan test

# Jalankan dengan Pest
./vendor/bin/pest

# Test spesifik
./vendor/bin/pest tests/Feature/YourTest.php

# Coverage
php artisan test --coverage

# Dengan parallel
php artisan test --parallel
```

---

## Debugging Tools

### Laravel Debugbar

Aktif di `config/debugbar.php`:

```env
APP_DEBUG=true  # Debugbar aktif otomatis
```

### Laravel Pail

```bash
php artisan pail
```

### Log Viewer

```bash
tail -f storage/logs/laravel.log
```

### Queue Monitoring

```bash
php artisan queue:monitor
php artisan queue:table  # Cek tabel jobs
```

---

## Lint & Code Style

```bash
# Laravel Pint (PSR-12)
./vendor/bin/pint

# Dry run
./vendor/bin/pint --test
```
