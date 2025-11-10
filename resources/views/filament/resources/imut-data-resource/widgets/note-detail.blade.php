<div class="space-y-4">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Nama Catatan</label>
            <p class="text-base text-gray-900 dark:text-white mt-1">{{ $note->note_name }}</p>
        </div>

        <div>
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Prioritas</label>
            <p class="text-base mt-1">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    {{ $note->priority === 'high' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                    {{ $note->priority === 'medium' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}
                    {{ $note->priority === 'low' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                ">
                    {{ $note->priority === 'high' ? 'Tinggi' : ($note->priority === 'medium' ? 'Sedang' : 'Rendah') }}
                </span>
            </p>
        </div>
    </div>

    @if($note->period_year)
    <div>
        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Periode</label>
        <p class="text-base text-gray-900 dark:text-white mt-1">
            {{ $note->period_display }}
        </p>
    </div>
    @endif

    @if(!empty($note->related_laporan_ids))
    <div>
        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Laporan Terkait</label>
        <p class="text-base text-gray-900 dark:text-white mt-1">{{ $note->laporan_names }}</p>
    </div>
    @endif

    @if($note->recommendation)
    <div>
        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Rekomendasi</label>
        <div class="mt-1 p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
            <p class="text-base text-gray-900 dark:text-white whitespace-pre-wrap">{{ $note->recommendation }}</p>
        </div>
    </div>
    @endif

    @if($note->analysis)
    <div>
        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Analisis</label>
        <div class="mt-1 p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
            <p class="text-base text-gray-900 dark:text-white whitespace-pre-wrap">{{ $note->analysis }}</p>
        </div>
    </div>
    @endif

    @if($note->additional_notes)
    <div>
        <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Catatan Tambahan</label>
        <div class="mt-1 p-3 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <p class="text-base text-gray-900 dark:text-white whitespace-pre-wrap">{{ $note->additional_notes }}</p>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div>
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Dibuat Oleh</label>
            <p class="text-base text-gray-900 dark:text-white mt-1">{{ $note->creator->name ?? '-' }}</p>
        </div>

        <div>
            <label class="text-sm font-semibold text-gray-700 dark:text-gray-300">Tanggal Dibuat</label>
            <p class="text-base text-gray-900 dark:text-white mt-1">{{ $note->created_at->format('d/m/Y H:i') }}</p>
        </div>
    </div>
</div>
