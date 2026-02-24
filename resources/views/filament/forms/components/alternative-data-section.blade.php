<div class="space-y-4">
    <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-amber-600" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="flex-grow">
                <h3 class="font-semibold text-amber-900">Data Laporan Harian</h3>
                <p class="mt-1 text-sm text-amber-800">
                    Belum ada dokumen pendukung yang diunggah. Lihat detail laporan harian untuk melihat semua data yang telah dikumpulkan.
                </p>
            </div>
        </div>
    </div>

    <div class="flex justify-center">
        <button wire:click="openTableView('{{ $formTemplateId }}', '{{ $imutProfileId }}', '{{ $unitKerjaId }}', '{{ $period }}', '{{ $laporanId }}')"
            type="button"
            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition-colors cursor-pointer">
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path d="M12.586 4.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM12.586 4.586l2.828 2.828m0 0l.793.793a2 2 0 11-2.828-2.828l.793.793zm0 0l-2.828-2.828" />
                <path fill-rule="evenodd" d="M3 10a7 7 0 1 0 14 0 7 7 0 0 0-14 0z" clip-rule="evenodd" />
            </svg>
            Lihat Data Laporan Harian
            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
</div>