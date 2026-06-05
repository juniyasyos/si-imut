<!-- Empty State -->
<div x-show="filteredIndicators.length === 0" class="text-center py-16">
    <div class="flex flex-col items-center justify-center space-y-4">
        <div class="relative">
            <div class="absolute inset-0 bg-primary-100 dark:bg-primary-900/20 rounded-full blur-2xl opacity-50"></div>
            <div class="relative w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-800 rounded-2xl flex items-center justify-center shadow-lg">
                @svg("heroicon-o-clipboard-document-list", "w-10 h-10 text-gray-400 dark:text-gray-500")
            </div>
        </div>
        <div class="space-y-2">
            <p class="text-base font-bold text-gray-700 dark:text-gray-300" x-show="!searchQuery && statusFilter === 'all'">Belum Ada Indikator</p>
            <p class="text-base font-bold text-gray-700 dark:text-gray-300" x-show="searchQuery || statusFilter !== 'all'">Tidak Ada Hasil</p>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm" x-show="!searchQuery && statusFilter === 'all'">
                Belum ada indikator mutu yang dikonfigurasi untuk unit kerja Anda.
            </p>
            <p class="text-sm text-gray-500 dark:text-gray-400 max-w-sm" x-show="searchQuery || statusFilter !== 'all'">
                Coba hubungi admin untuk memastikan indikator yang Anda cari sudah dikonfigurasi, atau periksa kembali kata kunci dan filter yang digunakan.
            </p>
        </div>
    </div>
</div>