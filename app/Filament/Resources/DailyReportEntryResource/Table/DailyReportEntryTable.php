<?php

namespace App\Filament\Resources\DailyReportEntryResource\Table;

use App\Filament\Resources\DailyReportEntryResource;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class DailyReportEntryTable extends DailyReportEntryResource
{
    /**
     * Get table columns configuration
     */
    public static function columns(): array
    {
        return [
            TextColumn::make('formTemplate.imutdata.title')
                ->label('Indikator Mutu')
                ->description(fn($record) => $record->formTemplate->imutdata->categories->title ?? null)
                ->searchable()
                ->sortable()
                ->wrap()
                ->weight('medium')
                ->icon('heroicon-o-clipboard-document-list')
                ->iconColor('primary'),

            TextColumn::make('report_date')
                ->label('Tanggal Laporan')
                ->date('d M Y')
                ->sortable()
                ->searchable()
                ->icon('heroicon-o-calendar')
                ->iconColor('success')
                ->description(fn($record) => $record->report_date->diffForHumans()),

            TextColumn::make('entry_time')
                ->label('Jam Input')
                ->time('H:i')
                ->sortable()
                ->icon('heroicon-o-clock')
                ->iconColor('warning')
                ->toggleable(),

            TextColumn::make('unitKerja.unit_name')
                ->label('Unit Kerja')
                ->sortable()
                ->searchable()
                ->icon('heroicon-o-building-office')
                ->toggleable(isToggledHiddenByDefault: false),

            TextColumn::make('submittedBy.name')
                ->label('Pelapor')
                ->sortable()
                ->searchable()
                ->icon('heroicon-o-user')
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->icon('heroicon-o-plus-circle')
                ->toggleable(isToggledHiddenByDefault: true),

            TextColumn::make('updated_at')
                ->label('Terakhir Diubah')
                ->dateTime('d/m/Y H:i')
                ->sortable()
                ->icon('heroicon-o-pencil')
                ->toggleable(isToggledHiddenByDefault: true),
        ];
    }

    /**
     * Get table filters
     */
    public static function filters(): array
    {
        return [
            SelectFilter::make('form_template_id')
                ->label('Indikator Mutu')
                ->relationship('formTemplate', 'title')
                ->searchable()
                ->preload()
                ->multiple()
                ->placeholder('Semua Indikator'),

            SelectFilter::make('unit_kerja_id')
                ->label('Unit Kerja')
                ->relationship('unitKerja', 'unit_name')
                ->searchable()
                ->preload()
                ->multiple()
                ->placeholder('Semua Unit'),

            Filter::make('report_date')
                ->label('Periode Tanggal')
                ->form([
                    DatePicker::make('date_from')
                        ->label('Dari Tanggal')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->placeholder('Pilih tanggal awal'),
                    DatePicker::make('date_until')
                        ->label('Sampai Tanggal')
                        ->native(false)
                        ->displayFormat('d/m/Y')
                        ->placeholder('Pilih tanggal akhir'),
                ])
                ->query(function (Builder $query, array $data): Builder {
                    return $query
                        ->when(
                            $data['date_from'],
                            fn(Builder $query, $date): Builder => $query->whereDate('report_date', '>=', $date),
                        )
                        ->when(
                            $data['date_until'],
                            fn(Builder $query, $date): Builder => $query->whereDate('report_date', '<=', $date),
                        );
                })
                ->indicateUsing(function (array $data): array {
                    $indicators = [];

                    if ($data['date_from'] ?? null) {
                        $indicators[] = 'Dari: ' . \Carbon\Carbon::parse($data['date_from'])->format('d M Y');
                    }

                    if ($data['date_until'] ?? null) {
                        $indicators[] = 'Sampai: ' . \Carbon\Carbon::parse($data['date_until'])->format('d M Y');
                    }

                    return $indicators;
                }),

            Filter::make('this_month')
                ->label('Bulan Ini')
                ->query(fn(Builder $query): Builder => $query->thisMonth())
                ->toggle(),

            Filter::make('this_week')
                ->label('Minggu Ini')
                ->query(fn(Builder $query): Builder => $query->thisWeek())
                ->toggle(),
        ];
    }

    /**
     * Get table actions
     */
    public static function actions(): array
    {
        return [
            ActionGroup::make([
                ViewAction::make()
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info'),
                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning'),
                DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->color('danger'),
            ])
                ->label('Aksi')
                ->icon('heroicon-m-ellipsis-vertical')
                ->size('sm')
                ->color('gray')
                ->button(),
        ];
    }

    /**
     * Get table bulk actions
     */
    public static function bulkActions(): array
    {
        return [
            BulkActionGroup::make([
                DeleteBulkAction::make()
                    ->label('Hapus yang dipilih')
                    ->icon('heroicon-o-trash'),
            ]),
        ];
    }
}
