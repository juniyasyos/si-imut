<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use App\Filament\Resources\DailyReportEntryResource\Pages\BaseDailyReportMonitoring;
use App\Filament\Resources\LaporanImutResource;
use App\Models\DailyReportResponse;
use App\Models\FormTemplate;
use App\Models\LaporanImut;
use App\Models\UnitKerja;

class MonitoringUnitDetail extends BaseDailyReportMonitoring
{
    protected static string $resource = LaporanImutResource::class;

    protected static ?string $title = 'Detail Laporan Unit Kerja';

    public ?LaporanImut $laporan = null;
    public ?UnitKerja $unitKerja = null;

    public function mount(int|string $record, int $unit): void
    {
        $this->laporan = LaporanImut::where('slug', $record)->firstOrFail();
        $this->unitKerja = UnitKerja::findOrFail($unit);

        $this->isMonitoringMode = true;
        $this->bootBase();
        $this->loadMatrixData();
    }

    // Use manual loading with specific unit filter
    protected function shouldUseMatrixService(): bool
    {
        return false;
    }

    protected function getReportsQuery($startDate, $endDate)
    {
        return DailyReportResponse::query()
            ->where('unit_kerja_id', $this->unitKerja->id)
            ->whereBetween('report_date', [$startDate, $endDate]);
    }

    protected function loadIndicators($startDate, $endDate): void
    {
        $this->indicators = FormTemplate::select([
            'form_templates.id',
            'form_templates.title',
            'imut_data.title as imut_data_title',
            'imut_kategori.category_name as category_title',
            'imut_profil.version as imut_profile_version',
        ])
            ->join('imut_profil', 'form_templates.imut_profile_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->join('imut_data_unit_kerja', 'imut_data.id', '=', 'imut_data_unit_kerja.imut_data_id')
            ->leftJoin('imut_kategori', 'imut_data.imut_kategori_id', '=', 'imut_kategori.id')
            ->where('imut_data_unit_kerja.unit_kerja_id', $this->unitKerja->id)
            ->where('form_templates.is_active', true) // only active form templates
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
            ->distinct()
            ->orderBy('imut_kategori.category_name')
            ->orderBy('imut_data.title')
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'title' => $template->imut_data_title ?? $template->title,
                    'category' => $template->category_title,
                    'imut_profile_version' => $template->imut_profile_version,
                ];
            })
            ->toArray();
    }

    /**
     * Load daily reports for selected indicator and date (unit-specific)
     */
    public function loadDailyReports(): void
    {
        if (!$this->selectedIndicatorId || !$this->selectedDate) {
            $this->dailyReports = [];
            return;
        }

        // Get daily reports for specific unit
        $reports = \App\Models\DailyReportResponse::query()
            ->select([
                'daily_report_responses.*',
                'unit_kerja.unit_name as unit_name',
                'users.name as submitted_by_name',
                'form_templates.title as form_title'
            ])
            ->join('form_templates', 'daily_report_responses.form_template_id', '=', 'form_templates.id')
            ->join('unit_kerja', 'daily_report_responses.unit_kerja_id', '=', 'unit_kerja.id')
            ->join('users', 'daily_report_responses.submitted_by', '=', 'users.id')
            ->where('form_templates.id', $this->selectedIndicatorId)
            ->where('daily_report_responses.unit_kerja_id', $this->unitKerja->id)
            ->whereDate('daily_report_responses.report_date', $this->selectedDate)
            ->latest('daily_report_responses.created_at')
            ->get();

        if ($reports->isEmpty()) {
            $this->dailyReports = [];
            return;
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
        $this->dailyReports = $reports->map(function ($report) use ($fieldResponses) {
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

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.siimut.resources.laporan-imuts.index') => 'Laporan IMUT',
            route('filament.siimut.resources.laporan-imuts.monitoring-daily-reports', $this->laporan->slug) => 'Monitoring',
            null => $this->unitKerja->unit_name,
        ];
    }
}
