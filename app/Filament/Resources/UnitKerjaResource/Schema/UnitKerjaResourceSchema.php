<?php

namespace App\Filament\Resources\UnitKerjaResource\Schema;

use App\Filament\Resources\UnitKerjaResource;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;

class UnitKerjaResourceSchema
{
    public static function make(): array
    {
        return [
            Section::make(__('filament-forms::unit-kerja.form.unit.title'))
                ->description(__('filament-forms::unit-kerja.form.unit.description'))
                ->schema([
                    TextInput::make('unit_name')
                        ->label(__('filament-forms::unit-kerja.fields.unit_name'))
                        ->placeholder(__('filament-forms::unit-kerja.form.unit.name_placeholder'))
                        ->helperText(__('filament-forms::unit-kerja.form.unit.helper_text'))
                        ->required()
                        ->unique('unit_kerja', 'unit_name', ignoreRecord: true)
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Textarea::make('description')
                        ->label(__('filament-forms::unit-kerja.fields.description'))
                        ->placeholder(__('filament-forms::unit-kerja.form.unit.description_placeholder'))
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ];
    }
}

