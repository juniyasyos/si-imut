<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kategori Indikator - {{ implode(', ', $categoryNames ?: $categories) }}</title>
    <link rel="stylesheet" href="{{ asset('css/print-report.css') }}">
    {{-- alpine for interactive notes checkbox --}}
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    {{-- chart.js and its plugins normally bundled via Vite, but ensure CDN fallback for PWA cached version --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
    @vite(['resources/css/app.css'])
    <style>
        html, body {
            overflow-y: scroll;
            scroll-behavior: smooth;
            scrollbar-color: #94a3b8 #e2e8f0;
            scrollbar-width: thin;
            scrollbar-gutter: stable both-edges;
        }

        body::-webkit-scrollbar {
            width: 12px;
        }

        body::-webkit-scrollbar-track {
            background: #e2e8f0;
        }

        body::-webkit-scrollbar-thumb {
            background-color: #64748b;
            border-radius: 9999px;
            border: 3px solid #e2e8f0;
        }

        body::-webkit-scrollbar-thumb:hover {
            background-color: #475569;
        }

        .scroll-tip {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 60;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1rem;
            border-radius: 9999px;
            background: rgba(15, 23, 42, 0.9);
            color: #f8fafc;
            font-size: 0.85rem;
            box-shadow: 0 20px 45px rgba(15, 23, 42, 0.18);
        }

        .scroll-tip span:last-child {
            opacity: 0.9;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white;
                transform: scale(0.8);
                transform-origin: top left;
                width: 125%;
            }

            .imut-section {
                page-break-inside: avoid;
                border-top: 2px solid #cbd5e1;
                border-left: none;
                border-right: none;
                border-bottom: none;
                margin-bottom: 20px;
            }

            .imut-data-row {
                grid-template-columns: 1fr;
                gap: 15px;
            }

            .imut-chart-wrapper {
                min-height: 200px;
            }
        }
    </style>
</head>

@php
$periodLabel = $periode;
$cats = $categoryNames ?: $categories;

// build map of {imutId: [region type names]} for toggles per-indikator
$imutBenchmarkTypes = [];
if(!empty($dataByImut)) {
foreach($dataByImut as $imut) {
$types = collect($imut['regionTypesInfo'] ?? [])->pluck('type')->filter()->unique()->values()->all();
if(count($types)) {
$imutBenchmarkTypes[$imut['id']] = $types;
}
}
}
@endphp

<body x-data='categoryReport(@json($imutBenchmarkTypes ?? []), @json($timMutuUsersData), {{ $defaultLeftSignerIndex }}, @json($rightSignerData))' class="bg-white text-gray-800 font-sans text-sm leading-relaxed">

    <!-- Action Buttons -->
    <div class="no-print my-6 max-w-full mx-auto space-y-3">
        <!-- Print Options -->
        <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="text-sm font-medium text-amber-900">🖨️ Opsi Print:</div>
                <div class="flex items-center gap-2">
                    <input type="radio" id="orientation-landscape" name="printOrientation" value="landscape" checked @change="printOrientation = $event.target.value">
                    <label for="orientation-landscape" class="text-sm text-amber-800 cursor-pointer">Landscape</label>
                </div>
                <div class="flex items-center gap-2">
                    <input type="radio" id="orientation-portrait" name="printOrientation" value="portrait" @change="printOrientation = $event.target.value">
                    <label for="orientation-portrait" class="text-sm text-amber-800 cursor-pointer">Portrait</label>
                </div>
            </div>
            <!-- Dropdown penanda tangan Mengetahui -->
            <div class="flex items-center gap-3" x-show="timMutuUsers.length > 0">
                <span class="text-sm font-medium text-amber-900">✍️ Mengetahui:</span>
                <select x-model.number="selectedLeftSignerIndex"
                    class="text-sm border border-amber-300 rounded-lg px-3 py-1.5 bg-white text-amber-900 focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <template x-for="(user, idx) in timMutuUsers" :key="user.id">
                        <option :value="idx" x-text="user.name"></option>
                    </template>
                </select>
            </div>
        </div>

        <!-- Control Panel -->
        <div class="flex flex-col gap-4">

            <!-- Bottom Action Bar -->
            <div class="flex flex-wrap items-center justify-end gap-4">
                <!-- Action Buttons -->
                <div class="flex items-center gap-3">

                    <a href="{{ url('/siimut/daily-report-entries') }}"
                        class="px-5 py-2.5 text-sm font-medium text-gray-700 bg-gray-100 
                       border border-gray-200 rounded-xl 
                       hover:bg-gray-200 transition">
                        Kembali
                    </a>

                    <button @click="fetchData()"
                        class="px-5 py-2.5 text-sm font-medium text-white 
                       bg-blue-600 rounded-xl 
                       hover:bg-blue-700 transition shadow-sm">
                        Refresh
                    </button>

                    <button @click="handlePrint()"
                        class="px-5 py-2.5 text-sm font-medium text-white 
                       bg-green-600 rounded-xl 
                       hover:bg-green-700 transition shadow-sm">
                        Cetak
                    </button>

                    <!-- <button @click="downloadPdf()"
                        class="px-5 py-2.5 text-sm font-medium text-white 
                       bg-indigo-600 rounded-xl 
                       hover:bg-indigo-700 transition shadow-sm">
                        PDF
                    </button> -->
                    `
                </div>

            </div>

        </div>
    </div>
    
    <!-- HEADER -->
    <x-basic-report-header
        title="Laporan Kategori Indikator Mutu"
        :additionalInfo="[
        ['label' => 'Kategori', 'value' => implode(', ', $cats)],
        ['label' => 'Periode', 'value' => $periodLabel],
        ['label' => 'Tanggal Cetak', 'value' => now()->translatedFormat('d F Y, H:i') . ' WIB']
    ]" />

    <!-- Loading State -->
    <div x-show="loading" x-cloak class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-8 rounded-lg shadow-xl">
            <div class="rounded-full h-12 w-12 border-b-4 border-blue-600 mx-auto mb-4" style="animation: spin 1s linear infinite;"></div>
            <p class="text-gray-700 font-medium">Memuat data...</p>
        </div>
    </div>

    <!-- Summary controller payload -->
    <div class="bg-gray-100 border border-gray-300 rounded p-4 mb-6 text-xs">
        <!-- DATA PER KATEGORI -->
        <div class="mt-10">

            @if($dataByImut && count($dataByImut) > 0)

            <!-- =============================== -->
            <!--        TABLE SUMMARY            -->
            <!-- =============================== -->
            <div class="mb-6 overflow-x-auto rounded-xl border border-gray-300 shadow-sm bg-white">
                <table class="min-w-full text-[11px] border-separate border-spacing-0" style="table-layout: fixed;">

                    @php
                    $lastMonth = last($allMonths)['label'] ?? null;
                    $grouped = collect($dataByImut)->groupBy('category');
                    // check if any category has scope = 'unit'
                    $hasUnitScope = collect($categoryDetails)->contains(fn($cat) => $cat->scope === 'unit');
                    $baseColspan = 3 + count($allMonths) * 3;
                    $colspan = $hasUnitScope ? $baseColspan + 1 : $baseColspan;
                    $counter = 0;
                    @endphp

                    <!-- ================= HEADER ================= -->
                    <thead class="uppercase tracking-wide">

                        <!-- Header utama -->
                        <tr class="bg-slate-800 text-white text-[10px]">
                            <th class="px-2 py-2 text-center border-gray-700" style="width: 50px;">No</th>
                            <th class="px-3 py-2 text-left border-gray-700" style="width: 300px; word-wrap: break-word;">Indikator Mutu</th>
                            <th class="px-2 py-2 text-center border-gray-700" style="width: 100px;">Target</th>
                            @if($hasUnitScope)
                            <th class="px-2 py-2 text-left border-gray-700">Unit</th>
                            @endif

                            @foreach($allMonths as $month)
                            <th class="px-2 py-2 w-24 text-center border-l border-gray-700 bg-slate-600"
                                colspan="3">
                                {{ $month['label'] }}
                            </th>
                            @endforeach
                        </tr>

                        <!-- Sub header -->
                        <tr class="bg-slate-100 text-slate-600 text-[9px] font-semibold">
                            <th class="border-gray-300"></th>
                            <th class="border-gray-300"></th>
                            <th class="border-gray-300"></th>
                            @if($hasUnitScope)
                            <th class="border-gray-300"></th>
                            @endif

                            @foreach($allMonths as $month)
                            <th class="px-1 py-1 text-center border-l border-gray-200">N</th>
                            <th class="px-1 py-1 text-center border-gray-300">D</th>
                            <th class="px-1 py-1 text-center border-gray-300">%</th>
                            @endforeach
                        </tr>
                    </thead>

                    <!-- ================= BODY ================= -->
                    <tbody class="text-slate-700">

                        @foreach($grouped as $category => $items)

                        <!-- CATEGORY HEADER -->
                        <tr class="bg-slate-200 text-slate-800 font-semibold text-[11px]">
                            <td colspan="{{ $colspan }}"
                                class="px-3 py-2 border-y border-gray-300">
                                {{ $category }}
                            </td>
                        </tr>

                        @foreach($items as $imut)
                        @php
                        $counter++;
                        $map = collect($imut['data'] ?? [])->keyBy('month_label');

                        $operatorMap = ['>=' => '≥','<='=> '≤',    '==' => '=',    '>' => '>',    '<'=> '<', '!='=> '≠',
                                    ];

                                    $operator = $imut['target_operator'] ?? '>=';
                                    $symbol = $operatorMap[$operator] ?? $operator;
                                    $standard = $imut['standard'] ?? 0;
                                    @endphp

                                    <tr class="hover:bg-slate-50 transition">

                                        <!-- NO -->
                                        <td class="px-2 py-1.5 border-b border-gray-100 text-center font-medium">
                                            {{ $counter }}
                                        </td>

                                        <!-- INDIKATOR -->
                                        <td class="px-3 py-1.5 border-b border-gray-100" style="width: 300px; word-wrap: break-word; overflow-wrap: break-word; max-width: 300px;">
                                            {{ $imut['title'] }}
                                        </td>

                                        <!-- TARGET -->
                                        <td class="px-2 py-1.5 border-b border-gray-100 text-center">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md
                            bg-blue-50 text-blue-700 border border-blue-200
                            font-semibold text-[10px]">
                                                {{ $symbol }} {{ round($standard, 2) }}%
                                            </span>
                                        </td>

                                        @if($hasUnitScope)
                                        <td class="px-0.5 py-0.5 border-b border-gray-100 text-xs align-top max-w-xs">
                                            @if(!empty($imut['units']))
                                            <div class="flex flex-col gap-1">
                                                @foreach($imut['units'] as $unit)
                                                <span class="px-1 py-0 rounded text-[8px] font-medium whitespace-nowrap bg-purple-50 text-purple-700 border border-purple-200 truncate">
                                                    {{ $unit }}
                                                </span>
                                                @endforeach
                                            </div>
                                            @else
                                            <span class="text-gray-400 text-[10px]">-</span>
                                            @endif
                                        </td>
                                        @endif

                                        <!-- DATA BULAN -->
                                        @foreach($allMonths as $month)

                                        @php
                                        $lookup = $month['value'];
                                        $d = $map[$lookup] ?? null;

                                        $numerator = $d['numerator'] ?? 0;
                                        $denominator = $d['denominator'] ?? 0;
                                        $percent = $d['percentage'] ?? 0;

                                        // Evaluasi berdasarkan operator
                                        $isBelowTarget = false;

                                        switch ($operator) {
                                        case '>=':
                                        $isBelowTarget = $percent < $standard;
                                            break;
                                            case '<=' :
                                            $isBelowTarget=$percent> $standard;
                                            break;
                                            case '>':
                                            $isBelowTarget = $percent <= $standard;
                                                break;
                                                case '<' :
                                                $isBelowTarget=$percent>= $standard;
                                                break;
                                                case '==':
                                                $isBelowTarget = round($percent,2) != $standard;
                                                break;
                                                case '!=':
                                                $isBelowTarget = round($percent,2) == $standard;
                                                break;
                                                }

                                                $percentClass = $isBelowTarget
                                                ? 'bg-amber-100 text-amber-800 font-bold'
                                                : 'text-emerald-700 font-semibold';
                                                @endphp

                                                <td class="px-1 py-1.5 border-b border-gray-100 text-center border-l border-gray-100">
                                                    {{ number_format($numerator) }}
                                                </td>

                                                <td class="px-1 py-1.5 border-b border-gray-100 text-center">
                                                    {{ number_format($denominator) }}
                                                </td>

                                                <td class="px-1 py-1.5 border-b border-gray-100 text-right tabular-nums {{ $percentClass }}">
                                                    {{ round($percent, 2) }}%
                                                </td>

                                                @endforeach
                                    </tr>

                                    @endforeach
                                    @endforeach

                    </tbody>
                </table>
            </div>

            {{-- ================= INTERPRETASI HASIL ================= --}}
            @if(count($dataByImut) > 0)

            @php
            $total = count($dataByImut);
            $achieved = $summary['achieved_count'] ?? 0;
            $notAchieved = $total - $achieved;
            $rate = $total > 0 ? ($achieved / $total) * 100 : 0;

            if ($rate >= 85) {
            $level = "Sangat Baik";
            $color = "text-emerald-700";
            } elseif ($rate >= 70) {
            $level = "Baik";
            $color = "text-blue-700";
            } elseif ($rate >= 50) {
            $level = "Cukup";
            $color = "text-amber-700";
            } else {
            $level = "Perlu Perhatian";
            $color = "text-red-700";
            }
            @endphp

            <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-5 text-sm text-slate-700">

                <div class="font-semibold text-slate-800 mb-3 uppercase tracking-wide">
                    Interpretasi Kinerja Mutu
                </div>

                <div class="space-y-3 leading-relaxed">

                    {{-- Keterangan Warna --}}
                    <div class="flex flex-wrap gap-6 text-sm">
                        <div class="flex items-center gap-2">
                            <span class="inline-block w-3 h-3 bg-yellow-200 border border-yellow-300 rounded-sm"></span>
                            <span>Indikator belum mencapai target</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="inline-block w-3 h-3 bg-white border border-slate-300 rounded-sm"></span>
                            <span>Indikator telah memenuhi target</span>
                        </div>
                    </div>

                    {{-- Ringkasan Kinerja --}}
                    <div class="pt-3 border-t border-slate-200">

                        Dari <strong>{{ $total }}</strong> indikator mutu yang dinilai,
                        <strong class="text-emerald-700">{{ $achieved }}</strong> indikator
                        telah memenuhi standar dan
                        <strong class="text-yellow-700">{{ $notAchieved }}</strong>
                        indikator masih berada di bawah target.

                    </div>
                </div>
            </div>

            @endif

            @foreach($categoryDetails as $catIndex => $cat)

            @php
            $categoryName = $cat->category_name;
            $subset = collect($dataByImut)->where('category', $categoryName)->values();
            @endphp

            <!-- JUDUL KATEGORI -->
            <div class="mt-14 mb-8 break-inside-avoid">

                @php
                $isEven = $catIndex % 2 === 0;

                $wrapperBg = $isEven
                ? 'bg-blue-50 border-blue-200'
                : 'bg-emerald-50 border-emerald-200';

                $headerBg = $isEven
                ? 'bg-blue-600'
                : 'bg-emerald-600';

                $badgeBg = $isEven
                ? 'bg-blue-100 text-blue-700 border-blue-200'
                : 'bg-emerald-100 text-emerald-700 border-emerald-200';
                @endphp

                <div class="rounded-2xl border shadow-sm overflow-hidden {{ $wrapperBg }}">

                    <!-- HEADER -->
                    <div class="px-6 py-4 text-white {{ $headerBg }}">
                        <div class="flex items-center justify-between">

                            <div>
                                <p class="text-xs font-semibold tracking-widest uppercase text-white/80">
                                    Kategori Indikator Mutu
                                </p>

                                <h2 class="mt-1 text-2xl font-bold leading-tight">
                                    {{ $catIndex + 1 }}. {{ $categoryName }}
                                </h2>
                            </div>

                            @if(isset($subset) && count($subset) > 0)
                            <div class="px-4 py-2 text-sm font-semibold rounded-lg border {{ $badgeBg }}">
                                {{ count($subset) }} INDIKATOR
                            </div>
                            @endif

                        </div>
                    </div>

                </div>
            </div>

            @if($subset->isEmpty())
            <div class="p-4 text-slate-500">
                Tidak terdapat indikator pada kategori ini.
            </div>
            @else

            @foreach($subset as $index => $imut)

            <div x-data='imutNotes(@json($imut["notes"]), @json($imut["data"]))' class="mt-8 rounded-2xl border border-gray-200 shadow-sm bg-white overflow-hidden imut-section">

                {{-- ================= HEADER IMUT ================= --}}
                <div class="px-6 py-5 bg-gradient-to-r from-slate-800 to-slate-700 text-white">
                    <div class="flex items-start justify-between gap-6">

                        <div>
                            <p class="text-xs uppercase tracking-widest text-white/70 font-semibold">
                                Indikator Mutu
                            </p>

                            <h3 class="mt-1 text-xl font-bold leading-snug">
                                {{ $index + 1 }}. {{ $imut['title'] }}
                            </h3>
                        </div>

                        {{-- PANEL TARGET --}}
                        <div class="text-right">
                            <div class="text-xs text-white/70 uppercase tracking-wide">
                                Standar Target
                            </div>

                            @php
                            // check if standard varies across months
                            $uniqueStandards = collect($imut['data'])->pluck('standard')->filter()->unique();
                            if ($uniqueStandards->count() > 1) {
                            $displayStd = $uniqueStandards->last();
                            $displayOp = collect($imut['data'])->pluck('operator')->filter()->last();
                            $note = ' (bervariasi)';
                            } else {
                            $displayStd = $uniqueStandards->first();
                            $displayOp = $imut['target_operator'];
                            $note = '';
                            }
                            // convert operator for display
                            $rawOp = $displayOp ?? '';
                            switch ($rawOp) {
                            case '>=':
                            $opIcon = '≥';
                            break;
                            case '<=':
                                $opIcon='≤' ;
                                break;
                                case '==' :
                                $opIcon='≡' ;
                                break;
                                case '>' :
                                $opIcon='&gt;' ;
                                break;
                                case '<' :
                                $opIcon='&lt;' ;
                                break;
                                default:
                                $opIcon=$rawOp;
                                }
                                @endphp

                                <div class="mt-1 inline-flex items-center gap-2 bg-white/10 border border-white/20 px-4 py-2 rounded-lg">
                                <span class="text-sm font-semibold">
                                    {!! $opIcon !!} {{ $displayStd !== null ? number_format($displayStd,2) . '%' : '-' }}{{ $note ?? '' }}
                                </span>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ================= BODY ================= --}}
            <div class="p-6">

                @php
                // list of region-type names for this imut (may be empty)
                $typesForThis = collect($imut['regionTypesInfo'] ?? [])->pluck('type')->filter()->unique()->values()->all();
                @endphp
                <div class="no-print">
                    <h2>Filter Tampilan Benckmarking</h2>
                    <div class="mb-4 bg-white border border-gray-200 rounded-lg p-4">
                        <div class="flex flex-wrap gap-3">
                            @foreach($typesForThis as $type)
                            <label class="flex items-center gap-2 px-3 py-2 border border-gray-200 rounded-lg bg-gray-50 hover:bg-gray-100 cursor-pointer text-sm text-gray-700">
                                <input type="checkbox" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                                    x-model="showBenchmarkCols['{{ $imut['id'] }}']['{{ $type }}']"
                                    @change="updateCharts()">
                                <span>{{ $type }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">

                    {{-- ================= TABEL ================= --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-xs border-collapse" style="table-layout: fixed;">

                            {{-- ================= HEADER ================= --}}
                            <thead>
                                @php
                                $rtMap = collect($imut['regionTypesInfo'] ?? [])->keyBy('id');
                                $defaultBg = $rtMap->first()['color'] ?? '#1e40af';
                                $defaultText = '#ffffff';
                                $baseStyle = "background-color: $defaultBg; color: $defaultText;";

                                $benchmarkCols = collect($imut['benchmarks'] ?? [])
                                ->groupBy('region_type_id')
                                ->map(function($group, $rtid) use ($rtMap) {
                                return [
                                'region_type_id' => $rtid,
                                'type' => $rtMap[$rtid]['type'] ?? '',
                                'color' => $rtMap[$rtid]['color'] ?? '#000',
                                'records' => $group->all(),
                                ];
                                })->values();
                                @endphp

                                <tr class="text-[10px] uppercase tracking-wide">
                                    <th class="px-2 py-1 text-left" style="{{ $defaultBg }}; width: 120px;">Periode</th>
                                    @if($hasUnitScope && !empty($imut['units']))
                                    <th class="px-2 py-1 text-left" style="{{ $defaultBg }}; width: 150px;">Unit</th>
                                    @endif
                                    <th class="px-2 py-1 text-center" style="{{ $defaultBg }}; width: 60px;">N</th>
                                    <th class="px-2 py-1 text-center" style="{{ $defaultBg }}">D</th>
                                    <th class="px-2 py-1 text-center" style="{{ $defaultBg }}">Standar</th>
                                    <th class="px-2 py-1 text-right" style="{{ $defaultBg }}">%</th>
                                    <th class="px-2 py-1 text-center" style="{{ $defaultBg }}">Status</th>

                                    @foreach($benchmarkCols as $col)
                                    @php
                                    $bgcol = $col['color'] . '33';
                                    $thStyle = "background-color: $bgcol; color: #fff;";
                                    @endphp
                                    <th
                                        x-show="showBenchmarkCols['{{ $imut['id'] }}'] !== undefined && showBenchmarkCols['{{ $imut['id'] }}']['{{ $col['type'] }}'] === true"
                                        x-cloak
                                        class="px-2 py-1 text-center border-l border-gray-300"
                                        style="{{ $baseStyle }}">
                                        Bm {{ $col['type'] }}
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>

                            {{-- ================= BODY ================= --}}
                            <tbody class="text-xs text-slate-700 divide-y divide-slate-200">

                                @php
                                $totalN = 0;
                                $totalD = 0;
                                $dataMonths = 0;
                                @endphp

                                @foreach($imut['data'] as $dataPoint)
                                @php
                                if ($dataPoint['status'] !== 'no-data') {
                                $totalN += $dataPoint['numerator'];
                                $totalD += $dataPoint['denominator'];
                                $dataMonths++;
                                }

                                $opSym = match($dataPoint['operator'] ?? '') {
                                '>=' => '≥',
                                '<='=> '≤',
                                    '==' => '≡',
                                    '>' => '&gt;',
                                    '<'=> '&lt;',
                                        default => $dataPoint['operator'] ?? ''
                                        };
                                        @endphp

                                        <tr class="hover:bg-slate-50 transition">

                                            <td class="px-3 py-1 font-medium text-slate-800">
                                                {{ $dataPoint['month_label'] }}
                                            </td>

                                            @if($hasUnitScope && !empty($imut['units']))
                                            <td class="px-0.5 py-0.5 text-xs max-w-xs">
                                                <div class="flex flex-col gap-0.5">
                                                    @foreach($imut['units'] as $unit)
                                                    <span class="inline-flex items-center px-0.5 py-0 rounded text-[7px] font-medium bg-purple-50 text-purple-700 border border-purple-200 truncate">
                                                        {{ $unit }}
                                                    </span>
                                                    @endforeach
                                                </div>
                                            </td>
                                            @endif

                                            <td class="px-3 py-1 text-center">
                                                {{ number_format($dataPoint['numerator']) }}
                                            </td>

                                            <td class="px-3 py-1 text-center">
                                                {{ number_format($dataPoint['denominator']) }}
                                            </td>

                                            <td class="px-3 py-1 text-center bg-amber-50 text-amber-800 font-medium">
                                                {!! $opSym !!}
                                                {{ $dataPoint['standard'] !== null ? round($dataPoint['standard'], 2).'%' : '-' }}
                                            </td>

                                            <td class="px-3 py-1 text-right bg-blue-50 text-blue-800 font-semibold">
                                                {{ round($dataPoint['percentage'], 2) }}%
                                            </td>

                                            <td class="px-3 py-1 text-center">
                                                @if($dataPoint['status']==='achieved')
                                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-emerald-100 text-emerald-700">
                                                    Tercapai
                                                </span>
                                                @elseif($dataPoint['status']==='no-data')
                                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-slate-100 text-slate-600">
                                                    No Data
                                                </span>
                                                @else
                                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-red-100 text-red-700">
                                                    Belum
                                                </span>
                                                @endif
                                            </td>

                                            @foreach($benchmarkCols as $col)
                                            @php
                                            $bmVal = null;
                                            foreach($col['records'] as $bm) {
                                            $val = $bm['monthly'][$dataPoint['month_label']] ?? null;
                                            if ($val !== null) { $bmVal = $val; break; }
                                            }
                                            $cellBg = $col['color'] . '22';
                                            $tdStyle = "background-color: $cellBg; color: {$col['color']};";
                                            @endphp

                                            <td
                                                x-show="showBenchmarkCols['{{ $imut['id'] }}'] !== undefined && showBenchmarkCols['{{ $imut['id'] }}']['{{ $col['type'] }}'] === true"
                                                x-cloak
                                                class="px-3 py-1 text-center font-medium"
                                                style="{{ $tdStyle }}">
                                                {{ $bmVal !== null ? number_format($bmVal,2).'%' : '-' }}
                                            </td>
                                            @endforeach

                                        </tr>
                                        @endforeach

                                        {{-- ================= TOTAL ================= --}}
                                        @if($dataMonths > 0)
                                        @php
                                        $overall = $totalD > 0 ? ($totalN / $totalD) * 100 : 0;
                                        $isAchieved = $overall >= $imut['standard'];
                                        @endphp

                                        <tr class="bg-slate-100 font-semibold text-slate-900">

                                            <td class="px-3 py-3">Total / Rata-rata</td>
                                            @if($hasUnitScope && !empty($imut['units']))
                                            <td class="px-3 py-3"></td>
                                            @endif
                                            <td class="px-3 py-3 text-center">{{ number_format($totalN) }}</td>
                                            <td class="px-3 py-3 text-center">{{ number_format($totalD) }}</td>

                                            <td class="px-3 py-3 text-center bg-amber-100 text-amber-900">
                                                {!! $opSym !!} {{ round($imut['standard'], 2) }}%
                                            </td>

                                            <td class="px-3 py-3 text-right bg-blue-100 text-blue-900 font-bold">
                                                {{ number_format($overall,2) }}%
                                            </td>

                                            <td class="px-3 py-3 text-center">
                                                @if($isAchieved)
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-emerald-200 text-emerald-800">
                                                    Tercapai
                                                </span>
                                                @else
                                                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full bg-red-200 text-red-800">
                                                    Belum
                                                </span>
                                                @endif
                                            </td>

                                            @foreach($benchmarkCols as $col)
                                            <td
                                                x-show="showBenchmarkCols['{{ $imut['id'] }}'] !== undefined && showBenchmarkCols['{{ $imut['id'] }}']['{{ $col['type'] }}'] === true"
                                                x-cloak
                                                class="px-3 py-3"></td>
                                            @endforeach
                                        </tr>
                                        @endif

                            </tbody>
                        </table>
                    </div>

                    {{-- ================= CHART ================= --}}
                    <div class="flex flex-col justify-center min-h-[320px]">
                        <canvas id="chart-{{ $imut['id'] }}"
                            data-chart
                            data-imut-id="{{ $imut['id'] }}"
                            data-json='{{ json_encode(array_merge($chartData['chart-' . $imut['id']] ?? [], ['standard' => $imut['standard']])) }}'>
                        </canvas>

                        <div class="mt-3 text-xs text-slate-600">
                            Garis horizontal menunjukkan nilai standar
                            <span class="font-semibold">{{ $imut['standard'] }}%</span>.
                        </div>
                    </div>

                </div>

                {{-- ================= ANALISIS ================= --}}
                @if(isset($overall))
                <div class="mt-6 p-4 rounded-lg border 
                {{ $isAchieved ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800' }}">

                    <div class="text-sm leading-relaxed">
                        Capaian rata-rata periode ini adalah
                        <span class="font-bold">{{ number_format($overall,2) }}%</span>,
                        dibandingkan dengan standar
                        @php
                        // reuse earlier conversion for inline analysis
                        $rawOp2 = $imut['target_operator'] ?? '';
                        switch ($rawOp2) {
                        case '>=':
                        $opIcon2 = '≥';
                        break;
                        case '<=':
                            $opIcon2='≤' ;
                            break;
                            case '==' :
                            $opIcon2='≡' ;
                            break;
                            case '>' :
                            $opIcon2='&gt;' ;
                            break;
                            case '<' :
                            $opIcon2='&lt;' ;
                            break;
                            default:
                            $opIcon2=$rawOp2;
                            }
                            @endphp
                            <span class="font-bold">{!! $opIcon2 !!} {{ $imut['standard'] }}%</span>.
                            <br>
                            Status keseluruhan:
                            <span class="font-bold">
                                {{ $isAchieved ? 'Memenuhi Standar' : 'Belum Memenuhi Standar' }}
                            </span>.
                    </div>
                </div>
                @endif

                <div class="mt-4">

                    <!-- CATATAN UNTUK INDIKATOR INI -->
                    <div class="mb-4" x-cloak>
                        <div class="font-semibold text-sm mb-1"> Analisis & Rekomendasi</div>
                        <template x-if="notes.length">
                            <div class="mb-3 space-y-2">
                                <template x-for="note in notes" :key="note.id">
                                    <label class="inline-flex items-center gap-2">
                                        <input type="checkbox" class="form-checkbox" x-model.number="selected" :value="note.id">
                                        <span x-text="note.note_name + ' (' + note.period_label + ')' "></span>
                                    </label>
                                </template>
                            </div>
                        </template>

                        <template x-if="notes.length">
                            <table class="w-full text-xs border-collapse">
                                <thead>
                                    <tr>
                                        <th class="border px-2 py-1">Periode</th>
                                        <th class="border px-2 py-1">Analisis</th>
                                        <th class="border px-2 py-1">Rekomendasi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="note in notes" :key="note.id">
                                        <tr x-show="selected.includes(note.id)">
                                            <td class="border px-2 py-1" x-text="note.period_label || ''"></td>
                                            <td class="border px-2 py-1" x-text="note.analysis"></td>
                                            <td class="border px-2 py-1" x-text="note.recommendation"></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </template>

                        <template x-if="notes.length === 0">
                            <div class="text-xs text-slate-500">Tidak ada catatan untuk periode ini.</div>
                        </template>
                    </div>
                </div>

            </div>
        </div>

        @endforeach
        @endif
        @endforeach

        <!-- Footer & Signature -->
        <div class="mt-10 border-t-2 border-gray-300 pt-6">
            <div class="mb-5 text-xs">
                <strong>📝 Catatan:</strong>
                <ul class="ml-5 mt-2 space-y-1">
                    <li>N = Numerator (Pembilang): Jumlah kejadian yang memenuhi kriteria</li>
                    <li>D = Denominator (Penyebut): Jumlah total kejadian yang diobservasi</li>
                    <li>Persentase = (N / D) × 100%</li>
                </ul>
            </div>

            <div class="flex justify-end mt-10">
                <!-- Kanan: Penanggung Jawab (user yang login) -->
                <div class="text-center w-56">
                    <span class="text-sm text-end">{{ now()->translatedFormat('d F Y') }},</span>
                    <div class="text-sm mb-4"><br><span class="font-medium">Penanggung Jawab</span><br><span class="font-medium">Tim Mutu</span></div>
                    <div class="h-16 flex items-end justify-center mb-2">
                        <template x-if="selectedLeftSigner && selectedLeftSigner.ttd_url">
                            <img :src="selectedLeftSigner.ttd_url" alt="Tanda Tangan" class="h-14 w-auto mx-auto object-contain">
                        </template>
                        <template x-if="!selectedLeftSigner || !selectedLeftSigner.ttd_url">
                            <div class="h-14"></div>
                        </template>
                    </div>
                    <div class="text-sm font-bold border-t-2 border-black pt-2"
                        x-text="selectedLeftSigner ? selectedLeftSigner.name : '(............................)'">
                    </div>
                </div>
            </div>

            <div class="text-center mt-6 text-sm text-gray-500">
                Dokumen ini dibuat secara otomatis oleh Sistem Informasi Indikator Mutu (SI-IMUT)
            </div>
        </div>

        @else
        <div class="p-6 text-center text-slate-500 border border-gray-300 mt-6">
            Tidak terdapat data yang sesuai dengan filter yang dipilih.
        </div>
        @endif

    </div>

    <script>
        // initialBenchmarkMap is an object mapping imutId -> array of region-type names
        function categoryReport(initialBenchmarkMap = {}, timMutuUsers = [], defaultLeftSignerIndex = 0, rightSigner = null) {
            // build initial visibility object so bindings won't fail
            const initShow = {};
            Object.entries(initialBenchmarkMap || {}).forEach(([imutId, types]) => {
                initShow[imutId] = {};
                types.forEach(t => {
                    initShow[imutId][t] = true;
                });
            });

            return {
                loading: false,
                printOrientation: 'landscape',
                allColumns: [],
                showColumns: {},
                displayMode: 'full',
                showLegend: false,
                useFullLabels: false,

                // benchmark toggles by imut
                benchmarkTypesByImut: initialBenchmarkMap || {},
                showBenchmarkCols: initShow,

                // TTD signers
                timMutuUsers: timMutuUsers || [],
                selectedLeftSignerIndex: defaultLeftSignerIndex || 0,
                rightSigner: rightSigner,
                get selectedLeftSigner() {
                    return this.timMutuUsers[this.selectedLeftSignerIndex] ?? null;
                },

                init() {
                    // displayMode watcher
                    this.$watch('displayMode', value => {
                        this.showLegend = value === 'legend';
                        this.useFullLabels = value === 'full';
                    });
                    this.showLegend = this.displayMode === 'legend';
                    this.useFullLabels = this.displayMode === 'full';

                    // ensure structure exists in case map changed dynamically
                    Object.entries(this.benchmarkTypesByImut).forEach(([imutId, types]) => {
                        if (!this.showBenchmarkCols[imutId]) {
                            this.showBenchmarkCols[imutId] = {};
                        }
                        types.forEach(t => {
                            if (this.showBenchmarkCols[imutId][t] === undefined) {
                                this.showBenchmarkCols[imutId][t] = true;
                            }
                        });
                    });

                    // expose updateCharts globally
                    window.categoryReportUpdate = this.updateCharts.bind(this);

                    setTimeout(() => this.updateCharts(), 0);
                },

                updateCharts() {
                    if (!window.categoryCharts || !window.categoryCharts.length) {
                        return;
                    }
                    window.categoryCharts.forEach(chart => {
                        const imutId = chart.imutId;
                        chart.data.datasets.forEach(ds => {
                            if (/^benchmark/i.test(ds.label || '')) {
                                const type = (ds.label || '').replace(/^benchmark\s*/i, '');
                                if (!this.showBenchmarkCols[imutId] || !this.showBenchmarkCols[imutId][type]) {
                                    ds.hidden = true;
                                } else {
                                    ds.hidden = false;
                                }
                            }
                        });
                        chart.update();
                    });
                },

                calculateDisplayColumns() {
                    // placeholder in case dynamic column showing is needed
                },

                fetchData() {
                    // for server-rendered report we don't actually reload,
                    // but this method exists to satisfy the button.
                    window.location.reload();
                },

                handlePrint() {
                    let printStyle = document.getElementById('print-orientation-style');
                    if (printStyle) {
                        printStyle.remove();
                    }
                    printStyle = document.createElement('style');
                    printStyle.id = 'print-orientation-style';
                    if (this.printOrientation === 'landscape') {
                        printStyle.textContent = '@page { size: A4 landscape; margin: 1cm; }';
                        document.body.classList.add('print-landscape');
                        document.body.classList.remove('print-portrait');
                    } else {
                        printStyle.textContent = '@page { size: A4 portrait; margin: 1cm; }';
                        document.body.classList.add('print-portrait');
                        document.body.classList.remove('print-landscape');
                    }
                    document.head.appendChild(printStyle);
                    setTimeout(() => {
                        window.print();
                    }, 300);
                },

                downloadPdf() {
                    // replicate current query parameters and orientation
                    const params = new URLSearchParams(window.location.search);
                    if (this.printOrientation) {
                        params.set('orientation', this.printOrientation);
                    }
                    const url = `${window.location.pathname.replace(/\/kategori$/, '/kategori/pdf')}?${params.toString()}`;
                    window.open(url, '_blank');
                }
            };
        }

        function imutNotes(notes, dataPoints) {
            return {
                notes: notes || [],
                dataPoints: dataPoints || [],
                selected: notes ? notes.map(n => n.id) : [],
                analysisFor(period) {
                    return this.notes.filter(n => this.selected.includes(n.id) && (n.months || []).includes(period))
                        .map(n => n.analysis || '')
                        .join(' ');
                },
                recFor(period) {
                    return this.notes.filter(n => this.selected.includes(n.id) && (n.months || []).includes(period))
                        .map(n => n.recommendation || '')
                        .join(' ');
                }
            };
        }

        document.addEventListener("DOMContentLoaded", function() {

            if (typeof Chart === 'undefined') {
                // try loading CDN if missing (cached PWA may have removed bundle)
                var s = document.createElement('script');
                s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
                document.head.appendChild(s);
            }
            if (typeof ChartDataLabels === 'undefined') {
                var p = document.createElement('script');
                p.src = 'https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2';
                document.head.appendChild(p);
            }
            Chart.register(ChartDataLabels);

            document.querySelectorAll("canvas[data-chart]").forEach(function(canvas) {

                const rawData = canvas.getAttribute("data-json");
                if (!rawData) return;

                const parsed = JSON.parse(rawData);

                const labels = parsed.labels || [];
                // use provided datasets directly, or fall back to constructing one
                let datasets = [];
                if (parsed.datasets && parsed.datasets.length) {
                    datasets = parsed.datasets;
                } else {
                    const values = parsed.values || [];
                    const standard = parsed.standard || 0;
                    const standardData = labels.map(() => standard);
                    datasets = [{
                            label: "Capaian (%)",
                            data: values,
                            borderColor: "#1d4ed8",
                            backgroundColor: "rgba(29, 78, 216, 0.08)",
                            fill: true,
                            tension: 0.25,
                            borderWidth: 3,
                            pointRadius: 5,
                            pointHoverRadius: 6,
                            pointBackgroundColor: "#1d4ed8",
                            pointBorderColor: "#ffffff",
                            pointBorderWidth: 2,
                        },
                        {
                            label: "Standar Target",
                            data: standardData,
                            borderColor: "#ed582f",
                            backgroundColor: "#ed582f",
                            borderDash: [8, 6],
                            borderWidth: 2,
                            pointRadius: 3,
                            pointBackgroundColor: "#ed582f",
                            pointBorderWidth: 0,
                            fill: false,
                            tension: 0
                        }
                    ];
                }

                // post-process dataset styles
                let standardValue = 0;
                datasets = datasets.map(ds => {
                    const label = (ds.label || '').toLowerCase();
                    // Capaian should keep existing styling, leave tension moderate
                    if (label.includes('benchmark')) {
                        // straight solid line, no points
                        return Object.assign({}, ds, {
                            tension: 0,
                            pointRadius: 0,
                            borderDash: [],
                        });
                    }
                    if (label.includes('standar')) {
                        // orange/red line to indicate threshold
                        const color = '#f97316'; // orange-500
                        standardValue = (ds.data && ds.data.length) ? ds.data[0] : 0;
                        return Object.assign({}, ds, {
                            borderColor: color,
                            backgroundColor: color,
                            borderDash: ds.borderDash || [8, 6],
                            tension: 0,
                            pointRadius: 3,
                            fill: false,
                        });
                    }
                    // default, return unchanged
                    return ds;
                });

                const chartInstance = new Chart(canvas, {
                    type: "line",
                    data: {
                        labels: labels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: true,
                        plugins: {

                            legend: {
                                position: "bottom",
                                labels: {
                                    font: {
                                        size: 12
                                    },
                                    usePointStyle: true
                                }
                            },

                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return context.dataset.label + ": " + context.parsed.y.toFixed(2) + "%";
                                    }
                                }
                            },

                            // ===== NILAI DI TITIK =====
                            datalabels: {
                                color: function(context) {
                                    const value = context.dataset.data[context.dataIndex];
                                    return value >= standardValue ? "#065f46" : "#991b1b";
                                },
                                anchor: "end",
                                align: "top",
                                offset: 4,
                                font: {
                                    weight: "600",
                                    size: 10
                                },
                                formatter: function(value) {
                                    return value.toFixed(1) + "%";
                                }
                            }
                        },

                        scales: {
                            y: {
                                beginAtZero: true,
                                max: 110,
                                ticks: {
                                    callback: function(value) {
                                        return value + "%";
                                    }
                                },
                                grid: {
                                    color: "#e5e7eb"
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
                // annotate with imutId and register chart
                const imutId = canvas.getAttribute('data-imut-id');
                if (imutId) {
                    chartInstance.imutId = imutId;
                }
                window.categoryCharts = window.categoryCharts || [];
                window.categoryCharts.push(chartInstance);
            });

        });
    </script>
</body>

</html>