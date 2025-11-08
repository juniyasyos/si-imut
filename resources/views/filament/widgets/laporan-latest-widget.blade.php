@php
    use Illuminate\Support\Facades\Gate;

    $laporan = $this->getLaporan();
    $user = auth()->user();

    $statusConfig = [
        'process' => [
            'label' => 'Sedang Berjalan',
            'color' => 'blue',
            'icon' => 'heroicon-m-arrow-path',
            'bg' => 'bg-blue-50 dark:bg-blue-950/30',
            'text' => 'text-blue-700 dark:text-blue-400',
            'ring' => 'ring-blue-200 dark:ring-blue-800',
            'badge' =>
                'bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-400 ring-blue-200 dark:ring-blue-800',
        ],
        'complete' => [
            'label' => 'Selesai',
            'color' => 'green',
            'icon' => 'heroicon-m-check-circle',
            'bg' => 'bg-green-50 dark:bg-green-950/30',
            'text' => 'text-green-700 dark:text-green-400',
            'ring' => 'ring-green-200 dark:ring-green-800',
            'badge' =>
                'bg-green-100 dark:bg-green-900/50 text-green-700 dark:text-green-400 ring-green-200 dark:ring-green-800',
        ],
        'coming_soon' => [
            'label' => 'Akan Datang',
            'color' => 'gray',
            'icon' => 'heroicon-m-clock',
            'bg' => 'bg-gray-50 dark:bg-gray-950/30',
            'text' => 'text-gray-700 dark:text-gray-400',
            'ring' => 'ring-gray-200 dark:ring-gray-800',
            'badge' =>
                'bg-gray-100 dark:bg-gray-900/50 text-gray-700 dark:text-gray-400 ring-gray-200 dark:ring-gray-800',
        ],
    ];

    $currentStatus = $statusConfig[$laporan?->status ?? 'coming_soon'] ?? $statusConfig['coming_soon'];

    // Check permissions for actions
    $canViewLaporan = $user?->can('view', $laporan ?? \App\Models\LaporanImut::class);
    $canManageBenchmarking = $user?->can('view_any', \App\Models\Benchmarking::class);
    $canViewAnalytics = $user?->can('view_any', \App\Models\LaporanImut::class);

    // Check permission untuk isi penilaian
    $canIsiPenilaian = false;
    $isiPenilaianUrl = null;

    if ($user && $laporan) {
        $userUnitKerjaIds = $user->unitKerjas->pluck('id')->toArray();
        $laporanUnitKerjaIds = $laporan->unitKerjas->pluck('id')->toArray();
        $canIsiPenilaian = !empty(array_intersect($userUnitKerjaIds, $laporanUnitKerjaIds));

        // Get URL untuk isi penilaian (jika user punya unit kerja yang match)
        if ($canIsiPenilaian) {
            $matchingUnitKerja = $user
                ->unitKerjas()
                ->whereIn('unit_kerja.id', $laporanUnitKerjaIds)
                ->first();

            if ($matchingUnitKerja) {
                $isiPenilaianUrl = \App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport::getUrl([
                    'laporan_id' => $laporan->id,
                    'unit_kerja_id' => $matchingUnitKerja->id,
                ]);
            }
        }
    }

    // Calculate progress if process
    $progressPercentage = 0;
    $daysRemaining = 0;
    if ($laporan && $laporan->status === 'process') {
        $start = $laporan->assessment_period_start;
        $end = $laporan->assessment_period_end;
        $today = now();
        $totalDays = $start->diffInDays($end);
        $passedDays = $start->diffInDays($today);
        $progressPercentage = $totalDays > 0 ? min(100, round(($passedDays / $totalDays) * 100)) : 0;
        $daysRemaining = max(0, $today->diffInDays($end, false));
    }
