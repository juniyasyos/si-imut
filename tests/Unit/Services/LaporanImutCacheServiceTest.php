<?php

use App\Services\LaporanImut\LaporanImutCacheService;
use App\Support\CacheKey;
use Illuminate\Cache\Repository as CacheRepository;
use Illuminate\Cache\ArrayStore;

beforeEach(function () {
    // Create a mock cache for testing
    $store = new ArrayStore();
    $this->cache = new CacheRepository($store);
    $this->service = new LaporanImutCacheService($this->cache);
});

it('can cache and retrieve latest laporan data', function () {
    $testData = ['id' => 1, 'name' => 'Test Laporan'];

    $result = $this->service->getCachedLatestLaporan(function () use ($testData) {
        return $testData;
    });

    expect($result)->toEqual($testData);

    // Second call should return cached data without calling callback
    $result2 = $this->service->getCachedLatestLaporan(function () {
        return ['different' => 'data']; // This shouldn't be called
    });

    expect($result2)->toEqual($testData);
});

it('can cache and retrieve dashboard chart data', function () {
    $chartData = [
        'labels' => ['Jan', 'Feb', 'Mar'],
        'data' => [10, 20, 30]
    ];

    $result = $this->service->getCachedChartData(function () use ($chartData) {
        return $chartData;
    });

    expect($result)->toEqual($chartData);
});

it('can cache and retrieve recent laporan list', function () {
    $laporanList = [
        ['id' => 1, 'title' => 'Laporan 1'],
        ['id' => 2, 'title' => 'Laporan 2'],
    ];
    $limit = 5;

    $result = $this->service->getCachedRecentLaporanList(function () use ($laporanList) {
        return $laporanList;
    }, $limit);

    expect($result)->toEqual($laporanList);
});

it('can cache and retrieve specific laporan data', function () {
    $laporanId = 123;
    $laporanData = ['id' => $laporanId, 'details' => 'test data'];

    $result = $this->service->getCachedLaporanData($laporanId, function () use ($laporanData) {
        return $laporanData;
    });

    expect($result)->toEqual($laporanData);
});

it('can use custom remember method', function () {
    $key = 'test-key';
    $data = ['test' => 'value'];
    $minutes = 10;

    $result = $this->service->remember($key, $minutes, function () use ($data) {
        return $data;
    });

    expect($result)->toEqual($data);
});

it('can clear laporan caches', function () {
    // Put some test data in cache first
    $this->cache->put(CacheKey::latestLaporan(), 'test-data');
    $this->cache->put(CacheKey::dashboardSiimutChartData(), 'chart-data');
    $this->cache->put(CacheKey::recentLaporanList(5), 'list-data');

    // Clear caches
    $this->service->clearLaporanCaches();

    // Verify caches are cleared
    expect($this->cache->has(CacheKey::latestLaporan()))->toBe(false);
    expect($this->cache->has(CacheKey::dashboardSiimutChartData()))->toBe(false);
    expect($this->cache->has(CacheKey::recentLaporanList(5)))->toBe(false);
});

it('can clear cache for specific laporan', function () {
    $laporanId = 456;

    // Put test data in cache
    $this->cache->put(CacheKey::dashboardSiimutAllData($laporanId), 'laporan-data');

    // Clear specific laporan cache
    $this->service->clearLaporanCaches($laporanId);

    // Verify specific cache is cleared
    expect($this->cache->has(CacheKey::dashboardSiimutAllData($laporanId)))->toBe(false);
});

it('can clear cache on laporan update', function () {
    $laporanId = 789;

    $this->cache->put(CacheKey::latestLaporan(), 'test');
    $this->cache->put(CacheKey::dashboardSiimutAllData($laporanId), 'test');

    $this->service->clearCacheOnLaporanUpdate($laporanId);

    expect($this->cache->has(CacheKey::latestLaporan()))->toBe(false);
    expect($this->cache->has(CacheKey::dashboardSiimutAllData($laporanId)))->toBe(false);
});

it('can clear cache on penilaian update', function () {
    $this->cache->put(CacheKey::latestLaporan(), 'test');
    $this->cache->put(CacheKey::dashboardSiimutChartData(), 'test');

    $this->service->clearCacheOnPenilaianUpdate();

    expect($this->cache->has(CacheKey::latestLaporan()))->toBe(false);
    expect($this->cache->has(CacheKey::dashboardSiimutChartData()))->toBe(false);
});
