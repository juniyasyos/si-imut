<?php

namespace App\Services\FormBuilder;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TimePicker;
use Illuminate\Support\Str;

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
                        ->deleteAction(
                            fn(\Filament\Forms\Components\Actions\Action $action) => $action
                                ->label('Hapus Field')
                                ->icon('heroicon-o-trash')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->modalHeading('Konfirmasi Hapus Field')
                                ->modalDescription(
                                    'Apakah Anda yakin ingin menghapus field ini? ' .
                                        'Semua data yang telah diinputkan untuk field ini akan dihapus secara permanen dan tidak dapat dikembalikan.'
                                )
                                ->modalSubmitActionLabel('Ya, Hapus Field')
                        )
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
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, $set) {
                            // auto-generate field_key from label
                            if (!empty($state)) {
                                $set('field_key', Str::slug($state));
                            }
                        }),

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

                    Hidden::make('field_key')
                        ->default(fn($get) => Str::slug($get('field_label') ?? '')),

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
                                ->required()
                                ->afterStateUpdated(function ($state, $set) {
                                    if (!empty($state)) {
                                        $set('value', Str::slug($state));
                                    }
                                }),

                            Hidden::make('value')
                                ->default(fn($get) => Str::slug($get('label') ?? '')),

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
                ->collapsed(false)
                ->columnSpanFull(),

            // Composite Field Preview (untuk Time Duration & Time Range)
            Section::make('Preview Sub-Fields')
                ->description('Field ini akan otomatis membuat beberapa sub-field terkait.')
                ->schema([
                    Placeholder::make('composite_preview')
                        ->content(function ($get) {
                            $fieldType = $get('field_type');
                            if (!FormFieldMapper::isCompositeFieldType($fieldType)) {
                                return '';
                            }

                            $structure = FormFieldMapper::getCompositeFieldStructure($fieldType);
                            $fieldKey = $get('field_key') ?: 'field_key';

                            $html = '<div class="space-y-2 text-sm">';
                            $html .= '<p class="font-medium text-gray-700">Sub-fields yang akan dibuat:</p>';

                            foreach ($structure as $subKey => $config) {
                                $fullKey = $fieldKey . '_' . $subKey;
                                $required = $config['required'] ? ' <span class="text-red-500">*</span>' : '';
                                $readonly = $config['readonly'] ?? false ? ' (read-only)' : '';
                                $type = $config['type'] ?? 'text';
                                $typeDisplay = ucfirst(str_replace('_', ' ', $type));

                                $html .= "<div class=\"flex items-center space-x-2\">";
                                $html .= "<span class=\"inline-block w-2 h-2 bg-blue-500 rounded-full\"></span>";
                                $html .= "<code class=\"text-xs bg-gray-100 px-2 py-1 rounded\">{$fullKey}</code>";
                                $html .= "<span>{$config['label']}{$required}{$readonly} <em class=\"text-gray-500\">({$typeDisplay})</em></span>";

                                if (isset($config['options']) && is_array($config['options'])) {
                                    $optionsStr = implode(', ', array_map(fn($opt) => "'{$opt}'", $config['options']));
                                    $html .= "<span class=\"text-xs text-gray-600\">Opsi: {$optionsStr}</span>";
                                }

                                // Show default value for valid_duration_setting
                                if ($subKey === 'valid_duration_setting' && $fieldType === 'time_duration') {
                                    $defaultValue = $get('default_valid_duration') ?? 480;
                                    $html .= "<span class=\"text-xs text-blue-600\">Default: {$defaultValue} menit</span>";
                                }

                                $html .= "</div>";
                            }

                            $html .= '</div>';
                            return new \Illuminate\Support\HtmlString($html);
                        })
                        ->columnSpanFull(),
                ])
                ->visible(fn($get) => FormFieldMapper::isCompositeFieldType($get('field_type')))
                ->collapsed(false)
                ->columnSpanFull(),

            // Time Duration Specific Settings
            Section::make('Pengaturan Time Duration')
                ->schema([
                    Select::make('validation_config.threshold_type')
                        ->label('Tipe Validasi Threshold')
                        ->options([
                            'less_than' => 'Durasi harus kurang dari threshold (≤)',
                            'greater_than' => 'Durasi harus lebih dari threshold (≥)',
                        ])
                        ->default('less_than')
                        ->helperText('Pilih apakah durasi yang valid adalah kurang dari atau lebih dari threshold yang ditentukan.')
                        ->live()
                        ->reactive(),

                    TimePicker::make('validation_config.threshold')
                        ->label('Threshold Durasi Valid')
                        ->seconds(false)
                        ->suffix('JamMenit')
                        ->afterStateHydrated(function ($state, callable $set) {
                            if (blank($state)) {
                                $set('validation_config.threshold', '00:10');
                            }
                        })
                        ->helperText('Durasi maksimal yang dianggap valid. Akan otomatis terisi di form pengisian.'),
                ])
                ->visible(fn($get) => $get('field_type') === 'time_duration')
                ->collapsed(false)
                ->columnSpanFull(),

            // Conditional Logic Configuration  
            Section::make('Logika Kondisional')
                ->schema([
                    Toggle::make('has_conditional_logic')
                        ->label('Aktifkan Logika Kondisional')
                        ->helperText('Field ini akan bergantung pada nilai field lain; pada preview, jika kondisi tidak terpenuhi field akan tetap tampil tetapi dinonaktifkan.')
                        ->live(),

                    Grid::make(2)
                        ->schema([
                            Select::make('conditional_logic.depends_on_field')
                                ->label('Bergantung pada Field')
                                ->options(fn($get) => self::getAvailableFieldsForCondition($get('../../fields') ?? []))
                                ->helperText('Field yang mempengaruhi kondisi (pada preview, jika kondisi tidak terpenuhi field akan dinonaktifkan)')
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
                        ->helperText('Field akan aktif ketika dependency memiliki nilai pemicu; jika tidak, field akan tetap tampil tetapi dinonaktifkan pada preview.')
                        ->options(fn($get) => self::getDependentFieldOptions($get('../../fields') ?? [], $get('conditional_logic.depends_on_field')))
                        ->visible(fn($get) => $get('has_conditional_logic') && !empty($get('conditional_logic.depends_on_field')))
                        ->live()
                        ->columnSpanFull(),
                ])
                ->collapsed(false)
                ->visible(fn($get) => in_array($get('field_type'), ['single_select', 'multi_select', 'text', 'number', 'date', 'boolean']))
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
