<!-- Mobile Status Cards -->
<!-- Success State -->
<template x-if="getStatusForDate(indicator.id, selectedDate) && getStatusForDate(indicator.id, selectedDate).cell_state === 'done'">
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
        <div class="flex items-center gap-2">
            @svg("heroicon-o-document", "w-5 h-5 text-green-600")
            <div>
                <div class="text-xs text-green-600 dark:text-green-500" x-text="(getStatusForDate(indicator.id, selectedDate).summary?.denominator ?? 0) + ' laporan'"></div>
            </div>
        </div>
    </div>
</template>

<!-- Pending State -->
<template x-if="getStatusForDate(indicator.id, selectedDate) && getStatusForDate(indicator.id, selectedDate).cell_state === 'pending'">
    <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-3">
        <div class="flex items-center gap-2">
            @svg("heroicon-m-exclamation-circle", "w-5 h-5 text-orange-600")
            <div class="text-sm font-medium text-orange-800 dark:text-orange-400">Belum ada laporan</div>
        </div>
    </div>
</template>

<!-- Overdue State -->
<template x-if="getStatusForDate(indicator.id, selectedDate) && getStatusForDate(indicator.id, selectedDate).cell_state === 'overdue'">
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
        <div class="flex items-center gap-2">
            @svg("heroicon-m-lock-closed", "w-5 h-5 text-red-600")
            <div class="text-sm font-medium text-red-800 dark:text-red-400">Terkunci</div>
        </div>
    </div>
</template>

<!-- Default State -->
<template x-if="!getStatusForDate(indicator.id, selectedDate) || getStatusForDate(indicator.id, selectedDate).cell_state === 'disabled'">
    <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
        <div class="flex items-center gap-2">
            @svg("heroicon-m-minus-circle", "w-5 h-5 text-gray-400")
            <div class="text-sm text-gray-600 dark:text-gray-400">—</div>
        </div>
    </div>
</template>