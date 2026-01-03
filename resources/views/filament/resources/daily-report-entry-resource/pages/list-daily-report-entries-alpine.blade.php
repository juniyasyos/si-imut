<x-filament-panels::page>
    @include('filament.resources.daily-report-entry-resource.pages.partials.alpine-matrix')

    <div class="space-y-6 relative"
        x-data="matrixManager({{ json_encode($this->getAlpineData()) }})"
        x-init="init()">

        @include('filament.resources.daily-report-entry-resource.pages.partials.month-navigation')

        <!-- Filters -->
        <div style="margin-bottom: -20px;">
            <div class="flex flex-row justify-end gap-3">
                <div class="bg-white dark:bg-slate-700/80 rounded-md shadow-sm inline-flex rounded-lg bg-gray-100 dark:bg-gray-700 p-1">
                    <button @click="filterPeriod = 'today'" :class="filterPeriod === 'today' ? 'bg-white dark:bg-slate-600/80 text-gray-900 dark:text-white shadow' : 'text-gray-500 dark:text-gray-400'" class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors duration-200">Hari Ini</button>
                    <button @click="filterPeriod = 'weekly'" :class="filterPeriod === 'weekly' ? 'bg-white dark:bg-slate-600/80 text-gray-900 dark:text-white shadow' : 'text-gray-500 dark:text-gray-400'" class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors duration-200">7 Hari</button>
                    <button @click="filterPeriod = 'monthly'" :class="filterPeriod === 'monthly' ? 'bg-white dark:bg-slate-600/80 text-gray-900 dark:text-white shadow' : 'text-gray-500 dark:text-gray-400'" class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors duration-200">Bulan Ini</button>
                </div>
            </div>
        </div>

        <!-- Desktop Matrix Table -->
        <div x-show="!isMobile" class="overflow-hidden rounded-xl bg-white dark:bg-slate-800/80 shadow-xl border border-slate-200 dark:border-slate-700">
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white dark:bg-slate-800/80">
                    <thead class="bg-gray-50 dark:bg-slate-900/50">
                        <tr class="border-b border-slate-200 dark:border-slate-700">
                            <th class="sticky left-0 z-20 bg-gray-50 dark:bg-slate-900/50 px-6 py-4 text-left border-r-2 border-slate-200 dark:border-slate-700 min-w-[220px]">
                                <span class="text-sm font-semibold text-gray-900 dark:text-white">Indikator Mutu</span>
                            </th>

                            <template x-for="day in daysInMonth" :key="day">
                                <th x-show="shouldShowCell(day)" class="relative text-center border-r border-slate-200 dark:border-slate-700 w-12 px-2 py-3" :class="[getDateInfo(day).isWeekend ? 'bg-red-50 dark:bg-red-900/20' : 'bg-gray-50 dark:bg-slate-900/50', filterPeriod === 'today' ? 'min-w-[760px] px-6 py-5' : '']">
                                    <div class="space-y-1">
                                        <template x-if="getDateInfo(day).isToday">
                                            <div class="flex flex-col items-center gap-1">
                                                <span class="px-2 py-0.5 text-[11px] font-semibold rounded bg-primary-600 text-white">Hari Ini</span>
                                                <div class="flex items-center gap-2">
                                                    <span :class="filterPeriod === 'today' ? 'text-sm' : 'text-xs text-gray-500'" class="text-gray-600 dark:text-gray-300" x-text="getDateInfo(day).dayName + ' · ' + getDateInfo(day).formatted"></span>
                                                </div>
                                            </div>
                                        </template>
                                        <template x-if="!getDateInfo(day).isToday">
                                            <div>
                                                <div :class="filterPeriod === 'today' ? 'text-sm font-bold' : 'text-xs font-medium'" x-text="day"></div>
                                                <div :class="filterPeriod === 'today' ? 'text-sm' : 'text-xs text-gray-500'" x-text="getDateInfo(day).shortDay"></div>
                                            </div>
                                        </template>
                                    </div>
                                </th>
                            </template>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <template x-for="indicator in indicators" :key="indicator.id">
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors duration-75">
                                <td class="sticky left-0 z-10 bg-white dark:bg-slate-800/80 hover:bg-gray-50 dark:hover:bg-gray-900/40 px-6 py-4 border-r-2 border-slate-200 dark:border-slate-700 transition-colors duration-75 min-w-[220px]">
                                    <div class="flex items-start gap-3">
                                        <div class="flex flex-col space-y-1 flex-1 min-w-0">
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white leading-snug" x-text="indicator.title"></span>
                                            <template x-if="indicator.category">
                                                <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                                    @svg("heroicon-m-tag", "w-3 h-3")
                                                    <span x-text="indicator.category"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </td>

                                <template x-for="day in daysInMonth" :key="day">
                                    <td x-show="shouldShowCell(day)" x-html="renderMatrixCell(indicator.id, day)"></td>
                                </template>
                            </tr>
                        </template>

                        <template x-if="indicators.length === 0">
                            <tr>
                                <td :colspan="visibleDaysCount + 1" class="px-4 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-4">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-primary-100 dark:bg-primary-900/20 rounded-full blur-2xl opacity-50"></div>
                                            <div class="relative w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-2xl flex items-center justify-center shadow-lg">
                                                @svg("heroicon-o-clipboard-document-list", "w-10 h-10 text-gray-400 dark:text-gray-500")
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <p class="text-base font-bold text-gray-700 dark:text-gray-300">Belum Ada Indikator</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">Belum ada indikator mutu yang dikonfigurasi untuk unit kerja Anda.</p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">Silakan hubungi administrator sistem</p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Mobile view -->
        <div x-show="isMobile" class="space-y-5">
            <template x-for="indicator in indicators" :key="indicator.id">
                <div class="bg-white dark:bg-slate-800/80 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-4">
                    <div class="mb-3">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white leading-tight" x-text="indicator.title"></div>
                        <template x-if="indicator.category">
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="indicator.category"></div>
                        </template>
                    </div>

                    <div class="flex gap-3 overflow-x-auto pb-3 -mx-1 px-1 scroll-smooth snap-x">
                        <template x-for="day in daysInMonth" :key="day">
                            <div x-show="shouldShowCell(day)" x-html="renderMobileCard(indicator, day)"></div>
                        </template>
                    </div>
                </div>
            </template>
        </div>
    </div>

    @include('filament.resources.daily-report-entry-resource.pages.partials.slide-over')
</x-filament-panels::page>