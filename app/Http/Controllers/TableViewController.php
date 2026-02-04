<?php

namespace App\Http\Controllers;

use App\Models\DailyReportResponse;
use App\Models\FormTemplate;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TableViewController extends Controller
{
    /**
     * Display the table view page
     * 
     * URL Parameters:
     * - form_template_id: ID form template (required)
     * - unit_kerja_id: ID unit kerja (optional, null = all units)
     * - period: Format Y-m (e.g., 2026-02)
     */
    public function index(Request $request)
    {
        return view('table-view');
    }

    /**
     * Get table data based on filters
     * 
     * URL Parameters:
     * - form_template_id: ID form template (primary filter, required)
     * - unit_kerja_id: ID unit kerja (optional, jika kosong akan filter berdasarkan unit user)
     * - period: Format Y-m (e.g., 2026-02)
     */
    public function getData(Request $request)
    {
        $user = Auth::user();

        // Get filter parameters
        $formTemplateId = $request->input('form_template_id');
        $unitKerjaId = $request->input('unit_kerja_id');
        $period = $request->input('period', now()->format('Y-m'));

        // Debug - check laravel.log or use: tail -f storage/logs/laravel.log
        Log::info('TableView getData params', [
            'user' => $user?->email,
            'form_template_id' => $formTemplateId,
            'unit_kerja_id' => $unitKerjaId,
            'period' => $period,
        ]);

        // Parse period
        [$year, $month] = explode('-', $period);

        // Build query
        $query = DailyReportResponse::query()
            ->with([
                'formTemplate.formFields.options',
                'formTemplate.imutProfile.imutData',
                'unitKerja',
                'submittedBy',
                'fieldResponses.formField.options'
            ])
            ->whereYear('report_date', $year)
            ->whereMonth('report_date', $month);

        // Apply form_template_id filter
        if ($formTemplateId) {
            $query->where('form_template_id', $formTemplateId);
        }

        // Apply unit_kerja_id filter
        if ($unitKerjaId) {
            $query->where('unit_kerja_id', $unitKerjaId);
        } else {
            // If no specific unit, use user's units scope
            $query->forUserUnits($user);
        }

        // Get entries
        $entries = $query->orderBy('report_date', 'asc')->get();

        // Debug query result
        Log::info('TableView query result', [
            'entries_count' => $entries->count(),
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings(),
        ]);

        if ($entries->isEmpty()) {
            return response()->json([
                'tableTitle' => 'Data Laporan Harian',
                'tableDescription' => 'Tidak ada data untuk periode yang dipilih',
                'tableConfig' => null,
                'tableData' => [],
                'metadata' => $this->buildMetadata($unitKerjaId, $period),
                'user' => $this->getUserInfo($user),
            ]);
        }

        // Get the first form template to build headers
        $formTemplate = $entries->first()->formTemplate;

        // Build table configuration from form fields
        $tableConfig = $this->buildTableConfig($formTemplate);

        // Transform entries to table data
        $tableData = $this->transformEntriesToTableData($entries, $formTemplate);

        // Build metadata
        $metadata = $this->buildMetadata(
            $unitKerjaId,
            $period,
            $formTemplate,
            $entries->first()->unitKerja
        );

        // Build summary
        $summary = $this->buildSummary($entries, $formTemplate);

        return response()->json([
            'tableTitle' => $metadata['imut_profile'] ?? 'Data Laporan Harian',
            'tableDescription' => sprintf(
                'Menampilkan %d data laporan harian %s periode %s',
                $entries->count(),
                $metadata['unit_kerja'] ?? '',
                $metadata['period_label'] ?? $period
            ),
            'tableConfig' => $tableConfig,
            'tableData' => $tableData,
            'metadata' => $metadata,
            'summary' => $summary,
            'user' => $this->getUserInfo($user),
        ]);
    }

    /**
     * Build table configuration from form template
     */
    private function buildTableConfig(FormTemplate $formTemplate): array
    {
        $headers = [];

        // Add Tanggal column
        $headers[] = [
            'key' => 'report_date',
            'label' => 'Tanggal',
            'align' => 'center',
            'bgColor' => 'bg-blue-700',
            'format' => 'date',
        ];

        // Process form fields
        $formFields = $formTemplate->formFields()->orderBy('order_index')->get();

        foreach ($formFields as $field) {
            $fieldType = $field->field_type;
            $options = $field->options;

            // Field types with options become parent-child columns
            if ($this->isMultiColumnFieldType($fieldType) && $options->count() > 0) {
                $children = [];
                foreach ($options as $option) {
                    $children[] = [
                        'key' => $field->field_key . '_' . $option->option_value,
                        'label' => $option->option_text,
                        'align' => 'center',
                        'bgColor' => 'bg-blue-600',
                        'format' => 'checkbox',
                    ];
                }

                $headers[] = [
                    'label' => $field->field_label,
                    'bgColor' => 'bg-blue-800',
                    'children' => $children,
                ];
            }
            // Time duration fields
            elseif ($fieldType === 'time_duration') {
                $headers[] = [
                    'label' => $field->field_label,
                    'bgColor' => 'bg-blue-800',
                    'children' => [
                        [
                            'key' => $field->field_key . '_start',
                            'label' => 'Mulai',
                            'align' => 'center',
                            'bgColor' => 'bg-blue-600',
                        ],
                        [
                            'key' => $field->field_key . '_end',
                            'label' => 'Selesai',
                            'align' => 'center',
                            'bgColor' => 'bg-blue-600',
                        ],
                        [
                            'key' => $field->field_key . '_valid',
                            'label' => 'Valid',
                            'align' => 'center',
                            'bgColor' => 'bg-blue-600',
                            'format' => 'checkbox',
                        ],
                    ],
                ];
            }
            // Simple fields
            else {
                $headers[] = [
                    'key' => $field->field_key,
                    'label' => $field->field_label,
                    'align' => $this->getFieldAlignment($fieldType),
                    'bgColor' => 'bg-blue-700',
                    'format' => $this->getFieldFormat($fieldType),
                ];
            }
        }

        // Add Pelapor column
        $headers[] = [
            'key' => 'submitted_by_name',
            'label' => 'Pelapor',
            'align' => 'left',
            'bgColor' => 'bg-blue-700',
        ];

        // Add validation compliance
        $headers[] = [
            'key' => 'validation_status',
            'label' => 'Valid',
            'align' => 'center',
            'bgColor' => 'bg-green-700',
            'format' => 'checkbox',
        ];

        return ['headers' => $headers];
    }

    /**
     * Transform entries to flat table data
     */
    private function transformEntriesToTableData($entries, FormTemplate $formTemplate): array
    {
        $tableData = [];
        $formFields = $formTemplate->formFields()->with('options')->orderBy('order_index')->get();

        foreach ($entries as $index => $entry) {
            $row = [
                'no' => $index + 1,
                'report_date' => $entry->report_date->format('Y-m-d'),
                'submitted_by_name' => $entry->submittedBy?->name ?? '-',
            ];

            // Build responses array from fieldResponses relation
            $responses = [];
            foreach ($entry->fieldResponses as $fieldResponse) {
                $fieldKey = $fieldResponse->formField?->field_key;
                if ($fieldKey) {
                    $responses[$fieldKey] = $fieldResponse->field_value;
                }
            }

            // Log first entry to see structure
            if ($index === 0) {
                Log::info('First Entry fieldResponses', [
                    'entry_id' => $entry->id,
                    'report_date' => $entry->report_date->format('Y-m-d'),
                    'fieldResponses_count' => $entry->fieldResponses->count(),
                    'fieldResponses' => $entry->fieldResponses->map(function ($fr) {
                        return [
                            'id' => $fr->id,
                            'form_field_id' => $fr->form_field_id,
                            'field_key' => $fr->formField?->field_key,
                            'field_label' => $fr->formField?->field_label,
                            'field_type' => $fr->formField?->field_type,
                            'field_value' => $fr->field_value,
                            'is_valid' => $fr->is_valid,
                            'compliance_score' => $fr->compliance_score,
                        ];
                    })->toArray(),
                    'responses_array' => $responses,
                ]);
            }

            // Process each form field
            foreach ($formFields as $field) {
                $fieldKey = $field->field_key;
                $fieldType = $field->field_type;
                $fieldValue = $responses[$fieldKey] ?? null;

                // Multi-column field types (select with options)
                if ($this->isMultiColumnFieldType($fieldType) && $field->options->count() > 0) {
                    foreach ($field->options as $option) {
                        $optionKey = $fieldKey . '_' . $option->option_value;

                        if (is_array($fieldValue)) {
                            // Multi-select: check if option is in array
                            $row[$optionKey] = in_array($option->option_value, $fieldValue) ? 1 : 0;
                        } else {
                            // Single select: check if matches
                            $row[$optionKey] = ($fieldValue === $option->option_value) ? 1 : 0;
                        }
                    }
                }
                // Time duration
                elseif ($fieldType === 'time_duration') {
                    if (is_array($fieldValue)) {
                        $row[$fieldKey . '_start'] = $fieldValue['start_time'] ?? '-';
                        $row[$fieldKey . '_end'] = $fieldValue['end_time'] ?? '-';
                        $row[$fieldKey . '_valid'] = $fieldValue['valid_indicator'] ?? 0;
                    } else {
                        $row[$fieldKey . '_start'] = '-';
                        $row[$fieldKey . '_end'] = '-';
                        $row[$fieldKey . '_valid'] = 0;
                    }
                }
                // Simple fields
                else {
                    $row[$fieldKey] = $fieldValue;
                }
            }

            // Add validation status from fieldResponses
            // Check if all field responses are valid
            $validCount = $entry->fieldResponses->where('is_valid', true)->count();
            $totalCount = $entry->fieldResponses->count();
            $row['validation_status'] = ($totalCount > 0 && $validCount === $totalCount) ? 1 : 0;

            $tableData[] = $row;
        }

        return $tableData;
    }

    /**
     * Build metadata for the response
     */
    private function buildMetadata($unitKerjaId, $period, ?FormTemplate $formTemplate = null, ?UnitKerja $unitKerja = null): array
    {
        [$year, $month] = explode('-', $period);

        $metadata = [
            'unit_kerja_id' => $unitKerjaId,
            'period' => $period,
            'year' => (int) $year,
            'month' => (int) $month,
            'period_label' => Carbon::createFromDate($year, $month, 1)->translatedFormat('F Y'),
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

    /**
     * Build summary statistics
     */
    private function buildSummary($entries, FormTemplate $formTemplate): array
    {
        $totalEntries = $entries->count();
        $formFields = $formTemplate->formFields()->with('options')->get();

        $summary = [
            'total_entries' => $totalEntries,
            'fields' => [],
        ];

        foreach ($formFields as $field) {
            if ($this->isMultiColumnFieldType($field->field_type) && $field->options->count() > 0) {
                $fieldSummary = [
                    'label' => $field->field_label,
                    'options' => [],
                ];

                foreach ($field->options as $option) {
                    $count = 0;
                    foreach ($entries as $entry) {
                        // Get field value from fieldResponses
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

        // Calculate overall validation compliance
        $validEntries = $entries->filter(function ($entry) {
            $validCount = $entry->fieldResponses->where('is_valid', true)->count();
            $totalCount = $entry->fieldResponses->count();
            return $totalCount > 0 && $validCount === $totalCount;
        })->count();

        if ($totalEntries > 0) {
            $summary['validation_compliance'] = round(($validEntries / $totalEntries) * 100, 2);
            $summary['valid_entries'] = $validEntries;
            $summary['invalid_entries'] = $totalEntries - $validEntries;
        }

        return $summary;
    }

    /**
     * Get user info for response
     */
    private function getUserInfo($user): array
    {
        return [
            'name' => $user?->name,
            'unit_kerja' => $user?->unitKerjas()->first()?->unit_name,
            'is_admin' => $user && (str_contains($user->email ?? '', 'admin') || $user->hasRole('super_admin')),
        ];
    }

    /**
     * Check if field type should create multiple columns
     */
    private function isMultiColumnFieldType(string $fieldType): bool
    {
        return in_array($fieldType, [
            'single_select',
            'multi_select',
            'compliance_checker',
            'conditional_trigger',
        ]);
    }

    /**
     * Get default alignment for field type
     */
    private function getFieldAlignment(string $fieldType): string
    {
        return match ($fieldType) {
            'number', 'rating_scale' => 'right',
            'boolean', 'single_select', 'multi_select', 'date' => 'center',
            default => 'left',
        };
    }

    /**
     * Get default format for field type
     */
    private function getFieldFormat(string $fieldType): ?string
    {
        return match ($fieldType) {
            'boolean' => 'boolean',
            'date' => 'date',
            'number' => 'number',
            default => null,
        };
    }
}