@endphp
<x-filament-widgets::widget>

    <x-filament::section>
        @if (!$laporan)
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center py-12">
                <div class="rounded-full bg-gray-100 dark:bg-gray-800 p-4 mb-4">
                    <x-heroicon-o-document-text class="w-12 h-12 text-gray-400 dark:text-gray-600" />
                </div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                    Belum Ada Laporan Tersedia
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center max-w-md">
                    Belum ada laporan yang tersedia saat ini. Silakan hubungi administrator untuk membuat laporan
                    periode baru.
                </p>
            </div>
        @else
            {{-- Main Content --}}
            <div class="space-y-4">
                {{-- Header Section with Status Badge --}}
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    {{-- Title & Period Info --}}
                    <div class="flex-1">
                        <div class="flex items-start gap-3">
                            <div
                                class="flex-shrink-0 rounded-xl {{ $currentStatus['bg'] }} p-3 ring-1 {{ $currentStatus['ring'] }}">
                                <x-dynamic-component :component="$currentStatus['icon']" class="w-6 h-6 {{ $currentStatus['text'] }}" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-1">
                                    {{ $laporan->name }}
                                </h2>
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm">
                                    <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                                        <x-heroicon-m-calendar class="w-4 h-4" />
                                        <span class="font-medium">
                                            {{ $laporan->assessment_period_start->translatedFormat('d M Y') }}
                                            <span class="text-gray-400 dark:text-gray-600">—</span>
                                            {{ $laporan->assessment_period_end->translatedFormat('d M Y') }}
                                        </span>
                                    </span>
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium ring-1 {{ $currentStatus['badge'] }}">
                                        <x-dynamic-component :component="$currentStatus['icon']" class="w-3.5 h-3.5" />
                                        {{ $currentStatus['label'] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Actions --}}
                    <div class="flex flex-wrap gap-2 lg:flex-shrink-0">
                        {{-- Tombol Isi Penilaian (prioritas tertinggi untuk user penilai) --}}
                        @if ($isiPenilaianUrl)
                            <a href="{{ $isiPenilaianUrl }}"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white
                                       bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800
                                       rounded-lg shadow-sm hover:shadow-md transition-all duration-200
                                       ring-1 ring-emerald-600 dark:ring-emerald-500">
                                <x-heroicon-m-pencil-square class="w-4 h-4" />
                                Isi Penilaian
                            </a>
                        @endif

                        @if ($canViewLaporan && $laporan)
                            <a href="{{ \App\Filament\Resources\LaporanImutResource::getUrl('edit', ['record' => $laporan]) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white
                                       bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800
                                       rounded-lg shadow-sm hover:shadow-md transition-all duration-200
                                       ring-1 ring-blue-600 dark:ring-blue-500">
                                <x-heroicon-m-eye class="w-4 h-4" />
                                Lihat Detail
                            </a>
                        @endif

                        @if ($canManageBenchmarking && $laporan)
                            <a href="{{ \App\Filament\Resources\BenchmarkingResource::getUrl('index', ['tableFilters' => ['laporan_imut_id' => ['value' => $laporan->id]]]) }}"
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold
                                       text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800
                                       hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg shadow-sm hover:shadow
                                       ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200">
                                <x-heroicon-m-chart-bar class="w-4 h-4" />
                                Data Benchmarking
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Progress Bar (only for process status) --}}
                @if ($laporan->status === 'process')
                    <div
                        class="rounded-xl bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-950/20 dark:to-cyan-950/20
                                p-4 ring-1 ring-blue-200 dark:ring-blue-800">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                Progress Periode
                            </span>
                            <span class="text-sm font-bold text-blue-700 dark:text-blue-400">
                                {{ $progressPercentage }}%
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-blue-600 to-cyan-600 rounded-full transition-all duration-500 ease-out"
                                style="width: {{ $progressPercentage }}%">
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-2 text-xs text-gray-600 dark:text-gray-400">
                            <x-heroicon-m-clock class="w-3.5 h-3.5" />
                            <span>
                                @if ($daysRemaining > 0)
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $daysRemaining }}
                                        hari</span> tersisa
                                @else
                                    Periode berakhir hari ini
                                @endif
                            </span>
                        </div>
                    </div>
                @endif

                {{-- Info Cards Grid --}}
                <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    {{-- Report Year/Month --}}
                    <div
                        class="rounded-lg bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-950/20 dark:to-pink-950/20
                              p-4 ring-1 ring-purple-200 dark:ring-purple-800">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-purple-100 dark:bg-purple-900/50 p-2">
                                <x-heroicon-m-calendar-days class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-purple-600 dark:text-purple-400">Periode Laporan
                                </p>
                                <p class="text-lg font-bold text-purple-900 dark:text-purple-100">
                                    {{ $laporan->report_month }}/{{ $laporan->report_year }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Duration --}}
                    <div
                        class="rounded-lg bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-950/20 dark:to-amber-950/20
                              p-4 ring-1 ring-orange-200 dark:ring-orange-800">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-orange-100 dark:bg-orange-900/50 p-2">
                                <x-heroicon-m-clock class="w-5 h-5 text-orange-600 dark:text-orange-400" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-orange-600 dark:text-orange-400">Durasi</p>
                                <p class="text-lg font-bold text-orange-900 dark:text-orange-100">
                                    {{ $laporan->assessment_period_start->diffInDays($laporan->assessment_period_end) + 1 }}
                                    Hari
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Created Date --}}
                    <div
                        class="rounded-lg bg-gradient-to-br from-teal-50 to-emerald-50 dark:from-teal-950/20 dark:to-emerald-950/20
                              p-4 ring-1 ring-teal-200 dark:ring-teal-800">
                        <div class="flex items-center gap-3">
                            <div class="rounded-lg bg-teal-100 dark:bg-teal-900/50 p-2">
                                <x-heroicon-m-document-plus class="w-5 h-5 text-teal-600 dark:text-teal-400" />
                            </div>
                            <div>
                                <p class="text-xs font-medium text-teal-600 dark:text-teal-400">Dibuat</p>
                                <p class="text-lg font-bold text-teal-900 dark:text-teal-100">
                                    {{ $laporan->created_at->translatedFormat('d M Y') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Stats --}}
                    @if ($canViewAnalytics)
                        <div
                            class="rounded-lg bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-950/20 dark:to-blue-950/20
                                  p-4 ring-1 ring-indigo-200 dark:ring-indigo-800">
                            <div class="flex items-center gap-3">
                                <div class="rounded-lg bg-indigo-100 dark:bg-indigo-900/50 p-2">
                                    <x-heroicon-m-chart-bar-square
                                        class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-indigo-600 dark:text-indigo-400">Status Data
                                    </p>
                                    <p class="text-lg font-bold text-indigo-900 dark:text-indigo-100">
                                        {{ ucfirst($laporan->status) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Additional Info Footer --}}
                @if ($laporan->description)
                    <div class="rounded-lg bg-gray-50 dark:bg-gray-800/50 p-4 ring-1 ring-gray-200 dark:ring-gray-700">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Catatan:</p>
                        <p class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $laporan->description }}
                        </p>
                    </div>
                @endif
            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
