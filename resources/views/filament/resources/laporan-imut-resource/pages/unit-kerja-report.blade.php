<x-filament-panels::page>
    <div class="mt-6">
        {{ $this->form }}
    </div>
    @if ($laporan)
        <livewire:reports.unit-kerja-summary-report :laporan-id="$laporan->id" />
    @endif
</x-filament-panels::page>
