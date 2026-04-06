# Widget Caching & N+1 Query Prevention Strategy

## Overview
Optimized `RecommendationAnalysisTimMutuWidget` dan `RecommendationAnalysisUnitKerjaWidget` dengan:
- ✅ Caching dengan otomatis invalidation
- ✅ Eager loading untuk mencegah N+1 queries
- ✅ Cache invalidation saat ImutPenilaian diupdate

## Cache Keys Ditambahkan

### CacheKey::recommendationAnalysisTimMutuOngoing()
- Cache untuk laporan yang sedang dalam fase analisis (Tim Mutu)
- TTL: 30 menit
- Invalidation: Otomatis saat ada ImutPenilaian update

### CacheKey::recommendationAnalysisTimMutuPrevious()
- Cache untuk laporan sebelumnya (Tim Mutu)
- TTL: 30 menit
- Invalidation: Otomatis saat ada ImutPenilaian update

### CacheKey::recommendationAnalysisUnitKerjaOngoing(userId)
- Cache per-user untuk laporan yang sedang berjalan (Unit Kerja)
- TTL: 30 menit
- Invalidation: Otomatis saat ada ImutPenilaian update

### CacheKey::recommendationAnalysisUnitKerjaPrevious(userId)
- Cache per-user untuk laporan sebelumnya (Unit Kerja)
- TTL: 30 menit
- Invalidation: Otomatis saat ada ImutPenilaian update

### CacheKey::recommendationAnalysisCompletionStats(laporanId)
- Cache untuk statistik completion pada laporan tertentu
- TTL: 30 menit
- Invalidation: Otomatis saat ada ImutPenilaian update

### CacheKey::recommendationAnalysisCompletionStatsUnitKerja(laporanId, unitKerjaId)
- Cache untuk statistik completion per unit kerja
- TTL: 30 menit
- Invalidation: Otomatis saat ada ImutPenilaian update

## Optimasi yang Dilakukan

### 1. Eager Loading
```php
// Sebelum: N+1 queries
foreach ($laporan->unitKerjas()->get() as $unitKerja) {
    $laporanUnitKerja = $laporan->laporanUnitKerjas()
        ->where('unit_kerja_id', $unitKerja->id)
        ->first(); // Query lagi untuk setiap unit!
    
    $totalPenilaians = $laporanUnitKerja->imutPenilaians()->count(); // Query lagi!
    $completed = $laporanUnitKerja->imutPenilaians()
        ->where('analysis', '!=', null)
        ->count(); // Query lagi!
}

// Setelah: Satu query dengan eager loading
$reports = LaporanImut::with('unitKerjas', 'laporanUnitKerjas.imutPenilaians')->get();

foreach ($laporanUnitKerjas as $laporanUnitKerja) {
    $imutPenilaians = $laporanUnitKerja->imutPenilaians; // Dari memory, bukan query!
    $totalPenilaians = $imutPenilaians->count(); // Collection count, bukan DB query
    $completed = $imutPenilaians->filter(...)->count(); // In-memory filtering
}
```

### 2. Caching dengan Laravel Cache::remember()
```php
public function getOngoingAnalysisReports(): array
{
    return Cache::remember(
        CacheKey::recommendationAnalysisTimMutuOngoing(),
        self::CACHE_DURATION, // 30 menit
        fn() => $this->computeOngoingAnalysisReports()
    );
}
```

**Keuntungan:**
- Jika cache ada → return langsung tanpa query DB
- Jika cache kosong → compute, simpan ke cache, return result
- Otomatis invalidate saat ImutPenilaian update

### 3. Automatic Cache Invalidation
Di `ImutPenilaian::clearCache()`:
```php
// Clear widget cache saat penilaian updated/deleted
Cache::forget(CacheKey::recommendationAnalysisTimMutuOngoing());
Cache::forget(CacheKey::recommendationAnalysisTimMutuPrevious());
Cache::forget(CacheKey::recommendationAnalysisCompletionStats($laporanId));
Cache::forget(CacheKey::recommendationAnalysisCompletionStatsUnitKerja($laporanId, $unitKerjaId));

// Clear per-user cache
foreach ($userIds as $userId) {
    Cache::forget(CacheKey::recommendationAnalysisUnitKerjaOngoing($userId));
    Cache::forget(CacheKey::recommendationAnalysisUnitKerjaPrevious($userId));
}
```

**Flow:**
1. User mengisi analisis → ImutPenilaian saved event fired
2. ImutPenilaian::booted() → clearCache() dipanggil otomatis
3. Semua widget cache untuk laporan tersebut dihapus
4. Widget me-render → Cache kosong → compute ulang dengan data terbaru
5. Result disimpan ke cache lagi untuk 30 menit ke depan

## Performance Impact

### Sebelum Optimasi (Worst Case)
```
1 laporan × 5 unit kerja × 20 penilaian per unit = 100 items

Queries per widget render:
1. SELECT * FROM laporan_imuts
2. SELECT * FROM unit_kerjas WHERE laporan_id = ?
3. SELECT * FROM laporan_unit_kerjas WHERE unit_kerja_id = ? // × 5
4. SELECT COUNT(*) FROM imut_penilaians WHERE laporan_unit_kerja_id = ? // × 5
5. SELECT COUNT(*) FROM imut_penilaians WHERE analysis != NULL // × 5

Total: ~1 + 1 + 5 + 5 + 5 = 17+ queries per render
```

