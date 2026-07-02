<div class="p-5 bg-gray-50 dark:bg-slate-800/80 rounded-xl border border-gray-200 dark:border-gray-600/50 mt-4">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-5">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
            Ringkasan Statistik
        </h3>
        <span class="text-xs text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-slate-700 px-3 py-1 rounded-full">
            {{ $stats['laporan_period'] ?? 'N/A' }}
        </span>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
        {{-- Overall Achievement --}}
        <div class="relative overflow-hidden bg-gradient-to-br from-indigo-50 to-blue-50 dark:from-indigo-900/30 dark:to-blue-900/20 rounded-xl p-4 border border-indigo-100 dark:border-indigo-800/40">
            <div class="text-xs font-medium text-indigo-600 dark:text-indigo-400 mb-1">Capaian Overall</div>
            <div class="text-2xl font-bold {{ $stats['overall_achievement'] >= 80 ? 'text-green-600 dark:text-green-400' : ($stats['overall_achievement'] >= 60 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400') }}">
                {{ $stats['overall_achievement'] }}%
            </div>
            <div class="mt-2 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
                <div class="h-1.5 rounded-full transition-all duration-500 {{ $stats['overall_achievement'] >= 80 ? 'bg-green-500' : ($stats['overall_achievement'] >= 60 ? 'bg-amber-500' : 'bg-red-500') }}"
                     style="width: {{ min($stats['overall_achievement'], 100) }}%"></div>
            </div>
        </div>

        {{-- Total Indikator --}}
        <div class="bg-white dark:bg-slate-700/40 rounded-xl p-4 border border-gray-200 dark:border-gray-700/50">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Total Indikator</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_imut_indicators'] }}</div>
            <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $stats['total_categories'] }} kategori</div>
        </div>

        {{-- Memenuhi Standar --}}
        <div class="bg-white dark:bg-slate-700/40 rounded-xl p-4 border border-green-200/60 dark:border-green-800/30">
            <div class="text-xs font-medium text-green-600 dark:text-green-400 mb-1">✓ Memenuhi Standar</div>
            <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['imut_meeting_standard'] }}</div>
            <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $stats['overall_achievement'] }}% dari total</div>
        </div>

        {{-- Di Bawah Standar --}}
        <div class="bg-white dark:bg-slate-700/40 rounded-xl p-4 border border-red-200/60 dark:border-red-800/30">
            <div class="text-xs font-medium text-red-600 dark:text-red-400 mb-1">✗ Di Bawah Standar</div>
            <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['imut_below_standard'] }}</div>
            <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ round(100 - $stats['overall_achievement'], 1) }}% dari total</div>
        </div>
    </div>

    {{-- Category Progress Bars --}}
    <div>
        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Detail Per Kategori</h4>
        <div class="space-y-2.5">
            @foreach ($stats['categories_detail'] as $category)
                @php
                    $pct = $category['achievement_percentage'];
                    $colorClass = $pct >= 80 ? 'bg-green-500' : ($pct >= 60 ? 'bg-amber-500' : 'bg-red-500');
                    $textClass = $pct >= 80 ? 'text-green-600 dark:text-green-400' : ($pct >= 60 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400');
                    $badgeClass = $pct >= 80 ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : ($pct >= 60 ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300');
                    $statusIcon = $pct >= 80 ? '✓' : ($pct >= 60 ? '⚠' : '✗');
                @endphp
                <div class="flex items-center gap-3 group">
                    <div class="w-28 sm:w-36 text-xs font-medium text-gray-700 dark:text-gray-300 truncate" title="{{ $category['category_name'] }}">
                        {{ $category['category_name'] }}
                    </div>
                    <div class="flex-1 bg-gray-100 dark:bg-gray-700/60 rounded-full h-2.5 relative overflow-hidden">
                        <div class="h-2.5 rounded-full transition-all duration-500 {{ $colorClass }}"
                             style="width: {{ min($pct, 100) }}%"></div>
                    </div>
                    <div class="flex items-center gap-1.5 min-w-[80px] justify-end">
                        <span class="text-xs font-bold {{ $textClass }}">{{ $pct }}%</span>
                        <span class="inline-flex items-center justify-center w-5 h-5 rounded-full text-[10px] font-bold {{ $badgeClass }}">
                            {{ $statusIcon }}
                        </span>
                    </div>
                    <div class="hidden sm:flex items-center gap-2 text-[11px] text-gray-400 dark:text-gray-500 min-w-[90px]">
                        <span>{{ $category['imut_meeting_standard'] }}/{{ $category['total_imut'] }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Collapsible Note --}}
    <details class="mt-4 pt-3 border-t border-gray-200 dark:border-gray-700/50">
        <summary class="text-xs text-gray-400 dark:text-gray-500 cursor-pointer hover:text-gray-600 dark:hover:text-gray-300 transition-colors select-none">
            ℹ️ Catatan metodologi perhitungan
        </summary>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-2 leading-relaxed">
            Perhitungan dilakukan dengan menjumlahkan seluruh numerator dan denominator dari
            semua unit kerja untuk setiap indikator,
            kemudian membandingkan hasil (numerator/denominator × 100%) dengan standar yang ditetapkan.
        </p>
    </details>
</div>