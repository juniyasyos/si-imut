<x-filament-panels::page>
    <div class="space-y-6 relative" x-data="{
        filterPeriod: 'today',
        selectedMonth: '{{ $selectedMonth }}',
        currentDate: new Date('{{ $selectedMonth }}-01'),
        isMobile: false,
        initResize() {
            this.isMobile = window.innerWidth < 900;
            window.addEventListener('resize', () => { this.isMobile = window.innerWidth < 900; });
        },
        isToday(day) {
            const today = new Date();
            const cellDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), day);
            return cellDate.toDateString() === today.toDateString();
        },
        // rolling 7-day window including today
        isInWeek(day) {
            const today = new Date();
            const start = new Date(today);
            start.setDate(today.getDate() - 6); // include today + 6 previous days = 7
            const cellDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), day);
            // normalize times
            start.setHours(0,0,0,0);
            today.setHours(23,59,59,999);
            return cellDate >= start && cellDate <= today;
        },
        isInMonth(day) {
            const today = new Date();
            const cellDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), day);
            return cellDate.getMonth() === today.getMonth() && cellDate.getFullYear() === today.getFullYear();
        },
        shouldShowCell(day) {
            if (this.filterPeriod === 'today') return this.isToday(day);
            if (this.filterPeriod === 'weekly') return this.isInWeek(day);
            if (this.filterPeriod === 'monthly') return this.isInMonth(day);
            return true;
        }
    }" x-init="initResize()">
        @include('filament.resources.daily-report-entry-resource.pages.partials.month-navigation')

        <!-- Matrix Table (desktop) -->
        <!-- Filters -->
        <div style="margin-bottom: -20px;">
            <div class="flex flex-row justify-end gap-3">
                <!-- Segmented Control -->
                <div class="bg-white dark:bg-gray-800 rounded-md shadow-sm inline-flex rounded-lg bg-gray-100 dark:bg-gray-700 p-1">
                    <button
                        @click="filterPeriod = 'today'"
                        :class="filterPeriod === 'today'
                ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow'
                : 'text-gray-500 dark:text-gray-300'"
                        class="px-4 py-2 text-sm rounded-md transition">
                        Hari Ini
                    </button>

                    <button
                        @click="filterPeriod = 'weekly'"
                        :class="filterPeriod === 'weekly'
                ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow'
                : 'text-gray-500 dark:text-gray-300'"
                        class="px-4 py-2 text-sm rounded-md transition">
                        Minggu Ini
                    </button>

                    <button
                        @click="filterPeriod = 'monthly'"
                        :class="filterPeriod === 'monthly'
                ? 'bg-white dark:bg-gray-800 text-gray-900 dark:text-white shadow'
                : 'text-gray-500 dark:text-gray-300'"
                        class="px-4 py-2 text-sm rounded-md transition">
                        Bulan Ini
                    </button>
                </div>
            </div>
        </div>
        <div x-show="!isMobile" class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr class="border-b border-gray-200 dark:border-gray-700">
                            <th class="sticky left-0 z-20 bg-gray-50 dark:bg-gray-900/50 px-6 py-4 text-left border-r-2 border-gray-200 dark:border-gray-700 min-w-[220px]">
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
                                class="relative text-center border-r border-gray-200 dark:border-gray-700 w-12 px-2 py-3 {{ $isWeekend ? 'bg-red-50 dark:bg-red-900/20' : 'bg-gray-50 dark:bg-gray-900/50' }}"
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
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors duration-150">
                            <!-- Nama indikator -->
                            <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/40 px-6 py-4 border-r-2 border-gray-200 dark:border-gray-700 transition-colors duration-150 min-w-[220px]">
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
                            $state = $this->getCellState($indicator['id'], $day);
                            $summary = $this->getCellSummary($indicator['id'], $day);
                            $cellData = $matrixData[$indicator['id']][$day] ?? null;
                            $dateStr = $cellData['date'] ?? '';
                            $isToday = $this->isToday($day);
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
                class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-4">
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
                    @php
                    $state = $this->getCellState($indicator['id'], $day);
                    $cellData = $matrixData[$indicator['id']][$day] ?? null;
                    $dateStr = $cellData['date'] ?? '';
                    $isToday = $this->isToday($day);
                    $d = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->day($day);
                    @endphp

                    <div
                        x-show="shouldShowCell({{ $day }})"
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
                        : 'bg-gray-50 border-gray-200 dark:bg-gray-900/40 dark:border-gray-700'
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
                                {{ $this->getCellSummary($indicator['id'], $day)['percentage'] ?? '0' }}%
                            </div>
                            <div class="text-[11px] text-gray-500">
                                {{ $this->getCellSummary($indicator['id'], $day)['numerator'] ?? 0 }}
                                /
                                {{ $this->getCellSummary($indicator['id'], $day)['denominator'] ?? 0 }}
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
                    @endforeach
                </div>
            </div>
            @endforeach
        </div>

        @include('filament.resources.daily-report-entry-resource.pages.partials.legend')
    </div>

    {{-- Slide-over rendered outside the page wrapper to prevent overflow clipping --}}
    @include('filament.resources.daily-report-entry-resource.pages.partials.slide-over')
</x-filament-panels::page>