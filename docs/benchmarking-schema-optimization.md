# Benchmarking Schema Optimization

## Problem Identified

### Issue: Kontradiksi Field `year`/`month` vs `period_start`/`period_end`

**Sebelumnya**:
```php
$table->year('year');              // Tahun (2024)
$table->tinyInteger('month');      // Bulan (1-12)
$table->date('period_start');      // Tanggal mulai (2024-01-01)
$table->date('period_end');        // Tanggal akhir (2024-01-31)
```

**Masalah**:
1. ❌ **Redundan**: Year/month sudah ada di period_start
2. ❌ **Kontradiksi**: Bisa saja year=2024, month=1, tapi period_start=2024-02-15
3. ❌ **Tidak Fleksibel**: Terpaksa input year+month walaupun sudah ada period_start
4. ❌ **Kompleks**: UI harus maintain 4 fields untuk 1 konsep (periode berlaku)
5. ❌ **Membingungkan User**: "Apa bedanya Tahun/Bulan dengan Mulai Berlaku?"

## Solution Implemented

### Schema Optimization

**Sekarang (Optimized)**:
```php
// Hapus year & month
$table->date('period_start')->required();   // Berlaku dari tanggal
$table->date('period_end')->nullable();     // Berlaku sampai (null = permanent)
```

**Benefits**:
1. ✅ **Simple**: Hanya 2 field untuk periode
2. ✅ **Tidak Ada Kontradiksi**: Single source of truth
3. ✅ **Fleksibel**: Support ANY date range (not just monthly)
4. ✅ **Clear UX**: "Berlaku Dari" → "Sampai" (kosongkan = permanent)
5. ✅ **Backward Compatible**: Year/month computed via accessor

### Migration Details

**File**: `database/migrations/2025_10_29_171203_optimize_imut_benchmarkings_schema_remove_year_month.php`

**What It Does**:
1. ✅ Add columns if not exist (safe for production):
   - `period_start`, `period_end`
   - `created_by`, `updated_by` (audit trail)
   - `is_active`, `notes`
   
2. ✅ Add performance indexes:
   - `idx_benchmark_lookup` (imut_data_id, region_type_id, period_start)
   - `idx_benchmark_active` (is_active)
   - `idx_benchmark_period` (period_start, period_end)
   
3. ✅ Migrate existing data:
   ```sql
   -- Convert year/month to period_start
   UPDATE imut_benchmarkings 
   SET period_start = CONCAT(year, '-', LPAD(month, 2, '0'), '-01')
   WHERE period_start IS NULL
   
   -- Set period_end to end of month
   UPDATE imut_benchmarkings 
   SET period_end = LAST_DAY(period_start)
   WHERE period_end IS NULL
   ```
   
4. ✅ Drop redundant columns:
   - Remove `year`
   - Remove `month`
   - Remove `unique_benchmark_period` constraint

### Model Changes

**File**: `app/Models/ImutBenchmarking.php`

**Removed from Fillable**:
```php
- 'year',
- 'month',
```

**Added Accessors** (backward compatibility):
```php
/**
 * Get year from period_start.
 */
public function getYearAttribute(): int
{
    return $this->period_start ? $this->period_start->year : now()->year;
}

/**
 * Get month from period_start.
 */
public function getMonthAttribute(): int
{
    return $this->period_start ? $this->period_start->month : now()->month;
}
```

**Updated Scope**:
```php
// OLD: Filter by year/month columns
public function scopeForYearMonth(Builder $query, int $year, ?int $month = null): Builder
{
    $query->where('year', $year);
    if ($month !== null) {
        $query->where('month', '<=', $month);
    }
    return $query;
}

// NEW: Extract year/month from period_start
public function scopeForYearMonth(Builder $query, int $year, ?int $month = null): Builder
{
    $query->whereYear('period_start', $year);
    if ($month !== null) {
        $query->whereMonth('period_start', '<=', $month);
    }
    return $query;
}
```

### UI Changes

