<?php

namespace App\Services;

use App\Models\DailyReportEntry;
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
            'profile.formTemplates'
        ]);

        // Get period dari laporan
        $laporan = $penilaian->laporanUnitKerja->laporanImut;
        $start = $laporan->assessment_period_start;
        $end = $laporan->assessment_period_end;

        // Get unit kerja & form template (indicator)
        $unitKerjaId = $penilaian->laporanUnitKerja->unit_kerja_id;

        // Get form template for this profile
        // Profile sudah validated valid period, jadi ambil template pertama
        $formTemplate = $penilaian->profile->formTemplates()->first();

        if (!$formTemplate) {
            Log::warning("No FormTemplate found for ImutProfile {$penilaian->imut_profil_id}");
            return $this->emptyResult($start, $end, 'No FormTemplate found');
        }

        // Query daily reports untuk indicator ini di unit kerja ini dalam periode ini
        $reports = DailyReportEntry::query()
            ->where('unit_kerja_id', $unitKerjaId)
            ->where('form_template_id', $formTemplate->id)
            ->whereBetween('report_date', [$start, $end])
            ->orderBy('report_date')
            ->get();

        // Calculate
        $denominator = $reports->count(); // Total laporan yang diinput
        $numerator = 0; // Yang compliance 100%
        $breakdown = [];

        foreach ($reports as $report) {
            $compliance = $formTemplate->calculateCompliance($report->responses);
            $score = $compliance['total_score'] ?? 0;
            $isPerfect = $score >= 100;

            if ($isPerfect) {
                $numerator++;
            }

            $breakdown[] = [
                'date' => $report->report_date->format('Y-m-d'),
                'compliance_score' => round($score, 2),
                'is_perfect' => $isPerfect,
            ];
        }

        $percentage = $denominator > 0
            ? round(($numerator / $denominator) * 100, 2)
            : 0;

        // Find missing dates
        $missingDates = $this->findMissingDates($reports, $start, $end);
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
