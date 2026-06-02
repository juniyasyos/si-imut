<!-- Alpine.js Script and Custom Styles -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dailyReportData', () => ({
            selectedDate: '{{ now()->format('Y-m-d') }}',
            selectedMonth: '{{ $selectedMonth ?: now()->format('Y-m') }}',
            currentDate: new Date('{{ ($selectedMonth ?: now()->format('Y-m')) }}-01'),
            isMobile: false,
            currentView: 'input',

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
                if (window.Livewire) {
                    try {
                        @this.call('handleDateSelected', date);
                    } catch (e) {
                        console.warn('Livewire dateSelected event failed:', e);
                    }
                }
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

            formatImutVersion(version) {
                if (!version) return '';
                return version.replace('/version-', 'v');
            }
        }));
    });

    // Parse URL params on page load and sync Alpine state
    document.addEventListener('DOMContentLoaded', function() {
        console.log('📍 [DOMContentLoaded] Page loaded');
        const urlParams = new URLSearchParams(window.location.search);
        const dateParam = urlParams.get('selectedDate');
        const monthParam = urlParams.get('selectedMonth');
        
        console.log('📍 [DOMContentLoaded] URL params from window.location.search:', {
            selectedDate: dateParam,
            selectedMonth: monthParam,
            fullSearch: window.location.search,
        });
        
        if (dateParam || monthParam) {
            console.log('📍 [DOMContentLoaded] URL params found, syncing with Alpine');
            const Alpine = window.Alpine;
            if (Alpine && Alpine.store) {
                // Try to sync Alpine data if available
                const dailyReportEl = document.querySelector('[x-data*="dailyReportData"]');
                if (dailyReportEl && dailyReportEl.__x) {
                    if (dateParam) {
                        dailyReportEl.__x.$data.selectedDate = dateParam;
                        console.log('📍 [DOMContentLoaded] Set Alpine selectedDate to:', dateParam);
                    }
                    if (monthParam) {
                        dailyReportEl.__x.$data.selectedMonth = monthParam;
                        console.log('📍 [DOMContentLoaded] Set Alpine selectedMonth to:', monthParam);
                    }
                }
            }
        } else {
            console.log('📍 [DOMContentLoaded] No URL params found, Alpine will use defaults');
        }
    });

    // Listen for URL update events from Livewire and update browser URL
    // Also sync Alpine state and reload matrix data if needed
    document.addEventListener('livewire:init', () => {
        Livewire.on('updateUrl', (urlPayload) => {
            console.log('🔗 [updateUrl event] Event received');
            console.log('🔗 [updateUrl event] URL to set:', urlPayload);
            
            if (urlPayload) {
                try {
                    // Normalize url string (Livewire 3 sometimes passes objects or arrays for events)
                    let urlString = urlPayload;
                    if (typeof urlPayload === 'object' && urlPayload !== null) {
                        urlString = urlPayload.url || (Array.isArray(urlPayload) ? urlPayload[0] : Object.values(urlPayload)[0]);
                    }
                    
                    if (typeof urlString !== 'string') {
                         console.warn('🔗 [updateUrl] Extracted URL is not a string:', urlString);
                         return;
                    }

                    console.log('🔗 [updateUrl] Updating browser URL to:', urlString);
                    
                    // Extract query params from the URL (could be from /livewire/update endpoint)
                    let urlParams = new URLSearchParams();
                    
                    // Try to parse as full URL, otherwise as query string
                    try {
                        const parsedUrl = new URL(urlString);
                        urlParams = new URLSearchParams(parsedUrl.search);
                    } catch (e) {
                        // If it fails, try treating it as a query string
                        if (urlString.includes('?')) {
                            urlParams = new URLSearchParams(urlString.split('?')[1]);
                        } else {
                            urlParams = new URLSearchParams(urlString);
                        }
                    }
                    
                    const newMonth = urlParams.get('selectedMonth');
                    const newDate = urlParams.get('selectedDate');
                    
                    console.log('🔗 [updateUrl] Parsed params:', {
                        selectedMonth: newMonth,
                        selectedDate: newDate,
                    });
                    
                    // Build proper page URL using current pathname
                    const pageUrl = window.location.pathname + '?' + urlParams.toString();
                    
                    console.log('🔗 [updateUrl] Proper page URL:', pageUrl);
                    
                    // Update browser URL without page refresh
                    window.history.replaceState({}, '', pageUrl);
                    console.log('🔗 [updateUrl] History state updated');
                    
                    const mainEl = document.querySelector('[x-data*="selectedDate"]');
                    console.log('🔗 [updateUrl] Found mainEl:', !!mainEl);
                    
                    if (mainEl && mainEl.__x) {
                        const oldMonth = mainEl.__x.$data.selectedMonth;
                        const oldDate = mainEl.__x.$data.selectedDate;
                        
                        console.log('🔗 [updateUrl] Old Alpine state:', { oldMonth, oldDate });
                        
                        // Update Alpine state from URL
                        if (newMonth && mainEl.__x.$data.selectedMonth !== newMonth) {
                            mainEl.__x.$data.selectedMonth = newMonth;
                            console.log('🔗 [updateUrl] Updated selectedMonth to:', newMonth);
                        }
                        if (newDate && mainEl.__x.$data.selectedDate !== newDate) {
                            mainEl.__x.$data.selectedDate = newDate;
                            console.log('🔗 [updateUrl] Updated selectedDate to:', newDate);
                        }
                        
                        console.log('🔗 [updateUrl] New Alpine state:', {
                            selectedMonth: mainEl.__x.$data.selectedMonth,
                            selectedDate: mainEl.__x.$data.selectedDate,
                        });
                        
                        // If month changed, reload matrix data
                        if (newMonth && oldMonth !== newMonth) {
                            console.log('🔗 [updateUrl] Month changed, will reload matrix data');
                            setTimeout(() => {
                                if (mainEl.__x.$data.loadMatrixDataAsync) {
                                    mainEl.__x.$data.loadMatrixDataAsync();
                                }
                            }, 100);
                        }
                    } else {
                        console.warn('🔗 [updateUrl] Could not find mainEl or __x data');
                    }
                } catch (error) {
                    console.error('🔗 [updateUrl] Error processing URL:', error);
                }
            } else {
                console.error('🔗 [updateUrl] No URL received:', url);
            }
        });
        console.log('🔗 [init] Livewire updateUrl listener registered');
    });
</script>

<style>
    [x-cloak] {
        display: none !important;
    }

    .date-list-item {
        transition: all 0.15s ease-in-out;
    }

    .date-list-item:hover {
        transform: translateX(2px);
    }

    .indicator-card {
        transition: all 0.2s ease-in-out;
    }

    .indicator-card:hover {
        transform: translateY(-1px);
    }
</style>