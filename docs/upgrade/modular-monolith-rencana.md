# Rencana Optimasi ke Modular Monolith

> **Dokumen ini berisi analisis dan rencana migrasi dari Laravel Monolith (technical layering) ke Modular Monolith architecture.**
> Dibuat: 2026-06-09 | Berdasarkan analisis kode eksisting.

---

## 1. Kondisi Saat Ini

Project **SIIMUT** saat ini adalah **Laravel Monolith dengan technical slicing** — diorganisir berdasarkan lapisan teknis (Models, Services, Controllers, dll.), bukan berdasarkan domain/bounded context.

### Struktur Namespace Saat Ini

```
app/
├── Console/Commands/
├── Domain/                    ← hanya 1 file, belum benar-benar dipakai
├── Facades/
├── Filament/Resources/*       ← resource per fitur tapi namespace rata
├── Http/Controllers/
├── Livewire/
├── Models/                    ← semua model dalam 1 folder (shared pool)
├── Observers/
├── Policies/
├── Providers/
├── QueryBuilders/
├── Repositories/              ← per domain, tapi namespace App\Repositories
├── Rules/
├── Services/                  ← per fitur, tapi namespace App\Services
├── Traits/
└── Support/
```

### Masalah yang Teridentifikasi

1. **Tidak ada bounded context** — semua model di `App\Models` bisa diimpor service mana pun
2. **Cross-module dependency tanpa batas** — `Services/DailyReport/` bisa langsung `use App\Models\...`
3. **Minimal interface/contract** — hanya 1 binding interface ditemukan
4. **Tidak ada module-level Service Provider**
5. **Komunikasi antar fitur via method call langsung**, bukan domain events
6. **Database migrations monolitik** — tidak ada pemisahan skema per konteks
7. **Tidak ada Anti-Corruption Layer**

### Yang SUDAH Agak Modular (bisa jadi starting point)

| Aspek | Kondisi |
|---|---|
| `Services/` di-group per fitur | ✅ DailyReport, Core, Chart, Form, Reporting, dll. |
| `Repositories/DailyReport/` | ✅ Repository per domain logic |
| `Domain/` folder sudah ada | ✅ Meski baru 1 file (TableViewDomain.php) |
| `Repositories/Interfaces/` | ✅ Folder contracts sudah ada |
| `UnitKerjaProvider` bind interface | ✅ Contoh service provider binding |

---

## 2. Target Arsitektur: Modular Monolith

### Prinsip Modular Monolith

- **Bounded Context** — setiap modul punya ruang lingkup yang jelas
- **Encapsulation** — internal implementation disembunyikan; hanya interface yang publik
- **Independent Deployability** — satu modul bisa diubah tanpa efek samping ke modul lain (dalam konteks monolith: tanpa merusak)
- **Explicit Contracts** — komunikasi antar modul lewat interface/events
- **Shared Kernel** — hal yang benar-benar shared (User, auth, utilities) tetap bersama

### Target Struktur Namespace

```
app/
├── Kernel/                                  ← Shared Kernel
│   ├── Models/         (User, UnitKerja, Role, dll.)
│   ├── Support/        (CacheKey, StorageFallback, dll.)
│   ├── Traits/
│   └── Providers/
│
├── Modules/                                  ← Bounded Contexts
│   │
│   ├── ImutMaster/                          ← Module: Master Data IMUT
│   │   ├── Models/          (ImutData, ImutProfile, ImutCategory, dll.)
│   │   ├── Contracts/
│   │   ├── Services/
│   │   ├── Repositories/
│   │   ├── QueryBuilders/
│   │   ├── Observers/
│   │   ├── Policies/
│   │   ├── Filament/        (Resources, Widgets, Pages)
│   │   └── ImutMasterServiceProvider.php
│   │
│   ├── DailyReport/                          ← Module: Laporan Harian
│   │   ├── Models/          (DailyReportResponse, FieldResponse, dll.)
│   │   ├── Contracts/
│   │   ├── Services/        (Monitoring, Export, dll.)
│   │   ├── Repositories/
│   │   ├── Domain/          (TableViewDomain, ValueObjects)
│   │   ├── Livewire/
│   │   ├── Filament/        (Resources, Widgets, Pages)
│   │   ├── Exports/
│   │   └── DailyReportServiceProvider.php
│   │
│   ├── Laporan/                              ← Module: Laporan IMUT
│   │   ├── Models/          (LaporanImut, LaporanUnitKerja, dll.)
│   │   ├── Contracts/
│   │   ├── Services/
│   │   ├── Filament/
│   │   └── LaporanServiceProvider.php
│   │
│   ├── Benchmarking/                        ← Module: Benchmarking
│   │   ├── Models/          (ImutBenchmarking, RegionType)
│   │   ├── Services/
│   │   ├── Contracts/
│   │   ├── Filament/
│   │   └── BenchmarkingServiceProvider.php
│   │
│   ├── FormEngine/                          ← Module: Form Engine (Dynamic Form)
│   │   ├── Models/          (FormTemplate, EnhancedFormField, FormField, dll.)
│   │   ├── Contracts/
│   │   ├── Services/        (FormBuilder, Validation, Calculation)
│   │   ├── Repositories/
│   │   ├── Filament/
│   │   └── FormEngineServiceProvider.php
│   │
│   └── ... (Chart, Reporting, Authorization, dll.)
```

