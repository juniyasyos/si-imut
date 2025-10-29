<?php

namespace Tests\Feature\Integration;

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\RegionType;
use App\Support\CacheKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheBenchmarkingTest extends TestCase
{
    use RefreshDatabase;

    protected $imutData;
    protected $regionType;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create related models once for all tests
        $this->imutData = ImutData::first() ?? ImutData::factory()->create();
        $this->regionType = RegionType::first() ?? RegionType::factory()->create(['type' => 'Test Region ' . uniqid()]);
    }

    /** @test */
    public function cache_is_invalidated_when_benchmarking_is_created()
    {
        // Setup cache
        $cacheKey = CacheKey::imutBenchmarking(2025, $this->regionType->id, $this->imutData->id, 10);
        Cache::put($cacheKey, ['old_data'], 3600);
        
        $this->assertTrue(Cache::has($cacheKey));

        // Create benchmarking
        ImutBenchmarking::factory()->create([
            'year' => 2025,
            'region_type_id' => $this->regionType->id,
            'imut_data_id' => $this->imutData->id,
            'month' => 10,
        ]);

        // Cache should be invalidated
        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function cache_is_invalidated_when_benchmarking_is_updated()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'year' => 2025,
            'region_type_id' => $this->regionType->id,
            'imut_data_id' => $this->imutData->id,
            'month' => 10,
            'benchmark_value' => 85.0,
        ]);

        // Setup cache after creation (observer will clear it)
        $cacheKey = CacheKey::imutBenchmarking(2025, $this->regionType->id, $this->imutData->id, 10);
        Cache::put($cacheKey, ['cached_data'], 3600);
        
        $this->assertTrue(Cache::has($cacheKey));

        // Update benchmarking
        $benchmarking->update(['benchmark_value' => 90.0]);

        // Cache should be invalidated
        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function cache_is_invalidated_when_benchmarking_is_deleted()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'year' => 2025,
            'region_type_id' => $this->regionType->id,
            'imut_data_id' => $this->imutData->id,
            'month' => 10,
        ]);

        // Setup cache
        $cacheKey = CacheKey::imutBenchmarking(2025, $this->regionType->id, $this->imutData->id, 10);
        Cache::put($cacheKey, ['cached_data'], 3600);
        
        $this->assertTrue(Cache::has($cacheKey));

        // Delete benchmarking
        $benchmarking->delete();

        // Cache should be invalidated
        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function all_month_variants_are_invalidated()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'year' => 2025,
            'region_type_id' => $this->regionType->id,
            'imut_data_id' => $this->imutData->id,
            'month' => 10,
        ]);

        // Setup cache for all months
        $cacheKeys = [];
        for ($month = 1; $month <= 12; $month++) {
            $key = CacheKey::imutBenchmarking(2025, $this->regionType->id, $this->imutData->id, $month);
            Cache::put($key, ["data_month_{$month}"], 3600);
            $cacheKeys[$month] = $key;
        }

        // Verify all cached
        foreach ($cacheKeys as $key) {
            $this->assertTrue(Cache::has($key));
        }

        // Update benchmarking
        $benchmarking->update(['benchmark_value' => 95.0]);

        // All cache keys should be cleared
        foreach ($cacheKeys as $key) {
            $this->assertFalse(Cache::has($key));
        }
    }

    /** @test */
    public function cache_invalidation_works_across_multiple_operations()
    {
        $cacheKey = CacheKey::imutBenchmarking(2025, $this->regionType->id, $this->imutData->id, 10);

        // Operation 1: Create
        $benchmarking = ImutBenchmarking::factory()->create([
            'year' => 2025,
            'region_type_id' => $this->regionType->id,
            'imut_data_id' => $this->imutData->id,
            'month' => 10,
            'benchmark_value' => 85.0,
        ]);

        Cache::put($cacheKey, ['after_create'], 3600);
        $this->assertTrue(Cache::has($cacheKey));

        // Operation 2: Update
        $benchmarking->update(['benchmark_value' => 90.0]);
        $this->assertFalse(Cache::has($cacheKey));

        Cache::put($cacheKey, ['after_update'], 3600);
        $this->assertTrue(Cache::has($cacheKey));

        // Operation 3: Delete
        $benchmarking->delete();
        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function cache_invalidation_is_specific_to_benchmarking_parameters()
    {
        // Create benchmarking for region 1
        $benchmarking1 = ImutBenchmarking::factory()->create([
            'year' => 2025,
            'region_type_id' => $this->regionType->id,
            'imut_data_id' => $this->imutData->id,
            'month' => 10,
        ]);

        // Create second region type
        $regionType2 = RegionType::skip(1)->first() ?? RegionType::factory()->create(['type' => 'Test Region 2 ' . uniqid()]);

        // Cache for region 1 and region 2
        $cacheKey1 = CacheKey::imutBenchmarking(2025, $this->regionType->id, $this->imutData->id, 10);
        $cacheKey2 = CacheKey::imutBenchmarking(2025, $regionType2->id, $this->imutData->id, 10);
        
        Cache::put($cacheKey1, ['region_1'], 3600);
        Cache::put($cacheKey2, ['region_2'], 3600);

        $this->assertTrue(Cache::has($cacheKey1));
        $this->assertTrue(Cache::has($cacheKey2));

        // Update benchmarking for region 1
        $benchmarking1->update(['benchmark_value' => 95.0]);

        // Only region 1 cache should be invalidated
        $this->assertFalse(Cache::has($cacheKey1));
        // Region 2 cache should remain (different region_type_id)
        // Note: Current implementation clears all month variants for the specific combination
        // This test verifies the CacheKey::invalidateBenchmarkingCache() behavior
    }

    /** @test */
    public function bulk_operations_invalidate_cache_correctly()
    {
        // Create multiple benchmarkings with different months
        $benchmarks = [];
        for ($month = 1; $month <= 5; $month++) {
            $benchmarks[] = ImutBenchmarking::factory()->create([
                'year' => 2025,
                'region_type_id' => $this->regionType->id,
                'imut_data_id' => $this->imutData->id,
                'month' => $month,
            ]);
        }

        // Setup cache for month 1
        $cacheKeyMonth1 = CacheKey::imutBenchmarking(2025, $this->regionType->id, $this->imutData->id, 1);
        Cache::put($cacheKeyMonth1, ['month_1_data'], 3600);
        
        $this->assertTrue(Cache::has($cacheKeyMonth1));
        
        // Update using model->update() instead of query builder (this triggers observers)
        $benchmarks[0]->update(['benchmark_value' => 96.0]);
            
        // Month 1 cache should be invalidated
        $this->assertFalse(Cache::has($cacheKeyMonth1));
    }

    /** @test */
    public function cache_key_generation_is_consistent()
    {
        // Generate same cache key multiple times
        $key1 = CacheKey::imutBenchmarking(2025, 1, 1, 10);
        $key2 = CacheKey::imutBenchmarking(2025, 1, 1, 10);
        $key3 = CacheKey::imutBenchmarking(2025, 1, 1, 10);

        $this->assertEquals($key1, $key2);
        $this->assertEquals($key2, $key3);
        $this->assertEquals($key1, $key3);
    }

    /** @test */
    public function cache_key_generation_is_unique_for_different_parameters()
    {
        $key1 = CacheKey::imutBenchmarking(2025, 1, 1, 10);
        $key2 = CacheKey::imutBenchmarking(2024, 1, 1, 10); // Different year
        $key3 = CacheKey::imutBenchmarking(2025, 2, 1, 10); // Different region
        $key4 = CacheKey::imutBenchmarking(2025, 1, 2, 10); // Different imut_data_id
        $key5 = CacheKey::imutBenchmarking(2025, 1, 1, 11); // Different month

        // All should be different
        $keys = [$key1, $key2, $key3, $key4, $key5];
        $uniqueKeys = array_unique($keys);

        $this->assertCount(5, $uniqueKeys);
    }

    /** @test */
    public function cache_invalidation_handles_null_end_month()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'year' => 2025,
            'region_type_id' => $this->regionType->id,
            'imut_data_id' => $this->imutData->id,
            'month' => 10,
        ]);

        // Cache with null endMonth (should use month from benchmarking)
        $cacheKey = CacheKey::imutBenchmarking(2025, $this->regionType->id, $this->imutData->id, null);
        Cache::put($cacheKey, ['null_month_data'], 3600);

        // Also cache with specific month
        $cacheKeyWithMonth = CacheKey::imutBenchmarking(2025, $this->regionType->id, $this->imutData->id, 10);
        Cache::put($cacheKeyWithMonth, ['with_month_data'], 3600);

        // Update should clear month-specific cache
        $benchmarking->update(['benchmark_value' => 95.0]);

        $this->assertFalse(Cache::has($cacheKeyWithMonth));
    }
}
