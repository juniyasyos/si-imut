<td x-show="shouldShowCell({{ $day }})"
    class="px-2 py-2 border border-gray-200 dark:border-gray-700"
    :class="filterPeriod === 'today' ? 'min-w-[400px] h-24 p-4' : 'w-16 h-16'"
    style="{{ $isToday ? 'background-color: rgba(37, 99, 235, 0.05);' : '' }}">
    {{-- State: DONE (Sudah diisi) --}}
    @if($state === 'done')
    <div x-data="{ showPopover: false }" class="relative">
        <button
            wire:click="openSlideOver({{ $indicatorId }}, '{{ $dateStr }}')"
            @mouseenter="showPopover = true"
            @mouseleave="showPopover = false"
            class="w-full group">
            <div class="flex flex-col items-center justify-center px-2 py-3 rounded-lg transition-all duration-200 {{ $isToday ? 'ring-2 ring-primary-300 dark:ring-primary-700' : '' }}"
                style="background-color: #f0fdf4;">
                <div class="flex items-center justify-center w-10 h-10 bg-green-50 dark:bg-green-900/20 rounded-lg mb-1">
                    @svg("heroicon-m-check-circle", "w-6 h-6 text-green-600 dark:text-green-400")
                </div>
                @if($summary)
                <span class="text-xs font-semibold text-gray-900 dark:text-white">
                    {{ $summary['percentage'] }}%
                </span>
                <span class="text-[10px] text-gray-500 dark:text-gray-400">
                    {{ $summary['numerator'] }}/{{ $summary['denominator'] }}
                </span>
                @endif
                @if($cellData && $cellData['count'] > 1)
                <span class="mt-1 px-2 py-0.5 text-[9px] font-bold rounded-full bg-green-100 text-green-700">
                    {{ $cellData['count'] }}x
                </span>
                @endif
            </div>
        </button>

        {{-- Popover detail --}}
        @if($summary)
        <div x-show="showPopover"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 z-[60] pointer-events-none"
            style="display: none;">
            <div class="text-xs rounded-lg shadow-xl p-3 min-w-[180px] bg-gray-800 dark:bg-gray-900 text-white">
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-300">Numerator:</span>
                        <span class="font-bold text-white">{{ $summary['numerator'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-300">Denominator:</span>
                        <span class="font-bold text-white">{{ $summary['denominator'] }}</span>
                    </div>
                    <div class="pt-2 flex justify-between items-center border-t border-gray-600">
                        <span class="text-gray-300">Persentase:</span>
                        <span class="font-bold text-green-400">{{ $summary['percentage'] }}%</span>
                    </div>
                    <div class="text-center pt-1 border-t border-gray-600">
                        <span class="text-[10px] text-gray-400">{{ $summary['count'] }} laporan harian</span>
                    </div>
                </div>
                <!-- Arrow -->
                <div class="absolute top-full left-1/2 transform -translate-x-1/2">
                    <div class="border-l-6 border-r-6 border-t-6 border-transparent border-t-gray-800"></div>
                </div>
            </div>
        </div>
        @endif
    </div>

    {{-- State: PENDING (Belum diisi - dapat input) --}}
    @elseif($state === 'pending')
    <button
        wire:click="openSlideOver({{ $indicatorId }}, '{{ $dateStr }}')"
        class="w-full group">
        <div class="flex flex-col items-center justify-center px-2 py-3 rounded-lg transition-all duration-200 {{ $isToday ? 'ring-2 ring-primary-300 dark:ring-primary-700' : '' }}"
            style="background-color: #fff7ed;">
            <div class="flex flex-col items-center justify-center w-12 h-12 bg-orange-500 rounded-lg mb-1">
                @svg("heroicon-o-plus-circle", "w-5 h-5 text-white mb-0.5")
                <span class="text-[9px] font-bold text-white">0%</span>
            </div>
            <span class="text-xs font-semibold text-gray-900 dark:text-white">Belum Diisi</span>
            <span class="text-[10px] text-gray-500 dark:text-gray-400">0/0</span>
        </div>
    </button>

    {{-- State: OVERDUE (Terlambat >7 hari) --}}
    @elseif($state === 'overdue')
    <div class="flex flex-col items-center justify-center px-2 py-3 rounded-lg cursor-not-allowed {{ $isToday ? 'ring-2 ring-primary-300 dark:ring-primary-700' : '' }}"
        style="background-color: #fef2f2;">
        <div class="flex items-center justify-center w-10 h-10 bg-red-50 dark:bg-red-900/20 rounded-lg mb-1">
            @svg("heroicon-o-lock-closed", "w-6 h-6 text-red-500 dark:text-red-400")
        </div>
        <span class="text-xs font-semibold text-gray-900 dark:text-white">Terkunci</span>
        <span class="text-[10px] text-gray-500 dark:text-gray-400">Lewat 7 hari</span>
    </div>

    {{-- State: DISABLED (Belum tiba) --}}
    @else
    <div class="flex flex-col items-center justify-center px-2 py-3 rounded-lg {{ $isToday ? 'ring-2 ring-primary-300 dark:ring-primary-700' : '' }}"
        style="background-color: #f9fafb;">
        <div class="flex items-center justify-center w-10 h-10 bg-gray-50 dark:bg-gray-700/50 rounded-lg mb-1">
            <span class="text-gray-300 dark:text-gray-600 text-lg font-light">-</span>
        </div>
        <span class="text-xs font-semibold text-gray-900 dark:text-white">Belum Tiba</span>
        <span class="text-[10px] text-gray-500 dark:text-gray-400">Masa depan</span>
    </div>
    @endif
</td>