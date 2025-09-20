<?php

namespace Tests\Feature\Cache;

use App\Models\User;
use App\Models\UnitKerja;
use App\Models\ImutData;
use App\Models\ImutCategory;
use App\Models\LaporanImut;
use App\Services\Cache\CacheManager;
use App\Services\Cache\LaporanImutCacheService;
use App\Services\Cache\ImutDataCacheService;
use App\Services\Cache\UserCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CacheManagerTest extends TestCase
{
    use RefreshDatabase;

    private CacheManager $cacheManager;

    protected function setUp(): void
    {
        parent::setUp();

        // Use array cache for testing
        config(['cache.default' => 'array']);

        $this->cacheManager = app(CacheManager::class);
    }

    /** @test */
    public function it_can_warm_up_caches(): void
    {
        // Create some test data
        $unitKerja = UnitKerja::factory()->create();
        $category = ImutCategory::factory()->create();
        $imutData = ImutData::factory()->create(['imut_kategori_id' => $category->id]);
        $imutData->unitKerja()->attach($unitKerja->id);

        $user = User::factory()->create();
        $user->unitKerja()->attach($unitKerja->id);
        LaporanImut::factory()->create(['created_by' => $user->id]);

        $result = $this->cacheManager->warmUp();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('duration_seconds', $result);
        $this->assertArrayHasKey('results', $result);
        $this->assertArrayHasKey('timestamp', $result);

        $this->assertTrue($result['success']);
        $this->assertIsFloat($result['duration_seconds']);
        $this->assertIsArray($result['results']);
    }

    /** @test */
    public function it_can_get_health_status(): void
    {
        $status = $this->cacheManager->getHealthStatus();

        $this->assertIsArray($status);
        $this->assertArrayHasKey('overall_status', $status);
        $this->assertArrayHasKey('timestamp', $status);
        $this->assertArrayHasKey('stores', $status);
        $this->assertArrayHasKey('services', $status);
        $this->assertArrayHasKey('metrics', $status);

        $this->assertContains($status['overall_status'], ['healthy', 'degraded', 'unhealthy']);
    }

    /** @test */
    public function it_can_flush_all_caches(): void
    {
        // Create and cache some data first
        $unitKerja = UnitKerja::factory()->create();
        $category = ImutCategory::factory()->create();
        $imutData = ImutData::factory()->create(['imut_kategori_id' => $category->id]);
        $imutData->unitKerja()->attach($unitKerja->id);

        $laporanCache = app(LaporanImutCacheService::class);
        $laporanCache->getDashboardSummary();

        $result = $this->cacheManager->flushAll();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('results', $result);
        $this->assertArrayHasKey('timestamp', $result);

        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_can_get_statistics(): void
    {
        $stats = $this->cacheManager->getStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('timestamp', $stats);
        $this->assertArrayHasKey('default_store', $stats);
        $this->assertArrayHasKey('stores', $stats);

        $this->assertEquals('array', $stats['default_store']);
    }

    /** @test */
    public function it_can_optimize_cache(): void
    {
        $result = $this->cacheManager->optimize();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('results', $result);
        $this->assertArrayHasKey('timestamp', $result);

        $this->assertTrue($result['success']);
    }

    /** @test */
    public function it_handles_laporan_imut_model_events(): void
    {
        $unitKerja = UnitKerja::factory()->create();
        $category = ImutCategory::factory()->create();
        $imutData = ImutData::factory()->create(['imut_kategori_id' => $category->id]);
        $imutData->unitKerja()->attach($unitKerja->id);

        $user = User::factory()->create();
        $user->unitKerja()->attach($unitKerja->id);
        $laporan = LaporanImut::factory()->create(['created_by' => $user->id]);

        // Test created event
        $this->cacheManager->handleModelEvent('LaporanImut', 'created', $laporan);

        // Test updated event
        $this->cacheManager->handleModelEvent('LaporanImut', 'updated', $laporan);

        // Test deleted event
        $this->cacheManager->handleModelEvent('LaporanImut', 'deleted', $laporan);

        // All event handling should complete without errors
        $this->assertTrue(true);
    }

    /** @test */
    public function it_handles_imut_data_model_events(): void
    {
        $unitKerja = UnitKerja::factory()->create();
        $category = ImutCategory::factory()->create();
        $imutData = ImutData::factory()->create(['imut_kategori_id' => $category->id]);
        $imutData->unitKerja()->attach($unitKerja->id);

        // Test events
        $this->cacheManager->handleModelEvent('ImutData', 'created', $imutData);
        $this->cacheManager->handleModelEvent('ImutData', 'updated', $imutData);
        $this->cacheManager->handleModelEvent('ImutData', 'deleted', $imutData);

        $this->assertTrue(true);
    }

    /** @test */
    public function it_handles_user_model_events(): void
    {
        $user = User::factory()->create();

        // Test events
        $this->cacheManager->handleModelEvent('User', 'created', $user);
        $this->cacheManager->handleModelEvent('User', 'updated', $user);
        $this->cacheManager->handleModelEvent('User', 'deleted', $user);

        $this->assertTrue(true);
    }

    /** @test */
    public function it_handles_unit_kerja_model_events(): void
    {
        $unitKerja = UnitKerja::factory()->create();

        // Test events
        $this->cacheManager->handleModelEvent('UnitKerja', 'created', $unitKerja);
        $this->cacheManager->handleModelEvent('UnitKerja', 'updated', $unitKerja);
        $this->cacheManager->handleModelEvent('UnitKerja', 'deleted', $unitKerja);

        $this->assertTrue(true);
    }

    /** @test */
    public function it_handles_unknown_model_events_gracefully(): void
    {
        $user = User::factory()->create();

        // Test with unknown model
        $this->cacheManager->handleModelEvent('UnknownModel', 'created', $user);

        // Test with unknown event
        $this->cacheManager->handleModelEvent('User', 'unknown_event', $user);

        // Should handle gracefully without errors
        $this->assertTrue(true);
    }

    /** @test */
    public function it_checks_individual_service_health(): void
    {
        $status = $this->cacheManager->getHealthStatus();

        $this->assertArrayHasKey('services', $status);
        $this->assertArrayHasKey('laporan_imut', $status['services']);
        $this->assertArrayHasKey('imut_data', $status['services']);
        $this->assertArrayHasKey('user', $status['services']);

        // Each service should have status and operations info
        foreach ($status['services'] as $serviceName => $serviceStatus) {
            $this->assertArrayHasKey('status', $serviceStatus);
            $this->assertContains($serviceStatus['status'], ['healthy', 'degraded', 'unhealthy']);

            if ($serviceStatus['status'] === 'healthy') {
                $this->assertArrayHasKey('operations', $serviceStatus);
                $this->assertArrayHasKey('put', $serviceStatus['operations']);
                $this->assertArrayHasKey('get', $serviceStatus['operations']);
                $this->assertArrayHasKey('forget', $serviceStatus['operations']);
            }
        }
    }

    /** @test */
    public function it_measures_cache_performance(): void
    {
        $startTime = microtime(true);

        // Perform multiple cache operations
        $laporanCache = app(LaporanImutCacheService::class);
        $imutDataCache = app(ImutDataCacheService::class);
        $userCache = app(UserCacheService::class);

        // Create test data
        $unitKerja = UnitKerja::factory()->create();
        $category = ImutCategory::factory()->create();
        $imutData = ImutData::factory()->create(['imut_kategori_id' => $category->id]);
        $imutData->unitKerja()->attach($unitKerja->id);

        $user = User::factory()->create();
        $user->unitKerja()->attach($unitKerja->id);
        $laporan = LaporanImut::factory()->create(['created_by' => $user->id]);

        // Perform cache operations
        $laporanCache->getDashboardSummary();
        $imutDataCache->getGlobalMetrics();
        $userCache->getRoleStatistics();

        $endTime = microtime(true);
        $duration = $endTime - $startTime;

        // Cache operations should complete within reasonable time (< 1 second for array cache)
        $this->assertLessThan(1.0, $duration);
    }

    /** @test */
    public function it_handles_cache_failures_gracefully(): void
    {
        // Test with invalid cache store to simulate failures
        config(['cache.default' => 'invalid_store']);

        $cacheManager = app(CacheManager::class);

        // These operations should not throw exceptions
        $healthStatus = $cacheManager->getHealthStatus();
        $this->assertEquals('unhealthy', $healthStatus['overall_status']);

        $stats = $cacheManager->getStatistics();
        $this->assertArrayHasKey('error', $stats);
    }
}
