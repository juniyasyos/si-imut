<x-filament-breezy::grid-section md=2 title="Tanda Tangan Digital" description="Upload tanda tangan digital Anda">
    <x-filament::card>
        <form wire:submit.prevent="submit" class="space-y-6">
            @if($user->ttd_url)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Tanda Tangan Saat Ini</label>
                    <img src="{{ $user->getFilamentTtdUrl() }}" alt="Tanda Tangan" class="max-w-xs max-h-32 border rounded">
                </div>
            @endif
            {{ $this->form }}

            <div class="text-right">
                <x-filament::button type="submit" form="submit" class="align-right">
                    Simpan
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
</x-filament-breezy::grid-section>
