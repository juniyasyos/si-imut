<?php

namespace App\Observers;

use App\Models\DailyReportResponse;
use App\Models\ImutPenilaian;
use App\Services\Reporting\DailyReportAggregationService;
use Illuminate\Support\Facades\Log;

class DailyReportResponseObserver
{
    public function __construct(
        protected DailyReportAggregationService $aggregationService
    ) {}

    /**
     * Handle the DailyReportResponse "saved" event (created & updated).
     *
     * ✨ OPTIMIZED: Direct sync calculation per DailyReportResponse (instead of per FieldResponse)
     *
     * 1. Reduces heavy queries (calculateForPenilaian) from N queries per submission to 1 query.
     * 2. Uses guard clause to skip calculation during 'pending' status, ensuring 100% data accuracy
     *    and eliminating the staleness/race-condition bug present in the old FieldResponseObserver.
     */
    public function saved(DailyReportResponse $dailyReport): void
    {
        // Skip calculation if the status is still pending (e.g. during creation step).
        // It will be calculated once the compliance_status is finalized and updated.
        if ($dailyReport->compliance_status === 'pending') {
            return;
        }

        $this->calculate($dailyReport);
    }

    /**
     * Handle the DailyReportResponse "deleted" event.
     */
    public function deleted(DailyReportResponse $dailyReport): void
    {
        $this->calculate($dailyReport);
    }

    /**
     * Perform the actual recalculation for the ImutPenilaian.
     */
    protected function calculate(DailyReportResponse $dailyReport): void
    {
        $imutPenilaian = $this->findImutPenilaian($dailyReport);
        
        if (!$imutPenilaian) {
            Log::debug("DailyReportResponse updated but no ImutPenilaian found (report_id={$dailyReport->id})");
            return;
        }

        try {
            // Direct calculation for this ImutPenilaian (sync, not async)
            $this->aggregationService->updatePenilaian($imutPenilaian);
            
            Log::info("Triggered updatePenilaian for {$imutPenilaian->id} from DailyReportResponse {$dailyReport->id}");
        } catch (\Exception $e) {
            Log::error("Failed to recalculate ImutPenilaian {$imutPenilaian->id} on DailyReportResponse change", [
                'error' => $e->getMessage(),
                'report_id' => $dailyReport->id,
            ]);
        }
    }

    /**
     * Find ImutPenilaian for a given DailyReportResponse
     * Traces: FormTemplate → ImutProfil → ImutPenilaian (matching unit & period)
     */
    private function findImutPenilaian(DailyReportResponse $dailyReport): ?ImutPenilaian
    {
        $formTemplate = $dailyReport->formTemplate;
        
        if (!$formTemplate || !$formTemplate->imut_profile_id) {
            return null;
        }

        return ImutPenilaian::query()
            ->where('imut_profil_id', $formTemplate->imut_profile_id)
            ->whereHas('laporanUnitKerja', function ($q) use ($dailyReport) {
                $q->where('unit_kerja_id', $dailyReport->unit_kerja_id);
            })
            ->whereHas('laporanUnitKerja.laporanImut', function ($q) use ($dailyReport) {
                $q->where('assessment_period_start', '<=', $dailyReport->report_date)
                  ->where('assessment_period_end', '>=', $dailyReport->report_date);
            })
            ->first();
    }
}
