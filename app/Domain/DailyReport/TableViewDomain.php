<?php

namespace App\Domain\DailyReport;

use Exception;
use App\Models\FormTemplate;
use App\Models\UnitKerja;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class TableViewDomain
{
    public function buildTableConfig(FormTemplate $formTemplate, ?Collection $entries = null): array
    {
        $headers = [];
        $fieldCounter = 0;

        $headers[] = [
            'key' => 'report_date',
            'label' => 'Tanggal',
            'align' => 'center',
            'bgColor' => 'bg-blue-700',
            'format' => 'date',
            'width' => '100px',
        ];

        $formFields = $formTemplate->formFields()
            ->with(['options' => function ($q) {
                $q->orderBy('order_index', 'ASC');
            }])
            ->orderBy('enhanced_form_fields.order_index', 'ASC')
            ->get();

        foreach ($formFields as $field) {
            $fieldType = $field->field_type;
            $options = $field->options;

            if ($this->isMultiColumnFieldType($fieldType) && $options->count() > 0) {
                $fieldLetter = chr(65 + $fieldCounter);
                $children = [];

                foreach ($options as $index => $option) {
                    $children[] = [
                        'key' => $field->field_key . '_' . $option->option_value,
                        'label' => $fieldLetter . ($index + 1),
                        'full_label' => $option->option_text,
                        'align' => 'center',
                        'bgColor' => 'bg-blue-600',
                        'format' => 'field_code',
                        'width' => '60px',
                        'option_code' => $fieldLetter . ($index + 1),
                        'option_value' => $option->option_value,
                    ];
                }

                $headers[] = [
                    'label' => $field->field_label,
                    'bgColor' => 'bg-blue-800',
                    'children' => $children,
                    'field_type' => 'multi_select_group',
                    'field_key' => $field->field_key,
                ];
                $fieldCounter++;
            } elseif ($fieldType === 'time_duration') {
                $headers[] = [
                    'label' => $field->field_label . ' (Jam:Menit)',
                    'bgColor' => 'bg-blue-800',
                    'children' => [
                        ['key' => $field->field_key . '_start', 'label' => 'Mulai', 'align' => 'center', 'bgColor' => 'bg-blue-600', 'width' => '100px'],
                        ['key' => $field->field_key . '_end', 'label' => 'Selesai', 'align' => 'center', 'bgColor' => 'bg-blue-600', 'width' => '100px'],
                        ['key' => $field->field_key . '_duration', 'label' => 'Selisih', 'align' => 'center', 'bgColor' => 'bg-blue-600', 'width' => '100px'],
                        ['key' => $field->field_key . '_valid', 'label' => 'Sesuai', 'align' => 'center', 'bgColor' => 'bg-blue-600', 'format' => 'checkbox', 'width' => '80px'],
                    ],
                ];
            } elseif ($fieldType === 'time_range') {
                $boundaryLabel = '(Jam Kerja)';

                if ($entries && $entries->count() > 0) {
                    $firstEntry = $entries->first();
                    $firstResponse = $firstEntry->fieldResponses->firstWhere('form_field_id', $field->id);
                    if ($firstResponse && is_array($firstResponse->field_value)) {
                        $startTime = $firstResponse->field_value['start_time'] ?? null;
                        $endTime = $firstResponse->field_value['end_time'] ?? null;

                        if ($startTime && $endTime) {
                            $boundaryLabel = "({$startTime} - {$endTime})";
                        }
                    }
                }

                $headers[] = [
                    'label' => $field->field_label . ' ' . $boundaryLabel,
                    'bgColor' => 'bg-blue-800',
                    'children' => [
                        ['key' => $field->field_key . '_input_value', 'label' => 'Waktu', 'align' => 'center', 'bgColor' => 'bg-blue-600', 'width' => '100px'],
                        ['key' => $field->field_key . '_valid_indicator', 'label' => 'Sesuai', 'align' => 'center', 'bgColor' => 'bg-blue-600', 'format' => 'checkbox', 'width' => '100px'],
                    ],
                ];
            } else {
                $headers[] = [
                    'key' => $field->field_key,
                    'label' => $field->field_label,
                    'align' => $this->getFieldAlignment($fieldType),
                    'bgColor' => 'bg-blue-700',
                    'format' => $this->getFieldFormat($fieldType),
                    'width' => $this->getFieldWidth($fieldType),
                ];
            }
        }

        $headers[] = ['key' => 'submitted_by_name', 'label' => 'Pengumpul Data', 'align' => 'left', 'bgColor' => 'bg-blue-700', 'width' => '150px'];
        $headers[] = ['key' => 'validation_status', 'label' => 'Status Kepatuhan', 'align' => 'center', 'bgColor' => 'bg-green-700', 'format' => 'checkbox', 'width' => '120px'];
        $headers[] = ['key' => 'is_validated', 'label' => 'Tervalidasi', 'align' => 'center', 'bgColor' => 'bg-yellow-700', 'width' => '130px'];
        $headers[] = ['key' => 'validated_by_name', 'label' => 'Validator', 'align' => 'left', 'bgColor' => 'bg-yellow-700', 'width' => '150px'];

        return [
            'headers' => $headers,
            'legend' => $this->buildLegend($formTemplate),
            'encoding_rules' => [1 => 'Dipilih', 0 => 'Tidak dipilih'],
        ];
    }

    public function transformEntriesToTableData(Collection $entries, FormTemplate $formTemplate): array
    {
        $tableData = [];
        $formFields = $formTemplate->formFields()
            ->with(['options' => function ($q) {
                $q->orderBy('order_index', 'ASC');
            }])
            ->orderBy('enhanced_form_fields.order_index', 'ASC')
            ->get();

        foreach ($entries as $index => $entry) {
            $row = [
                'no' => $index + 1,
                'form_template_id' => $entry->form_template_id,
                'form_template_title' => $entry->formTemplate?->title ?? '-',
                'form_template_is_active' => $entry->formTemplate?->is_active ?? false,
                'report_date' => $entry->report_date->format('Y-m-d'),
                'submitted_by_name' => $entry->submittedBy?->name ?? '-',
                'is_validated' => $entry->validation_status === 'valid' ? '✓' : ($entry->validation_status === 'invalid' ? '✗' : '—'),
                'validated_by_name' => $entry->validator?->name ?? '-',
            ];

            $responses = [];
            foreach ($entry->fieldResponses as $fieldResponse) {
                $fieldKey = $fieldResponse->formField?->field_key;
                if ($fieldKey) {
                    $responses[$fieldKey] = $fieldResponse->field_value;
                }
            }

            foreach ($formFields as $field) {
                $fieldKey = $field->field_key;
                $fieldType = $field->field_type;
                $fieldValue = $responses[$fieldKey] ?? null;

                if ($this->isMultiColumnFieldType($fieldType) && $field->options->count() > 0) {
                    $fieldLetter = chr(65 + $this->getFieldLetterIndex($formTemplate, $fieldKey));
                    foreach ($field->options as $optIndex => $option) {
                        $optionKey = $fieldKey . '_' . $option->option_value;
                        $row[$optionKey] = is_array($fieldValue)
                            ? (in_array($option->option_value, $fieldValue) ? $fieldLetter . ($optIndex + 1) : 0)
                            : (($fieldValue === $option->option_value) ? $fieldLetter . ($optIndex + 1) : 0);
                    }
                } elseif ($fieldType === 'time_duration') {
                    if (is_array($fieldValue)) {
                        $row[$fieldKey . '_start'] = $fieldValue['start_time'] ?? '-';
                        $row[$fieldKey . '_end'] = $fieldValue['end_time'] ?? '-';
                        $row[$fieldKey . '_valid'] = $fieldValue['valid_indicator'] ?? 0;

                        try {
                            if (!empty($fieldValue['start_time']) && !empty($fieldValue['end_time'])) {
                                $start = Carbon::createFromFormat('H:i', $fieldValue['start_time']);
                                $end = Carbon::createFromFormat('H:i', $fieldValue['end_time']);
                                $diff = $start->diff($end);
                                $row[$fieldKey . '_duration'] = sprintf('%02d:%02d', $diff->h, $diff->i);
                            } else {
                                $row[$fieldKey . '_duration'] = '-';
                            }
                        } catch (Exception $e) {
                            $row[$fieldKey . '_duration'] = '-';
                        }
                    } else {
                        $row[$fieldKey . '_start'] = '-';
                        $row[$fieldKey . '_end'] = '-';
                        $row[$fieldKey . '_valid'] = 0;
                        $row[$fieldKey . '_duration'] = '-';
                    }
                } elseif ($fieldType === 'time_range') {
                    $row[$fieldKey . '_input_value'] = is_array($fieldValue) ? ($fieldValue['input_value'] ?? '-') : '-';
                    $row[$fieldKey . '_valid_indicator'] = is_array($fieldValue) ? ($fieldValue['valid_indicator'] ?? 0) : 0;
                } else {
                    $row[$fieldKey] = $fieldValue;
                }
            }

            $complianceScorableFieldResponses = $entry->fieldResponses->filter(function ($fr) {
                return $this->isComplianceScoringFieldType($fr->formField?->field_type);
            });

            if ($complianceScorableFieldResponses->isEmpty()) {
                $row['validation_status'] = 1;
            } else {
                $validCount = $complianceScorableFieldResponses->where('compliance_score', '>', 0)->count();
                $row['validation_status'] = $validCount === $complianceScorableFieldResponses->count() ? 1 : 0;
            }

            $tableData[] = $row;
        }

        return $tableData;
    }

    public function buildMetadata($unitKerjaId, string $period, Carbon $startDate, Carbon $endDate, ?FormTemplate $formTemplate = null, ?UnitKerja $unitKerja = null): array
    {
        [$year, $month] = explode('-', $period);

        $startDay = $startDate->day;
        $endDay = $endDate->day;
        $startMonth = $startDate->translatedFormat('F');
        $endMonth = $endDate->translatedFormat('F');
        $yearLabel = $startDate->year;

        if ($startDate->month === $endDate->month) {
            $periodLabel = "{$startDay} - {$endDay} {$startMonth} {$yearLabel}";
        } else {
            $periodLabel = "{$startDay} {$startMonth} - {$endDay} {$endMonth} {$yearLabel}";
        }

        $metadata = [
            'unit_kerja_id' => $unitKerjaId,
            'period' => $period,
            'year' => (int) $year,
            'month' => (int) $month,
            'period_label' => $periodLabel,
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ];

        if ($unitKerja) {
            $metadata['unit_kerja'] = $unitKerja->unit_name;
        } elseif ($unitKerjaId) {
            $metadata['unit_kerja'] = UnitKerja::find($unitKerjaId)?->unit_name;
        }

        if ($formTemplate) {
            $metadata['imut_profile'] = $formTemplate->imutProfile?->title;
            $metadata['imut_data'] = $formTemplate->imutProfile?->imutData?->title;
            $metadata['form_template'] = $formTemplate->title;
        }

        return $metadata;
    }

    public function buildSummary(array $tableData, Collection $entries, FormTemplate $formTemplate): array
    {
        $totalEntries = count($tableData);
        $formFields = $formTemplate->formFields()->with('options')->get();

        $summary = [
            'total_entries' => $totalEntries,
            'fields' => [],
        ];

        foreach ($formFields as $field) {
            if ($this->isMultiColumnFieldType($field->field_type) && $field->options->count() > 0) {
                $fieldSummary = ['label' => $field->field_label, 'options' => []];

                foreach ($field->options as $option) {
                    $count = 0;
                    foreach ($entries as $entry) {
                        $fieldResponse = $entry->fieldResponses->firstWhere('form_field_id', $field->id);
                        $fieldValue = $fieldResponse?->field_value;

                        if (is_array($fieldValue) && in_array($option->option_value, $fieldValue)) {
                            $count++;
                        } elseif ($fieldValue === $option->option_value) {
                            $count++;
                        }
                    }

                    $fieldSummary['options'][$option->option_value] = [
                        'label' => $option->option_text,
                        'count' => $count,
                        'percentage' => $totalEntries > 0 ? round(($count / $totalEntries) * 100, 2) : 0,
                    ];
                }

                $summary['fields'][$field->field_key] = $fieldSummary;
            }
        }

        $complianceEntries = collect($tableData)->filter(fn ($row) => isset($row['validation_status']) && $row['validation_status'] == 1)->count();
        $nonComplianceEntries = collect($tableData)->filter(fn ($row) => isset($row['validation_status']) && $row['validation_status'] == 0)->count();

        $validEntries = 0;
        $validatedEntries = 0;
        foreach ($entries as $entry) {
            if ($entry->validation_status === 'valid') {
                $validEntries++;
                $validatedEntries++;
            } elseif ($entry->validation_status === 'invalid') {
                $validatedEntries++;
            }
        }

        $summary['compliance_entries'] = $complianceEntries;
        $summary['non_compliance_entries'] = $nonComplianceEntries;
        $summary['valid_entries'] = $validEntries;
        $summary['validated_entries'] = $validatedEntries;
        $summary['total_entries'] = $totalEntries;

        return $summary;
    }

    public function getUserInfo($user): array
    {
        return [
            'name' => $user?->name,
            'unit_kerja' => $user?->unitKerjas()->first()?->unit_name,
            'is_admin' => $user && (str_contains($user->email ?? '', 'admin') || $user->hasRole('super_admin')),
        ];
    }

    private function isComplianceScoringFieldType(string $fieldType): bool
    {
        return in_array($fieldType, [
            'boolean',
            'single_select',
            'multi_select',
            'conditional_trigger',
            'compliance_checker',
            'time_duration',
            'time_range',
            'rating_scale',
        ], true);
    }

    private function isMultiColumnFieldType(string $fieldType): bool
    {
        return in_array($fieldType, [
            'single_select',
            'multi_select',
            'compliance_checker',
            'conditional_trigger',
        ], true);
    }

    private function getFieldAlignment(string $fieldType): string
    {
        return match ($fieldType) {
            'number', 'rating_scale' => 'right',
            'boolean', 'single_select', 'multi_select', 'date' => 'center',
            default => 'left',
        };
    }

    private function getFieldFormat(string $fieldType): ?string
    {
        return match ($fieldType) {
            'boolean' => 'boolean',
            'date' => 'date',
            'number' => 'number',
            default => null,
        };
    }

    private function getFieldWidth(string $fieldType): string
    {
        return match ($fieldType) {
            'boolean', 'single_select', 'multi_select' => '120px',
            'number', 'rating_scale' => '100px',
            'date' => '120px',
            'text', 'textarea' => '200px',
            default => '150px',
        };
    }

    private function getFieldLetterIndex(FormTemplate $formTemplate, string $fieldKey): int
    {
        $formFields = $formTemplate->formFields()
            ->with(['options' => function ($q) {
                $q->orderBy('order_index', 'ASC');
            }])
            ->orderBy('enhanced_form_fields.order_index', 'ASC')
            ->get();

        $letterIndex = 0;
        foreach ($formFields as $field) {
            if ($field->field_key === $fieldKey) {
                return $letterIndex;
            }

            if ($this->isMultiColumnFieldType($field->field_type) && $field->options->count() > 0) {
                $letterIndex++;
            }
        }

        return 0;
    }

    private function buildLegend(FormTemplate $formTemplate): array
    {
        $legend = [];
        $formFields = $formTemplate->formFields()
            ->with(['options' => function ($q) {
                $q->orderBy('order_index', 'ASC');
            }])
            ->orderBy('enhanced_form_fields.order_index', 'ASC')
            ->get();

        $fieldCounter = 0;
        foreach ($formFields as $field) {
            $fieldOptions = $field->options;
            if ($this->isMultiColumnFieldType($field->field_type) && $fieldOptions->count() > 0) {
                $fieldLetter = chr(65 + $fieldCounter);
                $fieldLegend = ['field_label' => $field->field_label, 'field_key' => $field->field_key, 'options' => []];

                foreach ($fieldOptions as $index => $option) {
                    $fieldLegend['options'][] = [
                        'code' => $fieldLetter . ($index + 1),
                        'label' => $option->option_text,
                        'value' => $option->option_value,
                    ];
                }

                $legend[$field->field_key] = $fieldLegend;
                $fieldCounter++;
            }
        }

        return $legend;
    }
}
