<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Unit Kerja - {{ $unit->unit_name ?? 'Laporan' }}</title>
    <link rel="stylesheet" href="{{ asset('css/print-report.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @vite(['resources/css/app.css'])
    <script>
        // ensure global Chart exists if service worker cached old page
        if (typeof Chart === 'undefined') {
            var s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/chart.js';
            document.head.appendChild(s);
        }
    </script>
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

<body class="bg-white text-gray-800 font-sans leading-relaxed">
    @php
    // Prepare data
    $unit = $unit ?? null;
    $dataByImut = $dataByImut ?? collect();
    $summary = $summary ?? null;
    $allMonths = $allMonths ?? [];
    $periodLabel = $periodLabel ?? 'Laporan';

    if (!$unit) {
    echo '<div class="empty-state">Data unit kerja tidak ditemukan.</div>';
    return;
    }
    @endphp

    <!-- Header dengan Logo (Formal Style) -->
    <x-basic-report-header
        title="Laporan IMUT Per Unit Kerja"
        :additionalInfo="[
            ['label' => 'Unit Kerja', 'value' => $unit->unit_name],
            ['label' => 'Periode', 'value' => $periodLabel],
            ['label' => 'Tanggal Cetak', 'value' => now()->translatedFormat('d F Y, H:i') . ' WIB']
        ]" />

    <!-- Info Section -->
    <div class="bg-slate-50 border border-slate-200 rounded-md p-5 mb-6">
        <div class="flex py-2 border-b border-slate-200 last:border-b-0">
            <span class="font-semibold min-w-[150px] text-blue-700">Nama Unit Kerja:</span>
            <span class="text-slate-700">{{ $unit->unit_name }}</span>
        </div>
        <div class="flex py-2 border-b border-slate-200 last:border-b-0">
            <span class="font-semibold min-w-[150px] text-blue-700">Periode Laporan:</span>
            <span class="text-slate-700">{{ $periodLabel }}</span>
        </div>
        <div class="flex py-2 border-b border-slate-200 last:border-b-0">
            <span class="font-semibold min-w-[150px] text-blue-700">Deskripsi:</span>
            <span class="text-slate-700">{{ $unit->description ?? 'Tidak ada deskripsi' }}</span>
        </div>
        <div class="flex py-2 border-b border-slate-200 last:border-b-0">
            <span class="font-semibold min-w-[150px] text-blue-700">Total IMUT Data:</span>
            <span class="text-slate-700">{{ count($dataByImut) }} indikator</span>
        </div>
        <div class="flex py-2 border-b border-slate-200 last:border-b-0">
            <span class="font-semibold min-w-[150px] text-blue-700">Tanggal Cetak:</span>
            <span class="text-slate-700">{{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
        </div>
    </div>


    @php
    // Calculate summary data based on actual data
    $totalIndikator = count($dataByImut);
    $achievedCount = 0;
    $notAchievedCount = 0;
    $totalDataPoints = 0;
    $totalPercentageSum = 0;
    $validIndicatorsForAverage = 0;

    foreach($dataByImut as $imut) {
    // Hitung overall percentage untuk indikator ini
    $totalN = 0;
    $totalD = 0;
    $dataMonths = 0;

    foreach($imut['data'] ?? [] as $dataPoint) {
    if ($dataPoint['status'] !== 'no-data') {
    $totalN += $dataPoint['numerator'];
    $totalD += $dataPoint['denominator'];
    $dataMonths++;
    $totalDataPoints++;
    }
    }

    if($dataMonths > 0 && $totalD > 0) {
    $overallPercentage = ($totalN / $totalD) * 100;
    $totalPercentageSum += $overallPercentage;
    $validIndicatorsForAverage++;

    $operator = $imut['target_operator'] ?? '>=';
    $standard = $imut['standard'] ?? 0;

    $achieved = false;
    switch ($operator) {
    case '>=':
    case '≥':
    $achieved = $overallPercentage >= $standard;
    break;
    case '>':
    $achieved = $overallPercentage > $standard;
    break;
    case '<=':
        case '≤' :
        $achieved=$overallPercentage <=$standard;
        break;
        case '<' :
        $achieved=$overallPercentage < $standard;
        break;
        case '=' :
        case '==' :
        $achieved=round($overallPercentage, 2)==$standard;
        break;
        default:
        $achieved=$overallPercentage>= $standard;
        }

        if($achieved) {
        $achievedCount++;
        } else {
        $notAchievedCount++;
        }
        }
        }

        $averagePercentage = $validIndicatorsForAverage > 0 ? ($totalPercentageSum / $validIndicatorsForAverage) : 0;
        @endphp

        <!-- Summary Section -->
        @if(count($dataByImut) > 0)
        <div class="bg-sky-50 border-l-4 border-sky-400 rounded-md p-5 mb-6">
            <div class="text-sm font-bold text-sky-900 mb-4">📊 Ringkasan Laporan</div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="bg-white p-4 rounded border border-sky-200 text-center">
                    <div class="text-xs text-gray-600 mb-1 font-medium">Total IMUT Data</div>
                    <div class="text-2xl font-bold text-sky-900">{{ number_format($totalIndikator) }}</div>
                </div>
                <div class="bg-white p-4 rounded border border-sky-200 text-center">
                    <div class="text-xs text-gray-600 mb-1 font-medium">Rata-rata Pencapaian</div>
                    <div class="text-2xl font-bold text-sky-900">{{ number_format($averagePercentage, 2) }}%</div>
                </div>
                <div class="bg-white p-4 rounded border border-sky-200 text-center">
                    <div class="text-xs text-gray-600 mb-1 font-medium">Target Tercapai</div>
                    <div class="text-2xl font-bold text-sky-900">{{ number_format($achievedCount) }} / {{ number_format($totalIndikator) }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Table Summary -->
        @if(count($dataByImut) > 0)
        <div class="mb-4 overflow-x-auto rounded-lg border border-slate-200">

            <table class="min-w-full text-[11px] border-separate border-spacing-0">

                <!-- HEADER -->
                <thead class="bg-slate-50 text-slate-600 uppercase text-[9px] tracking-wide">
                    <tr>
                        <th class="px-1.5 py-1.5 text-center border-b border-slate-200 w-8">No</th>
                        <th class="px-2 py-1.5 text-left border-b border-slate-200 min-w-[180px]">Indikator</th>
                        <th class="px-1.5 py-1.5 text-center border-b border-slate-200 w-20">Target</th>

                        @foreach($allMonths as $month)
                        <th class="px-1 py-1.5 text-center border-b border-slate-200" colspan="3">
                            {{ $month['label'] }}
                        </th>
                        @endforeach
                    </tr>

                    <tr class="bg-white text-slate-400 text-[9px]">
                        <th class="border-slate-50"></th>
                        <th class="border-slate-50"></th>
                        <th class="border-slate-50"></th>
                        @foreach($allMonths as $month)
                        <th class="px-1 py-1 text-center border-b border-slate-100">N</th>
                        <th class="px-1 py-1 text-center border-b border-slate-100">D</th>
                        <th class="px-1 py-1 text-center border-b border-slate-100">%</th>
                        @endforeach
                    </tr>
                </thead>

                <!-- BODY -->
                <tbody class="text-slate-700">
                    @php
                    $lastMonth = last($allMonths)['label'] ?? null;
                    $grouped = collect($dataByImut)->groupBy('category');
                    $colspan = 3 + count($allMonths) * 3;
                    $counter = 0;
                    @endphp

                    @foreach($grouped as $category => $items)
                    <tr class="bg-gray-100 font-semibold">
                        <td colspan="{{ $colspan }}" class="px-2 py-1.5">{{ $category }}</td>
                    </tr>
                    @foreach($items as $idx => $imut)
                    @php
                    $counter++;
                    $map = collect($imut['data'] ?? [])->keyBy('month_label');

                    $operatorMap = [
                    '>=' => '≥',
                    '<='=> '≤',
                        '==' => '=',
                        '>' => '>',
                        '<'=> '<', '!='=> '≠',
                                ];

                                $operator = $imut['target_operator'] ?? '>=';
                                $symbol = $operatorMap[$operator] ?? $operator;
                                $standard = $imut['standard'] ?? 0;
                                @endphp

                                <tr class="hover:bg-slate-50 transition">

                                    <!-- NO -->
                                    <td class="px-1.5 py-1.5 border-b border-slate-100 text-center font-medium">
                                        {{ $counter }}
                                    </td>

                                    <!-- INDIKATOR -->
                                    <td class="px-2 py-1.5 border-b border-slate-100 truncate max-w-[220px]">
                                        {{ $imut['title'] }}
                                    </td>

                                    <!-- TARGET -->
                                    <td class="px-1.5 py-1.5 border-b border-slate-100 text-center">
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded bg-slate-100 text-slate-700 font-semibold text-[9px]">
                                            {{ $symbol }} {{ $standard }}%
                                        </span>
                                    </td>

                                    <!-- DATA BULAN -->
                                    @foreach($allMonths as $month)
                                    @php
                                    $label = $month['label'];
                                    $d = $map[$label] ?? null;
                                    $numerator = $d['numerator'] ?? 0;
                                    $denominator = $d['denominator'] ?? 0;
                                    $percent = $denominator > 0 ? ($numerator / $denominator) * 100 : 0;

                                    // Evaluasi target sesuai operator
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

                                            $highlightPercent = $isBelowTarget ? '' : 'bg-yellow-100';
                                            @endphp

                                            <td class="px-1 py-1.5 border-b border-slate-100 text-center">
                                                {{ number_format($numerator) }}
                                            </td>

                                            <td class="px-1 py-1.5 border-b border-slate-100 text-center">
                                                {{ number_format($denominator) }}
                                            </td>

                                            <td class="px-1 py-1.5 border-b border-slate-100 text-right font-semibold tabular-nums {{ $highlightPercent }}">
                                                {{ round($percent, 2) }}%
                                            </td>

                                            @endforeach
                                </tr>
                                @endforeach
                                @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Interpretation Section --}}
        @if(count($dataByImut) > 0)
        <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">

            <div class="font-semibold text-slate-800 mb-2 uppercase tracking-wide text-sm">
                Interpretasi Hasil
            </div>

            <div class="space-y-2 leading-relaxed">

                <div class="flex items-center gap-2">
                    <span class="inline-block w-3 h-3 bg-white border border-slate-300 rounded-sm"></span>
                    <span>Menunjukkan indikator yang <strong>belum mencapai target</strong>.</span>
                </div>

                <div class="flex items-center gap-2">
                    <span class="inline-block w-3 h-3 bg-yellow-200 rounded-sm border border-yellow-300"></span>
                    <span>Menunjukkan indikator yang <strong>telah memenuhi target</strong>.</span>
                </div>

                <div class="pt-2 border-t border-slate-200 mt-2">
                    Dari <strong>{{ $totalIndikator }}</strong> indikator mutu nasional yang dinilai,
                    terdapat <strong class="text-red-700">
                        {{ $notAchievedCount }}
                    </strong> indikator yang belum mencapai target dan
                    <strong class="text-emerald-700">
                        {{ $achievedCount }}
                    </strong> indikator yang telah memenuhi target.
                </div>

            </div>
        </div>
        @endif
        <div class="mt-8">
            @if ($dataByImut && count($dataByImut) > 0)

            @php
            $groupedByCategory = collect($dataByImut)->groupBy('category');
            $globalIndex = 0;
            @endphp

            @foreach ($groupedByCategory as $category => $items)
            @php
            $categoryCount = count($items);
            @endphp

            <div class="mb-6 mt-10 first:mt-0">

                <!-- Divider -->
                <div class="flex items-center gap-3 mb-3">
                    <div class="h-px flex-1 bg-slate-300"></div>
                    <div class="text-[11px] uppercase tracking-widest text-slate-500 font-semibold">
                        Kategori
                    </div>
                    <div class="h-px flex-1 bg-slate-300"></div>
                </div>

                <!-- Category Header Card -->
                <div class="flex items-center justify-between bg-gradient-to-r from-slate-700 to-slate-800 text-white rounded-lg px-5 py-3 shadow-sm">

                    <div>
                        <h2 class="text-sm font-semibold tracking-wide">
                            {{ $category }}
                        </h2>
                        <p class="text-[10px] text-slate-200 mt-0.5">
                            {{ $categoryCount }} Indikator Mutu
                        </p>
                    </div>

                    <div class="text-[10px] bg-white/15 px-3 py-1 rounded-full tracking-wide">
                        Section {{ $loop->iteration }}
                    </div>

                </div>

            </div>

            @foreach ($items as $index => $imut)
            @php
            $globalIndex++;

            // Operator symbol mapping untuk detail section
            $operatorMap = [
            '>=' => '≥',
            '<='=> '≤',
                '==' => '=',
                '>' => '>',
                '<'=> '<', '!='=> '≠',
                        ];
                        $operator = $imut['target_operator'] ?? '>=';
                        $symbol = $operatorMap[$operator] ?? $operator;
                        @endphp
                        <div class="mb-9 border border-slate-200 rounded-lg overflow-hidden bg-white break-inside-avoid">
                            <!-- Header -->
                            <div class="bg-gradient-to-r from-blue-700 to-blue-800 text-white px-5 py-4 border-b-2 border-blue-900">
                                <h3 class="text-sm font-bold mb-1">{{ $globalIndex }}. {{ $imut['title'] }}</h3>
                                <p class="text-xs text-blue-100">Kategori: {{ $imut['category'] }} | Target Standar: {{ $symbol }} {{ $imut['standard'] }}%</p>
                            </div>

                            <!-- Content -->
                            <div class="p-5">
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-5">
                                    <!-- Table -->
                                    <div>
                                        <table class="w-full border-collapse text-xs">
                                            <thead class="bg-slate-100 border-b-2 border-slate-300">
                                                <tr>
                                                    <th class="border border-slate-200 px-3 py-2 text-left font-semibold text-slate-700 w-1/6">Periode</th>
                                                    <th class="border border-slate-200 px-3 py-2 text-center font-semibold text-slate-700 w-1/6">N</th>
                                                    <th class="border border-slate-200 px-3 py-2 text-center font-semibold text-slate-700 w-1/6">D</th>
                                                    <th class="border border-slate-200 px-3 py-2 text-center font-semibold text-slate-700 w-1/6">Persentase</th>
                                                    <th class="border border-slate-200 px-3 py-2 text-center font-semibold text-slate-700 w-1/6">Nilai Standard</th>
                                                    <th class="border border-slate-200 px-3 py-2 text-center font-semibold text-slate-700 w-1/6">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                $totalN = 0;
                                                $totalD = 0;
                                                $achievedMonths = 0;
                                                $dataMonths = 0;
                                                @endphp

                                                @foreach ($imut['data'] as $dataPoint)
                                                @php
                                                if ($dataPoint['status'] !== 'no-data') {
                                                $totalN += $dataPoint['numerator'];
                                                $totalD += $dataPoint['denominator'];
                                                $dataMonths++;
                                                if ($dataPoint['status'] === 'achieved') {
                                                $achievedMonths++;
                                                }
                                                }
                                                @endphp
                                                <tr class="hover:bg-slate-50">
                                                    <td class="border border-slate-200 px-3 py-2">{{ $dataPoint['month_label'] }}</td>
                                                    <td class="border border-slate-200 px-3 py-2 text-center">{{ number_format($dataPoint['numerator']) }}</td>
                                                    <td class="border border-slate-200 px-3 py-2 text-center">{{ number_format($dataPoint['denominator']) }}</td>
                                                    <td class="border border-slate-200 px-3 py-2 text-right font-semibold">{{ number_format($dataPoint['percentage'], 2) }}%</td>
                                                    <td class="border border-slate-200 px-3 py-2 text-center font-semibold text-blue-600">{{ $symbol }} {{ $imut['standard'] }}%</td>
                                                    <td class="border border-slate-200 px-3 py-2 text-center">
                                                        @if ($dataPoint['status'] === 'achieved')
                                                        <span class="text-green-700 font-semibold">✓ Tercapai</span>
                                                        @elseif ($dataPoint['status'] === 'not-achieved')
                                                        <span class="text-red-700 font-semibold">✗ Belum Tercapai</span>
                                                        @else
                                                        <span class="text-gray-500 italic">- Tidak Ada Data</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @endforeach

                                                <!-- Total Row -->
                                                @if ($dataMonths > 0)
                                                <tr class="bg-slate-100 font-semibold">
                                                    <td class="border border-slate-200 px-3 py-2"><strong>Total / Rata-rata</strong></td>
                                                    <td class="border border-slate-200 px-3 py-2 text-center">{{ number_format($totalN) }}</td>
                                                    <td class="border border-slate-200 px-3 py-2 text-center">{{ number_format($totalD) }}</td>
                                                    @php
                                                    $overallPercentage = $totalD > 0 ? ($totalN / $totalD) * 100 : 0;
                                                    @endphp
                                                    <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($overallPercentage, 2) }}%</td>
                                                    <td class="border border-slate-200 px-3 py-2 text-center font-semibold text-blue-600">{{ $symbol }} {{ $imut['standard'] }}%</td>
                                                    <td class="border border-slate-200 px-3 py-2 text-center">
                                                        @php
                                                        $achieved = false;
                                                        $operator = $imut['target_operator'] ?? '>=';
                                                        switch ($operator) {
                                                        case '>=':
                                                        case '≥':
                                                        $achieved = $overallPercentage >= $imut['standard'];
                                                        break;
                                                        case '>':
                                                        $achieved = $overallPercentage > $imut['standard'];
                                                        break;
                                                        case '<=':
                                                            case '≤' :
                                                            $achieved=$overallPercentage <=$imut['standard'];
                                                            break;
                                                            case '<' :
                                                            $achieved=$overallPercentage < $imut['standard'];
                                                            break;
                                                            case '=' :
                                                            case '==' :
                                                            $achieved=$overallPercentage==$imut['standard'];
                                                            break;
                                                            default:
                                                            $achieved=$overallPercentage>= $imut['standard'];
                                                            }
                                                            @endphp
                                                            @if ($achieved)
                                                            <span class="text-green-700">✓ Tercapai</span>
                                                            @else
                                                            <span class="text-red-700">✗ Belum Tercapai</span>
                                                            @endif
                                                    </td>
                                                </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- Chart -->
                                    <div class="flex items-center justify-center min-h-[250px]">
                                        <canvas id="chart-{{ $imut['id'] }}" data-chart data-json='{{ json_encode($chartData['chart-' . $imut['id']] ?? []) }}' style="max-height: 250px;"></canvas>
                                    </div>
                                </div>

                                <!-- Analysis & Recommendations Table -->
                                <div class="mt-4">
                                    <h4 class="text-sm font-semibold text-gray-700 mb-2">📌 Analisis & Rekomendasi</h4>
                                    <table class="w-full border-collapse text-xs">
                                        <thead class="bg-slate-100 border-b-2 border-slate-300">
                                            <tr>
                                                <th class="border border-slate-200 px-3 py-2 text-left font-semibold text-slate-700 w-1/6">Periode</th>
                                                <th class="border border-slate-200 px-3 py-2 text-left font-semibold text-slate-700">Analisis</th>
                                                <th class="border border-slate-200 px-3 py-2 text-left font-semibold text-slate-700">Rekomendasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($imut['data'] as $dataPoint)
                                            <tr class="hover:bg-slate-50 align-top">
                                                <td class="border border-slate-200 px-3 py-2">{{ $dataPoint['month_label'] }}</td>
                                                <td class="border border-slate-200 px-3 py-2">{{ $dataPoint['analysis'] ?? '-' }}</td>
                                                <td class="border border-slate-200 px-3 py-2">{{ $dataPoint['recommendations'] ?? '-' }}</td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        @endforeach
                        @else
                        <div class="p-8 text-center text-gray-500 bg-slate-50 rounded-md">
                            Tidak ada data IMUT yang tersedia untuk unit kerja ini.
                        </div>
                        @endif
        </div>

        <!-- Footer & Signature -->
        @php
        // Get operator symbol for footer notes
        $footerOperatorMap = [
        '>=' => '≥',
        '<='=> '≤',
            '==' => '=',
            '>' => '>',
            '<'=> '<', '!='=> '≠',
                    ];
                    $genericOperator = '≥'; // Default untuk footer
                    if (isset($dataByImut) && count($dataByImut) > 0) {
                    $firstImut = collect($dataByImut)->first();
                    $rawOp = $firstImut['target_operator'] ?? '>=';
                    $genericOperator = $footerOperatorMap[$rawOp] ?? $rawOp;
                    }
                    @endphp
                    <x-report-footer-data-collector
                        :unit="$unit"
                        :leftUsers="$usersByUnit['pengumpul_data'] ?? null"
                        :leftSignatureImage="$usersByUnit['pengumpul_data'][0]['ttd_url'] ?? null"
                        :rightUsers="$usersByUnit['validator'] ?? null"
                        :rightSignatureImage="$usersByUnit['validator'][0]['ttd_url'] ?? null"
                        :notes="[
            'N = Numerator (Pembilang): Jumlah kejadian yang memenuhi kriteria',
            'D = Denominator (Penyebut): Jumlah total kejadian yang diobservasi',
            'Persentase = (N / D) × 100%',
            'Status Tercapai jika Persentase ' . $genericOperator . ' Target Standar',
        ]" />

                    <!-- Preview Controls -->
                    <div class="no-print flex gap-3 mt-6">
                        <button id="backBtn" class="px-5 py-2 border border-slate-300 rounded text-blue-700 font-semibold hover:bg-slate-100 transition text-sm">← Kembali</button>
                        <button id="printBtn" class="px-5 py-2 bg-blue-700 text-white font-semibold rounded hover:bg-blue-800 transition text-sm flex-1">🖨️ Cetak</button>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            // Initialize Charts from data-json attribute
                            if (typeof Chart !== 'undefined') {
                                const canvases = document.querySelectorAll('[data-chart]');
                                console.log('Found', canvases.length, 'charts to render');

                                canvases.forEach(canvas => {
                                    const chartId = canvas.id;
                                    const dataJson = canvas.dataset.json;

                                    console.log('Processing chart:', chartId, 'Data:', dataJson);

                                    if (dataJson) {
                                        try {
                                            const chartData = JSON.parse(dataJson);
                                            console.log('Parsed chart data:', chartData);

                                            new Chart(canvas, {
                                                type: 'line',
                                                data: chartData,
                                                options: {
                                                    responsive: true,
                                                    maintainAspectRatio: true,
                                                    plugins: {
                                                        legend: {
                                                            position: 'top',
                                                        },
                                                    },
                                                    scales: {
                                                        y: {
                                                            beginAtZero: true,
                                                            max: 100,
                                                            ticks: {
                                                                callback: function(value) {
                                                                    return value + '%';
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            });
                                            console.log('Chart', chartId, 'initialized successfully');
                                        } catch (e) {
                                            console.error('Error initializing chart', chartId, e);
                                            console.error('Data was:', dataJson);
                                        }
                                    } else {
                                        console.warn('No data found for chart', chartId);
                                    }
                                });
                            }

                            // Back button
                            const backBtn = document.getElementById('backBtn');
                            if (backBtn) {
                                backBtn.addEventListener('click', () => {
                                    window.history.back();
                                });
                            }

                            // Print button
                            const printBtn = document.getElementById('printBtn');
                            if (printBtn) {
                                printBtn.addEventListener('click', () => {
                                    window.print();
                                });
                            }
                        });
                    </script>
</body>

</html>