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
    public string $filterPeriod = 'today';
    public ?int $filterIndicator = null;

    public function mount(): void
    {
        $this->filterPeriod = 'today'; // Default filter
        $this->loadIndicatorStats();
    }

    public function loadIndicatorStats(): void
    {
        $user = Auth::user();
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();

        if (empty($unitKerjaIds)) {
            $this->indicatorStats = [];
            return;
        }

        $indicators = FormTemplate::with('imutdata')
            ->whereHas('imutdata', function ($query) use ($unitKerjaIds) {
                $query->whereHas('unitKerja', function ($q) use ($unitKerjaIds) {
                    $q->whereIn('unit_kerja.id', $unitKerjaIds);
                });
            })
            ->get();

        $this->indicatorStats = $indicators->map(function ($formTemplate) use ($unitKerjaIds) {
            $query = DailyReportEntry::where(function ($q) use ($formTemplate) {
                $q->where('form_template_id', $formTemplate->id);
            })
                ->whereIn('unit_kerja_id', $unitKerjaIds);

            if ($this->filterPeriod === 'today') {
                $query->whereDate('report_date', now());
            } elseif ($this->filterPeriod === 'weekly') {
                $query->whereBetween('report_date', [now()->startOfWeek(), now()->endOfWeek()]);
            } elseif ($this->filterPeriod === 'monthly') {
                $query->whereMonth('report_date', now()->month)->whereYear('report_date', now()->year);
            }

            $totalEntries = $query->count();

            $lastEntry = $query->latest('created_at')->first();

            $activePeriods = DailyReportEntry::where(function ($q) use ($formTemplate) {
                $q->where('form_template_id', $formTemplate->id);
            })
                ->whereIn('unit_kerja_id', $unitKerjaIds)
                ->selectRaw('COUNT(DISTINCT DATE_FORMAT(report_date, "%Y-%m")) as months')
                ->value('months') ?? 0;

            return [
                'id' => $formTemplate->id,
                'slug' => $formTemplate->imutdata->slug ?? $formTemplate->id,
                'title' => $formTemplate->imutdata->title ?? $formTemplate->title,
                'description' => $formTemplate->description,
                'total_entries' => $totalEntries,
                'last_entry_date' => $lastEntry?->report_date?->format('d M Y'),
                'last_entry_time' => $lastEntry?->created_at?->format('H:i'),
                'active_periods' => $activePeriods,
            ];
        })->toArray();
    }

    // public static function canAccess(): bool
    // {
    //     $user = Auth::user();
    //     return $user->hasRole('Unit Kerja') && $user->unitKerjas()->exists();
    // }
}
