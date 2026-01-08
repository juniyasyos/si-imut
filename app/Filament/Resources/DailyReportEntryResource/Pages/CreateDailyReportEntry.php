<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use App\Filament\Resources\DailyReportEntryResource;
use App\Models\FormTemplate;
use App\Models\DailyReportResponse;
use App\Models\FieldResponse;
use App\Models\EnhancedFormField;
use App\Services\DynamicForm\DynamicFormService;
use App\Services\DynamicForm\ComplianceCalculatorService;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateDailyReportEntry extends CreateRecord
{
    protected static string $resource = DailyReportEntryResource::class;

    protected static bool $canCreateAnother = false;

    protected static string $view = 'filament.pages.create-daily-report-entry';

    public ?FormTemplate $formTemplate = null;
    public ?string $originalIndicatorId = null;
    public ?string $originalDate = null;

    /**
     * Mount the component
     */
    public function mount(): void
    {
        // Load form template first before parent mount
        $indicatorId = request()->query('indicator');
        $date = request()->query('date');

        // Store original parameters for redirect
        $this->originalIndicatorId = $indicatorId;
        $this->originalDate = $date;

        if ($indicatorId) {
            $this->formTemplate = FormTemplate::with(['formFields.options', 'imutProfile'])->find($indicatorId);

            if (!$this->formTemplate) {
                Notification::make()
                    ->title('Form Template Tidak Ditemukan')
                    ->body('Form template tidak ditemukan atau sudah dihapus.')
                    ->danger()
                    ->send();

                $this->redirect($this->getResource()::getUrl('index'));
                return;
            }
        }

        parent::mount();

        if ($this->formTemplate) {
            // Initialize form data
            $this->data = DynamicFormService::initializeFormData($this->formTemplate);
            $this->form->fill($this->data);
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
     * Get the page heading
     */
    public function getHeading(): string
    {
        return '';
    }

    /**
     * Get form title for display
     */
    public function getFormTitle(): string
    {
        $indicatorId = request()->query('indicator');

        if ($indicatorId) {
            $formTemplate = FormTemplate::with('imutProfile')->find($indicatorId);
            if ($formTemplate && $formTemplate->imutProfile && $formTemplate->imutProfile->title) {
                return $formTemplate->imutProfile->title;
            }
        }

        return 'Laporan Harian';
    }

    /**
     * Get form description
     */
    public function getFormDescription(): ?string
    {
        if ($this->formTemplate) {
            return $this->formTemplate->description;
        }

        return null;
    }

    /**
     * Get formatted date
     */
    public function getFormattedDate(): string
    {
        $date = request()->query('date');

        if ($date) {
            try {
                return \Carbon\Carbon::createFromFormat('Y-m-d', $date)->format('d F Y');
            } catch (\Exception $e) {
                return now()->format('d F Y');
            }
        }

        return now()->format('d F Y');
    }

    /**
     * Get category badge color based on template
     */
    public function getCategoryBadgeColor(): string
    {
        if ($this->formTemplate && $this->formTemplate->imutProfile) {
            // Generate consistent color based on title
            $colors = ['blue', 'green', 'purple', 'orange', 'red', 'indigo', 'pink'];
            $index = abs(crc32($this->formTemplate->imutProfile->title)) % count($colors);
            return $colors[$index];
        }

        return 'gray';
    }

    /**
     * Configure the form
     */
    public function form(Form $form): Form
    {
        if (!$this->formTemplate) {
            return $form->schema([]);
        }

        return $form
            ->schema(DynamicFormService::buildFormSchema($this->formTemplate, true, true))
            ->statePath('data')
            ->live();
    }

    /**
     * Mutate form data before creating record
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = Auth::user();

        if (!$user) {
            Notification::make()
                ->title('Gagal membuat laporan')
                ->body('Anda harus login terlebih dahulu')
                ->danger()
                ->send();

            $this->halt();
        }

        /** @var \App\Models\User $user */
        $unitKerjaId = $user->unitKerjas()->first()?->id;

        if (!$unitKerjaId) {
            Notification::make()
                ->title('Gagal membuat laporan')
                ->body('Anda tidak terdaftar di unit kerja mana pun')
                ->danger()
                ->send();

            $this->halt();
        }

        // Get form template ID from URL parameter or use already loaded template
        $indicatorId = request()->query('indicator');
        if ($indicatorId) {
            $data['form_template_id'] = (int) $indicatorId;
        } elseif ($this->formTemplate) {
            $data['form_template_id'] = $this->formTemplate->id;
        } else {
            Notification::make()
                ->title('Error')
                ->body('Form template tidak ditemukan')
                ->danger()
                ->send();

            $this->halt();
        }

        // Get report date from URL parameter or use today
        $date = request()->query('date');
        if ($date) {
            try {
                $data['report_date'] = \Carbon\Carbon::createFromFormat('Y-m-d', $date);
            } catch (\Exception $e) {
                $data['report_date'] = now();
            }
        } else {
            $data['report_date'] = now();
        }

        // Create DailyReportResponse record like seeder
        $dailyReport = DailyReportResponse::create([
            'form_template_id' => $data['form_template_id'],
            'unit_kerja_id' => $unitKerjaId,
            'submitted_by' => Auth::id(),
            'report_date' => $data['report_date'],
            'total_score' => 0,
            'compliance_status' => 'pending',
            'auto_calculated' => true,
        ]);

        $responses = [];

        // Create FieldResponse records for each form field like seeder
        if ($this->formTemplate) {
            $sortedFields = $this->formTemplate->formFields->sortBy('order_index');

            foreach ($sortedFields as $field) {
                $fieldValue = $data[$field->field_key] ?? null;

                if ($fieldValue !== null) {
                    $responses[$field->field_key] = $fieldValue;

                    // Create field response record
                    FieldResponse::create([
                        'daily_report_response_id' => $dailyReport->id,
                        'form_field_id' => $field->id,
                        'field_value' => is_array($fieldValue) ? $fieldValue : [$fieldValue],
                        'compliance_score' => $field->calculateFieldScore($fieldValue) ?? 0,
                    ]);
                }
            }

            // Calculate final compliance using template method
            $complianceResult = $this->formTemplate->calculateCompliance($responses);

            // Update daily report with results
            $dailyReport->update([
                'total_score' => $complianceResult['total_score'],
                'compliance_status' => $complianceResult['compliance_status'],
                'calculation_details' => $complianceResult,
            ]);
        }

        // Store the created record for potential use
        $this->record = $dailyReport;

        // Return empty since we handled the creation manually
        return [];
    }

    /**
     * Override to prevent default record creation since we handle it manually
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Creation is handled in mutateFormDataBeforeCreate
        // Return the record we created there
        return $this->record;
    }

    /**
     * Override redirect after successful creation
     */
    protected function getRedirectUrl(): string
    {
        $params = [];

        // Use stored parameters instead of request()->query()
        if ($this->originalIndicatorId) {
            $params['indicator_id'] = $this->originalIndicatorId;
        }

        if ($this->originalDate) {
            $params['date'] = $this->originalDate;
        }

        $url = $this->getResource()::getUrl('index');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $url;
    }

    /**
     * Get success notification
     */
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Laporan berhasil dibuat')
            ->body('Laporan harian telah berhasil disimpan');
    }
}
