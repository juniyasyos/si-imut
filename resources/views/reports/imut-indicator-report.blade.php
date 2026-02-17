<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Indikator - {{ $imutData->title }}</title>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.tailwindcss.com"></script>
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

<body class="font-sans bg-gray-100 text-gray-900 leading-relaxed" x-data="reportData('{{ $imutData->slug ?? $imutData->id }}', '{{ $laporan->id }}')">

    <!-- Loading State -->
    <div x-show="loading" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-lg">
            <div class="rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto" style="animation: spin 1s linear infinite;"></div>
            <p class="mt-2 text-gray-600">Loading data...</p>
        </div>
    </div>

    <div class="max-w-6xl mx-auto bg-white p-8 shadow-xl border border-gray-200"
        style="min-height: calc(100vh - 100px);">

        <!-- Header dengan Logo -->
        <x-basic-report-header
            title="Laporan Triwulan Indikator Mutu" />

        <!-- Enhanced Dashboard -->
        <div class="bg-white border border-gray-300 rounded-lg shadow-sm mb-6 overflow-hidden">
            <div class="grid grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-gray-300">
                <!-- Left Section: Indikator -->
                <div class="p-6">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Nama Indikator</div>
                            <h3 class="text-base font-bold text-gray-900 leading-snug" x-text="imutData.title"></h3>
                        </div>
                    </div>
                </div>

                <!-- Right Section: Kategori -->
                <div class="p-6 bg-gray-50">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0 w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Kategori</div>
                            <h3 class="text-base font-bold text-gray-900 leading-snug" x-text="imutData.categories || '-'"></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Definition Section -->
        <div x-show="imutData.definition" class="bg-yellow-50 border border-yellow-300 rounded p-5 mb-6">
            <h4 class="text-sm font-semibold text-yellow-800 mb-2">📋 Definisi Operasional</h4>
            <p class="text-sm text-yellow-900 leading-relaxed" x-text="imutData.definition"></p>
        </div>

        <!-- Chart Section -->
        <div class="bg-white border border-gray-200 rounded p-6 mb-6">
            <!-- Chart Header -->
            <!-- Filter Section (No Print) -->
            <div class="no-print bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-5 border border-blue-200 shadow-sm">
                <div class="flex items-center gap-3 mb-4">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                    </svg>
                    <span class="text-sm font-bold text-gray-800">Filter & Pengaturan Tampilan</span>
                </div>

                <div class="space-y-4">
                    <!-- Filter Mode -->
                    <div class="bg-white rounded-lg p-3 border border-gray-200">
                        <label class="text-xs font-semibold text-gray-700 mb-2 block">Mode Periode</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button @click="changeFilterMode('yearly')"
                                :class="filterMode === 'yearly' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="px-3 py-2 text-xs font-medium rounded transition">
                                📆 Tahunan
                            </button>
                            <button @click="changeFilterMode('semester')"
                                :class="filterMode === 'semester' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="px-3 py-2 text-xs font-medium rounded transition">
                                📋 Semester
                            </button>
                            <button @click="changeFilterMode('quarter')"
                                :class="filterMode === 'quarter' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="px-3 py-2 text-xs font-medium rounded transition">
                                📊 Quarter
                            </button>
                            <button @click="changeFilterMode('custom')"
                                :class="filterMode === 'custom' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                                class="px-3 py-2 text-xs font-medium rounded transition">
                                📅 Kustom
                            </button>
                        </div>
                    </div>

                    <!-- Yearly Filter -->
                    <div x-show="filterMode === 'yearly'" class="bg-white rounded-lg p-3 border border-gray-200">
                        <label class="text-xs font-semibold text-gray-700 mb-2 block">Pilih Tahun</label>
                        <select x-model="yearlyYears" @change="applyFilters()" multiple
                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                            <template x-for="year in [2020, 2021, 2022, 2023, 2024, 2025, 2026]" :key="year">
                                <option :value="year" x-text="year"></option>
                            </template>
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Tahan Ctrl/Cmd untuk memilih beberapa tahun</p>
                    </div>

                    <!-- Semester Filter -->
                    <div x-show="filterMode === 'semester'" class="bg-white rounded-lg p-3 border border-gray-200">
                        <label class="text-xs font-semibold text-gray-700 mb-2 block">Tahun</label>
                        <select x-model="semesterYear" @change="applyFilters()"
                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 mb-2">
                            <template x-for="year in [2020, 2021, 2022, 2023, 2024, 2025, 2026]" :key="year">
                                <option :value="year" x-text="year"></option>
                            </template>
                        </select>
                        <label class="text-xs font-semibold text-gray-700 mb-2 block">Pilih Semester</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="S1" x-model="semesters" @change="applyFilters()"
                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500">
                                <span class="text-xs">Semester 1 (Jan-Jun)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="S2" x-model="semesters" @change="applyFilters()"
                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500">
                                <span class="text-xs">Semester 2 (Jul-Des)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Quarter Filter -->
                    <div x-show="filterMode === 'quarter'" class="bg-white rounded-lg p-3 border border-gray-200">
                        <label class="text-xs font-semibold text-gray-700 mb-2 block">Tahun</label>
                        <select x-model="quarterYear" @change="applyFilters()"
                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 mb-2">
                            <template x-for="year in [2020, 2021, 2022, 2023, 2024, 2025, 2026]" :key="year">
                                <option :value="year" x-text="year"></option>
                            </template>
                        </select>
                        <label class="text-xs font-semibold text-gray-700 mb-2 block">Pilih Quarter</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="Q1" x-model="quarters" @change="applyFilters()"
                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500">
                                <span class="text-xs">Q1 (Jan-Mar)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="Q2" x-model="quarters" @change="applyFilters()"
                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500">
                                <span class="text-xs">Q2 (Apr-Jun)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="Q3" x-model="quarters" @change="applyFilters()"
                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500">
                                <span class="text-xs">Q3 (Jul-Sep)</span>
                            </label>
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" value="Q4" x-model="quarters" @change="applyFilters()"
                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500">
                                <span class="text-xs">Q4 (Okt-Des)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Custom Range Filter -->
                    <div x-show="filterMode === 'custom'" class="bg-white rounded-lg p-3 border border-gray-200">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="text-xs font-semibold text-gray-700 mb-1 block">Dari</label>
                                <select x-model="startMonth" @change="applyFilters()"
                                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 mb-1">
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                                <select x-model="startYear" @change="applyFilters()"
                                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                                    <template x-for="year in [2020, 2021, 2022, 2023, 2024, 2025, 2026]" :key="year">
                                        <option :value="year" x-text="year"></option>
                                    </template>
                                </select>
                            </div>
                            <div>
                                <label class="text-xs font-semibold text-gray-700 mb-1 block">Sampai</label>
                                <select x-model="endMonth" @change="applyFilters()"
                                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 mb-1">
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                                <select x-model="endYear" @change="applyFilters()"
                                    class="w-full px-2 py-1.5 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                                    <template x-for="year in [2020, 2021, 2022, 2023, 2024, 2025, 2026]" :key="year">
                                        <option :value="year" x-text="year"></option>
                                    </template>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Display Options -->
                    <div class="bg-white rounded-lg p-3 border border-gray-200">
                        <label class="text-xs font-semibold text-gray-700 mb-2 block">Opsi Tampilan</label>
                        <div class="space-y-2">
                            <!-- Show Standard -->
                            <label class="flex items-center justify-between cursor-pointer group">
                                <span class="text-xs text-gray-700 group-hover:text-gray-900">Tampilkan Target Standar</span>
                                <input type="checkbox" x-model="showStandard" @change="updateChart()"
                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-2 focus:ring-blue-500">
                            </label>

                            <!-- Benchmark Options -->
                            <div x-show="availableBenchmarks.length > 0" class="pl-2 border-l-2 border-gray-200 space-y-2">
                                <label class="text-xs font-medium text-gray-600 block">Tampilkan Benchmark:</label>
                                <template x-for="regionType in availableBenchmarks" :key="regionType">
                                    <label class="flex items-center justify-between cursor-pointer group">
                                        <span class="text-xs text-gray-600 group-hover:text-gray-900" x-text="regionType"></span>
                                        <input type="checkbox" x-model="showBenchmarks[regionType]" @change="updateChart()"
                                            class="w-4 h-4 rounded border-gray-300 text-yellow-500 focus:ring-2 focus:ring-yellow-400">
                                    </label>
                                </template>
                            </div>
                        </div>
                    </div>

                    <!-- Notes Selection -->
                    <div x-show="availableNotes.length > 0" class="bg-white rounded-lg p-3 border border-gray-200">
                        <label class="text-xs font-semibold text-gray-700 mb-2 block">Catatan Analisis</label>
                        <select x-model="selectedNoteId" @change="applyFilters()"
                            class="w-full px-3 py-2 text-xs border border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Catatan --</option>
                            <template x-for="note in availableNotes" :key="note.id">
                                <option :value="note.id" x-text="note.period_display"></option>
                            </template>
                        </select>
                    </div>

                    <!-- Apply Button -->
                    <button @click="applyFilters()"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-lg shadow transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        <span class="text-xs">Terapkan Filter</span>
                    </button>
                </div>
            </div>
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 pb-4 border-b border-gray-200">
                <div class="mb-4 md:mb-0">
                    <div class="text-lg font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        Grafik Tren Pencapaian Indikator
                    </div>
                    <div class="text-xs text-gray-500 mt-1 ml-7">Perbandingan pencapaian periode yang dipilih</div>
                </div>
            </div>

            <!-- Chart Area -->
            <div id="trendChart" class="w-full h-96"></div>
        </div>

        <!-- Comparison Table -->
        <div class="mb-6">
            <div class="text-lg font-bold mb-4 text-gray-900">Tabel Perbandingan Pencapaian</div>

            <div class="overflow-x-auto border border-gray-200 rounded">
                <table class="w-full border-collapse text-sm bg-white">
                    <thead>
                        <tr>
                            <th rowspan="2" class="border border-gray-300 p-2 bg-gray-50 font-semibold text-gray-700 text-center">BULAN</th>
                            <th rowspan="2" class="border border-gray-300 p-2 bg-blue-600 text-white font-semibold text-center">STANDAR (%)</th>
                            <th colspan="2" class="border border-gray-300 p-2 bg-purple-600 text-white font-semibold text-center">DATA CAPAIAN</th>
                            <th rowspan="2" class="border border-gray-300 p-2 bg-green-600 text-white font-semibold text-center">HASIL (%)</th>
                            <th :colspan="availableBenchmarks.length"
                                x-show="availableBenchmarks.length > 0"
                                class="border border-gray-300 p-2 bg-yellow-600 text-white font-semibold text-center">BENCHMARK</th>
                        </tr>
                        <tr>
                            <th class="border border-gray-300 p-2 bg-purple-400 text-white text-xs font-semibold text-center">N</th>
                            <th class="border border-gray-300 p-2 bg-purple-400 text-white text-xs font-semibold text-center">D</th>
                            <template x-for="regionType in availableBenchmarks" :key="regionType">
                                <th class="border border-gray-300 p-2 bg-yellow-400 text-white text-xs font-semibold text-center" x-text="regionType"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody>
                        <template x-for="(data, index) in historicalData" :key="index">
                            <tr :class="data.is_current ? 'bg-blue-50 font-semibold' : 'hover:bg-gray-50'">
                                <td class="border border-gray-300 p-2" x-text="data.month + ' ' + data.year"></td>
                                <td class="border border-gray-300 p-2 text-center" x-text="imutData.standard + '%'"></td>
                                <td class="border border-gray-300 p-2 text-center" x-text="data.numerator"></td>
                                <td class="border border-gray-300 p-2 text-center" x-text="data.denominator"></td>
                                <td class="border border-gray-300 p-2 text-center" x-text="data.percentage + '%'"></td>
                                <template x-for="regionType in availableBenchmarks" :key="regionType">
                                    <th class="border border-gray-300 p-2 text-center" x-text="(data.benchmarks && data.benchmarks[regionType]) ? data.benchmarks[regionType] + '%' : '-'"></th>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Detail info per bulan -->
            <div class="mt-5 text-sm text-gray-700 bg-gray-50 p-4 rounded border border-gray-200">
                <strong>Keterangan:</strong>
                <ul class="ml-5 mt-1 space-y-1">
                    <li><strong>Standar</strong>: Target standar yang harus dicapai (<span x-text="imutData.standard"></span>%)</li>
                    <li><strong>N (Numerator)</strong>: <span x-text="imutData.numerator_description"></span></li>
                    <li><strong>D (Denominator)</strong>: <span x-text="imutData.denominator_description"></span></li>
                    <li><strong>Hasil</strong>: Pencapaian aktual = (N / D) × 100%</li>
                </ul>
            </div>
        </div>

        <!-- Analysis Box -->
        <div class="bg-blue-50 border border-blue-300 rounded p-6 mb-6">
            <h4 class="text-base font-semibold text-blue-900 mb-4">📊 Analisis dan Interpretasi Data</h4>

            <!-- Display selected note data -->
            <div x-show="selectedNote">
                <div class="bg-white rounded-lg p-4 mb-4 border border-blue-200">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <strong class="text-sm font-semibold text-gray-800" x-text="'Catatan: ' + (selectedNote ? selectedNote.note_name : '')"></strong>
                    </div>
                    <div class="text-xs text-gray-600 mb-3">
                        <span class="font-medium">Periode:</span> <span x-text="selectedNote ? selectedNote.period_display : ''"></span>
                    </div>
                </div>

                <div class="mb-4">
                    <div class="flex items-start gap-2 mb-2">
                        <svg class="w-5 h-5 text-blue-700 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        <strong class="text-sm font-semibold text-blue-900">Analisis Data</strong>
                    </div>
                    <div class="ml-7 text-sm leading-relaxed text-gray-800 whitespace-pre-line" x-text="selectedNote ? selectedNote.analysis : ''"></div>
                </div>

                <div class="mb-4">
                    <div class="flex items-start gap-2 mb-2">
                        <svg class="w-5 h-5 text-green-700 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <strong class="text-sm font-semibold text-green-900">Rekomendasi Tindak Lanjut</strong>
                    </div>
                    <div class="ml-7 text-sm leading-relaxed text-gray-800 whitespace-pre-line" x-text="selectedNote ? selectedNote.recommendation : ''"></div>
                </div>
            </div>

            <!-- Fallback: Auto-generated analysis when no note selected -->
            <div x-show="!selectedNote" class="space-y-4">
                <div class="bg-yellow-50 border border-yellow-300 rounded p-4 mb-4">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div class="flex-1">
                            <strong class="text-sm font-semibold text-yellow-800">Tidak Ada Catatan Analisis</strong>
                            <p class="text-xs text-yellow-700 mt-1">Pilih catatan analisis dari filter untuk menampilkan analisis dan rekomendasi yang telah dibuat.</p>
                        </div>
                    </div>
                </div>

                <div>
                    <strong class="block mb-2 text-sm">Capaian Indikator:</strong>
                    <p class="ml-4 text-sm leading-relaxed">
                        Capaian indikator <strong x-text="imutData.title"></strong> pada periode yang dipilih adalah
                        <strong x-text="summary.average_percentage ? summary.average_percentage.toFixed(2) + '%' : 'Loading...'"></strong>.
                        Target standar yang ditetapkan adalah <strong x-text="'≥ ' + imutData.standard + '%'"></strong>.

                        <span x-show="summary.average_percentage >= imutData.standard" class="text-green-700 font-semibold block mt-2">
                            ✓ Indikator ini telah memenuhi standar yang ditetapkan.
                        </span>
                        <span x-show="summary.average_percentage < imutData.standard" class="text-red-700 font-semibold block mt-2">
                            ✗ Indikator ini belum memenuhi standar yang ditetapkan
                            (kurang <span x-text="summary.average_percentage ? (imutData.standard - summary.average_percentage).toFixed(2) : 'Loading...'"></span>%).
                        </span>
                    </p>
                </div>
            </div>
        </div>


        <!-- Footer & Signature -->


        <x-report-footer-signature
            :leftSignature="$leftSignerName"
            :leftSignatureImage="$leftSignerImage"
            :rightSignature="$rightSignerName"
            :rightSignatureImage="$rightSignerImage"
            :date="$signatureDate" />

        <!-- DEBUG PANEL: tampilkan informasi TTD untuk troubleshooting (no-print) -->
        <!-- <div class="no-print mt-4 p-3 border border-red-200 bg-red-50 text-sm rounded">
            <details open>
                <summary class="font-semibold text-red-700">DEBUG TTD — informasi (hapus setelah verifikasi)</summary>


                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-800">
                    <div>
                        <pre class="whitespace-pre-wrap break-words bg-white p-2 border rounded text-xs">{{ json_encode($ttdDebug ?? [], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                    <div class="space-y-2">
                        <div>
                            <strong>Preview Left TTD</strong>
                            <div class="mt-2 border p-2 bg-white text-center">
                                @if($leftSignerImage)
                                <img src="{{ $leftSignerImage }}" alt="left-ttd" class="mx-auto h-20 object-contain border">
                                @else
                                <div class="text-red-500">(no image resolved)</div>
                                @endif
                            </div>
                        </div>

                        <div>
                            <strong>Preview Right TTD</strong>
                            <div class="mt-2 border p-2 bg-white text-center">
                                @if($rightSignerImage)
                                <img src="{{ $rightSignerImage }}" alt="right-ttd" class="mx-auto h-20 object-contain border">
                                @else
                                <div class="text-red-500">(no image resolved)</div>
                                @endif
                            </div>
                        </div>

                        <div>
                            <strong>Tips:</strong>
                            <ul class="ml-4 list-disc">
                                <li>Jika URL ter-resolve tetapi gambar tidak muncul, cek console/network di browser.</li>
                                <li>Jika `ttd_url` relatif, pastikan file ada di <code>storage/app/public/</code> dan sudah dijalankan <code>php artisan storage:link</code>.</li>
                                <li>Untuk S3, periksa konfigurasi disk `s3` di <code>.env</code>.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </details>
        </div> -->
    </div>

    <!-- Preview Controls -->
    <div class="no-print fixed bottom-5 right-5 flex gap-3 z-50">
        <button onclick="history.back()" class="px-4 py-2 bg-gray-600 text-white font-semibold rounded hover:bg-gray-700">
            ← Kembali
        </button>
        <button onclick="printReport()" class="px-4 py-2 bg-green-600 text-white font-semibold rounded hover:bg-green-700">
            🖨️ Print Report
        </button>
    </div>

    <script>
        function printReport() {
            window.print();
        }

        function reportData(indicator, periode) {
            // Calculate default date range (6 months ago to now)
            const now = new Date();
            const sixMonthsAgo = new Date();
            sixMonthsAgo.setMonth(sixMonthsAgo.getMonth() - 6);

            return {
                loading: true,
                periodFilter: 'year',
                selectedNoteId: null,
                selectedNote: null,
                showStandard: true,
                showBenchmarkNasional: true,
                showBenchmarkProvinsi: true,

                // Filter form data - Default to last 6 months
                filterMode: 'custom',
                startMonth: sixMonthsAgo.getMonth() + 1,
                startYear: sixMonthsAgo.getFullYear(),
                endMonth: now.getMonth() + 1,
                endYear: now.getFullYear(),
                quarterYear: new Date().getFullYear(),
                quarters: ['Q1', 'Q2', 'Q3', 'Q4'],
                semesterYear: new Date().getFullYear(),
                semesters: ['S1', 'S2'],
                yearlyYears: [new Date().getFullYear()],

                periodLabels: {},
                imutData: {},
                laporan: {},
                summary: {},
                historicalData: [],
                unitKerjaData: [],
                availableNotes: [],
                chart: null,

                async init() {
                    await this.loadData();
                },

                async loadData() {
                    try {
                        this.loading = true;

                        // Build query parameters
                        const params = new URLSearchParams({
                            filter_mode: this.filterMode,
                            selected_note_id: this.selectedNoteId || '',
                        });

                        // Add filter-specific parameters
                        switch (this.filterMode) {
                            case 'custom':
                                params.append('start_month', this.startMonth);
                                params.append('start_year', this.startYear);
                                params.append('end_month', this.endMonth);
                                params.append('end_year', this.endYear);
                                break;
                            case 'quarter':
                                params.append('quarter_year', this.quarterYear);
                                this.quarters.forEach(q => params.append('quarters[]', q));
                                break;
                            case 'semester':
                                params.append('semester_year', this.semesterYear);
                                this.semesters.forEach(s => params.append('semesters[]', s));
                                break;
                            case 'yearly':
                                this.yearlyYears.forEach(y => params.append('yearly_years[]', y));
                                break;
                        }

                        const response = await fetch(`/api/imut-data/report/${indicator}/${periode}?${params.toString()}`);
                        if (!response.ok) throw new Error('Failed to fetch data');

                        const data = await response.json();

                        this.periodLabels = data.periodLabels;
                        this.imutData = data.imutData;
                        this.laporan = data.laporan;
                        this.summary = data.summary;
                        this.historicalData = data.historicalData;
                        this.unitKerjaData = data.unitKerjaData;
                        this.availableNotes = data.availableNotes;

                        // Check available benchmarks dynamically
                        this.availableBenchmarks = [];
                        this.showBenchmarks = {};

                        const benchmarkSet = new Set();
                        this.historicalData.forEach(d => {
                            if (d.benchmarks) {
                                Object.keys(d.benchmarks).forEach(regionType => {
                                    benchmarkSet.add(regionType);
                                });
                            }
                        });

                        this.availableBenchmarks = Array.from(benchmarkSet).sort();
                        this.availableBenchmarks.forEach(regionType => {
                            this.showBenchmarks[regionType] = true;
                        });

                        // Use selected note from API if available
                        if (data.selectedNote) {
                            this.selectedNote = data.selectedNote;
                        } else if (this.availableNotes.length > 0) {
                            this.selectedNote = this.availableNotes[0];
                            this.selectedNoteId = this.availableNotes[0].id;
                        }

                        this.loading = false;

                        this.$nextTick(() => {
                            if (this.chart) {
                                this.chart.destroy();
                            }
                            this.initChart();
                        });

                    } catch (error) {
                        console.error('Error loading data:', error);
                        this.loading = false;
                        alert('Error loading data. Please check the API endpoint.');
                    }
                },

                async applyFilters() {
                    await this.loadData();
                },

                async changeFilterMode(mode) {
                    this.filterMode = mode;
                    await this.loadData();
                },

                async changeQuarters(quarters) {
                    this.quarters = quarters;
                    await this.loadData();
                },

                async changeSemesters(semesters) {
                    this.semesters = semesters;
                    await this.loadData();
                },

                async changeYearlyYears(years) {
                    this.yearlyYears = years;
                    await this.loadData();
                },

                initChart() {
                    if (!this.historicalData || this.historicalData.length === 0) {
                        console.log('No historical data available for chart');
                        return;
                    }

                    const chartLabels = this.historicalData.map(d => d.month_short + ' ' + d.year);
                    const chartData = this.historicalData.map(d => d.percentage || 0);
                    const standard = this.imutData.standard;

                    const benchmarkNasional = this.historicalData.map(d => d.benchmarks && d.benchmarks['Nasional'] ? d.benchmarks['Nasional'] : null);
                    const benchmarkProvinsi = this.historicalData.map(d => d.benchmarks && d.benchmarks['Provinsi'] ? d.benchmarks['Provinsi'] : null);

                    const series = [{
                        name: 'Pencapaian Aktual',
                        data: chartData
                    }];

                    // Add standard line if enabled
                    if (this.showStandard) {
                        series.push({
                            name: 'Target Standar',
                            data: Array(chartData.length).fill(standard)
                        });
                    }

                    // Add dynamic benchmark series
                    this.availableBenchmarks.forEach((regionType, index) => {
                        if (this.showBenchmarks[regionType]) {
                            series.push({
                                name: 'Benchmark ' + regionType,
                                data: this.historicalData.map(d => d.benchmarks && d.benchmarks[regionType] ? d.benchmarks[regionType] : null)
                            });
                        }
                    });

                    const options = {
                        series: series,
                        chart: {
                            type: 'line',
                            height: 380,
                            toolbar: {
                                show: false
                            },
                            zoom: {
                                enabled: false
                            },
                        },
                        colors: ['#3b82f6', '#ef4444', '#f59e0b', '#10b981'],
                        dataLabels: {
                            enabled: true,
                            enabledOnSeries: [0],
                            formatter: function(val) {
                                return val > 0 ? val.toFixed(2) + '%' : '-';
                            },
                            style: {
                                fontSize: '12px',
                                colors: ['#1e40af']
                            },
                            background: {
                                enabled: true,
                                foreColor: '#fff',
                                padding: 4,
                                borderRadius: 2,
                                borderWidth: 1,
                                borderColor: '#fff',
                                opacity: 0.9,
                            },
                        },
                        stroke: {
                            curve: 'smooth',
                            width: [4, 2, 3, 3],
                            dashArray: [0, 5, 0, 0]
                        },
                        markers: {
                            size: [7, 0, 5, 5],
                            strokeWidth: 2,
                            strokeColors: ['#fff'],
                            colors: ['#3b82f6', '#ef4444', '#f59e0b', '#10b981']
                        },
                        grid: {
                            borderColor: '#e2e8f0',
                            strokeDashArray: 4
                        },
                        xaxis: {
                            categories: chartLabels,
                            labels: {
                                style: {
                                    fontSize: '12px'
                                }
                            }
                        },
                        yaxis: {
                            title: {
                                text: 'Persentase (%)'
                            },
                            labels: {
                                formatter: function(val) {
                                    return val.toFixed(0) + '%';
                                }
                            },
                            min: 0,
                            max: 100
                        },
                        legend: {
                            position: 'top',
                            horizontalAlign: 'right',
                            fontSize: '13px',
                            fontWeight: 600
                        },
                        tooltip: {
                            shared: true,
                            intersect: false,
                            y: {
                                formatter: function(val) {
                                    return val.toFixed(2) + '%';
                                }
                            }
                        },
                        annotations: {
                            yaxis: [{
                                y: standard,
                                borderColor: '#ef4444',
                                label: {
                                    borderColor: '#ef4444',
                                    style: {
                                        color: '#fff',
                                        background: '#ef4444'
                                    },
                                    text: 'Target ' + standard + '%'
                                }
                            }]
                        }
                    };

                    this.chart = new ApexCharts(document.querySelector("#trendChart"), options);
                    this.chart.render();
                },

                updateChart() {
                    if (this.chart && this.historicalData && this.historicalData.length > 0) {
                        const chartLabels = this.historicalData.map(d => d.month_short + ' ' + d.year);
                        const chartData = this.historicalData.map(d => d.percentage || 0);

                        const series = [{
                            name: 'Pencapaian Aktual',
                            data: chartData
                        }];

                        // Add standard line if enabled
                        if (this.showStandard) {
                            series.push({
                                name: 'Target Standar',
                                data: Array(chartData.length).fill(this.imutData.standard)
                            });
                        }

                        // Add dynamic benchmark series based on showBenchmarks state
                        this.availableBenchmarks.forEach((regionType) => {
                            if (this.showBenchmarks[regionType]) {
                                series.push({
                                    name: 'Benchmark ' + regionType,
                                    data: this.historicalData.map(d => d.benchmarks && d.benchmarks[regionType] ? d.benchmarks[regionType] : null)
                                });
                            }
                        });

                        this.chart.updateOptions({
                            xaxis: {
                                categories: chartLabels
                            },
                            series: series
                        });
                    }
                }
            }
        }
    </script>
</body>

</html>