<?php

namespace App\Filament\Resources\ImutDataResource\Pages;

use App\Filament\Resources\ImutDataResource;
use App\Models\FormField;
use App\Models\FormHeader;
use App\Models\ImutData;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Str;

class ManageFormBuilder extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = ImutDataResource::class;

    protected static string $view = 'filament.resources.imut-data-resource.pages.manage-form-builder';

    public ?array $data = [];

    public ?ImutData $record = null;

    public function mount(): void
    {
        $imutDataId = request()->route('record');
        $this->record = ImutData::query()->where('slug', $imutDataId)->firstOrFail();

        $formHeader = FormHeader::where('imutdata_id', $this->record->id)->first();

        if ($formHeader) {
            $fields = $formHeader->formFields->map(function ($field) {
                return [
                    'id' => $field->id,
                    'key' => $field->key,
                    'label' => $field->label,
                    'type' => $field->type,
                    'is_required' => $field->is_required,
                    'options' => $field->options,
                    'order' => $field->order,
                ];
            })->toArray();

            $this->form->fill([
                'title' => $formHeader->title,
                'description' => $formHeader->description,
                'fields' => $fields,
            ]);
        } else {
            $this->form->fill([
                'title' => '',
                'description' => '',
                'fields' => [],
            ]);
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Pengaturan Form')
                    ->description('Atur judul dan deskripsi form seperti Google Form')
                    ->schema([
                        TextInput::make('title')
                            ->label('Judul Form')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Indikator High Alert')
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Deskripsi Form')
                            ->rows(3)
                            ->placeholder('Berikan deskripsi singkat tentang form ini...')
                            ->columnSpanFull(),
                    ]),

                Section::make('Pertanyaan Form')
                    ->description('Tambahkan pertanyaan/field untuk form Anda')
                    ->schema([
                        Repeater::make('fields')
                            ->label('Daftar Pertanyaan')
                            ->schema([
                                TextInput::make('label')
                                    ->label('Label Pertanyaan')
                                    ->required()
                                    ->placeholder('Contoh: Tanggal Kejadian')
                                    ->columnSpanFull(),

                                TextInput::make('key')
                                    ->label('Key (Unique Identifier)')
                                    ->required()
                                    ->placeholder('Contoh: tanggal_kejadian')
                                    ->unique(ignoreRecord: true)
                                    ->helperText('Gunakan format snake_case, contoh: tanggal_kejadian, no_rm')
                                    ->columnSpanFull()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $set('key', Str::slug($state, '_'));
                                    }),

                                Select::make('type')
                                    ->label('Tipe Input')
                                    ->required()
                                    ->options([
                                        'text' => 'Text - Teks pendek',
                                        'textarea' => 'Textarea - Teks panjang',
                                        'number' => 'Number - Angka',
                                        'date' => 'Date - Tanggal',
                                        'bool' => 'Boolean - Ya/Tidak',
                                        'select' => 'Select - Pilihan dropdown',
                                        'radio' => 'Radio - Pilihan radio button',
                                        'checkbox' => 'Checkbox - Pilihan multiple',
                                    ])
                                    ->reactive()
                                    ->columnSpan(1),

                                Checkbox::make('is_required')
                                    ->label('Wajib Diisi?')
                                    ->default(false)
                                    ->columnSpan(1),

                                Repeater::make('options')
                                    ->label('Opsi Pilihan')
                                    ->schema([
                                        TextInput::make('value')
                                            ->label('Value')
                                            ->required(),
                                        TextInput::make('label')
                                            ->label('Label')
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->visible(fn(callable $get) => in_array($get('type'), ['select', 'radio', 'checkbox']))
                                    ->columnSpanFull()
                                    ->helperText('Tambahkan opsi untuk pilihan dropdown/radio/checkbox'),
                            ])
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel('+ Tambah Pertanyaan')
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn(array $state): ?string => $state['label'] ?? 'Pertanyaan baru')
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('preview')
                ->label('Preview Form')
                ->icon('heroicon-o-eye')
                ->color('gray')
                ->url(fn() => ImutDataResource::getUrl('preview-form', ['record' => $this->record->slug])),

            Action::make('save')
                ->label('Simpan Form')
                ->icon('heroicon-o-check')
                ->action('save')
                ->color('success'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        $formHeader = FormHeader::updateOrCreate(
            ['imutdata_id' => $this->record->id],
            [
                'title' => $data['title'],
                'description' => $data['description'],
            ]
        );

        $existingFieldIds = collect($data['fields'])->pluck('id')->filter()->toArray();
        FormField::where('form_header_id', $formHeader->id)
            ->whereNotIn('id', $existingFieldIds)
            ->delete();

        foreach ($data['fields'] as $index => $fieldData) {
            FormField::updateOrCreate(
                [
                    'id' => $fieldData['id'] ?? null,
                    'form_header_id' => $formHeader->id,
                ],
                [
                    'key' => $fieldData['key'],
                    'label' => $fieldData['label'],
                    'type' => $fieldData['type'],
                    'is_required' => $fieldData['is_required'] ?? false,
                    'options' => $fieldData['options'] ?? null,
                    'order' => $index + 1,
                ]
            );
        }

        Notification::make()
            ->title('Form berhasil disimpan')
            ->success()
            ->send();
    }

    public function getBreadcrumbs(): array
    {
        return [
            ImutDataResource::getUrl('index') => 'Data IMUT',
            ImutDataResource::getUrl('edit', ['record' => $this->record->slug]) => $this->record->title,
            '#' => 'Form Builder',
        ];
    }
}
