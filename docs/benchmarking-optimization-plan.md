# 📊 Perencanaan Perbaikan Sistem Benchmarking SI-IMUT

**Tanggal:** 29 Oktober 2025  
**Branch:** fix-chart  
**Status:** Planning Phase

---

## 🔍 Executive Summary

Sistem benchmarking saat ini memiliki **5 masalah kritis** yang mempengaruhi akurasi data, performa, dan user experience. Dokumen ini merancang solusi komprehensif untuk memperbaiki semua masalah tersebut.

---

## 🔴 Masalah yang Ditemukan

### 1. **Query Benchmarking Tidak Spesifik ke Indikator** ⚠️ CRITICAL
**File:** `app/Filament/Resources/ImutDataResource/Widgets/LineChart.php:260-269`

**Masalah:**
```php
// ❌ SALAH - Tidak ada filter imut_data_id
ImutBenchmarking::query()
    ->where('year', $year)
    ->where('month', '<=', $endMonth)
    ->when($regionTypeId, fn($q) => $q->whereIn('region_type_id', (array) $regionTypeId))
    ->get()
```

**Dampak:**
- Chart menampilkan benchmarking dari SEMUA indikator
- Data tercampur dan tidak akurat
- Query mengambil ribuan row yang tidak diperlukan

---

### 2. **Inkonsistensi Cache Key** ⚠️ HIGH
**File:** `app/Support/CacheKey.php:44-53`

**Masalah:**
- `LineChart.php` tidak menggunakan parameter `$imutDataId`
- `UnitKerjaChart.php` sudah menggunakan `$imutDataId` dengan benar
- Cache tidak spesifik per indikator

**Dampak:**
- Cache collision - indikator berbeda menggunakan cache yang sama
- Data benchmarking salah karena cache dari indikator lain
- Waste memory - cache menyimpan data yang tidak relevan

---

### 3. **Missing Parameter `endMonth` di Cache Key** ⚠️ HIGH
**File:** `app/Support/CacheKey.php`

**Masalah:**
```php
// Signature method
public static function imutBenchmarking(int $year, array|int|null $regionTypeId = null, array|int|null $imutDataId = null): string

// Pemanggilan di LineChart.php
CacheKey::imutBenchmarking($year, $regionTypeId, null, $endMonth); // ❌ endMonth diabaikan
```

**Dampak:**
- Filter bulan tidak berfungsi dengan cache
- User mengubah filter tapi data tetap sama
- Cache stale - tidak update ketika filter berubah

---

### 4. **Tidak Ada Rentang Waktu Validity Benchmarking** ⚠️ CRITICAL
**File:** `database/migrations/2025_04_15_220947_create_imut_benchmarkings_table.php`

**Struktur Saat Ini:**
```php
Schema::create('imut_benchmarkings', function (Blueprint $table) {
    $table->year('year');
    $table->tinyInteger('month');
    $table->decimal('benchmark_value', 5, 2);
    // ❌ TIDAK ADA periode_start dan periode_end
});
```

**Masalah:**
1. Tidak bisa tentukan kapan benchmarking mulai berlaku
2. Tidak bisa tentukan kapan benchmarking berakhir/expired
3. Tidak bisa track historical benchmarking changes
4. Tidak bisa validasi apakah benchmarking masih valid untuk periode tertentu
5. Sulit untuk update benchmarking tanpa menghapus data lama

**Use Case yang Tidak Bisa Dilakukan:**
- "Benchmarking Nasional 2024 berlaku dari Januari 2024 sampai Juni 2024"
- "Mulai Juli 2024, gunakan benchmarking yang baru"
- "Tampilkan benchmarking yang aktif untuk periode laporan ini"
- "Audit trail: kapan benchmarking ini diubah"

---

### 5. **Tidak Ada Validasi Data Benchmarking** ⚠️ MEDIUM

**Masalah:**
- Tidak ada validasi duplikasi (imut_data_id + region_type_id + year + month)
- Tidak ada validasi range nilai (0-100%)
- Tidak ada validasi overlap periode
- Tidak ada soft delete validation

**Dampak:**
- Data duplikat bisa masuk
- Nilai benchmarking tidak valid (>100% atau negatif)
- Konflik periode tidak terdeteksi

---

## ✅ Solusi yang Diusulkan

### **FASE 1: Database Schema Enhancement** 🗄️

