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

<body x-data='categoryReport(@json($imutBenchmarkTypes ?? []))' class="bg-white text-gray-800 font-sans text-sm leading-relaxed">

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

    <!-- debugging section: dump controller payload -->
    <div class="bg-gray-100 border border-gray-300 rounded p-4 mb-6 text-xs font-mono">
        <!-- DATA PER KATEGORI -->
        <div class="mt-10">

            @if($dataByImut && count($dataByImut) > 0)

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

            <div x-data='imutNotes(@json($imut['notes']), @json($imut['data']))' class="mt-8 rounded-2xl border border-slate-200 shadow-sm bg-white overflow-hidden imut-section">

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

                <div class="grid grid-cols-1 gap-8">

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

                    {{-- ================= CHART ================= --}}
                    <div class="flex flex-col justify-center min-h-[260px]">

                        <canvas id="chart-{{ $imut['id'] }}"
                            data-chart
                            data-imut-id="{{ $imut['id'] }}"
                            data-json='{{ json_encode(array_merge($chartData['chart-' . $imut['id']] ?? [], ['standard' => $imut['standard']])) }}'>
                        </canvas>

                        {{-- INFO STANDAR --}}
                        <div class="mt-4 text-sm text-slate-600">
                            Garis horizontal pada grafik menunjukkan standar
                            <span class="font-semibold">
                                {{ $imut['standard'] }}%
                            </span>.
                        </div>

                    </div>

                    {{-- ================= TABEL ================= --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                @php
                                // prepare mapping for region types so benchmark headers can be coloured
                                $rtMap = collect($imut['regionTypesInfo'] ?? [])->keyBy('id');
                                // default background for non-benchmark headers
                                $defaultBg = $rtMap->first()['color'] ?? '#1e40af';
                                $defaultText = '#ffffff';
                                $baseStyle = "background-color: $defaultBg; color: $defaultText;";

                                // group benchmarks by region type to avoid duplicates
                                $benchmarkCols = collect($imut['benchmarks'] ?? [])->groupBy('region_type_id')->map(function($group, $rtid) use ($rtMap) {
                                return [
                                'region_type_id' => $rtid,
                                'type' => $rtMap[$rtid]['type'] ?? '',
                                'color' => $rtMap[$rtid]['color'] ?? '#000',
                                'records' => $group->all(),
                                ];
                                })->values();
                                @endphp
                                <tr class="text-xs uppercase tracking-wider">
                                    <th class="px-4 py-3 text-left" style="{{ $defaultBg }}">Periode</th>
                                    <th class="px-4 py-3 text-center" style="{{ $defaultBg }}">N</th>
                                    <th class="px-4 py-3 text-center" style="{{ $defaultBg }}">D</th>
                                    <th class="px-4 py-3 text-center" style="{{ $defaultBg }}">Standar</th>
                                    <th class="px-4 py-3 text-right" style="{{ $defaultBg }}">Persentase</th>
                                    <th class="px-4 py-3 text-center" style="{{ $baseStyle }}">Status</th>
                                    @foreach($benchmarkCols as $col)
                                    @php
                                    $rtcol = $col['color'];
                                    $bgcol = $rtcol . '33';
                                    $thStyle = "background-color: $bgcol; color: $defaultText;";
                                    @endphp
                                    <th x-show="showBenchmarkCols['{{ $imut['id'] }}'] && showBenchmarkCols['{{ $imut['id'] }}']['{{ $col['type'] }}']" class="px-4 py-3 text-center" style="{{ $thStyle }}">
                                        Benchmark {{ $col['type'] }}
                                    </th>
                                    @endforeach
                                </tr>
                            </thead>

                            <tbody class="text-sm text-slate-700 divide-y divide-slate-200">

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

                                $opSym = '';
                                switch($dataPoint['operator'] ?? '') {
                                case '>=': $opSym='≥'; break;
                                case '<=': $opSym='≤' ; break;
                                    case '==' : $opSym='≡' ; break;
                                    case '>' : $opSym='&gt;' ; break;
                                    case '<' : $opSym='&lt;' ; break;
                                    default: $opSym=$dataPoint['operator'] ?? '' ;
                                    }
                                    @endphp

                                    <tr class="hover:bg-slate-50 transition">

                                    <!-- Periode -->
                                    <td class="px-4 py-3 font-medium text-slate-800">
                                        {{ $dataPoint['month_label'] }}
                                    </td>

                                    <!-- N -->
                                    <td class="px-4 py-3 text-center">
                                        {{ number_format($dataPoint['numerator']) }}
                                    </td>

                                    <!-- D -->
                                    <td class="px-4 py-3 text-center">
                                        {{ number_format($dataPoint['denominator']) }}
                                    </td>

                                    <!-- Standar (Warna Amber Soft) -->
                                    <td class="px-4 py-3 text-center bg-amber-50 text-amber-800 font-medium">
                                        {!! $opSym !!}
                                        {{ $dataPoint['standard'] !== null ? number_format($dataPoint['standard'],2) . '%' : '-' }}
                                    </td>

                                    <!-- Persentase (Warna Biru Soft) -->
                                    <td class="px-4 py-3 text-right bg-blue-50 text-blue-800 font-semibold">
                                        {{ number_format($dataPoint['percentage'],2) }}%
                                    </td>

                                    <!-- Status -->
                                    <td class="px-4 py-3 text-center">
                                        @if($dataPoint['status']==='achieved')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-emerald-100 text-emerald-700">
                                            Tercapai
                                        </span>
                                        @elseif($dataPoint['status']==='no-data')
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-slate-100 text-slate-600">
                                            Tidak Ada Data
                                        </span>
                                        @else
                                        <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700">
                                            Belum
                                        </span>
                                        @endif
                                    </td>
                                    @foreach($benchmarkCols as $col)
                                    @php
                                    // pick first non-null monthly value among group's records for this month
                                    $bmVal = null;
                                    foreach($col['records'] as $bm) {
                                    $val = $bm['monthly'][$dataPoint['month_label']] ?? null;
                                    if ($val !== null) {
                                    $bmVal = $val;
                                    break;
                                    }
                                    }
                                    $rtcol = $col['color'];
                                    $cellBg = $rtcol . '22';
                                    $textCol = $rtcol;
                                    $tdStyle = "background-color: $cellBg; color: $textCol;";
                                    @endphp
                                    <td x-show="showBenchmarkCols['{{ $imut['id'] }}'] && showBenchmarkCols['{{ $imut['id'] }}']['{{ $col['type'] }}']" class="px-4 py-3 text-center font-medium" style="{{ $tdStyle }}">
                                        {{ $bmVal !== null ? number_format($bmVal,2) . '%' : '-' }}
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

                                        <td class="px-4 py-4">
                                            Total / Rata-rata
                                        </td>

                                        <td class="px-4 py-4 text-center">
                                            {{ number_format($totalN) }}
                                        </td>

                                        <td class="px-4 py-4 text-center">
                                            {{ number_format($totalD) }}
                                        </td>

                                        <!-- Standar Total -->
                                        <td class="px-4 py-4 text-center bg-amber-100 text-amber-900">
                                            ≥ {{ number_format($imut['standard'],2) }}%
                                        </td>

                                        <!-- Overall -->
                                        <td class="px-4 py-4 text-right bg-blue-100 text-blue-900 font-bold">
                                            {{ number_format($overall,2) }}%
                                        </td>

                                        <td class="px-4 py-4 text-center">
                                            @if($isAchieved)
                                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-emerald-200 text-emerald-800">
                                                Tercapai
                                            </span>
                                            @else
                                            <span class="px-3 py-1 text-xs font-bold rounded-full bg-red-200 text-red-800">
                                                Belum
                                            </span>
                                            @endif
                                        </td>
                                        @foreach($benchmarkCols as $col)
                                        @php
                                        // from all records for this column, find last non-null value across months
                                        $last = null;
                                        foreach($col['records'] as $bm) {
                                        $cand = collect($bm['monthly'])->filter()->last();
                                        if ($cand !== null) {
                                        $last = $cand;
                                        break;
                                        }
                                        }
                                        $rtcol = $col['color'];
                                        $cellBg = $rtcol . '22';
                                        $textCol = $rtcol;
                                        $tdTotalStyle = "background-color: $cellBg; color: $textCol;";
                                        @endphp
                                        <td x-show="showBenchmarkCols['{{ $imut['id'] }}'] && showBenchmarkCols['{{ $imut['id'] }}']['{{ $col['type'] }}']" class="px-4 py-4 text-center" style="{{ $tdTotalStyle }}">
                                            <span></span>
                                        </td>
                                        @endforeach

                                    </tr>
                                    @endif
                            </tbody>
                        </table>
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

        @else
        <div class="p-6 text-center text-slate-500 border border-slate-300 mt-6">
            Tidak terdapat data yang sesuai dengan filter yang dipilih.
        </div>
        @endif

    </div>

    <script>
        // initialBenchmarkMap is an object mapping imutId -> array of region-type names
        function categoryReport(initialBenchmarkMap = {}) {
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
                            this.$set(this.showBenchmarkCols, imutId, {});
                        }
                        types.forEach(t => {
                            if (this.showBenchmarkCols[imutId][t] === undefined) {
                                this.$set(this.showBenchmarkCols[imutId], t, true);
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