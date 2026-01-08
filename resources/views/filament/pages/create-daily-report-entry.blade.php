<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Modern Header Section --}}
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl shadow-lg overflow-hidden">
            <div class="px-6 py-8 text-white">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                                <x-heroicon-s-document-text class="w-6 h-6" />
                            </div>
                            <div>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-white/20 text-white backdrop-blur-sm">
                                    {{ $this->getCategoryBadgeColor() == 'blue' ? 'Laporan' : ucfirst($this->getCategoryBadgeColor()) }}
                                </span>
                            </div>
                        </div>
                        <h1 class="text-2xl font-bold mb-2">{{ $this->getFormTitle() }}</h1>
                        @if($this->getFormDescription())
                        <p class="text-blue-100 opacity-90">{{ $this->getFormDescription() }}</p>
                        @endif
                    </div>

                    <div class="flex flex-col sm:items-end gap-2">
                        <div class="flex items-center gap-2 text-blue-100">
                            <x-heroicon-s-calendar class="w-4 h-4" />
                            <span class="text-sm font-medium">{{ $this->getFormattedDate() }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-blue-100">
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
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Form Laporan</h3>
                            <div class="flex items-center gap-2">
                                <x-heroicon-s-shield-check class="w-5 h-5 text-green-500" />
                                <span class="text-sm text-gray-600">Auto-save aktif</span>
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
                <div class="bg-white rounded-xl shadow-sm border border-gray-200">
                    <div class="p-6">
                        <button type="button"
                            wire:click="create"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white font-semibold rounded-lg shadow-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 transform hover:scale-[1.02] focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            <x-heroicon-s-paper-airplane class="w-5 h-5" />
                            Simpan Laporan
                        </button>

                        <div class="mt-4 text-center">
                            <a href="{{ $this->getResource()::getUrl('index') }}"
                                class="text-sm text-gray-500 hover:text-gray-700 transition-colors">
                                Kembali ke daftar laporan
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Help Card --}}
                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-xl shadow-sm border border-yellow-200">
                    <div class="p-6">
                        <div class="flex items-start gap-3">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <x-heroicon-s-light-bulb class="w-5 h-5 text-yellow-600" />
                            </div>
                            <div>
                                <h4 class="font-semibold text-yellow-800 mb-2">Tips Pengisian</h4>
                                <ul class="text-sm text-yellow-700 space-y-1">
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
            border-radius: 3px;
        }

        .overflow-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }

        .overflow-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
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
    </style>
</x-filament-panels::page>