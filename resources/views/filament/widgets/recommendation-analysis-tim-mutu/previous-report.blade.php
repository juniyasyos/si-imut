@if ($previousReport)
    @php
        $stats = $previousReport['completion_stats'] ?? null;
    @endphp
    <div class="mb-6 rounded-lg border-2 border-gray-200 dark:border-slate-700 bg-gray-100 dark:bg-slate-800 p-5">
        <div class="mb-3 inline-flex items-center gap-2 rounded-full bg-gray-200 dark:bg-slate-700/50 px-3 py-1">
            <span class="text-xs font-semibold text-gray-700 dark:text-gray-300">
                @svg('heroicon-m-archive-box', 'h-4 w-4 inline mr-1')
                Laporan Sebelumnya
            </span>
        </div>
        <div class="flex items-start justify-between gap-4">
            <div class="flex-1">
                <h4 class="font-semibold text-gray-900 dark:text-gray-50">
                    {{ $previousReport['name'] }}
                </h4>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Periode Pengisian Analis & Rekomendasi:
                    <strong>{{ $previousReport['period_end']->format('d M Y') }}</strong> -
                    <strong>{{ $previousReport['analysis_deadline']->format('d M Y') }}</strong>
                </p>

                @if ($stats && $stats['total_units'] > 0)
                    <div class="mt-3 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-600 dark:text-gray-400">Progres Pengisian Analisis Semua Unit</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-50">{{ $stats['completed_units'] }}/{{ $stats['total_units'] }} ({{ $stats['percentage'] }}%)</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-slate-700/50">
                            <div class="h-full rounded-full transition-all duration-300" style="width: {{ $stats['percentage'] }}%; background-color: {{ match ($stats['percentage']) {
                                100 => '#16a34a',
                                default => '#f59e0b'
                            } }}"></div>
                        </div>
                    </div>

                    @if (!empty($stats['unit_details']))
                        <div class="mt-4 space-y-2">
                            {{-- Toggle Button --}}
                            <button type="button" wire:click="toggleExpandedDetails({{ $previousReport['id'] }})"
                                class="flex w-full items-center justify-between rounded-lg bg-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-300 dark:bg-slate-700 dark:text-gray-200 dark:hover:bg-slate-600">
                                <span>Detail Per Unit Kerja ({{ $stats['total_units'] }} units)</span>
                                <span class="transition-transform duration-200">
                                    @if ($this->isDetailExpanded($previousReport['id']))
                                        @svg('heroicon-m-chevron-up', 'h-4 w-4')
                                    @else
                                        @svg('heroicon-m-chevron-down', 'h-4 w-4')
                                    @endif
                                </span>
                            </button>

                            {{-- Expandable Content --}}
                            @if ($this->isDetailExpanded($previousReport['id']))
                                <div class="space-y-2 rounded-lg bg-white/30 p-3 dark:bg-slate-700">
                                    @foreach ($stats['unit_details'] as $detail)
                                        <div class="rounded-lg border border-gray-200 bg-white/80 p-3 text-xs dark:border-slate-700 dark:bg-slate-800">
                                            {{-- Header: Unit Name + Status --}}
                                            <div class="flex items-start justify-between gap-2 mb-2">
                                                <div class="flex-1">
                                                    <p class="font-semibold text-gray-900 dark:text-gray-50">
                                                        {{ $detail['unit_name'] }}
                                                    </p>
                                                    <p class="text-xs {{ $detail['is_completed'] ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }} font-medium mt-0.5">
                                                        {{ $detail['status_text'] }}
                                                    </p>
                                                </div>
                                            </div>

                                            {{-- Progress --}}
                                            <div class="mb-2">
                                                <div class="flex items-center justify-between text-xs mb-1">
                                                    <span class="text-gray-600 dark:text-gray-400">Pengisian Analisis</span>
                                                    <span class="font-semibold text-gray-900 dark:text-gray-50">{{ $detail['completed'] }}/{{ $detail['total'] }} ({{ $detail['percentage'] }}%)</span>
                                                </div>
                                                <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-slate-700/50">
                                                    <div class="h-full rounded-full transition-all duration-300"
                                                        style="width: {{ $detail['percentage'] }}%; background-color: {{ $detail['is_completed'] ? '#16a34a' : '#f59e0b' }}">
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Period Info --}}
                                            <div class="space-y-0.5 border-t border-gray-200 pt-1.5 dark:border-slate-700">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600 dark:text-gray-400">Periode Laporan:</span>
                                                    <span class="font-medium text-gray-900 dark:text-gray-50">{{ $detail['period_end']->format('d M Y') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
@endif
