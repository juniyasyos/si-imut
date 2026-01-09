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
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Guava\FilamentModalRelationManagers\Actions\ModalRelationManagerAction;
use App\Filament\Resources\ImutDataResource\RelationManagers\ProfilesRelationManager;
use Filament\Notifications\Notification;
use Guava\FilamentModalRelationManagers\Actions\Action\RelationManagerAction;
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

                                        return $query->pluck('category_name');
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
                            Section::make('Manajemen Benchmark')
                                ->description('Kelola data benchmark untuk indikator IMUT ini. Setiap region type dapat memiliki benchmark sendiri-sendiri.')
                                ->schema([
                                    Actions::make([
                                        Action::make('manage_all_benchmarks')
                                            ->label('Kelola Semua Benchmark')
                                            ->icon('heroicon-m-cog-8-tooth')
                                            ->color('primary')
                                            ->url(
                                                fn(?Model $record) => $record
                                                    ? '/admin/imut-benchmarkings?tableFilters[imut_data_id][value]=' . $record->id
                                                    : '/admin/imut-benchmarkings'
                                            ),

                                        Action::make('add_benchmark')
                                            ->label('Tambah Benchmark Baru')
                                            ->icon('heroicon-m-plus')
                                            ->color('success')
                                            ->url(
                                                fn(?Model $record) => $record
                                                    ? '/admin/imut-benchmarkings/create?imut_data_id=' . $record->id
                                                    : '/admin/imut-benchmarkings/create'
                                            ),
                                    ])
                                        ->alignCenter()
                                        ->visible(fn(?Model $record) => $record !== null),
                                ]),

                            Tabs::make('Benchmark by Region Type')
                                ->tabs(
                                    RegionType::all()->map(function ($regionType) {
                                        return Tab::make($regionType->type)
                                            ->icon('heroicon-m-map-pin')
                                            ->badge(fn(?Model $record) => $record?->benchmarkings()
                                                ->where('region_type_id', $regionType->id)
                                                ->where('is_active', true)
                                                ->count() ?: null)
                                            ->badgeColor(fn(?Model $record) => ($record?->benchmarkings()
                                                ->where('region_type_id', $regionType->id)
                                                ->where('is_active', true)
                                                ->count() > 0) ? 'success' : 'gray')
                                            ->schema([
                                                Section::make("Benchmark untuk {$regionType->type}")
                                                    ->description("Data benchmark yang berlaku untuk region type: {$regionType->type}")
                                                    ->schema([
                                                        TableRepeater::make("{$regionType->type}_benchmarkings")
                                                            ->label('')
                                                            ->relationship(
                                                                'benchmarkings',
                                                                fn($query) => $query
                                                                    ->where('region_type_id', $regionType->id)
                                                                    ->orderBy('is_active', 'desc')
                                                                    ->orderByDesc('period_start')
                                                            )
                                                            ->headers([
                                                                Header::make('region_name')->label('Region')->width('150px'),
                                                                Header::make('benchmark_value')->label('Nilai (%)')->width('100px'),
                                                                Header::make('period_start')->label('Berlaku Dari')->width('130px'),
                                                                Header::make('period_end')->label('Sampai')->width('130px'),
                                                                Header::make('is_active')->label('Status')->width('80px'),
                                                                Header::make('notes')->label('Catatan')->width('200px'),
                                                            ])
                                                            ->schema([
                                                                TextInput::make('region_name')
                                                                    ->label(false)
                                                                    ->placeholder($regionType->getDefaultRegionName() ?? 'Nama region'),

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
                                                                    ->placeholder('Permanent')
                                                                    ->afterOrEqual('period_start'),

                                                                Toggle::make('is_active')
                                                                    ->label(false)
                                                                    ->default(true)
                                                                    ->inline(false),

                                                                Textarea::make('notes')
                                                                    ->label(false)
                                                                    ->placeholder('Catatan...')
                                                                    ->rows(2),

                                                                // Hidden fields
                                                                Hidden::make('region_type_id')->default($regionType->id),
                                                                Hidden::make('created_by')->default(fn() => Auth::id()),
                                                                Hidden::make('updated_by')->default(fn() => Auth::id()),
                                                            ])
                                                            ->visibleOn('edit')
                                                            ->addable(fn() => Auth::user()->can('force_editable_imut::profile'))
                                                            ->deletable(fn() => Auth::user()->can('force_editable_imut::profile'))
                                                            ->reorderable(false)
                                                            ->defaultItems(0)
                                                            ->columnSpan('full')
                                                            ->emptyLabel("Belum ada benchmark untuk region type: {$regionType->type}"),
                                                    ])
                                                    ->collapsible()
                                                    ->collapsed(fn(?Model $record) => $record?->benchmarkings()
                                                        ->where('region_type_id', $regionType->id)
                                                        ->count() === 0),
                                            ]);
                                    })->push(
                                        Tab::make('⚙️ Kelola Region Type')
                                            ->icon('heroicon-m-cog-6-tooth')
                                            ->visible(Auth::user()->can('create_region::type::bencmarking'))
                                            ->schema([
                                                Section::make('Pengaturan Region Type')
                                                    ->description('Kelola region type yang tersedia untuk benchmarking')
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
                                                                        ->placeholder('Contoh: 🌍 Nasional, 📍 Provinsi')
                                                                        ->required(),
                                                                    ColorPicker::make('display_color')
                                                                        ->label('Warna Chart Default')
                                                                        ->placeholder('#3b82f6'),
                                                                    Select::make('chart_type')
                                                                        ->label('Tipe Chart Default')
                                                                        ->options([
                                                                            'line' => '📈 Line',
                                                                            'column' => '📊 Column',
                                                                        ])
                                                                        ->default('column'),
                                                                ])
                                                                ->action(function (array $data) {
                                                                    RegionType::create($data);

                                                                    Notification::make()
                                                                        ->title('Berhasil')
                                                                        ->body('Region Type berhasil ditambahkan.')
                                                                        ->success()
                                                                        ->send();

                                                                    redirect(request()->header('Referer'));
                                                                }),

                                                            Action::make('goto_region_type_list')
                                                                ->label('Kelola Semua Region Type')
                                                                ->icon('heroicon-m-list-bullet')
                                                                ->color('gray')
                                                                ->url(fn() => '/admin/region-type-bencmarkings/bencmarkings'),
                                                        ])
                                                            ->alignCenter(),
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
