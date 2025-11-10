<div class="p-6 bg-gray-50 dark:bg-slate-800/80 rounded-lg border border-gray-200 dark:border-gray-600 mt-4">
    <!-- Laporan Selector -->
    <div class="mb-6 flex items-center justify-between">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">📊 Ringkasan Statistik</h3>

        <div class="flex items-center gap-2">
            <label class="text-sm text-gray-700 dark:text-gray-300">Pilih Laporan:</label>
            <select
                wire:model.live="selectedLaporanId"
                class="text-sm rounded-lg border-gray-300 dark:border-gray-600 dark:bg-slate-700 dark:text-white focus:border-blue-500 focus:ring-blue-500"
            >
                @foreach ($stats['available_laporans'] as $laporan)
                    <option value="{{ $laporan['id'] }}">{{ $laporan['name'] }} - {{ $laporan['period'] }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="mb-6">
        <div class="flex items-center gap-2 mb-4">
            <span class="text-sm text-gray-600 dark:text-gray-400">
                📅 Menampilkan data: <strong class="text-blue-600 dark:text-blue-400">{{ $stats['laporan_used'] ?? 'N/A' }}</strong>
                ({{ $stats['laporan_period'] ?? 'N/A' }})
            </span>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div
                class="bg-[#DDE6FB] dark:bg-slate-700/40 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Kategori</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_categories'] }}</div>
            </div>

            <div
                class="bg-[#DDE6FB] dark:bg-slate-700/40 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-700">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total Indikator IMUT</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total_imut_indicators'] }}</div>
            </div>

            <div
                class="bg-[#DDE6FB] dark:bg-slate-700/40 rounded-lg p-4 shadow-sm border border-green-200 dark:border-green-700">
                <div class="text-sm text-green-600 dark:text-green-400 mb-1">✓ Memenuhi Standar</div>
                <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['imut_meeting_standard'] }}
                </div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ $stats['overall_achievement'] }}% dari total
                </div>
            </div>

            <div
                class="bg-[#DDE6FB] dark:bg-slate-700/40 rounded-lg p-4 shadow-sm border border-red-200 dark:border-red-700">
                <div class="text-sm text-red-600 dark:text-red-400 mb-1">✗ Di Bawah Standar</div>
                <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $stats['imut_below_standard'] }}</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                    {{ round(100 - $stats['overall_achievement'], 2) }}% dari total
                </div>
            </div>
        </div>
    </div>

    <!-- Category Details -->
    <div>
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">📈 Detail Per Kategori</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-700 dark:text-gray-300 uppercase bg-gray-300 dark:bg-slate-500">
                    <tr>
                        <th class="px-4 py-3 rounded-tl-lg">Kategori</th>
                        <th class="px-4 py-3 text-center">Total IMUT</th>
                        <th class="px-4 py-3 text-center">Memenuhi Standar</th>
                        <th class="px-4 py-3 text-center">Di Bawah Standar</th>
                        <th class="px-4 py-3 text-center rounded-tr-lg">Capaian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($stats['categories_detail'] as $category)
                        <tr
                            class="bg-slate-100 dark:bg-slate-700/40 border-b dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-800">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-white">
                                {{ $category['category_name'] }}
                            </td>
                            <td class="px-4 py-3 text-center text-gray-700 dark:text-gray-300">
                                {{ $category['total_imut'] }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                    {{ $category['imut_meeting_standard'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                    {{ $category['imut_below_standard'] }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-24 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $category['achievement_percentage'] >= 80 ? 'bg-green-600' : ($category['achievement_percentage'] >= 60 ? 'bg-yellow-600' : 'bg-red-600') }}"
                                            style="width: {{ $category['achievement_percentage'] }}%"></div>
                                    </div>
                                    <span
                                        class="text-xs font-semibold {{ $category['achievement_percentage'] >= 80 ? 'text-green-600' : ($category['achievement_percentage'] >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ $category['achievement_percentage'] }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Legend/Notes -->
    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <p class="text-xs text-gray-500 dark:text-gray-400">
            <strong>Catatan:</strong> Perhitungan dilakukan dengan menjumlahkan seluruh numerator dan denominator dari
            semua unit kerja untuk setiap indikator,
            kemudian membandingkan hasil (numerator/denominator × 100%) dengan standar yang ditetapkan.
        </p>
    </div>
</div>
