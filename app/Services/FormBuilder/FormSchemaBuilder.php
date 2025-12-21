<?php

namespace App\Services\FormBuilder;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\TagsInput;

class FormSchemaBuilder
{
    public static function buildFormSchema(): array
    {
        return [
            Section::make('Informasi Dasar Form')
                ->schema([
                    TextInput::make('title')
                        ->label('Judul Form')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Deskripsi Form')
                        ->maxLength(1000)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Section::make('Pengaturan Compliance')
                ->schema([
                    Select::make('compliance_method')
                        ->label('Metode Compliance')
                        ->options([
                            'auto_calculate' => 'Kalkulasi Otomatis (Direkomendasikan)',
                            'manual_check' => 'Pemeriksaan Manual',
                        ])
                        ->default('auto_calculate')
                        ->helperText('Kalkulasi otomatis akan menghitung compliance berdasarkan bobot field dan nilai kritikalitas.'),

                    Toggle::make('auto_fail_on_critical')
                        ->label('Auto Fail pada Field Kritical')
                        ->helperText('Jika diaktifkan, form akan langsung dianggap tidak compliant jika ada field kritical yang tidak terisi.')
                        ->default(true),
                ])
                ->columns(2),

            Section::make('Field Builder - Sederhana untuk Pelaporan Mutu')
                ->description('🎯 Fokus pada 3 elemen utama: (1) Pengumpul Data, (2) Data Validasi, (3) Matching/Compliance. Pilih field type yang sesuai kebutuhan pelaporan.')
                ->schema([
                    Repeater::make('fields')
                        ->label('Fields')
                        ->schema(self::getFieldSchema())
                        ->defaultItems(0)
                        ->addActionLabel('Tambah Field Baru')
                        ->reorderableWithButtons()
                        ->collapsible()
                        ->itemLabel(function (array $state): ?string {
                            $icon = FormFieldMapper::getFieldIcon($state['field_type'] ?? 'text_input');
                            $name = $state['field_label'] ?? 'Field Baru';
                            $critical = ($state['is_critical_field'] ?? false) ? ' 🔥' : '';

                            return "📋 {$name}{$critical}";
                        })
                        ->columnSpanFull()
                ]),
        ];
    }

    private static function getFieldSchema(): array
    {
        return [
            Grid::make(3)
                ->schema([
                    TextInput::make('field_label')
                        ->label('Nama Field')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true),

                    Select::make('field_type')
                        ->label('Tipe Field')
                        ->options(FormFieldMapper::getAllFieldTypes())
                        ->required()
                        ->default('short_text')
                        ->live(),

                    Select::make('compliance_weight')
                        ->label('Bobot Compliance')
                        ->options([
                            1 => 'Rendah (1)',
                            2 => 'Normal (2)',
                            3 => 'Tinggi (3)',
                            5 => 'Sangat Tinggi (5)',
                        ])
                        ->default(2)
                        ->helperText('Bobot untuk kalkulasi compliance score'),
                ]),

            Textarea::make('field_description')
                ->label('Deskripsi Field')
                ->maxLength(500)
                ->columnSpanFull(),

            Grid::make(2)
                ->schema([
                    Toggle::make('is_critical_field')
                        ->label('Field Kritical')
                        ->helperText('Field kritical harus terisi untuk compliance'),

                    TextInput::make('field_key')
                        ->label('Key Field')
                        ->helperText('Otomatis diisi dari nama field')
                        ->maxLength(255),
                ]),

            // Validation Configuration (Simplified)
            Grid::make(3)
                ->schema([
                    Toggle::make('validation_config.required')
                        ->label('Wajib Diisi'),

                    TextInput::make('validation_config.min')
                        ->label('Nilai Minimal')
                        ->numeric()
                        ->visible(fn($get) => $get('field_type') === 'number'),

                    TextInput::make('validation_config.max')
                        ->label('Nilai Maksimal')
                        ->numeric()
                        ->visible(fn($get) => $get('field_type') === 'number'),
                ]),

            // Options untuk field yang membutuhkan
            Repeater::make('options')
                ->label('Opsi Pilihan')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('label')
                                ->label('Label Opsi')
                                ->required(),

                            TextInput::make('value')
                                ->label('Value Opsi')
                                ->required(),

                            Toggle::make('is_correct')
                                ->label('Opsi Benar/Pass')
                                ->helperText('Centang jika opsi ini menandakan compliance/benar')
                                ->default(true),
                        ]),
                ])
                ->defaultItems(0)
                ->addActionLabel('Tambah Opsi')
                ->visible(fn($get) => FormFieldMapper::requiresOptions($get('field_type')))
                ->columnSpanFull(),

            // Simple Compliance Rules for Multi-Select
            Section::make('Aturan Compliance (untuk Multi-Select)')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('compliance_rules.minimum_correct')
                                ->label('Minimal Pilihan Benar')
                                ->numeric()
                                ->default(1)
                                ->helperText('Minimal berapa opsi benar yang harus dipilih untuk Pass'),

                            Toggle::make('compliance_rules.allow_wrong_selections')
                                ->label('Boleh Ada Pilihan Salah')
                                ->default(true)
                                ->helperText('Jika OFF, pilih opsi salah = otomatis Fail'),
                        ]),
                ])
                ->visible(fn($get) => $get('field_type') === 'multi_select')
                ->collapsed()
                ->columnSpanFull(),

            // Conditional Logic Configuration  
            Section::make('Logika Kondisional')
                ->schema([
                    Toggle::make('has_conditional_logic')
                        ->label('Aktifkan Logika Kondisional')
                        ->helperText('Field ini akan muncul/hilang berdasarkan field lain')
                        ->live(),

                    Grid::make(2)
                        ->schema([
                            Select::make('conditional_logic.depends_on_field')
                                ->label('Bergantung pada Field')
                                ->options(fn($get) => self::getAvailableFieldsForCondition($get('../../fields') ?? []))
                                ->helperText('Field yang akan mempengaruhi visibility')
                                ->visible(fn($get) => $get('has_conditional_logic'))
                                ->live(),

                            Select::make('conditional_logic.condition_type')
                                ->label('Jenis Kondisi')
                                ->options([
                                    'show_when' => 'Tampilkan Ketika',
                                    'hide_when' => 'Sembunyikan Ketika',
                                    'required_when' => 'Wajib Ketika',
                                ])
                                ->default('show_when')
                                ->visible(fn($get) => $get('has_conditional_logic')),
                        ]),

                    CheckboxList::make('conditional_logic.trigger_values')
                        ->label('Nilai Pemicu')
                        ->helperText('Field akan muncul/hilang ketika field dependency memiliki nilai yang dipilih')
                        ->options(fn($get) => self::getDependentFieldOptions($get('../../fields') ?? [], $get('conditional_logic.depends_on_field')))
                        ->visible(fn($get) => $get('has_conditional_logic') && !empty($get('conditional_logic.depends_on_field')))
                        ->live()
                        ->columnSpanFull(),
                ])
                ->collapsed()
                ->visible(fn($get) => in_array($get('field_type'), ['single_select', 'multi_select', 'text', 'number']))
                ->columnSpanFull(),
        ];
    }

    /**
     * Get available fields for conditional logic dependency
     */
    private static function getAvailableFieldsForCondition(array $fields): array
    {
        $options = [];

        foreach ($fields as $index => $field) {
            if (!empty($field['field_key']) && !empty($field['field_label'])) {
                $options[$field['field_key']] = $field['field_label'];
            }
        }

        return $options;
    }

    /**
     * Get options from dependent field for trigger values
     */
    private static function getDependentFieldOptions(array $fields, ?string $dependentFieldKey): array
    {
        if (empty($dependentFieldKey)) {
            return [];
        }

        $options = [];

        foreach ($fields as $field) {
            if (($field['field_key'] ?? '') === $dependentFieldKey) {
                // Get options from the dependent field
                $fieldOptions = $field['options'] ?? [];

                foreach ($fieldOptions as $option) {
                    $value = $option['value'] ?? $option['option_value'] ?? '';
                    $text = $option['label'] ?? $option['option_text'] ?? $value;

                    if (!empty($value)) {
                        $options[$value] = $text;
                    }
                }

                break;
            }
        }

        return $options;
    }
}
