<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Infolist untuk menampilkan informasi indikator --}}
        {{ $this->infolist }}

        {{-- Table untuk menampilkan data penilaian per unit kerja --}}
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header flex items-center gap-3 px-6 py-4">
                <div class="fi-section-header-content">
                    <h3 class="fi-section-title text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        Data Penilaian Per Unit Kerja
                    </h3>
                    <p class="fi-section-description mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Rincian pencapaian indikator mutu per unit kerja untuk periode yang dipilih.
                    </p>
                </div>
            </div>

            <div class="fi-section-content p-6">
                {{ $this->table }}
            </div>
        </div>

        {{-- Chart section (dapat ditambahkan widget chart di sini) --}}
        @if($this->selectedLaporan && !empty($this->summaryStats))
        <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <div class="fi-section-header flex items-center gap-3 px-6 py-4">
                <div class="fi-section-header-content">
                    <h3 class="fi-section-title text-base font-semibold leading-6 text-gray-950 dark:text-white">
                        Visualisasi Data
                    </h3>
                    <p class="fi-section-description mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Grafik pencapaian indikator mutu dalam periode yang dipilih.
                    </p>
                </div>
            </div>

            <div class="fi-section-content p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Target vs Achievement Card --}}
                    <div class="fi-stats-card rounded-xl bg-primary-50 p-6 dark:bg-primary-950/20">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary-500 text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Target</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->getTargetValue(), 1) }}%</p>
                            </div>
                        </div>
                    </div>

                    {{-- Achievement Card --}}
                    <div class="fi-stats-card rounded-xl bg-success-50 p-6 dark:bg-success-950/20">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-success-500 text-white">
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pencapaian</p>
                                <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($this->summaryStats['average_percentage'], 1) }}%</p>
                            </div>
                        </div>
                    </div>

                    {{-- Status Card --}}
                    <div class="fi-stats-card rounded-xl bg-{{ $this->summaryStats['average_percentage'] >= $this->getTargetValue() ? 'success' : 'danger' }}-50 p-6 dark:bg-{{ $this->summaryStats['average_percentage'] >= $this->getTargetValue() ? 'success' : 'danger' }}-950/20">
                        <div class="flex items-center">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-{{ $this->summaryStats['average_percentage'] >= $this->getTargetValue() ? 'success' : 'danger' }}-500 text-white">
                                @if($this->summaryStats['average_percentage'] >= $this->getTargetValue())
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                @else
                                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                @endif
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Status</p>
                                <p class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ $this->summaryStats['average_percentage'] >= $this->getTargetValue() ? 'Target Tercapai' : 'Belum Tercapai' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
</x-filament-panels::page>