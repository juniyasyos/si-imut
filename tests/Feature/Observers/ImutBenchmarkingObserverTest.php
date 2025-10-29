<?php

namespace Tests\Feature\Observers;

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\RegionType;
use App\Models\User;
use App\Support\CacheKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ImutBenchmarkingObserverTest extends TestCase
{
    use RefreshDatabase;

    protected $imutData;
    protected $regionType;

    protected function setUp(): void
    {
        parent::setUp();

        // Create related models once for all tests
        $this->imutData = ImutData::first() ?? ImutData::factory()->create();
        $this->regionType = RegionType::first() ?? RegionType::factory()->create(['type' => 'Test Region Obs ' . uniqid()]);
    }

    /** @test */
    public function it_sets_is_active_to_true_by_default_on_create()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'is_active' => null,
        ]);

        $this->assertTrue($benchmarking->is_active);
    }

    /** @test */
    public function it_sets_created_by_when_user_is_authenticated()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $benchmarking = ImutBenchmarking::factory()->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
        ]);

        $this->assertEquals($user->id, $benchmarking->created_by);
    }

    /** @test */
    public function it_sets_updated_by_when_user_is_authenticated()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $benchmarking = ImutBenchmarking::factory()->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
        ]);

        $this->assertEquals($user->id, $benchmarking->updated_by);
    }

    /** @test */
    public function it_updates_updated_by_on_model_update()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user1);
        $benchmarking = ImutBenchmarking::factory()->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
        ]);

        $this->assertEquals($user1->id, $benchmarking->updated_by);

        $this->actingAs($user2);
        $benchmarking->benchmark_value = 95.0;
        $benchmarking->save();

        $this->assertEquals($user2->id, $benchmarking->fresh()->updated_by);
    }

    /** @test */
    public function it_invalidates_cache_on_create()
    {
        $benchmarking = ImutBenchmarking::factory()->make([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'year' => 2025,
            'month' => 10,
        ]);

        // Set cache
        $cacheKey = CacheKey::imutBenchmarking(2025, $this->regionType->id, $this->imutData->id, 10);
        Cache::put($cacheKey, 'test_value', 60);

        $this->assertTrue(Cache::has($cacheKey));

        // Create benchmarking (should trigger observer to invalidate cache)
        $benchmarking->save();

        // Cache should be cleared
        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function it_invalidates_cache_on_update()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'year' => 2025,
            'month' => 10,
        ]);

        // Set cache
        $cacheKey = CacheKey::imutBenchmarking(2025, $this->regionType->id, $this->imutData->id, 10);
        Cache::put($cacheKey, 'test_value', 60);

        $this->assertTrue(Cache::has($cacheKey));

        // Update benchmarking
        $benchmarking->benchmark_value = 95.0;
        $benchmarking->save();

        // Cache should be cleared
        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function it_invalidates_cache_on_delete()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'year' => 2025,
            'month' => 10,
        ]);

        // Set cache
        $cacheKey = CacheKey::imutBenchmarking(2025, $this->regionType->id, $this->imutData->id, 10);
        Cache::put($cacheKey, 'test_value', 60);

        $this->assertTrue(Cache::has($cacheKey));

        // Delete benchmarking
        $benchmarking->delete();

        // Cache should be cleared
        $this->assertFalse(Cache::has($cacheKey));
    }

    /** @test */
    public function it_invalidates_multiple_cache_keys()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'year' => 2025,
            'month' => 10,
        ]);

        // Set multiple cache keys
        $keys = [];
        for ($month = 1; $month <= 12; $month++) {
            $key = CacheKey::imutBenchmarking(2025, $this->regionType->id, $this->imutData->id, $month);
            Cache::put($key, "test_value_{$month}", 60);
            $keys[] = $key;
        }

        // All should exist
        foreach ($keys as $key) {
            $this->assertTrue(Cache::has($key));
        }

        // Update benchmarking (should clear all month variants)
        $benchmarking->benchmark_value = 95.0;
        $benchmarking->save();

        // All should be cleared
        foreach ($keys as $key) {
            $this->assertFalse(Cache::has($key));
        }
    }

    /** @test */
    public function it_does_not_set_user_fields_when_not_authenticated()
    {
        // No authenticated user
        $benchmarking = ImutBenchmarking::factory()->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'created_by' => null,
            'updated_by' => null,
        ]);

        $this->assertNull($benchmarking->created_by);
        $this->assertNull($benchmarking->updated_by);
    }

    /** @test */
    public function it_preserves_existing_created_by_value()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $this->actingAs($user1);
        $benchmarking = ImutBenchmarking::factory()->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'created_by' => null, // Force null so observer sets it
            'updated_by' => null,
        ]);

        // Refresh to get observer-set values
        $benchmarking->refresh();
        $this->assertEquals($user1->id, $benchmarking->created_by);

        // Update with different user
        $this->actingAs($user2);
        $benchmarking->benchmark_value = 95.0;
        $benchmarking->save();

        // created_by should not change
        $this->assertEquals($user1->id, $benchmarking->fresh()->created_by);
        // But updated_by should change
        $this->assertEquals($user2->id, $benchmarking->fresh()->updated_by);
    }
}
