<x-filament-widgets::widget>
    @php
        $laporan = $this->getLaporan();
        $summary = $this->summary;
        $records = $this->records;
        $isPaginated = $records instanceof \Illuminate\Pagination\LengthAwarePaginator;
    @endphp

    <div class="w-full">
        @if (! $laporan)
            <div class="flex flex-col items-center justify-center rounded-2xl border border-slate-200 bg-white py-14 text-center shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
                    <x-heroicon-o-document-magnifying-glass class="h-8 w-8" />
                </div>
                <h3 class="text-base font-semibold text-slate-900 dark:text-white">Tidak Ada Laporan Aktif</h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Belum ada data laporan indikator mutu terbaru untuk ditampilkan.</p>
            </div>
        @else
            {{-- ================= SATU CARD SECTION UTUH DENGAN WARNA TEGAS ================= --}}
            <div class="w-full overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">

                {{-- 1. HEADER DOKUMEN: SOLID NAVY FORMAL (TANPA GRADIEN) --}}
                <div class="border-b border-slate-700 bg-slate-900 px-6 py-5 text-white dark:bg-slate-950">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md">
                                <x-heroicon-o-clipboard-document-check class="h-7 w-7" />
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-xl font-bold tracking-tight text-white">
                                        Rekapitulasi Capaian Indikator Mutu Per Unit Kerja
                                    </h2>
                                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider {{ ($laporan->status ?? '') === 'complete' ? 'bg-emerald-500 text-white' : 'bg-blue-500 text-white' }}">
                                        {{ strtoupper($laporan->status ?? 'PROSES') }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-slate-300 sm:text-sm">
                                    <span class="font-semibold text-white">{{ $laporan->name }}</span>
                                    <span class="mx-2 text-slate-500">&bull;</span>
                                    Periode: 
                                    <span class="font-medium text-slate-200">
                                        {{ $laporan->assessment_period_start ? \Carbon\Carbon::parse($laporan->assessment_period_start)->translatedFormat('d M Y') : '-' }}
                                        s/d
                                        {{ $laporan->assessment_period_end ? \Carbon\Carbon::parse($laporan->assessment_period_end)->translatedFormat('d M Y') : '-' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Tombol Cetak Dokumen -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            <!-- Dropdown Pemilih Periode / Bulan Laporan -->
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-slate-300">Bulan:</span>
                                <select wire:model.live="selectedLaporanId"
                                        class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition [color-scheme:dark] hover:bg-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                    @foreach ($this->availableLaporans as $item)
                                        @php
                                            $bulan = $item->assessment_period_start ? \Carbon\Carbon::parse($item->assessment_period_start)->translatedFormat('F Y') : ($item->name ?? "Laporan #{$item->id}");
                                        @endphp
                                        <option value="{{ $item->id }}" class="bg-slate-800 text-white">
                                            {{ $bulan }} ({{ strtoupper($item->status ?? 'PROSES') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <a href="{{ route('print.unit-kerja-rekap', ['laporan_id' => $laporan->id, 'orientation' => 'landscape']) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400">
                                <x-heroicon-o-printer class="h-4 w-4" />
                                <span>Cetak Laporan</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- 2. TOOLBAR FILTER & SEARCH --}}
                <div class="border-b border-slate-200 bg-slate-50/80 px-6 py-3.5 dark:border-slate-800 dark:bg-slate-800/40">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <!-- Search Box -->
                        <div class="relative flex-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                <x-heroicon-o-magnifying-glass class="h-4 w-4" />
                            </div>
                            <input type="text"
                                   wire:model.live.debounce.300ms="search"
                                   placeholder="Cari unit kerja..."
                                   class="w-full rounded-lg border border-slate-300 bg-white py-1.5 pl-9 pr-4 text-sm text-slate-900 placeholder-slate-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-500" />
                        </div>

                        <!-- Dropdowns -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Filter Status Capaian -->
                            <select wire:model.live="filterLevel"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                <option value="all">Semua Status Capaian</option>
                                <option value="high">Capaian Tinggi (≥ 80%)</option>
                                <option value="medium">Capaian Sedang (50% - 79%)</option>
                                <option value="low">Capaian Rendah (< 50%)</option>
                                <option value="has_below">Ada Tidak Standar</option>
                                <option value="has_empty">Ada Belum Terisi</option>
                                <option value="all_standard">100% Standar Tercapai</option>
                            </select>

                            <!-- Pilihan Baris -->
                            <select wire:model.live="perPage"
                                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-sm transition focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                                <option value="10">10 Baris</option>
                                <option value="25">25 Baris</option>
                                <option value="50">50 Baris</option>
                                <option value="all">Semua Unit</option>
                            </select>

                            @if ($search !== '' || $filterLevel !== 'all')
                                <button type="button"
                                        wire:click="resetFilter"
                                        class="inline-flex items-center gap-1 rounded-lg border border-rose-200 bg-rose-50 px-2.5 py-1.5 text-xs font-bold text-rose-700 shadow-sm transition hover:bg-rose-100 dark:border-rose-900/60 dark:bg-rose-950/40 dark:text-rose-300">
                                    <x-heroicon-o-x-mark class="h-3.5 w-3.5" />
                                    <span>Reset</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- 3. TABEL SEMI-LAPORAN FORMAL (HEADER TEGAS SLATE-800 & FULL LEBAR) --}}
                <div class="w-full overflow-x-auto">
                    <table class="w-full min-w-full table-auto text-left text-sm">
                        <!-- HEADER TABEL DENGAN WARNA TEGAS & TEKS PUTIH TAJAM -->
                        <thead class="border-b-2 border-slate-300 bg-slate-100 text-xs font-bold uppercase tracking-wider text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <tr>
                                <th scope="col" class="w-14 px-3.5 py-3.5 text-center">No</th>

                                <th scope="col" class="cursor-pointer px-5 py-3.5 transition hover:bg-slate-200 dark:hover:bg-slate-700" wire:click="sortBy('unit_name')">
                                    <div class="flex items-center gap-1.5">
                                        <span>Unit Kerja</span>
                                        @if ($sortColumn === 'unit_name')
                                            <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @else
                                            <span class="text-slate-400">⇅</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-32 cursor-pointer px-3.5 py-3.5 text-center transition hover:bg-slate-200 dark:hover:bg-slate-700" wire:click="sortBy('total_count')">
                                    <div class="flex items-center justify-center gap-1">
                                        <span>Jml IMUT</span>
                                        @if ($sortColumn === 'total_count')
                                            <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-36 cursor-pointer px-3.5 py-3.5 text-center transition hover:bg-slate-200 dark:hover:bg-slate-700" wire:click="sortBy('meets_standard_count')">
                                    <div class="flex items-center justify-center gap-1 text-emerald-700 dark:text-emerald-300">
                                        <span>Standar</span>
                                        @if ($sortColumn === 'meets_standard_count')
                                            <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-36 cursor-pointer px-3.5 py-3.5 text-center transition hover:bg-slate-200 dark:hover:bg-slate-700" wire:click="sortBy('below_standard_count')">
                                    <div class="flex items-center justify-center gap-1 text-rose-700 dark:text-rose-300">
                                        <span>Tidak Standar</span>
                                        @if ($sortColumn === 'below_standard_count')
                                            <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-28 cursor-pointer px-3.5 py-3.5 text-center transition hover:bg-slate-200 dark:hover:bg-slate-700" wire:click="sortBy('empty_count')">
                                    <div class="flex items-center justify-center gap-1 text-amber-700 dark:text-amber-300">
                                        <span>Kosong</span>
                                        @if ($sortColumn === 'empty_count')
                                            <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-40 cursor-pointer px-4 py-3.5 text-center transition hover:bg-slate-200 dark:hover:bg-slate-700" wire:click="sortBy('standard_percentage')">
                                    <div class="flex items-center justify-center gap-1 text-blue-700 dark:text-blue-300">
                                        <span>Persentase (%)</span>
                                        @if ($sortColumn === 'standard_percentage')
                                            <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @else
                                            <span class="text-slate-400">⇅</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-24 px-3.5 py-3.5 text-center">Aksi</th>
                            </tr>
                        </thead>

                        <!-- BODY TABEL: ZEBRA STRIPING & WARNA JELAS -->
                        <tbody class="divide-y divide-slate-200 bg-white font-medium dark:divide-slate-800 dark:bg-slate-900">
                            @forelse($records as $index => $row)
                                @php
                                    $rowNumber = $isPaginated
                                        ? ($records->currentPage() - 1) * $records->perPage() + $loop->iteration
                                        : $loop->iteration;
                                    $pct = (float) ($row->standard_percentage ?? 0);
                                @endphp
                                <tr class="transition even:bg-slate-50/70 hover:bg-blue-50/60 dark:even:bg-slate-800/40 dark:hover:bg-slate-800/80">
                                    <!-- No -->
                                    <td class="px-3.5 py-3 text-center font-mono text-xs text-slate-500 dark:text-slate-400">
                                        {{ $rowNumber }}
                                    </td>
                                    
                                    <!-- Unit Kerja -->
                                    <td class="px-5 py-3 text-slate-900 dark:text-white">
                                        <div class="font-bold text-slate-900 dark:text-white">{{ $row->unit_name }}</div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                            Pengisian: {{ number_format($row->filled_count ?? 0) }} dari {{ number_format($row->total_count ?? 0) }} indikator
                                        </div>
                                    </td>

                                    <!-- Jml Indikator -->
                                    <td class="px-3.5 py-3 text-center font-mono text-sm font-semibold text-slate-700 dark:text-slate-300">
                                        {{ number_format($row->total_count ?? 0) }}
                                    </td>

                                    <!-- Mencapai Standar -->
                                    <td class="px-3.5 py-3 text-center">
                                        <span class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2.5 py-1 font-mono text-xs font-bold text-emerald-700 ring-1 ring-inset ring-emerald-600/30 dark:bg-emerald-950/50 dark:text-emerald-400 dark:ring-emerald-500/40">
                                            <x-heroicon-m-check-circle class="h-3.5 w-3.5 text-emerald-600 dark:text-emerald-400" />
                                            {{ number_format($row->meets_standard_count ?? 0) }}
                                        </span>
                                    </td>

                                    <!-- Tidak Standar -->
                                    <td class="px-3.5 py-3 text-center">
                                        @if (($row->below_standard_count ?? 0) > 0)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-rose-50 px-2.5 py-1 font-mono text-xs font-bold text-rose-700 ring-1 ring-inset ring-rose-600/30 dark:bg-rose-950/50 dark:text-rose-400 dark:ring-rose-500/40">
                                                <x-heroicon-m-x-circle class="h-3.5 w-3.5 text-rose-600 dark:text-rose-400" />
                                                {{ number_format($row->below_standard_count ?? 0) }}
                                            </span>
                                        @else
                                            <span class="font-mono text-xs text-slate-400 dark:text-slate-500">0</span>
                                        @endif
                                    </td>

                                    <!-- Kosong -->
                                    <td class="px-3.5 py-3 text-center">
                                        @if (($row->empty_count ?? 0) > 0)
                                            <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2.5 py-1 font-mono text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-600/30 dark:bg-amber-950/50 dark:text-amber-400 dark:ring-amber-500/40">
                                                <x-heroicon-m-exclamation-triangle class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400" />
                                                {{ number_format($row->empty_count ?? 0) }}
                                            </span>
                                        @else
                                            <span class="font-mono text-xs text-slate-400 dark:text-slate-500">0</span>
                                        @endif
                                    </td>

                                    <!-- Persentase (%) -->
                                    <td class="px-4 py-3 text-center">
                                        <span class="rounded-full px-3 py-1 font-mono text-xs font-bold shadow-sm {{ $pct >= 80 ? 'bg-emerald-100 text-emerald-800 ring-1 ring-emerald-600/30 dark:bg-emerald-950 dark:text-emerald-300' : ($pct >= 50 ? 'bg-amber-100 text-amber-800 ring-1 ring-amber-600/30 dark:bg-amber-950 dark:text-amber-300' : 'bg-rose-100 text-rose-800 ring-1 ring-rose-600/30 dark:bg-rose-950 dark:text-rose-300') }}">
                                            {{ number_format($pct, 1) }}%
                                        </span>
                                    </td>

                                    <!-- Aksi Detail -->
                                    <td class="px-3.5 py-3 text-center">
                                        <a href="{{ \App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport::getUrl(['laporan_id' => $row->laporan_imut_id, 'unit_kerja_id' => $row->unit_kerja_id]) }}"
                                           class="inline-flex items-center gap-1 rounded-md border border-slate-300 bg-white px-2.5 py-1 text-xs font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700 dark:hover:text-white"
                                           title="Lihat Detail Indikator">
                                            <x-heroicon-o-eye class="h-3.5 w-3.5 text-blue-600 dark:text-blue-400" />
                                            <span>Detail</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-14 text-center text-slate-500 dark:text-slate-400">
                                        <div class="flex flex-col items-center justify-center">
                                            <x-heroicon-o-document-magnifying-glass class="h-8 w-8 text-slate-400" />
                                            <p class="mt-2 text-sm font-semibold">Tidak ada unit kerja yang sesuai filter.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                @if ($isPaginated && $records->hasPages())
                    <div class="border-t border-slate-200 bg-white px-6 py-3 dark:border-slate-800 dark:bg-slate-900">
                        {{ $records->links() }}
                    </div>
                @endif

                {{-- 4. EXECUTIVE SUMMARY (RANGKUMAN PALING BAWAH DENGAN WARNA JELAS) --}}
                <div class="border-t border-slate-200 bg-slate-50/90 p-6 dark:border-slate-800 dark:bg-slate-800/40">
                    <div class="mb-4 flex items-center gap-2">
                        <x-heroicon-s-chart-pie class="h-4 w-4 text-blue-600 dark:text-blue-400" />
                        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-800 dark:text-slate-200">
                            Ikhtisar & Rangkuman Keseluruhan Laporan
                        </h4>
                    </div>

                    <div class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 lg:grid-cols-6">
                        <!-- Total Unit -->
                        <div class="rounded-xl border border-slate-300 bg-white p-4 text-center shadow-sm dark:border-slate-700 dark:bg-slate-800">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Total Unit</span>
                            <div class="mt-1 font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($summary['total_units']) }}</div>
                        </div>

                        <!-- Jml Indikator -->
                        <div class="rounded-xl border border-slate-300 bg-white p-4 text-center shadow-sm dark:border-slate-700 dark:bg-slate-800">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">Jml Indikator</span>
                            <div class="mt-1 font-mono text-2xl font-bold text-slate-900 dark:text-white">{{ number_format($summary['total_indicators']) }}</div>
                        </div>

                        <!-- Mencapai Standar -->
                        <div class="rounded-xl border border-emerald-300 bg-emerald-50/80 p-4 text-center shadow-sm dark:border-emerald-800 dark:bg-emerald-950/40">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-400">Mencapai Standar</span>
                            <div class="mt-1 font-mono text-2xl font-bold text-emerald-700 dark:text-emerald-400">{{ number_format($summary['total_standard']) }}</div>
                        </div>

                        <!-- Tidak Standar -->
                        <div class="rounded-xl border border-rose-300 bg-rose-50/80 p-4 text-center shadow-sm dark:border-rose-800 dark:bg-rose-950/40">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-rose-800 dark:text-rose-400">Tidak Standar</span>
                            <div class="mt-1 font-mono text-2xl font-bold text-rose-700 dark:text-rose-400">{{ number_format($summary['total_below']) }}</div>
                        </div>

                        <!-- Kosong / Belum -->
                        <div class="rounded-xl border border-amber-300 bg-amber-50/80 p-4 text-center shadow-sm dark:border-amber-800 dark:bg-amber-950/40">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-400">Kosong / Belum</span>
                            <div class="mt-1 font-mono text-2xl font-bold text-amber-700 dark:text-amber-400">{{ number_format($summary['total_empty']) }}</div>
                        </div>

                        <!-- Rata-rata Capaian -->
                        <div class="rounded-xl border border-blue-300 bg-blue-50/80 p-4 text-center shadow-sm dark:border-blue-800 dark:bg-blue-950/40">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-800 dark:text-blue-400">Rata-rata Capaian</span>
                            <div class="mt-1 font-mono text-2xl font-bold text-blue-700 dark:text-blue-400">{{ number_format($summary['overall_percentage'], 1) }}%</div>
                        </div>
                    </div>
                </div>

            </div>
        @endif
    </div>
</x-filament-widgets::widget>
