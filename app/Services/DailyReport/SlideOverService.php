<?php

namespace App\Services\DailyReport;

use App\Models\DailyReportEntry;
use App\Models\FormTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SlideOverService
{
    /**
     * Load daily reports for selected indicator and date
     */
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

        // Get daily reports with basic data
        $reports = \App\Models\DailyReportResponse::query()
            ->select([
                'daily_report_responses.*',
                'unit_kerja.unit_name as unit_name',
                'users.name as submitted_by_name',
                'form_templates.title as form_title'
            ])
            ->join('form_templates', 'daily_report_responses.form_template_id', '=', 'form_templates.id')
            ->join('imut_profil', 'form_templates.imut_profile_id', '=', 'imut_profil.id')
            ->join('unit_kerja', 'daily_report_responses.unit_kerja_id', '=', 'unit_kerja.id')
            ->join('users', 'daily_report_responses.submitted_by', '=', 'users.id')
            ->where('form_templates.id', $indicatorId)
            ->where(function ($query) {
                $now = now();
                $query->where(function ($q) use ($now) {
                    $q->where('imut_profil.valid_from', '<=', $now)
                        ->where(function ($subQ) use ($now) {
                            $subQ->whereNull('imut_profil.valid_until')
                                ->orWhere('imut_profil.valid_until', '>=', $now);
                        });
                });
            })
            ->whereDate('daily_report_responses.report_date', $date)
            ->whereIn('daily_report_responses.unit_kerja_id', $userUnitIds)
            ->latest('daily_report_responses.created_at')
            ->get();

        if ($reports->isEmpty()) {
            return [];
        }

        // Get field responses for all reports
        $reportIds = $reports->pluck('id')->toArray();
        $fieldResponses = \App\Models\FieldResponse::query()
            ->select([
                'field_responses.*',
                'enhanced_form_fields.field_label'
            ])
            ->join('enhanced_form_fields', 'field_responses.form_field_id', '=', 'enhanced_form_fields.id')
            ->whereIn('field_responses.daily_report_response_id', $reportIds)
            ->get()
            ->groupBy('daily_report_response_id');

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
        } catch (\Exception $e) {
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
