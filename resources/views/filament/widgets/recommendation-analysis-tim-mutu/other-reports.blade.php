@if (count($reports) > 1)
    <div class="space-y-3">
        <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Laporan Lainnya:</p>
        @foreach (array_slice($reports, 1) as $report)
            @php
                $config = $statusConfig[$report['status']];
                $stats = $report['completion_stats'] ?? null;
            @endphp
            <div class="flex flex-col gap-3 rounded-lg border {{ $config['border'] }} {{ $config['bg'] }} px-4 py-3 transition hover:shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="flex flex-1 items-center gap-3">
                        <div class="rounded-lg {{ str_replace('bg-', 'bg-', $config['badge']) }} p-2 dark:bg-slate-800/50">
                            @svg($config['icon'], 'h-4 w-4')
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-gray-900 dark:text-gray-50">
                                {{ $report['name'] }}
                            </p>
                            <p class="text-xs {{ $config['text'] }} font-semibold mt-1 dark:text-gray-300">
                                📅 <strong>{{ $report['analysis_deadline']->format('d M Y') }}</strong>
                                @if (!$report['is_overdue'])
                                    <span class="mx-1">•</span>
                                    <strong>{{ $report['days_remaining'] }} hari lagi</strong>
                                @else
                                    <span class="mx-1">•</span>
                                    <span class="text-red-600 dark:text-red-400">⚠️ Terlewat</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 flex-shrink-0">
                        <div class="text-right">
                            @if (!$report['is_overdue'])
                                <div class="inline-flex items-center gap-1 rounded-full {{ $config['badge'] }} px-3 py-1">
                                    <span class="text-lg font-bold">{{ $report['days_remaining'] }}</span>
                                    <span class="text-xs font-semibold">hari</span>
                                </div>
                            @else
                                <div class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 dark:bg-red-900/40">
                                    <span class="text-xs font-bold text-red-600 dark:text-red-400">OVERDUE</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @if ($stats && $stats['total_units'] > 0)
                    <div class="space-y-2 border-t {{ $config['border'] }} pt-3 dark:border-slate-700">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-600 dark:text-gray-400">Pengisian: {{ $stats['completed_units'] }}/{{ $stats['total_units'] }} unit kerja</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-50">{{ $stats['percentage'] }}%</span>
                        </div>
                        <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-slate-700/50">
                            <div class="h-full rounded-full transition-all duration-300" style="width: {{ $stats['percentage'] }}%; background-color: {{ match ($stats['percentage']) {
                                100 => '#16a34a',
                                default => '#f59e0b'
                            } }}"></div>
                        </div>

                        {{-- Expandable Unit Details --}}
                        @if (!empty($stats['unit_details']))
                            <button type="button" wire:click="toggleExpandedDetails({{ $report['id'] }})"
                                class="flex w-full items-center justify-between rounded-lg bg-gray-100 px-2 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-slate-700/50 dark:text-gray-200 dark:hover:bg-slate-600 mt-2">
                                <span>{{ $stats['total_units'] }} Unit Kerja</span>
                                <span class="transition-transform duration-200">
                                    @if ($this->isDetailExpanded($report['id']))
                                        @svg('heroicon-m-chevron-up', 'h-3 w-3')
                                    @else
                                        @svg('heroicon-m-chevron-down', 'h-3 w-3')
                                    @endif
                                </span>
                            </button>

                            @if ($this->isDetailExpanded($report['id']))
                                <div class="space-y-2 rounded-lg bg-gray-50/50 p-2.5 dark:bg-slate-800/20">
                                    @foreach ($stats['unit_details'] as $detail)
                                        <div class="rounded-lg border border-gray-200 bg-white p-2.5 text-xs dark:border-slate-700 dark:bg-slate-800/40">
                                            {{-- Header: Unit Name + Status --}}
                                            <div class="flex items-start justify-between gap-2 mb-1.5">
                                                <div class="flex-1 min-w-0">
                                                    <p class="font-semibold text-gray-900 dark:text-gray-50 truncate">
                                                        {{ $detail['unit_name'] }}
                                                    </p>
                                                    <p class="text-xs {{ $detail['is_completed'] ? 'text-green-600 dark:text-green-400' : ($detail['is_overdue'] ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400') }} font-medium mt-0.5">
                                                        {{ $detail['status_text'] }}
                                                    </p>
                                                </div>
                                                @if (!$detail['is_overdue'] && !$detail['is_completed'] && $detail['total'] > 0)
                                                    <span class="inline-flex items-center gap-0.5 rounded-full bg-blue-100 px-1.5 py-0.5 font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 flex-shrink-0 whitespace-nowrap text-xs">
                                                        @svg('heroicon-m-clock', 'h-2.5 w-2.5')
                                                        {{ $detail['days_remaining'] }}h
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- Progress --}}
                                            @if ($detail['total'] > 0)
                                                <div class="mb-1.5">
                                                    <div class="flex items-center justify-between text-xs mb-0.5">
                                                        <span class="text-gray-600 dark:text-gray-400">{{ $detail['completed'] }}/{{ $detail['total'] }}</span>
                                                        <span class="font-semibold text-gray-900 dark:text-gray-50">{{ $detail['percentage'] }}%</span>
                                                    </div>
                                                    <div class="h-1 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-slate-700/50">
                                                        <div class="h-full rounded-full transition-all duration-300"
                                                            style="width: {{ $detail['percentage'] }}%; background-color: {{ $detail['is_completed'] ? '#16a34a' : ($detail['is_overdue'] ? '#dc2626' : '#f59e0b') }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            @else
                                                <p class="text-xs text-gray-500 dark:text-gray-400 italic mb-1.5">Tidak ada data penilaian</p>
                                            @endif

                                            {{-- Deadline Info (compact) --}}
                                            <div class="space-y-0.5 border-t border-gray-200 pt-1.5 dark:border-slate-700 text-xs">
                                                <div class="flex justify-between">
                                                    <span class="text-gray-600 dark:text-gray-400">Deadline:</span>
                                                    <span class="font-medium {{ $detail['is_overdue'] ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-50' }}">{{ $detail['analysis_deadline']->format('d M Y') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@endif
