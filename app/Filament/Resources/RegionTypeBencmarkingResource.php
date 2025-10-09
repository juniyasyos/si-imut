<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegionTypeBencmarkingResource\Pages;
use App\Models\RegionType;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

class RegionTypeBencmarkingResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = RegionType::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('type')
                ->required()
                ->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Created At'),
            ])
            ->filters([
                // Removed TrashedFilter since we no longer use SoftDeletes
            ])
            ->actions([
                EditAction::make()
                    ->modalHeading('Edit Data')
                    ->modalWidth('xl')
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
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRegionTypeBencmarkings::route('/bencmarkings'),
        ];
    }
}
