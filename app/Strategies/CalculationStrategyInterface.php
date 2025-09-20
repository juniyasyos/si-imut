<?php

namespace App\Strategies;

interface CalculationStrategyInterface
{
    /**
     * Calculate achievement percentage
     *
     * @param float $numerator
     * @param float $denominator
     * @return float
     */
    public function calculatePercentage(float $numerator, float $denominator): float;

    /**
     * Determine if target is achieved
     *
     * @param float $actual
     * @param float $target
     * @param string $operator
     * @return bool
     */
    public function isTargetAchieved(float $actual, float $target, string $operator): bool;

    /**
     * Get strategy name
     *
     * @return string
     */
    public function getName(): string;
}
