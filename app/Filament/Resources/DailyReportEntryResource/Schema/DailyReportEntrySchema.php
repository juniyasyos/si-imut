<?php

namespace App\Filament\Resources\DailyReportEntryResource\Schema;

use App\Filament\Resources\DailyReportEntryResource;
use App\Models\FormHeader;
use App\Traits\BuildsDynamicForm;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;

class DailyReportEntrySchema extends DailyReportEntryResource
{
    use BuildsDynamicForm;

    /**
     * Get the complete form schema for Daily Report Entry
     */
    public static function make(): array
    {
        $formHeaderId = request()->query('indicator') ?? request()->route('record');

        $formHeader = $formHeaderId
            ? FormHeader::with('formFields')->find($formHeaderId)
            : null;

        $schema = [
            static::getInformationSection($formHeader),
        ];

        // Add dynamic fields if form header exists
        if ($formHeader && $formHeader->formFields->isNotEmpty()) {
            $schema[] = static::getDataSection($formHeader);
        }

        return $schema;
    }

    /**
     * Information section for report metadata
     */
    protected static function getInformationSection(?FormHeader $formHeader = null): Section
    {
        // Get indicator from query parameter
        $indicatorId = request()->query('indicator');
        
        $fields = [];
        
        // Hidden field for form_header_id (auto-filled from query parameter)
        if ($indicatorId) {
            $fields[] = Select::make('form_header_id')
                ->label('Indikator Mutu')
                ->relationship('formHeader', 'title')
                ->required()
                ->default($indicatorId)
                ->disabled()
                ->dehydrated()
                ->hidden();
        }
        
        $fields[] = DatePicker::make('report_date')
            ->label('Tanggal Laporan')
            ->required()
            ->native(false)
            ->displayFormat('d/m/Y')
            ->maxDate(now())
            ->minDate(now()->subDays(6))
            ->helperText('💡 Data dapat diinput maksimal 6 hari yang lalu')
            ->default(now())
            ->columnSpanFull();

        return Section::make('📋 Informasi Laporan')
            ->description($formHeader ? "Indikator: {$formHeader->title}" : 'Isi informasi laporan')
            ->schema($fields)
            ->columns(1)
            ->collapsible()
            ->icon('heroicon-o-information-circle');
    }

    /**
     * Data section with dynamic form fields
     */
    protected static function getDataSection(FormHeader $formHeader): Section
    {
        $instance = new static();
        $dynamicFields = $instance->buildFormFields($formHeader->formFields);

        return Section::make('📝 Data Laporan')
            ->description($formHeader->description ?? 'Isi data laporan sesuai dengan indikator mutu')
            ->schema($dynamicFields)
            ->columns(2)
            ->collapsible()
            ->icon('heroicon-o-clipboard-document-check');
    }
}
