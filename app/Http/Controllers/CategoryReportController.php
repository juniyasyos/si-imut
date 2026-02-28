<?php

namespace App\Http\Controllers;

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\ImutProfile;
use App\Models\RegionType;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CategoryReportController extends Controller
{
    /**
     * Display aggregated indicator report for selected categories and period.
     *
     * Query parameters:
     * - categories: comma-separated list of ImutCategory IDs
     * - periode: Y-m (e.g. "2026-02")
     */
    public function show(Request $request)
    {
        $categoryInput = $request->input('categories', '');
        // parse comma-separated list and cast to integers (ignore empties)
        $categories = array_filter(array_map('intval', explode(',', $categoryInput)));

        $periode = $request->input('periode', now()->format('Y'));

        // calculate date range based on accepted formats:
        //  - YYYY                : whole year
        //  - YYYY-MM             : specific month
        //  - YYYY-Q[1-4]         : quarter
        //  - YYYY-S[1-2]         : semester
        //  - YYYY-MM,YYYY-MM     : custom range (start,end)
        //  - other -> error
        $startDate = null;
        $endDate = null;

        if (preg_match('/^\d{4}$/', $periode)) {
            // full year
            $year = intval($periode);
            $startDate = Carbon::createFromDate($year, 1, 1)->startOfDay();
            $endDate = Carbon::createFromDate($year, 12, 31)->endOfDay();
        } elseif (preg_match('/^(\d{4})-(\d{2})$/', $periode, $m)) {
            // month
            [$year, $month] = [intval($m[1]), intval($m[2])];
            if ($month < 1 || $month > 12) {
                abort(400, 'Bulan tidak valid dalam parameter periode');
            }
            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = (clone $startDate)->endOfMonth();
        } elseif (preg_match('/^(\d{4})-Q([1-4])$/', $periode, $m)) {
            // quarter
            $year = intval($m[1]);
            $quarter = intval($m[2]);
            $monthStart = ($quarter - 1) * 3 + 1;
            $startDate = Carbon::createFromDate($year, $monthStart, 1)->startOfMonth();
            $endDate = (clone $startDate)->addMonths(2)->endOfMonth();
        } elseif (preg_match('/^(\d{4})-S([12])$/', $periode, $m)) {
            // semester
            $year = intval($m[1]);
            $sem = intval($m[2]);
            $monthStart = $sem === 1 ? 1 : 7;
            $startDate = Carbon::createFromDate($year, $monthStart, 1)->startOfMonth();
            $endDate = (clone $startDate)->addMonths(5)->endOfMonth();
        } elseif (strpos($periode, ',') !== false) {
            // custom range
            [$p1, $p2] = explode(',', $periode, 2);
            try {
                $startDate = Carbon::parse($p1)->startOfMonth();
                $endDate = Carbon::parse($p2)->endOfMonth();
            } catch (\Exception $e) {
                abort(400, 'Format custom periode tidak valid');
            }
        }

        if (! $startDate || ! $endDate) {
            abort(400, 'Parameter periode tidak valid');
        }

        // first prepare list of months between start and end date
        $months = [];
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $months[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        // fetch penilaian records matching the categories and period
        // we rely on the laporan_imut.report_year/report_month fields instead of
        // the raw assessment_period dates; this is simpler and avoids the
        // "missing august" confusion when records span months.
        $yearMonthStrings = $months; // already in 'YYYY-MM' format

        $penilaians = ImutPenilaian::with(['profile.imutData', 'laporanUnitKerja.laporanImut'])
            ->when(count($categories) > 0, fn($q) => $q->whereHas('profile.imutData.categories', fn($q2) => $q2->whereIn('id', $categories)))
            ->when(count($yearMonthStrings) > 0, function ($q) use ($yearMonthStrings) {
                $q->whereHas('laporanUnitKerja.laporanImut', function ($q2) use ($yearMonthStrings) {
                    $q2->whereIn(


                        \Illuminate\Support\Facades\DB::raw("CONCAT(report_year,'-',LPAD(report_month,2,'0'))"),
                        $yearMonthStrings
                    );
                });
            })
            ->get();

        // group penilaians by indicator and by month of the laporan
        // use the report_year/report_month combination (falling back to
        // assessment_period_end if the former is missing) so that we remain
        // consistent with filtering above.
        $grouped = [];
        foreach ($penilaians as $p) {
            $imutDataId = $p->profile->imut_data_id;
            $laporan = $p->laporanUnitKerja->laporanImut;
            if ($laporan) {
                if ($laporan->report_year && $laporan->report_month) {
                    $m = sprintf('%04d-%02d', $laporan->report_year, $laporan->report_month);
                } else {
                    $dateForLabel = $laporan->assessment_period_end ?: $laporan->assessment_period_start;
                    $m = Carbon::parse($dateForLabel)->format('Y-m');
                }
            } else {
                $m = null;
            }
            $grouped[$imutDataId][$m][] = $p;
        }

        // build summary results and monthly data
        // since standard/operator may change each month, we will compute
        // row-level values later when building $dataByImut; here we just
        // gather basic indicator metadata and overall totals.
        $results = [];
        foreach ($grouped as $imutDataId => $itemsByMonth) {
            $allItems = collect($itemsByMonth)->flatten(1);
            $imutData = $allItems->first()->profile->imutData;
            $overallNumerator = $allItems->sum('numerator_value');
            $overallDenominator = $allItems->sum('denominator_value');
            $overallPercentage = $overallDenominator > 0 ? ($overallNumerator / $overallDenominator) * 100 : 0;

            $results[] = [
                'imut_data_id' => $imutDataId,
                'title'        => $imutData->title ?? '- tanpa judul -',
                'numerator'    => $overallNumerator,
                'denominator'  => $overallDenominator,
                'percentage'   => $overallPercentage,
                'category'     => $imutData->categories?->category_name,
            ];
        }

        // prepare monthly breakdown array for view/chart
        // fetch notes once per indicator to avoid N+1
        $notesMap = [];
        $imutIds = array_column($results, 'imut_data_id');
        if (count($imutIds) > 0) {
            $rawNotes = \App\Models\ImutDataNote::active()
                ->whereIn('imut_data_id', $imutIds)
                ->get(['id', 'imut_data_id', 'period_year', 'period_quarter', 'period_type', 'analysis', 'recommendation', 'note_name']);
            foreach ($rawNotes as $n) {
                $key = $n->imut_data_id;
                $arr = $n->toArray();
                // compute human period label for the note
                if ($arr['period_type'] === 'tahunan') {
                    $arr['period_label'] = "{$arr['period_year']}";
                    // months: all months of year
                    $arr['months'] = [];
                    for ($i = 1; $i <= 12; $i++) {
                        $arr['months'][] = sprintf('%04d-%02d', $arr['period_year'], $i);
                    }
                } else {
                    $quarterNames = ['Q1' => 'Triwulan I', 'Q2' => 'Triwulan II', 'Q3' => 'Triwulan III', 'Q4' => 'Triwulan IV'];
                    $arr['period_label'] = ($quarterNames[$arr['period_quarter']] ?? $arr['period_quarter']) . ' ' . $arr['period_year'];
                    // compute quarter months
                    $arr['months'] = [];
                    switch ($arr['period_quarter']) {
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
                        default:
                            $monthsRange = [];
                    }
                    foreach ($monthsRange as $mth) {
                        $arr['months'][] = sprintf('%04d-%02d', $arr['period_year'], $mth);
                    }
                }
                $notesMap[$key][] = $arr;
            }
        }

        $dataByImut = [];
        foreach ($results as $row) {
            $imutId = $row['imut_data_id'];
            $imutItems = $grouped[$imutId] ?? [];
            $monthly = [];
            $lastStandard = null;
            $lastOperator = null;
            $imutId = $row['imut_data_id'];
            $imutItems = $grouped[$imutId] ?? [];
            $monthly = [];
            $lastStandard = null;
            $lastOperator = null;

            foreach ($months as $m) {
                $items = collect($imutItems[$m] ?? []);
                $num = $items->sum('numerator_value');
                $den = $items->sum('denominator_value');
                $perc = $den > 0 ? ($num / $den) * 100 : 0;

                // determine standard/operator for this month from first item
                $std = null;
                $op = null;
                if ($items->isNotEmpty()) {
                    $firstProfile = $items->first()->profile;
                    $std = $firstProfile?->target_value;
                    $op  = $firstProfile?->target_operator;
                }

                // if we still don't have a standard, try to inherit previous
                if ($std === null) {
                    $std = $lastStandard;
                    $op = $lastOperator;
                }

                $lastStandard = $std;
                $lastOperator = $op;

                // compute status based on operator
                $status = 'no-data';
                if ($std !== null) {
                    switch ($op) {
                        case '<=':
                            $status = $perc <= $std ? 'achieved' : 'not-achieved';
                            break;
                        case '>':
                            $status = $perc > $std ? 'achieved' : 'not-achieved';
                            break;
                        case '<':
                            $status = $perc < $std ? 'achieved' : 'not-achieved';
                            break;
                        case '==':
                            $status = $perc == $std ? 'achieved' : 'not-achieved';
                            break;
                        case '>=':
                        default:
                            $status = $perc >= $std ? 'achieved' : 'not-achieved';
                    }
                } else {
                    $status = $den > 0 ? 'not-achieved' : 'no-data';
                }

                $monthly[] = [
                    'month_label' => $m,
                    'numerator' => $num,
                    'denominator' => $den,
                    'percentage' => $perc,
                    'status' => $status,
                    'standard' => $std,
                    'operator' => $op,
                    'analysis' => null,
                    'recommendations' => null,
                ];
            }

            // update row-level standard/operator using last known values
            $rowStandard = $lastStandard;
            $rowOperator = $lastOperator;

            // collect region type metadata and benchmarking values so view can render them
            $rtIds = RegionType::where('imut_data_id', $imutId)->pluck('id')->toArray();
            $regionTypeInfo = RegionType::whereIn('id', $rtIds)
                ->get(['id', 'type', 'chart_type', 'display_color'])
                ->map(function ($rt) {
                    return [
                        'id' => $rt->id,
                        'type' => $rt->type,
                        'default_region_name' => $rt->getDefaultRegionName(),
                        'chart_type' => $rt->getChartTypeWithFallback(),
                        'color' => $rt->getDisplayColorWithFallback(),
                    ];
                })
                ->toArray();

            // when building benchmarks we also want a per-month series
            $benchmarks = ImutBenchmarking::whereHas('regionType', fn($q) => $q->where('imut_data_id', $imutId))
                ->get()
                ->map(function ($b) use ($months) {
                    $val = floatval($b->benchmark_value);
                    // fill monthly values only for months within period range
                    $series = [];
                    foreach ($months as $m) {
                        $carbon = Carbon::createFromFormat('Y-m', $m)->startOfMonth();
                        $start = Carbon::parse($b->period_start)->startOfMonth();
                        $end = Carbon::parse($b->period_end)->endOfMonth();
                        $series[$m] = $carbon->betweenIncluded($start, $end) ? $val : null;
                    }

                    return [
                        'id' => $b->id,
                        'region_type_id' => $b->region_type_id,
                        'value' => $val,
                        'period_start' => $b->period_start,
                        'period_end' => $b->period_end,
                        'monthly' => $series,
                    ];
                })
                ->toArray();

            $dataByImut[] = [
                'id' => $imutId,
                'title' => $row['title'],
                'category' => $row['category'] ?? null,
                'target_operator' => $rowOperator,
                'standard' => $rowStandard,
                'data' => $monthly,
                'regionTypes' => $rtIds,
                'regionTypesInfo' => $regionTypeInfo,
                'benchmarks' => $benchmarks,
                'notes' => $notesMap[$imutId] ?? [],
            ];
        }

        // prepare chart data for each indicator
        $chartData = [];
        foreach ($dataByImut as $imut) {
            $labels = array_column($imut['data'], 'month_label');
            $baseDataset = [
                'label' => 'Persentase',
                'data' => array_column($imut['data'], 'percentage'),
                'borderColor' => '#3b82f6',
                'backgroundColor' => 'rgba(59, 130, 246, 0.3)',
                'fill' => true,
            ];

            $standardDataset = [
                'label' => 'Standar',
                'data' => array_column($imut['data'], 'standard'),
                'borderColor' => '#9ca3af',
                'borderDash' => [6, 6],
                'fill' => false,
            ];

            $datasets = [$baseDataset, $standardDataset];

            // add benchmark lines for each region type if available (merge multiple periods)
            if (!empty($imut['benchmarks']) && !empty($imut['regionTypesInfo'])) {
                $grouped = collect($imut['benchmarks'])->groupBy('region_type_id');
                foreach ($grouped as $rtid => $items) {
                    $rtInfo = collect($imut['regionTypesInfo'])->firstWhere('id', $rtid);
                    $rtLabel = $rtInfo['type'] ?? 'Benchmark';
                    $color = $rtInfo['color'] ?? '#6b7280';

                    // build line data by picking first non-null monthly value within the group for each month
                    $lineData = [];
                    foreach ($labels as $m) {
                        $val = null;
                        foreach ($items as $bm) {
                            if (isset($bm['monthly'][$m]) && $bm['monthly'][$m] !== null) {
                                $val = $bm['monthly'][$m];
                                break;
                            }
                        }
                        if ($val === null) {
                            // fallback to the group's first value
                            $val = $items->first()['value'] ?? 0;
                        }
                        $lineData[] = $val;
                    }

                    $datasets[] = [
                        'label' => 'Benchmark ' . $rtLabel,
                        'rtid'  => $rtid,
                        'data' => $lineData,
                        'borderColor' => $color,
                        'borderDash' => [4, 4],
                        'fill' => false,
                    ];
                }
            }

            $chartData['chart-' . $imut['id']] = [
                'labels' => $labels,
                'datasets' => $datasets,
            ];
        }

        $summary = [
            'total_indicators'  => count($results),
            'total_numerator'   => array_sum(array_column($results, 'numerator')),
            'total_denominator' => array_sum(array_column($results, 'denominator')),
            'average_percentage' => count($results) ? array_sum(array_column($results, 'percentage')) / count($results) : 0,
        ];


        // resolve names for display and load descriptions
        $categoryNames = [];
        $categoryDetails = collect();
        if (count($categories) > 0) {
            $categoryDetails = \App\Models\ImutCategory::whereIn('id', $categories)
                ->get(['category_name', 'description']);
            $categoryNames = $categoryDetails->pluck('category_name')->toArray();
        }

        return view('reports.category-laporan', [
            'results' => $results,
            'summary' => $summary,
            'periode' => $periode,
            'categories' => $categories,
            'categoryNames' => $categoryNames,
            'categoryDetails' => $categoryDetails,
            'dataByImut' => $dataByImut,
            'chartData' => $chartData,
            'notesMap' => $notesMap,
        ]);
    }
}
