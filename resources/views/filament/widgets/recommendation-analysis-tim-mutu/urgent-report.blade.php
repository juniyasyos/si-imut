@if ($mostUrgent)
    @php
        $config = $statusConfig[$mostUrgent['status']];
        $stats = $mostUrgent['completion_stats'] ?? null;
    @endphp
    <div class="mb-6 rounded-lg border-2 {{ $config['border'] }} {{ $config['bg'] }} p-5">
        <div class="flex items-start justify-between gap-4">
            <div class="flex items-start gap-3 flex-1">
                <div class="mt-1 rounded-lg {{ str_replace('bg-', 'bg-', $config['badge']) }} p-2 dark:bg-slate-800/50">
                    @svg($config['icon'], 'h-5 w-5')
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 dark:text-gray-50 mb-3">
                        {{ $mostUrgent['name'] }}
                    </h4>

                    {{-- Prominent Deadline Banner --}}
                    <div class="mb-4 rounded-lg {{ $config['bg'] }} border-2 {{ $config['border'] }} p-4 dark:bg-slate-800/30 dark:border-slate-700">
                        <div class="grid grid-cols-2 gap-4">
                            {{-- Deadline Date --}}
                            <div>
                                <p class="text-xs font-semibold uppercase {{ $config['text'] }} mb-1">Deadline Pengisian</p>
                                <p class="text-lg font-bold {{ $config['text'] }}">
                                    {{ $mostUrgent['analysis_deadline']->format('d M Y') }}
                                </p>
                                <p class="text-xs {{ $config['text'] }} mt-1">
                                    {{ $mostUrgent['analysis_deadline']->translatedFormat('l') }}
                                </p>
                            </div>

                            {{-- Days Remaining --}}
                            <div class="flex flex-col items-end justify-center">
                                @if ($mostUrgent['is_overdue'])
                                    <p class="text-sm font-bold text-red-600 dark:text-red-400 mb-1">TERLEWAT DEADLINE!</p>
                                    <p class="text-3xl font-black text-red-600 dark:text-red-400">
                                        {{ abs($mostUrgent['days_remaining']) }}<span class="text-xs ml-1">hari</span>
                                    </p>
                                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">Sudah melebihi</p>
                                @else
                                    <p class="text-sm font-bold uppercase {{ $config['text'] }} mb-1">Sisa Waktu</p>
                                    <p class="text-4xl font-black {{ $config['text'] }}">
                                        {{ $mostUrgent['days_remaining'] }}<span class="text-xs ml-1">hari</span>
                                    </p>
                                    @if ($mostUrgent['days_remaining'] < 1)
                                        <p class="text-xs {{ $config['text'] }} mt-2 text-center font-bold">HARI TERAKHIR!</p>
                                    @else
                                        <p class="text-xs {{ $config['text'] }} mt-1">sampai deadline</p>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Period Info --}}
                        <div class="mt-3 border-t {{ $config['border'] }} pt-3 dark:border-slate-700">
                            <p class="text-xs {{ $config['text'] }} dark:text-gray-300">
                                <span class="font-semibold">Periode Laporan:</span>
                                {{ $mostUrgent['period_end']->format('d M Y') }}
                                <span class="mx-1">|</span>
                                <span class="font-semibold">Status:</span> {{ $config['label'] }}
                            </p>
                        </div>
                    </div>

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
                                <button type="button" wire:click="toggleExpandedDetails({{ $mostUrgent['id'] }})"
                                    class="flex w-full items-center justify-between rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-slate-700/50 dark:text-gray-200 dark:hover:bg-slate-600">
                                    <span>Detail Per Unit Kerja ({{ $stats['total_units'] }} units)</span>
                                    <span class="transition-transform duration-200">
                                        @if ($this->isDetailExpanded($mostUrgent['id']))
                                            @svg('heroicon-m-chevron-up', 'h-4 w-4')
                                        @else
                                            @svg('heroicon-m-chevron-down', 'h-4 w-4')
                                        @endif
                                    </span>
                                </button>

                                {{-- Expandable Content --}}
                                @if ($this->isDetailExpanded($mostUrgent['id']))
                                    <div class="space-y-2 rounded-lg bg-gray-50 p-3 dark:bg-slate-700">
                                        @foreach ($stats['unit_details'] as $detail)
                                            <div class="rounded-lg border border-gray-200 bg-white p-3 text-xs dark:border-slate-800 dark:bg-slate-700">
                                                {{-- Header: Unit Name + Status --}}
                                                <div class="flex items-start justify-between gap-2 mb-2">
                                                    <div class="flex-1">
                                                        <p class="font-semibold text-gray-900 dark:text-gray-50">
                                                            {{ $detail['unit_name'] }}
                                                        </p>
                                                        <p class="text-xs {{ $detail['is_completed'] ? 'text-green-600 dark:text-green-400' : ($detail['is_overdue'] ? 'text-red-600 dark:text-red-400' : 'text-gray-600 dark:text-gray-400') }} font-medium mt-0.5">
                                                            {{ $detail['status_text'] }}
                                                        </p>
                                                    </div>
                                                    @if (!$detail['is_overdue'] && !$detail['is_completed'])
                                                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-1 font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300 flex-shrink-0">
                                                            @svg('heroicon-m-clock', 'h-3 w-3')
                                                            {{ $detail['days_remaining'] }}h
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Progress --}}
                                                <div class="mb-2">
                                                    <div class="flex items-center justify-between text-xs mb-1">
                                                        <span class="text-gray-600 dark:text-gray-400">Pengisian Analisis</span>
                                                        <span class="font-semibold text-gray-900 dark:text-gray-50">{{ $detail['completed'] }}/{{ $detail['total'] }} ({{ $detail['percentage'] }}%)</span>
                                                    </div>
                                                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-slate-700/50">
                                                        <div class="h-full rounded-full transition-all duration-300"
                                                            style="width: {{ $detail['percentage'] }}%; background-color: {{ $detail['is_completed'] ? '#16a34a' : ($detail['is_overdue'] ? '#dc2626' : '#f59e0b') }}">
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Deadline Info --}}
                                                <div class="space-y-0.5 border-t border-gray-200 pt-2 dark:border-slate-700">
                                                    <div class="flex justify-between items-center">
                                                        <span class="text-gray-600 dark:text-gray-400 font-semibold">📅 Deadline:</span>
                                                        <span class="font-bold {{ $detail['is_overdue'] ? 'text-red-600 dark:text-red-400' : 'text-blue-600 dark:text-blue-300' }}">
                                                            {{ $detail['analysis_deadline']->format('d M Y') }}
                                                        </span>
                                                    </div>
                                                    @if (!$detail['is_completed'])
                                                        <div class="flex justify-between items-center">
                                                            <span class="text-gray-600 dark:text-gray-400 font-semibold">⏱️ Sisa:</span>
                                                            <span class="font-bold {{ $detail['is_overdue'] ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                                                @if ($detail['is_overdue'])
                                                                    {{ abs($detail['days_remaining']) }} hari terlewat
                                                                @else
                                                                    {{ $detail['days_remaining'] }} hari
                                                                @endif
                                                            </span>
                                                        </div>
                                                    @endif
                                                    <div class="flex justify-between text-xs">
                                                        <span class="text-gray-600 dark:text-gray-400">Periode:</span>
                                                        <span class="text-gray-700 dark:text-gray-300">{{ $detail['period_end']->format('d M Y') }}</span>
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
    </div>
@endif
