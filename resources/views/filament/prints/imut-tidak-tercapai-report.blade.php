<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Indikator Mutu Tidak Tercapai - {{ $laporan->name ?? 'Laporan' }}</title>
    <link rel="stylesheet" href="{{ asset('css/print-report.css') }}">
    @vite(['resources/css/app.css'])
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: {{ $orientation === 'portrait' ? 'A4 portrait' : 'A4 landscape' }};
            margin: 10mm 15mm 15mm 15mm;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 10pt;
            color: #1e293b;
            background: #f8fafc;
            padding: 0;
            margin: 0;
        }

        .paper-container {
            max-width: {{ $orientation === 'portrait' ? '210mm' : '297mm' }};
            min-height: {{ $orientation === 'portrait' ? '297mm' : '210mm' }};
            margin: 20px auto;
            background: #ffffff;
            padding: 15mm 20mm 20mm 20mm;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            box-sizing: border-box;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #0f172a;
            color: #ffffff;
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        }

        .toolbar-group {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .btn-primary {
            background-color: #2563eb;
            color: #ffffff;
        }
        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-secondary {
            background-color: #334155;
            color: #f1f5f9;
            border-color: #475569;
        }
        .btn-secondary:hover {
            background-color: #475569;
        }

        .btn-outline {
            background-color: transparent;
            color: #cbd5e1;
            border-color: #475569;
        }
        .btn-outline:hover {
            background-color: #1e293b;
            color: #ffffff;
        }

        .btn-active {
            background-color: #0284c7;
            color: #ffffff;
            border-color: #38bdf8;
        }

        /* Compact Summary Strip */
        .summary-strip {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            margin-bottom: 16px;
            padding: 8px 12px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 8.5pt;
        }

        .summary-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 3px 10px;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            background: #ffffff;
            color: #334155;
        }

        .summary-chip strong {
            font-size: 9.5pt;
            color: #0f172a;
        }

        .summary-chip-danger {
            background: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }
        .summary-chip-danger strong {
            color: #dc2626;
        }

        /* Formal Table */
        .formal-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            margin-bottom: 24px;
        }

        .formal-table th,
        .formal-table td {
            border: 1px solid #94a3b8;
            padding: 6px 8px;
            vertical-align: middle;
        }

        .formal-table th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
            font-size: 8.5pt;
            letter-spacing: 0.5px;
        }

        .formal-table td.text-center {
            text-align: center;
        }

        .formal-table td.align-top {
            vertical-align: top;
        }

        .badge-cat {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 8pt;
            background: #e2e8f0;
            color: #1e293b;
        }

        .badge-fail {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 8.5pt;
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-std {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 8.5pt;
            background: #dcfce7;
            color: #166534;
        }

        /* Signature Section */
        .signature-section {
            margin-top: 35px;
            display: flex;
            justify-content: space-between;
            page-break-inside: avoid;
            font-size: 9.5pt;
        }

        .signature-box {
            text-align: center;
            width: 250px;
        }

        .signature-space {
            height: 65px;
        }

        .signature-name {
            font-weight: 700;
            text-decoration: underline;
            color: #0f172a;
        }

        .signature-title {
            color: #475569;
            font-size: 8.5pt;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: #ffffff;
                color: #000000;
            }

            .paper-container {
                box-shadow: none;
                margin: 0;
                padding: 0;
                max-width: 100%;
                min-height: auto;
            }

            .formal-table th,
            .summary-strip,
            .summary-chip,
            .badge-cat,
            .badge-fail,
            .badge-std {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar Atas (No-Print) -->
    <div class="toolbar no-print">
        <div class="toolbar-group">
            <span style="font-weight: 600; font-size: 14px;">Cetak Daftar Indikator Mutu Tidak Tercapai</span>
        </div>
        <div class="toolbar-group">
            <span style="font-size: 13px; color: #94a3b8;">Orientasi:</span>
            <a href="{{ request()->fullUrlWithQuery(['orientation' => 'landscape']) }}" class="btn {{ $orientation === 'landscape' ? 'btn-active' : 'btn-outline' }}">Landscape</a>
            <a href="{{ request()->fullUrlWithQuery(['orientation' => 'portrait']) }}" class="btn {{ $orientation === 'portrait' ? 'btn-active' : 'btn-outline' }}">Portrait</a>
            <a href="{{ route('export.imut-tidak-tercapai', array_filter(['period_type' => $periodContext['periodType'] ?? 'month', 'period' => $periodContext['selectedPeriod'] ?? null, 'category_id' => request('category_id'), 'search' => $search ?? ''], fn ($v) => $v !== null && $v !== '')) }}"
               target="_blank"
               class="btn"
               style="background-color: #059669; color: #ffffff;">
                Download Excel
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                    <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4zM1 7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H2a1 1 0 0 1-1-1zm3 4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1z"/>
                </svg>
                Cetak Laporan
            </button>
            <button onclick="window.close()" class="btn btn-secondary">Tutup</button>
        </div>
    </div>

    <!-- Halaman Dokumen Laporan -->
    <div class="paper-container">
        @php
            $headerReportName = $periodContext['reportName'] ?? ($laporan->name ?? '-');
            $headerPeriodType = $periodContext['periodTypeLabel'] ?? 'Bulan';
            $additionalInfo = [
                ['label' => 'Laporan / Periode', 'value' => $headerReportName . ' (' . $headerPeriodType . ')'],
                ['label' => 'Periode Asesmen', 'value' => $periodeText],
                ['label' => 'Tanggal Cetak', 'value' => now()->translatedFormat('d F Y, H:i') . ' WIB'],
            ];
            if (! empty($categoryName)) {
                $additionalInfo[] = ['label' => 'Kategori', 'value' => $categoryName];
            }
            if (! empty(trim($search))) {
                $additionalInfo[] = ['label' => 'Filter Pencarian', 'value' => $search];
            }
        @endphp

        <x-basic-report-header
            title="Daftar Indikator Mutu Tidak Tercapai Per Unit Kerja"
            subtitle="{{ $headerReportName }}"
            :additionalInfo="$additionalInfo" />

        <!-- Compact Summary Strip -->
        <div class="summary-strip">
            <strong style="color: #0f172a; margin-right: 4px;">Ikhtisar:</strong>
            <span class="summary-chip">
                <span>Unit Kerja:</span>
                <strong>{{ number_format($summary['total_units']) }}</strong>
            </span>
            <span class="summary-chip summary-chip-danger">
                <span>Total Tidak Tercapai:</span>
                <strong>{{ number_format($summary['total_indicators']) }}</strong>
            </span>
            @foreach ($summary['by_category'] as $catSummary)
                <span class="summary-chip">
                    <span>{{ $catSummary['label'] }}:</span>
                    <strong>{{ number_format($catSummary['count']) }}</strong>
                </span>
            @endforeach
        </div>

        <!-- Tabel Laporan Formal -->
        <table class="formal-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 24%;">Unit Kerja</th>
                    <th style="width: 39%;">Indikator</th>
                    <th style="width: 12%;">Kategori Indikator</th>
                    <th style="width: 10%;">Capaian</th>
                    <th style="width: 10%;">Nilai Standard</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($unitGroups as $index => $unit)
                    @php
                        $items = $unit->indicators ?? collect();
                        $rowCount = max(1, $items->count());
                        $rowBg = $index % 2 === 1 ? '#f8fafc' : '#ffffff';
                    @endphp
                    @foreach ($items as $item)
                        @php
                            $isFirst = $loop->first;
                            $catLabel = $item->category_short_name ?: ($item->category_name ?: '-');
                            $capaian = (float) ($item->capaian_percentage ?? 0);
                            $standard = \App\Filament\Widgets\ImutTidakTercapai::formatStandard($item->target_operator, $item->target_value);
                        @endphp
                        <tr style="background-color: {{ $rowBg }};">
                            @if ($isFirst)
                                <td rowspan="{{ $rowCount }}" class="text-center align-top" style="font-weight: 700;">
                                    {{ $index + 1 }}
                                </td>
                                <td rowspan="{{ $rowCount }}" class="align-top">
                                    <strong>{{ $unit->unit_name }}</strong>
                                    <div style="font-size: 8pt; color: #991b1b; margin-top: 2px;">
                                        {{ $rowCount }} indikator tidak tercapai
                                    </div>
                                </td>
                            @endif
                            <td>
                                <div style="font-weight: 600;">{{ $item->indicator_title }}</div>
                                <div style="font-size: 8pt; color: #475569; margin-top: 2px;">
                                    @if (($item->months_count ?? 1) > 1)
                                        <strong>Akumulasi {{ $item->months_count }} bln</strong> &bull;
                                    @endif
                                    Num: {{ number_format((float) $item->numerator_value) }} / Den: {{ number_format((float) $item->denominator_value) }}
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge-cat">{{ $catLabel }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge-fail">{{ number_format($capaian, 1) }}%</span>
                            </td>
                            <td class="text-center">
                                <span class="badge-std">{{ $standard['badge'] }}</span>
                                <div style="font-size: 7.5pt; color: #475569; margin-top: 2px;">{{ $standard['label'] }}</div>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 24px; color: #64748b;">
                            Tidak ada indikator yang tidak tercapai pada periode ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Area Pengesahan / Tanda Tangan -->
        <div class="signature-section">
            <div class="signature-box">
                <div>Mengetahui,</div>
                <div class="signature-title">Ketua Komite Mutu & Keselamatan Pasien</div>
                <div class="signature-space"></div>
                <div class="signature-name">( ..................................................... )</div>
                <div class="signature-title">NIP / NIK.</div>
            </div>
            <div class="signature-box">
                <div>Jember, {{ now()->translatedFormat('d F Y') }}</div>
                <div class="signature-title">Petugas Pengelola Data Mutu</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ auth()->user()?->name ?? '( ..................................................... )' }}</div>
                <div class="signature-title">NIP / NIK. {{ auth()->user()?->nik ?? '-' }}</div>
            </div>
        </div>
    </div>

</body>
</html>
