<x-filament-panels::page>
    {{--
    Daily Report Entry Dashboard - Optimized Version
    ========================================
    Single-file Alpine.js dashboard dengan organized sections.
    Semua logic tetap inline (Alpine requirement) tapi organized dengan comments.
    --}}

    <div x-data="{
    selectedDate: @entangle('selectedDate').live,
    selectedMonth: @entangle('selectedMonth').live,
    currentDate: new Date('{{ ($selectedMonth ?: now()->format('Y-m')) }}-01'),

    isMobile: false,
    isDateLoading: false,
    isLoadingMonth: false,
    slideOverClientOpen: false,
    slideOverLoading: false,
    slideOverRequest: null,

    monitoringSearchQuery: '',

    // Client-side search/filter state (no server round-trip needed)
    searchQuery: '',
    statusFilter: 'all',

    indicators: @js($indicators),
    matrixData: @js($matrixData),
    monitoringData: @js($monitoringTemplates),
    categoryColors: @js($categoryColors),

    init() {
        this.initResize();
        this.selectToday();
        this.ensureValidSelectedDate();

        this.$watch('selectedDate', (newDate) => {
            if (newDate) {
                $wire.call('loadAllReportCounts');
            }
        });
    },

    isCurrentOrFutureMonth() {
        const current = new Date();
        const selected = new Date(this.selectedMonth + '-01');

        return (
            selected.getFullYear() > current.getFullYear() ||
            (
                selected.getFullYear() === current.getFullYear() &&
                selected.getMonth() >= current.getMonth()
            )
        );
    },

    initResize() {
        this.isMobile = window.innerWidth < 1024;

        window.addEventListener('resize', () => {
            this.isMobile = window.innerWidth < 1024;
        });
    },

    ensureValidSelectedDate() {
        if (!this.selectedDate || this.selectedDate === 'null' || this.selectedDate === '') {
            this.selectedDate = '{{ now()->format('Y-m-d') }}';
        }
    },

    selectToday() {
        const today = new Date();
        const month = today.toISOString().slice(0, 7);

        if (month === this.selectedMonth) {
            this.selectedDate = today.toISOString().slice(0, 10);
        } else {
            this.ensureValidSelectedDate();
        }
    },

    selectDate(date) {
        this.selectedDate = date || '{{ now()->format('Y-m-d') }}';
    },

    async openSlideOverFast(indicatorId, date) {
        const resolvedDate = date || '{{ now()->format('Y-m-d') }}';

        this.slideOverRequest = {
            indicatorId: Number(indicatorId),
            date: resolvedDate,
        };

        this.slideOverClientOpen = true;
        this.slideOverLoading = true;

        try {
            await $wire.openSlideOver(indicatorId, resolvedDate);
        } catch (error) {
            console.error('[Slide Over] Gagal membuka data indikator:', error);

            this.slideOverClientOpen = false;
            this.slideOverRequest = null;
        } finally {
            this.slideOverLoading = false;
        }
    },

    closeSlideOverFast() {
        this.slideOverClientOpen = false;
        this.slideOverLoading = false;
        this.slideOverRequest = null;

        $wire.closeSlideOver();
    },

    async loadMatrixDataAsync() {
        this.isDateLoading = true;

        try {
            const snapshot = await $wire.getMatrixSnapshot();

            if (snapshot) {
                this.selectedMonth = snapshot.selectedMonth || this.selectedMonth;
                this.selectedDate = snapshot.selectedDate || this.selectedDate;
                this.indicators = snapshot.indicators || [];
                this.matrixData = snapshot.matrixData || {};
                this.daysInMonth = snapshot.daysInMonth || [];
                this.categoryColors = snapshot.categoryColors || {};
                this.monitoringMonth = this.selectedMonth;
                this.currentDate = new Date(`${this.selectedMonth}-01`);
            }
        } catch (error) {
            console.error('[Matrix] Gagal sinkronisasi data:', error);
        } finally {
            this.isDateLoading = false;
        }
    },

    get filteredIndicators() {
        let filtered = this.indicators;

        if (this.searchQuery.trim()) {
            const query = this.searchQuery.toLowerCase();
            filtered = filtered.filter(indicator =>
                indicator.title.toLowerCase().includes(query) ||
                (indicator.category && indicator.category.toLowerCase().includes(query))
            );
        }

        if (this.statusFilter && this.statusFilter !== 'all') {
            const date = new Date(this.selectedDate);
            const day = date.getDate();

            filtered = filtered.filter(indicator => {
                const cellData = this.matrixData[indicator.id]?.[day];
                const state = cellData ? cellData.cell_state : 'disabled';

                if (this.statusFilter === 'pending') {
                    return state === 'pending';
                } else if (this.statusFilter === 'done') {
                    return state !== 'pending' && state !== 'disabled';
                }
                return true;
            });
        }

        return filtered;
    },

    get filteredMonitoringData() {
        let filtered = this.monitoringData;

        if (this.monitoringSearchQuery.trim()) {
            const query = this.monitoringSearchQuery.toLowerCase();

            filtered = filtered.filter(item =>
                item.title.toLowerCase().includes(query) ||
                (item.category && item.category.toLowerCase().includes(query)) ||
                (item.profile_name && item.profile_name.toLowerCase().includes(query))
            );
        }

        return filtered;
    },

    getStatusForDate(indicatorId, selectedDate) {
        const date = new Date(selectedDate);
        const day = date.getDate();

        return this.matrixData[indicatorId]?.[day] || null;
    },

    getActionButton(indicatorId, selectedDate) {
        const date = new Date(selectedDate);
        const day = date.getDate();
        const cellData = this.matrixData[indicatorId]?.[day] || null;

        return {
            state: cellData ? cellData.cell_state : 'disabled',
            cellData: cellData,
        };
    },

    formatDate(dateString) {
        const date = new Date(dateString);

        return date.toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
        });
    },

    getMonthName() {
        const date = new Date(this.selectedMonth + '-01');

        return date.toLocaleDateString('id-ID', {
            month: 'long',
            year: 'numeric',
        });
    },

    formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num || 0);
    },

    formatImutVersion(version) {
        if (!version) return '';

        return version.replace('/version-', 'v');
    },

    getCategoryColor(category) {
        return this.categoryColors[category] ||
            'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    },

    isToday(dateString) {
        const today = new Date().toDateString();
        const checkDate = new Date(dateString).toDateString();

        return today === checkDate;
    },

    isFutureDate(dateString) {
        const today = new Date();
        today.setHours(23, 59, 59, 999);

        const checkDate = new Date(dateString);

        return checkDate > today;
    },

    getMonitoringPeriodText() {
        const date = new Date(this.selectedMonth + '-01');

        const monthName = date.toLocaleDateString('id-ID', {
            month: 'long',
            year: 'numeric',
        });

        return `Periode: Monitoring ${monthName}`;
    },

    changeMonitoringPeriod(direction) {
        const date = new Date(this.selectedMonth + '-01');

        if (direction === 'prev') {
            date.setMonth(date.getMonth() - 1);
        } else if (direction === 'next') {
            date.setMonth(date.getMonth() + 1);
        } else if (direction === 'current') {
            this.isLoadingMonth = true;
            $wire.call('selectMonth', '{{ now()->format('Y-m') }}').then(() => {
                this.isLoadingMonth = false;
            }).catch(() => {
                this.isLoadingMonth = false;
            });
            return;
        }

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const newPeriod = `${year}-${month}`;

        this.isLoadingMonth = true;
        $wire.call('selectMonth', newPeriod).then(() => {
            this.isLoadingMonth = false;
        }).catch(() => {
            this.isLoadingMonth = false;
        });
    },
}" x-cloak>

        {{-- Full Screen Loading Overlay --}}
        <div x-show="isDateLoading" x-transition.opacity.duration.500ms
            class="fixed inset-0 bg-white/70 dark:bg-slate-900/70 backdrop-blur-md z-[9999]" style="display: none;">
        </div>

        <div class="space-y-6 relative">

            @include('filament.resources.daily-report-entry-resource.pages.partials.components.header.header-section')

            {{-- Main Content --}}
            <div x-show="$wire.currentView === 'input'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                {{-- Sidebar: Date Navigation --}}
                <div class="lg:col-span-3" x-data="{}">
                    @include('filament.resources.daily-report-entry-resource.pages.partials.components.navigation.date-navigation')
                </div>

                {{-- Main Content: Indicators --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 lg:col-span-9 overflow-y-auto space-x-2"
                    x-data="{ 
                    contentSelectedDate: @entangle('selectedDate'),
                    
                    init() {
                        this.$watch('contentSelectedDate', (newDate) => {
                            if (newDate && newDate !== 'null' && newDate !== '') {
                                this.selectedDate = newDate;
                            } else {
                                if (!this.selectedDate || this.selectedDate === 'null' || this.selectedDate === '') {
                                    this.selectedDate = '{{ now()->format('Y-m-d') }}';
                                }
                            }
                        });
                        
                        if (this.contentSelectedDate && this.contentSelectedDate !== 'null' && this.contentSelectedDate !== '') {
                            this.selectedDate = this.contentSelectedDate;
                        } else {
                            this.selectedDate = '{{ now()->format('Y-m-d') }}';
                        }
                    }
                }">

                    <div wire:loading
                        wire:target="previousMonth, nextMonth, selectMonth, selectDate, loadAllReportCounts, indicatorSearch, statusFilter, goToIndicatorPage"
                        class="bg-white w-full dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                        <div
                            class="flex animate-pulse pb-2 mb-2 border-b border-slate-200 dark:border-slate-700 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="w-full space-y-3">
                                <div class="flex items-center gap-2">
                                    <div class="h-5 w-5 rounded-md bg-slate-200 dark:bg-slate-700"></div>
                                    <div class="h-5 w-48 rounded bg-slate-200 dark:bg-slate-700"></div>
                                </div>
                                <div class="h-4 w-72 max-w-full rounded bg-slate-200 dark:bg-slate-700"></div>
                                <div class="flex items-center gap-1">
                                    <div class="h-4 w-4 rounded-full bg-slate-200 dark:bg-slate-700"></div>
                                    <div class="h-3 w-28 rounded bg-slate-200 dark:bg-slate-700"></div>
                                </div>
                            </div>
                        </div>
                        @for ($i = 0; $i < 6; $i++)
                            <div
                                class="w-full animate-pulse rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
                                <div class="flex w-full items-start gap-3">
                                    <div class="h-10 w-10 shrink-0 rounded-lg bg-slate-200 dark:bg-slate-700"></div>
                                    <div class="min-w-0 flex-1 space-y-3">
                                        <div class="h-4 w-2/3 rounded bg-slate-200 dark:bg-slate-700"></div>
                                        <div class="space-y-2">
                                            <div class="h-3 w-full rounded bg-slate-200 dark:bg-slate-700"></div>
                                            <div class="h-3 w-4/5 rounded bg-slate-200 dark:bg-slate-700"></div>
                                        </div>
                                        <div class="flex items-center justify-between pt-1">
                                            <div class="h-3 w-24 rounded bg-slate-200 dark:bg-slate-700"></div>
                                            <div class="h-6 w-20 rounded-full bg-slate-200 dark:bg-slate-700"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div wire:loading.remove
                        wire:target="previousMonth, nextMonth, selectMonth, selectDate, loadAllReportCounts, indicatorSearch, statusFilter, goToIndicatorPage"
                        class="h-full p-6  lg:space-x-0 lg:space-y-1 max-h-none lg:max-h-[800px]">
                        <div class="sticky top-0 z-10 bg-white dark:bg-slate-800">
                            @include('filament.resources.daily-report-entry-resource.pages.partials.components.navigation.date-header')

                            {{-- Search & Filter Bar --}}
                            <div
                                class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between bg-white/95 pb-4 backdrop-blur dark:bg-slate-800/95">
                                {{-- Livewire Search --}}
                                <div class="relative w-full sm:w-64 mb-4">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                        @svg("heroicon-o-magnifying-glass", "h-4 w-4 text-slate-400")
                                    </div>
                                    <input type="text" wire:model.live.debounce.300ms="indicatorSearch"
                                        placeholder="Cari indikator..."
                                        class="block w-full pl-10 pr-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent" />
                                </div>

                                {{-- Status Filter --}}
                                <div class="flex items-center gap-2">
                                    <select wire:model.live="statusFilter"
                                        class="rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 focus:border-primary-500 focus:ring-2 focus:ring-primary-500 dark:border-slate-700 dark:bg-slate-700 dark:text-slate-300">
                                        <option value="all">Semua Status</option>
                                        <option value="pending">Belum Diisi</option>
                                        <option value="done">Sudah Diisi</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- Indicators List (Livewire server-side) --}}
                        <div class="space-y-3">
                            @forelse ($filteredIndicators as $indicator)
                                <div wire:key="indicator-{{ $indicator['id'] }}"
                                    class="indicator-card rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0 flex-1">
                                                    <h3
                                                        class="text-sm font-semibold leading-snug text-slate-900 dark:text-white">
                                                        {{ $indicator['title'] }}
                                                    </h3>
                                                    <div class="mt-1 flex items-center gap-2">
                                                        <span
                                                            class="text-xs italic text-gray-500 dark:text-gray-400">profile
                                                            version:</span>
                                                        <span
                                                            class="inline-flex items-center gap-1 text-xs italic text-gray-500 dark:text-gray-400">
                                                            @svg("heroicon-m-document-text", "w-3 h-3")
                                                            {{ $indicator['imut_profile_version'] ?? '-' }}
                                                        </span>
                                                    </div>
                                                    @if (!empty($indicator['category']))
                                                        <div class="mt-3">
                                                            <span
                                                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium {{ $categoryColors[$indicator['category']] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                                                @svg("heroicon-m-tag", "h-3 w-3 hidden sm:block")
                                                                {{ $indicator['category'] }}
                                                            </span>
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="mt-2">
                                                @php $count = $reportCounts[$indicator['id']] ?? 0; @endphp
                                                <span
                                                    class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium {{ $count > 0 ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300' }}">
                                                    @svg("heroicon-m-document-text", "h-3 w-3 hidden sm:block")
                                                    {{ $count }} laporan
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Action buttons — still use Alpine for slide-over --}}
                                        <div class="flex shrink-0 gap-2 lg:justify-end"
                                            x-data="{ indicator: @js($indicator) }">
                                            @include('filament.resources.daily-report-entry-resource.pages.partials.components.indicators.action-buttons')
                                        </div>
                                    </div>
                                </div>
                            @empty
                                @include('filament.resources.daily-report-entry-resource.pages.partials.components.indicators.indicators-empty-state')
                            @endforelse
                        </div>

                        {{-- Pagination Controls --}}
                        <div class="pb-10 pt-2">
                            @if ($indicatorTotalPages > 1)
                                <div
                                    class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 dark:border-slate-700">
                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        Halaman {{ $indicatorPage }} dari {{ $indicatorTotalPages }}
                                        &bull; {{ $indicatorTotal }} indikator
                                    </span>

                                    <div class="flex items-center gap-1">
                                        {{-- Prev --}}
                                        <button wire:click="goToIndicatorPage({{ $indicatorPage - 1 }})"
                                            @disabled($indicatorPage <= 1)
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700">
                                            @svg("heroicon-m-chevron-left", "h-4 w-4")
                                        </button>

                                        {{-- Page numbers --}}
                                        @for ($p = max(1, $indicatorPage - 2); $p <= min($indicatorTotalPages, $indicatorPage + 2); $p++)
                                                                    <button wire:click="goToIndicatorPage({{ $p }})"
                                                                        class="inline-flex h-8 w-8 items-center justify-center rounded-lg border text-xs font-medium transition
                                                                                                                                                                                                                                        {{ $p === $indicatorPage
                                            ? 'border-primary-500 bg-primary-600 text-white'
                                            : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700' }}">
                                                                        {{ $p }}
                                                                    </button>
                                        @endfor

                                        {{-- Next --}}
                                        <button wire:click="goToIndicatorPage({{ $indicatorPage + 1 }})"
                                            @disabled($indicatorPage >= $indicatorTotalPages)
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-400 dark:hover:bg-slate-700">
                                            @svg("heroicon-m-chevron-right", "h-4 w-4")
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @include('filament.resources.daily-report-entry-resource.pages.partials.components.monitoring.monitoring-view')
        </div>

        @include('filament.resources.daily-report-entry-resource.pages.partials.components.modal.slide-over')
        @include('filament.resources.daily-report-entry-resource.pages.partials.components.scripts.scripts-styles')
    </div>
</x-filament-panels::page>