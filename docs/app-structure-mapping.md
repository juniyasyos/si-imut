# Dokumentasi Struktur Folder App - SI-IMUT

## Overview
Dokumen ini memetakan struktur folder `app/` dalam aplikasi SI-IMUT (Sistem Informasi IMUT) yang dibangun menggunakan Laravel dengan Filament sebagai admin panel.

## Struktur Utama Folder App

```
app/
├── Console/            # Artisan commands
├── Facades/            # Custom facades
├── Filament/           # Filament admin panel components
├── Http/               # HTTP layer (Controllers, Middleware, Requests)
├── Jobs/               # Queue jobs
├── Livewire/           # Livewire components
├── Models/             # Eloquent models
├── Notifications/      # Notification classes
├── Observers/          # Model observers
├── Policies/           # Authorization policies
├── Providers/          # Service providers
├── Repositories/       # Repository pattern implementation
├── Rules/              # Custom validation rules
├── Services/           # Business logic services
├── Settings/           # Application settings
├── Support/            # Helper classes and utilities
├── Tables/             # Table-related classes
├── Traits/             # Reusable traits
└── View/               # View composers and view-related classes
```

---

## 1. Console/ - Artisan Commands

**Namespace:** `App\Console`

### Struktur:
```
Console/
└── Commands/
```

**Fungsi:** Berisi custom Artisan commands untuk automation dan maintenance tasks.

---

## 2. Facades/ - Custom Facades

**Namespace:** `App\Facades`

**Fungsi:** Berisi custom facade classes untuk memberikan akses statis ke service classes.

---

## 3. Filament/ - Admin Panel Components

**Namespace:** `App\Filament`

### Struktur Detail:
```
Filament/
├── Forms/                      # Form components
│   └── ImutBenchmarkingForm.php
├── Imports/                    # Data import classes
│   └── UserImporter.php
├── Pages/                      # Custom pages
│   ├── Dashboard.php
│   └── Login.php
├── Resources/                  # CRUD resources
│   ├── ActivitylogResource.php
│   ├── FolderCustomResource.php
│   ├── ImutCategoryResource.php
│   ├── ImutDataResource.php
│   ├── ImutPenilaianResource.php
│   ├── ImutProfileResource.php
│   ├── LaporanImutResource.php
│   ├── MediaCustomResource.php
│   ├── RegionTypeBencmarkingResource.php
│   ├── RoleResource.php
│   ├── UnitKerjaResource.php
│   ├── UserResource.php
│   │
│   ├── [ResourceName]/         # Resource sub-components
│   │   ├── Pages/             # Resource pages (List, Create, Edit, View)
│   │   ├── RelationManagers/  # Relationship managers
│   │   ├── Schema/            # Form schemas
│   │   ├── Tables/            # Table configurations
│   │   ├── Widgets/           # Resource-specific widgets
│   │   └── Forms/             # Custom forms
│
└── Widgets/                    # Dashboard widgets
    ├── AccountWidget.php
    ├── DashboardSiimutOverview.php
    ├── FilamentInfoWidget.php
    ├── ImutCapaianWidget.php
    ├── ImutCapaianUnitKerjaWidget.php
    ├── ImutTercapai.php
    ├── LaporanLatestWidget.php
    └── UnitKerja/
        ├── StatsForUnitKerja.php
        └── UnitKerjaInfo.php
```

### Key Classes:

#### Resources (CRUD Management):
- **ActivitylogResource**: Mengelola log aktivitas sistem
- **ImutCategoryResource**: Manajemen kategori IMUT
- **ImutDataResource**: Manajemen data IMUT utama
- **ImutPenilaianResource**: Manajemen penilaian IMUT
- **ImutProfileResource**: Manajemen profil IMUT
- **LaporanImutResource**: Manajemen laporan IMUT
- **UnitKerjaResource**: Manajemen unit kerja
- **UserResource**: Manajemen pengguna
- **RoleResource**: Manajemen peran/role

#### Widgets (Dashboard Components):
- **DashboardSiimutOverview**: Overview utama dashboard
- **ImutCapaianWidget**: Widget capaian IMUT
- **ImutTercapai**: Widget IMUT yang tercapai
- **LaporanLatestWidget**: Widget laporan terbaru

---

## 4. Http/ - HTTP Layer

**Namespace:** `App\Http`

### Struktur:
```
Http/
├── Controllers/        # HTTP controllers
├── Middleware/         # HTTP middleware
└── Requests/          # Form request validation
```

### Key Classes:
- **Controller**: Base controller class

---

## 5. Jobs/ - Queue Jobs

**Namespace:** `App\Jobs`

**Fungsi:** Berisi job classes untuk background processing dan queue operations.

---

## 6. Livewire/ - Livewire Components

**Namespace:** `App\Livewire`

