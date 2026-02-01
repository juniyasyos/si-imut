<!-- Desktop Monitoring Table Row -->
<tr class="hover:bg-gray-50 dark:hover:bg-slate-700/50 transition-colors">
    <!-- Form Template -->
    <td class="px-4 py-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-10 h-10 bg-primary-100 dark:bg-primary-900/20 rounded-lg flex items-center justify-center">
                @svg("heroicon-o-document-text", "w-5 h-5 text-primary-600 dark:text-primary-400")
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-semibold text-gray-900 dark:text-white leading-tight" x-text="item.title"></p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-show="item.description" x-text="item.description"></p>
            </div>
        </div>
    </td>

    <!-- Profil IMUT -->
    <td class="px-4 py-4">
        <div x-show="item.profile_name">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-lg border border-indigo-200 dark:border-indigo-800">
                @svg("heroicon-m-identification", "w-3.5 h-3.5")
                <span x-text="item.profile_name"></span>
            </span>
        </div>
        <div x-show="!item.profile_name">
            <span class="text-xs text-gray-400 dark:text-gray-500 italic">Tidak ada profil</span>
        </div>
    </td>

    <!-- Kategori -->
    <td class="px-4 py-4">
        <div x-show="item.category">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-lg" :class="getCategoryColor(item.category)">
                <span x-text="item.category"></span>
            </span>
        </div>
        <div x-show="!item.category">
            <span class="text-xs text-gray-400 dark:text-gray-500 italic">-</span>
        </div>
    </td>

    <!-- Total Response -->
    <td class="px-4 py-4 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-slate-700 rounded-lg">
            @svg("heroicon-o-chart-bar", "w-4 h-4 text-gray-600 dark:text-gray-400")
            <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="formatNumber(item.response_count)"></span>
            <span class="text-xs text-gray-500 dark:text-gray-400">response</span>
        </div>
    </td>

    <!-- Actions -->
    <td class="px-4 py-4">
        <div class="flex items-center justify-end gap-2">
            <!-- View Detail Button -->
            <button
                @click="$wire.call('viewMonitoringDetail', item.id)"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30 rounded-lg border border-primary-200 dark:border-primary-800 transition-colors"
                title="Lihat Detail">
                @svg("heroicon-o-eye", "w-4 h-4")
                <span>Detail</span>
            </button>

            <!-- View Responses Button -->
            <button
                @click="$wire.call('viewMonitoringResponses', item.id)"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30 rounded-lg border border-blue-200 dark:border-blue-800 transition-colors"
                title="Lihat Response">
                @svg("heroicon-o-document-chart-bar", "w-4 h-4")
                <span>Response</span>
            </button>

            <!-- Export Button -->
            <button
                @click="$wire.call('exportMonitoring', item.id)"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg border border-green-200 dark:border-green-800 transition-colors"
                title="Export Data">
                @svg("heroicon-o-arrow-down-tray", "w-4 h-4")
            </button>
        </div>
    </td>
</tr>