<?php

namespace App\Observers;

use App\Models\FieldResponse;
use App\Services\Reporting\DailyReportAggregationService;
use Illuminate\Support\Facades\Log;

class FieldResponseObserver
{
    public function __construct(
        protected DailyReportAggregationService $aggregationService
    ) {}

    /**
     * Handle the FieldResponse "created" event.
     * 
     * ✨ OPTIMIZED: Direct sync calculation instead of async job dispatch
     * 
     * With direct FieldResponse.imut_penilaian_id FK relationship:
     * 1. Access ImutPenilaian directly from FieldResponse
     * 2. Calculate numerator/denominator for that ImutPenilaian immediately
     * 3. Update ImutPenilaian sync (no queue delay)
     * 
     * Benefits:
     * - Faster: sync instead of queue delay
     * - Efficient: only recalc affected ImutPenilaian (not whole LaporanImut)
     * - Simpler: direct DB update instead of job dispatch
     */
    public function created(FieldResponse $fieldResponse): void
    {
        // Load the related ImutPenilaian with its relationships
        $fieldResponse->loadMissing(['imutPenilaian', 'imutPenilaian.laporanUnitKerja.laporanImut']);
        
        $imutPenilaian = $fieldResponse->imutPenilaian;
        
        if (!$imutPenilaian) {
            Log::debug("FieldResponse created but no ImutPenilaian found (field_response_id={$fieldResponse->id})");
            return;
        }

        $laporanImut = $imutPenilaian->laporanUnitKerja?->laporanImut;
        
        if (!$laporanImut) {
            Log::debug("FieldResponse has ImutPenilaian but no LaporanImut found (imut_penilaian_id={$imutPenilaian->id})");
            return;
        }

        try {
            // Direct calculation for this ImutPenilaian (sync, not async)
            $calculation = $this->aggregationService->calculateForPenilaian($imutPenilaian);
            
            // Update ImutPenilaian with new values immediately
            $imutPenilaian->update([
                'numerator_value' => $calculation['numerator'],
                'denominator_value' => $calculation['denominator'],
                'calculation_details' => $calculation['calculation_metadata'],
            ]);
            
            Log::info("Updated ImutPenilaian {$imutPenilaian->id} directly from FieldResponse {$fieldResponse->id}", [
                'numerator' => $calculation['numerator'],
                'denominator' => $calculation['denominator'],
                'percentage' => $calculation['percentage'],
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to recalculate ImutPenilaian {$imutPenilaian->id} on FieldResponse creation", [
                'error' => $e->getMessage(),
                'field_response_id' => $fieldResponse->id,
            ]);
        }
    }
}

