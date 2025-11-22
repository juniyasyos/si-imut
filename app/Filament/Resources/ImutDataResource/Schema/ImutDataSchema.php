<?php

namespace App\Filament\Resources\ImutDataResource\Schema;

use App\Filament\Resources\ImutDataResource;
use App\Models\RegionType;
use App\Models\UnitKerja;
use App\Models\User;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ImutDataSchema extends ImutDataResource
{
    public static function make(): array
    {
        return [
            Section::make('Informasi Unit Kerja')
                ->visible(function () {
                    $user = Auth::user();

                    if ($user->unitKerjas->isEmpty()) {
                        return false;
                    }

                    return $user->can('view_unit::kerja') ||
                        ! $user->can('attach_imut_data_to_unit_kerja_unit::kerja');
                })
                ->disabled()
                ->schema([
                    Placeholder::make('unitKerjaInfo')
                        ->label('Unit Kerja Pengguna')
                        ->content(
                            fn() =>
                            Auth::user()->unitKerjas->isNotEmpty()
                                ? Auth::user()->unitKerjas->map(function ($unit) {
                                    $nama = $unit->unit_name;
                                    $deskripsi = $unit->description ?? '-';
                                    return "• {$nama} — {$deskripsi}";
                                })->implode("\n")
                                : 'Tidak ada unit kerja yang terkait dengan akun ini.'
                        )
                        ->columnSpanFull(),
                ]),
            Tabs::make('')
                ->columnSpan(['lg' => 2])
                ->tabs([
                    Tab::make('📋 Form Profil Indikator')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('title')
                                    ->label(__('filament-forms::imut-data.fields.title'))
                                    ->placeholder(__('filament-forms::imut-data.form.main.title_placeholder'))
                                    ->helperText(__('filament-forms::imut-data.form.main.helper_text'))
                                    ->prefixIcon('heroicon-o-pencil-square')
                                    ->required()
                                    ->readOnly(fn(?Model $record) => ($record && $record->created_by !== Auth::id()) && ! Auth::user()->can('force_editable_imut::profile'))
                                    ->columnSpan(2)
                                    ->unique('imut_data', 'title', ignoreRecord: true)
                                    ->maxLength(255),

                                TextInput::make('slug')
                                    ->label(__('filament-forms::imut-data.fields.slug'))
                                    ->readOnly()
                                    ->disabled()
                                    ->extraAttributes(['class' => 'bg-gray-100 text-gray-500'])
                                    ->visibleOn('edit')
                                    ->columnSpan(1),

                                Select::make('imut_kategori_id')
                                    ->label(__('Kategori'))
                                    ->options(function () {
                                        $user = Auth::user();

                                        $query = \App\Models\ImutCategory::query();

                                        if (! ($user->can('create_imut::category') && $user->can('update_imut::category'))) {
                                            $query->where('is_use_global', true);
                                        }

                                        return $query->pluck('category_name', 'id');
                                    })
                                    ->searchable()
                                    ->required()
                                    ->disabled(fn(?Model $record) => ($record && $record->created_by !== Auth::id()) && ! Auth::user()->can('force_editable_imut::profile'))
                                    ->hint(__('filament-forms::imut-data.form.main.category_hint')),

                                Toggle::make('status')
                                    ->label(__('filament-forms::imut-data.fields.status'))
                                    ->helperText(__('filament-forms::imut-data.form.main.status_helper'))
                                    ->inline(true)
                                    ->columnSpan(2)
                                    ->onColor('success')
                                    ->disabled(fn(?Model $record) => ($record && $record->created_by !== Auth::id()) && ! Auth::user()->can('force_editable_imut::profile'))
                                    ->required()
                                    ->default(true)
                                    ->columnSpan(1),

                                RichEditor::make('description')
                                    ->label(__('filament-forms::imut-data.fields.description'))
                                    ->placeholder(__('filament-forms::imut-data.form.main.description_placeholder'))
                                    ->disabled(fn(?Model $record) => ($record && $record->created_by !== Auth::id()) && ! Auth::user()->can('force_editable_imut::profile'))
                                    ->helperText(__('filament-forms::imut-data.form.main.description_helper'))
                                    ->dehydrated(true)
                                    ->columnSpan(2)
                                    ->maxLength(255),

                                Select::make('created_by')
                                    ->label('Dibuat oleh')
                                    ->options(fn() => User::pluck('name', 'id'))
                                    ->default(function (?Model $record) {
                                        return $record?->created_by ?? Auth::id();
                                    })
                                    ->visibleOn('edit')
                                    ->disabled()
                                    ->dehydrated(false),

                                Hidden::make('created_by')
                                    ->default(fn() => Auth::id()),
                            ]),

                            Section::make('Unit Kerja')
                                ->description('Pilih unit kerja yang memiliki indikator mutu ini.')
                                ->columnSpanFull()
                                ->collapsed()
                                ->visible(fn() => Auth::user()->can('attach_imut_data_to_unit_kerja_unit::kerja'))
                                ->schema([
                                    CheckboxList::make('unitKerja')
                                        ->label('Unit Kerja yang Bisa Menilai')
                                        ->relationship('unitKerja', 'unit_name')
                                        ->options(UnitKerja::pluck('unit_name', 'id')->toArray())
                                        ->columns(3)
                                        ->required()
                                        ->bulkToggleable()
                                        ->default(fn() => Auth::user()->unitKerjas()->pluck('unit_kerja.id')->toArray())
                                        ->visible(fn() => Auth::user()->can('attach_imut_data_to_unit_kerja_unit::kerja'))
                                        ->dehydrated(true)
                                        ->name('unitKerjaIds'),
                                ])
                        ]),

                    Tab::make('📍 Benchmarking')
                        ->schema([
                            Tabs::make('Benchmark Tabs')
                                ->tabs(
                                    RegionType::all()->map(function ($regionType) {
                                        return Tab::make(ucfirst($regionType->type))
                                            ->badge(fn(?Model $record) => $record?->benchmarkings()
                                                ->where('region_type_id', $regionType->id)
                                                ->count() ?: null)
                                            ->schema([
                                                TableRepeater::make("{$regionType->type}_benchmarkings")
                                                    ->label('')
                                                    ->relationship('benchmarkings', fn($query) => $query
                                                        ->where('region_type_id', $regionType->id)
                                                        ->orderBy('is_active', 'desc')
                                                        ->orderByDesc('period_start')
                                                    )
                                                    ->headers([
                                                        Header::make('benchmark_value')->label('Nilai (%)')->width('100px'),
                                                        Header::make('period_start')->label('Berlaku Dari')->width('140px'),
                                                        Header::make('period_end')->label('Sampai')->width('140px'),
                                                        Header::make('is_active')->label('Aktif')->width('80px'),
                                                    ])
                                                    ->schema([
                                                        TextInput::make('benchmark_value')
                                                            ->label(false)
                                                            ->numeric()
                                                            ->step(0.01)
                                                            ->suffix('%')
                                                            ->placeholder('85.5')
                                                            ->required(),

                                                        DatePicker::make('period_start')
                                                            ->label(false)
                                                            ->placeholder('Tanggal mulai')
                                                            ->default(now()->startOfMonth())
                                                            ->required(),

                                                        DatePicker::make('period_end')
                                                            ->label(false)
                                                            ->placeholder('Permanent (kosongkan)')
                                                            ->afterOrEqual('period_start'),

                                                        Toggle::make('is_active')
                                                            ->label(false)
                                                            ->default(true)
                                                            ->inline(false),

                                                        // Hidden fields
                                                        Hidden::make('region_type_id')->default($regionType->id),
                                                        Hidden::make('notes'),
                                                        Hidden::make('created_by')->default(fn() => Auth::id()),
                                                        Hidden::make('updated_by')->default(fn() => Auth::id()),
                                                    ])
                                                    ->visibleOn('edit')
                                                    ->addable(fn() => Auth::user()->can('force_editable_imut::profile'))
                                                    ->deletable(fn() => Auth::user()->can('force_editable_imut::profile'))
                                                    ->reorderable(false)
                                                    ->defaultItems(0)
                                                    ->columnSpan('full')
                                                    ->emptyLabel('Belum ada data benchmarking'),
                                            ]);
                                    })->push(
                                        Tab::make('⚙️ Kelola Region')
                                            ->visible(Auth::user()->can('create_region::type::bencmarking'))
                                            ->schema([
                                                Actions::make([
                                                    Action::make('create_region_type')
                                                        ->label('Tambah Region Type')
                                                        ->icon('heroicon-m-plus')
                                                        ->color('primary')
                                                        ->modalHeading('Tambah Region Type Baru')
                                                        ->form([
                                                            TextInput::make('type')
                                                                ->label('Nama Region Type')
                                                                ->placeholder('Contoh: provinsi, rumah sakit, nasional')
                                                                ->required(),
                                                        ])
                                                        ->action(function (array $data) {
                                                            RegionType::create(['type' => $data['type']]);

                                                            Notification::make()
                                                                ->title('Berhasil')
                                                                ->body('Region Type berhasil ditambahkan.')
                                                                ->success()
                                                                ->send();

                                                            redirect(request()->header('Referer'));
                                                        }),

                                                    Action::make('goto_region_type_list')
                                                        ->label('Lihat Semua Region Type')
                                                        ->icon('heroicon-m-list-bullet')
                                                        ->color('gray')
                                                        ->url(fn() => static::getUrl('bencmarking-region-type')),
                                                ]),
                                            ])
                                    )->toArray()
                                ),
                        ])
                        ->visible(fn(?Model $record) => $record !== null
                            && $record->categories->is_benchmark_category === 1),

                ]),
        ];
    }
}
