@php
    $actionLabel = $actionLabel ?? 'Kelola Data';
@endphp

<button
    @click="console.log('🔘 [Action] Kelola Data clicked, indicator:', indicator.id, 'date:', selectedDate); openSlideOverFast(indicator.id, selectedDate || '{{ now()->format('Y-m-d') }}')"
    class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
    @svg("heroicon-m-cog-6-tooth", "w-4 h-4")
    {{ $actionLabel }}
</button>