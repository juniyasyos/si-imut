<?php

namespace App\Filament\Resources\Examples;

use App\Filament\Resources\LaporanImutResource\Table\LaporanImutTable;
use App\Filament\Resources\LaporanImutResource\Form\LaporanImutForm;
use App\Filament\Resources\LaporanImutResource\Pages;
use App\Models\LaporanImut;
use App\Traits\Filament\UsesBusinessLogic;
use App\Adapters\Filament\LaporanImutFilamentAdapter;
use Filament\Resources\Resource;

/**
 * Example LaporanImut Resource using Business Logic Abstraction
 *
 * This shows how to refactor existing Filament resources to use our abstraction layer
 */
class RefactoredLaporanImutResource extends Resource
{
    use UsesBusinessLogic;

    protected static ?string $model = LaporanImut::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $label = 'Laporan IMUT';
    protected static ?string $pluralLabel = 'Laporan IMUT';

    /**
     * Get the business adapter class for this resource
     */
    protected function getBusinessAdapterClass(): string
    {
        return LaporanImutFilamentAdapter::class;
    }

    /**
     * Define the form schema using business logic
     */
    public static function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form->schema([
            // Use existing form components or create new ones
            // The business logic will handle validation and processing
            \Filament\Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),

            \Filament\Forms\Components\Select::make('status')
                ->required()
                ->options([
                    'process' => 'Berlangsung',
                    'complete' => 'Selesai',
                    'coming_soon' => 'Akan Datang',
                ]),

            \Filament\Forms\Components\DatePicker::make('assessment_period_start')
                ->required()
                ->label('Periode Mulai'),

            \Filament\Forms\Components\DatePicker::make('assessment_period_end')
                ->required()
                ->label('Periode Selesai'),

            \Filament\Forms\Components\Select::make('unit_kerja_ids')
                ->multiple()
                ->relationship('unitKerjas', 'name')
                ->label('Unit Kerja'),

            \Filament\Forms\Components\Textarea::make('description')
                ->maxLength(1000)
                ->label('Deskripsi'),
        ]);
    }

    /**
     * Define the table using business logic
     */
    public static function table(\Filament\Tables\Table $table): \Filament\Tables\Table
    {
        return $table
            ->query(
                // Instead of direct model query, we could use business logic
                // But for Filament compatibility, we still use Eloquent Builder
                static::getModel()::query()
            )
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label('Nama'),

                \Filament\Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'process',
                        'success' => 'complete',
                        'secondary' => 'coming_soon',
                    ])
                    ->labels([
                        'process' => 'Berlangsung',
                        'complete' => 'Selesai',
                        'coming_soon' => 'Akan Datang',
                    ])
                    ->label('Status'),

                \Filament\Tables\Columns\TextColumn::make('assessment_period_start')
                    ->date()
                    ->sortable()
                    ->label('Periode Mulai'),

                \Filament\Tables\Columns\TextColumn::make('assessment_period_end')
                    ->date()
                    ->sortable()
                    ->label('Periode Selesai'),

                \Filament\Tables\Columns\TextColumn::make('unitKerjas_count')
                    ->counts('unitKerjas')
                    ->label('Jumlah Unit Kerja'),

                // Custom column using business logic
                \Filament\Tables\Columns\TextColumn::make('achievement_rate')
                    ->label('Tingkat Pencapaian')
                    ->getStateUsing(function ($record) {
                        $adapter = app(LaporanImutFilamentAdapter::class);
                        $stats = $adapter->getTableStatistics(['record' => $record]);
                        return ($stats['achievementRate'] ?? 0) . '%';
                    })
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        str_replace('%', '', $state) >= 80 => 'success',
                        str_replace('%', '', $state) >= 60 => 'warning',
                        default => 'danger',
                    }),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'process' => 'Berlangsung',
                        'complete' => 'Selesai',
                        'coming_soon' => 'Akan Datang',
                    ]),
            ])
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    /**
     * Override table query to use business logic filters
     */
    protected function getTableFilters(): array
    {
        $filters = [];

        // Extract filters from Filament's filter state
        $filterState = $this->tableFilters ?? [];

        foreach ($filterState as $name => $value) {
            if (!empty($value)) {
                $filters[] = [
                    'field' => $name,
                    'value' => $value,
                    'operator' => '='
                ];
            }
        }

        return $filters;
    }

    /**
     * Get the pages for this resource
     */
    public static function getPages(): array
    {
        return [
            'index' => \Filament\Resources\Pages\ListRecords::route('/'),
            'create' => \Filament\Resources\Pages\CreateRecord::route('/create'),
            'edit' => \Filament\Resources\Pages\EditRecord::route('/{record}/edit'),
        ];
    }
}
