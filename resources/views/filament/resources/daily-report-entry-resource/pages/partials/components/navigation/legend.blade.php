<!-- Date Legend Trigger -->
<div class="mb-4 mt-4">
    <button type="button" x-data @click="$dispatch('open-date-legend')"
        class="inline-flex w-full items-center justify-start text-left transition hover:bg-slate-100 dark:hover:bg-slate-800">

        <span class="inline-flex items-center gap-1 text-xs font-medium text-primary-600 dark:text-primary-400">
            @svg("heroicon-m-information-circle", "h-4 w-4")
            Lihat keterangan
        </span>
    </button>
</div>

<!-- Date Legend Modal -->
<div x-data="{ open: false }" x-on:open-date-legend.window="open = true" x-show="open" x-cloak
    class="fixed inset-0 z-50 flex items-center justify-center px-4">
    <!-- Backdrop -->
    <div x-show="open" x-transition.opacity @click="open = false"
        class="absolute inset-0 bg-slate-950/50 backdrop-blur-sm"></div>

    <!-- Modal -->
    <div x-show="open" x-transition
        class="relative z-10 w-full max-w-lg rounded-2xl border border-slate-200 bg-white shadow-2xl dark:border-slate-700 dark:bg-slate-900">
        <!-- Header -->
        <div class="flex items-start justify-between gap-4 border-b border-slate-200 px-5 py-4 dark:border-slate-700">
            <div>
                <h3 class="flex items-center gap-2 text-base font-semibold text-slate-900 dark:text-white">
                    @svg("heroicon-m-calendar-days", "h-5 w-5 text-primary-600")
                    Keterangan Status Tanggal
                </h3>
                <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">
                    Panduan membaca warna dan indikator pada kalender pengisian laporan.
                </p>
            </div>

            <button type="button" @click="open = false"
                class="rounded-lg p-1.5 text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 dark:hover:bg-slate-800 dark:hover:text-slate-200">
                @svg("heroicon-m-x-mark", "h-5 w-5")
            </button>
        </div>

        <!-- Content -->
        <div class="space-y-4 px-5 py-4 text-sm">
            <div class="flex gap-3 rounded-xl bg-emerald-50 p-3 dark:bg-emerald-950/30">
                <span class="mt-1 h-3 w-3 shrink-0 rounded-full bg-emerald-500 shadow-sm"></span>
                <div>
                    <p class="font-semibold text-emerald-700 dark:text-emerald-400">Sudah diisi</p>
                    <p class="text-xs text-emerald-700/80 dark:text-emerald-300/80">
                        Tanggal ini sudah memiliki data laporan. Anda bisa membuka detail untuk mengecek atau
                        meninjau isian.
                    </p>
                </div>
            </div>

            <div class="flex gap-3 rounded-xl bg-amber-50 p-3 dark:bg-amber-950/30">
                <span class="mt-1 h-3 w-3 shrink-0 rounded-full border-2 border-amber-400"></span>
                <div>
                    <p class="font-semibold text-amber-700 dark:text-amber-400">Belum diisi</p>
                    <p class="text-xs text-amber-700/80 dark:text-amber-300/80">
                        Tanggal ini masih belum memiliki laporan dan masih bisa dilakukan pengisian jika periode
                        belum terkunci.
                    </p>
                </div>
            </div>

            <div class="flex gap-3 rounded-xl bg-red-50 p-3 dark:bg-red-950/30">
                <span class="mt-1 h-3 w-3 shrink-0 rounded border border-red-400 bg-red-100 dark:bg-red-900/30"></span>
                <div>
                    <p class="font-semibold text-red-700 dark:text-red-400">Terkunci</p>
                    <p class="text-xs text-red-700/80 dark:text-red-300/80">
                        Tanggal ini sudah melewati batas waktu pengisian, sehingga tidak dapat diubah melalui input
                        reguler.
                    </p>
                </div>
            </div>

            <div class="flex gap-3 rounded-xl bg-slate-50 p-3 dark:bg-slate-800/70">
                <span class="mt-1 h-3 w-3 shrink-0 rounded border border-slate-400 opacity-60"></span>
                <div>
                    <p class="font-semibold text-slate-700 dark:text-slate-300">Masa depan</p>
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Tanggal ini belum dapat dipilih karena berada setelah tanggal hari ini.
                    </p>
                </div>
            </div>

            <div class="flex gap-3 rounded-xl bg-primary-50 p-3 dark:bg-primary-950/30">
                <span
                    class="mt-1 h-3 w-3 shrink-0 rounded-full bg-primary-500 ring-2 ring-primary-300 dark:ring-primary-700"></span>
                <div>
                    <p class="font-semibold text-primary-700 dark:text-primary-400">Hari ini</p>
                    <p class="text-xs text-primary-700/80 dark:text-primary-300/80">
                        Menandai tanggal berjalan agar lebih mudah ditemukan saat melakukan pengisian harian.
                    </p>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end border-t border-slate-200 px-5 py-4 dark:border-slate-700">
            <button type="button" @click="open = false"
                class="rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-primary-700">
                Mengerti
            </button>
        </div>
    </div>
</div>