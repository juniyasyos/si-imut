<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabel Data - SIIMUT</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- KaTeX for Mathematical Formulas -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.css">
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/katex.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/katex@0.16.8/dist/contrib/auto-render.min.js" onload="renderMathInElement(document.body, {delimiters: [{left: '$$', right: '$$', display: true}, {left: '$', right: '$', display: false}]});"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Libre+Barcode+128&family=Roboto:wght@300;400;500;700&display=swap');

        body {
            font-family: 'Roboto', 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .report-header {
            font-family: 'Roboto', sans-serif;
            letter-spacing: 0.5px;
        }

        @page {
            margin: 1cm;
        }

        @page: landscape {
            size: A4 landscape;
        }

        @page: portrait {
            size: A4 portrait;
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

            .print-container {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
            }

            .legend-container {
                page-break-before: always;
                margin-top: 20px;
            }
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }

        [x-cloak] {
            display: none !important;
        }

        /* Checkbox style for table cells */
        .cell-check {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .cell-check.checked {
            background-color: #dcfce7;
            color: #166534;
        }

        .cell-check.unchecked {
            background-color: #fee2e2;
            color: #991b1b;
        }

        /* Print orientation */
        body.print-landscape {
            margin: 0;
            padding: 1cm;
        }

        body.print-portrait {
            margin: 0;
            padding: 1cm;
        }

        @media print {
            body.print-landscape {
                width: 100%;
                height: 100%;
            }

            body.print-portrait {
                width: 100%;
                height: 100%;
            }
        }

        /* Legend styling */
        .legend-container {
            background: linear-gradient(135deg, #f0f7ff 0%, #e0f2fe 100%);
            border-left: 4px solid #0369a1;
        }

        .legend-header {
            background: linear-gradient(90deg, #0369a1 0%, #0284c7 100%);
            color: white;
        }

        .legend-field-group {
            background: white;
            border: 1px solid #cbd5e1;
            transition: box-shadow 0.2s;
        }

        .legend-field-group:hover {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
        }

        .legend-code {
            font-family: 'Monaco', 'Courier New', monospace;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        /* KaTeX Math Rendering Styles */
        .katex {
            font-size: 1rem;
            white-space: normal;
        }

        .katex-display {
            margin: 1em 0;
            display: block !important;
        }

        @media print {
            .katex {
                -webkit-print-color-adjust: exact;
                color-adjust: exact;
                print-color-adjust: exact;
            }

            .katex-display {
                margin: 0.5em 0;
            }
        }
    </style>
</head>

<body class="bg-gray-50 p-4 md:p-6" x-data="dynamicTable()">

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

        <!-- Filter Toggles -->
        <div class="flex flex-wrap justify-end gap-3">
            <div class="flex items-center gap-2 px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm">
                <input type="checkbox" id="showReporter" x-model="showReporter" @change="calculateDisplayColumns()">
                <label for="showReporter" class="text-sm text-gray-700 cursor-pointer">Tampilkan Kolom Pelapor</label>
            </div>
            <div class="flex items-center gap-2 px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm">
                <input type="checkbox" id="showLegend" x-model="showLegend">
                <label for="showLegend" class="text-sm text-gray-700 cursor-pointer">Tampilkan Legenda</label>
            </div>
            <div class="flex items-center gap-2 px-3 py-2 bg-white border border-gray-300 rounded-lg shadow-sm">
                <input type="checkbox" id="useFullLabels" x-model="useFullLabels">
                <label for="useFullLabels" class="text-sm text-gray-700 cursor-pointer">Label Lengkap</label>
            </div>

            <a href="{{ url('/siimut/daily-report-entries') }}"
                class="px-5 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition shadow-sm text-sm">
                ← Kembali
            </a>
            <button @click="fetchData()"
                class="px-5 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition shadow-sm text-sm">
                🔄 Refresh
            </button>
            <button @click="handlePrint()"
                class="px-5 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition shadow-sm text-sm">
                🖨️ Cetak
            </button>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" x-cloak class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-8 rounded-lg shadow-xl">
            <div class="rounded-full h-12 w-12 border-b-4 border-blue-600 mx-auto mb-4" style="animation: spin 1s linear infinite;"></div>
            <p class="text-gray-700 font-medium">Memuat data...</p>
        </div>
    </div>

    <!-- Error State -->
    <div x-show="error" x-cloak class="max-w-7xl mx-auto mb-6">
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 font-medium" x-text="errorMessage"></p>
                    <p class="text-xs text-red-600 mt-1">URL: <code class="bg-red-100 px-1 rounded" x-text="window.location.href"></code></p>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-full mx-auto bg-white p-6 md:p-8 shadow-xl border border-gray-200 print-container">

        <!-- Header dengan Logo -->
        <div class="report-header border-b-2 border-gray-800 pb-4 mb-6">
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
                    <h1 class="text-lg md:text-xl font-bold text-gray-900 mb-1" style="letter-spacing: 1px;">RUMAH SAKIT CITRA HUSADA JEMBER</h1>
                    <div class="text-xs text-gray-600 mb-2" style="letter-spacing: 0.5px;">Jl. Contoh No. 123, Jember, Jawa Timur 68100 | Telp: (0331) 123456</div>
                    <div class="h-px bg-gray-400 my-2"></div>
                    <h2 class="text-sm md:text-base font-bold text-gray-800 uppercase" style="letter-spacing: 1.5px;" x-text="tableTitle"></h2>
                    <div class="text-xs text-gray-600 mt-1" style="letter-spacing: 0.5px;">Sistem Informasi Indikator Mutu (SI-IMUT)</div>
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
                <div><span class="font-semibold text-gray-700">Unit Kerja:</span> <span class="text-gray-600" x-text="metadata.unit_kerja || '-'"></span></div>
                <div><span class="font-semibold text-gray-700">Periode:</span> <span class="text-gray-600" x-text="metadata.period_label || '-'"></span></div>
                <div><span class="font-semibold text-gray-700">Tanggal Cetak:</span> <span class="text-gray-600" x-text="new Date().toLocaleDateString('id-ID', {day: '2-digit', month: 'long', year: 'numeric'})"></span></div>
            </div>
        </div>

        <!-- Legend Panel - Professional Design -->
        <div x-show="!loading && !error && showLegend && tableConfig.legend" class="mb-4">
            <div class="legend-container rounded-lg border border-sky-200 overflow-hidden">
                <!-- Legend Header -->
                <div class="legend-header px-6 py-2 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 000-2A4 4 0 000 5v10a4 4 0 004 4h12a4 4 0 004-4V5a4 4 0 00-4-4 1 1 0 000 2 2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2V5z" clip-rule="evenodd"></path>
                        </svg>
                        <h3 class="text-lg font-bold">LEGENDA KODE FIELD</h3>
                    </div>
                    <div class="text-sm font-medium opacity-90">Panduan Interpretasi Data</div>
                </div>

                <!-- Field Groups -->
                <div class="p-2">
                    <div class="grid grid-cols-3 gap-6">
                        <template x-for="(legend, fieldKey) in tableConfig.legend" :key="'legend-' + fieldKey">
                            <div class="legend-field-group rounded-lg p-3">
                                <!-- Field Title -->
                                <div class="mb-3 pb-2 border-b-2 border-blue-300">
                                    <h4 class="text-sm font-bold text-slate-900" x-text="legend.field_label"></h4>
                                </div>

                                <!-- Options List -->
                                <div class="space-y-2">
                                    <template x-for="(option, index) in legend.options" :key="'option-' + index">
                                        <div class="flex items-start gap-3">
                                            <span class="inline-block min-w-max legend-code px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-mono border border-blue-300" x-text="option.code"></span>
                                            <span class="text-xs text-slate-700 mt-1" x-text="option.label"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Table -->
        <div x-show="!loading && !error && tableData.length > 0" class="rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse text-sm">
                    <!-- Table Header -->
                    <thead>
                        <!-- Row 1: Parent Headers -->
                        <tr class="bg-blue-700">
                            <template x-for="(header, index) in tableConfig.headers" :key="'parent-' + index">
                                <template x-if="!(header.key === 'submitted_by_name' && !showReporter)">
                                    <th :colspan="header.children ? header.children.length : 1"
                                        :rowspan="header.children ? 1 : (hasMultiLevelHeaders ? 2 : 1)"
                                        class="border border-gray-300 px-2 py-2 text-center font-semibold text-white text-xs"
                                        :class="header.bgColor || 'bg-blue-700'"
                                        :style="header.width ? 'width: ' + header.width : ''"
                                        x-text="header.label">
                                    </th>
                                </template>
                            </template>
                        </tr>

                        <!-- Row 2: Child Headers (only if multi-level) -->
                        <template x-if="hasMultiLevelHeaders">
                            <tr class="bg-blue-600">
                                <!-- Render flat child headers array - no nested loops to avoid browser reflow -->
                                <template x-for="(child, idx) in flatChildHeaders" :key="'child-' + idx">
                                    <th class="border border-gray-300 px-2 py-1.5 text-center text-xs font-semibold text-white"
                                        :class="child.bgColor || 'bg-blue-600'"
                                        :style="child.width ? 'width: ' + child.width : ''"
                                        :title="child.parent_label + ' → ' + child.label + ' (Key: ' + child.key + ')'"
                                        x-text="useFullLabels && child.full_label ? child.full_label : child.label">
                                    </th>
                                </template>
                            </tr>
                        </template>
                    </thead>

                    <!-- Table Body -->
                    <tbody>
                        <template x-for="(row, rowIndex) in tableData" :key="'row-' + rowIndex">
                            <tr class="hover:bg-gray-50 transition" :class="rowIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50'">
                                <template x-for="(column, colIndex) in displayColumns" :key="'cell-' + rowIndex + '-' + colIndex">
                                    <td class="border border-gray-300 px-2 py-1.5 text-xs"
                                        :class="getCellAlignment(column, row[column])"
                                        :style="getColumnWidth(column)"
                                        <span x-html="formatCellValue(row[column], column, row)"></span>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>

                    <!-- Summary Row -->
                    <tfoot x-show="summary && summary.total_entries > 0">
                        <tr class="bg-slate-100 border-t border-slate-300">
                            <td :colspan="displayColumns.length" class="border border-slate-300 px-4 py-3 text-sm">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                                    <!-- Total Data Section -->
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <span class="text-slate-600 font-medium text-xs uppercase tracking-wide">Total Entri Data</span>
                                            <span class="text-slate-900 font-bold text-lg ml-2" x-text="summary.total_entries"></span>
                                        </div>
                                    </div>

                                    <!-- Validation Compliance Section -->
                                    <div x-show="summary.validation_compliance !== undefined" class="flex items-center gap-3">
                                        <div>
                                            <span class="text-slate-600 font-medium text-xs uppercase tracking-wide">Tingkat Kebenaran Data yg Dikumpulkan</span>
                                            <span class="font-bold text-lg ml-2" :class="summary.validation_compliance >= 80 ? 'text-green-700' : 'text-red-700'" x-text="summary.validation_compliance + '%'"></span>
                                            <span class="text-xs text-slate-500 ml-2">
                                                (<span class="text-green-600 font-semibold" x-text="summary.valid_entries"></span> benar,
                                                <span class="text-red-600 font-semibold" x-text="summary.invalid_entries"></span> salah)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- No Data State -->
        <div x-show="!loading && !error && tableData.length === 0" class="rounded-lg shadow-sm border border-gray-200 p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
            </svg>
            <p class="mt-4 text-gray-600">Tidak ada data untuk periode yang dipilih</p>
        </div>

        <!-- Validation Formula & Analysis Section -->
        <div x-show="!loading && !error" class="mt-8">

            <!-- Validation Formula -->
            <div class="border border-gray-300 p-6 mb-6">
                <div class="text-center">
                    <div class="text-sm font-semibold text-gray-800 mb-4">RUMUS VALIDASI</div>

                    <!-- Main Formula -->
                    <div class="my-3 text-base">
                        $$\text{VALIDASI} = \frac{\sum \text{ITEM YANG VALID}}{\sum \text{YANG DI VALIDATOR}} \times 100\%$$
                    </div>

                    <!-- Calculation Result -->
                    <div class="mt-3 text-sm text-gray-700">
                        VALIDASI = <span x-text="summary.valid_entries || 0"></span>/<span x-text="summary.total_entries || 0"></span> X 100% = <span class="font-semibold" x-text="summary.validation_compliance !== undefined ? summary.validation_compliance + '%' : '0%'"></span>
                    </div>

                    <!-- Interpretation -->
                    <div class="text-center text-sm text-gray-700 mt-1">
                        Hasil validasi didapatkan data yang dilakukan validasi mencapai <span class="font-semibold" x-text="summary.validation_compliance !== undefined ? summary.validation_compliance + '%' : '0%'"></span>
                    </div>
                </div>
            </div>

            <!-- Analysis & Recommendation Table -->
            <div class="border border-gray-300 overflow-hidden">
                <table class="w-full border-collapse text-sm">
                    <tbody>
                        <!-- Analysis Row -->
                        <tr class="border-b border-gray-300">
                            <td class="border-r border-gray-300 px-4 py-3 font-semibold text-gray-800 bg-gray-100" style="width: 120px; vertical-align: top;">
                                Analisis
                            </td>
                            <td class="px-4 py-3 text-gray-800">
                                <div x-show="summary.total_entries > 0" x-text="getAnalysisText()"></div>
                                <div x-show="summary.total_entries === 0">
                                    Tidak ada data untuk periode yang dipilih.
                                </div>
                            </td>
                        </tr>

                        <!-- Recommendation Row -->
                        <tr>
                            <td class="border-r border-gray-300 px-4 py-3 font-semibold text-gray-800 bg-gray-100" style="width: 120px; vertical-align: top;">
                                Rekomendasi
                            </td>
                            <td class="px-4 py-3 text-gray-800">
                                <div x-text="getRecommendationText()"></div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>

        <!-- Footer & Signature -->
        <div class="mt-8 border-t-2 border-gray-300 pt-6">
            <div class="flex justify-between mt-8">
                <div class="text-center w-48 md:w-56">
                    <div class="text-sm mb-16">Mengetahui,<br>Kepala Bagian Mutu</div>
                    <div class="text-sm font-bold border-t-2 border-black pt-2">(...........................)</div>
                </div>
                <div class="text-center w-48 md:w-56">
                    <div class="text-sm mb-16">
                        <span x-text="metadata.period_label ? 'Jember, ' + new Date().toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : ''"></span>,<br>
                        Penanggung Jawab
                    </div>
                    <div class="text-sm font-bold border-t-2 border-black pt-2">(...........................)</div>
                </div>
            </div>

            <div class="text-center mt-6 text-xs text-gray-500">
                Dokumen ini dibuat secara otomatis oleh Sistem Informasi Indikator Mutu (SI-IMUT)
            </div>
        </div>
    </div>

    <script>
        function dynamicTable() {
            return {
                loading: true,
                error: false,
                errorMessage: '',
                tableTitle: 'Data Laporan Harian',
                tableDescription: '',
                tableConfig: {
                    headers: []
                },
                hasMultiLevelHeaders: false,
                tableData: [],
                displayColumns: [],
                flatChildHeaders: [], // Flatten array of {label, key, parent, bgColor, width, full_label}
                metadata: {},
                summary: {},
                userData: null,
                showReporter: false,
                showLegend: false,
                useFullLabels: false,
                printOrientation: 'landscape',

                async init() {
                    await this.fetchData();
                },

                getUrlParams() {
                    const params = new URLSearchParams(window.location.search);
                    return {
                        form_template_id: params.get('form_template_id'),
                        unit_kerja_id: params.get('unit_kerja_id'),
                        imut_profile_id: params.get('imut_profile_id'),
                        period: params.get('period') || new Date().toISOString().slice(0, 7),
                    };
                },

                async fetchData() {
                    this.loading = true;
                    this.error = false;
                    this.errorMessage = '';

                    try {
                        const params = this.getUrlParams();

                        // Remove null/undefined values from params
                        const cleanParams = Object.fromEntries(
                            Object.entries(params).filter(([_, v]) => v != null && v !== '')
                        );

                        const queryString = new URLSearchParams(cleanParams).toString();
                        const url = '{{ route("api.table-data") }}' + (queryString ? '?' + queryString : '');

                        const response = await fetch(url);

                        if (!response.ok) {
                            if (response.status === 401) {
                                throw new Error('Sesi Anda telah berakhir. Silakan login kembali.');
                            }
                            throw new Error('Error loading data (HTTP ' + response.status + ')');
                        }

                        const jsonData = await response.json();

                        // Set metadata
                        this.metadata = jsonData.metadata || {};
                        this.summary = jsonData.summary || {};
                        this.userData = jsonData.user || null;

                        // Set table config
                        if (jsonData.tableConfig && jsonData.tableConfig.headers) {
                            this.tableConfig = jsonData.tableConfig;
                            this.hasMultiLevelHeaders = jsonData.tableConfig.headers.some(h => h.children && h.children.length > 0);
                            this.calculateDisplayColumns();
                            this.buildFlatChildHeaders(); // Build flat array untuk row 2
                        } else {
                            this.tableConfig = {
                                headers: []
                            };
                            this.hasMultiLevelHeaders = false;
                            this.displayColumns = [];
                        }

                        // Set table data
                        this.tableData = jsonData.tableData || [];

                        // Auto-detect columns if no config but have data
                        if (this.displayColumns.length === 0 && this.tableData.length > 0) {
                            this.displayColumns = Object.keys(this.tableData[0]);
                        }

                        // Set title and description
                        this.tableTitle = jsonData.tableTitle || 'Data Laporan Harian';
                        this.tableDescription = jsonData.tableDescription || '';

                        console.log('✓ Data loaded:', {
                            title: this.tableTitle,
                            rows: this.tableData.length,
                            columns: this.displayColumns.length,
                            multiLevel: this.hasMultiLevelHeaders
                        });

                        // Log EXACT headers structure untuk debug visual mismatch
                        console.log('🔍 Headers structure detail:', this.tableConfig.headers.map((h, idx) => ({
                            index: idx,
                            label: h.label,
                            key: h.key || 'N/A',
                            children_count: (h.children || []).length,
                            children_labels: (h.children || []).map(c => c.label).join(', '),
                            children_keys: (h.children || []).map(c => c.key).join(', '),
                        })));

                        // Log displayColumns urutan
                        console.log('📊 displayColumns order:', this.displayColumns);

                    } catch (error) {
                        console.error('✗ Error:', error);
                        this.error = true;
                        this.errorMessage = error.message;
                    } finally {
                        this.loading = false;
                        // Re-render KaTeX formulas after data loaded
                        setTimeout(() => {
                            if (typeof renderMathInElement !== 'undefined') {
                                renderMathInElement(document.body, {
                                    delimiters: [{
                                            left: '$$',
                                            right: '$$',
                                            display: true
                                        },
                                        {
                                            left: '$',
                                            right: '$',
                                            display: false
                                        }
                                    ]
                                });
                            }
                        }, 100);
                    }
                },

                calculateDisplayColumns() {
                    this.displayColumns = [];
                    if (!this.tableConfig || !this.tableConfig.headers) return;

                    this.tableConfig.headers.forEach(header => {
                        if (header.children && Array.isArray(header.children)) {
                            header.children.forEach(child => {
                                this.displayColumns.push(child.key);
                            });
                        } else if (header.key) {
                            // Skip reporter column if not showing
                            if (header.key === 'submitted_by_name' && !this.showReporter) {
                                return;
                            }
                            this.displayColumns.push(header.key);
                        }
                    });
                },

                buildFlatChildHeaders() {
                    this.flatChildHeaders = [];
                    if (!this.tableConfig || !this.tableConfig.headers) return;

                    // Loop semua headers dan flatten children-nya ke single array
                    this.tableConfig.headers.forEach(header => {
                        if (header.children && Array.isArray(header.children)) {
                            header.children.forEach(child => {
                                this.flatChildHeaders.push({
                                    label: child.label,
                                    full_label: child.full_label,
                                    key: child.key,
                                    parent_label: header.label,
                                    bgColor: child.bgColor || 'bg-blue-600',
                                    width: child.width,
                                });
                            });
                        }
                    });

                    console.log('📋 Flat child headers count:', this.flatChildHeaders.length);
                    console.log('📋 Flat child headers:', this.flatChildHeaders.map(c => c.label).join(', '));
                },

                getHeaderConfig(columnKey) {
                    if (!this.tableConfig || !this.tableConfig.headers) return {};

                    for (let header of this.tableConfig.headers) {
                        if (header.key === columnKey) return header;
                        if (header.children) {
                            for (let child of header.children) {
                                if (child.key === columnKey) return child;
                            }
                        }
                    }
                    return {};
                },

                getCellAlignment(column, value) {
                    const config = this.getHeaderConfig(column);
                    if (config.align) return 'text-' + config.align;

                    if (typeof value === 'number') return 'text-center';

                    const centerColumns = ['no', 'id', 'status', 'score', 'skor'];
                    if (centerColumns.some(col => column.toLowerCase().includes(col))) {
                        return 'text-center';
                    }

                    return 'text-left';
                },

                handlePrint() {
                    // Create style element for page orientation
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

                    // Re-render KaTeX before printing
                    if (typeof renderMathInElement !== 'undefined') {
                        renderMathInElement(document.body, {
                            delimiters: [{
                                    left: '$$',
                                    right: '$$',
                                    display: true
                                },
                                {
                                    left: '$',
                                    right: '$',
                                    display: false
                                }
                            ]
                        });
                    }

                    // Trigger print dialog
                    setTimeout(() => {
                        window.print();
                    }, 300);
                },

                getColumnWidth(column) {
                    const config = this.getHeaderConfig(column);
                    if (config.width) return 'width: ' + config.width + '; min-width: ' + config.width;
                    return '';
                },

                formatCellValue(value, column, rowData = {}) {
                    const config = this.getHeaderConfig(column);

                    // Handle null/undefined
                    if (value === null || value === undefined || value === '') {
                        return '<span class="text-gray-400">-</span>';
                    }

                    // Format based on config
                    if (config.format) {
                        switch (config.format) {
                            case 'checkbox':
                                if (value === 1 || value === true || value === '1') {
                                    return '<span class="cell-check checked">✓</span>';
                                } else {
                                    return '<span class="cell-check unchecked">✗</span>';
                                }

                            case 'field_code':
                                // Render field code (A1, A2, A3) dengan visual indicator
                                if (value === 0 || value === false || value === '0') {
                                    return '<span class="inline-block w-6 h-6 bg-gray-100 text-gray-500 border border-gray-300 rounded text-center leading-6 font-medium text-xs" title="Tidak dipilih">-</span>';
                                } else {
                                    // value adalah kode seperti "A1", "A2", "B1", dll
                                    return '<span class="inline-block px-1.5 py-0.5 bg-green-100 text-green-800 border border-green-300 rounded text-center leading-5 font-mono font-bold text-xs" title="Dipilih: ' + value + '">' + value + '</span>';
                                }

                            case 'numeric_code':
                                const numericValue = parseInt(value);
                                if (numericValue === 1) {
                                    return '<span class="inline-block w-6 h-6 bg-green-100 text-green-800 border border-green-300 rounded text-center leading-6 font-medium text-xs" title="Dipilih">1</span>';
                                } else {
                                    return '<span class="inline-block w-6 h-6 bg-gray-100 text-gray-500 border border-gray-300 rounded text-center leading-6 font-medium text-xs" title="Tidak dipilih">0</span>';
                                }

                            case 'percentage':
                                const numVal = parseFloat(value);
                                if (isNaN(numVal)) return '<span class="text-gray-400">-</span>';
                                const color = numVal >= 80 ? 'text-green-600' : 'text-red-600';
                                return '<span class="font-semibold ' + color + '">' + numVal.toFixed(0) + '%</span>';

                            case 'date':
                                try {
                                    const date = new Date(value);
                                    if (!isNaN(date.getTime())) {
                                        return date.toLocaleDateString('id-ID', {
                                            day: '2-digit',
                                            month: 'short',
                                            year: 'numeric'
                                        });
                                    }
                                } catch (e) {}
                                return value;

                            case 'boolean':
                                if (value === true || value === 1 || value === '1' || value === 'true') {
                                    return '<span class="cell-check checked">✓</span>';
                                }
                                return '<span class="cell-check unchecked">✗</span>';

                            case 'number':
                                return parseFloat(value).toLocaleString('id-ID');
                        }
                    }

                    // Auto-detect: Boolean values (0/1)
                    if ((value === 0 || value === 1) && column.includes('_')) {
                        if (value === 1) {
                            return '<span class="cell-check checked">✓</span>';
                        }
                        return '<span class="cell-check unchecked">✗</span>';
                    }

                    // Auto-detect: Date
                    if (typeof value === 'string' && /^\d{4}-\d{2}-\d{2}/.test(value)) {
                        try {
                            const date = new Date(value);
                            if (!isNaN(date.getTime())) {
                                return date.toLocaleDateString('id-ID', {
                                    day: '2-digit',
                                    month: 'short',
                                    year: 'numeric'
                                });
                            }
                        } catch (e) {}
                    }

                    // Auto-detect: Status kepatuhan
                    if (column === 'status_kepatuhan') {
                        const lower = value.toString().toLowerCase();
                        if (lower === 'patuh' || lower === 'ya' || lower === 'sesuai') {
                            return '<span class="px-2 py-0.5 bg-green-100 text-green-800 rounded text-xs font-medium">✓ ' + value + '</span>';
                        }
                        return '<span class="px-2 py-0.5 bg-red-100 text-red-800 rounded text-xs font-medium">✗ ' + value + '</span>';
                    }

                    return value;
                },

                getAnalysisText() {
                    if (this.summary.total_entries === 0) {
                        return 'Tidak ada data untuk periode yang dipilih.';
                    }
                    const compliance = this.summary.validation_compliance !== undefined ? this.summary.validation_compliance + '%' : '0%';
                    const period = this.metadata.period_label || 'periode ini';
                    return `Capaian indikator angka Waktu Lapor Hasil Tes Kritis laboratorium pada bulan ${period} sesuai standar yaitu sebesar ${compliance}.`;
                },

                getRecommendationText() {
                    if (this.summary.total_entries === 0) {
                        return 'Tidak ada rekomendasi - data kosong';
                    }

                    const compliance = this.summary.validation_compliance || 0;

                    if (compliance >= 80) {
                        return 'Tetap melakukan monitoring waktu lapor tes kritis laboratorium ≤ 30 menit kepada seluruh staf';
                    } else if (compliance > 0) {
                        return 'Perlu peningkatan monitoring waktu lapor tes kritis laboratorium agar mencapai target ≤ 30 menit dan meningkatkan kepatuhan ke standar';
                    } else {
                        return 'Tidak ada rekomendasi - data kosong';
                    }
                }
            }
        }
    </script>
</body>

</html>