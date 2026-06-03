<!-- Date Navigation Sidebar -->
<div class="bg-white dark:bg-slate-800 rounded-xl shadow-sm border border-slate-200 dark:border-slate-700 p-4 h-full"
    x-data="{}">


    @include('filament.resources.daily-report-entry-resource.pages.partials.components.navigation.month-navigation')
    
    @include('filament.resources.daily-report-entry-resource.pages.partials.components.navigation.legend')

    <!-- Date List -->
    <!-- Skeleton Loading -->
    <div wire:loading class="w-full space-y-2">
        @for ($i = 0; $i < 6; $i++)
            <div
                class="w-full animate-pulse rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
                <div class="flex w-full items-start gap-3">
                    <div class="h-10 w-10 shrink-0 rounded-lg bg-slate-200 dark:bg-slate-700"></div>

                    <div class="min-w-0 flex-1 space-y-3">
                        <div class="h-4 w-2/3 rounded bg-slate-200 dark:bg-slate-700"></div>

                        <div class="space-y-2">
                            <div class="h-3 w-full rounded bg-slate-200 dark:bg-slate-700"></div>
                            <div class="h-3 w-4/5 rounded bg-slate-200 dark:bg-slate-700"></div>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <div class="h-3 w-24 rounded bg-slate-200 dark:bg-slate-700"></div>
                            <div class="h-6 w-20 rounded-full bg-slate-200 dark:bg-slate-700"></div>
                        </div>
                    </div>
                </div>
            </div>
        @endfor
    </div>

    <!-- Actual Date List -->
    <div wire:loading.remove
        class="flex flex-row lg:flex-col flex-nowrap overflow-x-auto lg:overflow-x-hidden overflow-y-hidden lg:overflow-y-auto space-x-2 lg:space-x-0 lg:space-y-1 max-h-none lg:max-h-[600px]">
        @foreach($daysInMonth as $day)
            @php
                $date = \Carbon\Carbon::createFromFormat('Y-m', $selectedMonth ?: now()->format('Y-m'))->day($day);
                $dateString = $date->format('Y-m-d');
                $dayName = $date->locale('id')->dayName;
                $isToday = $date->isToday();
                $isWeekend = in_array($date->dayOfWeek, [0, 6]);
                $isSelected = $selectedDate === $dateString;

                // Check if any indicator has data for this date
                $hasAnyData = false;
                foreach ($indicators as $indicator) {
                    $cellData = $matrixData[$indicator['id']][$day] ?? null;
                    if ($cellData && ($cellData['has_data'] ?? false)) {
                        $hasAnyData = true;
                        break;
                    }
                }

                // Locked = past date beyond the allowed input window
                $backDays = \App\Services\DailyReport\CachedSettingsService::getBackDataEntryDays();
                $isLocked = !$date->isFuture() && !$hasAnyData && $date->lt(now()->startOfDay()->subDays($backDays));
            @endphp

            <button wire:click="selectDate('{{ $dateString }}')" @if($date->isFuture()) disabled @endif
                class="group flex min-w-[150px] items-center gap-3 rounded-xl border px-3.5 py-3 text-left transition-all duration-200 sm:min-w-[150px] lg:w-full lg:min-w-0
                                        {{ $isSelected
            ? 'border-primary-300 bg-primary-50 text-primary-950 shadow-sm shadow-primary-100 dark:border-primary-700 dark:bg-primary-950/40 dark:text-primary-100 dark:shadow-primary-950/20'
            : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50 hover:shadow-sm dark:border-slate-700 dark:bg-slate-800/70 dark:text-slate-300 dark:hover:border-slate-600 dark:hover:bg-slate-800'
                                        }}
                                        {{ $isWeekend ? 'border-red-200 bg-red-50/60 dark:border-red-900/40 dark:bg-red-950/20' : '' }}
                                        {{ $date->isFuture() ? 'cursor-not-allowed opacity-50' : 'cursor-pointer hover:-translate-y-0.5' }}">
                <!-- Status Indicator -->
                <div class="flex shrink-0 items-center justify-center">
                    @if($date->isFuture())
                        <div
                            class="h-3 w-3 rounded-full border border-slate-400 bg-slate-100 dark:border-slate-600 dark:bg-slate-800">
                        </div>
                    @elseif($hasAnyData && $isToday)
                        <div class="h-3 w-3 rounded-full bg-emerald-500 ring-4 ring-emerald-100 dark:ring-emerald-900/40"></div>
                    @elseif($hasAnyData)
                        <div
                            class="h-3 w-3 rounded-full bg-emerald-500 ring-2 ring-transparent transition-all group-hover:ring-emerald-100 dark:group-hover:ring-emerald-900/40">
                        </div>
                    @elseif($isToday)
                        <div class="h-3 w-3 rounded-full bg-primary-500 ring-4 ring-primary-100 dark:ring-primary-900/40"></div>
                    @elseif($isLocked)
                        <div class="h-3 w-3 rounded-full border border-red-400 bg-red-100 dark:bg-red-950/50"></div>
                    @else
                        <div
                            class="h-3 w-3 rounded-full border-2 border-amber-400 bg-amber-50 ring-2 ring-transparent transition-all group-hover:ring-amber-100 dark:bg-amber-950/30 dark:group-hover:ring-amber-900/40">
                        </div>
                    @endif
                </div>

                <!-- Date Info -->
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="truncate text-sm font-semibold transition-colors
                                            {{ $date->isFuture()
            ? 'text-slate-400 dark:text-slate-500'
            : ($isToday
                ? 'text-primary-700 dark:text-primary-300'
                : 'text-slate-800 group-hover:text-slate-950 dark:text-slate-200 dark:group-hover:text-white')
                                            }}">
                            {{ $day }} {{ $date->format('M') }}
                        </span>

                        @if($isToday)
                            <span
                                class="rounded-full bg-primary-600 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white shadow-sm">
                                Today
                            </span>
                        @endif

                        @if($isSelected && !$isToday)
                            <span
                                class="rounded-full bg-emerald-600 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white shadow-sm">
                                Selected
                            </span>
                        @endif
                    </div>

                    <div
                        class="mt-0.5 truncate text-xs text-slate-500 transition-colors group-hover:text-slate-600 dark:text-slate-400 dark:group-hover:text-slate-300">
                        {{ $dayName }}
                    </div>
                </div>

                <!-- Hover Indicator -->
                <div
                    class="shrink-0 text-slate-400 opacity-0 transition-all duration-200 group-hover:translate-x-0.5 group-hover:opacity-100 dark:text-slate-500">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </div>
            </button>
        @endforeach
    </div>
</div>