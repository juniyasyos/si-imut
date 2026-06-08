<x-filament-panels::page>
    {{--
    Daily Report Entry Dashboard - Optimized Version
    ========================================
    Single-file Alpine.js dashboard dengan organized sections.
    Semua logic tetap inline (Alpine requirement) tapi organized dengan comments.
    --}}

    <div x-data="{
    selectedDate: '{{ $selectedDate ?: now()->format('Y-m-d') }}',
    selectedMonth: '{{ $selectedMonth ?: now()->format('Y-m') }}',
    currentDate: new Date('{{ ($selectedMonth ?: now()->format('Y-m')) }}-01'),

    isMobile: false,
    isDateLoading: false,
    isLoadingMonth: false,
    slideOverClientOpen: false,
    slideOverLoading: false,
    slideOverRequest: null,

    monitoringSearchQuery: '',
    monitoringMonth: '{{ $selectedMonth ?: now()->format('Y-m') }}',

    indicators: @js($indicators),
    matrixData: @js($matrixData),
    monitoringData: @js($monitoringTemplates),
    categoryColors: @js($categoryColors),

    reportCountsPollingInterval: null,
    pollingEnabled: true,
    pollingIntervalMs: 10000,

    init() {
        this.initResize();
        this.selectToday();
        this.ensureValidSelectedDate();
        this.monitoringMonth = this.selectedMonth;

        this.$watch('selectedDate', (newDate) => {
            if (newDate) {
                $wire.call('loadAllReportCounts');
            }
        });

        this.startReportCountsPolling();

        this.$watch(() => this.$el, () => {}, {
            destroy: () => this.stopReportCountsPolling()
        });
    },

    startReportCountsPolling() {
        if (this.reportCountsPollingInterval) {
            return;
        }

        $wire.call('loadAllReportCounts');

        this.reportCountsPollingInterval = setInterval(() => {
            if (!this.pollingEnabled) {
                return;
            }

            $wire.call('loadAllReportCounts').catch(error => {
                console.error('[Polling] Gagal memperbarui jumlah laporan:', error);
            });
        }, this.pollingIntervalMs);
    },

    stopReportCountsPolling() {
        if (this.reportCountsPollingInterval) {
            clearInterval(this.reportCountsPollingInterval);
            this.reportCountsPollingInterval = null;
        }
    },

    pauseReportCountsPolling() {
        this.pollingEnabled = false;
    },

    resumeReportCountsPolling() {
        this.pollingEnabled = true;
    },

    setPollingInterval(ms) {
        if (ms <= 0) return;

        this.pollingIntervalMs = ms;
        this.stopReportCountsPolling();
        this.startReportCountsPolling();
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
        const date = new Date(this.monitoringMonth + '-01');

        const monthName = date.toLocaleDateString('id-ID', {
            month: 'long',
            year: 'numeric',
        });

        return `Periode: Monitoring ${monthName}`;
    },

    changeMonitoringPeriod(direction) {
        const date = new Date(this.monitoringMonth + '-01');

        if (direction === 'prev') {
            date.setMonth(date.getMonth() - 1);
        } else if (direction === 'next') {
            date.setMonth(date.getMonth() + 1);
        } else if (direction === 'current') {
            this.monitoringMonth = '{{ now()->format('Y-m') }}';
            this.loadMonitoringData();
            return;
        }

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');

        this.monitoringMonth = `${year}-${month}`;
        this.loadMonitoringData();
    },

    loadMonitoringData() {
        this.isDateLoading = true;

        $wire.call('loadMonitoringForPeriod', this.monitoringMonth)
            .then(data => {
                this.monitoringData = data;
            })
            .catch(error => {
                console.error('[Monitoring] Gagal memuat data monitoring:', error);
            })
            .finally(() => {
                this.isDateLoading = false;
            });
    }
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
                <div class="lg:col-span-9" x-data="{ 
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
                        class="w-full h-full bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                        @include('filament.resources.daily-report-entry-resource.pages.partials.components.navigation.date-header')

                        {{-- Indicators List with Server-Side Filtering --}}
                        <div class="space-y-4 max-h-[600px] overflow-y-auto">

                            <template x-for="(indicator, index) in $wire.filteredIndicators" :key="indicator.id">
                                <div
                                    class="indicator-card mt-2 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-800">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0 flex-1">
                                                    <div>
                                                        <h3 class="text-sm font-semibold leading-snug text-slate-900 dark:text-white"
                                                            x-text="indicator.title"></h3>

                                                        <div class="flex items-center gap-2 mt-1">
                                                            <span
                                                                class="text-xs italic text-gray-500 dark:text-gray-400">profile
                                                                version:</span>
                                                            <span
                                                                class="inline-flex items-center gap-1 text-xs italic text-gray-500 dark:text-gray-400">
                                                                @svg("heroicon-m-document-text", "w-3 h-3")
                                                                <span
                                                                    x-text="formatImutVersion(indicator.imut_profile_version)"></span>
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="mt-4 flex flex-wrap items-center gap-2">
                                                        <span x-show="indicator.category"
                                                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                                                            :class="getCategoryColor(indicator.category)">
                                                            @svg("heroicon-m-tag", "h-3 w-3 hidden sm:block")
                                                            <span x-text="indicator.category"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-2">
                                                <span
                                                    :class="($wire.reportCounts[indicator.id] ?? 0) > 0
                                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                    : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300'"
                                                    class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium">
                                                    @svg("heroicon-m-document-text", "h-3 w-3 hidden sm:block")
                                                    <span
                                                        x-text="($wire.reportCounts[indicator.id] ?? 0) + ' laporan'"></span>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="flex shrink-0 gap-2 lg:justify-end">
                                            @include('filament.resources.daily-report-entry-resource.pages.partials.components.indicators.action-buttons')
                                        </div>
                                    </div>
                                </div>
                            </template>


                            <div x-show="!$wire.filteredIndicators || $wire.filteredIndicators.length === 0">
                                @include('filament.resources.daily-report-entry-resource.pages.partials.components.indicators.indicators-empty-state')
                            </div>
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