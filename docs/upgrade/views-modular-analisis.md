# Analisis Views — Persiapan Modular Monolith

> **Analisis struktur `resources/views/` untuk migrasi ke Modular Monolith.**
> Tanggal: 2026-06-09 | Melengkapi [modular-monolith-rencana.md](modular-monolith-rencana.md)

---

## 1. Ringkasan

| Metrik | Nilai |
|---|---|
| Total file Blade | 200 |
| Custom views (non-vendor) | 92 |
| Vendor views (published) | 108 |
| Layouts | 1 (print.blade.php) |
| Shared components | 8 |
| Livewire views | 10 |
| Filament resource views | 46 |
| Filament widgets | 9 |

---

## 2. Kondisi Saat Ini — Organisasi per Lapisan Teknis

Views diorganisir berdasarkan **jenis/teknis**, bukan berdasarkan domain:

```
resources/views/
├── components/           ← shared UI (logo, report-footer, dll.)
│   ├── logo.blade.php
│   ├── logo-report.blade.php
│   ├── basic-report-header.blade.php
│   ├── report-footer-*.blade.php
│   └── compliance-display.blade.php
│
├── layouts/
│   └── print.blade.php           ← layout cetak
│
├── filament/
│   ├── pages/                    ← Filament pages (campur aduk domain)
│   ├── widgets/                  ← Filament widgets (campur aduk domain)
│   ├── forms/                    ← custom form components
│   ├── modals/                   ← custom modal
│   ├── prints/                   ← print templates
│   ├── table/                    ← custom table view
│   └── resources/                ← resource-specific views
│       ├── daily-report-entry-resource/    ← 31 file (34% dari custom!)
│       ├── imut-data-resource/             ← 6 file
│       ├── imut-profile-resource/          ← 3 file
│       └── laporan-imut-resource/          ← 6 file
│
├── livewire/
│   ├── custom-personal-info.blade.php
│   ├── ttd-upload-component.blade.php
│   ├── test-table-component.blade.php
│   └── reports/                          ← report Livewire
│   └── overview/                         ← overview Livewire
│
├── reports/                    ← 3 file
├── forms/                      ← 1 file
├── tables/                     ← 1 file
├── auth/                       ← 1 file
├── table-view.blade.php        ← root view
└── test-iam-apps.blade.php     ← root view
```

### Masalah Utama

| # | Masalah | Dampak |
|---|---|---|
| 1 | **Views tercampur berdasarkan Filament concept**, bukan domain | Waktu cari view terkait fitur harus buka 3-4 folder berbeda |
| 2 | **Tidak ada pemisahan view namespace per module** | Semua view di-load dari satu direktori; tidak bisa override per modul |
| 3 | **Livewire views flat** — tidak dipisah per module (kecuali reports/overview) | Tidak ada isolasi antar modul |
| 4 | **Tidak ada view composer / shared data boundary** | Data bisa bocor antar view |
| 5 | **Dominasi Daily Report** — 31/92 file (34%) | DailyReport module paling kompleks secara UI |

---

## 3. Distribusi per Domain / Bounded Context

### a. Module: DailyReport (31 views — terbesar)