#### Task 1.1: Tambah Kolom Periode di Tabel
**File:** `database/migrations/2025_10_29_create_add_period_to_benchmarking.php` (NEW)

```php
Schema::table('imut_benchmarkings', function (Blueprint $table) {
    // Periode berlaku
    $table->date('period_start')->nullable()->after('month')
        ->comment('Tanggal mulai benchmarking berlaku');
    $table->date('period_end')->nullable()->after('period_start')
        ->comment('Tanggal akhir benchmarking berlaku (null = selamanya)');
    
    // Status aktif
    $table->boolean('is_active')->default(true)->after('period_end')
        ->comment('Status aktif benchmarking');
    
    // Metadata
    $table->text('notes')->nullable()->after('is_active')
        ->comment('Catatan atau alasan perubahan benchmarking');
    $table->foreignId('created_by')->nullable()->after('notes')
        ->constrained('users')->nullOnDelete();
    $table->foreignId('updated_by')->nullable()->after('created_by')
        ->constrained('users')->nullOnDelete();
});
```

#### Task 1.2: Tambah Index untuk Performance
```php
// Composite index untuk query optimization
$table->index(['imut_data_id', 'year', 'month', 'is_active'], 'idx_benchmark_lookup');
$table->index(['region_type_id', 'period_start', 'period_end'], 'idx_benchmark_period');
$table->index(['imut_data_id', 'region_type_id', 'year', 'month'], 'idx_benchmark_unique_check');
```

#### Task 1.3: Tambah Unique Constraint
```php
// Prevent duplikasi data untuk periode yang sama
$table->unique(
    ['imut_data_id', 'region_type_id', 'year', 'month', 'deleted_at'],
    'unique_benchmark_period'
);
```

---

### **FASE 2: Model & Business Logic** 🧩

#### Task 2.1: Update Model ImutBenchmarking
**File:** `app/Models/ImutBenchmarking.php`

