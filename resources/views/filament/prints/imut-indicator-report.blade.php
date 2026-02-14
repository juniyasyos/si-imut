<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Indikator - {{ $imutData->title ?? 'Indikator' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Libre+Barcode+128&family=Roboto:wght@300;400;500;700&display=swap');

        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .report-header {
            font-family: 'Roboto', sans-serif;
            letter-spacing: 0.5px;
        }

        @page {
            size: A4;
            margin: 1cm;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
                padding: 0;
                background: white !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                print-color-adjust: exact;
            }

            .page-break {
                page-break-before: always;
            }

            table {
                page-break-inside: avoid;
            }

            .chart-container {
                page-break-inside: avoid;
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Libre+Barcode+128&family=Roboto:wght@300;400;500;700&display=swap');

        body {
            font-family: 'Roboto', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        }

        .report-header {
            font-family: 'Roboto', sans-serif;
            letter-spacing: 0.5px;
        }

        @page {
            size: A4;
            margin: 1cm;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
                padding: 0;
                background: white !important;
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                print-color-adjust: exact;
            }

            .page-break {
                page-break-before: always;
            }

            table {
                page-break-inside: avoid;
            }

            .chart-container {
                page-break-inside: avoid;
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body class="font-sans bg-gray-100 text-gray-900 leading-relaxed" x-data="reportData()">
    @php
    // Prepare data
    $imutData = $imutData ?? null;
    $laporan = $laporan ?? null;
    $unitKerjaData = $unitKerjaData ?? collect();
    $summary = $summary ?? null;
    $selectedNote = $selectedNote ?? null;
    $availableNotes = $availableNotes ?? collect();
    $periodFilter = $periodFilter ?? 'year';

    if (!$imutData || !$laporan) {
    echo '<div class="empty-state">Data tidak ditemukan.</div>';
    return;
    }

    // Format dates
    $startDate = \Carbon\Carbon::parse($laporan->assessment_period_start);
    $endDate = \Carbon\Carbon::parse($laporan->assessment_period_end);

    $sameMonth = $startDate->month === $endDate->month && $startDate->year === $endDate->year;
    $periode = $sameMonth
    ? $startDate->translatedFormat('d') . ' – ' . $endDate->translatedFormat('d F Y')
    : $startDate->translatedFormat('d M') . ' – ' . $endDate->translatedFormat('d F Y');

    // Status mapping
    $statusLabels = [
    'process' => 'Proses',
    'complete' => 'Selesai',
    'coming_soon' => 'Akan Datang',
    ];
    $statusClass = 'status-' . ($laporan->status ?? 'process');
    $statusLabel = $statusLabels[$laporan->status] ?? 'Tidak Diketahui';

    // Month names
    $monthNames = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember',
    ];

    // Period filter labels
    $periodLabels = [
    'year' => 'Tahunan',
    'semester_1' => 'Semester 1',
    'semester_2' => 'Semester 2',
    'q1' => 'Triwulan I',
    'q2' => 'Triwulan II',
    'q3' => 'Triwulan III',
    'q4' => 'Triwulan IV',
    ];

    // Calculate achievement
    $overallPercentage = $summary['average_percentage'] ?? 0;
    $standard = $imutData->standard ?? 0;
    $isAchieved = $overallPercentage >= $standard;
    @endphp

    <!-- Filter Section (No Print) -->
    <div class="no-print bg-gradient-to-r from-blue-50 to-indigo-50 rounded p-4 border border-blue-200 mb-6">
        <form method="GET" action="{{ route('print.preview.imut-indicator-report') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block mb-2 font-semibold text-sm text-gray-700">Filter Periode:</label>
                <select name="period_filter" class="w-full px-3 py-2 text-sm font-medium border border-gray-300 rounded bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @foreach($periodLabels as $value => $label)
                    <option value="{{ $value }}" {{ $periodFilter === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            @if($availableNotes->isNotEmpty())
            <div>
                <label class="block mb-2 font-semibold text-sm text-gray-700">Catatan/Analisis:</label>
                <select name="note_id" class="w-full px-3 py-2 text-sm font-medium border border-gray-300 rounded bg-white focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @foreach($availableNotes as $note)
                    <option value="{{ $note->id }}" {{ $selectedNote && $selectedNote->id === $note->id ? 'selected' : '' }}>
                        {{ \Str::limit($note->title ?? 'Note ' . $note->created_at->format('d/m/Y'), 50) }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif

            <input type="hidden" name="imut_data_id" value="{{ $imutData->id }}">
            <input type="hidden" name="laporan_id" value="{{ $laporan->id }}">

            <div class="flex items-end">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white font-semibold rounded hover:bg-blue-700">
                    🔄 Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <div class="max-w-6xl mx-auto bg-white p-8 shadow-xl border border-gray-200" style="min-height: calc(100vh - 100px);">

        <!-- Header dengan Logo -->
        <x-basic-report-header
            title="Laporan Triwulan Indikator Mutu"
        />

        <!-- Enhanced Dashboard -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <!-- Left Card -->
            <div class="bg-gradient-to-br from-blue-500 to-blue-700 text-white p-6 rounded-lg shadow-md">
                <h3 class="text-lg font-bold mb-4">{{ $imutData->title }}</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-sm opacity-90">Kategori</span>
                        <span class="font-bold">{{ $imutData->categories }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-sm opacity-90">Standar Target</span>
                        <span class="text-2xl font-bold">{{ number_format($standard, 0) }}%</span>
                    </div>
                </div>
            </div>

            <!-- Right Card -->
            <div class="bg-gradient-to-br from-green-500 to-green-700 text-white p-6 rounded-lg shadow-md">
                <h4 class="text-lg font-bold mb-4">Pencapaian Saat Ini</h4>
                <div class="space-y-3">
                    <div class="text-center">
                        <div class="text-5xl font-bold">{{ number_format($overallPercentage, 2) }}%</div>
                        <div class="mt-2">
                            <span class="px-4 py-2 bg-white bg-opacity-20 rounded-full text-sm font-semibold">
                                {{ $isAchieved ? '✓ Target Tercapai' : '✗ Belum Tercapai' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white border-l-4 border-blue-500 p-4 rounded shadow">
                <div class="text-xs text-gray-500 mb-1">Total Unit Kerja</div>
                <div class="text-2xl font-bold text-gray-800">{{ number_format($summary['total_unit_kerja'] ?? 0) }}</div>
            </div>
            <div class="bg-white border-l-4 border-green-500 p-4 rounded shadow">
                <div class="text-xs text-gray-500 mb-1">Total Numerator</div>
                <div class="text-2xl font-bold text-gray-800">{{ number_format($summary['total_numerator'] ?? 0) }}</div>
            </div>
            <div class="bg-white border-l-4 border-yellow-500 p-4 rounded shadow">
                <div class="text-xs text-gray-500 mb-1">Total Denominator</div>
                <div class="text-2xl font-bold text-gray-800">{{ number_format($summary['total_denominator'] ?? 0) }}</div>
            </div>
            <div class="bg-white border-l-4 border-purple-500 p-4 rounded shadow">
                <div class="text-xs text-gray-500 mb-1">Rata-rata</div>
                <div class="text-2xl font-bold text-gray-800">{{ number_format($summary['average_percentage'] ?? 0, 2) }}%</div>
            </div>
        </div>

        <!-- Definition Section -->
        @if (isset($imutData->definition) && $imutData->definition)
        <div class="bg-yellow-50 border border-yellow-300 rounded p-5 mb-6">
            <h4 class="text-sm font-semibold text-yellow-800 mb-2">📋 Definisi Operasional</h4>
            <p class="text-sm text-yellow-900 leading-relaxed">{{ $imutData->definition }}</p>
        </div>
        @endif

        <!-- Chart Section -->
        <div class="bg-white border border-gray-200 rounded p-6 mb-6">
            <!-- Chart Header -->
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 pb-4 border-b border-gray-200">
                <div class="mb-4 md:mb-0">
                    <div class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Grafik Tren Pencapaian Indikator masuk sini
                    </div>
                    <div class="text-xs text-gray-500 mt-1 ml-7">Perbandingan pencapaian periode yang dipilih</div>
                </div>
            </div>

            <!-- Chart Area -->
            <div id="trendChart"></div>
        </div>

        <!-- Comparison Table -->
        <div class="mb-6">
            <div class="text-lg font-bold mb-4 text-gray-900">Tabel Perbandingan Pencapaian</div>

            @php
            // Map historical data ke bulan
            // Buat mapping dari nama bulan ke nomor bulan
            $monthNamesReverse = array_flip($monthNames);

            $historicalByMonth = [];
            foreach ($historicalData ?? [] as $data) {
            // Gunakan field 'month_short' untuk mendapatkan nama bulan
            $monthName = $data['month_short'] ?? '';
            $monthNum = $monthNamesReverse[$monthName] ?? null;

            if ($monthNum !== null) {
            $historicalByMonth[$monthNum] = $data;
            }
            }

            // Ambil benchmark dari relasi imutData (jika ada)
            // Struktur: $benchmarkData[$monthNum][$regionName] = value
            $benchmarkData = [];
            $regionNames = [];

            if (isset($imutData->id)) {
            // Ambil semua benchmark yang aktif untuk indikator ini dengan relasi regionType
            $benchmarks = \App\Models\ImutBenchmarking::with('regionType')
            ->where('imut_data_id', $imutData->id)
            ->where('is_active', true)
            ->get();

            // Untuk setiap bulan dalam historical data, cari benchmark yang berlaku
            foreach ($historicalData ?? [] as $hData) {
            $hMonthName = $hData['month_short'] ?? '';
            $hMonthNum = $monthNamesReverse[$hMonthName] ?? null;
            $hYear = $hData['year'] ?? $laporan->report_year;

            if ($hMonthNum !== null) {
            // Buat tanggal untuk bulan ini (tanggal 1)
            $monthDate = \Carbon\Carbon::create($hYear, $hMonthNum, 1);

            // Cari benchmark yang berlaku untuk bulan ini
            foreach ($benchmarks as $benchmark) {
            $periodStart = $benchmark->period_start ? \Carbon\Carbon::parse($benchmark->period_start) : null;
            $periodEnd = $benchmark->period_end ? \Carbon\Carbon::parse($benchmark->period_end) : null;

            // Cek apakah monthDate berada dalam rentang period_start dan period_end
            $isInPeriod = false;

            if ($periodStart) {
            // Month date >= period_start
            if ($monthDate->greaterThanOrEqualTo($periodStart->startOfMonth())) {
            // Jika period_end null, berarti berlaku selamanya
            if ($periodEnd === null) {
            $isInPeriod = true;
            }
            // Jika ada period_end, cek apakah monthDate <= period_end
                elseif ($monthDate->lessThanOrEqualTo($periodEnd->endOfMonth())) {
                $isInPeriod = true;
                }
                }
                }

                // Jika benchmark ini berlaku untuk bulan ini, simpan per region
                if ($isInPeriod) {
                $regionName = $benchmark->region_name ?? $benchmark->regionType->type ?? 'Unknown';

                // Simpan benchmark value
                if (!isset($benchmarkData[$hMonthNum])) {
                $benchmarkData[$hMonthNum] = [];
                }
                $benchmarkData[$hMonthNum][$regionName] = $benchmark->benchmark_value;

                // Tambahkan region name ke list (untuk header)
                if (!in_array($regionName, $regionNames)) {
                $regionNames[] = $regionName;
                }
                }
                }
                }
                }
                }

                // Hitung colspan untuk header benchmark
                $benchmarkColspan = count($regionNames);
                @endphp

                <div class="overflow-x-auto border border-gray-200 rounded">
                    <table class="w-full border-collapse text-sm bg-white">
                        <thead>
                            <tr>
                                <th rowspan="2" class="border border-gray-300 p-2 bg-gray-50 font-semibold text-gray-700 text-center">BULAN</th>
                                <th rowspan="2" class="border border-gray-300 p-2 bg-blue-600 text-white font-semibold text-center">STANDAR (%)</th>
                                <th rowspan="2" class="border border-gray-300 p-2 bg-green-600 text-white font-semibold text-center">HASIL SAAT INI (%)</th>
                                @if($benchmarkColspan > 0)
                                <th colspan="{{ $benchmarkColspan }}" class="border border-gray-300 p-2 bg-yellow-500 text-white font-semibold text-center">BENCHMARK (%)</th>
                                @else
                                <th rowspan="2" class="border border-gray-300 p-2 bg-yellow-500 text-white font-semibold text-center">BENCHMARK (%)</th>
                                @endif
                            </tr>
                            @if($benchmarkColspan > 0)
                            <tr>
                                @foreach($regionNames as $regionName)
                                <th class="border border-gray-300 p-2 bg-yellow-400 text-white text-xs font-semibold text-center">{{ $regionName }}</th>
                                @endforeach
                            </tr>
                            @endif
                        </thead>
                        <tbody>

                            @foreach ($historicalData ?? [] as $data)
                            @php
                            $monthName = $data['month_short'] ?? '-';
                            $monthNum = $monthNamesReverse[$monthName] ?? null;
                            $percentage = $data['percentage'] ?? null;
                            $year = $data['year'] ?? $laporan->report_year;
                            $isCurrentMonth = $monthNum == $laporan->report_month && $year == $laporan->report_year;
                            @endphp

                            <tr style="{{ $isCurrentMonth ? 'background: #dbeafe; font-weight: 600;' : '' }}">
                                <td style="font-weight: 600;">
                                    {{ strtoupper($monthName) }} {{ $year }}
                                    @if ($isCurrentMonth)
                                    <span style="color: #2563eb; font-size: 8pt;"> ●</span>
                                    @endif
                                </td>
                                <td class="standard-column" style="text-align: center;">
                                    {{ number_format($standard, 0) }}%
                                </td>
                                <td
                                    style="text-align: center; {{ $isCurrentMonth ? 'font-weight: 700; font-size: 11pt;' : '' }}">
                                    @if ($percentage !== null)
                                    <span
                                        style="color: {{ $percentage >= $standard ? '#059669' : '#dc2626' }}; font-weight: 600;">
                                        {{ number_format($percentage, 2) }}%
                                    </span>
                                    @else
                                    <span style="color: #94a3b8;">-</span>
                                    @endif
                                </td>

                                @if($benchmarkColspan > 0)
                                @foreach($regionNames as $regionName)
                                @php
                                $benchmarkValue = $benchmarkData[$monthNum][$regionName] ?? null;
                                @endphp
                                <td style="text-align: center;">
                                    @if($benchmarkValue !== null)
                                    {{ number_format($benchmarkValue, 2) }}%
                                    @else
                                    <span style="color: #94a3b8;">-</span>
                                    @endif
                                </td>
                                @endforeach
                                @else
                                <td style="text-align: center;">
                                    <span style="color: #94a3b8;">-</span>
                                </td>
                                @endif
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Detail info per bulan -->
                    <div style="margin-top: 15px; font-size: 9pt; color: #64748b;">
                        <strong>Keterangan:</strong>
                        <ul style="margin-left: 20px; margin-top: 5px;">
                            <li><strong>Standar</strong>: Target standar yang harus dicapai ({{ number_format($standard, 0) }}%)
                            </li>
                            <li><strong>Hasil Saat Ini</strong>: Pencapaian aktual di rumah sakit (warna hijau = tercapai, merah =
                                belum tercapai)</li>
                            <li><strong>Benchmark</strong>: Perbandingan dengan standar nasional/provinsi untuk indikator ini</li>
                            <li>Baris dengan tanda <span style="color: #2563eb;">●</span> adalah periode laporan aktif</li>
                        </ul>

                        <strong style="margin-top: 15px; display: block;">Detail Capaian per Bulan:</strong>
                        <ul style="margin-left: 20px; margin-top: 5px;">
                            @foreach ($historicalData ?? [] as $data)
                            <li style="margin-bottom: 3px;">
                                <strong>{{ $data['month'] }}:</strong>
                                @if ($data['percentage'] !== null)
                                <span
                                    style="color: {{ $data['percentage'] >= $standard ? '#059669' : '#dc2626' }}; font-weight: 600;">
                                    {{ number_format($data['percentage'], 2) }}%
                                </span>
                                <span style="color: #64748b; font-size: 8pt;">
                                    (N={{ number_format($data['numerator']) }},
                                    D={{ number_format($data['denominator']) }})
                                </span>
                                @else
                                <span style="color: #94a3b8;">Data belum tersedia</span>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- Analysis Box -->
                <div class="bg-blue-50 border border-blue-300 rounded p-6 mb-6">
                    <h4 class="text-base font-semibold text-blue-900 mb-4">📊 Analisis dan Interpretasi Data</h4>

                    @if($selectedNote)
                    <!-- Analysis from ImutDataNote -->
                    <div style="margin-bottom: 15px;">
                        <strong>Analisis Periode {{ $selectedNote->period_display ?? $periode }}:</strong>
                        <p style="margin-left: 15px; margin-top: 5px; line-height: 1.7;">
                            {{ $selectedNote->analysis }}
                        </p>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <strong>Rekomendasi Tindak Lanjut:</strong>
                        <div style="margin-left: 15px; margin-top: 5px; white-space: pre-line; line-height: 1.7;">
                            {{ $selectedNote->recommendation }}
                        </div>
                    </div>

                    @if($selectedNote->additional_notes)
                    <div style="margin-bottom: 0;">
                        <strong>Catatan Tambahan:</strong>
                        <p style="margin-left: 15px; margin-top: 5px; line-height: 1.7;">
                            {{ $selectedNote->additional_notes }}
                        </p>
                    </div>
                    @endif
                    @else
                    <!-- Fallback: Auto-generated analysis -->
                    <div style="margin-bottom: 15px;">
                        <strong>1. Capaian Indikator:</strong>
                        <p style="margin-left: 15px; margin-top: 5px;">
                            Capaian indikator <strong>{{ $imutData->title }}</strong> pada periode
                            <strong>{{ $periode }}</strong> adalah
                            <strong>{{ number_format($overallPercentage, 2) }}%</strong>.
                            Target standar yang ditetapkan adalah <strong>≥ {{ number_format($standard, 0) }}%</strong>.

                            @if ($isAchieved)
                            <span style="color: #065f46; font-weight: 600;">
                                ✓ Indikator ini telah memenuhi standar yang ditetapkan.
                            </span>
                            @else
                            <span style="color: #991b1b; font-weight: 600;">
                                ✗ Indikator ini belum memenuhi standar yang ditetapkan
                                (kurang {{ number_format($standard - $overallPercentage, 2) }}%).
                            </span>
                            @endif
                        </p>
                    </div>

                    @if (isset($historicalData) && count($historicalData) >= 2)
                    <div style="margin-bottom: 15px;">
                        <strong>2. Analisis Tren:</strong>
                        @php
                        $validData = array_filter($historicalData, fn($d) => $d['percentage'] !== null);
                        if (count($validData) >= 2) {
                        $lastData = end($validData);
                        $prevData = prev($validData);
                        $trend = $lastData['percentage'] - $prevData['percentage'];
                        $trendClass = $trend > 0 ? 'trend-up' : ($trend < 0 ? 'trend-down' : 'trend-stable' );
                            $trendText=$trend> 0 ? 'peningkatan' : ($trend < 0 ? 'penurunan' : 'stabil' );
                                }
                                @endphp
                                @if (count($validData)>= 2)
                                <p style="margin-left: 15px; margin-top: 5px;">
                                    Hasil monitoring menunjukkan adanya
                                    <span class="{{ $trendClass }}" style="font-weight: 600;">
                                        {{ $trendText }} sebesar {{ number_format(abs($trend), 2) }}%
                                    </span>
                                    dari periode sebelumnya ({{ $prevData['month_short'] }}:
                                    {{ number_format($prevData['percentage'], 2) }}%
                                    → {{ $lastData['month_short'] }}: {{ number_format($lastData['percentage'], 2) }}%).

                                    @if ($trend > 0)
                                    Ini menunjukkan perbaikan kinerja yang positif.
                                    @elseif($trend < 0)
                                        Perlu dilakukan evaluasi untuk mengatasi penurunan ini.
                                        @else
                                        Capaian relatif konsisten.
                                        @endif
                                        </p>
                                        @else
                                        <p style="margin-left: 15px; margin-top: 5px;">
                                            Data historis belum cukup untuk analisis tren.
                                        </p>
                                        @endif
                    </div>
                    @endif

                    <div style="margin-bottom: 15px;">
                        <strong>3. Capaian Per Unit Kerja:</strong>
                        <p style="margin-left: 15px; margin-top: 5px;">
                            Dari <strong>{{ $summary['total_unit_kerja'] }}</strong> unit kerja yang dinilai:
                        </p>
                        <ul style="margin-left: 30px; margin-top: 5px;">
                            @php
                            $achievedUnits = $unitKerjaData->where('percentage', '>=', $standard)->count();
                            $notAchievedUnits = $unitKerjaData->where('percentage', '<', $standard)->count();
                                @endphp
                                <li><strong>{{ $achievedUnits }}</strong> unit kerja
                                    ({{ number_format(($achievedUnits / $summary['total_unit_kerja']) * 100, 1) }}%) telah mencapai
                                    standar</li>
                                <li><strong>{{ $notAchievedUnits }}</strong> unit kerja
                                    ({{ number_format(($notAchievedUnits / $summary['total_unit_kerja']) * 100, 1) }}%) belum mencapai
                                    standar</li>
                        </ul>
                    </div>

                    <div style="margin-bottom: 0;">
                        <strong>4. Rekomendasi Tindak Lanjut:</strong>
                        <ul style="margin-left: 30px; margin-top: 5px;">
                            @if ($isAchieved)
                            <li>Pertahankan dan tingkatkan capaian melalui monitoring rutin</li>
                            <li>Dokumentasikan <em>best practices</em> dari unit yang berhasil</li>
                            <li>Berikan apresiasi kepada unit kerja yang mencapai target</li>
                            <li>Lakukan evaluasi berkala untuk memastikan konsistensi</li>
                            @else
                            <li>Identifikasi akar masalah di unit kerja yang belum mencapai target</li>
                            <li>Lakukan <em>root cause analysis</em> untuk penurunan capaian</li>
                            <li>Buat rencana perbaikan (<em>action plan</em>) dengan timeline jelas</li>
                            <li>Tingkatkan sosialisasi dan pelatihan terkait indikator ini</li>
                            <li>Lakukan monitoring lebih intensif pada unit yang belum mencapai target</li>
                            @endif
                        </ul>
                    </div>
                    @endif
                </div>


                <!-- Data Section by Unit Kerja -->
                <div class="bg-white border border-gray-200 rounded p-6 mb-6">
                    <div class="text-lg font-bold mb-4 text-gray-900">Data Per Unit Kerja</div>

                    @if ($unitKerjaData && $unitKerjaData->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse text-sm bg-white">
                            <thead>
                                <tr class="bg-gray-50">
                                    <th class="border border-gray-300 p-2 text-center font-semibold text-gray-700">No</th>
                                    <th class="border border-gray-300 p-2 font-semibold text-gray-700">Unit Kerja</th>
                                    <th class="border border-gray-300 p-2 text-center font-semibold text-gray-700">N</th>
                                    <th class="border border-gray-300 p-2 text-center font-semibold text-gray-700">D</th>
                                    <th class="border border-gray-300 p-2 text-center font-semibold text-gray-700">Persentase</th>
                                    <th class="border border-gray-300 p-2 text-center font-semibold text-gray-700">Standar</th>
                                    <th class="border border-gray-300 p-2 text-center font-semibold text-gray-700">Status</th>
                                    <th class="border border-gray-300 p-2 font-semibold text-gray-700">Profil</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($unitKerjaData as $index => $data)
                                @php
                                $percentage = $data->percentage ?? 0;
                                $dataStandard = $data->imut_standard ?? $standard;
                                $dataAchieved = $percentage >= $dataStandard;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $data->unit_kerja ?? '-' }}</td>
                                    <td class="text-center">{{ number_format($data->numerator_value ?? 0, 0) }}</td>
                                    <td class="text-center">{{ number_format($data->denominator_value ?? 0, 0) }}</td>
                                    <td class="text-right font-semibold">{{ number_format($percentage, 2) }}%</td>
                                    <td class="text-center">{{ number_format($dataStandard, 0) }}%</td>
                                    <td class="text-center">
                                        @if ($dataAchieved)
                                        <span style="color: #065f46; font-weight: 600;">✓ Tercapai</span>
                                        @else
                                        <span style="color: #991b1b; font-weight: 600;">✗ Belum</span>
                                        @endif
                                    </td>
                                    <td style="font-size: 9pt;">{{ $data->imut_profil ?? '-' }}</td>
                                </tr>
                                @endforeach

                                <!-- Summary Row -->
                                <tr style="background: #f0f9ff; font-weight: bold;">
                                    <td colspan="2" class="text-center">TOTAL / RATA-RATA</td>
                                    <td class="text-center">{{ number_format($summary['total_numerator'] ?? 0, 0) }}</td>
                                    <td class="text-center">{{ number_format($summary['total_denominator'] ?? 0, 0) }}</td>
                                    <td class="text-right" style="color: #1e40af;">
                                        {{ number_format($summary['average_percentage'] ?? 0, 2) }}%
                                    </td>
                                    <td class="text-center">{{ number_format($standard, 0) }}%</td>
                                    <td class="text-center">
                                        @if ($isAchieved)
                                        <span style="color: #065f46;">✓ Tercapai</span>
                                        @else
                                        <span style="color: #991b1b;">✗ Belum Tercapai</span>
                                        @endif
                                    </td>
                                    <td>-</td>
                                </tr>
                            </tbody>
                        </table>
                        @else
                        <div class="text-center py-8 text-gray-500">
                            Tidak ada data unit kerja yang tersedia untuk indikator ini.
                        </div>
                        @endif
                    </div>
                </div>
        </div>

        <!-- Analysis Section (if available) -->
        @if ($unitKerjaData->where('analysis', '!=', null)->isNotEmpty())
        <div class="bg-white border border-gray-200 rounded p-6 mb-6">
            <div class="text-lg font-bold mb-4 text-gray-900">Analisis Per Unit Kerja</div>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm bg-white">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border border-gray-300 p-2 text-center font-semibold text-gray-700">No</th>
                            <th class="border border-gray-300 p-2 font-semibold text-gray-700">Unit Kerja</th>
                            <th class="border border-gray-300 p-2 font-semibold text-gray-700">Analisis</th>
                            <th class="border border-gray-300 p-2 font-semibold text-gray-700">Rekomendasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach ($unitKerjaData as $data)
                        @if ($data->analysis || $data->recommendations)
                        <tr>
                            <td class="text-center">{{ $no++ }}</td>
                            <td>{{ $data->unit_kerja ?? '-' }}</td>
                            <td>{{ $data->analysis ?? '-' }}</td>
                            <td>{{ $data->recommendations ?? '-' }}</td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif

            <!-- Footer & Signature -->
            <div class="mt-10 border-t-2 border-gray-300 pt-6">
                <div class="flex justify-between mt-10">
                    <div class="text-center w-56">
                        <div class="text-sm mb-20">Mengetahui,<br>Kepala Bagian Mutu</div>
                        <div class="text-sm font-bold border-t-2 border-black pt-2">(...........................)</div>
                    </div>
                    <div class="text-center w-56">
                        <div class="text-sm mb-20">{{ now()->translatedFormat('d F Y') }},<br>Penanggung Jawab</div>
                        <div class="text-sm font-bold border-t-2 border-black pt-2">{{ $laporan->createdBy->name ?? '(...........................)' }}</div>
                    </div>
                </div>

                <div class="text-center mt-6 text-sm text-gray-500">
                    Dokumen ini dibuat secara otomatis oleh Sistem Informasi Indikator Mutu (SI-IMUT)
                </div>
            </div>

        </div>

        <!-- Preview Controls -->
        <div class="no-print fixed bottom-5 right-5 flex gap-3 z-50">
            <button onclick="history.back()" class="px-4 py-2 bg-gray-600 text-white font-semibold rounded hover:bg-gray-700">
                ← Kembali
            </button>
            <button onclick="window.print()" class="px-4 py-2 bg-green-600 text-white font-semibold rounded hover:bg-green-700">
                🖨️ Cetak
            </button>
        </div>

        <!-- Print Info Banner (hidden on print) -->
        <div class="no-print" style="position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 999; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 30px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); max-width: 600px; text-align: center;">
            <div style="font-weight: bold; font-size: 13px; margin-bottom: 5px;">📌 Penting: Pengaturan Print</div>
            <div style="font-size: 11px; line-height: 1.5;">
                Saat print, pastikan aktifkan <strong>"Background Graphics"</strong> di browser Anda agar chart dan warna tercetak dengan sempurna.
                <br>
                <span style="font-size: 10px; opacity: 0.9;">Chrome/Edge: More settings → ✓ Background graphics | Firefox: ✓ Print backgrounds</span>
            </div>
        </div>

        <script src="{{ asset('js/print-report.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @php
                // Prepare chart data - menggunakan data per bulan
                $chartLabels = array_map(fn($d) => $d['month_short'].
                    ' '.$d['year'], $historicalData ?? []);
                $chartData = array_map(fn($d) => $d['percentage'] ?? 0, $historicalData ?? []);
                $standard = $imutData - > standard ?? 100;

                // Prepare benchmark chart data per region
                $benchmarkChartData = [];
                $benchmarkColors = ['#f59e0b', '#8b5cf6', '#10b981', '#ef4444', '#06b6d4', '#ec4899', '#14b8a6'];

                foreach($regionNames ?? [] as $index => $regionName) {
                    $regionData = [];
                    foreach($historicalData ?? [] as $hData) {
                        $hMonthName = $hData['month_short'] ?? '';
                        $hMonthNum = $monthNamesReverse[$hMonthName] ?? null;

                        // Ambil nilai benchmark untuk region ini di bulan ini
                        $benchmarkValue = ($hMonthNum !== null && isset($benchmarkData[$hMonthNum][$regionName])) ?
                            $benchmarkData[$hMonthNum][$regionName] :
                            null;

                        $regionData[] = $benchmarkValue;
                    }

                    $benchmarkChartData[] = [
                        'name' => 'Benchmark: '.$regionName,
                        'data' => $regionData,
                        'color' => $benchmarkColors[$index % count($benchmarkColors)]
                    ];
                }
                @endphp

                // Prepare all series (actual + standard + benchmarks)
                var allSeries = [{
                    name: 'Pencapaian Aktual',
                    data: {
                        !!json_encode($chartData) !!
                    }
                }, {
                    name: 'Target Standar',
                    data: Array({
                        !!count($chartData) !!
                    }).fill({
                        {
                            $standard
                        }
                    })
                }];

                // Add benchmark series
                @foreach($benchmarkChartData as $bData)
                allSeries.push({
                    name: {
                        !!json_encode($bData['name']) !!
                    },
                    data: {
                        !!json_encode($bData['data']) !!
                    }
                });
                @endforeach

                // Chart configuration
                var options = {
                    series: allSeries,
                    chart: {
                        type: 'line',
                        height: 380,
                        toolbar: {
                            show: false,
                        },
                        zoom: {
                            enabled: false
                        }
                    },
                    colors: ['#3b82f6', '#ef4444', @foreach($benchmarkChartData as $bData) {
                        !!json_encode($bData['color']) !!
                    }, @endforeach],
                    dataLabels: {
                        enabled: true,
                        enabledOnSeries: [0], // Hanya tampilkan di series 0 (Pencapaian Aktual)
                        formatter: function(val) {
                            return val > 0 ? val.toFixed(2) + '%' : '-';
                        },
                        style: {
                            fontSize: '11px',
                            fontWeight: 600,
                            colors: ['#1e40af']
                        },
                        background: {
                            enabled: true,
                            foreColor: '#fff',
                            padding: 6,
                            borderRadius: 4,
                            borderWidth: 1,
                            borderColor: '#3b82f6',
                            opacity: 0.9
                        }
                    },
                    stroke: {
                        curve: 'smooth',
                        width: [4, 2, @foreach($benchmarkChartData as $bData) 3, @endforeach],
                        dashArray: [0, 5, @foreach($benchmarkChartData as $bData) 0, @endforeach]
                    },
                    markers: {
                        size: [7, 0, @foreach($benchmarkChartData as $bData) 5, @endforeach],
                        strokeWidth: 2,
                        strokeColors: ['#fff'],
                        colors: ['#3b82f6', '#ef4444', @foreach($benchmarkChartData as $bData) {
                            !!json_encode($bData['color']) !!
                        }, @endforeach],
                        hover: {
                            size: 9,
                            sizeOffset: 3
                        }
                    },
                    grid: {
                        borderColor: '#e2e8f0',
                        strokeDashArray: 4,
                        xaxis: {
                            lines: {
                                show: true
                            }
                        },
                        yaxis: {
                            lines: {
                                show: true
                            }
                        },
                        padding: {
                            top: 0,
                            right: 20,
                            bottom: 0,
                            left: 20
                        }
                    },
                    xaxis: {
                        categories: {
                            !!json_encode($chartLabels) !!
                        },
                        labels: {
                            style: {
                                fontSize: '12px',
                                fontWeight: 600,
                                colors: '#475569'
                            }
                        },
                        axisBorder: {
                            show: true,
                            color: '#cbd5e1'
                        },
                        axisTicks: {
                            show: true,
                            color: '#cbd5e1'
                        }
                    },
                    yaxis: {
                        title: {
                            text: 'Persentase Pencapaian (%)',
                            style: {
                                fontSize: '13px',
                                fontWeight: 600,
                                color: '#1e40af'
                            }
                        },
                        labels: {
                            formatter: function(val) {
                                return val.toFixed(0) + '%';
                            },
                            style: {
                                fontSize: '11px',
                                fontWeight: 500,
                                colors: '#64748b'
                            }
                        },
                        min: 0,
                        max: Math.max(100, {
                            {
                                $standard
                            }
                        }) + 10
                    },
                    legend: {
                        position: 'top',
                        horizontalAlign: 'right',
                        fontSize: '13px',
                        fontWeight: 600,
                        markers: {
                            width: 14,
                            height: 14,
                            radius: 3
                        },
                        itemMargin: {
                            horizontal: 15,
                            vertical: 5
                        }
                    },
                    tooltip: {
                        shared: true,
                        intersect: false,
                        theme: 'light',
                        style: {
                            fontSize: '12px'
                        },
                        y: {
                            formatter: function(val, {
                                seriesIndex,
                                dataPointIndex
                            }) {
                                // Series 0 = Pencapaian Aktual (dengan N/D)
                                if (seriesIndex === 0) {
                                    @php
                                    $tooltipData = array_map(function($d) {
                                        return [
                                            'n' => $d['numerator'],
                                            'd' => $d['denominator'],
                                        ];
                                    }, $historicalData ?? []);
                                    @endphp
                                    var data = {
                                        !!json_encode($tooltipData) !!
                                    };
                                    if (data[dataPointIndex]) {
                                        return val !== null ? val.toFixed(2) + '% (N=' + data[dataPointIndex].n + ', D=' +
                                            data[dataPointIndex].d + ')' : '-';
                                    }
                                }
                                // Series lainnya (Standard & Benchmark)
                                return val !== null ? val.toFixed(2) + '%' : '-';
                            }
                        }
                    },
                    annotations: {
                        yaxis: [{
                            y: {
                                {
                                    $standard
                                }
                            },
                            borderColor: '#ef4444',
                            strokeDashArray: 5,
                            label: {
                                borderColor: '#ef4444',
                                style: {
                                    color: '#fff',
                                    background: '#ef4444',
                                    fontSize: '11px',
                                    fontWeight: 600
                                },
                                text: 'Target: ' + {
                                    {
                                        $standard
                                    }
                                } + '%',
                                position: 'right',
                                offsetX: 0,
                                offsetY: 0
                            }
                        }]
                    }
                };

                var chart = new ApexCharts(document.querySelector("#trendChart"), options);

                // Simpan instance chart ke window untuk akses dari print handler
                window.chartInstance = chart;

                chart.render();

                // Auto print jika ada parameter auto_print
                @if(request() - > get('auto_print') === '1')
                // Tunggu chart selesai render baru trigger print
                chart.render().then(() => {
                    setTimeout(() => {
                        window.print();
                    }, 1000);
                });
                @endif
            });
        </script>
</body>

</html>