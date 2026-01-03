<x-filament-panels::page>
    <div>
        <div class="space-y-6 relative"
            x-data="{
             filterPeriod: $wire.entangle('filterPeriod').live,
             isMobile: false,
             selectedMonth: '{{ $selectedMonth }}',
             indicators: @js($indicators),
             matrixData: @js($matrixData),
             daysInMonth: @js($daysInMonth),
             
             initResize() {
                 this.isMobile = window.innerWidth < 900;
                 window.addEventListener('resize', () => { this.isMobile = window.innerWidth < 900; });
             },
             
             shouldShowCell(day) {
                 const today = new Date();
                 const currentDay = today.getDate();
                 const weekStart = currentDay - today.getDay();
                 
                 switch(this.filterPeriod) {
                     case 'today': return day === currentDay;
                     case 'weekly': return day >= weekStart && day <= (weekStart + 6);
                     case 'monthly': return true;
                     default: return true;
                 }
             },
             
             getDateInfo(day) {
                 const date = new Date(this.selectedMonth + '-' + day.toString().padStart(2, '0'));
                 const today = new Date();
                 return {
                     isToday: date.toDateString() === today.toDateString(),
                     isWeekend: [0, 6].includes(date.getDay()),
                     dayName: date.toLocaleDateString('id-ID', { weekday: 'long' }),
                     shortDay: date.toLocaleDateString('id-ID', { weekday: 'short' }).substr(0,3),
                     formatted: date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' })
                 };
             },
             
             get visibleDaysCount() {
                 return this.daysInMonth.filter(day => this.shouldShowCell(day)).length;
             }
         }"
            x-init="initResize()"
            wire:key="{{ $selectedMonth }}">
            @include('filament.resources.daily-report-entry-resource.pages.partials.month-navigation')

            <!-- Matrix Table (desktop) -->
            <!-- Filters -->
            <div style="margin-bottom: -20px;">
                <div class="flex flex-row justify-end gap-3">
                    <!-- Segmented Control -->
                    <div class="bg-white dark:bg-slate-700/80 rounded-md shadow-sm inline-flex rounded-lg bg-gray-100 dark:bg-gray-700 p-1">
                        <button
                            wire:click="setFilterPeriod('today')"
                            :class="filterPeriod === 'today'
                ? 'bg-white dark:bg-slate-600/80 text-gray-900 dark:text-white shadow'
                : 'text-gray-500 dark:text-gray-400'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors duration-200">
                            Hari Ini
                        </button>
                        <button
                            wire:click="setFilterPeriod('weekly')"
                            :class="filterPeriod === 'weekly'
                ? 'bg-white dark:bg-slate-600/80 text-gray-900 dark:text-white shadow'
                : 'text-gray-500 dark:text-gray-400'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors duration-200">
                            7 Hari
                        </button>
                        <button
                            wire:click="setFilterPeriod('monthly')"
                            :class="filterPeriod === 'monthly'
                ? 'bg-white dark:bg-slate-600/80 text-gray-900 dark:text-white shadow'
                : 'text-gray-500 dark:text-gray-400'"
                            class="px-3 py-1.5 text-sm font-medium rounded-md transition-colors duration-200">
                            Bulan Ini
                        </button>
                    </div>
                </div>
            </div>

            <!-- Loading overlay -->
            <div wire:loading wire:target="previousMonth,nextMonth" class="absolute inset-0 bg-white/80 dark:bg-slate-800/80 z-50 flex items-center justify-center">
                <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400">
                    <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Memuat data...</span>
                </div>
            </div>

            <!-- Unified Matrix/Mobile View -->
            <div x-show="!isMobile" class="overflow-hidden rounded-xl bg-white dark:bg-slate-800/80 shadow-xl border border-slate-200 dark:border-slate-700">
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white dark:bg-slate-800/80">
                        <thead class="bg-gray-50 dark:bg-slate-900/50">
                            <tr class="border-b border-slate-200 dark:border-slate-700">
                                <th class="sticky left-0 z-20 bg-gray-50 dark:bg-slate-900/50 px-6 py-4 text-left border-r-2 border-slate-200 dark:border-slate-700 min-w-[220px]">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Indikator Mutu</span>
                                </th>
                                <template x-for="day in daysInMonth" :key="day">
                                    <th x-show="shouldShowCell(day)"
                                        class="relative text-center border-r border-slate-200 dark:border-slate-700 w-12 px-2 py-3"
                                        :class="[
                                            getDateInfo(day).isWeekend ? 'bg-red-50 dark:bg-red-900/20' : 'bg-gray-50 dark:bg-slate-900/50',
                                            filterPeriod === 'today' ? 'min-w-[760px] px-6 py-5' : ''
                                        ]">
                                        <div class="space-y-1">
                                            <template x-if="getDateInfo(day).isToday">
                                                <div class="flex flex-col items-center gap-1">
                                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded bg-primary-600 text-white">Hari Ini</span>
                                                    <div class="flex items-center gap-2">
                                                        <span
                                                            :class="filterPeriod === 'today' ? 'text-sm' : 'text-xs text-gray-500'"
                                                            class="text-gray-600 dark:text-gray-300"
                                                            x-text="getDateInfo(day).dayName + ' · ' + getDateInfo(day).formatted">
                                                        </span>
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
                            @forelse($indicators as $indicator)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors duration-75">
                                <td class="sticky left-0 z-10 bg-white dark:bg-slate-800/80 hover:bg-gray-50 dark:hover:bg-gray-900/40 px-6 py-4 border-r-2 border-slate-200 dark:border-slate-700 transition-colors duration-75 min-w-[220px]">
                                    <div class="flex items-start gap-3">
                                        <div class="flex flex-col space-y-1 flex-1 min-w-0">
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white leading-snug">{{ $indicator['title'] }}</span>
                                            @if($indicator['category'])
                                            <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                                @svg("heroicon-m-tag", "w-3 h-3")
                                                {{ $indicator['category'] }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                @foreach($daysInMonth as $day)
                                @if($this->shouldShowCell($day, $filterPeriod))
                                @php
                                $cellData = $matrixData[$indicator['id']][$day] ?? null;
                                @endphp
                                @include('filament.resources.daily-report-entry-resource.pages.partials.matrix-cell', [
                                'state' => $cellData['cell_state'] ?? 'disabled',
                                'summary' => $cellData['summary'] ?? null,
                                'cellData' => $cellData,
                                'dateStr' => $cellData['date'] ?? '',
                                'isToday' => $cellData['is_today'] ?? false,
                                'indicatorId' => $indicator['id'],
                                'day' => $day,
                                ])
                                @endif
                                @endforeach
                            </tr>
                            @empty
                            <tr>
                                <td colspan="{{ count($daysInMonth) + 1 }}" class="px-4 py-16 text-center">
                                    @include('filament.resources.daily-report-entry-resource.pages.partials.empty-state')
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Mobile view -->
            <div x-show="isMobile" class="space-y-5">
                @foreach($indicators as $indicator)
                <div class="bg-white dark:bg-slate-800/80 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-4">
                    <div class="mb-3">
                        <div class="text-sm font-semibold text-gray-900 dark:text-white leading-tight">{{ $indicator['title'] }}</div>
                        @if($indicator['category'])
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $indicator['category'] }}</div>
                        @endif
                    </div>
                    <div class="flex gap-3 overflow-x-auto pb-3 -mx-1 px-1 scroll-smooth snap-x">
                        @foreach($daysInMonth as $day)
                        @if($this->shouldShowCell($day, $filterPeriod))
                        @php
                        $cellData = $matrixData[$indicator['id']][$day] ?? null;
                        @endphp
                        @include('filament.resources.daily-report-entry-resource.pages.partials.mobile-card', [
                        'indicator' => $indicator,
                        'day' => $day,
                        'cellData' => $cellData,
                        'selectedMonth' => $selectedMonth
                        ])
                        @endif
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>

            @include('filament.resources.daily-report-entry-resource.pages.partials.legend')
        </div>

        {{-- Slide-over rendered outside the page wrapper to prevent overflow clipping --}}
        @include('filament.resources.daily-report-entry-resource.pages.partials.slide-over')
    </div>
</x-filament-panels::page>