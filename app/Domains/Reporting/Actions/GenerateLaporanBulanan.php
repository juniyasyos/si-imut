<?php

namespace App\Domains\Reporting\Actions;

use App\Domains\Reporting\Events\LaporanGenerated;
use App\Domains\Reporting\Models\LaporanImut;
use Illuminate\Support\Facades\DB;

class GenerateLaporanBulanan
{
    /**
     * Create a new laporan IMUT record for the requested period.
     */
    public function execute(array $payload): LaporanImut
    {
        return DB::transaction(function () use ($payload) {
            $laporan = LaporanImut::create($payload);

            $laporan->refresh();

            event(new LaporanGenerated($laporan));

            return $laporan;
        });
    }
}
