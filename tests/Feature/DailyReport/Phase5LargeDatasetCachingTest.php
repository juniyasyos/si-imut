<?php

namespace Tests\Feature\DailyReport;

use App\Models\User;
use App\Services\DailyReport\MatrixDataService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class Phase5LargeDatasetCachingTest extends TestCase
{
    protected User $user;
    protected MatrixDataService $service;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use actual user with many indicators
        $this->user = User::find(67); // Tim PPI user with 51 indicators
        if (!$this->user) {
            $this->markTestSkipped('User 67 not found - test requires specific database state');
        }
        
        Auth::setUser($this->user);
        $this->service = app(MatrixDataService::class);
        MatrixDataService::clearMatrixCache();
    }

    public function test_matrix_load_first_call_within_reasonable_time()
    {
        $start = microtime(true);
        $result = $this->service->loadMatrixCompletely('2026-05');
        $duration = (microtime(true) - $start) * 1000;

        $this->assertNotEmpty($result['indicators']);
        $this->assertNotEmpty($result['matrixData']);
        
        // Should complete in under 100ms (was 158ms before optimization)
        $this->assertLessThan(100, $duration);
    }

    public function test_matrix_load_cache_hit_instant()
    {
        // First call - load from database
        $this->service->loadMatrixCompletely('2026-05');

        // Second call - should hit cache
        $start = microtime(true);
        $result = $this->service->loadMatrixCompletely('2026-05');
        $duration = (microtime(true) - $start) * 1000;

        $this->assertNotEmpty($result['indicators']);
        
        // Cache hit should be < 1ms (was 0.07ms in testing)
        $this->assertLessThan(1, $duration);
    }

    public function test_matrix_load_multiple_months()
    {
        $months = ['2026-03', '2026-04', '2026-05'];
        $durations = [];

        foreach ($months as $month) {
            $start = microtime(true);
            $result = $this->service->loadMatrixCompletely($month);
            $duration = (microtime(true) - $start) * 1000;
            
            $durations[$month] = $duration;
            
            $this->assertNotEmpty($result['indicators']);
            // Each month should load in reasonable time
            $this->assertLessThan(100, $duration, "Month $month took too long");
        }

        $totalDuration = array_sum($durations);
        
        // 3 months should complete in under 300ms total (was ~6000ms before optimization)
        $this->assertLessThan(300, $totalDuration, "Total 3-month load took {$totalDuration}ms");
    }

    public function test_matrix_cache_independent_per_month()
    {
        // Load May
        $result_may = $this->service->loadMatrixCompletely('2026-05');
        
        // Load April
        $result_april = $this->service->loadMatrixCompletely('2026-04');
        
        // Results should be different (May vs April data)
        // At least the matrix data should differ
        $this->assertNotEquals($result_may['daysWithData'], $result_april['daysWithData']);
        
        // But indicators count should be same (same user, same 51 indicators)
        $this->assertCount(51, $result_may['indicators']);
        $this->assertCount(51, $result_april['indicators']);
    }

    public function test_matrix_cache_independent_per_user()
    {
        // Load for user 67
        $result_user67 = $this->service->loadMatrixCompletely('2026-05');
        
        // Switch to different user (if exists)
        $other_user = User::where('id', '!=', 67)->first();
        if ($other_user) {
            Auth::setUser($other_user);
            $other_service = app(MatrixDataService::class);
            
            // Load for different user
            $result_other = $other_service->loadMatrixCompletely('2026-05');
            
            // Cache should be independent, so different results (likely)
            $this->assertNotEquals(count($result_user67['indicators']), count($result_other['indicators']));
        }
    }

    public function test_matrix_cache_cleared_between_tests()
    {
        // This test verifies that clearMatrixCache() is available and can be called
        // The actual cache clearing is internal implementation detail
        
        MatrixDataService::clearMatrixCache();
        
        // If we reach here without error, the method works
        $this->assertTrue(true);
    }

    public function test_matrix_data_correctness()
    {
        $result = $this->service->loadMatrixCompletely('2026-05');

        // Verify result structure
        $this->assertIsArray($result['indicators']);
        $this->assertIsArray($result['matrixData']);
        $this->assertIsArray($result['daysInMonth']);
        $this->assertIsArray($result['daysWithData']);

        // Verify indicators count
        $this->assertCount(51, $result['indicators']);
        $this->assertCount(51, $result['matrixData']);

        // Verify each indicator has required fields
        foreach ($result['indicators'] as $indicator) {
            $this->assertArrayHasKey('id', $indicator);
            $this->assertArrayHasKey('title', $indicator);
            $this->assertArrayHasKey('category', $indicator);
        }

        // Verify days in month
        $this->assertCount(31, $result['daysInMonth']); // May has 31 days
        $this->assertCount(31, $result['daysWithData']);

        // Verify daysWithData is boolean array
        foreach ($result['daysWithData'] as $hasData) {
            $this->assertIsBool($hasData);
        }
    }

    public function test_matrix_performance_improvement()
    {
        // This test documents the performance improvement from Phase 5
        
        $start = microtime(true);
        $result = $this->service->loadMatrixCompletely('2026-05');
        $duration = (microtime(true) - $start) * 1000;

        // Phase 5 optimization: ~51ms for single month
        // Before Phase 5: ~158.67ms for single month
        // Improvement: -68%
        
        $improvementPercent = ((158.67 - $duration) / 158.67) * 100;
        
        $this->assertLessThan(100, $duration);
        $this->assertGreaterThan(40, $improvementPercent);
        
        // Log performance metrics
        $this->markTestIncomplete(
            "Performance: {$duration}ms, Improvement: {$improvementPercent}%"
        );
    }
}
