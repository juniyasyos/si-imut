<!-- Status Indicators for Desktop -->
<!-- Has Data State -->
<template x-if="getStatusForDate(indicator.id, selectedDate) && getStatusForDate(indicator.id, selectedDate).cell_state === 'done'">
    <div class="flex items-center gap-1 text-green-700 dark:text-green-400">
        @svg("heroicon-m-check-circle", "w-4 h-4")
        <span class="text-sm font-semibold">Ada Data</span>
        <span class="text-xs text-gray-500" x-text="'(' + (getStatusForDate(indicator.id, selectedDate).summary?.numerator ?? 0) + ' laporan)'"></span>
    </div>
</template>

<!-- No Data State (Pending) -->
<template x-if="getStatusForDate(indicator.id, selectedDate) && getStatusForDate(indicator.id, selectedDate).cell_state === 'pending'">
    <div class="flex items-center gap-1 text-gray-600 dark:text-gray-400">
        @svg("heroicon-m-exclamation-circle", "w-4 h-4")
        <span class="text-sm font-medium">Belum Ada Data</span>
    </div>
</template>

<!-- Overdue State -->
<template x-if="getStatusForDate(indicator.id, selectedDate) && getStatusForDate(indicator.id, selectedDate).cell_state === 'overdue'">
    <div class="flex items-center gap-1 text-red-600 dark:text-red-400">
        @svg("heroicon-m-lock-closed", "w-4 h-4")
        <span class="text-sm font-medium">Terkunci</span>
    </div>
</template>

<!-- Default/Empty State -->
<template x-if="!getStatusForDate(indicator.id, selectedDate) || getStatusForDate(indicator.id, selectedDate).cell_state === 'disabled'">
    <div class="flex items-center gap-1 text-gray-400">
        @svg("heroicon-m-minus-circle", "w-4 h-4")
        <span class="text-sm">Belum Ada Data</span>
    </div>
</template>