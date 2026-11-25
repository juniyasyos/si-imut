<x-filament-widgets::widget>
    @php
        $context = $this->periodContext;
        $hasData = ! empty($context['laporanIds']);
        $summary = $this->summary;
        $unitGroups = $this->unitGroups;
        $isPaginated = $unitGroups instanceof \Illuminate\Pagination\LengthAwarePaginator;
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
            <div class="w-full overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-slate-700 dark:bg-slate-900">

                {{-- 1. HEADER DOKUMEN --}}
                <div class="border-b border-slate-700 bg-slate-900 text-white" style="background-color: #0f172a; color: #ffffff; padding: 1.25rem 1.5rem;">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div class="flex items-start gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-red-600 text-white shadow-md" style="background-color: #dc2626; color: #ffffff;">
                                @svg('heroicon-o-exclamation-triangle', 'h-7 w-7')
                            </div>
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <h2 class="text-xl font-bold tracking-tight text-white" style="color: #ffffff;">
                                        Daftar Indikator Mutu Tidak Tercapai Per Unit Kerja
                                    </h2>
                                </div>
                                <p class="text-xs text-slate-300 sm:text-sm" style="color: #cbd5e1; margin-top: 0.25rem;">
                                    <span class="font-semibold text-white" style="color: #ffffff;">{{ $context['reportName'] }}</span>
                                    <span class="text-slate-500" style="margin-left: 0.5rem; margin-right: 0.5rem;">&bull;</span>
                                    Periode ({{ $context['periodTypeLabel'] }}):
                                    <span class="font-medium text-slate-200" style="color: #e2e8f0;">
                                        {{ $context['startDate'] ? $context['startDate']->translatedFormat('d M Y') : '-' }}
                                        s/d
                                        {{ $context['endDate'] ? $context['endDate']->translatedFormat('d M Y') : '-' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <!-- Tombol Cetak & Pemilih Tipe + Periode -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Tipe Periode -->
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

                            <!-- Nilai Periode -->
                            <div class="flex items-center gap-1.5">
                                <span class="text-xs font-bold text-slate-300" style="color: #cbd5e1;">Periode:</span>
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

                            <a href="{{ route('print.imut-tidak-tercapai', ['period_type' => $periodType, 'period' => $context['selectedPeriod'], 'orientation' => 'landscape', 'category_id' => $filterCategoryId, 'search' => $search]) }}"
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
                                   placeholder="Cari unit kerja, nama indikator, atau kategori..."
                                   class="w-full rounded-lg border border-gray-300 bg-white text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-gray-500"
                                   style="padding: 0.375rem 1rem 0.375rem 2.25rem;" />
                        </div>

                        <!-- Dropdowns -->
                        <div class="flex flex-wrap items-center gap-2">
                            <!-- Filter Kategori Indikator -->
                            <select wire:model.live="filterCategoryId"
                                    class="rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-700 shadow-sm transition focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200"
                                    style="padding: 0.375rem 0.75rem;">
                                <option value="all">Semua Kategori Indikator</option>
                                @foreach ($this->availableCategories as $category)
                                    <option value="{{ $category->id }}">
                                        {{ $category->short_name ? "{$category->short_name} - {$category->category_name}" : $category->category_name }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Pilihan Baris Unit -->
                            <select wire:model.live="perPage"
                                    class="rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-700 shadow-sm transition focus:border-blue-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200"
                                    style="padding: 0.375rem 0.75rem;">
                                <option value="5">5 Unit</option>
                                <option value="10">10 Unit</option>
                                <option value="25">25 Unit</option>
                                <option value="all">Semua Unit</option>
                            </select>

                            @if ($search !== '' || $filterCategoryId !== 'all')
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

                {{-- 3. TABEL INDIKATOR TIDAK TERCAPAI (GROUPED BY UNIT KERJA WITH ROWSPAN) --}}
                <div class="w-full overflow-x-auto">
                    <table class="w-full min-w-full border-collapse text-left text-sm">
                        <thead class="border-b-2 border-slate-300 bg-slate-100 text-xs font-bold uppercase tracking-wider text-slate-700 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                            <tr>
                                <th scope="col" class="w-14 text-center" style="padding: 0.875rem;">No</th>
                                <th scope="col" class="w-60" style="padding: 0.875rem 1.25rem;">Unit Kerja</th>
                                <th scope="col" style="padding: 0.875rem 1.25rem;">Indikator</th>
                                <th scope="col" class="w-40 text-center" style="padding: 0.875rem;">Kategori Indikator</th>
                                <th scope="col" class="w-32 text-center" style="padding: 0.875rem;">Capaian</th>
                                <th scope="col" class="w-32 text-center" style="padding: 0.875rem;">Nilai Standard</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-slate-800 dark:bg-slate-900">
                            @forelse ($unitGroups as $unit)
                                @php
                                    $unitNumber = $isPaginated
                                        ? ($unitGroups->currentPage() - 1) * $unitGroups->perPage() + $loop->iteration
                                        : $loop->iteration;
                                    $items = $unit->indicators ?? collect();
                                    $rowCount = max(1, $items->count());
                                    $unitBgClass = $loop->even ? 'bg-gray-50 dark:bg-slate-800/30' : 'bg-white dark:bg-slate-900';
                                @endphp

                                @foreach ($items as $item)
                                    @php
                                        $isFirst = $loop->first;
                                        $isLast = $loop->last;
                                        $catLabel = $item->category_short_name ?: ($item->category_name ?: '-');
                                        $catFullName = $item->category_name ?: $catLabel;
                                        $catVar = \App\Filament\Widgets\ImutTidakTercapai::categoryColorVar($item->category_id);
                                        $capaian = (float) ($item->capaian_percentage ?? 0);
                                        $standard = \App\Filament\Widgets\ImutTidakTercapai::formatStandard($item->target_operator, $item->target_value);
                                    @endphp
                                    <tr class="transition hover:bg-blue-50/70 dark:hover:bg-slate-800/70 {{ $unitBgClass }}"
                                        style="{{ $isLast ? 'border-bottom: 2px solid #cbd5e1;' : '' }}">
                                        @if ($isFirst)
                                            <!-- No -->
                                            <td rowspan="{{ $rowCount }}"
                                                class="border-r border-gray-200 text-center align-top font-mono text-xs font-bold text-gray-600 dark:border-slate-700 dark:text-gray-300"
                                                style="padding: 0.875rem; vertical-align: top;">
                                                {{ $unitNumber }}
                                            </td>

                                            <!-- Unit Kerja -->
                                            <td rowspan="{{ $rowCount }}"
                                                class="border-r border-gray-200 align-top dark:border-slate-700"
                                                style="padding: 0.875rem 1.25rem; vertical-align: top;">
                                                <div class="font-bold text-gray-900 dark:text-white">
                                                    {{ $unit->unit_name }}
                                                </div>
                                                <div style="margin-top: 0.375rem;">
                                                    <span style="--c-50:var(--danger-50);--c-400:var(--danger-400);--c-600:var(--danger-600); padding: 0.125rem 0.5rem;"
                                                          class="fi-badge inline-flex items-center gap-1 rounded-md text-xs font-semibold ring-1 ring-inset fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/20 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30">
                                                        {{ $rowCount }} tidak tercapai
                                                    </span>
                                                </div>
                                                <div style="margin-top: 0.5rem;">
                                                    <a href="{{ \App\Filament\Resources\LaporanImutResource\Pages\UnitKerjaImutDataReport::getUrl(['laporan_id' => $unit->laporan_imut_id, 'unit_kerja_id' => $unit->unit_kerja_id]) }}"
                                                       class="inline-flex items-center gap-1 rounded-md border border-gray-300 bg-white text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-blue-600 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700 dark:hover:text-white"
                                                       style="padding: 0.25rem 0.5rem;">
                                                        @svg('heroicon-o-eye', 'h-3.5 w-3.5 text-blue-600 dark:text-blue-400')
                                                        <span>Lihat Detail</span>
                                                    </a>
                                                </div>
                                            </td>
                                        @endif

                                        <!-- Indikator -->
                                        <td class="text-gray-900 dark:text-white" style="padding: 0.75rem 1.25rem;">
                                            <div class="font-semibold text-gray-900 dark:text-white">
                                                {{ $item->indicator_title }}
                                            </div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400" style="margin-top: 0.25rem;">
                                                @if (($item->months_count ?? 1) > 1)
                                                    <span class="font-semibold text-gray-600 dark:text-gray-300">Akumulasi {{ $item->months_count }} bln</span>
                                                    <span style="margin-left: 0.25rem; margin-right: 0.25rem;">&bull;</span>
                                                @endif
                                                Num: <strong class="text-gray-700 dark:text-gray-200">{{ number_format((float) $item->numerator_value) }}</strong>
                                                /
                                                Den: <strong class="text-gray-700 dark:text-gray-200">{{ number_format((float) $item->denominator_value) }}</strong>
                                            </div>
                                        </td>

                                        <!-- Kategori Indikator -->
                                        <td class="text-center" style="padding: 0.75rem 0.875rem;">
                                            <span title="{{ $catFullName }}"
                                                  style="--c-50:var(--{{ $catVar }}-50);--c-400:var(--{{ $catVar }}-400);--c-600:var(--{{ $catVar }}-600); padding: 0.25rem 0.625rem;"
                                                  class="fi-badge inline-flex items-center rounded-md text-xs font-bold ring-1 ring-inset fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/20 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30">
                                                {{ $catLabel }}
                                            </span>
                                        </td>

                                        <!-- Capaian -->
                                        <td class="text-center" style="padding: 0.75rem 0.875rem;">
                                            <span style="--c-50:var(--danger-50);--c-400:var(--danger-400);--c-600:var(--danger-600); padding: 0.25rem 0.625rem;"
                                                  class="fi-badge inline-flex items-center gap-1 rounded-full font-mono text-xs font-bold ring-1 ring-inset fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/20 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30">
                                                {{ number_format($capaian, 1) }}%
                                            </span>
                                        </td>

                                        <!-- Nilai Standard -->
                                        <td class="text-center" style="padding: 0.75rem 0.875rem;">
                                            <div class="inline-flex flex-col items-center">
                                                <span style="--c-50:var(--success-50);--c-400:var(--success-400);--c-600:var(--success-600); padding: 0.25rem 0.625rem;"
                                                      class="fi-badge inline-flex items-center rounded-md font-mono text-xs font-bold ring-1 ring-inset fi-color-custom bg-custom-50 text-custom-600 ring-custom-600/20 dark:bg-custom-400/10 dark:text-custom-400 dark:ring-custom-400/30">
                                                    {{ $standard['badge'] }}
                                                </span>
                                                <span class="text-[11px] text-gray-500 dark:text-gray-400" style="margin-top: 0.125rem;">
                                                    {{ $standard['label'] }}
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-gray-500 dark:text-gray-400" style="padding: 3.5rem 1rem;">
                                        <div class="flex flex-col items-center justify-center">
                                            @svg('heroicon-o-check-badge', 'h-10 w-10 text-green-500')
                                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-200" style="margin-top: 0.5rem;">
                                                Tidak ada indikator yang tidak tercapai sesuai filter saat ini.
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- PAGINATION DI SEBELAH KIRI --}}
                @if ($isPaginated && $unitGroups->hasPages())
                    <div class="border-t border-gray-200 bg-white dark:border-slate-800 dark:bg-slate-900" style="padding: 0.75rem 1.5rem;">
                        {{ $unitGroups->links('filament.widgets.partials.imut-tercapai-pagination') }}
                    </div>
                @endif

                {{-- 4. IKHTISAR COMPACT --}}
                <div class="border-t border-gray-200 bg-gray-50 dark:border-slate-800 dark:bg-slate-800/40" style="padding: 0.875rem 1.5rem;">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            @svg('heroicon-s-chart-pie', 'h-4 w-4 text-red-600 dark:text-red-400')
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-200">
                                Ikhtisar:
                            </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-white text-xs text-gray-600 shadow-sm dark:border-slate-700 dark:bg-slate-800 dark:text-gray-300"
                                  style="padding: 0.375rem 0.75rem;">
                                <span>Unit Kerja</span>
                                <strong class="font-mono text-sm font-bold text-gray-900 dark:text-white">{{ number_format($summary['total_units']) }}</strong>
                            </span>

                            <span style="--c-50:var(--danger-50);--c-400:var(--danger-400);--c-600:var(--danger-600); padding: 0.375rem 0.75rem;"
                                  class="fi-badge inline-flex items-center gap-1.5 rounded-lg border border-red-200 bg-custom-50 text-xs text-custom-600 dark:border-red-800 dark:bg-custom-400/10 dark:text-custom-400">
                                <span>Tidak Tercapai</span>
                                <strong class="font-mono text-sm font-bold">{{ number_format($summary['total_indicators']) }}</strong>
                            </span>

                            @foreach ($summary['by_category'] as $catSummary)
                                <span title="{{ $catSummary['full_name'] }}"
                                      style="--c-50:var(--{{ $catSummary['color_var'] }}-50);--c-400:var(--{{ $catSummary['color_var'] }}-400);--c-600:var(--{{ $catSummary['color_var'] }}-600); padding: 0.375rem 0.75rem;"
                                      class="fi-badge inline-flex items-center gap-1.5 rounded-lg border border-gray-200 bg-custom-50 text-xs text-custom-600 dark:border-slate-700 dark:bg-custom-400/10 dark:text-custom-400">
                                    <span>{{ $catSummary['label'] }}</span>
                                    <strong class="font-mono text-sm font-bold">{{ number_format($catSummary['count']) }}</strong>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>

            </div>
        @endif
    </div>
</x-filament-widgets::widget>
