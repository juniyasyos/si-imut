{{-- 
    Indicators Loader Store
    Handles lazy loading of report counts for indicators
    
    Features:
    - Batch loading of report counts
    - Loading state management
    - Cache of loaded counts
--}}

x-data="{
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
            await \$wire.call('refreshMatrixData');
            await this.loadReportCount(indicatorId);
            setTimeout(() => this.refreshing[indicatorId] = false, 300);
        } catch (error) {
            console.error('❌ Error refreshing status:', error);
            this.refreshing[indicatorId] = false;
        }
    },
    
    async loadReportCount(indicatorId) {
        if (!indicatorId) {
            this.reportCounts[indicatorId] = 0;
            this.reportCountDate[indicatorId] = null;
            return;
        }
        
        const currentDate = this.selectedDate || '{{ now()->format('Y-m-d') }}';
        this.reportCountsLoading[indicatorId] = true;
        
        try {
            const count = await \$wire.call('getReportCountForIndicatorDate', indicatorId, currentDate);
            this.reportCounts[indicatorId] = Number(count || 0);
            this.reportCountDate[indicatorId] = currentDate;
        } catch (error) {
            console.error('❌ Error loading report count:', error);
            this.reportCounts[indicatorId] = 0;
        } finally {
            this.reportCountsLoading[indicatorId] = false;
        }
    },
    
    async loadReportCountsBatch(indicatorIds) {
        if (!indicatorIds || indicatorIds.length === 0) return;
        
        for (let i = 0; i < indicatorIds.length; i += this.batchSize) {
            const batch = indicatorIds.slice(i, i + this.batchSize);
            
            // Load batch in parallel
            await Promise.all(
                batch.map(id => this.loadReportCount(id))
            );
            
            // Small delay before next batch
            if (i + this.batchSize < indicatorIds.length) {
                await new Promise(resolve => setTimeout(resolve, 50));
            }
        }
    },
    
    getCategoryColor(category) {
        return this.categoryColors?.[category] || 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
    },
    
    formatImutVersion(version) {
        return version ? version.replace('/version-', 'v') : '';
    }
}"
@load-indicators.window="
    if (filteredIndicators.length > 0 && !loadingTriggered) {
        loadingTriggered = true;
        const ids = filteredIndicators.map(ind => ind.id);
        loadReportCountsBatch(ids);
    }
"
