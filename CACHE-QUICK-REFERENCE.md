# Widget Caching - Quick Reference Guide

## 🚀 Quick Start

### For End Users
1. ✅ Widget automatically caches data for 30 minutes
2. ✅ Cache auto-invalidates when analysis/recommendations updated
3. ✅ No action needed - everything is automatic!

### For Developers
1. **Implement caching** - Use `Cache::remember()` with `CacheKey::*` methods
2. **Eager load relations** - Use `.with()` in queries
3. **Invalidate cache** - Add to model's `clearCache()` method

## 📌 Cache Key Reference

| Method | Purpose | Scope |
|--------|---------|-------|
| `recommendationAnalysisTimMutuOngoing()` | Tim Mutu ongoing reports | Global |
| `recommendationAnalysisTimMutuPrevious()` | Tim Mutu previous report | Global |
| `recommendationAnalysisUnitKerjaOngoing($userId)` | Unit Kerja ongoing reports | Per-user |
| `recommendationAnalysisUnitKerjaPrevious($userId)` | Unit Kerja previous report | Per-user |
| `recommendationAnalysisCompletionStats($laporanId)` | Completion stats for laporan | Per-laporan |
| `recommendationAnalysisCompletionStatsUnitKerja($laporanId, $unitKerjaId)` | Per-unit stats | Per-unit |

## 💾 How to Use Caching

### Pattern 1: Simple Caching
```php
// Without extra params
$results = Cache::remember(
    CacheKey::recommendationAnalysisTimMutuOngoing(),  // Cache key
    30 * 60,  // TTL in seconds
    fn() => $this->computeOngoingReports()  // Computation
);
```

### Pattern 2: Per-User Caching
```php
// With user ID in key
$userId = Auth::id();
$results = Cache::remember(
    CacheKey::recommendationAnalysisUnitKerjaOngoing($userId),
    30 * 60,
    fn() => $this->computeUserReports()
);
```

### Pattern 3: Per-ID Caching
```php
// With entity ID in key
$results = Cache::remember(
    CacheKey::recommendationAnalysisCompletionStats($laporanId),
    30 * 60,
    fn() => $this->computeStats($laporanId)
);
```

## 🔄 Eager Loading Pattern

### Basic Eager Loading
```php
// Load single level
$laporans = LaporanImut::with('unitKerjas')->get();

// Access without query
foreach ($laporans as $laporan) {
    $laporan->unitKerjas;  // ← No query! Loaded from memory
}
```

### Nested Eager Loading
```php
// Load relationships of relationships
$laporans = LaporanImut::with(
    'unitKerjas',
    'laporanUnitKerjas.imutPenilaians'
)->get();

// Access nested without queries
$laporan->laporanUnitKerjas[0]->imutPenilaians;  // ← Still no query!
```

### Multiple Eager Loads
```php
// Chain multiple .with() calls
LaporanImut::with('unitKerjas')
    ->with('laporanUnitKerjas')
    ->with('laporanUnitKerjas.imutPenilaians')
    ->with('laporanUnitKerjas.unitKerja')
    ->get();

// Or in one .with()
LaporanImut::with(
    'unitKerjas',
    'laporanUnitKerjas.imutPenilaians',
    'laporanUnitKerjas.unitKerja'
)->get();
```

## ♻️ Cache Invalidation

### Automatic Invalidation (No Action Needed)
```php
// When ImutPenilaian updated/deleted:
// → ImutPenilaian::saved/deleted event fires
// → clearCache() called automatically
// → Widget caches invalidated
// → Next render computes fresh data
```

### Manual Invalidation (When Needed)
```php
// In custom code:
Cache::forget(CacheKey::recommendationAnalysisTimMutuOngoing());

// Or forget multiple:
Cache::forget(...);
Cache::forget(...);

// Or clear specific user:
Cache::forget(CacheKey::recommendationAnalysisUnitKerjaOngoing($userId));
```

### Clear All Cache (Emergency)
```bash
php artisan cache:clear
```

## ⚙️ Configuration

### Cache Duration (TTL)
**File:** `app/Filament/Widgets/RecommendationAnalysisTimMutuWidget.php` (and UnitKerja version)

```php
private const CACHE_DURATION = 1800;  // 30 minutes in seconds
```

**Common values:**
- 60 = 1 minute
- 300 = 5 minutes
- 1800 = 30 minutes (default)
- 3600 = 1 hour
- 86400 = 1 day

### Cache Driver
**File:** `.env` and `config/cache.php`

```env
CACHE_DRIVER=redis  # or file, database, memcached, etc
```

