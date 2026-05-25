<?php

namespace App\Filament\Resources\LaporanImutResource\Table;

use Filament\Actions\ExportAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use App\Filament\Exports\LaporanImutExporter;
use App\Filament\Resources\LaporanImutResource;
use App\Filament\Resources\LaporanImutResource\Pages\ImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaReport;
use App\Models\ImutPenilaian;
use App\Support\CacheKey;
use App\Tables\Columns\ProgressColumn;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class LaporanImutTable extends LaporanImutResource
{
    public static function columns(): array
    {
        return [
            TextColumn::make('name')
                ->label('Nama Laporan')
                ->sortable()
                ->searchable(),

            TextColumn::make('createdBy.name')
                ->label('Pembuat Laporan')
                ->alignCenter()
                ->sortable()
                ->searchable(),

            TextColumn::make('assessment_period')
                ->label('Periode Asesmen')
                ->alignCenter()
                ->getStateUsing(fn($record) => self::formatAssessmentPeriod($record)),

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
                ->label(fn($record) => match (self::resolveStatus($record)) {
                    'coming_soon' => 'Laporan belum Dibuka',
                    'complete' => 'Lihat Hasil',
                    default => 'Input Penilaian',
                })
                ->icon(fn($record) => match (self::resolveStatus($record)) {
                    'coming_soon' => 'heroicon-o-clock',
                    'complete' => 'heroicon-o-document-check',
                    default => 'heroicon-o-clipboard-document-list',
                })
                ->color(fn($record) => match (self::resolveStatus($record)) {
                    'coming_soon' => 'gray',
                    'complete' => 'success',
                    default => 'primary',
                })
                ->disabled(fn($record) => self::resolveStatus($record) === 'coming_soon')
                ->visible(
                    fn($record) =>
                    method_exists($record, 'trashed') &&
                        ! $record->trashed() &&
                        self::userHasAccessToLaporan($record)
                )
                ->url(function ($record) {
                    $url = self::getIsiPenilaianUrl($record);
                    if (! $url) return null;

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
                            ! $record->trashed() &&
                            Auth::user()->can('view_unit_kerja_report_laporan::imut')
                    )
                    ->url(fn($record) => UnitKerjaReport::getUrl(['laporan_id' => $record->id])),

                Action::make('summary_imut_data')
                    ->label('Summary IMUT')
                    ->icon('heroicon-o-document-chart-bar')
                    ->color('info')
                    ->visible(
                        fn($record) => method_exists($record, 'trashed') &&
                            ! $record->trashed() &&
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
            $today->gt($end)   => 'complete',
            default            => 'process',
        };
    }

    protected static function userHasAccessToLaporan($record): bool
    {
        $user = Auth::user();

        $userUnitKerjaIds = $user->unitKerjas->pluck('id')->toArray();
        $laporanUnitKerjaIds = $record->unitKerjas->pluck('id')->toArray();

        return ! empty(array_intersect($userUnitKerjaIds, $laporanUnitKerjaIds));
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

        return "$start - $end";
    }
}
