<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Filament\Resources\DailyReportEntryResource;
use App\Models\DailyReportEntry;
use App\Models\FormTemplate;
use App\Services\DailyReport\MatrixDataService;
use App\Services\DailyReport\SlideOverService;
use App\Traits\DailyReport\ReportManagementTrait;
use App\Traits\DailyReport\NavigationTrait;
use App\Traits\DailyReport\FormHandlerTrait;
use App\Traits\DailyReport\DebugTrait;
use Carbon\Carbon;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ListDailyReportEntries extends ListRecords implements HasForms
{
    use InteractsWithForms;
    use ReportManagementTrait;
    use NavigationTrait;
    use FormHandlerTrait;
    use DebugTrait;

    protected static string $resource = DailyReportEntryResource::class;
    protected static string $view = 'filament.resources.daily-report-entry-resource.pages.list-daily-report-entries-original';

    // Services
    protected MatrixDataService $matrixService;
    protected SlideOverService $slideOverService;

    // Add listeners for Alpine.js events
    protected $listeners = [
        'dateSelected' => 'handleDateSelected',
    ];

    // Matrix properties
    public string $selectedMonth;
    public array $indicators = [];
    public array $matrixData = [];
    public array $daysInMonth = [];

    // Loading states  
    public bool $loadingMatrix = false;
    public bool $loadingSlideOver = false;

    // Slide over properties
    public bool $slideOverOpen = false;
    public ?int $selectedIndicatorId = null;
    public ?string $selectedDate = null;
    public array $selectedIndicatorData = [];
    public $dailyReports = [];
    public string $filterPeriod = 'today';

    // Form slide over properties
    public bool $formSlideOverOpen = false;
    public ?FormTemplate $formTemplate = null;
    public array $reportData = [];

    /**
     * Boot the page
     */
    public function boot(): void
    {
        $this->matrixService = new MatrixDataService();
        $this->slideOverService = new SlideOverService();
    }

    /**
     * Mount the component
     */
    public function mount(): void
    {
        parent::mount();
        $this->selectedMonth = now()->format('Y-m');
        $this->selectedDate = now()->format('Y-m-d');
        $this->loadMatrixData();
        $this->checkAndOpenSlideOverFromUrl();
    }

    /**
     * Load matrix data for selected month
     */
    public function loadMatrixData(): void
    {
        $result = $this->matrixService->loadMatrixData($this->selectedMonth);

        $this->indicators = $result['indicators'];
        $this->matrixData = $result['matrixData'];
        $this->daysInMonth = $result['daysInMonth'];
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
     * Open slide over
     */
    public function openSlideOver(int $indicatorId, ?string $date = null): void
    {
        $validatedDate = $this->slideOverService->validateDate($date);

        Log::info('OpenSlideOver called', ['indicator_id' => $indicatorId, 'date' => $validatedDate]);

        $this->selectedIndicatorId = $indicatorId;
        $this->selectedDate = $validatedDate;

        // Load indicator data
        $this->selectedIndicatorData = $this->slideOverService->getSelectedIndicatorData($indicatorId, $this->indicators);

        // Load daily reports for this indicator and date
        $this->loadDailyReports();
        $this->slideOverOpen = true;

        // Update URL without page refresh
        $this->js("
            setTimeout(() => {
                const newUrl = window.location.pathname + '?indicator_id={$indicatorId}&date={$validatedDate}';
                console.log('Updating URL to:', newUrl);
                window.history.replaceState({}, '', newUrl);
            }, 100);
        ");
    }

    /**
     * Close slide over and clean URL
     */
    public function closeSlideOver(): void
    {
        $this->slideOverOpen = false;
        $this->selectedIndicatorId = null;
        $this->selectedIndicatorData = [];
        $this->dailyReports = [];

        // Clean URL parameters
        $this->js("
            const cleanUrl = window.location.pathname;
            console.log('Cleaning URL to:', cleanUrl);
            window.history.replaceState({}, '', cleanUrl);
        ");
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
}
