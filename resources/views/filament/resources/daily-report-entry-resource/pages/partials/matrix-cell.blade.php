<td class="px-3 py-3 border-gray-100 dark:border-gray-800 {{ $isToday ? 'bg-primary-50/70 dark:bg-primary-900/20' : '' }}">
    {{-- State: DONE (Sudah diisi) --}}
    @if($state === 'done')
    <div x-data="{ showPopover: false }" class="relative">
        <button
            wire:click="openSlideOver({{ $indicatorId }}, '{{ $dateStr }}')"
            @mouseenter="showPopover = true"
            @mouseleave="showPopover = false"
            class="w-full group">
            <div class="flex flex-col items-center justify-center px-4 py-3 rounded-lg transition-all duration-200
                    {{ $isToday 
                        ? 'bg-green-100 dark:bg-green-900/30 ring-2 ring-green-500/50 dark:ring-green-400/50' 
                        : 'bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 hover:shadow-md' 
                    }}">
                <x-heroicon-m-check-circle class="w-7 h-7 text-green-600 dark:text-green-400 mb-1.5" />
                @if($summary)
                <span class="text-base font-bold text-green-700 dark:text-green-300 leading-tight">
                    {{ $summary['percentage'] }}%
                </span>
                <span class="text-xs text-green-600 dark:text-green-400 opacity-80 mt-0.5">
                    {{ $summary['numerator'] }}/{{ $summary['denominator'] }}
                </span>
                @endif
                @if($cellData && $cellData['count'] > 1)
                <span class="mt-1 px-2 py-0.5 bg-green-600 dark:bg-green-500 text-white text-[10px] font-semibold rounded-full">
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
            class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 z-50 pointer-events-none"
            style="display: none;">
            <div class="bg-gray-900 dark:bg-gray-700 text-white text-xs rounded-lg shadow-xl p-3 min-w-[180px]">
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-300">Numerator:</span>
                        <span class="font-bold text-white">{{ $summary['numerator'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-300">Denominator:</span>
                        <span class="font-bold text-white">{{ $summary['denominator'] }}</span>
                    </div>
                    <div class="pt-2 border-t border-gray-600 flex justify-between items-center">
                        <span class="text-gray-300">Persentase:</span>
                        <span class="font-bold text-green-400">{{ $summary['percentage'] }}%</span>
                    </div>
                    <div class="text-center pt-1 border-t border-gray-600">
                        <span class="text-[10px] text-gray-400">{{ $summary['count'] }} laporan harian</span>
                    </div>
                </div>
                <!-- Arrow -->
                <div class="absolute top-full left-1/2 transform -translate-x-1/2">
                    <div class="border-[6px] border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
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
        <div class="flex flex-col items-center justify-center px-4 py-3 rounded-lg transition-all duration-200
                {{ $isToday 
                    ? 'bg-orange-100 dark:bg-orange-900/30 ring-2 ring-orange-500/50 dark:ring-orange-400/50 text-orange-700 dark:text-orange-300' 
                    : 'bg-orange-500 hover:bg-orange-600 dark:bg-orange-600 dark:hover:bg-orange-700 hover:shadow-lg transform hover:scale-105 text-white' 
                }}">
            <x-heroicon-o-plus-circle class="w-7 h-7 mb-1.5" />
            <span class="text-base font-bold leading-tight">0%</span>
            <span class="text-xs opacity-90 mt-0.5">0/0</span>
        </div>
    </button>

    {{-- State: OVERDUE (Terlambat >7 hari) --}}
    @elseif($state === 'overdue')
    <div class="flex flex-col items-center justify-center px-4 py-3 rounded-lg cursor-not-allowed
            {{ $isToday 
                ? 'bg-red-50 dark:bg-red-900/20 ring-2 ring-red-300/50 dark:ring-red-700/50' 
                : 'bg-red-50 dark:bg-red-900/10' 
            }}">
        <x-heroicon-o-lock-closed class="w-7 h-7 text-red-500 dark:text-red-400 mb-1" />
        <span class="text-xs text-red-600 dark:text-red-400 font-medium">Terkunci</span>
    </div>

    {{-- State: DISABLED (Belum tiba) --}}
    @else
    <div class="flex items-center justify-center px-4 py-3 rounded-lg
            {{ $isToday 
                ? 'bg-gray-100 dark:bg-gray-700/50 ring-2 ring-gray-300/50 dark:ring-gray-600/50' 
                : 'bg-gray-50 dark:bg-gray-800/50' 
            }}">
        <span class="text-gray-300 dark:text-gray-600 text-lg font-light">-</span>
    </div>
    @endif
</td>