<!-- Slide Over -->
<div class="!fixed !inset-0 !z-[9999] overflow-hidden"
    x-data="{ show: @entangle('slideOverOpen').live }"
    x-show="show"
    x-cloak
    style="display: none; position: fixed !important; top: 0 !important; right: 0 !important; bottom: 0 !important; left: 0 !important; z-index: 9999 !important;"
    x-transition:enter="transition-opacity ease-out duration-150"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-100"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="$wire.closeSlideOver()">

    <!-- Enhanced Backdrop with strong blur effect -->
    <div class="!absolute !inset-0 backdrop-blur-md backdrop-saturate-150"
        style="background: linear-gradient(to bottom right, rgba(17, 24, 39, 0.7), rgba(17, 24, 39, 0.8), rgba(0, 0, 0, 0.9));"
        x-show="show"
        x-transition:enter="ease-out duration-150"
        x-transition:enter-start="opacity-0 backdrop-blur-none"
        x-transition:enter-end="opacity-100 backdrop-blur-md"
        x-transition:leave="ease-in duration-100"
        x-transition:leave-start="opacity-100 backdrop-blur-md"
        x-transition:leave-end="opacity-0 backdrop-blur-none"
        wire:click="closeSlideOver"></div>

    <!-- Slide Over Panel -->
    <div class="!fixed !inset-y-0 !right-0 !left-auto flex max-w-full pl-0 sm:pl-10 md:pl-16 pointer-events-none"
        style="position: fixed !important; top: 0 !important; right: 0 !important; bottom: 0 !important; left: auto !important; z-index: 10000 !important;">
        <div class="w-screen max-w-full md:max-w-5xl pointer-events-auto"
            x-show="show"
            x-transition:enter="transform transition ease-out duration-200"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transform transition ease-in duration-150"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0">

            <div class="flex h-full flex-col shadow-[0_0_80px_rgba(0,0,0,0.5)] ring-1 backdrop-blur-xl bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-700">
                <!-- Header with enhanced gradient and glass effect -->
                <div class="relative px-6 py-7 shadow-2xl bg-gradient-to-br from-blue-600 via-blue-700 to-blue-800"
                    x-data="{ headerVisible: false }"
                    x-init="setTimeout(() => headerVisible = true, 50)"
                    x-show="headerVisible"
                    x-transition:enter="transition ease-out duration-200 delay-50"
                    x-transition:enter-start="opacity-0 -translate-y-6"
                    x-transition:enter-end="opacity-100 translate-y-0">

                    <!-- Animated decorative background pattern -->
                    <div class="absolute inset-0 opacity-[0.08]">
                        <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <pattern id="grid" width="32" height="32" patternUnits="userSpaceOnUse">
                                    <circle cx="16" cy="16" r="1.5" fill="white" />
                                </pattern>
                            </defs>
                            <rect width="100%" height="100%" fill="url(#grid)" />
                        </svg>
                    </div>

                    <!-- Gradient overlay for depth -->
                    <div class="absolute inset-0 bg-gradient-to-t from-black/10 via-transparent to-white/5 dark:from-black/20 dark:to-transparent"></div>

                    <div class="relative flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <h2 class="text-xl font-bold truncate drop-shadow-lg text-white dark:text-slate-100">
                                {{ $selectedIndicatorData['title'] ?? 'Indikator' }}
                            </h2>
                            <p class="mt-2 text-sm font-medium flex items-center drop-shadow-md text-sky-100 dark:text-slate-300">
                                @svg("heroicon-m-calendar", "w-4 h-4 mr-2")
                                {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}
                            </p>
                            @if(isset($selectedIndicatorData['category']))
                            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold backdrop-blur-md shadow-lg bg-white/25 text-white border border-white/30 dark:bg-slate-700/30 dark:text-slate-100 dark:border-slate-600">
                                {{ $selectedIndicatorData['category'] }}
                            </div>
                            @endif
                        </div>
                        <button wire:click="closeSlideOver"
                            type="button"
                            class="ml-4 flex-shrink-0 rounded-full p-2.5 focus:outline-none focus:ring-2 transition-all duration-150 transform hover:scale-110 hover:rotate-90 active:scale-95 backdrop-blur-sm text-sky-100 dark:text-slate-200"
                            onmouseover="this.style.backgroundColor='rgba(255,255,255,0.2)'; this.style.color='#ffffff';"
                            onmouseout="this.style.backgroundColor=''; this.style.color='#eff6ff';">
                            @svg("heroicon-o-x-mark", "h-6 w-6 drop-shadow-md")
                        </button>
                    </div>
                </div>

                <!-- Content with enhanced staggered animations and glass effect -->
                <div class="flex-1 overflow-y-auto px-6 py-6 backdrop-blur-sm bg-gray-50/60 dark:bg-slate-800/60">

                    <!-- Enhanced Add New Button - Redirects to create page -->
                    <div x-data="{ buttonVisible: false }"
                        x-init="setTimeout(() => buttonVisible = true, 200)"
                        x-show="buttonVisible"
                        x-transition:enter="transition ease-out duration-400 delay-100"
                        x-transition:enter-start="opacity-0 translate-y-6 scale-90"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        class="mb-6">
                        <button type="button"
                            wire:click="createNewReport"
                            class="group relative w-full flex items-center justify-center px-6 py-5 font-bold rounded-2xl shadow-2xl transform hover:scale-[1.03] active:scale-[0.97] transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-offset-2 overflow-hidden backdrop-blur-sm bg-gradient-to-r from-green-600 via-green-700 to-green-800 text-white"
                            onmouseover="this.classList.add('shadow-[0_0_50px_rgba(34,197,94,0.7)]')"
                            onmouseout="this.classList.remove('shadow-[0_0_50px_rgba(34,197,94,0.7)]')"
                            @click="console.log('Redirecting to create page for indicator:', {{ $selectedIndicatorId ?? 'null' }}, 'date:', '{{ $selectedDate ?? 'null' }}')">
                            <!-- Multiple shine effects on hover -->
                            <div class="absolute inset-0 transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"
                                style="background: linear-gradient(to right, transparent, rgba(255,255,255,0.3), transparent);"></div>
                            <div class="absolute inset-0 transform skew-x-12 translate-x-full group-hover:-translate-x-full transition-transform duration-1200 ease-in-out delay-100"
                                style="background: linear-gradient(to left, transparent, rgba(255,255,255,0.1), transparent);"></div>

                            <!-- Glow effect -->
                            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 blur-xl transition-opacity duration-500"
                                style="background: linear-gradient(to right, rgba(96, 165, 250, 0.2), rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.2));"></div>

                            @svg("heroicon-o-arrow-top-right-on-square", "relative w-6 h-6 mr-2.5 transform group-hover:rotate-12 group-hover:scale-110 transition-all duration-500 drop-shadow-lg")
                            <span class="relative text-base drop-shadow-md">Buat Laporan Baru</span>
                        </button>
                    </div>

                    @if($this->selectedIndicatorId && $this->selectedDate)
                    <!-- Existing Reports Section -->
                    <div class="mb-6"
                        x-data="{ contentVisible: false }"
                        x-init="setTimeout(() => contentVisible = true, 80)"
                        x-show="contentVisible"
                        x-transition:enter="transition ease-out duration-250 delay-80"
                        x-transition:enter-start="opacity-0 translate-y-6 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100">

                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Laporan Hari Ini</h3>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}
                            </span>
                        </div>

                        @if(!empty($this->dailyReports))
                        <div class="space-y-4">
                            @foreach($this->dailyReports as $report)
                            <div class="backdrop-blur-xl rounded-xl p-6 border shadow-lg transition-all duration-150 hover:shadow-xl bg-white/90 border-gray-200 dark:bg-slate-800/70 dark:border-slate-600">
                                <!-- Report Header -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-3 mb-2">
                                            <h4 class="font-semibold text-gray-900 dark:text-white">
                                                {{ $report['form_title'] }}
                                            </h4>
                                            @if($report['total_score'] >= 100)
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                                Patuh
                                            </span>
                                            @else
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                                                Tidak Patuh
                                            </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                                            <span class="flex items-center gap-1">
                                                @svg("heroicon-m-building-office", "w-4 h-4")
                                                {{ $report['unit_name'] }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                @svg("heroicon-m-user", "w-4 h-4")
                                                {{ $report['submitted_by_name'] }}
                                            </span>
                                            <span class="flex items-center gap-1">
                                                @svg("heroicon-m-clock", "w-4 h-4")
                                                {{ \Carbon\Carbon::parse($report['created_at'])->format('H:i') }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Score Display -->
                                    <div class="text-right">
                                        <div class="text-2xl font-bold {{ $report['total_score'] >= 90 ? 'text-green-600' : ($report['total_score'] >= 80 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ number_format($report['total_score'], 1) }}%
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Skor Kepatuhan</div>
                                    </div>
                                </div>

                                <!-- Field Responses Dropdown -->
                                <div class="mb-4" x-data="{ expanded: false }">
                                    <button @click="expanded = !expanded"
                                        type="button"
                                        class="w-full flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-slate-700/50 hover:bg-gray-100 dark:hover:bg-slate-700 transition-colors duration-150">
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 flex items-center">
                                            @svg("heroicon-m-document-text", "w-4 h-4 mr-2")
                                            Detail Jawaban ({{ count($report['field_responses']) }} field)
                                        </span>
                                        <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200"
                                            :class="{ 'rotate-180': expanded }"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>

                                    <div x-show="expanded"
                                        x-collapse
                                        class="mt-3 space-y-3">
                                        @foreach($report['field_responses'] as $response)
                                        <div class="p-3 rounded-lg bg-gray-50 dark:bg-slate-700/50 border border-gray-200 dark:border-slate-600">
                                            <div class="flex items-start justify-between mb-2">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ $response['field_label'] }}
                                                </span>
                                                <span class="text-xs px-2 py-1 rounded-full {{ $response['compliance_score'] >= 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ number_format($response['compliance_score'] * 100, 0) }}%
                                                </span>
                                            </div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">
                                                <span class="font-medium">Jawaban:</span>
                                                @if(is_array($response['field_value']))
                                                {{ implode(', ', $response['field_value']) }}
                                                @else
                                                {{ $response['field_value'] ?? 'Tidak ada jawaban' }}
                                                @endif
                                            </div>
                                        </div>
                                        @endforeach
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-slate-600">
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        ID: {{ $report['id'] }}
                                    </span>
                                    <div class="flex gap-2 flex-wrap">
                                        <button
                                            wire:click="viewReport({{ $report['id'] }})"
                                            title="Lihat detail lengkap laporan"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-blue-700 bg-blue-100 hover:bg-blue-200 transition-colors duration-100 dark:bg-blue-900/30 dark:text-blue-400 dark:hover:bg-blue-900/50">
                                            @svg("heroicon-m-eye", "w-3 h-3 mr-1")
                                            Detail
                                        </button>

                                        @if(\Carbon\Carbon::parse($report['created_at'])->diffInHours(now()) <= 24)
                                            <button
                                            wire:click="editReport({{ $report['id'] }})"
                                            title="Edit laporan (tersedia 24 jam setelah dibuat)"
                                            class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-yellow-700 bg-yellow-100 hover:bg-yellow-200 transition-colors duration-100 dark:bg-yellow-900/30 dark:text-yellow-400 dark:hover:bg-yellow-900/50">
                                            @svg("heroicon-m-pencil", "w-3 h-3 mr-1")
                                            Edit
                                            </button>

                                            <button
                                                wire:click="deleteReport({{ $report['id'] }})"
                                                wire:confirm="Apakah Anda yakin ingin menghapus laporan ini? Tindakan ini tidak dapat dibatalkan."
                                                title="Hapus laporan (tersedia 24 jam setelah dibuat)"
                                                class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-red-700 bg-red-100 hover:bg-red-200 transition-colors duration-100 dark:bg-red-900/30 dark:text-red-400 dark:hover:bg-red-900/50">
                                                @svg("heroicon-m-trash", "w-3 h-3 mr-1")
                                                Hapus
                                            </button>
                                            @else
                                            <span class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-gray-500 bg-gray-100 dark:bg-gray-800 dark:text-gray-400" title="Edit dan hapus hanya tersedia 24 jam setelah laporan dibuat">
                                                @svg("heroicon-m-lock-closed", "w-3 h-3 mr-1")
                                                Terkunci
                                            </span>
                                            @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <!-- Empty State -->
                        <div class="backdrop-blur-xl rounded-2xl p-10 text-center border-2 border-dashed shadow-xl hover:shadow-2xl transition-all duration-150 bg-white/80 border-gray-300 dark:bg-slate-800/60 dark:border-slate-700">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl mb-4 shadow-lg bg-gradient-to-br from-gray-100 via-gray-200 to-gray-100 dark:from-slate-700 dark:via-slate-800 dark:to-slate-700">
                                <x-heroicon-o-document-text class="w-10 h-10 text-gray-400 dark:text-slate-400" />
                            </div>
                            <p class="text-base font-semibold text-slate-700 dark:text-slate-200">Belum ada laporan untuk hari ini</p>
                            <p class="text-sm mt-2 text-slate-500 dark:text-slate-400">Mulai buat laporan harian dengan klik tombol di bawah</p>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Enhanced Helper text with badge styling -->
                    <p class="mt-6 text-sm text-center text-slate-500 dark:text-slate-400"
                        x-data="{ helperVisible: false }"
                        x-init="setTimeout(() => helperVisible = true, 600)"
                        x-show="helperVisible"
                        x-transition:enter="transition ease-out duration-400 delay-550"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        Tekan
                        <kbd class="inline-flex items-center px-3 py-1.5 mx-1 text-xs font-bold rounded-lg shadow-md transition-colors duration-200 text-slate-700 bg-white border-2 border-gray-300 dark:text-slate-200 dark:bg-slate-700 dark:border-slate-600"
                            onmouseover="this.style.backgroundColor='#f3f4f6';"
                            onmouseout="this.style.backgroundColor='#ffffff';">ESC</kbd>
                        untuk menutup
                    </p>

                    <!-- Debug toggle button (for development) -->
                    <div class="mt-4 text-center">
                        <button @click="document.querySelector('[x-data]').__x.$data.debug = !document.querySelector('[x-data]').__x.$data.debug"
                            class="text-xs text-gray-400 hover:text-gray-600 transition-colors duration-150">
                            Toggle Debug Info
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Input Modal -->
<div class="!fixed !inset-0 !z-[10000] overflow-y-auto"
    x-data="{ 
        show: @entangle('formSlideOverOpen').live,
        debug: false
    }"
    x-show="show"
    x-cloak
    style="display: none; position: fixed !important; top: 0 !important; right: 0 !important; bottom: 0 !important; left: 0 !important; z-index: 10000 !important;"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="$wire.closeFormSlideOver()"
    x-init="console.log('Modal initialized, formSlideOverOpen:', @entangle('formSlideOverOpen').live)">

    <!-- Debug info (remove after testing) -->
    <div x-show="debug" class="fixed top-4 left-4 bg-black text-white p-4 z-50 rounded text-sm">
        <div>Modal show: <span x-text="show"></span></div>
        <div>formSlideOverOpen: {{ $formSlideOverOpen ?? 'null' }}</div>
        <div>selectedIndicatorId: {{ $selectedIndicatorId ?? 'null' }}</div>
        <div>formTemplate: {{ $this->formTemplate ? 'exists' : 'null' }}</div>
        @if($this->formTemplate)
        <div>Template ID: {{ $this->formTemplate->id }}</div>
        <div>Template title: {{ $this->formTemplate->title }}</div>
        <div>Template imut_profile_id: {{ $this->formTemplate->imut_profile_id }}</div>
        <div>formFields count: {{ $this->formTemplate->formFields->count() }}</div>
        @else
        <div>formFields count: 0</div>
        @endif
    </div>

    <!-- Modal backdrop -->
    <div class="!fixed !inset-0 bg-black/60 backdrop-blur-sm transition-opacity"
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        wire:click="closeFormSlideOver"></div>

    <!-- Modal container -->
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
        <div class="relative transform overflow-hidden rounded-2xl bg-white dark:bg-slate-900 shadow-2xl transition-all w-full max-w-4xl max-h-[90vh] flex flex-col"
            x-show="show"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">

            <!-- Modal Header -->
            <div class="relative px-6 py-6 bg-gradient-to-br from-emerald-600 via-emerald-700 to-emerald-800 text-white"
                x-data="{ headerVisible: false }"
                x-init="setTimeout(() => headerVisible = true, 100)"
                x-show="headerVisible"
                x-transition:enter="transition ease-out duration-200 delay-100"
                x-transition:enter-start="opacity-0 -translate-y-6"
                x-transition:enter-end="opacity-100 translate-y-0">

                <!-- Background pattern -->
                <div class="absolute inset-0 opacity-10">
                    <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                        <pattern id="modal-grid" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                            <circle cx="10" cy="10" r="1" fill="currentColor" />
                        </pattern>
                        <rect width="100%" height="100%" fill="url(#modal-grid)" />
                    </svg>
                </div>

                <div class="relative flex items-center justify-between">
                    <div class="flex-1">
                        <h3 class="text-xl font-bold leading-6">
                            Form Laporan Harian
                        </h3>
                        <p class="mt-2 text-sm text-emerald-100 flex items-center">
                            @svg("heroicon-m-document-text", "w-4 h-4 mr-2")
                            {{ $selectedIndicatorData['title'] ?? 'Indikator' }} • {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}
                        </p>
                    </div>
                    <button wire:click="closeFormSlideOver"
                        type="button"
                        class="rounded-full p-2 hover:bg-white/20 transition-colors duration-150 focus:outline-none focus:ring-2 focus:ring-white/50">
                        @svg("heroicon-o-x-mark", "h-5 w-5")
                    </button>
                </div>
            </div>

            <!-- Modal Content - Google Form Style -->
            <div class="flex-1 overflow-y-auto">
                @if($this->formTemplate && $this->formTemplate->formFields->isNotEmpty())

                <!-- Simple Form Content -->
                <div class="p-6">
                    <div class="mb-6 text-center">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            {{ $this->formTemplate->title ?? 'Form Laporan Harian' }}
                        </h3>
                        @if($this->formTemplate && $this->formTemplate->description)
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            {{ $this->formTemplate->description }}
                        </p>
                        @endif
                        <div class="mt-4 flex justify-center space-x-4 text-xs text-gray-500">
                            <span>{{ $this->formTemplate->formFields->count() }} pertanyaan</span>
                            <span>•</span>
                            <span>{{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}</span>
                        </div>
                    </div>

                    <!-- Simple Form Rendering -->
                    <div class="max-w-2xl mx-auto">
                        {{ $this->reportEntryForm }}
                    </div>
                </div>

                @else
                <!-- No Form Template State -->
                <div class="p-6 text-center">
                    <div class="max-w-sm mx-auto">
                        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mx-auto mb-4">
                            @svg("heroicon-o-document-text", "w-8 h-8 text-gray-400")
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">No template</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-sm">
                            Form template tidak ditemukan.
                        </p>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Simple Footer -->
    @if($this->formTemplate && $this->formTemplate->formFields->isNotEmpty())
    <div class="px-6 py-4 bg-gray-50 dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700">
        <div class="flex justify-between items-center">
            <span class="text-sm text-gray-500 dark:text-gray-400">
                {{ $this->formTemplate->formFields->count() }} pertanyaan
            </span>

            <div class="flex space-x-3">
                <button wire:click="closeFormSlideOver"
                    type="button"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 dark:bg-slate-700 dark:text-slate-200 dark:border-slate-600">
                    Batal
                </button>

                <button wire:click="saveReport"
                    type="button"
                    class="px-6 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md">
                    Simpan Laporan
                </button>
            </div>
        </div>
    </div>
    @endif

</div>