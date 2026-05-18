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
        $this->loadMatrixData();
        $this->loadMonitoringTemplates();
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
        parent::closeSlideOver();

        // Clean URL parameters
        $this->js("
            const cleanUrl = window.location.pathname;
            console.log('Cleaning URL to:', cleanUrl);
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
        $this->monitoringTemplates = $this->monitoringService->loadMonitoringTemplates(
            $user,
            $this->selectedMonth
        );
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

    public function deleteReport($recordId): void
    {
        try {
            $record = DailyReportResponse::findOrFail($recordId);

            // Delete the record
            $record->delete();

            // Close slide-over if open
            $this->slideOverOpen = true;

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
    