```
filament/resources/daily-report-entry-resource/
├── pages/
│   └── list-daily-report-entries.blade.php
└── pages/partials/components/
    ├── header/
    │   ├── filters-section.blade.php
    │   └── header-section.blade.php
    ├── indicators/
    │   ├── action-buttons.blade.php
    │   ├── desktop-indicator-card.blade.php
    │   ├── indicators-empty-state.blade.php
    │   ├── loading-skeleton.blade.php
    │   ├── share-button.blade.php
    │   └── status-indicator.blade.php
    ├── mobile/
    │   ├── legend.blade.php
    │   ├── mobile-action-buttons.blade.php
    │   ├── mobile-card.blade.php
    │   ├── mobile-indicator-card.blade.php
    │   └── mobile-status-cards.blade.php
    ├── modal/
    │   └── slide-over.blade.php
    ├── monitoring/
    │   ├── alpine-matrix.blade.php
    │   ├── desktop-monitoring-card.blade.php
    │   ├── desktop-monitoring-row.blade.php
    │   ├── empty-state.blade.php
    │   ├── matrix-cell.blade.php
    │   ├── mobile-monitoring-card.blade.php
    │   ├── monitoring-view.blade.php
    │   └── table-header.blade.php
    ├── navigation/
    │   ├── date-header.blade.php
    │   ├── date-navigation.blade.php
    │   ├── legend.blade.php
    │   ├── month-navigation.blade.php
    │   └── ...
    ├── scripts/
    │   └── scripts-styles.blade.php
    └── stores/
        ├── content-syncer.blade.php
        ├── dashboard-state.blade.php
        └── indicators-loader.blade.php
```

Juga di Filament pages:
- `filament/pages/list-daily-reports.blade.php`
- `filament/pages/create-daily-report-entry.blade.php`
- `filament/pages/edit-daily-report-entry.blade.php`

Di Filament widgets:
- `filament/widgets/laporan-latest-widget.blade.php`
- `filament/widgets/laporan-unit-widget.blade.php`

**Total: ~36 view files** — sebuah modul besar dengan struktur sub-komponen yang baik di dalam folder `partials/components/`.

### b. Module: ImutMaster (6 views)

```
filament/resources/imut-data-resource/
├── pages/
│   ├── imut-data-overview.blade.php
│   ├── imut-data-unit-kerja-overview.blade.php
│   ├── imut-indicator-report.blade.php
│   └── summary-imut-data-diagram.blade.php
└── widgets/
    ├── imut-data-notes-slide-over.blade.php
    └── note-detail.blade.php
```

### c. Module: ImutProfile (3 views)

```
filament/resources/imut-profile-resource/
├── pages/
│   ├── list-daily-reports.blade.php
│   ├── manage-form-builder.blade.php
│   └── preview-form-builder.blade.php
```

### d. Module: Laporan (6 views)

```
filament/resources/laporan-imut-resource/
├── pages/
│   ├── imut-data-report.blade.php
│   ├── imut-data-unit-kerja-report.blade.php
│   ├── monitoring-daily-reports.blade.php
│   ├── penilaian-laporan.blade.php
│   ├── unit-kerja-imut-data-report.blade.php
│   └── unit-kerja-report.blade.php
```

### e. Cross-Cutting / Shared

| Direktori | Jumlah | Fungsi |
|---|---|---|
| `components/` | 8 | Logo, report header/footer, compliance display |
| `filament/widgets/` | 9 | Dashboard widgets (dashboard, analysis, recommendation) |
| `filament/prints/` | 2 | Print templates (imut-data, imut-indicator) |
| `filament/forms/` | 2 | Custom form components (dynamic-form, alternative-data) |
| `livewire/` | 10 | Livewire components (ttd, reports, overview) |
| `reports/` | 3 | Standalone report views (category, unit-kerja) |

---

## 4. Pola View yang Ditemukan

### 4.1 Semua Filament Pages pakai Component Layout

```blade
<x-filament-panels::page>
    ...
</x-filament-panels::page>
```

Tidak ada satupun yang `@extends` layout — semua via Filament component system. Ini berarti **migrasi views tidak perlu mengubah layout**.

### 4.2 Tidak Ada View Namespace Registration

Tidak ditemukan `View::addNamespace()` atau `loadViewsFrom()` di seluruh codebase. Semua view menggunakan path default Laravel (`resources/views/`).

### 4.3 Livewire Views: Pemisahan Minimal

