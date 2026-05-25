<?php

namespace App\Filament\Resources\ImutDataResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\AttachAction;
use App\Repositories\Interfaces\ImutDataRepositoryInterface;
use Filament\Actions\Action;
use Filament\Actions\DetachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DetachBulkAction;
use App\Filament\Resources\ImutDataResource\Pages\UnitKerjaOverview;
use App\Models\UnitKerja;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class UnitKerjaRelationManager extends RelationManager
{
    protected static string $relationship = 'unitKerja';

    public function form(Schema $schema): Schema
    {
        return $schema;
        // ->schema([
        //     Forms\Components\TextInput::make('unit_name')
        //         ->label('Nama Unit')
        //         ->required()
        //         ->maxLength(255),
        // ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('unit_name')
            ->columns([
                TextColumn::make('unit_name')->label('Nama Unit Kerja')->searchable(),
                TextColumn::make('pivot.assignedBy.name')
                    ->label('Dikaitkan Oleh')
                    ->sortable(),
                TextColumn::make('pivot.assigned_at')
                    ->label('Tanggal Penugasan')
                    ->date()
                    ->sortable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->label('Tambah Unit Kerja')
                    ->color('primary')
                    ->recordSelect(function ($livewire) {
                        $relatedIds = $livewire->ownerRecord->unitKerja()->pluck('id')->toArray();

                        $options = app(ImutDataRepositoryInterface::class)
                            ->getAvailableUnitKerjaOptionsForAttach($livewire->ownerRecord);

                        return Select::make('recordId')
                            ->label('Pilih Unit Kerja')
                            ->placeholder('Cari unit kerja...')
                            ->helperText('Pilih unit kerja yang ingin ditautkan')
                            ->options($options)
                            ->searchable()
                            ->preload()
                            ->required();
                    })
                    ->modalHeading('Tambah Unit Kerja ke Imut Data')
                    ->modalSubmitActionLabel('Simpan')
                    ->preloadRecordSelect()
                    ->action(function (array $data, $livewire) {
                        $imut = $livewire->ownerRecord;

                        app(ImutDataRepositoryInterface::class)
                            ->attachUnitKerjas($imut, [$data['recordId']], auth()->id());
                    })
                    ->attachAnother(false)
                    ->recordSelectSearchColumns(['unit_name']),
            ])
            ->recordActions([
                Action::make('lihat_berdasarkan_unit_kerja')
                    ->label('🏢 Lihat Unit Kerja')
                    ->color('success')
                    ->url(function ($record) {
                        return UnitKerjaOverview::getUrl([
                            'record_imut_data' => $record->imut_data_id,
                            'record_unit_kerja' => $record->unit_kerja_id
                        ]);
                    }),
                DetachAction::make()
                    ->label('Lepas')
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DetachBulkAction::make()->label('Lepas Beberapa'),
                ]),
            ]);
    }
}
