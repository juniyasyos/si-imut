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
    @keydown.escape.window="$wire.closeSlideOver()">>

    <!-- Optimized Backdrop -->
    <div class="!absolute !inset-0"
        style="background: rgba(0, 0, 0, 0.8);"
        x-show="show"
        x-transition:enter="ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-100"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
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

            <div class="flex h-full flex-col shadow-xl bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-700">
                <!-- Simplified Header -->
                <div class="relative px-6 py-6 bg-blue-600 text-white">

                    <div class="flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <h2 class="text-xl font-bold truncate text-white">
                                {{ $selectedIndicatorData['title'] ?? 'Indikator' }}
                            </h2>
                            <p class="mt-2 text-sm font-medium flex items-center text-blue-100">
                                @svg("heroicon-m-calendar", "w-4 h-4 mr-2")
                                {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}
                            </p>
                            @if(isset($selectedIndicatorData['category']))
                            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/20 text-white border border-white/30">
                                {{ $selectedIndicatorData['category'] }}
                            </div>
                            @endif
                        </div>
                        <button wire:click="closeSlideOver"
                            type="button"
                            class="ml-4 flex-shrink-0 rounded-full p-2 hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/50 text-white">
                            @svg("heroicon-o-x-mark", "h-6 w-6")
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex-1 overflow-y-auto px-6 py-6 bg-gray-50 dark:bg-slate-800">

                    <!-- Add New Button -->
                    <div class="mb-6">
                        <button type="button"
                            wire:click="createNewReport"
                            class="w-full flex items-center justify-center px-6 py-4 font-semibold rounded-lg bg-green-600 hover:bg-green-700 text-white transition-colors focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                            @svg("heroicon-o-plus", "w-5 h-5 mr-2")
                            <span>Buat Laporan Baru</span>
                        </button>
                    </div>

                    @if($this->selectedIndicatorId && $this->selectedDate)
                    <!-- Existing Reports Section -->
                    <div class="mb-6">

                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">List Laporan di Hari Ini</h3>
                        </div>

                        @if(!empty($this->dailyReports))
                        <div class="space-y-4">
                            @foreach($this->dailyReports as $report)
                            <div class="rounded-lg p-4 border bg-white dark:bg-slate-800 border-gray-200 dark:border-slate-600 hover:shadow-md transition-shadow">
                                <!-- Report Header - Compact Version -->
                                <div class="flex items-center justify-between mb-4">
                                    <div class="flex items-center gap-3">
                                        @if($report['total_score'] >= 80)
                                        <div class="flex items-center gap-2">
                                            <span class="text-2xl">🟢</span>
                                            <span class="text-lg font-bold text-green-700 dark:text-green-400">Patuh</span>
                                        </div>
                                        @else
                                        <div class="flex items-center gap-2">
                                            <span class="text-2xl">🔴</span>
                                            <span class="text-lg font-bold text-red-700 dark:text-red-400">Tidak Patuh</span>
                                        </div>
                                        @endif
                                        <div class="text-3xl font-bold {{ $report['total_score'] >= 80 ? 'text-green-600 dark:text-green-500' : 'text-red-600 dark:text-red-500' }}">
                                            {{ number_format($report['total_score'], 0) }}%
                                        </div>
                                    </div>
                                </div>

                                <!-- Meta Info - Single Line -->
                                <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 mb-4">
                                    <span>{{ $report['unit_name'] }}</span>
                                    <span>·</span>
                                    <span>{{ $report['submitted_by_name'] }}</span>
                                    <span>·</span>
                                    <span>{{ \Carbon\Carbon::parse($report['created_at'])->format('H:i') }}</span>
                                </div>

                                <!-- Field Responses Dropdown -->
                                <div class="mb-4" x-data="{ expanded: false }">
                                    <button @click="expanded = !expanded"
                                        type="button"
                                        class="w-full flex items-center justify-between p-2 rounded text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700">
                                        <span class="flex items-center">
                                            <svg class="w-4 h-4 mr-2 transition-transform duration-150"
                                                :class="{ 'rotate-90': expanded }"
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                            Detail Jawaban ({{ count($report['field_responses']) }} field)
                                        </span>
                                    </button>

                                    <div x-show="expanded" x-collapse class="mt-3 space-y-3">
                                        @foreach($report['field_responses'] as $response)
                                        <div class="p-3 rounded border bg-gray-50 dark:bg-slate-700/50 border-gray-200 dark:border-slate-600">
                                            <div class="flex items-start justify-between mb-2">
                                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                                    {{ $response['field_label'] }}
                                                </span>
                                                <span class="text-xs px-2 py-1 rounded {{ $response['compliance_score'] >= 1 ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400' }}">
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
                                <div class="flex items-center gap-2 pt-3 border-t border-gray-200 dark:border-slate-600">
                                    <button
                                        wire:click="editReport({{ $report['id'] }})"
                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded text-yellow-700 bg-yellow-50 hover:bg-yellow-100 dark:bg-yellow-900/20 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-600">
                                        @svg("heroicon-m-pencil", "w-4 h-4 mr-1.5")
                                        Edit
                                    </button>

                                    <button type="button"
                                        wire:click="deleteReport({{ $report['id'] }})"
                                        class="flex-1 inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded text-red-700 bg-red-50 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 border border-red-200 dark:border-red-600">
                                        @svg("heroicon-m-trash", "w-4 h-4 mr-1.5")
                                        Hapus
                                    </button>

                                    @if(auth()->user()->can('validate_reports'))
                                    <div class="flex-1 relative" x-data="{ open: false }">
                                        <button @click="open = !open" type="button"
                                            class="w-full inline-flex items-center justify-between px-4 py-2 text-sm font-medium rounded border bg-white hover:bg-gray-50 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 border-gray-300 dark:border-slate-600 transition-colors">
                                            <span class="flex items-center gap-2">
                                                @if($report['is_validated'] === 'valid')
                                                <span class="text-green-600 dark:text-green-400">✓</span>
                                                <span>Valid</span>
                                                @elseif($report['is_validated'] === 'invalid')
                                                <span class="text-red-600 dark:text-red-400">✗</span>
                                                <span>Invalid</span>
                                                @else
                                                <span class="text-gray-400">—</span>
                                                <span class="text-gray-500">Tentukan Status</span>
                                                @endif
                                            </span>
                                            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path>
                                            </svg>
                                        </button>

                                        <!-- Dropdown Menu -->
                                        <div x-show="open" @click.away="open = false"
                                            x-transition:enter="transition ease-out duration-100"
                                            x-transition:enter-start="opacity-0 scale-95"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            x-transition:leave="transition ease-in duration-75"
                                            x-transition:leave-start="opacity-100 scale-100"
                                            x-transition:leave-end="opacity-0 scale-95"
                                            class="absolute top-full left-0 right-0 mt-2 bg-white dark:bg-slate-800 border border-gray-300 dark:border-slate-600 rounded-lg shadow-lg z-10">

                                            <button type="button" wire:click="toggleValidation({{ $report['id'] }}, 'valid')" @click="open = false"
                                                class="w-full text-left px-4 py-3 hover:bg-green-50 dark:hover:bg-green-900/20 flex items-center gap-3 {{ $report['is_validated'] === 'valid' ? 'bg-green-50 dark:bg-green-900/20' : '' }} border-b border-gray-200 dark:border-slate-700 last:border-0">
                                                <span class="text-lg text-green-600">✓</span>
                                                <div>
                                                    <div class="font-medium text-gray-900 dark:text-white">Valid</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">Laporan telah tervalidasi</div>
                                                </div>
                                            </button>

                                            <button type="button" wire:click="toggleValidation({{ $report['id'] }}, 'invalid')" @click="open = false"
                                                class="w-full text-left px-4 py-3 hover:bg-red-50 dark:hover:bg-red-900/20 flex items-center gap-3 {{ $report['is_validated'] === 'invalid' ? 'bg-red-50 dark:bg-red-900/20' : '' }} border-b border-gray-200 dark:border-slate-700 last:border-0">
                                                <span class="text-lg text-red-600">✗</span>
                                                <div>
                                                    <div class="font-medium text-gray-900 dark:text-white">Invalid</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">Laporan tidak valid</div>
                                                </div>
                                            </button>

                                            <button type="button" wire:click="toggleValidation({{ $report['id'] }}, null)" @click="open = false"
                                                class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-900/20 flex items-center gap-3 {{ $report['is_validated'] === null ? 'bg-gray-50 dark:bg-gray-900/20' : '' }} border-b border-gray-200 dark:border-slate-700 last:border-0">
                                                <span class="text-lg text-gray-400">—</span>
                                                <div>
                                                    <div class="font-medium text-gray-900 dark:text-white">Hapus</div>
                                                    <div class="text-xs text-gray-500 dark:text-gray-400">Batalkan validasi</div>
                                                </div>
                                            </button>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <!-- Empty State -->
                        <div class="rounded-lg p-8 text-center border-2 border-dashed border-gray-300 dark:border-slate-600 bg-white dark:bg-slate-800">
                            <div class="w-16 h-16 rounded-lg mb-4 mx-auto bg-gray-100 dark:bg-slate-700 flex items-center justify-center">
                                <x-heroicon-o-document-text class="w-8 h-8 text-gray-400 dark:text-slate-400" />
                            </div>
                            <p class="text-base font-semibold text-slate-700 dark:text-slate-200">Belum ada laporan untuk hari ini</p>
                            <p class="text-sm mt-2 text-slate-500 dark:text-slate-400">Mulai buat laporan harian dengan klik tombol di bawah</p>
                        </div>
                        @endif
                    </div>
                    @endif

                    <!-- Helper text -->
                    <p class="mt-6 text-sm text-center text-slate-500 dark:text-slate-400">
                        Tekan
                        <kbd class="inline-flex items-center px-2 py-1 mx-1 text-xs font-semibold rounded border text-slate-700 bg-white border-gray-300 dark:text-slate-200 dark:bg-slate-700 dark:border-slate-600">ESC</kbd>
                        untuk menutup
                    </p>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Input Modal -->
<div class="!fixed !inset-0 !z-[10000] overflow-y-auto"
    x-data="{ show: @entangle('formSlideOverOpen').live }"
    x-show="show"
    x-cloak
    style="display: none; position: fixed !important; top: 0 !important; right: 0 !important; bottom: 0 !important; left: 0 !important; z-index: 10000 !important;"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="$wire.closeFormSlideOver()">


    <!-- Modal backdrop -->
    <div class="!fixed !inset-0 bg-black/60 transition-opacity"
        x-show="show"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        wire:click="closeFormSlideOver"></div>

    <!-- Modal container -->
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-6">
        <div class="relative overflow-hidden rounded-xl bg-white dark:bg-slate-900 shadow-xl w-full max-w-4xl max-h-[90vh] flex flex-col"
            x-show="show"
            x-transition:enter="ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95">

            <!-- Modal Header -->
            <div class="px-6 py-6 bg-emerald-600 text-white">

                <div class="flex items-center justify-between">
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
                        class="rounded-full p-2 hover:bg-white/20 focus:outline-none focus:ring-2 focus:ring-white/50">
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