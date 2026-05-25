<?php

namespace App\Services\DailyReport;

use App\Models\DailyReportResponse;
use App\Models\FieldResponse;
use App\Models\EnhancedFormField;
use App\Models\FormTemplate;
use App\Models\UnitKerja;
use App\Models\User;
use App\Filament\Resources\ImutProfileResource\Pages\Helper\TimeUtility;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk menangani pembuatan Daily Report
 * Mengekstrak semua business logic dari Filament Page
 */
class DailyReportBuildService
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
     * Buat Daily Report dengan field responses
     *
     * @param FormTemplate $template
     * @param array $formData
     * @param UnitKerja $unitKerja
     * @param User $submittedBy
     * @param Carbon $reportDate
     * @return DailyReportResponse
     * @throws \Exception
     */
    public function create(
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

            // 2. Build dan store field responses
            $responses = [];
            foreach ($template->formFields->sortBy('order_index') as $field) {
                $fieldResponse = $this->fieldResponseBuilder->build(
                    $dailyReport,
                    $field,
                    $formData
                );

                $responses[$field->field_key] = $fieldResponse;

                // Update history suggestions untuk text fields
                $this->updateHistorySuggestions($field, $formData);
            }

            // 3. Calculate compliance ONCE (unified logic)
            $compliance = $this->complianceService->calculate($template, $formData);

            // 4. Update DailyReportResponse with compliance data via repository
            $repo->updateById($dailyReport->id, [
                'total_score' => $compliance['score'],
                'compliance_status' => $compliance['compliance_status'] ?? ($compliance['status'] ?? false),
            ]);

            return $dailyReport;
        });
    }

    /**
     * Update history suggestions untuk field tertentu
     * Dipanggil saat form submission untuk merekam suggestion history
     */
    private function updateHistorySuggestions(EnhancedFormField $field, array $formData): void
    {
        $fieldValue = $formData[$field->field_key] ?? null;

        // Hanya text fields yang perlu history
        if (!in_array($field->field_type, ['text', '']) || empty(trim($fieldValue))) {
            return;
        }

        if (!is_string($fieldValue)) {
            return;
        }

        $currentSuggestions = $field->history_suggestions ?? [];

        if (!in_array($fieldValue, $currentSuggestions)) {
            array_unshift($currentSuggestions, $fieldValue);

            // Keep only last 10 suggestions
            $currentSuggestions = array_slice($currentSuggestions, 0, 10);

            $field->update(['history_suggestions' => $currentSuggestions]);
        }
    }
}
