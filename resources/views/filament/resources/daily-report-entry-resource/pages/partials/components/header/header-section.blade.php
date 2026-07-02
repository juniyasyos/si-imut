<!-- Header Section -->
<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800">
    <div class="flex flex-col gap-5">
        <!-- Top Header -->
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div class="min-w-0">
                <h1 class="flex items-center gap-2 text-xl font-bold text-slate-950 dark:text-white">
                    @svg("heroicon-o-calendar-days", "h-6 w-6 text-primary-600")
                    <span>SI-IMUT – Laporan Harian</span>
                </h1>

                <div class="mt-2 flex flex-wrap items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                    <span>Input, pantau, dan kelola laporan indikator mutu harian.</span>

                    <span class="hidden h-1 w-1 rounded-full bg-slate-300 dark:bg-slate-600 sm:inline-block"></span>

                    <span
                        class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-700 dark:bg-slate-700 dark:text-slate-300">
                        @svg("heroicon-m-building-office-2", "h-3.5 w-3.5")
                        <span>Unit Kerja:</span>
                        <span class="font-semibold">
                            {{ auth()->user()->unitKerjas->first()?->unit_name ?? 'Tidak ada unit' }}
                        </span>
                    </span>
                </div>
            </div>

            <!-- Tab Navigation -->
            <div class="flex w-full rounded-lg bg-slate-100 p-1 dark:bg-slate-700 sm:w-auto">
                <button @click="$wire.changeView('input')" :class="$wire.currentView === 'input'
                        ? 'bg-white text-slate-950 shadow-sm dark:bg-slate-600 dark:text-white'
                        : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300'"
                    class="flex-1 rounded-md px-4 py-2 text-sm font-medium transition-all sm:flex-none">
                    Input Harian
                </button>

                <button @click="$wire.changeView('monitoring')" :class="$wire.currentView === 'monitoring'
                        ? 'bg-white text-slate-950 shadow-sm dark:bg-slate-600 dark:text-white'
                        : 'text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-300'"
                    class="flex-1 rounded-md px-4 py-2 text-sm font-medium transition-all sm:flex-none">
                    Monitoring Bulanan
                </button>
            </div>
        </div>
    </div>
</div>