### Sesudah Optimasi (First Load)
```
1. SELECT * FROM laporan_imuts
2. SELECT * FROM unit_kerjas WHERE laporan_id IN (...)
3. SELECT * FROM laporan_unit_kerjas WHERE laporan_imut_id IN (...)
4. SELECT * FROM imut_penilaians WHERE laporan_unit_kerja_id IN (...)

Total: 4 queries (eager loading)
```

### Sesudah Optimasi (Cached - Status Quo)
```
Cache hit → 0 queries! ✓
Return cached result langsung dari memory
```

## Cache Invalidation Timeline

```
0:00:00 - Widget rendered
        └─ Query DB (4 queries)
        └─ Result cached untuk 30 menit
        
0:15:00 - User renders widget lagi
        └─ Cache hit! Return dari memory (0 queries)
        
0:18:45 - Another user updates analysis
        └─ ImutPenilaian save event triggered
        └─ clearCache() dipanggil
        └─ All widget caches invalidated
        
0:18:46 - Widget rendered again
        └─ Cache miss (invalidated tadi)
        └─ Query DB again (4 queries)
        └─ Result cached lagi untuk 30 menit
```

## Implementation Details

### TimMutuWidget vs UnitKerjaWidget

#### Tim Mutu Widget
- Cache key global: `widget:recommendation_analysis:tim_mutu:ongoing_reports`
- Semua Tim Mutu user share same cache
- Invalidation: Saat ANY ImutPenilaian updated

#### Unit Kerja Widget  
- Cache key per-user: `widget:recommendation_analysis:unit_kerja:ongoing_reports:user:{userId}`
- Setiap user punya cache sendiri (only their unit kerjas)
- Invalidation: Saat ImutPenilaian updated PADA unit kerja user tsb

## Testing Caching

### Verify Cache Working
```bash
# In tinker
> Cache::get(CacheKey::recommendationAnalysisTimMutuOngoing())
// null (first call)

> $widget = new \App\Filament\Widgets\RecommendationAnalysisTimMutuWidget();
> $widget->getOngoingAnalysisReports();
// Queries DB, caches result

> Cache::get(CacheKey::recommendationAnalysisTimMutuOngoing())
// Returns array (cached!)

> Cache::get(CacheKey::recommendationAnalysisTimMutuOngoing()) === Cache::get(CacheKey::recommendationAnalysisTimMutuOngoing())
// true (same cached data)
```

### Verify N+1 Prevention
```bash
# Use Laravel Debugbar or DB::listen()
> DB::listen(function ($query) { echo $query->sql . "\n"; });

> $widget->getOngoingAnalysisReports();
// Should show only 4 queries (eager loading)
// NOT 17+ queries (N+1)
```

### Verify Cache Invalidation
```bash
# Get laporan with ongoing analysis
> $laporan = LaporanImut::where('status', 'process')->first();
> $unitKerja = $laporan->unitKerjas()->first();
> $laporanUnitKerja = $laporan->laporanUnitKerjas()->where('unit_kerja_id', $unitKerja->id)->first();
> $penilaian = $laporanUnitKerja->imutPenilaians()->first();

# Cache widget
> $widget = new \App\Filament\Widgets\RecommendationAnalysisTimMutuWidget();
> $widget->getOngoingAnalysisReports();
> Cache::has(CacheKey::recommendationAnalysisTimMutuOngoing())
// true

# Update penilaian
> $penilaian->update(['analysis' => 'Updated analysis text']);
> Cache::has(CacheKey::recommendationAnalysisTimMutuOngoing())
// false (cache invalidated automatically!)
```

## Configuration

### Cache Duration
Diatur di widget class:
```php
private const CACHE_DURATION = 1800; // 30 menit dalam seconds
```

Untuk change cache duration:
- Edit `CACHE_DURATION` constant di kedua widget classes
- Atau lakukan cache:clear saat development

### Cache Driver
Default menggunakan config cache driver (biasanya `file` atau `redis`).
Untuk production, gunakan Redis untuk better performance:

```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),
```

## Related Files Modified

1. **app/Support/CacheKey.php**
   - Ditambah 6 cache key methods untuk widget

2. **app/Filament/Widgets/RecommendationAnalysisTimMutuWidget.php**
   - Eager loading dengan `with()`
   - Caching dengan `Cache::remember()`
   - Refactored into `compute*` methods

3. **app/Filament/Widgets/RecommendationAnalysisUnitKerjaWidget.php**
   - Sama seperti tim mutu, tapi per-user caching
   - Eager loading relationships

4. **app/Models/ImutPenilaian.php**
   - Enhanced `clearCache()` untuk widget invalidation
   - Clearing per-user cache untuk affected units

## Monitoring

### Laravel Telescope
Buka `/telescope` untuk melihat:
- SQL queries count
- Cache hits vs misses
- Performance metrics

### Manual Logging
Untuk debug cache behavior:
```php
// Di widget method
\Log::info('Cache hit: ' . CacheKey::recommendationAnalysisTimMutuOngoing());
\Log::info('Computing from scratch...');
```

## Future Optimizations

1. **Cache Warming**
   - Pre-compute cache saat aplikasi startup
   - Command: `php artisan app:warm-recommendation-cache`

2. **Granular Invalidation**
   - Invalidate hanya affected laporan, bukan semua
   - Saat ini: all or nothing

3. **Async Recomputation**
   - Queue job untuk recompute cache
   - Langsung return stale data (background refresh)

4. **Database Query Optimization**
   - Add indexes untuk frequently queried columns
   - Use database views untuk complex aggregations
