@props([
'leftTitle' => 'Pengumpul Data',
'leftSignature' => '(...........................)',
'leftUsers' => null,
'leftSignatureImage' => null, // URL gambar TTD untuk kiri
'rightTitle' => 'Validator Data / Penanggung Jawab',
'rightSignature' => '(...........................)',
'rightUsers' => null,
'rightSignatureImage' => null, // URL gambar TTD untuk kanan
'unit' => null, // Optional UnitKerja model — component will fallback to SignatoryService when provided
'showDate' => true,
'date' => null,
'datePrefix' => 'Jember, ',
'showSystemNote' => true,
'systemNote' => 'Dokumen ini dibuat secara otomatis oleh Sistem Informasi Indikator Mutu (SI-IMUT)',
'notes' => null,
'debug' => false,
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
            <div class="text-xs mb-10 font-semibold">
                <br><br>{{ $leftTitle }}
            </div>
            @if($leftSignatureImage)
            <div class="">
                <img src="{{ $leftSignatureImage }}" alt="Tanda Tangan" class="h-24 w-auto mx-auto object-contain">
            </div>
            @endif

            {{-- Fallback: jika tidak ada leftUsers tapi prop `unit` diberikan, ambil dari SignatoryService --}}
            @php
            if (empty($leftUsers) && isset($unit)) {
            $svc = app(\App\Services\Support\SignatoryService::class);
            $sign = $svc->pickForUnit($unit);
            if ($sign['pengumpul']) {
            $leftUsers = [[
            'id' => $sign['pengumpul']->id,
            'name' => $sign['pengumpul']->name,
            'ttd_url' => $svc->getTtdUrl($sign['pengumpul']) ?? null,
            ]];
            if (! $leftSignatureImage && ! empty($leftUsers[0]['ttd_url'])) {
            $leftSignatureImage = $leftUsers[0]['ttd_url'];
            }
            }
            if ($sign['validator'] && empty($rightUsers)) {
            $rightUsers = [[
            'id' => $sign['validator']->id,
            'name' => $sign['validator']->name,
            'ttd_url' => $svc->getTtdUrl($sign['validator']) ?? null,
            ]];
            if (! $rightSignatureImage && ! empty($rightUsers[0]['ttd_url'])) {
            $rightSignatureImage = $rightUsers[0]['ttd_url'];
            }
            }
            }
            @endphp

            @if($leftUsers && count($leftUsers) > 0)
            <div class="text-xs font-semibold text-gray-600 mb-12 min-h-8">
                @foreach($leftUsers as $user)
                <div>{{ $user['name'] ?? $user }}</div>
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
            <div class="text-xs mb-10 font-semibold">{{ $rightTitle }}</div>
            @if($rightSignatureImage)
            <div class="mb-2">
                <img src="{{ $rightSignatureImage }}" alt="Tanda Tangan" class="h-24 w-auto mx-auto object-contain">
            </div>
            @endif
            @if($rightUsers && count($rightUsers) > 0)
            <div class="text-xs font-semibold text-gray-600 mb-3 min-h-8">
                @foreach($rightUsers as $user)
                <div>{{ $user['name'] ?? $user }}</div>
                @endforeach
            </div>
            @endif
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
            'leftSignatureImage' => $leftSignatureImage,
            'rightTitle' => $rightTitle,
            'rightSignature' => $rightSignature,
            'rightUsers' => $rightUsers,
            'rightSignatureImage' => $rightSignatureImage,
            'showDate' => $showDate,
            'date' => $date,
            'datePrefix' => $datePrefix,
            'showSystemNote' => $showSystemNote,
            'systemNote' => $systemNote,
            'notes' => $notes,
            'borderColor' => $borderColor,
            ],
            'attributes' => $attributes->getAttributes(),
            ];
            @endphp

            <div class="mb-3 font-semibold">Server (Blade) — semua data</div>
            <pre class="whitespace-pre-wrap text-[11px] max-h-64 overflow-auto rounded bg-white p-2 border">{{ json_encode($__debugData, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) }}</pre>

            <div class="mt-3 mb-1 font-semibold">Client (runtime) — JS data</div>
            <pre x-text="JSON.stringify({
                leftUsersAlpine: (typeof usersByUnit !== 'undefined' ? usersByUnit.pengumpul_data : null),
                rightUsersAlpine: (typeof usersByUnit !== 'undefined' ? usersByUnit.validator : null),
                dateAlpine: <?php echo json_encode($date ?? null); ?>
            }, null, 2)" class="whitespace-pre-wrap text-[11px] max-h-64 overflow-auto rounded bg-white p-2 border"></pre>

        </div>
    </div>
    @endif

    @if($showSystemNote)
    <div class="text-center mt-6 text-sm text-gray-500">
        {{ $systemNote }}
    </div>
    @endif
</div>