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

        <!-- Indicators Grid -->
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
                            <span>Periode: {{ $indicator['active_periods'] }} bulan</span>
                        </div>

                        @if($indicator['last_entry_date'])
                        <div class="flex items-center text-gray-600 dark:text-gray-400">
                            @svg("heroicon-o-clock", "w-4 h-4 mr-2")
                            <span>Terakhir: {{ $indicator['last_entry_date'] }}, {{ $indicator['last_entry_time'] }}</span>
                        </div>
                        @endif

                        <div class="pt-3 border-t border-gray-200 dark:border-gray-700">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Bulan Ini</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $indicator['this_month'] }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Minggu Ini</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $indicator['this_week'] }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex gap-2">
                        <a href="{{ route('filament.admin.pages.daily-report-periods') }}?indicator={{ $indicator['id'] }}"
                            class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                            @svg("heroicon-o-eye", "w-4 h-4 mr-2")
                            Lihat Detail
                        </a>
                        <a href="{{ route('filament.admin.resources.daily-report-entries.create') }}?indicator={{ $indicator['id'] }}"
                            class="inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 transition-colors">
                            @svg("heroicon-o-plus", "w-4 h-4 mr-1")
                            Input
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-12 text-center">
                    @svg("heroicon-o-clipboard-document-list", "w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4")
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">
                        Belum Ada Indikator
                    </h3>
                    <p class="text-gray-500 dark:text-gray-400">
                        Belum ada indikator mutu yang dikonfigurasi untuk unit Anda.
                    </p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>