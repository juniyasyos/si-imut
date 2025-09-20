<?php

namespace App\Strategies\Calculation;

use App\Strategies\CalculationStrategyInterface;

class StandardCalculationStrategy implements CalculationStrategyInterface
{
    public function calculatePercentage(float $numerator, float $denominator): float
    {
        if ($denominator <= 0) {
            return 0.0;
        }

        $percentage = ($numerator / $denominator) * 100;

        return round($percentage, 2);
    }

    public function isTargetAchieved(float $actual, float $target, string $operator): bool
    {
        return match ($operator) {
            '>=' => $actual >= $target,
            '<=' => $actual <= $target,
            '>' => $actual > $target,
            '<' => $actual < $target,
            '=' => abs($actual - $target) < 0.01, // Allow small floating point differences
            default => false,
        };
    }

    public function getName(): string
    {
        return 'standard';
    }
}
