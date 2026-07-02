<?php

namespace App\Exports;

use App\Models\FormTemplate;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class MonitoringExport implements FromCollection, WithHeadings, WithTitle
{
    public function __construct(
        protected FormTemplate $template,
        protected Carbon $startDate,
        protected Carbon $endDate,
    ) {}

    public function collection(): Collection
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
                $fieldResponse = $response->fieldResponses->where('form_field_id', $field->id)->first();
                $value = '';

                if ($fieldResponse) {
                    $fieldValue = $fieldResponse->field_value;

                    // Format value based on field type
                    switch ($field->field_type) {
                        case 'boolean':
                            $value = ($fieldValue == 1 || $fieldValue === true || $fieldValue === '1') ? 'Ya' : 'Tidak';
                            break;

                        case 'single_select':
                        case 'multi_select':
                            if (is_array($fieldValue)) {
                                $selectedOptions = [];
                                foreach ($fieldValue as $optionValue) {
                                    $option = $field->options->firstWhere('option_value', $optionValue);
                                    if ($option) {
                                        $selectedOptions[] = $option->option_text;
                                    }
                                }
                                $value = implode(', ', $selectedOptions);
                            } else {
                                $option = $field->options->firstWhere('option_value', $fieldValue);
                                $value = $option ? $option->option_text : $fieldValue;
                            }
                            break;

                        case 'time_duration':
                        case 'time_range':
                            if (is_array($fieldValue)) {
                                if (isset($fieldValue['start_time']) && isset($fieldValue['end_time'])) {
                                    $value = $fieldValue['start_time'] . ' - ' . $fieldValue['end_time'];
                                } elseif (isset($fieldValue['duration'])) {
                                    $value = $fieldValue['duration'] . ' menit';
                                } else {
                                    $value = json_encode($fieldValue);
                                }
                            } else {
                                $value = $fieldValue;
                            }
                            break;

                        case 'number':
                            $value = is_numeric($fieldValue) ? number_format($fieldValue, 0, ',', '.') : $fieldValue;
                            break;

                        case 'date':
                            if ($fieldValue && strtotime($fieldValue)) {
                                $value = date('d/m/Y', strtotime($fieldValue));
                            } else {
                                $value = $fieldValue;
                            }
                            break;

                        default:
                            if (is_array($fieldValue)) {
                                $value = json_encode($fieldValue);
                            } else {
                                $value = $fieldValue;
                            }
                            break;
                    }
                }

                $row[$field->field_label] = $value;
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

        foreach ($this->template->formFields as $field) {
            $headings[] = $field->field_label;
        }

        return $headings;
    }

    public function title(): string
    {
        return substr($this->template->imutProfile->imutData->title, 0, 31);
    }
}
