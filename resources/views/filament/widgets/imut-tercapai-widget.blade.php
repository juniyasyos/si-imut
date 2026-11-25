<x-filament-widgets::widget>
    @php
        $laporan = $this->getLaporan();
        $summary = $this->summary;
        $records = $this->records;
        $isPaginated = $records instanceof \Illuminate\Pagination\LengthAwarePaginator;
    @endphp

    <div class="space-y-6">
        @if (! $laporan)
            <div class="rounded-xl border border-gray-200 bg-white p-12 text-center shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 text-amber-500 dark:bg-amber-950/40">
                    <x-heroicon-o-document-magnifying-glass class="h-8 w-8" />
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Tidak Ada Laporan Aktif</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Belum ada data laporan indikator mutu terbaru untuk ditampilkan.</p>
            </div>
        @else
            {{-- ================= HEADER LAPORAN & SUMMARY ================= --}}
            <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <!-- Header Title Bar -->
                <div class="border-b border-slate-200 bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-950 px-6 py-5 text-white dark:border-slate-800">
                    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center rounded-md bg-blue-500/20 px-2.5 py-0.5 text-xs font-semibold tracking-wide text-blue-200 ring-1 ring-inset ring-blue-400/30">
                                    LAPORAN REKAPITULASI
                                </span>
                                <span class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-medium {{ ($laporan->status ?? '') === 'complete' ? 'bg-emerald-500/20 text-emerald-300 ring-1 ring-inset ring-emerald-400/30' : 'bg-amber-500/20 text-amber-300 ring-1 ring-inset ring-amber-400/30' }}">
                                    {{ strtoupper($laporan->status ?? 'PROSES') }}
                                </span>
                            </div>
                            <h2 class="mt-2 text-xl font-bold tracking-tight text-white md:text-2xl">
                                Rekapitulasi Capaian Indikator Mutu Unit Kerja
                            </h2>
                            <p class="mt-1 text-xs text-slate-300 md:text-sm">
                                {{ $laporan->name }} &bull; Periode: 
                                <span class="font-medium text-white">
                                    {{ $laporan->assessment_period_start ? \Carbon\Carbon::parse($laporan->assessment_period_start)->translatedFormat('d M Y') : '-' }}
                                    s/d
                                    {{ $laporan->assessment_period_end ? \Carbon\Carbon::parse($laporan->assessment_period_end)->translatedFormat('d M Y') : '-' }}
                                </span>
                            </p>
                        </div>

                        <!-- Print Action Buttons -->
                        <div class="flex flex-wrap items-center gap-2">
                            <a href="{{ route('print.unit-kerja-rekap', ['laporan_id' => $laporan->id, 'orientation' => 'landscape']) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-3.5 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <x-heroicon-o-printer class="h-4 w-4" />
                                <span>Cetak Landscape</span>
                            </a>
                            <a href="{{ route('print.unit-kerja-rekap', ['laporan_id' => $laporan->id, 'orientation' => 'portrait']) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1.5 rounded-lg border border-slate-600 bg-slate-800/80 px-3.5 py-2 text-xs font-semibold text-slate-200 shadow-sm transition hover:bg-slate-700 hover:text-white focus:outline-none">
                                <x-heroicon-o-document-text class="h-4 w-4" />
                                <span>Cetak Portrait</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Executive Summary Cards -->
                <div class="grid grid-cols-2 divide-y divide-slate-100 bg-slate-50/50 sm:grid-cols-3 sm:divide-y-0 sm:divide-x md:grid-cols-6 dark:divide-slate-800 dark:bg-slate-900/50">
                    <div class="p-4 text-center">
                        <span class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Unit</span>
                        <div class="mt-1 text-2xl font-bold text-slate-800 dark:text-slate-100 font-mono">{{ number_format($summary['total_units']) }}</div>
                    </div>
                    <div class="p-4 text-center">
                        <span class="text-xs font-medium uppercase tracking-wider text-slate-500 dark:text-slate-400">Jml Indikator</span>
                        <div class="mt-1 text-2xl font-bold text-slate-800 dark:text-slate-100 font-mono">{{ number_format($summary['total_indicators']) }}</div>
                    </div>
                    <div class="p-4 text-center">
                        <span class="text-xs font-medium uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Mencapai Standar</span>
                        <div class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400 font-mono">{{ number_format($summary['total_standard']) }}</div>
                    </div>
                    <div class="p-4 text-center">
                        <span class="text-xs font-medium uppercase tracking-wider text-rose-600 dark:text-rose-400">Tidak Standar</span>
                        <div class="mt-1 text-2xl font-bold text-rose-600 dark:text-rose-400 font-mono">{{ number_format($summary['total_below']) }}</div>
                    </div>
                    <div class="p-4 text-center">
                        <span class="text-xs font-medium uppercase tracking-wider text-amber-600 dark:text-amber-400">Kosong / Belum</span>
                        <div class="mt-1 text-2xl font-bold text-amber-600 dark:text-amber-400 font-mono">{{ number_format($summary['total_empty']) }}</div>
                    </div>
                    <div class="p-4 text-center">
                        <span class="text-xs font-medium uppercase tracking-wider text-blue-600 dark:text-blue-400">Akumulasi Capaian</span>
                        <div class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400 font-mono">{{ number_format($summary['overall_percentage'], 1) }}%</div>
                    </div>
                </div>
            </div>

            {{-- ================= TOOLBAR & FILTER ================= --}}
            <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm md:flex-row md:items-center md:justify-between dark:border-slate-800 dark:bg-slate-900">
                <!-- Search Box -->
                <div class="relative flex-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                        <x-heroicon-o-magnifying-glass class="h-4 w-4" />
                    </div>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Cari nama unit kerja..."
                           class="w-full rounded-lg border border-slate-300 bg-white py-2 pl-9 pr-4 text-sm text-slate-800 placeholder-slate-400 transition focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder-slate-500" />
                </div>

                <!-- Dropdown Filters -->
                <div class="flex flex-wrap items-center gap-2">
                    <!-- Status Filter -->
                    <select wire:model.live="filterLevel"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 transition focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        <option value="all">Semua Capaian</option>
                        <option value="high">Capaian Tinggi (≥ 80%)</option>
                        <option value="medium">Capaian Sedang (50% - 79%)</option>
                        <option value="low">Capaian Rendah (< 50%)</option>
                        <option value="has_below">Ada Tidak Standar</option>
                        <option value="has_empty">Ada Belum Terisi</option>
                        <option value="all_standard">100% Standar Tercapai</option>
                    </select>

                    <!-- Per Page -->
                    <select wire:model.live="perPage"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-medium text-slate-700 transition focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        <option value="10">10 Baris</option>
                        <option value="25">25 Baris</option>
                        <option value="50">50 Baris</option>
                        <option value="all">Semua Unit</option>
                    </select>

                    @if ($search !== '' || $filterLevel !== 'all')
                        <button type="button"
                                wire:click="resetFilter"
                                class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-2 text-xs font-medium text-rose-600 transition hover:bg-rose-100 dark:border-rose-900/50 dark:bg-rose-950/40 dark:text-rose-400">
                            <x-heroicon-o-x-mark class="h-3.5 w-3.5" />
                            <span>Reset</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- ================= TABEL FORMAL SEMI-LAPORAN ================= --}}
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <table class="min-w-full divide-y divide-slate-200 text-left text-sm dark:divide-slate-800">
                    <thead class="bg-slate-100/90 text-xs font-bold uppercase tracking-wider text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                        <tr>
                            <th scope="col" class="w-12 px-3 py-3.5 text-center">No</th>
                            
                            <th scope="col" class="cursor-pointer px-4 py-3.5 transition hover:bg-slate-200/70 dark:hover:bg-slate-700/60" wire:click="sortBy('unit_name')">
                                <div class="flex items-center gap-1">
                                    <span>Unit Kerja</span>
                                    @if ($sortColumn === 'unit_name')
                                        <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-600">⇅</span>
                                    @endif
                                </div>
                            </th>

                            <th scope="col" class="w-28 cursor-pointer px-3 py-3.5 text-center transition hover:bg-slate-200/70 dark:hover:bg-slate-700/60" wire:click="sortBy('total_count')">
                                <div class="flex items-center justify-center gap-1">
                                    <span>Jml Indikator</span>
                                    @if ($sortColumn === 'total_count')
                                        <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </div>
                            </th>

                            <th scope="col" class="w-32 cursor-pointer px-3 py-3.5 text-center transition hover:bg-slate-200/70 dark:hover:bg-slate-700/60" wire:click="sortBy('meets_standard_count')">
                                <div class="flex items-center justify-center gap-1">
                                    <span>Standar</span>
                                    @if ($sortColumn === 'meets_standard_count')
                                        <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </div>
                            </th>

                            <th scope="col" class="w-32 cursor-pointer px-3 py-3.5 text-center transition hover:bg-slate-200/70 dark:hover:bg-slate-700/60" wire:click="sortBy('below_standard_count')">
                                <div class="flex items-center justify-center gap-1">
                                    <span>Tidak Standar</span>
                                    @if ($sortColumn === 'below_standard_count')
                                        <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </div>
                            </th>

                            <th scope="col" class="w-24 cursor-pointer px-3 py-3.5 text-center transition hover:bg-slate-200/70 dark:hover:bg-slate-700/60" wire:click="sortBy('empty_count')">
                                <div class="flex items-center justify-center gap-1">
                                    <span>Kosong</span>
                                    @if ($sortColumn === 'empty_count')
                                        <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </div>
                            </th>

                            <th scope="col" class="w-36 cursor-pointer px-4 py-3.5 text-center transition hover:bg-slate-200/70 dark:hover:bg-slate-700/60" wire:click="sortBy('standard_percentage')">
                                <div class="flex items-center justify-center gap-1">
                                    <span>Persentase</span>
                                    @if ($sortColumn === 'standard_percentage')
                                        <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                    @else
                                        <span class="text-slate-300 dark:text-slate-600">⇅</span>
                                    @endif
                                </div>
                            </th>

                            <th scope="col" class="w-24 px-3 py-3.5 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white font-medium dark:divide-slate-800/80 dark:bg-slate-900">
                        @forelse($records as $index => $row)
                            @php
                                $rowNumber = $isPaginated
                                    ? ($records->currentPage() - 1) * $records->perPage() + $loop->iteration
                                    : $loop->iteration;
                                $pct = (float) ($row->standard_percentage ?? 0);
                            @endphp
                            <tr class="transition hover:bg-slate-50/80 dark:hover:bg-slate-800/50">
                                <td class="px-3 py-3 text-center text-xs text-slate-500 font-mono dark:text-slate-400">
                                    {{ $rowNumber }}
                                </td>
                                
                                <td class="px-4 py-3 text-slate-900 dark:text-slate-100">
                                    <div class="font-semibold">{{ $row->unit_name }}</div>
                                    <div class="text-[11px] text-slate-400 dark:text-slate-500">
                                        Terisi: {{ number_format($row->filled_count ?? 0) }} / {{ number_format($row->total_count ?? 0) }} indikator
                                    </div>
                                </td>

                                <td class="px-3 py-3 text-center text-slate-700 font-mono text-sm dark:text-slate-300">
                                    {{ number_format($row->total_count ?? 0) }}
                                </td>

                                <td class="px-3 py-3 text-center">
                                    <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 font-mono dark:bg-emerald-950/40 dark:text-emerald-400">
                                        <x-heroicon-m-check-circle class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" />
                                        {{ number_format($row->meets_standard_count ?? 0) }}
                                    </span>
                                </td>

                                <td class="px-3 py-3 text-center">
                                    @if (($row->below_standard_count ?? 0) > 0)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700 font-mono dark:bg-rose-950/40 dark:text-rose-400">
                                            <x-heroicon-m-x-circle class="h-3.5 w-3.5 text-rose-600 dark:text-rose-400" />
                                            {{ number_format($row->below_standard_count ?? 0) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 font-mono">0</span>
                                    @endif
                                </td>

                                <td class="px-3 py-3 text-center">
                                    @if (($row->empty_count ?? 0) > 0)
                                        <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 font-mono dark:bg-amber-950/40 dark:text-amber-400">
                                            <x-heroicon-m-exclamation-triangle class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />
                                            {{ number_format($row->empty_count ?? 0) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 font-mono">0</span>
                                    @endif
                                </td>

                                <td class="px-4 py-3 text-center">
                                    <div class="inline-flex items-center gap-1.5">
                                        <span class="rounded-full px-2.5 py-0.5 text-xs font-bold font-mono {{ $pct >= 80 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : ($pct >= 50 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300') }}">
                                            {{ number_format($pct, 1) }}%
                                        </span>
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono mt-0.5">
                                        {{ $row->meets_standard_count ?? 0 }}/{{ $row->total_count ?? 0 }}
                                    </div>
                                </td>

                                <td class="px-3 py-3 text-center">
                                    <a href="{{ \App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport::getUrl(['laporan_id' => $row->laporan_imut_id, 'unit_kerja_id' => $row->unit_kerja_id]) }}"
                                       class="inline-flex items-center gap-1 rounded-md border border-slate-200 bg-white px-2 py-1 text-xs font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 dark:hover:text-white"
                                       title="Lihat Rincian Indikator">
                                        <x-heroicon-o-eye class="h-3.5 w-3.5 text-blue-500" />
                                        <span>Detail</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-slate-500 dark:text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <x-heroicon-o-document-magnifying-glass class="h-8 w-8 text-slate-300 dark:text-slate-600" />
                                        <p class="mt-2 text-sm font-medium">Tidak ada data unit kerja yang sesuai filter.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="border-t-2 border-slate-300 bg-slate-100/90 text-xs font-bold text-slate-800 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                        <tr>
                            <td colspan="2" class="px-4 py-3 text-center uppercase tracking-wider">
                                Total Keseluruhan
                            </td>
                            <td class="px-3 py-3 text-center font-mono">
                                {{ number_format($summary['total_indicators']) }}
                            </td>
                            <td class="px-3 py-3 text-center text-emerald-700 font-mono dark:text-emerald-400">
                                {{ number_format($summary['total_standard']) }}
                            </td>
                            <td class="px-3 py-3 text-center text-rose-700 font-mono dark:text-rose-400">
                                {{ number_format($summary['total_below']) }}
                            </td>
                            <td class="px-3 py-3 text-center text-amber-700 font-mono dark:text-amber-400">
                                {{ number_format($summary['total_empty']) }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono">
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-bold {{ $summary['overall_percentage'] >= 80 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : ($summary['overall_percentage'] >= 50 ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-rose-100 text-rose-800 dark:bg-rose-900/40 dark:text-rose-300') }}">
                                    {{ number_format($summary['overall_percentage'], 1) }}%
                                </span>
                            </td>
                            <td class="px-3 py-3"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- ================= PAGINATION ================= --}}
            @if ($isPaginated && $records->hasPages())
                <div class="pt-2">
                    {{ $records->links() }}
                </div>
            @endif
        @endif
    </div>
</x-filament-widgets::widget>
