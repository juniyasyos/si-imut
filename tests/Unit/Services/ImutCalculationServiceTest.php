<?php

namespace Tests\Unit\Services;

use App\Services\ImutCalculationService;
use Tests\TestCase;

class ImutCalculationServiceTest extends TestCase
{
    public function test_percentage_calculation()
    {
        // Test normal calculation
        $this->assertEquals(50.0, ImutCalculationService::calculatePercentage(50, 100));
        $this->assertEquals(75.0, ImutCalculationService::calculatePercentage(75, 100));
        $this->assertEquals(100.0, ImutCalculationService::calculatePercentage(100, 100));

        // Test with decimals
        $this->assertEquals(66.67, ImutCalculationService::calculatePercentage(2, 3));

        // Test edge cases
        $this->assertEquals(0.0, ImutCalculationService::calculatePercentage(0, 100));
        $this->assertEquals(0.0, ImutCalculationService::calculatePercentage(null, 100));
        $this->assertEquals(0.0, ImutCalculationService::calculatePercentage(50, 0));
        $this->assertEquals(0.0, ImutCalculationService::calculatePercentage(50, null));
    }

    public function test_meets_standard()
    {
        // Test equals operator
        $this->assertTrue(ImutCalculationService::meetsStandard(80.0, 80.0, '='));
        $this->assertFalse(ImutCalculationService::meetsStandard(79.99, 80.0, '='));

        // Test greater than or equal
        $this->assertTrue(ImutCalculationService::meetsStandard(80.0, 80.0, '>='));
        $this->assertTrue(ImutCalculationService::meetsStandard(81.0, 80.0, '>='));
        $this->assertFalse(ImutCalculationService::meetsStandard(79.0, 80.0, '>='));

        // Test less than or equal
        $this->assertTrue(ImutCalculationService::meetsStandard(80.0, 80.0, '<='));
        $this->assertTrue(ImutCalculationService::meetsStandard(79.0, 80.0, '<='));
        $this->assertFalse(ImutCalculationService::meetsStandard(81.0, 80.0, '<='));

        // Test greater than
        $this->assertTrue(ImutCalculationService::meetsStandard(81.0, 80.0, '>'));
        $this->assertFalse(ImutCalculationService::meetsStandard(80.0, 80.0, '>'));

        // Test less than
        $this->assertTrue(ImutCalculationService::meetsStandard(79.0, 80.0, '<'));
        $this->assertFalse(ImutCalculationService::meetsStandard(80.0, 80.0, '<'));
    }

    public function test_percentage_expression_generation()
    {
        $expression = ImutCalculationService::percentageExpression('numerator', 'denominator');

        $this->assertStringContainsString('ROUND', $expression);
        $this->assertStringContainsString('numerator', $expression);
        $this->assertStringContainsString('denominator', $expression);
        $this->assertStringContainsString('percentage', $expression);
        $this->assertStringContainsString('100.0', $expression);
    }

    public function test_filled_count_expression_generation()
    {
        $expression = ImutCalculationService::filledCountExpression();

        $this->assertStringContainsString('SUM', $expression);
        $this->assertStringContainsString('CASE', $expression);
        $this->assertStringContainsString('numerator_value', $expression);
        $this->assertStringContainsString('denominator_value', $expression);
        $this->assertStringContainsString('filled_count', $expression);
    }

    public function test_sum_expression_generation()
    {
        $expression = ImutCalculationService::sumExpression('test_column', 'test_alias');

        $this->assertStringContainsString('COALESCE', $expression);
        $this->assertStringContainsString('SUM(test_column)', $expression);
        $this->assertStringContainsString('test_alias', $expression);
    }
}
