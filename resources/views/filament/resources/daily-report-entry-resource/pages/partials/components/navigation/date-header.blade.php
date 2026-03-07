<!-- Selected Date Header -->
<div class="mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                @svg("heroicon-m-calendar-days", "w-5 h-5 text-primary-600")
                <span x-text="formatDate(selectedDate)"></span>
            </h2>
            <div class="mt-1 space-y-1">
                <p class="text-md text-gray-600 dark:text-gray-400">
                    Unit Kerja: {{ auth()->user()->unitKerjas->first()?->unit_name ?? 'Tidak ada unit' }}
                </p>
                <p class="text-xs text-blue-600 dark:text-blue-400 flex items-center gap-1">
                    @svg("heroicon-m-information-circle", "w-4 h-4")
                    Bisa input H+{{ \App\Models\LaporanImutAutoGenerationSetting::getInstance()->getBackDataEntryDays() }}
                </p>
            </div>
        </div>
    </div>
</div>