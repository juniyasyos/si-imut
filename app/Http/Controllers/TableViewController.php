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
        // Log::info('TableView getData params', [
        //     'user' => $user?->email,
        //     'form_template_id' => $formTemplateId,
        //     'unit_kerja_id' => $unitKerjaId,
        //     'period' => $period,
        // ]);

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
        $fieldCounter = 0; // Counter untuk field (A, B, C...)
        $fieldCodeMapping = []; // Track kode untuk logging

        // Add Tanggal column
        $headers[] = [
            'key' => 'report_date',
            'label' => 'Tanggal',
            'align' => 'center',
            'bgColor' => 'bg-blue-700',
            'format' => 'date',
            'width' => '100px',
        ];

        // Process form fields - Explicitly with order_index - WITH OPTIONS EAGER LOAD
        $formFields = $formTemplate->formFields()
            ->with(['options' => function ($q) {
                $q->orderBy('order_index', 'ASC');
            }])
            ->orderBy('enhanced_form_fields.order_index', 'ASC')
            ->get();

        foreach ($formFields as $field) {
            $fieldType = $field->field_type;
            // Gunakan relation yang sudah eager-loaded & ordered di line 160-161
            $options = $field->options;

            // Field types with options become parent-child columns with numeric codes
            if ($this->isMultiColumnFieldType($fieldType) && $options->count() > 0) {
                $fieldLetter = chr(65 + $fieldCounter); // A, B, C, D...
                $fieldCodes = [];
                $children = [];
                foreach ($options as $index => $option) {
                    // Format: A1, A2, A3 (Field 1), B1, B2, B3 (Field 2), dst
                    $optionCode = $fieldLetter . ($index + 1);
                    $fieldCodes[] = $optionCode;
                    $children[] = [
                        'key' => $field->field_key . '_' . $option->option_value,
                        'label' => $optionCode,
                        'full_label' => $option->option_text,
                        'align' => 'center',
                        'bgColor' => 'bg-blue-600',
                        'format' => 'field_code',
                        'width' => '60px',
                        'option_code' => $optionCode,
                        'option_value' => $option->option_value,
                    ];
                }

                // Store mapping untuk logging
                $fieldCodeMapping[$field->field_key] = [
                    'field_label' => $field->field_label,
                    'field_letter' => $fieldLetter,
                    'codes' => $fieldCodes,
                    'options' => $options->pluck('option_text', 'option_value')->toArray()
                ];

                $headers[] = [
                    'label' => $field->field_label,
                    'bgColor' => 'bg-blue-800',
                    'children' => $children,
                    'field_type' => 'multi_select_group',
                    'field_key' => $field->field_key,
                ];
                $fieldCounter++; // Increment untuk field berikutnya
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
                            'width' => '100px',
                        ],
                        [
                            'key' => $field->field_key . '_end',
                            'label' => 'Selesai',
                            'align' => 'center',
                            'bgColor' => 'bg-blue-600',
                            'width' => '100px',
                        ],
                        [
                            'key' => $field->field_key . '_valid',
                            'label' => 'Sesuai',
                            'align' => 'center',
                            'bgColor' => 'bg-blue-600',
                            'format' => 'checkbox',
                            'width' => '80px',
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
                    'width' => $this->getFieldWidth($fieldType),
                ];
            }
        }

        // Add Pelapor column
        $headers[] = [
            'key' => 'submitted_by_name',
            'label' => 'Pengumpul Data',
            'align' => 'left',
            'bgColor' => 'bg-blue-700',
            'width' => '150px',
        ];

        // Add validation compliance
        $headers[] = [
            'key' => 'validation_status',
            'label' => 'Sesuai',
            'align' => 'center',
            'bgColor' => 'bg-green-700',
            'format' => 'checkbox',
            'width' => '80px',
        ];

        // Build legend for multi-select fields
        $legend = $this->buildLegend($formTemplate);

        // // Log field code mapping untuk debugging
        // Log::info('Table config field code mapping', [
        //     'form_template_id' => $formTemplate->id,
        //     'field_code_mapping' => $fieldCodeMapping,
        //     'total_multi_column_fields' => $fieldCounter,
        //     'total_headers' => count($headers),
        // ]);

        // // Log DETAILED headers structure untuk debugging mismatch
        // Log::info('Table config headers detail - FOR DEBUG', [
        //     'form_template_id' => $formTemplate->id,
        //     'headers_count' => count($headers),
        //     'headers_structure' => array_map(function ($h, $idx) {
        //         return [
        //             'index' => $idx,
        //             'label' => $h['label'],
        //             'key' => $h['key'] ?? 'N/A',
        //             'children_count' => count($h['children'] ?? []),
        //             'children_labels' => array_map(fn($c) => $c['label'], $h['children'] ?? []),
        //         ];
        //     }, $headers, array_keys($headers)),
        // ]);

        return [
            'headers' => $headers,
            'legend' => $legend,
            'encoding_rules' => [
                1 => 'Dipilih',
                0 => 'Tidak dipilih'
            ]
        ];
    }

    /**
     * Transform entries to flat table data
     */
    private function transformEntriesToTableData($entries, FormTemplate $formTemplate): array
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

                // Multi-column field types (select with options) - Store KODE (A1, A2, A3) atau 0 jika tidak dipilih
                if ($this->isMultiColumnFieldType($fieldType) && $field->options->count() > 0) {
                    // Cari field letter index (A=0, B=1, C=2, dst)
                    $fieldLetterIndex = $this->getFieldLetterIndex($formTemplate, $fieldKey);
                    $fieldLetter = chr(65 + $fieldLetterIndex); // A, B, C...

                    // Gunakan relation yang sudah eager-loaded & ordered
                    $fieldOptions = $field->options;
                    foreach ($fieldOptions as $optIndex => $option) {
                        $optionKey = $fieldKey . '_' . $option->option_value;
                        $optionCode = $fieldLetter . ($optIndex + 1); // A1, A2, A3 dst

                        if (is_array($fieldValue)) {
                            // Multi-select: check if option is in array
                            $row[$optionKey] = in_array($option->option_value, $fieldValue) ? $optionCode : 0;
                        } else {
                            // Single select: check if matches
                            $row[$optionKey] = ($fieldValue === $option->option_value) ? $optionCode : 0;
                        }
                    }
                }
                // Time duration
                elseif ($fieldType === 'time_duration') {
                    if (is_array($fieldValue)) {
                        $row[$fieldKey . '_start'] = $fieldValue['start_time'] ?? '-';
                        $row[$fieldKey . '_end'] = $fieldValue['end_time'] ?? '-';
                        $row[$fieldKey . '_valid'] = $fieldValue['valid_indicator'] ?? 0;

                        // Log validation logic for time duration
                        Log::info('Time duration validation logic', [
                            'entry_id' => $entry->id,
                            'field_key' => $fieldKey,
                            'field_value' => $fieldValue,
                            'start_time' => $fieldValue['start_time'] ?? null,
                            'end_time' => $fieldValue['end_time'] ?? null,
                            'valid_indicator' => $fieldValue['valid_indicator'] ?? 0,
                            'is_valid_set' => isset($fieldValue['valid_indicator']),
                        ]);
                    } else {
                        $row[$fieldKey . '_start'] = '-';
                        $row[$fieldKey . '_end'] = '-';
                        $row[$fieldKey . '_valid'] = 0;

                        // Log when field value is not an array
                        Log::info('Time duration field value is not an array', [
                            'entry_id' => $entry->id,
                            'field_key' => $fieldKey,
                            'field_value' => $fieldValue,
                            'valid_indicator_set_to' => 0,
                        ]);
                    }
                }
                // Simple fields
                else {
                    $row[$fieldKey] = $fieldValue;
                }
            }

            // Add validation status from fieldResponses
            // Check if all field responses have compliance_score > 0
            $validCount = $entry->fieldResponses->where('compliance_score', '>', 0)->count();
            $totalCount = $entry->fieldResponses->count();
            $row['validation_status'] = ($totalCount > 0 && $validCount === $totalCount) ? 1 : 0;

            // Log validation status for each entry
            Log::info('Entry validation status', [
                'entry_id' => $entry->id,
                'report_date' => $entry->report_date->format('Y-m-d'),
                'total_field_responses' => $totalCount,
                'valid_field_responses' => $validCount,
                'validation_status' => $row['validation_status'],
                'field_responses_details' => $entry->fieldResponses->map(function ($fr) {
                    return [
                        'field_key' => $fr->formField?->field_key,
                        'is_valid' => $fr->is_valid,
                        'compliance_score' => $fr->compliance_score,
                    ];
                })->toArray(),
            ]);

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
            $validCount = $entry->fieldResponses->where('compliance_score', '>', 0)->count();
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

    /**
     * Get default width for field type
     */
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

    /**
     * Get field letter index untuk kode (A=0, B=1, C=2, dst)
     * Hanya count multi-column fields yang punya options
     */
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
            // Hanya count field yang multi-column dengan options
            if ($this->isMultiColumnFieldType($field->field_type) && $field->options->count() > 0) {
                $letterIndex++;
            }
        }
        return 0;
    }

    /**
     * Build legend for multi-select fields
     */
    private function buildLegend(FormTemplate $formTemplate): array
    {
        $legend = [];
        $formFields = $formTemplate->formFields()
            ->with(['options' => function ($q) {
                $q->orderBy('order_index', 'ASC');
            }])
            ->orderBy('enhanced_form_fields.order_index', 'ASC')
            ->get();
        $fieldCounter = 0; // Counter untuk field (A, B, C...)
        $legendCodeMapping = []; // Track untuk logging

        foreach ($formFields as $field) {
            // Gunakan relation yang sudah eager-loaded & ordered
            $fieldOptions = $field->options;
            if ($this->isMultiColumnFieldType($field->field_type) && $fieldOptions->count() > 0) {
                $fieldLetter = chr(65 + $fieldCounter); // A, B, C, D...
                $fieldLegend = [
                    'field_label' => $field->field_label,
                    'field_key' => $field->field_key,
                    'options' => []
                ];
                $fieldOptionCodes = [];

                foreach ($fieldOptions as $index => $option) {
                    // Format: A1, A2, A3 (Field 1), B1, B2, B3 (Field 2), dst
                    $optionCode = $fieldLetter . ($index + 1);
                    $fieldOptionCodes[$optionCode] = $option->option_text;
                    $fieldLegend['options'][] = [
                        'code' => $optionCode,
                        'label' => $option->option_text,
                        'value' => $option->option_value
                    ];
                }

                $legend[$field->field_key] = $fieldLegend;
                $legendCodeMapping[$field->field_key] = [
                    'field_label' => $field->field_label,
                    'field_letter' => $fieldLetter,
                    'codes' => $fieldOptionCodes,
                ];
                $fieldCounter++; // Increment untuk field berikutnya
            }
        }

        // Log legend structure
        Log::info('Table legend field code mapping', [
            'form_template_id' => $formTemplate->id,
            'legend_code_mapping' => $legendCodeMapping,
            'total_multi_column_fields' => $fieldCounter,
        ]);

        // Validate sync dengan header
        Log::info('Field counter consistency check', [
            'form_template_id' => $formTemplate->id,
            'legend_field_counter' => $fieldCounter,
            'all_formfields_count' => $formTemplate->formFields()->count(),
            'multi_column_fields_count' => $formTemplate->formFields()
                ->whereIn('field_type', ['single_select', 'multi_select', 'compliance_checker', 'conditional_trigger'])
                ->whereHas('options')
                ->count(),
        ]);

        return $legend;
    }
}
