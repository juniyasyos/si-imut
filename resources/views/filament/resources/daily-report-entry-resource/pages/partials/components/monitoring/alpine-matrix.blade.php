<script>
    function matrixManager(data) {
        return {
            // Data dari backend
            indicators: data.indicators || [],
            matrixData: data.matrixData || {},
            daysInMonth: data.daysInMonth || [],
            selectedMonth: data.selectedMonth,
            today: data.today,

            // State
            filterPeriod: 'today',
            isMobile: false,
            currentDate: null,

            init() {
                this.currentDate = new Date(this.selectedMonth + '-01');
                this.initResize();
            },

            initResize() {
                this.isMobile = window.innerWidth < 900;
                window.addEventListener('resize', () => {
                    this.isMobile = window.innerWidth < 900;
                });
            },

            // Date utilities
            getDateInfo(day) {
                const date = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), day);
                const today = new Date();
                const isToday = date.toDateString() === today.toDateString();
                const isWeekend = [0, 6].includes(date.getDay());

                return {
                    date: date,
                    isToday: isToday,
                    isWeekend: isWeekend,
                    dayName: date.toLocaleDateString('id-ID', {
                        weekday: 'long'
                    }),
                    shortDay: date.toLocaleDateString('id-ID', {
                        weekday: 'short'
                    }).substr(0, 3),
                    formatted: date.toLocaleDateString('id-ID', {
                        day: 'numeric',
                        month: 'short'
                    })
                };
            },

            // Filter functions
            isToday(day) {
                return this.getDateInfo(day).isToday;
            },

            isInWeek(day) {
                const today = new Date();
                const start = new Date(today);
                start.setDate(today.getDate() - 6);
                const cellDate = this.getDateInfo(day).date;
                start.setHours(0, 0, 0, 0);
                today.setHours(23, 59, 59, 999);
                return cellDate >= start && cellDate <= today;
            },

            isInMonth(day) {
                const today = new Date();
                const cellDate = this.getDateInfo(day).date;
                return cellDate.getMonth() === today.getMonth() &&
                    cellDate.getFullYear() === today.getFullYear();
            },

            shouldShowCell(day) {
                if (this.filterPeriod === 'today') return this.isToday(day);
                if (this.filterPeriod === 'weekly') return this.isInWeek(day);
                if (this.filterPeriod === 'monthly') return this.isInMonth(day);
                return true;
            },

            get visibleDaysCount() {
                return this.daysInMonth.filter(day => this.shouldShowCell(day)).length;
            },

            // Matrix cell rendering
            getCellData(indicatorId, day) {
                return this.matrixData[indicatorId]?.[day] || null;
            },

            renderMatrixCell(indicatorId, day) {
                const cellData = this.getCellData(indicatorId, day);
                if (!cellData) return '';

                const state = cellData.cell_state || 'disabled';
                const summary = cellData.summary;
                const isToday = cellData.is_today;

                let classes = 'relative h-16 w-12 border border-slate-200 dark:border-slate-600 transition-all duration-200 cursor-pointer hover:shadow-md ';
                let content = '';

                // State-based styling
                switch (state) {
                    case 'done':
                        classes += isToday ?
                            'bg-gradient-to-br from-green-100 to-green-200 dark:from-green-900/40 dark:to-green-800/40 border-green-300 dark:border-green-600' :
                            'bg-green-50 dark:bg-green-900/30 border-green-200 dark:border-green-700';

                        content = `
                        <div class="p-1 h-full flex flex-col justify-center items-center">
                            <div class="text-xs font-bold text-green-700 dark:text-green-300">${summary?.percentage || 0}%</div>
                            <div class="text-[10px] text-green-600 dark:text-green-400">${summary?.numerator || 0}/${summary?.denominator || 0}</div>
                        </div>
                    `;
                        break;

                    case 'pending':
                        classes += isToday ?
                            'bg-gradient-to-br from-yellow-100 to-orange-200 dark:from-yellow-900/40 dark:to-orange-800/40 border-orange-300 dark:border-orange-600' :
                            'bg-yellow-50 dark:bg-yellow-900/30 border-yellow-200 dark:border-yellow-700';

                        content = `
                        <div class="p-1 h-full flex items-center justify-center">
                            <div class="text-[10px] font-medium text-yellow-700 dark:text-yellow-300">Pending</div>
                        </div>
                    `;
                        break;

                    case 'overdue':
                        classes += 'bg-red-50 dark:bg-red-900/30 border-red-200 dark:border-red-700';
                        content = `
                        <div class="p-1 h-full flex items-center justify-center">
                            <div class="text-[10px] font-medium text-red-700 dark:text-red-300">Overdue</div>
                        </div>
                    `;
                        break;

                    default:
                        classes += 'bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-600';
                        content = `
                        <div class="p-1 h-full flex items-center justify-center">
                            <div class="text-[10px] text-gray-400">-</div>
                        </div>
                    `;
                }

                // Add click handler
                const clickHandler = state !== 'disabled' ?
                    `wire:click="openSlideOver(${indicatorId}, '${cellData.date}')"` :
                    '';

                return `<div class="${classes}" ${clickHandler}>${content}</div>`;
            },

            // Mobile card rendering
            renderMobileCard(indicator, day) {
                const cellData = this.getCellData(indicator.id, day);
                const state = cellData?.cell_state || 'disabled';
                const summary = cellData?.summary;
                const dateInfo = this.getDateInfo(day);

                let cardClass = 'min-w-[150px] rounded-xl p-3 flex-shrink-0 snap-start border transition ';
                cardClass += dateInfo.isToday ?
                    'bg-blue-50 border-blue-200 dark:bg-blue-900/20' :
                    'bg-gray-50 border-slate-200 dark:bg-gray-900/40 dark:border-slate-700';

                let statusBadge = '';
                let content = '';

                if (cellData?.has_data) {
                    statusBadge = `<span class="text-[10px] px-2 py-0.5 rounded-full bg-green-100 text-green-700">${cellData.count}x</span>`;
                    content = `
                    <div class="text-base font-bold text-green-700">${summary?.percentage || 0}%</div>
                    <div class="text-[11px] text-gray-500">${summary?.numerator || 0}/${summary?.denominator || 0}</div>
                `;
                } else {
                    statusBadge = '<span class="text-[10px] px-2 py-0.5 rounded-full bg-orange-100 text-orange-700">Kosong</span>';

                    if (state === 'pending') {
                        content = '<div class="text-sm font-semibold text-orange-700">Belum diisi</div>';
                    } else if (state === 'overdue') {
                        content = '<div class="text-sm font-semibold text-red-600">Terkunci</div>';
                    } else {
                        content = '<div class="text-sm text-gray-400">-</div>';
                    }
                }

                const clickHandler = state !== 'disabled' ?
                    `wire:click="openSlideOver(${indicator.id}, '${cellData?.date || ''}')"` :
                    '';

                return `
                <div class="${cardClass}" ${clickHandler}>
                    <div class="flex items-center justify-between mb-2">
                        <div class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            ${day} <span class="text-[11px] text-gray-400 ml-0.5">${dateInfo.shortDay}</span>
                        </div>
                        ${statusBadge}
                    </div>
                    <div class="mb-3">${content}</div>
                    <div class="text-[10px] text-gray-400">
                        ${state === 'disabled' ? 'Tidak tersedia' : (cellData?.has_data ? 'Sudah diisi' : 'Belum diisi')}
                    </div>
                </div>
            `;
            }
        }
    }
</script>