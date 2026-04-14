@php
$reports = $this->getOngoingAnalysisReports();
$hasReports = !empty($reports);
$mostUrgent = $this->getMostUrgentReport();
$previousReport = $this->getPreviousAnalysisReport();
$showPrevious = !$hasReports && $previousReport;

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
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            Monitoring Pengisian Analisis & Rekomendasi
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if($hasReports)
                            Overview status pengisian semua unit kerja
                            @elseif($showPrevious)
                            Laporan terakhir yang telah diselesaikan
                            @else
                            Riwayat pengisian analisis dan rekomendasi
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
        <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 py-12 text-center dark:border-slate-700">
            <div class="mb-4 rounded-2xl bg-gray-100 p-4 dark:bg-slate-800">
                @svg('heroicon-o-check-circle', 'h-10 w-10 text-gray-400 dark:text-gray-500')
            </div>

            <h3 class="mb-1 text-base font-semibold text-gray-900 dark:text-gray-50">
                Tidak Ada Laporan Aktif
            </h3>
            <p class="max-w-md text-sm text-gray-500 dark:text-gray-400">
                Tidak ada laporan yang sedang dalam fase pengisian analisis dan rekomendasi saat ini.
            </p>
        </div>
        @elseif($showPrevious)
        {{-- Show Previous Report if no ongoing reports --}}
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
                            Periode Pengisian Analis & Rekomendasi:  <strong>{{ $previousReport['period_end']->format('d M Y') }}</strong> - <strong>{{ $previousReport['analysis_deadline']->format('d M Y') }}</strong>
                    </p>

                    @if ($stats && $stats['total_units'] > 0)
                    <div class="mt-3 space-y-2">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-gray-600 dark:text-gray-400">Progres Pengisian Analisis Semua Unit</span>
                            <span class="font-semibold text-gray-900 dark:text-gray-50">{{ $stats['completed_units'] }}/{{ $stats['total_units'] }} ({{ $stats['percentage'] }}%)</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-slate-700/50">
                            <div class="h-full rounded-full transition-all duration-300" style="width: {{ $stats['percentage'] }}%; background-color: {{ match($stats['percentage']) {
                                                100 => '#16a34a',
                                                default => '#f59e0b'
                                            } }}"></div>
                        </div>
                    </div>

                    @if (!empty($stats['unit_details']))
                    <div class="mt-4 space-y-2">
                        {{-- Toggle Button --}}
                        <button type="button" wire:click="toggleExpandedDetails({{ $previousReport['id'] }})" class="flex w-full items-center justify-between rounded-lg bg-gray-200 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-300 dark:bg-slate-700 dark:text-gray-200 dark:hover:bg-slate-600">
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
                                        <p class="font-semibold text-gray-900 dark:text-gray-50">{{ $detail['unit_name'] }}</p>
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
                                        <div class="h-full rounded-full transition-all duration-300" style="width: {{ $detail['percentage'] }}%; background-color: {{ $detail['is_completed'] ? '#16a34a' : '#f59e0b' }}"></div>
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
                </div>
                @endif
            </div>
            @endif
        </div>
        @endif
        @else
        {{-- Most Urgent Report (Summary Card) --}}
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
                                    <span class="font-semibold">Periode Laporan:</span> {{ $mostUrgent['period_end']->format('d M Y') }}
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
                                <div class="h-full rounded-full transition-all duration-300" style="width: {{ $stats['percentage'] }}%; background-color: {{ match($stats['percentage']) {
                                                100 => '#16a34a',
                                                default => '#f59e0b'
                                            } }}"></div>
                            </div>
                        </div>

                        @if (!empty($stats['unit_details']))
                        <div class="mt-4 space-y-2">
                            {{-- Toggle Button --}}
                            <button type="button" wire:click="toggleExpandedDetails({{ $mostUrgent['id'] }})" class="flex w-full items-center justify-between rounded-lg bg-gray-100 px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-slate-700/50 dark:text-gray-200 dark:hover:bg-slate-600">
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
                                            <p class="font-semibold text-gray-900 dark:text-gray-50">{{ $detail['unit_name'] }}</p>
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
                                            <div class="h-full rounded-full transition-all duration-300" style="width: {{ $detail['percentage'] }}%; background-color: {{ $detail['is_completed'] ? '#16a34a' : ($detail['is_overdue'] ? '#dc2626' : '#f59e0b') }}"></div>
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

        {{-- Reports List --}}
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
                        <div class="h-full rounded-full transition-all duration-300" style="width: {{ $stats['percentage'] }}%; background-color: {{ match($stats['percentage']) {
                                            100 => '#16a34a',
                                            default => '#f59e0b'
                                        } }}"></div>
                    </div>

                    {{-- Expandable Unit Details --}}
                    @if (!empty($stats['unit_details']))
                    <button type="button" wire:click="toggleExpandedDetails({{ $report['id'] }})" class="flex w-full items-center justify-between rounded-lg bg-gray-100 px-2 py-1.5 text-xs font-medium text-gray-700 transition hover:bg-gray-200 dark:bg-slate-700/50 dark:text-gray-200 dark:hover:bg-slate-600 mt-2">
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
                                    <p class="font-semibold text-gray-900 dark:text-gray-50 truncate">{{ $detail['unit_name'] }}</p>
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
                                    <div class="h-full rounded-full transition-all duration-300" style="width: {{ $detail['percentage'] }}%; background-color: {{ $detail['is_completed'] ? '#16a34a' : ($detail['is_overdue'] ? '#dc2626' : '#f59e0b') }}"></div>
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

        {{-- Footer Info --}}
        <div class="mt-6 border-t border-gray-200 pt-4 dark:border-slate-700">
            <p class="text-xs text-gray-500 dark:text-gray-400">
                <strong>Catatan:</strong> Pengisian analisis dan rekomendasi dimulai setelah periode penilaian berakhir.
                Monitoring progress semua unit kerja dan follow-up yang belum menyelesaikan pengisian sebelum deadline.
            </p>
        </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>