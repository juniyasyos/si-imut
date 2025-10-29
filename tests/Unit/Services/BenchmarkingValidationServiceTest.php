<?php

namespace Tests\Unit\Services;

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\RegionType;
use App\Services\BenchmarkingValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BenchmarkingValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BenchmarkingValidationService $service;
    protected ImutData $imutData;
    protected RegionType $regionType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new BenchmarkingValidationService();
        $this->imutData = ImutData::factory()->create();
        $this->regionType = RegionType::factory()->create();
    }

    /** @test */
    public function validate_benchmark_value_accepts_valid_values()
    {
        $result = $this->service->validateBenchmarkValue(85.5);

        $this->assertTrue($result['valid']);
        $this->assertNull($result['message']);
    }

    /** @test */
    public function validate_benchmark_value_rejects_value_above_max()
    {
        $result = $this->service->validateBenchmarkValue(150);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('120', $result['message']);
    }

    /** @test */
    public function validate_benchmark_value_rejects_negative_value()
    {
        $result = $this->service->validateBenchmarkValue(-5);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('0', $result['message']);
    }

    /** @test */
    public function validate_benchmark_value_accepts_custom_range()
    {
        $result = $this->service->validateBenchmarkValue(50, 0, 100);

        $this->assertTrue($result['valid']);
    }

    /** @test */
    public function validate_period_logic_accepts_valid_period()
    {
        $result = $this->service->validatePeriodLogic('2025-10-01', '2025-10-31');

        $this->assertTrue($result['valid']);
        $this->assertNull($result['message']);
    }

    /** @test */
    public function validate_period_logic_rejects_end_before_start()
    {
        $result = $this->service->validatePeriodLogic('2025-10-31', '2025-10-01');

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('setelah', $result['message']);
    }

    /** @test */
    public function validate_period_logic_accepts_null_end_date()
    {
        $result = $this->service->validatePeriodLogic('2025-10-01', null);

        $this->assertTrue($result['valid']);
    }

    /** @test */
    public function validate_year_month_consistency_accepts_consistent_data()
    {
        $result = $this->service->validateYearMonthConsistency(2025, 10, '2025-10-01');

        $this->assertTrue($result['valid']);
    }

    /** @test */
    public function validate_year_month_consistency_rejects_inconsistent_year()
    {
        $result = $this->service->validateYearMonthConsistency(2025, 10, '2024-10-01');

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('tidak konsisten', $result['message']);
    }

    /** @test */
    public function validate_year_month_consistency_rejects_inconsistent_month()
    {
        $result = $this->service->validateYearMonthConsistency(2025, 10, '2025-09-01');

        $this->assertFalse($result['valid']);
    }

    /** @test */
    public function validate_duplicate_detects_existing_record()
    {
        ImutBenchmarking::factory()->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'year' => 2025,
            'month' => 10,
        ]);

        $result = $this->service->validateDuplicate(
            $this->imutData->id,
            $this->regionType->id,
            2025,
            10
        );

        $this->assertFalse($result['valid']);
        $this->assertNotNull($result['existing']);
        $this->assertStringContainsString('sudah ada', $result['message']);
    }

    /** @test */
    public function validate_duplicate_passes_for_unique_record()
    {
        $result = $this->service->validateDuplicate(
            $this->imutData->id,
            $this->regionType->id,
            2025,
            10
        );

        $this->assertTrue($result['valid']);
        $this->assertNull($result['existing']);
    }

    /** @test */
    public function validate_duplicate_ignores_specified_id()
    {
        $existing = ImutBenchmarking::factory()->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'year' => 2025,
            'month' => 10,
        ]);

        $result = $this->service->validateDuplicate(
            $this->imutData->id,
            $this->regionType->id,
            2025,
            10,
            $existing->id
        );

        $this->assertTrue($result['valid']);
    }

    /** @test */
    public function validate_period_overlap_detects_overlapping_periods()
    {
        ImutBenchmarking::factory()->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'period_start' => '2025-10-01',
            'period_end' => '2025-10-31',
            'is_active' => true,
        ]);

        $result = $this->service->validatePeriodOverlap(
            $this->imutData->id,
            $this->regionType->id,
            '2025-10-15',
            '2025-10-20'
        );

        $this->assertFalse($result['valid']);
        $this->assertGreaterThan(0, $result['overlapping']->count());
    }

    /** @test */
    public function validate_period_overlap_passes_for_non_overlapping_periods()
    {
        ImutBenchmarking::factory()->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'period_start' => '2025-10-01',
            'period_end' => '2025-10-31',
            'is_active' => true,
        ]);

        $result = $this->service->validatePeriodOverlap(
            $this->imutData->id,
            $this->regionType->id,
            '2025-11-01',
            '2025-11-30'
        );

        $this->assertTrue($result['valid']);
        $this->assertEquals(0, $result['overlapping']->count());
    }

    /** @test */
    public function validate_active_for_date_passes_for_valid_active_benchmarking()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'period_start' => '2025-10-01',
            'period_end' => '2025-10-31',
            'is_active' => true,
        ]);

        $result = $this->service->validateActiveForDate(
            $benchmarking,
            \Carbon\Carbon::create(2025, 10, 15)
        );

        $this->assertTrue($result['valid']);
    }

    /** @test */
    public function validate_active_for_date_fails_for_inactive()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'is_active' => false,
        ]);

        $result = $this->service->validateActiveForDate($benchmarking);

        $this->assertFalse($result['valid']);
        $this->assertStringContainsString('tidak aktif', $result['message']);
    }

    /** @test */
    public function validate_complete_validates_all_rules()
    {
        $data = [
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'year' => 2025,
            'month' => 10,
            'benchmark_value' => 85.5,
            'period_start' => '2025-10-01',
            'period_end' => '2025-10-31',
        ];

        $result = $this->service->validateComplete($data);

        $this->assertTrue($result['valid']);
        $this->assertEmpty($result['errors']);
    }

    /** @test */
    public function validate_complete_detects_missing_required_fields()
    {
        $data = [
            'year' => 2025,
            // Missing required fields
        ];

        $result = $this->service->validateComplete($data);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    /** @test */
    public function validate_complete_detects_invalid_benchmark_value()
    {
        $data = [
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'year' => 2025,
            'month' => 10,
            'benchmark_value' => 150, // Invalid
            'period_start' => '2025-10-01',
        ];

        $result = $this->service->validateComplete($data);

        $this->assertFalse($result['valid']);
        $this->assertNotEmpty($result['errors']);
    }

    /** @test */
    public function get_benchmarking_stats_returns_correct_statistics()
    {
        ImutBenchmarking::factory()->count(3)->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'is_active' => true,
            'benchmark_value' => 85.0,
        ]);

        ImutBenchmarking::factory()->count(2)->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'is_active' => false,
        ]);

        $stats = $this->service->getBenchmarkingStats($this->imutData->id, $this->regionType->id);

        $this->assertEquals(5, $stats['total']);
        $this->assertEquals(3, $stats['active']);
        $this->assertEquals(2, $stats['inactive']);
    }

    /** @test */
    public function get_active_benchmarkings_returns_only_active_records()
    {
        ImutBenchmarking::factory()->count(2)->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'is_active' => true,
        ]);

        ImutBenchmarking::factory()->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'is_active' => false,
        ]);

        $results = $this->service->getActiveBenchmarkings($this->imutData->id, $this->regionType->id);

        $this->assertEquals(2, $results->count());
        $this->assertTrue($results->every(fn($b) => $b->is_active === true));
    }
}
