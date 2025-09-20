<?php

use App\Strategies\Calculation\StandardCalculationStrategy;
use App\Strategies\Calculation\QualityIndicatorStrategy;
use App\Strategies\Calculation\SafetyIndicatorStrategy;

describe('StandardCalculationStrategy', function () {
    beforeEach(function () {
        $this->strategy = new StandardCalculationStrategy();
    });

    it('calculates percentage correctly', function () {
        expect($this->strategy->calculatePercentage(75, 100))->toBe(75.0)
            ->and($this->strategy->calculatePercentage(80, 80))->toBe(100.0)
            ->and($this->strategy->calculatePercentage(33.33, 100))->toBe(33.33);
    });

    it('returns zero for invalid denominator', function () {
        expect($this->strategy->calculatePercentage(50, 0))->toBe(0.0)
            ->and($this->strategy->calculatePercentage(50, -10))->toBe(0.0);
    });

    it('rounds percentage to 2 decimal places', function () {
        expect($this->strategy->calculatePercentage(33.333333, 100))->toBe(33.33);
    });

    it('evaluates target achievement correctly', function () {
        expect($this->strategy->isTargetAchieved(85, 80, '>='))->toBeTrue()
            ->and($this->strategy->isTargetAchieved(75, 80, '>='))->toBeFalse()
            ->and($this->strategy->isTargetAchieved(75, 80, '<='))->toBeTrue()
            ->and($this->strategy->isTargetAchieved(85, 80, '<='))->toBeFalse()
            ->and($this->strategy->isTargetAchieved(80, 80, '='))->toBeTrue()
            ->and($this->strategy->isTargetAchieved(80.005, 80, '='))->toBeTrue() // Within tolerance
            ->and($this->strategy->isTargetAchieved(81, 80, '='))->toBeFalse();
    });

    it('returns false for unknown operator', function () {
        expect($this->strategy->isTargetAchieved(85, 80, 'unknown'))->toBeFalse();
    });

    it('has correct strategy name', function () {
        expect($this->strategy->getName())->toBe('standard');
    });
});

describe('QualityIndicatorStrategy', function () {
    beforeEach(function () {
        $this->strategy = new QualityIndicatorStrategy();
    });

    it('caps percentage at 100%', function () {
        expect($this->strategy->calculatePercentage(150, 100))->toBe(100.0)
            ->and($this->strategy->calculatePercentage(80, 100))->toBe(80.0);
    });

    it('enforces baseline quality threshold', function () {
        // Below baseline (80%) should fail regardless of target
        expect($this->strategy->isTargetAchieved(75, 70, '>='))->toBeFalse()
            ->and($this->strategy->isTargetAchieved(85, 80, '>='))->toBeTrue();
    });

    it('has correct strategy name', function () {
        expect($this->strategy->getName())->toBe('quality_indicator');
    });
});

describe('SafetyIndicatorStrategy', function () {
    beforeEach(function () {
        $this->strategy = new SafetyIndicatorStrategy();
    });

    it('calculates percentage normally', function () {
        expect($this->strategy->calculatePercentage(5, 100))->toBe(5.0)
            ->and($this->strategy->calculatePercentage(2.5, 100))->toBe(2.5);
    });

    it('defaults to lower-is-better for unknown operators', function () {
        expect($this->strategy->isTargetAchieved(3, 5, 'unknown'))->toBeTrue() // 3 <= 5
            ->and($this->strategy->isTargetAchieved(7, 5, 'unknown'))->toBeFalse(); // 7 > 5
    });

    it('evaluates safety targets correctly', function () {
        // For safety, lower values are typically better
        expect($this->strategy->isTargetAchieved(3, 5, '<='))->toBeTrue()
            ->and($this->strategy->isTargetAchieved(7, 5, '<='))->toBeFalse()
            ->and($this->strategy->isTargetAchieved(3, 5, '<'))->toBeTrue()
            ->and($this->strategy->isTargetAchieved(5, 5, '<'))->toBeFalse();
    });

    it('has correct strategy name', function () {
        expect($this->strategy->getName())->toBe('safety_indicator');
    });
});
