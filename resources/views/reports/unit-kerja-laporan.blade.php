<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Unit Kerja - {{ $unit->unit_name ?? 'Laporan' }}</title>
    <link rel="stylesheet" href="{{ asset('css/print-report.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    <div class="border-b-2 border-gray-800 pb-4 mb-6">
        <div class="flex items-start justify-between gap-4 md:gap-6 mb-3">
            <!-- Logo Kiri -->
            <div class="w-14 h-14 md:w-16 md:h-16 flex-shrink-0">
                <svg viewBox="0 0 100 100" class="w-full h-full">
                    <circle cx="50" cy="50" r="45" fill="#1e40af" />
                    <path d="M50 20 L50 80 M20 50 L80 50" stroke="white" stroke-width="8" stroke-linecap="round" />
                    <circle cx="50" cy="50" r="12" fill="white" />
                </svg>
            </div>

            <!-- Text Content -->
            <div class="flex-1 text-center">
                <h1 class="text-lg md:text-xl font-bold text-gray-900 mb-1 tracking-wide">RUMAH SAKIT CITRA HUSADA JEMBER</h1>
                <div class="text-xs text-gray-600 mb-2 tracking-tighter">Jl. Contoh No. 123, Jember, Jawa Timur 68100 | Telp: (0331) 123456</div>
                <div class="h-px bg-gray-400 my-2"></div>
                <h2 class="text-sm md:text-base font-bold text-gray-800 uppercase tracking-widest">Laporan IMUT Per Unit Kerja</h2>
                <div class="text-xs text-gray-600 mt-1 tracking-tighter">Sistem Informasi Indikator Mutu (SI-IMUT)</div>
            </div>

            <!-- Logo Kanan -->
            <div class="w-14 h-14 md:w-16 md:h-16 flex-shrink-0">
                <svg viewBox="0 0 100 100" class="w-full h-full">
                    <rect width="100" height="100" rx="10" fill="#059669" />
                    <path d="M30 50 L45 65 L70 35" stroke="white" stroke-width="8" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                </svg>
            </div>
        </div>

        <!-- Document Info Bar -->
        <div class="bg-gray-100 border border-gray-300 rounded px-3 md:px-4 py-2 flex flex-wrap justify-between items-center text-xs gap-2">
            <div><span class="font-semibold text-gray-700">Unit Kerja:</span> <span class="text-gray-600">{{ $unit->unit_name }}</span></div>
            <div><span class="font-semibold text-gray-700">Periode:</span> <span class="text-gray-600">{{ $periodLabel }}</span></div>
            <div><span class="font-semibold text-gray-700">Tanggal Cetak:</span> <span class="text-gray-600">{{ now()->translatedFormat('d F Y, H:i') }} WIB</span></div>
        </div>
    </div>

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
            <span class="text-slate-700">{{ $summary['total_imut_data'] ?? 0 }} indikator</span>
        </div>
        <div class="flex py-2 border-b border-slate-200 last:border-b-0">
            <span class="font-semibold min-w-[150px] text-blue-700">Tanggal Cetak:</span>
            <span class="text-slate-700">{{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
        </div>
    </div>

    <!-- Summary Section -->
    @if ($summary)
    <div class="bg-sky-50 border-l-4 border-sky-400 rounded-md p-5 mb-6">
        <div class="text-sm font-bold text-sky-900 mb-4">📊 Ringkasan Laporan</div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded border border-sky-200 text-center">
                <div class="text-xs text-gray-600 mb-1 font-medium">Total IMUT Data</div>
                <div class="text-2xl font-bold text-sky-900">{{ number_format($summary['total_imut_data'] ?? 0) }}</div>
            </div>
            <div class="bg-white p-4 rounded border border-sky-200 text-center">
                <div class="text-xs text-gray-600 mb-1 font-medium">Data Terisi</div>
                <div class="text-2xl font-bold text-sky-900">{{ number_format($summary['total_data_points'] ?? 0) }}</div>
            </div>
            <div class="bg-white p-4 rounded border border-sky-200 text-center">
                <div class="text-xs text-gray-600 mb-1 font-medium">Rata-rata Pencapaian</div>
                <div class="text-2xl font-bold text-sky-900">{{ number_format($summary['average_percentage'] ?? 0, 1) }}%</div>
            </div>
            <div class="bg-white p-4 rounded border border-sky-200 text-center">
                <div class="text-xs text-gray-600 mb-1 font-medium">Target Tercapai</div>
                <div class="text-2xl font-bold text-sky-900">{{ number_format($summary['achieved_count'] ?? 0) }} / {{ number_format($summary['total_data_points'] ?? 0) }}</div>
            </div>
        </div>
    </div>
    @endif

    <!-- IMUT Data Sections -->
    <div class="mt-8">
        @if ($dataByImut && count($dataByImut) > 0)

        @foreach ($dataByImut as $index => $imut)
        <div class="mb-9 border border-slate-200 rounded-lg overflow-hidden bg-white break-inside-avoid">
            <!-- Header -->
            <div class="bg-gradient-to-r from-blue-700 to-blue-800 text-black px-5 py-4 border-b-2 border-blue-900">
                <h3 class="text-sm font-bold mb-1">{{ $index + 1 }}. {{ $imut['title'] }}</h3>
                <p class="text-xs text-blue-100">Kategori: {{ $imut['category'] }} | Target Standar: {{ $imut['target_operator'] }} {{ $imut['standard'] }}%</p>
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
                                    <td class="border border-slate-200 px-3 py-2 text-center font-semibold text-blue-600">{{ $imut['standard'] }}%</td>
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
                                    <td class="border border-slate-200 px-3 py-2 text-center font-semibold text-blue-600">{{ $imut['target_operator'] }} {{ $imut['standard'] }}%</td>
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
            </div>
        </div>
        @endforeach

        @else
        <div class="p-8 text-center text-gray-500 bg-slate-50 rounded-md">
            Tidak ada data IMUT yang tersedia untuk unit kerja ini.
        </div>
        @endif
    </div>

    <!-- Footer & Signature -->
    <div class="mt-10 pt-5 border-t-2 border-slate-300">
        <div class="mb-5">
            <strong>📝 Catatan:</strong>
            <ul class="ml-5 mt-2 text-xs space-y-1">
                <li>N = Numerator (Pembilang): Jumlah kejadian yang memenuhi kriteria</li>
                <li>D = Denominator (Penyebut): Jumlah total kejadian yang diobservasi</li>
                <li>Persentase = (N / D) × 100%</li>
                <li>Status Tercapai jika Persentase {{ $imut['target_operator'] ?? '>=' }} Target Standar</li>
                <li>Target Standar untuk indikator ini adalah: <strong>{{ $imut['target_operator'] ?? '>=' }} {{ $imut['standard'] ?? 0 }}%</strong></li>
            </ul>
        </div>

        <div class="grid grid-cols-2 gap-12 mt-8">
            <div class="text-center">
                <div class="text-xs mb-24 font-semibold"><br><br>Pengumpul Data</div>
                <div class="text-xs font-bold border-t border-black pt-1">(...........................)</div>
            </div>
            <div class="text-center">
                <div class="text-xs mb-2">
                    <span>Jember, {{ now()->translatedFormat('d F Y') }}</span>
                </div>
                <div class="text-xs mb-24 font-semibold">Validator Data / Penanggung Jawab</div>
                <div class="text-xs font-bold border-t border-black pt-1">(...........................)</div>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-gray-500">
            Dokumen ini dibuat secara otomatis oleh Sistem Informasi Indikator Mutu (SI-IMUT)
        </div>
    </div>

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