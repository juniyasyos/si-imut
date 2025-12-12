<!-- Slide Over -->
<div class="!fixed !inset-0 !z-[9999] overflow-hidden"
    x-data="{ show: @entangle('slideOverOpen').live }"
    x-show="show"
    x-cloak
    style="display: none; position: fixed !important; top: 0 !important; right: 0 !important; bottom: 0 !important; left: 0 !important; z-index: 9999 !important;"
    x-transition:enter="transition-opacity ease-out duration-400"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition-opacity ease-in duration-300"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    @keydown.escape.window="$wire.closeSlideOver()">

    <!-- Enhanced Backdrop with strong blur effect -->
    <div class="!absolute !inset-0 backdrop-blur-md backdrop-saturate-150"
        style="background: linear-gradient(to bottom right, rgba(17, 24, 39, 0.7), rgba(17, 24, 39, 0.8), rgba(0, 0, 0, 0.9));"
        x-show="show"
        x-transition:enter="ease-out duration-400"
        x-transition:enter-start="opacity-0 backdrop-blur-none"
        x-transition:enter-end="opacity-100 backdrop-blur-md"
        x-transition:leave="ease-in duration-300"
        x-transition:leave-start="opacity-100 backdrop-blur-md"
        x-transition:leave-end="opacity-0 backdrop-blur-none"
        wire:click="closeSlideOver"></div>

    <!-- Slide Over Panel -->
    <div class="!fixed !inset-y-0 !right-0 !left-auto flex max-w-full pl-10 sm:pl-16 pointer-events-none"
        style="position: fixed !important; top: 0 !important; right: 0 !important; bottom: 0 !important; left: auto !important; z-index: 10000 !important;">
        <div class="w-screen max-w-full md:max-w-5xl pointer-events-auto"
            x-show="show"
            x-transition:enter="transform transition ease-out duration-500"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transform transition ease-in duration-400"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0">

            <div class="flex h-full flex-col shadow-[0_0_80px_rgba(0,0,0,0.5)] ring-1 backdrop-blur-xl"
                style="background-color: #ffffff; box-shadow: 0 0 80px rgba(0,0,0,0.5); border: 1px solid rgba(0,0,0,0.05);">
                <!-- Header with enhanced gradient and glass effect -->
                <div class="relative px-6 py-7 shadow-2xl"
                    style="background: linear-gradient(to bottom right, #2563eb, #1d4ed8, #1e40af);"
                    x-data="{ headerVisible: false }"
                    x-init="setTimeout(() => headerVisible = true, 150)"
                    x-show="headerVisible"
                    x-transition:enter="transition ease-out duration-500 delay-150"
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
                    <div class="absolute inset-0 bg-gradient-to-t from-black/10 via-transparent to-white/5"></div>

                    <div class="relative flex items-start justify-between">
                        <div class="flex-1 min-w-0">
                            <h2 class="text-xl font-bold truncate drop-shadow-lg" style="color: #ffffff;">
                                {{ $selectedIndicatorData['title'] ?? 'Indikator' }}
                            </h2>
                            <p class="mt-2 text-sm font-medium flex items-center drop-shadow-md" style="color: #eff6ff;">
                                @svg("heroicon-m-calendar", "w-4 h-4 mr-2")
                                {{ \Carbon\Carbon::parse($selectedDate)->translatedFormat('d F Y') }}
                            </p>
                            @if(isset($selectedIndicatorData['category']))
                            <div class="mt-3 inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold backdrop-blur-md shadow-lg"
                                style="background-color: rgba(255,255,255,0.25); color: #ffffff; border: 1px solid rgba(255,255,255,0.3);">
                                {{ $selectedIndicatorData['category'] }}
                            </div>
                            @endif
                        </div>
                        <button wire:click="closeSlideOver"
                            type="button"
                            class="ml-4 flex-shrink-0 rounded-full p-2.5 focus:outline-none focus:ring-2 transition-all duration-300 transform hover:scale-110 hover:rotate-90 active:scale-95 backdrop-blur-sm"
                            style="color: #eff6ff;"
                            onmouseover="this.style.backgroundColor='rgba(255,255,255,0.2)'; this.style.color='#ffffff';"
                            onmouseout="this.style.backgroundColor=''; this.style.color='#eff6ff';">
                            @svg("heroicon-o-x-mark", "h-6 w-6 drop-shadow-md")
                        </button>
                    </div>
                </div>

                <!-- Content with enhanced staggered animations and glass effect -->
                <div class="flex-1 overflow-y-auto px-6 py-6 backdrop-blur-sm"
                    style="background: linear-gradient(to bottom right, #f9fafb, rgba(243, 244, 246, 0.5), #f9fafb);">
                    <!-- Data List Placeholder with enhanced styling -->
                    <div class="mb-6"
                        x-data="{ contentVisible: false }"
                        x-init="setTimeout(() => contentVisible = true, 300)"
                        x-show="contentVisible"
                        x-transition:enter="transition ease-out duration-500 delay-250"
                        x-transition:enter-start="opacity-0 translate-y-6 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                        <div class="backdrop-blur-xl rounded-2xl p-10 text-center border-2 border-dashed shadow-xl hover:shadow-2xl transition-all duration-300"
                            style="background-color: rgba(255,255,255,0.8); border-color: #d1d5db;"
                            onmouseover="this.style.borderColor='#9ca3af';"
                            onmouseout="this.style.borderColor='#d1d5db';">
                            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl mb-4 shadow-lg"
                                style="background: linear-gradient(to bottom right, #f3f4f6, #e5e7eb, #f3f4f6); box-shadow: 0 0 0 4px #f3f4f6;">
                                <x-heroicon-o-document-text class="w-10 h-10" style="color: #9ca3af;" />
                            </div>
                            <p class="text-base font-semibold" style="color: #374151;">Data list akan muncul di sini</p>
                            <p class="text-sm mt-2" style="color: #6b7280;">Riwayat entry hari ini</p>
                        </div>
                    </div>

                    <!-- Enhanced Divider with glow effect -->
                    <div class="relative my-8"
                        x-data="{ dividerVisible: false }"
                        x-init="setTimeout(() => dividerVisible = true, 400)"
                        x-show="dividerVisible"
                        x-transition:enter="transition ease-out duration-500 delay-350"
                        x-transition:enter-start="opacity-0 scale-x-0"
                        x-transition:enter-end="opacity-100 scale-x-100">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full shadow-sm" style="border-top: 2px solid #d1d5db;"></div>
                        </div>
                        <div class="relative flex justify-center">
                            <span class="px-4 py-1 text-xs font-bold uppercase tracking-widest backdrop-blur-sm rounded-full shadow-md"
                                style="background: linear-gradient(to bottom right, #f9fafb, #ffffff, #f9fafb); color: #4b5563; border: 1px solid #e5e7eb;">
                                Tambah Data Baru
                            </span>
                        </div>
                    </div>

                    <!-- Enhanced Add New Button with advanced animations -->
                    <div x-data="{ buttonVisible: false }"
                        x-init="setTimeout(() => buttonVisible = true, 500)"
                        x-show="buttonVisible"
                        x-transition:enter="transition ease-out duration-500 delay-450"
                        x-transition:enter-start="opacity-0 translate-y-6 scale-90"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100">
                        <button type="button"
                            class="group relative w-full flex items-center justify-center px-6 py-5 font-bold rounded-2xl shadow-2xl transform hover:scale-[1.03] active:scale-[0.97] transition-all duration-300 focus:outline-none focus:ring-4 focus:ring-offset-2 overflow-hidden backdrop-blur-sm"
                            style="background: linear-gradient(to right, #2563eb, #1d4ed8, #1e40af); color: #ffffff; box-shadow: 0 0 40px rgba(37, 99, 235, 0.5);"
                            onmouseover="this.style.background='linear-gradient(to right, #1d4ed8, #1e40af, #1e3a8a)'; this.style.boxShadow='0 0 50px rgba(37, 99, 235, 0.7)';"
                            onmouseout="this.style.background='linear-gradient(to right, #2563eb, #1d4ed8, #1e40af)'; this.style.boxShadow='0 0 40px rgba(37, 99, 235, 0.5)';">
                            <!-- Multiple shine effects on hover -->
                            <div class="absolute inset-0 transform -skew-x-12 -translate-x-full group-hover:translate-x-full transition-transform duration-1000 ease-in-out"
                                style="background: linear-gradient(to right, transparent, rgba(255,255,255,0.3), transparent);"></div>
                            <div class="absolute inset-0 transform skew-x-12 translate-x-full group-hover:-translate-x-full transition-transform duration-1200 ease-in-out delay-100"
                                style="background: linear-gradient(to left, transparent, rgba(255,255,255,0.1), transparent);"></div>

                            <!-- Glow effect -->
                            <div class="absolute inset-0 opacity-0 group-hover:opacity-100 blur-xl transition-opacity duration-500"
                                style="background: linear-gradient(to right, rgba(96, 165, 250, 0.2), rgba(59, 130, 246, 0.2), rgba(37, 99, 235, 0.2));"></div>

                            @svg("heroicon-o-plus-circle", "relative w-6 h-6 mr-2.5 transform group-hover:rotate-180 group-hover:scale-110 transition-all duration-500 drop-shadow-lg")
                            <span class="relative text-base drop-shadow-md">Tambah Entry Baru</span>
                        </button>
                    </div>

                    <!-- Enhanced Helper text with badge styling -->
                    <p class="mt-6 text-sm text-center"
                        style="color: #6b7280;"
                        x-data="{ helperVisible: false }"
                        x-init="setTimeout(() => helperVisible = true, 600)"
                        x-show="helperVisible"
                        x-transition:enter="transition ease-out duration-400 delay-550"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        Tekan
                        <kbd class="inline-flex items-center px-3 py-1.5 mx-1 text-xs font-bold rounded-lg shadow-md transition-colors duration-200"
                            style="color: #374151; background-color: #ffffff; border: 2px solid #d1d5db;"
                            onmouseover="this.style.backgroundColor='#f3f4f6';"
                            onmouseout="this.style.backgroundColor='#ffffff';">ESC</kbd>
                        untuk menutup
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>