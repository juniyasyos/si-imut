<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Models\ImutCategory;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use App\Services\DailyReport\MatrixDataService;
use App\Services\DailyReport\SlideOverService;
use App\Traits\DailyReport\NavigationTrait;
use App\Traits\DailyReport\ReportManagementTrait;
use Carbon\Carbon;
use Filament\Resources\Pages\Page;
use Livewire\Attributes\Url;

abstract class BaseDailyReportMonitoring extends Page
{
    use NavigationTrait;
    use ReportManagementTrait;

    protected string $view = 'filament.resources.daily-report-entry-resource.pages.list-daily-report-entries-original';

    protected MatrixDataService $matrixService;
    protected SlideOverService $slideOverService;

    // Page mode
    public bool $isMonitoringMode = false;

    // Matrix properties
    #[Url]
    public string $selectedMonth;

    public ?string $selectedDate = null;
    public array $indicators = [];
    public array $matrixData = [];
    public array $daysInMonth = [];
    public array $monitoringTemplates = [];

    // Category info pulled from database – used by the frontend to render
    public array $imutCategories = [];
    public array $categoryColors = [];

    // Loading states
    public bool $loadingMatrix = false;
    public bool $loadingSlideOver = false;

    // Slide over properties (used by view even if not all pages use them)
    public bool $slideOverOpen = false;
    public ?int $selectedIndicatorId = null;
    public array $selectedIndicatorData = [];
    public $dailyReports = [];
    public string $filterPeriod = 'today';

    // Bulk selection
    public array $selectedReports = [];

    // Form slide over properties
    public bool $formSlideOverOpen = false;
    public $formTemplate = null;
    public array $reportData = [];

    // Listeners
    protected $listeners = [
        'dateSelected' => 'handleDateSelected',
    ];

    public function boot(): void
    {
        $this->matrixService = new MatrixDataService();
        $this->slideOverService = app(SlideOverService::class);

        // load category list and pre‑compute CSS classes for each
        $this->imutCategories = ImutCategory::query()
            ->orderBy('id')
            ->get(['id', 'category_name'])
            ->map(function ($c) {
                return [
                    'id' => $c->id,
                    'name' => $c->category_name,
                ];
            })
            ->toArray();

        $this->categoryColors = collect($this->imutCategories)
            ->mapWithKeys(function ($c) {
                return [
                    $c['name'] => $this->getCategoryColorClass($c['id']),
                ];
            })->toArray();
    }

    public function bootBase(): void
    {
        $this->selectedMonth = $this->selectedMonth ?? now()->format('Y-m');
        $this->selectedDate = now()->format('Y-m-d');
    }

    /**
     * Load matrix data - can be overridden if needed custom logic
     */
    public function loadMatrixData(): void
    {
        if ($this->shouldUseMatrixService()) {
            $this->loadMatrixFromService();
        } else {
            $this->loadMatrixManually();
        }
    }

    /**
     * Use MatrixDataService for loading (default for ListDailyReportEntries)
     */
    protected function loadMatrixFromService(): void
    {
        $result = $this->matrixService->loadMatrixData($this->selectedMonth);

        $this->indicators = $result['indicators'];
        $this->matrixData = $result['matrixData'];
        $this->daysInMonth = $result['daysInMonth'];

        // ensure color map includes any categories returned by the service
        foreach ($this->indicators as $indicator) {
            if (!empty($indicator['category']) && !isset($this->categoryColors[$indicator['category']])) {
                // compute color by id if available
                $id = $indicator['category_id'] ?? null;
                $this->categoryColors[$indicator['category']] = $this->getCategoryColorClass($id);
            }
        }
    }

