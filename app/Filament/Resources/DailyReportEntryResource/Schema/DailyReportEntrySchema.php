<?php

namespace App\Filament\Resources\DailyReportEntryResource\Schema;

use App\Filament\Resources\DailyReportEntryResource;
use App\Models\FormTemplate;
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
        $formTemplateId = request()->query('indicator') ?? request()->route('record');

        $formTemplate = $formTemplateId
            ? FormTemplate::with('formFields')->find($formTemplateId)
            : null;

        $schema = [
            static::getInformationSection($formTemplate),
        ];

        // Add dynamic fields if form header exists
        if ($formTemplate && $formTemplate->formFields->isNotEmpty()) {
            $schema[] = static::getDataSection($formTemplate);
        }

        return $schema;
    }

    /**
     * Information section for report metadata
     */
    protected static function getInformationSection(?FormTemplate $formTemplate = null): Section
    {
        // Get indicator from query parameter
        $indicatorId = request()->query('indicator');

        $fields = [];

        // Hidden field for form_template_id (auto-filled from query parameter)
        if ($indicatorId) {
            $fields[] = Select::make('form_template_id')
                ->label('Indikator Mutu')
                ->relationship(
                    'formTemplate',
                    'title',
                    fn($query) => $query->whereHas('imutProfile', function ($q) {
                        $q->where('valid_from', '<=', now())
                            ->where(function ($subQ) {
                                $subQ->whereNull('valid_until')
                                    ->orWhere('valid_until', '>=', now());
                            });
                    })
                        ->whereNotNull('scoring_config')
                )
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
            ->default(function () {
                $dateParam = request()->query('date');
                if ($dateParam) {
                    try {
                        return \Carbon\Carbon::createFromFormat('Y-m-d', $dateParam);
                    } catch (\Exception $e) {
                        return now();
                    }
                }
                return now();
            })
            ->columnSpanFull();

        return Section::make('📋 Informasi Laporan')
            ->description($formTemplate ? "Indikator: {$formTemplate->title}" : 'Isi informasi laporan')
            ->schema($fields)
            ->columns(1)
            ->collapsible()
            ->icon('heroicon-o-information-circle');
    }

    /**
     * Data section with dynamic form fields
     */
    protected static function getDataSection(FormTemplate $formTemplate): Section
    {
        $instance = new static();
        $dynamicFields = $instance->buildFormFields($formTemplate->formFields);

        return Section::make('📝 Data Laporan')
            ->description($formTemplate->description ?? 'Isi data laporan sesuai dengan indikator mutu')
            ->schema($dynamicFields)
            ->columns(2)
            ->collapsible()
            ->icon('heroicon-o-clipboard-document-check');
    }
}
