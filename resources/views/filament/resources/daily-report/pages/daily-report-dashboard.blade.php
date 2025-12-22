<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Laporan Harian - {{ auth()->user()->unitKerjas->first()?->unit_name ?? 'Unit Kerja' }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Periode Aktif: {{ now()->format('F Y') }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex flex-col md:flex-row gap-4">
                    <div>
                        <label for="filterPeriod" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filter Periode</label>
                        <select id="filterPeriod" wire:model="filterPeriod" class="form-select mt-1 rounded-lg border-gray-300 dark:border-gray-600">
                            <option value="today">Hari Ini</option>
                            <option value="weekly">Minggu Ini</option>
                            <option value="monthly">Bulan Ini</option>
                        </select>
                    </div>

                    <div>
                        <label for="filterIndicator" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Filter Indikator</label>
                        <select id="filterIndicator" wire:model="filterIndicator" class="form-select mt-1 rounded-lg border-gray-300 dark:border-gray-600">
                            <option value="">Semua Indikator</option>
                            @foreach($indicatorStats as $indicator)
                            <option value="{{ $indicator['id'] }}">{{ $indicator['title'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daily Data Highlight -->
        @if($filterPeriod === 'today')
        <div class="bg-primary-50 dark:bg-primary-900 rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-primary-800 dark:text-primary-200 mb-4">Highlight Hari Ini</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($indicatorStats as $indicator)
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow hover:shadow-lg transition-shadow border border-gray-200 dark:border-gray-700">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white line-clamp-2">
                                {{ $indicator['title'] }}
                            </h3>
                            <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800 dark:bg-primary-900 dark:text-primary-200">
                                {{ $indicator['total_entries'] }}
                            </span>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div class="flex items-center text-gray-600 dark:text-gray-400">
                                @svg("heroicon-o-calendar", "w-4 h-4 mr-2")
                                <span>Periode Aktif: {{ $indicator['active_periods'] }} bulan</span>
                            </div>

                            @if($indicator['last_entry_date'])
                            <div class="flex items-center text-gray-600 dark:text-gray-400">
                                @svg("heroicon-o-clock", "w-4 h-4 mr-2")
                                <span>Terakhir: {{ $indicator['last_entry_date'] }}, {{ $indicator['last_entry_time'] }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @empty
                <div class="col-span-full">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                        @svg("heroicon-o-clipboard-document-list", "w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4")
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                            Tidak Ada Data Hari Ini
                        </h3>
                        <p class="text-gray-500 dark:text-gray-400">
                            Tidak ada data yang tersedia untuk hari ini.
                        </p>
                    </div>
                </div>
                @endforelse
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>