# Project Structure

## Ringkasan

Project SIIMUT adalah aplikasi **Laravel 12** dengan arsitektur **modular monolith** yang menggunakan **Filament 3.2** sebagai admin panel. Struktur project mengikuti konvensi Laravel standar dengan tambahan direktori modular di `app/Modules/`.

---

## Tree Singkat

```txt
.
├── app/                           # Source code utama aplikasi
│   ├── Console/                   # Artisan commands
│   ├── Domain/                    # Domain layer (DailyReport)
│   ├── Facades/                   # Service facades
│   ├── Filament/                  # Filament resources, pages, widgets
│   │   ├── Exports/               # Export configuration
│   │   ├── Imports/               # Import configuration
│   │   ├── Pages/                 # Custom Filament pages
│   │   ├── Plugins/               # Filament plugins
│   │   ├── Resources/             # CRUD resources (8 resources)
│   │   ├── Responses/             # Auth responses
│   │   ├── Traits/                # Filament-specific traits
│   │   └── Widgets/               # Dashboard widgets
│   ├── Forms/                     # Form-related logic
│   ├── Http/                      # Controllers, Middleware, Requests
│   │   ├── Controllers/           # Web & API controllers
│   │   ├── Middleware/             # Custom middleware
│   │   └── Requests/              # Form requests
│   ├── Jobs/                      # Queue jobs
│   ├── Kernel/                    # Core kernel support (traits, providers)
│   ├── Livewire/                  # Livewire components
│   ├── Models/                    # Eloquent models
│   ├── Modules/                   # Modular monolith (7 modules)
│   │   ├── Authorization/         # Manajemen otorisasi & role
│   │   ├── Benchmarking/          # Sistem benchmarking indikator
│   │   ├── DailyReport/           # Laporan harian (modul terbesar)
│   │   ├── FormEngine/            # Form builder engine
│   │   ├── ImutMaster/            # Master data indikator mutu
│   │   ├── Laporan/               # Laporan periodik
│   │   └── Reporting/             # Reporting service
│   ├── Notifications/             # Notification classes
│   ├── Observers/                 # Eloquent observers
│   ├── Policies/                  # Authorization policies
│   ├── Providers/                 # Service providers
│   ├── QueryBuilders/             # Custom query builders
│   ├── Repositories/              # Repository pattern
│   ├── Rules/                     # Custom validation rules
│   ├── Services/                  # Service layer
│   │   ├── Authorization/         # Permission services
│   │   ├── Benchmarking/          # Benchmarking services
│   │   ├── Chart/                 # Chart data processing
│   │   ├── Core/                  # Core services
│   │   ├── DailyReport/           # Daily report services
│   │   ├── DynamicForm/           # Dynamic form services
│   │   ├── Form/                  # Form-related services
│   │   ├── FormBuilder/           # Form builder services
│   │   ├── Laporan/               # Laporan services
│   │   ├── Reporting/             # Reporting services
│   │   └── Support/               # Support utilities
│   ├── Settings/                  # Settings (Filament plugin)
│   ├── Support/                   # Support classes & helpers
│   ├── Tables/                    # Custom table columns
│   ├── Traits/                    # Shared traits
│   └── View/                      # View composers / components
│
├── bootstrap/                     # Laravel bootstrap files
├── config/                        # Konfigurasi aplikasi (30+ file)
├── database/                      # Database
│   ├── factories/                 # Model factories
│   ├── migrations/                # Migration files
│   ├── schema/                    # Database schema dumps
│   ├── seeders/                   # Database seeders
│   └── settings/                  # Settings migrations
├── docs/                          # Dokumentasi project
│   ├── images/                    # Screenshot & gambar
│   ├── releases/                  # Release notes per versi
│   ├── upgrade/                   # Panduan upgrade
│   └── ai-agents/                 # AI agent configurations
├── lang/                          # File lokalization
├── public/                        # Public assets (entry point)
├── resources/                     # Frontend resources
│   ├── css/                       # Stylesheets (Tailwind)
│   ├── js/                        # JavaScript
│   └── views/                     # Blade templates
├── routes/                        # Route definitions
│   ├── api.php                    # API routes
│   ├── console.php                # Console routes
│   ├── livewire-report.php        # Livewire report routes
│   ├── test.php                   # Test routes
│   └── web.php                    # Web routes
├── scripts/                       # Utility scripts
├── storage/                       # Laravel storage
├── stubs/                         # Custom stubs
├── tests/                         # Unit & feature tests
├── vendor/                        # Composer dependencies
│
├── .docker/                       # Docker setup
│   ├── db/                        # MySQL config
│   ├── logs/                      # Docker logs mount
│   ├── nginx/                     # Nginx config
│   ├── php/                       # PHP Dockerfile & config
│   ├── phpmyadmin/                # phpMyAdmin
│   ├── redis/                     # Redis
│   └── scripts/                   # Docker helper scripts
│
├── .env                           # Environment (local, gitignored)
├── .env.example                   # Environment template
├── .env.example.docker            # Docker environment template
├── composer.json                  # PHP dependencies
├── docker-compose.yml             # Docker compose config
├── Makefile                       # Make commands
├── package.json                   # Node.js dependencies
├── vite.config.js                 # Vite bundler config
└── tailwind.config.js             # Tailwind CSS config
```

---

## Penjelasan Folder Utama

### `app/` — Source Code

