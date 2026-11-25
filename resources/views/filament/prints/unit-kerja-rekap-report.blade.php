<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Capaian Indikator Mutu Unit Kerja - {{ $laporan->name ?? 'Laporan' }}</title>
    <link rel="stylesheet" href="{{ asset('css/print-report.css') }}">
    <style>
        @page {
            size: {{ $orientation === 'portrait' ? 'A4 portrait' : 'A4 landscape' }};
            margin: 15mm 15mm 15mm 15mm;
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
            padding: 20mm;
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

        /* Formal Report Styles */
        .report-header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .report-header .institution-name {
            font-size: 14pt;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #0f172a;
            margin-bottom: 4px;
        }

        .report-header .sub-institution {
            font-size: 10pt;
            color: #475569;
            margin-bottom: 8px;
        }

        .report-header h1 {
            font-size: 14pt;
            font-weight: 700;
            color: #1e3a8a;
            margin: 6px 0 2px 0;
            text-transform: uppercase;
        }

        .report-header h2 {
            font-size: 11pt;
            font-weight: 500;
            color: #334155;
            margin: 0;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 8px 24px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 9.5pt;
        }

        .meta-item {
            display: flex;
        }

        .meta-label {
            width: 140px;
            font-weight: 600;
            color: #475569;
        }

        .meta-value {
            flex: 1;
            color: #0f172a;
        }

        /* Summary Cards */
        .summary-bar {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            margin-bottom: 20px;
        }

        .summary-box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            background: #ffffff;
            text-align: center;
        }

        .summary-box .num {
            font-size: 14pt;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.2;
        }

        .summary-box .lbl {
            font-size: 8pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 4px;
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

        .formal-table td.text-right {
            text-align: right;
        }

        .formal-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .formal-table tfoot th,
        .formal-table tfoot td {
            background-color: #e2e8f0;
            font-weight: 700;
            border-top: 2px solid #0f172a;
            color: #0f172a;
        }

        .badge-pct {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 8.5pt;
        }

        .badge-pct-high {
            background: #dcfce7;
            color: #166534;
        }

        .badge-pct-mid {
            background: #fef9c3;
            color: #854d0e;
        }

        .badge-pct-low {
            background: #fee2e2;
            color: #991b1b;
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

            .formal-table th {
                background-color: #f1f5f9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .badge-pct {
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
            <span style="font-weight: 600; font-size: 14px;">🖨️ Cetak Rekapitulasi Capaian Unit Kerja</span>
        </div>
        <div class="toolbar-group">
            <span style="font-size: 13px; color: #94a3b8;">Orientasi:</span>
            <a href="{{ request()->fullUrlWithQuery(['orientation' => 'landscape']) }}" class="btn {{ $orientation === 'landscape' ? 'btn-active' : 'btn-outline' }}">Landscape</a>
            <a href="{{ request()->fullUrlWithQuery(['orientation' => 'portrait']) }}" class="btn {{ $orientation === 'portrait' ? 'btn-active' : 'btn-outline' }}">Portrait</a>
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
        <!-- Header Dokumen -->
        <div class="report-header">
            <div class="institution-name">SISTEM INFORMASI INDIKATOR MUTU (SIIMUT)</div>
            <div class="sub-institution">RUMAH SAKIT CITRA HUSADA JEMBER</div>
            <h1>Rekapitulasi Capaian Indikator Mutu Per Unit Kerja</h1>
            <h2>{{ $laporan->name }}</h2>
        </div>

        <!-- Metadata Informasi Laporan -->
        <div class="meta-grid">
            <div class="meta-item">
                <span class="meta-label">Nama Laporan</span>
                <span class="meta-value">: {{ $laporan->name }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Status Laporan</span>
                <span class="meta-value">: {{ strtoupper($laporan->status ?? '-') }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Periode Asesmen</span>
                <span class="meta-value">: {{ $periodeText }}</span>
            </div>
            <div class="meta-item">
                <span class="meta-label">Waktu Cetak</span>
                <span class="meta-value">: {{ now()->translatedFormat('d F Y, H:i') }} WIB</span>
            </div>
        </div>

        <!-- Summary Bar -->
        <div class="summary-bar">
            <div class="summary-box">
                <div class="num">{{ number_format($summary['total_units']) }}</div>
                <div class="lbl">Total Unit</div>
            </div>
            <div class="summary-box">
                <div class="num">{{ number_format($summary['total_indicators']) }}</div>
                <div class="lbl">Jml Indikator</div>
            </div>
            <div class="summary-box">
                <div class="num" style="color: #166534;">{{ number_format($summary['total_standard']) }}</div>
                <div class="lbl">Mencapai Standar</div>
            </div>
            <div class="summary-box">
                <div class="num" style="color: #dc2626;">{{ number_format($summary['total_below']) }}</div>
                <div class="lbl">Tidak Standar</div>
            </div>
            <div class="summary-box">
                <div class="num" style="color: #64748b;">{{ number_format($summary['total_empty']) }}</div>
                <div class="lbl">Kosong / Belum</div>
            </div>
            <div class="summary-box">
                <div class="num" style="color: #1e40af;">{{ number_format($summary['overall_percentage'], 2) }}%</div>
                <div class="lbl">Persentase Capaian</div>
            </div>
        </div>

        <!-- Tabel Laporan Formal -->
        <table class="formal-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 35%;">Unit Kerja</th>
                    <th style="width: 12%;">Jml Indikator</th>
                    <th style="width: 12%;">Mencapai Standar</th>
                    <th style="width: 12%;">Tidak Standar</th>
                    <th style="width: 12%;">Kosong</th>
                    <th style="width: 12%;">Persentase (%)</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $index => $row)
                    @php
                        $pct = (float) ($row->standard_percentage ?? 0);
                        $pctClass = match(true) {
                            $pct >= 80 => 'badge-pct-high',
                            $pct >= 50 => 'badge-pct-mid',
                            default => 'badge-pct-low',
                        };
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td><strong>{{ $row->unit_name }}</strong></td>
                        <td class="text-center">{{ number_format($row->total_count ?? 0) }}</td>
                        <td class="text-center" style="color: #166534; font-weight: 600;">{{ number_format($row->meets_standard_count ?? 0) }}</td>
                        <td class="text-center" style="color: #dc2626; font-weight: 600;">{{ number_format($row->below_standard_count ?? 0) }}</td>
                        <td class="text-center" style="color: #64748b;">{{ number_format($row->empty_count ?? 0) }}</td>
                        <td class="text-center">
                            <span class="badge-pct {{ $pctClass }}">{{ number_format($pct, 2) }}%</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center" style="padding: 24px; color: #64748b;">Tidak ada data unit kerja yang ditemukan.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" class="text-center">TOTAL KESELURUHAN</th>
                    <th class="text-center">{{ number_format($summary['total_indicators']) }}</th>
                    <th class="text-center" style="color: #166534;">{{ number_format($summary['total_standard']) }}</th>
                    <th class="text-center" style="color: #dc2626;">{{ number_format($summary['total_below']) }}</th>
                    <th class="text-center" style="color: #64748b;">{{ number_format($summary['total_empty']) }}</th>
                    <th class="text-center">
                        <span class="badge-pct {{ $summary['overall_percentage'] >= 80 ? 'badge-pct-high' : ($summary['overall_percentage'] >= 50 ? 'badge-pct-mid' : 'badge-pct-low') }}">
                            {{ number_format($summary['overall_percentage'], 2) }}%
                        </span>
                    </th>
                </tr>
            </tfoot>
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
