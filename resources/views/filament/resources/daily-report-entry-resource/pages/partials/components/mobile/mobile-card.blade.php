@php
$state = $cellData['cell_state'] ?? 'disabled';
$summary = $cellData['summary'] ?? null;
$dateStr = $cellData['date'] ?? '';
$isToday = $cellData['is_today'] ?? false;
$d = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth ?: now()->format('Y-m'))->day($day);
@endphp

<div class="min-w-[150px] rounded-xl p-3 flex-shrink-0 snap-start border transition {{ $isToday
    ? 'bg-blue-50 border-blue-200 dark:bg-blue-900/20'
    : 'bg-gray-50 border-slate-200 dark:bg-gray-900/40 dark:border-slate-700'
}}">
    <!-- Date & Status -->
    <div class="flex items-center justify-between mb-2">
        <div class="text-xs font-medium text-gray-700 dark:text-gray-300">
            {{ $day }}
            <span class="text-[11px] text-gray-400 ml-0.5">{{ $d->isoFormat('ddd') }}</span>
        </div>

        @if($cellData && $cellData['has_data'])
        <span class="text-[10px] px-2 py-0.5 rounded-full bg-green-100 text-green-700">
            {{ $cellData['count'] }}x
        </span>
        @else
        <span class="text-[10px] px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">
            Kosong
        </span>
        @endif
    </div>

    <!-- Content -->
    <div class="mb-3">
        @if($state === 'done')
        <div class="text-base font-bold text-green-700">{{ $summary['percentage'] ?? '0' }}%</div>
        <div class="text-[11px] text-gray-500">{{ $summary['numerator'] ?? 0 }} / {{ $summary['denominator'] ?? 0 }}</div>
        @elseif($state === 'pending')
        <div class="text-sm font-semibold text-orange-700">Belum diisi</div>
        @elseif($state === 'overdue')
        <div class="text-sm font-semibold text-red-600">Terkunci</div>
        @else
        <div class="text-sm text-gray-400">—</div>
        @endif
    </div>

    <!-- Action Button -->
    <button
        wire:click="openSlideOver({{ $indicator['id'] }}, '{{ $dateStr }}')"
        class="w-full text-xs py-2.5 rounded-lg bg-primary-600 text-white font-medium transition active:scale-95 disabled:opacity-60">
        Input / Lihat
    </button>
</div>