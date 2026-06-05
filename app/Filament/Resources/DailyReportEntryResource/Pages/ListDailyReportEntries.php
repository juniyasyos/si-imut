<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Filament\Resources\DailyReportEntryResource;
use App\Models\DailyReportResponse;
use App\Services\DailyReport\DailyReportMonitoringService;
use App\Services\DailyReport\SlideOverService;
use App\Traits\DailyReport\FormHandlerTrait;
use App\Traits\DailyReport\DebugTrait;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ListDailyReportEntries extends BaseDailyReportMonitoring implements HasForms
{
    use InteractsWithForms;
    use FormHandlerTrait;
    use DebugTrait;

    protected static string $resource = DailyReportEntryResource::class;

    private DailyReportMonitoringService $monitoringService;

    // ========================================
    // PUBLIC PROPERTIES: Report Counts & Filtering
    // ========================================
    public array $reportCounts = [];
    public array $filteredIndicators = [];

    public function __construct()
    {
        $this->monitoringService = app(DailyReportMonitoringService::class);
    }

    public function boot(): void
    {
        parent::boot();
    }

    public function mount(): void
    {
        $this->bootBase();
        // If URL contains `selectedDate` and/or `selectedMonth` query parameters, initialize page state from them
        $reqDate = request()->query('selectedDate');
        $reqMonth = request()->query('selectedMonth');

        \Illuminate\Support\Facades\Log::info('ListDailyReportEntries mount', [
            'reqDate' => $reqDate,
            'reqMonth' => $reqMonth,
            'url' => request()->url(),
        ]);

        if ($reqMonth) {
            try {
                $validMonth = Carbon::createFromFormat('Y-m', $reqMonth)->format('Y-m');
                $this->selectedMonth = $validMonth;
                \Illuminate\Support\Facades\Log::info('Mount: selectedMonth set', ['selectedMonth' => $validMonth]);
            } catch (\Exception $e) {
                // ignore invalid month formats
                \Illuminate\Support\Facades\Log::warning('Mount: invalid month format', ['reqMonth' => $reqMonth]);
            }
        }

        if ($reqDate) {
            try {
                $validDate = Carbon::createFromFormat('Y-m-d', $reqDate)->format('Y-m-d');
                $this->selectedDate = $validDate;
                $this->selectedMonth = Carbon::createFromFormat('Y-m-d', $validDate)->format('Y-m');
                \Illuminate\Support\Facades\Log::info('Mount: selectedDate set', ['selectedDate' => $validDate, 'selectedMonth' => $this->selectedMonth]);
            } catch (\Exception $e) {
                // ignore invalid date formats
                \Illuminate\Support\Facades\Log::warning('Mount: invalid date format', ['reqDate' => $reqDate]);
            }
        }

        // Ensure selectedDate always has a value (default: today)
        if (!$this->selectedDate) {
            $this->selectedDate = now()->format('Y-m-d');
            $this->selectedMonth = now()->format('Y-m');
            \Illuminate\Support\Facades\Log::info('Mount: set defaults', ['selectedDate' => $this->selectedDate, 'selectedMonth' => $this->selectedMonth]);
        }

        \Illuminate\Support\Facades\Log::info('Mount final state', [
            'selectedDate' => $this->selectedDate,
            'selectedMonth' => $this->selectedMonth,
        ]);

        // Read view from URL query parameter
        $requestedView = request()->query('view', 'input');
        if (in_array($requestedView, ['input', 'monitoring'])) {
            $this->currentView = $requestedView;
        }

        \Illuminate\Support\Facades\Log::info('📋 [Page Init] View and period loaded', [
            'view' => $this->currentView,
            'month' => $this->selectedMonth,
            'date' => $this->selectedDate,
            'url' => request()->fullUrl(),
        ]);

        \Log::info('📦 [DailyReport] Data load plan', [
            'source' => 'mount',
            'selectedMonth' => $this->selectedMonth,
            'selectedDate' => $this->selectedDate,
            'view' => $this->currentView,
            'loadMatrix' => true,
            'loadMonitoring' => $this->currentView === 'monitoring',
        ]);

        $this->loadMatrixData();

        $this->loadAllReportCounts();

        // Compute filtered indicators for display
        $this->computeFilteredIndicators();

        \Log::info('📦 [DailyReport] Matrix data loaded', [
            'source' => 'mount',
            'selectedMonth' => $this->selectedMonth,
            'indicators_count' => count($this->indicators),
            'matrix_rows_count' => count($this->matrixData),
            'daysInMonth_count' => count($this->daysInMonth),
            'daysWithData_count' => count($this->daysWithData),
        ]);

        if ($this->currentView === 'monitoring') {
            $this->loadMonitoringTemplates();
        } else {
            \Log::info('📦 [DailyReport] Monitoring data skipped', [
                'source' => 'mount',
                'selectedMonth' => $this->selectedMonth,
                'view' => $this->currentView,
            ]);
        }

        $this->checkAndOpenSlideOverFromUrl();
    }

    // Use MatrixDataService (default behavior from base class)
    protected function shouldUseMatrixService(): bool
    {
        return true;
    }

    // Dummy implementations - not used when shouldUseMatrixService() returns true
    protected function getReportsQuery($startDate, $endDate)
    {
        return null;
    }

    protected function loadIndicators($startDate, $endDate): void
    {
        // Not used when using MatrixDataService
    }

    /**
     * Check URL parameters and auto-open slide-over if specified
     */
    protected function checkAndOpenSlideOverFromUrl(): void
    {
        $request = request();
        $indicatorId = $request->query('indicator_id');
        $date = $request->query('date');

        if ($indicatorId && $date) {
            try {
                $validDate = Carbon::createFromFormat('Y-m-d', $date)->format('Y-m-d');

                if ($this->slideOverService->validateIndicator((int) $indicatorId, $this->indicators)) {
                    $this->selectedMonth = Carbon::createFromFormat('Y-m-d', $validDate)->format('Y-m');
                    $this->loadMatrixData();
                    $this->openSlideOver((int) $indicatorId, $validDate);
                }
            } catch (\Exception $e) {
                Log::warning('Invalid date format in URL parameter', ['date' => $date]);
            }
        }
    }

    /**
     * Get the page title
     */
    public function getTitle(): string
    {
        return '';
    }

    /**
     * Get page subheading
     */
    public function getSubheading(): ?string
    {
        return "";
    }

    /**
     * Open slide over (with URL update for this page)
     */
    public function openSlideOver(int $indicatorId, ?string $date = null): void
    {
        parent::openSlideOver($indicatorId, $date);

        $validatedDate = $this->selectedDate;

        // Log::info('OpenSlideOver called', ['indicator_id' => $indicatorId, 'date' => $validatedDate]);

        // Update URL immediately - avoid setTimeout that disrupts Livewire reactivity
        $this->js("
            const newUrl = window.location.pathname + '?indicator_id={$indicatorId}&date={$validatedDate}';
            console.log('🔗 Updating URL to:', newUrl);
            window.history.replaceState({}, '', newUrl);
        ");
    }

    /**
     * Close slide over and clean URL
     */
    public function closeSlideOver(): void
    {
        parent::closeSlideOver();

        $selectedMonth = json_encode($this->selectedMonth);
        $selectedDate = json_encode($this->selectedDate);
        $view = json_encode($this->currentView);

        // Restore the filtered list URL immediately using the browser URL,
        // not the Livewire request endpoint.
        $this->js("
            const params = new URLSearchParams();
            params.set('selectedMonth', {$selectedMonth});
            params.set('selectedDate', {$selectedDate});
            " . ($this->currentView !== 'input' ? "params.set('view', {$view});" : "") . "

            const cleanUrl = window.location.pathname + '?' + params.toString();
            console.log('🔗 Cleaning URL to:', cleanUrl);
            window.history.replaceState({}, '', cleanUrl);
        ");
    }



    /**
     * Get real indicator status from database - used by status indicator
     */
    public function getRealIndicatorStatus(int $indicatorId, string $date): array
    {
        return $this->matrixService->getRealIndicatorStatus($indicatorId, $date);
    }

    /**
     * Generate URL for specific indicator and date
     */
    public static function getUrlForIndicator(int $indicatorId, string $date): string
    {
        $baseUrl = static::getUrl();
        return SlideOverService::getUrlForIndicator($indicatorId, $date, $baseUrl);
    }

    /**
     * Get the forms for this component
     */
    public function getForms(): array
    {
        return [
            'reportEntryForm',
        ];
    }

    /**
     * Get header actions
     */
    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * Load monitoring templates data for monthly period
     * Delegates to service layer for business logic
     */
    protected function loadMonitoringTemplates(): void
    {
        $user = Auth::user();

        \Log::info('📦 [DailyReport] Loading monitoring data', [
            'source' => 'loadMonitoringTemplates',
            'selectedMonth' => $this->selectedMonth,
            'selectedDate' => $this->selectedDate,
            'view' => $this->currentView,
        ]);

        $this->monitoringTemplates = $this->monitoringService->loadMonitoringTemplates(
            $user,
            $this->selectedMonth
        );
        $this->monitoringTemplatesLoadedForMonth = $this->selectedMonth;

        \Log::info('📊 [Monitoring] Templates loaded', [
            'month' => $this->selectedMonth,
            'count' => count($this->monitoringTemplates),
            'user_id' => $user->id,
            'timestamp' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Change current view and update URL
     * 
     * @param string $view 'input' or 'monitoring'
     */
    public function changeView(string $view): void
    {
        if (!in_array($view, ['input', 'monitoring'])) {
            return;
        }

        // Simply update the property - #[Url] binding will handle URL update automatically
        $this->currentView = $view;

        \Log::info('📦 [DailyReport] View changed', [
            'source' => 'changeView',
            'view' => $view,
            'selectedMonth' => $this->selectedMonth,
            'monitoringAlreadyLoadedForMonth' => $this->monitoringTemplatesLoadedForMonth,
            'willLoadMonitoring' => $view === 'monitoring' && $this->monitoringTemplatesLoadedForMonth !== $this->selectedMonth,
        ]);

        if ($view === 'monitoring' && $this->monitoringTemplatesLoadedForMonth !== $this->selectedMonth) {
            $this->loadMonitoringTemplates();
        }
    }

    /**
     * Update selected month and refresh monitoring data
     * Automatically updates URL via #[Url] binding
     * 
     * @param string $month Format: Y-m (e.g., 2026-06)
     */
    public function selectMonth(string $month): void
    {
        // Validate month format
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            \Log::warning('📅 [Monitoring] Invalid month format provided', ['month' => $month]);
            return;
        }

        // Parse and validate the date
        try {
            $date = Carbon::createFromFormat('Y-m', $month);
            $oldMonth = $this->selectedMonth;
            $this->selectedMonth = $month;

            \Log::info('📅 [Monitoring] Month changed', [
                'from' => $oldMonth,
                'to' => $month,
                'user_id' => Auth::id(),
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);

            \Log::info('📦 [DailyReport] Matrix data reload requested', [
                'source' => 'selectMonth',
                'selectedMonth' => $this->selectedMonth,
                'view' => $this->currentView,
                'loadMonitoring' => $this->currentView === 'monitoring',
            ]);

            if ($this->currentView === 'monitoring') {
                // Reload monitoring templates only when the monitoring tab is visible
                $this->loadMonitoringTemplates();

                \Log::debug('📅 [Monitoring] Templates loaded for month', [
                    'month' => $month,
                    'template_count' => count($this->monitoringTemplates)
                ]);
            }
        } catch (\Exception $e) {
            // Invalid date, do nothing
            \Log::error('📅 [Monitoring] Error changing month', [
                'month' => $month,
                'error' => $e->getMessage()
            ]);
            return;
        }
    }

    /**
     * View monitoring detail
     */
    public function viewMonitoringDetail(int $templateId): void
    {
        // Redirect to form template detail page
        $this->redirect(route('filament.admin.resources.form-templates.view', ['record' => $templateId]));
    }

    /**
     * View monitoring responses
     */
    public function viewMonitoringResponses(int $templateId): void
    {
        // Redirect to daily report entries filtered by template
        $this->redirect(route('filament.admin.resources.daily-report-entries.index', [
            'tableFilters' => [
                'form_template_id' => ['value' => $templateId]
            ]
        ]));
    }

    /**
     * Export monitoring data to Excel
     * Delegates to service layer
     */
    public function exportMonitoring(int $templateId, ?string $month = null): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $user = Auth::user();
            $currentMonth = $month ?? $this->monitoringMonth ?? now()->format('Y-m');

            return $this->monitoringService->exportMonitoring($user, $templateId, $currentMonth);
        } catch (\Exception $e) {
            Log::error('Export monitoring data failed', [
                'template_id' => $templateId,
                'month' => $currentMonth ?? 'unknown',
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Gagal export data: ' . $e->getMessage());
        }
    }

    /**
     * Load monitoring data for specific period (called from Alpine.js)
     * Delegates to service layer
     */
    public function loadMonitoringForPeriod(string $month): array
    {
        $user = Auth::user();
        return $this->monitoringService->loadMonitoringForPeriod($user, $month);
    }

    /**
     * Get report count for indicator on specific date (called from Alpine.js)
     * Delegates to service layer
     */
    public function getReportCountForIndicatorDate(int $indicatorId, string $date): int
    {
        return $this->monitoringService->getReportCountForIndicatorDate($indicatorId, $date);
    }

    /**
     * Get filtered indicators based on search query and status filter
     * Server-side filtering to improve performance
     * Updated whenever searchQuery or statusFilter changes
     */
    public function computeFilteredIndicators(): void
    {
        $filtered = collect($this->indicators);

        // Filter by search query (title or category)
        if ($this->searchQuery) {
            $query = strtolower($this->searchQuery);
            $filtered = $filtered->filter(function ($indicator) use ($query) {
                $titleMatch = str_contains(strtolower($indicator['title']), $query);
                $categoryMatch = isset($indicator['category']) && str_contains(strtolower($indicator['category']), $query);
                return $titleMatch || $categoryMatch;
            });
        }

        // Filter by status
        if ($this->statusFilter && $this->statusFilter !== 'all') {
            $date = new \DateTime($this->selectedDate);
            $day = (int) $date->format('d');

            $filtered = $filtered->filter(function ($indicator) use ($day) {
                $cellData = $this->matrixData[$indicator['id']][$day] ?? null;
                $state = $cellData ? $cellData['cell_state'] : 'disabled';
                return $state === $this->statusFilter;
            });
        }

        $this->filteredIndicators = $filtered->toArray();
    }

    /**
     * Update filtered indicators when search query changes
     * Livewire automatically calls this when searchQuery property updates
     */
    public function updatedSearchQuery(): void
    {
        $this->computeFilteredIndicators();
    }

    /**
     * Update filtered indicators when status filter changes
     * Livewire automatically calls this when statusFilter property updates
     */
    public function updatedStatusFilter(): void
    {
        $this->computeFilteredIndicators();
    }

    /**
     * Load report counts for all indicators on the selected date
     * Server-side batch operation to eliminate client-side flickering
     * Called automatically when selectedDate changes
     */
    public function loadAllReportCounts(): void
    {
        if (!$this->selectedDate || !$this->indicators) {
            $this->reportCounts = [];
            return;
        }

        $this->reportCounts = [];

        foreach ($this->indicators as $indicator) {
            $this->reportCounts[$indicator['id']] = $this->monitoringService->getReportCountForIndicatorDate(
                $indicator['id'],
                $this->selectedDate
            );
        }

        if (config('app.debug')) {
            \Log::info('📊 [reportCounts] Loaded for date', [
                'date' => $this->selectedDate,
                'indicators_count' => count($this->indicators),
                'counts_loaded' => count($this->reportCounts),
                'timestamp' => now()->format('Y-m-d H:i:s')
            ]);
        }
    }

    public function deleteReport($recordId): void
    {
        try {
            $repo = app(\App\Repositories\Interfaces\DailyReportResponseRepositoryInterface::class);
            $record = $repo->findById($recordId);

            if (!$record) {
                throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
            }

            // Delete the record via repository
            $deleted = $repo->deleteById($recordId);
            if (!$deleted) {
                throw new \Exception('Gagal menghapus laporan');
            }

            // Close slide-over if open
            $this->slideOverOpen = false;

            // Show success notification
            Notification::make()
                ->success()
                ->title('Berhasil')
                ->body('Laporan berhasil dihapus')
                ->send();

            // Reload data
            $this->loadMatrixData();
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Notification::make()
                ->danger()
                ->title('Akses Ditolak')
                ->body('Anda tidak memiliki izin untuk menghapus laporan ini')
                ->send();
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Notification::make()
                ->danger()
                ->title('Data Tidak Ditemukan')
                ->body('Laporan yang akan dihapus tidak ditemukan')
                ->send();
        } catch (\Exception $e) {
            Log::error('Error deleting daily report response', [
                'id' => $recordId,
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);

            Notification::make()
                ->danger()
                ->title('Terjadi Kesalahan')
                ->body('Gagal menghapus laporan')
                ->send();
        }
    }

}
