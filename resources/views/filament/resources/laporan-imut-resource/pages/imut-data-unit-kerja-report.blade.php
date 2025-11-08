<x-filament-panels::page>
    {{ $this->form }}
    <livewire:reports.imut-data-unit-kerja-detail-report :laporan-id="$data['laporanId']" :imut-data-id="request()->get('imut_data_id')" />
</x-filament-panels::page>
