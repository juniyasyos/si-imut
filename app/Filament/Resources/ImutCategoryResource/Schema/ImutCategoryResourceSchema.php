<?php

namespace App\Filament\Resources\ImutCategoryResource\Schema;

use App\Filament\Resources\ImutCategoryResource;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;

class ImutCategoryResourceSchema
{
    public static function make(): array
    {
        return [
            Section::make()->schema([
                Grid::make(2)->schema([
                    TextInput::make('category_name')
                        ->label(__('filament-forms::imut-category.fields.category_name'))
                        ->placeholder(__('filament-forms::imut-category.form.name_placeholder'))
                        ->helperText(__('filament-forms::imut-category.form.helper_text'))
                        ->required()
                        ->columnSpan(1)
                        ->maxLength(100),

                    TextInput::make('short_name')
                        ->label(__('filament-forms::imut-category.fields.short_name'))
                        ->placeholder(__('filament-forms::imut-category.form.short_placeholder'))
                        ->helperText(__('filament-forms::imut-category.form.short_helper_text'))
                        ->unique('imut_kategori', 'short_name', ignoreRecord: true)
                        ->required()
                        ->columnSpan(1)
                        ->maxLength(50),

                    ToggleButtons::make('scope')
                        ->label(__('filament-forms::imut-category.fields.scope'))
                        ->options([
                            'internal' => __('filament-forms::imut-category.fields.scope_internal'),
                            'national' => __('filament-forms::imut-category.fields.scope_national'),
                            'unit' => __('filament-forms::imut-category.fields.scope_unit'),
                            'global' => __('filament-forms::imut-category.fields.scope_global'),
                        ])
                        ->default('internal')
                        ->required()
                        ->inline()
                        ->columnSpan(2)
                        ->colors([
                            'internal' => 'success',
                            'national' => 'warning',
                            'unit' => 'gray',
                            'global' => 'primary',
                        ])
                        ->helperText(__('filament-forms::imut-category.fields.scope_helper_text')),

                    Toggle::make('is_use_global')
                        ->label(__('filament-forms::imut-category.form.is_use_global'))
                        ->helperText(__('filament-forms::imut-category.form.is_use_global_helper'))
                        ->inline(true)
                        ->columnSpan(2)
                        ->onColor('success')
                        ->required()
                        ->default(true)
                        ->columnSpan(1),

                    Toggle::make('is_benchmark_category')
                        ->label(__('filament-forms::imut-category.form.is_benchmark_category'))
                        ->helperText(__('filament-forms::imut-category.form.is_benchmark_category_helper'))
                        ->inline(true)
                        ->columnSpan(2)
                        ->onColor('success')
                        ->required()
                        ->default(true)
                        ->columnSpan(1),

                    Textarea::make('description')
                        ->label(__('filament-forms::imut-category.fields.description'))
                        ->placeholder(__('filament-forms::imut-category.fields.description_placeholder'))
                        ->helperText(__('filament-forms::imut-category.fields.description_helpertext'))
                        ->columnSpanFull(),
                ]),
            ]),
        ];
    }
}

