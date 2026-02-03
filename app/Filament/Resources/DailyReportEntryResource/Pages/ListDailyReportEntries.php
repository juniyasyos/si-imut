<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Filament\Resources\DailyReportEntryResource;
use App\Models\DailyReportEntry;
use App\Models\FormTemplate;
use App\Services\DailyReport\SlideOverService;
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

    /**
     * Load monitoring templates data for monthly period
     */
    protected function loadMonitoringTemplates(): void
    {
        try {
            $user = Auth::user();

            // Parse month (format: Y-m)
            $date = Carbon::createFromFormat('Y-m', $this->selectedMonth);

            // Get period settings
            $settings = \App\Models\LaporanImutAutoGenerationSetting::getInstance();
            $periodStart = $settings->period_start_day;
            $periodEnd = $settings->period_end_day;

            // Calculate period based on settings
            if ($periodStart <= $periodEnd) {
                // Same month period (e.g., 1-31)
                $startDate = $date->copy()->day($periodStart)->startOfDay();
                $endDate = $date->copy()->day($periodEnd)->endOfDay();
            } else {
                // Cross-month period (e.g., 5 this month - 4 next month)
                $startDate = $date->copy()->day($periodStart)->startOfDay();
                $endDate = $date->copy()->addMonth()->day($periodEnd)->endOfDay();
            }

            // Get user's unit kerja IDs
            $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();

            // Build query - use same scope as list daily report
            $templates = FormTemplate::query()
                ->forUserUnits($user)
                ->with(['imutProfile.imutData.categories'])
                ->whereHas('imutProfile', function ($query) {
                    $query->where('valid_from', '<=', now())
                        ->where(function ($q) {
                            $q->whereNull('valid_until')
                                ->orWhere('valid_until', '>=', now());
                        });
                })
                ->withCount(['dailyReportResponses as response_count' => function ($query) use ($startDate, $endDate, $unitKerjaIds) {
                    $query->whereBetween('report_date', [$startDate, $endDate]);

                    // Only filter by unit_kerja if user has units (not admin/tim mutu)
                    if (!empty($unitKerjaIds)) {
                        $query->whereIn('unit_kerja_id', $unitKerjaIds);
                    }
                }])
                ->get();

            // dd($startDate, $endDate, $templates);


            // Get first unit kerja ID for URL (or null for all units)
            $firstUnitKerjaId = !empty($unitKerjaIds) ? $unitKerjaIds[0] : null;

            $mapped = $templates->map(function ($template) use ($firstUnitKerjaId) {
                return [
                    'id' => $template->id,
                    'imut_profile_id' => $template->imutProfile?->id,
                    'unit_kerja_id' => $firstUnitKerjaId,
                    'title' => $template->imutProfile->imutData->title,
                    'description' => $template->description,
                    'profile_name' => $template->imutProfile?->title ?? null,
                    'imut_profile_version' => $template->imutProfile?->version ?? null,
                    'category' => null,
                    'response_count' => $template->response_count ?? 0,
                ];
            });

            $this->monitoringTemplates = $mapped->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading monitoring data', [
                'month' => $this->selectedMonth,
                'error' => $e->getMessage()
            ]);
            $this->monitoringTemplates = [];
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
     * Export monitoring data
     */
    public function exportMonitoring(int $templateId): void
    {
        // TODO: Implement export functionality
        $this->notify('info', 'Export functionality will be available soon');
    }

    /**
     * Load monitoring data for specific period (called from Alpine.js)
     */
    public function loadMonitoringForPeriod(string $month): array
    {
        try {
            $user = Auth::user();

            // Parse month (format: Y-m)
            $date = Carbon::createFromFormat('Y-m', $month);

            // Get period settings
            $settings = \App\Models\LaporanImutAutoGenerationSetting::getInstance();
            $periodStart = $settings->period_start_day;
            $periodEnd = $settings->period_end_day;

            // Calculate period based on settings
            if ($periodStart <= $periodEnd) {
                // Same month period (e.g., 1-31)
                $startDate = $date->copy()->day($periodStart)->startOfDay();
                $endDate = $date->copy()->day($periodEnd)->endOfDay();
            } else {
                // Cross-month period (e.g., 5 this month - 4 next month)
                $startDate = $date->copy()->day($periodStart)->startOfDay();
                $endDate = $date->copy()->addMonth()->day($periodEnd)->endOfDay();
            }

            // Get user's unit kerja IDs
            $unitKerjaIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();

            // Build query - use same scope as list daily report
            $templates = FormTemplate::query()
                ->forUserUnits($user)
                ->with(['imutProfile.imutData.categories'])
                ->whereHas('imutProfile', function ($query) {
                    $query->where('valid_from', '<=', now())
                        ->where(function ($q) {
                            $q->whereNull('valid_until')
                                ->orWhere('valid_until', '>=', now());
                        });
                })
                ->withCount(['dailyReportResponses as response_count' => function ($query) use ($startDate, $endDate, $unitKerjaIds) {
                    $query->whereBetween('report_date', [$startDate, $endDate]);

                    // Only filter by unit_kerja if user has units (not admin/tim mutu)
                    if (!empty($unitKerjaIds)) {
                        $query->whereIn('unit_kerja_id', $unitKerjaIds);
                    }
                }])
                ->get();

            return $templates->map(function ($template) {
                return [
                    'id' => $template->id,
                    'title' => $template->imutProfile->imutData->title,
                    'description' => $template->description,
                    'profile_name' => $template->imutProfile?->title ?? null,
                    'imut_profile_version' => $template->imutProfile?->version ?? null,
                    'category' => null,
                    'response_count' => $template->response_count ?? 0,
                ];
            })->toArray();
        } catch (\Exception $e) {
            Log::error('Error loading monitoring data for period', [
                'month' => $month,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
