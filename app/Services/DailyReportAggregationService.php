<?php

namespace App\Services;

use App\Models\DailyReportResponse;
use App\Models\FormTemplate;
use App\Models\ImutPenilaian;
use App\Models\ImutProfile;
use App\Models\LaporanImut;
use App\Models\LaporanUnitKerja;
use App\Models\UnitKerja;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DailyReportAggregationService
{
    /**
     * Calculate N/D untuk satu ImutPenilaian dari daily reports
     */
    public function calculateForPenilaian(ImutPenilaian $penilaian): array
    {
        $penilaian->loadMissing([
            'laporanUnitKerja.laporanImut',
            'laporanUnitKerja.unitKerja',
            'profile.formTemplates',
            'profile.imutData'
        ]);

        // Get period dari laporan
        $laporan = $penilaian->laporanUnitKerja->laporanImut;
        $start = $laporan->assessment_period_start;
        $end = $laporan->assessment_period_end;

        // Get unit kerja & form template (indicator)
        $unitKerjaId = $penilaian->laporanUnitKerja->unit_kerja_id;
        $unitKerjaName = $penilaian->laporanUnitKerja->unitKerja->unit_name ?? 'Unknown';

        // Get form template for this profile
        // Profile sudah validated valid period, jadi ambil template pertama
        $formTemplate = $penilaian->profile->formTemplates()->first();

        if (!$formTemplate) {
            Log::warning("No FormTemplate found for ImutProfile {$penilaian->imut_profil_id}");
            return $this->emptyResult($start, $end, 'No FormTemplate found');
        }

        $imutDataTitle = $penilaian->profile->imutData->title ?? 'Unknown';

        // Query daily reports untuk indicator ini di unit kerja ini dalam periode ini
        $reports = DailyReportResponse::query()
            ->where('unit_kerja_id', $unitKerjaId)
            ->where('form_template_id', $formTemplate->id)
            ->whereBetween('report_date', [$start, $end])
            ->orderBy('report_date')
            ->get();

        // DD khusus untuk IGD dan Kepatuhan Kebersihan Tangan
        // if (stripos($unitKerjaName, 'igd') !== false && stripos($imutDataTitle, 'kepatuhan kebersihan tangan') !== false) {
        //     dd([
        //         'unit_kerja' => [
        //             'id' => $unitKerjaId,
        //             'name' => $unitKerjaName,
        //         ],
        //         'imut_data' => [
        //             'id' => $penilaian->profile->imutData->id ?? null,
        //             'title' => $imutDataTitle,
        //         ],
        //         'form_template' => [
        //             'id' => $formTemplate->id,
        //             'title' => $formTemplate->title,
        //         ],
        //         'periode' => [
        //             'start' => $start->format('Y-m-d'),
        //             'end' => $end->format('Y-m-d'),
        //         ],
        //         'penilaian_id' => $penilaian->id,
        //         'query_params' => [
        //             'unit_kerja_id' => $unitKerjaId,
        //             'form_template_id' => $formTemplate->id,
        //             'date_range' => [$start->format('Y-m-d'), $end->format('Y-m-d')],
        //         ],
        //         'daily_reports' => [
        //             'total_count' => $reports->count(),
        //             'reports' => $reports->map(function ($report) {
        //                 return [
        //                     'id' => $report->id,
        //                     'report_date' => $report->report_date->format('Y-m-d'),
        //                     'total_score' => $report->total_score,
        //                     'compliance_status' => $report->compliance_status,
        //                     'responses' => $report->responses,
        //                     'calculation_details' => $report->calculation_details,
        //                     'created_at' => $report->created_at?->format('Y-m-d H:i:s'),
        //                 ];
        //             })->toArray(),
        //         ]
        //     ]);
        // }

        // Calculate
        $denominator = $reports->count(); // Total laporan yang diinput
        $numerator = 0; // Yang compliance 100%
        $breakdown = [];
        $calculationSteps = []; // Untuk debug

        foreach ($reports as $report) {
            // Ambil status dari calculation_details karena lebih akurat
            $calculationDetails = $report->calculation_details ?? [];
            $score = $calculationDetails['total_score'] ?? $report->total_score ?? 0;
            $complianceStatus = $calculationDetails['compliance_status'] ?? $report->compliance_status ?? false;

            // Perfect jika compliance_status true ATAU score >= 100
            $isPerfect = $complianceStatus === true || $score >= 100;

            $step = [
                'report_id' => $report->id,
                'date' => $report->report_date->format('Y-m-d'),
                'raw_data' => [
                    'total_score_field' => $report->total_score,
                    'compliance_status_field' => $report->compliance_status,
                    'calculation_details' => $calculationDetails,
                ],
                'used_values' => [
                    'score' => $score,
                    'compliance_status' => $complianceStatus,
                ],
                'logic' => [
                    'compliance_status_is_true' => $complianceStatus === true,
                    'score_gte_100' => $score >= 100,
                    'formula' => sprintf(
                        '(%s === true) || (%s >= 100)',
                        var_export($complianceStatus, true),
                        $score
                    ),
                ],
                'result' => [
                    'is_perfect' => $isPerfect,
                    'numerator_before' => $numerator,
                ],
            ];

            if ($isPerfect) {
                $numerator++;
                $step['result']['numerator_after'] = $numerator;
                $step['result']['action'] = '✅ MASUK NUMERATOR';
            } else {
                $step['result']['numerator_after'] = $numerator;
                $step['result']['action'] = '❌ TIDAK DIHITUNG';
            }

            $calculationSteps[] = $step;

            $breakdown[] = [
                'date' => $report->report_date->format('Y-m-d'),
                'report_id' => $report->id,
                'compliance_score' => round($score, 2),
                'compliance_status_field' => $report->compliance_status,
                'compliance_status_calculated' => $complianceStatus,
                'is_perfect' => $isPerfect,
            ];
        }

        $percentage = $denominator > 0
            ? round(($numerator / $denominator) * 100, 2)
            : 0;

        // Find missing dates
        $missingDates = $this->findMissingDates($reports, $start, $end);
        $totalDays = $start->diffInDays($end) + 1;

        // // DD hasil perhitungan untuk IGD dan Kepatuhan Kebersihan Tangan
        // if (stripos($unitKerjaName, 'igd') !== false && stripos($imutDataTitle, 'kepatuhan kebersihan tangan') !== false) {
        //     dd([
        //         'IDENTITAS' => [
        //             'unit_kerja' => $unitKerjaName,
        //             'imut_data' => $imutDataTitle,
        //             'penilaian_id' => $penilaian->id,
        //         ],
        //         'PERIODE' => [
        //             'start' => $start->format('Y-m-d'),
        //             'end' => $end->format('Y-m-d'),
        //             'total_days' => $totalDays,
        //         ],
        //         'DATA_INPUT' => [
        //             'total_reports' => $reports->count(),
        //             'reports_by_date' => $reports->groupBy(fn($r) => $r->report_date->format('Y-m-d'))
        //                 ->map(fn($group) => $group->count())
        //                 ->toArray(),
        //         ],
        //         'PROSES_KALKULASI' => $calculationSteps,
        //         'HASIL_AKHIR' => [
        //             'numerator' => $numerator,
        //             'denominator' => $denominator,
        //             'percentage' => $percentage,
        //             'formula' => sprintf('(%d / %d) × 100 = %s%%', $numerator, $denominator, $percentage),
        //         ],
        //         'BREAKDOWN' => $breakdown,
        //         'MISSING_DATES' => $missingDates,
        //         'PERHATIAN' => [
        //             'duplicate_dates' => $reports->groupBy(fn($r) => $r->report_date->format('Y-m-d'))
        //                 ->filter(fn($group) => $group->count() > 1)
        //                 ->map(fn($group) => [
        //                     'date' => $group->first()->report_date->format('Y-m-d'),
        //                     'count' => $group->count(),
        //                     'ids' => $group->pluck('id')->toArray(),
        //                 ])
        //                 ->values()
        //                 ->toArray(),
        //         ]
        //     ]);
        // }

        return [
            'numerator' => $numerator,
            'denominator' => $denominator,
            'percentage' => $percentage,
            'calculation_metadata' => [
                'calculated_at' => now()->toDateTimeString(),
                'total_days_in_period' => $totalDays,
                'days_reported' => $denominator,
                'days_perfect' => $numerator,
                'missing_dates' => $missingDates,
                'compliance_breakdown' => $breakdown,
                'form_template_id' => $formTemplate->id,
                'form_template_title' => $formTemplate->title,
            ],
        ];
    }

    /**
     * Update ImutPenilaian dengan hasil calculation
     */
    public function updatePenilaian(ImutPenilaian $penilaian): bool
    {
        $result = $this->calculateForPenilaian($penilaian);

        return $penilaian->update([
            'numerator_value' => $result['numerator'],
            'denominator_value' => $result['denominator'],
            'is_auto_calculated' => true,
            'calculation_metadata' => $result['calculation_metadata'],
        ]);
    }

    /**
     * Calculate untuk semua ImutPenilaian dalam LaporanImut
     */
    public function calculateForLaporan(LaporanImut $laporan): array
    {
        $laporan->loadMissing('laporanUnitKerjas.imutPenilaians');

        $results = [
            'total_penilaians' => 0,
            'calculated' => 0,
            'skipped' => 0,
            'errors' => [],
        ];

        foreach ($laporan->laporanUnitKerjas as $laporanUnitKerja) {
            foreach ($laporanUnitKerja->imutPenilaians as $penilaian) {
                $results['total_penilaians']++;

                try {
                    $this->updatePenilaian($penilaian);
                    $results['calculated']++;
                } catch (\Exception $e) {
                    $results['skipped']++;
                    $results['errors'][] = [
                        'penilaian_id' => $penilaian->id,
                        'error' => $e->getMessage(),
                    ];
                    Log::error("Failed to calculate penilaian {$penilaian->id}: {$e->getMessage()}");
                }
            }
        }

        return $results;
    }

    /**
     * Find dates without reports in the period
     */
    private function findMissingDates(Collection $reports, Carbon $start, Carbon $end): array
    {
        $reportedDates = $reports->pluck('report_date')
            ->map(fn($d) => $d->format('Y-m-d'))
            ->toArray();

        $missingDates = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            if (!in_array($dateStr, $reportedDates)) {
                $missingDates[] = $dateStr;
            }
            $current->addDay();
        }

        return $missingDates;
    }

    /**
     * Empty result when no form template found
     */
    private function emptyResult(Carbon $start, Carbon $end, string $error = 'Unknown error'): array
    {
        $totalDays = $start->diffInDays($end) + 1;

        return [
            'numerator' => 0,
            'denominator' => 0,
            'percentage' => 0,
            'calculation_metadata' => [
                'calculated_at' => now()->toDateTimeString(),
                'total_days_in_period' => $totalDays,
                'days_reported' => 0,
                'days_perfect' => 0,
                'missing_dates' => [],
                'compliance_breakdown' => [],
                'error' => $error,
            ],
        ];
    }
}
