<?php

namespace App\Services;

use App\Models\ImutDataNote;
use App\Models\ImutPenilaian;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk membangun detailed report data (monthly breakdown, chart data, notes)
 */
class CategoryReportDataBuilderService
{
    /**
     * Build detailed data untuk setiap indicator dengan monthly breakdown
     */
    public function buildMonthlyBreakdown(
        array $grouped,
        array $results,
        array $months
    ): array {
        $dataByImut = [];

        foreach ($results as $row) {
            $imutId = $row['imut_data_id'];
            $imutItems = $grouped[$imutId] ?? [];
            $monthly = [];
            $lastStandard = null;
            $lastOperator = null;

            // collect unique units untuk indicator ini
            $units = [];
            if (isset($grouped[$imutId])) {
                foreach ($grouped[$imutId] as $monthItems) {
                    foreach ($monthItems as $penilaian) {
                        $unitKerja = $penilaian->laporanUnitKerja?->unitKerja;
                        if ($unitKerja && !isset($units[$unitKerja->id])) {
                            $units[$unitKerja->id] = $unitKerja->unit_name;
                        }
                    }
                }
            }
            $units = array_values($units);

            // Build monthly data
            foreach ($months as $m) {
                $items = collect($imutItems[$m] ?? []);
                $num = $items->sum('numerator_value');
                $den = $items->sum('denominator_value');
                $perc = $den > 0 ? ($num / $den) * 100 : 0;

                $std = null;
                $op = null;
                if ($items->isNotEmpty()) {
                    $firstProfile = $items->first()->profile;
                    $std = $firstProfile?->target_value;
                    $op = $firstProfile?->target_operator;
                }

                if ($std === null) {
                    $std = $lastStandard;
                    $op = $lastOperator;
                }

                $lastStandard = $std;
                $lastOperator = $op;

                // Compute status
                $status = $this->computeStatus($perc, $std, $op, $den);

                $monthly[] = [
                    'month' => $m,
                    'month_label' => Carbon::parse($m . '-01')->translatedFormat('M Y'),
                    'numerator' => $num,
                    'denominator' => $den,
                    'percentage' => $perc,
                    'standard' => $std,
                    'operator' => $op,
                    'status' => $status,
                    'units_filled' => $items->count(),
                    'units_total' => count($units),
                ];
            }

            $dataByImut[] = [
                'imut_data' => $row,
                'units' => $units,
                'monthly' => $monthly,
            ];
        }

        return $dataByImut;
    }

    /**
     * Build chart data untuk month-to-month trend
     */
    public function buildChartData(array $grouped, array $results, array $months): array
    {
        $series = [];

        foreach ($results as $row) {
            $imutId = $row['imut_data_id'];
            $imutItems = $grouped[$imutId] ?? [];
            $data = [];

            foreach ($months as $m) {
                $items = collect($imutItems[$m] ?? []);
                $num = $items->sum('numerator_value');
                $den = $items->sum('denominator_value');
                $perc = $den > 0 ? ($num / $den) * 100 : 0;
                $data[] = round($perc, 2);
            }

            $series[] = [
                'name' => $row['title'],
                'data' => $data,
                'imut_data_id' => $imutId,
            ];
        }

        return [
            'categories' => array_map(fn($m) => Carbon::parse($m . '-01')->translatedFormat('M'), $months),
            'series' => $series,
        ];
    }