    /**
     * Load matrix manually for specific unit (for MonitoringUnitDetail)
     */
    protected function loadMatrixManually(): void
    {
        $this->loadingMatrix = true;

        try {
            $startDate = Carbon::parse($this->selectedMonth . '-01')->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();

            // Get reports with custom filter
            $reportsQueryOrCollection = $this->getReportsQuery($startDate, $endDate);
            if ($reportsQueryOrCollection instanceof Arrayable || $reportsQueryOrCollection instanceof Collection) {
                $reports = $reportsQueryOrCollection instanceof Collection ? $reportsQueryOrCollection : collect($reportsQueryOrCollection->toArray());
            } else {
                $reports = $reportsQueryOrCollection->get();
            }

            // Build matrix data with aggregation for multiple reports per day
            $matrix = [];
            $grouped = $reports->groupBy(function ($report) {
                $day = Carbon::parse($report->report_date)->day;
                return $report->form_template_id . '-' . $day;
            });

            foreach ($grouped as $key => $dayReports) {
                [$indicatorId, $day] = explode('-', $key);

                $totalCount = $dayReports->count();
                $compliantCount = $dayReports->where('compliance_status', true)->count();
                $compliancePercentage = $totalCount > 0 ? round(($compliantCount / $totalCount) * 100, 1) : 0;

                // Determine cell state based on compliance
                if ($compliancePercentage >= 100) {
                    $cellState = 'perfect';
                } elseif ($compliancePercentage >= 80) {
                    $cellState = 'good';
                } else {
                    $cellState = 'poor';
                }

                if (!isset($matrix[$indicatorId])) {
                    $matrix[$indicatorId] = [];
                }

                $matrix[$indicatorId][$day] = [
                    'cell_state' => $cellState,
                    'count' => $totalCount,
                    'compliance_percentage' => $compliancePercentage,
                    'compliant_count' => $compliantCount,
                    'date' => $dayReports->first()->report_date->format('Y-m-d'),
                ];
            }

            $this->matrixData = $matrix;

            // Generate days in month
            $date = Carbon::parse($this->selectedMonth . '-01');
            $this->daysInMonth = range(1, $date->daysInMonth);

            // Load indicators
            $this->loadIndicators($startDate, $endDate);
        } finally {
            $this->loadingMatrix = false;
        }
    }

    /**
     * Determine cell state based on score and compliance
     */

    /**
     * Helper used by frontend to assign a consistent tailwind badge class per
     * category.  We mirror the simple palette that previously lived in the
     * javascript so that both blade + livewire and alpine versions stay in
     * sync.  If the color scheme ever needs to be customised per-category the
     * logic can be extended here or the category table can gain a column.
     *
     * @param int|null $categoryId
     * @return string
     */
    protected function getCategoryColorClass(?int $categoryId): string
    {
        $colors = [
            'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
            'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
            'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
            'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
            'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
            'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400',
        ];
        if (!is_numeric($categoryId)) {
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
        }
        return $colors[$categoryId % count($colors)];
    }
    protected function determineCellState($report): string
    {
        if ($report->compliance_status || $report->total_score >= 100) {
            return 'perfect';
        } elseif ($report->total_score >= 80) {
            return 'good';
        }
        return 'poor';
    }

    /**
     * Should use MatrixDataService or manual loading?
     * Override this in child class
     */
    protected function shouldUseMatrixService(): bool
    {
        return true; // Default: use service
    }

    /**
     * Get indicator key from report (usually form_template_id)
     */
    protected function getIndicatorKey($report): int
    {
        return $report->form_template_id;
    }

    /**
     * Open slide over
     */
    public function openSlideOver(int $indicatorId, ?string $date = null): void
    {
        $validatedDate = $this->slideOverService->validateDate($date);

        $this->selectedIndicatorId = $indicatorId;
        $this->selectedDate = $validatedDate;

        // Load indicator data
        $this->selectedIndicatorData = $this->slideOverService->getSelectedIndicatorData($indicatorId, $this->indicators);

        // Load daily reports for this indicator and date
        $this->loadDailyReports();
        $this->slideOverOpen = true;
    }

    /**
     * Close slide over
     */
    public function closeSlideOver(): void
    {
        $this->slideOverOpen = false;
        $this->selectedIndicatorId = null;
        $this->selectedIndicatorData = [];
        $this->dailyReports = [];
        $this->selectedReports = [];
    }

    /**
     * Load daily reports for selected indicator and date
     */
    public function loadDailyReports(): void
    {
        if (!$this->selectedIndicatorId || !$this->selectedDate) {
            $this->dailyReports = [];
            return;
        }

        $this->dailyReports = $this->slideOverService->loadDailyReports($this->selectedIndicatorId, $this->selectedDate);
    }

    /**
     * Get reports query - override in child class for custom filter
     */
    abstract protected function getReportsQuery($startDate, $endDate);

    /**
     * Load indicators - override in child class for custom filter
     */
    abstract protected function loadIndicators($startDate, $endDate): void;
}
