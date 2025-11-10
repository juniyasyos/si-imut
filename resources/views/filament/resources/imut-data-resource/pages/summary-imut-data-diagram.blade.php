<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Tabs Navigation -->
        <div x-data="{ activeTab: 'summary' }" class="space-y-4">
            <!-- Tab Headers -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button
                        @click="activeTab = 'summary'"
                        :class="activeTab === 'summary' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                    >
                        <span class="flex items-center gap-2">
                            <x-heroicon-o-chart-bar class="w-5 h-5" />
                            Summary Data
                        </span>
                    </button>
                    <button
                        @click="activeTab = 'notes'"
                        :class="activeTab === 'notes' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors"
                    >
                        <span class="flex items-center gap-2">
                            <x-heroicon-o-document-text class="w-5 h-5" />
                            Catatan
                        </span>
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div>
                <!-- Summary Tab -->
                <div x-show="activeTab === 'summary'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">
                    <livewire:overview.imut-data-summary-table :imut-data-id="$data['imutDataId']" />
                </div>

                <!-- Notes Tab -->
                <div x-show="activeTab === 'notes'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">
                    @livewire(\App\Filament\Resources\ImutDataResource\Widgets\ImutDataNotesReport::class, ['imutDataId' => $data['imutDataId']])
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
