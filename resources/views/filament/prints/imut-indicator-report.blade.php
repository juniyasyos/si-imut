<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Indikator - {{ $imutData->title ?? 'Indikator' }}</title>
    <link rel="stylesheet" href="{{ asset('css/print-report.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body>
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
    <div class="filter-section no-print" style="background: white; padding: 20px; margin-bottom: 20px; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        <form method="GET" action="{{ route('print.preview.imut-indicator-report') }}" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">Filter Periode:</label>
                <select name="period_filter" class="filter-select" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
                    @foreach($periodLabels as $value => $label)
                        <option value="{{ $value }}" {{ $periodFilter === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            @if($availableNotes->isNotEmpty())
            <div>
                <label style="display: block; margin-bottom: 5px; font-weight: 600; font-size: 14px;">Catatan/Analisis:</label>
                <select name="note_id" class="filter-select" style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
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

            <div style="display: flex; align-items: flex-end;">
                <button type="submit" style="padding: 8px 16px; background: #3b82f6; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">
                    🔄 Terapkan Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="institution">SISTEM INFORMASI INDIKATOR MUTU</div>
        <h1>Laporan Triwulan Indikator Mutu</h1>
        <h2>RUMAH SAKIT CITRA HUSADA JEMBER</h2>
    </div>

    <!-- Indicator Info Section -->
    <div class="indicator-info">
        <h3>{{ $imutData->title }}</h3>
        <div class="indicator-detail">
            <div class="indicator-detail-item">
                <div class="indicator-detail-label">Kategori</div>
                <div class="indicator-detail-value">{{ $imutData->categories }}</div>
            </div>
            <div class="indicator-detail-item">
                <div class="indicator-detail-label">Standar Target</div>
                <div class="indicator-detail-value">{{ number_format($standard, 0) }}%</div>
            </div>
            <div class="indicator-detail-item">
                <div class="indicator-detail-label">Pencapaian Rata-rata</div>
                <div class="indicator-detail-value">{{ number_format($overallPercentage, 2) }}%</div>
            </div>
            <div class="indicator-detail-item">
                <div class="indicator-detail-label">Status Capaian</div>
                <div class="indicator-detail-value">
                    <span class="achievement-badge {{ $isAchieved ? 'achievement-met' : 'achievement-not-met' }}">
                        {{ $isAchieved ? '✓ Tercapai' : '✗ Belum Tercapai' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Definition Section -->
    @if (isset($imutData->definition) && $imutData->definition)
        <div class="definition-section">
            <h4>📋 Definisi Operasional</h4>
            <p>{{ $imutData->definition }}</p>
        </div>
    @endif

    <!-- Chart Section -->
    <div class="chart-container">
        <div class="chart-header">
            <div>
                <div class="chart-title">Grafik Tren Pencapaian Indikator</div>
                <div class="chart-subtitle">Perbandingan pencapaian periode yang dipilih</div>
            </div>
        </div>
        <div id="trendChart"></div>
    </div>

    <!-- Comparison Table -->
    <div class="comparison-table-section">
        <div class="table-title">Tabel Perbandingan Pencapaian</div>

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

        <table class="comparison-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 150px; vertical-align: middle;">BULAN</th>
                    <th rowspan="2" style="width: 100px; background: #3b82f6; color: white; vertical-align: middle;">STANDAR (%)</th>
                    <th rowspan="2" style="width: 120px; background: #10b981; color: white; vertical-align: middle;">HASIL SAAT INI (%)</th>
                    @if($benchmarkColspan > 0)
                        <th colspan="{{ $benchmarkColspan }}" style="background: #f59e0b; color: white;">BENCHMARK (%)</th>
                    @else
                        <th rowspan="2" style="width: 120px; background: #f59e0b; color: white; vertical-align: middle;">BENCHMARK (%)</th>
                    @endif
                </tr>
                @if($benchmarkColspan > 0)
                <tr>
                    @foreach($regionNames as $regionName)
                        <th style="width: 100px; background: #fbbf24; color: white; font-size: 9pt;">{{ $regionName }}</th>
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

    <!-- Analysis Box with Better Layout -->
    <div class="analysis-box">
        <h4>📊 Analisis dan Interpretasi Data</h4>

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
                            $trendClass = $trend > 0 ? 'trend-up' : ($trend < 0 ? 'trend-down' : 'trend-stable');
                            $trendText = $trend > 0 ? 'peningkatan' : ($trend < 0 ? 'penurunan' : 'stabil');
                        }
                    @endphp
                    @if (count($validData) >= 2)
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

    <!-- Summary Section -->
    @if ($summary)
        <div class="summary-section">
            <div class="summary-title">Ringkasan Data Indikator</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-item-label">Total Unit Kerja</div>
                    <div class="summary-item-value">{{ number_format($summary['total_unit_kerja'] ?? 0) }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-item-label">Total Numerator (N)</div>
                    <div class="summary-item-value">{{ number_format($summary['total_numerator'] ?? 0) }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-item-label">Total Denominator (D)</div>
                    <div class="summary-item-value">{{ number_format($summary['total_denominator'] ?? 0) }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-item-label">Pencapaian Rata-rata</div>
                    <div class="summary-item-value">{{ number_format($summary['average_percentage'] ?? 0, 2) }}%</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Data Section by Unit Kerja -->
    <div class="table-section">
        <div class="table-title">Data Per Unit Kerja</div>

        @if ($unitKerjaData && $unitKerjaData->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 30%;">Unit Kerja</th>
                        <th class="text-center" style="width: 10%;">N</th>
                        <th class="text-center" style="width: 10%;">D</th>
                        <th class="text-center" style="width: 12%;">Persentase</th>
                        <th class="text-center" style="width: 10%;">Standar</th>
                        <th class="text-center" style="width: 13%;">Status</th>
                        <th style="width: 10%;">Profil</th>
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
                            {{ number_format($summary['average_percentage'] ?? 0, 2) }}%</td>
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
            <div class="empty-state">
                Tidak ada data unit kerja yang tersedia untuk indikator ini.
            </div>
        @endif
    </div>

    <!-- Analysis Section (if available) -->
    @if ($unitKerjaData->where('analysis', '!=', null)->isNotEmpty())
        <div class="table-section">
            <div class="table-title">Analisis Per Unit Kerja</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 25%;">Unit Kerja</th>
                        <th style="width: 35%;">Analisis</th>
                        <th style="width: 35%;">Rekomendasi</th>
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
    <div class="footer">
        <div style="margin-bottom: 10px;">
            <strong>Catatan:</strong>
            <ul style="margin-left: 20px; margin-top: 5px;">
                <li>N = Numerator (Pembilang):
                    {{ $imutData->numerator_description ?? 'Jumlah kejadian yang memenuhi kriteria' }}</li>
                <li>D = Denominator (Penyebut):
                    {{ $imutData->denominator_description ?? 'Jumlah total kejadian yang diobservasi' }}</li>
                <li>Persentase = (N / D) × 100%</li>
                <li>Target standar untuk indikator ini adalah ≥ {{ number_format($standard, 0) }}%</li>
            </ul>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-label">Mengetahui,<br>Kepala Bagian Mutu</div>
                <div class="signature-name">(...........................)</div>
            </div>
            <div class="signature-box">
                <div class="signature-label">{{ now()->translatedFormat('d F Y') }},<br>Penanggung Jawab</div>
                <div class="signature-name">{{ $laporan->createdBy->name ?? '(...........................)' }}</div>
            </div>
        </div>

        <div style="text-align: center; margin-top: 20px; font-size: 8pt; color: #94a3b8;">
            Dokumen ini dibuat secara otomatis oleh Sistem Informasi Indikator Mutu (SI-IMUT)
        </div>
    </div>

    <!-- Preview Controls (hidden on print) -->
    <div class="preview-controls no-print">
        <button id="backBtn" class="secondary">← Kembali</button>
        <button id="printBtn">🖨️ Cetak</button>
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
                $chartLabels = array_map(fn($d) => $d['month_short'] . ' ' . $d['year'], $historicalData ?? []);
                $chartData = array_map(fn($d) => $d['percentage'] ?? 0, $historicalData ?? []);
                $standard = $imutData->standard ?? 100;

                // Prepare benchmark chart data per region
                $benchmarkChartData = [];
                $benchmarkColors = ['#f59e0b', '#8b5cf6', '#10b981', '#ef4444', '#06b6d4', '#ec4899', '#14b8a6'];

                foreach ($regionNames ?? [] as $index => $regionName) {
                    $regionData = [];
                    foreach ($historicalData ?? [] as $hData) {
                        $hMonthName = $hData['month_short'] ?? '';
                        $hMonthNum = $monthNamesReverse[$hMonthName] ?? null;

                        // Ambil nilai benchmark untuk region ini di bulan ini
                        $benchmarkValue = ($hMonthNum !== null && isset($benchmarkData[$hMonthNum][$regionName]))
                            ? $benchmarkData[$hMonthNum][$regionName]
                            : null;

                        $regionData[] = $benchmarkValue;
                    }

                    $benchmarkChartData[] = [
                        'name' => 'Benchmark: ' . $regionName,
                        'data' => $regionData,
                        'color' => $benchmarkColors[$index % count($benchmarkColors)]
                    ];
                }
            @endphp

            // Prepare all series (actual + standard + benchmarks)
            var allSeries = [{
                name: 'Pencapaian Aktual',
                data: {!! json_encode($chartData) !!}
            }, {
                name: 'Target Standar',
                data: Array({!! count($chartData) !!}).fill({{ $standard }})
            }];

            // Add benchmark series
            @foreach ($benchmarkChartData as $bData)
                allSeries.push({
                    name: {!! json_encode($bData['name']) !!},
                    data: {!! json_encode($bData['data']) !!}
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
                colors: ['#3b82f6', '#ef4444', @foreach ($benchmarkChartData as $bData) {!! json_encode($bData['color']) !!}, @endforeach],
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
                    width: [4, 2, @foreach ($benchmarkChartData as $bData) 3, @endforeach],
                    dashArray: [0, 5, @foreach ($benchmarkChartData as $bData) 0, @endforeach]
                },
                markers: {
                    size: [7, 0, @foreach ($benchmarkChartData as $bData) 5, @endforeach],
                    strokeWidth: 2,
                    strokeColors: ['#fff'],
                    colors: ['#3b82f6', '#ef4444', @foreach ($benchmarkChartData as $bData) {!! json_encode($bData['color']) !!}, @endforeach],
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
                    categories: {!! json_encode($chartLabels) !!},
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
                    max: Math.max(100, {{ $standard }}) + 10
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
                                    $tooltipData = array_map(function ($d) {
                                        return [
                                            'n' => $d['numerator'],
                                            'd' => $d['denominator'],
                                        ];
                                    }, $historicalData ?? []);
                                @endphp
                                var data = {!! json_encode($tooltipData) !!};
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
                        y: {{ $standard }},
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
                            text: 'Target: ' + {{ $standard }} + '%',
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
            @if(request()->get('auto_print') === '1')
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
