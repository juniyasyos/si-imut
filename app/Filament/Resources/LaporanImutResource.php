<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanImutResource\Pages;
use App\Filament\Resources\LaporanImutResource\Pages\ImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\ImutDataUnitKerjaReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaReport;
use App\Filament\Resources\LaporanImutResource\Schema\LaporanImutSchema;
use App\Filament\Resources\LaporanImutResource\Table\LaporanImutTable;
use App\Models\LaporanImut;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanImutResource extends Resource implements HasShieldPermissions
{
    use \App\Traits\HasActiveIcon;

    protected static ?int $authUserId = null;

    protected static array $authUserUnitKerjaIds = [];

    protected static bool $authCanCreateImutData = false;

    protected static ?string $model = LaporanImut::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Audit Bulanan';

    protected static ?string $modelLabel = 'Audit Bulanan';

    public static function getGloballySearchableAttributes(): array
    {
        return ['assessment_period'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Periode Asesmen' => $record->assessment_period,
        ];
    }

    public static function getGlobalSearchResultTitle(Model $record): string
    {
        return "Laporan {$record->assessment_period}";
    }

    public static function getGlobalSearchResultUrl(Model $record): ?string
    {
        return static::getUrl(name: 'edit', parameters: ['record' => $record]);
    }

    protected static function resolveAuthContext(): void
    {
        $user = Auth::user();
        $userId = $user?->id;

        if (static::$authUserId === $userId) {
            return;
        }

        static::$authUserId = $userId;
        static::$authUserUnitKerjaIds = $user
            ? $user->unitKerjas->pluck('id')->toArray()
            : [];
        static::$authCanCreateImutData = $user?->can('create_imut::data') ?? false;
    }

    protected static function getAuthUserUnitKerjaIds(): array
    {
        static::resolveAuthContext();

        return static::$authUserUnitKerjaIds;
    }

    protected static function canCreateImutData(): bool
    {
        static::resolveAuthContext();

        return static::$authCanCreateImutData;
    }

    public static function getPermissionPrefixes(): array
    {
        return array_merge([
            // default Filament Shield permissions
            'view',
            'view_any',
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

            // custom laporan report
            'view_unit_kerja_report',
            'view_unit_kerja_report_detail',
            'view_imut_data_report',
            'view_imut_data_report_detail',
        ]);
    }

    public static function getLabel(): ?string
    {
        return __('Laporan IMUT');
    }

    public static function getPluralLabel(): ?string
    {
        return __('Daftar Laporan IMUT');
    }

    public static function getNavigationGroup(): ?string
    {
        return __('filament-forms::imut-data.navigation.group');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(LaporanImutSchema::make());
    }

    // ===================== Table Start Component =======================
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                $query = $query->with([
                    'unitKerjas:id,unit_name',
                    'createdBy:id,name',
                ]);

                $userUnitIds = static::getAuthUserUnitKerjaIds();
                $canCreateImutData = static::canCreateImutData();

                // withCount for unitKerjas remains global, but imutPenilaians counts are scoped to user's units
                $query->withCount(['unitKerjas']);

                if ($canCreateImutData) {
                    // User with create IMUT data permission sees global counts
                    $query->withCount([
                        'imutPenilaians as imut_penilaians_count',
                        'imutPenilaians as completed_penilaians_count' => fn($q) => $q->whereNotNull('numerator_value'),
                    ]);

                    $query->addSelect([
                        'daily_imut_data_count' => DB::table('imut_data as d')
                            ->selectRaw('COUNT(DISTINCT d.id)')
                            ->join('imut_profil as p', 'p.imut_data_id', '=', 'd.id')
                            ->whereColumn('p.valid_from', '<=', 'laporan_imuts.assessment_period_end')
                            ->where(function ($q) {
                                $q->whereNull('p.valid_until')
                                    ->orWhereColumn('p.valid_until', '>=', 'laporan_imuts.assessment_period_start');
                            })
                            ->where('d.is_monthly', false),

                        'monthly_imut_data_count' => DB::table('imut_data as d')
                            ->selectRaw('COUNT(DISTINCT d.id)')
                            ->join('imut_profil as p', 'p.imut_data_id', '=', 'd.id')
                            ->whereColumn('p.valid_from', '<=', 'laporan_imuts.assessment_period_end')
                            ->where(function ($q) {
                                $q->whereNull('p.valid_until')
                                    ->orWhereColumn('p.valid_until', '>=', 'laporan_imuts.assessment_period_start');
                            })
                            ->where('d.is_monthly', true),
                    ]);
                } else {
                    // User without create IMUT data permission sees counts scoped to their unit kerja
                    $unitScope = fn($q) => $q->whereIn('unit_kerja_id', $userUnitIds);

                    $query->withCount([
                        'imutPenilaians as imut_penilaians_count' => fn($q) => $q
                            ->whereHas('laporanUnitKerja', $unitScope),

                        'imutPenilaians as completed_penilaians_count' => fn($q) => $q
                            ->whereNotNull('numerator_value')
                            ->whereHas('laporanUnitKerja', $unitScope),
                    ]);

                    $query->addSelect([
                        'daily_imut_data_count' => DB::table('imut_data as d')
                            ->selectRaw('COUNT(DISTINCT d.id)')
                            ->join('imut_data_unit_kerja as du', 'du.imut_data_id', '=', 'd.id')
                            ->join('imut_profil as p', 'p.imut_data_id', '=', 'd.id')
                            ->whereIn('du.unit_kerja_id', $userUnitIds)
                            ->whereColumn('p.valid_from', '<=', 'laporan_imuts.assessment_period_end')
                            ->where(function ($q) {
                                $q->whereNull('p.valid_until')
                                    ->orWhereColumn('p.valid_until', '>=', 'laporan_imuts.assessment_period_start');
                            })
                            ->where('d.is_monthly', false),

                        'monthly_imut_data_count' => DB::table('imut_data as d')
                            ->selectRaw('COUNT(DISTINCT d.id)')
                            ->join('imut_data_unit_kerja as du', 'du.imut_data_id', '=', 'd.id')
                            ->join('imut_profil as p', 'p.imut_data_id', '=', 'd.id')
                            ->whereIn('du.unit_kerja_id', $userUnitIds)
                            ->whereColumn('p.valid_from', '<=', 'laporan_imuts.assessment_period_end')
                            ->where(function ($q) {
                                $q->whereNull('p.valid_until')
                                    ->orWhereColumn('p.valid_until', '>=', 'laporan_imuts.assessment_period_start');
                            })
                            ->where('d.is_monthly', true),
                    ]);
                }

                return $query->latest();
            })
            ->columns(LaporanImutTable::columns())
            ->filters(LaporanImutTable::filters())
            ->headerActions(LaporanImutTable::headerActions())
            ->actions(LaporanImutTable::actions())
            ->bulkActions(LaporanImutTable::bulkActions());
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanImuts::route('/'),
            'create' => Pages\CreateLaporanImut::route('/create'),
            'edit' => Pages\EditLaporanImut::route('/{record:slug}/edit'),
            'monitoring-daily-reports' => Pages\MonitoringDailyReports::route('/{record:slug}/monitoring-daily-reports'),
            'monitoring-unit-detail' => Pages\MonitoringUnitDetail::route('/{record:slug}/monitoring-unit-detail/{unit}'),
            'unit-kerja-report' => UnitKerjaReport::route('/unit-kerja-report'),
            'unit-kerja-imut-data-report-detail' => UnitKerjaImutDataReport::route('/unit-kerja-imut-data-report'),
            'imut-data-report' => ImutDataReport::route('/imut-data-report'),
            'imut-data-unit-kerja-report-detail' => ImutDataUnitKerjaReport::route('/imut-data-unit-kerja-report'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()
            ->orderByDesc('assessment_period_start');
    }
}