<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyReportEntryResource\Infolist\DailyReportEntryInfolist;
use App\Filament\Resources\DailyReportEntryResource\Pages;
use App\Filament\Resources\DailyReportEntryResource\Schema\DailyReportEntrySchema;
use App\Filament\Resources\DailyReportEntryResource\Table\DailyReportEntryTable;
use App\Models\DailyReportResponse;
use App\Models\FormTemplate;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DailyReportEntryResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = DailyReportResponse::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static ?string $navigationLabel = 'Laporan Harian';

    protected static ?string $modelLabel = 'Laporan Harian';

    protected static ?string $pluralModelLabel = 'Laporan Harian';

    protected static ?string $navigationGroup = 'Quality Indicators';

    protected static ?int $navigationSort = 1;

    protected static bool $shouldRegisterNavigation = true;

    /**
     * Check if navigation should be registered for current user
     */
    public static function shouldRegisterNavigation(): bool
    {
        return static::canViewAny();
    }

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
                    ->whereHas('formTemplate', function (Builder $query) {
                        $query->whereHas('imutProfile', function (Builder $query) {
                            $query->where('valid_from', '<=', now())
                                ->where(function (Builder $q) {
                                    $q->whereNull('valid_until')
                                        ->orWhere('valid_until', '>=', now());
                                });
                        });
                        // Removed scoring_config filter to show all daily reports
                        // scoring_config is optional and shouldn't prevent viewing reports
                    })
                    ->with(['formTemplate.imutProfile.imutData.categories', 'formTemplate.formFields', 'unitKerja', 'submittedBy'])
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

    /**
     * Check if user can view any records
     */
    public static function canViewAny(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        return $user->unitKerjas()->exists();
    }

    /**
     * Check if user can create records.  In addition to belonging to at
     * least one unit, we also require that the indicator referenced by the
     * `indicator` query parameter is part of an IMUT dataset assigned to
     * one of the user's units.  This keeps the behaviour in sync with the
     * page's `authorizeAccess` method.
     */
    public static function canCreate(): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        if (! $user->unitKerjas()->exists()) {
            return false;
        }

        $indicatorId = request()->query('indicator') ?? request()->input('indicator');
        if ($indicatorId) {
            $template = FormTemplate::with('imutProfile.imutData.unitKerja')->find($indicatorId);

            if (! $template) {
                return false;
            }

            if ($user->can('view_all_data_imut::data')) {
                return true;
            }

            $userUnitIds = $user->unitKerjas()->pluck('unit_kerja.id')->toArray();
            $hasUnitAccess = $template->imutProfile
                && $template->imutProfile->imutData
                && $template->imutProfile->imutData->unitKerja()
                ->whereIn('unit_kerja_id', $userUnitIds)
                ->exists();

            return $hasUnitAccess && $user->can('view_by_unit_kerja_imut::data');
        }

        return false;
    }

    /**
     * Check if user can view specific record
     */
    public static function canView($record): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        if (!$user->unitKerjas()->exists()) {
            return false;
        }

        // Check if the record belongs to user's unit kerja
        $userUnitIds = $user->unitKerjas()->pluck('unit_kerja.id');
        return $userUnitIds->contains($record->unit_kerja_id);
    }

    /**
     * Check if user can edit specific record
     */
    public static function canEdit($record): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        if (!$user->unitKerjas()->exists()) {
            return false;
        }

        // Check if the record belongs to user's unit kerja
        $userUnitIds = $user->unitKerjas()->pluck('unit_kerja.id');
        return $userUnitIds->contains($record->unit_kerja_id);
    }

    /**
     * Check if user can delete specific record
     */
    public static function canDelete($record): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        if (!$user->unitKerjas()->exists()) {
            return false;
        }

        // Check if the record belongs to user's unit kerja
        $userUnitIds = $user->unitKerjas()->pluck('unit_kerja.id');
        return $userUnitIds->contains($record->unit_kerja_id);
    }

    /**
     * Check if user can delete any records
     */
    public static function canDeleteAny(): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        /** @var \App\Models\User $user */
        return $user->unitKerjas()->exists();
    }

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

    /**
     * Generate URL for creating report entry for specific indicator and date
     * Usage: DailyReportEntryResource::getCreateUrl($indicatorId, $date)
     */
    public static function getCreateUrl(int $indicatorId, ?string $date = null): string
    {
        return static::getUrl('create') . '?' . http_build_query([
            'indicator' => $indicatorId,
            'date' => $date ?? now()->format('Y-m-d')
        ]);
    }

    /**
     * Generate URL for editing report entry with optional parameters
     * Usage: DailyReportEntryResource::getEditUrl($recordId, $indicatorId, $date)
     */
    public static function getEditUrl(int $recordId, ?int $indicatorId = null, ?string $date = null): string
    {
        $url = static::getUrl('edit', ['record' => $recordId]);

        $params = [];
        if ($indicatorId) {
            $params['indicator'] = $indicatorId;
        }
        if ($date) {
            $params['date'] = $date;
        }

        return $params ? $url . '?' . http_build_query($params) : $url;
    }

    /**
     * Generate URL for viewing report entry with optional parameters
     * Usage: DailyReportEntryResource::getViewUrl($recordId, $indicatorId, $date)
     */
    public static function getViewUrl(int $recordId, ?int $indicatorId = null, ?string $date = null): string
    {
        $url = static::getUrl('view', ['record' => $recordId]);

        $params = [];
        if ($indicatorId) {
            $params['indicator'] = $indicatorId;
        }
        if ($date) {
            $params['date'] = $date;
        }

        return $params ? $url . '?' . http_build_query($params) : $url;
    }

    /**
     * Generate URL for opening slide-over for specific indicator and date
     * Usage: DailyReportEntryResource::getSlideOverUrl($indicatorId, $date)
     */
    public static function getSlideOverUrl(int $indicatorId, string $date): string
    {
        return static::getUrl('index') . '?indicator_id=' . $indicatorId . '&date=' . $date;
    }
}
