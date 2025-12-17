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

            Section::make('Field Builder')
                ->description('💡 Tips: Field yang paling penting untuk harian adalah pengumpul data, validasi dan matching data.')
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
                            $name = $state['field_name'] ?? 'Field Baru';
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
                    TextInput::make('field_name')
                        ->label('Nama Field')
                        ->required()
                        ->maxLength(255)
                        ->live(onBlur: true),

                    Select::make('field_type')
                        ->label('Tipe Field')
                        ->options(FormFieldMapper::getAllFieldTypes())
                        ->required()
                        ->default('text_input')
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

            // Validation Configuration
            Fieldset::make('Konfigurasi Validasi')
                ->schema([
                    Group::make([
                        Toggle::make('validation_config.required')
                            ->label('Wajib Diisi'),

                        TextInput::make('validation_config.min_length')
                            ->label('Panjang Minimal')
                            ->numeric()
                            ->visible(fn($get) => in_array($get('field_type'), ['text_input', 'textarea_input'])),

                        TextInput::make('validation_config.max_length')
                            ->label('Panjang Maksimal')
                            ->numeric()
                            ->visible(fn($get) => in_array($get('field_type'), ['text_input', 'textarea_input'])),

                        TextInput::make('validation_config.min')
                            ->label('Nilai Minimal')
                            ->numeric()
                            ->visible(fn($get) => $get('field_type') === 'numeric_input'),

                        TextInput::make('validation_config.max')
                            ->label('Nilai Maksimal')
                            ->numeric()
                            ->visible(fn($get) => $get('field_type') === 'numeric_input'),

                        TextInput::make('validation_config.regex')
                            ->label('Pattern Regex')
                            ->visible(fn($get) => in_array($get('field_type'), ['text_input', 'phone_input'])),
                    ])->columns(3),
                ])
                ->columnSpanFull(),

            // Options untuk field yang membutuhkan
            Repeater::make('options')
                ->label('Opsi Pilihan')
                ->schema([
                    Grid::make(3)
                        ->schema([
                            TextInput::make('option_text')
                                ->label('Label Opsi')
                                ->required(),

                            TextInput::make('option_value')
                                ->label('Value Opsi')
                                ->required(),

                            Select::make('compliance_value')
                                ->label('Nilai Compliance')
                                ->options([
                                    0 => 'Tidak Compliant (0)',
                                    0.5 => 'Sebagian Compliant (0.5)',
                                    1 => 'Compliant Penuh (1)',
                                ])
                                ->default(1)
                                ->helperText('Nilai untuk kalkulasi compliance'),
                        ]),
                ])
                ->defaultItems(0)
                ->addActionLabel('Tambah Opsi')
                ->visible(fn($get) => FormFieldMapper::requiresOptions($get('field_type')))
                ->columnSpanFull(),

            // Conditional Logic
            Fieldset::make('Logic Kondisional (Opsional)')
                ->schema([
                    Select::make('conditional_logic.trigger_field')
                        ->label('Field Pemicu')
                        ->options(function ($get) {
                            $formData = $get('../../');
                            return self::getAvailableFieldsFromState($formData);
                        })
                        ->live(),

                    Select::make('conditional_logic.trigger_condition')
                        ->label('Kondisi')
                        ->options([
                            'equals' => 'Sama dengan',
                            'not_equals' => 'Tidak sama dengan',
                            'contains' => 'Mengandung',
                            'not_empty' => 'Tidak kosong',
                            'empty' => 'Kosong',
                        ])
                        ->live(),

                    Select::make('conditional_logic.trigger_value')
                        ->label('Nilai Pemicu')
                        ->options(function ($get) {
                            $triggerField = $get('conditional_logic.trigger_field');
                            $formData = $get('../../');
                            return self::getFieldOptionsFromState($formData, $triggerField);
                        })
                        ->visible(fn($get) => in_array($get('conditional_logic.trigger_condition'), ['equals', 'not_equals', 'contains'])),

                    Select::make('conditional_logic.action')
                        ->label('Aksi')
                        ->options([
                            'show' => 'Tampilkan Field',
                            'hide' => 'Sembunyikan Field',
                            'require' => 'Wajibkan Field',
                            'unrequire' => 'Tidak Wajibkan Field',
                        ]),
                ])
                ->columns(2)
                ->columnSpanFull(),
        ];
    }

    private static function getAvailableFieldsFromState($formData): array
    {
        $fields = $formData['fields'] ?? [];
        $options = [];

        foreach ($fields as $field) {
            if (!empty($field['field_name'])) {
                $key = $field['field_key'] ?? \Illuminate\Support\Str::slug($field['field_name']);
                $options[$key] = $field['field_name'];
            }
        }

        return $options;
    }

    private static function getFieldOptionsFromState($formData, ?string $fieldKey): array
    {
        if (!$fieldKey) return [];

        $fields = $formData['fields'] ?? [];

        foreach ($fields as $field) {
            $currentKey = $field['field_key'] ?? \Illuminate\Support\Str::slug($field['field_name']);
            if ($currentKey === $fieldKey) {
                $options = $field['options'] ?? [];
                $result = [];

                foreach ($options as $option) {
                    if (is_array($option) && isset($option['option_text'])) {
                        $result[$option['option_value']] = $option['option_text'];
                    }
                }

                return $result;
            }
        }

        return [];
    }
}