### Key Classes:
- **UnitKerjaReport**: Komponen laporan unit kerja
- **ImutDataUnitKerjaReport**: Laporan data IMUT per unit kerja
- **ImutDataReport**: Laporan data IMUT
- **ImutDataOverview**: Overview data IMUT
- **ImutDataUnitKerjaOverviewTable**: Tabel overview data IMUT unit kerja
- **UnitKerjaImutDataReport**: Laporan data IMUT untuk unit kerja

**Fungsi:** Interactive components untuk real-time UI updates tanpa page refresh.

---

## 7. Models/ - Eloquent Models

**Namespace:** `App\Models`

### Core Models:

#### User Management:
- **User**: Model pengguna dengan authentication dan authorization
- **Role**: Model peran/role sistem
- **Position**: Model posisi/jabatan

#### Unit Kerja:
- **UnitKerja**: Model unit kerja/organisasi
- **UserUnitKerja**: Pivot model relasi user-unit kerja

#### IMUT System:
- **ImutData**: Model data IMUT utama
- **ImutProfile**: Model profil IMUT
- **ImutCategory**: Model kategori IMUT
- **ImutPenilaian**: Model penilaian IMUT (dengan media support)
- **ImutDataUnitKerja**: Pivot model relasi IMUT-unit kerja
- **ImutBenchmarking**: Model benchmarking IMUT

#### Reporting:
- **LaporanImut**: Model laporan IMUT
- **LaporanUnitKerja**: Model laporan unit kerja

#### Geographic:
- **RegionType**: Model tipe region/wilayah

### Model Relationships:
```
User ←→ UnitKerja (Many-to-Many through UserUnitKerja)
UnitKerja ←→ ImutData (Many-to-Many through ImutDataUnitKerja)
ImutData ←→ ImutProfile (One-to-Many)
ImutData → ImutCategory (Many-to-One)
ImutPenilaian → ImutData (Many-to-One)
User → Role (Many-to-One)
```

---

## 8. Notifications/ - Notification Classes

**Namespace:** `App\Notifications`

### Key Classes:
- **BackupNotifiable**: Notifikasi untuk backup sistem

**Fungsi:** Menangani notifikasi email, SMS, dan channel lainnya.

---

## 9. Observers/ - Model Observers

**Namespace:** `App\Observers`

**Fungsi:** Observer classes untuk mendengarkan model events (creating, created, updating, etc.).

---

## 10. Policies/ - Authorization Policies

**Namespace:** `App\Policies`

### Key Classes:
- **UserPolicy**: Otorisasi untuk model User
- **RolePolicy**: Otorisasi untuk model Role
- **UnitKerjaPolicy**: Otorisasi untuk model UnitKerja
- **ImutDataPolicy**: Otorisasi untuk model ImutData
- **ImutProfilePolicy**: Otorisasi untuk model ImutProfile
- **ImutCategoryPolicy**: Otorisasi untuk model ImutCategory
- **ImutPenilaianPolicy**: Otorisasi untuk model ImutPenilaian
- **LaporanImutPolicy**: Otorisasi untuk model LaporanImut
- **RegionTypePolicy**: Otorisasi untuk model RegionType
- **ActivityPolicy**: Otorisasi untuk log aktivitas
- **MediaCustomPolicy**: Otorisasi untuk media files
- **FolderCustomPolicy**: Otorisasi untuk folder management

**Fungsi:** Mendefinisikan rules otorisasi untuk setiap model/action.

---

## 11. Providers/ - Service Providers

**Namespace:** `App\Providers`

### Key Classes:
- **AppServiceProvider**: Service provider utama aplikasi
- **AuthServiceProvider**: Service provider untuk authentication/authorization
- **AdminPanelProvider**: Service provider untuk Filament admin panel
- **UnitKerjaProvider**: Service provider khusus unit kerja
- **LaporanImutServiceProvider**: Service provider untuk laporan IMUT

**Fungsi:** Bootstrap services dan bind dependencies ke service container.

---

## 12. Repositories/ - Repository Pattern

**Namespace:** `App\Repositories`

**Fungsi:** Implementasi repository pattern untuk data access layer abstraction.

---

## 13. Rules/ - Custom Validation Rules

**Namespace:** `App\Rules`

**Fungsi:** Custom validation rules yang dapat digunakan dalam form validation.

---

## 14. Services/ - Business Logic Services

**Namespace:** `App\Services`

