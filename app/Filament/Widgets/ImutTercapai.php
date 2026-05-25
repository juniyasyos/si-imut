<?php

namespace App\Filament\Widgets;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\Action;
use App\QueryBuilders\UnitKerjaReportQueryBuilder;
use App\Facades\LaporanImut as LaporanImutFacade;
use App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport;
use App\Repositories\Interfaces\LaporanRepositoryInterface;
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

    public function table(Table $table): Table
    {
        $laporan = LaporanImutFacade::getLatestLaporan();

        if (! $laporan) {
            return $table
                ->query(LaporanUnitKerja::query()->whereRaw('1 = 0'))
                ->columns([
                    TextColumn::make('message')
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
                TextColumn::make('unit_name')
                    ->label('Unit Kerja')
                    ->wrap()
                    ->weight('medium')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('completion_status')
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
                    ->sortable(query: function($query, $direction) {
                        $filledCountExpr = "SUM(CASE WHEN imut_penilaians.numerator_value IS NOT NULL AND imut_penilaians.denominator_value IS NOT NULL AND imut_penilaians.denominator_value != 0 THEN 1 ELSE 0 END)";
                        return $query->orderByRaw("({$filledCountExpr} / NULLIF(COUNT(imut_penilaians.id), 0)) " . $direction);
                    }),

                TextColumn::make('below_standard_count')
                    ->label('Di Bawah Standar')
                    ->alignCenter()
                    ->state(fn($record) => number_format($record->below_standard_count ?? 0))
                    ->badge()
                    ->color(fn($record) => ($record->below_standard_count ?? 0) > 0 ? 'success' : 'danger')
                    ->icon(fn($record) => ($record->below_standard_count ?? 0) > 0 ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->tooltip('IMUT yang sudah terisi tapi tidak memenuhi standar mutu')
                    ->sortable(),

                TextColumn::make('priority_level')
                    ->label('Prioritas')
                    ->alignCenter()
                    ->state(fn($record) => $this->getPriorityLevel($record))
                    ->badge()
                    ->color(fn($record) => $this->getPriorityColor($record))
                    ->icon(fn($record) => $this->getPriorityIcon($record)),
            ])
            ->recordActions([
                Action::make('view_details')
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
        $laporanRepository = app(LaporanRepositoryInterface::class);
        $filledCountExpr = UnitKerjaReportQueryBuilder::getFilledCountExpression();

        return $laporanRepository->getReportByUnitKerja($laporanId)
            ->havingRaw("{$filledCountExpr} < COUNT(imut_penilaians.id) AND COUNT(imut_penilaians.id) > 0")
            ->orderByRaw("({$filledCountExpr} / NULLIF(COUNT(imut_penilaians.id), 0)) ASC");
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
