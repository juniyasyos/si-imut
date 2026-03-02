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
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

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
                        ->readonly(fn($record) => self::shouldBeReadonly($record))
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label('Deskripsi Form')
                        ->maxLength(1000)
                        ->readOnly(fn($record) => self::shouldBeReadonly($record))
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
                        // ->disabled(fn($record) => self::shouldBeReadonly($record))
                        ->disabled()
                        ->default('auto_calculate')
                        ->helperText('Kalkulasi otomatis akan menghitung compliance berdasarkan bobot field dan nilai kritikalitas.'),

                    Toggle::make('auto_fail_on_critical')
                        ->label('Auto Fail pada Field Kritical')
                        ->disabled()
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
                        ->disabled(fn($record) => self::shouldBeReadonly($record))
                        ->addActionLabel('Tambah Field Baru')
                        ->reorderableWithButtons()
                        ->collapsible(true)
                        ->collapsed()
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
                ])
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
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, callable $get) {
                            // Reset compliance weight to 0 for text fields, allow editing for others
                            if ($state === 'text') {
                                $set('compliance_weight', '0');
                            } elseif ($get('compliance_weight') === '0') {
                                // If changing from text to other type and weight is 0, set to default
                                $set('compliance_weight', '2');
                            }

                            // Reset custom labels when changing field type
                            if (in_array($state, ['time_duration', 'time_range'])) {
                                // Keep existing custom labels if they exist, otherwise set defaults
                                $validationConfig = $get('validation_config') ?? [];
                                $customLabels = $validationConfig['custom_labels'] ?? [];
                                if (empty($customLabels['start_time'])) {
                                    $validationConfig['custom_labels']['start_time'] = 'Waktu Mulai';
                                    $set('validation_config', $validationConfig);
                                }
                                if (empty($customLabels['end_time'])) {
                                    $validationConfig['custom_labels']['end_time'] = 'Waktu Selesai';
                                    $set('validation_config', $validationConfig);
                                }
                            } else {
                                // Clear custom labels for non-composite fields
                                $validationConfig = $get('validation_config') ?? [];
                                unset($validationConfig['custom_labels']);
                                $set('validation_config', $validationConfig);
                            }
                        }),

                    Select::make('compliance_weight')
                        ->label('Bobot Compliance')
                        ->options([
                            '0' => 'Tidak Berkontribusi (0)',
                            '1' => 'Rendah (1)',
                            '2' => 'Normal (2)',
                            '3' => 'Tinggi (3)',
                            '5' => 'Sangat Tinggi (5)',
                        ])
                        ->default('2')
                        ->disabled()
                        ->visible()
                        ->helperText(fn($get) => $get('field_type') === 'text'
                            ? 'Field teks tidak berkontribusi pada compliance score (otomatis 0)'
                            : 'Bobot untuk kalkulasi compliance score')
                        ->afterStateHydrated(function ($state, callable $set, callable $get) {
                            $fieldType = $get('field_type');
                            if ($fieldType === 'text') {
                                $set('compliance_weight', '0');
                            } elseif (blank($state)) {
                                $set('compliance_weight', '2');
                            }

                            // Set default custom labels for composite fields
                            if (in_array($fieldType, ['time_duration', 'time_range'])) {
                                $validationConfig = $get('validation_config') ?? [];
                                $customLabels = $validationConfig['custom_labels'] ?? [];
                                if (empty($customLabels['start_time'])) {
                                    $validationConfig['custom_labels']['start_time'] = 'Waktu Mulai';
                                    $set('validation_config', $validationConfig);
                                }
                                if (empty($customLabels['end_time'])) {
                                    $validationConfig['custom_labels']['end_time'] = 'Waktu Selesai';
                                    $set('validation_config', $validationConfig);
                                }
                            }
                        }),
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

                    TextInput::make('validation_config.max_length')
                        ->label('Panjang Maksimal')
                        ->numeric()
                        ->default(255)
                        ->visible(fn($get) => $get('field_type') === 'text'),
                ]),

            // Default Value & History untuk Text Fields
            Section::make('Nilai Default & Riwayat')
                ->schema([
                    TableRepeater::make('history_suggestions')
                        ->label('Saran Riwayat Input')
                        ->headers([
                            Header::make('value')->label('Saran Input')->width('100%'),
                        ])
                        ->schema([
                            TextInput::make('value')
                                ->label('')
                                ->placeholder('Masukkan saran input...'),
                        ])
                        ->defaultItems(0)
                        ->addActionLabel('Tambah Saran')
                        ->helperText('Saran input akan muncul sebagai dropdown saat user mengetik. Maksimal 10 saran akan disimpan.')
                        ->visible(fn($get) => $get('field_type') === 'text')
                        ->columnSpanFull(),
                ])
                ->visible(fn($get) => $get('field_type') === 'text')
                ->collapsed(false)
                ->columnSpanFull(),

            // Options untuk field yang membutuhkan
            TableRepeater::make('options')
                ->label('Opsi Pilihan')
                ->headers([
                    Header::make('label')->label('Label Opsi')->width('70%'),
                    Header::make('is_correct')->label('Benar/Pass (Centang jika opsi ini menandakan compliance/benar)')->width('30%'),
                ])
                ->schema([
                    TextInput::make('label')
                        ->label('')
                        ->placeholder('Masukkan label opsi...')
                        ->afterStateUpdated(function ($state, $set) {
                            if (!empty($state)) {
                                $set('value', Str::slug($state));
                            }
                        }),

                    Toggle::make('is_correct')
                        ->label('')
                        ->default(true),

                    Hidden::make('value')
                        ->default(fn($get) => Str::slug($get('label') ?? '')),
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
                            $html .= '<p class="font-small text-gray-700 dark:text-gray-100">Sub-fields yang akan dibuat:</p>';

                            foreach ($structure as $subKey => $config) {
                                // Use custom label if provided, otherwise use default label
                                $validationConfig = $get('validation_config') ?? [];
                                $customLabels = $validationConfig['custom_labels'] ?? [];
                                $actualLabel = $customLabels[$subKey] ?? $config['label'];
                                $fullKey = $fieldKey . '_' . $subKey;
                                $required = $config['required'] ? ' <span class="text-red-500">*</span>' : '';
                                $readonly = $config['readonly'] ?? false ? ' (read-only)' : '';
                                $type = $config['type'] ?? 'text';
                                $typeDisplay = ucfirst(str_replace('_', ' ', $type));

                                $html .= "<div class=\"flex items-center space-x-2\">";
                                $html .= "<span class=\"inline-block w-2 h-2 bg-blue-500 rounded-full\"></span>";
                                $html .= "<code class=\"text-xs bg-gray-100 dark:bg-slate-800 px-2 py-1 rounded\">{$fullKey}</code>";
                                $html .= "<span>{$actualLabel}{$required}{$readonly} <em class=\"text-gray-500\">({$typeDisplay})</em></span>";

                                if (isset($config['options']) && is_array($config['options'])) {
                                    $optionsStr = implode(', ', array_map(fn($opt) => "'{$opt}'", $config['options']));
                                    $html .= "<span class=\"text-xs text-gray-600\">Opsi: {$optionsStr}</span>";
                                }

                                // Show default value for valid_duration_setting
                                if ($subKey === 'valid_duration_setting' && $fieldType === 'time_duration') {
                                    $defaultValue = $get('default_valid_duration') ?? 480;
                                    $html .= "<span class=\"text-xs text-blue-600\">Default: {$defaultValue} menit</span>";
                                }

                                // Show default values for time_range
                                if ($fieldType === 'time_range') {
                                    if ($subKey === 'start_time') {
                                        $defaultStart = $get('validation_config.default_start_time') ?? '08:00';
                                        $html .= "<span class=\"text-xs text-blue-600\">Default: {$defaultStart}</span>";
                                    } elseif ($subKey === 'end_time') {
                                        $defaultEnd = $get('validation_config.default_end_time') ?? '17:00';
                                        $html .= "<span class=\"text-xs text-blue-600\">Default: {$defaultEnd}</span>";
                                    }
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
                    Grid::make(2)
                        ->schema([
                            TextInput::make('validation_config.custom_labels.start_time')
                                ->label('Label Field Waktu Mulai')
                                ->placeholder('Waktu Mulai')
                                ->default('Waktu Mulai')
                                ->helperText('Label yang ditampilkan untuk field waktu mulai')
                                ->maxLength(100)
                                ->live(onBlur: true),

                            TextInput::make('validation_config.custom_labels.end_time')
                                ->label('Label Field Waktu Selesai')
                                ->placeholder('Waktu Selesai')
                                ->default('Waktu Selesai')
                                ->helperText('Label yang ditampilkan untuk field waktu selesai')
                                ->maxLength(100)
                                ->live(onBlur: true),
                        ]),

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
                        ->format('H:i') 
                        ->displayFormat('H:i')
                        ->hoursStep(1)
                        ->minutesStep(1)
                        ->suffix('Jam:Menit')
                        ->afterStateHydrated(function ($state, callable $set) {
                            if (blank($state)) {
                                $set('validation_config.threshold', '00:10');
                            }
                        })
                        ->helperText('Durasi maksimal yang dianggap valid. Format 24 jam (contoh: 01:30 untuk 1 jam 30 menit).'),
                ])
                ->visible(fn($get) => $get('field_type') === 'time_duration')
                ->collapsed(false)
                ->columnSpanFull(),

            // Time Range Specific Settings
            Section::make('Pengaturan Time Range')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('validation_config.custom_labels.start_time')
                                ->label('Label Field Waktu Mulai')
                                ->placeholder('Waktu Mulai')
                                ->helperText('Label yang ditampilkan untuk field waktu mulai')
                                ->maxLength(100)
                                ->live(onBlur: true),

                            TextInput::make('validation_config.custom_labels.end_time')
                                ->label('Label Field Waktu Selesai')
                                ->placeholder('Waktu Selesai')
                                ->helperText('Label yang ditampilkan untuk field waktu selesai')
                                ->maxLength(100)
                                ->live(onBlur: true),
                        ]),

                    TimePicker::make('validation_config.default_start_time')
                        ->label('Default Waktu Mulai Rentang')
                        ->seconds(false)
                        ->afterStateHydrated(function ($state, callable $set) {
                            if (blank($state)) {
                                $set('validation_config.default_start_time', '08:00');
                            }
                        })
                        ->helperText('Waktu mulai default untuk rentang. Akan di-set otomatis di form.'),

                    TimePicker::make('validation_config.default_end_time')
                        ->label('Default Waktu Selesai Rentang')
                        ->seconds(false)
                        ->afterStateHydrated(function ($state, callable $set) {
                            if (blank($state)) {
                                $set('validation_config.default_end_time', '17:00');
                            }
                        })
                        ->helperText('Waktu selesai default untuk rentang. Akan di-set otomatis di form.'),
                ])
                ->visible(fn($get) => $get('field_type') === 'time_range')
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
                                ->options(fn($get) => self::getAvailableFieldsForCondition(
                                    $get('../../fields') ?? [],
                                    ($get('field_key'))
                                ))
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
     * Only returns fields that appear BEFORE the current field (higher up in the form)
     * Uses order_index to determine field position instead of array key
     * 
     * @param array $fields All form fields
     * @param string $currentFieldKey The field_key of the current field being edited (required non-null)
     * @return array Options of available fields that appear before current field
     */
    private static function getAvailableFieldsForCondition(array $fields, string $currentFieldKey): array
    {
        $options = [];

        // If currentFieldKey is empty (field baru tanpa field_key), return empty
        if (empty($currentFieldKey)) {
            return $options;
        }

        $currentOrderIndex = null;

        // First pass: find current field's order_index using field_key
        foreach ($fields as $field) {
            $fieldKey = $field['field_key'] ?? '';
            if (!empty($fieldKey) && $fieldKey === $currentFieldKey) {
                $currentOrderIndex = $field['order_index'] ?? 0;
                break;
            }
        }

        // Second pass: collect only fields that appear BEFORE current field (using order_index)
        foreach ($fields as $field) {
            $fieldKey = $field['field_key'] ?? '';
            $fieldLabel = $field['field_label'] ?? '';

            if (!empty($fieldKey) && !empty($fieldLabel)) {
                // Skip if this is the current field
                if ($fieldKey === $currentFieldKey) {
                    continue;
                }

                // Skip if this field appears after or at same position as current field
                $fieldOrderIndex = $field['order_index'] ?? 0;
                if ($currentOrderIndex !== null && $fieldOrderIndex >= $currentOrderIndex) {
                    continue;
                }

                $options[$fieldKey] = $fieldLabel;
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

    private static function shouldBeReadonly(Model $record): bool
    {
        return !($record->imutData->created_by === Auth::id()) && Auth::user()?->can('delete_imut::profile');
    }
}
