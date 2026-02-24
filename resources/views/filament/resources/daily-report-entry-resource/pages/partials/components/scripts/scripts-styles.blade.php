<!-- Alpine.js Script and Custom Styles -->
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('dailyReportData', () => ({
            selectedDate: '{{ now()->format('Y - m - d ') }}',
            selectedMonth: '{{ $selectedMonth }}',
            currentDate: new Date('{{ $selectedMonth }}-01'),
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

    // Listen for URL update events from Livewire
    document.addEventListener('livewire:init', () => {
        Livewire.on('url-updated', (event) => {
            console.log('URL update event received:', event);
            if (event[0] && event[0].url) {
                console.log('Updating URL to:', event[0].url);
                // Update browser URL without page refresh
                window.history.replaceState({}, '', event[0].url);
            }
        });
    });

    // Alternative event listener (fallback)
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Livewire !== 'undefined') {
            Livewire.on('url-updated', function(data) {
                console.log('Alternative listener - URL update:', data);
                if (data && data.url) {
                    window.history.replaceState({}, '', data.url);
                }
            });
        }
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