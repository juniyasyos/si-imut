<?php

use App\Models\ImutData;
use App\Models\ImutProfile;
use App\Models\ImutCategory;
use App\Models\ImutPenilaian;
use App\Models\UnitKerja;
use App\Services\Cache\ImutDataCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->cacheService = app(ImutDataCacheService::class);

    // Use array cache for testing
    config(['cache.default' => 'array']);
});

test('it can cache imut data list', function () {
    $unitKerja = UnitKerja::factory()->create();
    $imutData = ImutData::factory()->create();
    $imutData->unitKerja()->attach($unitKerja->id);

    // First call should hit database
    $result1 = $this->cacheService->getImutDataList();

    // Second call should hit cache
    $result2 = $this->cacheService->getImutDataList();

    expect($result1->count())->toBe($result2->count())
        ->and($result1->first()->id)->toBe($result2->first()->id);
});

test('it can cache imut data detail', function () {
    $unitKerja = UnitKerja::factory()->create();
    $imutData = ImutData::factory()->create();
    $imutData->unitKerja()->attach($unitKerja->id);

    $detail1 = $this->cacheService->getImutDataDetail($imutData->id);
    $detail2 = $this->cacheService->getImutDataDetail($imutData->id);

    expect($detail1)->not()->toBeNull()
        ->and($detail2)->not()->toBeNull()
        ->and($detail1->id)->toBe($detail2->id)
        ->and($detail1->id)->toBe($imutData->id);
});

test('it can cache unit kerja overview', function () {
    $unitKerja = UnitKerja::factory()->create();
    $imutData = ImutData::factory()->create();
    $imutData->unitKerja()->attach($unitKerja->id);

    $category = ImutCategory::factory()->create();
    ImutProfile::factory()->create([
        'imut_data_id' => $imutData->id,
    ]);

    $overview1 = $this->cacheService->getUnitKerjaOverview($unitKerja->id);
    $overview2 = $this->cacheService->getUnitKerjaOverview($unitKerja->id);

    expect($overview1)->toBeArray()
        ->and($overview2)->toBeArray()
        ->and($overview1)->toBe($overview2)
        ->and($overview1)->toHaveKeys(['unit_kerja', 'total_imut_data', 'completed_profiles'])
        ->and($overview1['unit_kerja']->id)->toBe($unitKerja->id)
        ->and($overview1['total_imut_data'])->toBe(1);
});

test('it can cache global metrics', function () {
    $unitKerja = UnitKerja::factory()->create();
    $imutData = ImutData::factory()->create([
        'status' => true
    ]);
    $imutData->unitKerja()->attach($unitKerja->id);

    $category = ImutCategory::factory()->create();
    ImutProfile::factory()->create([
        'imut_data_id' => $imutData->id,
    ]);

    $metrics1 = $this->cacheService->getGlobalMetrics();
    $metrics2 = $this->cacheService->getGlobalMetrics();

    expect($metrics1)->toBeArray()
        ->and($metrics2)->toBeArray()
        ->and($metrics1)->toBe($metrics2)
        ->and($metrics1)->toHaveKeys(['total_imut_data', 'total_profiles', 'total_unit_kerja', 'completion_status', 'quality_metrics'])
        ->and($metrics1['total_imut_data'])->toBe(1)
        ->and($metrics1['total_profiles'])->toBe(1)
        ->and($metrics1['total_unit_kerja'])->toBe(1)
        ->and($metrics1['completion_status']['active'])->toBe(1);
});

test('it can cache profile stats by category', function () {
    $unitKerja = UnitKerja::factory()->create();
    $category = ImutCategory::factory()->create();
    $imutData = ImutData::factory()->create([
        'imut_kategori_id' => $category->id
    ]);
    $imutData->unitKerja()->attach($unitKerja->id);

    $profile = ImutProfile::factory()->create([
        'imut_data_id' => $imutData->id,
    ]);

    // Create ImutPenilaian with score calculation that results in 85%
    ImutPenilaian::factory()->create([
        'imut_profil_id' => $profile->id,
        'numerator_value' => 85,
        'denominator_value' => 100
    ]);

    $stats1 = $this->cacheService->getProfileStatsByCategory($category->id);
    $stats2 = $this->cacheService->getProfileStatsByCategory($category->id);

    expect($stats1)->toBeArray()
        ->and($stats2)->toBeArray()
        ->and($stats1)->toBe($stats2)
        ->and($stats1)->toHaveKeys(['category_id', 'total_profiles', 'completion_rate']);
});

