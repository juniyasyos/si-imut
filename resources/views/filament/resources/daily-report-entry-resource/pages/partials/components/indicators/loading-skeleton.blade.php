{{-- Loading Skeleton for Indicators --}}
<div class="flex animate-pulse pb-2 mb-2 border-b border-slate-200 dark:border-slate-700 flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
    <div class="w-full space-y-3">
        {{-- Title --}}
        <div class="flex items-center gap-2">
            <div class="h-5 w-5 rounded-md bg-slate-200 dark:bg-slate-700"></div>
            <div class="h-5 w-48 rounded bg-slate-200 dark:bg-slate-700"></div>
        </div>

        {{-- Unit Kerja --}}
        <div class="h-4 w-72 max-w-full rounded bg-slate-200 dark:bg-slate-700"></div>

        {{-- Info --}}
        <div class="flex items-center gap-1">
            <div class="h-4 w-4 rounded-full bg-slate-200 dark:bg-slate-700"></div>
            <div class="h-3 w-28 rounded bg-slate-200 dark:bg-slate-700"></div>
        </div>
    </div>
</div>

{{-- Skeleton Cards --}}
@for ($i = 0; $i < 6; $i++)
    <div class="w-full animate-pulse rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-700 dark:bg-slate-800">
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
