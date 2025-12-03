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

    public function mount(ImutData $record): void
    {
        $this->record = $record;

        $formHeader = FormHeader::where('imutdata_id', $this->record->id)->first();

        if ($formHeader) {
            $fields = $formHeader->formFields->map(function ($field) {
                return [
                    'id' => $field->id,
                    'key' => $field->key,
                    'label' => $field->label,
                    'description' => $field->description,
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
                Section::make('Informasi Laporan Harian')
                    ->description('Pengaturan form untuk pengisian laporan harian indikator mutu')
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
                    ]),

                Section::make('Field Laporan Harian')
                    ->description('Tentukan field yang perlu diisi dalam laporan harian (numerator, denominator, atau data pendukung lainnya)')
                    ->schema([
                        Repeater::make('fields')
                            ->label('Daftar Field Laporan')
                            ->schema([
                                Section::make()
                                    ->schema([
                                        TextInput::make('label')
                                            ->label('📌 Nama Field / Pertanyaan')
                                            ->required()
                                            ->placeholder('Contoh: Jumlah Pasien yang Dilakukan Cuci Tangan')
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                                if (empty($get('key')) || $get('key') === Str::slug($get('../../fields.' . $get('../../currentIndex') . '.label') ?? '', '_')) {
                                                    $set('key', Str::slug($state, '_'));
                                                }
                                            })
                                            ->columnSpanFull(),

                                        Textarea::make('description')
                                            ->label('📝 Deskripsi / Petunjuk Pengisian')
                                            ->placeholder('Contoh: Hitung total pasien yang mendapat edukasi cuci tangan dari petugas kesehatan')
                                            ->helperText('Deskripsi akan ditampilkan sebagai helper text di form laporan harian')
                                            ->rows(2)
                                            ->columnSpanFull(),

                                        Select::make('type')
                                            ->label('Tipe Input')
                                            ->required()
                                            ->options([
                                                'text' => '📝 Text - Teks pendek',
                                                'textarea' => '📄 Textarea - Teks panjang',
                                                'number' => '🔢 Number - Angka',
                                                'date' => '📅 Date - Tanggal',
                                                'bool' => '✅ Boolean - Ya/Tidak',
                                                'select' => '📋 Select - Pilihan dropdown',
                                                'radio' => '🔘 Radio - Pilihan radio button',
                                                'checkbox' => '☑️ Checkbox - Pilihan multiple',
                                            ])
                                            ->reactive()
                                            ->columnSpan(1),

                                        Checkbox::make('is_required')
                                            ->label('Wajib Diisi')
                                            ->default(false)
                                            ->inline(false)
                                            ->columnSpan(1),
                                    ])
                                    ->columns(2)
                                    ->columnSpanFull(),

                                Section::make('Opsi Pilihan')
                                    ->description('Tambahkan pilihan untuk select, radio, atau checkbox')
                                    ->schema([
                                        Repeater::make('options')
                                            ->label('')
                                            ->schema([
                                                TextInput::make('value')
                                                    ->label('Value')
                                                    ->required()
                                                    ->placeholder('nilai-1'),
                                                TextInput::make('label')
                                                    ->label('Label Tampilan')
                                                    ->required()
                                                    ->placeholder('Pilihan 1'),
                                            ])
                                            ->columns(2)
                                            ->defaultItems(2)
                                            ->addActionLabel('+ Tambah Opsi')
                                            ->reorderableWithButtons()
                                            ->collapsible()
                                            ->itemLabel(fn(array $state): ?string => $state['label'] ?? null)
                                            ->columnSpanFull(),
                                    ])
                                    ->visible(fn(callable $get) => in_array($get('type'), ['select', 'radio', 'checkbox']))
                                    ->columnSpanFull()
                                    ->collapsed(false),

                                TextInput::make('key')
                                    ->label('Key')
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
            $key = $fieldData['key'] ?? Str::slug($fieldData['label'], '_');

            FormField::updateOrCreate(
                [
                    'id' => $fieldData['id'] ?? null,
                    'form_header_id' => $formHeader->id,
                ],
                [
                    'key' => $key,
                    'label' => $fieldData['label'],
                    'description' => $fieldData['description'] ?? null,
                    'type' => $fieldData['type'],
                    'is_required' => $fieldData['is_required'] ?? false,
                    'options' => $fieldData['options'] ?? null,
                    'order' => $index + 1,
                ]
            );
        }

        Notification::make()
            ->title('Konfigurasi laporan harian berhasil disimpan')
            ->success()
            ->send();
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
