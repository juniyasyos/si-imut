# Optimasi Pengembangan ke Modular Monolith

> **Panduan teknis implementasi Modular Monolith untuk SIIMUT.**
> Melengkapi: [modular-monolith-rencana.md](modular-monolith-rencana.md), [views-modular-analisis.md](views-modular-analisis.md)
> Tanggal: 2026-06-09

---

## Daftar Isi

1. [Prinsip Dasar](#1-prinsip-dasar)
2. [Strategi Migrasi: Strangler Fig Pattern](#2-strategi-migrasi-strangler-fig-pattern)
3. [Module Bootstrap Kit](#3-module-bootstrap-kit)
4. [Konvensi Kode](#4-konvensi-kode)
5. [Pola Komunikasi Antar Module](#5-pola-komunikasi-antar-module)
6. [Optimasi Filament & Livewire per Module](#6-optimasi-filament--livewire-per-module)
7. [Database & Migration Strategy](#7-database--migration-strategy)
8. [Testing Strategy](#8-testing-strategy)
9. [Development Workflow](#9-development-workflow)
10. [Tooling & Automation](#10-tooling--automation)
11. [Checklist Migrasi per Module](#11-checklist-migrasi-per-module)
12. [Troubleshooting](#12-troubleshooting)

---

## 1. Prinsip Dasar

### 1.1 Aturan Emas Modular Monolith

1. **Module A tidak boleh `use` class concrete Module B** — hanya lewat interface/event
2. **Module A tidak boleh query table Module B** — lewat repository interface
3. **Module A tidak boleh `@include` view Module B secara langsung** — lewat namespace view
4. **Shared Kernel (User, UnitKerja, Role) milik semua module** — tidak perlu diisolasi
5. **Domain Events untuk komunikasi async** — bukan method call langsung
6. **Anti-Corruption Layer (ACL) untuk komunikasi sync** — via DTO, bukan Eloquent Model

### 1.2 Visual Dependency Graph

```
                   ┌─────────────────────────────────────┐
                   │           Shared Kernel              │
                   │  (User, UnitKerja, Support, Traits)  │
                   └──────────┬──────────────────────────┘
                              │ depends on
         ┌────────────────────┼────────────────────┐
         │                    │                    │
         ▼                    ▼                    ▼
   ┌──────────┐       ┌──────────────┐     ┌────────────┐
   │FormEngine│◄──────│ DailyReport  │────►│  Laporan   │
   │(no deps) │       │(dep: FormEng,│     │(dep: Imut, │
   │          │       │ ImutMaster,  │     │ DailyRpt)  │
   └──────────┘       │ Auth)        │     └────────────┘
         │            └──────────────┘           │
         ▼                                       │
   ┌──────────┐                                   │
   │ImutMaster│◄──────────────────────────────────┘
   │(no deps) │         ┌──────────────┐
   └──────────┘◄───────│ Benchmarking │
                        │(dep: Imut)   │
                        └──────────────┘
```

### 1.3 Urutan Ekstraksi Module (Topological Order)

Modules harus di-extract dalam urutan **dependensi** — module tanpa dependensi ekstrak dulu:

| Urutan | Module | Depend Ke | Digunakan Oleh |
|---|---|---|---|
| 1 | **FormEngine** | — | DailyReport |
| 2 | **ImutMaster** | — | DailyReport, Laporan, Benchmarking |
| 3 | **Authorization** | ImutMaster | Semua |
| 4 | **DailyReport** | FormEngine, ImutMaster, Auth | Laporan |
| 5 | **Benchmarking** | ImutMaster | Laporan |
| 6 | **Chart/Reporting** | DailyReport, ImutMaster | Laporan |
| 7 | **Laporan** | ImutMaster, DailyReport, Chart | — |

---

## 2. Strategi Migrasi: Strangler Fig Pattern

Migrasi dilakukan **bertahap per module** tanpa menghentikan development fitur baru.

```
Fase 0         Fase 1               Fase 2               Fase 3
[Monolith] → [Module Boundary] → [Extract Module] → [Isolate Fully]
                ↓                    ↓                    ↓
           Old code still         Old code can         Old code dead
           works via alias        still be used        — all via module
           but new code           but new code          contracts
           uses module            forced to use
                                  module contract
```

### 2.1 Fase Boundary — Implementasi Paralel

Module baru dibuat **di samping** kode lama. Keduanya coexist.

```php
// ❌ LAMA — langsung
use App\Models\FormTemplate;
$data = FormTemplate::find($id);

// ✅ BARU — lewat contract (parallel implementation)
use App\Modules\FormEngine\Contracts\FormEngineInterface;

class DailyReportService
{
    public function __construct(
        private FormEngineInterface $formEngine
    ) {}
    
    public function getTemplate(int $profileId): TemplateDTO
    {
        return $this->formEngine->getActiveTemplate($profileId);
    }
}
```

**Adapter untuk backward compatibility:**

```php
// Modules/FormEngine/FormEngineService.php (internal)
class FormEngineService implements FormEngineInterface
{
    public function getActiveTemplate(int $profileId): TemplateDTO
    {
        // Panggil service LAMA dulu
        $template = (new \App\Services\FormTemplateLoadingService)->load($profileId);
        return new TemplateDTO(...);
    }
}
```

Ketika semua consumer sudah migrasi ke `FormEngineInterface`, kode lama di `app/Services/` bisa dihapus.

### 2.2 Feature Flag per Module

Gunakan config untuk toggle module:

```php
// config/modules.php
return [
    'daily_report' => [
        'enabled' => env('MODULE_DAILY_REPORT_ENABLED', true),
        'version' => 'legacy', // 'legacy' | 'boundary' | 'extracted'
    ],
    'form_engine' => [
        'enabled' => env('MODULE_FORM_ENGINE_ENABLED', true),
        'version' => 'extracted',
    ],
];
```

```php
// Modules/DailyReport/DailyReportServiceProvider.php
public function register(): void
{
    if (config('modules.daily_report.version') === 'extracted') {
        // register module provider penuh
        $this->loadViewsFrom(__DIR__.'/resources/views', 'daily-report');
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
```

---

## 3. Module Bootstrap Kit

Template untuk membuat module baru — copy-paste siap pakai.

### 3.1 Struktur Folder Module

```
app/Modules/{ModuleName}/
├── Contracts/
│   ├── {ModuleName}Interface.php
│   └── DTOs/
│       └── {Entity}DTO.php
├── Database/
│   └── Migrations/
│       └── {date}_create_{table}_table.php
├── Exceptions/
│   └── {ModuleName}Exception.php
├── Filament/
│   ├── Resources/
│   ├── Pages/
│   ├── Widgets/
│   └── Schemas/
├── Http/
│   └── Controllers/
├── Livewire/
├── Models/
├── Observers/
├── Policies/
├── Repositories/
├── Resources/
│   └── Views/
├── Services/ (internal)
│   └── {ModuleName}Service.php
├── Events/
│   ├── {Entity}Created.php
│   └── Listeners/
├── {ModuleName}ServiceProvider.php
├── routes.php
└── helpers.php (opsional)
```

### 3.2 Module Service Provider Template

```php
<?php

namespace App\Modules\{ModuleName};

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class {ModuleName}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 1. Bind contracts
        $this->app->bind(
            Contracts\{ModuleName}Interface::class,
            Services\{ModuleName}Service::class
        );

        // 2. Register views
        $this->loadViewsFrom(__DIR__.'/resources/views', '{module-slug}');

        // 3. Register migrations (hanya jika sudah di fase 3)
        // $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }

    public function boot(): void
    {
        // 1. Register routes
        $this->registerRoutes();

        // 2. Register policies
        // $this->registerPolicies();

        // 3. Register observers
        // {Model}::observe(Observers\{Model}Observer::class);
    }

    protected function registerRoutes(): void
    {
        Route::middleware('web')
            ->prefix('{module-slug}')
            ->group(__DIR__.'/routes.php');
    }
}
```

### 3.3 Contract Interface Template

```php
<?php

namespace App\Modules\{ModuleName}\Contracts;

use App\Modules\{ModuleName}\Contracts\DTOs\{Entity}DTO;

interface {ModuleName}Interface
{
    /**
     * Mendapatkan entity by ID.
     * 
     * @return {Entity}DTO Data Transfer Object — bukan Eloquent Model
     */
    public function findById(int $id): ?{Entity}DTO;

    /**
     * Mendapatkan daftar entity untuk profile tertentu.
     *
     * @return array<{Entity}DTO>
     */
    public function getByProfile(int $profileId): array;

    /**
     * Menyimpan entity baru.
     */
    public function store(array $data): {Entity}DTO;
}
```

### 3.4 DTO Template

```php
<?php

namespace App\Modules\{ModuleName}\Contracts\DTOs;

class {Entity}DTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly array $meta = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            meta: $data['meta'] ?? [],
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'meta' => $this->meta,
        ];
    }
}
```

### 3.5 Makefile / Bash Script untuk Generate Module

```bash
# bin/make-module
#!/bin/bash
NAME=$1
SLUG=$(echo $NAME | sed 's/\([A-Z]\)/-\1/g' | sed 's/^-//' | tr '[:upper:]' '[:lower:]')
BASE="app/Modules/$NAME"

mkdir -p "$BASE"/{Contracts/DTOs,Database/Migrations,Exceptions,Filament/{Resources,Pages,Widgets,Schemas},Http/Controllers,Livewire,Models,Observers,Policies,Repositories,Services,Resources/Views,Events/Listeners}

# Service Provider
cat > "$BASE/${NAME}ServiceProvider.php" << EOF
<?php

namespace App\Modules\\${NAME};

use Illuminate\Support\ServiceProvider;

class ${NAME}ServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        \$this->app->bind(
            Contracts\\${NAME}Interface::class,
            Services\\${NAME}Service::class
        );
        \$this->loadViewsFrom(__DIR__.'/resources/views', '${SLUG}');
    }

    public function boot(): void
    {
        //
    }
}
EOF

# Interface
cat > "$BASE/Contracts/${NAME}Interface.php" << EOF
<?php

namespace App\Modules\\${NAME}\Contracts;

interface ${NAME}Interface
{
    //
}
EOF

echo "✅ Module $NAME created at $BASE"
```

---

## 4. Konvensi Kode

### 4.1 Namespace Convention

| Area | Pattern |
|---|---|
| Module root | `App\Modules\{ModuleName}` |
| Contract / Interface | `App\Modules\{ModuleName}\Contracts\{Name}Interface` |
| DTO | `App\Modules\{ModuleName}\Contracts\DTOs\{Name}DTO` |
| Service | `App\Modules\{ModuleName}\Services\{Name}Service` |
| Repository | `App\Modules\{ModuleName}\Repositories\{Name}Repository` |
| Model | `App\Modules\{ModuleName}\Models\{Name}` |
| Event | `App\Modules\{ModuleName}\Events\{Name}Event` |
| Exception | `App\Modules\{ModuleName}\Exceptions\{Name}Exception` |
| Filament Resource | `App\Modules\{ModuleName}\Filament\Resources\{Name}Resource` |
| Filament Widget | `App\Modules\{ModuleName}\Filament\Widgets\{Name}Widget` |
| Livewire | `App\Modules\{ModuleName}\Livewire\{Name}` |
| View namespace | `{module-slug}::path.to.view` |
| Migration | `{date}_{action}_{table}_table.php` (prefixed) |

### 4.2 Slug Convention per Module

| Module | PHP Namespace | View Alias | DB Prefix (opsional) |
|---|---|---|---|
| FormEngine | `App\Modules\FormEngine` | `form-engine` | `fe_` |
| ImutMaster | `App\Modules\ImutMaster` | `imut-master` | `im_` |
| DailyReport | `App\Modules\DailyReport` | `daily-report` | `dr_` |
| Laporan | `App\Modules\Laporan` | `laporan` | `lp_` |
| Benchmarking | `App\Modules\Benchmarking` | `benchmarking` | `bm_` |
| Authorization | `App\Modules\Authorization` | `authz` | `az_` |
| Reporting | `App\Modules\Reporting` | `reporting` | `rp_` |

### 4.3 Filament Resource Naming

```php
// ❌ LAMA — namespace rata
App\Filament\Resources\ImutDataResource::class

// ✅ BARU — dalam module
App\Modules\ImutMaster\Filament\Resources\ImutDataResource::class
```

**Auto-discovery Filament panels registration:**

```php
// Modules/{ModuleName}/Filament/{ModuleName}Plugin.php
class {ModuleName}Plugin implements Plugin
{
    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                Resources\{Entity1}Resource::class,
                Resources\{Entity2}Resource::class,
            ])
            ->pages([
                Pages\{CustomPage}::class,
            ])
            ->widgets([
                Widgets\{Widget1}::class,
            ]);
    }
}
```

Kemudian daftarkan di PanelProvider:

```php
// app/Providers/Filament/AdminPanelProvider.php
public function panel(Panel $panel): Panel
{
    return $panel
        ->plugins([
            \App\Modules\DailyReport\Filament\DailyReportPlugin::class,
            \App\Modules\ImutMaster\Filament\ImutMasterPlugin::class,
            \App\Modules\FormEngine\Filament\FormEnginePlugin::class,
            // ...
        ]);
}
```

### 4.4 Livewire Naming

```php
// ❌ LAMA
namespace App\Livewire\Overview;
class ImutDataOverview extends Component {}

// ✅ BARU
namespace App\Modules\ImutMaster\Livewire;
class Overview extends Component {}
```

**Registrasi Livewire di Service Provider:**

```php
// Modules/ImutMaster/ImutMasterServiceProvider.php
use Livewire\Livewire;

public function boot(): void
{
    Livewire::component('imut-master::overview', Livewire\Overview::class);
    Livewire::component('imut-master::data-table', Livewire\DataTable::class);
}
```

---

## 5. Pola Komunikasi Antar Module

### 5.1 Sync Communication — Interface + DTO

```php
// Modules/DailyReport/Services/DailyReportService.php

use App\Modules\FormEngine\Contracts\FormEngineInterface;
use App\Modules\FormEngine\Contracts\DTOs\FormTemplateDTO;

class DailyReportService
{
    public function __construct(
        private FormEngineInterface $formEngine
    ) {}

    public function createReport(array $data): void
    {
        // ✅ Lewat contract — return DTO
        $template = $this->formEngine->getActiveTemplate($data['profile_id']);
        
        // ✅ TemplateDTO hanya berisi data yang diperlukan
        // ✅ Tidak perlu tahu internal FormEngine
        $this->validateAgainstTemplate($data, $template);
    }
}
```

### 5.2 Async Communication — Domain Events

```php
// Modules/DailyReport/Events/DailyReportSubmitted.php
class DailyReportSubmitted
{
    public function __construct(
        public readonly int $reportId,
        public readonly int $unitKerjaId,
        public readonly array $scores,
    ) {}
}

// Modules/Laporan/Listeners/UpdateLaporanStatus.php
class UpdateLaporanStatus
{
    public function handle(DailyReportSubmitted $event): void
    {
        $laporan = Laporan::where('unit_kerja_id', $event->unitKerjaId)->first();
        $laporan->updateDailyReportStatus($event->scores);
    }
}
```

**Registrasi Event:**

```php
// Modules/Laporan/LaporanServiceProvider.php
public function boot(): void
{
    \Illuminate\Support\Facades\Event::listen(
        \App\Modules\DailyReport\Events\DailyReportSubmitted::class,
        Listeners\UpdateLaporanStatus::class
    );
}
```

### 5.3 Data Query — Repository Interface

Module hanya boleh query data miliknya sendiri. Untuk data module lain, lewat contract:

```php
// Modules/DailyReport/Contracts/DailyReportQueryInterface.php
interface DailyReportQueryInterface
{
    /** @return array<DailyReportSummaryDTO> */
    public function getSummariesByPeriod(
        Carbon $start, 
        Carbon $end, 
        ?int $unitKerjaId = null
    ): array;
}

// Modules/Laporan/Services/LaporanService.php
class LaporanService
{
    public function __construct(
        private DailyReportQueryInterface $dailyReports,
        private ImutMasterInterface $imutMaster,
    ) {}

    public function generateReport(int $unitKerjaId, Carbon $month): array
    {
        $profile = $this->imutMaster->getActiveProfile($unitKerjaId);
        $entries = $this->dailyReports->getSummariesByPeriod(
            $month->copy()->startOfMonth(),
            $month->copy()->endOfMonth(),
            $unitKerjaId,
        );
        
        return ['profile' => $profile, 'entries' => $entries];
    }
}
```

### 5.4 Anti-Corruption Layer Pattern

Ketika satu module perlu data dari module lain dengan transformasi:

```php
// Modules/Laporan/Adapters/DailyReportAdapter.php
// ⚠️ Hanya adapter ini yang tahu internal DailyReport
use App\Models\DailyReportResponse; // OLD — hanya adapter boleh akses ini

class DailyReportAdapter implements DailyReportQueryInterface
{
    public function getSummariesByPeriod(Carbon $start, Carbon $end, ?int $unitKerjaId): array
    {
        // Query read-only ke model module lain (hanya di adapter)
        $responses = DailyReportResponse::query()
            ->whereBetween('report_date', [$start, $end])
            ->when($unitKerjaId, fn($q) => $q->where('unit_kerja_id', $unitKerjaId))
            ->get();

        // Return DTO — module consumer tidak tahu Eloquent model
        return $responses->map(fn($r) => DailyReportSummaryDTO::fromArray([
            'id' => $r->id,
            'date' => $r->report_date->format('Y-m-d'),
            'total_score' => $r->total_score,
            'unit_name' => $r->unitKerja?->unit_name,
        ]))->all();
    }
}
```

---

## 6. Optimasi Filament & Livewire per Module

### 6.1 Filament Plugin per Module

Setiap module jadi **Filament Plugin**, yang mendaftarkan Resources, Pages, Widgets-nya sendiri:

```php
// Modules/DailyReport/Filament/DailyReportPlugin.php
class DailyReportPlugin implements Plugin
{
    public function getId(): string
    {
        return 'daily-report';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                Resources\DailyReportEntryResource::class,
            ])
            ->pages([
                Pages\CreateDailyReportEntry::class,
                Pages\EditDailyReportEntry::class,
                Pages\ListDailyReportEntries::class,
            ])
            ->widgets([
                Widgets\LatestReportsWidget::class,
            ]);
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
```

**Keuntungan:**
- Module bisa dienable/disable tanpa ubah PanelProvider
- Plugin bisa punya konfigurasi sendiri
- Resources tidak perlu di-register manual satu-satu

### 6.2 Livewire Component Optimization

Registrasi Livewire component di Service Provider module:

```php
// Modules/DailyReport/DailyReportServiceProvider.php
use Livewire\Livewire;

public function boot(): void
{
    Livewire::component('daily-report.dashboard', Livewire\Dashboard::class);
    Livewire::component('daily-report.monitoring-table', Livewire\MonitoringTable::class);
    
    // Hanya register data-table widget jika module enabled
    if (config('modules.daily-report.widgets.data-table', true)) {
        Livewire::component('daily-report.data-table', Livewire\DataTable::class);
    }
}
```

### 6.3 View Loading Optimization

Hanya load views dari module yang aktif:

```php
// AppServiceProvider.php — batch register
public function register(): void
{
    foreach (config('modules', []) as $module => $cfg) {
        if (!($cfg['enabled'] ?? true)) continue;
        
        $providerClass = "App\\Modules\\{$module}\\{$module}ServiceProvider";
        if (class_exists($providerClass)) {
            $this->app->register($providerClass);
        }
    }
}
```

### 6.4 Widget Hanya Load Sesuai Module

```php
// Modules/DailyReport/Filament/Widgets/LatestReportsWidget.php
class LatestReportsWidget extends Widget
{
    protected function getData(): array
    {
        // ✅ Aman — hanya panggil module sendiri via contract
        return app(DailyReportQueryInterface::class)
            ->getLatest(limit: 5);
    }
}
```

---

## 7. Database & Migration Strategy

### 7.1 Satu Database — Migration per Module

Tetap satu database MySQL, tapi migrasi dipisah per module:

```
database/migrations/
├── kernel/                              ← Shared Kernel
│   ├── 0001_01_01_000000_create_users_table.php
│   ├── 0001_01_01_000001_create_unit_kerja_table.php
│   └── ...
└── modules/
    ├── form_engine/
    │   ├── 2025_01_01_000001_create_form_templates_table.php
    │   └── 2025_01_01_000002_create_form_fields_table.php
    ├── imut_master/
    │   ├── 2025_01_01_000001_create_imut_data_table.php
    │   └── 2025_01_01_000002_create_imut_profiles_table.php
    ├── daily_report/
    │   └── 2025_01_01_000001_create_daily_report_responses_table.php
    └── ...
```

### 7.2 Migration Path Resolution di Module

```php
// Modules/FormEngine/FormEngineServiceProvider.php
public function register(): void
{
    if ($this->app->runningInConsole()) {
        $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
    }
}
```

Tambahkan path di `config/database.php` atau registrasi di `AppServiceProvider`:

```php
// app/Providers/AppServiceProvider.php
public function register(): void
{
    // Registrasi migration path per module
    $modules = ['FormEngine', 'ImutMaster', 'DailyReport', 'Laporan', 'Benchmarking'];
    foreach ($modules as $module) {
        $path = app_path("Modules/{$module}/Database/Migrations");
        if (is_dir($path)) {
            $this->loadMigrationsFrom($path);
        }
    }
}
```

### 7.3 Foreign Key Convention

Foreign key antar module tetap diperbolehkan (satu database), tapi harus melalui interface:

```php
// Modules/DailyReport/Models/DailyReportResponse.php
class DailyReportResponse extends Model
{
    public function formTemplate()
    {
        // ⚠️ Dibiarkan relasi Eloquent — tapi di service layer hanya lewat DTO
        return $this->belongsTo(\App\Modules\FormEngine\Models\FormTemplate::class);
    }
}
```

---

## 8. Testing Strategy

### 8.1 Unit Test per Module (Isolated)

```php
// tests/Unit/Modules/DailyReport/DailyReportServiceTest.php
class DailyReportServiceTest extends TestCase
{
    public function test_can_create_daily_report(): void
    {
        // Mock interface dari module lain
        $formEngine = $this->createMock(FormEngineInterface::class);
        $formEngine->method('getActiveTemplate')
            ->willReturn(new FormTemplateDTO(
                id: 1,
                name: 'Test Template',
                fields: [],
            ));

        $service = new DailyReportService($formEngine, /* ... */);
        $result = $service->createReport([...]);
        
        $this->assertInstanceOf(DailyReportDTO::class, $result);
    }
}
```

### 8.2 Integration Test per Module (With DB)

```php
// tests/Feature/Modules/DailyReport/DailyReportTest.php
class DailyReportTest extends TestCase
{
    // ✅ Hanya migrasi module ini + kernel
    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate:fresh', [
            '--path' => [
                'database/migrations/kernel',
                'app/Modules/DailyReport/Database/Migrations',
            ],
        ]);
    }
    
    public function test_full_report_flow(): void
    {
        // Test dengan real DB, mock hanya interface antar module
    }
}
```

### 8.3 Cross-Module Smoke Test

```php
// tests/Feature/Integration/ModuleIntegrationTest.php
class ModuleIntegrationTest extends TestCase
{
    public function test_all_module_service_providers_can_register(): void
    {
        $modules = ['FormEngine', 'ImutMaster', 'DailyReport', 'Laporan', 'Benchmarking'];
        
        foreach ($modules as $module) {
            $provider = "App\\Modules\\{$module}\\{$module}ServiceProvider";
            $this->assertTrue(class_exists($provider));
            
            $instance = new $provider($this->app);
            $instance->register();
            
            // Inject dependencies dan test service bisa di-resolve
            $interface = "App\\Modules\\{$module}\\Contracts\\{$module}Interface";
            if (interface_exists($interface)) {
                $this->assertNotNull($this->app->make($interface));
            }
        }
    }
}
```

### 8.4 Visual Regression untuk Views

```php
// tests/Feature/Modules/DailyReport/Views/ReportViewTest.php
class ReportViewTest extends TestCase
{
    public function test_monitoring_view_renders(): void
    {
        $view = $this->view('daily-report::monitoring.view', [
            'data' => [...],
        ]);
        
        $view->assertSee('Monitoring');
        $view->assertDontSee('Error');
    }
}
```

---

## 9. Development Workflow

### 9.1 Git Branch Strategy

```
main
├── chore/modular/foundation        ← Setup folder, autoload, config
├── chore/modular/form-engine        ← Extract FormEngine module
├── chore/modular/imut-master        ← Extract ImutMaster module
├── chore/modular/daily-report       ← Extract DailyReport module
├── feat/new-feature                 ← Feature baru langsung pakai modular
└── fix/bug-fix                      ← Bug fix tetap di kode lama + buat issue migrasi
```

### 9.2 Standard Commit Message untuk Modular

| Prefix | Example |
|---|---|
| `module:` | `module(form-engine): extract FormTemplate model and repository` |
| `module-bc:` | `module-bc(daily-report): register DailyReportPlugin to Filament panel` |
| `module-event:` | `module-event(laporan): add DailyReportSubmitted listener` |
| `module-test:` | `module-test(imut-master): add unit test for ImutMasterInterface` |
| `module-docs:` | `module-docs: update module dependency graph` |

### 9.3 PR Checklist untuk Setiap Module Migration

```markdown
## Module Migration: {Name}

- [ ] `composer.json` autoload updated
- [ ] Folder structure created with all subdirectories
- [ ] Contract Interface defined
- [ ] DTOs created
- [ ] Service Provider registered
- [ ] Views moved with `loadViewsFrom()`
- [ ] Filament Plugin created
- [ ] Livewire components registered
- [ ] Policies moved
- [ ] Observers moved
- [ ] Database migrations isolated
- [ ] Unit tests pass (isolated)
- [ ] Integration tests pass (with DB)
- [ ] Feature flag working (`legacy` ← → `extracted`)
- [ ] Manual visual check (no UI breakage)
- [ ] All `use App\Models` from other modules replaced with contracts
```

### 9.4 Daily Development Flow

```
1. Pull latest main
2. Work di feature branch
3. Kalau sentuh kode module lain → lewat contract
4. Kalau contract belum ada → bikin contract dulu (Minimal Viable Contract)
5. Kalau butuh data baru di contract → update DTO
6. Test dengan module version = 'boundary' (masih bisa akses kode lama)
7. Setelah feature stabil → update ke 'extracted'
```

---

## 10. Tooling & Automation

### 10.1 Code Sniffer — Laravel Pint Custom

```json
// pint.json
{
  "preset": "laravel",
  "rules": {
    "PSR1": {
      "PSR1.Classes.ClassDeclaration": false
    },
    "ordered_imports": {
      "imports_order": ["class", "const", "function"],
      "sort_algorithm": "alpha"
    }
  }
}
```

Config tambahan untuk modular:

```json
{
  "notName": [
    "app/Modules/*/Database/Migrations/*"
  ]
}
```

### 10.2 Module Dependency Analyzer

```bash
# bin/check-module-deps
#!/bin/bash
MODULE=$1
echo "Checking dependencies for module: $MODULE"

# Cari semua "use App" dalam folder module
grep -rn "use App\\\\" "app/Modules/$MODULE" --include="*.php" | \
  grep -v "use App\\\\Modules\\\\$MODULE" | \
  grep -v "use App\\\\Kernel\\\\" | \
  grep -v "use App\\\\Providers\\\\" | \
  grep -v "use App\\\\Console\\\\" || echo "✅ No external module dependency violations found"
```

### 10.3 Module Integrity CI Check

```yaml
# .github/workflows/modular-check.yml
name: Modular Architecture Check

on:
  pull_request:
    paths:
      - 'app/Modules/**'

jobs:
  check-deps:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Check module boundaries
        run: |
          MODULES=("FormEngine" "ImutMaster" "DailyReport" "Laporan" "Benchmarking")
          for module in "${MODULES[@]}"; do
            echo "Checking $module..."
            # Module hanya boleh use namespace sendiri atau App\Kernel
            deps=$(grep -rn "use App\\\\" "app/Modules/$module" --include="*.php" | \
              grep -v "use App\\\\Modules\\\\$module" | \
              grep -v "use App\\\\Kernel" | \
              grep -v "use App\\\\Providers" || true)
            if [ -n "$deps" ]; then
              echo "❌ $module has external dependencies:"
              echo "$deps"
              exit 1
            fi
          done
          echo "✅ All module boundaries respected"
```

### 10.4 Laravel Ide-helper untuk Contracts

```bash
# Generate IDE helper untuk contract interfaces
php artisan ide-helper:generate
php artisan ide-helper:models --dir="app/Modules"
```

### 10.5 Composer Scripts

```json
// composer.json
{
  "scripts": {
    "module:check": "bash bin/check-module-deps",
    "module:make": "bash bin/make-module",
    "module:test": [
      "php artisan test --filter=Modules",
      "@module:check"
    ],
    "post-autoload-dump": [
      "@module:check"
    ]
  }
}
```

---

## 11. Checklist Migrasi per Module

### Fase 0: Foundation (1x untuk semua module)

- [ ] Autoload PSR-4 di `composer.json` sudah include `App\\Modules\\`
- [ ] Folder `app/Kernel/` sudah ada dengan Models dan Support
- [ ] `config/modules.php` dibuat dengan daftar module + version toggle
- [ ] `ModuleServiceProvider` trait/abstract class dibuat
- [ ] CI check untuk module boundaries sudah aktif

### Per Module — Phase 1: Boundary

- [ ] Interface contract dibuat (`{Module}Interface.php`)
- [ ] DTOs dibuat (`DTOs/{Entity}DTO.php`)
- [ ] Service Provider dibuat & registered
- [ ] View namespace registered via `loadViewsFrom()`
- [ ] Service implement interface dengan delegasi ke kode lama
- [ ] Semua `use App\Models` dari modul lain diganti dengan interface injection
- [ ] Feature flag di `config/modules.php` set ke `'boundary'`

### Per Module — Phase 2: Extract

- [ ] Models dipindah ke `app/Modules/{Name}/Models/`
- [ ] Services dipindah ke `app/Modules/{Name}/Services/`
- [ ] Repositories dipindah ke `app/Modules/{Name}/Repositories/`
- [ ] Filament resources dipindah & jadi plugin
- [ ] Livewire components dipindah
- [ ] Views dipindah ke `resources/views/` dalam module
- [ ] Policies & Observers dipindah
- [ ] Migration path diaktifkan
- [ ] Feature flag di `config/modules.php` set ke `'extracted'`
- [ ] Batch CI test green

### Per Module — Phase 3: Cleanup

- [ ] Kode lama di `app/Models/`, `app/Services/`, `app/Filament/` untuk entity ini dihapus
- [ ] Relasi yang cross-module diganti dengan query via interface
- [ ] Events untuk komunikasi async dipasang
- [ ] Unit test coverage > 80%
- [ ] Dokumentasi module di `docs/modules/{name}.md` dibuat
- [ ] Feature flag set ke `'extracted'` permanent (legacy path removed)

---

## 12. Troubleshooting

### 12.1 "Class not found" setelah pindah namespace

```bash
composer dump-autoload
php artisan optimize:clear
```

### 12.2 Filament Resources tidak muncul

Pastikan plugin didaftarkan di PanelProvider dan method `getPages()`, `getResources()` return array yang benar:

```php
// Modules/{Name}/Filament/{Name}Plugin.php
public function register(Panel $panel): void
{
    $panel->resources([
        Resources\FirstResource::class,
        Resources\SecondResource::class,
    ]);
}
```

### 12.3 Livewire component tidak terdaftar

```php
// Cek registrasi di ServiceProvider
Livewire::component('namespace.component', ComponentClass::class);

// Pastikan namespace di blade sesuai:
// <livewire:namespace.component />
```

### 12.4 View tidak ditemukan

```php
// Pastikan loadViewsFrom path benar (relative ke ServiceProvider)
$this->loadViewsFrom(__DIR__.'/resources/views', 'module-slug');

// Coba publish:
php artisan view:clear
```

### 12.5 Migration tidak jalan

```php
// Migration harus di-register di console context
if ($this->app->runningInConsole()) {
    $this->loadMigrationsFrom(__DIR__.'/Database/Migrations');
}

// Atau register path di AppServiceProvider (method alternatif)
$this->loadMigrationsFrom(app_path('Modules/FormEngine/Database/Migrations'));
```

### 12.6 Policy Error setelah dipindah

```php
// Di AuthServiceProvider atau Module Service Provider
Gate::policy(
    \App\Modules\FormEngine\Models\FormTemplate::class,
    \App\Modules\FormEngine\Policies\FormTemplatePolicy::class
);
```

### 12.7 Circular Dependency Antar Module

Jika Module A butuh Module B dan Module B butuh Module A → **desain ulang**.

Solusi:
- Pisahkan interface bersama ke Kernel
- Atau gunakan Event (async) untuk putus dependency
- Atau buat module ketiga yang aggregasi

```php
// ❌ Circular:
// DailyReport → FormEngine (get template)
// FormEngine → DailyReport (get report count for template usage stats)

// ✅ Fix via Event:
// DailyReport submit → dispatch FormTemplateUsed event
// FormEngine listen → update usage stats

// ✅ Fix via parent module:
// Modules/Core/Contracts/TemplateUsageInterface
```

### 12.8 Performance Issue dengan DTO

DTO conversion untuk list besar bisa overhead:

```php
// ❌ Convert satu-satu
$dtos = $models->map(fn($m) => EntityDTO::fromEloquent($m));

// ✅ Batch + cache
$dtos = Cache::remember("module:entity:list:$ids", 300, function () use ($models) {
    return $models->map(fn($m) => EntityDTO::fromEloquent($m));
});

// ✅ Atau return paginated collection dengan DTO dari query builder
public function getPaginated(int $perPage = 15): LengthAwarePaginator
{
    return DB::table('form_templates')
        ->select(['id', 'name', 'description'])
        ->paginate($perPage)
        ->through(fn($row) => FormTemplateDTO::fromArray((array)$row));
}
```

---

## Lampiran

### A. Resource Migration Mapping

| Old Path | New Path (Module) |
|---|---|
| `app/Models/FormTemplate.php` | `app/Modules/FormEngine/Models/FormTemplate.php` |
| `app/Models/FormField.php` | `app/Modules/FormEngine/Models/FormField.php` |
| `app/Models/ImutData.php` | `app/Modules/ImutMaster/Models/ImutData.php` |
| `app/Models/DailyReportResponse.php` | `app/Modules/DailyReport/Models/DailyReportResponse.php` |
| `app/Services/DailyReport/DailyReportService.php` | `app/Modules/DailyReport/Services/DailyReportService.php` |
| `app/Filament/Resources/ImutDataResource.php` | `app/Modules/ImutMaster/Filament/Resources/ImutDataResource.php` |
| `resources/views/filament/resources/daily-report-entry-resource/**` | `app/Modules/DailyReport/resources/views/**` |

### B. Config Template

```php
// config/modules.php
return [
    /*
    | Module version states:
    | - 'disabled': Module tidak aktif
    | - 'legacy':   Kode lama di app/ (default selama migrasi)
    | - 'boundary': Module registered tapi delegasi ke kode lama
    | - 'extracted': Module fully independent, kode lama bisa dihapus
    */
    
    'form_engine' => [
        'enabled' => env('MODULE_FORM_ENGINE_ENABLED', true),
        'version' => env('MODULE_FORM_ENGINE_VERSION', 'legacy'),
    ],
    'imut_master' => [
        'enabled' => env('MODULE_IMUT_MASTER_ENABLED', true),
        'version' => env('MODULE_IMUT_MASTER_VERSION', 'legacy'),
    ],
    'daily_report' => [
        'enabled' => env('MODULE_DAILY_REPORT_ENABLED', true),
        'version' => env('MODULE_DAILY_REPORT_VERSION', 'legacy'),
    ],
    'laporan' => [
        'enabled' => env('MODULE_LAPORAN_ENABLED', true),
        'version' => env('MODULE_LAPORAN_VERSION', 'legacy'),
    ],
    'benchmarking' => [
        'enabled' => env('MODULE_BENCHMARKING_ENABLED', true),
        'version' => env('MODULE_BENCHMARKING_VERSION', 'legacy'),
    ],
];
```

### C. Daftar Interface Contracts

| Interface | Module | Method Signatures |
|---|---|---|
| `FormEngineInterface` | FormEngine | `getActiveTemplate(int $profileId): FormTemplateDTO`, `calculateScore(array $responses): ScoreResult`, `validateField(string $fieldKey, mixed $value): ValidationResult` |
| `ImutMasterInterface` | ImutMaster | `findImutData(int $id): ImutDataDTO`, `getActiveProfile(int $unitKerjaId): ?ImutProfileDTO`, `calculateScore(float $numerator, float $denominator): float` |
| `DailyReportQueryInterface` | DailyReport | `getSummariesByPeriod(Carbon $start, Carbon $end, ?int $unitKerjaId): array`, `getLatest(int $limit): array` |
| `BenchmarkingInterface` | Benchmarking | `getBenchmarkData(int $imutDataId, int $regionTypeId): BenchmarkDTO` |
| `AuthorizationInterface` | Authorization | `canAccessReport(User $user, int $unitKerjaId): bool`, `getAccessibleUnitKerja(User $user): Collection` |

---

*Dokumen ini adalah panduan teknis langsung untuk implementasi. Update saat ada keputusan arsitektur baru.*
