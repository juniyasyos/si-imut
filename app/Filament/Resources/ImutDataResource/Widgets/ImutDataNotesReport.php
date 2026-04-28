<?php

namespace App\Filament\Resources\ImutDataResource\Widgets;

use App\Models\ImutDataNote;
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
use Illuminate\Support\Facades\Auth;

class ImutDataNotesReport extends BaseWidget
{
    public ?int $imutDataId = null;

    protected int | string | array $columnSpan = 'full';

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
                            'semester' => 'Semester',
                            'triwulan' => 'Triwulan',
                        ])
                        ->default('tahunan')
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $set, Get $get) {
                            if ($state === 'tahunan') {
                                $set('period_quarter', null);
                                $set('period_semester', null);
                            } elseif ($state === 'semester') {
                                $set('period_quarter', null);
                            } elseif ($state === 'triwulan') {
                                $set('period_semester', null);
                            }
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
                        ->placeholder('Pilih tahun'),

                    Select::make('period_semester')
                        ->label('Semester')
                        ->options([
                            'S1' => 'S1 (Jan-Jun)',
                            'S2' => 'S2 (Jul-Des)',
                        ])
                        ->placeholder('Pilih semester')
                        ->reactive()
                        ->visible(fn(Get $get) => $get('period_type') === 'semester')
                        ->required(fn(Get $get) => $get('period_type') === 'semester'),

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
                        ->visible(fn(Get $get) => $get('period_type') === 'triwulan')
                        ->required(fn(Get $get) => $get('period_type') === 'triwulan'),
                ]),

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

                TextColumn::make('priority')
                    ->label('Prioritas')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'success',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
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
                        'semester' => 'Semester',
                        'triwulan' => 'Triwulan',
                    ])
                    ->placeholder('Semua Tipe'),

                SelectFilter::make('period_semester')
                    ->label('Semester')
                    ->options([
                        'S1' => 'S1 (Jan-Jun)',
                        'S2' => 'S2 (Jul-Des)',
                    ])
                    ->placeholder('Semua Semester'),

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
                    ->modalContent(fn(ImutDataNote $record) => view(
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
