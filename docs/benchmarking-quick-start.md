# Benchmarking System - Quick Start Guide

## 🚀 Quick Start

### Installation

```bash
# Run migrations
php artisan migrate

# Run seeder (optional)
php artisan db:seed --class=ImutBenchmarkingSeeder
```

### Basic Usage

#### 1. Create Benchmarking

```php
use App\Models\ImutBenchmarking;

$benchmarking = ImutBenchmarking::create([
    'imut_data_id' => 67,
    'region_type_id' => 53,
    'region_name' => 'Indonesia',
    'year' => 2025,
    'month' => 10,
    'benchmark_value' => 85.0,
    'period_start' => '2025-10-01',
    'period_end' => '2025-10-31',
    'is_active' => true,
]);

// Auto-filled by Observer:
// - created_by
// - updated_by
// - Cache invalidation
```

#### 2. Query Active Benchmarkings

```php
use Carbon\Carbon;

// Get active benchmarkings for today
$active = ImutBenchmarking::activeForPeriod(Carbon::now())
    ->forIndicator(67)
    ->forRegion(53)
    ->get();

// Get data up to October 2025
$data = ImutBenchmarking::forYearMonth(2025, 10)
    ->forIndicator(67)
    ->get();
```

#### 3. Validate Before Save

```php
use App\Services\BenchmarkingValidationService;

$validator = new BenchmarkingValidationService();
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
    // Safe to create
    ImutBenchmarking::create($data);
}
```

## 📋 Available Scopes

| Scope | Usage | Description |
|-------|-------|-------------|
| `activeForPeriod($date)` | `->activeForPeriod(Carbon::now())` | Active for specific date |
| `forIndicator($id)` | `->forIndicator(67)` | Filter by indicator |
| `forRegion($id)` | `->forRegion(53)` | Filter by region(s) |
| `forYearMonth($y, $m)` | `->forYearMonth(2025, 10)` | Filter by year/month |

## 🔧 Validation Methods

| Method | Purpose |
|--------|---------|
| `validateBenchmarkValue()` | Check value range (0-100) |
| `validatePeriodLogic()` | Ensure start < end |
| `validateDuplicate()` | Detect duplicate records |
| `validatePeriodOverlap()` | Detect overlapping periods |
| `validateComplete()` | Run all validations |
| `getBenchmarkingStats()` | Get statistics |

## 🧪 Testing with Factories

```php
// Create active benchmarking
$benchmarking = ImutBenchmarking::factory()
    ->active()
    ->forYearMonth(2025, 10)
    ->create();

// Create permanent (no end date)
$permanent = ImutBenchmarking::factory()
    ->permanent()
    ->create();

// Create inactive
$inactive = ImutBenchmarking::factory()
    ->inactive()
    ->create();
```

## 📊 Get Statistics

```php
$validator = new BenchmarkingValidationService();
$stats = $validator->getBenchmarkingStats(67, 53);

// Returns:
// [
//     'total' => 12,
//     'active' => 10,
//     'avg_value' => 87.5,
//     ...
// ]
```

## 🔑 Key Features

✅ **Auto-filled fields** (created_by, updated_by, is_active)  
✅ **Automatic cache invalidation** on create/update/delete  
✅ **Comprehensive validation** before save  
✅ **Period validity** checking  
✅ **Duplicate & overlap** detection  
✅ **Factory states** for testing  

## 📚 More Information

See full documentation: `docs/benchmarking-system-implementation.md`

## 🐛 Common Issues

**Issue:** Cache not updating
```php
// Manually invalidate
CacheKey::invalidateBenchmarkingCache($year, $regionTypeId, $imutDataId);
```

**Issue:** Duplicate key error
```php
// Check existing before create
$exists = ImutBenchmarking::where([...])->exists();
```

**Issue:** Period validation fails
```php
// Validate first
$result = $validator->validatePeriodLogic($start, $end);
```

## ✅ Run Tests

```bash
# Run all benchmarking tests
php artisan test --filter="ImutBenchmarking|CacheBenchmarking|BenchmarkingValidation"

# Results: 59 tests, 153 assertions, 100% pass
```
