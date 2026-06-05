<!-- Desktop Indicator Card -->
<div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4 mt-2 hover:shadow-sm transition-all duration-200 indicator-card">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <!-- Indicator Info -->
        <div class="flex-1 min-w-0">
            <div class="flex justify-between items-center gap-2">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-snug" x-text="indicator.title">
                </h3>
            </div>

            <!-- ImutProfile Version -->
            <div class="mb-4 mt-1" x-show="indicator.imut_profile_version">
                <div class="flex items-center gap-2">
                    <span class="text-xs italic text-gray-500 dark:text-gray-400">profile version:</span>
                    <span class="inline-flex items-center gap-1 text-xs italic text-gray-500 dark:text-gray-400">
                        @svg("heroicon-m-document-text", "w-3 h-3")
                        <span x-text="formatImutVersion(indicator.imut_profile_version)"></span>
                    </span>
                </div>
            </div>


            <!-- Category -->
            <div class="mt-2" x-show="indicator.category">
                <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded"
                    :class="getCategoryColor(indicator.category)">
                    @svg("heroicon-m-tag", "w-3 h-3")
                    <span x-text="indicator.category"></span>
                </span>
            </div>

            <div class="mt-2">
                <span class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-md" :class="reportCountsLoading[indicator.id]
                    ? 'bg-gray-100 text-gray-500' 
                    : (reportCounts[indicator.id] > 0 ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6M7 8h10M5 3h14a2 2 0 012 2v14l-4-3H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
                    </svg>

                    <span x-text="reportCountsLoading[indicator.id] ? 'Memuat...' : (reportCounts[indicator.id] ? reportCounts[indicator.id] + ' laporan' : '0 laporan')"></span>
                </span>
            </div>
        </div>

        <!-- Dynamic Action Button -->
        <div class="flex-shrink-0">
            @include('filament.resources.daily-report-entry-resource.pages.partials.components.indicators.action-buttons')
        </div>
    </div>
</div>