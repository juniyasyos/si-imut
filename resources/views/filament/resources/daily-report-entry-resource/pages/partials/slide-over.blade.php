<!-- Slide Over -->
<div class="fixed inset-0 z-50 overflow-hidden"
    x-data="{ show: @entangle('slideOverOpen').live }"
    x-show="show"
    x-cloak
    style="display: none;"
    x-transition:enter="transition-opacity ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-250"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="$wire.closeSlideOver()">

    <!-- Backdrop with blur effect -->
    <div class="absolute inset-0 bg-gray-900/60 dark:bg-gray-950/75 backdrop-blur-sm"
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        wire:click="closeSlideOver"></div>

    <!-- Slide Over Panel -->
    <div class="fixed inset-y-0 right-0 flex max-w-full pl-10 pointer-events-none">
        <div class="w-screen max-w-3xl pointer-events-auto"
            x-show="show"
            x-transition:enter="transform transition ease-out duration-200 sm:duration-200"
            x-transition:enter-start="translate-x-full opacity-0 scale-95"
            x-transition:enter-end="translate-x-0 opacity-100 scale-100"
            x-transition:leave="transform transition ease-in duration-150 sm:duration-400"
            x-transition:leave-start="translate-x-0 opacity-100 scale-100"
            x-transition:leave-end="translate-x-full opacity-0 scale-95">

            <div class="flex h-full flex-col bg-white dark:bg-gray-800 shadow-2xl ring-1 ring-black/10 dark:ring-white/10">
                <!-- Header with gradient -->
                <div class="relative bg-gradient-to-br from-primary-600 to-primary-700 dark:from-primary-700 dark:to-primary-800 px-6 py-6 shadow-lg"
                    x-data="{ headerVisible: false }"
                    x-init="setTimeout(() => headerVisible = true, 100)"
                    x-show="headerVisible"
                    x-transition:enter="transition ease-out duration-400 delay-100"
                    x-transition:enter-start="opacity-0 -translate-y-4"
                    x-transition:enter-end="opacity-100 translate-y-0">

                    <!-- Decorative background pattern -->
                    <div class="absolute inset-0 opacity-10">
                        <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <pattern id="grid" width="32" height="32" patternUnits="userSpaceOnUse">
                                    <circle cx="16" cy="16" r="1" fill="white" />
                                </pattern>
                            </defs>
                            <rect width="100%" height="100%" fill="url(#grid)" />
                        </svg>
                    </div>

                    <div class="relative flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <h2 class="text-lg font-bold text-white truncate">
                                {{ $selectedIndicatorData['title'] ?? 'Indikator' }}
                            </h2>
                            <p class="mt-1.5 text-sm text-primary-100 font-medium flex items-center">
                                <x-heroicon-m-calendar class="w-4 h-4 mr-1.5" />
                                {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}
                            </p>
                            @if(isset($selectedIndicatorData['category']))
                            <div class="mt-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white backdrop-blur-sm">
                                {{ $selectedIndicatorData['category'] }}
                            </div>
                            @endif
                        </div>
                        <button wire:click="closeSlideOver"
                            type="button"
                            class="ml-3 flex-shrink-0 rounded-full p-2 text-primary-100 hover:bg-white/10 hover:text-white focus:outline-none focus:ring-2 focus:ring-white/50 transition-all duration-200 transform hover:scale-110">
                            <x-heroicon-o-x-mark class="h-6 w-6" />
                        </button>
                    </div>
                </div>

                <!-- Content with staggered animations -->
                <div class="flex-1 overflow-y-auto px-6 py-6 bg-gray-50 dark:bg-gray-900/50">
                    <!-- Data List Placeholder -->
                    <div class="mb-6"
                        x-data="{ contentVisible: false }"
                        x-init="setTimeout(() => contentVisible = true, 250)"
                        x-show="contentVisible"
                        x-transition:enter="transition ease-out duration-400 delay-200"
                        x-transition:enter-start="opacity-0 translate-y-4"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-8 text-center border-2 border-dashed border-gray-200 dark:border-gray-700 shadow-sm">
                            <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 mb-3">
                                <x-heroicon-o-document-text class="w-8 h-8 text-gray-400 dark:text-gray-500" />
                            </div>
                            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Data list akan muncul di sini</p>
                            <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">Riwayat entry hari ini</p>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="relative my-6"
                        x-data="{ dividerVisible: false }"
                        x-init="setTimeout(() => dividerVisible = true, 350)"
                        x-show="dividerVisible"
                        x-transition:enter="transition ease-out duration-300 delay-300"
                        x-transition:enter-start="opacity-0 scale-x-0"
                        x-transition:enter-end="opacity-100 scale-x-100">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t-2 border-gray-300 dark:border-gray-600"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span class="bg-gray-50 dark:bg-gray-900/50 px-3 text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                Tambah Data Baru
                            </span>
                        </div>
                    </div>

                    <!-- Add New Button with icon animation -->
                    <div x-data="{ buttonVisible: false }"
                        x-init="setTimeout(() => buttonVisible = true, 450)"
                        x-show="buttonVisible"
                        x-transition:enter="transition ease-out duration-400 delay-400"
                        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                        <button type="button"
                            class="group relative w-full flex items-center justify-center px-5 py-4 bg-gradient-to-r from-primary-600 to-primary-700 hover:from-primary-700 hover:to-primary-800 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl transform hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900 overflow-hidden">
                            <!-- Shine effect on hover -->
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-700 ease-in-out"></div>

                            <x-heroicon-o-plus-circle class="relative w-5 h-5 mr-2 transform group-hover:rotate-90 transition-transform duration-300" />
                            <span class="relative">Tambah Entry Baru</span>
                        </button>
                    </div>

                    <!-- Helper text -->
                    <p class="mt-4 text-xs text-center text-gray-500 dark:text-gray-500"
                        x-data="{ helperVisible: false }"
                        x-init="setTimeout(() => helperVisible = true, 550)"
                        x-show="helperVisible"
                        x-transition:enter="transition ease-out duration-300 delay-500"
                        x-transition:enter-start="opacity-0"
                        x-transition:enter-end="opacity-100">
                        <kbd class="px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-100 border border-gray-200 rounded-lg dark:bg-gray-700 dark:text-gray-300 dark:border-gray-600">ESC</kbd>
                        untuk menutup
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>