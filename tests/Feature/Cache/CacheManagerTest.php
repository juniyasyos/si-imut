<?php

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

uses(RefreshDatabase::class);

beforeEach(function () {
    // Use array cache for testing
    config(['cache.default' => 'array']);

    $this->cacheManager = app(CacheManager::class);
});

test('it can warm up caches', function () {
    // Create some test data
    $unitKerja = UnitKerja::factory()->create();
    $category = ImutCategory::factory()->create();
    $imutData = ImutData::factory()->create(['imut_kategori_id' => $category->id]);
    $imutData->unitKerja()->attach($unitKerja->id);

    $user = User::factory()->create();
    $user->unitKerjas()->attach($unitKerja->id);
    LaporanImut::factory()->create(['created_by' => $user->id]);

    $result = $this->cacheManager->warmUp();

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['success', 'duration_seconds', 'results', 'timestamp'])
        ->and($result['success'])->toBeTrue()
        ->and($result['duration_seconds'])->toBeFloat()
        ->and($result['results'])->toBeArray();
});

test('it can get health status', function () {
    $status = $this->cacheManager->getHealthStatus();

    expect($status)->toBeArray()
        ->and($status)->toHaveKeys(['overall_status', 'timestamp', 'stores', 'services', 'metrics'])
        ->and($status['overall_status'])->toBeIn(['healthy', 'degraded', 'unhealthy']);
});

test('it can flush all caches', function () {
    // Create and cache some data first
    $unitKerja = UnitKerja::factory()->create();
    $category = ImutCategory::factory()->create();
    $imutData = ImutData::factory()->create(['imut_kategori_id' => $category->id]);
    $imutData->unitKerja()->attach($unitKerja->id);

    $laporanCache = app(LaporanImutCacheService::class);
    $laporanCache->getDashboardSummary();

    $result = $this->cacheManager->flushAll();

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['success', 'results', 'timestamp'])
        ->and($result['success'])->toBeTrue();
});

test('it can get statistics', function () {
    $stats = $this->cacheManager->getStatistics();

    expect($stats)->toBeArray()
        ->and($stats)->toHaveKeys(['timestamp', 'default_store', 'stores'])
        ->and($stats['default_store'])->toBe('array');
});

test('it can optimize cache', function () {
    $result = $this->cacheManager->optimize();

    expect($result)->toBeArray()
        ->and($result)->toHaveKeys(['success', 'results', 'timestamp'])
        ->and($result['success'])->toBeTrue();
});

test('it handles laporan imut model events', function () {
    $unitKerja = UnitKerja::factory()->create();
    $category = ImutCategory::factory()->create();
    $imutData = ImutData::factory()->create(['imut_kategori_id' => $category->id]);
    $imutData->unitKerja()->attach($unitKerja->id);

    $user = User::factory()->create();
    $user->unitKerjas()->attach($unitKerja->id);
    $laporan = LaporanImut::factory()->create(['created_by' => $user->id]);

    // Test created event
    $this->cacheManager->handleModelEvent('LaporanImut', 'created', $laporan);

    // Test updated event
    $this->cacheManager->handleModelEvent('LaporanImut', 'updated', $laporan);

    // Test deleted event
    $this->cacheManager->handleModelEvent('LaporanImut', 'deleted', $laporan);

    // All event handling should complete without errors
    expect(true)->toBeTrue();
});

test('it handles imut data model events', function () {
    $unitKerja = UnitKerja::factory()->create();
    $category = ImutCategory::factory()->create();
    $imutData = ImutData::factory()->create(['imut_kategori_id' => $category->id]);
    $imutData->unitKerja()->attach($unitKerja->id);

    // Test events
    $this->cacheManager->handleModelEvent('ImutData', 'created', $imutData);
    $this->cacheManager->handleModelEvent('ImutData', 'updated', $imutData);
    $this->cacheManager->handleModelEvent('ImutData', 'deleted', $imutData);

    expect(true)->toBeTrue();
});

test('it handles user model events', function () {
    $user = User::factory()->create();

    // Test events
    $this->cacheManager->handleModelEvent('User', 'created', $user);
    $this->cacheManager->handleModelEvent('User', 'updated', $user);
    $this->cacheManager->handleModelEvent('User', 'deleted', $user);

    expect(true)->toBeTrue();
});

test('it handles unit kerja model events', function () {
    $unitKerja = UnitKerja::factory()->create();

    // Test events
    $this->cacheManager->handleModelEvent('UnitKerja', 'created', $unitKerja);
    $this->cacheManager->handleModelEvent('UnitKerja', 'updated', $unitKerja);
    $this->cacheManager->handleModelEvent('UnitKerja', 'deleted', $unitKerja);

    expect(true)->toBeTrue();
});

test('it handles unknown model events gracefully', function () {
    $user = User::factory()->create();

    // Test with unknown model
    $this->cacheManager->handleModelEvent('UnknownModel', 'created', $user);

    // Test with unknown event
    $this->cacheManager->handleModelEvent('User', 'unknown_event', $user);

    // Should handle gracefully without errors
    expect(true)->toBeTrue();
});

test('it checks individual service health', function () {
    $status = $this->cacheManager->getHealthStatus();

    expect($status)->toHaveKey('services')
        ->and($status['services'])->toHaveKeys(['laporan_imut', 'imut_data', 'user']);

    // Each service should have status and operations info
    foreach ($status['services'] as $serviceName => $serviceStatus) {
        expect($serviceStatus)->toHaveKey('status')
            ->and($serviceStatus['status'])->toBeIn(['healthy', 'degraded', 'unhealthy']);

        if ($serviceStatus['status'] === 'healthy') {
            expect($serviceStatus)->toHaveKey('operations')
                ->and($serviceStatus['operations'])->toHaveKeys(['put', 'get', 'forget']);
        }
    }
});

test('it measures cache performance', function () {
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
    $user->unitKerjas()->attach($unitKerja->id);
    $laporan = LaporanImut::factory()->create(['created_by' => $user->id]);

    // Perform cache operations
    $laporanCache->getDashboardSummary();
    $imutDataCache->getGlobalMetrics();
    $userCache->getRoleStatistics();

    $endTime = microtime(true);
    $duration = $endTime - $startTime;

    // Cache operations should complete within reasonable time (< 1 second for array cache)
    expect($duration)->toBeLessThan(1.0);
});

test('it handles cache failures gracefully', function () {
    // Test with invalid cache store to simulate failures
    config(['cache.default' => 'invalid_store']);

    $cacheManager = app(CacheManager::class);

    // These operations should not throw exceptions
    $healthStatus = $cacheManager->getHealthStatus();
    expect($healthStatus['overall_status'])->toBe('unhealthy');

    $stats = $cacheManager->getStatistics();
    expect($stats)->toHaveKey('error');
});