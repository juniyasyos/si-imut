<x-filament-panels::page>
    <div class="space-y-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                    <x-heroicon-o-document-text class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Form Builder</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Buat form dinamis seperti Google Form</p>
                </div>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                <div class="flex gap-3">
                    <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" />
                    <div class="text-sm text-blue-800 dark:text-blue-300">
                        <p class="font-semibold mb-1">Cara Menggunakan Form Builder:</p>
                        <ul class="list-disc list-inside space-y-1 ml-2">
                            <li>Isi judul dan deskripsi form</li>
                            <li>Tambah pertanyaan dengan klik tombol "+ Tambah Pertanyaan"</li>
                            <li>Pilih tipe input sesuai kebutuhan (Text, Number, Date, Boolean, dll)</li>
                            <li>Untuk Select/Radio/Checkbox, tambahkan opsi pilihan</li>
                            <li>Drag untuk mengatur urutan pertanyaan</li>
                            <li>Klik "Simpan Form" untuk menyimpan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <form wire:submit.prevent="save" class="space-y-6">
            {{ $this->form }}
        </form>
    </div>
</x-filament-panels::page>