@if($isMonitoringMode ?? false)
<!-- Monitoring Mode - Simple Lihat Button -->
<button @click="openSlideOverFast(indicator.id, selectedDate || '{{ now()->format('Y-m-d') }}')" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-3 rounded-lg transition">
    Lihat
</button>
@else
<!-- Regular Mode - Conditional Buttons -->
<template x-if="getActionButton(indicator.id, selectedDate).state === 'done'">
    <button @click="openSlideOverFast(indicator.id, selectedDate || '{{ now()->format('Y-m-d') }}')" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-3 rounded-lg transition">
        Edit Data
    </button>
</template>

<template x-if="getActionButton(indicator.id, selectedDate).state === 'done_locked'">
    <button @click="openSlideOverFast(indicator.id, selectedDate || '{{ now()->format('Y-m-d') }}')" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white text-sm font-medium py-3 rounded-lg transition">
        Lihat Data
    </button>
</template>

<template x-if="getActionButton(indicator.id, selectedDate).state === 'pending'">
    <button @click="openSlideOverFast(indicator.id, selectedDate || '{{ now()->format('Y-m-d') }}')" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium py-3 rounded-lg transition">
        Isi Data
    </button>
</template>

<template x-if="getActionButton(indicator.id, selectedDate).state === 'overdue'">
    <button disabled class="flex-1 bg-gray-300 text-gray-500 text-sm font-medium py-3 rounded-lg cursor-not-allowed">
        Terkunci
    </button>
</template>

<template x-if="!getActionButton(indicator.id, selectedDate).state || getActionButton(indicator.id, selectedDate).state === 'disabled'">
    <button @click="openSlideOverFast(indicator.id, selectedDate || '{{ now()->format('Y-m-d') }}')" class="flex-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium py-3 rounded-lg transition">
        Lihat Data
    </button>
</template>
@endif