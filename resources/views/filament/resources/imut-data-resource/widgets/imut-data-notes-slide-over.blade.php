<div class="space-y-4">
    <div class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                Analisis & Rekomendasi Indikator Mutu
            </h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                Menyajikan hasil analisis capaian indikator mutu yang dilengkapi dengan insight serta
                rekomendasi strategis berdasarkan periode triwulan, semester, dan tahunan guna
                mendukung evaluasi dan peningkatan kinerja secara berkelanjutan.
            </p>
        </div>
    </div>

    @if(isset($record) && $record->id)
    @livewire(\App\Filament\Resources\ImutDataResource\Widgets\ImutDataNotesReport::class, ['imutDataId' => $record->id], key('imut-data-notes-'.$record->id))
    @else
    <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600 dark:border-slate-700 dark:bg-slate-900/50 dark:text-gray-300">
        Data indikator mutu tidak tersedia untuk dianalisis.
    </div>
    @endif
</div>