**Perubahan:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ImutBenchmarking extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'imut_data_id',
        'region_type_id',
        'region_name',
        'year',
        'month',
        'benchmark_value',
        'period_start',      // ✅ NEW
        'period_end',        // ✅ NEW
        'is_active',         // ✅ NEW
        'notes',             // ✅ NEW
        'created_by',        // ✅ NEW
        'updated_by',        // ✅ NEW
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
        'is_active' => 'boolean',
        'benchmark_value' => 'decimal:2',
    ];

    // ✅ NEW: Scope untuk filter periode aktif
    public function scopeActiveForPeriod(Builder $query, Carbon $date): Builder
    {
        return $query->where('is_active', true)
            ->where(function ($q) use ($date) {
                $q->where('period_start', '<=', $date)
                    ->where(function ($q2) use ($date) {
                        $q2->whereNull('period_end')
                            ->orWhere('period_end', '>=', $date);
                    });
            });
    }

    // ✅ NEW: Scope untuk filter by indikator
    public function scopeForIndicator(Builder $query, int $imutDataId): Builder
    {
        return $query->where('imut_data_id', $imutDataId);
    }

    // ✅ NEW: Scope untuk filter by region
    public function scopeForRegion(Builder $query, int|array $regionTypeId): Builder
    {
        if (is_array($regionTypeId)) {
            return $query->whereIn('region_type_id', $regionTypeId);
        }
        return $query->where('region_type_id', $regionTypeId);
    }

    // ✅ NEW: Scope untuk filter tahun-bulan
    public function scopeForYearMonth(Builder $query, int $year, ?int $month = null): Builder
    {
        $query->where('year', $year);
        
        if ($month !== null) {
            $query->where('month', '<=', $month);
        }
        
        return $query;
    }

    // ✅ NEW: Check apakah benchmarking valid untuk periode tertentu
    public function isValidForPeriod(Carbon $date): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->period_start && $date->lt($this->period_start)) {
            return false;
        }

        if ($this->period_end && $date->gt($this->period_end)) {
            return false;
        }

        return true;
    }

    // ✅ NEW: Get benchmarking value untuk periode tertentu
    public static function getValueForPeriod(
        int $imutDataId,
        int $regionTypeId,
        Carbon $date
    ): ?float {
        $benchmark = static::query()
            ->forIndicator($imutDataId)
            ->forRegion($regionTypeId)
            ->activeForPeriod($date)
            ->orderByDesc('period_start')
            ->first();

        return $benchmark?->benchmark_value;
    }

    // Relations
    public function imutData(): BelongsTo
    {
        return $this->belongsTo(ImutData::class);
    }

    public function regionType(): BelongsTo
    {
        return $this->belongsTo(RegionType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
```

---

### **FASE 3: Cache Key Optimization** 🔑

#### Task 3.1: Update CacheKey.php
**File:** `app/Support/CacheKey.php`

**Perubahan:**

```php
public static function imutBenchmarking(
    int $year,
    array|int|null $regionTypeId = null,
    array|int|null $imutDataId = null,
    ?int $endMonth = null  // ✅ NEW parameter
): string {
    $regionPart = is_array($regionTypeId)
        ? implode(',', $regionTypeId)
        : ($regionTypeId ?? 'all');

    $imutPart = is_array($imutDataId)
        ? implode(',', $imutDataId)
        : ($imutDataId ?? 'all');
    
    // ✅ NEW: tambahkan endMonth ke cache key
    $monthPart = $endMonth ?? 'all';

    return "imut:benchmarking:{$year}:month:{$monthPart}:region:{$regionPart}:imut:{$imutPart}";
}

// ✅ NEW: Method khusus untuk invalidate benchmarking cache
public static function invalidateBenchmarkingCache(
    int $imutDataId,
    int $year,
    ?int $regionTypeId = null
): void {
    // Clear semua variant cache untuk indikator dan tahun tertentu
    $pattern = "imut:benchmarking:{$year}:*:imut:{$imutDataId}";
    
    // Karena Laravel cache tidak support wildcard delete,
    // kita perlu clear berdasarkan kombinasi yang umum
    for ($month = 1; $month <= 12; $month++) {
        Cache::forget(static::imutBenchmarking($year, $regionTypeId, $imutDataId, $month));
        Cache::forget(static::imutBenchmarking($year, null, $imutDataId, $month));
    }
    
    // Clear cache "all months"
    Cache::forget(static::imutBenchmarking($year, $regionTypeId, $imutDataId));
    Cache::forget(static::imutBenchmarking($year, null, $imutDataId));
}
```

---

### **FASE 4: Widget/Chart Fixes** 📈

#### Task 4.1: Fix LineChart.php
**File:** `app/Filament/Resources/ImutDataResource/Widgets/LineChart.php`

**Perubahan di method `getChartSeries()` sekitar line 260:**

```php
if ($showBenchmarking) {
    // ✅ FIX: Tambahkan imutDataId dan endMonth ke cache key
    $benchmarkKey = CacheKey::imutBenchmarking($year, $regionTypeId, $imutDataId, $endMonth);
    
    $benchmarking = Cache::remember(
        $benchmarkKey,
        now()->addMinutes(30),
        fn() => ImutBenchmarking::query()
            ->with('regionType:id,type')
            ->select('year', 'month', 'benchmark_value', 'region_type_id', 'period_start', 'period_end')
            ->forIndicator($imutDataId)  // ✅ FIX: Filter by indikator
            ->forYearMonth($year, $endMonth)  // ✅ FIX: Filter by year & month
            ->where('is_active', true)  // ✅ FIX: Hanya yang aktif
            ->when($regionTypeId, fn($q) => $q->forRegion($regionTypeId))
            ->orderBy('year')
            ->orderBy('month')
            ->get()
    );

    // ... rest of the code
}
```

#### Task 4.2: Standardisasi UnitKerjaChart.php
**File:** `app/Filament/Resources/ImutDataResource/Widgets/UnitKerjaChart.php`

Pastikan menggunakan pattern yang sama dengan LineChart (sudah lebih baik, tapi perlu disesuaikan dengan scopes baru).

---

### **FASE 5: Form Schema Enhancement** 📝

#### Task 5.1: Update ImutDataSchema.php
**File:** `app/Filament/Resources/ImutDataResource/Schema/ImutDataSchema.php`

**Tambahkan field periode di form benchmarking:**

```php
$schema = [
    // Existing fields
    TextInput::make('region_name')
        ->placeholder($regionType->type === 'provinsi' ? 'Contoh: Jawa Barat' : 'Contoh: RS Harapan Sehat')
        ->required(),
    
    Hidden::make('region_type_id')
        ->default($regionType->id),
    
    TextInput::make('year')
        ->numeric()
        ->minValue(2000)
        ->maxValue(now()->year + 1)
        ->placeholder(now()->year)
        ->required(),

    Select::make('month')
        ->options([
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret',
            4 => 'April', 5 => 'Mei', 6 => 'Juni',
            7 => 'Juli', 8 => 'Agustus', 9 => 'September',
            10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ])
        ->required(),

    TextInput::make('benchmark_value')
        ->numeric()
        ->step(0.01)
        ->suffix('%')
        ->minValue(0)  // ✅ NEW: Validasi
        ->maxValue(100)  // ✅ NEW: Validasi
        ->placeholder('Contoh: 85.5')
        ->required(),
    
    // ✅ NEW: Periode berlaku
    DatePicker::make('period_start')
        ->label('Berlaku Mulai')
        ->required()
        ->default(now()->startOfMonth())
        ->helperText('Tanggal mulai benchmarking ini berlaku')
        ->reactive()
        ->afterStateUpdated(function ($state, callable $set, callable $get) {
            // Auto-set period_end jika belum diisi
            if (!$get('period_end') && $state) {
                $set('period_end', Carbon::parse($state)->endOfYear());
            }
        }),
    
    DatePicker::make('period_end')
        ->label('Berlaku Sampai')
        ->helperText('Kosongkan jika berlaku selamanya')
        ->after('period_start')
        ->nullable(),
    
    // ✅ NEW: Status aktif
    Toggle::make('is_active')
        ->label('Status Aktif')
        ->default(true)
        ->helperText('Nonaktifkan untuk tidak menampilkan benchmarking ini'),
    
    // ✅ NEW: Catatan
    Textarea::make('notes')
        ->label('Catatan')
        ->rows(2)
        ->placeholder('Catatan atau alasan perubahan benchmarking')
        ->columnSpanFull(),
];
```

**Tambahkan validasi custom:**

```php
TableRepeater::make("{$regionType->type}_benchmarkings")
    ->label('')
    ->streamlined()
    ->relationship('benchmarkings', fn($query) => $query->where('region_type_id', $regionType->id))
    ->headers($headers)
    ->schema($schema)
    ->defaultItems(1)
    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
        // ✅ NEW: Set created_by
        $data['created_by'] = auth()->id();
        return $data;
    })
    ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
        // ✅ NEW: Set updated_by
        $data['updated_by'] = auth()->id();
        return $data;
    })
    ->rules([
        function () {
            return function ($attribute, $value, $fail) {
                // ✅ NEW: Custom validation untuk overlap periode
                if (!isset($value['period_start']) || !isset($value['period_end'])) {
                    return;
                }
                
                $start = Carbon::parse($value['period_start']);
                $end = $value['period_end'] ? Carbon::parse($value['period_end']) : null;
                
                if ($end && $start->gt($end)) {
                    $fail('Period start harus sebelum period end.');
                }
                
                // Check overlap dengan benchmarking lain
                // ... implement overlap checking logic
            };
        }
    ]);
