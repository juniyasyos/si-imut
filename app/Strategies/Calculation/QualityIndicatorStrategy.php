<?php

namespace App\Strategies\Calculation;

use App\Strategies\CalculationStrategyInterface;

class QualityIndicatorStrategy implements CalculationStrategyInterface
{
    public function calculatePercentage(float $numerator, float $denominator): float
    {
        if ($denominator <= 0) {
            return 0.0;
        }

        // Quality indicators might need special handling for certain scenarios
        $percentage = ($numerator / $denominator) * 100;

        // Ensure percentage doesn't exceed 100% for quality indicators
        $percentage = min($percentage, 100);

        return round($percentage, 2);
    }

    public function isTargetAchieved(float $actual, float $target, string $operator): bool
    {
        // Quality indicators often have minimum thresholds
        $baseline = 80.0; // Minimum quality baseline

        // First check if meets baseline
        if ($actual < $baseline) {
            return false;
        }

        // Then check against target
        return match ($operator) {
            '>=' => $actual >= $target,
            '<=' => $actual <= $target,
            '>' => $actual > $target,
            '<' => $actual < $target,
            '=' => abs($actual - $target) < 0.01,
            default => false,
        };
    }

    public function getName(): string
    {
        return 'quality_indicator';
    }
}
