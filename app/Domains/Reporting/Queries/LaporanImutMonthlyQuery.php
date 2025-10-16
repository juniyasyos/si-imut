<?php

namespace App\Domains\Reporting\Queries;

use App\Domains\Reporting\Models\LaporanImut;
use Illuminate\Support\Collection;

class LaporanImutMonthlyQuery
{
    /**
     * Retrieve the most recent laporan IMUT records ensuring the collection is filled.
     */
    public function execute(int $limit = 6): Collection
    {
        $laporan = LaporanImut::with('unitKerjas')
            ->orderByDesc('assessment_period_start')
            ->limit($limit)
            ->get();

        if ($laporan->count() < $limit) {
            $additional = LaporanImut::where('status', '!=', LaporanImut::STATUS_PROCESS)
                ->orderByDesc('assessment_period_start')
                ->limit($limit - $laporan->count())
                ->with('unitKerjas')
                ->get();

            $laporan = $laporan->concat($additional);
        }

        return $laporan->sortBy('assessment_period_start')->values();
    }
}