```

---

### **FASE 6: Validation Service** 🛡️

#### Task 6.1: Buat BenchmarkingValidationService
**File:** `app/Services/BenchmarkingValidationService.php` (NEW)

```php
<?php

namespace App\Services;

use App\Models\ImutBenchmarking;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class BenchmarkingValidationService
{
    /**
     * Validate benchmarking data sebelum save
     */
    public function validate(array $data, ?int $excludeId = null): void
    {
        $this->validateDateRange($data);
        $this->validateValue($data);
        $this->validateDuplication($data, $excludeId);
        $this->validatePeriodOverlap($data, $excludeId);
    }

    /**
     * Validasi range tanggal
     */
    protected function validateDateRange(array $data): void
    {
        if (!isset($data['period_start'])) {
            return;
        }

        $start = Carbon::parse($data['period_start']);
        
        if (isset($data['period_end']) && $data['period_end']) {
            $end = Carbon::parse($data['period_end']);
            
            if ($start->gt($end)) {
                throw ValidationException::withMessages([
                    'period_end' => 'Tanggal akhir harus setelah tanggal mulai.'
                ]);
            }
            
            // Max 5 tahun validity
            if ($end->diffInYears($start) > 5) {
                throw ValidationException::withMessages([
                    'period_end' => 'Periode benchmarking maksimal 5 tahun.'
                ]);
            }
        }
    }

    /**
     * Validasi nilai benchmarking
     */
    protected function validateValue(array $data): void
    {
        $value = $data['benchmark_value'] ?? null;
        
        if ($value === null) {
            return;
        }

        if ($value < 0 || $value > 100) {
            throw ValidationException::withMessages([
                'benchmark_value' => 'Nilai benchmarking harus antara 0-100%.'
            ]);
        }
    }

    /**
     * Validasi duplikasi
     */
    protected function validateDuplication(array $data, ?int $excludeId): void
    {
        $exists = ImutBenchmarking::query()
            ->where('imut_data_id', $data['imut_data_id'])
            ->where('region_type_id', $data['region_type_id'])
            ->where('year', $data['year'])
            ->where('month', $data['month'])
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'month' => 'Benchmarking untuk periode ini sudah ada.'
            ]);
        }
    }

    /**
     * Validasi overlap periode
     */
    protected function validatePeriodOverlap(array $data, ?int $excludeId): void
    {
        if (!isset($data['period_start'])) {
            return;
        }

        $start = Carbon::parse($data['period_start']);
        $end = isset($data['period_end']) && $data['period_end'] 
            ? Carbon::parse($data['period_end']) 
            : null;

        $query = ImutBenchmarking::query()
            ->where('imut_data_id', $data['imut_data_id'])
            ->where('region_type_id', $data['region_type_id'])
            ->where('is_active', true)
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId));

        // Check overlap
        $query->where(function ($q) use ($start, $end) {
            // Case 1: New period starts during existing period
            $q->where(function ($q1) use ($start) {
                $q1->where('period_start', '<=', $start)
                    ->where(function ($q2) use ($start) {
                        $q2->whereNull('period_end')
                            ->orWhere('period_end', '>=', $start);
                    });
            });

            // Case 2: New period ends during existing period
            if ($end) {
                $q->orWhere(function ($q1) use ($end) {
                    $q1->where('period_start', '<=', $end)
                        ->where(function ($q2) use ($end) {
                            $q2->whereNull('period_end')
                                ->orWhere('period_end', '>=', $end);
                        });
                });
            }

            // Case 3: Existing period is within new period
            $q->orWhere(function ($q1) use ($start, $end) {
                $q1->where('period_start', '>=', $start);
                if ($end) {
                    $q1->where('period_start', '<=', $end);
                }
            });
        });

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'period_start' => 'Periode benchmarking overlap dengan data yang sudah ada.'
            ]);
        }
    }

    /**
     * Get active benchmarking untuk periode tertentu
     */
    public function getActiveBenchmarking(
        int $imutDataId,
        int $regionTypeId,
        Carbon $date
    ): ?ImutBenchmarking {
        return ImutBenchmarking::query()
            ->forIndicator($imutDataId)
            ->forRegion($regionTypeId)
            ->activeForPeriod($date)
            ->orderByDesc('period_start')
            ->first();
    }
}
```

---

### **FASE 7: Seeder & Factory Update** 🌱

#### Task 7.1: Update ImutBenchmarkingSeeder
**File:** `database/seeders/ImutBenchmarkingSeeder.php`

```php
public function run(): void
{
    $this->initImut();
    $regions = RegionType::all();
    $laporans = \App\Models\LaporanImut::all();

    ImutData::whereHas('categories', fn($q) => $q->where('is_benchmark_category', true))
        ->get()
        ->each(function ($d) use ($regions, $laporans) {
            foreach ($laporans as $lap) {
                $y = Carbon::parse($lap->assessment_period_start)->year;
                $m = Carbon::parse($lap->assessment_period_start)->month;
                
                // ✅ NEW: Set period_start dan period_end
                $periodStart = Carbon::parse($lap->assessment_period_start)->startOfMonth();
                $periodEnd = $periodStart->copy()->endOfMonth();
                
                foreach ($regions as $r) {
                    $nm = match ($r->type) {
                        '🌐 Nasional'   => 'Indonesia',
                        '🏛️ Provinsi'   => 'Jawa Timur',
                        '🏥 Rumah Sakit' => fake()->company . ' Hospital',
                        default         => 'Unknown',
                    };
                    
                    ImutBenchmarking::factory()->create([
                        'imut_data_id'   => $d->id,
                        'region_type_id' => $r->id,
                        'region_name'    => $nm,
                        'year'           => $y,
                        'month'          => $m,
                        'period_start'   => $periodStart,  // ✅ NEW
                        'period_end'     => $periodEnd,    // ✅ NEW
                        'is_active'      => true,          // ✅ NEW
                        'created_by'     => $this->adminUserId,  // ✅ NEW
                    ]);
                }
            }
        });
}
```

#### Task 7.2: Update ImutBenchmarkingFactory
**File:** `database/factories/ImutBenchmarkingFactory.php`

```php
public function definition(): array
{
    $regionType = RegionType::inRandomOrder()->first();
    
    if (!$regionType) {
        $regionType = RegionType::factory()->create(['type' => '🌐 Nasional']);
    }

    $year = $this->faker->numberBetween(2022, 2025);
    $month = $this->faker->numberBetween(1, 12);
    
    // ✅ NEW: Generate period based on year and month
    $periodStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
    $periodEnd = $periodStart->copy()->endOfMonth();

    return [
        'imut_data_id' => ImutData::factory(),
        'region_type_id' => $regionType->id,
        'region_name' => $this->generateRegionName($regionType->type),
        'year' => $year,
        'month' => $month,
        'benchmark_value' => $this->faker->randomFloat(2, 70, 100),
        'period_start' => $periodStart,      // ✅ NEW
        'period_end' => $periodEnd,          // ✅ NEW
        'is_active' => true,                 // ✅ NEW
        'notes' => $this->faker->optional()->sentence(),  // ✅ NEW
        'created_by' => User::factory(),     // ✅ NEW
    ];
}

