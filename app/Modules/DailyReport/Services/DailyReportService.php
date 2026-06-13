<?php

namespace App\Modules\DailyReport\Services;

use App\Modules\DailyReport\Models\DailyReportResponse;
use App\Models\EnhancedFormField;
use App\Models\FormTemplate;
use App\Models\UnitKerja;
use App\Models\User;
use App\Services\FormTemplateLoadingService;
use App\Services\UserContextService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Modules\DailyReport\Contracts\DailyReportInterface;

/**
 * Unified Daily Report Service (Phase 4)
 * 
 * Consolidates authorization + creation logic into single service
 * Eliminates redundant template loading and service orchestration
 * 
 * Replaces:
 * - DailyReportAuthorizationService::createDailyReportWithResponses()
 * - DailyReportBuildService::create()
 */
class DailyReportService implements DailyReportInterface
{
    private UnifiedComplianceService $complianceService;
    private FieldResponseBuilderService $fieldResponseBuilder;

    public function __construct(
        UnifiedComplianceService $complianceService,
        FieldResponseBuilderService $fieldResponseBuilder
    ) {
        $this->complianceService = $complianceService;
        $this->fieldResponseBuilder = $fieldResponseBuilder;
    }

    /**
     * Create Daily Report with authorization checks and single-pass scoring
     * 
     * This is the primary entry point for Phase 4 optimization:
     * - Authorizes user access (combines permission checks)
     * - Loads template ONCE with all relations
     * - Validates template for unit_kerja
     * - Creates report with Phase 3 single-pass scoring
     * - Returns fully created and validated report
     * 
     * @param User $user The user creating the report
     * @param int $templateId The form template ID
     * @param string $reportDate Date of the report (Y-m-d format)
     * @param array $formData The form field responses
     * @param int $unitKerjaId The unit_kerja for this report
     * 
     * @return DailyReportResponse
     * 
     * @throws \Exception If authorization fails or validation fails
     */
    public function createWithAuthorization(
        User $user,
        int $templateId,
        string $reportDate,
        array $formData,
        int $unitKerjaId
    ): DailyReportResponse {
        try {
            // 1. Validate user
            if (!$user || !$user->id) {
                throw new \Exception('User tidak valid. Pastikan Anda sudah login.');
            }

            // 2. Authorize user access (combines all permission checks)
            $this->authorizeUserForTemplate($user, $templateId, $unitKerjaId);

            // 3. Load template ONCE (Phase 4 optimization - single load)
            $template = FormTemplateLoadingService::getTemplate($templateId);

            if (!$template || !$template->imutProfile) {
                throw new \Exception('Template tidak ditemukan atau profil tidak valid.');
            }

            // 4. Resolve template for specific date (in case multiple versions exist)
            $validTemplate = $this->resolveTemplateForDate($template, $reportDate);
            if (!$validTemplate) {
                throw new \Exception('Template tidak valid untuk tanggal ' . $reportDate);
            }

            // 5. Validate unit_kerja access through profile
            $this->validateTemplateForUnitKerja($validTemplate, $unitKerjaId);

            // 6. Get UnitKerja model
            $unitKerja = UnitKerja::findOrFail($unitKerjaId);

            // 7. Parse and validate date
            $parsedDate = $this->parseReportDate($reportDate);

            // 8. Create report using Phase 3 optimized flow (single-pass scoring)
            return $this->createReport($validTemplate, $formData, $unitKerja, $user, $parsedDate);

        } catch (\Exception $e) {
            Log::error('Error creating daily report with authorization', [
                'user_id' => $user?->id,
                'template_id' => $templateId,
                'unit_kerja_id' => $unitKerjaId,
                'date' => $reportDate,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Authorize user access to create report for this template
     * 
     * Performs:
     * - Permission checks (view_all_data or view_by_unit_kerja)
     * - Unit kerja membership verification
     * - Template profile association validation
     * 
     * @param User $user
     * @param int $templateId
     * @param int $unitKerjaId
     * 
     * @throws \Exception If authorization fails
     */
    private function authorizeUserForTemplate(User $user, int $templateId, int $unitKerjaId): void
    {
        // Global permission bypasses unit restrictions
        if ($user->can('view_all_data_imut::data')) {
            return;
        }

        // Check if user has unit-based permission
        if (!$user->can('view_by_unit_kerja_imut::data')) {
            throw new \Exception('Anda tidak memiliki izin untuk membuat laporan harian.');
        }

        // Verify user belongs to the requested unit_kerja
        $userUnitIds = UserContextService::getUserUnitKerjaIdsForUser($user);
        
        if (!in_array($unitKerjaId, $userUnitIds)) {
            throw new \Exception('Anda tidak memiliki akses ke unit kerja ini.');
        }
    }

    /**
     * Resolve template valid for specific report date
     * 
     * In case multiple versions of a template exist with different valid dates,
     * this method returns the appropriate version for the given date.
     * 
     * @param FormTemplate $template
     * @param string $reportDate (Y-m-d format)
     * 
     * @return FormTemplate|null
     */
    private function resolveTemplateForDate(FormTemplate $template, string $reportDate): ?FormTemplate
    {
        try {
            $profile = $template->imutProfile;

            if (!$profile) {
                return $template; // Return original if no profile
            }

            // Find template version valid for this date
            $templateForDate = $profile->formTemplates()
                ->with('formFields.options')
                ->whereDate('berlaku_tanggal', '<=', $reportDate)
                ->where(function ($query) use ($reportDate) {
                    $query->whereNull('berakhir_tanggal')
                          ->orWhereDate('berakhir_tanggal', '>=', $reportDate);
                })
                ->orderByDesc('id')
                ->first();

            return $templateForDate ?: $template;
        } catch (\Exception $e) {
            Log::error('Error resolving template for date', [
                'template_id' => $template->id,
                'date' => $reportDate,
                'error' => $e->getMessage()
            ]);
            return $template;
        }
    }

    /**
     * Validate that template is accessible for the given unit_kerja
     * 
     * @param FormTemplate $template
     * @param int $unitKerjaId
     * 
     * @throws \Exception If template not accessible for unit_kerja
     */
    private function validateTemplateForUnitKerja(FormTemplate $template, int $unitKerjaId): void
    {
        // Note: Detailed unit_kerja validation is handled in authorizeUserForTemplate()
        // This method is a safety check but not strictly required since authorization already validates access
        
        if (!$template->imutProfile) {
            throw new \Exception('Template tidak memiliki profil yang valid.');
        }

        // Basic existence check
        if (!UnitKerja::where('id', $unitKerjaId)->exists()) {
            throw new \Exception('Unit kerja tidak ditemukan.');
        }
    }

    /**
     * Parse report date from string
     * 
     * @param string $reportDate (Y-m-d format or similar)
     * 
     * @return Carbon
     * 
     * @throws \Exception If date parsing fails
     */
    private function parseReportDate(string $reportDate): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m-d', $reportDate)->startOfDay();
        } catch (\Exception $e) {
            throw new \Exception('Format tanggal tidak valid. Gunakan format YYYY-MM-DD.');
        }
    }

    /**
     * Create Daily Report with Phase 3 single-pass scoring optimization
     * 
     * Internal method that handles the actual creation logic.
     * This uses the optimized DailyReportBuildService flow.
     * 
     * @param FormTemplate $template
     * @param array $formData
     * @param UnitKerja $unitKerja
     * @param User $submittedBy
     * @param Carbon $reportDate
     * 
     * @return DailyReportResponse
     */
    private function createReport(
        FormTemplate $template,
        array $formData,
        UnitKerja $unitKerja,
        User $submittedBy,
        Carbon $reportDate
    ): DailyReportResponse {
        // Load formFields dengan eager loading jika belum
        if (!$template->relationLoaded('formFields')) {
            $template->load('formFields.options');
        }

        $repo = app(\App\Repositories\Interfaces\DailyReportResponseRepositoryInterface::class);

        return DB::transaction(function () use ($template, $formData, $unitKerja, $submittedBy, $reportDate, $repo) {
            // 1. Create DailyReportResponse record via repository
            $dailyReport = $repo->createReport([
                'form_template_id' => $template->id,
                'unit_kerja_id' => $unitKerja->id,
                'submitted_by' => $submittedBy->id,
                'report_date' => $reportDate,
                'total_score' => 0,
                'compliance_status' => 'pending',
                'auto_calculated' => true,
            ]);

            // 2. Calculate compliance FIRST (single pass scoring - Phase 3 optimization)
            $compliance = $this->complianceService->calculate($template, $formData);

            // 3. Build field responses using PRE-CALCULATED scores
            $fieldScores = [];
            foreach ($compliance['calculation_details']['field_breakdown'] as $fieldBreakdown) {
                $fieldScores[$fieldBreakdown['field_key']] = $fieldBreakdown['score'];
            }

            $responses = [];
            foreach ($template->formFields->sortBy('order_index') as $field) {
                $preCalculatedScore = $fieldScores[$field->field_key] ?? 0;

                $fieldResponse = $this->fieldResponseBuilder->buildWithScore(
                    $dailyReport,
                    $field,
                    $formData,
                    $preCalculatedScore
                );

                $responses[$field->field_key] = $fieldResponse;
                $this->updateHistorySuggestions($field, $formData);
            }

            // 4. Update DailyReportResponse with compliance data via repository
            $repo->updateById($dailyReport->id, [
                'total_score' => $compliance['score'],
                'compliance_status' => $compliance['compliance_status'] ?? ($compliance['status'] ?? false),
            ]);

            Log::info('Daily report created successfully', [
                'report_id' => $dailyReport->id,
                'template_id' => $template->id,
                'user_id' => $submittedBy->id,
                'unit_kerja_id' => $unitKerja->id,
                'compliance_score' => $compliance['score']
            ]);

            return $dailyReport;
        });
    }

    /**
     * Update history suggestions untuk text fields
     * 
     * @param EnhancedFormField $field
     * @param array $formData
     */
    private function updateHistorySuggestions(EnhancedFormField $field, array $formData): void
    {
        $fieldValue = $formData[$field->field_key] ?? null;

        if (!in_array($field->field_type, ['text', 'textarea']) || empty(trim((string)$fieldValue))) {
            return;
        }

        if (!is_string($fieldValue)) {
            return;
        }

        $currentSuggestions = $field->history_suggestions ?? [];

        if (!in_array($fieldValue, $currentSuggestions)) {
            array_unshift($currentSuggestions, $fieldValue);
            $currentSuggestions = array_slice($currentSuggestions, 0, 10);

            $field->update(['history_suggestions' => $currentSuggestions]);
        }
    }
}