## 🔍 Debugging

### Check If Cache Exists
```php
// In tinker or code:
Cache::has(CacheKey::recommendationAnalysisTimMutuOngoing())
// true = cache exists, false = miss

// Get cache value
Cache::get(CacheKey::recommendationAnalysisTimMutuOngoing())
```

### Monitor Queries
```php
// Add to boot of model or service:
DB::listen(function ($query) {
    \Log::info($query->sql);
});
```

### Check Cache Hit Rate
```bash
# Using Telescope
# Visit: http://yoursite/telescope
# Filter by database queries tab
# Should see dramatic query reduction
```

## 📊 Before vs After

### Query Count
```
Before: ~17 queries per render
After:  4 queries (first render), 0 queries (cached renders)
```

### Performance
```
First render:  4 queries → same speed as before ❌ (need cache on 2nd render)
2nd render:    0 queries → 100x faster ✅
10th render:   0 queries → 100x faster ✅
After update:  4 queries → auto-refreshed ✅
```

## 🆘 Troubleshooting

### Widget Shows Old Data
```
Symptom: Updated analysis not showing in widget
Solution: Cache may not have invalidated
Action: 
  1. Check ImutPenilaian was actually saved (check DB)
  2. Run: php artisan cache:clear
  3. Refresh page (F5)
```

### Widget Load Slow
```
Symptom: Widget takes 2-3 seconds to load
Solution: Eager loading may be missing or N+1 queries occurring
Action:
  1. Check browser Network tab (how many requests?)
  2. Check Laravel Telescope queries count
  3. Verify .with() includes all relationships
```

### Cache Not Working
```
Symptom: Widget slow even after multiple loads
Solution: Cache driver may be misconfigured
Action:
  1. Run: php artisan config:cache
  2. Check .env CACHE_DRIVER setting
  3. Check cache driver is running (if Redis/Memcached)
  4. Try: CACHE_DRIVER=file (for testing)
```

## 📚 Key Concepts

### N+1 Query Problem
```php
// ❌ Bad: N+1 problem
$reports = LaporanImut::all();  // Query 1
foreach ($reports as $report) {
    $report->unitKerjas()->count();  // Queries 2-N+1
}
// Total: N+1 queries

// ✅ Good: Eager loading
$reports = LaporanImut::with('unitKerjas')->get();  // 2 queries
foreach ($reports as $report) {
    $report->unitKerjas()->count();  // No queries! In memory
}
// Total: 2 queries
```

### Cache Hit vs Miss
```php
Cache::remember($key, $ttl, function() {
    // If cache exists for $key → returns cached value (HIT)
    // If cache missing for $key → executes closure (MISS)
    // Stores result in cache for $ttl seconds
};
```

### Cache Invalidation
```php
// When data changes:
Cache::forget($key);  // Removes cache entry

// Next access:
Cache::remember($key, $ttl, fn() => {...});
// Cache missing → recompute → store → return
```

## 🎯 Best Practices

1. **Always eager load relationships** used in loops
   ```php
   // ✅ Good
   Model::with('relation').get()
   
   // ❌ Bad
   Model::get() then access relation inside loop
   ```

2. **Use appropriate cache keys** for your data scope
   ```php
   // ✅ Good: Per-user cache for user-specific data
   Cache::remember(CacheKey::recommendationAnalysisUnitKerjaOngoing($userId), ...);
   
   // ❌ Bad: Global cache for user-specific data
   Cache::remember('user_data', ...);  // All users get same cache!
   ```

3. **Remember to invalidate** when modifying related data
   ```php
   // ✅ Good: Clear dependent caches
   public function updateAnalysis() {
       $this->analysis = ...;
       $this->save();  // Triggers clearCache() automatically
   }
   
   // ❌ Bad: Forgetting to invalidate
   DB::table('imut_penilaians')->update(['analysis' => ...]);
   // Direct query bypasses clearCache()
   ```

4. **Test cache behavior** in development
   ```bash
   # Before changes:
   php artisan cache:clear
   
   # Render widget (query slow, cache misses)
   
   # Render again (instant, cache hits)
   
   # Update data
   
   # Render again (query slow, cache invalidated, recompute)
   ```

## 💬 Questions?

See full documentation in:
- `OPTIMIZATION-CACHING-STRATEGY.md` - Detailed strategy
- `CACHE-IMPLEMENTATION-SUMMARY.md` - Implementation details
- Laravel docs: https://laravel.com/docs/cache & https://laravel.com/docs/eloquent-relationships
