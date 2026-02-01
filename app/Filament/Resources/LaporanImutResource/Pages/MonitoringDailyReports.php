<?php

namespace App\Filament\Resources\LaporanImutResource\Pages;

use App\Filament\Resources\LaporanImutResource;
use App\Models\DailyReportResponse;
use App\Models\LaporanImut;
use App\Models\UnitKerja;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class MonitoringDailyReports extends Page
{
    protected static string $resource = LaporanImutResource::class;

    protected static string $view = 'filament.resources.laporan-imut-resource.pages.monitoring-daily-reports';

    protected static ?string $title = 'Monitoring Daily Reports';

    protected static ?string $navigationLabel = 'Monitoring Daily Reports';

    public ?LaporanImut $laporan = null;

    public array $unitKerjaStats = [];

    public function mount(int|string $record): void
    {
        $this->laporan = LaporanImut::where('slug', $record)->firstOrFail();
        $this->loadUnitKerjaStats();
    }

    protected function loadUnitKerjaStats(): void
    {
        $start = $this->laporan->assessment_period_start;
        $end = $this->laporan->assessment_period_end;
        $today = now()->startOfDay();

        // Get all unit kerja in this laporan
        $unitKerjas = $this->laporan->unitKerjas()->with('imutData.profiles.formTemplates')->get();

        $this->unitKerjaStats = $unitKerjas->map(function ($unitKerja) use ($start, $end, $today) {
            // Get total expected indicators for this unit kerja
            $expectedIndicators = $unitKerja->imutData()->where('status', true)->count();

            // Get all form templates for this unit kerja's indicators
            $formTemplateIds = $unitKerja->imutData()
                ->where('status', true)
                ->with('profiles.formTemplates')
                ->get()
                ->flatMap(function ($imutData) use ($start, $end) {
                    return $imutData->profiles()
                        ->validForPeriod($start, $end)
                        ->with('formTemplates')
                        ->get()
                        ->flatMap->formTemplates;
                })
                ->pluck('id')
                ->unique();

            // Total days in period
            $totalDays = $start->diffInDays($end) + 1;
            $daysPassed = $start->diffInDays($today) + 1;
            $daysPassed = min($daysPassed, $totalDays); // Cap at total days

            // Expected total reports = indicators × days passed
            $expectedReports = $expectedIndicators * $daysPassed;

            // Get actual reports submitted
            $actualReports = DailyReportResponse::query()
                ->where('unit_kerja_id', $unitKerja->id)
                ->whereIn('form_template_id', $formTemplateIds)
                ->whereBetween('report_date', [$start, min($today, $end)])
                ->count();

            // Get today's reports
            $todayReports = DailyReportResponse::query()
                ->where('unit_kerja_id', $unitKerja->id)
                ->whereIn('form_template_id', $formTemplateIds)
                ->whereDate('report_date', $today)
                ->count();

            // Get compliance data (reports with 100% score)
            $perfectReports = DailyReportResponse::query()
                ->where('unit_kerja_id', $unitKerja->id)
                ->whereIn('form_template_id', $formTemplateIds)
                ->whereBetween('report_date', [$start, min($today, $end)])
                ->where(function ($query) {
                    $query->where('total_score', '>=', 100)
                        ->orWhereRaw("JSON_EXTRACT(calculation_details, '$.compliance_status') = true");
                })
                ->count();

            // Calculate percentages
            $completionRate = $expectedReports > 0
                ? round(($actualReports / $expectedReports) * 100, 1)
                : 0;

            $complianceRate = $actualReports > 0
                ? round(($perfectReports / $actualReports) * 100, 1)
                : 0;

            // Get last submission time
            $lastSubmission = DailyReportResponse::query()
                ->where('unit_kerja_id', $unitKerja->id)
                ->whereIn('form_template_id', $formTemplateIds)
                ->latest('created_at')
                ->first();

            // Status determination
            $status = $this->determineStatus($completionRate, $todayReports, $expectedIndicators);

            return [
                'id' => $unitKerja->id,
                'name' => $unitKerja->unit_name,
                'expected_indicators' => $expectedIndicators,
                'expected_reports' => $expectedReports,
                'actual_reports' => $actualReports,
                'perfect_reports' => $perfectReports,
                'today_reports' => $todayReports,
                'completion_rate' => $completionRate,
                'compliance_rate' => $complianceRate,
                'last_submission' => $lastSubmission?->created_at,
                'last_submission_human' => $lastSubmission?->created_at?->diffForHumans(),
                'status' => $status,
                'days_passed' => $daysPassed,
                'total_days' => $totalDays,
            ];
        })->toArray();

        // Sort by completion rate descending
        usort($this->unitKerjaStats, fn($a, $b) => $b['completion_rate'] <=> $a['completion_rate']);
    }

    protected function determineStatus(float $completionRate, int $todayReports, int $expectedIndicators): string
    {
        // Active: ada laporan hari ini
        if ($todayReports > 0) {
            return 'active';
        }

        // Complete: completion rate >= 90%
        if ($completionRate >= 90) {
            return 'complete';
        }

        // Warning: completion rate 50-89%
        if ($completionRate >= 50) {
            return 'warning';
        }

        // Danger: completion rate < 50%
        return 'danger';
    }

    public function getStatusColor(string $status): string
    {
        return match ($status) {
            'active' => 'success',
            'complete' => 'info',
            'warning' => 'warning',
            'danger' => 'danger',
            default => 'gray',
        };
    }

    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'active' => '🟢 Aktif Hari Ini',
            'complete' => '✅ Lengkap',
            'warning' => '⚠️ Perlu Perhatian',
            'danger' => '🔴 Tidak Aktif',
            default => 'Unknown',
        };
    }

    public function refreshStats(): void
    {
        $this->loadUnitKerjaStats();
        $this->dispatch('stats-refreshed');
    }

    public function getBreadcrumbs(): array
    {
        return [
            route('filament.siimut.resources.laporan-imuts.index') => 'Laporan IMUT',
            route('filament.siimut.resources.laporan-imuts.edit', $this->laporan->slug) => $this->laporan->name,
            null => 'Monitoring Daily Reports',
        ];
    }
}
