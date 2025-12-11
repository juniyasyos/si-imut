<td class="px-3 py-3" style="border-color: #f3f4f6; {{ $isToday ? 'background-color: rgba(37, 99, 235, 0.05);' : '' }}">
    {{-- State: DONE (Sudah diisi) --}}
    @if($state === 'done')
    <div x-data="{ showPopover: false }" class="relative">
        <button
            wire:click="openSlideOver({{ $indicatorId }}, '{{ $dateStr }}')"
            @mouseenter="showPopover = true"
            @mouseleave="showPopover = false"
            class="w-full group">
            <div class="flex flex-col items-center justify-center px-4 py-3 rounded-lg transition-all duration-200"
                style="{{ $isToday 
                    ? 'background-color: #dcfce7; box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.5);' 
                    : 'background-color: #f0fdf4;' }}"
                onmouseover="if(!{{ $isToday ? 'true' : 'false' }}) { this.style.backgroundColor='#dcfce7'; this.style.boxShadow='0 4px 6px rgba(0,0,0,0.1)'; }"
                onmouseout="if(!{{ $isToday ? 'true' : 'false' }}) { this.style.backgroundColor='#f0fdf4'; this.style.boxShadow=''; }">
                <x-heroicon-m-check-circle class="w-7 h-7 mb-1.5" style="color: #16a34a;" />
                @if($summary)
                <span class="text-base font-bold leading-tight" style="color: #15803d;">
                    {{ $summary['percentage'] }}%
                </span>
                <span class="text-xs mt-0.5" style="color: #16a34a; opacity: 0.8;">
                    {{ $summary['numerator'] }}/{{ $summary['denominator'] }}
                </span>
                @endif
                @if($cellData && $cellData['count'] > 1)
                <span class="mt-1 px-2 py-0.5 text-[10px] font-semibold rounded-full" style="background-color: #16a34a; color: #ffffff;">
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
            <div class="text-xs rounded-lg shadow-xl p-3 min-w-[180px]" style="background-color: #111827; color: #ffffff;">
                <div class="space-y-2">
                    <div class="flex justify-between items-center">
                        <span style="color: #d1d5db;">Numerator:</span>
                        <span class="font-bold" style="color: #ffffff;">{{ $summary['numerator'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span style="color: #d1d5db;">Denominator:</span>
                        <span class="font-bold" style="color: #ffffff;">{{ $summary['denominator'] }}</span>
                    </div>
                    <div class="pt-2 flex justify-between items-center" style="border-top: 1px solid #4b5563;">
                        <span style="color: #d1d5db;">Persentase:</span>
                        <span class="font-bold" style="color: #4ade80;">{{ $summary['percentage'] }}%</span>
                    </div>
                    <div class="text-center pt-1" style="border-top: 1px solid #4b5563;">
                        <span class="text-[10px]" style="color: #9ca3af;">{{ $summary['count'] }} laporan harian</span>
                    </div>
                </div>
                <!-- Arrow -->
                <div class="absolute top-full left-1/2 transform -translate-x-1/2">
                    <div style="border: 6px solid transparent; border-top-color: #111827;"></div>
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
        <div class="flex flex-col items-center justify-center px-4 py-3 rounded-lg transition-all duration-200"
            style="{{ $isToday 
                ? 'background-color: #ffedd5; box-shadow: 0 0 0 2px rgba(249, 115, 22, 0.5); color: #c2410c;' 
                : 'background-color: #f97316; color: #ffffff;' }}"
            onmouseover="if(!{{ $isToday ? 'true' : 'false' }}) { this.style.backgroundColor='#ea580c'; this.style.boxShadow='0 10px 15px rgba(0,0,0,0.1)'; this.style.transform='scale(1.05)'; }"
            onmouseout="if(!{{ $isToday ? 'true' : 'false' }}) { this.style.backgroundColor='#f97316'; this.style.boxShadow=''; this.style.transform=''; }">
            <x-heroicon-o-plus-circle class="w-7 h-7 mb-1.5" />
            <span class="text-base font-bold leading-tight">0%</span>
            <span class="text-xs mt-0.5" style="opacity: 0.9;">0/0</span>
        </div>
    </button>

    {{-- State: OVERDUE (Terlambat >7 hari) --}}
    @elseif($state === 'overdue')
    <div class="flex flex-col items-center justify-center px-4 py-3 rounded-lg cursor-not-allowed"
        style="{{ $isToday 
            ? 'background-color: #fef2f2; box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.5);' 
            : 'background-color: #fef2f2;' }}">
        <x-heroicon-o-lock-closed class="w-7 h-7 mb-1" style="color: #ef4444;" />
        <span class="text-xs font-medium" style="color: #dc2626;">Terkunci</span>
    </div>

    {{-- State: DISABLED (Belum tiba) --}}
    @else
    <div class="flex items-center justify-center px-4 py-3 rounded-lg"
        style="{{ $isToday 
            ? 'background-color: #f3f4f6; box-shadow: 0 0 0 2px rgba(209, 213, 219, 0.5);' 
            : 'background-color: #f9fafb;' }}">
        <span class="text-lg font-light" style="color: #d1d5db;">-</span>
    </div>
    @endif
</td>