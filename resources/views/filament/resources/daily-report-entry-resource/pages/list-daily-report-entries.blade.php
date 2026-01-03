<x-filament-panels::page>
    <div>
        <div class="space-y-6 relative"
            x-data="{
             filterPeriod: $wire.entangle('filterPeriod').live,
             isMobile: false,
             initResize() {
                 this.isMobile = window.innerWidth < 900;
                 window.addEventListener('resize', () => { this.isMobile = window.innerWidth < 900; });
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

            <div x-show="!isMobile" class="overflow-hidden rounded-xl bg-white dark:bg-slate-800/80 shadow-xl border border-slate-200 dark:border-slate-700">
                <!-- Loading overlay for matrix -->
                <div wire:loading wire:target="previousMonth,nextMonth" class="absolute inset-0 bg-white/80 dark:bg-slate-800/80 z-50 flex items-center justify-center">
                    <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400">
                        <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span>Memuat data...</span>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white dark:bg-slate-800/80">
                        <thead class="bg-gray-50 dark:bg-slate-900/50">
                            <tr class="border-b border-slate-200 dark:border-slate-700">
                                <th class="sticky left-0 z-20 bg-gray-50 dark:bg-slate-900/50 px-6 py-4 text-left border-r-2 border-slate-200 dark:border-slate-700 min-w-[220px]">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white">Indikator Mutu</span>
                                </th>

                                @foreach($daysInMonth as $day)
                                @php
                                $date = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->day($day);
                                $dayName = $date->locale('id')->dayName;
                                $isWeekend = in_array($date->dayOfWeek, [0, 6]);
                                $isToday = $date->isToday();
                                @endphp
                                @if($this->shouldShowCell($day, $filterPeriod))
                                <th
                                    class="relative text-center border-r border-slate-200 dark:border-slate-700 w-12 px-2 py-3 {{ $isWeekend ? 'bg-red-50 dark:bg-red-900/20' : 'bg-gray-50 dark:bg-slate-900/50' }}"
                                    :class="filterPeriod === 'today' ? 'min-w-[760px] px-6 py-5' : ''">
                                    <div class="space-y-1">
                                        @if($isToday)
                                        <div class="flex flex-col items-center gap-1">
                                            <span class="px-2 py-0.5 text-[11px] font-semibold rounded bg-primary-600 text-white">Hari Ini</span>
                                            <div class="flex items-center gap-2">
                                                <span :class="filterPeriod === 'today' ? 'text-sm' : 'text-xs text-gray-500'" class="text-gray-600 dark:text-gray-300">{{ $dayName }} · {{ $date->translatedFormat('d M') }}</span>
                                            </div>
                                        </div>
                                        @else
                                        <div :class="filterPeriod === 'today' ? 'text-sm font-bold' : 'text-xs font-medium'">{{ $day }}</div>
                                        <div :class="filterPeriod === 'today' ? 'text-sm' : 'text-xs text-gray-500'">{{ substr($dayName,0,3) }}</div>
                                        @endif
                                    </div>
                                </th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($indicators as $indicator)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors duration-75">
                                <!-- Nama indikator -->
                                <td class="sticky left-0 z-10 bg-white dark:bg-slate-800/80 hover:bg-gray-50 dark:hover:bg-gray-900/40 px-6 py-4 border-r-2 border-slate-200 dark:border-slate-700 transition-colors duration-75 min-w-[220px]">
                                    <div class="flex items-start gap-3">
                                        <div class="flex flex-col space-y-1 flex-1 min-w-0">
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white leading-snug">
                                                {{ $indicator['title'] }}
                                            </span>
                                            @if($indicator['category'])
                                            <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                                @svg("heroicon-m-tag", "w-3 h-3")
                                                {{ $indicator['category'] }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                <!-- Cell hari -->
                                @foreach($daysInMonth as $day)
                                @if($this->shouldShowCell($day, $filterPeriod))
                                @php
                                $cellData = $matrixData[$indicator['id']][$day] ?? null;
                                $state = $cellData['cell_state'] ?? 'disabled';
                                $summary = $cellData['summary'] ?? null;
                                $dateStr = $cellData['date'] ?? '';
                                $isToday = $cellData['is_today'] ?? false;
                                $indicatorId = $indicator['id'];
                                @endphp

                                @include('filament.resources.daily-report-entry-resource.pages.partials.matrix-cell', [
                                'state' => $state,
                                'summary' => $summary,
                                'cellData' => $cellData,
                                'dateStr' => $dateStr,
                                'isToday' => $isToday,
                                'indicatorId' => $indicatorId,
                                'day' => $day,
                                ])
                                @endif
                                @endforeach
                            </tr>

                            @empty
                            <tr>
                                <td colspan="{{ count($daysInMonth) + 1 }}" class="px-4 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center space-y-4">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-primary-100 dark:bg-primary-900/20 rounded-full blur-2xl opacity-50"></div>
                                            <div class="relative w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-2xl flex items-center justify-center shadow-lg">
                                                @svg("heroicon-o-clipboard-document-list", "w-10 h-10 text-gray-400 dark:text-gray-500")
                                            </div>
                                        </div>
                                        <div class="space-y-2">
                                            <p class="text-base font-bold text-gray-700 dark:text-gray-300">Belum Ada Indikator</p>
                                            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">
                                                Belum ada indikator mutu yang dikonfigurasi untuk unit kerja Anda.
                                            </p>
                                            <p class="text-xs text-gray-400 dark:text-gray-500">
                                                Silakan hubungi administrator sistem
                                            </p>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
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
                <template x-for="indicator in indicators" :key="indicator.id">
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors duration-75">
                        <!-- Nama indikator -->
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

                        <!-- Cell hari -->
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
                                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">
                                        Belum ada indikator mutu yang dikonfigurasi untuk unit kerja Anda.
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        Silakan hubungi administrator sistem
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile view: stacked cards per indicator -->
    <div x-show="isMobile" class="space-y-5">
        <template x-for="indicator in indicators" :key="indicator.id">
            <div class="bg-white dark:bg-slate-800/80 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-4">
                <!-- Indicator Header -->
                <div class="mb-3">
                    <div class="text-sm font-semibold text-gray-900 dark:text-white leading-tight" x-text="indicator.title"></div>
                    <template x-if="indicator.category">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5" x-text="indicator.category"></div>
                    </template>
                </div>

                <!-- Horizontal Date Cards -->
                <div class="flex gap-3 overflow-x-auto pb-3 -mx-1 px-1 scroll-smooth snap-x">
                    <template x-for="day in daysInMonth" :key="day">
                        <div x-show="shouldShowCell(day)" x-html="renderMobileCard(indicator, day)"></div>
                    </template>
                </div>
            </div>
        </template>
    </div>

    <!-- Matrix Table (desktop) -->
    <!-- Filters -->
    <div style="margin-bottom: -20px;">
        <div class="flex flex-row justify-end gap-3">
            <!-- Segmented Control -->
            <div class="bg-white dark:bg-slate-700/80 rounded-md shadow-sm inline-flex rounded-lg bg-gray-100 dark:bg-gray-700 p-1">
                <button
                    @click="filterPeriod = 'today'"
                    :class="filterPeriod === 'today'
                ? 'bg-white dark:bg-slate-600/80 text-gray-900 dark:text-white shadow'
                : 'text-gray-500 dark:text-gray-300'"
                    class="px-4 py-2 text-sm rounded-md transition">
                    Hari Ini
                </button>

                <button
                    @click="filterPeriod = 'weekly'"
                    :class="filterPeriod === 'weekly'
                ? 'bg-white dark:bg-slate-600/80 text-gray-900 dark:text-white shadow'
                : 'text-gray-500 dark:text-gray-300'"
                    class="px-4 py-2 text-sm rounded-md transition">
                    Minggu Ini
                </button>

                <button
                    @click="filterPeriod = 'monthly'"
                    :class="filterPeriod === 'monthly'
                ? 'bg-white dark:bg-slate-600/80 text-gray-900 dark:text-white shadow'
                : 'text-gray-500 dark:text-gray-300'"
                    class="px-4 py-2 text-sm rounded-md transition">
                    Bulan Ini
                </button>
            </div>
        </div>
    </div>
    <div x-show="!isMobile" class="bg-white dark:bg-slate-700/80 rounded-xl shadow-md border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="bg-gray-50 dark:bg-slate-900/50">
                    <tr class="border-b border-slate-200 dark:border-slate-700">
                        <th class="sticky left-0 z-20 bg-gray-50 dark:bg-slate-900/50 px-6 py-4 text-left border-r-2 border-slate-200 dark:border-slate-700 min-w-[220px]">
                            <span class="text-sm font-semibold text-gray-900 dark:text-white">Indikator Mutu</span>
                        </th>

                        @foreach($daysInMonth as $day)
                        @php
                        $date = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->day($day);
                        $dayName = $date->locale('id')->dayName;
                        $isWeekend = in_array($date->dayOfWeek, [0, 6]);
                        $isToday = $date->isToday();
                        @endphp
                        <th x-show="shouldShowCell({{ $day }})"
                            class="relative text-center border-r border-slate-200 dark:border-slate-700 w-12 px-2 py-3 {{ $isWeekend ? 'bg-red-50 dark:bg-red-900/20' : 'bg-gray-50 dark:bg-slate-900/50' }}"
                            :class="filterPeriod === 'today' ? 'min-w-[760px] px-6 py-5' : ''">
                            <div class="space-y-1">
                                @if($isToday)
                                <div class="flex flex-col items-center gap-1">
                                    <span class="px-2 py-0.5 text-[11px] font-semibold rounded bg-primary-600 text-white">Hari Ini</span>
                                    <div class="flex items-center gap-2">
                                        <span :class="filterPeriod === 'today' ? 'text-sm' : 'text-xs text-gray-500'" class="text-gray-600 dark:text-gray-300">{{ $dayName }} · {{ $date->translatedFormat('d M') }}</span>
                                    </div>
                                </div>
                                @else
                                <div :class="filterPeriod === 'today' ? 'text-sm font-bold' : 'text-xs font-medium'">{{ $day }}</div>
                                <div :class="filterPeriod === 'today' ? 'text-sm' : 'text-xs text-gray-500'">{{ substr($dayName,0,3) }}</div>
                                @endif
                            </div> @endif
                        </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($indicators as $indicator)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors duration-75">
                        <!-- Nama indikator -->
                        <td class="sticky left-0 z-10 bg-white dark:bg-slate-800/80 hover:bg-gray-50 dark:hover:bg-gray-900/40 px-6 py-4 border-r-2 border-slate-200 dark:border-slate-700 transition-colors duration-75 min-w-[220px]">
                            <div class="flex items-start gap-3">
                                <div class="flex flex-col space-y-1 flex-1 min-w-0">
                                    <span class="text-sm font-semibold text-gray-900 dark:text-white leading-snug">
                                        {{ $indicator['title'] }}
                                    </span>
                                    @if($indicator['category'])
                                    <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                        @svg("heroicon-m-tag", "w-3 h-3")
                                        {{ $indicator['category'] }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </td>

                        <!-- Cell hari -->
                        @foreach($daysInMonth as $day)
                        @php
                        $cellData = $matrixData[$indicator['id']][$day] ?? null;
                        $state = $cellData['cell_state'] ?? 'disabled';
                        $summary = $cellData['summary'] ?? null;
                        $dateStr = $cellData['date'] ?? '';
                        $isToday = $cellData['is_today'] ?? false;
                        $indicatorId = $indicator['id'];
                        @endphp

                        @include('filament.resources.daily-report-entry-resource.pages.partials.matrix-cell', [
                        'state' => $state,
                        'summary' => $summary,
                        'cellData' => $cellData,
                        'dateStr' => $dateStr,
                        'isToday' => $isToday,
                        'indicatorId' => $indicatorId,
                        'day' => $day,
                        ])
                        @endforeach
                    </tr>

                    @empty
                    <tr>
                        <td colspan="{{ count($daysInMonth) + 1 }}" class="px-4 py-16 text-center">
                            <div class="flex flex-col items-center justify-center space-y-4">
                                <div class="relative">
                                    <div class="absolute inset-0 bg-primary-100 dark:bg-primary-900/20 rounded-full blur-2xl opacity-50"></div>
                                    <div class="relative w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-2xl flex items-center justify-center shadow-lg">
                                        @svg("heroicon-o-clipboard-document-list", "w-10 h-10 text-gray-400 dark:text-gray-500")
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <p class="text-base font-bold text-gray-700 dark:text-gray-300">Belum Ada Indikator</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">
                                        Belum ada indikator mutu yang dikonfigurasi untuk unit kerja Anda.
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-gray-500">
                                        Silakan hubungi administrator sistem
                                    </p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile view: stacked cards per indicator -->
    <div x-show="isMobile" class="space-y-5">

        @foreach($indicators as $indicator)
        <div
            class="bg-white dark:bg-slate-800/80 rounded-xl shadow-sm border border-slate-100 dark:border-slate-700 p-4">
            <!-- Indicator Header -->
            <div class="mb-3">
                <div class="text-sm font-semibold text-gray-900 dark:text-white leading-tight">
                    {{ $indicator['title'] }}
                </div>
                @if($indicator['category'])
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                    {{ $indicator['category'] }}
                </div>
                @endif
            </div>

            <!-- Horizontal Date Cards -->
            <div
                class="flex gap-3 overflow-x-auto pb-3 -mx-1 px-1 scroll-smooth snap-x">
                @foreach($daysInMonth as $day)
                @if($this->shouldShowCell($day, $filterPeriod))
                @php
                $cellData = $matrixData[$indicator['id']][$day] ?? null;
                $state = $cellData['cell_state'] ?? 'disabled';
                $summary = $cellData['summary'] ?? null;
                $dateStr = $cellData['date'] ?? '';
                $isToday = $cellData['is_today'] ?? false;
                $d = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->day($day);
                @endphp

                <div
                    class="
                    min-w-[150px]
                    rounded-xl
                    p-3
                    flex-shrink-0
                    snap-start
                    border
                    transition
                    {{ $isToday
                        ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/20'
                        : 'bg-gray-50 border-slate-200 dark:bg-gray-900/40 dark:border-slate-700'
                    }}
                ">
                    <!-- Date & Status -->
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            {{ $day }}
                            <span class="text-[11px] text-gray-400 ml-0.5">
                                {{ $d->isoFormat('ddd') }}
                            </span>
                        </div>

                        @if($cellData && $cellData['has_data'])
                        <span
                            class="text-[10px] px-2 py-0.5 rounded-full bg-green-100 text-green-700">
                            {{ $cellData['count'] }}x
                        </span>
                        @else
                        <span
                            class="text-[10px] px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">
                            Kosong
                        </span>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="mb-3">
                        @if($state === 'done')
                        <div class="text-base font-bold text-green-700">
                            {{ $summary['percentage'] ?? '0' }}%
                        </div>
                        <div class="text-[11px] text-gray-500">
                            {{ $summary['numerator'] ?? 0 }}
                            /
                            {{ $summary['denominator'] ?? 0 }}
                        </div>
                        @elseif($state === 'pending')
                        <div class="text-sm font-semibold text-orange-700">
                            Belum diisi
                        </div>
                        @elseif($state === 'overdue')
                        <div class="text-sm font-semibold text-red-600">
                            Terkunci
                        </div>
                        @else
                        <div class="text-sm text-gray-400">—</div>
                        @endif
                    </div>

                    <!-- Action Button -->
                    <button
                        wire:click="openSlideOver({{ $indicator['id'] }}, '{{ $dateStr }}')"
                        class="
                        w-full
                        text-xs
                        py-2.5
                        rounded-lg
                        bg-primary-600
                        text-white
                        font-medium
                        transition
                        active:scale-95
                        disabled:opacity-60
                    ">
                        Input / Lihat
                    </button>
                </div>
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