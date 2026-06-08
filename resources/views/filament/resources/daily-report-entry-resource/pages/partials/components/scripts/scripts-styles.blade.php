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
                        //
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

            formatImutVersion(version) {
                if (!version) return '';

                return version.replace('/version-', 'v');
            },
        }));
    });

    document.addEventListener('DOMContentLoaded', function () {
        const urlParams = new URLSearchParams(window.location.search);
        const dateParam = urlParams.get('selectedDate');
        const monthParam = urlParams.get('selectedMonth');

        if (dateParam || monthParam) {
            const dailyReportEl = document.querySelector('[x-data*="dailyReportData"]');

            if (dailyReportEl && dailyReportEl.__x) {
                if (dateParam) {
                    dailyReportEl.__x.$data.selectedDate = dateParam;
                }

                if (monthParam) {
                    dailyReportEl.__x.$data.selectedMonth = monthParam;
                }
            }
        }
    });

    document.addEventListener('livewire:init', () => {
        if (window.Alpine && !Alpine.store('matrixSnapshot')) {
            Alpine.store('matrixSnapshot', {
                snapshot: null,
                synced: false,
            });
        }

        Livewire.on('matrixSnapshotUpdated', (payload) => {
            try {
                const snapshot = payload?.snapshot ?? payload;

                if (!snapshot || typeof snapshot !== 'object') {
                    return;
                }

                if (window.Alpine && Alpine.store('matrixSnapshot')) {
                    Alpine.store('matrixSnapshot').snapshot = snapshot;
                    Alpine.store('matrixSnapshot').synced = false;
                }

                const attemptSync = (attempts = 0) => {
                    if (attempts > 20) {
                        return;
                    }

                    const currentEl = document.querySelector('[x-data*="dailyReportData"]');

                    if (!currentEl || !currentEl.__x) {
                        setTimeout(() => attemptSync(attempts + 1), 100);
                        return;
                    }

                    const data = currentEl.__x.$data;

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

                    if (window.Alpine && Alpine.store('matrixSnapshot')) {
                        Alpine.store('matrixSnapshot').synced = true;
                    }
                };

                attemptSync();
            } catch (error) {
                //
            }
        });

        Livewire.on('updateUrl', (urlPayload) => {
            if (!urlPayload) {
                return;
            }

            try {
                let urlString = urlPayload;

                if (typeof urlPayload === 'object' && urlPayload !== null) {
                    urlString = urlPayload.url || (
                        Array.isArray(urlPayload)
                            ? urlPayload[0]
                            : Object.values(urlPayload)[0]
                    );
                }

                if (typeof urlString !== 'string') {
                    return;
                }

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

                const pageUrl = window.location.pathname + '?' + urlParams.toString();

                window.history.replaceState({}, '', pageUrl);
            } catch (error) {
                //
            }
        });
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