<?php

namespace App\Services;

use App\Models\DailyReportResponse;
use App\Models\ImutPenilaian;
use App\Models\LaporanImut;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DailyReportAggregationService
{
    /**
     * Calculate N/D untuk satu ImutPenilaian dari daily reports
     */
    public function calculateForPenilaian(ImutPenilaian $penilaian): array
    {
        // caller should eager load relationships to avoid N+1, but we load
        // anything missing just in case.
        $penilaian->loadMissing([
            'laporanUnitKerja.laporanImut',
            'laporanUnitKerja.unitKerja',
            'profile.formTemplates',
            'profile.imutData',
        ]);

        $laporan = $penilaian->laporanUnitKerja->laporanImut;
        $start = Carbon::parse($laporan->assessment_period_start);
        $end = Carbon::parse($laporan->assessment_period_end);

        $unitKerjaId = $penilaian->laporanUnitKerja->unit_kerja_id;
        $unitKerjaName = $penilaian->laporanUnitKerja->unitKerja->unit_name ?? 'Unknown';

        $formTemplate = $penilaian->profile->formTemplates()->first();
        if (! $formTemplate) {
            Log::warning("No FormTemplate found for ImutProfile {$penilaian->imut_profil_id}");
            return $this->emptyResult($start, $end, 'No FormTemplate found');
        }

        $imutDataTitle = $penilaian->profile->imutData->title ?? 'Unknown';

        // build the base query once; clones will be used for each count so that
        // additional where/selected columns do not leak between uses.
        $baseQuery = DailyReportResponse::query()
            ->where('unit_kerja_id', $unitKerjaId)
            ->where('form_template_id', $formTemplate->id)
            ->whereBetween('report_date', [
                $start->toDateString(),
                $end->toDateString(),
            ]);

        // denominator is simply the total number of reported rows
        $denominator = (clone $baseQuery)->count();

        // numerator – mimic the original PHP logic but perform the work in SQL
        // so we don't have to hydrate a collection. the JSON checks are required
        // because the definitive values come from calculation_details in many
        // cases.
        $numerator = (clone $baseQuery)
            ->where(function ($q) {
                $q->where('compliance_status', true)
                    ->orWhereRaw("JSON_EXTRACT(calculation_details, '$.compliance_status') = true")
                    ->orWhere('total_score', '>=', 100)
                    ->orWhereRaw("JSON_EXTRACT(calculation_details, '$.total_score') >= 100");
            })
            ->count();

        $percentage = $denominator > 0
            ? ceil(($numerator / $denominator) * 100 * 100) / 100
            : 0;

        $missingDates = $this->findMissingDates(clone $baseQuery, $start, $end);
        $totalDays = $start->diffInDays($end) + 1;

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
                'form_template_id' => $formTemplate->id,
                'form_template_title' => $formTemplate->title,
            ],
        ];

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

        // Jangan timpa data yang sudah ada jika tidak ada daily reports untuk periode ini.
        // Ini mencegah data lama (hasil migrasi) tertimpa nilai 0 ketika FieldResponse
        // diinsert sementara DailyReportResponse untuk bulan lama belum di-migrasi.
        if ($result['denominator'] === 0 && $penilaian->denominator_value !== null) {
            Log::info(
                "Skipping auto-calculation for ImutPenilaian {$penilaian->id}: "
                    . "tidak ada daily reports untuk periode ini dan data sudah ada "
                    . "(denominator_value={$penilaian->denominator_value}). "
                    . 'Kemungkinan sedang dalam proses migrasi data lama.'
            );

            return false;
        }

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
        // load all related models that will be touched during calculation. the
        // legacy implementation only fetched penilaians, which meant each
        // updatePenilaian() call would run additional queries for unitKerja,
        // formTemplates, etc.
        $laporan->load([
            'laporanUnitKerjas.unitKerja',
            'laporanUnitKerjas.imutPenilaians.profile.formTemplates',
            'laporanUnitKerjas.imutPenilaians.profile.imutData',
        ]);

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
    /**
     * Find dates without reports in the period. Accepts an Eloquent query so
     * that we avoid hydrating every row just to collect the dates.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     */
    private function findMissingDates($query, Carbon $start, Carbon $end): array
    {
        // clone to avoid modifying the original builder
        $reportedDates = (clone $query)
            ->select(DB::raw('DATE(report_date) as report_date'))
            ->distinct()
            ->pluck('report_date')
            ->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))
            ->toArray();

        $missingDates = [];
        $current = $start->copy();

        while ($current <= $end) {
            $dateStr = $current->format('Y-m-d');
            if (! in_array($dateStr, $reportedDates)) {
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
                'error' => $error,
            ],
        ];
    }
}
