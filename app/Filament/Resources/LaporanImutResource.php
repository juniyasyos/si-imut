<?php

namespace App\Filament\Resources;

use App\Traits\HasActiveIcon;
use Filament\Schemas\Schema;
use App\Filament\Resources\LaporanImutResource\Pages\ListLaporanImuts;
use App\Filament\Resources\LaporanImutResource\Pages\CreateLaporanImut;
use App\Filament\Resources\LaporanImutResource\Pages\EditLaporanImut;
use App\Filament\Resources\LaporanImutResource\Pages\MonitoringDailyReports;
use App\Filament\Resources\LaporanImutResource\Pages\MonitoringUnitDetail;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\LaporanImutResource\Pages;
use App\Filament\Resources\LaporanImutResource\Pages\ImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\ImutDataUnitKerjaReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaReport;
use App\Filament\Resources\LaporanImutResource\Schema\LaporanImutSchema;
use App\Filament\Resources\LaporanImutResource\Table\LaporanImutTable;
use App\Models\LaporanImut;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LaporanImutResource extends Resource implements HasShieldPermissions
{
    use HasActiveIcon;

    protected static ?string $model = LaporanImut::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

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

    public static function form(Schema $schema): Schema
    {
        return $schema->components(LaporanImutSchema::make());
    }

    // ===================== Table Start Component =======================
    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->with([
                'unitKerjas:id,unit_name',
                'createdBy:id,name',
            ]))
            ->columns(LaporanImutTable::columns())
            ->filters(LaporanImutTable::filters())
            ->headerActions(LaporanImutTable::headerActions())
            ->recordActions(LaporanImutTable::actions())
            ->toolbarActions(LaporanImutTable::bulkActions());
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLaporanImuts::route('/'),
            'create' => CreateLaporanImut::route('/create'),
            'edit' => EditLaporanImut::route('/{record:slug}/edit'),
            'monitoring-daily-reports' => MonitoringDailyReports::route('/{record:slug}/monitoring-daily-reports'),
            'monitoring-unit-detail' => MonitoringUnitDetail::route('/{record:slug}/monitoring-unit-detail/{unit}'),
            'unit-kerja-report' => UnitKerjaReport::route('/unit-kerja-report'),
            'unit-kerja-imut-data-report-detail' => UnitKerjaImutDataReport::route('/unit-kerja-imut-data-report'),
            'imut-data-report' => ImutDataReport::route('/imut-data-report'),
            'imut-data-unit-kerja-report-detail' => ImutDataUnitKerjaReport::route('/imut-data-unit-kerja-report'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderByDesc('assessment_period_start');
    }
}