// ✅ NEW: Helper method
private function generateRegionName(string $type): string
{
    return match ($type) {
        '🌐 Nasional' => 'Indonesia',
        '🏛️ Provinsi' => $this->faker->randomElement([
            'DKI Jakarta', 'Jawa Barat', 'Jawa Tengah', 
            'Jawa Timur', 'Bali', 'Sumatera Utara'
        ]),
        '🏥 Rumah Sakit' => $this->faker->company() . ' Hospital',
        default => 'Unknown Region',
    };
}
```

---

### **FASE 8: Testing** 🧪

#### Task 8.1: Unit Test untuk Model
**File:** `tests/Unit/Models/ImutBenchmarkingTest.php` (NEW)

```php
<?php

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\RegionType;
use Carbon\Carbon;

test('scope activeForPeriod returns only active benchmarks for date', function () {
    $imutData = ImutData::factory()->create();
    $regionType = RegionType::factory()->create();
    
    // Active benchmark for current period
    $active = ImutBenchmarking::factory()->create([
        'imut_data_id' => $imutData->id,
        'region_type_id' => $regionType->id,
        'period_start' => now()->subMonth(),
        'period_end' => now()->addMonth(),
        'is_active' => true,
    ]);
    
    // Expired benchmark
    $expired = ImutBenchmarking::factory()->create([
        'imut_data_id' => $imutData->id,
        'region_type_id' => $regionType->id,
        'period_start' => now()->subYear(),
        'period_end' => now()->subMonth(),
        'is_active' => true,
    ]);
    
    // Future benchmark
    $future = ImutBenchmarking::factory()->create([
        'imut_data_id' => $imutData->id,
        'region_type_id' => $regionType->id,
        'period_start' => now()->addMonth(),
        'period_end' => now()->addYear(),
        'is_active' => true,
    ]);
    
    $results = ImutBenchmarking::activeForPeriod(now())->get();
    
    expect($results)->toHaveCount(1);
    expect($results->first()->id)->toBe($active->id);
});

