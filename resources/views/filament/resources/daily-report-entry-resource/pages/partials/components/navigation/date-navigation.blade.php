<!-- Date Navigation Sidebar -->
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4" x-data="{ isLoading: false }">

    <!-- Loading Overlay - Full Screen -->
    <div x-show="isLoading" x-transition.opacity.duration.300ms class="fixed inset-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-sm z-[9999] flex items-center justify-center" style="display: none;">
        <div class="flex flex-col items-center gap-3 bg-white dark:bg-slate-800 p-6 rounded-2xl shadow-2xl border border-gray-200 dark:border-gray-700">
            <svg class="animate-spin h-10 w-10 text-primary-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-semibold text-gray-900 dark:text-white">Memuat data...</span>
        </div>
    </div>

    @include('filament.resources.daily-report-entry-resource.pages.partials.components.navigation.month-navigation')

    <!-- Date Legend -->
    <div class="mb-4 p-3 mt-4 bg-slate-50 dark:bg-slate-900/50 rounded-lg">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
            @svg("heroicon-m-calendar", "w-4 h-4")
            <span>{{ \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->locale('id')->translatedFormat('F Y') }}</span>
        </h3>
        <div class="space-y-1.5 text-xs">
            <div class="flex items-center gap-2">
                <span class="flex-shrink-0 w-3 h-3 rounded-full bg-green-500 shadow-sm"></span>
                <span class="font-medium text-green-700 dark:text-green-400">Sudah diisi</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="flex-shrink-0 w-3 h-3 rounded-full border-2 border-orange-400"></span>
                <span class="font-medium text-orange-600 dark:text-orange-400">Belum diisi</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="flex-shrink-0 w-3 h-3 rounded border border-red-400 bg-red-50 dark:bg-red-900/20"></span>
                <span class="font-medium text-red-600 dark:text-red-400">Terkunci</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="flex-shrink-0 w-3 h-3 rounded border border-gray-400 opacity-50"></span>
                <span class="font-medium text-gray-500 dark:text-gray-400">Masa depan</span>
            </div>
            <div class="flex items-center gap-2 mt-1 pt-1.5 border-t border-slate-200 dark:border-slate-700">
                <span class="flex-shrink-0 w-3 h-3 rounded-full bg-primary-500 ring-2 ring-primary-300 dark:ring-primary-700"></span>
                <span class="font-medium text-primary-600 dark:text-primary-400">Hari ini</span>
            </div>
        </div>
    </div>

    <!-- Date List -->
    <div class="
    flex
    flex-row lg:flex-col
    flex-nowrap

    overflow-x-auto lg:overflow-x-hidden
    overflow-y-hidden lg:overflow-y-auto

    space-x-2 lg:space-x-0
    lg:space-y-1

    max-h-none lg:max-h-[500px]
    ">
        @foreach($daysInMonth as $day)
        @php
        $date = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->day($day);
        $dateString = $date->format('Y-m-d');
        $dayName = $date->locale('id')->dayName;
        $isToday = $date->isToday();
        $isWeekend = in_array($date->dayOfWeek, [0, 6]);
        $isSelected = $selectedDate === $dateString;

        // Check if any indicator has data for this date
        $hasAnyData = false;
        foreach($indicators as $indicator) {
        $cellData = $matrixData[$indicator['id']][$day] ?? null;
        if ($cellData && ($cellData['has_data'] ?? false)) {
        $hasAnyData = true;
        break;
        }
        }

        // Locked = past date beyond the allowed input window
        $backDays = \App\Models\LaporanImutAutoGenerationSetting::getInstance()->getBackDataEntryDays();
        $isLocked = !$date->isFuture() && !$hasAnyData && $date->lt(now()->startOfDay()->subDays($backDays));
        @endphp

        <button
            @click="isLoading = true; setTimeout(() => isLoading = false, 1500)"
            wire:click="selectDate('{{ $dateString }}')"
            class="min-w-[150px] sm:min-w-[150px] lg:min-w-0 lg:w-full flex items-center gap-3 p-3 rounded-lg border transition-all duration-200 text-left group hover:scale-[1.02] transform
                   {{ $isSelected 
                       ? 'bg-primary-100 dark:bg-primary-600/30 border-primary-200 dark:border-primary-800 text-primary-900 dark:text-primary-100 shadow-md shadow-primary-100 dark:shadow-primary-900/30' 
                       : 'hover:bg-slate-50 dark:hover:bg-slate-600 border-transparent hover:shadow-sm' }}
                   {{ $isWeekend ? 'bg-red-50/50 dark:bg-red-900/10' : '' }}
                   {{ $date->isFuture() ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }}"
            @if($date->isFuture()) disabled @endif>

            <!-- Status Indicator -->
            <div class="flex-shrink-0 transition-all duration-200 group-hover:scale-110">
                @if($date->isFuture())
                <div class="w-3 h-3 rounded border border-gray-400 opacity-50"></div>
                @elseif($hasAnyData && $isToday)
                <div class="w-3 h-3 rounded-full bg-green-500 shadow-sm ring-2 ring-primary-300 dark:ring-primary-700"></div>
                @elseif($hasAnyData)
                <div class="w-3 h-3 rounded-full bg-green-500 shadow-sm group-hover:shadow-green-200 ring-0 group-hover:ring-2 ring-green-200"></div>
                @elseif($isToday)
                <div class="w-3 h-3 rounded-full bg-primary-500 ring-2 ring-primary-300 dark:ring-primary-700"></div>
                @elseif($isLocked)
                <div class="w-3 h-3 rounded border border-red-400 bg-red-50 dark:bg-red-900/20"></div>
                @else
                <div class="w-3 h-3 rounded-full border-2 border-orange-400 group-hover:shadow-orange-200 ring-0 group-hover:ring-2 ring-orange-100"></div>
                @endif
            </div>

            <!-- Date Info -->
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    @if($isToday)
                    <span class="text-sm font-bold flex items-center gap-1 group-hover:scale-105 transition-transform">
                        <span class="text-primary-600 animate-pulse">▶</span>
                        {{ $day }} {{ $date->format('M') }}
                    </span>
                    @else
                    <span class="text-sm transition-all duration-200 {{ $date->isFuture() ? 'text-gray-400' : 'text-gray-700 dark:text-gray-300 group-hover:text-gray-900 dark:group-hover:text-white' }} group-hover:font-medium">
                        {{ $day }} {{ $date->format('M') }}
                    </span>
                    @endif
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 truncate transition-colors duration-200 group-hover:text-gray-600 dark:group-hover:text-gray-300">
                    {{ $dayName }}
                </div>

                <div>
                    <!-- Status badges -->
                    @if($isToday)
                    <span class="text-xs px-1.5 py-0.5 bg-gradient-to-r from-primary-600 to-primary-700 text-white rounded-full font-medium shadow-sm animate-pulse">Today</span>
                    @endif
                    @if($isSelected && !$isToday)
                    <span class="text-xs px-1.5 py-0.5 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white rounded-full font-medium shadow-sm">Selected</span>
                    @endif
                </div>
            </div>

            <!-- Hover indicator -->
            <div class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                <svg class="w-3 h-3 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </div>
        </button>
        @endforeach
    </div>
</div>