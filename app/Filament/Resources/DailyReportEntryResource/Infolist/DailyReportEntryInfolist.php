<?php

namespace App\Filament\Resources\DailyReportEntryResource\Infolist;

use App\Filament\Resources\DailyReportEntryResource;
use App\Traits\BuildsDynamicForm;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;

class DailyReportEntryInfolist extends DailyReportEntryResource
{
    use BuildsDynamicForm;

    /**
     * Get the complete infolist schema
     */
    public static function make(): array
    {
        return [
            static::getHeaderSection(),
            static::getInformationSection(),
            static::getDataSection(),
        ];
    }

    /**
     * Header section with key information
     */
    protected static function getHeaderSection(): Section
    {
        return Section::make()
            ->schema([
                Split::make([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('formTemplate.imutdata.title')
                                ->label('Indikator Mutu')
                                ->icon('heroicon-o-clipboard-document-list')
                                ->iconColor('primary')
                                ->weight('bold')
                                ->size('lg')
                                ->columnSpanFull(),

                            TextEntry::make('formTemplate.imutdata.categories.title')
                                ->label('Kategori IMUT')
                                ->badge()
                                ->color('info')
                                ->icon('heroicon-o-tag'),

                            TextEntry::make('report_date')
                                ->label('Tanggal Laporan')
                                ->date('d F Y')
                                ->icon('heroicon-o-calendar')
                                ->iconColor('success')
                                ->badge()
                                ->color('success'),
                        ]),
                ]),
            ]);
    }

    /**
     * Information section with metadata
     */
    protected static function getInformationSection(): Section
    {
        return Section::make('Detail Laporan')
            ->description('Informasi metadata laporan harian')
            ->schema([
                Grid::make(3)
                    ->schema([
                        TextEntry::make('unitKerja.unit_name')
                            ->label('Unit Kerja')
                            ->icon('heroicon-o-building-office')
                            ->iconColor('info')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('submittedBy.name')
                            ->label('Pelapor')
                            ->icon('heroicon-o-user')
                            ->iconColor('warning')
                            ->badge()
                            ->color('warning'),

                        TextEntry::make('entry_time')
                            ->label('Waktu Input')
                            ->time('H:i')
                            ->icon('heroicon-o-clock')
                            ->iconColor('info')
                            ->badge(),

                        TextEntry::make('created_at')
                            ->label('Dibuat pada')
                            ->dateTime('d F Y, H:i')
                            ->icon('heroicon-o-plus-circle')
                            ->iconColor('success'),

                        TextEntry::make('updated_at')
                            ->label('Terakhir diubah')
                            ->dateTime('d F Y, H:i')
                            ->icon('heroicon-o-pencil')
                            ->iconColor('warning')
                            ->placeholder('Belum pernah diubah'),
                    ]),
            ])
            ->icon('heroicon-o-information-circle')
            ->collapsible();
    }

    /**
     * Data section with dynamic form responses
     */
    protected static function getDataSection(): Section
    {
        return Section::make('Data Laporan')
            ->description('Data yang dilaporkan sesuai dengan indikator mutu')
            ->schema(function ($record) {
                if (!$record || !$record->formTemplate) {
                    return [
                        TextEntry::make('info')
                            ->label('')
                            ->default('Tidak ada data form yang tersedia')
                            ->columnSpanFull(),
                    ];
                }

                $fields = [];
                $formFields = $record->formTemplate->formFields()
                    ->orderBy('order')
                    ->get();

                foreach ($formFields as $field) {
                    $fields[] = TextEntry::make("responses.{$field->key}")
                        ->label($field->label)
                        ->formatStateUsing(function ($state) use ($field) {
                            $instance = new static();
                            return $instance->formatFieldValue($state, $field->type);
                        })
                        ->placeholder('-')
                        ->badge(fn() => in_array($field->type, ['bool', 'select', 'radio']))
                        ->color(fn($state) => match ($field->type) {
                            'bool' => $state ? 'success' : 'danger',
                            default => 'gray',
                        })
                        ->icon(fn() => match ($field->type) {
                            'text' => 'heroicon-o-pencil',
                            'textarea' => 'heroicon-o-document-text',
                            'number' => 'heroicon-o-hashtag',
                            'date' => 'heroicon-o-calendar',
                            'bool' => 'heroicon-o-check-circle',
                            'select' => 'heroicon-o-chevron-down',
                            'radio' => 'heroicon-o-check-circle',
                            'checkbox' => 'heroicon-o-check-badge',
                            default => 'heroicon-o-document',
                        })
                        ->columnSpan(fn() => in_array($field->type, ['textarea']) ? 2 : 1);
                }

                return $fields;
            })
            ->columns(2)
            ->icon('heroicon-o-clipboard-document-check')
            ->collapsible();
    }
}