```
livewire/
├── overview/
│   ├── imut-data-overview.blade.php
│   ├── imut-data-summary-table.blade.php
│   └── imut-data-unit-kerja-table.blade.php
├── reports/
│   ├── imut-data-summary-report.blade.php
│   ├── imut-data-unit-kerja-detail-report.blade.php
│   ├── unit-kerja-imut-data-detail-report.blade.php
│   └── unit-kerja-summary-report.blade.php
└── root: personal-info, test-table, ttd-upload
```

Ada sedikit pemisahan `overview/` dan `reports/`, tapi masih campur namespace `App\Livewire\*`.

### 4.4 Widgets: Tidak Ada Pola Modular

9 widget view di satu folder `filament/widgets/` — tidak ada grouping per domain.

---

## 5. Rekomendasi Migrasi Views per Module

### Fase 1: Setup View Namespace Per Module

Di `register()` tiap Module Service Provider:

```php
// Modules/DailyReport/DailyReportServiceProvider.php
public function register(): void
{
    $this->loadViewsFrom(
        __DIR__ . '/resources/views', 
        'daily-report'
    );
}
```

Maka view bisa dipanggil sebagai:
```blade
{{-- Sebelum --}}
@include('filament.resources.daily-report-entry-resource.pages.partials.components.monitoring.matrix-cell')

{{-- Sesudah --}}
@include('daily-report::monitoring.matrix-cell')
```

### Fase 2: Struktur View Per Module

Setiap module memiliki `resources/views/` sendiri:

```
app/Modules/
├── DailyReport/
│   └── resources/views/
│       ├── filament/
│       │   ├── pages/
│       │   │   ├── list-daily-report-entries.blade.php
│       │   │   ├── create-daily-report-entry.blade.php
│       │   │   └── edit-daily-report-entry.blade.php
│       │   ├── widgets/
│       │   │   ├── laporan-latest-widget.blade.php
│       │   │   └── laporan-unit-widget.blade.php
│       │   └── modals/
│       │       └── daily-report-detail.blade.php
│       ├── monitoring/            ← partials/components/monitoring/ → di-simplify
│       │   ├── view.blade.php
│       │   ├── matrix-cell.blade.php
│       │   ├── desktop-card.blade.php
│       │   └── mobile-card.blade.php
│       ├── indicators/
│       │   ├── status-indicator.blade.php
│       │   └── loading-skeleton.blade.php
│       ├── navigation/
│       │   ├── date-navigation.blade.php
│       │   └── legend.blade.php
│       └── stores/
│           ├── dashboard-state.blade.php
│           └── content-syncer.blade.php
│
├── ImutMaster/
│   └── resources/views/
│       ├── filament/
│       │   ├── pages/
│       │   └── widgets/
│       └── ...
│
└── Laporan/
    └── resources/views/
        └── ...
```

### Fase 3: Simplifikasi Struktur Partial

**Saat ini** (terlalu dalam):

```
daily-report-entry-resource/pages/partials/components/monitoring/matrix-cell.blade.php
```

**Target**:

```
daily-report/monitoring/matrix-cell.blade.php
```

Level folder dari 7 → 3. Dengan namespace view `daily-report::`, panggilannya jadi:

```blade
@include('daily-report::monitoring.matrix-cell')
```

Ini juga memudahkan refactor jika subkomponen ingin dipromosi jadi Livewire component di masa depan.

### Fase 4: Shared Components

File di `resources/views/components/` tetap menjadi **Shared UI Kernel**:

```
resources/views/components/     ← shared tetap di sini
├── logo.blade.php
├── logo-report.blade.php
├── compliance-display.blade.php
└── report-footer-*.blade.php
```

Atau jika ingin dipindah ke `app/Kernel/`:

```
app/Kernel/
└── resources/views/
    └── components/
        ├── logo.blade.php
        └── ...
```

---

## 6. Tabel Mapping: Posisi Saat Ini → Target Modular

