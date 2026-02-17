<?php

namespace App\Http\Controllers;

use App\Models\DailyReportResponse;
use App\Models\FormTemplate;
use App\Models\UnitKerja;
use App\Models\User;
use App\Models\LaporanImutAutoGenerationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        $date = Carbon::createFromDate($year, $month, 1);

        // Get period settings
        $settings = LaporanImutAutoGenerationSetting::getInstance();

        // Use full month approach (1 - end of month)
        $startDate = $date->copy()->startOfMonth()->startOfDay();
        $endDate = $date->copy()->endOfMonth()->endOfDay();

        // Build query
        $query = DailyReportResponse::query()
            ->with([
                'formTemplate.formFields.options',
                'formTemplate.imutProfile.imutData',
                'unitKerja',
                'submittedBy',
                'validator',
                'fieldResponses.formField.options'
            ])
            ->whereBetween('report_date', [$startDate, $endDate]);

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

        if ($entries->isEmpty()) {
            return response()->json([
                'tableTitle' => 'Data Laporan Harian',
                'tableDescription' => 'Tidak ada data untuk periode yang dipilih',
                'tableConfig' => null,
                'tableData' => [],
                'metadata' => $this->buildMetadata($unitKerjaId, $period, $startDate, $endDate),
                'user' => $this->getUserInfo($user),
            ]);
        }

        // Get the first form template to build headers
        $formTemplate = $entries->first()->formTemplate;

        // Build table configuration from form fields
        $tableConfig = $this->buildTableConfig($formTemplate, $entries);

        // Transform entries to table data
        $tableData = $this->transformEntriesToTableData($entries, $formTemplate);

        // Build metadata
        $metadata = $this->buildMetadata(
            $unitKerjaId,
            $period,
            $startDate,
            $endDate,
            $formTemplate,
            $entries->first()->unitKerja
        );

        // Build summary (pass tableData which contains validation_status)
        $summary = $this->buildSummary($tableData, $entries, $formTemplate);

        // Get users (pengumpul data & validator) berdasarkan unit kerja laporan
        // Pass current entries so selection (top pengumpul) is computed for the active period
        $reportUnitKerja = $entries->first()->unitKerja;
        $usersByUnit = $this->getUsersByUnit($reportUnitKerja, $entries);

        Log::info('TableView query result', [
            'tableTitle' => $metadata['imut_data'] ?? 'Data Laporan Harian',
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
            'usersByUnit' => $usersByUnit,
        ]);


        return response()->json([
            'tableTitle' => $metadata['imut_data'] ?? 'Data Laporan Harian',
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
            'usersByUnit' => $usersByUnit,
        ]);
    }

    /**
     * Build table configuration from form template
     */
    private function buildTableConfig(FormTemplate $formTemplate, $entries = null): array
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
                    'label' => $field->field_label . ' (Jam:Menit)',
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
                            'key' => $field->field_key . '_duration',
                            'label' => 'Selisih',
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
            // Time range fields
            elseif ($fieldType === 'time_range') {
                // Extract boundary times from first entry for header label
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
                        [
                            'key' => $field->field_key . '_input_value',
                            'label' => 'Waktu',
                            'align' => 'center',
                            'bgColor' => 'bg-blue-600',
                            'width' => '100px',
                        ],
                        [
                            'key' => $field->field_key . '_valid_indicator',
                            'label' => 'Sesuai',
                            'align' => 'center',
                            'bgColor' => 'bg-blue-600',
                            'format' => 'checkbox',
                            'width' => '100px',
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
            'label' => 'Status Kepatuhan',
            'align' => 'center',
            'bgColor' => 'bg-green-700',
            'format' => 'checkbox',
            'width' => '120px',
        ];
        // Add validation status column
        $headers[] = [
            'key' => 'is_validated',
            'label' => 'Tervalidasi',
            'align' => 'center',
            'bgColor' => 'bg-yellow-700',
            'width' => '130px',
        ];

        // Add validator name column
        $headers[] = [
            'key' => 'validated_by_name',
            'label' => 'Validator',
            'align' => 'left',
            'bgColor' => 'bg-yellow-700',
            'width' => '150px',
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
            // Pre-calculate validation status based on field responses - REMOVE THIS UNUSED LOGIC
            // Will be recalculated later using consistent compliance_score logic
            $totalFieldResponses = $entry->fieldResponses->count();

            $row = [
                'no' => $index + 1,
                'report_date' => $entry->report_date->format('Y-m-d'),
                'submitted_by_name' => $entry->submittedBy?->name ?? '-',
                'is_validated' => $entry->validation_status === 'valid' ? '✓' : ($entry->validation_status === 'invalid' ? '✗' : '—'),
                'validated_by_name' => $entry->validator?->name ?? '-',
            ];

            // Build responses array from fieldResponses relation
            $responses = [];
            foreach ($entry->fieldResponses as $fieldResponse) {
                $fieldKey = $fieldResponse->formField?->field_key;
                if ($fieldKey) {
                    $responses[$fieldKey] = $fieldResponse->field_value;
                }
            }

            // // Log first entry to see structure
            // if ($index === 0) {
            //     Log::info('First Entry fieldResponses', [
            //         'entry_id' => $entry->id,
            //         'report_date' => $entry->report_date->format('Y-m-d'),
            //         'fieldResponses_count' => $entry->fieldResponses->count(),
            //         'fieldResponses' => $entry->fieldResponses->map(function ($fr) {
            //             return [
            //                 'id' => $fr->id,
            //                 'form_field_id' => $fr->form_field_id,
            //                 'field_key' => $fr->formField?->field_key,
            //                 'field_label' => $fr->formField?->field_label,
            //                 'field_type' => $fr->formField?->field_type,
            //                 'field_value' => $fr->field_value,
            //                 'is_valid' => $fr->is_valid,
            //                 'compliance_score' => $fr->compliance_score,
            //             ];
            //         })->toArray(),
            //         'responses_array' => $responses,
            //     ]);
            // }

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

                        // Calculate duration
                        $duration = '-';
                        if (!empty($fieldValue['start_time']) && !empty($fieldValue['end_time'])) {
                            try {
                                $start = Carbon::createFromFormat('H:i', $fieldValue['start_time']);
                                $end = Carbon::createFromFormat('H:i', $fieldValue['end_time']);
                                $diff = $start->diff($end);
                                $duration = sprintf('%02d:%02d', $diff->h, $diff->i);
                            } catch (\Exception $e) {
                                $duration = '-';
                            }
                        }
                        $row[$fieldKey . '_duration'] = $duration;

                        // Log validation logic for time duration
                        // Log::info('Time duration validation logic', [
                        //     'entry_id' => $entry->id,
                        //     'field_key' => $fieldKey,
                        //     'field_value' => $fieldValue,
                        //     'start_time' => $fieldValue['start_time'] ?? null,
                        //     'end_time' => $fieldValue['end_time'] ?? null,
                        //     'valid_indicator' => $fieldValue['valid_indicator'] ?? 0,
                        //     'is_valid_set' => isset($fieldValue['valid_indicator']),
                        // ]);
                    } else {
                        $row[$fieldKey . '_start'] = '-';
                        $row[$fieldKey . '_end'] = '-';
                        $row[$fieldKey . '_valid'] = 0;
                        $row[$fieldKey . '_duration'] = '-';

                        // // Log when field value is not an array
                        // Log::info('Time duration field value is not an array', [
                        //     'entry_id' => $entry->id,
                        //     'field_key' => $fieldKey,
                        //     'field_value' => $fieldValue,
                        //     'valid_indicator_set_to' => 0,
                        // ]);
                    }
                }
                // Time range
                elseif ($fieldType === 'time_range') {
                    if (is_array($fieldValue)) {
                        // Display user input time (not the boundary range)
                        $row[$fieldKey . '_input_value'] = $fieldValue['input_value'] ?? '-';
                        $row[$fieldKey . '_valid_indicator'] = $fieldValue['valid_indicator'] ?? 0;
                    } else {
                        $row[$fieldKey . '_input_value'] = '-';
                        $row[$fieldKey . '_valid_indicator'] = 0;
                    }
                }
                // Simple fields
                else {
                    $row[$fieldKey] = $fieldValue;
                }
            }

            // Add validation status from fieldResponses
            // PERBAIKAN: Check HANYA field yang berkontribusi pada compliance scoring
            // (Bukan field text/number yang hanya untuk data support)

            $complianceScorableFieldResponses = $entry->fieldResponses->filter(function ($fr) {
                $fieldType = $fr->formField?->field_type;
                // Hanya include field types yang berkontribusi pada compliance score
                return $this->isComplianceScoringFieldType($fieldType);
            });

            if ($complianceScorableFieldResponses->isEmpty()) {
                // Tidak ada compliance-scoring field, dianggap valid by default
                $row['validation_status'] = 1;
            } else {
                // Check apakah SEMUA compliance-scoring field memiliki compliance_score > 0
                $validCount = $complianceScorableFieldResponses->where('compliance_score', '>', 0)->count();
                $totalCount = $complianceScorableFieldResponses->count();
                $row['validation_status'] = ($validCount === $totalCount) ? 1 : 0;
            }

            // // Log validation status for each entry
            // Log::info('Entry validation status & compliance-scoring fields analysis', [
            //     'entry_id' => $entry->id,
            //     'report_date' => $entry->report_date->format('Y-m-d'),
            //     'total_field_responses' => $entry->fieldResponses->count(),
            //     'compliance_scoring_fields' => $complianceScorableFieldResponses->count(),
            //     'valid_compliance_fields' => $complianceScorableFieldResponses->where('compliance_score', '>', 0)->count(),
            //     'validation_status' => $row['validation_status'],
            //     'field_responses_details' => $entry->fieldResponses->map(function ($fr) {
            //         $fieldType = $fr->formField?->field_type;
            //         return [
            //             'field_key' => $fr->formField?->field_key,
            //             'field_type' => $fieldType,
            //             'contributes_to_compliance' => $this->isComplianceScoringFieldType($fieldType),
            //             'field_value' => $fr->field_value,
            //             'is_valid' => $fr->is_valid,
            //             'compliance_score' => $fr->compliance_score,
            //         ];
            //     })->toArray(),
            // ]);

            $tableData[] = $row;
        }

        return $tableData;
    }

    /**
     * Build metadata for the response
     */
    private function buildMetadata($unitKerjaId, $period, $startDate, $endDate, ?FormTemplate $formTemplate = null, ?UnitKerja $unitKerja = null): array
    {
        [$year, $month] = explode('-', $period);

        // Calculate period label based on actual date range
        $startDay = $startDate->day;
        $endDay = $endDate->day;
        $startMonth = $startDate->translatedFormat('F');
        $endMonth = $endDate->translatedFormat('F');
        $yearLabel = $startDate->year;

        if ($startDate->month === $endDate->month) {
            // Same month
            $periodLabel = "{$startDay} - {$endDay} {$startMonth} {$yearLabel}";
        } else {
            // Cross month
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

    /**
     * Build summary statistics
     * 
     * Compliance Data (dari validation_status di tableData):
     *   - compliance_entries = count validation_status = 1 (sesuai/patuh)
     *   - non_compliance_entries = count validation_status = 0 (tidak sesuai/patuh)
     * 
     * Validation Data (dari is_validated di database):
     *   - valid_entries = count validation_status dalam DB = 'valid' (✓)
     *   - validated_entries = count validation_status = 'valid' OR 'invalid' (✓ + ✗)
     *   - — diabaikan
     */
    private function buildSummary($tableData, $entries, FormTemplate $formTemplate): array
    {
        $totalEntries = count($tableData);
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

        // ===== COMPLIANCE DATA (dari validation_status di tableData) =====
        // Hitung dari kolom validation_status (1=sesuai, 0=tidak sesuai)
        // INDEPENDEN dari is_validated (tervalidasi atau tidak)
        $complianceEntries = 0;    // validation_status = 1
        $nonComplianceEntries = 0; // validation_status = 0

        foreach ($tableData as $row) {
            if (isset($row['validation_status'])) {
                if ($row['validation_status'] == 1) {
                    $complianceEntries++;
                } else {
                    $nonComplianceEntries++;
                }
            }
        }

        // ===== VALIDATION DATA (dari is_validated dalam database) =====
        // Count entries berdasarkan database validation_status field:
        // - 'valid' = ✓ (valid_entries)
        // - 'invalid' = ✗ (tidak valid tapi sudah divalidasi)
        // - lainnya = — (belum divalidasi / abaikan)
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

        // Set summary with all metrics
        // Compliance metrics (dari validation_status di tableData)
        $summary['compliance_entries'] = $complianceEntries;       // Jumlah sesuai/patuh
        $summary['non_compliance_entries'] = $nonComplianceEntries; // Jumlah tidak sesuai/patuh

        // Validation metrics (dari is_validated dalam database)
        $summary['valid_entries'] = $validEntries;        // Jumlah ✓
        $summary['validated_entries'] = $validatedEntries; // Jumlah ✓ + ✗
        $summary['total_entries'] = $totalEntries;        // Semua entries

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
     * Get pengumpul data & validator berdasarkan unit kerja
     * - optimized: query only users that belong to the specified unit
     * - period-aware: if $entries provided, pick top pengumpul by submission count
     * - returns only the selected TTDs (pengumpul top & PIC/validator) in arrays
     */
    private function getUsersByUnit($unitKerja, $entries = null): array
    {
        if (!$unitKerja) {
            return [
                'pengumpul_data' => [],
                'validator' => [],
            ];
        }

        // Precompute counts from $entries (if available) to choose "top" users
        $submissionCounts = [];
        $validationCounts = [];
        $submitterIds = [];
        $validatorIds = [];
        if ($entries && $entries->count()) {
            $groupedSubmitted = $entries->groupBy('submitted_by')->map->count();
            $submissionCounts = $groupedSubmitted->toArray();
            $submitterIds = array_map('intval', array_keys($submissionCounts));

            $groupedValidated = $entries->groupBy('validated_by')->map->count();
            $validationCounts = $groupedValidated->toArray();
            $validatorIds = array_map('intval', array_keys($validationCounts));
        }

        // Query only users that belong to the target unit (more efficient)
        $unitUsers = User::with('roles', 'unitKerjas')
            ->where('status', 'active')
            ->whereHas('unitKerjas', function ($q) use ($unitKerja) {
                $q->where('unit_kerja.id', $unitKerja->id);
            })
            ->get();

        // 1) Try selecting top pengumpul from actual submitters in the current entries (preferred)
        //    IMPORTANT: include submitters even when they are not attached to the unit —
        //    the footer should reflect who actually submitted the entries for the period.
        $submitterCandidates = collect();
        if (!empty($submitterIds)) {
            $submitterCandidates = User::with('roles', 'unitKerjas')
                ->whereIn('id', $submitterIds)
                ->get()
                // prefer users who also belong to this unit (stable ordering)
                ->sortByDesc(fn($u) => $u->unitKerjas->pluck('id')->contains($unitKerja->id) ? 1 : 0);
        }

        // 2) Fallback candidate set: unit users who have role pengumpul_data
        $pengumpulRoleCandidates = $unitUsers->filter(fn($u) => $u->roles->pluck('name')->contains('pengumpul_data'));

        // 3) Validator candidates: prefer validators who actually validated entries in this period
        //    same rule as submitters — prefer validators from $entries even if not unit-affiliated
        $validatorCandidatesFromEntries = collect();
        if (!empty($validatorIds)) {
            $validatorCandidatesFromEntries = User::with('roles', 'unitKerjas')
                ->whereIn('id', $validatorIds)
                ->get()
                ->sortByDesc(fn($u) => $u->roles->pluck('name')->contains('validator_pic') ? 1 : 0);
        }

        $validatorRoleCandidates = $unitUsers->filter(fn($u) => $u->roles->pluck('name')->intersect(['validator_pic', 'validator'])->isNotEmpty());

        // Choose top pengumpul: prefer submitterCandidates who have pengumpul_data role.
        // IMPORTANT: never select a user whose role contains 'validator_pic' as the pengumpul
        // even when they were the submitter for the period.
        $topPengumpul = null;
        // if ($submitterCandidates->isNotEmpty()) {
        //     // 1) prefer submitters with the explicit pengumpul_data role
        //     $preferred = $submitterCandidates->filter(fn($u) => $u->roles->pluck('name')->contains('pengumpul_data'));

        //     if ($preferred->isNotEmpty()) {
        //         $pool = $preferred;
        //     } else {
        //         // 2) otherwise consider submitters but exclude any who are validator_pic
        //         $nonValidatorPicSubmitters = $submitterCandidates->reject(fn($u) => $u->roles->pluck('name')->contains('validator_pic'));

        //         // If excluding validator_pic leaves an empty pool, fall back to original submitters
        //         $pool = $nonValidatorPicSubmitters->isNotEmpty() ? $nonValidatorPicSubmitters : $submitterCandidates;
        //     }

        //     // choose top by submission count (preserves previous behavior)
        //     $topPengumpul = $pool->sortByDesc(fn($u) => $submissionCounts[$u->id] ?? 0)->first();
        // } elseif ($pengumpulRoleCandidates->isNotEmpty()) {
        //     }
        $topPengumpul = $pengumpulRoleCandidates->sortByDesc(fn($u) => $submissionCounts[$u->id] ?? 0)->first();

        // Choose top validator: prefer validators who validated entries in this period and prefer role validator_pic
        $topValidator = null;
        if ($validatorCandidatesFromEntries->isNotEmpty()) {
            $preferredPic = $validatorCandidatesFromEntries->filter(fn($u) => $u->roles->pluck('name')->contains('validator_pic'));
            $pool = $preferredPic->isNotEmpty() ? $preferredPic : $validatorCandidatesFromEntries;
            $topValidator = $pool->sortByDesc(fn($u) => $validationCounts[$u->id] ?? 0)->first();
        } elseif ($validatorRoleCandidates->isNotEmpty()) {
            $preferredPic = $validatorRoleCandidates->filter(fn($u) => $u->roles->pluck('name')->contains('validator_pic'));
            $pool = $preferredPic->isNotEmpty() ? $preferredPic : $validatorRoleCandidates;
            $topValidator = $pool->first();
        }

        $formatUser = function ($user, $unitUsers) {
            if (!$user) return null;

            $ttd = null;

            // 1) If stored value is already an absolute URL, use it directly (covers explicit S3/minio URLs)
            if ($user->ttd_url && preg_match('#^https?://#i', $user->ttd_url)) {
                $ttd = trim($user->ttd_url);
            } else {
                // 2) Prefer canonical URL from the model helper (this prefers S3/MinIO when available)
                $ttd = $user->getFilamentTtdUrl();

                // 3) If helper returned a public-disk URL ("/storage/...") normalize to a relative path
                if ($ttd) {
                    $pathOnly = parse_url($ttd, PHP_URL_PATH) ?: $ttd;
                    if (str_contains($pathOnly, '/storage/')) {
                        $ttd = '/' . ltrim($pathOnly, '/');
                    }
                }

                // 4) Last-resort: if no URL yet but file exists on local `public` disk, build relative path
                if (! $ttd && $user->ttd_url && Storage::disk('public')->exists($user->ttd_url)) {
                    $rawPublicUrl = trim(Storage::disk('public')->url($user->ttd_url));
                    $pathOnly = parse_url($rawPublicUrl, PHP_URL_PATH) ?: $rawPublicUrl;
                    $ttd = '/' . ltrim($pathOnly, '/');
                }
            }

            // Keep S3/external URL as-is (ttd may be absolute S3 URL) or relative /storage/... for local files
            $ttd = $ttd ? trim($ttd) : null;

            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->toArray(),
                'ttd_url' => $ttd ? trim($ttd) : '',
                'unit_users' => $unitUsers
            ];
        };

        return [
            // Keep same keys/types for backward compatibility — but return only the selected entries
            'pengumpul_data' => $topPengumpul ? [$formatUser($topPengumpul, $topPengumpul)] : [],
            'validator' => $topValidator ? [$formatUser($topValidator, $topPengumpul)] : [],
            'unit_kerja_id' => $unitKerja->id,
            'unit_kerja_name' => $unitKerja->unit_name,
        ];
    }

    /**
     * Check if field type contributes to compliance scoring
     * (NOT just for data collection)
     */
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
        ]);
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

        // // Log legend structure
        // Log::info('Table legend field code mapping', [
        //     'form_template_id' => $formTemplate->id,
        //     'legend_code_mapping' => $legendCodeMapping,
        //     'total_multi_column_fields' => $fieldCounter,
        // ]);

        // // Validate sync dengan header
        // Log::info('Field counter consistency check', [
        //     'form_template_id' => $formTemplate->id,
        //     'legend_field_counter' => $fieldCounter,
        //     'all_formfields_count' => $formTemplate->formFields()->count(),
        //     'multi_column_fields_count' => $formTemplate->formFields()
        //         ->whereIn('field_type', ['single_select', 'multi_select', 'compliance_checker', 'conditional_trigger'])
        //         ->whereHas('options')
        //         ->count(),
        // ]);

        return $legend;
    }
}
