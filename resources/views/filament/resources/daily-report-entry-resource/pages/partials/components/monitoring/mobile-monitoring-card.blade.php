<!-- Mobile Monitoring Card -->
<div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
    <!-- Header with Icon and Title -->
    <div class="flex items-start gap-3 mb-4">
        <div class="flex-shrink-0 w-12 h-12 bg-primary-100 dark:bg-primary-900/20 rounded-xl flex items-center justify-center">
            @svg("heroicon-o-document-text", "w-6 h-6 text-primary-600 dark:text-primary-400")
        </div>
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-tight" x-text="item.title"></h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1" x-show="item.description" x-text="item.description"></p>
        </div>
    </div>

    <!-- Info Grid -->
    <div class="space-y-3 mb-4">
        <!-- Profile -->
        <div class="flex items-center justify-between">
            <span class="text-xs text-gray-500 dark:text-gray-400">Profil IMUT:</span>
            <div x-show="item.profile_name">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-md border border-indigo-200 dark:border-indigo-800">
                    @svg("heroicon-m-identification", "w-3 h-3")
                    <span x-text="item.profile_name"></span>
                </span>
            </div>
            <span x-show="!item.profile_name" class="text-xs text-gray-400 dark:text-gray-500 italic">Tidak ada</span>
        </div>

        <!-- Category -->
        <div class="flex items-center justify-between">
            <span class="text-xs text-gray-500 dark:text-gray-400">Kategori:</span>
            <div x-show="item.category">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-md" :class="getCategoryColor(item.category)" x-text="item.category"></span>
            </div>
            <span x-show="!item.category" class="text-xs text-gray-400 dark:text-gray-500 italic">-</span>
        </div>

        <!-- Response Count -->
        <div class="flex items-center justify-between py-2 px-3 bg-gray-50 dark:bg-slate-700 rounded-lg">
            <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Total Response:</span>
            <div class="flex items-center gap-1.5">
                @svg("heroicon-o-chart-bar", "w-4 h-4 text-primary-600 dark:text-primary-400")
                <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="formatNumber(item.response_count)"></span>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex flex-col gap-2">
        <!-- View Detail Button -->
        <button
            @click="$wire.call('viewMonitoringDetail', item.id)"
            class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30 rounded-lg border border-primary-200 dark:border-primary-800 transition-colors">
            @svg("heroicon-o-eye", "w-4 h-4")
            <span>Lihat Detail</span>
        </button>

        <!-- View Responses & Export Buttons -->
        <div class="grid grid-cols-2 gap-2">
            <button
                @click="$wire.call('viewMonitoringResponses', item.id)"
                class="flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800 transition-colors">
                @svg("heroicon-o-document-chart-bar", "w-4 h-4")
                <span>Response</span>
            </button>

            <button
                @click="$wire.call('exportMonitoring', item.id)"
                class="flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg border border-green-200 dark:border-green-800 transition-colors">
                @svg("heroicon-o-arrow-down-tray", "w-4 h-4")
                <span>Export</span>
            </button>
        </div>
    </div>
</div>