---

## 3. Fase Migrasi

### Fase 0: Persiapan & Foundation (1-2 sprint)

- [ ] Buat folder `app/Modules/` dan `app/Kernel/`
- [ ] Setup PSR-4 autoload di `composer.json`:
  ```json
  "autoload": {
      "psr-4": {
          "App\\": "app/",
          "App\\Kernel\\": "app/Kernel/",
          "App\\Modules\\": "app/Modules/"
      }
  }
  ```
- [ ] Pindahkan shared code ke `app/Kernel/`
  - Support utilities (CacheKey, StorageFallback, PeriodFilter, dll.)
  - Base traits & helpers
  - Core providers
- [ ] Setup code style & architecture guideline untuk module

### Fase 1: Extract Module Contracts (2-3 sprint)

- [ ] Identifikasi bounded context berdasarkan analisis dependency:
  - `DailyReport` ↔ `FormEngine` (FormTemplate, FormField)
  - `ImutMaster` ↔ `Benchmarking` (ImutData digunakan bersama)
  - `Laporan` ↔ `ImutMaster` (ImutData, ImutProfile)
  - `Authorization` ↔ semua modul
  - `Chart`/`Reporting` ↔ `DailyReport` + `ImutMaster`
- [ ] Buat **interface contracts** untuk komunikasi antar module:
  ```
  app/Modules/DailyReport/Contracts/
  ├── FormEngineInterface.php        ← interface yang diimplement FormEngine
  ├── ImutMasterInterface.php       ← interface yang diimplement ImutMaster
  └── ...
  ```
- [ ] Pindahkan **interface definitions** ke module yang menyediakan
- [ ] Module consumer hanya boleh depend ke interface, bukan ke konkrit

### Fase 2: Ekstraksi Per Module (3-5 sprint)

**Urutan prioritas (dari yang paling independen):**

1. **FormEngine** — paling sedikit dependensi dari modul lain, paling banyak digunakan
2. **ImutMaster** — master data, dependensi minimal
3. **Benchmarking** — depend ke ImutMaster via interface
4. **DailyReport** — depend ke FormEngine + ImutMaster + Authorization
5. **Laporan** — depend ke ImutMaster + Reporting
6. **Chart / Reporting** — lapisan presentasi agregasi

Setiap ekstraksi mencakup:
- [ ] Buat namespace module lengkap
- [ ] Pindahkan Models
- [ ] Buat ServiceProvider per module
- [ ] Buat interface publik
- [ ] Register via config (`config/modules.php`) atau auto-discover

### Fase 3: Anti-Corruption Layer & Domain Events (3-4 sprint)

- [ ] Untuk setiap relasi antar module, buat **Anti-Corruption Layer**:
  - Module A tidak boleh langsung query table module B
  - Harus lewat interface yang return Data Transfer Object (DTO) bukan Eloquent Model
- [ ] Setup **Domain Events** untuk komunikasi async:
  - `DailyReportSubmitted` → `Laporan` mereact dengan update status
  - `FormTemplateUpdated` → `DailyReport` validasi ulang entry lama
- [ ] Internal event bus (atau mulai dengan Laravel Events)

### Fase 4: Database Isolation (Opsional — 2-3 sprint)

- [ ] Pisahkan migrasi per module:
  ```
  database/migrations/
  ├── kernel/
  ├── modules/
  │   ├── daily_report/
  │   ├── imut_master/
  │   ├── form_engine/
  │   └── ...
  ```
