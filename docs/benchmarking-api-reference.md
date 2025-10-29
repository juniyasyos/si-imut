# Benchmarking System - API Documentation

## Model API

### ImutBenchmarking

#### Properties

```php
class ImutBenchmarking extends Model
{
    protected $fillable = [
        'imut_data_id',
        'region_type_id',
        'region_name',
        'year',
        'month',
        'benchmark_value',
        'period_start',      // Added in v1.0
        'period_end',        // Added in v1.0
        'is_active',         // Added in v1.0
        'notes',             // Added in v1.0
        'created_by',        // Added in v1.0
        'updated_by',        // Added in v1.0
    ];
}
```

#### Scopes

##### `activeForPeriod(Carbon $date): Builder`

**Purpose:** Get benchmarkings active for a specific date.

**Parameters:**
- `$date` (Carbon): The date to check validity against

**Returns:** `Illuminate\Database\Eloquent\Builder`

**Example:**
```php
use Carbon\Carbon;

$date = Carbon::create(2025, 10, 15);
$benchmarkings = ImutBenchmarking::activeForPeriod($date)->get();

// SQL: WHERE is_active = 1 
//      AND period_start <= '2025-10-15'
//      AND (period_end >= '2025-10-15' OR period_end IS NULL)
```

---

##### `forIndicator(int $imutDataId): Builder`

**Purpose:** Filter benchmarkings by indicator (imut_data_id).

**Parameters:**
- `$imutDataId` (int): The indicator ID

**Returns:** `Illuminate\Database\Eloquent\Builder`

**Example:**
```php
$benchmarkings = ImutBenchmarking::forIndicator(67)->get();

// SQL: WHERE imut_data_id = 67
```

---

##### `forRegion(int|array $regionTypeId): Builder`

**Purpose:** Filter benchmarkings by region(s).

**Parameters:**
- `$regionTypeId` (int|array): Single region ID or array of IDs

**Returns:** `Illuminate\Database\Eloquent\Builder`

**Example:**
```php
// Single region
$benchmarkings = ImutBenchmarking::forRegion(53)->get();

// Multiple regions
$benchmarkings = ImutBenchmarking::forRegion([53, 54, 55])->get();

// SQL: WHERE region_type_id = 53
// OR: WHERE region_type_id IN (53, 54, 55)
```

---

##### `forYearMonth(int $year, int|null $month = null): Builder`

**Purpose:** Filter benchmarkings by year and optionally up to specific month.

**Parameters:**
- `$year` (int): The year
- `$month` (int|null): Optional month (1-12). If provided, includes data where month <= $month

**Returns:** `Illuminate\Database\Eloquent\Builder`

**Example:**
```php
// All data for 2025
$benchmarkings = ImutBenchmarking::forYearMonth(2025)->get();

// Data up to October 2025 (cumulative)
$benchmarkings = ImutBenchmarking::forYearMonth(2025, 10)->get();

// SQL: WHERE year = 2025
// OR: WHERE year = 2025 AND month <= 10
```

---

#### Instance Methods

##### `isValidForPeriod(Carbon $date): bool`

**Purpose:** Check if this benchmarking is valid for a specific date.

**Parameters:**
- `$date` (Carbon): The date to check

**Returns:** `bool` - true if active and date within period

**Example:**
```php
$benchmarking = ImutBenchmarking::find(1);
$isValid = $benchmarking->isValidForPeriod(Carbon::now());

if ($isValid) {
    echo "Benchmark is active for today";
}
```

---

#### Static Methods

##### `getValueForPeriod(int $imutDataId, int $regionTypeId, Carbon $date): float|null`

**Purpose:** Get benchmark value for specific parameters and date.

**Parameters:**
- `$imutDataId` (int): Indicator ID
- `$regionTypeId` (int): Region type ID
- `$date` (Carbon): Date to check

**Returns:** `float|null` - Benchmark value or null if not found

**Example:**
```php
$value = ImutBenchmarking::getValueForPeriod(
    imutDataId: 67,
    regionTypeId: 53,
    date: Carbon::create(2025, 10, 15)
);

if ($value !== null) {
    echo "Benchmark value: {$value}%";
}
```

