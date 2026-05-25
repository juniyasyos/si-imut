<?php

namespace App\Filament\Resources\DailyReportEntryResource\Pages;

use Filament\Schemas\Schema;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\DailyReportEntryResource;
use App\Models\FormTemplate;
use App\Services\DailyReport\DailyReportEntryContextService;
use App\Services\DailyReport\UnifiedComplianceService;
use App\Services\DynamicForm\DynamicFormService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDailyReportEntry extends EditRecord
{
    protected static string $resource = DailyReportEntryResource::class;

    protected string $view = 'filament.pages.edit-daily-report-entry';

    public ?FormTemplate $formTemplate = null;
    public ?string $originalIndicatorId = null;
    public ?string $originalDate = null;
    private DailyReportEntryContextService $contextService;

    public function __construct()
    {
        $this->contextService = app(DailyReportEntryContextService::class);
    }

    /**
     * Mount the component
     */

    public function mount(int | string $record): void
    {
        // Load record first to get form_template_id
        $dailyReport = DailyReportEntryResource::getModel()::findOrFail($record);

        // Load form template BEFORE parent::mount() so it's available for form() method
        if ($dailyReport->form_template_id) {
            $this->formTemplate = FormTemplate::with(['formFields.options', 'imutProfile'])
                ->find($dailyReport->form_template_id);
        }

        // Store parameters for redirect
        $this->originalIndicatorId = request()->query('indicator') ?? $dailyReport->form_template_id;
        $this->originalDate = request()->query('date') ?? $dailyReport->report_date->format('Y-m-d');

        // Now call parent mount which will call form() method
        parent::mount($record);
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
        return $this->contextService->getFormTitle($this->formTemplate, 'Edit Laporan Harian');
    }

    /**
     * Get form description
     */
    public function getFormDescription(): ?string
    {
        return $this->formTemplate?->description;
    }

    /**
     * Get formatted date
     */
    public function getFormattedDate(): string
    {
        return $this->contextService->getFormattedDate($this->record->report_date->format('Y-m-d'));
    }

    /**
     * Get category badge color
     */
    public function getCategoryBadgeColor(): string
    {
        return $this->contextService->getCategoryBadgeColor($this->formTemplate);
    }

    /**
     * Configure the form
     */
    public function form(Schema $schema): Schema
    {
        if (!$this->formTemplate) {
            return $schema->components([]);
        }

        return $schema
            ->components(DynamicFormService::buildFormSchema($this->formTemplate, true, true))
            ->statePath('data')
            ->live();
    }

    /**
     * Mutate form data before fill - load existing field responses
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Load field responses from the record
        $fieldResponses = $this->record->fieldResponses()
            ->with('formField')
            ->get();

        // Map field responses to form data
        foreach ($fieldResponses as $response) {
            $field = $response->formField;
            $fieldKey = $field->field_key;
            $fieldValue = $response->field_value;

            // Handle different field types
            if ($field->field_type === 'time_duration' && is_array($fieldValue)) {
                // Time duration has sub-fields
                $data[$fieldKey . '_start_time'] = $fieldValue['start_time'] ?? null;
                $data[$fieldKey . '_end_time'] = $fieldValue['end_time'] ?? null;
                $data[$fieldKey . '_valid_duration_setting'] = $fieldValue['valid_duration_setting'] ?? null;
                $data[$fieldKey . '_valid_indicator'] = $fieldValue['valid_indicator'] ?? null;

            } elseif ($field->field_type === 'time_range' && is_array($fieldValue)) {
                // Time range has input_value
                $data[$fieldKey . '_input_value'] = $fieldValue['input_value'] ?? null;
            } else {
                // For other field types, use the field_value directly
                // If it's an array with single element, extract it
                if (is_array($fieldValue) && count($fieldValue) === 1 && isset($fieldValue[0])) {
                    $data[$fieldKey] = $fieldValue[0];
                } else {
                    $data[$fieldKey] = $fieldValue;
                }
            }
        }

        return $data;
    }

    /**
     * Mutate form data before save - update field responses
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Since we're using statePath('data'), the actual form data is in $data['data']
        $formData = $data['data'] ?? $data;

        // Update field responses based on form data
        if ($this->formTemplate) {
            $sortedFields = $this->formTemplate->formFields->sortBy('order_index');

            foreach ($sortedFields as $field) {
                $fieldKey = $field->field_key;
                $fieldValue = $formData[$fieldKey] ?? null;

                // Find existing field response or create new one
                $fieldResponse = $this->record->fieldResponses()
                    ->where('form_field_id', $field->id)
                    ->first();

                // Handle different field types
                if ($field->field_type === 'time_duration') {
                    $startTime = $formData[$fieldKey . '_start_time'] ?? null;
                    $endTime = $formData[$fieldKey . '_end_time'] ?? null;
                    $validDuration = $formData[$fieldKey . '_valid_duration_setting'] ?? null;
                    $validIndicator = $formData[$fieldKey . '_valid_indicator'] ?? null;

                    $compositeValue = [
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'valid_duration_setting' => $validDuration,
                        'valid_indicator' => $validIndicator,
                    ];

                    if ($fieldResponse) {
                        $fieldResponse->update([
                            'field_value' => $compositeValue,
                            'compliance_score' => ($startTime && $endTime) ? (($validIndicator == '1') ? 100 : 0) : 0,
                        ]);
                    } else {
                        $this->record->fieldResponses()->create([
                            'form_field_id' => $field->id,
                            'field_value' => $compositeValue,
                            'compliance_score' => ($startTime && $endTime) ? (($validIndicator == '1') ? 100 : 0) : 0,
                        ]);
                    }
                } elseif ($field->field_type === 'time_range') {
                    $inputValue = $formData[$fieldKey . '_input_value'] ?? null;

                    // Get start_time and end_time from validation_config
                    $validationConfig = $field->validation_config ?? [];
                    $startTime = $validationConfig['default_start_time'] ?? '00:00';
                    $endTime = $validationConfig['default_end_time'] ?? '23:59';

                    $validIndicator = $formData[$fieldKey . '_valid_indicator'] ?? null;

                    $compositeValue = [
                        'input_value' => $inputValue,
                        'start_time' => $startTime,
                        'end_time' => $endTime,
                        'valid_indicator' => $validIndicator,
                    ];

                    if ($fieldResponse) {
                        $fieldResponse->update([
                            'field_value' => $compositeValue,
                            'compliance_score' => $inputValue ? (($validIndicator == '1') ? 100 : 0) : 0,
                        ]);
                    } else {
                        $this->record->fieldResponses()->create([
                            'form_field_id' => $field->id,
                            'field_value' => $compositeValue,
                            'compliance_score' => $inputValue ? (($validIndicator == '1') ? 100 : 0) : 0,
                        ]);
                    }
                } else {
                    // For other field types
                    $processedValue = $fieldValue !== null ? (is_array($fieldValue) ? $fieldValue : [$fieldValue]) : [];

                    if ($fieldResponse) {
                        $fieldResponse->update([
                            'field_value' => $processedValue,
                            'compliance_score' => $fieldValue !== null ? ($field->calculateFieldScore($fieldValue) ?? 0) : 0,
                        ]);
                    } else {
                        $this->record->fieldResponses()->create([
                            'form_field_id' => $field->id,
                            'field_value' => $processedValue,
                            'compliance_score' => $fieldValue !== null ? ($field->calculateFieldScore($fieldValue) ?? 0) : 0,
                        ]);
                    }

                    // Update history suggestions for text fields
                    if ($field->field_type === 'text' && is_string($fieldValue) && !empty(trim($fieldValue))) {
                        $this->updateHistorySuggestions($field, $fieldValue);
                    }
                }
            }

            // Recalculate compliance using central service
            $complianceService = app(UnifiedComplianceService::class);
            $complianceResult = $complianceService->calculate($this->formTemplate, $formData);
            $this->record->update([
                'total_score' => $complianceResult['total_score'],
                'compliance_status' => $complianceResult['compliance_status'],
                'calculation_details' => $complianceResult,
            ]);
        }

        // Return the original data structure for Filament to handle
        return $data;
    }

    /**
     * Update history suggestions for text fields
     */
    private function updateHistorySuggestions($field, string $newValue): void
    {
        $currentSuggestions = $field->history_suggestions ?? [];

        // Add new value to the beginning if not already present
        if (!in_array($newValue, $currentSuggestions)) {
            array_unshift($currentSuggestions, $newValue);

            // Update the field
            $field->update(['history_suggestions' => $currentSuggestions]);
        }
    }



    /**
     * Get header actions
     */
    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Lihat Detail')
                ->icon('heroicon-o-eye')
                ->color('info'),
            DeleteAction::make()
                ->label('Hapus')
                ->icon('heroicon-o-trash')
                ->successNotificationTitle('Laporan berhasil dihapus'),
        ];
    }

    /**
     * Get success notification
     */
    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Laporan berhasil diperbarui')
            ->body('Perubahan telah berhasil disimpan');
    }

    /**
     * Redirect after save
     */
    protected function getRedirectUrl(): string
    {
        $params = [];
        if ($this->originalIndicatorId) {
            $params['indicator_id'] = $this->originalIndicatorId;
        }
        if ($this->originalDate) {
            $params['date'] = $this->originalDate;
        }

        $url = $this->getResource()::getUrl('index');
        return !empty($params) ? $url . '?' . http_build_query($params) : $url;
    }
}
