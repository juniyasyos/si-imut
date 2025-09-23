<?php

namespace Tests\Unit\Services\Form;

use App\Services\Calculator\ImutCalculatorService;
use App\Services\Form\FormCalculationService;
use PHPUnit\Framework\TestCase;
use Mockery;

class FormCalculationServiceTest extends TestCase
{
    private FormCalculationService $service;
    private ImutCalculatorService $mockCalculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockCalculator = Mockery::mock(ImutCalculatorService::class);
        $this->service = new FormCalculationService($this->mockCalculator);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_updates_penilaian_result_correctly()
    {
        $setValues = [];
        $getValues = [
            'numerator_value' => '80',
            'denominator_value' => '100',
        ];

        $set = function ($key, $value) use (&$setValues) {
            $setValues[$key] = $value;
        };

        $get = function ($key) use ($getValues) {
            return $getValues[$key] ?? null;
        };

        $this->mockCalculator
            ->shouldReceive('calculateImutResult')
            ->with(80.0, 100.0)
            ->andReturn(['percentage' => 80.0, 'is_valid' => true]);

        $this->service->updatePenilaianResult($set, $get);

        $this->assertEquals(80.0, $setValues['result_operation']);
    }

    /** @test */
    public function it_handles_empty_values_in_penilaian_result()
    {
        $setValues = [];
        $getValues = [];

        $set = function ($key, $value) use (&$setValues) {
            $setValues[$key] = $value;
        };

        $get = function ($key) use ($getValues) {
            return $getValues[$key] ?? null;
        };

        $this->mockCalculator
            ->shouldReceive('calculateImutResult')
            ->with(0.0, 0.0)
            ->andReturn(['percentage' => 0.0, 'is_valid' => false]);

        $this->service->updatePenilaianResult($set, $get);

        $this->assertEquals(0.0, $setValues['result_operation']);
    }

    /** @test */
    public function it_calculates_end_period_for_weekly_analysis()
    {
        $setValues = [];
        $getValues = [
            'start_period' => '2024-01-01',
            'analysis_period_type' => 'mingguan',
            'analysis_period_value' => 2,
        ];

        $set = function ($key, $value) use (&$setValues) {
            $setValues[$key] = $value;
        };

        $get = function ($key) use ($getValues) {
            return $getValues[$key] ?? null;
        };

        $this->service->calculateEndPeriod($set, $get);

        $this->assertEquals('2024-01-15', $setValues['end_period']);
    }

    /** @test */
    public function it_calculates_end_period_for_monthly_analysis()
    {
        $setValues = [];
        $getValues = [
            'start_period' => '2024-01-01',
            'analysis_period_type' => 'bulanan',
            'analysis_period_value' => 3,
        ];

        $set = function ($key, $value) use (&$setValues) {
            $setValues[$key] = $value;
        };

        $get = function ($key) use ($getValues) {
            return $getValues[$key] ?? null;
        };

        $this->service->calculateEndPeriod($set, $get);

        $this->assertEquals('2024-04-01', $setValues['end_period']);
    }

    /** @test */
    public function it_handles_missing_data_in_end_period_calculation()
    {
        $setValues = [];
        $getValues = [];

        $set = function ($key, $value) use (&$setValues) {
            $setValues[$key] = $value;
        };

        $get = function ($key) use ($getValues) {
            return $getValues[$key] ?? null;
        };

        $this->service->calculateEndPeriod($set, $get);

        // Should not set any value when data is missing
        $this->assertEmpty($setValues);
    }

    /** @test */
    public function it_validates_numerator_denominator()
    {
        // Valid case
        $this->assertTrue($this->service->isValidNumeratorDenominator(80, 100));

        // Invalid case - zero denominator
        $this->assertFalse($this->service->isValidNumeratorDenominator(80, 0));

        // Invalid case - negative denominator
        $this->assertFalse($this->service->isValidNumeratorDenominator(80, -10));
    }

    /** @test */
    public function it_generates_performance_suggestions()
    {
        $this->mockCalculator
            ->shouldReceive('isTargetAchieved')
            ->with(80.0, '>=', 75.0)
            ->andReturn(true);

        $suggestion = $this->service->generatePerformanceSuggestion(80.0, 75.0, '>=');

        $this->assertStringContainsString('Target tercapai', $suggestion);
        $this->assertStringContainsString('80%', $suggestion);
        $this->assertStringContainsString('75%', $suggestion);
    }

    /** @test */
    public function it_generates_improvement_suggestions()
    {
        $this->mockCalculator
            ->shouldReceive('isTargetAchieved')
            ->with(70.0, '>=', 80.0)
            ->andReturn(false);

        $suggestion = $this->service->generatePerformanceSuggestion(70.0, 80.0, '>=');

        $this->assertStringContainsString('Perlu peningkatan', $suggestion);
        $this->assertStringContainsString('10%', $suggestion);
    }

    /** @test */
    public function it_formats_display_values_correctly()
    {
        // Percentage format
        $result = $this->service->formatDisplayValue(75.567, 'percentage');
        $this->assertEquals('75.57%', $result);

        // Decimal format
        $result = $this->service->formatDisplayValue(75.567, 'decimal');
        $this->assertEquals('75.57', $result);

        // Integer format
        $result = $this->service->formatDisplayValue(75.567, 'integer');
        $this->assertEquals('76', $result);

        // Currency format
        $result = $this->service->formatDisplayValue(1000000, 'currency');
        $this->assertEquals('Rp 1.000.000', $result);

        // Default format
        $result = $this->service->formatDisplayValue(75.567, 'unknown');
        $this->assertEquals('75.567', $result);
    }

    /** @test */
    public function it_handles_different_operators_in_suggestions()
    {
        // Test <= operator
        $this->mockCalculator
            ->shouldReceive('isTargetAchieved')
            ->with(90.0, '<=', 80.0)
            ->andReturn(false);

        $suggestion = $this->service->generatePerformanceSuggestion(90.0, 80.0, '<=');
        $this->assertStringContainsString('melebihi batas maksimal', $suggestion);

        // Test = operator
        $this->mockCalculator
            ->shouldReceive('isTargetAchieved')
            ->with(75.0, '=', 80.0)
            ->andReturn(false);

        $suggestion = $this->service->generatePerformanceSuggestion(75.0, 80.0, '=');
        $this->assertStringContainsString('Selisih', $suggestion);

        // Test invalid operator
        $this->mockCalculator
            ->shouldReceive('isTargetAchieved')
            ->with(75.0, 'invalid', 80.0)
            ->andReturn(false);

        $suggestion = $this->service->generatePerformanceSuggestion(75.0, 80.0, 'invalid');
        $this->assertStringContainsString('Evaluasi manual', $suggestion);
    }
}
