<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tabel Data - SIIMUT</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
            size: A4 landscape;
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

            .print-container {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                box-shadow: none !important;
                border: none !important;
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
    </style>
</head>

<body class="bg-gray-50 p-4 md:p-6" x-data="dynamicTable()">

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

        <!-- Description -->
        <div x-show="!loading && !error" class="mb-4">
            <p class="text-gray-600 text-sm" x-text="tableDescription"></p>
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
                                <th :colspan="header.children ? header.children.length : 1"
                                    :rowspan="header.children ? 1 : (hasMultiLevelHeaders ? 2 : 1)"
                                    class="border border-gray-300 px-2 py-2 text-center font-semibold text-white text-xs"
                                    :class="header.bgColor || 'bg-blue-700'"
                                    x-text="header.label">
                                </th>
                            </template>
                        </tr>

                        <!-- Row 2: Child Headers (only if multi-level) -->
                        <template x-if="hasMultiLevelHeaders">
                            <tr class="bg-blue-600">
                                <template x-for="(header, hIndex) in tableConfig.headers" :key="'childrow-' + hIndex">
                                    <template x-if="header.children">
                                        <template x-for="(child, cIndex) in header.children" :key="'child-' + hIndex + '-' + cIndex">
                                            <th class="border border-gray-300 px-2 py-1.5 text-center text-xs font-semibold text-white"
                                                :class="child.bgColor || 'bg-blue-600'"
                                                x-text="child.label">
                                            </th>
                                        </template>
                                    </template>
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
                                        :class="getCellAlignment(column, row[column])">
                                        <span x-html="formatCellValue(row[column], column, row)"></span>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>

                    <!-- Summary Row -->
                    <tfoot x-show="summary && summary.total_entries > 0">
                        <tr class="bg-gray-100 font-semibold">
                            <td :colspan="displayColumns.length" class="border border-gray-300 px-3 py-2 text-xs">
                                <div class="flex flex-wrap justify-between gap-4">
                                    <span>Total Data: <strong x-text="summary.total_entries"></strong></span>
                                    <span x-show="summary.validation_compliance !== undefined">
                                        Validasi: <strong :class="summary.validation_compliance >= 80 ? 'text-green-600' : 'text-red-600'" x-text="summary.validation_compliance + '%'"></strong>
                                        (<span class="text-green-600" x-text="summary.valid_entries"></span> valid,
                                        <span class="text-red-600" x-text="summary.invalid_entries"></span> invalid)
                                    </span>
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

    <!-- Action Buttons -->
    <div class="no-print mt-6 max-w-full mx-auto flex flex-wrap justify-end gap-3">
        <a href="{{ url('/siimut/daily-report-entries') }}"
            class="px-5 py-2 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 transition shadow-sm text-sm">
            ← Kembali
        </a>
        <button @click="fetchData()"
            class="px-5 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition shadow-sm text-sm">
            🔄 Refresh
        </button>
        <button onclick="window.print()"
            class="px-5 py-2 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700 transition shadow-sm text-sm">
            🖨️ Cetak
        </button>
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
                metadata: {},
                summary: {},
                userData: null,

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

                    } catch (error) {
                        console.error('✗ Error:', error);
                        this.error = true;
                        this.errorMessage = error.message;
                    } finally {
                        this.loading = false;
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
                            this.displayColumns.push(header.key);
                        }
                    });
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
                }
            }
        }
    </script>
</body>

</html>