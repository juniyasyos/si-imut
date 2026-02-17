@props([
'leftTitle' => 'Pengumpul Data',
'leftSignature' => '(...........................)',
'leftUsers' => null,
'leftUsersAlpine' => null, // Untuk Alpine.js data
'leftSignatureImage' => null, // URL gambar TTD untuk kiri
'rightTitle' => 'Validator Data / PIC Unit Kerja',
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
'debug' => false,
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
            <div class="text-sm font-semibold"><br><br>{{ $leftTitle }}</div>

            {{-- Signature area: prefer Alpine-driven user TTD, fallback to server-side image, otherwise show placeholder line + penanda --}}
            <div class="mb-1">
                {{-- Server-side provided signature image (e.g. from getFilamentTtdUrl()) --}}
                @if($leftSignatureImage)
                <div class="mb-1">
                    <img src="{{ $leftSignatureImage }}" alt="Tanda Tangan" class="h-32 w-auto mx-auto object-contain">
                </div>
                @endif

                {{-- Alpine-driven signature (first user) --}}
                <div x-show="{{ $leftUsersAlpine }} && {{ $leftUsersAlpine }}.length > 0 && {{ $leftUsersAlpine }}[0].ttd_url" class="mb-1">
                    <img :src="(typeof {{ $leftUsersAlpine }} !== 'undefined' && {{ $leftUsersAlpine }}.length > 0 && {{ $leftUsersAlpine }}[0].ttd_url) ? (({{ $leftUsersAlpine }}[0].ttd_url.indexOf('http') === 0 || {{ $leftUsersAlpine }}[0].ttd_url.indexOf('/') === 0) ? {{ $leftUsersAlpine }}[0].ttd_url : '/storage/' + {{ $leftUsersAlpine }}[0].ttd_url) : ''" alt="Tanda Tangan" class="h-32 w-auto mx-auto object-contain">
                </div>

                {{-- Placeholder when no image is available; reserves same vertical space and shows penanda --}}
                <div x-show="!((typeof {{ $leftUsersAlpine }} !== 'undefined' && {{ $leftUsersAlpine }}.length > 0 && {{ $leftUsersAlpine }}[0].ttd_url) || {{ $leftSignatureImage ? 'true' : 'false' }})" class="mb-1 h-32 flex items-end justify-center"></div>

                {{-- Show signature text: if prop is default-placeholder, hide it when a TTD image exists; otherwise always show --}}
                @if($leftSignature !== '(...........................)')
                <div class="text-xs font-bold pt-1">{{ $leftSignature }}</div>
                @else
                <div x-show="!((typeof {{ $leftUsersAlpine }} !== 'undefined' && {{ $leftUsersAlpine }}.length > 0 && {{ $leftUsersAlpine }}[0].ttd_url) || {{ $leftSignatureImage ? 'true' : 'false' }})" class="text-xs font-bold pt-1">{{ $leftSignature }}</div>
                @endif
            </div>

            <div class="text-xs text-gray-600 mb-12 min-h-8">
                <div x-show="{{ $leftUsersAlpine }} && {{ $leftUsersAlpine }}.length > 0">
                    <template x-for="user in {{ $leftUsersAlpine }}" :key="'left-' + user.id">
                        <div x-text="user.name" class="font-semibold"></div>
                    </template>
                </div>
            </div>
        </div>
        <div class="text-center flex-1 mt-2">
            @if($showDate)
            <div class="text-sm mb-2">
                @if($dateAlpine)
                {{-- keluarkan expression JS mentah (jangan di-escape sebagai &quot;) --}}
                <span x-text='{!! $dateAlpine !!}'></span>
                @else
                {{-- Client-side fallback: gunakan Alpine `metadata.period_label` bila tersedia. Ini mencegah
                     HTML-escaped JS expression yang memecah Alpine runtime. --}}
                <span x-text="(typeof metadata !== 'undefined' && metadata.period_label) ? '{{ $datePrefix }}' + new Date().toLocaleDateString('id-ID', {day: 'numeric', month: 'long', year: 'numeric'}) : '{{ $datePrefix }}{{ $date ?? now()->translatedFormat('d F Y') }}'"></span>
                @endif
            </div>
            @endif
            <div class="text-sm font-semibold">{{ $rightTitle }}</div>

            {{-- Signature area: prefer Alpine-driven user TTD, fallback to server-side image, otherwise show placeholder line + penanda --}}
            <div class="mb-1">
                {{-- Server-side provided signature image (e.g. from getFilamentTtdUrl()) --}}
                @if($rightSignatureImage)
                <div class="mb-1">
                    <img src="{{ $rightSignatureImage }}" alt="Tanda Tangan" class="h-32 w-auto mx-auto object-contain border-b border-gray-400">
                </div>
                @endif

                {{-- Alpine-driven signature (first user) --}}
                <div x-show="{{ $rightUsersAlpine }} && {{ $rightUsersAlpine }}.length > 0 && {{ $rightUsersAlpine }}[0].ttd_url" class="mb-1">
                    <img :src="(typeof {{ $rightUsersAlpine }} !== 'undefined' && {{ $rightUsersAlpine }}.length > 0 && {{ $rightUsersAlpine }}[0].ttd_url) ? (({{ $rightUsersAlpine }}[0].ttd_url.indexOf('http') === 0 || {{ $rightUsersAlpine }}[0].ttd_url.indexOf('/') === 0) ? {{ $rightUsersAlpine }}[0].ttd_url : '/storage/' + {{ $rightUsersAlpine }}[0].ttd_url) : ''" alt="Tanda Tangan" class="h-32 w-auto mx-auto object-contain border-b border-gray-400">
                </div>

                {{-- Placeholder when no image is available; reserves same vertical space and shows penanda --}}
                <div x-show="!((typeof {{ $rightUsersAlpine }} !== 'undefined' && {{ $rightUsersAlpine }}.length > 0 && {{ $rightUsersAlpine }}[0].ttd_url) || {{ $rightSignatureImage ? 'true' : 'false' }})" class="mb-1 h-32 flex items-end justify-center border-b border-gray-400"></div>

                {{-- Show signature text: if prop is default-placeholder, hide it when a TTD image exists; otherwise always show --}}
                @if($rightSignature !== '(...........................)')
                <div class="text-xs font-bold pt-1">{{ $rightSignature }}</div>
                @else
                <div x-show="!((typeof {{ $rightUsersAlpine }} !== 'undefined' && {{ $rightUsersAlpine }}.length > 0 && {{ $rightUsersAlpine }}[0].ttd_url) || {{ $rightSignatureImage ? 'true' : 'false' }})" class="text-xs font-bold pt-1">{{ $rightSignature }}</div>
                @endif
            </div>

            <div class="text-xs text-gray-600 mb-3 min-h-8">
                <div x-show="{{ $rightUsersAlpine }} && {{ $rightUsersAlpine }}.length > 0">
                    <template x-for="user in {{ $rightUsersAlpine }}" :key="'right-' + user.id">
                        <div x-text="user.name" class="font-semibold"></div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    @if($debug)
    <div x-data="{ open: false }" class="mt-6">
        <button type="button" x-on:click="open = !open" class="text-xs text-gray-600 underline mb-2">
            <span x-text="open ? 'Sembunyikan debug log' : 'Tampilkan debug log (JSON)'"></span>
        </button>

        <div x-show="open" x-cloak class="bg-gray-50 p-3 rounded text-xs text-gray-700 border">
            @php
            $__debugData = [
            'props' => [
            'leftTitle' => $leftTitle,
            'leftSignature' => $leftSignature,
            'leftUsers' => $leftUsers,
            'leftUsersAlpine' => $leftUsersAlpine,
            'leftSignatureImage' => $leftSignatureImage,
            'rightTitle' => $rightTitle,
            'rightSignature' => $rightSignature,
            'rightUsers' => $rightUsers,
            'rightUsersAlpine' => $rightUsersAlpine,
            'rightSignatureImage' => $rightSignatureImage,
            'showDate' => $showDate,
            'date' => $date,
            'datePrefix' => $datePrefix,
            'dateAlpine' => $dateAlpine,
            'showSystemNote' => $showSystemNote,
            'systemNote' => $systemNote,
            'notes' => $notes,
            'borderColor' => $borderColor,
            'marginTop' => $marginTop,
            ],
            'attributes' => $attributes->getAttributes(),
            ];
            @endphp

            <div class="mb-3 font-semibold">Server (Blade) — semua data</div>
            <pre class="whitespace-pre-wrap text-[11px] max-h-64 overflow-auto rounded bg-white p-2 border">{{ json_encode($__debugData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre>


            <div class="mt-3 mb-1 font-semibold">Client (Alpine) — runtime data</div>
            <pre x-text="JSON.stringify({
                leftUsersAlpine: (typeof {{ $leftUsersAlpine ?? 'null' }} !== 'undefined' ? {{ $leftUsersAlpine ?? 'null' }} : null),
                rightUsersAlpine: (typeof {{ $rightUsersAlpine ?? 'null' }} !== 'undefined' ? {{ $rightUsersAlpine ?? 'null' }} : null),
                // server-provided `dateAlpine` (raw JS expression) is NOT evaluated here to avoid
                // HTML-escaped tokens breaking Alpine. Show literal prop value instead.
                dateAlpine: <?php echo json_encode($dateAlpine ?? null); ?>
            }, null, 2)" class="whitespace-pre-wrap text-[11px] max-h-64 overflow-auto rounded bg-white p-2 border"></pre>

        </div>
    </div>
    @endif

    @if($showSystemNote)
    <div class="text-center mt-6 text-xs text-gray-500">
        {{ $systemNote }}
    </div>
    @endif
</div>