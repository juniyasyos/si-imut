<!-- Desktop Monitoring Card -->
<div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4 mt-2 hover:shadow-sm transition-all duration-200">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <!-- Template Info -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-snug" x-text="item.title"></h3>
            </div>

            <!-- Description if exists -->
            <div class="mt-1" x-show="item.description">
                <p class="text-xs text-gray-500 dark:text-gray-400" x-text="item.description"></p>
            </div>

            <div class="flex items-center gap-4 mt-2">
                <!-- Response Count -->
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Responses:</span>
                    <div class="inline-flex items-center gap-1.5 px-2 py-0.5 bg-gray-100 dark:bg-slate-700 rounded-md">
                        @svg("heroicon-o-chart-bar", "w-3.5 h-3.5 text-gray-600 dark:text-gray-400")
                        <span class="text-xs font-bold text-gray-900 dark:text-white" x-text="formatNumber(item.response_count)"></span>
                    </div>
                </div>
            </div>

            <!-- Category & Profile -->
            <div class="flex items-center gap-3 mt-2">
                <!-- Category -->
                <div x-show="item.category">
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded" :class="getCategoryColor(item.category)">
                        @svg("heroicon-m-tag", "w-3 h-3")
                        <span x-text="item.category"></span>
                    </span>
                </div>

                <!-- Profile -->
                <div x-show="item.profile_name">
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-full border border-indigo-200 dark:border-indigo-800">
                        @svg("heroicon-m-identification", "w-3 h-3")
                        <span x-text="item.profile_name"></span>
                    </span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex-shrink-0">
            <div class="flex items-center gap-2">
                <!-- View Detail Button -->
                <button
                    @click="$wire.call('viewMonitoringDetail', item.id)"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30 rounded-lg border border-primary-200 dark:border-primary-800 transition-colors"
                    title="Lihat Detail">
                    @svg("heroicon-o-eye", "w-4 h-4")
                    <span class="hidden sm:inline">Detail</span>
                </button>

                <!-- View Responses Button -->
                <button
                    @click="$wire.call('viewMonitoringResponses', item.id)"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800 transition-colors"
                    title="Lihat Response">
                    @svg("heroicon-o-document-chart-bar", "w-4 h-4")
                    <span class="hidden sm:inline">Response</span>
                </button>

                <!-- Export Button -->
                <button
                    @click="$wire.call('exportMonitoring', item.id)"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg border border-green-200 dark:border-green-800 transition-colors"
                    title="Export Data">
                    @svg("heroicon-o-arrow-down-tray", "w-4 h-4")
                </button>
            </div>
        </div>
    </div>
</div>