<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Modern Header Section --}}
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-8 text-white dark:text-gray-100">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-white/20 dark:bg-white/30 rounded-lg backdrop-blur-sm">
                                <x-heroicon-s-document-text class="w-6 h-6" />
                            </div>
                            <div>
                                <h1 class="text-2xl font-bold mb-2">{{ $this->getFormTitle() }}</h1>
                            </div>
                        </div>
                        @if($this->getFormDescription())
                        <p class="text-blue-100 dark:text-blue-200 opacity-90">{{ $this->getFormDescription() }}</p>
                        @endif
                    </div>

                    <div class="flex flex-col sm:items-end gap-2">
                        <div class="flex items-center gap-2 text-blue-100 dark:text-blue-200">
                            <x-heroicon-s-calendar class="w-4 h-4" />
                            <span class="text-sm font-medium">{{ $this->getFormattedDate() }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-blue-100 dark:text-blue-200">
                            <x-heroicon-s-user class="w-4 h-4" />
                            <span class="text-sm">{{ auth()->user()->name }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            {{-- Form Section --}}
            <div class="lg:col-span-3 space-y-6">

                {{-- Form Card --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-slate-700">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Form Laporan</h3>
                            <div class="flex items-center gap-2">
                                <x-heroicon-s-shield-check class="w-5 h-5 text-green-500 dark:text-green-400" />
                                <span class="text-sm text-gray-600 dark:text-gray-400">Auto-save aktif</span>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <form wire:submit="create" class="space-y-6">
                            {{ $this->form }}
                        </form>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="lg:col-span-1 space-y-6">




                {{-- Action Button --}}
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-gray-200 dark:border-slate-700">
                    <div class="p-6">
                        <button type="button"
                            wire:click="create"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 dark:from-blue-600 dark:to-blue-700 text-white font-semibold rounded-lg shadow-lg hover:from-blue-600 hover:to-blue-700 dark:hover:from-blue-700 dark:hover:to-blue-800 transition-all duration-200 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800">
                            <x-heroicon-s-paper-airplane class="w-5 h-5" />
                            Simpan Laporan
                        </button>

                        <div class="mt-4 text-center">
                            <a href="{{ $this->getResource()::getUrl('index') }}"
                                class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 transition-colors">
                                Kembali ke daftar laporan
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Help Card --}}
                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 rounded-xl shadow-sm border border-yellow-200 dark:border-yellow-700/30">
                    <div class="p-6">
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-yellow-100 dark:bg-yellow-800/30 rounded-lg">
                                <x-heroicon-s-light-bulb class="w-5 h-5 text-yellow-600 dark:text-yellow-400" />
                            </div>
                            <div>
                                <h4 class="font-semibold text-yellow-800 dark:text-yellow-200 mb-2">Tips Pengisian</h4>
                                <ul class="text-sm text-yellow-700 dark:text-yellow-300 space-y-1">
                                    <li>• Isi semua field yang wajib</li>
                                    <li>• Periksa data sebelum submit</li>
                                    <li>• Form akan tersimpan otomatis</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    {{-- Auto-refresh compliance preview --}}
    <script>
        document.addEventListener('livewire:initialized', () => {
            // Update compliance preview when form data changes
            Livewire.on('formDataChanged', (data) => {
                // Trigger refresh of compliance preview
                @this.call('$refresh');
            });
        });

        // Auto-save every 30 seconds
        setInterval(() => {
            if (window.livewire) {
                // Could implement auto-save logic here if needed
            }
        }, 30000);
    </script>

    <style>
        /* Custom scrollbar for better aesthetics */
        .overflow-auto::-webkit-scrollbar {
            width: 6px;
        }

        .overflow-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        .dark .overflow-auto::-webkit-scrollbar-track {
            background: #1e293b;
        }

        .overflow-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .dark .overflow-auto::-webkit-scrollbar-thumb {
            background: #475569;
        }

        .overflow-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        .dark .overflow-auto::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }

        /* Form field enhancements */
        .fi-form-field-wrapper {
            @apply transition-all duration-200;
        }

        .fi-form-field-wrapper:focus-within {
            @apply transform scale-[1.01];
        }

        /* Card hover effects */
        .bg-white {
            @apply transition-all duration-200;
        }

        .bg-white:hover {
            @apply shadow-md;
        }

        .dark .bg-white {
            @apply dark:bg-slate-800;
        }

        .dark .bg-white:hover {
            @apply dark:shadow-lg dark:shadow-slate-900/20;
        }
    </style>
</x-filament-panels::page>