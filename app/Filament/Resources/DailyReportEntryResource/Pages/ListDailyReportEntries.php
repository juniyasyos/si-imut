<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Filament\Resources\DailyReportEntryResource;
use App\Models\DailyReportEntry;
use App\Models\FormTemplate;
use App\Traits\DailyReport\FormHandlerTrait;
use App\Traits\DailyReport\DebugTrait;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ListDailyReportEntries extends BaseDailyReportMonitoring implements HasForms
{
    use InteractsWithForms;
    use FormHandlerTrait;
    use DebugTrait;

    protected static string $resource = DailyReportEntryResource::class;

    public function boot(): void
    {
        parent::boot();
    }

    public function mount(): void
    {
        $this->bootBase();
        $this->loadMatrixData();
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

        Log::info('OpenSlideOver called', ['indicator_id' => $indicatorId, 'date' => $validatedDate]);

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
}
