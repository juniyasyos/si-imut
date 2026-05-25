<?php

namespace App\Filament\Resources\ImutDataResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Models\RegionType;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\{CreateAction, EditAction, DeleteAction, DeleteBulkAction, BulkActionGroup};
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ColorColumn;
use Illuminate\Support\Facades\Auth;

class RegionTypeRelationManager extends RelationManager
{
    protected static string $relationship = 'regionTypes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('type')
                    ->label('Nama Region Type')
                    ->placeholder('Contoh: 🌍 Nasional, 📍 Provinsi')
                    ->required(),

                ColorPicker::make('display_color')
                    ->label('Warna Chart Default')
                    ->placeholder('#3b82f6'),

                Select::make('chart_type')
                    ->label('Tipe Chart Default')
                    ->options(RegionType::getChartTypes())
                    ->default('column'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('type')
                    ->label('Region Type')
                    ->searchable()
                    ->sortable(),

                ColorColumn::make('display_color')
                    ->label('Warna')
                    ->copyable()
                    ->copyMessage('Warna berhasil disalin.')
                    ->copyMessageDuration(1500),

                TextColumn::make('chart_type')
                    ->label('Tipe Chart')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'line' => 'info',
                        'column' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('benchmarkings_count')
                    ->label('Jumlah Benchmark')
                    ->counts('benchmarkings')
                    ->badge()
                    ->color('primary'),
            ])
            ->headerActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Tambah Region Type')
                    ->icon('heroicon-m-plus')
                    ->visible(Auth::user()->can('create_region::type::bencmarking')),
            ])
            ->recordActions([
                \Filament\Actions\EditAction::make()
                    ->visible(Auth::user()->can('update_region::type::bencmarking')),
                \Filament\Actions\DeleteAction::make()
                    ->visible(Auth::user()->can('delete_region::type::bencmarking')),
            ])
            ->toolbarActions([
                \Filament\Actions\BulkActionGroup::make([
                    \Filament\Actions\DeleteBulkAction::make()
                        ->visible(Auth::user()->can('delete_region::type::bencmarking')),
                ]),
            ])
            ->paginated(false);
    }
}
