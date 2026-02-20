@php
use Illuminate\Support\Facades\Gate;

$laporan = $this->getLaporan();
$user = auth()->user();
$unitSummaries = $this->getUnitSummaries();

$totalUnits = collect($unitSummaries)->count();
$criticalUnits = collect($unitSummaries)->where('compliance_rate', '<', 60)->count();
    $avgCompletion = $totalUnits > 0
    ? round(collect($unitSummaries)->avg('completion_rate'))
    : 0;
    @endphp

    <x-filament-widgets::widget>
        <x-filament::section class="pt-8 pb-6">

            @if (!$laporan)
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <div class="mb-6 rounded-2xl bg-gray-100 p-5 dark:bg-slate-800/80">
                    @svg("heroicon-o-document-text", "h-12 w-12 text-gray-400 dark:text-gray-600")
                </div>
                <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">
                    Belum Ada Laporan
                </h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Saat ini belum terdapat laporan indikator mutu yang aktif.
                </p>
            </div>
            @else

            <div class="space-y-10">

                {{-- ================= EXECUTIVE SUMMARY ================= --}}
                <div class="rounded-2xl bg-gradient-to-r from-slate-900 to-slate-800 p-6 text-white shadow-lg">
                    <h2 class="text-lg font-semibold tracking-tight">
                        Ringkasan Minggu Ini
                    </h2>

                    <div class="mt-4 grid grid-cols-2 gap-6 lg:grid-cols-4 text-sm">
                        <div>
                            <div class="text-slate-400">Total Unit</div>
                            <div class="text-2xl font-bold">{{ $totalUnits }}</div>
                        </div>

                        <div>
                            <div class="text-slate-400">Rata Completion</div>
                            <div class="text-2xl font-bold">{{ $avgCompletion }}%</div>
                        </div>

                        <div>
                            <div class="text-slate-400">Unit Kritis</div>
                            <div class="text-2xl font-bold text-red-400">
                                {{ $criticalUnits }}
                            </div>
                        </div>

                        <div>
                            <div class="text-slate-400">Periode</div>
                            <div class="text-sm">
                                {{ $laporan->assessment_period_start->translatedFormat('d M') }}
                                -
                                {{ $laporan->assessment_period_end->translatedFormat('d M Y') }}
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================= UNIT CARDS ================= --}}
                <div class="space-y-6">

                    @forelse($unitSummaries as $s)

                    @php
                    $status =
                    $s['compliance_rate'] >= 90 ? 'Excellent' :
                    ($s['compliance_rate'] >= 75 ? 'Good' :
                    ($s['compliance_rate'] >= 60 ? 'Warning' : 'Critical'));

                    $statusColor =
                    $status === 'Excellent' ? 'bg-green-100 text-green-700 dark:bg-green-900/40' :
                    ($status === 'Good' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/40' :
                    ($status === 'Warning' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40'
                    : 'bg-red-100 text-red-700 dark:bg-red-900/40'));

                    $borderColor =
                    $status === 'Critical'
                    ? 'border-red-400'
                    : 'border-gray-200 dark:border-gray-700';
                    @endphp

                    <div class="rounded-2xl border {{ $borderColor }} bg-white/80 dark:bg-slate-800/70 backdrop-blur-sm p-6 shadow-sm hover:shadow-md transition">

                        {{-- Header --}}
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $s['unit_name'] }}
                            </h3>

                            <span class="px-3 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">
                                {{ $status }}
                            </span>
                        </div>

                        <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-8">

                            {{-- KPI --}}
                            <div class="space-y-6">

                                {{-- Completion --}}
                                <div>
                                    <div class="text-xs uppercase tracking-wider text-gray-400">
                                        Completion Rate
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <div class="text-3xl font-bold text-gray-900 dark:text-white">
                                            {{ $s['completion_rate'] }}%
                                        </div>

                                        @if(($s['completion_delta'] ?? 0) != 0)
                                        <span class="text-xs font-semibold
                                    {{ $s['completion_delta'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $s['completion_delta'] > 0 ? '↑' : '↓' }}
                                            {{ abs($s['completion_delta']) }}%
                                        </span>
                                        @endif
                                    </div>

                                    <div class="mt-2 h-2 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                        <div class="h-full bg-blue-600 transition-all"
                                            style="width: {{ $s['completion_rate'] }}%"></div>
                                    </div>
                                </div>

                                {{-- Compliance --}}
                                <div>
                                    <div class="text-xs uppercase tracking-wider text-gray-400">
                                        Compliance Rate
                                    </div>

                                    <div class="flex items-center gap-3">
                                        <div class="text-3xl font-bold text-gray-900 dark:text-white">
                                            {{ $s['compliance_rate'] }}%
                                        </div>

                                        @if(($s['compliance_delta'] ?? 0) != 0)
                                        <span class="text-xs font-semibold
                                    {{ $s['compliance_delta'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $s['compliance_delta'] > 0 ? '↑' : '↓' }}
                                            {{ abs($s['compliance_delta']) }}%
                                        </span>
                                        @endif
                                    </div>

                                    <div class="mt-2 h-2 rounded-full bg-gray-200 dark:bg-gray-700 overflow-hidden">
                                        <div class="h-full bg-green-600 transition-all"
                                            style="width: {{ $s['compliance_rate'] }}%"></div>
                                    </div>
                                </div>

                                {{-- Missing Today --}}
                                @if(($s['missing_today'] ?? 0) > 0)
                                <div class="text-sm text-red-600 font-medium">
                                    🚨 {{ $s['missing_today'] }} indikator belum diisi hari ini
                                </div>
                                @endif

                                {{-- Last Activity --}}
                                @if(isset($s['last_activity']))
                                <div class="text-xs text-gray-400">
                                    Terakhir update: {{ $s['last_activity'] }}
                                </div>
                                @endif

                            </div>

                            {{-- Trend --}}
                            <div>
                                <div class="text-xs uppercase tracking-wider text-gray-400">
                                    Trend 7 Hari
                                </div>

                                <div class="mt-3 flex items-end h-24 gap-2">
                                    @php $max = max($s['trend']); @endphp
                                    @foreach($s['trend'] as $date => $count)
                                    <div
                                        class="flex-1 bg-gradient-to-t from-blue-600 to-blue-400 rounded-md"
                                        style="height: {{ $max > 0 ? ($count/$max)*100 : 0 }}%;">
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                        </div>

                    </div>

                    @empty
                    <div class="text-sm text-gray-500">
                        Anda tidak memiliki unit kerja dalam laporan ini.
                    </div>
                    @endforelse

                </div>

                {{-- ================= ACTIVITY TERBARU ================= --}}
                <div>
                    <div class="text-xs uppercase tracking-wider text-gray-400">
                        Activity Terbaru
                    </div>

                    <ul class="mt-3 space-y-2">

                        @foreach($s['recent_reports'] as $r)

                        @php
                        $isPerfect =
                        (($r->calculation_details['compliance_status'] ?? null) === true)
                        || (($r->total_score ?? 0) >= 100);
                        @endphp

                        <li class="flex items-center justify-between rounded-lg px-3 py-2
                                   transition hover:bg-gray-50 dark:hover:bg-slate-700/50">

                            <div class="flex flex-col text-sm">
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ $r->report_date->format('d M') }}
                                    —
                                    {{ $r->formTemplate?->title ?? 'Indikator' }}
                                </span>

                                <span class="text-xs text-gray-400">
                                    oleh {{ $r->submittedBy?->name ?? '—' }}
                                </span>
                            </div>

                            <span class="flex items-center justify-center w-8 h-8 rounded-full
                                {{ $isPerfect
                                    ? 'bg-green-100 text-green-600 dark:bg-green-900/40'
                                    : 'bg-gray-100 text-gray-500 dark:bg-gray-700' }}">

                                {{ $isPerfect ? '✓' : '–' }}
                            </span>

                        </li>

                        @endforeach

                        @if($s['recent_reports']->isEmpty())
                        <li class="text-sm text-gray-500">
                            (tidak ada aktivitas terbaru)
                        </li>
                        @endif

                    </ul>
                </div>

            </div>
            @endif

        </x-filament::section>
    </x-filament-widgets::widget>