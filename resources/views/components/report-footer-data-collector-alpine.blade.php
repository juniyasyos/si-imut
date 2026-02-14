@props([
    'leftTitle' => 'Pengumpul Data',
    'leftSignature' => '(...........................)',
    'leftUsers' => null,
    'leftUsersAlpine' => null, // Untuk Alpine.js data
    'leftSignatureImage' => null, // URL gambar TTD untuk kiri
    'rightTitle' => 'Validator Data / Penanggung Jawab',
    'rightSignature' => '(...........................)',
    'rightUsers' => null,
    'rightUsersAlpine' => null, // Untuk Alpine.js data
    'rightSignatureImage' => null, // URL gambar TTD untuk kanan
    'showDate' => true,
    'date' => null,
    'datePrefix' => 'Jember, ',
    'dateAlpine' => null, // Untuk Alpine.js date
    'showSystemNote' => true,
    'systemNote' => 'Dokumen ini dibuat secara otomatis oleh Sistem Informasi Indikator Mutu (SI-IMUT)',
    'notes' => null,
    'borderColor' => 'border-gray-300',
    'marginTop' => 'mt-8'
])

<!-- Footer & Signature -->
<div class="{{ $marginTop }} border-t-2 {{ $borderColor }} pt-6">
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

    <div class="flex justify-between mt-8 gap-4">
        <div class="text-center flex-1">
            <div class="text-sm mb-10 font-semibold"><br><br>{{ $leftTitle }}</div>
            @if($leftSignatureImage)
            <div class="mb-2">
                <img src="{{ $leftSignatureImage }}" alt="Tanda Tangan" class="max-h-12 mx-auto border-b border-gray-400">
            </div>
            @endif
            <div class="text-sm font-bold border-black pt-2">{{ $leftSignature }}</div>
            @if($leftUsersAlpine)
            <div class="text-xs text-gray-600 mb-12 min-h-8">
                <div x-show="{{ $leftUsersAlpine }} && {{ $leftUsersAlpine }}.length > 0">
                    <template x-for="user in {{ $leftUsersAlpine }}" :key="'left-' + user.id">
                        <div x-text="user.name"></div>
                        <div x-show="user.ttd_url" class="mt-1">
                            <img :src="user.ttd_url" alt="Tanda Tangan" class="max-h-8 mx-auto border-b border-gray-400">
                        </div>
                    </template>
                </div>
            </div>
            @elseif($leftUsers && count($leftUsers) > 0)
            <div class="text-xs text-gray-600 mb-12 min-h-8">
                @foreach($leftUsers as $user)
                <div>{{ $user['name'] ?? $user }}</div>
                @endforeach
            </div>
            @endif
        </div>
        <div class="text-center flex-1">
            @if($showDate)
            <div class="text-sm mb-2">
                @if($dateAlpine)
                <span x-text="{{ $dateAlpine }}"></span>
                @else
                <span>{{ $datePrefix }}{{ $date ?? now()->translatedFormat('d F Y') }}</span>
                @endif
            </div>
            @endif
            <div class="text-sm mb-10 font-semibold">{{ $rightTitle }}</div>
            @if($rightSignatureImage)
            <div class="mb-2">
                <img src="{{ $rightSignatureImage }}" alt="Tanda Tangan" class="max-h-12 mx-auto border-b border-gray-400">
            </div>
            @endif
            <div class="text-sm font-bold border-black pt-2">{{ $rightSignature }}</div>
            @if($rightUsersAlpine)
            <div class="text-xs text-gray-600 mb-3 min-h-8">
                <div x-show="{{ $rightUsersAlpine }} && {{ $rightUsersAlpine }}.length > 0">
                    <template x-for="user in {{ $rightUsersAlpine }}" :key="'right-' + user.id">
                        <div x-text="user.name"></div>
                        <div x-show="user.ttd_url" class="mt-1">
                            <img :src="user.ttd_url" alt="Tanda Tangan" class="max-h-8 mx-auto border-b border-gray-400">
                        </div>
                    </template>
                </div>
            </div>
            @elseif($rightUsers && count($rightUsers) > 0)
            <div class="text-xs text-gray-600 mb-3 min-h-8">
                @foreach($rightUsers as $user)
                <div>{{ $user['name'] ?? $user }}</div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    @if($showSystemNote)
    <div class="text-center mt-6 text-xs text-gray-500">
        {{ $systemNote }}
    </div>
    @endif
</div>