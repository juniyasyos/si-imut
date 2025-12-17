<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Models\FormField;
use App\Models\FormHeader;
use App\Models\ImutData;
use App\Models\FormTemplate;
use App\Models\EnhancedFormField;
use App\Models\FormFieldOption;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Builder\Block;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ManageFormBuilder extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ImutDataResource::class;

    protected static string $view = 'filament.resources.imut-data-resource.pages.manage-form-builder';

    public ?array $data = [];

    public ?ImutData $record = null;

    public function mount(ImutData $record): void
    {
        $this->record = $record;

        // Try to load from new FormTemplate first, fallback to old FormHeader
        $formTemplate = FormTemplate::where('imut_data_id', $this->record->id)->first();

        if ($formTemplate) {
            $this->loadFromFormTemplate($formTemplate);
        } else {
            $this->loadFromLegacyFormHeader();
        }
    }

    private function loadFromFormTemplate(FormTemplate $formTemplate): void
    {
        $fields = $formTemplate->formFields->map(function ($field) {
            $options = $field->options->map(function ($option) {
                return [
                    'option_text' => $option->option_text,
                    'option_value' => $option->option_value,
                    'compliance_value' => $option->compliance_value,
                ];
            })->toArray();

            return [
                'id' => $field->id,
                'field_key' => $field->field_key,
                'field_name' => $field->field_name,
                'field_description' => $field->field_description,
                'field_type' => $field->field_type,
                'validation_config' => $field->validation_config,
                'compliance_weight' => $field->compliance_weight,
                'is_critical_field' => $field->is_critical_field,
                'conditional_logic' => $field->conditional_logic,
                'options' => $options,
                'order_index' => $field->order_index,
            ];
        })->toArray();

        $this->form->fill([
            'title' => $formTemplate->title,
            'description' => $formTemplate->description,
            'compliance_method' => $formTemplate->compliance_method,
            'auto_fail_on_critical' => $formTemplate->auto_fail_on_critical,
            'fields' => $fields,
        ]);
    }

    private function loadFromLegacyFormHeader(): void
    {
        $formHeader = FormHeader::where('imutdata_id', $this->record->id)->first();

        if ($formHeader) {
            $fields = $formHeader->formFields->map(function ($field) {
                $options = $field->options;

                // Konversi options dari array sederhana ke format repeater
                if (is_array($options) && !empty($options)) {
                    // Cek apakah sudah format object [{label, value}] atau masih array sederhana ["item1", "item2"]
                    $firstItem = reset($options);

                    if (!is_array($firstItem)) {
                        // Konversi dari array sederhana ke format repeater
                        $options = collect($options)->map(function ($item) {
                            return [
                                'label' => $item,
                                'value' => Str::slug($item, '_'),
                            ];
                        })->toArray();
                    }
                }

                return [
                    'id' => $field->id,
                    'field_key' => $field->key,
                    'field_label' => $field->label,
                    'field_description' => $field->description,
                    'field_type' => $this->mapLegacyFieldType($field->type),
                    'validation_config' => ['required' => $field->is_required],
                    'compliance_weight' => 1,
                    'is_critical_field' => false,
                    'conditional_logic' => null,
                    'options' => $options,
                    'order_index' => $field->order,
                ];
            })->toArray();

            $this->form->fill([
                'title' => $formHeader->title,
                'description' => $formHeader->description,
                'compliance_method' => 'auto_calculate',
                'auto_fail_on_critical' => true,
                'fields' => $fields,
            ]);
        } else {
            $this->form->fill([
                'title' => '',
                'description' => '',
                'compliance_method' => 'auto_calculate',
                'auto_fail_on_critical' => true,
                'fields' => [],
            ]);
        }
    }

    private function mapLegacyFieldType(string $type): string
    {
        return match ($type) {
            'bool' => 'boolean',
            'select' => 'single_select',
            'radio' => 'single_select',
            'checkbox' => 'multi_select',
            'textarea' => 'long_text',
            default => 'short_text'
        };
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('📋 Konfigurasi Laporan Harian')
                    ->description('Pengaturan form untuk pengisian laporan harian indikator mutu dengan sistem compliance otomatis')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Laporan Harian')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Laporan Harian Kepatuhan Cuci Tangan')
                            ->helperText('Judul akan ditampilkan di halaman pengisian laporan harian')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Instruksi Pengisian')
                            ->rows(3)
                            ->placeholder('Berikan petunjuk pengisian untuk petugas yang melaporkan...')
                            ->helperText('Deskripsi akan membantu petugas memahami cara pengisian laporan')
                            ->columnSpanFull(),

                        Select::make('compliance_method')
                            ->label('Metode Perhitungan Compliance')
                            ->options([
                                'auto_calculate' => '🤖 Auto Calculate - Hitung otomatis berdasarkan field',
                                'weighted' => '⚖️ Weighted Scoring - Berdasarkan bobot field',
                                'manual' => '✋ Manual - Input manual oleh user'
                            ])
                            ->default('auto_calculate')
                            ->required()
                            ->reactive()
                            ->helperText('Pilih cara sistem menghitung compliance score'),

                        Toggle::make('auto_fail_on_critical')
                            ->label('Auto Fail pada Field Critical')
                            ->helperText('Jika diaktifkan, gagal pada field critical akan langsung membuat compliance = 0')
                            ->default(true)
                            ->inline(false),
                    ]),

                Section::make('🛠️ Field Pengumpul Data')
                    ->description('Desain field untuk pengumpulan data dengan validasi otomatis dan conditional logic')
                    ->schema([
                        Repeater::make('fields')
                            ->label('Daftar Field Laporan')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('field_label')
                                            ->label('📌 Nama Field / Pertanyaan')
                                            ->required()
                                            ->placeholder('Contoh: Apakah petugas melakukan cuci tangan?')
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if (empty($get('field_key'))) {
                                                    $set('field_key', Str::slug($state, '_'));
                                                }
                                            })
                                            ->columnSpanFull(),

                                        Textarea::make('field_description')
                                            ->label('📝 Deskripsi / Petunjuk Pengisian')
                                            ->placeholder('Contoh: Observasi petugas saat melakukan kontak dengan pasien')
                                            ->helperText('Panduan untuk pengumpul data')
                                            ->rows(2)
                                            ->columnSpanFull(),

                                        Select::make('field_type')
                                            ->label('Tipe Field')
                                            ->required()
                                            ->options([
                                                // Basic Types
                                                'text' => '📝 Text Input',
                                                'number' => '🔢 Number Input',
                                                'date' => '📅 Date Picker',
                                                'boolean' => '✅ Yes/No Choice',

                                                // Selection Types
                                                'single_select' => '🔘 Single Selection',
                                                'multi_select' => '☑️ Multiple Selection',
                                                'rating_scale' => '⭐ Rating Scale (1-5)',

                                                // Time-based Types
                                                'time_duration' => '⏱️ Time Duration (minutes)',
                                                'time_range' => '🕐 Time Range Validation',
                                                'datetime' => '📅⏰ Date & Time',

                                                // Advanced Types
                                                'conditional_trigger' => '🔄 Conditional Trigger',
                                                'compliance_checker' => '✅ Auto Compliance Check',
                                                'weighted_score' => '⚖️ Weighted Scoring Field'
                                            ])
                                            ->reactive()
                                            ->columnSpan(1),

                                        Toggle::make('is_critical_field')
                                            ->label('🚨 Critical Field')
                                            ->helperText('Field yang akan menyebabkan auto-fail jika tidak sesuai')
                                            ->default(false)
                                            ->inline(false)
                                            ->columnSpan(1),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),

                                Section::make('⚙️ Konfigurasi Validasi & Scoring')
                                    ->schema([
                                        KeyValue::make('validation_config')
                                            ->label('Aturan Validasi')
                                            ->keyLabel('Parameter')
                                            ->valueLabel('Nilai')
                                            ->reorderable(false)
                                            ->addActionLabel('+ Tambah Aturan')
                                            ->helperText('Contoh: required=true, min_selections=2, max_minutes=40')
                                            ->columnSpan(2),

                                        TextInput::make('compliance_weight')
                                            ->label('Bobot Compliance')
                                            ->numeric()
                                            ->default(1)
                                            ->step(1)
                                            ->minValue(1)
                                            ->helperText('Semakin tinggi semakin berpengaruh pada skor total')
                                            ->columnSpan(1),
                                    ])
                                    ->columns(3)
                                    ->collapsed()
                                    ->columnSpanFull(),

                                Section::make('🔄 Conditional Logic')
                                    ->description('Atur field mana yang ditampilkan berdasarkan jawaban field sebelumnya')
                                    ->schema([
                                        Select::make('conditional_parent')
                                            ->label('Field Trigger')
                                            ->options(function (callable $get) {
                                                $allFields = $get('../../fields') ?? [];
                                                $currentIndex = $get('../../currentIndex');

                                                $options = [];
                                                for ($i = 0; $i < $currentIndex; $i++) {
                                                    if (isset($allFields[$i]['field_label'])) {
                                                        $options[$allFields[$i]['field_key'] ?? "field_$i"] = $allFields[$i]['field_label'];
                                                    }
                                                }
                                                return $options;
                                            })
                                            ->placeholder('Pilih field yang mengontrol visibility')
                                            ->reactive()
                                            ->columnSpan(2),

                                        KeyValue::make('conditional_rules')
                                            ->label('Aturan Conditional')
                                            ->keyLabel('Nilai Trigger')
                                            ->valueLabel('Aksi')
                                            ->addActionLabel('+ Tambah Aturan')
                                            ->helperText('Contoh: tidak=hide_all_below, ya=show_fields')
                                            ->visible(fn(callable $get) => !empty($get('conditional_parent')))
                                            ->columnSpan(2),
                                    ])
                                    ->columns(2)
                                    ->collapsed()
                                    ->columnSpanFull(),

                                Section::make('Opsi Pilihan')
                                    ->description('Tambahkan pilihan untuk select, radio, atau checkbox dengan nilai compliance')
                                    ->schema([
                                        Repeater::make('options')
                                            ->label('')
                                            ->schema([
                                                TextInput::make('option_label')
                                                    ->label('Label Tampilan')
                                                    ->required()
                                                    ->placeholder('Ya / Sesuai Standard')
                                                    ->reactive()
                                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                        // Auto-generate key dari label jika kosong
                                                        if (empty($get('option_key'))) {
                                                            $set('option_key', Str::slug($state, '_'));
                                                        }
                                                    })
                                                    ->columnSpan(2),

                                                TextInput::make('option_key')
                                                    ->label('Key/Value')
                                                    ->placeholder('Auto-generated')
                                                    ->disabled()
                                                    ->dehydrated()
                                                    ->helperText('Dibuat otomatis dari label')
                                                    ->columnSpan(1),

                                                Select::make('compliance_value')
                                                    ->label('Nilai Compliance')
                                                    ->options([
                                                        0 => '❌ Tidak Sesuai (0)',
                                                        1 => '✅ Sesuai (50)',
                                                        2 => '⭐ Excellent (100)'
                                                    ])
                                                    ->default(1)
                                                    ->required()
                                                    ->helperText('Pengaruh pilihan ini terhadap compliance')
                                                    ->columnSpan(1),

                                                Textarea::make('option_description')
                                                    ->label('Deskripsi Pilihan')
                                                    ->placeholder('Penjelasan kapan memilih opsi ini...')
                                                    ->rows(2)
                                                    ->columnSpan(2),
                                            ])
                                            ->columns(3)
                                            ->defaultItems(0)
                                            ->addActionLabel('+ Tambah Opsi')
                                            ->reorderableWithButtons()
                                            ->collapsible()
                                            ->itemLabel(
                                                fn(array $state): ?string => ($state['option_label'] ?? 'Opsi') .
                                                    ' (' . ($state['compliance_value'] == 0 ? '❌' : ($state['compliance_value'] == 2 ? '⭐' : '✅')) . ')'
                                            )
                                            ->columnSpanFull()
                                            ->mutateDehydratedStateUsing(function (array $state): array {
                                                // Pastikan setiap option punya key yang valid
                                                return collect($state)->map(function ($option, $index) {
                                                    if (empty($option['option_key'])) {
                                                        $option['option_key'] = !empty($option['option_label'])
                                                            ? Str::slug($option['option_label'], '_')
                                                            : 'option_' . ($index + 1);
                                                    }
                                                    return $option;
                                                })->toArray();
                                            }),
                                    ])
                                    ->visible(fn(callable $get) => in_array($get('field_type'), [
                                        'single_select',
                                        'multi_select',
                                        'rating_scale',
                                        'conditional_trigger'
                                    ]))
                                    ->columnSpanFull()
                                    ->collapsed(false),

                                // Conditional Logic Section
                                Group::make([
                                    Select::make('parent_field_id')
                                        ->label('Tampilkan Ketika Field')
                                        ->options(fn() => $this->getAvailableFields())
                                        ->nullable()
                                        ->reactive()
                                        ->afterStateUpdated(
                                            fn($state, callable $set) =>
                                            $state ? null : $set('condition_value', null)
                                        )
                                        ->visible(fn(callable $get) => $get('field_type') !== 'conditional_trigger'),

                                    Select::make('condition_value')
                                        ->label('Memiliki Nilai')
                                        ->options(fn(callable $get) => $this->getFieldOptions($get('parent_field_id')))
                                        ->nullable()
                                        ->visible(fn(callable $get) => !empty($get('parent_field_id'))),
                                ])
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->label('Logika Kondisional (Opsional)'),

                                TextInput::make('field_key')
                                    ->label('Field Key')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated()
                                    ->hidden(),
                            ])
                            ->columns(1)
                            ->defaultItems(0)
                            ->addActionLabel('+ Tambah Field Baru')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->collapsed(false)
                            ->itemLabel(fn(array $state): ?string => ($state['label'] ?? 'Field baru') . ($state['is_required'] ?? false ? ' *' : ''))
                            ->columnSpanFull()
                            ->cloneable(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview Form Laporan Harian')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn() => ImutDataResource::getUrl('preview-form', ['record' => $this->record])),

            Action::make('save')
                ->label('Simpan Konfigurasi Field')
                ->icon('heroicon-o-check')
                ->action('save')
                ->color('success'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        try {
            DB::beginTransaction();

            // Create or update FormTemplate
            $formTemplate = FormTemplate::updateOrCreate(
                ['imut_data_id' => $this->record->id],
                [
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'compliance_method' => $data['compliance_method'] ?? 'auto_calculate',
                    'auto_fail_on_critical' => $data['auto_fail_on_critical'] ?? true,
                    'is_active' => true,
                ]
            );

            // Delete old fields that are not in the new data
            $existingFieldIds = collect($data['fields'])->pluck('id')->filter()->toArray();
            EnhancedFormField::where('form_template_id', $formTemplate->id)
                ->whereNotIn('id', $existingFieldIds)
                ->delete();

            // Process each field
            foreach ($data['fields'] as $index => $fieldData) {
                $field = EnhancedFormField::updateOrCreate(
                    [
                        'id' => $fieldData['id'] ?? null,
                        'form_template_id' => $formTemplate->id,
                    ],
                    [
                        'field_key' => $fieldData['field_key'] ?? Str::slug($fieldData['field_name'], '_'),
                        'field_name' => $fieldData['field_name'],
                        'field_description' => $fieldData['field_description'] ?? null,
                        'field_type' => $fieldData['field_type'],
                        'validation_config' => $fieldData['validation_config'] ?? [],
                        'compliance_weight' => $fieldData['compliance_weight'] ?? 1,
                        'is_critical_field' => $fieldData['is_critical_field'] ?? false,
                        'parent_field_id' => $fieldData['parent_field_id'] ?? null,
                        'condition_value' => $fieldData['condition_value'] ?? null,
                        'order_index' => $index + 1,
                        'is_active' => true,
                    ]
                );

                // Clear existing options
                FormFieldOption::where('enhanced_form_field_id', $field->id)->delete();

                // Create new options if provided
                if (!empty($fieldData['options'])) {
                    foreach ($fieldData['options'] as $optionIndex => $optionData) {
                        FormFieldOption::create([
                            'enhanced_form_field_id' => $field->id,
                            'option_text' => $optionData['option_text'],
                            'option_value' => $optionData['option_value'],
                            'compliance_value' => $optionData['compliance_value'] ?? 0,
                            'order_index' => $optionIndex + 1,
                        ]);
                    }
                }
            }

            // Also create/update legacy format for backward compatibility
            $this->saveLegacyFormat($data, $formTemplate);

            DB::commit();

            Notification::make()
                ->title('Konfigurasi laporan harian berhasil disimpan')
                ->body('Form berhasil diperbarui dengan fitur auto-compliance calculation')
                ->success()
                ->send();
        } catch (\Exception $e) {
            DB::rollBack();

            Notification::make()
                ->title('Error menyimpan konfigurasi')
                ->body('Terjadi kesalahan: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    private function saveLegacyFormat($data, FormTemplate $formTemplate): void
    {
        // Maintain backward compatibility with existing FormHeader/FormField
        $formHeader = FormHeader::updateOrCreate(
            ['imutdata_id' => $this->record->id],
            [
                'title' => $data['title'],
                'description' => $data['description'],
            ]
        );

        $existingFieldIds = collect($data['fields'])->pluck('legacy_id')->filter()->toArray();
        FormField::where('form_header_id', $formHeader->id)
            ->whereNotIn('id', $existingFieldIds)
            ->delete();

        foreach ($data['fields'] as $index => $fieldData) {
            // Convert enhanced field type to legacy type
            $legacyType = $this->mapToLegacyFieldType($fieldData['field_type']);

            // Convert options to legacy format
            $legacyOptions = null;
            if (!empty($fieldData['options'])) {
                $legacyOptions = collect($fieldData['options'])->pluck('option_text')->toArray();
            }

            FormField::updateOrCreate(
                [
                    'id' => $fieldData['legacy_id'] ?? null,
                    'form_header_id' => $formHeader->id,
                ],
                [
                    'key' => $fieldData['field_key'] ?? Str::slug($fieldData['field_name'], '_'),
                    'label' => $fieldData['field_name'],
                    'description' => $fieldData['field_description'] ?? null,
                    'type' => $legacyType,
                    'is_required' => $fieldData['validation_config']['required'] ?? false,
                    'options' => $legacyOptions,
                    'order' => $index + 1,
                ]
            );
        }
    }

    private function mapToLegacyFieldType(string $enhancedType): string
    {
        return match ($enhancedType) {
            'single_select' => 'select',
            'multi_select' => 'checkbox',
            'rating_scale' => 'select',
            'boolean' => 'radio',
            'time_duration' => 'text',
            'time_range' => 'text',
            'conditional_trigger' => 'select',
            'compliance_checker' => 'select',
            default => 'text',
        };
    }



    private function getNextSortOrder(): int
    {
        $data = $this->form->getRawState();
        $fields = $data['fields'] ?? [];
        return count($fields) + 1;
    }

    private function getAvailableFields(): array
    {
        $data = $this->form->getRawState();
        $fields = $data['fields'] ?? [];

        $options = [];
        foreach ($fields as $field) {
            if (!empty($field['field_name'])) {
                $options[$field['field_key'] ?? Str::slug($field['field_name'])] = $field['field_name'];
            }
        }

        return $options;
    }

    private function getFieldOptions(?string $fieldKey): array
    {
        if (!$fieldKey) return [];

        $data = $this->form->getRawState();
        $fields = $data['fields'] ?? [];

        foreach ($fields as $field) {
            if (($field['field_key'] ?? Str::slug($field['field_name'])) === $fieldKey) {
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

    public function getBreadcrumbs(): array
    {
        return [
            ImutDataResource::getUrl('index') => 'Data IMUT',
            ImutDataResource::getUrl('edit', ['record' => $this->record]) => $this->record->title,
            '#' => 'Konfigurasi Form Laporan Harian',
        ];
    }
}
