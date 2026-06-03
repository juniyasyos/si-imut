<?php

namespace App\Console\Commands;

use App\Services\DailyReport\MatrixDataService;
use App\Services\DailyReport\SlideOverService;
use App\Services\DailyReport\DailyReportMonitoringService;
use App\Services\DailyReport\TableViewService;
use App\Services\UserContextService;
use App\Services\FormTemplateLoadingService;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BenchmarkDailyReportServices extends Command
{
    protected $signature = 'benchmark:daily-report {--user-id=1} {--month=}';

    protected $description = 'Benchmark Daily Report services to identify N+1 and redundant queries';

    private int $queryCount = 0;
    private array $queries = [];
    private float $startTime = 0;

    public function handle(): int
    {
        $userId = (int)$this->option('user-id');
        $month = $this->option('month') ?? now()->format('Y-m');
        $verbose = (bool)$this->option('verbose');

        $user = User::find($userId);
        if (!$user) {
            $this->error("User {$userId} not found");
            return 1;
        }

        Auth::setUser($user);

        $this->info('═══════════════════════════════════════════════════════════════');
        $this->info('  DAILY REPORT SERVICES BENCHMARK');
        $this->info('═══════════════════════════════════════════════════════════════');
        $this->line("User: {$user->name} (ID: {$user->id})");
        $this->line("Month: {$month}");
        $this->line('');

        // Setup query monitoring
        DB::listen(function ($query) use ($verbose) {
            $this->queryCount++;
            $this->queries[] = [
                'sql' => $query->sql,
                'bindings' => $query->bindings,
                'time' => $query->time,
            ];

            if ($verbose) {
                $this->line("  [Q{$this->queryCount}] {$query->sql} ({$query->time}ms)");
            }
        });

        $this->benchmark();

        return 0;
    }

    private function benchmark(): void
    {
        $this->line('');
        $this->info('🔴 SCENARIO 1: Load Matrix Data (Current Implementation)');
        $this->line('─────────────────────────────────────────────────────────');
        
        $this->queryCount = 0;
        $this->queries = [];
        $this->startTime = microtime(true);

        $matrixResult = [];
        $indicatorData = [];

        try {
            $service = app(MatrixDataService::class);
            $matrixResult = $service->loadMatrixCompletely(now()->format('Y-m'));
            $indicatorData = $matrixResult['indicators'] ?? [];
            
            $duration = (microtime(true) - $this->startTime) * 1000;
            
            $this->line("Queries: {$this->queryCount}");
            $this->line("Duration: " . number_format($duration, 2) . "ms");
            $this->line("Indicators: " . count($matrixResult['indicators']));
            $this->line("Matrix rows: " . count($matrixResult['matrixData']));
            
            if ($this->option('verbose')) {
                $this->printQueryDetails();
            } else {
                $this->printQuerySummary();
            }
        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
        }

        // ─────────────────────────────────────

        $this->line('');
        $this->info('🟠 SCENARIO 2: Slide Over Service (Load Single Date Reports)');
        $this->line('─────────────────────────────────────────────────────────');

        // Use data from Scenario 1 (don't query again)
        if (!empty($indicatorData)) {
            $this->queryCount = 0;
            $this->queries = [];
            $this->startTime = microtime(true);

            try {
                $service = app(SlideOverService::class);
                $indicator = reset($indicatorData);  // Get first indicator from Scenario 1
                
                if ($indicator) {
                    $indicatorId = $indicator['id'];
                    $date = now()->format('Y-m-d');
                    
                    $result = $service->loadDailyReports($indicatorId, $date);
                    
                    $duration = (microtime(true) - $this->startTime) * 1000;
                    
                    $this->line("Indicator: {$indicatorId}");
                    $this->line("Date: {$date}");
                    $this->line("Queries: {$this->queryCount}");
                    $this->line("Duration: " . number_format($duration, 2) . "ms");
                    $this->line("Reports loaded: " . count($result));
                    
                    if ($this->option('verbose')) {
                        $this->printQueryDetails();
                    } else {
                        $this->printQuerySummary();
                    }
                } else {
                    $this->warn("No indicators found for this user");
                }
            } catch (\Exception $e) {
                $this->error("Error: {$e->getMessage()}");
            }
        }

        // ─────────────────────────────────────

        $this->line('');
        $this->info('🔵 SCENARIO 3: Sequential Calls (Simulating Real User Flow)');
        $this->line('─────────────────────────────────────────────────────────');
        $this->line('Calling: Matrix Load + Slide Over + Monitoring Check');

        $this->queryCount = 0;
        $this->queries = [];
        $this->startTime = microtime(true);

        try {
            // REUSE Matrix data from Scenario 1 (no additional query)
            if (!empty($matrixResult)) {
                // Call 1: Use Matrix data
                // (already loaded in Scenario 1)
                
                // Call 2: Slide Over (using indicator from Scenario 1, no extra query)
                $slideOverService = app(SlideOverService::class);
                if (!empty($indicatorData)) {
                    $firstIndicator = reset($indicatorData);
                    $slideOverService->loadDailyReports($firstIndicator['id'], now()->format('Y-m-d'));
                }

                // Call 3: Monitoring count check (using indicator from Scenario 1, no extra query)
                $monitoringService = app(DailyReportMonitoringService::class);
                $user = Auth::user();
                if (!empty($indicatorData)) {
                    $firstIndicator = reset($indicatorData);
                    $count = $monitoringService->getReportCountForIndicatorDate(
                        $firstIndicator['id'],
                        now()->format('Y-m-d')
                    );
                }
            }

            $duration = (microtime(true) - $this->startTime) * 1000;

            $this->line("Total Queries: {$this->queryCount}");
            $this->line("Total Duration: " . number_format($duration, 2) . "ms");
            $this->line("Avg per call: " . number_format($duration / 3, 2) . "ms");

            $this->line('');
            $this->warn('⚠️  Check for redundant unit_kerja queries!');
            $this->printRedundancyAnalysis();

        } catch (\Exception $e) {
            $this->error("Error: {$e->getMessage()}");
        }

        // ─────────────────────────────────────

        $this->line('');
        $this->info('📊 QUERY ANALYSIS SUMMARY');
        $this->line('═══════════════════════════════════════════════════════════════');
        
        $this->printQueryGroups();

        // Print cache stats
        $this->line('');
        $this->info('📦 CACHE STATISTICS');
        $this->line('─────────────────────────────────────────────────────────');
        $cacheStats = UserContextService::getCacheStats();
        if (!empty($cacheStats)) {
            foreach ($cacheStats as $key => $stats) {
                $hits = $stats['hits'] ?? 0;
                $misses = $stats['misses'] ?? 0;
                $this->line("{$key}: {$hits} hits, {$misses} misses");
            }
        } else {
            $this->line("No cache statistics available");
        }
    }

    private function printQueryDetails(): void
    {
        $this->line('');
        $this->line('Query Details:');
        foreach ($this->queries as $index => $query) {
            $this->line("  [{$index}] {$query['sql']}");
            if (!empty($query['bindings'])) {
                $this->line("       Bindings: " . json_encode($query['bindings']));
            }
            $this->line("       Time: {$query['time']}ms");
        }
    }

    private function printQuerySummary(): void
    {
        $this->line('');
        $this->line('Query Summary:');
        
        $grouped = [];
        foreach ($this->queries as $query) {
            $type = $this->getQueryType($query['sql']);
            if (!isset($grouped[$type])) {
                $grouped[$type] = ['count' => 0, 'time' => 0];
            }
            $grouped[$type]['count']++;
            $grouped[$type]['time'] += $query['time'];
        }

        foreach ($grouped as $type => $data) {
            $this->line("  {$type}: {$data['count']} queries ({$data['time']}ms)");
        }
    }

    private function printRedundancyAnalysis(): void
    {
        $this->line('');
        $this->warn('Redundancy Check:');

        // Get cache stats to see actual database hits
        $cacheStats = UserContextService::getCacheStats();
        
        foreach ($cacheStats as $key => $stats) {
            $misses = $stats['misses'] ?? 0;
            $hits = $stats['hits'] ?? 0;
            
            if ($misses > 1) {
                $this->warn("  ⚠️  {$key}: Database queried {$misses} times!");
                $this->warn("       Expected 1 query with {$hits} cache hits.");
            } elseif ($misses === 1 && $hits > 0) {
                $this->info("  ✅ {$key}: 1 query + {$hits} cache hits (optimal)");
            }
        }

        // Look for duplicate selects (actual SQL duplicates)
        $sqlCounts = array_count_values(array_map(fn($q) => $q['sql'], $this->queries));
        $duplicates = array_filter($sqlCounts, fn($count) => $count > 1);

        if (!empty($duplicates)) {
            $this->warn("  ⚠️  Duplicate queries detected:");
            foreach ($duplicates as $sql => $count) {
                $this->warn("       Query executed {$count} times:");
                $this->warn("       " . substr($sql, 0, 80) . "...");
            }
        }
    }

    private function printQueryGroups(): void
    {
        $tables = [];

        foreach ($this->queries as $query) {
            // Extract table names from query
            preg_match_all('/(?:from|join|into|update|table)\s+`?(\w+)`?/i', $query['sql'], $matches);
            
            foreach ($matches[1] as $table) {
                if (!isset($tables[$table])) {
                    $tables[$table] = 0;
                }
                $tables[$table]++;
            }
        }

        arsort($tables);

        $this->line('');
        $this->line('Queries by Table:');
        foreach ($tables as $table => $count) {
            $bar = str_repeat('▓', min($count, 30));
            $this->line("  {$table}: {$count} {$bar}");
        }

        $this->line('');
        $this->line('Total Queries: ' . $this->queryCount);
    }

    private function getQueryType(string $sql): string
    {
        if (stripos($sql, 'SELECT') === 0) return 'SELECT';
        if (stripos($sql, 'INSERT') === 0) return 'INSERT';
        if (stripos($sql, 'UPDATE') === 0) return 'UPDATE';
        if (stripos($sql, 'DELETE') === 0) return 'DELETE';
        return 'OTHER';
    }

    private function hasIndicators(): bool
    {
        $user = Auth::user();
        if (!$user) return false;

        $unitKerjaIds = UserContextService::getUserUnitKerjaIds();
        if (empty($unitKerjaIds)) return false;

        $templates = FormTemplateLoadingService::getActiveTemplatesForUnitKerjas($unitKerjaIds);
        return !$templates->isEmpty();
    }

    private function getFirstIndicator(): ?array
    {
        $user = Auth::user();
        if (!$user) return null;

        $unitKerjaIds = UserContextService::getUserUnitKerjaIds();
        if (empty($unitKerjaIds)) return null;

        $templates = FormTemplateLoadingService::getActiveTemplatesForUnitKerjas($unitKerjaIds);
        $template = $templates->first();

        if (!$template) return null;

        return [
            'id' => $template->id,
            'title' => $template->title,
        ];
    }
}
