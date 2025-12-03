<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header with Back Button -->
        <div class="flex items-center gap-4">
            <a href="{{ route('filament.admin.pages.daily-report-dashboard') }}"
                class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                <x-heroicon-o-arrow-left class="w-5 h-5 mr-1" />
                Kembali
            </a>
        </div>

        <!-- Indicator Info -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                {{ $formHeader->imutdata->title ?? $formHeader->title }}
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Unit: {{ auth()->user()->unitKerjas->first()?->unit_name }}
            </p>
            @if($formHeader->description)
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ $formHeader->description }}
            </p>
            @endif
        </div>

        <!-- Stats -->
        <div class="bg-primary-50 dark:bg-primary-900/20 border border-primary-200 dark:border-primary-800 rounded-lg p-4">
            <div class="flex items-center gap-2">
                <x-heroicon-o-information-circle class="w-5 h-5 text-primary-600 dark:text-primary-400 flex-shrink-0" />
                <p class="text-sm text-primary-800 dark:text-primary-200">
                    📊 Total Keseluruhan: <strong>{{ collect($periods)->sum('total_entries') }} entries</strong>
                    dari {{ count($periods) }} periode
                </p>
            </div>
        </div>

        <!-- Periods Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Periode
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Total Entries
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Range Data
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Hari Terisi
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($periods as $period)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-lg bg-primary-100 dark:bg-primary-900">
                                    <span class="text-xs font-bold text-primary-600 dark:text-primary-400">
                                        {{ $period['month_short'] }}
                                    </span>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $period['month_name'] }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                {{ $period['total_entries'] }} entries
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $period['first_date'] }} - {{ $period['last_date'] }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $period['days_with_data'] }} hari
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('filament.admin.pages.daily-report-entries') }}?indicator={{ $formHeader->id }}&period={{ $period['period'] }}"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-md text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                                <x-heroicon-o-eye class="w-4 h-4 mr-1" />
                                Lihat Detail
                            </a>
                            <a href="{{ route('filament.admin.pages.create-daily-report-entry') }}?indicator={{ $formHeader->id }}&period={{ $period['period'] }}"
                                class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md text-white bg-primary-600 hover:bg-primary-700 transition-colors">
                                <x-heroicon-o-plus class="w-4 h-4 mr-1" />
                                Tambah
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <x-heroicon-o-inbox class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-600 mb-3" />
                            <p class="text-gray-500 dark:text-gray-400 mb-4">
                                Belum ada data laporan untuk indikator ini
                            </p>
                            <a href="{{ route('filament.admin.pages.create-daily-report-entry', ['indicator' => $formHeader->id]) }}"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-white bg-primary-600 hover:bg-primary-700 transition-colors">
                                <x-heroicon-o-plus class="w-5 h-5 mr-2" />
                                Mulai Input Laporan
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-filament-panels::page>