---

#### Relationships

##### `creator(): BelongsTo`

**Purpose:** Get the user who created this benchmarking.

**Returns:** `BelongsTo` relationship to User model

**Example:**
```php
$benchmarking = ImutBenchmarking::find(1);
$creator = $benchmarking->creator;

echo "Created by: {$creator->name}";
```

---

##### `updater(): BelongsTo`

**Purpose:** Get the user who last updated this benchmarking.

**Returns:** `BelongsTo` relationship to User model

**Example:**
```php
$benchmarking = ImutBenchmarking::find(1);
$updater = $benchmarking->updater;

echo "Last updated by: {$updater->name}";
```

---

## Validation Service API

### BenchmarkingValidationService

#### Methods

##### `validateBenchmarkValue(float $value, float $min = 0, float $max = 100): array`

**Purpose:** Validate if benchmark value is within acceptable range.

**Parameters:**
- `$value` (float): The value to validate
- `$min` (float): Minimum acceptable value (default: 0)
- `$max` (float): Maximum acceptable value (default: 100)

**Returns:**
```php
[
    'valid' => bool,
    'errors' => array,
    'value' => float
]
```

**Example:**
```php
$validator = new BenchmarkingValidationService();
$result = $validator->validateBenchmarkValue(85.5);

if ($result['valid']) {
    echo "Value is valid";
}
```

---

##### `validatePeriodLogic(string|Carbon $periodStart, string|Carbon|null $periodEnd): array`

**Purpose:** Validate that period_end is after period_start.

**Parameters:**
- `$periodStart` (string|Carbon): Start date
- `$periodEnd` (string|Carbon|null): End date (null = permanent)

**Returns:**
```php
[
    'valid' => bool,
    'errors' => array,
    'period_start' => Carbon,
    'period_end' => Carbon|null
]
```

**Example:**
```php
$result = $validator->validatePeriodLogic('2025-10-01', '2025-10-31');

if (!$result['valid']) {
    foreach ($result['errors'] as $error) {
        echo $error;
    }
}
```

---

##### `validateYearMonthConsistency(int $year, int $month, string|Carbon $periodStart): array`

**Purpose:** Validate that year/month matches period_start.

**Parameters:**
- `$year` (int): Year value
- `$month` (int): Month value (1-12)
- `$periodStart` (string|Carbon): Period start date

**Returns:**
```php
[
    'valid' => bool,
    'errors' => array
]
```

**Example:**
```php
$result = $validator->validateYearMonthConsistency(2025, 10, '2025-10-01');
```

---

##### `validateDuplicate(int $imutDataId, int $regionTypeId, int $year, int $month, int|null $ignoreId = null): array`

**Purpose:** Check if duplicate record exists.

**Parameters:**
- `$imutDataId` (int): Indicator ID
- `$regionTypeId` (int): Region type ID
- `$year` (int): Year
- `$month` (int): Month
- `$ignoreId` (int|null): ID to ignore (for updates)

**Returns:**
```php
[
    'valid' => bool,
    'errors' => array,
    'duplicate' => ImutBenchmarking|null
]
```

**Example:**
```php
$result = $validator->validateDuplicate(67, 53, 2025, 10);

if (!$result['valid']) {
    echo "Duplicate found: " . $result['duplicate']->id;
}
```

---

##### `validatePeriodOverlap(int $imutDataId, int $regionTypeId, string|Carbon $periodStart, string|Carbon|null $periodEnd, int|null $ignoreId = null): array`

**Purpose:** Check for overlapping periods.

**Parameters:**
- `$imutDataId` (int): Indicator ID
- `$regionTypeId` (int): Region type ID
- `$periodStart` (string|Carbon): Start date
- `$periodEnd` (string|Carbon|null): End date
- `$ignoreId` (int|null): ID to ignore (for updates)

**Returns:**
```php
[
    'valid' => bool,
    'errors' => array,
    'overlapping' => Collection
]
```

