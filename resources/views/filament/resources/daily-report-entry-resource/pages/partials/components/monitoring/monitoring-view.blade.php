<!-- Monitoring Bulanan View -->
<div x-show="$wire.currentView === 'monitoring'"
    class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6"
    style="display: none;">


    <!-- Header with Search -->
    <div class="mb-6">
        <div class="flex flex-col gap-4">
            <!-- Title and Period Navigation -->
            <div class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-slate-700">
                <div class="flex flex-col gap-4 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="min-w-0">
                        <div class="mb-2 flex items-center gap-2">
                            <span
                                class="rounded-md bg-primary-50 px-2 py-1 text-xs font-semibold uppercase tracking-wide text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                                Periode Monitoring
                            </span>
                        </div>

                        <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                            <span class="text-primary-600 dark:text-primary-400"
                                x-text="getMonitoringPeriodText()"></span>
                        </h2>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Ringkasan pemantauan capaian indikator pada periode berjalan.
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex shrink-0 items-center gap-2 rounded-lg bg-slate-100 p-1 dark:bg-slate-800">
                        <button @click="changeMonitoringPeriod('prev')" :disabled="isLoadingMonth"
                            :class="isLoadingMonth ? 'opacity-50 cursor-not-allowed' : 'hover:bg-white hover:shadow-sm dark:hover:bg-slate-700'"
                            class="inline-flex items-center gap-1.5 rounded-md border border-transparent px-3 py-2 text-sm font-medium text-slate-700 transition-all dark:text-slate-300">
                            <!-- svg tetap -->
                            <span class="hidden sm:inline">Periode Sebelumnya</span>
                        </button>
                        <button @click="changeMonitoringPeriod('current')" :disabled="isLoadingMonth"
                            :class="isLoadingMonth ? 'opacity-50 cursor-not-allowed' : 'hover:bg-white hover:shadow-sm dark:hover:bg-slate-700'"
                            class="inline-flex items-center gap-1.5 rounded-md bg-white px-3 py-2 text-sm font-semibold text-slate-900 shadow-sm ring-1 ring-slate-200 transition-all dark:bg-slate-700 dark:text-white dark:ring-slate-600">
                            <!-- svg tetap -->
                            <span class="hidden sm:inline">Kembali ke Periode Saat Ini</span>
                        </button>
                        <button @click="changeMonitoringPeriod('next')"
                            :disabled="isLoadingMonth || isCurrentOrFutureMonth()" :class="(isLoadingMonth || isCurrentOrFutureMonth())
                                ? 'opacity-50 cursor-not-allowed'
                                : 'hover:bg-white hover:shadow-sm dark:hover:bg-slate-700'"
                            class="inline-flex items-center gap-1.5 rounded-md border border-transparent px-3 py-2 text-sm font-medium text-slate-700 transition-all dark:text-slate-300">
                            <span class="hidden sm:inline">Periode Berikutnya</span>
                            <!-- svg tetap -->
                        </button>
                    </div>
                </div>
            </div>

            <!-- Search Box (Livewire) -->
            <div class="flex items-center gap-2">
                <div class="relative w-full sm:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        @svg("heroicon-o-magnifying-glass", "w-5 h-5 text-gray-400")
                    </div>
                    <input type="text"
                        wire:model.live.debounce.300ms="monitoringSearch"
                        :disabled="isLoadingMonth"
                        placeholder="Cari form template..."
                        :class="isLoadingMonth ? 'opacity-50 cursor-not-allowed' : 'opacity-100'"
                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-slate-700 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-opacity">
                </div>
                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-300">
                    {{ $monitoringTotal }} item
                </span>
            </div>
        </div>
    </div>

    <!-- Templates List -->
    <div class="space-y-4 max-h-[600px] overflow-y-auto"
        :class="isLoadingMonth ? 'opacity-50 pointer-events-none' : 'opacity-100'">
        <!-- Loading Skeleton -->
        <div x-show="isLoadingMonth" class="space-y-4 py-4">
            <template x-for="i in 3" :key="i">
                <div
                    class="animate-pulse rounded-3xl border border-slate-200 bg-white/80 p-6 shadow-sm dark:border-slate-700 dark:bg-slate-800/70">
                    <div class="flex flex-col gap-4">
                        <div class="h-4 w-3/5 rounded-full bg-slate-200 dark:bg-slate-700"></div>
                        <div class="h-3 w-1/2 rounded-full bg-slate-200 dark:bg-slate-700"></div>
                        <div class="grid gap-3 sm:grid-cols-3">
                            <div class="h-10 rounded-2xl bg-slate-200 dark:bg-slate-700"></div>
                            <div class="h-10 rounded-2xl bg-slate-200 dark:bg-slate-700"></div>
                            <div class="h-10 rounded-2xl bg-slate-200 dark:bg-slate-700"></div>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div x-show="!isLoadingMonth">
            <!-- Desktop View -->
            <div class="hidden lg:block mt-2">
                @forelse ($pagedMonitoringTemplates as $item)
                    <div class="group mt-2 rounded-xl border border-slate-200 bg-white p-4 transition-all duration-200 hover:border-slate-300 hover:shadow-sm dark:border-slate-700 dark:bg-slate-700 dark:hover:border-slate-600">
                        <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                            <!-- Main Info -->
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start gap-2">
                                    <h3 class="min-w-0 flex-1 text-base font-semibold leading-snug text-slate-950 dark:text-white">
                                        {{ $item['title'] }}
                                    </h3>
                                </div>

                                @if (!empty($item['description']))
                                    <p class="mt-1 line-clamp-2 text-sm leading-6 text-slate-500 dark:text-slate-400">
                                        {{ $item['description'] }}
                                    </p>
                                @endif

                                <div class="mt-3 flex flex-wrap items-center gap-2 text-xs">
                                    <div class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1.5 font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        <svg class="h-3.5 w-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                        <span class="font-semibold text-slate-950 dark:text-white">{{ number_format($item['response_count'] ?? 0) }}</span>
                                        <span>Respon</span>
                                    </div>

                                    <div class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 px-2.5 py-1.5 font-medium text-indigo-700 dark:bg-indigo-500/10 dark:text-indigo-300" x-tooltip="'Versi Profil Imut'">
                                        @svg("heroicon-m-document-text", "h-3.5 w-3.5")
                                        <span>{{ $item['imut_profile_version'] ?? 'v1.0' }}</span>
                                    </div>

                                    @if (!empty($item['form_template_version']))
                                        <div class="inline-flex items-center gap-1.5 rounded-lg bg-amber-50 px-2.5 py-1.5 font-medium text-amber-700 dark:bg-slate-500 dark:text-amber-300" x-tooltip="'Versi Template Form'">
                                            @svg("heroicon-m-clipboard-document-list", "h-3.5 w-3.5")
                                            <span>{{ $item['form_template_version'] }}</span>
                                        </div>
                                    @endif

                                    @if (!empty($item['profile_name']))
                                        <div class="inline-flex items-center gap-1.5 rounded-lg bg-slate-100 px-2.5 py-1.5 font-medium text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                            <svg class="h-3.5 w-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                                            </svg>
                                            <span>{{ $item['profile_name'] }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="ml-auto flex shrink-0 flex-col items-end gap-1">
                                @if (!empty($item['category']))
                                    <span class="inline-flex shrink-0 items-center rounded-md gap-1 px-2 py-1 text-xs font-semibold bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                        @svg("heroicon-m-tag", "w-3 h-3")
                                        {{ $item['category'] }}
                                    </span>
                                @endif
                                <!-- Actions -->
                                <div class="flex shrink-0 items-center gap-2 border-t border-slate-100 pt-3 dark:border-slate-800 xl:border-t-0 xl:pt-0">
                                    <a href="{{ route('table-view') }}?form_template_id={{ $item['id'] }}&imut_profile_id={{ $item['imut_profile_id'] ?? '' }}&unit_kerja_id={{ $item['unit_kerja_id'] ?? '' }}&period={{ $selectedMonth }}"
                                        target="_blank"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-primary-700">
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="hidden sm:inline">Lihat Data</span>
                                    </a>
                                    <a href="{{ route('export.monitoring', $item['id']) }}?month={{ $selectedMonth }}"
                                        target="_blank"
                                        class="inline-flex items-center justify-center gap-1.5 rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 transition-colors hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                        <svg class="h-4 w-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        <span class="hidden sm:inline">Export Excel</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>

            <!-- Mobile View -->
            <div class="block lg:hidden space-y-4">
                @foreach ($pagedMonitoringTemplates as $item)
                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                        <div class="mb-3">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-tight">{{ $item['title'] }}</h3>
                            @if (!empty($item['description']))
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $item['description'] }}</p>
                            @endif
                        </div>
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center justify-between py-2 px-3 bg-slate-50 dark:bg-slate-700 rounded-lg">
                                <span class="text-xs font-medium text-gray-600 dark:text-gray-400">Total Response:</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($item['response_count'] ?? 0) }}</span>
                            </div>
                            @if (!empty($item['category']))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-slate-100 text-slate-700 dark:bg-slate-700 dark:text-slate-300 rounded">
                                    {{ $item['category'] }}
                                </span>
                            @endif
                            @if (!empty($item['form_template_version']))
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 rounded" x-tooltip="'Versi Template Form'">
                                    @svg("heroicon-m-clipboard-document-list", "h-3 w-3")
                                    v{{ $item['form_template_version'] }}
                                </span>
                            @endif
                        </div>
                        <div class="flex flex-col gap-2">
                            <a href="{{ route('table-view') }}?form_template_id={{ $item['id'] }}&imut_profile_id={{ $item['imut_profile_id'] ?? '' }}&unit_kerja_id={{ $item['unit_kerja_id'] ?? '' }}&period={{ $selectedMonth }}"
                                target="_blank"
                                class="flex items-center justify-center gap-2 w-full px-3 py-2 text-sm font-medium text-primary-700 dark:text-primary-400 bg-primary-50 dark:bg-primary-900/20 hover:bg-primary-100 dark:hover:bg-primary-900/30 rounded-lg border border-primary-200 dark:border-primary-800 transition-colors">
                                <span>Lihat Tabel</span>
                            </a>
                            <a href="{{ route('export.monitoring', $item['id']) }}?month={{ $selectedMonth }}"
                                target="_blank"
                                class="flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-medium text-green-700 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/30 rounded-lg border border-green-200 dark:border-green-800 transition-colors">
                                <span>Download Excel</span>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Empty State -->
            @if (empty($pagedMonitoringTemplates))
                <div class="text-center py-16">
                    <div class="space-y-4">
                        <div class="w-16 h-16 bg-slate-100 dark:bg-slate-700 rounded-2xl flex items-center justify-center mx-auto">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Tidak ada data</h3>
                            <p class="text-gray-500 dark:text-gray-400">Belum ada data monitoring untuk periode ini</p>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Pagination Controls --}}
            @if ($monitoringTotalPages > 1)
                <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-700">
                    <span class="text-xs text-slate-500 dark:text-slate-400">
                        Halaman {{ $monitoringPage }} dari {{ $monitoringTotalPages }}
                        &bull; {{ $monitoringTotal }} indikator
                    </span>
                    <div class="flex items-center gap-1">
                        <button wire:click="goToMonitoringPage({{ $monitoringPage - 1 }})" @disabled($monitoringPage <= 1)
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                            @svg("heroicon-m-chevron-left", "h-4 w-4")
                        </button>
                        @for ($p = max(1, $monitoringPage - 2); $p <= min($monitoringTotalPages, $monitoringPage + 2); $p++)
                            <button wire:click="goToMonitoringPage({{ $p }})"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-lg border text-xs font-medium transition
                                    {{ $p === $monitoringPage
                                        ? 'border-primary-500 bg-primary-600 text-white'
                                        : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400' }}">
                                {{ $p }}
                            </button>
                        @endfor
                        <button wire:click="goToMonitoringPage({{ $monitoringPage + 1 }})" @disabled($monitoringPage >= $monitoringTotalPages)
                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400">
                            @svg("heroicon-m-chevron-right", "h-4 w-4")
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </div>