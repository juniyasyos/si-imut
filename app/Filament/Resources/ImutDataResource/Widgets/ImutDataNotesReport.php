<?php

namespace App\Filament\Resources\ImutDataResource\Widgets;

use App\Models\ImutDataNote;
use App\Models\LaporanImut;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Grid;
use Filament\Forms\Get;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ImutDataNotesReport extends BaseWidget
{
    public ?int $imutDataId = null;

    protected int | string | array $columnSpan = 'full';

    protected function getLaporanOptions(Get $get): array
    {
        $year = $get('period_year');
        $quarter = $get('period_quarter');
        $periodType = $get('period_type');

        if (!$year) {
            return LaporanImut::orderBy('name', 'desc')
                ->pluck('name', 'id')
                ->toArray();
        }

        $query = LaporanImut::query();

        if ($periodType === 'tahunan') {
            // Untuk tahunan, ambil laporan yang periode assessmentnya di tahun tersebut
            $query->whereYear('assessment_period_start', $year)
                  ->orWhereYear('assessment_period_end', $year);
        } elseif ($periodType === 'triwulan' && $quarter) {
            // Mapping triwulan ke bulan
            $quarterMonths = [
                'Q1' => [1, 2, 3],    // Jan-Mar
                'Q2' => [4, 5, 6],    // Apr-Jun
                'Q3' => [7, 8, 9],    // Jul-Sep
                'Q4' => [10, 11, 12], // Oct-Des
            ];

            $months = $quarterMonths[$quarter] ?? [];

            if (!empty($months)) {
                // Ambil laporan yang assessment period-nya overlap dengan triwulan
                $query->where(function ($q) use ($year, $months) {
                    foreach ($months as $month) {
                        $q->orWhere(function ($subQuery) use ($year, $month) {
                            $subQuery->whereYear('assessment_period_start', $year)
                                     ->whereMonth('assessment_period_start', $month);
                        })->orWhere(function ($subQuery) use ($year, $month) {
                            $subQuery->whereYear('assessment_period_end', $year)
                                     ->whereMonth('assessment_period_end', $month);
                        });
                    }
                });
            }
        }

        return $query->orderBy('name', 'desc')
            ->pluck('name', 'id')
            ->toArray();
    }

    protected function autoSelectLaporan($year, $quarter, $periodType, callable $set): void
    {
        if (!$year) {
            return;
        }

        $query = LaporanImut::query();

        if ($periodType === 'tahunan') {
            // Untuk tahunan, ambil laporan yang periode assessmentnya di tahun tersebut
            $query->whereYear('assessment_period_start', $year)
                  ->orWhereYear('assessment_period_end', $year);
        } elseif ($periodType === 'triwulan' && $quarter) {
            // Mapping triwulan ke bulan
            $quarterMonths = [
                'Q1' => [1, 2, 3],    // Jan-Mar
                'Q2' => [4, 5, 6],    // Apr-Jun
                'Q3' => [7, 8, 9],    // Jul-Sep
                'Q4' => [10, 11, 12], // Oct-Des
            ];

            $months = $quarterMonths[$quarter] ?? [];

            if (!empty($months)) {
                // Ambil laporan yang assessment period-nya overlap dengan triwulan
                $query->where(function ($q) use ($year, $months) {
                    foreach ($months as $month) {
                        $q->orWhere(function ($subQuery) use ($year, $month) {
                            $subQuery->whereYear('assessment_period_start', $year)
                                     ->whereMonth('assessment_period_start', $month);
                        })->orWhere(function ($subQuery) use ($year, $month) {
                            $subQuery->whereYear('assessment_period_end', $year)
                                     ->whereMonth('assessment_period_end', $month);
                        });
                    }
                });
            }
        }

        $laporanIds = $query->pluck('id')->toArray();

        // Auto-select laporan yang sesuai
        if (!empty($laporanIds)) {
            $set('related_laporan_ids', $laporanIds);
        }
    }

    protected function getFormSchema(): array
    {
        return [
            TextInput::make('note_name')
                ->label('Nama Catatan')
                ->required()
                ->maxLength(255)
                ->placeholder('Masukkan nama catatan'),

            Grid::make(3)
                ->schema([
                    Select::make('period_type')
                        ->label('Tipe Periode')
                        ->options([
                            'tahunan' => 'Tahunan',
                            'triwulan' => 'Triwulan',
                        ])
                        ->default('tahunan')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, Get $get) {
                            if ($state === 'tahunan') {
                                $set('period_quarter', null);
                            }
                            $this->autoSelectLaporan($get('period_year'), $get('period_quarter'), $state, $set);
                        }),

                    Select::make('period_year')
                        ->label('Tahun')
                        ->options(function () {
                            $currentYear = date('Y');
                            $years = [];
                            for ($i = $currentYear - 5; $i <= $currentYear + 2; $i++) {
                                $years[$i] = $i;
                            }
                            return $years;
                        })
                        ->default(date('Y'))
                        ->searchable()
                        ->reactive()
                        ->placeholder('Pilih tahun')
                        ->afterStateUpdated(function ($state, callable $set, Get $get) {
                            $this->autoSelectLaporan($state, $get('period_quarter'), $get('period_type'), $set);
                        }),

                    Select::make('period_quarter')
                        ->label('Triwulan')
                        ->options([
                            'Q1' => 'Q1 (Jan-Mar)',
                            'Q2' => 'Q2 (Apr-Jun)',
                            'Q3' => 'Q3 (Jul-Sep)',
                            'Q4' => 'Q4 (Oct-Des)',
                        ])
                        ->placeholder('Pilih triwulan')
                        ->reactive()
                        ->visible(fn (Get $get) => $get('period_type') === 'triwulan')
                        ->required(fn (Get $get) => $get('period_type') === 'triwulan')
                        ->afterStateUpdated(function ($state, callable $set, Get $get) {
                            $this->autoSelectLaporan($get('period_year'), $state, $get('period_type'), $set);
                        }),
                ]),

            Select::make('related_laporan_ids')
                ->label('Laporan Terkait')
                ->multiple()
                ->searchable()
                ->preload()
                ->options(fn (Get $get) => $this->getLaporanOptions($get))
                ->placeholder('Pilih laporan yang terkait')
                ->helperText(function (Get $get) {
                    $year = $get('period_year');
                    $quarter = $get('period_quarter');
                    $periodType = $get('period_type');

                    if (!$year) {
                        return 'Pilih tahun untuk auto-select laporan terkait';
                    }

                    if ($periodType === 'triwulan' && !$quarter) {
                        return 'Pilih triwulan untuk auto-select laporan terkait';
                    }

                    $count = count($this->getLaporanOptions($get));
                    return "✓ {$count} laporan otomatis dipilih berdasarkan periode";
                }),

            Textarea::make('recommendation')
                ->label('Rekomendasi')
                ->rows(3)
                ->maxLength(65535)
                ->placeholder('Masukkan rekomendasi'),

            Textarea::make('analysis')
                ->label('Analisis')
                ->rows(3)
                ->maxLength(65535)
                ->placeholder('Masukkan analisis'),

            Textarea::make('additional_notes')
                ->label('Catatan Tambahan')
                ->rows(3)
                ->maxLength(65535)
                ->placeholder('Masukkan catatan tambahan lainnya'),

            Select::make('priority')
                ->label('Prioritas')
                ->options([
                    'low' => 'Rendah',
                    'medium' => 'Sedang',
                    'high' => 'Tinggi',
                ])
                ->default('medium')
                ->required(),

            Toggle::make('is_active')
                ->label('Status Aktif')
                ->default(true),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ImutDataNote::query()
                    ->where('imut_data_id', $this->imutDataId)
                    ->with(['creator'])
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                TextColumn::make('note_name')
                    ->label('Nama Catatan')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->limit(50),

                TextColumn::make('period')
                    ->label('Periode')
                    ->getStateUsing(function (ImutDataNote $record) {
                        return $record->period_display;
                    })
                    ->sortable(['period_year', 'period_quarter'])
                    ->searchable(),

                TextColumn::make('laporan_names')
                    ->label('Laporan Terkait')
                    ->wrap()
                    ->grow()
                    ->tooltip(fn (ImutDataNote $record): string => $record->laporan_names),

                TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'success',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'high' => 'Tinggi',
                        'medium' => 'Sedang',
                        'low' => 'Rendah',
                    })
                    ->sortable(),

                IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),

                TextColumn::make('creator.name')
                    ->label('Dibuat Oleh')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('period_year')
                    ->label('Tahun')
                    ->options(function () {
                        return ImutDataNote::query()
                            ->distinct()
                            ->whereNotNull('period_year')
                            ->orderBy('period_year', 'desc')
                            ->pluck('period_year', 'period_year')
                            ->toArray();
                    })
                    ->placeholder('Semua Tahun'),

                SelectFilter::make('period_type')
                    ->label('Tipe Periode')
                    ->options([
                        'tahunan' => 'Tahunan',
                        'triwulan' => 'Triwulan',
                    ])
                    ->placeholder('Semua Tipe'),

                SelectFilter::make('period_quarter')
                    ->label('Triwulan')
                    ->options([
                        'Q1' => 'Q1 (Jan-Mar)',
                        'Q2' => 'Q2 (Apr-Jun)',
                        'Q3' => 'Q3 (Jul-Sep)',
                        'Q4' => 'Q4 (Oct-Des)',
                    ])
                    ->placeholder('Semua Triwulan'),

                SelectFilter::make('priority')
                    ->label('Prioritas')
                    ->options([
                        'high' => 'Tinggi',
                        'medium' => 'Sedang',
                        'low' => 'Rendah',
                    ]),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif',
                    ]),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Catatan')
                    ->icon('heroicon-o-plus')
                    ->modalHeading('Tambah Catatan Baru')
                    ->form($this->getFormSchema())
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['imut_data_id'] = $this->imutDataId;
                        $data['created_by'] = Auth::id();
                        return $data;
                    })
                    ->successNotificationTitle('Catatan berhasil ditambahkan'),
            ])
            ->actions([
                Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Detail Catatan')
                    ->modalContent(fn (ImutDataNote $record) => view(
                        'filament.resources.imut-data-resource.widgets.note-detail',
                        ['note' => $record]
                    ))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup'),

                EditAction::make()
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->modalHeading('Edit Catatan')
                    ->form($this->getFormSchema())
                    ->successNotificationTitle('Catatan berhasil diperbarui'),

                DeleteAction::make()
                    ->label('Hapus')
                    ->icon('heroicon-o-trash')
                    ->successNotificationTitle('Catatan berhasil dihapus')
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('Belum Ada Catatan')
            ->emptyStateDescription('Tambahkan catatan untuk data IMUT ini')
            ->emptyStateIcon('heroicon-o-document-text');
    }
}
