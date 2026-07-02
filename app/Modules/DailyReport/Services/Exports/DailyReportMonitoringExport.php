<?php

namespace App\Modules\DailyReport\Services\Exports;

use App\Models\FormTemplate;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * Export class for Daily Report Monitoring data to Excel
 */
class DailyReportMonitoringExport implements FromCollection, WithHeadings, WithTitle
{
    private FormTemplate $template;

    public function __construct(FormTemplate $template)
    {
        $this->template = $template;
    }

    public function collection()
    {
        $data = collect();

        foreach ($this->template->dailyReportResponses as $response) {
            $row = [
                'Tanggal' => $response->report_date->format('d/m/Y'),
                'Unit Kerja' => $response->unitKerja->unit_name ?? '',
                'Pengumpul Data' => $response->submittedBy->name ?? '',
                'Validator' => $response->validator->name ?? '',
                'Status Validasi' => $response->is_validated ? 'Tervalidasi' : 'Belum Divalidasi',
            ];

            // Add field responses
            foreach ($this->template->formFields as $field) {
                $fieldResponse = $response->fieldResponses
                    ->where('form_field_id', $field->id)
                    ->first();
                
                $row[$field->field_label] = $this->formatFieldValue($field, $fieldResponse);
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
            'Status Validasi'
        ];

        // Add field headings
        foreach ($this->template->formFields as $field) {
            $headings[] = $field->field_label;
        }

        return $headings;
    }

    public function title(): string
    {
        return substr($this->template->imutProfile->imutData->title, 0, 31);
    }

    /**
     * Format field value based on field type
     */
    private function formatFieldValue($field, $fieldResponse): string
    {
        if (!$fieldResponse) {
            return '';
        }

        $fieldValue = $fieldResponse->field_value;

        return match ($field->field_type) {
            'boolean' => ($fieldValue == 1 || $fieldValue === true || $fieldValue === '1') ? 'Ya' : 'Tidak',
            'single_select' => $this->formatSelectValue($field, $fieldValue),
            'multi_select' => $this->formatMultiSelectValue($field, $fieldValue),
            'time_duration' => $this->formatTimeDuration($fieldValue),
            'time_range' => $this->formatTimeRange($fieldValue),
            'number' => is_numeric($fieldValue) ? number_format($fieldValue, 0, ',', '.') : $fieldValue,
            'date' => $this->formatDate($fieldValue),
            default => is_array($fieldValue) ? json_encode($fieldValue) : (string)$fieldValue,
        };
    }

    /**
     * Format single select field value
     */
    private function formatSelectValue($field, $fieldValue): string
    {
        $option = $field->options->firstWhere('option_value', $fieldValue);
        return $option ? $option->option_text : (string)$fieldValue;
    }

    /**
     * Format multi select field value
     */
    private function formatMultiSelectValue($field, $fieldValue): string
    {
        if (!is_array($fieldValue)) {
            return $this->formatSelectValue($field, $fieldValue);
        }

        $selectedOptions = collect($fieldValue)
            ->map(fn($value) => $field->options->firstWhere('option_value', $value)?->option_text)
            ->filter()
            ->toArray();

        return implode(', ', $selectedOptions);
    }

    /**
     * Format time duration field value
     */
    private function formatTimeDuration($fieldValue): string
    {
        if (!is_array($fieldValue)) {
            return (string)$fieldValue;
        }

        if (isset($fieldValue['duration'])) {
            return $fieldValue['duration'] . ' menit';
        }

        return json_encode($fieldValue);
    }

    /**
     * Format time range field value
     */
    private function formatTimeRange($fieldValue): string
    {
        if (!is_array($fieldValue)) {
            return (string)$fieldValue;
        }

        if (isset($fieldValue['start_time']) && isset($fieldValue['end_time'])) {
            return $fieldValue['start_time'] . ' - ' . $fieldValue['end_time'];
        }

        return json_encode($fieldValue);
    }

    /**
     * Format date field value
     */
    private function formatDate($fieldValue): string
    {
        if ($fieldValue && strtotime($fieldValue)) {
            return date('d/m/Y', strtotime($fieldValue));
        }

        return (string)$fieldValue;
    }
}
