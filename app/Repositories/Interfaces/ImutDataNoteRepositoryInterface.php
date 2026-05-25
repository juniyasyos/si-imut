<?php

namespace App\Repositories\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface ImutDataNoteRepositoryInterface
{
    public function getActiveNotesForIndicators(array $imutIds, array $months, int $startYear, int $endYear): Collection;
}