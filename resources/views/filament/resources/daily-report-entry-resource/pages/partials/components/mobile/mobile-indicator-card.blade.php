<!-- Mobile Indicator Card -->
<div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
    <!-- Mobile Header -->
    <div class="flex items-start justify-between gap-3 mb-3">
        <div class="flex-1 min-w-0">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-tight" x-text="indicator.title"></h3>
            <div class="mt-1" x-show="indicator.category">
                <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full" :class="getCategoryColor(indicator.category)">
                    <span x-text="indicator.category"></span>
                </span>
            </div>
            <div class="mt-2" x-show="indicator.imut_profile_version">
                <div class="flex items-center gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400">Profil:</span>
                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 rounded-full border border-indigo-200 dark:border-indigo-800">
                        @svg("heroicon-m-document-text", "w-3 h-3")
                        <span x-text="formatImutVersion(indicator.imut_profile_version)"></span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Status -->
    <div class="mb-4">
        @include('filament.resources.daily-report-entry-resource.pages.partials.components.mobile.mobile-status-cards')
    </div>

    <!-- Mobile Action Button -->
    <div class="flex gap-2">
        @include('filament.resources.daily-report-entry-resource.pages.partials.components.mobile.mobile-action-buttons')
    </div>
</div>