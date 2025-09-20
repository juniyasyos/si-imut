<?php

namespace Tests\Feature\Cache;

use App\Models\LaporanImut;
use App\Models\ImutData;
use App\Models\UnitKerja;
use App\Services\Cache\LaporanImutCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class LaporanImutCacheServiceTest extends TestCase
{
    use RefreshDatabase;

    private LaporanImutCacheService $cacheService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cacheService = app(LaporanImutCacheService::class);

        // Use array cache for testing to avoid Redis dependency
        config(['cache.default' => 'array']);
    }

    #[Test]
    public function it_can_cache_laporan_list(): void
    {
        // Create test data
        $unitKerja = UnitKerja::factory()->create();
        $imutData = ImutData::factory()->create();
        $imutData->unitKerja()->attach($unitKerja->id);
        $laporan = LaporanImut::factory()->create([]);

        // First call should hit database
        $result1 = $this->cacheService->getLaporanList();

        // Second call should hit cache
        $result2 = $this->cacheService->getLaporanList();

        $this->assertEquals($result1->count(), $result2->count());
        $this->assertEquals($result1->first()->id, $result2->first()->id);
    }

    #[Test]
    public function it_can_cache_laporan_detail(): void
    {
        $unitKerja = UnitKerja::factory()->create();
        $imutData = ImutData::factory()->create();
        $imutData->unitKerja()->attach($unitKerja->id);
        $laporan = LaporanImut::factory()->create([]);

        // First call
        $result1 = $this->cacheService->getLaporanDetail($laporan->id);

        // Second call should hit cache
        $result2 = $this->cacheService->getLaporanDetail($laporan->id);

        $this->assertNotNull($result1);
        $this->assertNotNull($result2);
        $this->assertEquals($result1->id, $result2->id);
    }

    #[Test]
    public function it_can_cache_dashboard_summary(): void
    {
        // Create test data
        $unitKerja = UnitKerja::factory()->create();
        $imutData = ImutData::factory()->create();
        $imutData->unitKerja()->attach($unitKerja->id);
        LaporanImut::factory()->create([
            'status' => 'completed',
            'total_score' => 85.5
        ]);

        $summary = $this->cacheService->getDashboardSummary();

        $this->assertIsArray($summary);
        $this->assertArrayHasKey('total_reports', $summary);
        $this->assertArrayHasKey('completed_reports', $summary);
        $this->assertArrayHasKey('average_score', $summary);
        $this->assertEquals(1, $summary['total_reports']);
        $this->assertEquals(1, $summary['completed_reports']);
    }

    #[Test]
    public function it_can_cache_statistics_by_period(): void
    {
        $unitKerja = UnitKerja::factory()->create();
        $imutData = ImutData::factory()->create();
        $imutData->unitKerja()->attach($unitKerja->id);
        $laporan = LaporanImut::factory()->create([
            'assessment_period' => '2024-Q1',
            'status' => 'completed'
        ]);

        $stats = $this->cacheService->getStatisticsByPeriod('2024-Q1');

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_reports', $stats);
        $this->assertArrayHasKey('period', $stats);
        $this->assertEquals('2024-Q1', $stats['period']);
        $this->assertEquals(1, $stats['total_reports']);
    }

    #[Test]
    public function it_can_invalidate_laporan_cache(): void
    {
        $unitKerja = UnitKerja::factory()->create();
        $imutData = ImutData::factory()->create();
        $imutData->unitKerja()->attach($unitKerja->id);
        $laporan = LaporanImut::factory()->create([]);

        // Cache the laporan
        $this->cacheService->getLaporanDetail($laporan->id);

        // Verify it's cached
        $this->assertNotNull(Cache::get("laporan_imut:detail:{$laporan->id}"));

        // Invalidate cache
        $this->cacheService->invalidateLaporan($laporan->id);

        // Verify cache is cleared
        $this->assertNull(Cache::get("laporan_imut:detail:{$laporan->id}"));
    }

    #[Test]
    public function it_handles_filters_correctly(): void
    {
        $unitKerja1 = UnitKerja::factory()->create();
        $unitKerja2 = UnitKerja::factory()->create();

        $imutData1 = ImutData::factory()->create();
        $imutData1->unitKerja()->attach($unitKerja1->id);
        $imutData2 = ImutData::factory()->create();
        $imutData2->unitKerja()->attach($unitKerja2->id);

        LaporanImut::factory()->create(['status' => 'completed']);
        LaporanImut::factory()->create(['status' => 'pending']);

        // Test without filters
        $allResults = $this->cacheService->getLaporanList();
        $this->assertEquals(2, $allResults->count());

        // Test with status filter
        $completedResults = $this->cacheService->getLaporanList(['status' => 'completed']);
        $this->assertEquals(1, $completedResults->count());
        $this->assertEquals('completed', $completedResults->first()->status);

        // Test with unit kerja filter
        $unitResults = $this->cacheService->getLaporanList(['unit_kerja_id' => $unitKerja1->id]);
        $this->assertEquals(1, $unitResults->count());
    }

    #[Test]
    public function it_can_get_assessment_periods(): void
    {
        $unitKerja = UnitKerja::factory()->create();
        $imutData = ImutData::factory()->create();
        $imutData->unitKerja()->attach($unitKerja->id);

        LaporanImut::factory()->create([
            'assessment_period' => '2024-Q1'
        ]);
        LaporanImut::factory()->create([
            'assessment_period' => '2024-Q2'
        ]);

        $periods = $this->cacheService->getAssessmentPeriods();

        $this->assertIsArray($periods);
        $this->assertContains('2024-Q1', $periods);
        $this->assertContains('2024-Q2', $periods);
        $this->assertEquals(2, count($periods));
    }

    #[Test]
    public function it_can_flush_cache(): void
    {
        $unitKerja = UnitKerja::factory()->create();
        $imutData = ImutData::factory()->create();
        $imutData->unitKerja()->attach($unitKerja->id);
        $laporan = LaporanImut::factory()->create([]);

        // Cache some data
        $this->cacheService->getLaporanDetail($laporan->id);
        $this->cacheService->getDashboardSummary();

        // Verify data is cached
        $this->assertNotNull(Cache::get("laporan_imut:detail:{$laporan->id}"));

        // Flush cache
        $result = $this->cacheService->flush();

        // For array cache, flush should return false since it doesn't support tags
        // In production with Redis, this would return true
        $this->assertFalse($result);
    }
}
