<?php

namespace App\Repositories;

use App\Models\ImutDataNote;
use App\Repositories\Interfaces\ImutDataNoteRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class ImutDataNoteRepository implements ImutDataNoteRepositoryInterface
{
    public function getActiveNotesForIndicators(array $imutIds, array $months, int $startYear, int $endYear): Collection
    {
        if (count($imutIds) === 0) {
            return collect();
        }

        $notesQuery = ImutDataNote::active()
            ->whereIn('imut_data_id', $imutIds);

        $years = range($startYear, $endYear);

        $notesQuery->where(function ($q) use ($years, $months) {
            $q->orWhere(function ($sub) use ($years) {
                $sub->where('period_type', 'tahunan')
                    ->whereIn('period_year', $years);
            });

            $q->orWhere(function ($sub) use ($years, $months) {
                $sub->where('period_type', 'semester')
                    ->whereIn('period_year', $years)
                    ->where(function ($s) use ($months) {
                        $hasS1Month = false;
                        $hasS2Month = false;
                        foreach ($months as $m) {
                            $monthNum = (int) Carbon::parse($m . '-01')->month;
                            if ($monthNum >= 1 && $monthNum <= 6) {
                                $hasS1Month = true;
                            }
                            if ($monthNum >= 7 && $monthNum <= 12) {
                                $hasS2Month = true;
                            }
                        }
                        if ($hasS1Month) {
                            $s->orWhere('period_semester', 'S1');
                        }
                        if ($hasS2Month) {
                            $s->orWhere('period_semester', 'S2');
                        }
                    });
            });

            $q->orWhere(function ($sub) use ($years, $months) {
                $sub->where('period_type', 'triwulan')
                    ->whereIn('period_year', $years)
                    ->where(function ($s) use ($months) {
                        $quarterMap = [
                            'Q1' => [1, 2, 3],
                            'Q2' => [4, 5, 6],
                            'Q3' => [7, 8, 9],
                            'Q4' => [10, 11, 12],
                        ];

                        $overlappingQuarters = [];
                        foreach ($months as $m) {
                            $monthNum = (int) Carbon::parse($m . '-01')->month;
                            foreach ($quarterMap as $qName => $qMonths) {
                                if (in_array($monthNum, $qMonths) && ! in_array($qName, $overlappingQuarters, true)) {
                                    $overlappingQuarters[] = $qName;
                                }
                            }
                        }

                        if (! empty($overlappingQuarters)) {
                            $s->whereIn('period_quarter', $overlappingQuarters);
                        }
                    });
            });
        });

        return $notesQuery->get(['id', 'imut_data_id', 'period_year', 'period_quarter', 'period_semester', 'period_type', 'analysis', 'recommendation', 'note_name']);
    }
}