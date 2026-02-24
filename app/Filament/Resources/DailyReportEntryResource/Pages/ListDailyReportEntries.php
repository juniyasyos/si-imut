<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Filament\Resources\DailyReportEntryResource;
use App\Models\DailyReportEntry;
use App\Models\DailyReportResponse;
use App\Models\FormTemplate;
use App\Services\DailyReport\SlideOverService;
use App\Traits\DailyReport\FormHandlerTrait;
use App\Traits\DailyReport\DebugTrait;
use Carbon\Carbon;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

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
     */
    protected function loadMonitoringTemplates(): void
    {
        try {
            $user = Auth::user();

            // Parse month (format: Y-m)
            $date = Carbon::createFromFormat('Y-m', $this->selectedMonth);

            // Get period settings
            $settings = \App\Models\LaporanImutAutoGenerationSetting::getInstance();

            // Use full month approach (1 - end of month)
            $startDate = $date->copy()->startOfMonth()->startOfDay();
            $endDate = $date->copy()->endOfMonth()->endOfDay();

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
                ->whereHas('imutProfile.imutData', function ($query) {
                    $query->where('is_monthly', true);
                })
                ->withCount(['dailyReportResponses as response_count' => function ($query) use ($startDate, $endDate, $unitKerjaIds) {
                    $query->whereBetween('report_date', [$startDate, $endDate]);

                    // Only filter by unit_kerja if user has units (not admin/tim mutu)
                    if (!empty($unitKerjaIds)) {
                        $query->whereIn('unit_kerja_id', $unitKerjaIds);
                    }
                }])
                ->get();

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
     * Export monitoring data to Excel/CSV
     */
    public function exportMonitoring(int $templateId, ?string $month = null): \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\RedirectResponse
    {
        try {
            $user = Auth::user();
            $currentMonth = $month ?? $this->monitoringMonth ?? now()->format('Y-m');

            // Parse month
            $date = Carbon::createFromFormat('Y-m', $currentMonth);

            // Get period settings
            $settings = \App\Models\LaporanImutAutoGenerationSetting::getInstance();

            // Use full month approach
            $startDate = $date->copy()->startOfMonth()->startOfDay();
            $endDate = $date->copy()->endOfMonth()->endOfDay();

            // Get template with responses
            $template = \App\Models\FormTemplate::with([
                'imutProfile.imutData',
                'formFields.options',
                'dailyReportResponses' => function ($query) use ($startDate, $endDate, $user) {
                    $query->whereBetween('report_date', [$startDate, $endDate])
                        ->forUserUnits($user)
                        ->with(['submittedBy', 'validator', 'unitKerja', 'fieldResponses.formField']);
                }
            ])->findOrFail($templateId);

            // Generate filename
            $filename = 'monitoring_' . $template->imutProfile->imutData->title . '_' . $currentMonth . '.xlsx';
            $filename = preg_replace('/[^A-Za-z0-9\-_.]/', '_', $filename);

            // Create Excel file
            return \Maatwebsite\Excel\Facades\Excel::download(
                new class($template, $startDate, $endDate) implements \Maatwebsite\Excel\Concerns\FromCollection, \Maatwebsite\Excel\Concerns\WithHeadings, \Maatwebsite\Excel\Concerns\WithTitle {
                    private $template;
                    private $startDate;
                    private $endDate;

                    public function __construct($template, $startDate, $endDate)
                    {
                        $this->template = $template;
                        $this->startDate = $startDate;
                        $this->endDate = $endDate;
                    }

                    public function collection()
                    {
                        $data = collect();

                        foreach ($this->template->dailyReportResponses as $response) {
                            $row = [
                                'Tanggal' => $response->report_date->format('d/m/Y'),
                                'Unit Kerja' => $response->unitKerja->unit_name ?? '',
                                'Pengumpul Data' => $response->submittedBy->name ?? '',
                                'Validator' => $response->validator->name ?? '',
                                'Status Validasi' => $response->is_validated ? 'Tervalidasi' : 'Belum Divalidasi',
                            ];

                            // Add field responses
                            foreach ($this->template->formFields as $field) {
                                $fieldResponse = $response->fieldResponses->where('form_field_id', $field->id)->first();
                                $value = '';

                                if ($fieldResponse) {
                                    $fieldValue = $fieldResponse->field_value;

                                    // Format value based on field type
                                    switch ($field->field_type) {
                                        case 'boolean':
                                            $value = ($fieldValue == 1 || $fieldValue === true || $fieldValue === '1') ? 'Ya' : 'Tidak';
                                            break;

                                        case 'single_select':
                                        case 'multi_select':
                                            if (is_array($fieldValue)) {
                                                $selectedOptions = [];
                                                foreach ($fieldValue as $optionValue) {
                                                    $option = $field->options->firstWhere('option_value', $optionValue);
                                                    if ($option) {
                                                        $selectedOptions[] = $option->option_text;
                                                    }
                                                }
                                                $value = implode(', ', $selectedOptions);
                                            } else {
                                                $option = $field->options->firstWhere('option_value', $fieldValue);
                                                $value = $option ? $option->option_text : $fieldValue;
                                            }
                                            break;

                                        case 'time_duration':
                                        case 'time_range':
                                            if (is_array($fieldValue)) {
                                                if (isset($fieldValue['start_time']) && isset($fieldValue['end_time'])) {
                                                    $value = $fieldValue['start_time'] . ' - ' . $fieldValue['end_time'];
                                                } elseif (isset($fieldValue['duration'])) {
                                                    $value = $fieldValue['duration'] . ' menit';
                                                } else {
                                                    $value = json_encode($fieldValue);
                                                }
                                            } else {
                                                $value = $fieldValue;
                                            }
                                            break;

                                        case 'number':
                                            $value = is_numeric($fieldValue) ? number_format($fieldValue, 0, ',', '.') : $fieldValue;
                                            break;

                                        case 'date':
                                            if ($fieldValue && strtotime($fieldValue)) {
                                                $value = date('d/m/Y', strtotime($fieldValue));
                                            } else {
                                                $value = $fieldValue;
                                            }
                                            break;

                                        default:
                                            if (is_array($fieldValue)) {
                                                $value = json_encode($fieldValue);
                                            } else {
                                                $value = $fieldValue;
                                            }
                                            break;
                                    }
                                }

                                $row[$field->field_label] = $value;
                            }

                            $data->push($row);
                        }

                        return $data;
                    }

                    public function headings(): array
                    {
                        $headings = [
                            'Tanggal',
                            'Unit Kerja',
                            'Pengumpul Data',
                            'Validator',
                            'Status Validasi'
                        ];

                        // Add field headings
                        foreach ($this->template->formFields as $field) {
                            $headings[] = $field->field_label;
                        }

                        return $headings;
                    }

                    public function title(): string
                    {
                        return substr($this->template->imutProfile->imutData->title, 0, 31);
                    }
                },
                $filename
            );

        } catch (\Exception $e) {
            \Log::error('Export monitoring data failed', [
                'template_id' => $templateId,
                'user_id' => $user->id ?? null,
                'month' => $currentMonth,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // For file download endpoints, redirect back with error message
            return redirect()->back()->with('error', 'Gagal export data: ' . $e->getMessage());
        }
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

            // Use full month approach (1 - end of month)
            $startDate = $date->copy()->startOfMonth()->startOfDay();
            $endDate = $date->copy()->endOfMonth()->endOfDay();

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

    /**
     * Delete a daily report entry via API
     */
    public function getReportCountForIndicatorDate(int $indicatorId, string $date): int
    {
        try {
            $reports = $this->slideOverService->loadDailyReports($indicatorId, $date);
            return count($reports);
        } catch (\Exception $e) {
            Log::error('Error fetching report count', ['indicator_id' => $indicatorId, 'date' => $date, 'error' => $e->getMessage()]);
            return 0;
        }
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
