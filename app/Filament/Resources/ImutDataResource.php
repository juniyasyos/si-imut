<?php

namespace App\Filament\Resources;

use App\Traits\HasActiveIcon;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\ImutDataResource\Pages\ListImutData;
use App\Filament\Resources\ImutDataResource\Pages\CreateImutData;
use App\Filament\Resources\ImutDataResource\Pages\EditImutData;
use App\Filament\Resources\ImutProfileResource\Pages\CreateImutProfile;
use App\Filament\Resources\ImutProfileResource\Pages\EditImutProfile;
use App\Filament\Resources\RegionTypeBencmarkingResource\Pages\ListRegionTypeBencmarkings;
use App\Filament\Resources\ImutProfileResource\Pages\ManageFormBuilder;
use App\Filament\Resources\ImutProfileResource\Pages\FormBuilder;
use App\Filament\Resources\ImutProfileResource\Pages\ListDailyReports;
use App\Filament\Resources\ImutDataResource\Pages;
use App\Filament\Resources\ImutDataResource\Pages\UnitKerjaOverview;
use App\Filament\Resources\ImutDataResource\Pages\SummaryDiagram;
use App\Filament\Resources\ImutDataResource\RelationManagers\ProfilesRelationManager;
use App\Filament\Resources\ImutDataResource\RelationManagers\RegionTypeRelationManager;
use App\Filament\Resources\ImutDataResource\RelationManagers\UnitKerjaRelationManager;
use App\Filament\Resources\ImutDataResource\Schema\ImutDataSchema;
use App\Filament\Resources\ImutDataResource\Table\TableSchema;
use App\Models\ImutData;
use App\Support\CacheKey;
use App\Repositories\Interfaces\ImutDataRepositoryInterface;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Resources\Resource;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ImutDataResource extends Resource implements HasShieldPermissions
{
    use HasActiveIcon;

    /**
     * Request-scoped memoized navigation badges to avoid repeated cache store reads.
     *
     * @var array<string, string|null>
     */
    private static array $navigationBadgeMemo = [];

    protected static ?string $model = ImutData::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

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

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        $memoKey = "imut_data_badge:user:{$user->id}";

        if (array_key_exists($memoKey, self::$navigationBadgeMemo)) {
            return self::$navigationBadgeMemo[$memoKey];
        }

        $repository = app(ImutDataRepositoryInterface::class);

        if ($user->can('view_all_data_imut::data')) {
            self::$navigationBadgeMemo[$memoKey] = cache()->remember(
                CacheKey::imutDataNavigationBadgeCount(),
                now()->addMinutes(10),
                fn() => (string) $repository->countForNavigationBadge($user)
            );

            return self::$navigationBadgeMemo[$memoKey];
        }

        if ($user->can('view_by_unit_kerja_imut::data')) {
            $cacheKey = CacheKey::imutDataNavigationBadgeCountForUser($user->id);

            self::$navigationBadgeMemo[$memoKey] = cache()->remember(
                $cacheKey,
                now()->addMinutes(10),
                fn() => (string) $repository->countForNavigationBadge($user)
            );

            return self::$navigationBadgeMemo[$memoKey];
        }

        self::$navigationBadgeMemo[$memoKey] = null;

        return null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(ImutDataSchema::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn() => TableSchema::query())
            ->columns(TableSchema::columns())
            ->headerActions(TableSchema::headerActions())
            ->filters(TableSchema::filters())
            ->filtersLayout(FiltersLayout::Dropdown)
            ->filtersFormColumns(2) // 🔥 bikin 3 kolom
            ->recordActions(TableSchema::actions())
            ->toolbarActions(TableSchema::bulkActions());
    }

    public static function getTableQuery(): Builder
    {
        $query = static::getModel()::query();
        $user = Auth::user();

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
            'index' => ListImutData::route('/'),
            'create' => CreateImutData::route('/create'),
            'edit' => EditImutData::route('/edit={record:slug}'),
            'create-profile' => CreateImutProfile::route('/{imutDataSlug}/profile/create'),
            'edit-profile' => EditImutProfile::route('/{imutDataSlug}/profile/edit={record}'),
            'bencmarking-region-type' => ListRegionTypeBencmarkings::route('/bencmarkings/region-type'),
            'overview-unit-kerja' => UnitKerjaOverview::route('/overview/unit-kerja'),
            'overview-imut-data' => SummaryDiagram::route('overview/summary-imut-data'),
            'manage-form-builder' => ManageFormBuilder::route('/{imutDataSlug}/{record:slug}/form-builder/{templateId?}'),
            'preview-form' => FormBuilder::route('/{imutDataSlug}/{record:slug}/form-builder/preview'),
            'list-daily-reports' => ListDailyReports::route('/{imutDataSlug}/{record:slug}/daily-reports'),
        ];
    }
}
