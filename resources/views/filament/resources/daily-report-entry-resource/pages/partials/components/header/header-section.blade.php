<!-- Header Section -->
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
    <div class="flex flex-col gap-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    @svg("heroicon-o-calendar-days", "w-7 h-7 text-primary-600")
                    SI-IMUT – Laporan Harian
                </h1>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Input dan monitoring laporan harian indikator mutu
                </p>
            </div>

            <!-- Tab Navigation -->
            <div class="flex bg-slate-100 dark:bg-slate-700 rounded-lg p-1">
                <button
                    @click="$wire.changeView('input')"
                    :class="$wire.currentView === 'input'
                        ? 'bg-white dark:bg-slate-600 text-gray-900 dark:text-white shadow-sm'
                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-all">
                    Input Harian
                </button>
                <button
                    @click="$wire.changeView('monitoring')"
                    :class="$wire.currentView === 'monitoring'
                        ? 'bg-white dark:bg-slate-600 text-gray-900 dark:text-white shadow-sm'
                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300'"
                    class="px-4 py-2 text-sm font-medium rounded-md transition-all">
                    Monitoring Bulanan
                </button>
            </div>
        </div>

        @include('filament.resources.daily-report-entry-resource.pages.partials.components.header.filters-section')
    </div>
</div>