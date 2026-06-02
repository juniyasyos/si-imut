<?php

namespace App\Filament\Resources\LaporanImutResource\Table;

use App\Filament\Exports\LaporanImutExporter;
use App\Filament\Resources\LaporanImutResource;
use App\Filament\Resources\LaporanImutResource\Pages\ImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaReport;
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
use Carbon\Carbon;
use App\Models\LaporanImutAutoGenerationSetting;

class LaporanImutTable extends LaporanImutResource
{
    public static function columns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Laporan')
                ->searchable()
                ->sortable()
                ->description(fn($record) => sprintf(
                    'Analisis & Rekomendasi: %s/%s Terisi',
                    number_format($record->completed_penilaians_count),
                    number_format($record->imut_penilaians_count),
                )),
            TextColumn::make('assessment_period')
                ->label('Periode')
                ->icon('heroicon-m-calendar')
                ->getStateUsing(fn($record) => self::formatAssessmentPeriod($record))
                ->description(function ($record) {
                    $end = Carbon::parse($record->assessment_period_end);

                    $analysisDuration =
                        $record->recommendation_analysis_duration
                        ?? LaporanImutAutoGenerationSetting::getInstance()->recommendation_analysis_duration;

                    return 'Analisis: '
                        . $end->translatedFormat('d M Y')
                        . ' → '
                        . $end->copy()->addDays($analysisDuration)->translatedFormat('d M Y');
                }),

            TextColumn::make('imut_data_summary')
                ->label('IMUT Data')
                ->alignCenter()
                ->getStateUsing(fn($record): string => sprintf(
                    'Harian: %s • Bulanan: %s',
                    number_format($record->daily_imut_data_count ?? 0),
                    number_format($record->monthly_imut_data_count ?? 0),
                ))
                ->badge()
                ->color('gray'),

            TextColumn::make('status')
                ->label('Status')
                ->badge()
                ->alignCenter()
                ->getStateUsing(fn($record) => self::resolveStatus($record))
                ->color(fn($state) => match ($state) {
                    'coming_soon' => 'gray',
                    'process' => 'primary',
                    'complete' => 'success',
                    default => 'secondary',
                }),

            TextColumn::make('created_at')
                ->label('Dibuat')
                ->alignCenter()
                ->dateTime()
                ->toggleable(isToggledHiddenByDefault: true)
                ->sortable(),
        ];
    }

    public static function filters(): array
    {
        return [
            TrashedFilter::make()
                ->default('with'),
        ];
    }

    public static function headerActions(): array
    {
        return [
            ExportAction::make()->exporter(LaporanImutExporter::class),
        ];
    }

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

    public static function actions(): array
    {
        return [
            Action::make('isi_penilaian')
                ->button()
                ->label(fn($record): string => match (self::resolveStatus($record)) {
                    'coming_soon' => 'Periode Belum Dibuka',
                    'complete' => 'Lihat Data IMUT',
                    default => 'Isi Data IMUT',
                })
                ->icon(fn($record): string => match (self::resolveStatus($record)) {
                    'coming_soon' => 'heroicon-o-clock',
                    'complete' => 'heroicon-o-eye',
                    default => 'heroicon-o-clipboard-document-list',
                })
                ->color(fn($record): string => match (self::resolveStatus($record)) {
                    'coming_soon' => 'gray',
                    'complete' => 'success',
                    default => 'primary',
                })
                ->tooltip(fn($record): string => match (self::resolveStatus($record)) {
                    'coming_soon' => 'Periode pengisian data indikator mutu belum dimulai.',
                    'complete' => 'Lihat hasil pengisian data indikator mutu.',
                    default => 'Input data indikator mutu untuk periode laporan ini.',
                })
                ->disabled(
                    fn($record): bool =>
                    self::resolveStatus($record) === 'coming_soon'
                )
                ->visible(
                    fn($record): bool =>
                    method_exists($record, 'trashed')
                    && !$record->trashed()
                    && self::userHasAccessToLaporan($record)
                )
                ->url(function ($record): ?string {
                    $url = self::getIsiPenilaianUrl($record);

                    if (!$url) {
                        return null;
                    }

                    return self::resolveStatus($record) === 'complete'
                        ? $url . '&readonly=1'
                        : $url;
                }),

            ActionGroup::make([
                Action::make('summary_unit_kerja')
                    ->label('Summary Unit')
                    ->icon('heroicon-o-building-office')
                    ->color('info')
                    ->visible(
                        fn($record) => method_exists($record, 'trashed') &&
                        !$record->trashed() &&
                        Auth::user()->can('view_unit_kerja_report_laporan::imut')
                    )
                    ->url(fn($record) => UnitKerjaReport::getUrl(['laporan_id' => $record->id])),

                Action::make('summary_imut_data')
                    ->label('Summary IMUT')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('info')
                    ->visible(
                        fn($record) => method_exists($record, 'trashed') &&
                        !$record->trashed() &&
                        Auth::user()->can('view_imut_data_report_laporan::imut')
                    )
                    ->url(fn($record) => ImutDataReport::getUrl(['laporan_id' => $record->id])),
            ])
                ->tooltip('Lihat Laporan Ringkasan')
                ->icon('heroicon-o-chart-bar')
                ->label('Laporan Ringkasan')
                ->color('secondary'),

            ActionGroup::make([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make()
                    ->visible(
                        fn($record) =>
                        Gate::allows('restore', $record) &&
                        method_exists($record, 'trashed') &&
                        $record->trashed()
                    ),
                ForceDeleteAction::make()
                    ->visible(
                        fn($record) =>
                        Gate::allows('forceDelete', $record) &&
                        method_exists($record, 'trashed') &&
                        $record->trashed()
                    ),
            ])
                ->tooltip('Kelola Laporan')
                ->icon('heroicon-o-ellipsis-horizontal')
                ->label('Aksi')
                ->color('gray'),
        ];
    }

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

    protected static function userHasAccessToLaporan($record): bool
    {
        $user = Auth::user();

        $userUnitKerjaIds = $user->unitKerjas->pluck('id')->toArray();
        $laporanUnitKerjaIds = $record->unitKerjas->pluck('id')->toArray();

        return !empty(array_intersect($userUnitKerjaIds, $laporanUnitKerjaIds));
    }

    protected static function getIsiPenilaianUrl($record): ?string
    {
        $user = Auth::user();

        // Prefer using already-loaded collections to avoid extra DB calls per row.
        $userUnitIds = $user->unitKerjas->pluck('id')->toArray();
        $laporanUnitIds = $record->unitKerjas->pluck('id')->toArray();

        $common = array_values(array_intersect($userUnitIds, $laporanUnitIds));

        if (empty($common)) {
            return null;
        }

        $matchingId = $common[0];

        return UnitKerjaImutDataReport::getUrl([
            'laporan_id' => $record->id,
            'unit_kerja_id' => $matchingId,
        ]);
    }

    protected static function formatAssessmentPeriod($record): string
    {
        $start = Carbon::parse($record->assessment_period_start)->translatedFormat('d M');
        $end = Carbon::parse($record->assessment_period_end)->translatedFormat('d M Y');

        return "$start → $end";
    }
}
