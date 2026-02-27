<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Kategori Indikator - {{ implode(', ', $categoryNames ?: $categories) }}</title>
    <link rel="stylesheet" href="{{ asset('css/print-report.css') }}">
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

<body class="bg-white text-gray-800 font-sans text-sm leading-relaxed">

    @php
    $periodLabel = $periode;
    $cats = $categoryNames ?: $categories;
    @endphp

    <!-- HEADER -->
    <x-basic-report-header
        title="Laporan Kategori Indikator Mutu"
        :additionalInfo="[
        ['label' => 'Kategori', 'value' => implode(', ', $cats)],
        ['label' => 'Periode', 'value' => $periodLabel],
        ['label' => 'Tanggal Cetak', 'value' => now()->translatedFormat('d F Y, H:i') . ' WIB']
    ]" />

    <!-- RINGKASAN EKSEKUTIF -->
    <div class="mt-8 border border-slate-300 rounded-md">
        <div class="bg-slate-100 px-4 py-2 font-semibold text-slate-700 border-b border-slate-300">
            Ringkasan Umum
        </div>
        <div class="p-4">
            <table class="w-full text-sm">
                <tr>
                    <td class="w-1/4 py-1 text-slate-600">Periode</td>
                    <td class="py-1 font-medium">{{ $periodLabel }}</td>
                </tr>
                <tr>
                    <td class="py-1 text-slate-600">Total Indikator</td>
                    <td class="py-1 font-medium">{{ number_format($summary['total_indicators'] ?? 0) }}</td>
                </tr>
                <tr>
                    <td class="py-1 text-slate-600">Kategori</td>
                    <td class="py-1 font-medium">{{ implode(', ', $cats) }}</td>
                </tr>
            </table>
        </div>
    </div>

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

        <div class="mt-8 rounded-2xl border border-slate-200 shadow-sm bg-white overflow-hidden imut-section">

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

            <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">

                {{-- ================= TABEL ================= --}}
                <div class="overflow-x-auto">
                    <table class="w-full text-sm border-collapse">
                        <thead>
                            <tr class="bg-slate-800 text-white text-xs uppercase tracking-wider">
                                <th class="px-4 py-3 text-left">Periode</th>
                                <th class="px-4 py-3 text-center">N</th>
                                <th class="px-4 py-3 text-center">D</th>
                                <th class="px-4 py-3 text-center">Standar</th>
                                <th class="px-4 py-3 text-right">Persentase</th>
                                <th class="px-4 py-3 text-center">Status</th>
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

                                </tr>
                                @endif
                        </tbody>
                    </table>
                </div>


                {{-- ================= CHART ================= --}}
                <div class="flex flex-col justify-center min-h-[260px]">

                    <canvas id="chart-{{ $imut['id'] }}"
                        data-chart
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
                let values = [];
                let standardData = [];

                if (parsed.datasets && parsed.datasets.length) {
                    values = parsed.datasets[0].data || [];
                    if (parsed.datasets[1]) {
                        standardData = parsed.datasets[1].data || [];
                    }
                } else {
                    values = parsed.values || [];
                    const standard = parsed.standard || 0;
                    standardData = labels.map(() => standard);
                }

                const standardValue = standardData.length ? standardData[0] : 0;

                new Chart(canvas, {
                    type: "line",
                    data: {
                        labels: labels,
                        datasets: [

                            // ===== CAPAIAN =====
                            {
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

                            // ===== STANDAR =====
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
                        ]
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
                                max: 100,
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

            });

        });
    </script>
</body>

</html>