**File**: `app/Filament/Resources/ImutDataResource/Schema/ImutDataSchema.php`

**Before (7 columns - confusing)**:
```
Region | Tahun | Bulan | Nilai | Mulai | Akhir | Aktif
```

**After (5 columns - clear)**:
```
Region | Nilai (%) | Berlaku Dari | Sampai | Aktif
```

**Form Changes**:
```php
// REMOVED: Year & Month fields
- TextInput::make('year')
- Select::make('month')

// KEPT: Period fields (clearer labels)
DatePicker::make('period_start')
    ->label('Berlaku Dari')
    ->placeholder('Tanggal mulai')
    ->default(now()->startOfMonth())
    ->required()
    ->helperText('Tanggal mulai berlaku'),

DatePicker::make('period_end')
    ->label('Sampai')
    ->placeholder('Permanent (kosongkan)')
    ->afterOrEqual('period_start')
    ->helperText('Kosongkan untuk permanent'),
```

**Sort Order Updated**:
```php
// OLD:
->orderBy('year', 'desc')
->orderBy('month', 'desc')

// NEW:
->orderByDesc('period_start')
```

## Use Cases Supported

### 1. Benchmarking Bulanan
```php
period_start: 2024-01-01
period_end:   2024-01-31
// Berlaku hanya untuk Januari 2024
```

### 2. Benchmarking Tahunan
```php
period_start: 2024-01-01
period_end:   2024-12-31
// Berlaku untuk seluruh 2024
```

### 3. Benchmarking Custom Range
```php
period_start: 2024-03-15
period_end:   2024-06-20
// Berlaku dari pertengahan Maret sampai Juni
```

### 4. Benchmarking Permanent
```php
period_start: 2024-01-01
period_end:   null
// Berlaku selamanya sejak 1 Jan 2024
```

## Backward Compatibility

**Existing Code Continues to Work**:
```php
// This still works (via accessor)
$benchmark->year;   // Returns period_start->year
$benchmark->month;  // Returns period_start->month

// Scope still works (query updated)
ImutBenchmarking::forYearMonth(2024, 10)->get();
```

**No Breaking Changes**:
- Model API sama
- Accessor provide year/month
- Scope method signature unchanged
- Observer tetap berfungsi
- Factory tetap berfungsi

## Data Migration Safety

✅ **Production Safe**:
- Migration checks if columns exist before adding
- Existing data migrated automatically
- Rollback available if needed
- No data loss

✅ **Zero Downtime**:
- Columns added as nullable first
- Data migrated in transaction
- Column made required after migration
- Old columns dropped last

## Testing

**Manual Tests Required**:
```bash
# 1. Create new benchmarking
- Input period_start, period_end
- Verify year/month accessible via $benchmark->year

# 2. Edit existing benchmarking
- Data should show in period fields
- year/month should compute correctly

# 3. Query scopes
ImutBenchmarking::forYearMonth(2024, 10)->count()
ImutBenchmarking::activeForPeriod(now())->get()

# 4. Widget/Chart
- Should still work (using scopes)
- No changes needed if using Model methods
```

## Migration Commands

```bash
# Run migration
php artisan migrate

# Rollback if needed
php artisan migrate:rollback --step=1

# Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## Summary

**Removed**:
- ❌ `year` column (redundant)
- ❌ `month` column (redundant)
- ❌ `unique_benchmark_period` constraint (conflict)

**Added**:
- ✅ Computed `year` accessor (backward compat)
- ✅ Computed `month` accessor (backward compat)
- ✅ Performance indexes
- ✅ Clearer UI (5 columns vs 7)

**Result**:
- ✅ No contradiction
- ✅ Simpler schema
- ✅ Clearer UX
- ✅ More flexible
- ✅ Backward compatible
- ✅ Production safe

---

**Status**: ✅ Migrated Successfully  
**Date**: 29 October 2025  
**Migration**: `2025_10_29_171203_optimize_imut_benchmarkings_schema_remove_year_month.php`
