<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImutBenchmarkingResource\Pages;
use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\RegionType;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ImutBenchmarkingResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = ImutBenchmarking::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Benchmark Management';

    protected static ?string $navigationGroup = 'IMUT Data Management';

    protected static ?int $navigationSort = 3;

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['imutData.title', 'regionType.type'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Indikator' => $record->imutData->title,
            'Region' => $record->regionType->type,
            'Value' => $record->benchmark_value . '%',
        ];
    }

    public static function getLabel(): ?string
    {
        return __('Benchmark Data');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Benchmark Data');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Benchmark')
                ->schema([
                    Forms\Components\Select::make('imut_data_id')
                        ->label('Indikator IMUT')
                        ->relationship('imutData', 'title')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('title')
                                ->required()
                                ->maxLength(255),
                        ]),

                    Forms\Components\Select::make('region_type_id')
                        ->label('Tipe Region')
                        ->relationship('regionType', 'type')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('type')
                                ->required()
                                ->maxLength(255),
                        ]),


                    Forms\Components\TextInput::make('benchmark_value')
                        ->label('Nilai Benchmark (%)')
                        ->numeric()
                        ->step(0.01)
                        ->suffix('%')
                        ->required()
                        ->minValue(0)
                        ->maxValue(100),
                ]),

            Section::make('Periode Berlaku')
                ->schema([
                    Forms\Components\DatePicker::make('period_start')
                        ->label('Berlaku Mulai')
                        ->default(now()->startOfMonth())
                        ->required(),

                    Forms\Components\DatePicker::make('period_end')
                        ->label('Berlaku Sampai')
                        ->afterOrEqual('period_start')
                        ->helperText('Kosongkan jika berlaku permanent/selamanya'),

                    Forms\Components\Toggle::make('is_active')
                        ->label('Status Aktif')
                        ->default(true)
                        ->helperText('Hanya benchmark aktif yang akan ditampilkan di chart'),
                ]),

            Section::make('Catatan')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan/Keterangan')
                        ->maxLength(1000)
                        ->rows(3)
                        ->placeholder('Tambahkan catatan atau keterangan tentang benchmark ini'),
                ])
                ->collapsible()
                ->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('imutData.title')
                    ->label('Indikator IMUT')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->wrap(),

                Tables\Columns\TextColumn::make('regionType.type')
                    ->label('Tipe Region')
                    ->badge()
                    ->color('gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('benchmark_value')
                    ->label('Benchmark')
                    ->formatStateUsing(fn($state) => number_format($state, 2) . '%')
                    ->sortable()
                    ->alignment('center')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('period_start')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('period_end')
                    ->label('Berakhir')
                    ->date('d M Y')
                    ->placeholder('Permanent')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('imut_data_id')
                    ->label('Indikator IMUT')
                    ->relationship('imutData', 'title')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('region_type_id')
                    ->label('Tipe Region')
                    ->relationship('regionType', 'type')
                    ->multiple(),

                Tables\Filters\Filter::make('is_active')
                    ->label('Hanya Aktif')
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true))
                    ->default(),

                Tables\Filters\Filter::make('current_period')
                    ->label('Periode Saat Ini')
                    ->query(fn(Builder $query): Builder => $query->activeForPeriod(now()))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('period_start', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImutBenchmarkings::route('/'),
            'create' => Pages\CreateImutBenchmarking::route('/create'),
            'view' => Pages\ViewImutBenchmarking::route('/{record}'),
            'edit' => Pages\EditImutBenchmarking::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('is_active', true)->count();
    }
}
