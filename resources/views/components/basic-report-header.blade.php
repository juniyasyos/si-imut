@props([
'title' => 'Laporan',
'subtitle' => 'Sistem Informasi Indikator Mutu (SI-IMUT)',
'documentNumber' => 'IMUT/LAP/001/2024',
'pageNumber' => '1 dari 1',
'additionalInfo' => []
])

<div class="report-header border-b-2 border-gray-800 pb-4 mb-8">
    <div class="flex items-start justify-between gap-6 mb-3">
        <!-- Logo Kiri -->
        <div class="w-32 h-32 flex-shrink-0">
            <img src="{{ asset('images/assets/logo-rs.webp') }}"
                alt="Logo RS Citra Husada Jember" class="w-full h-full object-contain">
        </div>

        <!-- Text Content -->
        <div class="flex-1 text-center">
            <h1 class="text-xl font-bold text-gray-900 mb-1" style="letter-spacing: 1px;">RUMAH SAKIT CITRA HUSADA JEMBER</h1>
            <div class="text-xs text-gray-600 mb-2" style="letter-spacing: 0.5px;">Jl. Teratai No. 22, Kab. Jember, Jawa Timur | Telp. (0331) 486200 </div>
            <div class="text-xs text-gray-600 mb-2" style="letter-spacing: 0.5px;">Telp. (0331) 486200 | Fax. (0331) 427088</div>
            <div class="h-px bg-gray-400 my-2"></div>
            <h2 class="text-base font-bold text-gray-800 uppercase" style="letter-spacing: 1.5px;">{{ $title }}</h2>
            <div class="text-xs text-gray-600 mt-1" style="letter-spacing: 0.5px;">{{ $subtitle }}</div>
        </div>

        <!-- Logo Kanan -->
        <div class="w-14 h-14 md:w-16 md:h-16 mr-5 mt-4">
            <x-logo-report />
        </div>
    </div>

    <!-- Document Info Bar -->
    <div class="bg-gray-100 border border-gray-300 rounded px-4 py-2 flex justify-between items-center text-xs">
        @if(count($additionalInfo) > 0)
        @foreach($additionalInfo as $info)
        <div><span class="font-semibold text-gray-700">{{ $info['label'] }}:</span> <span class="text-gray-600">{{ $info['value'] }}</span></div>
        @endforeach
        @else
        <div><span class="font-semibold text-gray-700">Nomor Dokumen:</span> <span class="text-gray-600">{{ $documentNumber }}</span></div>
        <div><span class="font-semibold text-gray-700">Tanggal Cetak:</span> <span class="text-gray-600" x-text="new Date().toLocaleDateString('id-ID', {day: '2-digit', month: 'long', year: 'numeric'})"></span></div>
        <div><span class="font-semibold text-gray-700">Halaman:</span> <span class="text-gray-600">{{ $pageNumber }}</span></div>
        @endif
    </div>
</div>