<?php

namespace Tests\Unit\Models;

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\RegionType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImutBenchmarkingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary related models
        $this->regionType = RegionType::factory()->create(['type' => 'provinsi']);
        $this->imutData = ImutData::factory()->create();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_create_benchmarking_with_all_fields()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'imut_data_id' => $this->imutData->id,
            'region_type_id' => $this->regionType->id,
            'year' => 2025,
            'month' => 10,
            'benchmark_value' => 85.5,
            'period_start' => '2025-10-01',
            'period_end' => '2025-10-31',
            'is_active' => true,
            'notes' => 'Test notes',
        ]);

        $this->assertDatabaseHas('imut_benchmarkings', [
            'id' => $benchmarking->id,
            'imut_data_id' => $this->imutData->id,
            'benchmark_value' => 85.5,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function scope_active_for_period_filters_correctly()
    {
        // Create benchmarking for October 2025
        $activeBenchmark = ImutBenchmarking::factory()->create([
            'period_start' => '2025-10-01',
            'period_end' => '2025-10-31',
            'is_active' => true,
        ]);

        // Create inactive benchmarking
        $inactiveBenchmark = ImutBenchmarking::factory()->create([
            'period_start' => '2025-10-01',
            'period_end' => '2025-10-31',
            'is_active' => false,
        ]);

        // Create benchmarking for different period
        $differentPeriod = ImutBenchmarking::factory()->create([
            'period_start' => '2025-11-01',
            'period_end' => '2025-11-30',
            'is_active' => true,
        ]);

        // Test date within October 2025
        $testDate = \Carbon\Carbon::create(2025, 10, 15);
        $results = ImutBenchmarking::activeForPeriod($testDate)->get();

        $this->assertTrue($results->contains($activeBenchmark));
        $this->assertFalse($results->contains($inactiveBenchmark));
        $this->assertFalse($results->contains($differentPeriod));
    }

    /** @test */
    public function scope_for_indicator_filters_by_imut_data_id()
    {
        $indicator1 = ImutData::factory()->create();
        $indicator2 = ImutData::factory()->create();

        $benchmark1 = ImutBenchmarking::factory()->create(['imut_data_id' => $indicator1->id]);
        $benchmark2 = ImutBenchmarking::factory()->create(['imut_data_id' => $indicator2->id]);

        $results = ImutBenchmarking::forIndicator($indicator1->id)->get();

        $this->assertTrue($results->contains($benchmark1));
        $this->assertFalse($results->contains($benchmark2));
    }

    /** @test */
    public function scope_for_region_filters_by_region_type_id()
    {
        // Use existing region types from database
        $region1 = \App\Models\RegionType::first();
        $region2 = \App\Models\RegionType::skip(1)->first() ?? \App\Models\RegionType::factory()->create(['type' => 'Test Region ' . uniqid()]);

        $benchmark1 = ImutBenchmarking::factory()->create(['region_type_id' => $region1->id]);
        $benchmark2 = ImutBenchmarking::factory()->create(['region_type_id' => $region2->id]);

        $results = ImutBenchmarking::forRegion($region1->id)->get();

        $this->assertTrue($results->contains($benchmark1));
        $this->assertFalse($results->contains($benchmark2));
    }

    /** @test */
    public function scope_for_region_accepts_array()
    {
        // Use existing region types from database
        $region1 = \App\Models\RegionType::first();
        $region2 = \App\Models\RegionType::skip(1)->first() ?? \App\Models\RegionType::factory()->create(['type' => 'Test Region ' . uniqid()]);
        $region3 = \App\Models\RegionType::skip(2)->first() ?? \App\Models\RegionType::factory()->create(['type' => 'Test Region ' . uniqid()]);

        $benchmark1 = ImutBenchmarking::factory()->create(['region_type_id' => $region1->id]);
        $benchmark2 = ImutBenchmarking::factory()->create(['region_type_id' => $region2->id]);
        $benchmark3 = ImutBenchmarking::factory()->create(['region_type_id' => $region3->id]);

        $results = ImutBenchmarking::forRegion([$region1->id, $region2->id])->get();

        $this->assertTrue($results->contains($benchmark1));
        $this->assertTrue($results->contains($benchmark2));
        $this->assertFalse($results->contains($benchmark3));
    }

    /** @test */
    public function scope_for_year_month_filters_correctly()
    {
        $benchmark2025_10 = ImutBenchmarking::factory()->create(['year' => 2025, 'month' => 10]);
        $benchmark2025_09 = ImutBenchmarking::factory()->create(['year' => 2025, 'month' => 9]);
        $benchmark2024_10 = ImutBenchmarking::factory()->create(['year' => 2024, 'month' => 10]);

        // Filter by year and month
        $results = ImutBenchmarking::forYearMonth(2025, 10)->get();

        $this->assertTrue($results->contains($benchmark2025_10));
        $this->assertTrue($results->contains($benchmark2025_09)); // month <= 10
        $this->assertFalse($results->contains($benchmark2024_10));
    }

    /** @test */
    public function is_valid_for_period_returns_true_for_date_within_period()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'period_start' => '2025-10-01',
            'period_end' => '2025-10-31',
            'is_active' => true,
        ]);

        $testDate = \Carbon\Carbon::create(2025, 10, 15);
        $this->assertTrue($benchmarking->isValidForPeriod($testDate));
    }

    /** @test */
    public function is_valid_for_period_returns_false_for_date_outside_period()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'period_start' => '2025-10-01',
            'period_end' => '2025-10-31',
            'is_active' => true,
        ]);

        $testDate = \Carbon\Carbon::create(2025, 11, 15);
        $this->assertFalse($benchmarking->isValidForPeriod($testDate));
    }

    /** @test */
    public function is_valid_for_period_returns_false_for_inactive()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'period_start' => '2025-10-01',
            'period_end' => '2025-10-31',
            'is_active' => false,
        ]);

        $testDate = \Carbon\Carbon::create(2025, 10, 15);
        $this->assertFalse($benchmarking->isValidForPeriod($testDate));
    }

    /** @test */
    public function is_valid_for_period_handles_null_end_date()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'period_start' => '2025-10-01',
            'period_end' => null, // Permanent
            'is_active' => true,
        ]);

        $futureDate = \Carbon\Carbon::create(2030, 12, 31);
        $this->assertTrue($benchmarking->isValidForPeriod($futureDate));
    }

    /** @test */
    public function get_value_for_period_returns_correct_value()
    {
        $imutData = ImutData::first() ?? ImutData::factory()->create();
        $regionType = RegionType::first() ?? RegionType::factory()->create(['type' => 'Test Unique ' . uniqid()]);

        ImutBenchmarking::factory()->create([
            'imut_data_id' => $imutData->id,
            'region_type_id' => $regionType->id,
            'benchmark_value' => 90.0,
            'period_start' => '2025-10-01',
            'period_end' => '2025-10-31',
            'is_active' => true,
        ]);

        $testDate = \Carbon\Carbon::create(2025, 10, 15);
        $value = ImutBenchmarking::getValueForPeriod($imutData->id, $regionType->id, $testDate);

        $this->assertEquals(90.0, $value);
    }

    /** @test */
    public function get_value_for_period_returns_null_when_no_match()
    {
        $testDate = \Carbon\Carbon::create(2025, 10, 15);
        $value = ImutBenchmarking::getValueForPeriod(999, 999, $testDate);

        $this->assertNull($value);
    }

    /** @test */
    public function it_has_creator_relationship()
    {
        $user = User::factory()->create();
        $benchmarking = ImutBenchmarking::factory()->create([
            'created_by' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $benchmarking->creator);
        $this->assertEquals($user->id, $benchmarking->creator->id);
    }

    /** @test */
    public function it_has_updater_relationship()
    {
        $user = User::factory()->create();
        $benchmarking = ImutBenchmarking::factory()->create([
            'updated_by' => $user->id,
        ]);

        $this->assertInstanceOf(User::class, $benchmarking->updater);
        $this->assertEquals($user->id, $benchmarking->updater->id);
    }

    /** @test */
    public function it_casts_dates_correctly()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'period_start' => '2025-10-01',
            'period_end' => '2025-10-31',
        ]);

        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $benchmarking->period_start);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $benchmarking->period_end);
    }

    /** @test */
    public function it_casts_is_active_to_boolean()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'is_active' => 1,
        ]);

        $this->assertIsBool($benchmarking->is_active);
        $this->assertTrue($benchmarking->is_active);
    }

    /** @test */
    public function it_casts_benchmark_value_to_decimal()
    {
        $benchmarking = ImutBenchmarking::factory()->create([
            'benchmark_value' => 85.567,
        ]);

        // Decimal:2 should round to 2 decimal places
        $this->assertEquals('85.57', $benchmarking->benchmark_value);
    }
}
