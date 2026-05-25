<div class="mt-8">
    <div class="space-y-8">
        {{-- Hero Header --}}
        <div class="bg-gradient-to-r from-primary-600 to-primary-400 dark:from-primary-700 dark:to-primary-500 rounded-xl shadow-lg p-8 text-white">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-3 bg-white/20 rounded-lg backdrop-blur-sm">
                            <x-heroicon-o-chart-bar-square class="w-8 h-8" />
                        </div>
                        <div>
                            <h1 class="text-sm md:text-lg lg:text-3xl font-bold">
                                {{ $laporan->name }}
                            </h1>
                            <p class="text-xs md:text-sm lg:text-md text-primary-100 mt-1 flex items-center gap-2">
                                <x-heroicon-o-calendar class="w-4 h-4" />
                                {{ $laporan->assessment_period_start->format('d M Y') }} - {{ $laporan->assessment_period_end->format('d M Y') }}
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 mt-4 text-sm">
                        <div class="flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg backdrop-blur-sm">
                            <x-heroicon-o-building-office-2 class="w-4 h-4" />
                            <span>{{ count($unitKerjaStats) }} Unit Kerja</span>
                        </div>
                        <div class="flex items-center gap-2 bg-white/10 px-3 py-1.5 rounded-lg backdrop-blur-sm">
                            <x-heroicon-o-document-text class="w-4 h-4" />
                            <span>{{ collect($unitKerjaStats)->sum('actual_reports') }} Laporan Terisi</span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <x-filament::button wire:click="refreshStats" color="gray" outlined size="lg" class="!bg-white/20 !border-white/30 hover:!bg-white/30 !text-white flex items-center">
                        <x-heroicon-o-arrow-path class="w-5 h-5 mr-2" />
                        Refresh Data
                    </x-filament::button>
                </div>
            </div>
        </div>

        {{-- Summary Stats --}}
        @php
        $totalUnits = count($unitKerjaStats);
        $activeToday = collect($unitKerjaStats)->where('status', 'active')->count();
        $avgCompletion = $totalUnits > 0 ? round(collect($unitKerjaStats)->avg('completion_rate'), 1) : 0;
        $avgCompliance = $totalUnits > 0 ? round(collect($unitKerjaStats)->avg('compliance_rate'), 1) : 0;
        @endphp

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            {{-- Total Unit Kerja --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:shadow-md transition-shadow p-6 border border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Unit Kerja</p>
                        <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $totalUnits }}</p>
                    </div>
                    <div class="p-3 bg-gray-100 dark:bg-gray-700 rounded-lg">
                        <x-heroicon-o-building-office-2 class="w-8 h-8 text-gray-600 dark:text-gray-400" />
                    </div>
                </div>
            </div>

            {{-- Aktif Hari Ini --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:shadow-md transition-shadow p-6 border border-green-200 dark:border-green-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-green-600 dark:text-green-400">Aktif Hari Ini</p>
                        <p class="text-3xl font-bold text-green-700 dark:text-green-300 mt-2">{{ $activeToday }}</p>
                        <p class="text-xs text-green-600 dark:text-green-400 mt-1">{{ round(($activeToday / max($totalUnits, 1)) * 100) }}% dari total</p>
                    </div>
                    <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                        <x-heroicon-o-check-circle class="w-8 h-8 text-green-600 dark:text-green-400" />
                    </div>
                </div>
            </div>

            {{-- Rata-rata Completion --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:shadow-md transition-shadow p-6 border border-blue-200 dark:border-blue-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-blue-600 dark:text-blue-400">Rata-rata Completion</p>
                        <p class="text-3xl font-bold text-blue-700 dark:text-blue-300 mt-2">{{ $avgCompletion }}%</p>
                        <div class="w-24 bg-blue-200 dark:bg-blue-900/30 rounded-full h-1.5 mt-2">
                            <div class="bg-blue-600 h-1.5 rounded-full" style="width: {{ $avgCompletion }}%"></div>
                        </div>
                    </div>
                    <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                        <x-heroicon-o-chart-bar class="w-8 h-8 text-blue-600 dark:text-blue-400" />
                    </div>
                </div>
            </div>

            {{-- Rata-rata Compliance --}}
            <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:shadow-md transition-shadow p-6 border border-purple-200 dark:border-purple-700">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-purple-600 dark:text-purple-400">Rata-rata Compliance</p>
                        <p class="text-3xl font-bold text-purple-700 dark:text-purple-300 mt-2">{{ $avgCompliance }}%</p>
                        <div class="w-24 bg-purple-200 dark:bg-purple-900/30 rounded-full h-1.5 mt-2">
                            <div class="bg-purple-600 h-1.5 rounded-full" style="width: {{ $avgCompliance }}%"></div>
                        </div>
                    </div>
                    <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                        <x-heroicon-o-shield-check class="w-8 h-8 text-purple-600 dark:text-purple-400" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Unit Kerja Grid --}}
        <div>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Unit Kerja</h2>
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-information-circle class="w-4 h-4" />
                    <span>Data diupdate otomatis setiap 60 detik</span>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @foreach ($unitKerjaStats as $stat)
                <div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm hover:shadow-lg transition-all duration-300 border border-gray-200 dark:border-gray-700 overflow-hidden group">
                    {{-- Status Bar --}}
                    <div class="h-2 {{ 
                            match($stat['status']) {
                                'active' => 'bg-gradient-to-r from-green-500 to-emerald-500',
                                'complete' => 'bg-gradient-to-r from-blue-500 to-cyan-500',
                                'warning' => 'bg-gradient-to-r from-yellow-500 to-orange-500',
                                'danger' => 'bg-gradient-to-r from-red-500 to-rose-500',
                                default => 'bg-gray-500'
                            }
                        }}"></div>

                    <div class="p-6">
                        {{-- Header --}}
                        <div class="flex items-start justify-between mb-5">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <div class="p-2 rounded-lg {{
                                            match($stat['status']) {
                                                'active' => 'bg-green-100 dark:bg-green-900/30',
                                                'complete' => 'bg-blue-100 dark:bg-blue-900/30',
                                                'warning' => 'bg-yellow-100 dark:bg-yellow-900/30',
                                                'danger' => 'bg-red-100 dark:bg-red-900/30',
                                                default => 'bg-gray-100 dark:bg-gray-900/30'
                                            }
                                        }}">
                                        <x-heroicon-o-building-office class="w-5 h-5 {{
                                                match($stat['status']) {
                                                    'active' => 'text-green-600 dark:text-green-400',
                                                    'complete' => 'text-blue-600 dark:text-blue-400',
                                                    'warning' => 'text-yellow-600 dark:text-yellow-400',
                                                    'danger' => 'text-red-600 dark:text-red-400',
                                                    default => 'text-gray-600 dark:text-gray-400'
                                                }
                                            }}" />
                                    </div>
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ $stat['name'] }}
                                    </h3>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{
                                            match($stat['status']) {
                                                'active' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                                'complete' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                                'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                                'danger' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                                default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400'
                                            }
                                        }}">
                                        {{ $this->getStatusLabel($stat['status']) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Progress Bars --}}
                        <div class="space-y-5">
                            {{-- Completion Rate --}}
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Completion Rate</span>
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">{{ $stat['completion_rate'] }}%</span>
                                </div>
                                <div class="relative">
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                                        <div class="h-3 rounded-full transition-all duration-500 ease-out {{ 
                                                $stat['completion_rate'] >= 90 ? 'bg-gradient-to-r from-green-500 to-emerald-500' : 
                                                ($stat['completion_rate'] >= 50 ? 'bg-gradient-to-r from-yellow-500 to-orange-500' : 'bg-gradient-to-r from-red-500 to-rose-500')
                                            }}" style="width: {{ min($stat['completion_rate'], 100) }}%"></div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1.5 flex items-center justify-between">
                                    <span>{{ $stat['actual_reports'] }} / {{ $stat['expected_reports'] }} laporan</span>
                                    <span class="font-medium">{{ $stat['expected_reports'] - $stat['actual_reports'] }} kurang</span>
                                </div>
                            </div>

                            {{-- Compliance Rate --}}
                            <div>
                                <div class="flex justify-between items-center mb-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Compliance Rate</span>
                                    <span class="text-lg font-bold text-purple-700 dark:text-purple-300">{{ $stat['compliance_rate'] }}%</span>
                                </div>
                                <div class="relative">
                                    <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                                        <div class="bg-gradient-to-r from-purple-500 to-indigo-500 h-3 rounded-full transition-all duration-500 ease-out" style="width: {{ min($stat['compliance_rate'], 100) }}%"></div>
                                    </div>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1.5">
                                    {{ $stat['perfect_reports'] }} dari {{ $stat['actual_reports'] }} laporan perfect (100%)
                                </div>
                            </div>
                        </div>

                        {{-- Stats Grid --}}
                        <div class="grid grid-cols-3 gap-4 mt-5 pt-5 border-t border-gray-200 dark:border-gray-700">
                            <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Hari Ini</div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stat['today_reports'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">laporan</div>
                            </div>
                            <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Indikator</div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stat['expected_indicators'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">aktif</div>
                            </div>
                            <div class="text-center p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Progress</div>
                                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stat['days_passed'] }}/{{ $stat['total_days'] }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">hari</div>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="mt-5 pt-5 border-t border-gray-200 dark:border-gray-700">
                            <a href="{{ route('filament.siimut.resources.laporan-imuts.monitoring-unit-detail', [
                                    'record' => $laporan->slug,
                                    'unit' => $stat['id']
                                ]) }}"
                                class="block w-full">
                                <x-filament::button color="primary" size="md" class="w-full flex flex-row justify-center group-hover:shadow-lg transition-all">
                                    <x-heroicon-o-document-text class="w-5 h-5 mr-2" />
                                    Lihat Detail Laporan
                                    <x-heroicon-o-arrow-right class="w-4 h-4 ml-2" />
                                </x-filament::button>
                            </a>
                        </div>

                        {{-- Last Submission --}}
                        @if ($stat['last_submission'])
                        <div class="mt-4 flex items-center justify-center text-xs text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-gray-700/30 rounded-lg py-2">
                            <x-heroicon-o-clock class="w-4 h-4 mr-1.5" />
                            <span>Terakhir input: <span class="font-medium">{{ $stat['last_submission_human'] }}</span></span>
                        </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        @if (empty($unitKerjaStats))
        <div class="bg-white dark:bg-slate-800 rounded-lg shadow p-12 text-center">
            <x-heroicon-o-inbox class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" />
            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Tidak Ada Data</h3>
            <p class="text-gray-500 dark:text-gray-400">Belum ada unit kerja yang terdaftar di laporan ini.</p>
        </div>
        @endif
    </div>

    @script
    <script>
        // Auto refresh setiap 60 detik
        setInterval(() => {
            $wire.refreshStats();
        }, 60000);

        // Listen to refresh event
        $wire.on('stats-refreshed', () => {
            // Optional: show toast notification
            console.log('Stats refreshed');
        });
    </script>
    @endscript
</div>