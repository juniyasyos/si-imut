@php
$reports = $this->getOngoingAnalysisReports();
$hasReports = !empty($reports);
$mostUrgent = $this->getMostUrgentReport();

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

<x-filament-widgets::widget class="dark:bg-slate-900">
    <x-filament::section class="pt-6 pb-4 dark:bg-slate-900">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    @svg('heroicon-m-clipboard-document-check', 'h-6 w-6 text-blue-600 dark:text-blue-300')
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-slate-50">
                            Pengisian Analisis & Rekomendasi
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-slate-400">
                            Periode pengisian analisis dan rekomendasi laporan
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

        @if (!$hasReports)
        {{-- Empty State --}}
        <div class="flex flex-col items-center justify-center rounded-lg border-2 border-dashed border-gray-300 py-12 text-center dark:border-slate-700">
            <div class="mb-4 rounded-2xl bg-gray-100 p-4 dark:bg-slate-800">
                @svg('heroicon-o-check-circle', 'h-10 w-10 text-gray-400 dark:text-slate-500')
            </div>

            <h3 class="mb-1 text-base font-semibold text-gray-900 dark:text-slate-50">
                Tidak Ada Laporan Aktif
            </h3>
            <p class="max-w-md text-sm text-gray-500 dark:text-slate-400">
                Tidak ada laporan yang sedang dalam fase pengisian analisis dan rekomendasi saat ini.
            </p>
        </div>
        @else
        {{-- Most Urgent Report (Summary) --}}
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
                        <div class="mb-2 flex items-center gap-2">
                            <span class="inline-block rounded-full px-3 py-1 text-xs font-semibold {{ $config['badge'] }} dark:bg-slate-800/50">
                                {{ $config['label'] }}
                            </span>
                            <span class="text-xs font-medium {{ $config['text'] }} dark:text-slate-300">
                                @if ($mostUrgent['is_overdue'])
                                Sudah Melewati Deadline
                                @else
                                {{ $mostUrgent['days_remaining'] }} {{ $mostUrgent['days_remaining'] === 1 ? 'Hari' : 'Hari' }} Tersisa
                                @endif
                            </span>
                        </div>
                        <h4 class="font-semibold text-gray-900 dark:text-slate-50">
                            {{ $mostUrgent['name'] }}
                        </h4>
                        <p class="mt-1 text-sm text-gray-600 dark:text-slate-400">
                            Periode Pengisian Analis & Rekomendasi:  <strong>{{ $mostUrgent['period_end']->format('d M Y') }}</strong> - <strong>{{ $mostUrgent['analysis_deadline']->format('d M Y') }}</strong>
                        </p>

                        @if ($stats && $stats['total_units'] > 0)
                        <div class="mt-3 space-y-2">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-gray-600 dark:text-slate-400">Progres Pengisian Analisis Unit Kerja</span>
                                <span class="font-semibold text-gray-900 dark:text-slate-50">{{ $stats['completed_units'] }}/{{ $stats['total_units'] }} ({{ $stats['percentage'] }}%)</span>
                            </div>
                            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-slate-700/50">
                                <div class="h-full rounded-full transition-all duration-300" style="width: {{ $stats['percentage'] }}%; background-color: {{ match($stats['percentage']) {
                                                100 => '#16a34a',
                                                default => '#f59e0b'
                                            } }}"></div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                <a href="{{ route('filament.admin.resources.laporan-imuts.index') }}" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-700 dark:hover:bg-blue-600">
                    <span>Lihat Detail</span>
                    @svg('heroicon-m-arrow-right', 'ml-2 h-4 w-4')
                </a>
            </div>
        </div>
        @endif

        {{-- Reports List --}}
        <div class="space-y-3">
            @foreach ($reports as $report)
            @php
            $config = $statusConfig[$report['status']];
            $stats = $report['completion_stats'] ?? null;
            @endphp
            <div class="flex flex-col gap-3 rounded-lg border {{ $config['border'] }} {{ $config['bg'] }} px-4 py-3 transition hover:shadow-sm dark:border-slate-700 dark:bg-slate-800/30">
                <div class="flex items-center gap-4">
                    <div class="flex flex-1 items-center gap-3">
                        <div class="rounded-lg {{ str_replace('bg-', 'bg-', $config['badge']) }} p-2 dark:bg-slate-800/50">
                            @svg($config['icon'], 'h-4 w-4')
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-medium text-gray-900 dark:text-slate-50">
                                {{ $report['name'] }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">
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
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                {{ $report['status'] === 'urgent' ? 'SEGERA' : ($report['status'] === 'warning' ? 'SEGERA' : 'Dalam waktu') }}
                            </p>
                        </div>
                    </div>
                </div>

                @if ($stats && $stats['total_units'] > 0)
                <div class="space-y-2 border-t {{ $config['border'] }} pt-3 dark:border-slate-700">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-gray-600 dark:text-slate-400">Pengisian: {{ $stats['completed_units'] }}/{{ $stats['total_units'] }} unit kerja</span>
                        <span class="font-semibold text-gray-900 dark:text-slate-50">{{ $stats['percentage'] }}%</span>
                    </div>
                    <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-slate-700/50">
                        <div class="h-full rounded-full transition-all duration-300" style="width: {{ $stats['percentage'] }}%; background-color: {{ match($stats['percentage']) {
                                        100 => '#16a34a',
                                        default => '#f59e0b'
                                    } }}"></div>
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        {{-- Footer Info --}}
        <div class="mt-6 border-t border-gray-200 pt-4 dark:border-slate-700">
            <p class="text-xs text-gray-500 dark:text-slate-400">
                <strong>Catatan:</strong> Pengisian analisis dan rekomendasi dimulai setelah periode penilaian berakhir.
                Pastikan semua unit kerja menyelesaikan pengisian sebelum deadline.
            </p>
        </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>