### Struktur Detail:
```
Services/
├── ApiService.php                      # Service untuk API operations
├── DashboardImutService.php           # Service untuk dashboard IMUT
├── ImutChartSeriesService.php         # Service untuk chart series
├── ImutPenilaianService.php           # Service untuk penilaian IMUT
├── LaporanImutService.php             # Service untuk laporan IMUT
├── LaporanRedirectService.php         # Service untuk redirect laporan
├── UnitKerjaStatService.php           # Service untuk statistik unit kerja
├── Authorization/                      # Authorization services
├── Calculator/                         # Calculation services
│   └── ImutCalculatorService.php
├── Chart/                             # Chart-related services
│   ├── ChartDataProcessorService.php
│   └── UnitKerjaChartDataService.php
├── Data/                              # Data processing services
│   └── DateFormattingService.php
└── Form/                              # Form-related services
    ├── FormCalculationService.php
    └── ImutDataFormBuilderService.php
```

### Key Service Categories:

#### Core Business Services:
- **ImutPenilaianService**: Logic untuk penilaian IMUT
- **LaporanImutService**: Logic untuk laporan IMUT
- **DashboardImutService**: Logic untuk dashboard

#### Calculation Services:
- **ImutCalculatorService**: Kalkulasi IMUT
- **FormCalculationService**: Kalkulasi form

#### Chart & Data Services:
- **ChartDataProcessorService**: Pemrosesan data chart
- **UnitKerjaChartDataService**: Data chart unit kerja
- **ImutChartSeriesService**: Chart series untuk IMUT

#### Utility Services:
- **DateFormattingService**: Format tanggal
- **UnitKerjaStatService**: Statistik unit kerja

---

## 15. Settings/ - Application Settings

**Namespace:** `App\Settings`

**Fungsi:** Configuration dan settings management untuk aplikasi.

---

## 16. Support/ - Helper Classes

**Namespace:** `App\Support`

### Key Classes:
- **CacheKey**: Konstanta untuk cache keys
- **ApexChartConfig**: Konfigurasi untuk ApexCharts

**Fungsi:** Utility classes, helpers, dan constants.

---

## 17. Tables/ - Table Classes

**Namespace:** `App\Tables`

**Fungsi:** Custom table implementations dan table-related logic.

---

## 18. Traits/ - Reusable Traits

**Namespace:** `App\Traits`

**Fungsi:** Traits yang dapat digunakan ulang di berbagai class untuk shared functionality.

---

## 19. View/ - View-Related Classes

**Namespace:** `App\View`

**Fungsi:** View composers, view creators, dan view-related logic.

---

## Pola Arsitektur yang Digunakan

### 1. **Repository Pattern**
- Abstraksi data access layer
- Memisahkan business logic dari data layer

### 2. **Service Layer Pattern**
- Business logic dipisahkan ke service classes
- Services di-inject ke controllers/resources

### 3. **Policy-Based Authorization**
- Setiap model memiliki policy class
- Centralized authorization logic

### 4. **Resource Pattern (Filament)**
- CRUD operations diorganisir dalam Resource classes
- Setiap resource memiliki Pages, Tables, Schemas

### 5. **Observer Pattern**
- Model events di-handle dengan Observer classes

---

## Namespace Mapping

| Folder | Namespace | Purpose |
|--------|-----------|---------|
| `Console/` | `App\Console` | Artisan commands |
| `Facades/` | `App\Facades` | Custom facades |
| `Filament/` | `App\Filament` | Admin panel components |
| `Http/` | `App\Http` | HTTP layer |
| `Jobs/` | `App\Jobs` | Background jobs |
| `Livewire/` | `App\Livewire` | Interactive components |
| `Models/` | `App\Models` | Database models |
| `Notifications/` | `App\Notifications` | Notification classes |
| `Observers/` | `App\Observers` | Model observers |
| `Policies/` | `App\Policies` | Authorization policies |
| `Providers/` | `App\Providers` | Service providers |
| `Repositories/` | `App\Repositories` | Data repositories |
| `Rules/` | `App\Rules` | Validation rules |
| `Services/` | `App\Services` | Business logic |
| `Settings/` | `App\Settings` | App settings |
| `Support/` | `App\Support` | Utilities |
| `Tables/` | `App\Tables` | Table classes |
| `Traits/` | `App\Traits` | Reusable traits |
| `View/` | `App\View` | View-related |

---

## Key Features Sistem

### 1. **IMUT Management**
- Data IMUT (ImutData, ImutProfile, ImutCategory)
- Penilaian IMUT (ImutPenilaian)
- Benchmarking (ImutBenchmarking)

### 2. **Unit Kerja Management**
- Manajemen unit kerja organisasi
- Relasi user-unit kerja
- Statistik dan reporting per unit kerja

### 3. **User & Role Management**
- Authentication & authorization
- Role-based access control
- Position management

### 4. **Reporting System**
- Laporan IMUT
- Laporan unit kerja
- Dashboard dengan widgets

### 5. **Chart & Analytics**
- Chart data processing
- Interactive dashboards
- Real-time statistics

---

*Generated on: 2025-10-15*
*SI-IMUT Application Structure Documentation*
