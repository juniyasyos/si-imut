<?php

namespace App\Domains\Imut\Actions;

use App\Domains\Imut\Events\ImutPenilaianSubmitted;
use App\Domains\Imut\Models\ImutPenilaian;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class SubmitImutPenilaian
{
    /**
     * Persist penilaian changes and trigger downstream updates.
     */
    public function execute(ImutPenilaian $penilaian, array $payload): ImutPenilaian
    {
        return DB::transaction(function () use ($penilaian, $payload) {
            $penilaian->fill(Arr::only($payload, $penilaian->getFillable()));
            $penilaian->save();

            $penilaian->refresh();

            event(new ImutPenilaianSubmitted($penilaian));

            return $penilaian;
        });
    }
}
