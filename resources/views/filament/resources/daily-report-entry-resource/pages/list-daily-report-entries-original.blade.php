<x-filament-panels::page>
    <div x-data="{
            selectedDate: '{{ request()->query('selectedDate') ? request()->query('selectedDate') : now()->format('Y-m-d') }}',
            selectedMonth: '{{ request()->query('selectedMonth') ? request()->query('selectedMonth') : now()->format('Y-m') }}',
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

                        <!-- Indicators List -->
                        <div class="space-y-4 max-h-[600px] overflow-y-auto">
                            <!-- Desktop View -->
                            <div class="hidden lg:block">
                                <template x-for="indicator in filteredIndicators" :key="indicator.id">
                                    @include('filament.resources.daily-report-entry-resource.pages.partials.components.indicators.desktop-indicator-card')
                                </template>
                            </div>

                            <!-- Mobile View -->
                            <div class="block lg:hidden space-y-4">
                                <template x-for="indicator in filteredIndicators" :key="indicator.id">
                                    @include('filament.resources.daily-report-entry-resource.pages.partials.components.mobile.mobile-indicator-card')
                                </template>
                            </div>

                            @include('filament.resources.daily-report-entry-resource.pages.partials.components.indicators.indicators-empty-state')
                        </div>
                    </div>
                </div>
            </div>

            @include('filament.resources.daily-report-entry-resource.pages.partials.components.monitoring.monitoring-view')
            @include('filament.resources.daily-report-entry-resource.pages.partials.components.monitoring.legend')
        </div>

        {{-- Slide-over MOVED OUTSIDE conditional views to prevent display: none from parent --}}
        @include('filament.resources.daily-report-entry-resource.pages.partials.components.modal.slide-over')

        {{-- Scripts and styles --}}
        @include('filament.resources.daily-report-entry-resource.pages.partials.components.scripts.scripts-styles')
    </div>
</x-filament-panels::page>