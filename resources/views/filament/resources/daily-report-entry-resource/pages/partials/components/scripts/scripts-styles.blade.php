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
        // Use Alpine.store for cleaner state management
        if (window.Alpine && !Alpine.store('matrixSnapshot')) {
            Alpine.store('matrixSnapshot', {
                snapshot: null,
                synced: false
            });
        }

        Livewire.on('matrixSnapshotUpdated', (payload) => {
            if (monthTimings.navigationStartTime) {
                monthTimings.recordEvent('matrixSnapshotUpdated', {
                    indicatorsCount: payload?.snapshot?.indicators?.length ?? 0,
                    hasMatrixData: !!payload?.snapshot?.matrixData
                });
            }

            console.log('📦 [matrixSnapshotUpdated] Event received');

            try {
                const snapshot = payload?.snapshot ?? payload;

                if (!snapshot || typeof snapshot !== 'object') {
                    console.warn('📦 [matrixSnapshotUpdated] Invalid snapshot payload:', snapshot);
                    return;
                }

                // Store snapshot untuk saat Alpine ready
                if (window.Alpine && Alpine.store('matrixSnapshot')) {
                    Alpine.store('matrixSnapshot').snapshot = snapshot;
                    Alpine.store('matrixSnapshot').synced = false;
                }

                // Wait for Alpine to be ready after Livewire finishes rendering
                const attemptSync = (attempts = 0) => {
                    if (attempts > 20) {
                        console.warn('📦 [matrixSnapshotUpdated] Timeout waiting for Alpine');
                        return;
                    }

                    // Find element lebih specific dan reliable
                    const currentEl = document.querySelector('[x-data*="dailyReportData"]');
                    
                    if (!currentEl || !currentEl.__x) {
                        if (attempts < 5) {
                            console.log('📦 [matrixSnapshotUpdated] Alpine not ready yet, retrying...', attempts);
                        }
                        setTimeout(() => attemptSync(attempts + 1), 100);
                        return;
                    }

                    const data = currentEl.__x.$data;
                    console.log('📦 [matrixSnapshotUpdated] Alpine ready on attempt', attempts, ', syncing data');

                    // Sync snapshot data to Alpine
                    if (snapshot.selectedMonth) data.selectedMonth = snapshot.selectedMonth;
                    if (snapshot.selectedDate) data.selectedDate = snapshot.selectedDate;
                    if (snapshot.indicators) data.indicators = snapshot.indicators;
                    if (snapshot.matrixData) data.matrixData = snapshot.matrixData;
                    if (snapshot.daysInMonth) data.daysInMonth = snapshot.daysInMonth;
                    if (snapshot.daysWithData) data.daysWithData = snapshot.daysWithData;
                    if (snapshot.categoryColors) data.categoryColors = snapshot.categoryColors;
                    if (snapshot.monitoringTemplates) data.monitoringData = snapshot.monitoringTemplates;

                    data.monitoringMonth = data.selectedMonth;
                    data.currentDate = new Date(`${data.selectedMonth}-01`);

                    // Mark as synced
                    if (window.Alpine && Alpine.store('matrixSnapshot')) {
                        Alpine.store('matrixSnapshot').synced = true;
                    }

                    console.log('📦 [matrixSnapshotUpdated] Data synced:', {
                        selectedMonth: data.selectedMonth,
                        selectedDate: data.selectedDate,
                        indicatorsCount: data.indicators?.length ?? 0,
                    });
                };
                attemptSync();
            } catch (error) {
                console.error('📦 [matrixSnapshotUpdated] Error syncing snapshot:', error);
            }
        });

        Livewire.on('updateUrl', (urlPayload) => {
            if (monthTimings.navigationStartTime) {
                monthTimings.recordEvent('updateUrlEvent', {
                    hasUrl: !!urlPayload
                });
            }

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
                    
                    // Extract query params from the URL
                    let urlParams = new URLSearchParams();
                    
                    try {
                        const parsedUrl = new URL(urlString);
                        urlParams = new URLSearchParams(parsedUrl.search);
                    } catch (e) {
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
                    
                    // Alpine will naturally update from Blade's re-rendered x-data
                    // No manual sync needed anymore - Blade has the new $selectedMonth value
                    console.log('🔗 [updateUrl] Alpine will update automatically from Blade re-render');
                    
                } catch (error) {
                    console.error('🔗 [updateUrl] Error processing URL:', error);
                }
            } else {
                console.error('🔗 [updateUrl] No URL received:', url);
            }
        });
        console.log('🔗 [init] Livewire updateUrl listener registered');
    });

    // Wait for Livewire to finish updating, then trigger Alpine sync if needed
    document.addEventListener('livewire:updated', () => {
        console.log('✅ [livewire:updated] Livewire finished updating, Alpine should be ready now');
        if (monthTimings.navigationStartTime) {
            monthTimings.recordEvent('livewireUpdated');
        }
    });

    // ⏱️ Month Navigation Timing Tracker - GLOBAL
    const monthTimings = {
        navigationStartTime: null,
        navigationId: null,
        timings: {},

        start(direction) {
            const id = `nav-${direction}-${Date.now()}`;
            this.navigationStartTime = performance.now();
            this.navigationId = id;
            this.timings = {
                    id,
                    direction,
                    startTime: new Date().toISOString(),
                    timestamps: {
                        navigationStart: 0,
                    }
                };
                console.log(`⏱️ [MONTH NAVIGATION START] ${direction.toUpperCase()}`);
                console.log(`⏱️ [ID] ${id}`);
            },

            recordEvent(eventName, details = {}) {
                if (!this.navigationStartTime) return;
                
                const elapsed = performance.now() - this.navigationStartTime;
                this.timings.timestamps[eventName] = elapsed;
                
                console.log(`⏱️ [${eventName}] +${elapsed.toFixed(2)}ms (elapsed: ${this.formatTime(elapsed)})`);
                if (details && Object.keys(details).length > 0) {
                    console.log(`   └─ Details:`, details);
                }
            },

            formatTime(ms) {
                if (ms < 1000) return `${ms.toFixed(0)}ms`;
                return `${(ms / 1000).toFixed(2)}s`;
            },

            end(renderComplete = false) {
                if (!this.navigationStartTime) return;
                
                const totalTime = performance.now() - this.navigationStartTime;
                
                console.log(`⏱️ [MONTH NAVIGATION COMPLETE]`);
                console.log(`⏱️ [TOTAL DURATION] ${this.formatTime(totalTime)}`);
                console.log(`⏱️ [BREAKDOWN]`);
                
                const events = Object.entries(this.timings.timestamps);
                for (let i = 0; i < events.length; i++) {
                    const [eventName, time] = events[i];
                    const nextTime = events[i + 1]?.[1] ?? totalTime;
                    const duration = nextTime - time;
                    console.log(`   ├─ ${eventName}: ${this.formatTime(time)} (${this.formatTime(duration)} duration)`);
                }
                
                console.log(`⏱️ [FULL METRICS]`, {
                    id: this.navigationId,
                    direction: this.timings.direction,
                    startTime: this.timings.startTime,
                    totalDurationMs: totalTime,
                    totalDurationFormatted: this.formatTime(totalTime),
                    timestamps: this.timings.timestamps,
                });
                
                this.navigationStartTime = null;
                this.navigationId = null;
            }
        };

    // Track month navigation start
    document.addEventListener('livewire:call', ({ detail }) => {
        if (detail.method === 'previousMonth' || detail.method === 'nextMonth') {
            const direction = detail.method === 'previousMonth' ? 'prev' : 'next';
            monthTimings.start(direction);
            monthTimings.recordEvent('navigationStart', { method: detail.method });
        }
    });

    // Track Livewire request sending
    document.addEventListener('livewire:requesting', ({ detail }) => {
        if (monthTimings.navigationStartTime) {
            monthTimings.recordEvent('livewireRequesting', {
                method: detail.method || detail.action || 'unknown'
            });
        }
    });

    // Track Livewire response received
    document.addEventListener('livewire:response', ({ detail }) => {
        if (monthTimings.navigationStartTime) {
            monthTimings.recordEvent('livewireResponseReceived', {
                responseTime: `${detail.duration || '?'}ms`,
                success: !detail.error
            });
        }
    });

    // Track Livewire finished
    document.addEventListener('livewire:finished', () => {
        if (monthTimings.navigationStartTime) {
            monthTimings.recordEvent('livewireFinished');
        }
    });

    // Track Alpine DOM rendering completion
    document.addEventListener('alpine:init', () => {
        if (monthTimings.navigationStartTime) {
            monthTimings.recordEvent('alpineInit');
        }
    });

    // Listen for when Livewire indicates loading is complete
    const observer = new MutationObserver(() => {
        if (monthTimings.navigationStartTime) {
            const loadingEl = document.querySelector('[wire\\:loading]');
            if (loadingEl && !loadingEl.offsetParent) { // Hidden = loading complete
                monthTimings.recordEvent('wireLoadingComplete');
                monthTimings.end(true);
                observer.disconnect();
                observer.observe(document.body, { subtree: true, attributes: true });
            }
        }
    });
    observer.observe(document.body, { subtree: true, attributes: true, attributeFilter: ['style', 'class'] });

    // Global helper for testing
    window.monthTimings = monthTimings;
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