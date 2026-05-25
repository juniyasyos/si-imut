<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\RegionTypeBencmarkingResource\Pages\ListRegionTypeBencmarkings;
use App\Filament\Resources\RegionTypeBencmarkingResource\Pages;
use App\Models\RegionType;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class RegionTypeBencmarkingResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = RegionType::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Region Type Benchmarking';

    protected static ?int $navigationSort = 1;

    public static function getGloballySearchableAttributes(): array
    {
        return ['type'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Type' => $record->type,
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->type ?? '';
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl(name: 'edit', parameters: ['record' => $record]);
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
        ];
    }

    public static function getLabel(): ?string
    {
        return __('Region Type');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Region Types');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-forms::imut-data.navigation.group');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('type')
                ->label('Nama Region Type')
                ->required()
                ->maxLength(255)
                ->placeholder('contoh: 🌍 Nasional'),

            ColorPicker::make('display_color')
                ->label('Warna Chart')
                ->placeholder('#3b82f6')
                ->nullable(),

            Select::make('chart_type')
                ->label('Tipe Chart')
                ->options(RegionType::getChartTypes())
                ->default('column')
                ->required()
                ->native(false),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->label('Region Type')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                ColorColumn::make('display_color')
                    ->label('Warna')
                    ->sortable()
                    ->default('#3b82f6')
                    ->toggleable(),

                TextColumn::make('display_color')
                    ->label('Kode Warna')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn($state) => $state ?? '#3b82f6 (default)')
                    ->toggleable(),

                TextColumn::make('chart_type')
                    ->label('Tipe Chart')
                    ->badge()
                    ->formatStateUsing(fn($state) => match($state) {
                        'line' => '📈 Line',
                        'column' => '📊 Column',
                        default => '📊 Column',
                    })
                    ->color(fn($state) => match($state) {
                        'line' => 'success',
                        'column' => 'primary',
                        default => 'primary',
                    })
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->label('Dibuat')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Removed TrashedFilter since we no longer use SoftDeletes
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Edit Region Type')
                    ->modalWidth('2xl')
                    ->modal(),

                DeleteAction::make()
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        try {
                            $record->delete();

                            Notification::make()
                                ->title('Data berhasil dihapus')
                                ->body('Data telah dihapus dengan sukses.')
                                ->success()
                                ->send();
                        } catch (QueryException $e) {
                            Notification::make()
                                ->title('Gagal Menghapus Data')
                                ->body('Data ini masih terhubung ke data lain, sehingga tidak bisa dihapus. Silakan periksa relasi yang terkait.')
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),
            ])
            ->toolbarActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRegionTypeBencmarkings::route('/bencmarkings'),
        ];
    }
}
