<!-- Monitoring Bulanan View -->
<div x-show="currentView === 'monitoring'"
    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6"
    style="display: none;">

    <!-- Full Screen Loading Overlay -->
    <div x-show="isDateLoading" x-transition.opacity.duration.500ms class="fixed inset-0 bg-white/70 dark:bg-slate-900/70 backdrop-blur-md z-[9999]" style="display: none;"></div>

    <!-- Header with Search -->
    <div class="mb-6">
        <div class="flex flex-col gap-4">
            <!-- Title and Period Navigation -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Monitoring Bulanan</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1" x-text="getMonitoringPeriodText()"></p>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2">
                    <!-- Period Navigation -->
                    <button
                        @click="changeMonitoringPeriod('prev')"
                        class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                        <span class="hidden sm:inline">Sebelumnya</span>
                    </button>

                    <button
                        @click="changeMonitoringPeriod('current')"
                        class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg hover:bg-primary-100 dark:hover:bg-primary-900/30 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <span class="hidden sm:inline">Bulan Ini</span>
                    </button>

                    <button
                        @click="changeMonitoringPeriod('next')"
                        class="inline-flex items-center gap-1 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-700 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-600 transition-colors">
                        <span class="hidden sm:inline">Berikutnya</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Search Box -->
            <div class="flex items-center gap-2">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        @svg("heroicon-o-magnifying-glass", "w-5 h-5 text-gray-400")
                    </div>
                    <input type="text"
                        x-model="monitoringSearchQuery"
                        placeholder="Cari form template..."
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                </div>
            </div>
        </div>
    </div>

    <!-- Templates List -->
    <div class="space-y-4 max-h-[600px] overflow-y-auto">
        <!-- Desktop View -->
        <div class="hidden lg:block">
            <template x-for="item in filteredMonitoringData" :key="item.id">
                <div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4 mt-2 hover:shadow-sm transition-all duration-200">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <!-- Template Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <h3 class="text-md font-semibold text-gray-900 dark:text-white leading-snug" x-text="item.title"></h3>
                            </div>
                            <!-- Description if exists -->
                            <div class="mt-1 mb-2" x-show="item.description">
                                <p class="text-sm text-gray-700 dark:text-gray-400" x-text="item.description"></p>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs text-gray-500 dark:text-gray-400">Profil IMUT:</span>
                                <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-full border border-indigo-200 dark:border-indigo-800">
                                    @svg("heroicon-m-document-text", "w-3 h-3")
                                    <span x-text="formatImutVersion(item.imut_profile_version)"></span>
                                </span>
                            </div>

                            <div class="flex items-center gap-4 mt-2">
                                <!-- Response Count -->
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Responses:</span>
                                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-gray-100 dark:bg-slate-700 rounded-md">
                                        <svg class="w-3.5 h-3.5 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        <span class="text-xs font-bold text-gray-900 dark:text-white" x-text="formatNumber(item.response_count)"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Category & Profile -->
                            <div class="flex items-center gap-3 mt-2">
                                <!-- Category -->
                                <div x-show="item.category">
                                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded" :class="getCategoryColor(item.category)">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                        </svg>
                                        <span x-text="item.category"></span>
                                    </span>
                                </div>

                                <!-- Profile -->
                                <div x-show="item.profile_name">
                                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-full border border-indigo-200 dark:border-indigo-800">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                        </svg>
                                        <span x-text="item.profile_name"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex-shrink-0">
                            <div class="flex items-center gap-2">
                                <!-- View Detail Button - Open Table View -->
                                <a
                                    :href="`{{ route('table-view') }}?form_template_id=${item.id}&imut_profile_id=${item.imut_profile_id}&unit_kerja_id=${item.unit_kerja_id || ''}&period=${monitoringMonth}`"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30 rounded-lg border border-primary-200 dark:border-primary-800 transition-colors"
                                    title="Lihat Tabel Data">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                    </svg>
                                    <span class="hidden sm:inline">Lihat Tabel</span>
                                </a>

                                <!-- Export Button -->
                                <a
                                    :href="`{{ route('export.monitoring', ':templateId') }}`.replace(':templateId', item.id) + '?month=' + monitoringMonth"
                                    target="_blank"
                                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg border border-green-200 dark:border-green-800 transition-colors"
                                    title="Download Excel">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <span class="hidden sm:inline">Download Excel</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Mobile View -->
        <div class="block lg:hidden space-y-4">
            <template x-for="item in filteredMonitoringData" :key="item.id">
                <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                    <!-- Header with Title -->
                    <div class="mb-3">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-tight" x-text="item.title"></h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="item.description" x-text="item.description"></p>
                    </div>

                    <!-- Info Grid -->
                    <div class="space-y-2 mb-4">
                        <!-- Response Count -->
                        <div class="flex items-center justify-between py-2 px-3 bg-gray-50 dark:bg-slate-700 rounded-lg">
                            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Total Response:</span>
                            <div class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                                <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="formatNumber(item.response_count)"></span>
                            </div>
                        </div>

                        <!-- Category & Profile -->
                        <div class="flex items-center gap-2 flex-wrap">
                            <!-- Category -->
                            <div x-show="item.category">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded" :class="getCategoryColor(item.category)" x-text="item.category"></span>
                            </div>

                            <!-- Profile -->
                            <div x-show="item.profile_name">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-md border border-indigo-200 dark:border-indigo-800">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                    </svg>
                                    <span x-text="item.profile_name"></span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col gap-2">
                        <!-- View Detail Button - Open Table View -->
                        <a
                            :href="`{{ route('table-view') }}?form_template_id=${item.id}&imut_profile_id=${item.imut_profile_id}&unit_kerja_id=${item.unit_kerja_id || ''}&period=${monitoringMonth}`"
                            target="_blank"
                            class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30 rounded-lg border border-primary-200 dark:border-primary-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                            </svg>
                            <span>Lihat Tabel</span>
                        </a>

                        <!-- View Responses & Export Buttons -->
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                @click="$wire.call('viewMonitoringResponses', item.id)"
                                class="flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>Response</span>
                            </button>

                            <a
                                :href="`{{ route('export.monitoring', ':templateId') }}`.replace(':templateId', item.id) + '?month=' + monitoringMonth"
                                target="_blank"
                                class="flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg border border-green-200 dark:border-green-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                <span>Download Excel</span>
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Empty State -->
        <div x-show="filteredMonitoringData.length === 0" class="text-center py-16">
            <div class="space-y-4">
                <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-2xl flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tidak ada data</h3>
                    <p class="text-gray-500 dark:text-gray-400">Belum ada data monitoring untuk periode ini</p>
                </div>
            </div>
        </div>
    </div>
</div>