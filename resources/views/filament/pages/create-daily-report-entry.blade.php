<x-filament-panels::page>
    <div class="max-w-4xl">
        <div class="mb-6">
            <h2 class="text-xl font-semibold">{{ $formHeader->title }}</h2>
            @if($formHeader->description)
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $formHeader->description }}</p>
            @endif
        </div>

        <form wire:submit="create">
            {{ $this->form }}

            <div class="mt-6 flex gap-3">
                @foreach ($this->getFormActions() as $action)
                {{ $action }}
                @endforeach
            </div>
        </form>
    </div>
</x-filament-panels::page>