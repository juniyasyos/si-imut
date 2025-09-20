<?php

namespace App\Strategies\Calculation;

use App\Strategies\CalculationStrategyInterface;

class SafetyIndicatorStrategy implements CalculationStrategyInterface
{
    public function calculatePercentage(float $numerator, float $denominator): float
    {
        if ($denominator <= 0) {
            return 0.0;
        }

        // Safety indicators often use inverse calculation (fewer is better)
        // For example: infection rate, medication errors, falls
        $percentage = ($numerator / $denominator) * 100;

        return round($percentage, 2);
    }

    public function isTargetAchieved(float $actual, float $target, string $operator): bool
    {
        // Safety indicators typically want lower values
        // Default operator for safety should be '<=' (actual should be less than or equal to target)

        return match ($operator) {
            '>=' => $actual >= $target,
            '<=' => $actual <= $target,
            '>' => $actual > $target,
            '<' => $actual < $target,
            '=' => abs($actual - $target) < 0.01,
            default => $actual <= $target, // Default for safety: lower is better
        };
    }

    public function getName(): string
    {
        return 'safety_indicator';
    }
}
