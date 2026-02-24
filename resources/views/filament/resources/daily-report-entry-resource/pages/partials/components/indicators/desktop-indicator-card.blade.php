<!-- Desktop Indicator Card -->
<div class="border border-slate-200 dark:border-slate-700 rounded-xl p-4 mt-2 hover:shadow-sm transition-all duration-200 indicator-card"
    x-data="{
         // status for refreshing the card
         refreshing: false,

         async refreshStatus() {
             if (this.refreshing) return;
             this.refreshing = true;
             try {
                 // Force refresh matrix data from server
                 await $wire.call('refreshMatrixData');
                 // Give a small delay for visual feedback
                 setTimeout(() => this.refreshing = false, 300);
             } catch (error) {
                 console.error('Error refreshing status:', error);
                 this.refreshing = false;
             }
         }
     }">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <!-- Indicator Info -->
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-snug" x-text="indicator.title"></h3>
                <!-- Refresh indicator -->
                <button @click="refreshStatus()"
                    :disabled="refreshing"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors"
                    title="Refresh status">
                    <svg :class="{ 'animate-spin': refreshing }" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                </button>
            </div>

            <div class="flex items-center gap-4 mt-2">
                <!-- Target -->
                <div x-show="indicator.target" class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Target:</span>
                    <span class="text-xs font-medium text-gray-700 dark:text-gray-300" x-text="indicator.target"></span>
                </div>
            </div>

            <!-- Category -->
            <div class="mt-2" x-show="indicator.category">
                <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded" :class="getCategoryColor(indicator.category)">
                    @svg("heroicon-m-tag", "w-3 h-3")
                    <span x-text="indicator.category"></span>
                </span>
            </div>

            <!-- ImutProfile Version -->
            <div class="mt-2" x-show="indicator.imut_profile_version">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Profil IMUT:</span>
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-full border border-indigo-200 dark:border-indigo-800">
                        @svg("heroicon-m-document-text", "w-3 h-3")
                        <span x-text="formatImutVersion(indicator.imut_profile_version)"></span>
                    </span>
                </div>
            </div>

            <div class="mt-2">
                <span
                    class="inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-md"
                    :class="getReportCount(indicator.id, selectedDate) > 0 
            ? 'bg-green-100 text-green-700' 
            : 'bg-gray-100 text-gray-500'">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6M7 8h10M5 3h14a2 2 0 012 2v14l-4-3H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
                    </svg>

                    <span x-text="getReportCount(indicator.id, selectedDate) + ' laporan'"></span>
                </span>
            </div>
        </div>

        <!-- Dynamic Action Button -->
        <div class="flex-shrink-0">
            @include('filament.resources.daily-report-entry-resource.pages.partials.components.indicators.action-buttons')
        </div>
    </div>
</div>