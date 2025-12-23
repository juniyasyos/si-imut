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
'bg-blue-100/80 dark:bg-blue-900/40 text-blue-700 dark:text-blue-300 ring-blue-200/70 dark:ring-blue-800/70',
],
'complete' => [
'label' => 'Selesai',
'color' => 'green',
'icon' => 'heroicon-m-check-circle',
'bg' => 'bg-green-50 dark:bg-green-950/30',
'text' => 'text-green-700 dark:text-green-400',
'ring' => 'ring-green-200 dark:ring-green-800',
'badge' =>
'bg-green-100/80 dark:bg-green-900/40 text-green-700 dark:text-green-300 ring-green-200/70 dark:ring-green-800/70',
],
'coming_soon' => [
'label' => 'Akan Datang',
'color' => 'gray',
'icon' => 'heroicon-m-clock',
'bg' => 'bg-gray-50 dark:bg-gray-950/30',
'text' => 'text-gray-700 dark:text-gray-400',
'ring' => 'ring-gray-200 dark:ring-gray-800',
'badge' =>
'bg-gray-100/80 dark:bg-gray-900/40 text-gray-700 dark:text-gray-300 ring-gray-200/70 dark:ring-gray-800/70',
],
];

$currentStatus = $statusConfig[$laporan?->status ?? 'coming_soon'] ?? $statusConfig['coming_soon'];

// Permissions
$canViewLaporan = $user?->can('view', $laporan ?? \App\Models\LaporanImut::class);
$canManageBenchmarking = $user?->can('view_any', \App\Models\Benchmarking::class);
$canViewAnalytics = $user?->can('view_any', \App\Models\LaporanImut::class);

// Isi penilaian
$canIsiPenilaian = false;
$isiPenilaianUrl = null;

if ($user && $laporan) {
$userUnitKerjaIds = $user->unitKerjas->pluck('id')->toArray();
$laporanUnitKerjaIds = $laporan->unitKerjas->pluck('id')->toArray();
$canIsiPenilaian = !empty(array_intersect($userUnitKerjaIds, $laporanUnitKerjaIds));

if ($canIsiPenilaian) {
$matchingUnitKerja = $user->unitKerjas()->whereIn('unit_kerja.id', $laporanUnitKerjaIds)->first();

if ($matchingUnitKerja) {
$isiPenilaianUrl = \App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport::getUrl([
'laporan_id' => $laporan->id,
'unit_kerja_id' => $matchingUnitKerja->id,
]);
}
}
}

// Progress
$progressPercentage = 0;
$daysRemaining = 0;

if ($laporan && $laporan->status === 'process') {
$start = $laporan->assessment_period_start;
$end = $laporan->assessment_period_end;
$today = now();

$totalDays = max(1, $start->diffInDays($end));
$passedDays = min($totalDays, $start->diffInDays($today));
$progressPercentage = min(100, round(($passedDays / $totalDays) * 100));
$daysRemaining = max(0, $today->diffInDays($end, false));
}
@endphp

