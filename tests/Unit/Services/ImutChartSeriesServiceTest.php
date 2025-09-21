<?php

use App\Services\ImutChartSeriesService;
use App\Strategies\CalculationContext;
use App\Strategies\Calculation\StandardCalculationStrategy;
use App\Strategies\Calculation\QualityIndicatorStrategy;
use App\Strategies\Calculation\SafetyIndicatorStrategy;
use Illuminate\Support\Facades\Cache;

describe('ImutChartSeriesService with Strategy Pattern', function () {

    it('can instantiate service with calculation context', function () {
        $service = new ImutChartSeriesService();

        expect($service)->toBeInstanceOf(ImutChartSeriesService::class);
    });

    it('creates appropriate strategy context for different categories', function () {
        // Test standard category
        $standardContext = CalculationContext::createForCategory('administrasi');
        expect($standardContext->getStrategy())->toBeInstanceOf(StandardCalculationStrategy::class);

        // Test quality category
        $qualityContext = CalculationContext::createForCategory('mutu pelayanan');
        expect($qualityContext->getStrategy())->toBeInstanceOf(QualityIndicatorStrategy::class);

        // Test safety category
        $safetyContext = CalculationContext::createForCategory('keselamatan pasien');
        expect($safetyContext->getStrategy())->toBeInstanceOf(SafetyIndicatorStrategy::class);
    });

    it('maintains backward compatibility with existing functionality', function () {
        // Mock cache to avoid facade issues in unit test
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(['test_category']);

        $service = new ImutChartSeriesService();

        // Test basic methods still work
        expect($service->getDefaultColors())->toBeArray();
        expect($service->getCategories())->toBeArray();
    });

    it('strategy pattern integration works for different calculation types', function () {
        // Test standard calculation
        $standardContext = CalculationContext::createForCategory('default');
        $standardResult = $standardContext->calculatePercentage(80, 100);
        expect($standardResult)->toBe(80.0);

        // Test quality calculation (capped at 100%)
        $qualityContext = CalculationContext::createForCategory('mutu pelayanan');
        $qualityResult = $qualityContext->calculatePercentage(120, 100);
        expect($qualityResult)->toBe(100.0);

        // Test safety calculation
        $safetyContext = CalculationContext::createForCategory('keselamatan pasien');
        $safetyResult = $safetyContext->calculatePercentage(5, 100);
        expect($safetyResult)->toBe(5.0);
    });

    it('strategy pattern handles target achievement correctly', function () {
        // Standard: 80% achieves 75% target
        $standardContext = CalculationContext::createForCategory('default');
        expect($standardContext->isTargetAchieved(80, 75, '>='))->toBeTrue();

        // Quality: Must meet baseline 80% first
        $qualityContext = CalculationContext::createForCategory('mutu pelayanan');
        expect($qualityContext->isTargetAchieved(75, 70, '>='))->toBeFalse(); // Below 80% baseline
        expect($qualityContext->isTargetAchieved(85, 82, '>='))->toBeTrue(); // Above baseline and target

        // Safety: Lower is better by default
        $safetyContext = CalculationContext::createForCategory('keselamatan pasien');
        expect($safetyContext->isTargetAchieved(3, 5, '<='))->toBeTrue(); // 3 <= 5 is good for safety
    });
});
