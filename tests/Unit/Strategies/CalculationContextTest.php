<?php

use App\Strategies\CalculationContext;
use App\Strategies\Calculation\StandardCalculationStrategy;
use App\Strategies\Calculation\QualityIndicatorStrategy;
use App\Strategies\Calculation\SafetyIndicatorStrategy;

describe('CalculationContext', function () {
    it('can set and get strategy', function () {
        $context = new CalculationContext();
        $strategy = new QualityIndicatorStrategy();

        $context->setStrategy($strategy);

        expect($context->getStrategy())->toBe($strategy);
    });

    it('uses standard strategy by default', function () {
        $context = new CalculationContext();

        expect($context->getStrategy())->toBeInstanceOf(StandardCalculationStrategy::class);
    });

    it('can calculate percentage using context', function () {
        $context = new CalculationContext();

        $result = $context->calculatePercentage(75, 100);

        expect($result)->toBe(75.0);
    });

    it('can check target achievement using context', function () {
        $context = new CalculationContext();

        $achieved = $context->isTargetAchieved(85, 80, '>=');
        $notAchieved = $context->isTargetAchieved(75, 80, '>=');

        expect($achieved)->toBeTrue()
            ->and($notAchieved)->toBeFalse();
    });

    it('can create context for indicator type', function () {
        $safetyContext = CalculationContext::createForIndicatorType('safety');
        $qualityContext = CalculationContext::createForIndicatorType('quality');
        $standardContext = CalculationContext::createForIndicatorType('other');

        expect($safetyContext->getStrategy())->toBeInstanceOf(SafetyIndicatorStrategy::class)
            ->and($qualityContext->getStrategy())->toBeInstanceOf(QualityIndicatorStrategy::class)
            ->and($standardContext->getStrategy())->toBeInstanceOf(StandardCalculationStrategy::class);
    });

    it('can create context for category', function () {
        $safetyContext = CalculationContext::createForCategory('keselamatan pasien');
        $qualityContext = CalculationContext::createForCategory('mutu pelayanan');
        $standardContext = CalculationContext::createForCategory('other category');

        expect($safetyContext->getStrategy())->toBeInstanceOf(SafetyIndicatorStrategy::class)
            ->and($qualityContext->getStrategy())->toBeInstanceOf(QualityIndicatorStrategy::class)
            ->and($standardContext->getStrategy())->toBeInstanceOf(StandardCalculationStrategy::class);
    });

    it('returns available strategies', function () {
        $strategies = CalculationContext::getAvailableStrategies();

        expect($strategies)->toHaveKey('standard')
            ->and($strategies)->toHaveKey('quality')
            ->and($strategies)->toHaveKey('safety')
            ->and($strategies['standard'])->toBe(StandardCalculationStrategy::class);
    });
});
