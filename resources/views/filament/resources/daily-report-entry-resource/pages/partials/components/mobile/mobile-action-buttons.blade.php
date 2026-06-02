@php
    $actionLabel = $actionLabel ?? 'Kelola Data';
@endphp

<button
    @click="openSlideOverFast(indicator.id, selectedDate || '{{ now()->format('Y-m-d') }}')"
    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-3 rounded-lg transition">
    {{ $actionLabel }}
</button>