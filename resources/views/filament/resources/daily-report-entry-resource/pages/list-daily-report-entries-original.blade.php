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
            }
        }" x-cloak>
            <!-- Header Section -->
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                <div class="flex flex-col gap-4">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                @svg("heroicon-o-calendar-days", "w-7 h-7 text-primary-600")
                                SI-IMUT – Laporan Harian
                            </h1>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                Input dan monitoring laporan harian indikator mutu
                            </p>
                        </div>

                        <!-- Tab Navigation -->
                        <div class="flex bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
                            <button
                                @click="currentView = 'input'"
                                :class="currentView === 'input'
                                    ? 'bg-white dark:bg-slate-600 text-gray-900 dark:text-white shadow-sm'
                                    : 'text-gray-500 dark:text-gray-400'"
                                class="px-4 py-2 text-sm font-medium rounded-md transition-all">
                                Input Harian
                            </button>
                            <button
                                @click="currentView = 'monitoring'"
                                :class="currentView === 'monitoring'
                                    ? 'bg-white dark:bg-slate-600 text-gray-900 dark:text-white shadow-sm'
                                    : 'text-gray-500 dark:text-gray-400'"
                                class="px-4 py-2 text-sm font-medium rounded-md transition-all">
                                Monitoring Bulanan
                            </button>
                        </div>
                    </div>

                    <!-- Filters and Search Section -->
                    <div x-show="currentView === 'input'" class="border-t border-gray-200 dark:border-gray-700 pt-4">
                        <div class="flex flex-col lg:flex-row gap-4">
                            <!-- Search -->
                            <div class="flex-1">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        @svg("heroicon-m-magnifying-glass", "w-5 h-5 text-gray-400")
                                    </div>
                                    <input
                                        x-model="searchQuery"
                                        type="text"
                                        placeholder="Cari indikator..."
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                                </div>
                            </div>

                            <!-- Quick Filters -->
                            <div class="flex flex-wrap gap-2">
                                <button
                                    @click="statusFilter = 'all'"
                                    :class="statusFilter === 'all' ? 'bg-primary-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                    class="px-3 py-1.5 text-xs font-medium rounded-full transition">
                                    Semua
                                </button>
                                <button
                                    @click="statusFilter = 'pending'"
                                    :class="statusFilter === 'pending' ? 'bg-orange-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                    class="px-3 py-1.5 text-xs font-medium rounded-full transition">
                                    Belum Diisi
                                </button>
                                <button
                                    @click="statusFilter = 'done'"
                                    :class="statusFilter === 'done' ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                    class="px-3 py-1.5 text-xs font-medium rounded-full transition">
                                    Selesai
                                </button>
                                <button
                                    @click="statusFilter = 'overdue'"
                                    :class="statusFilter === 'overdue' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                                    class="px-3 py-1.5 text-xs font-medium rounded-full transition">
                                    Terlambat
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div x-show="currentView === 'input'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <!-- Sidebar: Date Navigation -->
                <div class="lg:col-span-3">
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4">
                        @include('filament.resources.daily-report-entry-resource.pages.partials.month-navigation')

                        <!-- Date Legend -->
                        <div class="mb-4 p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                @svg("heroicon-m-calendar", "w-4 h-4")
                                <span x-text="getMonthName().toUpperCase()"></span>
                            </h3>
                            <div class="space-y-2 text-xs">
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                                    <span>● = Lengkap</span>
                                </div>
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                    <span class="w-3 h-3 rounded-full border-2 border-orange-400"></span>
                                    <span>○ = Kosong</span>
                                </div>
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                    <span class="w-3 h-3 rounded border border-gray-400"></span>
                                    <span>▢ = Future</span>
                                </div>
                            </div>
                        </div>

                        <!-- Date List -->
                        <div class="space-y-1 max-h-[400px] overflow-y-auto">
                            @foreach($daysInMonth as $day)
                            @php
                            $date = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth)->day($day);
                            $dateString = $date->format('Y-m-d');
                            $dayName = $date->locale('id')->dayName;
                            $isToday = $date->isToday();
                            $isWeekend = in_array($date->dayOfWeek, [0, 6]);

                            // Check if any indicator has data for this date
                            $hasAnyData = false;
                            foreach($indicators as $indicator) {
                            $cellData = $matrixData[$indicator['id']][$day] ?? null;
                            if ($cellData && ($cellData['has_data'] ?? false)) {
                            $hasAnyData = true;
                            break;
                            }
                            }
                            @endphp

                            <button
                                @click="selectDate('{{ $dateString }}')"
                                :class="selectedDate === '{{ $dateString }}' 
                                    ? 'bg-primary-50 dark:bg-primary-900/30 border-primary-200 dark:border-primary-800 text-primary-900 dark:text-primary-100'
                                    : 'hover:bg-gray-50 dark:hover:bg-gray-800 border-transparent'"
                                class="w-full flex items-center gap-3 p-3 rounded-lg border transition-all text-left {{ $isWeekend ? 'bg-red-50/50 dark:bg-red-900/10' : '' }}">

                                <!-- Status Indicator -->
                                <div class="flex-shrink-0">
                                    @if($date->isFuture())
                                    <div class="w-3 h-3 rounded border border-gray-400"></div>
                                    @elseif($hasAnyData)
                                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                                    @else
                                    <div class="w-3 h-3 rounded-full border-2 border-orange-400"></div>
                                    @endif
                                </div>

                                <!-- Date Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        @if($isToday)
                                        <span class="text-sm font-bold">▶ {{ $day }} {{ $date->format('M') }}</span>
                                        @else
                                        <span class="text-sm {{ $date->isFuture() ? 'text-gray-400' : 'text-gray-700 dark:text-gray-300' }}">
                                            {{ $day }} {{ $date->format('M') }}
                                        </span>
                                        @endif
                                        @if($isToday)
                                        <span class="text-xs px-1.5 py-0.5 bg-primary-600 text-white rounded font-medium">Today</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                        {{ $dayName }}
                                    </div>
                                </div>
                            </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Main Content: Indicators for Selected Date -->
                <div class="lg:col-span-9">
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6">
                        <!-- Selected Date Header -->
                        <div class="mb-6 pb-4 border-b border-gray-200 dark:border-gray-700">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                        @svg("heroicon-m-calendar-days", "w-5 h-5 text-primary-600")
                                        <span x-text="formatDate(selectedDate)"></span>
                                    </h2>
                                    <div class="mt-1 space-y-1">
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            Unit: {{ auth()->user()->unitKerjas->first()?->nama_unit ?? 'Tidak ada unit' }}
                                        </p>
                                        <p class="text-xs text-blue-600 dark:text-blue-400 flex items-center gap-1">
                                            @svg("heroicon-m-information-circle", "w-4 h-4")
                                            Bisa input H‑6
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Indicators List -->
                        <div class="space-y-4 max-h-[600px] overflow-y-auto">
                            <!-- Desktop View -->
                            <div class="hidden lg:block">
                                <template x-for="indicator in filteredIndicators" :key="indicator.id">
                                    <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:shadow-sm transition-all duration-200 indicator-card">
                                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                                            <!-- Indicator Info -->
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-snug" x-text="indicator.title"></h3>

                                                <div class="flex items-center gap-4 mt-2">
                                                    <!-- Dynamic Status Display -->
                                                    <div class="flex items-center gap-2">
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">Status:</span>
                                                        <div>
                                                            <!-- Success State -->
                                                            <template x-if="getStatusForDate(indicator.id, selectedDate) && getStatusForDate(indicator.id, selectedDate).cell_state === 'done'">
                                                                <div class="flex items-center gap-1 text-green-700 dark:text-green-400">
                                                                    @svg("heroicon-m-check-circle", "w-4 h-4")
                                                                    <span class="text-sm font-semibold" x-text="(getStatusForDate(indicator.id, selectedDate).summary?.percentage ?? '0') + '%'"></span>
                                                                    <span class="text-xs text-gray-500" x-text="'(' + (getStatusForDate(indicator.id, selectedDate).summary?.numerator ?? 0) + '/' + (getStatusForDate(indicator.id, selectedDate).summary?.denominator ?? 0) + ')'"></span>
                                                                </div>
                                                            </template>

                                                            <!-- Pending State -->
                                                            <template x-if="getStatusForDate(indicator.id, selectedDate) && getStatusForDate(indicator.id, selectedDate).cell_state === 'pending'">
                                                                <div class="flex items-center gap-1 text-orange-600 dark:text-orange-400">
                                                                    @svg("heroicon-m-exclamation-circle", "w-4 h-4")
                                                                    <span class="text-sm font-medium">Belum diisi</span>
                                                                </div>
                                                            </template>

                                                            <!-- Overdue State -->
                                                            <template x-if="getStatusForDate(indicator.id, selectedDate) && getStatusForDate(indicator.id, selectedDate).cell_state === 'overdue'">
                                                                <div class="flex items-center gap-1 text-red-600 dark:text-red-400">
                                                                    @svg("heroicon-m-lock-closed", "w-4 h-4")
                                                                    <span class="text-sm font-medium">Terkunci</span>
                                                                </div>
                                                            </template>

                                                            <!-- Default/Empty State -->
                                                            <template x-if="!getStatusForDate(indicator.id, selectedDate) || getStatusForDate(indicator.id, selectedDate).cell_state === 'disabled'">
                                                                <div class="flex items-center gap-1 text-gray-400">
                                                                    @svg("heroicon-m-minus-circle", "w-4 h-4")
                                                                    <span class="text-sm">—</span>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>

                                                    <!-- Target -->
                                                    <div x-show="indicator.target" class="flex items-center gap-2">
                                                        <span class="text-xs text-gray-500 dark:text-gray-400">Target:</span>
                                                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300" x-text="indicator.target"></span>
                                                    </div>
                                                </div>

                                                <!-- Category -->
                                                <div class="mt-2" x-show="indicator.category">
                                                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-1 rounded" :class="getCategoryColor(indicator.category)">
                                                        @svg("heroicon-m-tag", "w-3 h-3")
                                                        <span x-text="indicator.category"></span>
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Dynamic Action Button -->
                                            <div class="flex-shrink-0">
                                                <!-- Edit Button (Done State) -->
                                                <template x-if="getActionButton(indicator.id, selectedDate).state === 'done'">
                                                    <button
                                                        @click="$wire.openSlideOver(indicator.id, selectedDate)"
                                                        class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                                                        @svg("heroicon-m-pencil", "w-4 h-4")
                                                        Edit
                                                    </button>
                                                </template>

                                                <!-- Fill Button (Pending State) -->
                                                <template x-if="getActionButton(indicator.id, selectedDate).state === 'pending'">
                                                    <button
                                                        @click="$wire.openSlideOver(indicator.id, selectedDate)"
                                                        class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                                                        @svg("heroicon-m-plus", "w-4 h-4")
                                                        Isi
                                                    </button>
                                                </template>

                                                <!-- Locked Button (Overdue State) -->
                                                <template x-if="getActionButton(indicator.id, selectedDate).state === 'overdue'">
                                                    <button
                                                        disabled
                                                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-300 text-gray-500 text-sm font-medium rounded-lg cursor-not-allowed">
                                                        @svg("heroicon-m-lock-closed", "w-4 h-4")
                                                        Terkunci
                                                    </button>
                                                </template>

                                                <!-- Default Button -->
                                                <template x-if="!getActionButton(indicator.id, selectedDate).state || getActionButton(indicator.id, selectedDate).state === 'disabled'">
                                                    <button
                                                        @click="$wire.openSlideOver(indicator.id, selectedDate)"
                                                        class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors">
                                                        @svg("heroicon-m-eye", "w-4 h-4")
                                                        Lihat
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Mobile View -->
                            <div class="block lg:hidden space-y-4">
                                <template x-for="indicator in filteredIndicators" :key="indicator.id">
                                    <div class="bg-white dark:bg-slate-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4 shadow-sm">
                                        <!-- Mobile Header -->
                                        <div class="flex items-start justify-between gap-3 mb-3">
                                            <div class="flex-1 min-w-0">
                                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white leading-tight" x-text="indicator.title"></h3>
                                                <div class="mt-1" x-show="indicator.category">
                                                    <span class="inline-flex items-center gap-1 text-xs font-medium px-2 py-0.5 rounded-full" :class="getCategoryColor(indicator.category)">
                                                        <span x-text="indicator.category"></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Mobile Status -->
                                        <div class="mb-4">
                                            <!-- Success State -->
                                            <template x-if="getStatusForDate(indicator.id, selectedDate) && getStatusForDate(indicator.id, selectedDate).cell_state === 'done'">
                                                <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-3">
                                                    <div class="flex items-center gap-2">
                                                        @svg("heroicon-m-check-circle", "w-5 h-5 text-green-600")
                                                        <div>
                                                            <div class="text-sm font-semibold text-green-800 dark:text-green-400" x-text="(getStatusForDate(indicator.id, selectedDate).summary?.percentage ?? '0') + '%'"></div>
                                                            <div class="text-xs text-green-600 dark:text-green-500" x-text="'(' + (getStatusForDate(indicator.id, selectedDate).summary?.numerator ?? 0) + '/' + (getStatusForDate(indicator.id, selectedDate).summary?.denominator ?? 0) + ')'"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Pending State -->
                                            <template x-if="getStatusForDate(indicator.id, selectedDate) && getStatusForDate(indicator.id, selectedDate).cell_state === 'pending'">
                                                <div class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-lg p-3">
                                                    <div class="flex items-center gap-2">
                                                        @svg("heroicon-m-exclamation-circle", "w-5 h-5 text-orange-600")
                                                        <div class="text-sm font-medium text-orange-800 dark:text-orange-400">Belum diisi</div>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Overdue State -->
                                            <template x-if="getStatusForDate(indicator.id, selectedDate) && getStatusForDate(indicator.id, selectedDate).cell_state === 'overdue'">
                                                <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-3">
                                                    <div class="flex items-center gap-2">
                                                        @svg("heroicon-m-lock-closed", "w-5 h-5 text-red-600")
                                                        <div class="text-sm font-medium text-red-800 dark:text-red-400">Terkunci</div>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Default State -->
                                            <template x-if="!getStatusForDate(indicator.id, selectedDate) || getStatusForDate(indicator.id, selectedDate).cell_state === 'disabled'">
                                                <div class="bg-gray-50 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                                    <div class="flex items-center gap-2">
                                                        @svg("heroicon-m-minus-circle", "w-5 h-5 text-gray-400")
                                                        <div class="text-sm text-gray-600 dark:text-gray-400">—</div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Mobile Action Button -->
                                        <div class="flex gap-2">
                                            <template x-if="getActionButton(indicator.id, selectedDate).state === 'done'">
                                                <button @click="$wire.openSlideOver(indicator.id, selectedDate)" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-3 rounded-lg transition">
                                                    Edit Data
                                                </button>
                                            </template>
                                            <template x-if="getActionButton(indicator.id, selectedDate).state === 'pending'">
                                                <button @click="$wire.openSlideOver(indicator.id, selectedDate)" class="flex-1 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium py-3 rounded-lg transition">
                                                    Isi Data
                                                </button>
                                            </template>
                                            <template x-if="getActionButton(indicator.id, selectedDate).state === 'overdue'">
                                                <button disabled class="flex-1 bg-gray-300 text-gray-500 text-sm font-medium py-3 rounded-lg cursor-not-allowed">
                                                    Terkunci
                                                </button>
                                            </template>
                                            <template x-if="!getActionButton(indicator.id, selectedDate).state || getActionButton(indicator.id, selectedDate).state === 'disabled'">
                                                <button @click="$wire.openSlideOver(indicator.id, selectedDate)" class="flex-1 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium py-3 rounded-lg transition">
                                                    Lihat Data
                                                </button>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>

                            <!-- Empty State -->
                            <div x-show="filteredIndicators.length === 0" class="text-center py-16">
                                <div class="flex flex-col items-center justify-center space-y-4">
                                    <div class="relative">
                                        <div class="absolute inset-0 bg-primary-100 dark:bg-primary-900/20 rounded-full blur-2xl opacity-50"></div>
                                        <div class="relative w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-2xl flex items-center justify-center shadow-lg">
                                            @svg("heroicon-o-clipboard-document-list", "w-10 h-10 text-gray-400 dark:text-gray-500")
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <p class="text-base font-bold text-gray-700 dark:text-gray-300" x-show="!searchQuery && statusFilter === 'all'">Belum Ada Indikator</p>
                                        <p class="text-base font-bold text-gray-700 dark:text-gray-300" x-show="searchQuery || statusFilter !== 'all'">Tidak Ada Hasil</p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm" x-show="!searchQuery && statusFilter === 'all'">
                                            Belum ada indikator mutu yang dikonfigurasi untuk unit kerja Anda.
                                        </p>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm" x-show="searchQuery || statusFilter !== 'all'">
                                            Coba ubah filter atau kata kunci pencarian.
                                        </p>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>

            <!-- Monitoring Bulanan View (placeholder) -->
            <div x-show="currentView === 'monitoring'" class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-6" style="display: none;">
                <div class="text-center py-16">
                    <div class="space-y-4">
                        <div class="w-16 h-16 bg-primary-100 dark:bg-primary-900/20 rounded-2xl flex items-center justify-center mx-auto">
                            @svg("heroicon-o-chart-bar", "w-8 h-8 text-primary-600")
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Monitoring Bulanan</h3>
                            <p class="text-gray-500 dark:text-gray-400">Fitur monitoring bulanan akan segera tersedia</p>
                        </div>
                    </div>
                </div>
            </div>

            @include('filament.resources.daily-report-entry-resource.pages.partials.legend')
        </div>

        {{-- Slide-over rendered outside the page wrapper to prevent overflow clipping --}}
        @include('filament.resources.daily-report-entry-resource.pages.partials.slide-over')
    </div>

    <!-- Custom styles for smooth animations -->
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

    <!-- Enhanced Alpine.js functionality -->
    <script>
        // Auto-select today's date on load if no date is selected
        document.addEventListener('alpine:init', () => {
            Alpine.data('dailyReportData', () => ({
                selectedDate: '{{ now()->format('
                Y - m - d ') }}',
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
                    const month = today.toISOString().slice(0, 7); // YYYY-MM format
                    if (month === this.selectedMonth) {
                        this.selectedDate = today.toISOString().slice(0, 10); // YYYY-MM-DD format
                    }
                },

                selectDate(date) {
                    this.selectedDate = date;
                    // Trigger Livewire event
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

                getDateStatus(dateString, hasData) {
                    if (this.isFutureDate(dateString)) return 'future';
                    return hasData ? 'complete' : 'empty';
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
                }
            }));
        });
    </script>
</x-filament-panels::page>