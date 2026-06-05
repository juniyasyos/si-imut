<!-- Filters and Search Section -->
<div x-show="$wire.currentView === 'input'" class="border-t border-slate-200 dark:border-slate-700 pt-4">
    <div class="flex flex-col lg:flex-row gap-4">
        <!-- Search -->
        <div class="flex-1">
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    @svg("heroicon-m-magnifying-glass", "w-5 h-5 text-gray-400")
                </div>
                <input
                    wire:model.live="searchQuery"
                    type="text"
                    placeholder="Cari indikator..."
                    class="block w-full pl-10 pr-3 py-2 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-500 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
        </div>

        <!-- Quick Filters -->
        <div class="flex flex-wrap gap-2">
            <button
                wire:click="$set('statusFilter', 'pending')"
                :class="$wire.statusFilter === 'pending' ? 'bg-orange-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300'"
                class="px-3 py-1.5 text-xs font-medium rounded-full transition">
                Belum Diisi
            </button>
            <button
                wire:click="$set('statusFilter', 'done')"
                :class="$wire.statusFilter === 'done' ? 'bg-green-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-gray-700 dark:text-gray-300'"
                class="px-3 py-1.5 text-xs font-medium rounded-full transition">
                Sudah Diisi
            </button>
        </div>
    </div>
</div>