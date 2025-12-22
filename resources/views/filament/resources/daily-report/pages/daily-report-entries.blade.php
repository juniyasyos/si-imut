<x-filament-panels::page>
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold">{{ $formTemplate->title ?? $formTemplate->imutdata->title }}</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                    Periode: {{ $periodName }}
                </p>
            </div>
        </div>

        {{ $this->table }}
    </div>
</x-filament-panels::page>