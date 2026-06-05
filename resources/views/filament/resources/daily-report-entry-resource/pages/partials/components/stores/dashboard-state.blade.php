{{-- 
    Dashboard State Alpine.js Store
    Centralized state management for the daily report entry dashboard
    
    This component provides:
    - Date selection and navigation
    - Indicators and matrix data
    - Monitoring templates
    - Category colors
    - Responsive state management
--}}

@props([
    'selectedDate' => null,
    'selectedMonth' => null,
    'indicators' => [],
    'matrixData' => [],
    'categoryColors' => [],
    'monitoringTemplates' => [],
])

x-data="{
    // ============================================
    // STATE: Date & Navigation
    // ============================================
    selectedDate: '{{ $selectedDate ?: now()->format('Y-m-d') }}',
    selectedMonth: '{{ $selectedMonth ?: now()->format('Y-m') }}',
    currentDate: new Date('{{ ($selectedMonth ?: now()->format('Y-m')) }}-01'),
    
    // ============================================
    // STATE: UI & Loading
    // ============================================
    isMobile: false,
    isDateLoading: false,
    isLoadingMonth: false,
    slideOverClientOpen: false,
    slideOverLoading: false,
    slideOverRequest: null,
    
    // ============================================
    // STATE: Filters
    // ============================================
    searchQuery: '',
    statusFilter: 'all',
    monitoringSearchQuery: '',
    monitoringMonth: '{{ $selectedMonth ?: now()->format('Y-m') }}',
    
    // ============================================
    // STATE: Data
    // ============================================
    indicators: @js($indicators),
    matrixData: @js($matrixData),
    monitoringData: @js($monitoringTemplates),
    categoryColors: @js($categoryColors),
    
    // ============================================
    // INITIALIZATION
    // ============================================
    init() {
        this.initResize();
        this.selectToday();
        this.ensureValidSelectedDate();
        this.monitoringMonth = this.selectedMonth;
    },
    
    // ============================================
    // DATE MANAGEMENT
    // ============================================
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
    
    // ============================================
    // SLIDE-OVER MANAGEMENT
    // ============================================
    async openSlideOverFast(indicatorId, date) {
        const resolvedDate = date || '{{ now()->format('Y-m-d') }}';
        
        this.slideOverRequest = {
            indicatorId: Number(indicatorId),
            date: resolvedDate,
        };
        this.slideOverClientOpen = true;
        this.slideOverLoading = true;
        
        try {
            await \$wire.openSlideOver(indicatorId, resolvedDate);
        } catch (error) {
            console.error('❌ Failed to open slide-over:', error);
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
        \$wire.closeSlideOver();
    },
    
    // ============================================
    // MATRIX SNAPSHOT
    // ============================================
    async loadMatrixDataAsync() {
        this.isDateLoading = true;
        
        try {
            const snapshot = await \$wire.getMatrixSnapshot();
            
            if (snapshot) {
                this.selectedMonth = snapshot.selectedMonth || this.selectedMonth;
                this.selectedDate = snapshot.selectedDate || this.selectedDate;
                this.indicators = snapshot.indicators || [];
                this.matrixData = snapshot.matrixData || {};
                this.daysInMonth = snapshot.daysInMonth || [];
                this.categoryColors = snapshot.categoryColors || {};
                this.monitoringMonth = this.selectedMonth;
                this.currentDate = new Date(\`\${this.selectedMonth}-01\`);
            }
        } catch (error) {
            console.error('❌ Failed to sync matrix snapshot:', error);
        } finally {
            this.isDateLoading = false;
        }
    },
    
    // ============================================
    // FILTERING & COMPUTED
    // ============================================
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
                return state === this.statusFilter;
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
    
    // ============================================
    // STATUS & DATA HELPERS
    // ============================================
    getStatusForDate(indicatorId, selectedDate) {
        const date = new Date(selectedDate);
        const day = date.getDate();
        return this.matrixData[indicatorId]?.[day] || null;
    },
    
    getActionButton(indicatorId, selectedDate) {
        const date = new Date(selectedDate);
        const day = date.getDate();
        const cellData = this.matrixData[indicatorId]?.[day];
        const state = cellData ? cellData.cell_state : 'disabled';
        
        return { state, cellData };
    },
    
    // ============================================
    // FORMATTING UTILITIES
    // ============================================
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
    
    formatNumber(num) {
        return new Intl.NumberFormat('id-ID').format(num || 0);
    },
    
    formatImutVersion(version) {
        return version ? version.replace('/version-', 'v') : '';
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
    
    // ============================================
    // MONITORING
    // ============================================
    getMonitoringPeriodText() {
        const date = new Date(this.monitoringMonth + '-01');
        const monthName = date.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' });
        return \`Periode: Monitoring \${monthName}\`;
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
        this.monitoringMonth = \`\${year}-\${month}\`;
        
        this.loadMonitoringData();
    },
    
    loadMonitoringData() {
        this.isDateLoading = true;
        \$wire.call('loadMonitoringForPeriod', this.monitoringMonth).then(data => {
            this.monitoringData = data;
            this.isDateLoading = false;
        });
    }
}"
