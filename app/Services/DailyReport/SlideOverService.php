<?php

namespace App\Services\DailyReport;

use Exception;
use App\Repositories\Interfaces\DailyReportResponseRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class SlideOverService
{
    /**
     * Load daily reports for selected indicator and date
     */
    public function __construct(
        private readonly DailyReportResponseRepositoryInterface $dailyReportRepository,
    ) {
    }

    public function loadDailyReports(int $indicatorId, string $date): array
    {
        $user = Auth::user();
        if (!$user) {
            return [];
        }

        $userUnitIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
        if (empty($userUnitIds)) {
            return [];
        }

        $reports = $this->dailyReportRepository->getReportsForIndicatorDate(
            $indicatorId,
            $date,
            $userUnitIds
        );

        if ($reports->isEmpty()) {
            return [];
        }

        $fieldResponses = $this->dailyReportRepository->getFieldResponsesForReportIds(
            $reports->pluck('id')->all()
        );

        // Map reports with field responses
        return $reports->map(function ($report) use ($fieldResponses) {
            $reportFieldResponses = $fieldResponses->get($report->id, collect());

            return [
                'id' => $report->id,
                'total_score' => $report->total_score,
                'compliance_status' => $report->compliance_status,
                'notes' => $report->notes,
                'created_at' => $report->created_at,
                'unit_name' => $report->unit_name,
                'submitted_by_name' => $report->submitted_by_name,
                'form_title' => $report->form_title,
                'is_validated' => $report->validation_status,
                'field_responses' => $reportFieldResponses->map(function ($response) {
                    return [
                        'field_label' => $response->field_label,
                        'compliance_score' => $response->compliance_score,
                        'field_value' => $response->field_value,
                    ];
                })->toArray()
            ];
        })->toArray();
    }

    /**
     * Get selected indicator data
     */
    public function getSelectedIndicatorData(int $indicatorId, array $indicators): array
    {
        return collect($indicators)->firstWhere('id', $indicatorId) ?? [];
    }

    /**
     * Generate URL for specific indicator and date
     */
    public static function getUrlForIndicator(int $indicatorId, string $date, string $baseUrl): string
    {
        return $baseUrl . '?' . http_build_query([
            'indicator_id' => $indicatorId,
            'date' => $date
        ]);
    }

    /**
     * Validate and format date
     */
    public function validateDate(?string $date): string
    {
        if (empty($date)) {
            return now()->format('Y-m-d');
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d');
        } catch (Exception $e) {
            return now()->format('Y-m-d');
        }
    }

    /**
     * Check if indicator exists in user's accessible indicators
     */
    public function validateIndicator(int $indicatorId, array $indicators): bool
    {
        return collect($indicators)->contains('id', $indicatorId);
    }
}
