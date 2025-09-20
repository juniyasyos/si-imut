<?php

use App\Services\LaporanImut\LaporanImutCalculationService;

describe('LaporanImutCalculationService Strategy Integration', function () {

    it('can use strategy pattern for percentage calculation', function () {
        $service = new LaporanImutCalculationService();

        // Test standard calculation
        $result = $service->calculateAchievementPercentageWithStrategy(75, 100, 'standard');
        expect($result)->toBe(75.0);

        // Test quality calculation (capped at 100%)
        $result = $service->calculateAchievementPercentageWithStrategy(120, 100, 'quality');
        expect($result)->toBe(100.0);

        // Test safety calculation
        $result = $service->calculateAchievementPercentageWithStrategy(50, 100, 'safety');
        expect($result)->toBe(50.0);
    });

    it('maintains backward compatibility with legacy method', function () {
        $service = new LaporanImutCalculationService();

        $legacyResult = $service->calculateAchievementPercentage(75, 100);
        $strategyResult = $service->calculateAchievementPercentageWithStrategy(75, 100, 'standard');

        expect($legacyResult)->toBe($strategyResult);
    });

    it('can set calculation strategy and reuse context', function () {
        $service = new LaporanImutCalculationService();

        $service->setCalculationStrategy('quality');

        // Should use quality strategy (capped at 100%)
        $result1 = $service->calculateAchievementPercentageWithStrategy(120, 100);
        expect($result1)->toBe(100.0);

        // Should still use quality strategy if no type specified
        $result2 = $service->calculateAchievementPercentageWithStrategy(110, 100);
        expect($result2)->toBe(100.0);
    });
});
