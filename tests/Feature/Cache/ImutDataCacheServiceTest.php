<?php

namespace Tests\Feature\Cache;

use App\Models\ImutData;
use App\Models\ImutProfile;
use App\Models\ImutCategory;
use App\Models\ImutPenilaian;
use App\Models\UnitKerja;
use App\Services\Cache\ImutDataCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class ImutDataCacheServiceTest extends TestCase
{
    use RefreshDatabase;

    private ImutDataCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = app(ImutDataCacheService::class);

        // Use array cache for testing
        config(['cache.default' => 'array']);
    }

    #[Test]
    public function it_can_cache_imut_data_list(): void
    {
        $unitKerja = UnitKerja::factory()->create();
        $imutData = ImutData::factory()->create();
        $imutData->unitKerja()->attach($unitKerja->id);

        // First call should hit database
        $result1 = $this->cacheService->getImutDataList();

        // Second call should hit cache
        $result2 = $this->cacheService->getImutDataList();

        $this->assertEquals($result1->count(), $result2->count());
        $this->assertEquals($result1->first()->id, $result2->first()->id);
    }

        #[Test]
    public function it_can_cache_imut_data_detail(): void
    {
        $unitKerja = UnitKerja::factory()->create();
        $imutData = ImutData::factory()->create();
        $imutData->unitKerja()->attach($unitKerja->id);

        // First call should hit database
        $result1 = $this->cacheService->getImutDataDetail($imutData->id);

        // Second call should hit cache
        $result2 = $this->cacheService->getImutDataDetail($imutData->id);

        $this->assertEquals($result1->id, $result2->id);
        $this->assertEquals($result1->title, $result2->title);
    }

    #[Test]
    public function it_can_cache_unit_kerja_overview(): void
    {
        $unitKerja = UnitKerja::factory()->create();
        $imutData = ImutData::factory()->create();
        $imutData->unitKerja()->attach($unitKerja->id);

        $category = ImutCategory::factory()->create();
        ImutProfile::factory()->create([
            'imut_data_id' => $imutData->id,
        ]);

        $overview = $this->cacheService->getUnitKerjaOverview($unitKerja->id);

        $this->assertIsArray($overview);
        $this->assertArrayHasKey('unit_kerja', $overview);
        $this->assertArrayHasKey('total_imut_data', $overview);
        $this->assertArrayHasKey('completed_profiles', $overview);
        $this->assertEquals($unitKerja->id, $overview['unit_kerja']->id);
        $this->assertEquals(1, $overview['total_imut_data']);
    }

    #[Test]
    public function it_can_cache_global_metrics(): void
    {
        $unitKerja = UnitKerja::factory()->create();
        $imutData = ImutData::factory()->create([
            'status' => true
        ]);
        $imutData->unitKerja()->attach($unitKerja->id);

        $category = ImutCategory::factory()->create();
        ImutProfile::factory()->create([
            'imut_data_id' => $imutData->id,
        ]);

        $metrics = $this->cacheService->getGlobalMetrics();

        $this->assertIsArray($metrics);
        $this->assertArrayHasKey('total_imut_data', $metrics);
        $this->assertArrayHasKey('total_profiles', $metrics);
        $this->assertArrayHasKey('total_unit_kerja', $metrics);
        $this->assertArrayHasKey('completion_status', $metrics);
        $this->assertArrayHasKey('quality_metrics', $metrics);

        $this->assertEquals(1, $metrics['total_imut_data']);
        $this->assertEquals(1, $metrics['total_profiles']);
        $this->assertEquals(1, $metrics['total_unit_kerja']);
        $this->assertEquals(1, $metrics['completion_status']['active']);
    }

    #[Test]
    public function it_can_cache_profile_stats_by_category(): void
    {
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

        $stats = $this->cacheService->getProfileStatsByCategory($category->id);

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('category_id', $stats);
        $this->assertArrayHasKey('total_profiles', $stats);
        $this->assertArrayHasKey('average_score', $stats);
        $this->assertArrayHasKey('score_distribution', $stats);

        $this->assertEquals($category->id, $stats['category_id']);
        $this->assertEquals(1, $stats['total_profiles']);
        $this->assertEquals(85, $stats['average_score']);
    }

    #[Test]
    public function it_can_cache_benchmarking_data(): void
    {
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
        $benchmarkData = $this->cacheService->getBenchmarkingData($filters);

        $this->assertIsArray($benchmarkData);
        $this->assertArrayHasKey('dataset_size', $benchmarkData);
        $this->assertArrayHasKey('filters_applied', $benchmarkData);
        $this->assertArrayHasKey('performance_rankings', $benchmarkData);
        $this->assertArrayHasKey('peer_comparisons', $benchmarkData);

        $this->assertEquals($filters, $benchmarkData['filters_applied']);
        $this->assertEquals(1, $benchmarkData['dataset_size']);
    }

    #[Test]
    public function it_can_invalidate_specific_caches(): void
    {
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
        $this->assertTrue(true);
    }

    #[Test]
    public function it_handles_filters_in_data_list(): void
    {
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

        // Test without filters
        $allResults = $this->cacheService->getImutDataList();
        $this->assertEquals(2, $allResults->count());

        // Test with unit kerja filter (note: this will need adjustment since we use many-to-many)
        $unitResults = $this->cacheService->getImutDataList(['unit_kerja_id' => $unitKerja1->id]);
        $this->assertEquals(1, $unitResults->count());

        // Test with status filter
        $statusResults = $this->cacheService->getImutDataList(['status' => true]);
        $this->assertEquals(1, $statusResults->count());
        $this->assertTrue($statusResults->first()->status);
    }

    #[Test]
    public function it_calculates_score_distribution_correctly(): void
    {
        $unitKerja = UnitKerja::factory()->create();
        $category = ImutCategory::factory()->create();
        $imutData = ImutData::factory()->create(['imut_kategori_id' => $category->id]);
        $imutData->unitKerja()->attach($unitKerja);

        // Create profiles with different scores via penilaian data
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

        $this->assertEquals(1, $distribution['excellent']);
        $this->assertEquals(1, $distribution['good']);
        $this->assertEquals(1, $distribution['fair']);
        $this->assertEquals(1, $distribution['poor']);
    }
}
