<x-filament-panels::page>
    <div>
        <div class="space-y-6 relative" x-data="{
            selectedDate: '{{ now()->format('Y-m-d') }}',
            selectedMonth: '{{ $selectedMonth }}',
            currentDate: new Date('{{ $selectedMonth }}-01'),
            isMobile: false,
            currentView: 'input',
            searchQuery: '',
            statusFilter: 'all',
            indicators: @js($indicators),
            matrixData: @js($matrixData),
            
            init() {
                this.initResize();
                this.selectToday();
            },
            
            initResize() {
                this.isMobile = window.innerWidth < 1024;
                window.addEventListener('resize', () => { 
                    this.isMobile = window.innerWidth < 1024; 
                });
            },
            
            selectToday() {
                const today = new Date();
                const month = today.toISOString().slice(0, 7);
                if (month === this.selectedMonth) {
                    this.selectedDate = today.toISOString().slice(0, 10);
                }
            },
            
            selectDate(date) {
                this.selectedDate = date;
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
                const colors = {
                    'Keselamatan Pasien': 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400',
                    'Mutu Klinis': 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400',
                    'Manajemen': 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400',
                    'Sasaran Keselamatan': 'bg-purple-100 text-purple-800 dark:bg-purple-900/20 dark:text-purple-400',
                    'Pencegahan Infeksi': 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400',
                    'Akreditasi': 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900/20 dark:text-indigo-400'
                };
                return colors[category] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
            },

            formatImutVersion(version) {
                if (!version) return '';
                return version.replace('/version-', 'v');
            }
        }" x-cloak>

            @include('filament.resources.daily-report-entry-resource.pages.partials.components.header.header-section')

            <!-- Main Content -->
            <div x-show="currentView === 'input'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Sidebar: Date Navigation -->
                <div class="lg:col-span-3">
                    @include('filament.resources.daily-report-entry-resource.pages.partials.components.navigation.date-navigation')
                </div>

                <!-- Main Content: Indicators for Selected Date -->
                <div class="lg:col-span-9">
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
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

        {{-- Slide-over rendered outside the page wrapper to prevent overflow clipping --}}
        @include('filament.resources.daily-report-entry-resource.pages.partials.components.modal.slide-over')
    </div>

    @include('filament.resources.daily-report-entry-resource.pages.partials.components.scripts.scripts-styles')
</x-filament-panels::page>