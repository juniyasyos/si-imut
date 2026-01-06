<!-- Desktop Indicator Card -->
<div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-sm transition-all duration-200 indicator-card">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <!-- Indicator Info -->
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-snug" x-text="indicator.title"></h3>

            <div class="flex items-center gap-4 mt-2">
                <!-- Dynamic Status Display -->
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Status:</span>
                    <div>
                        @include('filament.resources.daily-report-entry-resource.pages.partials.components.indicators.status-indicator')
                    </div>
                </div>

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
        </div>

        <!-- Dynamic Action Button -->
        <div class="flex-shrink-0">
            @include('filament.resources.daily-report-entry-resource.pages.partials.components.indicators.action-buttons')
        </div>
    </div>
</div>