<!-- Month Navigation -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
    <div class="flex items-center justify-between">
        <button wire:click="previousMonth"
            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition">
            <x-heroicon-o-chevron-left class="w-5 h-5 mr-1" />
            Prev
        </button>

        <div class="text-center">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                {{ \Carbon\Carbon::parse($selectedMonth . '-01')->translatedFormat('F Y') }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                {{ count($indicators) }} Indikator
            </p>
        </div>

        <button wire:click="nextMonth"
            @if(!$this->canGoNextMonth()) disabled @endif
            class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600 transition disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white dark:disabled:hover:bg-gray-700">
            Next
            <x-heroicon-o-chevron-right class="w-5 h-5 ml-1" />
        </button>
    </div>
</div>