<x-filament-widgets::widget>
    <x-filament::section class="pt-8 pb-4">
        @if (!$laporan)
        {{-- Empty State --}}
        <div class="flex flex-col items-center justify-center py-10 text-center">
            <div class="mb-4 rounded-2xl bg-gray-100 p-4 dark:bg-slate-800/80">
                @svg("heroicon-o-document-text", "h-10 w-10 text-gray-400 dark:text-gray-600")
            </div>

            <h3 class="mb-1 text-base font-semibold text-gray-900 dark:text-gray-50">
                Belum Ada Laporan
            </h3>
            <p class="max-w-md text-sm text-gray-500 dark:text-gray-400">
                Saat ini belum terdapat laporan indikator mutu yang aktif. Hubungi administrator untuk membuat
                periode laporan baru.
            </p>
        </div>
        @else
        <div class="space-y-5">
            {{-- Header: Judul + Status + Periode + Aksi --}}
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                {{-- Judul & Periode --}}
                <div class="flex-1">
                    <div class="flex items-start gap-3">
                        <div
                            class="flex h-11 w-11 items-center justify-center rounded-xl {{ $currentStatus['bg'] }} {{ $currentStatus['ring'] }} ring-1">
                            <x-dynamic-component :component="$currentStatus['icon']" class="h-5 w-5 {{ $currentStatus['text'] }}" />
                        </div>

                        <div class="min-w-0 flex-1 space-y-1">
                            <h2 class="truncate text-lg font-semibold text-gray-900 dark:text-gray-50">
                                {{ $laporan->name }}
                            </h2>

                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs">
                                <span class="inline-flex items-center gap-1.5 text-gray-600 dark:text-gray-400">
                                    @svg("heroicon-m-calendar", "h-4 w-4")
                                    <span>
                                        {{ $laporan->assessment_period_start->translatedFormat('d M Y') }}
                                        <span class="text-gray-400 dark:text-gray-600">—</span>
                                        {{ $laporan->assessment_period_end->translatedFormat('d M Y') }}
                                    </span>
                                </span>

                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-[11px] font-medium ring-1 {{ $currentStatus['badge'] }}">
                                    <x-dynamic-component :component="$currentStatus['icon']" class="h-3.5 w-3.5" />
                                    {{ $currentStatus['label'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Aksi Cepat --}}
                <div class="flex flex-wrap gap-2 lg:flex-shrink-0">
                    {{-- Tombol Isi Penilaian (prioritas tertinggi untuk user penilai) --}}
                    @if ($isiPenilaianUrl)
                    <a href="{{ $isiPenilaianUrl }}"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white
                                       bg-gradient-to-r from-emerald-600 to-teal-700 hover:from-emerald-700 hover:to-teal-800
                                       rounded-lg shadow-sm hover:shadow-md transition-all duration-200
                                       ring-1 ring-emerald-600 dark:ring-emerald-500">
                        @svg("heroicon-m-pencil-square", "w-4 h-4")
                        Isi Penilaian
                    </a>
                    @endif

                    @if ($canViewLaporan && $laporan)
                    <a href="{{ \App\Filament\Resources\LaporanImutResource::getUrl('edit', ['record' => $laporan]) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white
                                       bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800
                                       rounded-lg shadow-sm hover:shadow-md transition-all duration-200
                                       ring-1 ring-blue-600 dark:ring-blue-500">
                        @svg("heroicon-m-eye", "w-4 h-4")
                        Lihat Detail
                    </a>
                    @endif

                    @if ($canManageBenchmarking && $laporan)
                    <a href="{{ \App\Filament\Resources\BenchmarkingResource::getUrl('index', ['tableFilters' => ['laporan_imut_id' => ['value' => $laporan->id]]]) }}"
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold
                                       text-gray-700 dark:text-gray-300 bg-white dark:bg-slate-800/80
                                       hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg shadow-sm hover:shadow
                                       ring-1 ring-gray-300 dark:ring-gray-600 transition-all duration-200">
                        @svg("heroicon-m-chart-bar", "w-4 h-4")
                        Data Benchmarking
                    </a>
                    @endif
                </div>
            </div>

            {{-- Progress waktu (hanya status process) --}}
            @if ($laporan->status === 'process')
            <div
                class="rounded-xl border border-blue-100 bg-blue-50/70 p-4 dark:border-blue-900 dark:bg-blue-950/40">
                <div class="mb-2 flex items-center justify-between">
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200">
                        Progress Periode Waktu
                    </span>
                    <span class="text-sm font-semibold text-blue-700 dark:text-blue-400">
                        {{ $progressPercentage }}%
                    </span>
                </div>

                <div class="h-2.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                    <div class="h-full rounded-full bg-blue-600 transition-all duration-500 ease-out"
                        style="width: {{ $progressPercentage }}%">
                    </div>
                </div>

                <div class="mt-2 flex items-center gap-2 text-xs text-gray-600 dark:text-gray-400">
                    @svg("heroicon-m-clock", "h-3.5 w-3.5")
                    @if ($daysRemaining > 0)
                    <span>
                        <span class="font-semibold text-gray-900 dark:text-gray-100">
                            {{ $daysRemaining }} hari
                        </span>
                        tersisa hingga akhir periode.
                    </span>
                    @else
                    <span>Periode penilaian berakhir hari ini.</span>
                    @endif
                </div>
            </div>
            @endif

            {{-- Info ringkas (grid) --}}
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                {{-- Periode laporan --}}
                <div
                    class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3.5 text-sm dark:border-gray-700 dark:bg-slate-700/80">
                    <div class="rounded-lg bg-gray-100 p-2 dark:bg-slate-800/80">
                        @svg("heroicon-m-calendar-days", "h-5 w-5 text-gray-600 dark:text-gray-300")
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                            Periode Laporan
                        </p>
                        <p class="truncate text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $laporan->report_month }}/{{ $laporan->report_year }}
                        </p>
                    </div>
                </div>

                {{-- Durasi hari --}}
                <div
                    class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3.5 text-sm dark:border-gray-700 dark:bg-slate-700/80">
                    <div class="rounded-lg bg-gray-100 p-2 dark:bg-slate-800/80">
                        @svg("heroicon-m-clock", "h-5 w-5 text-gray-600 dark:text-gray-300")
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                            Durasi Periode
                        </p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $laporan->assessment_period_start->diffInDays($laporan->assessment_period_end) + 1 }}
                            hari
                        </p>
                    </div>
                </div>

                {{-- Tanggal dibuat --}}
                <div
                    class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3.5 text-sm dark:border-gray-700 dark:bg-slate-700/80">
                    <div class="rounded-lg bg-gray-100 p-2 dark:bg-slate-800/80">
                        @svg("heroicon-m-document-plus", "h-5 w-5 text-gray-600 dark:text-gray-300")
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                            Dibuat
                        </p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ $laporan->created_at->translatedFormat('d M Y') }}
                        </p>
                    </div>
                </div>

                {{-- Status data / analytics --}}
                @if ($canViewAnalytics)
                <div
                    class="flex items-center gap-3 rounded-lg border border-gray-200 bg-white p-3.5 text-sm dark:border-gray-700 dark:bg-slate-700/80">
                    <div class="rounded-lg bg-gray-100 p-2 dark:bg-slate-800/80">
                        @svg("heroicon-m-chart-bar-square", "h-5 w-5 text-gray-600 dark:text-gray-300")
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-medium text-gray-500 dark:text-gray-400">
                            Status Data
                        </p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                            {{ ucfirst($laporan->status) }}
                        </p>
                    </div>
                </div>
                @endif
            </div>

            {{-- Catatan laporan --}}
            @if ($laporan->description)
            <div
                class="rounded-lg border border-dashed border-gray-200 bg-gray-50 p-3.5 text-sm dark:border-gray-700 dark:bg-slate-700/80">
                <p class="mb-1 text-xs font-medium text-gray-500 dark:text-gray-400">
                    Catatan laporan
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-200">
                    {{ $laporan->description }}
                </p>
            </div>
            @endif
        </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>