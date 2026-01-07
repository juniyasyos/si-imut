<!-- Month Navigation -->
<div class="bg-white dark:bg-slate-700/60 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4 relative"
    wire:loading.class="opacity-75">

    <div class="flex items-center justify-between">
        <button wire:click="previousMonth"
            wire:loading.attr="disabled"
            wire:target="previousMonth"
            class="inline-flex items-center px-4 py-2 bg-white dark:bg-slate-700/60 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-slate-600 transition disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 active:scale-95 group">
            <svg wire:loading.remove wire:target="previousMonth" class="w-5 h-5 mr-1 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            <svg wire:loading wire:target="previousMonth" class="animate-spin w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span wire:loading.remove wire:target="previousMonth"></span>
            <span wire:loading wire:target="previousMonth">Loading...</span>
        </button>

        <div class="text-center">
            <h3 class="text-xl font-bold text-gray-900 dark:text-white transition-all duration-300">
                {{ \Carbon\Carbon::parse($selectedMonth . '-01')->locale('id')->translatedFormat('F Y') }}
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
                {{ count($indicators) }} Indikator
            </p>
        </div>

        <button wire:click="nextMonth"
            wire:loading.attr="disabled"
            wire:target="nextMonth"
            @if(!$this->canGoNextMonth()) disabled @endif
            class="inline-flex items-center px-4 py-2 bg-white dark:bg-slate-700/60 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-slate-50 dark:hover:bg-slate-600 transition disabled:opacity-50 disabled:cursor-not-allowed transform hover:scale-105 active:scale-95 group">
            <span wire:loading.remove wire:target="nextMonth"></span>
            <span wire:loading wire:target="nextMonth">Loading...</span>
            <svg wire:loading.remove wire:target="nextMonth" class="w-5 h-5 ml-1 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <svg wire:loading wire:target="nextMonth" class="animate-spin w-4 h-4 ml-1" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </button>
    </div>
</div>