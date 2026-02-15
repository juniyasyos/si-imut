<x-filament-breezy::grid-section md=2 title="Tanda Tangan Digital" description="Upload tanda tangan digital Anda">
    <x-filament::card>
        <form wire:submit.prevent="submit" id="ttd-form" class="space-y-6">
            {{ $this->form }}

            <div class="text-right">
                <x-filament::button type="submit" form="ttd-form" class="align-right">
                    Simpan
                </x-filament::button>
            </div>
        </form>
    </x-filament::card>
</x-filament-breezy::grid-section>