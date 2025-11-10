<?php

namespace App\Filament\Widgets;

use App\Facades\LaporanImut as LaporanImutFacade;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport;
use App\Models\LaporanUnitKerja;
use Filament\Tables;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;
use Filament\Tables\Filters\SelectFilter;

class ImutTercapai extends BaseWidget
{
    protected static ?int $sort = 6;
    protected int|string|array $columnSpan = 'full';
    protected static ?string $heading = '⚠️ Unit Kerja Perlu Perhatian';
    protected static ?string $pollingInterval = '30s';

    public static function canView(): bool
    {
        return Auth::user()?->can('widget_ImutTercapai') ?? false;
    }

    public function table(Tables\Table $table): Tables\Table
    {
        $laporan = LaporanImutFacade::getLatestLaporan();

        if (! $laporan) {
            return $table
                ->query(LaporanUnitKerja::query()->whereRaw('1 = 0'))
                ->columns([
                    Tables\Columns\TextColumn::make('message')
                        ->label('Informasi')
                        ->getStateUsing(fn() => 'Tidak ada laporan IMUT terbaru.')
                ]);
        }

        return $table
            ->query(fn() => $this->getIncompleteUnitsQuery($laporan->id))
            ->paginated([5, 10, 25, 50])
            ->defaultPaginationPageOption(5)
            ->defaultSort('percentage', 'asc')
            ->striped()
            ->recordClasses(fn($record) => match ($this->getPriorityLevel($record)) {
                'KRITIS' => 'bg-red-50/70 dark:bg-red-900/20',
                'TINGGI' => 'bg-orange-50/60 dark:bg-orange-900/20',
                'SEDANG' => 'bg-amber-50/60 dark:bg-amber-900/10',
                default  => 'bg-white dark:bg-gray-900/40',
            })
            ->columns([
                Tables\Columns\TextColumn::make('unit_name')
                    ->label('Unit Kerja')
                    ->wrap()
                    ->weight('medium')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('completion_status')
                    ->label('Kelengkapan')
                    ->alignCenter()
                    ->state(fn($record) =>
                        number_format($record->filled_count ?? 0) . ' / ' . number_format($record->total_count ?? 0)
                    )
                    ->description(fn($record) =>
                        Number::format($record->percentage ?? 0, 1, locale: app()->getLocale()) . '%'
                    )
                    ->badge()
                    ->color(fn($record) => match (true) {
                        ! is_numeric($record->percentage) => 'gray',
                        $record->percentage >= 80 => 'success',
                        $record->percentage >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(query: fn($query, $direction) =>
                        $query->orderByRaw('(filled_count / NULLIF(total_count, 0)) ' . $direction)
                    ),

                Tables\Columns\TextColumn::make('below_standard_count')
                    ->label('Di Bawah Standar')
                    ->alignCenter()
                    ->state(fn($record) => number_format($record->below_standard_count ?? 0))
                    ->badge()
                    ->color(fn($record) => ($record->below_standard_count ?? 0) > 0 ? 'success' : 'danger')
                    ->icon(fn($record) => ($record->below_standard_count ?? 0) > 0 ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->tooltip('IMUT yang sudah terisi tapi tidak memenuhi standar mutu')
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority_level')
                    ->label('Prioritas')
                    ->alignCenter()
                    ->state(fn($record) => $this->getPriorityLevel($record))
                    ->badge()
                    ->color(fn($record) => $this->getPriorityColor($record))
                    ->icon(fn($record) => $this->getPriorityIcon($record)),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn($record) => UnitKerjaImutDataReport::getUrl([
                        'laporan_id'    => $record->laporan_imut_id,
                        'unit_kerja_id' => $record->unit_kerja_id,
                    ])),
            ])
            ->emptyStateHeading('Semua Unit Sudah Lengkap! 🎉')
            ->emptyStateDescription('Tidak ada unit kerja yang memerlukan perhatian.')
            ->emptyStateIcon('heroicon-o-check-circle');
    }

    protected function getIncompleteUnitsQuery(int $laporanId): Builder
    {
        return LaporanUnitKerja::getReportByUnitKerja($laporanId)
            ->havingRaw('filled_count < total_count AND total_count > 0')
            ->orderByRaw('(filled_count / NULLIF(total_count, 0)) ASC');
    }

    protected function getPriorityLevel($record): string
    {
        $percentage = $record->percentage ?? 0;
        return match (true) {
            $percentage >= 80 => 'RENDAH',
            $percentage >= 50 => 'SEDANG',
            $percentage >= 25 => 'TINGGI',
            default => 'KRITIS',
        };
    }

    protected function getPriorityColor($record): string
    {
        return match ($this->getPriorityLevel($record)) {
            'RENDAH' => 'success',
            'SEDANG' => 'warning',
            'TINGGI' => 'danger',
            'KRITIS' => 'danger',
            default => 'gray',
        };
    }

    protected function getPriorityIcon($record): string
    {
        return match ($this->getPriorityLevel($record)) {
            'RENDAH' => 'heroicon-o-check-badge',
            'SEDANG' => 'heroicon-o-exclamation-triangle',
            'TINGGI' => 'heroicon-o-fire',
            'KRITIS' => 'heroicon-o-shield-exclamation',
            default => 'heroicon-o-question-mark-circle',
        };
    }
}
