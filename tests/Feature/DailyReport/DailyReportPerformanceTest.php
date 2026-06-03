<?php

namespace Tests\Feature\DailyReport;

use App\Models\User;
use App\Services\DailyReport\MatrixDataService;
use App\Services\DailyReport\SlideOverService;
use App\Services\DailyReport\DailyReportMonitoringService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DailyReportPerformanceTest extends TestCase
{
    private User $user;
    private array $queryLog = [];
    private float $startTime = 0;

    protected function setUp(): void
    {
        parent::setUp();

        // Get existing user with unit_kerja
        $this->user = User::with('unitKerjas')->first();
        
        if (!$this->user || $this->user->unitKerjas->isEmpty()) {
            $this->markTestSkipped('No user with unit_kerja found');
        }

        Auth::setUser($this->user);

        // Enable query logging
        DB::listen(function ($query) {
            $this->queryLog[] = [
                'sql' => $query->sql,
                'time' => $query->time,
            ];
        });
    }

    /**
     * Test: Matrix Data Service - Query Count
     * Current: ~6 queries expected
     */
    public function test_matrix_data_service_query_count(): void
    {
        $this->queryLog = [];
        $this->startTime = microtime(true);

        $service = app(MatrixDataService::class);
        $result = $service->loadMatrixCompletely(now()->format('Y-m'));

        $duration = (microtime(true) - $this->startTime) * 1000;

        echo "\n";
        echo "═══ MatrixDataService::loadMatrixCompletely ═══\n";
        echo "Query Count: " . count($this->queryLog) . "\n";
        echo "Duration: {$duration}ms\n";
        echo "Indicators: " . count($result['indicators']) . "\n";
        echo "\n";

        $this->assertNotEmpty($result['indicators']);
        $this->assertNotEmpty($result['matrixData']);
        
        // Current implementation should have queries
        $this->assertGreaterThan(0, count($this->queryLog), 'Expected some queries');
    }

    /**
     * Test: Slide Over Service - Query Count
     * Current: May have N+1 on unit_kerja lookup (NO CACHE)
     */
    public function test_slide_over_service_query_count(): void
    {
        $this->queryLog = [];
        
        $service = app(SlideOverService::class);
        $firstIndicator = $this->getFirstIndicator();

        if (!$firstIndicator) {
            $this->markTestSkipped('No indicators found');
        }

        $this->startTime = microtime(true);
        $result = $service->loadDailyReports($firstIndicator['id'], now()->format('Y-m-d'));
        $duration = (microtime(true) - $this->startTime) * 1000;

        echo "\n";
        echo "═══ SlideOverService::loadDailyReports ═══\n";
        echo "Query Count: " . count($this->queryLog) . "\n";
        echo "Duration: {$duration}ms\n";
        echo "Reports: " . count($result) . "\n";
        echo "\n";

        // Check for unit_kerja queries (should cache but doesn't)
        $unitKerjaQueries = $this->countQueriesForTable('unit_kerja');
        if ($unitKerjaQueries > 0) {
            echo "⚠️  Unit Kerja queries: {$unitKerjaQueries} (should be cached!)\n";
        }
    }

    /**
     * Test: Multiple Services Called in Sequence (Real World Scenario)
     * Simulates: User loads matrix + opens slide over + checks monitoring
     */
    public function test_sequential_services_redundancy(): void
    {
        echo "\n";
        echo "═══ SEQUENTIAL SERVICE CALLS (Real World Scenario) ═══\n";
        echo "Scenario: Load Matrix → Open SlideOver → Check Monitoring\n";
        echo "\n";

        $this->queryLog = [];
        $this->startTime = microtime(true);

        // Call 1: Load Matrix
        echo "[1] Loading Matrix...\n";
        $matrixService = app(MatrixDataService::class);
        $matrix = $matrixService->loadMatrixCompletely(now()->format('Y-m'));
        $queryCount1 = count($this->queryLog);
        echo "    Queries: {$queryCount1}\n";

        // Call 2: Load SlideOver
        echo "[2] Opening SlideOver...\n";
        $indicator = $this->getFirstIndicator();
        if ($indicator) {
            $slideService = app(SlideOverService::class);
            $reports = $slideService->loadDailyReports($indicator['id'], now()->format('Y-m-d'));
            $queryCount2 = count($this->queryLog) - $queryCount1;
            echo "    Queries: {$queryCount2}\n";
        }

        // Call 3: Check Monitoring
        echo "[3] Checking Monitoring...\n";
        $monitoringService = app(DailyReportMonitoringService::class);
        if ($indicator) {
            $count = $monitoringService->getReportCountForIndicatorDate(
                $indicator['id'],
                now()->format('Y-m-d')
            );
            $queryCount3 = count($this->queryLog) - $queryCount1 - $queryCount2;
            echo "    Queries: {$queryCount3}\n";
        }

        $duration = (microtime(true) - $this->startTime) * 1000;

        echo "\n";
        echo "TOTAL: " . count($this->queryLog) . " queries in {$duration}ms\n";

        $this->analyzeRedundancy();
    }

    /**
     * Test: Monitor getUserUnitKerjaIds() calls
     * Current: Can be called multiple times per request without sharing cache
     */
    public function test_get_user_unit_kerja_ids_redundancy(): void
    {
        echo "\n";
        echo "═══ getUserUnitKerjaIds() Redundancy Check ═══\n";

        $this->queryLog = [];
        $unitKerjaSelectCount = 0;

        // Simulate accessing unit kerja multiple times
        $matrixService = app(MatrixDataService::class);
        $monitoringService = app(DailyReportMonitoringService::class);

        // Call 1
        echo "[1] MatrixDataService accessing unit kerja...\n";
        $matrix = $matrixService->loadMatrixCompletely(now()->format('Y-m'));
        $count1 = $this->countQueriesForTable('unit_kerja');
        echo "    Unit Kerja queries so far: {$count1}\n";

        // Call 2
        echo "[2] DailyReportMonitoringService accessing unit kerja...\n";
        if ($this->getFirstIndicator()) {
            $result = $monitoringService->getReportCountForIndicatorDate(
                $this->getFirstIndicator()['id'],
                now()->format('Y-m-d')
            );
        }
        $count2 = $this->countQueriesForTable('unit_kerja');
        echo "    Unit Kerja queries so far: {$count2}\n";

        if ($count2 > $count1) {
            echo "\n⚠️  REDUNDANCY DETECTED!\n";
            echo "    Unit Kerja queried multiple times without cache sharing\n";
        }

        $this->assertGreaterThan(0, $count1);
    }

    /**
     * Test: FormTemplate Eager Loading Consistency
     * Check if relations are loaded consistently
     */
    public function test_form_template_eager_loading(): void
    {
        echo "\n";
        echo "═══ FormTemplate Eager Loading Check ═══\n";

        $this->queryLog = [];

        // Simulate what different services do
        $templateQueryCount = $this->countQueriesForTable('form_templates');

        echo "Form Templates queries: {$templateQueryCount}\n";

        // Check for potential N+1 on formFields
        $fieldQueryCount = $this->countQueriesForTable('form_fields');
        echo "Form Fields queries: {$fieldQueryCount}\n";

        if ($fieldQueryCount > 5) {
            echo "⚠️  Potential N+1 on form_fields!\n";
        }
    }

    /**
     * HELPER: Count queries for specific table
     */
    private function countQueriesForTable(string $table): int
    {
        return count(array_filter($this->queryLog, function ($query) use ($table) {
            return stripos($query['sql'], $table) !== false;
        }));
    }

    /**
     * HELPER: Get first indicator for current user
     */
    private function getFirstIndicator(): ?array
    {
        $template = \App\Models\FormTemplate::with('imutProfile')
            ->whereHas('imutProfile', function ($q) {
                $q->where('valid_from', '<=', now())
                    ->where(function ($subQ) {
                        $subQ->whereNull('valid_until')
                            ->orWhere('valid_until', '>=', now());
                    });
            })
            ->first(['id', 'title']);

        return $template ? ['id' => $template->id, 'title' => $template->title] : null;
    }

    /**
     * HELPER: Analyze redundancy patterns
     */
    private function analyzeRedundancy(): void
    {
        echo "\n";
        echo "───── REDUNDANCY ANALYSIS ─────\n";

        // Count duplicate queries
        $sqlCounts = [];
        foreach ($this->queryLog as $query) {
            $normalized = preg_replace('/\?/g', 'X', $query['sql']);
            if (!isset($sqlCounts[$normalized])) {
                $sqlCounts[$normalized] = 0;
            }
            $sqlCounts[$normalized]++;
        }

        $duplicates = array_filter($sqlCounts, fn($count) => $count > 1);

        if (!empty($duplicates)) {
            echo "⚠️  Duplicate queries detected:\n";
            foreach ($duplicates as $sql => $count) {
                echo "    Executed {$count}x: " . substr($sql, 0, 60) . "...\n";
            }
        } else {
            echo "✓ No duplicate queries detected\n";
        }

        // Check for unit_kerja redundancy
        $unitKerjaCount = $this->countQueriesForTable('unit_kerja');
        if ($unitKerjaCount > 2) {
            echo "⚠️  Unit Kerja queried {$unitKerjaCount}x (should be cached)\n";
        }
    }
}
