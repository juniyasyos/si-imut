@props([
    'leftTitle' => 'Pengumpul Data',
    'leftSignature' => '(...........................)',
    'leftUsers' => null,
    'leftSignatureImage' => null, // URL gambar TTD untuk kiri
    'rightTitle' => 'Validator Data / Penanggung Jawab',
    'rightSignature' => '(...........................)',
    'rightUsers' => null,
    'rightSignatureImage' => null, // URL gambar TTD untuk kanan
    'showDate' => true,
    'date' => null,
    'datePrefix' => 'Jember, ',
    'showSystemNote' => true,
    'systemNote' => 'Dokumen ini dibuat secara otomatis oleh Sistem Informasi Indikator Mutu (SI-IMUT)',
    'notes' => null,
    'borderColor' => 'border-slate-300'
])

<!-- Footer & Signature -->
<div class="mt-10 pt-5 border-t-2 {{ $borderColor }}">
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

    <div class="grid grid-cols-2 gap-12 mt-8">
        <div class="text-center">
            <div class="text-xs mb-24 font-semibold">
                <br><br>{{ $leftTitle }}
            </div>
            @if($leftSignatureImage)
            <div class="mb-2">
                <img src="{{ $leftSignatureImage }}" alt="Tanda Tangan" class="max-h-12 mx-auto border-b border-gray-400">
            </div>
            @endif
            <div class="text-xs font-bold border-t border-black pt-1">{{ $leftSignature }}</div>
            @if($leftUsers && count($leftUsers) > 0)
            <div class="text-xs text-gray-600 mb-12 min-h-8">
                @foreach($leftUsers as $user)
                <div>{{ $user['name'] ?? $user }}</div>
                @if(isset($user['ttd_url']) && $user['ttd_url'])
                <div class="mt-1">
                    <img src="{{ $user['ttd_url'] }}" alt="Tanda Tangan" class="max-h-8 mx-auto border-b border-gray-400">
                </div>
                @endif
                @endforeach
            </div>
            @endif
        </div>
        <div class="text-center">
            @if($showDate)
            <div class="text-xs mb-2">
                <span>{{ $datePrefix }}{{ $date ?? now()->translatedFormat('d F Y') }}</span>
            </div>
            @endif
            <div class="text-xs mb-24 font-semibold">{{ $rightTitle }}</div>
            @if($rightSignatureImage)
            <div class="mb-2">
                <img src="{{ $rightSignatureImage }}" alt="Tanda Tangan" class="max-h-12 mx-auto border-b border-gray-400">
            </div>
            @endif
            <div class="text-xs font-bold border-t border-black pt-1">{{ $rightSignature }}</div>
            @if($rightUsers && count($rightUsers) > 0)
            <div class="text-xs text-gray-600 mb-3 min-h-8">
                @foreach($rightUsers as $user)
                <div>{{ $user['name'] ?? $user }}</div>
                @if(isset($user['ttd_url']) && $user['ttd_url'])
                <div class="mt-1">
                    <img src="{{ $user['ttd_url'] }}" alt="Tanda Tangan" class="max-h-8 mx-auto border-b border-gray-400">
                </div>
                @endif
                @endforeach
            </div>
            @endif
        </div>
    </div>

    @if($showSystemNote)
    <div class="text-center mt-6 text-sm text-gray-500">
        {{ $systemNote }}
    </div>
    @endif
</div>