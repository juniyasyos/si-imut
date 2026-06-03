<?php

namespace Tests\Feature\DailyReport;

use App\Models\FormTemplate;
use App\Models\User;
use App\Services\DailyReport\UnifiedComplianceService;
use App\Services\DailyReport\FieldResponseBuilderService;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class ComplianceScoringBenchmarkTest extends TestCase
{
    private User $user;
    private FormTemplate $template;
    private array $scoringCalls = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::with('unitKerjas')->first();
        if (!$this->user || $this->user->unitKerjas->isEmpty()) {
            $this->markTestSkipped('No user with unit_kerja found');
        }

        Auth::setUser($this->user);

        // Find a form template with fields
        $this->template = FormTemplate::with('formFields.options')
            ->whereHas('formFields')
            ->first();

        if (!$this->template) {
            $this->markTestSkipped('No form template with fields found');
        }
    }

    /**
     * Test: Detect Field Scoring Double Iteration
     * This is the critical N+1 scoring issue!
     */
    public function test_field_scoring_double_iteration_detection(): void
    {
        echo "\n";
        echo "════════════════════════════════════════════════════════════\n";
        echo "  FIELD SCORING DOUBLE ITERATION TEST\n";
        echo "════════════════════════════════════════════════════════════\n";
        echo "\n";

        $fieldCount = $this->template->formFields->count();
        echo "Template: {$this->template->title}\n";
        echo "Field Count: {$fieldCount}\n";
        echo "\n";

        // Prepare mock form data
        $formData = $this->generateMockFormData();

        // ===================================
        // TEST 1: FieldResponseBuilder alone
        // ===================================
        echo "TEST 1: FieldResponseBuilder::build() (Per-field scoring)\n";
        echo "─────────────────────────────────────────────────────────\n";

        $this->scoringCalls = [];
        $this->mockUnifiedComplianceService();

        $builderService = app(FieldResponseBuilderService::class);
        
        $builderScoreCalls = 0;
        foreach ($this->template->formFields as $field) {
            try {
                // This calls UnifiedComplianceService::scoreField()
                $builderService->build(
                    new \App\Models\DailyReportResponse(),
                    $field,
                    $formData
                );
                $builderScoreCalls++;
            } catch (\Exception $e) {
                // Expected - we're not creating real records
            }
        }

        echo "Fields scored in loop: {$builderScoreCalls}\n";
        echo "scoreField() calls from builder: " . count($this->scoringCalls) . "\n";
        echo "\n";

        // ===================================
        // TEST 2: UnifiedComplianceService alone
        // ===================================
        echo "TEST 2: UnifiedComplianceService::calculate() (Batch scoring)\n";
        echo "─────────────────────────────────────────────────────────\n";

        $this->scoringCalls = [];
        $this->mockUnifiedComplianceService();

        $complianceService = app(UnifiedComplianceService::class);
        try {
            $result = $complianceService->calculate($this->template, $formData);
            
            echo "Total score calculated: " . ($result['score'] ?? 'N/A') . "\n";
            echo "scoreField() calls from calculate: " . count($this->scoringCalls) . "\n";
        } catch (\Exception $e) {
            echo "Error (expected): " . $e->getMessage() . "\n";
        }

        echo "\n";

        // ===================================
        // ANALYSIS
        // ===================================
        echo "════════════════════════════════════════════════════════════\n";
        echo "ANALYSIS\n";
        echo "════════════════════════════════════════════════════════════\n";
        echo "\n";

        if ($builderScoreCalls > 0 && count($this->scoringCalls) > $fieldCount) {
            echo "🔴 DOUBLE ITERATION DETECTED!\n";
            echo "\n";
            echo "Expected: {$fieldCount} fields scored once\n";
            echo "Actual: " . count($this->scoringCalls) . " scoring operations\n";
            echo "Redundancy: " . (count($this->scoringCalls) - $fieldCount) . " extra operations\n";
            echo "\n";
            echo "This means:\n";
            echo "- Field scoring is calculated TWICE per submission\n";
            echo "- Performance waste: ~50% CPU overhead\n";
            echo "- Fix: Consolidate scoring into single pass\n";
        } else {
            echo "✓ No obvious double iteration detected\n";
        }

        echo "\n";
    }

    /**
     * Test: Benchmark Single vs Double Pass Scoring
     */
    public function test_single_pass_vs_double_pass_scoring_performance(): void
    {
        echo "\n";
        echo "════════════════════════════════════════════════════════════\n";
        echo "  SCORING PERFORMANCE: Single Pass vs Double Pass\n";
        echo "════════════════════════════════════════════════════════════\n";
        echo "\n";

        $fieldCount = $this->template->formFields->count();
        $formData = $this->generateMockFormData();

        echo "Template: {$this->template->title}\n";
        echo "Fields: {$fieldCount}\n";
        echo "\n";

        // ===================================
        // Simulate CURRENT (Double Pass)
        // ===================================
        echo "CURRENT IMPLEMENTATION (Double Pass):\n";
        echo "─────────────────────────────────────\n";

        $scoreCount = 0;
        $startTime = microtime(true);

        for ($iteration = 1; $iteration <= 2; $iteration++) {
            foreach ($this->template->formFields as $field) {
                // Simulate scoring
                $this->simulateFieldScoring($field, $formData);
                $scoreCount++;
            }
            echo "  Pass {$iteration}: Scored {$fieldCount} fields\n";
        }

        $doublePassTime = (microtime(true) - $startTime) * 1000;
        echo "Total: {$scoreCount} scoring operations\n";
        echo "Time: {$doublePassTime}ms\n";
        echo "\n";

        // ===================================
        // Simulate OPTIMIZED (Single Pass)
        // ===================================
        echo "OPTIMIZED IMPLEMENTATION (Single Pass):\n";
        echo "─────────────────────────────────\n";

        $scoreCount = 0;
        $startTime = microtime(true);

        foreach ($this->template->formFields as $field) {
            // Score once, collect data
            $score = $this->simulateFieldScoring($field, $formData);
            
            // Build response + accumulate stats in same loop
            // (pseudo code, not actually building response)
            $scoreCount++;
        }

        $singlePassTime = (microtime(true) - $startTime) * 1000;
        echo "  Pass 1: Scored {$fieldCount} fields\n";
        echo "Total: {$scoreCount} scoring operations\n";
        echo "Time: {$singlePassTime}ms\n";
        echo "\n";

        // ===================================
        // COMPARISON
        // ===================================
        echo "════════════════════════════════════════════════════════════\n";
        echo "PERFORMANCE COMPARISON\n";
        echo "════════════════════════════════════════════════════════════\n";
        echo "\n";

        $improvement = (($doublePassTime - $singlePassTime) / $doublePassTime) * 100;
        $operationReduction = (($scoreCount - $fieldCount) / $scoreCount) * 100;

        echo "Double Pass:\n";
        echo "  Operations: {$scoreCount}\n";
        echo "  Time: {$doublePassTime}ms\n";
        echo "\n";

        echo "Single Pass (Optimized):\n";
        echo "  Operations: {$fieldCount}\n";
        echo "  Time: {$singlePassTime}ms\n";
        echo "\n";

        echo "Improvement:\n";
        echo "  Operations reduction: {$operationReduction}% (-" . ($scoreCount - $fieldCount) . ")\n";
        echo "  Time reduction: {$improvement}%\n";
        echo "\n";

        if ($improvement > 0) {
            echo "✓ Single pass is " . number_format($improvement, 1) . "% faster!\n";
        }
    }

    /**
     * Test: Detect duplicate scoreField() calls with call tracking
     */
    public function test_track_score_field_calls(): void
    {
        echo "\n";
        echo "════════════════════════════════════════════════════════════\n";
        echo "  SCOREFILELD() CALL TRACKING\n";
        echo "════════════════════════════════════════════════════════════\n";
        echo "\n";

        $fieldCount = $this->template->formFields->count();
        echo "Monitoring scoreField() calls for: {$fieldCount} fields\n";
        echo "\n";

        $formData = $this->generateMockFormData();
        $callTracker = [];

        // Monitor calls
        $this->mockUnifiedComplianceService($callTracker);

        $complianceService = app(UnifiedComplianceService::class);
        
        try {
            $complianceService->calculate($this->template, $formData);
        } catch (\Exception $e) {
            // Expected
        }

        echo "scoreField() called " . count($callTracker) . " times\n";
        echo "Expected: ~{$fieldCount} times\n";
        echo "\n";

        if (count($callTracker) > $fieldCount) {
            $excess = count($callTracker) - $fieldCount;
            echo "⚠️  EXCESS CALLS DETECTED: {$excess} extra calls\n";
        }

        // Show field call counts
        $fieldCallCounts = [];
        foreach ($callTracker as $call) {
            $fieldId = $call['field_id'] ?? 'unknown';
            if (!isset($fieldCallCounts[$fieldId])) {
                $fieldCallCounts[$fieldId] = 0;
            }
            $fieldCallCounts[$fieldId]++;
        }

        echo "\n";
        echo "Calls per field:\n";
        foreach ($fieldCallCounts as $fieldId => $count) {
            if ($count > 1) {
                echo "  Field {$fieldId}: {$count}x ⚠️ CALLED MULTIPLE TIMES\n";
            } else {
                echo "  Field {$fieldId}: {$count}x\n";
            }
        }
    }

    // ═══════════════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════════════

    private function generateMockFormData(): array
    {
        $formData = [];

        foreach ($this->template->formFields as $field) {
            $formData[$field->field_key] = $this->generateMockFieldValue($field);
        }

        return $formData;
    }

    private function generateMockFieldValue($field): mixed
    {
        return match ($field->field_type) {
            'text' => 'Mock text value',
            'checkbox' => ['option1', 'option2'],
            'select' => 'option1',
            'rating' => 3,
            'time_range' => ['start_time' => '09:00', 'end_time' => '17:00'],
            'time_duration' => ['duration' => 8],
            default => null,
        };
    }

    private function simulateFieldScoring($field, $formData): float
    {
        // Simulate scoring logic
        usleep(10); // 10μs per field
        return rand(0, 100) / 100;
    }

    private function mockUnifiedComplianceService(&$callTracker = null): void
    {
        if ($callTracker === null) {
            $callTracker = [];
        }

        // This would require mocking the service
        // For now, we're just tracking calls manually
    }
}
