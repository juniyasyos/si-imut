<?php

namespace App\Observers;

use App\Jobs\CalculateLaporanFromDailyReports;
use App\Models\FieldResponse;
use App\Models\LaporanImut;
use Illuminate\Support\Facades\Log;

class FieldResponseObserver
{
    /**
     * Handle the FieldResponse "created" event.
     */
    public function created(FieldResponse $fieldResponse): void
    {
        // Ensure related daily report is loaded
        $fieldResponse->loadMissing('dailyReportResponse');

        $report = $fieldResponse->dailyReportResponse;

        if (! $report) {
            Log::debug("FieldResponse created but no DailyReportResponse found (field_response_id={$fieldResponse->id})");
            return;
        }

        $reportDate = $report->report_date;
        $unitKerjaId = $report->unit_kerja_id;
        $formTemplateId = $report->form_template_id;

        // Cari LaporanImut yang rentang perubahannya mencakup tanggal laporan
        // dan memiliki unit kerja + profil/form template yang relevan.
        // Hanya proses laporan yang masih aktif (STATUS_PROCESS) untuk menghindari
        // perhitungan otomatis menimpa data lama saat migrasi database.
        $laporans = LaporanImut::query()
            ->where('status', LaporanImut::STATUS_PROCESS)
            ->where('assessment_period_start', '<=', $reportDate)
            ->where('assessment_period_end', '>=', $reportDate)
            ->whereHas('laporanUnitKerjas', function ($q) use ($unitKerjaId, $formTemplateId) {
                $q->where('unit_kerja_id', $unitKerjaId)
                    ->whereHas('imutPenilaians', function ($q2) use ($formTemplateId) {
                        $q2->whereHas('profile', function ($q3) use ($formTemplateId) {
                            $q3->whereHas('formTemplates', function ($q4) use ($formTemplateId) {
                                $q4->where('id', $formTemplateId);
                            });
                        });
                    });
            })
            ->get();

        // Fallback: jika tidak ditemukan berdasarkan formTemplate, berarti
        // perhitungan mungkin masih relevan untuk laporan yang mencakup unit + tanggal.
        // Tetap filter hanya laporan aktif (STATUS_PROCESS) agar data lama tidak tertimpa.
        if ($laporans->isEmpty()) {
            $laporans = LaporanImut::query()
                ->where('status', LaporanImut::STATUS_PROCESS)
                ->where('assessment_period_start', '<=', $reportDate)
                ->where('assessment_period_end', '>=', $reportDate)
                ->whereHas('laporanUnitKerjas', fn($q) => $q->where('unit_kerja_id', $unitKerjaId))
                ->get();
        }

        if ($laporans->isEmpty()) {
            Log::debug("No LaporanImut found to recalculate for DailyReportResponse {$report->id} (date={$reportDate}, unit={$unitKerjaId})");
            return;
        }

        foreach ($laporans as $laporan) {
            // Dispatch job yang sudah menggunakan queue (ShouldQueue)
            CalculateLaporanFromDailyReports::dispatch($laporan->id);
            Log::info("Dispatched CalculateLaporanFromDailyReports for laporan_id={$laporan->id} due to FieldResponse {$fieldResponse->id}");
        }
    }
}
