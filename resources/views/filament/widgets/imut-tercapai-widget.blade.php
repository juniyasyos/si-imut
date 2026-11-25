<x-filament-widgets::widget>
    @php
        $laporan = $this->getLaporan();
        $summary = $this->summary;
        $records = $this->records;
        $isPaginated = $records instanceof \Illuminate\Pagination\LengthAwarePaginator;
    @endphp

    <x-filament::section class="w-full">
        @if (! $laporan)
            <div class="flex flex-col items-center justify-center rounded-2xl border border-gray-200 bg-white py-14 text-center shadow-sm dark:border-slate-700 dark:bg-slate-900">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400">
                    @svg('heroicon-o-document-magnifying-glass', 'h-8 w-8')
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Tidak Ada Laporan Aktif</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Belum ada data laporan indikator mutu terbaru untuk ditampilkan.</p>
            </div>
        @else
            {{-- ================= SATU CARD SECTION UTUH DENGAN WARNA TEGAS ================= --}}
            <div class="w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">

                {{-- 1. HEADER DOKUMEN: SOLID NAVY FORMAL --}}
                <div class="border-b border-slate-700 bg-slate-900 px-6 py-5 text-white" style="background-color: #0f172a; color: #ffffff;">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white shadow-md" style="background-color: #2563eb; color: #ffffff;">
                                @svg('heroicon-o-clipboard-document-check', 'h-7 w-7')
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-xl font-bold tracking-tight text-white" style="color: #ffffff;">
                                        Rekapitulasi Capaian Indikator Mutu Per Unit Kerja
                                    </h2>
                                    @php
                                        $isComplete = ($laporan->status ?? '') === 'complete';
                                    @endphp
                                    <span class="inline-flex items-center rounded-md px-2.5 py-0.5 text-xs font-bold uppercase tracking-wider text-white {{ $isComplete ? 'bg-green-600' : 'bg-blue-600' }}"
                                          style="background-color: {{ $isComplete ? '#16a34a' : '#2563eb' }}; color: #ffffff;">
                                        {{ strtoupper($laporan->status ?? 'PROSES') }}
                                    </span>
                                </div>
                                <p class="mt-1 text-xs text-slate-300 sm:text-sm" style="color: #cbd5e1;">
                                    <span class="font-semibold text-white" style="color: #ffffff;">{{ $laporan->name }}</span>
                                    <span class="mx-2 text-slate-500">&bull;</span>
                                    Periode:
                                    <span class="font-medium text-slate-200" style="color: #e2e8f0;">
                                        {{ $laporan->assessment_period_start ? \Carbon\Carbon::parse($laporan->assessment_period_start)->translatedFormat('d M Y') : '-' }}
                                        s/d
                                        {{ $laporan->assessment_period_end ? \Carbon\Carbon::parse($laporan->assessment_period_end)->translatedFormat('d M Y') : '-' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Tombol Cetak & Pilih Bulan -->
                        <div class="flex flex-wrap items-center gap-2.5">
                            <!-- Dropdown Pemilih Periode / Bulan Laporan -->
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-slate-300" style="color: #cbd5e1;">Bulan:</span>
                                <select wire:model.live="selectedLaporanId"
                                        class="rounded-lg border border-slate-600 bg-slate-800 px-3 py-1.5 text-xs font-bold text-white shadow-sm transition [color-scheme:dark] hover:bg-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                        style="background-color: #1e293b; color: #ffffff; border-color: #475569;">
                                    @foreach ($this->availableLaporans as $item)
                                        @php
                                            $bulan = $item->assessment_period_start ? \Carbon\Carbon::parse($item->assessment_period_start)->translatedFormat('F Y') : ($item->name ?? "Laporan #{$item->id}");
                                        @endphp
                                        <option value="{{ $item->id }}" class="bg-slate-800 text-white" style="background-color: #1e293b; color: #ffffff;">
                                            {{ $bulan }} ({{ strtoupper($item->status ?? 'PROSES') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <a href="{{ route('print.unit-kerja-rekap', ['laporan_id' => $laporan->id, 'orientation' => 'landscape']) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 px-4 py-2 text-xs font-bold text-white shadow transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400"
                               style="background-color: #2563eb; color: #ffffff;">
                                @svg('heroicon-o-printer', 'h-4 w-4')
                                <span>Cetak Laporan</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- 2. TOOLBAR FILTER & SEARCH --}}
                <div class="border-b border-gray-200 bg-gray-50 px-6 py-3.5 dark:border-slate-700 dark:bg-slate-800/50">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <!-- Search Box -->
                        <div class="relative flex-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                                @svg('heroicon-o-magnifying-glass', 'h-4 w-4')
                            </div>
                            <input type="text"
                                   wire:model.live.debounce.300ms="search"
                                   placeholder="Cari unit kerja..."
                                   class="w-full rounded-lg border border-gray-300 bg-white py-1.5 pl-9 pr-4 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-gray-500" />
                        </div>

                        <!-- Dropdowns -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Filter Status Capaian -->
                            <select wire:model.live="filterLevel"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                                <option value="all">Semua Status Capaian</option>
                                <option value="high">Capaian Tinggi (≥ 80%)</option>
                                <option value="medium">Capaian Sedang (50% - 79%)</option>
                                <option value="low">Capaian Rendah (&lt; 50%)</option>
                                <option value="has_below">Ada Tidak Standar</option>
                                <option value="has_empty">Ada Belum Terisi</option>
                                <option value="all_standard">100% Standar Tercapai</option>
                            </select>

                            <!-- Pilihan Baris -->
                            <select wire:model.live="perPage"
                                    class="rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm transition focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200">
                                <option value="10">10 Baris</option>
                                <option value="25">25 Baris</option>
                                <option value="50">50 Baris</option>
                                <option value="all">Semua Unit</option>
                            </select>

                            @if ($search !== '' || $filterLevel !== 'all')
                                <button type="button"
                                        wire:click="resetFilter"
                                        class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-bold text-red-700 shadow-sm transition hover:bg-red-100 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300">
                                    @svg('heroicon-o-x-mark', 'h-3.5 w-3.5')
                                    <span>Reset</span>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- 3. TABEL SEMI-LAPORAN FORMAL (HEADER TEGAS SLATE-800 & TEKS PUTIH TAJAM) --}}
                <div class="w-full overflow-x-auto">
                    <table class="w-full min-w-full table-auto text-left text-sm">
                        <thead class="border-b-2 border-slate-700 bg-slate-800 text-xs font-bold uppercase tracking-wider text-white"
                               style="background-color: #1e293b; color: #ffffff;">
                            <tr>
                                <th scope="col" class="w-14 px-3.5 py-3.5 text-center text-white" style="color: #ffffff;">No</th>

                                <th scope="col" class="cursor-pointer px-5 py-3.5 text-white transition hover:bg-slate-700" style="color: #ffffff;" wire:click="sortBy('unit_name')">
                                    <div class="flex items-center gap-1.5">
                                        <span>Unit Kerja</span>
                                        @if ($sortColumn === 'unit_name')
                                            <span class="text-blue-400" style="color: #60a5fa;">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @else
                                            <span class="text-slate-400" style="color: #94a3b8;">⇅</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-32 cursor-pointer px-3.5 py-3.5 text-center text-white transition hover:bg-slate-700" style="color: #ffffff;" wire:click="sortBy('total_count')">
                                    <div class="flex items-center justify-center gap-1">
                                        <span>Jml IMUT</span>
                                        @if ($sortColumn === 'total_count')
                                            <span class="text-blue-400" style="color: #60a5fa;">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-36 cursor-pointer px-3.5 py-3.5 text-center transition hover:bg-slate-700" wire:click="sortBy('meets_standard_count')">
                                    <div class="flex items-center justify-center gap-1 text-green-300" style="color: #86efac;">
                                        <span>Standar</span>
                                        @if ($sortColumn === 'meets_standard_count')
                                            <span class="text-blue-400" style="color: #60a5fa;">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-36 cursor-pointer px-3.5 py-3.5 text-center transition hover:bg-slate-700" wire:click="sortBy('below_standard_count')">
                                    <div class="flex items-center justify-center gap-1 text-red-300" style="color: #fca5a5;">
                                        <span>Tidak Standar</span>
                                        @if ($sortColumn === 'below_standard_count')
                                            <span class="text-blue-400" style="color: #60a5fa;">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-28 cursor-pointer px-3.5 py-3.5 text-center transition hover:bg-slate-700" wire:click="sortBy('empty_count')">
                                    <div class="flex items-center justify-center gap-1 text-amber-300" style="color: #fcd34d;">
                                        <span>Kosong</span>
                                        @if ($sortColumn === 'empty_count')
                                            <span class="text-blue-400" style="color: #60a5fa;">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-40 cursor-pointer px-4 py-3.5 text-center transition hover:bg-slate-700" wire:click="sortBy('standard_percentage')">
                                    <div class="flex items-center justify-center gap-1 text-blue-300" style="color: #93c5fd;">
                                        <span>Persentase (%)</span>
                                        @if ($sortColumn === 'standard_percentage')
                                            <span class="text-blue-400" style="color: #60a5fa;">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @else
                                            <span class="text-slate-400" style="color: #94a3b8;">⇅</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-24 px-3.5 py-3.5 text-center text-white" style="color: #ffffff;">Aksi</th>
                            </tr>
                        </thead>

                        <!-- BODY TABEL: ZEBRA STRIPING & WARNA JELAS -->
                        <tbody class="divide-y divide-gray-200 bg-white font-medium dark:divide-slate-800 dark:bg-slate-900">
                            @forelse($records as $index => $row)
                                @php
                                    $rowNumber = $isPaginated
                                        ? ($records->currentPage() - 1) * $records->perPage() + $loop->iteration
                                        : $loop->iteration;
                                    $pct = (float) ($row->standard_percentage ?? 0);
                                @endphp
                                <tr class="transition hover:bg-blue-50/70 dark:hover:bg-slate-800/80 {{ $loop->even ? 'bg-gray-50 dark:bg-slate-800/40' : '' }}">
                                    <!-- No -->
                                    <td class="px-3.5 py-3 text-center font-mono text-xs text-gray-500 dark:text-gray-400">
                                        {{ $rowNumber }}
                                    </td>

                                    <!-- Unit Kerja -->
                                    <td class="px-5 py-3 text-gray-900 dark:text-white">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $row->unit_name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            Pengisian: {{ number_format($row->filled_count ?? 0) }} dari {{ number_format($row->total_count ?? 0) }} indikator
                                        </div>
                                    </td>

                                    <!-- Jml Indikator -->
                                    <td class="px-3.5 py-3 text-center font-mono text-sm font-semibold text-gray-700 dark:text-gray-300">
                                        {{ number_format($row->total_count ?? 0) }}
                                    </td>

                                    <!-- Mencapai Standar -->
                                    <td class="px-3.5 py-3 text-center">
                                        <span style="--c-50:var(--success-50);--c-400:var(--success-400);--c-600:var(--success-600);"
                                              class="fi-badge inline-flex items-center gap-1 rounded-md px-2.5 py-1 font-mono text-xs font-bold ring-1 ring-inset fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/20 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30">
                                            @svg('heroicon-m-check-circle', 'h-3.5 w-3.5')
                                            {{ number_format($row->meets_standard_count ?? 0) }}
                                        </span>
                                    </td>

                                    <!-- Tidak Standar -->
                                    <td class="px-3.5 py-3 text-center">
                                        @if (($row->below_standard_count ?? 0) > 0)
                                            <span style="--c-50:var(--danger-50);--c-400:var(--danger-400);--c-600:var(--danger-600);"
                                                  class="fi-badge inline-flex items-center gap-1 rounded-md px-2.5 py-1 font-mono text-xs font-bold ring-1 ring-inset fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/20 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30">
                                                @svg('heroicon-m-x-circle', 'h-3.5 w-3.5')
                                                {{ number_format($row->below_standard_count ?? 0) }}
                                            </span>
                                        @else
                                            <span class="font-mono text-xs text-gray-400 dark:text-gray-500">0</span>
                                        @endif
                                    </td>

                                    <!-- Kosong -->
                                    <td class="px-3.5 py-3 text-center">
                                        @if (($row->empty_count ?? 0) > 0)
                                            <span style="--c-50:var(--warning-50);--c-400:var(--warning-400);--c-600:var(--warning-600);"
                                                  class="fi-badge inline-flex items-center gap-1 rounded-md px-2.5 py-1 font-mono text-xs font-bold ring-1 ring-inset fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/20 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30">
                                                @svg('heroicon-m-exclamation-triangle', 'h-3.5 w-3.5')
                                                {{ number_format($row->empty_count ?? 0) }}
                                            </span>
                                        @else
                                            <span class="font-mono text-xs text-gray-400 dark:text-gray-500">0</span>
                                        @endif
                                    </td>

                                    <!-- Persentase (%) -->
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $pctVar = $pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                                        @endphp
                                        <span style="--c-50:var(--{{ $pctVar }}-50);--c-400:var(--{{ $pctVar }}-400);--c-600:var(--{{ $pctVar }}-600);"
                                              class="fi-badge inline-flex items-center rounded-full px-3 py-1 font-mono text-xs font-bold shadow-sm ring-1 ring-inset fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/20 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30">
                                            {{ number_format($pct, 1) }}%
                                        </span>
                                    </td>

                                    <!-- Aksi Detail -->
                                    <td class="px-3.5 py-3 text-center">
                                        <a href="{{ \App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport::getUrl(['laporan_id' => $row->laporan_imut_id, 'unit_kerja_id' => $row->unit_kerja_id]) }}"
                                           class="inline-flex items-center gap-1 rounded-md border border-gray-300 bg-white px-2.5 py-1 text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700 dark:hover:text-white"
                                           title="Lihat Detail Indikator">
                                            @svg('heroicon-o-eye', 'h-3.5 w-3.5 text-blue-600 dark:text-blue-400')
                                            <span>Detail</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-4 py-14 text-center text-gray-500 dark:text-gray-400">
                                        <div class="flex flex-col items-center justify-center">
                                            @svg('heroicon-o-document-magnifying-glass', 'h-8 w-8 text-gray-400')
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
                    <div class="border-t border-gray-200 bg-white px-6 py-3 dark:border-slate-800 dark:bg-slate-900">
                        {{ $records->links() }}
                    </div>
                @endif

                {{-- 4. EXECUTIVE SUMMARY (RANGKUMAN PALING BAWAH DENGAN WARNA JELAS) --}}
                <div class="border-t border-gray-200 bg-gray-50 p-6 dark:border-slate-800 dark:bg-slate-800/40">
                    <div class="mb-4 flex items-center gap-2">
                        @svg('heroicon-s-chart-pie', 'h-4 w-4 text-blue-600 dark:text-blue-400')
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-800 dark:text-gray-200">
                            Ikhtisar & Rangkuman Keseluruhan Laporan
                        </h4>
                    </div>

                    <div class="grid grid-cols-2 gap-3.5 sm:grid-cols-3 lg:grid-cols-6">
                        <!-- Total Unit -->
                        <div class="rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm dark:border-slate-700 dark:bg-slate-800">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Unit</span>
                            <div class="mt-1 font-mono text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($summary['total_units']) }}</div>
                        </div>

                        <!-- Jml Indikator -->
                        <div class="rounded-xl border border-gray-200 bg-white p-4 text-center shadow-sm dark:border-slate-700 dark:bg-slate-800">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Jml Indikator</span>
                            <div class="mt-1 font-mono text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($summary['total_indicators']) }}</div>
                        </div>

                        <!-- Mencapai Standar -->
                        <div style="--c-50:var(--success-50);--c-400:var(--success-400);--c-600:var(--success-600);"
                             class="rounded-xl border border-green-200 bg-custom-50 p-4 text-center shadow-sm dark:border-green-800 dark:bg-custom-400/10">
                            <span class="text-xs font-bold uppercase tracking-wider text-custom-600 dark:text-custom-400">Mencapai Standar</span>
                            <div class="mt-1 font-mono text-2xl font-bold text-custom-600 dark:text-custom-400">{{ number_format($summary['total_standard']) }}</div>
                        </div>

                        <!-- Tidak Standar -->
                        <div style="--c-50:var(--danger-50);--c-400:var(--danger-400);--c-600:var(--danger-600);"
                             class="rounded-xl border border-red-200 bg-custom-50 p-4 text-center shadow-sm dark:border-red-800 dark:bg-custom-400/10">
                            <span class="text-xs font-bold uppercase tracking-wider text-custom-600 dark:text-custom-400">Tidak Standar</span>
                            <div class="mt-1 font-mono text-2xl font-bold text-custom-600 dark:text-custom-400">{{ number_format($summary['total_below']) }}</div>
                        </div>

                        <!-- Kosong / Belum -->
                        <div style="--c-50:var(--warning-50);--c-400:var(--warning-400);--c-600:var(--warning-600);"
                             class="rounded-xl border border-amber-200 bg-custom-50 p-4 text-center shadow-sm dark:border-amber-800 dark:bg-custom-400/10">
                            <span class="text-xs font-bold uppercase tracking-wider text-custom-600 dark:text-custom-400">Kosong / Belum</span>
                            <div class="mt-1 font-mono text-2xl font-bold text-custom-600 dark:text-custom-400">{{ number_format($summary['total_empty']) }}</div>
                        </div>

                        <!-- Rata-rata Capaian -->
                        <div style="--c-50:var(--info-50);--c-400:var(--info-400);--c-600:var(--info-600);"
                             class="rounded-xl border border-blue-200 bg-custom-50 p-4 text-center shadow-sm dark:border-blue-800 dark:bg-custom-400/10">
                            <span class="text-xs font-bold uppercase tracking-wider text-custom-600 dark:text-custom-400">Rata-rata Capaian</span>
                            <div class="mt-1 font-mono text-2xl font-bold text-custom-600 dark:text-custom-400">{{ number_format($summary['overall_percentage'], 1) }}%</div>
                        </div>
                    </div>
                </div>

            </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>
