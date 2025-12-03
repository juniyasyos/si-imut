<?php

namespace App\Filament\Pages;

use App\Models\FormHeader;
use App\Models\DailyReportEntry;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class DailyReportDashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string $view = 'filament.pages.daily-report-dashboard';

    protected static ?string $navigationLabel = 'Laporan Harian';

    protected static ?string $title = 'Laporan Harian';

    protected static ?string $navigationGroup = 'Quality Indicators';

    protected static ?int $navigationSort = 1;

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
        $indicators = FormHeader::with('imutdata')
            ->whereHas('imutdata', function ($query) use ($unitKerjaIds) {
                $query->whereHas('unitKerja', function ($q) use ($unitKerjaIds) {
                    $q->whereIn('unit_kerja.id', $unitKerjaIds);
                });
            })
            ->get();

        $this->indicatorStats = $indicators->map(function ($formHeader) use ($unitKerjaIds) {
            $totalEntries = DailyReportEntry::where('form_header_id', $formHeader->id)
                ->whereIn('unit_kerja_id', $unitKerjaIds)
                ->count();

            $thisMonthEntries = DailyReportEntry::where('form_header_id', $formHeader->id)
                ->whereIn('unit_kerja_id', $unitKerjaIds)
                ->whereMonth('report_date', now()->month)
                ->whereYear('report_date', now()->year)
                ->count();

            $thisWeekEntries = DailyReportEntry::where('form_header_id', $formHeader->id)
                ->whereIn('unit_kerja_id', $unitKerjaIds)
                ->whereBetween('report_date', [now()->startOfWeek(), now()->endOfWeek()])
                ->count();

            $lastEntry = DailyReportEntry::where('form_header_id', $formHeader->id)
                ->whereIn('unit_kerja_id', $unitKerjaIds)
                ->latest('created_at')
                ->first();

            $activePeriods = DailyReportEntry::where('form_header_id', $formHeader->id)
                ->whereIn('unit_kerja_id', $unitKerjaIds)
                ->selectRaw('COUNT(DISTINCT DATE_FORMAT(report_date, "%Y-%m")) as months')
                ->value('months') ?? 0;

            return [
                'id' => $formHeader->id,
                'slug' => $formHeader->imutdata->slug ?? $formHeader->id,
                'title' => $formHeader->imutdata->title ?? $formHeader->title,
                'description' => $formHeader->description,
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
