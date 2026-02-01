<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

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

    protected static string $view = 'filament.resources.daily-report-entry-resource.pages.list-daily-report-entries-original';

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

    // Loading states
    public bool $loadingMatrix = false;
    public bool $loadingSlideOver = false;

    // Slide over properties (used by view even if not all pages use them)
    public bool $slideOverOpen = false;
    public ?int $selectedIndicatorId = null;
    public array $selectedIndicatorData = [];
    public $dailyReports = [];
    public string $filterPeriod = 'today';

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
        $this->slideOverService = new SlideOverService();
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
            $reports = $this->getReportsQuery($startDate, $endDate)->get();

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
