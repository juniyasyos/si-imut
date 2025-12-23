<x-filament-widgets::widget class="fi-filament-info-widget">
    <x-filament::section>
        @if ($unitKerja)
        <div class="space-y-6">
            {{-- Header Section --}}
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <div>
                            <h2 class="text-[clamp(14px,2.5vw,28px)] font-semibold text-gray-900 dark:text-white">
                                {{ $unitKerja->unit_name }}
                            </h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400">
                                ID: {{ $unitKerja->id }} • Dibuat
                                {{ $unitKerja->created_at->translatedFormat('d M Y') }}
                            </p>
                        </div>
                    </div>
                    @if ($unitKerja->description)
                    <p class="text-sm text-gray-600 dark:text-gray-400 ml-13">
                        {{ $unitKerja->description }}
                    </p>
                    @endif
                </div>
            </div>

            {{-- Statistics Grid --}}
            <div class="grid grid-cols-2 gap-2">
                <div
                    class="px-4 py-3 border rounded-lg border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-slate-800/80/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Penanggung Jawab</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ $stats['total_users'] }}
                            </p>
                        </div>
                        @svg('heroicon-o-users', 'w-8 h-8 text-gray-400 dark:text-gray-600')
                    </div>
                </div>

                <div
                    class="px-4 py-3 border rounded-lg border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-slate-800/80/50">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Laporan</p>
                            <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">
                                {{ $stats['total_reports'] }}
                            </p>
                        </div>
                        @svg("heroicon-o-clipboard-document-list", "w-8 h-8 text-gray-400 dark:text-gray-600")
                    </div>
                </div>
            </div>

            {{-- Team Members --}}
            <div>
                <h3 class="mb-3 text-xs font-semibold tracking-wide text-gray-500 uppercase dark:text-gray-400">
                    Penanggung Jawab
                </h3>
                <div class="space-y-2">
                    @forelse ($unitKerja->users as $user)
                    <div
                        class="flex items-center gap-3 px-3 py-2 border rounded-lg border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <div
                            class="flex items-center justify-center w-8 h-8 text-xs font-medium rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                            {{ Str::substr($user->name, 0, 2) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate dark:text-white">
                                {{ $user->name }}
                            </p>
                            <p class="text-xs text-gray-500 truncate dark:text-gray-400">{{ $user->email }}
                            </p>
                        </div>
                    </div>
                    @empty
                    <div
                        class="px-4 py-8 text-center border border-dashed rounded-lg border-gray-300 dark:border-gray-700">
                        @svg("heroicon-o-user-group", "w-12 h-12 mx-auto text-gray-400 dark:text-gray-600")
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            Belum ada penanggung jawab terdaftar
                        </p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
        @else
        <div class="px-4 py-12 text-center">
            @svg("heroicon-o-building-office-2", "w-16 h-16 mx-auto text-gray-400 dark:text-gray-600")
            <p class="mt-4 text-sm font-medium text-gray-900 dark:text-white">
                Tidak Ada Unit Kerja
            </p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                Anda belum memiliki unit kerja yang terdaftar dalam sistem.
            </p>
        </div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>