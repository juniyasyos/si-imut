<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use App\Filament\Resources\DailyReportEntryResource\Pages\BaseDailyReportMonitoring;
use App\Filament\Resources\LaporanImutResource;
use App\Models\FormTemplate;
use App\Models\LaporanImut;
use App\Models\UnitKerja;
use App\Repositories\Interfaces\DailyReportResponseRepositoryInterface;

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
        $repo = app(DailyReportResponseRepositoryInterface::class);
        // Return a collection directly from repository; Base loader accepts collections now
        return $repo->getTableViewEntries(
            auth()->user(),
            null,
            $this->unitKerja->id,
            $startDate,
            $endDate
        );
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

        $dailyReportRepository = app(DailyReportResponseRepositoryInterface::class);

        $reports = $dailyReportRepository->getReportsForIndicatorDate(
            $this->selectedIndicatorId,
            $this->selectedDate,
            [$this->unitKerja->id]
        );

        if ($reports->isEmpty()) {
            $this->dailyReports = [];
            return;
        }

        $fieldResponses = $dailyReportRepository->getFieldResponsesForReportIds(
            $reports->pluck('id')->all()
        );

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
