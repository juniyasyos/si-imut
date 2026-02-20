<?php

namespace App\Filament\Widgets;

use App\Models\DailyReportResponse;
use App\Models\LaporanImut;
use App\Filament\Widgets\LaporanLatestWidget;
use Filament\Widgets\Widget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class LaporanUnitWidget extends Widget
{
    protected static string $view = 'filament.widgets.laporan-unit-widget';

    public static function canView(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        // user must have the new permission and must belong to at least one unit kerja
        return  $user->unitKerjas()->exists();
    }

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function getLaporan(): ?LaporanImut
    {
        return LaporanLatestWidget::getLatestLaporan();
    }

    /**
     * Summaries per unit kerja that belong to current user and are part of latest laporan
     *
     * @return array<int,array{
     *     unit_id:int,
     *     unit_name:string,
     *     today:int,
     *     perfect:int,
     *     last_submission:?\Illuminate\Support\Carbon
     * }>
     */
    public function getUnitSummaries(): array
    {
        $laporan = $this->getLaporan();
        if (! $laporan) {
            return [];
        }

        $today = now()->toDateString();
        $start = $laporan->assessment_period_start;
        $end = $laporan->assessment_period_end;

        $user = Auth::user();
        if (! $user) {
            return [];
        }

        $units = $user->unitKerjas()
            ->whereHas('laporanUnitKerjas', function ($q) use ($laporan) {
                $q->where('laporan_imut_id', $laporan->id);
            })
            ->get();

        $summaries = [];

        foreach ($units as $unit) {
            // gather relevant formTemplate ids for this unit within period
            $formIds = $unit->imutData()
                ->where('status', true)
                ->with(['profiles' => function ($q) use ($start, $end) {
                    $q->validForPeriod($start, $end);
                }, 'profiles.formTemplates'])
                ->get()
                ->flatMap(function ($imutData) {
                    return $imutData->profiles->flatMap->formTemplates;
                })
                ->pluck('id')
                ->unique()
                ->toArray();

            // basic counts
            $todayCount = DailyReportResponse::query()
                ->whereDate('report_date', $today)
                ->whereBetween('report_date', [$start, $end])
                ->where('unit_kerja_id', $unit->id)
                ->whereIn('form_template_id', $formIds)
                ->count();

            $perfectCount = DailyReportResponse::query()
                ->whereBetween('report_date', [$start, $end])
                ->where('unit_kerja_id', $unit->id)
                ->whereIn('form_template_id', $formIds)
                ->where(function ($q) {
                    $q->where('total_score', '>=', 100)
                        ->orWhereRaw("JSON_EXTRACT(calculation_details, '$.compliance_status') = true");
                })
                ->count();

            $last = DailyReportResponse::query()
                ->whereBetween('report_date', [$start, $end])
                ->where('unit_kerja_id', $unit->id)
                ->whereIn('form_template_id', $formIds)
                ->latest('created_at')
                ->first();

            // advanced stats
            $totalIndicators = count($formIds);
            $daysPassed = min($start->diffInDays(now()) + 1, $start->diffInDays($end) + 1);
            $expectedReports = $totalIndicators * $daysPassed;
            $actualReports = DailyReportResponse::query()
                ->whereBetween('report_date', [$start, min(now(), $end)])
                ->where('unit_kerja_id', $unit->id)
                ->whereIn('form_template_id', $formIds)
                ->count();

            $completionRate = $expectedReports > 0
                ? round(($actualReports / $expectedReports) * 100, 1)
                : 0;

            $complianceRate = $actualReports > 0
                ? round(($perfectCount / $actualReports) * 100, 1)
                : 0;

            // daily trend last 7 days
            $trend = [];
            $base = now()->subDays(6);
            for ($i = 0; $i < 7; $i++) {
                $d = $base->copy()->addDays($i)->toDateString();
                $trend[$d] = DailyReportResponse::query()
                    ->whereDate('report_date', $d)
                    ->where('unit_kerja_id', $unit->id)
                    ->whereIn('form_template_id', $formIds)
                    ->count();
            }

            // recent reports list
            $recent = DailyReportResponse::with(['formTemplate'])
                ->where('unit_kerja_id', $unit->id)
                ->whereBetween('report_date', [$start, $end])
                ->latest('created_at')
                ->limit(5)
                ->get();

            $summaries[] = [
                'unit_id' => $unit->id,
                'unit_name' => $unit->unit_name,
                'today' => $todayCount,
                'perfect' => $perfectCount,
                'last_submission' => $last?->created_at,
                'total_indicators' => $totalIndicators,
                'expected_reports' => $expectedReports,
                'actual_reports' => $actualReports,
                'completion_rate' => $completionRate,
                'compliance_rate' => $complianceRate,
                'trend' => $trend,
                'recent_reports' => $recent,
            ];
        }

        return $summaries;
    }
}
