<!-- Status Indicators for Desktop -->
<!-- Success State -->
<template x-if="getStatusForDate(indicator.id, selectedDate) && getStatusForDate(indicator.id, selectedDate).cell_state === 'done'">
    <div class="flex items-center gap-1 text-green-700 dark:text-green-400">
        @svg("heroicon-m-check-circle", "w-4 h-4")
        <span class="text-sm font-semibold" x-text="(getStatusForDate(indicator.id, selectedDate).summary?.percentage ?? '0') + '%'"></span>
        <span class="text-xs text-gray-500" x-text="'(' + (getStatusForDate(indicator.id, selectedDate).summary?.numerator ?? 0) + '/' + (getStatusForDate(indicator.id, selectedDate).summary?.denominator ?? 0) + ')'"></span>
    </div>
</template>

<!-- Pending State -->
<template x-if="getStatusForDate(indicator.id, selectedDate) && getStatusForDate(indicator.id, selectedDate).cell_state === 'pending'">
    <div class="flex items-center gap-1 text-orange-600 dark:text-orange-400">
        @svg("heroicon-m-exclamation-circle", "w-4 h-4")
        <span class="text-sm font-medium">Belum diisi</span>
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
        <span class="text-sm">—</span>
    </div>
</template>