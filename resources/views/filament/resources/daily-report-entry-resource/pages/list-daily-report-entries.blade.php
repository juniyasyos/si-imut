<x-filament-panels::page>
    <div class="space-y-6 relative">
        @include('filament.resources.daily-report-entry-resource.pages.partials.month-navigation')

        <!-- Matrix Table -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    @include('filament.resources.daily-report-entry-resource.pages.partials.table-header')

                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($indicators as $indicator)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40 transition-colors duration-150">
                            <!-- Nama indikator -->
                            <td class="sticky left-0 z-10 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/40 px-6 py-4 border-r-2 border-gray-200 dark:border-gray-700 transition-colors duration-150 min-w-[300px]">
                                <div class="flex items-start gap-3">
                                    <div class="flex flex-col space-y-1 flex-1 min-w-0">
                                        <span class="text-sm font-semibold text-gray-900 dark:text-white leading-snug">
                                            {{ $indicator['title'] }}
                                        </span>
                                        @if($indicator['category'])
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-600 dark:text-gray-400">
                                            <x-heroicon-m-tag class="w-3 h-3" />
                                            {{ $indicator['category'] }}
                                        </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <!-- Cell hari -->
                            @foreach($daysInMonth as $day)
                            @php
                            $state = $this->getCellState($indicator['id'], $day);
                            $summary = $this->getCellSummary($indicator['id'], $day);
                            $cellData = $matrixData[$indicator['id']][$day] ?? null;
                            $dateStr = $cellData['date'] ?? '';
                            $isToday = $this->isToday($day);
                            $indicatorId = $indicator['id'];
                            @endphp

                            @include('filament.resources.daily-report-entry-resource.pages.partials.matrix-cell', [
                            'state' => $state,
                            'summary' => $summary,
                            'cellData' => $cellData,
                            'dateStr' => $dateStr,
                            'isToday' => $isToday,
                            'indicatorId' => $indicatorId
                            ])
                            @endforeach
                        </tr>

                        @empty
                        <tr>
                            <td colspan="{{ count($daysInMonth) + 1 }}" class="px-4 py-16 text-center">
                                <div class="flex flex-col items-center justify-center space-y-4">
                                    <div class="relative">
                                        <div class="absolute inset-0 bg-primary-100 dark:bg-primary-900/20 rounded-full blur-2xl opacity-50"></div>
                                        <div class="relative w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-2xl flex items-center justify-center shadow-lg">
                                            <x-heroicon-o-clipboard-document-list class="w-10 h-10 text-gray-400 dark:text-gray-500" />
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <p class="text-base font-bold text-gray-700 dark:text-gray-300">Belum Ada Indikator</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm">
                                            Belum ada indikator mutu yang dikonfigurasi untuk unit kerja Anda.
                                        </p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500">
                                            Silakan hubungi administrator sistem
                                        </p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @include('filament.resources.daily-report-entry-resource.pages.partials.legend')
    </div>

    {{-- Slide-over rendered outside the page wrapper to prevent overflow clipping --}}
    @include('filament.resources.daily-report-entry-resource.pages.partials.slide-over')
</x-filament-panels::page>