| Path | Fungsi | Catatan |
|---|---|---|
| `app/Filament/` | Semua resource Filament (CRUD admin panel) | **Inti aplikasi** |
| `app/Http/` | Controller, middleware, form request | Standar Laravel |
| `app/Livewire/` | Komponen Livewire interaktif | Untuk halaman real-time |
| `app/Models/` | Eloquent ORM models | **Hati-hati** saat mengubah relasi |
| `app/Modules/` | Modular monolith — 7 modul bisnis | **Inti bisnis logic** |
| `app/Policies/` | Authorization policies (gate) | Jangan sembarangan diubah |
| `app/Services/` | Service layer — logic bisnis | Referensi utama logic |
| `app/Jobs/` | Queue job classes | Untuk task asynchronous |

### `config/` — Konfigurasi

30+ file konfigurasi Laravel dan package. File penting:

| File | Fungsi |
|---|---|
| `app.php` | Konfigurasi aplikasi utama |
| `database.php` | Koneksi database |
| `filament.php` | Konfigurasi Filament panel |
| `permission.php` | Konfigurasi Spatie Permission |
| `iam.php` | Konfigurasi IAM/SSO |
| `filesystems.php` | Filesystem disk (local, s3) |
| `media-library.php` | Media library config |
| `cache.php`, `session.php`, `queue.php` | Cache, session, queue |

### `database/` — Database

| Path | Fungsi |
|---|---|
| `migrations/` | Semua migration (termasuk dari modul) |
| `seeders/` | Database seeders |
| `factories/` | Model factories untuk testing |
| `schema/` | SQL dump untuk setup cepat |
| `settings/` | Migration untuk settings plugin |

### `Modules/` — Modular Monolith

Project menggunakan package `nwidart/laravel-modules`. Setiap modul independen:

| Modul | Fungsi |
|---|---|
| **Authorization** | Role, permission, user management, SSO |
| **Benchmarking** | Benchmarking indikator antar unit/region |
| **DailyReport** | Laporan harian — modul terbesar |
| **FormEngine** | Form builder, template versioning, compliance scoring |
| **ImutMaster** | Master data indikator mutu |
| **Laporan** | Laporan periodik (triwulan, tahunan) |
| **Reporting** | Reporting service & export |

Setiap modul memiliki struktur internal yang konsisten:

```
ModuleName/
├── Contracts/        # Interface & DTO
├── Database/
│   └── Migrations/   # Migration spesifik modul
├── Filament/         # Resources, Pages, Widgets
├── Http/             # Controllers
├── Livewire/         # Livewire components
├── Models/           # Eloquent models modul
├── Policies/         # Policies modul
├── Services/         # Service classes
└── Providers/        # Service provider
```

### `.docker/` — Infrastructure

| Path | Fungsi |
|---|---|
| `php/` | PHP 8.4 FPM Dockerfile + php.ini |
| `nginx/` | Nginx config (default.conf) |
| `db/` | MySQL 8.1 custom config |
| `redis/` | Redis config |
| `scripts/` | Helper scripts untuk Docker |

### `resources/views/` — Blade Templates

Struktur views cukup kompleks dengan partials untuk Filament resources, form components, dan vendor overrides:

```
resources/views/
├── auth/                            # Halaman auth
├── components/                      # Blade components global
├── filament/                        # Filament-specific templates
│   ├── forms/                       # Form components
│   ├── modals/                      # Modal overrides
│   ├── pages/                       # Custom page layouts
│   ├── prints/                      # Print templates
│   ├── resources/                   # Resource-specific views
│   ├── table/                       # Table customizations
│   └── widgets/                     # Widget templates
├── forms/                           # Form builder views
├── layouts/                         # Layout templates
├── livewire/                        # Livewire component views
├── reports/                         # Report templates
├── tables/                          # Table views
└── vendor/                          # Vendor override views
```

---

## File Konfigurasi Penting di Root

| File | Fungsi | Wajib Diedit? |
|---|---|---|
| `.env` | Environment settings | ✅ Ya (lokalisasi) |
| `.env.example` | Template .env | ✅ Ya (jika ada variable baru) |
| `composer.json` | PHP dependencies & scripts | Hati-hati |
| `package.json` | Node.js dependencies | Hati-hati |
| `docker-compose.yml` | Docker orchestration | Hati-hati |
| `Makefile` | Make command shortcuts | ✅ Boleh |
| `vite.config.js` | Vite bundler config | Hati-hati |
| `tailwind.config.js` | Tailwind CSS config | ✅ Boleh |

---

## Catatan Struktur Penting

### ⚠️ Hati-hati saat mengubah:

- **`app/Models/`** — Relasi, casts, dan event bisa berdampak luas.
- **`app/Policies/`** — Akses kontrol sensitif, jangan diubah tanpa test.
- **`database/migrations/`** — Migration yang sudah dijalankan di production.
- **`config/*`** — Beberapa file (permission.php, filament.php) dipakai banyak services.
- **`vendor/`** — Jangan diedit langsung (composer-managed).

### ✅ Aman diedit:

- **`docs/`** — Dokumentasi (ini dia).
- **`app/Services/`** — Service baru boleh ditambah.
- **`scripts/`** — Utility scripts.
- **`tests/`** — Testing files.

### 🏷️ Legacy / Needs Review

Tidak ada folder yang secara eksplisit ditandai legacy. Namun, beberapa area perlu diperhatikan:

- `app/Kernel/` — Berisi duplikat trait/support dari `app/Traits/` dan `app/Support/`. **Needs Review**: kemungkinan duplikasi kode.
- `app/Domain/` — Hanya berisi `DailyReport/TableViewDomain.php`. Terlihat underutilized.
- `routes/test.php` — Route khusus testing. Perlu dipastikan tidak aktif di production.

---

## Referensi

- **Dokumentasi utama**: [README.md](../README.md)
- **Laravel convention**: [laravel.com/docs/12.x/structure](https://laravel.com/docs/12.x/structure)
