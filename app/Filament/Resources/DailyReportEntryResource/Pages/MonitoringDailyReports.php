<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Repositories\Interfaces\DailyReportResponseRepositoryInterface;
use App\Filament\Resources\DailyReportEntryResource;
use App\Models\UnitKerja;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;

class MonitoringDailyReports extends Page
{
    protected static string $resource = DailyReportEntryResource::class;

    protected string $view = 'filament.resources.daily-report-entry-resource.pages.monitoring-daily-reports';

    protected static ?string $title = 'Monitoring Daily Reports';

    public array $unitKerjaStats = [];
    public ?string $periodFilter = 'current_month';

    public function mount(): void
    {
        $this->loadUnitKerjaStats();
    }

    public function refreshStats(): void
    {
        $this->loadUnitKerjaStats();
        $this->dispatch('stats-refreshed');
    }

    protected function loadUnitKerjaStats(): void
    {
        // Determine period based on filter
        $startDate = match ($this->periodFilter) {
            'today' => now()->startOfDay(),
            'yesterday' => now()->subDay()->startOfDay(),
            'current_week' => now()->startOfWeek(),
            'current_month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            default => now()->startOfMonth(),
        };

        $endDate = match ($this->periodFilter) {
            'today' => now()->endOfDay(),
            'yesterday' => now()->subDay()->endOfDay(),
            'current_week' => now()->endOfWeek(),
            'current_month' => now()->endOfMonth(),
            'last_month' => now()->subMonth()->endOfMonth(),
            default => now()->endOfMonth(),
        };

        $unitKerjas = UnitKerja::query()
            ->where('is_active', true)
            ->withCount([
                'dailyReportResponses as total_reports' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('report_date', [$startDate, $endDate]);
                },
                'dailyReportResponses as perfect_reports' => function ($query) use ($startDate, $endDate) {
                    $query->whereBetween('report_date', [$startDate, $endDate])
                        ->where(function ($q) {
                            $q->where('compliance_status', true)
                                ->orWhere('total_score', '>=', 100);
                        });
                },
                'dailyReportResponses as today_reports' => function ($query) {
                    $query->whereDate('report_date', today());
                }
            ])
            ->get();

        $this->unitKerjaStats = $unitKerjas->map(function ($unit) use ($startDate, $endDate) {
            $totalReports = $unit->total_reports ?? 0;
            $perfectReports = $unit->perfect_reports ?? 0;
            $todayReports = $unit->today_reports ?? 0;

            $complianceRate = $totalReports > 0
                ? round(($perfectReports / $totalReports) * 100, 1)
                : 0;

            // Get last submission
            $repo = app(DailyReportResponseRepositoryInterface::class);
            $lastSubmission = $repo->getLatestForUnitAndFormIds($unit->id, $startDate, $endDate, []);

            // Determine status
            $status = $this->determineStatus($todayReports, $complianceRate);

            return [
                'id' => $unit->id,
                'name' => $unit->unit_name,
                'total_reports' => $totalReports,
                'perfect_reports' => $perfectReports,
                'today_reports' => $todayReports,
                'compliance_rate' => $complianceRate,
                'status' => $status,
                'last_submission' => $lastSubmission?->created_at,
                'last_submission_human' => $lastSubmission?->created_at?->diffForHumans(),
            ];
        })->toArray();
    }

    protected function determineStatus(int $todayReports, float $complianceRate): string
    {
        if ($todayReports > 0) {
            return 'active';
        }

        if ($complianceRate >= 90) {
            return 'complete';
        }

        if ($complianceRate >= 50) {
            return 'warning';
        }

        return 'danger';
    }

    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'active' => 'Aktif Hari Ini',
            'complete' => 'Complete',
            'warning' => 'Perlu Perhatian',
            'danger' => 'Kritis',
            default => 'Unknown',
        };
    }

    public function changePeriod(string $period): void
    {
        $this->periodFilter = $period;
        $this->loadUnitKerjaStats();
    }
}