    /**
     * Fetch dan organize notes untuk indicators
     */
    public function fetchAndOrganizeNotes(array $imutIds, array $months, Carbon $startDate, Carbon $endDate): array
    {
        $notesMap = [];

        if (count($imutIds) === 0) {
            return $notesMap;
        }

        $notesQuery = ImutDataNote::active()
            ->whereIn('imut_data_id', $imutIds);

        $yearStart = $startDate->year;
        $yearEnd = $endDate->year;

        $years = [];
        for ($y = $yearStart; $y <= $yearEnd; $y++) {
            $years[] = $y;
        }

        // Filter notes berdasarkan period overlap
        $notesQuery->where(function ($q) use ($years, $months) {
            // Annual notes
            $q->orWhere(function ($sub) use ($years) {
                $sub->where('period_type', 'tahunan')
                    ->whereIn('period_year', $years);
            });

            // Semester notes
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

            // Quarter notes
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
                                if (in_array($monthNum, $qMonths) && !in_array($qName, $overlappingQuarters)) {
                                    $overlappingQuarters[] = $qName;
                                }
                            }
                        }

                        if (!empty($overlappingQuarters)) {
                            $s->whereIn('period_quarter', $overlappingQuarters);
                        }
                    });
            });
        });

        $rawNotes = $notesQuery->get(['id', 'imut_data_id', 'period_year', 'period_quarter', 'period_semester', 'period_type', 'analysis', 'recommendation', 'note_name']);

        foreach ($rawNotes as $n) {
            $key = $n->imut_data_id;
            $arr = $n->toArray();
            $arr['period_label'] = $this->computePeriodLabel($arr);
            $arr['months'] = $this->computeNotePeriodMonths($arr);

            if (!isset($notesMap[$key])) {
                $notesMap[$key] = [];
            }
            $notesMap[$key][] = $arr;
        }

        return $notesMap;
    }

    /**
     * Compute human-readable period label untuk note
     */
    private function computePeriodLabel(array $note): string
    {
        if ($note['period_type'] === 'tahunan') {
            return "{$note['period_year']}";
        } elseif ($note['period_type'] === 'semester') {
            $semesterNames = ['S1' => 'Semester I', 'S2' => 'Semester II'];
            return ($semesterNames[$note['period_semester']] ?? $note['period_semester']) . ' ' . $note['period_year'];
        } else {
            // quarter
            $quarterNames = ['Q1' => 'Triwulan I', 'Q2' => 'Triwulan II', 'Q3' => 'Triwulan III', 'Q4' => 'Triwulan IV'];
            return ($quarterNames[$note['period_quarter']] ?? $note['period_quarter']) . ' ' . $note['period_year'];
        }
    }

    /**
     * Compute months covered by note period
     */
    private function computeNotePeriodMonths(array $note): array
    {
        $months = [];
        $monthsRange = [];

        if ($note['period_type'] === 'tahunan') {
            $monthsRange = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        } elseif ($note['period_type'] === 'semester') {
            $monthsRange = $note['period_semester'] === 'S1' ? [1, 2, 3, 4, 5, 6] : [7, 8, 9, 10, 11, 12];
        } else {
            // quarter
            switch ($note['period_quarter']) {
                case 'Q1':
                    $monthsRange = [1, 2, 3];
                    break;
                case 'Q2':
                    $monthsRange = [4, 5, 6];
                    break;
                case 'Q3':
                    $monthsRange = [7, 8, 9];
                    break;
                case 'Q4':
                    $monthsRange = [10, 11, 12];
                    break;
            }
        }

        foreach ($monthsRange as $mth) {
            $months[] = sprintf('%04d-%02d', $note['period_year'], $mth);
        }

        return $months;
    }

    /**
     * Compute achievement summary
     */
    public function computeAchievementSummary(array $grouped, array $results): int
    {
        $achievedCount = 0;

        foreach ($results as $row) {
            $imutId = $row['imut_data_id'];
            $imutItems = $grouped[$imutId] ?? [];

            $allItems = collect($imutItems)->flatten(1);
            $totalN = $allItems->sum('numerator_value');
            $totalD = $allItems->sum('denominator_value');
            $overall = $totalD > 0 ? ($totalN / $totalD) * 100 : 0;

            $std = null;
            $op = null;

            if ($allItems->isNotEmpty()) {
                $firstProfile = $allItems->first()->profile;
                $std = $firstProfile?->target_value;
                $op = $firstProfile?->target_operator;
            }

            if ($std !== null) {
                $ach = match ($op) {
                    '<=' => $overall <= $std,
                    '>' => $overall > $std,
                    '<' => $overall < $std,
                    '=', '==' => $overall == $std,
                    default => $overall >= $std,
                };

                if ($ach) {
                    $achievedCount++;
                }
            }
        }

        return $achievedCount;
    }

    /**
     * Compute status berdasarkan operator dan standard
     */
    private function computeStatus(float $percentage, ?float $standard, ?string $operator, float $denominator): string
    {
        if ($standard === null) {
            return $denominator > 0 ? 'not-achieved' : 'no-data';
        }

        return match ($operator) {
            '<=' => $percentage <= $standard ? 'achieved' : 'not-achieved',
            '>' => $percentage > $standard ? 'achieved' : 'not-achieved',
            '<' => $percentage < $standard ? 'achieved' : 'not-achieved',
            '=', '==' => $percentage == $standard ? 'achieved' : 'not-achieved',
            default => $percentage >= $standard ? 'achieved' : 'not-achieved',
        };
    }
}
