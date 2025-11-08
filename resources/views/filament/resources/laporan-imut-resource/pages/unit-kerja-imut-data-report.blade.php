<x-filament-panels::page>
    {{ $this->form }}
    <livewire:reports.unit-kerja-imut-data-detail-report :laporan-id="$data['laporanId']" :unit-kerja-id="request()->get('unit_kerja_id')" />
</x-filament-panels::page>
