<!-- Action Buttons -->
<!-- Manage Data Button (When data exists) -->
<template x-if="getActionButton(indicator.id, selectedDate).state === 'done'">
    <button
        @click="$wire.openSlideOver(indicator.id, selectedDate || '{{ now()->format('Y-m-d') }}')"
        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
        @svg("heroicon-m-cog-6-tooth", "w-4 h-4")
        Kelola Data
    </button>
</template>

<!-- Add Data Button (When no data) -->
<template x-if="getActionButton(indicator.id, selectedDate).state === 'pending'">
    <button
        @click="console.log('Opening slide-over for indicator:', indicator.id, 'date:', selectedDate || '{{ now()->format('Y-m-d') }}'); $wire.openSlideOver(indicator.id, selectedDate || '{{ now()->format('Y-m-d') }}')"
        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
        @svg("heroicon-m-plus", "w-4 h-4")
        Tambah Data
    </button>
</template>

<!-- Locked Button (Overdue State) -->
<template x-if="getActionButton(indicator.id, selectedDate).state === 'overdue'">
    <button
        disabled
        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-300 text-gray-500 text-sm font-medium rounded-lg cursor-not-allowed">
        @svg("heroicon-m-lock-closed", "w-4 h-4")
        Terkunci
    </button>
</template>

<!-- Default Button -->
<template x-if="!getActionButton(indicator.id, selectedDate).state || getActionButton(indicator.id, selectedDate).state === 'disabled'">
    <button
        @click="$wire.openSlideOver(indicator.id, selectedDate || '{{ now()->format('Y-m-d') }}')"
        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
        @svg("heroicon-m-eye", "w-4 h-4")
        Lihat
    </button>
</template>