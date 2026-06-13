<?php

namespace Tests\Unit\Services\Calculator;

use App\Services\Core\ImutCalculatorService;
use PHPUnit\Framework\TestCase;

class ImutCalculatorServiceTest extends TestCase
{
    private ImutCalculatorService $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new ImutCalculatorService();
    }

    /** @test */
    public function it_calculates_percentage_correctly()
    {
        // Test normal calculation
        $result = $this->calculator->calculatePercentage(75, 100);
        $this->assertEquals(75.0, $result);

        // Test with decimals
        $result = $this->calculator->calculatePercentage(33, 100);
        $this->assertEquals(33.0, $result);

        // Test rounding
        $result = $this->calculator->calculatePercentage(1, 3);
        $this->assertEquals(33.34, $result);
    }

    /** @test */
    public function it_handles_zero_denominator()
    {
        $result = $this->calculator->calculatePercentage(10, 0);
        $this->assertEquals(0.0, $result);
    }

    /** @test */
    public function it_evaluates_target_achievement_correctly()
    {
        // Test >= operator
        $this->assertTrue($this->calculator->isTargetAchieved(80, '>=', 75));
        $this->assertTrue($this->calculator->isTargetAchieved(75, '>=', 75));
        $this->assertFalse($this->calculator->isTargetAchieved(70, '>=', 75));

        // Test <= operator
        $this->assertTrue($this->calculator->isTargetAchieved(5, '<=', 10));
        $this->assertTrue($this->calculator->isTargetAchieved(10, '<=', 10));
        $this->assertFalse($this->calculator->isTargetAchieved(15, '<=', 10));

        // Test = operator
        $this->assertTrue($this->calculator->isTargetAchieved(100, '=', 100));
        $this->assertFalse($this->calculator->isTargetAchieved(99, '=', 100));

        // Test > operator
        $this->assertTrue($this->calculator->isTargetAchieved(85, '>', 80));
        $this->assertFalse($this->calculator->isTargetAchieved(80, '>', 80));

        // Test < operator
        $this->assertTrue($this->calculator->isTargetAchieved(5, '<', 10));
        $this->assertFalse($this->calculator->isTargetAchieved(10, '<', 10));

        // Test invalid operator
        $this->assertFalse($this->calculator->isTargetAchieved(80, 'invalid', 75));
    }

    /** @test */
    public function it_calculates_imut_result()
    {
        // Valid calculation
        $result = $this->calculator->calculateImutResult(80, 100);
        $expected = [
            'percentage' => 80.0,
            'is_valid' => true
        ];
        $this->assertEquals($expected, $result);

        // Invalid calculation (zero denominator)
        $result = $this->calculator->calculateImutResult(80, 0);
        $expected = [
            'percentage' => 0.0,
            'is_valid' => false
        ];
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_evaluates_penilaian_comprehensively()
    {
        // Achieved target
        $result = $this->calculator->evaluatePenilaian(80, 100, 75, '>=');
        $expected = [
            'percentage' => 80.0,
            'is_achieved' => true,
            'is_valid' => true
        ];
        $this->assertEquals($expected, $result);

        // Not achieved target
        $result = $this->calculator->evaluatePenilaian(70, 100, 75, '>=');
        $expected = [
            'percentage' => 70.0,
            'is_achieved' => false,
            'is_valid' => true
        ];
        $this->assertEquals($expected, $result);

        // Invalid calculation
        $result = $this->calculator->evaluatePenilaian(80, 0, 75, '>=');
        $expected = [
            'percentage' => 0.0,
            'is_achieved' => false,
            'is_valid' => false
        ];
        $this->assertEquals($expected, $result);
    }

    /** @test */
    public function it_processes_batch_evaluations()
    {
        $penilaians = [
            ['numerator' => 80, 'denominator' => 100, 'target' => 75, 'operator' => '>='],
            ['numerator' => 60, 'denominator' => 100, 'target' => 75, 'operator' => '>='],
            ['numerator' => 90, 'denominator' => 100, 'target' => 85, 'operator' => '>='],
        ];

        $results = $this->calculator->batchEvaluatePenilaian($penilaians);

        $this->assertCount(3, $results);
        $this->assertTrue($results[0]['is_achieved']); // 80 >= 75
        $this->assertFalse($results[1]['is_achieved']); // 60 < 75
        $this->assertTrue($results[2]['is_achieved']); // 90 >= 85
    }

    /** @test */
    public function it_calculates_achievement_statistics()
    {
        $evaluationResults = [
            ['is_achieved' => true],
            ['is_achieved' => false],
            ['is_achieved' => true],
            ['is_achieved' => true],
        ];

        $stats = $this->calculator->calculateAchievementStats($evaluationResults);

        $expected = [
            'total' => 4,
            'achieved' => 3,
            'percentage_achieved' => 75.0
        ];

        $this->assertEquals($expected, $stats);
    }

    /** @test */
    public function it_handles_empty_evaluation_results()
    {
        $stats = $this->calculator->calculateAchievementStats([]);

        $expected = [
            'total' => 0,
            'achieved' => 0,
            'percentage_achieved' => 0.0
        ];

        $this->assertEquals($expected, $stats);
    }

    /** @test */
    public function it_handles_edge_cases_in_percentage_calculation()
    {
        // Very small numbers
        $result = $this->calculator->calculatePercentage(0.001, 0.1);
        $this->assertEquals(1.0, $result);

        // Very large numbers
        $result = $this->calculator->calculatePercentage(1000000, 1000000);
        $this->assertEquals(100.0, $result);

        // Negative numbers
        $result = $this->calculator->calculatePercentage(-10, 100);
        $this->assertEquals(-10.0, $result);
    }

    /** @test */
    public function it_respects_precision_parameter()
    {
        // Default precision (2)
        $result = $this->calculator->calculatePercentage(1, 3);
        $this->assertEquals(33.34, $result);

        // Custom precision (4)
        $result = $this->calculator->calculatePercentage(1, 3, 4);
        $this->assertEquals(33.3334, $result);

        // Zero precision
        $result = $this->calculator->calculatePercentage(1, 3, 0);
        $this->assertEquals(34.0, $result);
    }
}