test('isValidForPeriod returns correct validation', function () {
    $benchmark = ImutBenchmarking::factory()->create([
        'period_start' => Carbon::parse('2024-01-01'),
        'period_end' => Carbon::parse('2024-12-31'),
        'is_active' => true,
    ]);
    
    expect($benchmark->isValidForPeriod(Carbon::parse('2024-06-15')))->toBeTrue();
    expect($benchmark->isValidForPeriod(Carbon::parse('2023-12-31')))->toBeFalse();
    expect($benchmark->isValidForPeriod(Carbon::parse('2025-01-01')))->toBeFalse();
});

test('getValueForPeriod returns correct benchmark value', function () {
    $imutData = ImutData::factory()->create();
    $regionType = RegionType::factory()->create();
    
    ImutBenchmarking::factory()->create([
        'imut_data_id' => $imutData->id,
        'region_type_id' => $regionType->id,
        'period_start' => now()->subYear(),
        'period_end' => now()->subMonth(),
        'benchmark_value' => 80.00,
        'is_active' => true,
    ]);
    
    $current = ImutBenchmarking::factory()->create([
        'imut_data_id' => $imutData->id,
        'region_type_id' => $regionType->id,
        'period_start' => now()->subMonth(),
        'period_end' => now()->addYear(),
        'benchmark_value' => 85.50,
        'is_active' => true,
    ]);
    
    $value = ImutBenchmarking::getValueForPeriod(
        $imutData->id,
        $regionType->id,
        now()
    );
    
    expect($value)->toBe(85.50);
});
```

#### Task 8.2: Integration Test untuk Validation
**File:** `tests/Feature/Services/BenchmarkingValidationServiceTest.php` (NEW)

```php
<?php

