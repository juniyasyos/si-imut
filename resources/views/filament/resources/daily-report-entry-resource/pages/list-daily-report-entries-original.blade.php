<x-filament-panels::page>
    <div x-data="{
            selectedDate: '{{ $selectedDate ?: now()->format('Y-m-d') }}',
            selectedMonth: '{{ $selectedMonth ?: now()->format('Y-m') }}',
            currentDate: new Date('{{ ($selectedMonth ?: now()->format('Y-m')) }}-01'),
            isMobile: false,
            searchQuery: '',
            statusFilter: 'all',
            indicators: @js($indicators),
            matrixData: @js($matrixData),
            monitoringData: @js($monitoringTemplates),
            // category palette generated on server based on ImutCategory model
            categoryColors: @js($categoryColors),
            monitoringSearchQuery: '',
            monitoringMonth: '{{ $selectedMonth ?: now()->format("Y-m") }}',
            isDateLoading: false,
            isLoadingMonth: false,
            slideOverClientOpen: false,
            slideOverLoading: false,
            slideOverRequest: null,

            async openSlideOverFast(indicatorId, date) {
                const resolvedDate = date || '{{ now()->format("Y-m-d") }}';

                this.slideOverRequest = {
                    indicatorId: Number(indicatorId),
                    date: resolvedDate,
                };
                this.slideOverClientOpen = true;
                this.slideOverLoading = true;

                console.log('🎪 [Fast Open] Opening slide-over immediately:', this.slideOverRequest);

                try {
                    await $wire.openSlideOver(indicatorId, resolvedDate);
                } catch (error) {
                    console.error('🎪 [Fast Open] Failed to open slide-over:', error);
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
                    console.error('📊 [Alpine] Failed to sync matrix snapshot:', error);
                } finally {
                    this.isDateLoading = false;
                }
            },
            
            init() {
                console.log('📊 [Alpine init] Starting init');
                console.log('📊 [Alpine init] Initial state:', {
                    selectedDate: this.selectedDate,
                    selectedMonth: this.selectedMonth,
                });
                this.initResize();
                this.selectToday();
                this.ensureValidSelectedDate();
                this.monitoringMonth = this.selectedMonth;
                console.log('📊 [Alpine init] After init:', {
                    selectedDate: this.selectedDate,
                    selectedMonth: this.selectedMonth,
                });
            },
            
            initResize() {
                this.isMobile = window.innerWidth < 1024;
                window.addEventListener('resize', () => { 
                    this.isMobile = window.innerWidth < 1024; 
                });
            },
            
            ensureValidSelectedDate() {
                // Ensure selectedDate always has a valid value
                if (!this.selectedDate) {
                    this.selectedDate = '{{ now()->format("Y-m-d") }}';
                    console.log('📊 [ensureValidSelectedDate] Set to:', this.selectedDate);
                }
                console.log('📊 [ensureValidSelectedDate] Final:', this.selectedDate);
            },
            
            selectToday() {
                const today = new Date();
                const month = today.toISOString().slice(0, 7);
                if (month === this.selectedMonth) {
                    this.selectedDate = today.toISOString().slice(0, 10);
                    console.log('📊 [selectToday] Updated to:', this.selectedDate);
                } else {
                    // If not viewing current month, ensure we have a valid date
                    this.ensureValidSelectedDate();
                }
            },
            
            selectDate(date) {
                const oldDate = this.selectedDate;
                this.selectedDate = date || '{{ now()->format("Y-m-d") }}';
                console.log('📊 [selectDate] Changed from', oldDate, 'to:', this.selectedDate);
            },
            
            get filteredIndicators() {
                let filtered = this.indicators;
                
                // Search filter
                if (this.searchQuery.trim()) {
                    const query = this.searchQuery.toLowerCase();
                    filtered = filtered.filter(indicator => 
                        indicator.title.toLowerCase().includes(query) ||
                        (indicator.category && indicator.category.toLowerCase().includes(query))
                    );
                }
                
                // Status filter
                if (this.statusFilter && this.statusFilter !== 'all') {
                    const date = new Date(this.selectedDate);
                    const day = date.getDate();
                    
                    filtered = filtered.filter(indicator => {
                        const cellData = this.matrixData[indicator.id] && this.matrixData[indicator.id][day];
                        const state = cellData ? cellData.cell_state : 'disabled';
                        return state === this.statusFilter;
                    });
                }
                
                return filtered;
            },
            
            getStatusForDate(indicatorId, selectedDate) {
                const date = new Date(selectedDate);
                const day = date.getDate();
                const cellData = this.matrixData[indicatorId] && this.matrixData[indicatorId][day];
                return cellData || null;
            },
            
            getActionButton(indicatorId, selectedDate) {
                const date = new Date(selectedDate);
                const day = date.getDate();
                const cellData = this.matrixData[indicatorId] && this.matrixData[indicatorId][day];
                const state = cellData ? cellData.cell_state : 'disabled';
                return {
                    state: state,
                    cellData: cellData
                };
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
            
            formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            },
            
            getMonthName() {
                const date = new Date(this.selectedMonth + '-01');
                return date.toLocaleDateString('id-ID', { 
                    month: 'long', 
                    year: 'numeric' 
                });
            },
            
            getCategoryColor(category) {
                // look up class generated from the database; fall back to gray if
                // nothing matches (e.g. category was deleted but earlier indicator
                // still kept the name)
                return this.categoryColors[category] ||
                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
            },

            formatImutVersion(version) {
                if (!version) return '';
                return version.replace('/version-', 'v');
            },

            // Monitoring functions
            get filteredMonitoringData() {
                let filtered = this.monitoringData;
                
                // Search filter
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

            getMonitoringPeriodText() {
                const date = new Date(this.monitoringMonth + '-01');
                const monthName = date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
                return `Periode: Monitoring ${monthName}`;
            },

            changeMonitoringPeriod(direction) {
                const date = new Date(this.monitoringMonth + '-01');
                
                if (direction === 'prev') {
                    date.setMonth(date.getMonth() - 1);
                } else if (direction === 'next') {
                    date.setMonth(date.getMonth() + 1);
                } else if (direction === 'current') {
                    this.monitoringMonth = '{{ now()->format("Y-m") }}';
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
                $wire.call('loadMonitoringForPeriod', this.monitoringMonth).then(data => {
                    this.monitoringData = data;
                    this.isDateLoading = false;
                });
            },
            
            formatNumber(num) {
                return new Intl.NumberFormat('id-ID').format(num || 0);
            }
        }" x-cloak>

        <!-- Full Screen Loading Overlay -->
        <div x-show="isDateLoading" x-transition.opacity.duration.500ms
            class="fixed inset-0 bg-white/70 dark:bg-slate-900/70 backdrop-blur-md z-[9999]" style="display: none;">
        </div>

        <div class="space-y-6 relative">

            @include('filament.resources.daily-report-entry-resource.pages.partials.components.header.header-section')

            <!-- Main Content -->
            <div x-show="$wire.currentView === 'input'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Sidebar: Date Navigation (Livewire Isolated) -->
                <div class="lg:col-span-3" x-data="{}">
                    @include('filament.resources.daily-report-entry-resource.pages.partials.components.navigation.date-navigation')
                </div>

                <!-- Main Content: Indicators for Selected Date (Alpine.js Isolated) -->
                <div class="lg:col-span-9" x-data="{ 
                         contentSelectedDate: @entangle('selectedDate'),
                         
                         init() {
                             // Watch for Livewire selectedDate changes and sync to Alpine
                             this.$watch('contentSelectedDate', (newDate) => {
                                 // Ensure we don't accept null or invalid dates
                                 if (newDate && newDate !== 'null' && newDate !== '') {
                                     this.selectedDate = newDate;
                                 } else {
                                     // If Livewire sends null/empty, keep current selectedDate or use today
                                     if (!this.selectedDate || this.selectedDate === 'null' || this.selectedDate === '') {
                                         this.selectedDate = '{{ now()->format("Y-m-d") }}';
                                     }
                                 }
                             });
                             
                             // Initialize with current selectedDate, but validate it
                             if (this.contentSelectedDate && this.contentSelectedDate !== 'null' && this.contentSelectedDate !== '') {
                                 this.selectedDate = this.contentSelectedDate;
                             } else {
                                 this.selectedDate = '{{ now()->format("Y-m-d") }}';
                             }
                         }
                     }">

                    <div wire:loading
                        class="bg-white w-full dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">

                        <div
                            class="flex animate-pulse pb-2 mb-2 border-b border-slate-200 dark:border-slate-700 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="w-full space-y-3">
                                <!-- Title -->
                                <div class="flex items-center gap-2">
                                    <div class="h-5 w-5 rounded-md bg-slate-200 dark:bg-slate-700"></div>
                                    <div class="h-5 w-48 rounded bg-slate-200 dark:bg-slate-700"></div>
                                </div>

                                <!-- Unit Kerja -->
                                <div class="h-4 w-72 max-w-full rounded bg-slate-200 dark:bg-slate-700"></div>

                                <!-- Info -->
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
                        class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                        @include('filament.resources.daily-report-entry-resource.pages.partials.components.navigation.date-header')

                        <!-- Indicators List - Lazy Loading Optimization -->
                        <div class="space-y-4 max-h-[600px] overflow-y-auto" x-data="{
                                 reportCounts: {},
                                 reportCountsLoading: {},
                                 refreshing: {},
                                 reportCountDate: {},
                                 batchSize: 5,
                                 loadingTriggered: false,

                                 async refreshStatus(indicatorId) {
                                     if (this.refreshing[indicatorId]) return;
                                     this.refreshing[indicatorId] = true;
                                     try {
                                         await $wire.call('refreshMatrixData');
                                         await this.loadReportCount(indicatorId);
                                         setTimeout(() => this.refreshing[indicatorId] = false, 300);
                                     } catch (error) {
                                         console.error('Error refreshing status:', error);
                                         this.refreshing[indicatorId] = false;
                                     }
                                 },

                                 async loadReportCount(indicatorId) {
                                     if (!indicatorId || !selectedDate) {
                                         this.reportCounts[indicatorId] = 0;
                                         this.reportCountDate[indicatorId] = null;
                                         return;
                                     }

                                     const currentDate = selectedDate;
                                     this.reportCountsLoading[indicatorId] = true;

                                     try {
                                         const count = await $wire.call('getReportCountForIndicatorDate', indicatorId, currentDate);
                                         this.reportCounts[indicatorId] = Number(count || 0);
                                         this.reportCountDate[indicatorId] = currentDate;
                                     } catch (error) {
                                         console.error('Error loading report count:', error);
                                         this.reportCounts[indicatorId] = 0;
                                     } finally {
                                         this.reportCountsLoading[indicatorId] = false;
                                     }
                                 },

                                 async loadReportCountsBatch(indicatorIds) {
                                     if (!indicatorIds || indicatorIds.length === 0) return;
                                     
                                     // Load in batches of 5 to avoid browser throttling
                                     for (let i = 0; i < indicatorIds.length; i += this.batchSize) {
                                         const batch = indicatorIds.slice(i, i + this.batchSize);
                                         
                                         // Load batch in parallel
                                         await Promise.all(
                                             batch.map(id => this.loadReportCount(id))
                                         );
                                         
                                         // Small delay before next batch (prevents UI freeze)
                                         if (i + this.batchSize < indicatorIds.length) {
                                             await new Promise(resolve => setTimeout(resolve, 50));
                                         }
                                     }
                                 },

                                 getCategoryColor(category) {
                                     return categoryColors[category] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                                 },

                                 formatImutVersion(version) {
                                     if (!version) return '';
                                     return version.replace('/version-', 'v');
                                 }
                             }" @load-indicators.window="
                                 if (filteredIndicators.length > 0 && !loadingTriggered) {
                                     loadingTriggered = true;
                                     const ids = filteredIndicators.map(ind => ind.id);
                                     loadReportCountsBatch(ids);
                                 }
                             ">

                            <template x-for="(indicator, index) in filteredIndicators" :key="indicator.id">
                                <div
                                    class="indicator-card mt-2 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-700 dark:bg-slate-800"
                                >
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                        {{-- Indicator Info --}}
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-start justify-between gap-3">
                                                <div class="min-w-0 flex-1">
                                                    <div>
                                                        <h3
                                                            class="text-sm font-semibold leading-snug text-slate-900 dark:text-white"
                                                            x-text="indicator.title"
                                                        ></h3>

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

                                                    {{-- Meta --}}
                                                    <div class="mt-4 flex flex-wrap items-center gap-2">
                                                        {{-- Category --}}
                                                        <span
                                                            x-show="indicator.category"
                                                            class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-medium"
                                                            :class="getCategoryColor(indicator.category)"
                                                        >
                                                            @svg("heroicon-m-tag", "h-3 w-3 hidden sm:block")
                                                            <span x-text="indicator.category"></span>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Report Count --}}
                                            <div class="mt-2">
                                                {{-- Skeleton Loading --}}
                                                <span
                                                    x-show="!reportCounts[indicator.id] && reportCountsLoading[indicator.id]"
                                                    class="inline-flex animate-pulse items-center gap-1 rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-500 dark:bg-slate-700 dark:text-slate-300"
                                                >
                                                    @svg("heroicon-m-document-text", "h-3 w-3 hidden sm:block")
                                                    <span>Memuat...</span>
                                                </span>

                                                {{-- Empty State --}}
                                                <span
                                                    x-show="!reportCounts[indicator.id] && !reportCountsLoading[indicator.id]"
                                                    class="inline-flex items-center gap-1 rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-500 dark:bg-slate-700 dark:text-slate-300"
                                                >
                                                    @svg("heroicon-m-document-text", "h-3 w-3 hidden sm:block")
                                                    <span>0 laporan</span>
                                                </span>

                                                {{-- Actual Count --}}
                                                <span
                                                    x-show="reportCounts[indicator.id]"
                                                    :class="reportCounts[indicator.id] > 0
                                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300'
                                                        : 'bg-slate-100 text-slate-500 dark:bg-slate-700 dark:text-slate-300'"
                                                    class="inline-flex items-center gap-1 rounded-md px-2 py-1 text-xs font-medium"
                                                >
                                                    @svg("heroicon-m-document-text", "h-3 w-3 hidden sm:block")
                                                    <span x-text="reportCounts[indicator.id] + ' laporan'"></span>
                                                </span>
                                            </div>
                                        </div>

                                        {{-- Action Button --}}
                                        <div class="flex shrink-0 gap-2 lg:justify-end">
                                            @include('filament.resources.daily-report-entry-resource.pages.partials.components.indicators.action-buttons')
                                        </div>
                                    </div>
                                </div>
                            </template>

                            @include('filament.resources.daily-report-entry-resource.pages.partials.components.indicators.indicators-empty-state')

                            <!-- Trigger batch loading after DOM settles -->
                            <script>
                                setTimeout(() => {
                                    const container = document.querySelector('[x-data*="reportCounts"]');
                                    if (container && container.__x) {
                                        container.__x.$dispatch('load-indicators');
                                    }
                                }, 200);
                            </script>
                        </div>
                    </div>
                </div>
            </div>

            @include('filament.resources.daily-report-entry-resource.pages.partials.components.monitoring.monitoring-view')
        </div>

        {{-- Slide-over MOVED OUTSIDE conditional views to prevent display: none from parent --}}
        @include('filament.resources.daily-report-entry-resource.pages.partials.components.modal.slide-over')

        {{-- Scripts and styles --}}
        @include('filament.resources.daily-report-entry-resource.pages.partials.components.scripts.scripts-styles')
    </div>
</x-filament-panels::page>