| Domain | Posisi Saat Ini | Target Module | Estimasi File |
|---|---|---|---|
| Daily Report entries | `filament/resources/daily-report-entry-resource/` | `Modules/DailyReport/` | 31 |
| Daily Report pages | `filament/pages/{create,edit,list}-daily*` | `Modules/DailyReport/` | 3 |
| Laporan monitoring | `filament/resources/laporan-imut-resource/monitoring*` | `Modules/DailyReport/` | 1 |
| Widgets terkait laporan | `filament/widgets/laporan-*` | `Modules/DailyReport/` | 2 |
| Imut Data | `filament/resources/imut-data-resource/` | `Modules/ImutMaster/` | 6 |
| Imut Profile | `filament/resources/imut-profile-resource/` | `Modules/ImutMaster/` | 3 |
| Form Builder | `filament/resources/imut-profile-resource/manage-form*` | `Modules/FormEngine/` | 2 |
| Laporan IMUT | `filament/resources/laporan-imut-resource/` | `Modules/Laporan/` | 6 |
| Dashboard widgets | `filament/widgets/{recommendation,account,dll}` | `Modules/` atau tetap di Kernel | 7 |
| Shared UI | `components/`, `filament/forms/`, `filament/table/` | `Kernel/` | 12 |
| Livewire | `livewire/overview/`, `livewire/reports/` | Per module masing-masing | 10 |
| Print | `filament/prints/`, `layouts/print*` | Per module atau Kernel | 3 |
| Reports | `reports/` | Per module | 3 |

---

## 7. Catatan Khusus

### 7.1 Daily Report Module Paling Kompleks

31 file view di satu folder resource — tertinggi dari modul lain dengan selisih jauh (next: 6). Ini menandakan:
- Perlu dipecah lagi jadi sub-module? (Monitoring, Navigation, Indicators, Stores)
- Atau memang kompleksitas UI-nya tinggi (dashboard realtime, matrix monitoring, mobile responsive)
- Saat migrasi, prioritaskan DailyReport sebagai module pertama karena paling berat

### 7.2 Mobile vs Desktop Views

Ada pola pemisahan mobile/desktop di beberapa tempat:
```blade
monitoring/
├── desktop-monitoring-card.blade.php
├── desktop-monitoring-row.blade.php
└── mobile-monitoring-card.blade.php

indicators/
├── desktop-indicator-card.blade.php
└── ...
```

Ini **bukan masalah modular** — ini separation of concern yang baik. Bisa tetap dipertahankan dalam module.

### 7.3 Alpine.js Stores via Blade

Ada 3 file `stores/` yang berisi logika state management Alpine.js:
```
stores/
├── content-syncer.blade.php
├── dashboard-state.blade.php
└── indicators-loader.blade.php
```

Ini adalah **JavaScript-in-Blade** pattern. Saat migrasi, bisa dipertimbangkan untuk:
- Tetap di Blade (paling cepat)
- Atau dipindah ke file JS terpisah di module (lebih clean untuk modular)

### 7.4 Multiple Resource Menunjuk Module Sama

Ada resource yang terpisah tapi secara domain masuk ke module yang sama:
- `ImutDataResource` + `ImutProfileResource` + `ImutCategoryResource` → **ImutMaster**
- `DailyReportEntryResource` + halaman create/edit → **DailyReport**

Ini perlu digabung dalam satu struktur module.

---

## 8. Prinsip Migrasi Views

1. **Jangan pindahkan semua sekaligus** — mulai dari module dengan dependensi paling sedikit
2. **Gunakan `loadViewsFrom()`** di Module Service Provider — ini memungkinkan views module tetap berfungsi selama masa transisi
3. **Path alias vs file move** — di awal, cukup register namespace view tanpa pindah file. File dipindah nanti.
4. **Override priority** — view dari module SP lebih prioritas dari `resources/views/`
5. **Test visual** — setiap selesai migrasi 1 module, cek apakah tampilannya masih sama

---

*Dokumen ini melengkapi rencana modular monolith. Update saat progres migrasi views dimulai.*
