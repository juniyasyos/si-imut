<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Indicators Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($this->indicatorStats as $indicator)
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-md transition-all duration-200 border border-gray-200 dark:border-gray-700 overflow-hidden">
                <div class="p-6">
                    <!-- Header -->
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-2 mb-1">
                                {{ $indicator['title'] }}
                            </h3>
                            @if($indicator['category'])
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                {{ $indicator['category'] }}
                            </span>
                            @endif
                        </div>
                        <span class="ml-3 inline-flex items-center justify-center w-10 h-10 rounded-full text-sm font-bold bg-primary-100 text-primary-700 dark:bg-primary-900 dark:text-primary-200">
                            {{ $indicator['total_entries'] }}
                        </span>
                    </div>

                    <!-- Stats -->
                    <div class="space-y-3 mb-6">
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-calendar class="w-4 h-4 mr-2 flex-shrink-0" />
                            <span>{{ $indicator['active_periods'] }} periode aktif</span>
                        </div>

                        @if($indicator['last_entry_date'])
                        <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                            <x-heroicon-o-clock class="w-4 h-4 mr-2 flex-shrink-0" />
                            <span>Terakhir: {{ $indicator['last_entry_date'] }}, {{ $indicator['last_entry_time'] }}</span>
                        </div>
                        @endif

                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="grid grid-cols-2 gap-4">
                                <div class="text-center">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Bulan Ini</p>
                                    <p class="text-2xl font-bold text-primary-600 dark:text-primary-400">{{ $indicator['this_month'] }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Minggu Ini</p>
                                    <p class="text-2xl font-bold text-success-600 dark:text-success-400">{{ $indicator['this_week'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-2">
                        <x-filament::button
                            wire:navigate
                            href="{{ \App\Filament\Resources\DailyReportEntryResource::getUrl('index') }}?tableFilters[form_header_id][values][0]={{ $indicator['id'] }}"
                            color="gray"
                            size="sm"
                            class="flex-1">
                            <x-heroicon-o-table-cells class="w-4 h-4 mr-1" />
                            Lihat Data
                        </x-filament::button>
                        <x-filament::button
                            wire:navigate
                            href="{{ \App\Filament\Resources\DailyReportEntryResource::getUrl('create') }}?indicator={{ $indicator['id'] }}"
                            color="primary"
                            size="sm">
                            <x-heroicon-o-plus class="w-4 h-4 mr-1" />
                            Input
                        </x-filament::button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm p-12 text-center border border-gray-200 dark:border-gray-700">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-700 mb-4">
                        <x-heroicon-o-clipboard-document-list class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                        Belum Ada Indikator
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto">
                        Belum ada indikator mutu yang dikonfigurasi untuk unit kerja Anda. Hubungi administrator untuk setup indikator.
                    </p>
                </div>
            </div>
            @endforelse
        </div>

        <!-- Quick Stats Summary (if there are indicators) -->
        @if(count($this->indicatorStats) > 0)
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900">
                            <x-heroicon-o-clipboard-document-list class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Indikator</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ count($this->indicatorStats) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-success-100 dark:bg-success-900">
                            <x-heroicon-o-check-circle class="w-6 h-6 text-success-600 dark:text-success-400" />
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Laporan</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ array_sum(array_column($this->indicatorStats, 'total_entries')) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-warning-100 dark:bg-warning-900">
                            <x-heroicon-o-calendar class="w-6 h-6 text-warning-600 dark:text-warning-400" />
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Bulan Ini</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ array_sum(array_column($this->indicatorStats, 'this_month')) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-info-100 dark:bg-info-900">
                            <x-heroicon-o-calendar-days class="w-6 h-6 text-info-600 dark:text-info-400" />
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Minggu Ini</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ array_sum(array_column($this->indicatorStats, 'this_week')) }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>