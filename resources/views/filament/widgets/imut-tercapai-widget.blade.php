<x-filament-widgets::widget>
    @php
        $context = $this->periodContext;
        $hasData = ! empty($context['laporanIds']);
        $summary = $this->summary;
        $records = $this->records;
        $isPaginated = $records instanceof \Illuminate\Pagination\LengthAwarePaginator;
    @endphp

    <div class="w-full">
        @if (! $hasData)
            <div class="flex flex-col items-center justify-center rounded-2xl border border-gray-200 bg-white text-center shadow-sm dark:border-slate-700 dark:bg-slate-900" style="padding-top: 3.5rem; padding-bottom: 3.5rem;">
                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400" style="margin-left: auto; margin-right: auto; margin-bottom: 1rem;">
                    @svg('heroicon-o-document-magnifying-glass', 'h-8 w-8')
                </div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Tidak Ada Laporan Aktif</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400" style="margin-top: 0.25rem;">Belum ada data laporan indikator mutu terbaru untuk ditampilkan.</p>
            </div>
        @else
            {{-- ================= SATU CARD SECTION UTUH DENGAN WARNA TEGAS ================= --}}
            <div class="w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">

                {{-- 1. HEADER DOKUMEN: SOLID NAVY FORMAL --}}
                <div class="border-b border-slate-700 bg-slate-900 text-white" style="background-color: #0f172a; color: #ffffff; padding: 1.25rem 1.5rem;">
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
                                        $isComplete = ($context['status'] ?? '') === 'complete';
                                    @endphp
                                    <span class="inline-flex items-center rounded-md text-xs font-bold uppercase tracking-wider text-white {{ $isComplete ? 'bg-green-600' : 'bg-blue-600' }}"
                                          style="background-color: {{ $isComplete ? '#16a34a' : '#2563eb' }}; color: #ffffff; padding: 0.125rem 0.625rem;">
                                        {{ strtoupper($context['status'] ?? 'PROSES') }}
                                    </span>
                                </div>
                                <p class="text-xs text-slate-300 sm:text-sm" style="color: #cbd5e1; margin-top: 0.25rem;">
                                    <span class="font-semibold text-white" style="color: #ffffff;">{{ $context['reportName'] }}</span>
                                    <span class="text-slate-500" style="margin-left: 0.5rem; margin-right: 0.5rem;">&bull;</span>
                                    Periode:
                                    <span class="font-medium text-slate-200" style="color: #e2e8f0;">
                                        {{ $context['startDate'] ? $context['startDate']->translatedFormat('d M Y') : '-' }}
                                        s/d
                                        {{ $context['endDate'] ? $context['endDate']->translatedFormat('d M Y') : '-' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Tombol Cetak & Pemilih Bulan -->
                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Tipe Periode disembunyikan sementara (gunakan bulanan)
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-slate-300" style="color: #cbd5e1;">Tipe:</span>
                                <select wire:model.live="periodType"
                                        class="rounded-lg border border-slate-600 bg-slate-800 text-xs font-bold text-white shadow-sm transition [color-scheme:dark] hover:bg-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                        style="background-color: #1e293b; color: #ffffff; border-color: #475569; padding: 0.375rem 0.75rem;">
                                    <option value="month" class="bg-slate-800 text-white" style="background-color: #1e293b; color: #ffffff;">Bulan</option>
                                    <option value="quarter" class="bg-slate-800 text-white" style="background-color: #1e293b; color: #ffffff;">Kuartal</option>
                                    <option value="semester" class="bg-slate-800 text-white" style="background-color: #1e293b; color: #ffffff;">Semester</option>
                                    <option value="year" class="bg-slate-800 text-white" style="background-color: #1e293b; color: #ffffff;">Tahun</option>
                                </select>
                            </div>
                            --}}

                            <!-- Pemilih Bulan -->
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-slate-300" style="color: #cbd5e1;">Bulan:</span>
                                <select wire:model.live="selectedPeriod"
                                        class="rounded-lg border border-slate-600 bg-slate-800 text-xs font-bold text-white shadow-sm transition [color-scheme:dark] hover:bg-slate-700 focus:border-blue-400 focus:outline-none focus:ring-2 focus:ring-blue-400"
                                        style="background-color: #1e293b; color: #ffffff; border-color: #475569; padding: 0.375rem 0.75rem;">
                                    @foreach ($context['options'] as $key => $label)
                                        <option value="{{ $key }}" class="bg-slate-800 text-white" style="background-color: #1e293b; color: #ffffff;">
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="button"
                                    wire:click="openExportModal"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 text-xs font-bold text-white shadow transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                                    style="background-color: #059669; color: #ffffff; padding: 0.5rem 1rem;">
                                @svg('heroicon-o-arrow-down-tray', 'h-4 w-4')
                                <span>Download Excel</span>
                            </button>

                            <a href="{{ route('print.unit-kerja-rekap', ['period_type' => $periodType, 'period' => $context['selectedPeriod'], 'orientation' => 'landscape']) }}"
                               target="_blank"
                               class="inline-flex items-center gap-1.5 rounded-lg bg-blue-600 text-xs font-bold text-white shadow transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400"
                               style="background-color: #2563eb; color: #ffffff; padding: 0.5rem 1rem;">
                                @svg('heroicon-o-printer', 'h-4 w-4')
                                <span>Cetak Laporan</span>
                            </a>
                        </div>
                    </div>
                </div>

                {{-- 2. TOOLBAR FILTER & SEARCH --}}
                <div class="border-b border-gray-200 bg-gray-50 dark:border-slate-700 dark:bg-slate-800/50" style="padding: 0.875rem 1.5rem;">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <!-- Search Box -->
                        <div class="relative flex-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center text-gray-400" style="padding-left: 0.875rem;">
                                @svg('heroicon-o-magnifying-glass', 'h-4 w-4')
                            </div>
                            <input type="text"
                                   wire:model.live.debounce.300ms="search"
                                   placeholder="Cari unit kerja..."
                                   class="w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-gray-500"
                                   style="padding: 0.375rem 1rem 0.375rem 2.25rem;" />
                        </div>

                        <!-- Dropdowns -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Filter Status Capaian -->
                            <select wire:model.live="filterLevel"
                                    class="rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-700 shadow-sm transition focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200"
                                    style="padding: 0.375rem 0.75rem;">
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
                                    class="rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-700 shadow-sm transition focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200"
                                    style="padding: 0.375rem 0.75rem;">
                                <option value="10">10 Baris</option>
                                <option value="25">25 Baris</option>
                                <option value="50">50 Baris</option>
                                <option value="all">Semua Unit</option>
                            </select>

                            @if ($search !== '' || $filterLevel !== 'all')
                                <button type="button"
                                        wire:click="resetFilter"
                                        class="inline-flex items-center gap-1 rounded-lg border border-red-200 bg-red-50 text-xs font-bold text-red-700 shadow-sm transition hover:bg-red-100 dark:border-red-800 dark:bg-red-950/40 dark:text-red-300"
                                        style="padding: 0.375rem 0.625rem;">
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
                        <thead class="border-b-2 border-slate-300 bg-slate-100 text-xs font-bold uppercase tracking-wider text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <tr>
                                <th scope="col" class="w-14 text-center" style="padding: 0.875rem;">No</th>

                                <th scope="col" class="cursor-pointer transition hover:bg-slate-200 dark:hover:bg-slate-700" style="padding: 0.875rem 1.25rem;" wire:click="sortBy('unit_name')">
                                    <div class="flex items-center gap-1.5">
                                        <span>Unit Kerja</span>
                                        @if ($sortColumn === 'unit_name')
                                            <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @else
                                            <span class="text-slate-400">⇅</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-32 cursor-pointer text-center transition hover:bg-slate-200 dark:hover:bg-slate-700" style="padding: 0.875rem;" wire:click="sortBy('total_count')">
                                    <div class="flex items-center justify-center gap-1">
                                        <span>Jml IMUT</span>
                                        @if ($sortColumn === 'total_count')
                                            <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-36 cursor-pointer text-center transition hover:bg-slate-200 dark:hover:bg-slate-700" style="padding: 0.875rem;" wire:click="sortBy('meets_standard_count')">
                                    <div class="flex items-center justify-center gap-1 text-emerald-700 dark:text-emerald-300">
                                        <span>Standar</span>
                                        @if ($sortColumn === 'meets_standard_count')
                                            <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-36 cursor-pointer text-center transition hover:bg-slate-200 dark:hover:bg-slate-700" style="padding: 0.875rem;" wire:click="sortBy('below_standard_count')">
                                    <div class="flex items-center justify-center gap-1 text-rose-700 dark:text-rose-300">
                                        <span>Tidak Standar</span>
                                        @if ($sortColumn === 'below_standard_count')
                                            <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-28 cursor-pointer text-center transition hover:bg-slate-200 dark:hover:bg-slate-700" style="padding: 0.875rem;" wire:click="sortBy('empty_count')">
                                    <div class="flex items-center justify-center gap-1 text-amber-700 dark:text-amber-300">
                                        <span>Kosong</span>
                                        @if ($sortColumn === 'empty_count')
                                            <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-40 cursor-pointer text-center transition hover:bg-slate-200 dark:hover:bg-slate-700" style="padding: 0.875rem 1rem;" wire:click="sortBy('standard_percentage')">
                                    <div class="flex items-center justify-center gap-1 text-blue-700 dark:text-blue-300">
                                        <span>Persentase (%)</span>
                                        @if ($sortColumn === 'standard_percentage')
                                            <span class="text-blue-600 dark:text-blue-400">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                                        @else
                                            <span class="text-slate-400">⇅</span>
                                        @endif
                                    </div>
                                </th>

                                <th scope="col" class="w-24 text-center" style="padding: 0.875rem;">Aksi</th>
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
                                    <td class="text-center font-mono text-xs text-gray-500 dark:text-gray-400" style="padding: 0.75rem 0.875rem;">
                                        {{ $rowNumber }}
                                    </td>

                                    <!-- Unit Kerja -->
                                    <td class="text-gray-900 dark:text-white" style="padding: 0.75rem 1.25rem;">
                                        <div class="font-bold text-gray-900 dark:text-white">{{ $row->unit_name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            Pengisian: {{ number_format($row->filled_count ?? 0) }} dari {{ number_format($row->total_count ?? 0) }} indikator
                                        </div>
                                    </td>

                                    <!-- Jml Indikator -->
                                    <td class="text-center font-mono text-sm font-semibold text-gray-700 dark:text-gray-300" style="padding: 0.75rem 0.875rem;">
                                        {{ number_format($row->total_count ?? 0) }}
                                    </td>

                                    <!-- Mencapai Standar -->
                                    <td class="text-center" style="padding: 0.75rem 0.875rem;">
                                        <span style="--c-50:var(--success-50);--c-400:var(--success-400);--c-600:var(--success-600); padding: 0.25rem 0.625rem;"
                                              class="fi-badge inline-flex items-center gap-1 rounded-md font-mono text-xs font-bold ring-1 ring-inset fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/20 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30">
                                            @svg('heroicon-m-check-circle', 'h-3.5 w-3.5')
                                            {{ number_format($row->meets_standard_count ?? 0) }}
                                        </span>
                                    </td>

                                    <!-- Tidak Standar -->
                                    <td class="text-center" style="padding: 0.75rem 0.875rem;">
                                        @if (($row->below_standard_count ?? 0) > 0)
                                            <span style="--c-50:var(--danger-50);--c-400:var(--danger-400);--c-600:var(--danger-600); padding: 0.25rem 0.625rem;"
                                                  class="fi-badge inline-flex items-center gap-1 rounded-md font-mono text-xs font-bold ring-1 ring-inset fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/20 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30">
                                                @svg('heroicon-m-x-circle', 'h-3.5 w-3.5')
                                                {{ number_format($row->below_standard_count ?? 0) }}
                                            </span>
                                        @else
                                            <span class="font-mono text-xs text-gray-400 dark:text-gray-500">0</span>
                                        @endif
                                    </td>

                                    <!-- Kosong -->
                                    <td class="text-center" style="padding: 0.75rem 0.875rem;">
                                        @if (($row->empty_count ?? 0) > 0)
                                            <span style="--c-50:var(--warning-50);--c-400:var(--warning-400);--c-600:var(--warning-600); padding: 0.25rem 0.625rem;"
                                                  class="fi-badge inline-flex items-center gap-1 rounded-md font-mono text-xs font-bold ring-1 ring-inset fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/20 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30">
                                                @svg('heroicon-m-exclamation-triangle', 'h-3.5 w-3.5')
                                                {{ number_format($row->empty_count ?? 0) }}
                                            </span>
                                        @else
                                            <span class="font-mono text-xs text-gray-400 dark:text-gray-500">0</span>
                                        @endif
                                    </td>

                                    <!-- Persentase (%) -->
                                    <td class="text-center" style="padding: 0.75rem 1rem;">
                                        @php
                                            $pctVar = $pct >= 80 ? 'success' : ($pct >= 50 ? 'warning' : 'danger');
                                        @endphp
                                        <span style="--c-50:var(--{{ $pctVar }}-50);--c-400:var(--{{ $pctVar }}-400);--c-600:var(--{{ $pctVar }}-600); padding: 0.25rem 0.75rem;"
                                              class="fi-badge inline-flex items-center rounded-full font-mono text-xs font-bold shadow-sm ring-1 ring-inset fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/20 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30">
                                            {{ number_format($pct, 1) }}%
                                        </span>
                                    </td>

                                    <!-- Aksi Detail -->
                                    <td class="text-center" style="padding: 0.75rem 0.875rem;">
                                        <a href="{{ \App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport::getUrl(['laporan_id' => $row->laporan_imut_id, 'unit_kerja_id' => $row->unit_kerja_id]) }}"
                                           class="inline-flex items-center gap-1 rounded-md border border-gray-300 bg-white text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700 dark:hover:text-white"
                                           style="padding: 0.25rem 0.625rem;"
                                           title="Lihat Detail Indikator">
                                            @svg('heroicon-o-eye', 'h-3.5 w-3.5 text-blue-600 dark:text-blue-400')
                                            <span>Detail</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-gray-500 dark:text-gray-400" style="padding: 3.5rem 1rem;">
                                        <div class="flex flex-col items-center justify-center">
                                            @svg('heroicon-o-document-magnifying-glass', 'h-8 w-8 text-gray-400')
                                            <p class="text-sm font-semibold" style="margin-top: 0.5rem;">Tidak ada unit kerja yang sesuai filter.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION --}}
                @if ($isPaginated && $records->hasPages())
                    <div class="border-t border-gray-200 bg-white dark:border-slate-800 dark:bg-slate-900" style="padding: 0.75rem 1.5rem;">
                        {{ $records->links('filament.widgets.partials.imut-tercapai-pagination') }}
                    </div>
                @endif
            </div>
        @endif

        {{-- MODAL EXPORT EXCEL --}}
        @if ($showExportModal)
            @php
                $exportContext = $this->exportPeriodContext;
                $exportMonthLabels = $this->exportMonthLabels;
                $availableCategories = $this->availableCategories;
                $availableUnits = $this->availableUnitKerjas;
            @endphp
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/70 backdrop-blur-sm"
                 style="padding: 1rem;"
                 wire:keydown.escape.window="closeExportModal">
                <div class="w-full max-w-2xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900"
                     style="max-height: 90vh; display: flex; flex-direction: column;">
                    {{-- Modal Header --}}
                    <div class="flex items-center justify-between border-b border-gray-200 bg-slate-900 text-white dark:border-slate-700"
                         style="padding: 1rem 1.5rem;">
                        <div class="flex items-center gap-2.5">
                            <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-600/20 text-emerald-400">
                                @svg('heroicon-o-table-cells', 'h-5 w-5')
                            </span>
                            <div>
                                <h3 class="text-sm font-bold text-white">Download Excel Rekapitulasi IMUT</h3>
                                <p class="text-xs text-slate-300">Pilih jenjang periode, kategori IMUT, dan filter data yang diinginkan</p>
                            </div>
                        </div>
                        <button type="button"
                                wire:click="closeExportModal"
                                class="rounded-lg text-slate-400 transition hover:bg-slate-800 hover:text-white"
                                style="padding: 0.375rem;"
                                title="Tutup">
                            @svg('heroicon-o-x-mark', 'h-5 w-5')
                        </button>
                    </div>

                    {{-- Modal Body --}}
                    <div class="flex flex-1 flex-col gap-4 overflow-y-auto" style="padding: 1.25rem 1.5rem;">
                        {{-- 1. Jenjang Periode & Periode --}}
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200" style="margin-bottom: 0.375rem;">
                                    Jenjang Periode
                                </label>
                                <select wire:model.live="exportPeriodType"
                                        class="w-full rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                        style="padding: 0.5rem 0.75rem;">
                                    <option value="month">Bulan</option>
                                    <option value="quarter">Kuartal (Triwulan)</option>
                                    <option value="semester">Semester</option>
                                    <option value="year">Tahun</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200" style="margin-bottom: 0.375rem;">
                                    Pilih Periode
                                </label>
                                <select wire:model.live="exportPeriod"
                                        class="w-full rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                        style="padding: 0.5rem 0.75rem;">
                                    @forelse ($exportContext['options'] as $optKey => $optLabel)
                                        <option value="{{ $optKey }}">{{ $optLabel }}</option>
                                    @empty
                                        <option value="">Tidak ada periode tersedia</option>
                                    @endforelse
                                </select>
                            </div>
                        </div>

                        {{-- Preview Kolom Bulan Excel --}}
                        @if (! empty($exportMonthLabels))
                            <div class="rounded-lg border border-emerald-200 bg-emerald-50/70 text-xs text-emerald-800 dark:border-emerald-800/50 dark:bg-emerald-950/30 dark:text-emerald-300"
                                 style="padding: 0.625rem 0.875rem;">
                                <span class="font-bold">Kolom Bulan di Excel:</span>
                                <span>{{ implode(', ', $exportMonthLabels) }}</span>
                                <span class="text-emerald-700 dark:text-emerald-400"> (masing-masing: Jml Indikator, Tidak Sesuai Standart, Persentase %)</span>
                            </div>
                        @endif

                        {{-- 2. Kategori IMUT --}}
                        <div>
                            <div class="flex items-center justify-between" style="margin-bottom: 0.375rem;">
                                <label class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">
                                    Kategori IMUT
                                    <span class="font-normal normal-case text-gray-500 dark:text-gray-400">(kosongkan untuk semua kategori)</span>
                                </label>
                                @if (! empty($exportCategoryIds))
                                    <button type="button"
                                            wire:click="resetExportCategories"
                                            class="text-xs font-semibold text-rose-600 hover:underline dark:text-rose-400">
                                        Reset ({{ count($exportCategoryIds) }})
                                    </button>
                                @endif
                            </div>
                            <div class="grid grid-cols-1 gap-2 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 sm:grid-cols-2 dark:border-slate-700 dark:bg-slate-800/60"
                                 style="max-height: 140px; padding: 0.625rem 0.75rem;">
                                @forelse ($availableCategories as $category)
                                    <label class="flex cursor-pointer items-start gap-2 rounded text-xs text-gray-700 hover:bg-white dark:text-gray-200 dark:hover:bg-slate-800"
                                           style="padding: 0.25rem 0.375rem;">
                                        <input type="checkbox"
                                               wire:model.live="exportCategoryIds"
                                               value="{{ $category->id }}"
                                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-700"
                                               style="margin-top: 0.125rem;">
                                        <span>
                                            @if ($category->short_name)
                                                <span class="font-bold">{{ $category->short_name }}</span> -
                                            @endif
                                            {{ $category->category_name }}
                                        </span>
                                    </label>
                                @empty
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Belum ada kategori IMUT.</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- 3. Filter Unit Kerja --}}
                        <div>
                            <div class="flex items-center justify-between" style="margin-bottom: 0.375rem;">
                                <label class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">
                                    Unit Kerja
                                    <span class="font-normal normal-case text-gray-500 dark:text-gray-400">(kosongkan untuk semua unit)</span>
                                </label>
                                @if (! empty($exportUnitKerjaIds))
                                    <button type="button"
                                            wire:click="resetExportUnits"
                                            class="text-xs font-semibold text-rose-600 hover:underline dark:text-rose-400">
                                        Reset ({{ count($exportUnitKerjaIds) }})
                                    </button>
                                @endif
                            </div>
                            <input type="text"
                                   wire:model.live.debounce.250ms="exportUnitSearch"
                                   placeholder="Cari nama unit kerja..."
                                   class="w-full rounded-lg border border-gray-300 bg-white text-xs text-gray-800 placeholder-gray-400 focus:border-emerald-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                   style="padding: 0.375rem 0.75rem; margin-bottom: 0.5rem;" />
                            <div class="grid grid-cols-1 gap-1.5 overflow-y-auto rounded-lg border border-gray-200 bg-gray-50 sm:grid-cols-2 dark:border-slate-700 dark:bg-slate-800/60"
                                 style="max-height: 140px; padding: 0.625rem 0.75rem;">
                                @forelse ($availableUnits as $unit)
                                    <label class="flex cursor-pointer items-center gap-2 rounded text-xs text-gray-700 hover:bg-white dark:text-gray-200 dark:hover:bg-slate-800"
                                           style="padding: 0.25rem 0.375rem;">
                                        <input type="checkbox"
                                               wire:model.live="exportUnitKerjaIds"
                                               value="{{ $unit->id }}"
                                               class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 dark:border-slate-600 dark:bg-slate-700">
                                        <span class="truncate">{{ $unit->unit_name }}</span>
                                    </label>
                                @empty
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Unit kerja tidak ditemukan.</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- 4. Filter Status Capaian & Urutan --}}
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200" style="margin-bottom: 0.375rem;">
                                    Status Capaian
                                </label>
                                <select wire:model.live="exportFilterLevel"
                                        class="w-full rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                        style="padding: 0.5rem 0.75rem;">
                                    <option value="all">Semua Status Capaian</option>
                                    <option value="high">Capaian Tinggi (≥ 80%)</option>
                                    <option value="medium">Capaian Sedang (50% - 79%)</option>
                                    <option value="low">Capaian Rendah (&lt; 50%)</option>
                                    <option value="has_below">Ada Tidak Sesuai Standar (&gt; 0)</option>
                                    <option value="has_empty">Ada Indikator Kosong (&gt; 0)</option>
                                    <option value="all_standard">Sesuai Standar 100%</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200" style="margin-bottom: 0.375rem;">
                                    Urutan Unit Kerja
                                </label>
                                <select wire:model.live="exportSort"
                                        class="w-full rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-800 shadow-sm focus:border-emerald-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                                        style="padding: 0.5rem 0.75rem;">
                                    <option value="percentage_desc">Persentase Tertinggi → Terendah</option>
                                    <option value="percentage_asc">Persentase Terendah → Tertinggi</option>
                                    <option value="unit_name_asc">Nama Unit A → Z</option>
                                    <option value="unit_name_desc">Nama Unit Z → A</option>
                                    <option value="below_desc">Tidak Sesuai Standar Terbanyak</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 bg-gray-50 dark:border-slate-700 dark:bg-slate-800/80"
                         style="padding: 0.875rem 1.5rem;">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            File akan diunduh dalam format <span class="font-semibold text-gray-700 dark:text-gray-200">.xlsx</span>
                        </span>

                        <div class="flex items-center gap-2">
                            <button type="button"
                                    wire:click="closeExportModal"
                                    class="rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-100 dark:border-slate-600 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700"
                                    style="padding: 0.5rem 1rem;">
                                Batal
                            </button>

                            <a href="{{ $this->exportUrl }}"
                               target="_blank"
                               wire:click="closeExportModal"
                               class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 text-xs font-bold text-white shadow transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                               style="background-color: #059669; color: #ffffff; padding: 0.5rem 1.125rem;">
                                @svg('heroicon-o-arrow-down-tray', 'h-4 w-4')
                                <span>Download Excel (.xlsx)</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-widgets::widget>
