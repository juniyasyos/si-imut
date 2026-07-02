# 🏥 SIIMUT — Sistem Indikator Mutu untuk Rumah Sakit

[![License](https://img.shields.io/badge/License-Proprietary-red?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/badge/PHP-8.3-blue?style=flat-square&logo=php)](composer.json)
[![Laravel Version](https://img.shields.io/badge/Laravel-12-red?style=flat-square&logo=laravel)](composer.json)
[![Filament Version](https://img.shields.io/badge/Filament-3.2-purple?style=flat-square)](composer.json)
[![GitHub last commit](https://img.shields.io/github/last-commit/juniyasyos/SI-IMUT?style=flat-square)](https://github.com/juniyasyos/si-imut)

**SIIMUT** adalah platform berbasis web untuk memantau, menganalisis, dan meningkatkan mutu layanan kesehatan di rumah sakit Indonesia. Sistem ini selaras dengan standar **Kementerian Kesehatan RI**, **KARS**, dan **SNARS**.

---

## 📋 Daftar Isi

- [Tujuan](#tujuan)
- [Fitur Utama](#fitur-utama)
- [Tech Stack](#tech-stack)
- [Gambaran Struktur Project](#gambaran-struktur-project)
- [Cara Instalasi Singkat](#cara-instalasi-singkat)
- [Cara Menjalankan](#cara-menjalankan)
- [Dokumentasi Detail](#dokumentasi-detail)
- [Status Project](#status-project)
- [Lisensi](#lisensi)

---

## 🎯 Tujuan

- ✅ **Efisiensi & Akurasi** — Digitalisasi pencatatan dan analisis indikator mutu.
- ✅ **Kepatuhan Standar** — Memastikan standar KARS & SNARS melalui pemantauan sistematis.
- ✅ **Analisis Data** — Laporan real-time dan visualisasi untuk keputusan berbasis bukti.
- ✅ **Peningkatan Mutu** — Identifikasi tren, analisis masalah, dan optimalisasi layanan.

## ⚙️ Fitur Utama

| Fitur | Deskripsi |
|---|---|
| **Dashboard Monitoring** | Visualisasi indikator mutu real-time dengan grafik dan tabel. |
| **Daily Report** | Input laporan harian dengan mekanisme live update (Livewire). |
| **Form Builder** | Template form versi, field config, compliance scoring (mirip Google Form). |
| **Manajemen Indikator** | CRUD indikator mutu, kategori, profiling. |
| **Laporan & Export** | PDF (Browsershot), Excel, laporan triwulan, print-ready. |
| **Benchmarking** | Bandingkan indikator antar unit kerja, region, periode. |
| **RBAC** | Role-based access control dengan permission & policy. |
| **Audit Log** | Lacak perubahan data (LogsActivity, created_by/updated_by). |
| **PWA + Offline** | Service worker, cache manifest untuk produksi. |
| **SSO / API** | Dynamic SSO dan API service untuk integrasi identitas. |
| **Media Manager** | Upload, folder sync, struktur media per unit kerja. |

## 🏗️ Tech Stack

| Layer | Teknologi |
|---|---|
| **Backend** | PHP 8.3+, Laravel 12 |
| **Admin Panel** | Filament 3.2 |
| **Frontend** | Livewire, Alpine.js, Blade, Tailwind CSS |
| **Database** | MySQL 8.1 (default) + Eloquent ORM |
| **Queue / Cache** | Database driver (default), Redis opsional |
| **Storage** | S3 / MinIO (local S3-compatible) |
| **PWA** | Service worker + cache manifest |
| **Dev Tools** | Vite, Pest, Debugbar, Laravel Pail |

## 📁 Gambaran Struktur Project

```
project/
├── app/
│   ├── Filament/         # Resource, Pages, Widgets, Plugins
│   ├── Http/             # Controllers, Middleware, Requests
│   ├── Livewire/         # Komponen Livewire
│   ├── Models/           # Eloquent Models
│   ├── Modules/          # Modular monolith (7 modul)
│   ├── Policies/         # Authorization policies
│   ├── Services/         # Service layer
│   └── ...
├── config/               # Konfigurasi aplikasi
├── database/             # Migrasi, seeder, factory
├── docs/                 # Dokumentasi teknis
├── resources/            # Blade views, CSS, JS
├── routes/               # Definisi route
├── .docker/              # Docker setup (PHP, Nginx, MySQL, Redis)
└── ...
```

> 🔍 Lihat [docs/PROJECT_STRUCTURE.md](docs/PROJECT_STRUCTURE.md) untuk penjelasan detail.

## 🚀 Cara Instalasi Singkat

```bash
# 1. Clone
git clone https://github.com/juniyasyos/si-imut.git SIIMUT
cd SIIMUT

# 2. Install dependensi
composer install
npm install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Migrasi database
php artisan migrate --seed

# 5. Generate permission (Filament Shield)
php artisan shield:generate --all --panel=admin
php artisan shield:super-admin --user=1
```

> 📖 Lihat [docs/INSTALLATION.md](docs/INSTALLATION.md) untuk panduan lengkap.

## 🖥️ Cara Menjalankan

```bash
# Development (server + queue + schedule + log + Vite)
composer run dev

# Atau mode Laravel-only
composer run dev-lara

# IAM/SSO development mode
composer run iam

# Docker environment
make up
```

> 📖 Lihat [docs/USAGE.md](docs/USAGE.md) untuk panduan penggunaan.

## AI Agent & RAG Usage

Project ini punya `rag-project/` untuk membantu AI agent memahami SIIMUT tanpa membaca seluruh repo dari nol.

Urutan agent:
1. Baca `AGENTS.md`
2. Baca `rag-project/README.md`
3. Baca docs RAG
4. Query RAG jika bisa
5. Baru baca source file relevan

## 📚 Dokumentasi Detail

Dokumentasi teknis lengkap ada di folder [`docs/`](docs/README.md):

| Dokumen | Fungsi |
|---|---|
| [PROJECT_STRUCTURE.md](docs/PROJECT_STRUCTURE.md) | Struktur folder dan file |
| [INSTALLATION.md](docs/INSTALLATION.md) | Panduan instalasi lengkap |
| [CONFIGURATION.md](docs/CONFIGURATION.md) | Konfigurasi environment |
| [USAGE.md](docs/USAGE.md) | Panduan penggunaan |
| [DEVELOPMENT.md](docs/DEVELOPMENT.md) | Panduan developer |
| [DEPLOYMENT.md](docs/DEPLOYMENT.md) | Panduan deployment |
| [TROUBLESHOOTING.md](docs/TROUBLESHOOTING.md) | Pemecahan masalah umum |
| [VERSIONING.md](docs/VERSIONING.md) | Aturan versi |
| [CHANGELOG.md](CHANGELOG.md) | Catatan perubahan per versi |
| [ROADMAP.md](docs/ROADMAP.md) | Rencana pengembangan |

## 📌 Status Project

- **Versi Terakhir:** `0.1.0` — stabil awal
- **Status:** Active Development
- **Branch Utama:** `main`
- **Branch Development:** `modular-monolist`

## 📜 Lisensi

Proprietary — All rights reserved. Lihat file [LICENSE](LICENSE) untuk detail.

---

**Dibuat dengan ❤️ untuk mutu layanan kesehatan Indonesia.**
