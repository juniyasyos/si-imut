<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Filament\Resources\DailyReportEntryResource;
use App\Models\DailyReportEntry;
use App\Models\FormHeader;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListDailyReportEntries extends ListRecords
{
    protected static string $resource = DailyReportEntryResource::class;

    protected static string $view = 'filament.resources.daily-report-entry-resource.pages.list-daily-report-entries';

    public array $indicatorStats = [];

    /**
     * Mount the component
     */
    public function mount(): void
    {
        parent::mount();
        $this->loadIndicatorStats();
    }

    /**
     * Get the page title
     */
    public function getTitle(): string
    {
        return 'Dashboard Laporan Harian';
    }

    /**
     * Get page subheading
     */
    public function getSubheading(): ?string
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        /** @var \App\Models\User $user */
        $unitName = $user->unitKerjas()->first()->unit_name ?? 'Unit Kerja';
        return "{$unitName} - Periode: " . now()->translatedFormat('F Y');
    }

    /**
     * Load indicator statistics
     */
    public function loadIndicatorStats(): void
    {
        $user = Auth::user();

        if (!$user) {
            $this->indicatorStats = [];
            return;
        }

        /** @var \App\Models\User $user */
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();

        if (empty($unitKerjaIds)) {
            $this->indicatorStats = [];
            return;
        }

        // Get all form headers where the imutdata is assigned to user's units
        $indicators = FormHeader::with('imutdata.categories')
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
                ->thisMonth()
                ->count();

            $thisWeekEntries = DailyReportEntry::where('form_header_id', $formHeader->id)
                ->whereIn('unit_kerja_id', $unitKerjaIds)
                ->thisWeek()
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
                'category' => $formHeader->imutdata->imutKategori->title ?? null,
                'description' => $formHeader->description,
                'total_entries' => $totalEntries,
                'this_month' => $thisMonthEntries,
                'this_week' => $thisWeekEntries,
                'last_entry_date' => $lastEntry?->report_date?->translatedFormat('d M Y'),
                'last_entry_time' => $lastEntry?->created_at?->format('H:i'),
                'active_periods' => $activePeriods,
            ];
        })->toArray();
    }

    /**
     * Get header actions
     */
    protected function getHeaderActions(): array
    {
        return [
            // Create hanya melalui card indikator
        ];
    }
}
