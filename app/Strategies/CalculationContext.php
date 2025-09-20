<?php

namespace App\Strategies;

use App\Strategies\Calculation\StandardCalculationStrategy;
use App\Strategies\Calculation\QualityIndicatorStrategy;
use App\Strategies\Calculation\SafetyIndicatorStrategy;

class CalculationContext
{
    private CalculationStrategyInterface $strategy;

    public function __construct(?CalculationStrategyInterface $strategy = null)
    {
        $this->strategy = $strategy ?? new StandardCalculationStrategy();
    }

    /**
     * Set calculation strategy
     */
    public function setStrategy(CalculationStrategyInterface $strategy): void
    {
        $this->strategy = $strategy;
    }

    /**
     * Get current strategy
     */
    public function getStrategy(): CalculationStrategyInterface
    {
        return $this->strategy;
    }

    /**
     * Calculate percentage using current strategy
     */
    public function calculatePercentage(float $numerator, float $denominator): float
    {
        return $this->strategy->calculatePercentage($numerator, $denominator);
    }

    /**
     * Check if target is achieved using current strategy
     */
    public function isTargetAchieved(float $actual, float $target, string $operator): bool
    {
        return $this->strategy->isTargetAchieved($actual, $target, $operator);
    }

    /**
     * Create strategy based on indicator type
     */
    public static function createForIndicatorType(string $type): self
    {
        $strategy = match (strtolower($type)) {
            'safety', 'keselamatan' => new SafetyIndicatorStrategy(),
            'quality', 'mutu', 'kualitas' => new QualityIndicatorStrategy(),
            default => new StandardCalculationStrategy(),
        };

        return new self($strategy);
    }

    /**
     * Create strategy based on category
     */
    public static function createForCategory(string $category): self
    {
        $strategy = match (strtolower($category)) {
            'keselamatan pasien', 'patient safety' => new SafetyIndicatorStrategy(),
            'mutu pelayanan', 'service quality' => new QualityIndicatorStrategy(),
            default => new StandardCalculationStrategy(),
        };

        return new self($strategy);
    }

    /**
     * Get available strategies
     */
    public static function getAvailableStrategies(): array
    {
        return [
            'standard' => StandardCalculationStrategy::class,
            'quality' => QualityIndicatorStrategy::class,
            'safety' => SafetyIndicatorStrategy::class,
        ];
    }
}
