<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DailyReport\MatrixDataService;
use App\Services\UserContextService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProfileMonthNavigation extends Command
{
    protected $signature = 'profile:month-navigation {--user-id= : User ID to profile} {--months=3 : Number of months to test}';
    protected $description = 'Profile month navigation performance to identify bottlenecks';

    public function handle(): int
    {
        $userId = $this->option('user-id') ?? 1;
        $monthsToTest = (int) $this->option('months');

        // Find user
        $user = \App\Models\User::find($userId);
        if (!$user) {
            $this->error("User {$userId} not found");
            return 1;
        }

        $this->info("📊 Profiling month navigation for user: {$user->name} (ID: {$user->id})");
        $this->line("═══════════════════════════════════════════════════════════════");

        Auth::setUser($user);

        // Get user's unit kerja and indicators count
        $unitKerjaIds = UserContextService::getUserUnitKerjaIdsForUserId($user->id);
        $this->info("👤 User Unit Kerja IDs: " . implode(', ', $unitKerjaIds));

        $indicatorCount = \App\Models\FormTemplate::forUserUnitKerjas($unitKerjaIds)
            ->monthlyIndicators()
            ->activeForCurrentDate()
            ->distinct()
            ->count();
        
        $this->info("📋 Indicators count: {$indicatorCount}");

        // Get report count for this user
        $reportCount = \App\Models\DailyReportResponse::whereIn('unit_kerja_id', $unitKerjaIds)
            ->whereMonth('report_date', '>=', now()->month - 3)
            ->count();
        
        $this->info("📊 Total reports (last 3 months): {$reportCount}");

        $this->line("");
        $this->info("🔄 Testing month navigation queries...");
        $this->line("───────────────────────────────────────────────────────────────");

        $service = new MatrixDataService();
        $months = [];
        $totalQueryTime = 0;
        $totalQueries = 0;

        // Generate test months
        for ($i = 0; $i < $monthsToTest; $i++) {
            $months[] = now()->subMonths($i)->format('Y-m');
        }

        $this->table(
            ['Month', 'Queries', 'Time (ms)', 'Avg/Query'],
            array_map(function ($month) use ($service, &$totalQueryTime, &$totalQueries) {
                // Reset cache to simulate fresh request
                MatrixDataService::clearMatrixCache();

                DB::flushQueryLog();
                DB::enableQueryLog();

                $start = microtime(true);
                $result = $service->loadMatrixCompletely($month);
                $duration = (microtime(true) - $start) * 1000;

                $queries = DB::getQueryLog();
                $queryCount = count($queries);
                $avgTime = $queryCount > 0 ? $duration / $queryCount : 0;

                $totalQueryTime += $duration;
                $totalQueries += $queryCount;

                return [
                    $month,
                    $queryCount,
                    round($duration, 2) . 'ms',
                    round($avgTime, 2) . 'ms'
                ];
            }, $months)
        );

        $this->line("");
        $this->info("📈 Summary:");
        $this->line("  Total queries: {$totalQueries}");
        $this->line("  Total time: " . round($totalQueryTime, 2) . "ms");
        $this->line("  Average per month: " . round($totalQueryTime / $monthsToTest, 2) . "ms");

        // Show query details for first month
        $this->line("");
        $this->info("🔍 Query details for month " . $months[0] . ":");
        $this->line("───────────────────────────────────────────────────────────────");

        MatrixDataService::clearMatrixCache();
        DB::flushQueryLog();
        DB::enableQueryLog();

        $service->loadMatrixCompletely($months[0]);

        $queries = DB::getQueryLog();
        foreach ($queries as $i => $query) {
            $this->line("Query " . ($i + 1) . ": " . round($query['time'], 2) . "ms");
            $this->line("  " . substr($query['query'], 0, 120) . "...");
            
            if (!empty($query['bindings'])) {
                $this->line("  Bindings: " . json_encode($query['bindings']));
            }
        }

        $this->line("");
        $this->info("✅ Profile complete");

        return 0;
    }
}
