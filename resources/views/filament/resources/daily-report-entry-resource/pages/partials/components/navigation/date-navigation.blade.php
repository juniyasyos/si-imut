<!-- Date Navigation Sidebar -->
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
    @include('filament.resources.daily-report-entry-resource.pages.partials.components.navigation.month-navigation')

    <!-- Date Legend -->
    <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
            @svg("heroicon-m-calendar", "w-4 h-4")
            <span x-text="getMonthName().toUpperCase()"></span>
        </h3>
        <div class="space-y-2 text-xs">
            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                <span class="w-3 h-3 rounded-full bg-green-500"></span>
                <span>● = Lengkap</span>
            </div>
            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                <span class="w-3 h-3 rounded-full border-2 border-orange-400"></span>
                <span>○ = Kosong</span>
            </div>
            <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                <span class="w-3 h-3 rounded border border-gray-400"></span>
                <span>▢ = Future</span>
            </div>
        </div>
    </div>

    <!-- Date List -->
    <div class="space-y-1 max-h-[400px] overflow-y-auto">
        @foreach($daysInMonth as $day)
        @php
        $date = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->day($day);
        $dateString = $date->format('Y-m-d');
        $dayName = $date->locale('id')->dayName;
        $isToday = $date->isToday();
        $isWeekend = in_array($date->dayOfWeek, [0, 6]);

        // Check if any indicator has data for this date
        $hasAnyData = false;
        foreach($indicators as $indicator) {
        $cellData = $matrixData[$indicator['id']][$day] ?? null;
        if ($cellData && ($cellData['has_data'] ?? false)) {
        $hasAnyData = true;
        break;
        }
        }
        @endphp

        <button
            @click="selectDate('{{ $dateString }}')"
            :class="selectedDate === '{{ $dateString }}' 
                ? 'bg-primary-50 dark:bg-primary-900/30 border-primary-200 dark:border-primary-800 text-primary-900 dark:text-primary-100'
                : 'hover:bg-gray-50 dark:hover:bg-gray-800 border-transparent'"
            class="w-full flex items-center gap-3 p-3 rounded-lg border transition-all text-left {{ $isWeekend ? 'bg-red-50/50 dark:bg-red-900/10' : '' }}">

            <!-- Status Indicator -->
            <div class="flex-shrink-0">
                @if($date->isFuture())
                <div class="w-3 h-3 rounded border border-gray-400"></div>
                @elseif($hasAnyData)
                <div class="w-3 h-3 rounded-full bg-green-500"></div>
                @else
                <div class="w-3 h-3 rounded-full border-2 border-orange-400"></div>
                @endif
            </div>

            <!-- Date Info -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    @if($isToday)
                    <span class="text-sm font-bold">▶ {{ $day }} {{ $date->format('M') }}</span>
                    @else
                    <span class="text-sm {{ $date->isFuture() ? 'text-gray-400' : 'text-gray-700 dark:text-gray-300' }}">
                        {{ $day }} {{ $date->format('M') }}
                    </span>
                    @endif
                    @if($isToday)
                    <span class="text-xs px-1.5 py-0.5 bg-primary-600 text-white rounded font-medium">Today</span>
                    @endif
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                    {{ $dayName }}
                </div>
            </div>
        </button>
        @endforeach
    </div>
</div>