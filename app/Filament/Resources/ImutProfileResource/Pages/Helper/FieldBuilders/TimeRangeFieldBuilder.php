<?php

namespace App\Filament\Resources\ImutProfileResource\Pages\Helper\FieldBuilders;

use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TimePicker;

/**
 * Builder for time range fields
 */
class TimeRangeFieldBuilder
{
    /**
     * Create a time range field
     * 
     * @param string $fieldKey Base field key
     * @param bool $required Whether the field is required
     * @param mixed $visibleCondition Visibility condition
     * @return Grid
     */
    public static function create(
        string $fieldKey,
        bool $required = false,
        $visibleCondition = true
    ): Grid {
        return Grid::make(2)
            ->schema([
                TimePicker::make($fieldKey . '_start_time')
                    ->label('Waktu Mulai')
                    ->required($required)
                    ->seconds(false),

                TimePicker::make($fieldKey . '_end_time')
                    ->label('Waktu Selesai')
                    ->required($required)
                    ->seconds(false),
            ])
            ->visible($visibleCondition)
            ->columnSpanFull();
    }
}
