<?php

namespace App\Filament\Resources\LaporanImutResource\Table;

use App\Filament\Exports\LaporanImutExporter;
use App\Filament\Resources\LaporanImutResource;
use App\Filament\Resources\LaporanImutResource\Pages\ImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaReport;
use App\Models\LaporanImutAutoGenerationSetting;
use Carbon\Carbon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class LaporanImutTable extends LaporanImutResource
{
    /*
    |--------------------------------------------------------------------------
    | Columns
    |--------------------------------------------------------------------------
    */

    public static function columns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Laporan')
                ->searchable()
                ->sortable()
                ->description(fn($record): string => self::formatCompletionSummary($record)),

            TextColumn::make('assessment_period')
                ->label('Periode')
                ->icon('heroicon-m-calendar')
                ->badge()
                ->color(fn($record) => $record->status === 'complete' ? 'success' : 'warning')
                ->getStateUsing(fn($record): string => self::formatAssessmentPeriod($record))
                ->description(fn($record): string => self::formatAnalysisPeriod($record)),

            TextColumn::make('imut_data_summary')
                ->label('Data IMUT')
                ->alignCenter()
                ->getStateUsing(fn($record): string => self::formatImutDataSummary($record))
                ->badge()
                ->color('gray'),

            TextColumn::make('status')
                ->label('Pengisian Analisis & Rekomendasi')
                ->badge()
                ->alignCenter()
                ->getStateUsing(fn($record): string => self::analysisSubmissionState($record))
                ->formatStateUsing(fn(string $state): string => self::formatAnalysisSubmissionLabel($state))
                ->color(fn(string $state): string => self::analysisSubmissionColor($state)),

            TextColumn::make('created_at')
                ->label('Dibuat')
                ->alignCenter()
                ->dateTime()
                ->toggleable(isToggledHiddenByDefault: true)
                ->sortable(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    public static function filters(): array
    {
        return [
            TrashedFilter::make()
                ->default('with'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Header Actions
    |--------------------------------------------------------------------------
    */

    public static function headerActions(): array
    {
        return [
            ExportAction::make()
                ->exporter(LaporanImutExporter::class),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Bulk Actions
    |--------------------------------------------------------------------------
    */

    public static function bulkActions(): array
    {
        return [
            BulkActionGroup::make([
                RestoreBulkAction::make(),
                ForceDeleteBulkAction::make(),
            ]),

            DeleteBulkAction::make(),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Row Actions
    |--------------------------------------------------------------------------
    */

    public static function actions(): array
    {
        return [
            Action::make('isi_data_imut')
                ->button()
                ->label(fn($record): string => self::dataImutActionLabel($record))
                ->icon(fn($record): string => self::dataImutActionIcon($record))
                ->color(fn($record): string => self::dataImutActionColor($record))
                ->tooltip(fn($record): string => self::dataImutActionTooltip($record))
                ->disabled(fn($record): bool => self::resolveStatus($record) === 'coming_soon')
                ->visible(fn($record): bool => self::canAccessDataImutAction($record))
                ->url(fn($record): ?string => self::getIsiPenilaianUrl($record)),

            ActionGroup::make([
                Action::make('summary_unit_kerja')
                    ->label('Ringkasan per Unit Kerja')
                    ->icon('heroicon-o-building-office')
                    ->color('info')
                    ->visible(fn($record): bool => self::canViewUnitKerjaSummary($record))
                    ->url(fn($record): string => UnitKerjaReport::getUrl([
                        'laporan_id' => $record->id,
                    ])),

                Action::make('summary_imut_data')
                    ->label('Ringkasan per Indikator')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('info')
                    ->visible(fn($record): bool => self::canViewImutDataSummary($record))
                    ->url(fn($record): string => ImutDataReport::getUrl([
                        'laporan_id' => $record->id,
                    ])),
            ])
                ->label('Aksi Cepat')
                ->button()
                ->tooltip('Buka ringkasan laporan berdasarkan unit kerja atau indikator')
                ->icon('heroicon-o-bolt')
                ->color('secondary'),

            ActionGroup::make([
                EditAction::make()
                    ->label('Edit Laporan'),

                DeleteAction::make()
                    ->label('Hapus Laporan'),

                RestoreAction::make()
                    ->label('Pulihkan Laporan')
                    ->visible(fn($record): bool => self::canRestoreRecord($record)),

                ForceDeleteAction::make()
                    ->label('Hapus Permanen')
                    ->visible(fn($record): bool => self::canForceDeleteRecord($record)),
            ])
                ->label('Aksi')
                ->button()
                ->tooltip('Edit, hapus, pulihkan, atau hapus permanen laporan')
                ->icon('heroicon-o-ellipsis-vertical')
                ->color('gray'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Status Helpers
    |--------------------------------------------------------------------------
    */

    protected static function resolveStatus($record): string
    {
        $today = Carbon::today();
        $start = Carbon::parse($record->assessment_period_start);
        $end = Carbon::parse($record->assessment_period_end);

        return match (true) {
            $today->lt($start) => 'coming_soon',
            $today->gt($end) => 'complete',
            default => 'process',
        };
    }

    protected static function analysisSubmissionState($record): string
    {
        $today = Carbon::today();
        $analysisStart = Carbon::parse($record->assessment_period_end)->startOfDay();

        $duration = $record->recommendation_analysis_duration
            ?? LaporanImutAutoGenerationSetting::getInstance()->recommendation_analysis_duration;

        $analysisEnd = $analysisStart->copy()->addDays($duration)->endOfDay();

        return match (true) {
            $today->lt($analysisStart) => 'not_open',
            $today->gt($analysisEnd) => 'closed',
            default => 'open',
        };
    }

    protected static function formatAnalysisSubmissionLabel(string $state): string
    {
        return match ($state) {
            'not_open' => 'Belum Dibuka',
            'open' => 'Masih Dibuka',
            'closed' => 'Sudah Ditutup',
            default => 'Tidak Diketahui',
        };
    }

    protected static function analysisSubmissionColor(string $state): string
    {
        return match ($state) {
            'not_open' => 'warning',
            'open' => 'success',
            'closed' => 'gray',
            default => 'secondary',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Action State Helpers
    |--------------------------------------------------------------------------
    */

    protected static function dataImutActionLabel($record): string
    {
        return match (self::resolveStatus($record)) {
            'coming_soon' => 'Periode Belum Dibuka',
            'complete' => 'Lihat Data IMUT',
            default => 'Isi Data IMUT',
        };
    }

    protected static function dataImutActionIcon($record): string
    {
        return match (self::resolveStatus($record)) {
            'coming_soon' => 'heroicon-o-lock-closed',
            'complete' => 'heroicon-o-eye',
            default => 'heroicon-o-pencil-square',
        };
    }

    protected static function dataImutActionColor($record): string
    {
        return match (self::resolveStatus($record)) {
            'coming_soon' => 'gray',
            'complete' => 'success',
            default => 'primary',
        };
    }

    protected static function dataImutActionTooltip($record): string
    {
        return match (self::resolveStatus($record)) {
            'coming_soon' => 'Periode laporan belum dibuka untuk pengisian data IMUT.',
            'complete' => 'Lihat data IMUT yang sudah selesai diisi.',
            default => 'Isi data indikator mutu untuk periode laporan ini.',
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Permission Helpers
    |--------------------------------------------------------------------------
    */

    protected static function canAccessDataImutAction($record): bool
    {
        return self::isActiveRecord($record)
            && self::userHasAccessToLaporan($record);
    }

    protected static function canViewUnitKerjaSummary($record): bool
    {
        return self::isActiveRecord($record)
            && Auth::user()?->can('view_unit_kerja_report_laporan::imut');
    }

    protected static function canViewImutDataSummary($record): bool
    {
        return self::isActiveRecord($record)
            && Auth::user()?->can('view_imut_data_report_laporan::imut');
    }

    protected static function canRestoreRecord($record): bool
    {
        return Gate::allows('restore', $record)
            && self::isTrashedRecord($record);
    }

    protected static function canForceDeleteRecord($record): bool
    {
        return Gate::allows('forceDelete', $record)
            && self::isTrashedRecord($record);
    }

    protected static function userHasAccessToLaporan($record): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        $userUnitKerjaIds = $user->unitKerjas->pluck('id')->toArray();
        $laporanUnitKerjaIds = $record->unitKerjas->pluck('id')->toArray();

        return !empty(array_intersect($userUnitKerjaIds, $laporanUnitKerjaIds));
    }

    protected static function isActiveRecord($record): bool
    {
        return method_exists($record, 'trashed')
            && !$record->trashed();
    }

    protected static function isTrashedRecord($record): bool
    {
        return method_exists($record, 'trashed')
            && $record->trashed();
    }

    /*
    |--------------------------------------------------------------------------
    | URL Helpers
    |--------------------------------------------------------------------------
    */

    protected static function getIsiPenilaianUrl($record): ?string
    {
        $user = Auth::user();

        if (!$user) {
            return null;
        }

        $userUnitIds = static::getAuthUserUnitKerjaIds();
        $laporanUnitIds = $record->unitKerjas->pluck('id')->toArray();

        $commonUnitIds = array_values(array_intersect($userUnitIds, $laporanUnitIds));

        if (empty($commonUnitIds)) {
            return null;
        }

        $url = UnitKerjaImutDataReport::getUrl([
            'laporan_id' => $record->id,
            'unit_kerja_id' => $commonUnitIds[0],
        ]);

        return self::resolveStatus($record) === 'complete'
            ? $url . '&readonly=1'
            : $url;
    }

    /*
    |--------------------------------------------------------------------------
    | Formatters
    |--------------------------------------------------------------------------
    */

    protected static function formatCompletionSummary($record): string
    {
        $canCreateImutData = static::canCreateImutData();

        if ($canCreateImutData) {
            $completedUnitKerja = static::getCompletedUnitKerjaCount($record);
            $totalUnitKerja = number_format($record->unit_kerjas_count ?? 0);
            $completionPercentage = (int) ($record->unit_kerjas_count ?? 0) > 0
                ? round(($completedUnitKerja / (int) $record->unit_kerjas_count) * 100, 1)
                : 0;

            return sprintf(
                'Analisis %s/%s unit kerja sudah lengkap (%s%%).',
                number_format($completedUnitKerja),
                $totalUnitKerja,
                number_format($completionPercentage, 1),
            );
        }

        return sprintf(
            'Analisis & Rekomendasi: %s/%s Terisi',
            number_format($record->completed_penilaians_count ?? 0),
            number_format($record->imut_penilaians_count ?? 0),
        );
    }

    protected static function getCompletedUnitKerjaCount($record): int
    {
        $unitKerjaStats = $record->laporanUnitKerjas()
            ->select([
                'laporan_unit_kerjas.unit_kerja_id',
                \Illuminate\Support\Facades\DB::raw('COUNT(imut_penilaians.id) as total_indicators'),
                \Illuminate\Support\Facades\DB::raw('SUM(
                    CASE
                        WHEN imut_penilaians.numerator_value IS NOT NULL
                        AND imut_penilaians.denominator_value IS NOT NULL
                        AND imut_penilaians.denominator_value != 0
                        THEN 1
                        ELSE 0
                    END
                ) as filled_indicators'),
            ])
            ->join('imut_penilaians', 'laporan_unit_kerjas.id', '=', 'imut_penilaians.laporan_unit_kerja_id')
            ->groupBy('laporan_unit_kerjas.unit_kerja_id', 'laporan_unit_kerjas.id')
            ->get();

        return $unitKerjaStats
            ->groupBy('unit_kerja_id')
            ->filter(function ($stats): bool {
                $totalIndicators = $stats->sum('total_indicators');
                $totalFilled = $stats->sum('filled_indicators');

                return $totalIndicators > 0 && $totalFilled == $totalIndicators;
            })
            ->count();
    }

    protected static function formatAssessmentPeriod($record): string
    {
        $start = Carbon::parse($record->assessment_period_start)->translatedFormat('d M');
        $end = Carbon::parse($record->assessment_period_end)->translatedFormat('d M Y');

        return "{$start} → {$end}";
    }

    protected static function formatAnalysisPeriod($record): string
    {
        $end = Carbon::parse($record->assessment_period_end);

        $duration = $record->recommendation_analysis_duration
            ?? LaporanImutAutoGenerationSetting::getInstance()->recommendation_analysis_duration;

        return sprintf(
            'Analisis: %s → %s',
            $end->translatedFormat('d M Y'),
            $end->copy()->addDays($duration)->translatedFormat('d M Y'),
        );
    }

    protected static function formatImutDataSummary($record): string
    {
        return sprintf(
            'Harian: %s • Bulanan: %s',
            number_format($record->daily_imut_data_count ?? 0),
            number_format($record->monthly_imut_data_count ?? 0),
        );
    }
}