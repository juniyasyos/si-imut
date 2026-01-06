<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Filament\Resources\DailyReportEntryResource;
use App\Models\DailyReportEntry;
use App\Models\FormTemplate;
use Carbon\Carbon;
use Filament\Resources\Pages\ListRecords;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Placeholder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListDailyReportEntries extends ListRecords implements HasForms
{
    use InteractsWithForms;
    protected static string $resource = DailyReportEntryResource::class;

    protected static string $view = 'filament.resources.daily-report-entry-resource.pages.list-daily-report-entries-original';

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
    public $dailyReports = []; // Cache for slide-over data
    public string $filterPeriod = 'today';

    // Form slide over properties
    public bool $formSlideOverOpen = false;
    public ?FormTemplate $formTemplate = null;
    public array $reportData = [];

    /**
     * Handle date selection from frontend
     */
    public function handleDateSelected(string $date): void
    {
        $this->selectedDate = $date;
        // Optionally, you can reload data or trigger other actions
        // based on the selected date if needed
    }

    /**
     * Filter logic methods
     */
    public function isToday(int $day): bool
    {
        $today = now();
        $cellDate = Carbon::createFromFormat('Y-m', $this->selectedMonth)->day($day);
        return $cellDate->isSameDay($today);
    }

    public function isInWeek(int $day): bool
    {
        $realToday = now();
        $start = $realToday->copy()->subDays(6)->startOfDay();
        $cellDate = Carbon::createFromFormat('Y-m', $this->selectedMonth)->day($day)->startOfDay();

        $inRange = $cellDate->between($start, $realToday->copy()->endOfDay());

        $currentMonth = Carbon::createFromFormat('Y-m', $this->selectedMonth);
        $realMonth = $realToday->copy()->startOfMonth();

        if ($currentMonth->isSameMonth($realMonth)) {
            return $inRange;
        }

        if ($cellDate->lt($realToday)) {
            $daysDiff = $realToday->diffInDays($cellDate);
            return $daysDiff <= 6;
        }

        return false;
    }

    public function isInMonth(int $day): bool
    {
        $today = now();
        $cellDate = Carbon::createFromFormat('Y-m', $this->selectedMonth)->day($day);
        return $cellDate->isSameMonth($today);
    }

    public function shouldShowCell(int $day, string $filterPeriod = null): bool
    {
        $period = $filterPeriod ?? $this->filterPeriod;
        return match ($period) {
            'today' => $this->isToday($day),
            'weekly' => $this->isInWeek($day),
            'monthly' => $this->isInMonth($day),
            default => true
        };
    }

    public function setFilterPeriod(string $period): void
    {
        $this->filterPeriod = $period;
    }

    /**
     * Mount the component
     */
    public function mount(): void
    {
        parent::mount();
        $this->selectedMonth = now()->format('Y-m');
        $this->selectedDate = now()->format('Y-m-d'); // Initialize selected date
        $this->loadMatrixData();
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
            $this->indicators = [];
            $this->matrixData = [];
            return;
        }

        // Get indicators for user's units (filter by valid imut profiles)
        $formTemplates = FormTemplate::select([
            'form_templates.id',
            'form_templates.title',
            'imut_data.title as imut_data_title',
            'imut_kategori.category_name as category_title',
            'imut_profil.version as imut_profile_version',
            'imut_profil.valid_from as imut_profile_valid_from',
            'imut_profil.valid_until as imut_profile_valid_until'
        ])
            ->join('imut_profil', 'form_templates.imut_profile_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->join('imut_data_unit_kerja', 'imut_data.id', '=', 'imut_data_unit_kerja.imut_data_id')
            ->leftJoin('imut_kategori', 'imut_data.imut_kategori_id', '=', 'imut_kategori.id')
            ->whereIn('imut_data_unit_kerja.unit_kerja_id', $unitKerjaIds)
            ->where(function ($query) {
                $now = now();
                $query->where(function ($q) use ($now) {
                    // Check if already started and not yet expired
                    $q->where('imut_profil.valid_from', '<=', $now)
                        ->where(function ($subQ) use ($now) {
                            $subQ->whereNull('imut_profil.valid_until')
                                ->orWhere('imut_profil.valid_until', '>=', $now);
                        });
                });
            })
            ->distinct()
            ->get();
        $this->indicators = $formTemplates->map(function ($formTemplate) {
            return [
                'id' => $formTemplate->id,
                'title' => $formTemplate->imut_data_title ?? $formTemplate->title,
                'category' => $formTemplate->category_title,
                'imut_profile_version' => $formTemplate->imut_profile_version,
            ];
        })
            ->toArray();

        // Debug: Log final indicators count after deduplication
        \Log::info('Final indicators for UI (after deduplication)', [
            'user_id' => $user->id,
            'original_count' => $formTemplates->count(),
            'deduplicated_count' => count($this->indicators),
            'sample_titles' => array_slice(array_column($this->indicators, 'title'), 0, 5)
        ]);

        // Calculate days in selected month
        $date = Carbon::parse($this->selectedMonth . '-01');
        $daysCount = $date->daysInMonth;
        $this->daysInMonth = range(1, $daysCount);

        // Get compliance summary for selected month only (optimized aggregate query)
        $startDate = $date->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $date->copy()->endOfMonth()->format('Y-m-d');

        $complianceSummaries = \App\Models\DailyReportResponse::select([
            'form_templates.id as form_template_id',
            DB::raw('DATE(daily_report_responses.report_date) as report_date'),
            DB::raw('COUNT(*) as total_count'),
            DB::raw('SUM(CASE WHEN compliance_status = 1 THEN 1 ELSE 0 END) as compliant_count')
        ])
            ->join('form_templates', 'daily_report_responses.form_template_id', '=', 'form_templates.id')
            ->join('imut_profil', 'form_templates.imut_profile_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->join('imut_data_unit_kerja', 'imut_data.id', '=', 'imut_data_unit_kerja.imut_data_id')
            ->whereIn('imut_data_unit_kerja.unit_kerja_id', $unitKerjaIds)
            ->where(function ($query) {
                $now = now();
                $query->where(function ($q) use ($now) {
                    // Check if already started and not yet expired
                    $q->where('imut_profil.valid_from', '<=', $now)
                        ->where(function ($subQ) use ($now) {
                            $subQ->whereNull('imut_profil.valid_until')
                                ->orWhere('imut_profil.valid_until', '>=', $now);
                        });
                });
            })
            ->whereBetween('daily_report_responses.report_date', [$startDate, $endDate])
            ->groupBy('form_templates.id', DB::raw('DATE(daily_report_responses.report_date)'))
            ->get()
            ->groupBy('form_template_id')
            ->map(function ($dates) {
                return $dates->keyBy('report_date');
            });

        // Build matrix data
        $this->matrixData = [];
        foreach ($this->indicators as $indicator) {
            foreach ($this->daysInMonth as $day) {
                $dateStr = $date->copy()->day($day)->format('Y-m-d');

                $summary = $complianceSummaries->get($indicator['id'])?->get($dateStr);
                $totalCount = $summary ? $summary->total_count : 0;
                $compliantCount = $summary ? $summary->compliant_count : 0;
                $compliancePercentage = $totalCount > 0 ? round(($compliantCount / $totalCount) * 100, 1) : 0;

                // Pre-compute cell state to avoid method calls in view
                $cellDate = $date->copy()->day($day)->startOfDay();
                $today = now()->startOfDay();
                $sixDaysAgo = now()->copy()->subDays(6)->startOfDay();

                $cellState = 'disabled'; // default for future dates
                if ($cellDate->lte($today)) { // not future (today or past)
                    if ($totalCount > 0) {
                        $cellState = 'done';
                    } elseif ($cellDate->gte($sixDaysAgo)) {
                        $cellState = 'pending'; // within 7 days (can still input)
                    } else {
                        $cellState = 'overdue'; // older than 7 days (locked)
                    }
                }

                // Pre-compute summary to avoid method calls in view
                $summaryData = null;
                if ($totalCount > 0) {
                    $summaryData = [
                        'count' => $totalCount,
                        'numerator' => $compliantCount,
                        'denominator' => $totalCount,
                        'percentage' => $compliancePercentage,
                    ];
                }

                $this->matrixData[$indicator['id']][$day] = [
                    'date' => $dateStr,
                    'has_data' => $totalCount > 0,
                    'count' => $totalCount,
                    'compliance_percentage' => $compliancePercentage,
                    'compliance_count' => $compliantCount,
                    'total_count' => $totalCount,
                    'cell_state' => $cellState, // Pre-computed state
                    'summary' => $summaryData, // Pre-computed summary
                    'is_today' => $cellDate->isToday(), // Pre-computed today check
                ];
            }
        }
    }

    /**
     * Get Alpine.js data for frontend rendering
     */
    public function getAlpineData(): array
    {
        return [
            'indicators' => $this->indicators,
            'matrixData' => $this->matrixData,
            'daysInMonth' => $this->daysInMonth,
            'selectedMonth' => $this->selectedMonth,
            'today' => now()->format('Y-m-d'),
        ];
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
     * Open slide over
     */
    public function openSlideOver(int $indicatorId, string $date): void
    {
        $this->selectedIndicatorId = $indicatorId;
        $this->selectedDate = $date;

        // Load indicator data
        $indicator = collect($this->indicators)->firstWhere('id', $indicatorId);
        $this->selectedIndicatorData = $indicator ?? [];

        // Load daily reports for this indicator and date (optimized)
        $this->loadDailyReports();

        $this->slideOverOpen = true;
    }

    /**
     * Load daily reports for selected indicator and date (optimized)
     */
    public function loadDailyReports(): void
    {
        if (!$this->selectedIndicatorId || !$this->selectedDate) {
            $this->dailyReports = [];
            return;
        }

        // Get user unit IDs once
        $user = Auth::user();
        if (!$user) {
            $this->dailyReports = [];
            return;
        }

        /** @var \App\Models\User $user */
        $userUnitIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();

        if (empty($userUnitIds)) {
            $this->dailyReports = [];
            return;
        }

        // Step 1: Get daily reports with basic data (single optimized query)
        $reports = \App\Models\DailyReportResponse::query()
            ->select([
                'daily_report_responses.*',
                'unit_kerja.unit_name as unit_name',
                'users.name as submitted_by_name',
                'form_templates.title as form_title'
            ])
            ->join('form_templates', 'daily_report_responses.form_template_id', '=', 'form_templates.id')
            ->join('imut_profil', 'form_templates.imut_profile_id', '=', 'imut_profil.id')
            ->join('imut_data', 'imut_profil.imut_data_id', '=', 'imut_data.id')
            ->join('imut_data_unit_kerja', 'imut_data.id', '=', 'imut_data_unit_kerja.imut_data_id')
            ->join('unit_kerja', 'daily_report_responses.unit_kerja_id', '=', 'unit_kerja.id')
            ->join('users', 'daily_report_responses.submitted_by', '=', 'users.id')
            ->where('form_templates.id', $this->selectedIndicatorId)
            ->where(function ($query) {
                $now = now();
                $query->where(function ($q) use ($now) {
                    // Check if already started and not yet expired
                    $q->where('imut_profil.valid_from', '<=', $now)
                        ->where(function ($subQ) use ($now) {
                            $subQ->whereNull('imut_profil.valid_until')
                                ->orWhere('imut_profil.valid_until', '>=', $now);
                        });
                });
            })
            ->whereDate('daily_report_responses.report_date', $this->selectedDate)
            ->whereIn('imut_data_unit_kerja.unit_kerja_id', $userUnitIds)
            ->latest('daily_report_responses.created_at')
            ->get();

        if ($reports->isEmpty()) {
            $this->dailyReports = [];
            return;
        }

        // Step 2: Get field responses for all reports in a single query
        $reportIds = $reports->pluck('id')->toArray();
        $fieldResponses = \App\Models\FieldResponse::query()
            ->select([
                'field_responses.*',
                'enhanced_form_fields.field_label'
            ])
            ->join('enhanced_form_fields', 'field_responses.form_field_id', '=', 'enhanced_form_fields.id')
            ->whereIn('field_responses.daily_report_response_id', $reportIds)
            ->get()
            ->groupBy('daily_report_response_id');

        // Step 3: Map reports with field responses
        $this->dailyReports = $reports->map(function ($report) use ($fieldResponses) {
            $reportFieldResponses = $fieldResponses->get($report->id, collect());

            return [
                'id' => $report->id,
                'total_score' => $report->total_score,
                'compliance_status' => $report->compliance_status,
                'notes' => $report->notes,
                'created_at' => $report->created_at,
                'unit_name' => $report->unit_name,
                'submitted_by_name' => $report->submitted_by_name,
                'form_title' => $report->form_title,
                'field_responses' => $reportFieldResponses->map(function ($response) {
                    return [
                        'field_label' => $response->field_label,
                        'compliance_score' => $response->compliance_score,
                        'field_value' => $response->field_value,
                    ];
                })->toArray()
            ];
        })->toArray();
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
        $this->dailyReports = []; // Clear cached data
    }

    /**
     * Create new report
     */
    public function createNewReport(): void
    {
        if ($this->selectedIndicatorId && $this->selectedDate) {
            // Debug logging
            \Log::info('createNewReport: selectedIndicatorId = ' . $this->selectedIndicatorId);

            // Load form template directly
            $this->formTemplate = FormTemplate::where('imut_profile_id', $this->selectedIndicatorId)->first();

            \Log::info('createNewReport: FormTemplate found = ' . ($this->formTemplate ? 'YES (id: ' . $this->formTemplate->id . ', title: ' . $this->formTemplate->title . ')' : 'NO'));

            if ($this->formTemplate) {
                $this->initializeReportData();
                $this->formSlideOverOpen = true;
            } else {
                \Filament\Notifications\Notification::make()
                    ->title('Form Template Tidak Ditemukan')
                    ->body('Form template untuk indikator ini belum dikonfigurasi. Indicator ID: ' . $this->selectedIndicatorId)
                    ->warning()
                    ->send();
            }
        } else {
            \Log::warning('createNewReport: Missing data - selectedIndicatorId: ' . $this->selectedIndicatorId . ', selectedDate: ' . $this->selectedDate);
        }
    }

    /**
     * Close form slide over and reset data
     */
    public function closeFormSlideOver(): void
    {
        $this->formSlideOverOpen = false;
        $this->formTemplate = null;
        $this->reportData = [];
    }

    /**
     * View report details
     */
    public function viewReport(int $reportId): void
    {
        $this->redirect(DailyReportEntryResource::getUrl('view', ['record' => $reportId]));
    }

    /**
     * Edit report
     */
    public function editReport(int $reportId): void
    {
        $this->redirect(DailyReportEntryResource::getUrl('edit', ['record' => $reportId]));
    }

    /**
     * Initialize empty report data
     */
    protected function initializeReportData(): void
    {
        $this->reportData = [
            'field_responses' => [],
            'notes' => '',
        ];

        // Initialize field responses using actual FormField models
        if ($this->formTemplate) {
            $formFields = $this->formTemplate->formFields;
            foreach ($formFields as $field) {
                // Set default values based on field type
                $defaultValue = null;

                switch ($field->field_type) {
                    case 'text':
                    case 'textarea':
                        $defaultValue = '';
                        break;
                    case 'number':
                        $defaultValue = null;
                        break;
                    case 'single_select':
                    case 'boolean':
                        $defaultValue = null;
                        break;
                    case 'multi_select':
                        $defaultValue = [];
                        break;
                    default:
                        $defaultValue = null;
                }

                $this->reportData['field_responses'][$field->field_key] = $defaultValue;
            }
        }

        // Fill form with initialized data
        $this->reportEntryForm->fill($this->reportData);
    }

    /**
     * Save the report
     */
    public function saveReport(): void
    {
        try {
            // Validate form data
            $this->reportEntryForm->validate();

            // Get form data
            $formData = $this->reportEntryForm->getState();

            // Calculate compliance score using the new method
            $complianceData = $this->calculateCompliance($formData);

            // Create the report
            $report = DailyReportEntry::create([
                'imut_profile_id' => $this->selectedIndicatorData['imut_profile_id'],
                'form_template_id' => $this->formTemplate->id,
                'report_date' => $this->selectedDate,
                'submitted_by_id' => Auth::id(),
                'unit_kerja_id' => Auth::user()->unitKerjas->first()?->id,
                'field_responses' => $formData['field_responses'] ?? [],
                'notes' => $formData['notes'] ?? '',
                'total_score' => $complianceData['score'],
                'compliance_status' => $complianceData['score'] >= 80,
                'status' => 'submitted',
            ]);

            // Success notification
            \Filament\Notifications\Notification::make()
                ->title('Laporan Berhasil Disimpan')
                ->body('Laporan harian telah berhasil dibuat dengan skor kepatuhan ' . number_format($complianceData['score'], 1) . '%')
                ->success()
                ->send();

            // Close form and refresh data
            $this->closeFormSlideOver();
            $this->loadSlideOverData(); // Refresh slide over data
            $this->loadMatrixData(); // Refresh matrix data

        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Gagal Menyimpan')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Get the form for report entry - Simple Google Form style
     */
    public function reportEntryForm(Form $form): Form
    {
        if (!$this->formTemplate) {
            return $form->schema([
                Placeholder::make('no_template')
                    ->content('Form template tidak ditemukan.')
            ]);
        }

        $schema = [];

        // Simple form header
        $schema[] = Section::make('Form Laporan Harian')
            ->description($this->formTemplate->description ?? 'Isi form berikut dengan lengkap')
            ->schema([
                Placeholder::make('info')
                    ->content("Template: {$this->formTemplate->title}")
            ]);

        // Add fields directly without complex processing
        $formFields = $this->formTemplate->formFields()->orderBy('order_index')->get();

        foreach ($formFields as $index => $field) {
            $fieldKey = $field->field_key;

            // Simple field creation based on type
            switch ($field->field_type) {
                case 'text':
                    $schema[] = Section::make($field->field_label)
                        ->description($field->field_description)
                        ->schema([
                            TextInput::make("field_responses.{$fieldKey}")
                                ->label('')
                                ->required($field->validation_config['required'] ?? false)
                                ->placeholder('Masukkan jawaban Anda...')
                        ]);
                    break;

                case 'number':
                    $schema[] = Section::make($field->field_label)
                        ->description($field->field_description)
                        ->schema([
                            TextInput::make("field_responses.{$fieldKey}")
                                ->label('')
                                ->numeric()
                                ->required($field->validation_config['required'] ?? false)
                                ->placeholder('Masukkan angka...')
                        ]);
                    break;

                case 'single_select':
                    $options = [];
                    foreach ($field->options as $option) {
                        $options[$option->option_value] = $option->option_text;
                    }

                    $schema[] = Section::make($field->field_label)
                        ->description($field->field_description)
                        ->schema([
                            Radio::make("field_responses.{$fieldKey}")
                                ->label('')
                                ->options($options)
                                ->required($field->validation_config['required'] ?? false)
                        ]);
                    break;

                case 'boolean':
                    $options = ['1' => 'Ya', '0' => 'Tidak'];
                    if ($field->options->count() > 0) {
                        $options = [];
                        foreach ($field->options as $option) {
                            $options[$option->option_value] = $option->option_text;
                        }
                    }

                    $schema[] = Section::make($field->field_label)
                        ->description($field->field_description)
                        ->schema([
                            Radio::make("field_responses.{$fieldKey}")
                                ->label('')
                                ->options($options)
                                ->required($field->validation_config['required'] ?? false)
                        ]);
                    break;

                case 'multi_select':
                    $options = [];
                    foreach ($field->options as $option) {
                        $options[$option->option_value] = $option->option_text;
                    }

                    $schema[] = Section::make($field->field_label)
                        ->description($field->field_description)
                        ->schema([
                            CheckboxList::make("field_responses.{$fieldKey}")
                                ->label('')
                                ->options($options)
                                ->required($field->validation_config['required'] ?? false)
                        ]);
                    break;

                case 'textarea':
                    $schema[] = Section::make($field->field_label)
                        ->description($field->field_description)
                        ->schema([
                            Textarea::make("field_responses.{$fieldKey}")
                                ->label('')
                                ->required($field->validation_config['required'] ?? false)
                                ->rows(3)
                                ->placeholder('Masukkan jawaban Anda...')
                        ]);
                    break;

                default:
                    $schema[] = Section::make($field->field_label)
                        ->description($field->field_description)
                        ->schema([
                            TextInput::make("field_responses.{$fieldKey}")
                                ->label('')
                                ->required($field->validation_config['required'] ?? false)
                                ->placeholder('Masukkan jawaban Anda...')
                        ]);
                    break;
            }
        }

        // Simple notes section
        $schema[] = Section::make('Catatan Tambahan (Opsional)')
            ->schema([
                Textarea::make('notes')
                    ->label('')
                    ->placeholder('Tambahkan catatan jika diperlukan...')
                    ->rows(3)
            ]);

        return $form
            ->schema($schema)
            ->statePath('reportData')
            ->live();
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
     * Calculate simple compliance score - Google Form style
     */
    protected function calculateCompliance(array $data): array
    {
        if (!$this->formTemplate) {
            return ['score' => 0, 'status' => 'No Template'];
        }

        $totalFields = $this->formTemplate->formFields->count();
        $filledFields = 0;

        // Simple calculation: count filled fields
        foreach ($this->formTemplate->formFields as $field) {
            $fieldValue = $data['field_responses'][$field->field_key] ?? null;

            if (!empty($fieldValue)) {
                $filledFields++;
            }
        }

        $percentage = $totalFields > 0 ? ($filledFields / $totalFields) * 100 : 0;
        $status = $percentage >= 80 ? 'Completed' : 'Incomplete';

        return [
            'score' => $percentage,
            'status' => $status,
            'filled_fields' => $filledFields,
            'total_fields' => $totalFields
        ];
    }

    /**
     * Delete report
     */
    public function deleteReport(int $reportId): void
    {
        $report = \App\Models\DailyReportResponse::findOrFail($reportId);

        // Check permissions
        $user = Auth::user();
        if (!$user || !$user->can('delete', $report)) {
            $this->addError('delete', 'Anda tidak memiliki izin untuk menghapus laporan ini.');
            return;
        }

        // Check if report can be deleted (within 24 hours)
        if ($report->created_at->diffInHours(now()) > 24) {
            $this->addError('delete', 'Laporan hanya dapat dihapus dalam 24 jam setelah dibuat.');
            return;
        }

        try {
            $report->delete();

            // Refresh matrix data and slide-over data
            $this->loadMatrixData();
            $this->loadDailyReports();

            // Show success notification
            \Filament\Notifications\Notification::make()
                ->title('Laporan berhasil dihapus')
                ->success()
                ->send();
        } catch (\Exception $e) {
            $this->addError('delete', 'Gagal menghapus laporan: ' . $e->getMessage());
        }
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