**Example:**
```php
$result = $validator->validatePeriodOverlap(
    67, 53, '2025-10-01', '2025-10-31'
);

if (!$result['valid']) {
    echo "Found " . $result['overlapping']->count() . " overlapping periods";
}
```

---

##### `validateActiveForDate(int $imutDataId, int $regionTypeId, Carbon $date): array`

**Purpose:** Check if there's an active benchmarking for specific date.

**Parameters:**
- `$imutDataId` (int): Indicator ID
- `$regionTypeId` (int): Region type ID
- `$date` (Carbon): Date to check

**Returns:**
```php
[
    'valid' => bool,
    'errors' => array,
    'benchmarking' => ImutBenchmarking|null
]
```

**Example:**
```php
$result = $validator->validateActiveForDate(67, 53, Carbon::now());

if ($result['valid']) {
    $current = $result['benchmarking'];
    echo "Current benchmark: {$current->benchmark_value}%";
}
```

---

##### `validateComplete(array $data, int|null $ignoreId = null): array`

**Purpose:** Run all validations comprehensively.

**Parameters:**
- `$data` (array): Benchmarking data to validate
- `$ignoreId` (int|null): ID to ignore (for updates)

**Required data keys:**
```php
[
    'imut_data_id' => int,
    'region_type_id' => int,
    'year' => int,
    'month' => int,
    'benchmark_value' => float,
    'period_start' => string|Carbon,
    'period_end' => string|Carbon|null,
]
```

**Returns:**
```php
[
    'valid' => bool,
    'errors' => array,
    'warnings' => array
]
```

**Example:**
```php
$result = $validator->validateComplete([
    'imut_data_id' => 67,
    'region_type_id' => 53,
    'year' => 2025,
    'month' => 10,
    'benchmark_value' => 85.5,
    'period_start' => '2025-10-01',
    'period_end' => '2025-10-31',
]);

if ($result['valid']) {
    ImutBenchmarking::create($data);
} else {
    return response()->json(['errors' => $result['errors']], 422);
}
```

---

##### `getBenchmarkingStats(int|null $imutDataId = null, int|null $regionTypeId = null): array`

**Purpose:** Get statistics for benchmarkings.

**Parameters:**
- `$imutDataId` (int|null): Optional indicator filter
- `$regionTypeId` (int|null): Optional region filter

**Returns:**
```php
[
    'total' => int,
    'active' => int,
    'inactive' => int,
    'permanent' => int,
    'temporary' => int,
    'avg_value' => float,
    'min_value' => float,
    'max_value' => float,
]
```

**Example:**
```php
$stats = $validator->getBenchmarkingStats(67, 53);

echo "Total: {$stats['total']}\n";
echo "Active: {$stats['active']}\n";
echo "Average value: {$stats['avg_value']}%\n";
```

---

##### `getActiveBenchmarkings(Carbon|null $date = null): Collection`

**Purpose:** Get all active benchmarkings for specific date.

**Parameters:**
- `$date` (Carbon|null): Date to check (default: now)

**Returns:** `Illuminate\Support\Collection`

**Example:**
```php
$active = $validator->getActiveBenchmarkings(Carbon::now());

foreach ($active as $benchmark) {
    echo "{$benchmark->region_name}: {$benchmark->benchmark_value}%\n";
}
```

---

## Cache API

### CacheKey

#### Methods

##### `imutBenchmarking(int $year, int $regionTypeId, int $imutDataId, int|null $endMonth = null): string`

**Purpose:** Generate cache key for benchmarking data.

**Parameters:**
- `$year` (int): Year
- `$regionTypeId` (int): Region type ID
- `$imutDataId` (int): Indicator ID
- `$endMonth` (int|null): Optional end month (1-12)

**Returns:** `string` - Cache key

**Example:**
```php
use App\Support\CacheKey;

$key = CacheKey::imutBenchmarking(2025, 53, 67, 10);
// Returns: "benchmarking_2025_53_67_10"

$data = Cache::remember($key, 3600, function() {
    return ImutBenchmarking::forYearMonth(2025, 10)
        ->forIndicator(67)
        ->forRegion(53)
        ->get();
});
```

