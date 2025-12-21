<?php

namespace App\Filament\Pages;

use App\Models\FormTemplate;
use App\Models\DailyReportEntry;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class DailyReportDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string $view = 'filament.resources.daily-report.pages.daily-report-dashboard';

    protected static ?string $navigationLabel = 'Dashboard Laporan';

    protected static ?string $title = 'Dashboard Laporan Harian';

    protected static ?string $navigationGroup = 'Quality Indicators';

    protected static ?int $navigationSort = 0;

    protected static bool $shouldRegisterNavigation = false;

    public array $indicatorStats = [];
    public function mount(): void
    {
        $this->loadIndicatorStats();
    }

    public function loadIndicatorStats(): void
    {
        $user = Auth::user();

        // Get user's unit IDs (they might have multiple units)
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();

        if (empty($unitKerjaIds)) {
            $this->indicatorStats = [];
            return;
        }

        // Get all form headers where the imutdata is assigned to user's units
        $indicators = FormTemplate::with('imutdata')
            ->whereHas('imutdata', function ($query) use ($unitKerjaIds) {
                $query->whereHas('unitKerja', function ($q) use ($unitKerjaIds) {
                    $q->whereIn('unit_kerja.id', $unitKerjaIds);
                });
            })
            ->get();

        $this->indicatorStats = $indicators->map(function ($formTemplate) use ($unitKerjaIds) {
            $totalEntries = DailyReportEntry::where('form_header_id', $formTemplate->id)
                ->whereIn('unit_kerja_id', $unitKerjaIds)
                ->count();

            $thisMonthEntries = DailyReportEntry::where('form_header_id', $formTemplate->id)
                ->whereIn('unit_kerja_id', $unitKerjaIds)
                ->whereMonth('report_date', now()->month)
                ->whereYear('report_date', now()->year)
                ->count();

            $thisWeekEntries = DailyReportEntry::where('form_header_id', $formTemplate->id)
                ->whereIn('unit_kerja_id', $unitKerjaIds)
                ->whereBetween('report_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();

            $lastEntry = DailyReportEntry::where('form_header_id', $formTemplate->id)
                ->whereIn('unit_kerja_id', $unitKerjaIds)
                ->latest('created_at')
                ->first();

            $activePeriods = DailyReportEntry::where('form_header_id', $formTemplate->id)
                ->whereIn('unit_kerja_id', $unitKerjaIds)
                ->selectRaw('COUNT(DISTINCT DATE_FORMAT(report_date, "%Y-%m")) as months')
                ->value('months') ?? 0;

            return [
                'id' => $formTemplate->id,
                'slug' => $formTemplate->imutdata->slug ?? $formTemplate->id,
                'title' => $formTemplate->imutdata->title ?? $formTemplate->title,
                'description' => $formTemplate->description,
                'total_entries' => $totalEntries,
                'this_month' => $thisMonthEntries,
                'this_week' => $thisWeekEntries,
                'last_entry_date' => $lastEntry?->report_date?->format('d M Y'),
                'last_entry_time' => $lastEntry?->created_at?->format('H:i'),
                'active_periods' => $activePeriods,
            ];
        })->toArray();
    }

    public static function canAccess(): bool
    {
        $user = Auth::user();
        return $user->hasRole('Unit Kerja') && $user->unitKerjas()->exists();
    }
}