use App\Services\BenchmarkingValidationService;
use App\Models\ImutBenchmarking;
use Illuminate\Validation\ValidationException;

test('validates period overlap correctly', function () {
    $service = new BenchmarkingValidationService();
    
    $existing = ImutBenchmarking::factory()->create([
        'period_start' => now(),
        'period_end' => now()->addMonths(6),
        'is_active' => true,
    ]);
    
    // Should throw exception for overlapping period
    expect(fn() => $service->validate([
        'imut_data_id' => $existing->imut_data_id,
        'region_type_id' => $existing->region_type_id,
        'year' => now()->year,
        'month' => now()->month,
        'benchmark_value' => 85,
        'period_start' => now()->addMonths(3),
        'period_end' => now()->addMonths(9),
    ]))->toThrow(ValidationException::class);
});

test('allows non-overlapping periods', function () {
    $service = new BenchmarkingValidationService();
    
    $existing = ImutBenchmarking::factory()->create([
        'period_start' => now(),
        'period_end' => now()->addMonths(6),
        'is_active' => true,
    ]);
    
    // Should not throw for non-overlapping period
    $service->validate([
        'imut_data_id' => $existing->imut_data_id,
        'region_type_id' => $existing->region_type_id,
        'year' => now()->addYear()->year,
        'month' => now()->month,
        'benchmark_value' => 90,
        'period_start' => now()->addYear(),
        'period_end' => now()->addYear()->addMonths(6),
    ]);
    
    expect(true)->toBeTrue(); // If no exception, test passes
});
```

---

### **FASE 9: Documentation** 📚

#### Task 9.1: Update README atau Wiki
Tambahkan dokumentasi tentang:
- Cara kerja sistem benchmarking dengan periode
- Cara input data benchmarking
- Cara validasi overlap
- Best practices untuk update benchmarking

#### Task 9.2: Buat Migration Guide
Dokumentasi untuk migrasi data existing ke schema baru.

---

## 📋 Implementation Checklist

### Database & Schema
- [ ] Create migration untuk tambah kolom period_start, period_end, is_active, notes, created_by, updated_by
- [ ] Create migration untuk tambah indexes
- [ ] Create migration untuk tambah unique constraint
- [ ] Run migration di development
- [ ] Test rollback migration

### Model & Business Logic
- [ ] Update ImutBenchmarking model dengan fillable baru
- [ ] Tambah casts untuk date fields
- [ ] Implement scope: activeForPeriod
- [ ] Implement scope: forIndicator
- [ ] Implement scope: forRegion
- [ ] Implement scope: forYearMonth
- [ ] Implement method: isValidForPeriod
- [ ] Implement static method: getValueForPeriod
- [ ] Tambah relations: creator, updater

### Cache & Performance
- [ ] Update CacheKey::imutBenchmarking dengan parameter endMonth
- [ ] Implement CacheKey::invalidateBenchmarkingCache
- [ ] Update semua pemanggilan cache key di widgets
- [ ] Test cache invalidation

### Widgets & Charts
- [ ] Fix LineChart::getChartSeries - tambah filter imut_data_id
- [ ] Fix LineChart - update cache key call
- [ ] Fix LineChart - gunakan scopes baru
- [ ] Standardisasi UnitKerjaChart dengan pattern yang sama
- [ ] Test chart rendering dengan data baru

### Forms & Validation
- [ ] Update ImutDataSchema - tambah field period_start
- [ ] Update ImutDataSchema - tambah field period_end
- [ ] Update ImutDataSchema - tambah field is_active
- [ ] Update ImutDataSchema - tambah field notes
- [ ] Tambah minValue/maxValue validation untuk benchmark_value
- [ ] Implement mutateBeforeCreate untuk set created_by
- [ ] Implement mutateBeforeSave untuk set updated_by
- [ ] Create BenchmarkingValidationService
- [ ] Implement date range validation
- [ ] Implement value validation
- [ ] Implement duplication check
- [ ] Implement overlap validation
- [ ] Integrate validation service dengan form

### Seeders & Factories
- [ ] Update ImutBenchmarkingSeeder untuk set periode
- [ ] Update ImutBenchmarkingFactory untuk generate periode
- [ ] Update Factory untuk set created_by
- [ ] Test seeder dengan data baru

### Testing
- [ ] Write unit tests untuk Model scopes
- [ ] Write unit tests untuk isValidForPeriod
- [ ] Write unit tests untuk getValueForPeriod
- [ ] Write feature tests untuk BenchmarkingValidationService
- [ ] Write feature tests untuk overlap detection
- [ ] Write integration tests untuk chart dengan benchmarking
- [ ] Test cache behavior
- [ ] Run full test suite

### Documentation
- [ ] Update CHANGELOG.md
- [ ] Create migration guide untuk existing data
- [ ] Document benchmarking workflow
- [ ] Add inline code comments
- [ ] Update API documentation (if any)

### Deployment
- [ ] Review all changes
- [ ] Test in staging environment
- [ ] Prepare rollback plan
- [ ] Create backup sebelum migration
- [ ] Run migration in production
- [ ] Monitor for errors
- [ ] Update production cache

---

## ⏱️ Estimasi Waktu

| Fase | Estimasi | Priority |
|------|----------|----------|
| FASE 1: Database Schema | 2 jam | CRITICAL |
| FASE 2: Model & Logic | 3 jam | CRITICAL |
| FASE 3: Cache Optimization | 2 jam | HIGH |
| FASE 4: Widget Fixes | 3 jam | CRITICAL |
| FASE 5: Form Enhancement | 3 jam | HIGH |
| FASE 6: Validation Service | 4 jam | HIGH |
| FASE 7: Seeder & Factory | 2 jam | MEDIUM |
| FASE 8: Testing | 5 jam | HIGH |
| FASE 9: Documentation | 2 jam | MEDIUM |

**Total Estimasi: 26 jam (3-4 hari kerja)**

---

## 🎯 Success Criteria

1. ✅ Query benchmarking hanya mengambil data untuk indikator yang diminta
2. ✅ Cache key spesifik per indikator, tahun, bulan, dan region
3. ✅ Filter bulan berfungsi dengan baik (tidak terjebak di cache)
4. ✅ Benchmarking memiliki periode validity (start & end)
5. ✅ Tidak ada duplikasi data benchmarking
6. ✅ Tidak ada overlap periode untuk indikator dan region yang sama
7. ✅ Chart menampilkan data benchmarking yang akurat
8. ✅ Performa query meningkat (< 100ms untuk chart loading)
9. ✅ Semua test passing
10. ✅ Dokumentasi lengkap dan up-to-date

---

## 🚨 Risks & Mitigation

### Risk 1: Data Migration
**Problem:** Existing data tidak punya period_start/period_end  
**Mitigation:** 
- Set period_start = first day of year-month
- Set period_end = last day of year-month
- Set is_active = true untuk semua existing data
- Create data migration script

### Risk 2: Performance Degradation
**Problem:** Index baru bisa slow down insert/update  
**Mitigation:**
- Monitor query performance
- Use proper indexing strategy
- Batch insert untuk seeder

### Risk 3: Cache Invalidation
**Problem:** Old cache masih ada setelah update  
**Mitigation:**
- Clear all benchmarking cache saat deploy
- Implement proper cache tagging
- Add manual cache clear command

### Risk 4: Breaking Changes
**Problem:** API/form breaking untuk existing users  
**Mitigation:**
- Backward compatible validation
- Nullable period fields
- Gradual rollout

---

## 📞 Next Steps

1. **Review** dokumen ini dengan tim
2. **Approve** scope pekerjaan
3. **Create** feature branch: `feat/benchmarking-period-validation`
4. **Start** implementation sesuai fase
5. **Test** setiap fase sebelum lanjut
6. **Deploy** ke staging
7. **Monitor** & fix bugs
8. **Deploy** ke production

---

**Prepared by:** GitHub Copilot  
**Date:** October 29, 2025  
**Version:** 1.0  
**Status:** Ready for Review
