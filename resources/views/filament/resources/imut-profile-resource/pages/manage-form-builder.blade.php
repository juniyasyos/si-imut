<x-filament-panels::page>
    <div class="space-y-6">
        <div class="fi-section-header">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-full bg-primary-100 dark:bg-primary-900 flex items-center justify-center">
                    @svg("heroicon-o-document-text", "w-6 h-6 text-primary-600 dark:text-primary-400")
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Form Builder</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Buat form dinamis seperti Google Form</p>
                </div>
            </div>

            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
                <div class="flex gap-3">
                    @svg("heroicon-o-information-circle", "w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5")
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

        <!-- Auto-save status indicator -->
        <div class="fixed bottom-4 right-4 z-50">
            <div id="auto-save-indicator" class="bg-green-100 border border-green-300 text-green-800 px-3 py-2 rounded-lg shadow-lg text-sm font-medium hidden">
                <svg class="w-4 h-4 inline-block mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                </svg>
                Auto-saved
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let autoSaveEnabled = true;

            // Auto-save every 20 seconds
            setInterval(function() {
                if (autoSaveEnabled && window.Livewire) {
                    try {
                        // Find the Livewire component and call autoSave
                        const component = Livewire.find('{{ $this->getId() }}');
                        if (component) {
                            component.call('autoSave');
                            showAutoSaveIndicator();
                        }
                    } catch (e) {
                        console.log('Auto-save skipped:', e.message);
                    }
                }
            }, 20000); // 20 seconds

            function showAutoSaveIndicator() {
                const indicator = document.getElementById('auto-save-indicator');
                if (indicator) {
                    indicator.classList.remove('hidden');
                    setTimeout(() => {
                        indicator.classList.add('hidden');
                    }, 2000); // Hide after 2 seconds
                }
            }

            // Disable auto-save when user is actively typing
            let typingTimer;
            document.addEventListener('input', function() {
                autoSaveEnabled = false;
                clearTimeout(typingTimer);
                typingTimer = setTimeout(() => {
                    autoSaveEnabled = true;
                }, 3000); // Re-enable after 3 seconds of no typing
            });

            console.log('Auto-save initialized - saving every 20 seconds');
        });
    </script>
</x-filament-panels::page>