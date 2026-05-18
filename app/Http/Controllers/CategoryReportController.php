<?php

namespace App\Http\Controllers;

use App\Models\ImutBenchmarking;
use App\Models\ImutData;
use App\Models\ImutPenilaian;
use App\Models\ImutProfile;
use App\Models\RegionType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\Reporting\CategoryAggregationService;
use App\Services\Support\PeriodParserService;

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
                $categories = array_filter(array_map('intval', explode(',', $categoryInput)));
                $periode = $request->input('periode', now()->format('Y'));

                try {
                    $aggregationService = app(CategoryAggregationService::class);
                    $aggregatedData = $aggregationService->aggregate($categories, $periode);
                } catch (\InvalidArgumentException $e) {
                    abort(400, $e->getMessage());
                }

                $startDate = $aggregatedData['startDate'];
                $endDate = $aggregatedData['endDate'];

        // first prepare list of months between start and end date
        $months = [];
        $cursor = $startDate->copy();
        while ($cursor->lte($endDate)) {
            $months[] = $cursor->format('Y-m');
            $cursor->addMonth();
        }

        // build same structure used elsewhere for views (value+label)
        $allMonths = collect($months)->map(function ($m) {
            return [
                'value' => $m,
                'label' => Carbon::parse($m . '-01')->translatedFormat('M Y'),
            ];
        })->toArray();

        // fetch penilaian records matching the categories and period
        // we rely on the laporan_imut.report_year/report_month fields instead of
        // the raw assessment_period dates; this is simpler and avoids the
        // "missing august" confusion when records span months.
        $yearMonthStrings = $months; // already in 'YYYY-MM' format

        $penilaians = ImutPenilaian::with(['profile.imutData', 'laporanUnitKerja.laporanImut'])
            ->whereHas('profile.imutData', fn($q) => $q->where('status', true))
            ->when(count($categories) > 0, fn($q) => $q->whereHas('profile.imutData.categories', fn($q2) => $q2->whereIn('id', $categories)))
            ->when(count($yearMonthStrings) > 0, function ($q) use ($yearMonthStrings, $startDate, $endDate) {
                $q->whereHas('laporanUnitKerja.laporanImut', function ($q2) use ($yearMonthStrings, $startDate, $endDate) {
                    $q2->whereIn(
                        \Illuminate\Support\Facades\DB::raw("CONCAT(report_year,'-',LPAD(report_month,2,'0'))"),
                        $yearMonthStrings
                    )
                        // if report_year/month not populated, fall back to raw period range
                        ->orWhereBetween('assessment_period_start', [$startDate, $endDate])
                        ->orWhereBetween('assessment_period_end', [$startDate, $endDate])
                        ->orWhere(function ($q3) use ($startDate, $endDate) {
                            $q3->where('assessment_period_start', '<=', $startDate)
                                ->where('assessment_period_end', '>=', $endDate);
                        });
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
            // Build filter criteria untuk notes berdasarkan periode yang sedang dilihat
            $notesQuery = \App\Models\ImutDataNote::active()
                ->whereIn('imut_data_id', $imutIds);

            // Determine period filtering based on $periode format
            $yearStart = $startDate->year;
            $yearEnd = $endDate->year;
            $monthStart = $startDate->month;
            $monthEnd = $endDate->month;

            // Get unique years involved in the period
            $years = [];
            for ($y = $yearStart; $y <= $yearEnd; $y++) {
                $years[] = $y;
            }

            // Filter notes based on report period overlap
            $notesQuery->where(function ($q) use ($years, $monthStart, $monthEnd, $yearStart, $yearEnd, $months) {
                // Include annual notes that match any year in range
                $q->orWhere(function ($sub) use ($years) {
                    $sub->where('period_type', 'tahunan')
                        ->whereIn('period_year', $years);
                });

                // Include semester notes that overlap with report months
                $q->orWhere(function ($sub) use ($years, $months) {
                    $sub->where('period_type', 'semester')
                        ->whereIn('period_year', $years)
                        ->where(function ($s) use ($months) {
                            // S1: Jan-Jun (months 1-6)
                            // S2: Jul-Des (months 7-12)
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

                // Include quarter notes that overlap with report months
                $q->orWhere(function ($sub) use ($years, $months) {
                    $sub->where('period_type', 'triwulan')
                        ->whereIn('period_year', $years)
                        ->where(function ($s) use ($months) {
                            // Q1: Jan-Mar (1-3), Q2: Apr-Jun (4-6), Q3: Jul-Sep (7-9), Q4: Oct-Des (10-12)
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

            // // Debug: lihat query dan hasil
            // dd([
            //     'imutIds' => $imutIds,
            //     'years' => $years,
            //     'months' => $months,
            //     'sql' => $notesQuery->toSql(),
            //     'bindings' => $notesQuery->getBindings(),
            //     'rawNotes' => $notesQuery->get(['id', 'imut_data_id', 'period_year', 'period_quarter', 'period_semester', 'period_type', 'analysis', 'recommendation', 'note_name'])->toArray(),
            //     'allData' => \App\Models\ImutDataNote::active()->whereIn('imut_data_id', $imutIds)->get(['id', 'imut_data_id', 'period_year', 'period_quarter', 'period_semester', 'period_type', 'analysis', 'recommendation', 'note_name'])->toArray(),
            // ]);

            $rawNotes = $notesQuery->get(['id', 'imut_data_id', 'period_year', 'period_quarter', 'period_semester', 'period_type', 'analysis', 'recommendation', 'note_name']);

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
                } elseif ($arr['period_type'] === 'semester') {
                    $semesterNames = ['S1' => 'Semester I', 'S2' => 'Semester II'];
                    $arr['period_label'] = ($semesterNames[$arr['period_semester']] ?? $arr['period_semester']) . ' ' . $arr['period_year'];
                    // compute semester months
                    $arr['months'] = [];
                    $monthsRange = [];
                    if ($arr['period_semester'] === 'S1') {
                        $monthsRange = [1, 2, 3, 4, 5, 6];
                    } elseif ($arr['period_semester'] === 'S2') {
                        $monthsRange = [7, 8, 9, 10, 11, 12];
                    }
                    foreach ($monthsRange as $mth) {
                        $arr['months'][] = sprintf('%04d-%02d', $arr['period_year'], $mth);
                    }
                } else {
                    // triwulan
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

            // collect unique units for this imut from all penilaians
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
            $units = array_values($units); // reset keys

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
                'units' => $units,
            ];

            if ($request->boolean('debug_notes')) {
                dd($dataByImut);
            }
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

        // compute achieved indicators based on final monthly values in dataByImut
        $achievedCount = 0;
        foreach ($dataByImut as $imut) {
            $overall = 0;
            $std = $imut['standard'] ?? null;
            $op  = $imut['target_operator'] ?? '>=';
            if ($std !== null) {
                // calculate overall from data (sum N/D)
                $totalN = array_sum(array_column($imut['data'], 'numerator'));
                $totalD = array_sum(array_column($imut['data'], 'denominator'));
                $overall = $totalD > 0 ? ($totalN / $totalD) * 100 : 0;
                switch ($op) {
                    case '<=':
                        $ach = $overall <= $std;
                        break;
                    case '>':
                        $ach = $overall > $std;
                        break;
                    case '<':
                        $ach = $overall < $std;
                        break;
                    case '==':
                    case '=':
                        $ach = $overall == $std;
                        break;
                    case '>=':
                    default:
                        $ach = $overall >= $std;
                        break;
                }
                if ($ach) {
                    $achievedCount++;
                }
            }
        }

        $summary = [
            'total_indicators'  => count($results),
            'total_numerator'   => array_sum(array_column($results, 'numerator')),
            'total_denominator' => array_sum(array_column($results, 'denominator')),
            'average_percentage' => count($results) ? array_sum(array_column($results, 'percentage')) / count($results) : 0,
            'achieved_count' => $achievedCount,
        ];


        // resolve names for display and load descriptions
        $categoryNames = [];
        $categoryDetails = collect();
        if (count($categories) > 0) {
            $categoryDetails = \App\Models\ImutCategory::whereIn('id', $categories)
                ->get(['id', 'category_name', 'description', 'scope']);
            $categoryNames = $categoryDetails->pluck('category_name')->toArray();
        }

        // Resolve Tim Mutu users for left signer dropdown ("Mengetahui")
        $signatoryService = app(\App\Services\Support\SignatoryService::class);
        $timMutuUsersData = [];
        $defaultLeftSignerIndex = 0;
        try {
            if (class_exists(\Spatie\Permission\Models\Role::class) && \Spatie\Permission\Models\Role::where('name', 'tim_mutu')->exists()) {
                $timMutuUsers = User::role('tim_mutu')->orderBy('name')->get();
                $timMutuUsersData = $timMutuUsers->map(fn($u) => [
                    'id'      => $u->id,
                    'name'    => $u->name,
                    'ttd_url' => $signatoryService->getTtdUrl($u),
                ])->values()->toArray();
                foreach ($timMutuUsersData as $i => $u) {
                    if (stripos($u['name'], 'yogi') !== false) {
                        $defaultLeftSignerIndex = $i;
                        break;
                    }
                }
            }
        } catch (\Throwable $e) {
            $timMutuUsersData = [];
        }

        // Right signer: currently logged-in user ("Penanggung Jawab")
        $authUser = auth()->user();
        $rightSignerData = [
            'id'      => $authUser?->id,
            'name'    => $authUser?->name ?? '(...........................)',
            'ttd_url' => $authUser ? $signatoryService->getTtdUrl($authUser) : null,
        ];

        return view('reports.category-laporan', [
            'results'                => $results,
            'summary'                => $summary,
            'periode'                => $periode,
            'categories'             => $categories,
            'categoryNames'          => $categoryNames,
            'categoryDetails'        => $categoryDetails,
            'dataByImut'             => $dataByImut,
            'chartData'              => $chartData,
            'notesMap'               => $notesMap,
            'allMonths'              => $allMonths,
            'timMutuUsersData'       => $timMutuUsersData,
            'defaultLeftSignerIndex' => $defaultLeftSignerIndex,
            'rightSignerData'        => $rightSignerData,
        ]);
    }
}
