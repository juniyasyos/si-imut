<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyReportEntryResource\Infolist\DailyReportEntryInfolist;
use App\Filament\Resources\DailyReportEntryResource\Pages;
use App\Filament\Resources\DailyReportEntryResource\Schema\DailyReportEntrySchema;
use App\Filament\Resources\DailyReportEntryResource\Table\DailyReportEntryTable;
use App\Models\DailyReportEntry;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DailyReportEntryResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = DailyReportEntry::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Laporan Harian';

    protected static ?string $modelLabel = 'Laporan Harian';

    protected static ?string $pluralModelLabel = 'Laporan Harian';

    protected static ?string $navigationGroup = 'Quality Indicators';

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = true;

    protected static ?string $recordTitleAttribute = 'report_date';

    protected static ?string $slug = 'daily-report-entries';

    /**
     * Get the form schema for create and edit pages
     */
    public static function form(Form $form): Form
    {
        return $form->schema(DailyReportEntrySchema::make());
    }

    /**
     * Get the table configuration
     */
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn(Builder $query) => $query
                    ->forUserUnits(Auth::user())
                    ->with(['formTemplate.imutdata.categories', 'formTemplate.formFields', 'unitKerja', 'submittedBy'])
                    ->latest('report_date')
            )
            ->columns(DailyReportEntryTable::columns())
            ->filters(DailyReportEntryTable::filters())
            ->actions(DailyReportEntryTable::actions())
            ->bulkActions(DailyReportEntryTable::bulkActions())
            ->defaultSort('report_date', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('60s')
            ->deferLoading()
            ->persistFiltersInSession()
            ->persistSortInSession()
            ->persistSearchInSession()
            ->emptyStateHeading('Belum ada laporan harian')
            ->emptyStateDescription('Mulai buat laporan harian dengan mengklik tombol "Buat Laporan Harian" di bawah')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }

    /**
     * Get the infolist schema for view page
     */
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema(DailyReportEntryInfolist::make());
    }

    /**
     * Get relations for this resource
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Get pages for this resource
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDailyReportEntries::route('/'),
            'create' => Pages\CreateDailyReportEntry::route('/create'),
            'view' => Pages\ViewDailyReportEntry::route('/{record}'),
            'edit' => Pages\EditDailyReportEntry::route('/{record}/edit'),
            // 'list' => Pages\ListUnitKerjaDailyReport::route('/list'),
        ];
    }

    // /**
    //  * Check if user can view any records
    //  */
    // public static function canViewAny(): bool
    // {
    //     $user = Auth::user();

    //     if (!$user) {
    //         return false;
    //     }

    //     /** @var \App\Models\User $user */
    //     return $user->hasRole('Unit Kerja') && $user->unitKerjas()->exists();
    // }

    /**
     * Modify query to show only user's unit data
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->forUserUnits(Auth::user());
    }

    /**
     * Get permissions for shield plugin
     */
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

    /**
     * Get navigation badge (count of reports this month)
     */
    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        /** @var \App\Models\User $user */
        if (!$user->hasRole('Unit Kerja')) {
            return null;
        }

        $count = static::getModel()::query()
            ->forUserUnits($user)
            ->thisMonth()
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    /**
     * Get navigation badge color
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
