<!-- Selected Date Header -->
<div class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0 flex-1">
            <div class="flex items-start gap-3">
                <div
                    class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-600 dark:bg-primary-950/40 dark:text-primary-400">
                    @svg("heroicon-m-calendar-days", "h-5 w-5")
                </div>

                <div class="min-w-0">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                        Tanggal Laporan
                    </p>

                    <h2 class="mt-0.5 text-lg font-semibold leading-snug text-slate-950 dark:text-white">
                        <span x-text="formatDate(selectedDate)"></span>
                    </h2>
                </div>
            </div>
        </div>

        <div class="shrink-0 lg:pt-1">
            <span
                class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 dark:border-emerald-800 dark:bg-emerald-950/30 dark:text-emerald-300">
                @svg("heroicon-m-check-circle", "h-3.5 w-3.5")
                Periode Aktif
            </span>
        </div>
    </div>

    <div
        class="mt-4 rounded-lg border border-blue-100 bg-blue-50 px-3 py-2.5 text-xs text-blue-700 dark:border-blue-900/60 dark:bg-blue-950/30 dark:text-blue-300">
        <div class="flex items-start gap-2">
            @svg("heroicon-m-information-circle", "mt-0.5 h-4 w-4 shrink-0")
            <p class="leading-relaxed">
                Pengisian laporan harian masih dapat dilakukan untuk tanggal laporan hingga
                <span class="font-semibold">
                    {{ \App\Services\DailyReport\CachedSettingsService::getBackDataEntryDays() }} hari ke belakang
                </span>
                dari hari ini.
            </p>
        </div>
    </div>
</div>