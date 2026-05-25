<?php

namespace App\Traits\DailyReport;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use App\Models\FormTemplate;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Placeholder;

trait FormHandlerTrait
{
    /**
     * Get the form for report entry - Simple Google Form style
     */
    public function reportEntryForm(Schema $form): Schema
    {
        if (!$this->formTemplate) {
            return $form->components([
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

        foreach ($formFields as $field) {
            $fieldKey = $field->field_key;

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
            ->components($schema)
            ->statePath('reportData')
            ->live();
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
     * Initialize empty report data
     */
    protected function initializeReportData(): void
    {
        $this->reportData = [
            'field_responses' => [],
            'notes' => '',
        ];

        if ($this->formTemplate) {
            $formFields = $this->formTemplate->formFields;
            foreach ($formFields as $field) {
                $defaultValue = match ($field->field_type) {
                    'text', 'textarea' => '',
                    'number' => null,
                    'single_select', 'boolean' => null,
                    'multi_select' => [],
                    default => null,
                };

                $this->reportData['field_responses'][$field->field_key] = $defaultValue;
            }
        }

        $this->reportEntryForm->fill($this->reportData);
    }
}
