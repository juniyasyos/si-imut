<?php

namespace App\Filament\Resources\ImutProfileResource\RelationManagers;

use App\Models\FormTemplate;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

class FormTemplatesRelationManager extends RelationManager
{
    protected static string $relationship = 'formTemplates';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),

                Textarea::make('description')
                    ->maxLength(65535),

                Select::make('compliance_method')
                    ->options([
                        'auto_calculate' => 'Auto Calculate',
                        'manual' => 'Manual',
                        'weighted' => 'Weighted',
                    ])
                    ->default('auto_calculate')
                    ->required(),

                Toggle::make('auto_fail_on_critical')
                    ->label('Auto Fail on Critical')
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                TextColumn::make('title')
                    ->searchable(),

                TextColumn::make('compliance_method')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'auto_calculate' => 'success',
                        'manual' => 'warning',
                        'weighted' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('auto_fail_on_critical')
                    ->label('Auto Fail')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}
