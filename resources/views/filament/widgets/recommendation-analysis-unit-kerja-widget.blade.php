@php
$reports = $this->getRelevantAnalysisReports();
$hasReports = !empty($reports);
$mostUrgent = $this->getMostUrgentReport();
$previousReport = $this->getPreviousRelevantAnalysisReport();
$showPrevious = !$hasReports && $previousReport;
$user = auth()->user();

$statusConfig = [
'urgent' => [
'color' => 'red',
'label' => 'URGENT',
'bg' => 'bg-red-50 dark:bg-red-950/30',
'text' => 'text-red-700 dark:text-red-400',
'border' => 'border-red-200 dark:border-red-800',
'badge' => 'bg-red-100/80 dark:bg-red-900/40 text-red-700 dark:text-red-300',
'icon' => 'heroicon-m-exclamation-circle',
],
'warning' => [
'color' => 'amber',
'label' => 'PERHATIAN',
'bg' => 'bg-amber-50 dark:bg-amber-950/30',
'text' => 'text-amber-700 dark:text-amber-400',
'border' => 'border-amber-200 dark:border-amber-800',
'badge' => 'bg-amber-100/80 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300',
'icon' => 'heroicon-m-exclamation-triangle',
],
'info' => [
'color' => 'blue',
'label' => 'BERLANGSUNG',
'bg' => 'bg-blue-50 dark:bg-blue-950/30',
'text' => 'text-blue-700 dark:text-blue-400',
'border' => 'border-blue-200 dark:border-blue-800',
'badge' => 'bg-blue-100/80 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300',
'icon' => 'heroicon-m-arrow-path',
],
];
@endphp

