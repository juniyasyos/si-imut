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
                    <div class="inline-flex items-center gap-2 rounded-full bg-blue-100 px-3 py-1 dark:bg-slate-800/50">
                        <span class="text-xl font-bold text-blue-700 dark:text-blue-300">{{ count($reports) }}</span>
                        <span class="text-sm text-blue-700 dark:text-blue-300">
                            {{ count($reports) === 1 ? 'Laporan Aktif' : 'Laporan Aktif' }}
                        </span>
                    </div>
                @endif
            </div>
        </div>

        @if (!$hasReports && !$showPrevious)
            @include('filament.widgets.recommendation-analysis-tim-mutu.empty-state')
        @elseif($showPrevious)
            @include('filament.widgets.recommendation-analysis-tim-mutu.previous-report')
        @else
            @include('filament.widgets.recommendation-analysis-tim-mutu.urgent-report')
            @include('filament.widgets.recommendation-analysis-tim-mutu.other-reports')

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