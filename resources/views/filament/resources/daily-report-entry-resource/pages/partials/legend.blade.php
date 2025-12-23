<!-- Legend -->
<div class="bg-white dark:bg-slate-800/80 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
    <div class="flex items-center justify-center gap-8 text-sm flex-wrap">
        {{-- Sudah diisi --}}
        <div class="flex items-center gap-2.5">
            <div class="flex items-center justify-center w-10 h-10 bg-green-50 dark:bg-green-900/20 rounded-lg">
                @svg("heroicon-m-check-circle", "w-6 h-6 text-green-600 dark:text-green-400")
            </div>
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-gray-900 dark:text-white">Sudah Diisi</span>
                <span class="text-[10px] text-gray-500 dark:text-gray-400">Klik untuk detail</span>
            </div>
        </div>

        {{-- Belum diisi --}}
        <div class="flex items-center gap-2.5">
            <div class="flex flex-col items-center justify-center w-12 h-12 bg-orange-500 rounded-lg">
                @svg("heroicon-o-plus-circle", "w-5 h-5 text-white mb-0.5")
                <span class="text-[9px] font-bold text-white">0%</span>
            </div>
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-gray-900 dark:text-white">Belum Diisi</span>
                <span class="text-[10px] text-gray-500 dark:text-gray-400">Dapat input (&le;7 hari)</span>
            </div>
        </div>

        {{-- Terlambat --}}
        <div class="flex items-center gap-2.5">
            <div class="flex items-center justify-center w-10 h-10 bg-red-50 dark:bg-red-900/20 rounded-lg">
                @svg("heroicon-o-lock-closed", "w-6 h-6 text-red-500 dark:text-red-400")
            </div>
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-gray-900 dark:text-white">Terkunci</span>
                <span class="text-[10px] text-gray-500 dark:text-gray-400">Lewat 7 hari</span>
            </div>
        </div>

        {{-- Belum tiba --}}
        <div class="flex items-center gap-2.5">
            <div class="flex items-center justify-center w-10 h-10 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <span class="text-gray-300 dark:text-gray-600 text-lg font-light">-</span>
            </div>
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-gray-900 dark:text-white">Belum Tiba</span>
                <span class="text-[10px] text-gray-500 dark:text-gray-400">Tanggal masa depan</span>
            </div>
        </div>

        {{-- Hari ini --}}
        <div class="flex items-center gap-2.5 pl-4 ml-4 border-l-2 border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-center w-10 h-10 bg-primary-50 dark:bg-primary-900/20 rounded-lg ring-2 ring-primary-300 dark:ring-primary-700">
                @svg("heroicon-m-calendar", "w-5 h-5 text-primary-600 dark:text-primary-400")
            </div>
            <div class="flex flex-col">
                <span class="text-xs font-semibold text-primary-700 dark:text-primary-300">Hari Ini</span>
                <span class="text-[10px] text-gray-500 dark:text-gray-400">Kolom dengan ring</span>
            </div>
        </div>
    </div>
</div>