<x-filament-widgets::widget>
    <x-filament::section class="pt-6 pb-4 dark:bg-slate-900">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @svg('heroicon-m-clipboard-document-check', 'h-6 w-6 text-blue-600 dark:text-blue-300')
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-50">
                            Pengisian Analisis & Rekomendasi
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if($hasReports)
                            Kolaborasi Anda dalam laporan berkala
                            @elseif($showPrevious)
                            Laporan terakhir yang telah diselesaikan
                            @else
                            Riwayat pengisian analisis dan rekomendasi Anda
                            @endif
                        </p>
                    </div>
                </div>
                @if ($hasReports)
                <div class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-4 py-2 dark:bg-slate-800/50">
                    <span class="text-2xl font-bold text-blue-700 dark:text-blue-300">{{ count($reports) }}</span>
                    <span class="text-sm text-blue-700 dark:text-blue-300">
                        {{ count($reports) === 1 ? 'Laporan Aktif' : 'Laporan Aktif' }}
                    </span>
                </div>
                @endif
            </div>
        </div>

        @if (!$hasReports && !$showPrevious)
        {{-- Empty State --}}
        <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 py-12 text-center dark:border-slate-300">
            <div class="mb-4 rounded-2xl bg-gray-100 p-4 dark:bg-slate-800">
                @svg('heroicon-o-check-circle', 'h-10 w-10 text-gray-400 dark:text-gray-500')
            </div>

            <h3 class="mb-1 text-base font-semibold text-gray-900 dark:text-gray-50">
                Tidak Ada Laporan Aktif
            </h3>
            <p class="max-w-md text-sm text-gray-500 dark:text-gray-400">
                Tidak ada laporan yang memerlukan pengisian analisis dan rekomendasi dari unit kerja Anda saat ini.
            </p>
        </div>
        @elseif($showPrevious)
        {{-- Show Previous Report if no ongoing reports --}}
        @if ($previousReport)
        @php
        $stats = $previousReport['user_completion_stats'] ?? [];
        @endphp
        <div class="mb-6 rounded-lg border-2 border-gray-200 dark:border-slate-300 bg-gray-50 dark:bg-slate-700 p-5">
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
                            Periode Pengisian Analis & Rekomendasi:  <strong>{{ $previousReport['period_end']->format('d M Y') }}</strong> - <strong>{{ $previousReport['analysis_deadline']->format('d M Y') }}</strong>
                    </p>

                    {{-- Unit Kerja Completion Status --}}
                    @if (!empty($stats))
                    <div class="mt-4 space-y-3">
                        <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Status Pengisian Unit Anda:</p>
                        @foreach ($stats as $stat)
                        <div class="rounded-lg border border-gray-200 bg-white/50 p-3 text-xs dark:border-slate-300 dark:bg-slate-800/40">
                            {{-- Header: Unit Name + Status --}}
                            <div class="flex items-center justify-between gap-2 mb-2">
                                <span class="font-semibold text-gray-900 dark:text-gray-50">{{ $stat['unit_name'] }}</span>
                                <span class="inline-flex items-center gap-1 rounded-full {{ $stat['is_completed'] ? 'bg-green-100/80 dark:bg-slate-700/50 text-green-700 dark:text-green-300' : 'bg-blue-100/80 dark:bg-slate-700/50 text-blue-700 dark:text-blue-300' }} px-2 py-0.5 font-semibold flex-shrink-0 text-xs">
                                    @if ($stat['is_completed'])
                                    @svg('heroicon-m-check-circle', 'h-3 w-3')
                                    Selesai
                                    @else
                                    {{ $stat['percentage'] }}%
                                    @endif
                                </span>
                            </div>

                            {{-- Progress --}}
                            @if ($stat['total'] > 0)
                            <div class="mb-2">
                                <div class="flex items-center justify-between text-xs mb-1">
                                    <span class="text-gray-600 dark:text-gray-400">{{ $stat['completed'] }}/{{ $stat['total'] }} item</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-50">{{ $stat['percentage'] }}%</span>
                                </div>
                                <div class="h-1 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-slate-700/50">
                                    <div class="h-full rounded-full transition-all duration-300" style="width: {{ $stat['percentage'] }}%; background-color: {{ $stat['is_completed'] ? '#16a34a' : '#f59e0b' }}"></div>
                                </div>
                            </div>
                            @endif

                            {{-- Period Info --}}
                            <div class="space-y-0.5 border-t border-gray-200 pt-1.5 dark:border-slate-300">
                                <div class="flex justify-between text-xs">
                                    <span class="text-gray-600 dark:text-gray-400">Periode:</span>
                                    <span class="font-medium text-gray-900 dark:text-gray-50">{{ $stat['period_end']->format('d M Y') }}</span>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
        @else
        {{-- Most Urgent Report (Summary) --}}
        @if ($mostUrgent)
        @php
        $config = $statusConfig[$mostUrgent['status']];
        $stats = $mostUrgent['user_completion_stats'] ?? [];
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
                        <div class="mb-4 rounded-lg {{ $config['bg'] }} border-2 {{ $config['border'] }} p-4 dark:bg-slate-800/30 dark:border-slate-300">
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
                            <div class="mt-3 border-t {{ $config['border'] }} pt-3 dark:border-slate-300">
                                <p class="text-xs {{ $config['text'] }} dark:text-gray-300">
                                    <span class="font-semibold">Periode Laporan:</span> {{ $mostUrgent['period_end']->format('d M Y') }}
                                    <span class="mx-1">|</span>
                                    <span class="font-semibold">Status:</span> {{ $config['label'] }}
                                </p>
                            </div>
                        </div>

                        {{-- Unit Kerja Completion Status --}}
                        @if (!empty($stats))
                        <div class="mt-4 space-y-3">
                            <p class="text-xs font-semibold text-gray-700 dark:text-gray-300">Pengisian Analisis Unit Kerja Anda:</p>
                            @foreach ($stats as $stat)
                            <div class="rounded-lg border border-gray-200 bg-white/50 p-3 text-xs dark:border-slate-300 dark:bg-slate-800/50">
                                {{-- Header: Unit Name + Status --}}
                                <div class="flex items-center justify-between gap-2 mb-2">
                                    <span class="font-semibold text-gray-900 dark:text-gray-50">{{ $stat['unit_name'] }}</span>
                                    <span class="inline-flex items-center gap-1 rounded-full {{ $stat['is_completed'] ? 'bg-green-100/80 dark:bg-slate-700/50 text-green-700 dark:text-green-300' : 'bg-blue-100/80 dark:bg-slate-700/50 text-blue-700 dark:text-blue-300' }} px-2 py-0.5 font-semibold flex-shrink-0 text-xs">
                                        @if ($stat['is_completed'])
                                        @svg('heroicon-m-check-circle', 'h-3 w-3')
                                        Selesai
                                        @else
                                        {{ $stat['percentage'] }}%
                                        @endif
                                    </span>
                                </div>

                                {{-- Progress --}}
                                @if ($stat['total'] > 0)
                                <div class="mb-2">
                                    <div class="flex items-center justify-between text-xs mb-1">
                                        <span class="text-gray-600 dark:text-gray-400">{{ $stat['completed'] }}/{{ $stat['total'] }} item</span>
                                        <span class="font-medium text-gray-900 dark:text-gray-50">{{ $stat['percentage'] }}%</span>
                                    </div>
                                    <div class="h-1 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-slate-700/50">
                                        <div class="h-full rounded-full transition-all duration-300" style="width: {{ $stat['percentage'] }}%; background-color: {{ $stat['is_completed'] ? '#16a34a' : '#f59e0b' }}"></div>
                                    </div>
                                </div>
                                @endif

                                {{-- Deadline Info --}}
                                <div class="space-y-0.5 border-t border-gray-200 pt-1.5 dark:border-slate-300">
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600 dark:text-gray-400">Deadline:</span>
                                        <span class="font-medium {{ $stat['is_overdue'] ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-gray-50' }}">{{ $stat['analysis_deadline']->format('d M Y') }}</span>
                                    </div>
                                    @if (!$stat['is_completed'] && !$stat['is_overdue'] && $stat['total'] > 0)
                                    <div class="flex justify-between text-xs">
                                        <span class="text-gray-600 dark:text-gray-400">Sisa:</span>
                                        <span class="font-medium text-blue-600 dark:text-blue-400">{{ $stat['days_remaining'] }} hari</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                <a href="{{ route('filament.siimut.resources.laporan-imuts.index') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 dark:hover:bg-blue-600">
                    <span>Lihat</span>
                    @svg('heroicon-m-arrow-right', 'ml-2 h-4 w-4')
                </a>
            </div>
        </div>
        @endif

        {{-- Reports List --}}
        @if (count($reports) > 1)
        <div class="space-y-3">
            @foreach (array_slice($reports, 1) as $report)
            @php
            $config = $statusConfig[$report['status']];
            $stats = $report['user_completion_stats'] ?? [];
            @endphp
            <div class="flex flex-col gap-3 rounded-lg border {{ $config['border'] }} {{ $config['bg'] }} px-4 py-3 transition hover:shadow-sm dark:border-slate-300 dark:bg-slate-800/30">
                <div class="flex items-center gap-4">
                    <div class="flex flex-1 items-center gap-3">
                        <div class="rounded-lg {{ str_replace('bg-', 'bg-', $config['badge']) }} p-2 dark:bg-slate-800/50">
                            @svg($config['icon'], 'h-4 w-4')
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-gray-900 dark:text-gray-50">
                                {{ $report['name'] }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                Deadline: {{ $report['analysis_deadline']->format('d M Y') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <div class="text-lg font-bold {{ $config['text'] }}">
                                @if ($report['is_overdue'])
                                <span class="text-red-600 dark:text-red-400">Overdue</span>
                                @else
                                {{ $report['days_remaining'] }}<span class="text-sm font-normal"> hari</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                @if (!empty($stats))
                <div class="border-t {{ $config['border'] }} pt-3 dark:border-slate-300">
                    <p class="mb-2 text-xs font-semibold text-gray-700 dark:text-gray-300">Unit Anda:</p>
                    <div class="space-y-2">
                        @foreach ($stats as $stat)
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-700 dark:text-gray-300">{{ $stat['unit_name'] }}: {{ $stat['completed'] }}/{{ $stat['total'] }}</span>
                            <span class="font-semibold {{ $stat['is_completed'] ? 'text-green-600 dark:text-green-400' : 'text-gray-600 dark:text-gray-400' }}">
                                {{ $stat['percentage'] }}%
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Footer Info --}}
        <div class="mt-6 border-t border-gray-200 pt-4 dark:border-slate-300">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                <strong>Catatan:</strong> Pastikan semua item dalam unit kerja Anda terisi dengan analisis dan rekomendasi sebelum deadline.
                Hubungi Tim Mutu jika ada kendala.
            </p>
        </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>