<?php

namespace App\Exports;

use App\Exports\Concerns\HasSafeExcelValueBinder;
use App\Modules\FormEngine\Models\FormTemplate;
use App\Support\DailyReportFieldValueFormatter;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithTitle;

class MonitoringExport implements FromCollection, WithHeadings, WithTitle, WithCustomValueBinder, WithStrictNullComparison
{
    use HasSafeExcelValueBinder;

    public function __construct(
        protected FormTemplate $template,
        protected ?Carbon $startDate = null,
        protected ?Carbon $endDate = null,
    ) {}

    public function collection(): Collection
    {
        $data = collect();
        $orderedFields = $this->getOrderedFields();

        foreach ($this->template->dailyReportResponses as $response) {
            $row = [
                $response->report_date?->format('d/m/Y') ?? '',
                $response->unitKerja->unit_name ?? '',
                $response->submittedBy->name ?? '',
                $response->validator->name ?? '',
                DailyReportFieldValueFormatter::formatValidationStatus($response->validation_status),
            ];

            foreach ($orderedFields as $field) {
                $fieldResponse = $response->fieldResponses->firstWhere('form_field_id', $field->id);
                $row[] = $fieldResponse
                    ? DailyReportFieldValueFormatter::formatForExport($field, $fieldResponse->field_value)
                    : '';
            }

            $data->push($row);
        }

        return $data;
    }

    public function headings(): array
    {
        $headings = [
            'Tanggal',
            'Unit Kerja',
            'Pengumpul Data',
            'Validator',
            'Status Validasi',
        ];

        foreach ($this->getOrderedFields() as $field) {
            $headings[] = $field->field_label;
        }

        return $headings;
    }

    public function title(): string
    {
        $rawTitle = $this->template->imutProfile?->imutData?->title ?? $this->template->title;

        return DailyReportFieldValueFormatter::sanitizeSheetTitle($rawTitle);
    }

    protected function getOrderedFields(): Collection
    {
        return $this->template->formFields->sortBy('order_index')->values();
    }
}
