<x-filament-panels::page>
    {{-- Main Container --}}
    <div class="space-y-4 sm:space-y-6 pb-24 lg:pb-6">
        {{-- Header Section --}}
        <header class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 rounded-lg sm:rounded-xl shadow-lg overflow-hidden">
            <div class="px-4 sm:px-6 py-6 sm:py-8 text-white">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    {{-- Title Section --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start sm:items-center gap-3 mb-2">
                            <div class="flex-shrink-0 p-2 bg-white/20 dark:bg-white/30 rounded-lg backdrop-blur-sm">
                                <x-heroicon-s-document-text class="w-5 h-5 sm:w-6 sm:h-6" />
                            </div>
                            <h1 class="text-xl sm:text-2xl font-bold truncate">{{ $this->getFormTitle() }}</h1>
                        </div>
                        @if($this->getFormDescription())
                        <p class="text-sm sm:text-base text-blue-100 dark:text-blue-200 line-clamp-2 sm:line-clamp-none">
                            {{ $this->getFormDescription() }}
                        </p>
                        @endif
                    </div>

                    {{-- Meta Info --}}
                    <div class="flex flex-row sm:flex-col sm:items-end gap-3 sm:gap-2 text-sm sm:text-base">
                        <div class="flex items-center gap-2 text-blue-100 dark:text-blue-200">
                            <x-heroicon-s-calendar class="w-4 h-4 flex-shrink-0" />
                            <span class="text-xs sm:text-sm font-medium">{{ $this->getFormattedDate() }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-blue-100 dark:text-blue-200">
                            <x-heroicon-s-user class="w-4 h-4 flex-shrink-0" />
                            <span class="text-xs sm:text-sm truncate max-w-[150px] sm:max-w-none">{{ auth()->user()->name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-4 sm:gap-6">
            {{-- Form Section --}}
            <div class="lg:col-span-3 space-y-4 sm:space-y-6">
                <article class="bg-white dark:bg-slate-800 rounded-lg sm:rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
                    <header class="px-4 sm:px-6 py-3 sm:py-4 border-b border-gray-200 dark:border-slate-700 bg-gray-50/50 dark:bg-slate-700/50">
                        <div class="flex items-center justify-between">
                            <h2 class="text-base sm:text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <x-heroicon-s-clipboard-document-check class="w-5 h-5 text-blue-500 dark:text-blue-400" />
                                Form Laporan
                            </h2>
                            <div class="hidden sm:flex items-center gap-2">
                                <x-heroicon-s-shield-check class="w-5 h-5 text-green-500 dark:text-green-400" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Auto-save</span>
                            </div>
                        </div>
                    </header>
                    <div class="p-4 sm:p-6">
                        {{ $this->form }}
                    </div>
                </article>
            </div>

            {{-- Desktop Sidebar (Sticky) --}}
            <aside class="hidden lg:block lg:col-span-1">
                <div class="sticky top-0 space-y-4 sm:space-y-6">
                    {{-- Action Card --}}
                    <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700 overflow-hidden">
                        <div class="p-4 sm:p-6 space-y-4">
                            <button type="button"
                                wire:click="create"
                                class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 text-white font-semibold rounded-lg shadow-lg hover:from-blue-600 hover:to-blue-700 dark:hover:from-blue-700 dark:hover:to-blue-800 transition-all duration-200 hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800 active:scale-95">
                                <x-heroicon-s-paper-airplane class="w-5 h-5" />
                                Simpan Laporan
                            </button>

                            <a href="{{ $this->getResource()::getUrl('index') }}"
                                class="block text-center text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors py-2 rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700/50">
                                ← Kembali ke daftar
                            </a>
                        </div>
                    </div>

                    {{-- Help Card --}}
                    <div class="bg-gradient-to-br from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 rounded-xl shadow-sm border border-yellow-200 dark:border-yellow-700/30 overflow-hidden">
                        <div class="p-4 sm:p-6">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0 p-2 bg-yellow-100 dark:bg-yellow-800/30 rounded-lg">
                                    <x-heroicon-s-light-bulb class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-yellow-800 dark:text-yellow-200 mb-2">Tips Pengisian</h3>
                                    <ul class="text-sm text-yellow-700 dark:text-yellow-300 space-y-1.5">
                                        <li class="flex items-start gap-2">
                                            <span class="text-yellow-600 dark:text-yellow-400 mt-0.5">•</span>
                                            <span>Isi semua field yang wajib</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="text-yellow-600 dark:text-yellow-400 mt-0.5">•</span>
                                            <span>Periksa data sebelum submit</span>
                                        </li>
                                        <li class="flex items-start gap-2">
                                            <span class="text-yellow-600 dark:text-yellow-400 mt-0.5">•</span>
                                            <span>Data tersimpan otomatis</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    {{-- Mobile Fixed Action Bar --}}
    <div class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white dark:bg-slate-800 border-t border-gray-200 dark:border-slate-700 shadow-lg backdrop-blur-lg bg-white/95 dark:bg-slate-800/95">
        <div class="px-4 py-3 safe-bottom">
            <div class="flex items-center gap-3">
                <button type="button"
                    wire:click="create"
                    class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 text-white font-semibold rounded-lg shadow-lg hover:from-blue-600 hover:to-blue-700 dark:hover:from-blue-700 dark:hover:to-blue-800 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 active:scale-95">
                    <x-heroicon-s-paper-airplane class="w-5 h-5" />
                    <span>Simpan</span>
                </button>
                <a href="{{ $this->getResource()::getUrl('index') }}"
                    class="inline-flex items-center justify-center px-4 py-3 border-2 border-gray-300 dark:border-slate-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-slate-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-gray-400 dark:focus:ring-slate-500 active:scale-95">
                    <x-heroicon-s-arrow-left class="w-5 h-5" />
                </a>
            </div>
        </div>
    </div>


    {{-- Auto-save functionality --}}
    @script
    <script>
        // Auto-save interval (optional - can be enabled if needed)
        let autoSaveInterval;
        
        document.addEventListener('livewire:navigated', () => {
            // Could implement auto-save logic here if needed
            // autoSaveInterval = setInterval(() => {
            //     $wire.call('saveDraft');
            // }, 30000);
        });

        // Cleanup on page leave
        document.addEventListener('livewire:navigating', () => {
            if (autoSaveInterval) {
                clearInterval(autoSaveInterval);
            }
        });
    </script>
    @endscript

    <style>
        /* Safe area for mobile devices with notch/home indicator */
        .safe-bottom {
            padding-bottom: max(0.75rem, env(safe-area-inset-bottom));
        }

        /* Smooth scrollbar styling */
        .overflow-auto::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        .overflow-auto::-webkit-scrollbar-track {
            @apply bg-gray-100 dark:bg-slate-700 rounded;
        }

        .overflow-auto::-webkit-scrollbar-thumb {
            @apply bg-gray-300 dark:bg-slate-600 rounded hover:bg-gray-400 dark:hover:bg-slate-500;
        }

        /* Form field focus enhancements */
        .fi-fo-field-wrp:focus-within {
            @apply ring-1 ring-blue-500 dark:ring-blue-400 rounded-lg;
        }

        /* Prevent layout shift on mobile */
        @media (max-width: 1023px) {
            .fi-main {
                padding-bottom: 0 !important;
            }
        }

        /* Optimize sticky positioning */
        @supports (position: sticky) {
            .sticky {
                position: -webkit-sticky;
                position: sticky;
            }
        }

        /* Loading state styling */
        [wire\:loading] {
            @apply opacity-70 pointer-events-none;
        }

        /* Touch-friendly interactive elements */
        @media (hover: none) and (pointer: coarse) {
            button, a {
                min-height: 44px;
                min-width: 44px;
            }
        }
    </style>
</x-filament-panels::page>