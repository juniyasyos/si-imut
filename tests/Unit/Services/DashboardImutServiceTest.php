<?php

use App\Services\DashboardImutService;
use App\Strategies\CalculationContext;
use Illuminate\Support\Facades\Cache;

describe('DashboardImutService with Strategy Pattern', function () {

    beforeEach(function () {
        // Mock cache for dependencies
        Cache::shouldReceive('remember')->andReturn([]);
        Cache::shouldReceive('get')->andReturn(null);
        Cache::shouldReceive('put')->andReturn(true);
    });

    it('can instantiate service with calculation context', function () {
        $service = new DashboardImutService();

        expect($service)->toBeInstanceOf(DashboardImutService::class);
    });

    it('uses strategy pattern for percentage calculations in resolveIcon', function () {
        $service = new DashboardImutService();

        // Use reflection to test protected method
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('resolveIcon');
        $method->setAccessible(true);

        // Test high achievement (>= 80%)
        $highIcon = $method->invokeArgs($service, [80, 100]);
        expect($highIcon)->toBe('heroicon-o-check-circle');

        // Test low achievement (< 80%)
        $lowIcon = $method->invokeArgs($service, [50, 100]);
        expect($lowIcon)->toBe('heroicon-o-adjustments-vertical');
    });

    it('uses strategy pattern for percentage calculations in resolvePercentageColor', function () {
        $service = new DashboardImutService();

        // Use reflection to test protected method
        $reflection = new ReflectionClass($service);
        $method = $reflection->getMethod('resolvePercentageColor');
        $method->setAccessible(true);

        // Test success color (>= 80%)
        $successColor = $method->invokeArgs($service, [85, 100]);
        expect($successColor)->toBe('success');

        // Test warning color (>= 50% but < 80%)
        $warningColor = $method->invokeArgs($service, [65, 100]);
        expect($warningColor)->toBe('warning');

        // Test danger color (< 50%)
        $dangerColor = $method->invokeArgs($service, [30, 100]);
        expect($dangerColor)->toBe('danger');
    });

    it('handles edge cases properly', function () {
        $service = new DashboardImutService();
        $reflection = new ReflectionClass($service);

        // Test with zero total
        $iconMethod = $reflection->getMethod('resolveIcon');
        $iconMethod->setAccessible(true);

        $iconResult = $iconMethod->invokeArgs($service, [0, 0]);
        expect($iconResult)->toBe('heroicon-o-adjustments-vertical');

        // Test color with zero total
        $colorMethod = $reflection->getMethod('resolvePercentageColor');
        $colorMethod->setAccessible(true);

        $colorResult = $colorMethod->invokeArgs($service, [0, 0]);
        expect($colorResult)->toBe('danger');
    });

    it('strategy pattern maintains consistent percentage calculation', function () {
        $context = new CalculationContext();

        // Test that Strategy Pattern gives same results as before
        expect($context->calculatePercentage(80, 100))->toBe(80.0);
        expect($context->calculatePercentage(50, 100))->toBe(50.0);
        expect($context->calculatePercentage(0, 0))->toBe(0.0);
        expect($context->calculatePercentage(120, 100))->toBe(120.0); // Standard allows over 100%
    });
});