---

##### `invalidateBenchmarkingCache(int $year, int $regionTypeId, int $imutDataId): void`

**Purpose:** Invalidate all cache variants for specific combination.

**Parameters:**
- `$year` (int): Year
- `$regionTypeId` (int): Region type ID
- `$imutDataId` (int): Indicator ID

**Returns:** `void`

**Note:** Clears cache for all 12 months (1-12).

**Example:**
```php
use App\Support\CacheKey;

// Manually invalidate cache
CacheKey::invalidateBenchmarkingCache(2025, 53, 67);

// Clears keys:
// - benchmarking_2025_53_67_1
// - benchmarking_2025_53_67_2
// - ...
// - benchmarking_2025_53_67_12
```

---

## Observer API

### ImutBenchmarkingObserver

**Note:** Observer is automatically registered. No manual intervention needed.

#### Events Handled

##### `creating(ImutBenchmarking $benchmarking): void`

**Triggered:** Before creating new record

**Actions:**
- Sets `is_active = true` if null
- Sets `created_by` from authenticated user
- Sets `updated_by` from authenticated user

---

##### `created(ImutBenchmarking $benchmarking): void`

**Triggered:** After record created

**Actions:**
- Invalidates related cache

---

##### `updating(ImutBenchmarking $benchmarking): void`

**Triggered:** Before updating record

**Actions:**
- Sets `updated_by` from authenticated user

---

##### `updated(ImutBenchmarking $benchmarking): void`

**Triggered:** After record updated

**Actions:**
- Invalidates related cache

---

##### `deleted(ImutBenchmarking $benchmarking): void`

**Triggered:** After record deleted

**Actions:**
- Invalidates related cache

---

## Factory API

### ImutBenchmarkingFactory

#### States

##### `active(): self`

**Purpose:** Create active benchmarking.

**Example:**
```php
$benchmarking = ImutBenchmarking::factory()->active()->create();
// is_active = true
```

---

##### `inactive(): self`

**Purpose:** Create inactive benchmarking.

**Example:**
```php
$benchmarking = ImutBenchmarking::factory()->inactive()->create();
// is_active = false
```

---

##### `permanent(): self`

**Purpose:** Create permanent benchmarking (no end date).

**Example:**
```php
$benchmarking = ImutBenchmarking::factory()->permanent()->create();
// period_end = null
```

---

##### `forYearMonth(int $year, int $month): self`

**Purpose:** Create benchmarking for specific year/month.

**Example:**
```php
$benchmarking = ImutBenchmarking::factory()
    ->forYearMonth(2025, 10)
    ->create();
// year = 2025, month = 10
// period_start = 2025-10-01
// period_end = 2025-10-31
```

---

##### `forIndicator(int $imutDataId): self`

**Purpose:** Create benchmarking for specific indicator.

**Example:**
```php
$benchmarking = ImutBenchmarking::factory()
    ->forIndicator(67)
    ->create();
// imut_data_id = 67
```

---

##### `forRegion(int $regionTypeId): self`

**Purpose:** Create benchmarking for specific region.

**Example:**
```php
$benchmarking = ImutBenchmarking::factory()
    ->forRegion(53)
    ->create();
// region_type_id = 53
```

---

## Response Formats

### Success Response

```php
// Validation success
[
    'valid' => true,
    'errors' => [],
    'warnings' => []
]

// Statistics
[
    'total' => 12,
    'active' => 10,
    'inactive' => 2,
    'avg_value' => 87.5
]
```

### Error Response

```php
// Validation failure
[
    'valid' => false,
    'errors' => [
        'Benchmark value must be between 0 and 100',
        'Period end must be after period start'
    ],
    'warnings' => [
        'This creates a permanent benchmarking'
    ]
]

// With duplicate info
[
    'valid' => false,
    'errors' => ['Duplicate record exists'],
    'duplicate' => ImutBenchmarking {#123}
]
```

---

**Last Updated:** October 29, 2025  
**API Version:** 1.0.0
