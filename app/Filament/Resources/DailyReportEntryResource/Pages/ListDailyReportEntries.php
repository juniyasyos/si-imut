<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Filament\Resources\DailyReportEntryResource;
use App\Models\DailyReportEntry;
use App\Models\FormTemplate;
use Carbon\Carbon;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListDailyReportEntries extends ListRecords
{
    protected static string $resource = DailyReportEntryResource::class;

    protected static string $view = 'filament.resources.daily-report-entry-resource.pages.list-daily-report-entries';

    // Matrix properties
    public string $selectedMonth;
    public array $indicators = [];
    public array $matrixData = [];
    public array $daysInMonth = [];

    // Slide over properties
    public bool $slideOverOpen = false;
    public ?int $selectedIndicatorId = null;
    public ?string $selectedDate = null;
    public array $selectedIndicatorData = [];

    /**
     * Mount the component
     */
    public function mount(): void
    {
        parent::mount();
        $this->selectedMonth = now()->format('Y-m');
        $this->loadMatrixData();
    }

    /**
     * Get the page title
     */
    public function getTitle(): string
    {
        return 'Laporan Harian';
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
        $monthName = Carbon::parse($this->selectedMonth . '-01')->translatedFormat('F Y');

        return "{$unitName} - {$monthName}";
    }

    /**
     * Load matrix data for selected month
     */
    public function loadMatrixData(): void
    {
        $user = Auth::user();

        if (!$user) {
            return;
        }

        /** @var \App\Models\User $user */
        $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();

        if (empty($unitKerjaIds)) {
            return;
        }

        // Get indicators for user's units (use modern FormTemplate)
        $this->indicators = FormTemplate::with('imutdata.categories')
            ->whereHas('imutdata', function ($query) use ($unitKerjaIds) {
                $query->whereHas('unitKerja', function ($q) use ($unitKerjaIds) {
                    $q->whereIn('unit_kerja.id', $unitKerjaIds);
                });
            })
            ->get()
            ->map(function ($formTemplate) {
                return [
                    'id' => $formTemplate->id,
                    'title' => $formTemplate->imutdata->title ?? $formTemplate->title,
                    'category' => $formTemplate->imutdata->categories->title ?? null,
                ];
            })
            ->toArray();

        // Calculate days in selected month
        $date = Carbon::parse($this->selectedMonth . '-01');
        $daysCount = $date->daysInMonth;
        $this->daysInMonth = range(1, $daysCount);

        // Get all entries for selected month
        $startDate = $date->startOfMonth()->format('Y-m-d');
        $endDate = $date->endOfMonth()->format('Y-m-d');

        $entries = DailyReportEntry::whereIn('unit_kerja_id', $unitKerjaIds)
            ->whereBetween('report_date', [$startDate, $endDate])
            ->get()
            ->groupBy(function ($entry) {
                $indicatorId = $entry->form_template_id ?? $entry->form_template_id;
                return $indicatorId . '_' . $entry->report_date->format('Y-m-d');
            });

        // Build matrix data
        $this->matrixData = [];
        foreach ($this->indicators as $indicator) {
            foreach ($this->daysInMonth as $day) {
                $dateStr = $date->copy()->day($day)->format('Y-m-d');
                $key = $indicator['id'] . '_' . $dateStr;

                $dayEntries = $entries->has($key) ? $entries[$key] : collect();
                $entryData = $dayEntries->map(function ($entry) {
                    return [
                        'id' => $entry->id,
                        'numerator' => $entry->data['numerator'] ?? 0,
                        'denominator' => $entry->data['denominator'] ?? 0,
                    ];
                })->toArray();

                $this->matrixData[$indicator['id']][$day] = [
                    'date' => $dateStr,
                    'has_data' => $dayEntries->isNotEmpty(),
                    'count' => $dayEntries->count(),
                    'entries' => $entryData,
                ];
            }
        }
    }

    /**
     * Get cell state for rendering
     */
    public function getCellState(int $indicatorId, int $day): string
    {
        $cellData = $this->matrixData[$indicatorId][$day] ?? null;

        if (!$cellData) {
            return 'disabled';
        }

        $cellDate = Carbon::parse($cellData['date']);
        $today = now()->startOfDay();
        $sixDaysAgo = now()->subDays(6)->startOfDay();

        // Use rolling 7-day window (today and previous 6 days) for editable range
        // (cells older than 6 days before today are considered overdue)

        // Future date
        if ($cellDate->isAfter($today)) {
            return 'disabled';
        }

        // Has data
        if ($cellData['has_data']) {
            return 'done';
        }

        // Too old (more than 6 days ago)
        if ($cellDate->isBefore($sixDaysAgo)) {
            return 'overdue';
        }

        // Can input (within 7 days)
        return 'pending';
    }

    /**
     * Check if day is today
     */
    public function isToday(int $day): bool
    {
        $date = Carbon::parse($this->selectedMonth . '-01')->day($day);
        return $date->isToday();
    }

    /**
     * Check if can go to next month
     */
    public function canGoNextMonth(): bool
    {
        $currentMonth = Carbon::parse($this->selectedMonth . '-01');
        $thisMonth = now()->startOfMonth();
        return $currentMonth->isBefore($thisMonth);
    }

    /**
     * Get cell summary data
     */
    public function getCellSummary(int $indicatorId, int $day): ?array
    {
        $cellData = $this->matrixData[$indicatorId][$day] ?? null;

        if (!$cellData || !$cellData['has_data']) {
            return null;
        }

        // Get entries for this indicator and date
        $entries = $cellData['entries'] ?? [];

        if (empty($entries)) {
            return null;
        }

        // Calculate totals
        $totalNum = 0;
        $totalDenum = 0;
        $count = count($entries);

        foreach ($entries as $entry) {
            $totalNum += $entry['numerator'] ?? 0;
            $totalDenum += $entry['denominator'] ?? 0;
        }

        $percentage = $totalDenum > 0 ? round(($totalNum / $totalDenum) * 100, 1) : 0;

        return [
            'count' => $count,
            'numerator' => $totalNum,
            'denominator' => $totalDenum,
            'percentage' => $percentage,
        ];
    }

    /**
     * Open slide over
     */
    public function openSlideOver(int $indicatorId, string $date): void
    {
        $this->selectedIndicatorId = $indicatorId;
        $this->selectedDate = $date;

        // Load indicator data
        $indicator = collect($this->indicators)->firstWhere('id', $indicatorId);
        $this->selectedIndicatorData = $indicator ?? [];

        $this->slideOverOpen = true;
    }

    /**
     * Close slide over
     */
    public function closeSlideOver(): void
    {
        $this->slideOverOpen = false;
        $this->selectedIndicatorId = null;
        $this->selectedDate = null;
        $this->selectedIndicatorData = [];
    }

    /**
     * Navigate to previous month
     */
    public function previousMonth(): void
    {
        $date = Carbon::parse($this->selectedMonth . '-01')->subMonth();
        $this->selectedMonth = $date->format('Y-m');
        $this->loadMatrixData();
    }

    /**
     * Navigate to next month
     */
    public function nextMonth(): void
    {
        // Only allow navigation up to current month
        if (!$this->canGoNextMonth()) {
            return;
        }

        $date = Carbon::parse($this->selectedMonth . '-01')->addMonth();
        $this->selectedMonth = $date->format('Y-m');
        $this->loadMatrixData();
    }

    /**
     * Get header actions
     */
    protected function getHeaderActions(): array
    {
        return [];
    }
}
