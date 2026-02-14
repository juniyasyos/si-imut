@props([
    'leftTitle' => 'Mengetahui',
    'leftSubtitle' => 'Kepala Bagian Mutu',
    'leftSignature' => '(...........................)',
    'leftSignatureImage' => null, // URL gambar TTD untuk kiri
    'rightTitle' => 'Penanggung Jawab',
    'rightSubtitle' => null,
    'rightSignature' => '(...........................)',
    'rightSignatureImage' => null, // URL gambar TTD untuk kanan
    'showDate' => true,
    'date' => null,
    'showSystemNote' => true,
    'systemNote' => 'Dokumen ini dibuat secara otomatis oleh Sistem Informasi Indikator Mutu (SI-IMUT)',
    'notes' => null
])

<!-- Footer & Signature -->
<div class="mt-10 border-t-2 border-gray-300 pt-6">
    @if($notes)
    <div class="mb-5">
        <strong>📝 Catatan:</strong>
        <ul class="ml-5 mt-2 text-xs space-y-1">
            @foreach($notes as $note)
            <li>{{ $note }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="flex justify-between mt-10">
        <div class="text-center w-56">
            <div class="text-sm mb-20">
                {{ $leftTitle }}
                @if($leftSubtitle)
                <br>{{ $leftSubtitle }}
                @endif
            </div>
            @if($leftSignatureImage)
            <div class="mb-2">
                <img src="{{ $leftSignatureImage }}" alt="Tanda Tangan" class="max-h-12 mx-auto border-b border-gray-400">
            </div>
            @endif
            <div class="text-sm font-bold border-t-2 border-black pt-2">{{ $leftSignature }}</div>
        </div>
        <div class="text-center w-56">
            <div class="text-sm mb-20">
                @if($showDate && $rightSubtitle)
                {{ $date ?? now()->translatedFormat('d F Y') }},<br>{{ $rightTitle }}
                @elseif($showDate)
                {{ $date ?? now()->translatedFormat('d F Y') }},<br>{{ $rightTitle }}
                @else
                {{ $rightTitle }}
                @endif
                @if($rightSubtitle && (!$showDate || !$rightTitle))
                <br>{{ $rightSubtitle }}
                @endif
            </div>
            @if($rightSignatureImage)
            <div class="mb-2">
                <img src="{{ $rightSignatureImage }}" alt="Tanda Tangan" class="max-h-12 mx-auto border-b border-gray-400">
            </div>
            @endif
            <div class="text-sm font-bold border-t-2 border-black pt-2">{{ $rightSignature }}</div>
        </div>
    </div>

    @if($showSystemNote)
    <div class="text-center mt-6 text-sm text-gray-500">
        {{ $systemNote }}
    </div>
    @endif
</div>