- [ ] Pertimbangkan schema prefix per module (atau tetap satu database)
- [ ] Module hanya boleh migrasi table miliknya sendiri

### Fase 5: Testing & Validasi (Berkelanjutan)

- [ ] Pastikan tidak ada `use App\Models\...` dari module lain
- [ ] Pastikan tidak ada `use App\Services\...` dari module lain secara langsung
- [ ] Unit test per module bisa jalan independen
- [ ] Integration test untuk interaksi antar module via contracts

---

## 4. Detail Per Module

### a. Module: FormEngine

**Cakupan:** Dynamic form templates, field definitions, scoring logic

**Model eksisting:**
- `FormTemplate`
- `EnhancedFormField`
- `FormField`
- `FormFieldOption`
- `FieldResponse`

**Services eksisting:**
- `Services/DynamicForm/DynamicFormService`
- `Services/DynamicForm/ComplianceCalculatorService`
- `Services/Form/FormCalculationService`
- `Services/Form/FormMutationService`
- `Services/Form/FormTemplateVersionService`
- `Services/Form/ImutDataFormBuilderService`
- `Services/FormBuilder/FormDataService`
- `Services/FormBuilder/FormPersistenceService`
- `Services/FormBuilder/FormSchemaBuilder`
- `Services/FormTemplateLoadingService`

**Public contracts (yang diekspos ke modul lain):**
- `FormEngineInterface::getActiveTemplate(int $imutProfileId): TemplateDTO`
- `FormEngineInterface::calculateScore(array $responses): ScoreResult`
- `FormEngineInterface::validateField(string $fieldKey, mixed $value): ValidationResult`

### b. Module: ImutMaster

**Cakupan:** Master data IMUT (indikator, profil, kategori, penilaian)

**Model eksisting:**
- `ImutData`
- `ImutProfile`
- `ImutCategory`
- `ImutPenilaian`
- `ImutDataNote`
- `ImutDataUnitKerja`
- `LaporanImutProfile`

**Services eksisting:**
- `Services/Core/ImutCalculationService`
- `Services/Core/ImutCalculatorService`

### c. Module: DailyReport

**Cakupan:** Laporan harian entry, monitoring, compliance checking

**Model eksisting:**
- `DailyReportResponse`
- `DailyReportEntry`
- `FieldResponse` (shared dengan FormEngine — perlu di-ACL)

**Services eksisting:**
- `Services/DailyReport/DailyReportService`
- `Services/DailyReport/...` (10+ services)
- `Domain/DailyReport/TableViewDomain`

**Dependencies ke modul lain:**
- `FormEngineInterface` (form template, form fields)
- `ImutMasterInterface` (imut data, penilaian)
- `AuthorizationInterface` (siapa boleh akses)

### d. Module: Laporan

**Cakupan:** Laporan bulanan, laporan unit kerja, auto-generation

**Model eksisting:**
- `LaporanImut`
- `LaporanUnitKerja`
- `LaporanImutAutoGenerationSetting`
- `LaporanImutProfile`

### e. Module: Benchmarking

**Cakupan:** Benchmark values per region type

**Model eksisting:**
- `ImutBenchmarking`
- `RegionType`

### f. Module: Chart & Reporting

**Cakupan:** Data visualization, agregasi, statistik

**Services eksisting:**
- `Services/Chart/ChartDataProcessorService`
- `Services/Chart/ImutChartSeriesService`
- `Services/Chart/UnitKerjaChartDataService`
- `Services/Reporting/CategoryAggregationService`
- `Services/Reporting/CategoryReportDataBuilderService`
- `Services/Reporting/DailyReportAggregationService`
- `Services/Reporting/ImutReportService`
- `Services/Reporting/UnitKerjaStatService`

### g. Module: Authorization

**Cakupan:** Permission checking, policy, role-based access

**Eksisting:**
- `Services/Authorization/ImutDataPermissionService`
- `Services/DailyReport/DailyReportAuthorizationService`
- `Policies/*`

---

## 5. Komponen Shared Kernel

Hal-hal yang tetap di `app/Kernel/` karena dipakai semua modul:

