<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImutDataResource\Pages;
use App\Filament\Resources\ImutDataResource\Pages\UnitKerjaOverview;
use App\Filament\Resources\ImutDataResource\Pages\SummaryDiagram;
use App\Filament\Resources\ImutDataResource\RelationManagers\ProfilesRelationManager;
use App\Filament\Resources\ImutDataResource\RelationManagers\RegionTypeRelationManager;
use App\Filament\Resources\ImutDataResource\RelationManagers\UnitKerjaRelationManager;
use App\Filament\Resources\ImutDataResource\Schema\ImutDataSchema;
use App\Filament\Resources\ImutDataResource\Table\TableSchema;
use App\Models\ImutData;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ImutDataResource extends Resource implements HasShieldPermissions
{
    use \App\Traits\HasActiveIcon;

    protected static ?string $model = ImutData::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 3;

    public static function getGloballySearchableAttributes(): array
    {
        return ['title'];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return $record->title;
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl(name: 'edit', parameters: ['record' => $record]);
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('filament-forms::imut-data.fields.imut_kategori_id') => $record->kategori->category_name ?? '-',
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'view_all_data',
            'view_by_unit_kerja',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
        ];
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getLabel(): ?string
    {
        return __('filament-forms::imut-data.navigation.title');
    }

    public static function getPluralLabel(): ?string
    {
        return __('filament-forms::imut-data.navigation.plural');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-forms::imut-data.navigation.group');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(ImutDataSchema::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn() => TableSchema::query())
            ->columns(TableSchema::columns())
            ->headerActions(TableSchema::headerActions())
            ->filters(TableSchema::filters())
            ->actions(TableSchema::actions())
            ->bulkActions(TableSchema::bulkActions());
    }

    public static function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = static::getModel()::query();
        $user = \Illuminate\Support\Facades\Auth::user();

        if ($user->can('view_all_data_imut::data')) {
            return $query;
        }

        if ($user->can('view_by_unit_kerja_imut::data')) {
            return $query->whereHas('unitKerja', function ($q) use ($user) {
                $q->where('unit_kerja_id', $user->unit_kerja_id);
            });
        }

        return $query->whereRaw('1 = 0');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListImutData::route('/'),
            'create' => Pages\CreateImutData::route('/create'),
            'edit' => Pages\EditImutData::route('/edit={record:slug}'),
            'create-profile' => \App\Filament\Resources\ImutProfileResource\Pages\CreateImutProfile::route('/{imutDataSlug}/profile/create'),
            'edit-profile' => \App\Filament\Resources\ImutProfileResource\Pages\EditImutProfile::route('/{imutDataSlug}/profile/edit={record}'),
            'bencmarking-region-type' => \App\Filament\Resources\RegionTypeBencmarkingResource\Pages\ListRegionTypeBencmarkings::route('/bencmarkings/region-type'),
            'overview-unit-kerja' => UnitKerjaOverview::route('/overview/unit-kerja'),
            'overview-imut-data' => SummaryDiagram::route('overview/summary-imut-data'),
            'manage-form-builder' => \App\Filament\Resources\ImutProfileResource\Pages\ManageFormBuilder::route('/{imutDataSlug}/{record:slug}/form-builder'),
            'preview-form' => \App\Filament\Resources\ImutProfileResource\Pages\FormBuilder::route('/{imutDataSlug}/{record:slug}/form-builder/preview'),
            'list-daily-reports' => \App\Filament\Resources\ImutProfileResource\Pages\ListDailyReports::route('/{imutDataSlug}/{record:slug}/daily-reports'),
        ];
    }
}
