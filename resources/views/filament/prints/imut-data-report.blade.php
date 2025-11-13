<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan IMUT - {{ $laporan->name ?? 'Laporan' }}</title>
    <link rel="stylesheet" href="{{ asset('css/print-report.css') }}">
</head>

<body>
    @php
        // Prepare data
        $laporan = $laporan ?? null;
        $dataByCategory = $dataByCategory ?? collect();
        $summary = $summary ?? null;

        if (!$laporan) {
            echo '<div class="empty-state">Data laporan tidak ditemukan.</div>';
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
    @endphp

    <!-- Header -->
    <div class="header">
        <div class="institution">SISTEM INFORMASI INDIKATOR MUTU</div>
        <h1>Laporan IMUT Per Laporan</h1>
        <h2>{{ $laporan->name }}</h2>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <div class="info-row">
            <span class="info-label">Nama Laporan:</span>
            <span class="info-value">{{ $laporan->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Periode Asesmen:</span>
            <span class="info-value">{{ $periode }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="info-value">
                <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
            </span>
        </div>
        <div class="info-row">
            <span class="info-label">Dibuat oleh:</span>
            <span class="info-value">{{ $laporan->createdBy->name ?? 'Tidak diketahui' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Tanggal Cetak:</span>
            <span class="info-value">{{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
        </div>
    </div>

    <!-- Summary Section -->
    @if ($summary)
        <div class="summary-section">
            <div class="summary-title">Ringkasan Laporan</div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-item-label">Total IMUT Data</div>
                    <div class="summary-item-value">{{ number_format($summary['total_imut_data'] ?? 0) }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-item-label">Total Unit Kerja</div>
                    <div class="summary-item-value">{{ number_format($summary['total_unit_kerja'] ?? 0) }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-item-label">Rata-rata Pencapaian</div>
                    <div class="summary-item-value">{{ number_format($summary['average_percentage'] ?? 0, 2) }}%</div>
                </div>
                <div class="summary-item">
                    <div class="summary-item-label">Data Terisi</div>
                    <div class="summary-item-value">{{ number_format($summary['filled_count'] ?? 0) }} /
                        {{ number_format($summary['total_count'] ?? 0) }}</div>
                </div>
            </div>
        </div>
    @endif

    <!-- Data Section by Category -->
    <div class="table-section">
        <div class="table-title">Data IMUT Per Kategori</div>

        @if ($dataByCategory && $dataByCategory->isNotEmpty())
            <table>
                <thead>
                    <tr>
                        <th style="width: 5%;">No</th>
                        <th style="width: 35%;">Nama IMUT Data</th>
                        <th class="text-center" style="width: 10%;">N (Pembilang)</th>
                        <th class="text-center" style="width: 10%;">D (Penyebut)</th>
                        <th class="text-center" style="width: 12%;">Persentase</th>
                        <th class="text-center" style="width: 10%;">Standar</th>
                        <th class="text-center" style="width: 18%;">Status Capaian</th>
                    </tr>
                </thead>
                <tbody>
                    @php $globalNo = 1; @endphp

                    @foreach ($dataByCategory as $category => $items)
                        <!-- Category Header -->
                        <tr class="category-header">
                            <td colspan="7">
                                <strong>{{ $category }}</strong>
                                <span style="font-weight: normal; font-size: 9pt;">({{ $items->count() }} item)</span>
                            </td>
                        </tr>

                        <!-- Category Items -->
                        @foreach ($items as $item)
                            @php
                                $percentage = $item->percentage ?? 0;
                                $standard = $item->imut_standard ?? 0;
                                $isAchieved = $percentage >= $standard;
                            @endphp
                            <tr>
                                <td class="text-center">{{ $globalNo++ }}</td>
                                <td>{{ $item->imut_data_title ?? '-' }}</td>
                                <td class="text-center">{{ number_format($item->total_numerator ?? 0, 0) }}</td>
                                <td class="text-center">{{ number_format($item->total_denominator ?? 0, 0) }}</td>
                                <td class="text-right font-semibold">{{ number_format($percentage, 2) }}%</td>
                                <td class="text-center">{{ number_format($standard, 0) }}%</td>
                                <td class="text-center">
                                    @if ($isAchieved)
                                        <span style="color: #065f46; font-weight: 600;">✓ Tercapai</span>
                                    @else
                                        <span style="color: #991b1b; font-weight: 600;">✗ Belum Tercapai</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty-state">
                Tidak ada data IMUT yang tersedia untuk laporan ini.
            </div>
        @endif
    </div>

    <!-- Footer & Signature -->
    <div class="footer">
        <div style="margin-bottom: 10px;">
            <strong>Catatan:</strong>
            <ul style="margin-left: 20px; margin-top: 5px;">
                <li>N = Numerator (Pembilang): Jumlah kejadian yang memenuhi kriteria</li>
                <li>D = Denominator (Penyebut): Jumlah total kejadian yang diobservasi</li>
                <li>Persentase = (N / D) × 100%</li>
                <li>Status Tercapai jika Persentase ≥ Standar IMUT</li>
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

    <script src="{{ asset('js/print-report.js') }}"></script>
</body>

</html>
