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

        try {
            $service = app(MatrixDataService::class);
            $result = $service->loadMatrixCompletely(now()->format('Y-m'));
            
            $duration = (microtime(true) - $this->startTime) * 1000;
            
            $this->line("Queries: {$this->queryCount}");
            $this->line("Duration: " . number_format($duration, 2) . "ms");
            $this->line("Indicators: " . count($result['indicators']));
            $this->line("Matrix rows: " . count($result['matrixData']));
            
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

        if ($this->hasIndicators()) {
            $this->queryCount = 0;
            $this->queries = [];
            $this->startTime = microtime(true);

            try {
                $service = app(SlideOverService::class);
                $indicators = $this->getFirstIndicator();
                
                if ($indicators) {
                    $indicatorId = $indicators['id'];
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
            // Call 1: Matrix
            $matrixService = app(MatrixDataService::class);
            $matrixResult = $matrixService->loadMatrixCompletely(now()->format('Y-m'));

            // Call 2: Slide Over
            $slideOverService = app(SlideOverService::class);
            if ($this->hasIndicators()) {
                $indicators = $this->getFirstIndicator();
                if ($indicators) {
                    $slideOverService->loadDailyReports($indicators['id'], now()->format('Y-m-d'));
                }
            }

            // Call 3: Monitoring count check
            $monitoringService = app(DailyReportMonitoringService::class);
            $user = Auth::user();
            $count = $monitoringService->getReportCountForIndicatorDate(
                $this->getFirstIndicator()['id'] ?? 1,
                now()->format('Y-m-d')
            );

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

        // Count SELECT queries for unit_kerja
        $unitKerjaQueries = array_filter($this->queries, function ($q) {
            return stripos($q['sql'], 'unit_kerja') !== false && 
                   stripos($q['sql'], 'SELECT') !== false;
        });

        if (count($unitKerjaQueries) > 1) {
            $this->warn("  ⚠️  Unit Kerja queried " . count($unitKerjaQueries) . " times!");
            $this->warn("       This suggests getUserUnitKerjaIds() is called multiple times");
            $this->warn("       without sharing cache across services.");
        }

        // Count form_templates queries
        $formQueries = array_filter($this->queries, function ($q) {
            return stripos($q['sql'], 'form_templates') !== false;
        });

        if (count($formQueries) > 2) {
            $this->warn("  ⚠️  Form Templates queried " . count($formQueries) . " times!");
            $this->warn("       Check for duplicate eager loading or N+1 queries.");
        }

        // Look for duplicate selects
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