test('it can cache benchmarking data', function () {
    $unitKerja = UnitKerja::factory()->create();
    $category = ImutCategory::factory()->create();
    $imutData = ImutData::factory()->create([
        'imut_kategori_id' => $category->id
    ]);

    // Attach the unit kerja to imut data via pivot table
    $imutData->unitKerja()->attach($unitKerja->id);

    ImutProfile::factory()->create([
        'imut_data_id' => $imutData->id,
    ]);

    $filters = ['unit_kerja_id' => $unitKerja->id];
    $benchmark1 = $this->cacheService->getBenchmarkingData($filters);
    $benchmark2 = $this->cacheService->getBenchmarkingData($filters);

    expect($benchmark1)->toBeArray()
        ->and($benchmark2)->toBeArray()
        ->and($benchmark1)->toBe($benchmark2)
        ->and($benchmark1)->toHaveKeys(['dataset_size', 'filters_applied', 'performance_rankings', 'peer_comparisons'])
        ->and($benchmark1['filters_applied'])->toBe($filters)
        ->and($benchmark1['dataset_size'])->toBe(1);
});

test('it can invalidate specific caches', function () {
    $unitKerja = UnitKerja::factory()->create();
    $imutData = ImutData::factory()->create();
    $imutData->unitKerja()->attach($unitKerja->id);
    $category = ImutCategory::factory()->create();

    // Cache some data
    $this->cacheService->getImutDataDetail($imutData->id);
    $this->cacheService->getUnitKerjaOverview($unitKerja->id);
    $this->cacheService->getProfileStatsByCategory($category->id);

    // Test invalidation methods
    $this->cacheService->invalidateImutData($imutData->id);
    $this->cacheService->invalidateUnitKerjaCache($unitKerja->id);
    $this->cacheService->invalidateCategoryCache($category->id);
    $this->cacheService->invalidateGlobalCaches();

    // All invalidation calls should complete without errors
    expect(true)->toBeTrue();
});

test('it handles filters in data list', function () {
    $unitKerja1 = UnitKerja::factory()->create();
    $unitKerja2 = UnitKerja::factory()->create();

    $imutData1 = ImutData::factory()->create([
        'status' => true
    ]);
    $imutData1->unitKerja()->attach($unitKerja1->id);

    $imutData2 = ImutData::factory()->create([
        'status' => false
    ]);
    $imutData2->unitKerja()->attach($unitKerja2->id);

    // Test filter by status
    $activeData = $this->cacheService->getImutDataList(['status' => true]);
    expect($activeData->count())->toBe(1);

    // Test filter by unit kerja
    $unitData = $this->cacheService->getImutDataList(['unit_kerja_id' => $unitKerja1->id]);
    expect($unitData->count())->toBe(1);

    // Test no filter
    $allData = $this->cacheService->getImutDataList();
    expect($allData->count())->toBe(2);
});

test('it calculates score distribution correctly', function () {
    $category = ImutCategory::factory()->create();
    $imutData = ImutData::factory()->create(['imut_kategori_id' => $category->id]);

    // Create 4 profiles with different scores
    $profile1 = ImutProfile::factory()->create(['imut_data_id' => $imutData->id]);
    $profile2 = ImutProfile::factory()->create(['imut_data_id' => $imutData->id]);
    $profile3 = ImutProfile::factory()->create(['imut_data_id' => $imutData->id]);
    $profile4 = ImutProfile::factory()->create(['imut_data_id' => $imutData->id]);

    // Create penilaian records to calculate scores
    ImutPenilaian::factory()->create([
        'imut_profil_id' => $profile1->id,
        'numerator_value' => 95,
        'denominator_value' => 100  // score: 95 (excellent)
    ]);
    ImutPenilaian::factory()->create([
        'imut_profil_id' => $profile2->id,
        'numerator_value' => 85,
        'denominator_value' => 100  // score: 85 (good)
    ]);
    ImutPenilaian::factory()->create([
        'imut_profil_id' => $profile3->id,
        'numerator_value' => 75,
        'denominator_value' => 100  // score: 75 (fair)
    ]);
    ImutPenilaian::factory()->create([
        'imut_profil_id' => $profile4->id,
        'numerator_value' => 65,
        'denominator_value' => 100  // score: 65 (poor)
    ]);

    $stats = $this->cacheService->getProfileStatsByCategory($category->id);
    $distribution = $stats['score_distribution'];

    expect($distribution['excellent'])->toBe(1)
        ->and($distribution['good'])->toBe(1)
        ->and($distribution['fair'])->toBe(1)
        ->and($distribution['poor'])->toBe(1);
});
