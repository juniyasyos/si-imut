<?php

namespace App\Domain\DailyReport;

use App\Models\FormTemplate;
use App\Models\UnitKerja;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class TableViewDomain
{
    public function buildTableConfig(FormTemplate $formTemplate, ?EloquentCollection $entries = null): array
    {
        $formFields = $this->getOrderedFormFields($formTemplate);
        $fieldLetterMap = $this->buildFieldLetterMap($formFields);

        $headers = [
            [
                'key' => 'report_date',
                'label' => 'Tanggal',
                'align' => 'center',
                'bgColor' => 'bg-blue-700',
                'format' => 'date',
                'width' => '100px',
            ],
        ];

        foreach ($formFields as $field) {
            $fieldKey = $field->field_key;
            $fieldType = $field->field_type;
            $options = $field->options;

            if ($this->isMultiColumnFieldType($fieldType) && $options->isNotEmpty()) {
                $fieldLetter = $fieldLetterMap[$fieldKey] ?? '';

                $headers[] = [
                    'label' => $field->field_label,
                    'bgColor' => 'bg-blue-800',
                    'children' => $options
                        ->values()
                        ->map(fn($option, int $index): array => [
                            'key' => $fieldKey . '_' . $option->option_value,
                            'label' => $fieldLetter . ($index + 1),
                            'full_label' => $option->option_text,
                            'align' => 'center',
                            'bgColor' => 'bg-blue-600',
                            'format' => 'field_code',
                            'width' => '60px',
                            'option_code' => $fieldLetter . ($index + 1),
                            'option_value' => $option->option_value,
                        ])
                        ->all(),
                    'field_type' => 'multi_select_group',
                    'field_key' => $fieldKey,
                ];

                continue;
            }

            if ($fieldType === 'time_duration') {
                $headers[] = [
                    'label' => $field->field_label . ' (Jam:Menit)',
                    'bgColor' => 'bg-blue-800',
                    'children' => [
                        [
                            'key' => $fieldKey . '_start',
                            'label' => 'Mulai',
                            'align' => 'center',
                            'bgColor' => 'bg-blue-600',
                            'width' => '100px',
                        ],
                        [
                            'key' => $fieldKey . '_end',
                            'label' => 'Selesai',
                            'align' => 'center',
                            'bgColor' => 'bg-blue-600',
                            'width' => '100px',
                        ],
                        [
                            'key' => $fieldKey . '_duration',
                            'label' => 'Selisih',
                            'align' => 'center',
                            'bgColor' => 'bg-blue-600',
                            'width' => '100px',
                        ],
                        [
                            'key' => $fieldKey . '_valid',
                            'label' => 'Sesuai',
                            'align' => 'center',
                            'bgColor' => 'bg-blue-600',
                            'format' => 'checkbox',
                            'width' => '80px',
                        ],
                    ],
                ];

                continue;
            }

            if ($fieldType === 'time_range') {
                $headers[] = [
                    'label' => $field->field_label . ' ' . $this->resolveTimeRangeBoundaryLabel($entries, $field->id),
                    'bgColor' => 'bg-blue-800',
                    'children' => [
                        [
                            'key' => $fieldKey . '_input_value',
                            'label' => 'Waktu',
                            'align' => 'center',
                            'bgColor' => 'bg-blue-600',
                            'width' => '100px',
                        ],
                        [
                            'key' => $fieldKey . '_valid_indicator',
                            'label' => 'Sesuai',
                            'align' => 'center',
                            'bgColor' => 'bg-blue-600',
                            'format' => 'checkbox',
                            'width' => '100px',
                        ],
                    ],
                ];

                continue;
            }

            $headers[] = [
                'key' => $fieldKey,
                'label' => $field->field_label,
                'align' => $this->getFieldAlignment($fieldType),
                'bgColor' => 'bg-blue-700',
                'format' => $this->getFieldFormat($fieldType),
                'width' => $this->getFieldWidth($fieldType),
            ];
        }

        $headers[] = [
            'key' => 'submitted_by_name',
            'label' => 'Pengumpul Data',
            'align' => 'left',
            'bgColor' => 'bg-blue-700',
            'width' => '150px',
        ];

        $headers[] = [
            'key' => 'validation_status',
            'label' => 'Status Kepatuhan',
            'align' => 'center',
            'bgColor' => 'bg-green-700',
            'format' => 'checkbox',
            'width' => '120px',
        ];

        $headers[] = [
            'key' => 'is_validated',
            'label' => 'Tervalidasi',
            'align' => 'center',
            'bgColor' => 'bg-yellow-700',
            'width' => '130px',
        ];

        $headers[] = [
            'key' => 'validated_by_name',
            'label' => 'Validator',
            'align' => 'left',
            'bgColor' => 'bg-yellow-700',
            'width' => '150px',
        ];

        return [
            'headers' => $headers,
            'legend' => $this->buildLegendFromFields($formFields, $fieldLetterMap),
            'encoding_rules' => [
                1 => 'Dipilih',
                0 => 'Tidak dipilih',
            ],
        ];
    }

    public function transformEntriesToTableData(EloquentCollection $entries, FormTemplate $formTemplate): array
    {
        $this->loadEntryRelations($entries);

        $formFields = $this->getOrderedFormFields($formTemplate);
        $fieldLetterMap = $this->buildFieldLetterMap($formFields);
        $fieldIdToKeyMap = $formFields->pluck('field_key', 'id')->all();

        $tableData = [];

        foreach ($entries->values() as $index => $entry) {
            $row = [
                'no' => $index + 1,
                'form_template_id' => $entry->form_template_id,
                'form_template_title' => $entry->formTemplate?->title ?? '-',
                'form_template_is_active' => $entry->formTemplate?->is_active ?? false,
                'report_date' => $entry->report_date?->format('Y-m-d'),
                'submitted_by_name' => $entry->submittedBy?->name ?? '-',
                'is_validated' => $this->formatValidationMark($entry->validation_status),
                'validated_by_name' => $entry->validator?->name ?? '-',
            ];

            $responsesByFieldKey = $this->mapResponsesByFieldKey($entry->fieldResponses, $fieldIdToKeyMap);

            foreach ($formFields as $field) {
                $fieldKey = $field->field_key;
                $fieldType = $field->field_type;
                $fieldValue = $responsesByFieldKey[$fieldKey]['value'] ?? null;

                if ($this->isMultiColumnFieldType($fieldType) && $field->options->isNotEmpty()) {
                    $fieldLetter = $fieldLetterMap[$fieldKey] ?? '';

                    foreach ($field->options->values() as $optionIndex => $option) {
                        $optionKey = $fieldKey . '_' . $option->option_value;

                        $row[$optionKey] = $this->isOptionSelected($fieldValue, $option->option_value)
                            ? $fieldLetter . ($optionIndex + 1)
                            : 0;
                    }

                    continue;
                }

                if ($fieldType === 'time_duration') {
                    $row = array_merge($row, $this->formatTimeDurationField($fieldKey, $fieldValue));

                    continue;
                }

                if ($fieldType === 'time_range') {
                    $row[$fieldKey . '_input_value'] = is_array($fieldValue)
                        ? ($fieldValue['input_value'] ?? '-')
                        : '-';

                    $row[$fieldKey . '_valid_indicator'] = is_array($fieldValue)
                        ? ($fieldValue['valid_indicator'] ?? 0)
                        : 0;

                    continue;
                }

                $row[$fieldKey] = $fieldValue;
            }

            $row['validation_status'] = $this->calculateComplianceStatus($entry->fieldResponses);

            $tableData[] = $row;
        }

        return $tableData;
    }

    public function buildMetadata(
        $unitKerjaId,
        string $period,
        Carbon $startDate,
        Carbon $endDate,
        ?FormTemplate $formTemplate = null,
        ?UnitKerja $unitKerja = null
    ): array {
        if ($formTemplate) {
            $formTemplate->loadMissing('imutProfile.imutData');
        }

        [$year, $month] = explode('-', $period);

        $periodLabel = $this->buildPeriodLabel($startDate, $endDate);

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
            $metadata['unit_kerja'] = UnitKerja::query()
                ->whereKey($unitKerjaId)
                ->value('unit_name');
        }

        if ($formTemplate) {
            $metadata['imut_profile'] = $formTemplate->imutProfile?->title;
            $metadata['imut_data'] = $formTemplate->imutProfile?->imutData?->title;
            $metadata['form_template'] = $formTemplate->title;
        }

        return $metadata;
    }

    public function buildSummary(array $tableData, EloquentCollection $entries, FormTemplate $formTemplate): array
    {
        $this->loadEntryRelations($entries);

        $totalEntries = count($tableData);
        $formFields = $this->getOrderedFormFields($formTemplate);
        $summary = [
            'total_entries' => $totalEntries,
            'fields' => [],
        ];

        foreach ($formFields as $field) {
            if (!$this->isMultiColumnFieldType($field->field_type) || $field->options->isEmpty()) {
                continue;
            }

            $optionCounts = $field->options
                ->pluck('option_value')
                ->mapWithKeys(fn($value) => [$value => 0])
                ->all();

            foreach ($entries as $entry) {
                $fieldValue = $entry->fieldResponses
                    ->firstWhere('form_field_id', $field->id)
                        ?->field_value;

                foreach ($field->options as $option) {
                    if ($this->isOptionSelected($fieldValue, $option->option_value)) {
                        $optionCounts[$option->option_value]++;
                    }
                }
            }

            $summary['fields'][$field->field_key] = [
                'label' => $field->field_label,
                'options' => $field->options
                    ->mapWithKeys(fn($option): array => [
                        $option->option_value => [
                            'label' => $option->option_text,
                            'count' => $optionCounts[$option->option_value] ?? 0,
                            'percentage' => $totalEntries > 0
                                ? round((($optionCounts[$option->option_value] ?? 0) / $totalEntries) * 100, 2)
                                : 0,
                        ],
                    ])
                    ->all(),
            ];
        }

        $complianceEntries = collect($tableData)
            ->where('validation_status', 1)
            ->count();

        $nonComplianceEntries = collect($tableData)
            ->where('validation_status', 0)
            ->count();

        $validatedEntries = $entries
            ->whereIn('validation_status', ['valid', 'invalid'])
            ->count();

        $validEntries = $entries
            ->where('validation_status', 'valid')
            ->count();

        return array_merge($summary, [
            'compliance_entries' => $complianceEntries,
            'non_compliance_entries' => $nonComplianceEntries,
            'valid_entries' => $validEntries,
            'validated_entries' => $validatedEntries,
            'total_entries' => $totalEntries,
        ]);
    }

    public function getUserInfo($user): array
    {
        $user?->loadMissing('unitKerjas');

        return [
            'name' => $user?->name,
            'unit_kerja' => $user?->unitKerjas?->first()?->unit_name,
            'is_admin' => $user && (
                str_contains($user->email ?? '', 'admin')
                || $user->hasRole('super_admin')
            ),
        ];
    }

    private function getOrderedFormFields(FormTemplate $formTemplate): Collection
    {
        return $formTemplate->formFields()
            ->with([
                'options' => fn($query) => $query->orderBy('order_index', 'ASC'),
            ])
            ->orderBy('enhanced_form_fields.order_index', 'ASC')
            ->get();
    }

    private function loadEntryRelations(EloquentCollection $entries): void
    {
        $entries->loadMissing([
            'formTemplate',
            'submittedBy',
            'validator',
            'fieldResponses.formField',
        ]);
    }

    private function buildFieldLetterMap(Collection $formFields): array
    {
        $map = [];
        $counter = 0;

        foreach ($formFields as $field) {
            if ($this->isMultiColumnFieldType($field->field_type) && $field->options->isNotEmpty()) {
                $map[$field->field_key] = chr(65 + $counter);
                $counter++;
            }
        }

        return $map;
    }

    private function mapResponsesByFieldKey(Collection $fieldResponses, array $fieldIdToKeyMap): array
    {
        $responses = [];

        foreach ($fieldResponses as $fieldResponse) {
            $fieldKey = $fieldIdToKeyMap[$fieldResponse->form_field_id] ?? null;

            if (!$fieldKey) {
                continue;
            }

            $responses[$fieldKey] = [
                'value' => $fieldResponse->field_value,
                'compliance_score' => $fieldResponse->compliance_score,
            ];
        }

        return $responses;
    }

    private function resolveTimeRangeBoundaryLabel(?EloquentCollection $entries, int $fieldId): string
    {
        if (!$entries || $entries->isEmpty()) {
            return '(Jam Kerja)';
        }

        $entries->loadMissing('fieldResponses');

        $fieldResponse = $entries
            ->first()
            ?->fieldResponses
            ->firstWhere('form_field_id', $fieldId);

        $fieldValue = $fieldResponse?->field_value;

        if (!is_array($fieldValue)) {
            return '(Jam Kerja)';
        }

        $startTime = $fieldValue['start_time'] ?? null;
        $endTime = $fieldValue['end_time'] ?? null;

        return $startTime && $endTime
            ? "({$startTime} - {$endTime})"
            : '(Jam Kerja)';
    }

    private function formatValidationMark(?string $status): string
    {
        return match ($status) {
            'valid' => '✓',
            'invalid' => '✗',
            default => '—',
        };
    }

    private function formatTimeDurationField(string $fieldKey, mixed $fieldValue): array
    {
        if (!is_array($fieldValue)) {
            return [
                $fieldKey . '_start' => '-',
                $fieldKey . '_end' => '-',
                $fieldKey . '_valid' => 0,
                $fieldKey . '_duration' => '-',
            ];
        }

        return [
            $fieldKey . '_start' => $fieldValue['start_time'] ?? '-',
            $fieldKey . '_end' => $fieldValue['end_time'] ?? '-',
            $fieldKey . '_valid' => $fieldValue['valid_indicator'] ?? 0,
            $fieldKey . '_duration' => $this->calculateDuration(
                $fieldValue['start_time'] ?? null,
                $fieldValue['end_time'] ?? null
            ),
        ];
    }

    private function calculateDuration(?string $startTime, ?string $endTime): string
    {
        if (!$startTime || !$endTime) {
            return '-';
        }

        try {
            $start = Carbon::createFromFormat('H:i', $startTime);
            $end = Carbon::createFromFormat('H:i', $endTime);
            $diff = $start->diff($end);

            return sprintf('%02d:%02d', $diff->h, $diff->i);
        } catch (\Throwable) {
            return '-';
        }
    }

    private function calculateComplianceStatus(Collection $fieldResponses): int
    {
        $scorableResponses = $fieldResponses->filter(
            fn($fieldResponse): bool => $this->isComplianceScoringFieldType(
                $fieldResponse->formField?->field_type
            )
        );

        if ($scorableResponses->isEmpty()) {
            return 1;
        }

        return $scorableResponses->every(
            fn($fieldResponse): bool => (float) $fieldResponse->compliance_score > 0
        ) ? 1 : 0;
    }

    private function isOptionSelected(mixed $fieldValue, mixed $optionValue): bool
    {
        if (is_array($fieldValue)) {
            return in_array($optionValue, $fieldValue, true);
        }

        return $fieldValue === $optionValue;
    }

    private function buildPeriodLabel(Carbon $startDate, Carbon $endDate): string
    {
        $startDay = $startDate->day;
        $endDay = $endDate->day;
        $startMonth = $startDate->translatedFormat('F');
        $endMonth = $endDate->translatedFormat('F');
        $year = $startDate->year;

        return $startDate->month === $endDate->month
            ? "{$startDay} - {$endDay} {$startMonth} {$year}"
            : "{$startDay} {$startMonth} - {$endDay} {$endMonth} {$year}";
    }

    private function buildLegendFromFields(Collection $formFields, array $fieldLetterMap): array
    {
        $legend = [];

        foreach ($formFields as $field) {
            if (!$this->isMultiColumnFieldType($field->field_type) || $field->options->isEmpty()) {
                continue;
            }

            $fieldLetter = $fieldLetterMap[$field->field_key] ?? '';

            $legend[$field->field_key] = [
                'field_label' => $field->field_label,
                'field_key' => $field->field_key,
                'options' => $field->options
                    ->values()
                    ->map(fn($option, int $index): array => [
                        'code' => $fieldLetter . ($index + 1),
                        'label' => $option->option_text,
                        'value' => $option->option_value,
                    ])
                    ->all(),
            ];
        }

        return $legend;
    }

    private function isComplianceScoringFieldType(?string $fieldType): bool
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

    private function isMultiColumnFieldType(?string $fieldType): bool
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
}