```
app/Kernel/
├── Models/
│   ├── User.php
│   ├── UnitKerja.php
│   ├── Role.php
│   ├── UserUnitKerja.php
│   └── ...
├── Support/
│   ├── CacheKey.php
│   ├── StorageFallback.php
│   ├── PeriodFilter.php
│   ├── ApexChartConfig.php
│   └── ...
├── Traits/
├── Facades/
├── Services/Support/       ← DateFormattingService, GreetingService, dll.
└── Providers/
    ├── AppServiceProvider.php
    └── AuthServiceProvider.php
```

---

## 6. Pola Anti-Corruption Layer (ACL)

Setiap kali Module A perlu data Module B, jangan langsung Eloquent:

```php
// ❌ BURUK — langsung use model modul lain
use App\Models\FormTemplate;
$template = FormTemplate::find($id);  // DailyReport module akses langsung FormEngine model

// ✅ BAIK — lewat interface contract
use App\Modules\FormEngine\Contracts\FormEngineInterface;
$template = $this->formEngine->getActiveTemplate($profileId);
// return TemplateDTO { id, fields: [...], scoringConfig: {...} }
```

**DTO (Data Transfer Object)** memastikan module consumer tidak bergantung pada struktur internal module provider.

---

## 7. Domain Events

Event untuk komunikasi antar module:

| Event | Publisher | Subscriber |
|---|---|---|
| `FormTemplateCreated` | FormEngine | DailyReport (prep matriks), Laporan (re-kalkulasi) |
| `FormTemplateUpdated` | FormEngine | DailyReport (validasi ulang entry) |
| `DailyReportSubmitted` | DailyReport | Laporan (update status), Reporting (refresh agregasi) |
| `DailyReportApproved` | DailyReport | Laporan (trigger monthly report), Notification |
| `ImutProfileActivated` | ImutMaster | FormEngine (load template), Benchmarking (sinkron) |
| `LaporanImutGenerated` | Laporan | Notification, Reporting |

---

## 8. Risiko & Mitigasi

| Risiko | Mitigasi |
|---|---|
| **Merge conflicts berkepanjangan** selama restruktur | Lakukan bertahap, fitur per fitur. Jangan refactor + feature dalam 1 PR |
| **Regression** karena namespace berubah | alias/forward compatibility di awal fase; test coverage sebelum restruktur |
| **Performance** karena DTO conversion di ACL | Lazy loading DTO, cache di contract level |
| **Team resistance** — perubahan besar | Edukasi: Modular Monolith ≠ Microservices. Tidak perlu docker, tetap satu deploy |
| **Filament resource references** pindah namespace | Buat alias/seeder di service provider tiap module |
| **Query builder & report yang kompleks** | Report tetap boleh query langsung read-only via ACL. CQRS-light |
| **Policies & Gates berubah namespace** | Register ulang policies di module SP |

---

## 9. Estimasi Timeline (Optimistic)

| Fase | Durasi | Sprint |
|---|---|---|
| Fase 0: Foundation | 1-2 minggu | Sprint 1 |
| Fase 1: Contracts | 2-3 minggu | Sprint 2-3 |
| Fase 2: Extract FormEngine | 1-2 minggu | Sprint 3-4 |
| Fase 2: Extract ImutMaster | 1-2 minggu | Sprint 4-5 |
| Fase 2: Extract DailyReport | 2-3 minggu | Sprint 5-7 |
| Fase 2: Extract Laporan | 1-2 minggu | Sprint 7-8 |
| Fase 2: Extract sisanya | 2-3 minggu | Sprint 8-10 |
| Fase 3: ACL + Domain Events | 3-4 minggu | Sprint 10-14 |
| Fase 4: Database Isolation | 2-3 minggu | Sprint 14-16 |
| **Total** | **~16 minggu** | **~4 bulan** |

---

## 10. Catatan Tambahan

- **Jangan memisahkan database dulu** — Modular Monolith dimulai dari kode, bukan database. Pisahkan skema nanti jika benefitnya jelas (deploy independence, scaling).
- **Prioritaskan contract isolation** di atas pemindahan file. Sebuah service bisa tetap di folder lama asal semua consumer lewat interface.
- **Filament Resources** tetap bisa di `app/Filament/` selama masa transisi, dengan forwarder ke module internal.
- **Livewire components** perlu dipindah ke masing-masing module karena terkait erat dengan domain logic.
- **Gunakan `composer dump-autoload` + test suite** untuk validasi tidak ada broken import setelah setiap fase.

---

*Dokumen ini adalah living document — update saat migrasi berlangsung dan temuan baru muncul.*
