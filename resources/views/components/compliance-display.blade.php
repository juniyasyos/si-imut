<div class="space-y-4 text-sm sm:text-base">
    @php
        $isCompliant = ($compliance['score'] ?? 0) >= 100;
        $scoreColor = $isCompliant ? 'green' : 'red';
    @endphp

    <div @class([
        'rounded-xl border p-3 sm:p-4 transition-colors',
        'border-green-200 bg-green-100 dark:border-green-900/40 dark:text-white dark:bg-green-900' => $isCompliant,
        'border-red-200 bg-red-100 dark:border-red-900/40 dark:bg-red-800' => !$isCompliant,
    ])>
        <h3
            class="mb-1 text-sm font-semibold sm:text-base lg:text-lg text-{{ $scoreColor }}-800 dark:text-{{ $scoreColor }}-300">
            Tingkat Kepatuhan
        </h3>

        <div
            class="text-2xl sm:text-3xl lg:text-4xl font-bold leading-tight text-{{ $scoreColor }}-600 dark:text-{{ $scoreColor }}-400">
            {{ number_format($compliance['score'] ?? 0, 1) }}%
        </div>

        <div class="mt-1 text-sm sm:text-base font-semibold text-{{ $scoreColor }}-700 dark:text-{{ $scoreColor }}-300">
            {{ $isCompliant ? 'Sesuai Standar' : 'Perlu Perbaikan' }}
        </div>

        <p class="mt-2 text-xs sm:text-sm text-{{ $scoreColor }}-600 dark:text-{{ $scoreColor }}-400">
            Seluruh pertanyaan harus dijawab dengan benar untuk mencapai tingkat kepatuhan maksimal.
        </p>
    </div>

    <div class="rounded-xl border border-slate-200 bg-slate-100 p-3 sm:p-4 dark:border-slate-800 dark:bg-slate-700">
        <h4 class="mb-3 text-sm sm:text-base font-semibold text-slate-800 dark:text-slate-100">
            Rincian Penilaian
        </h4>

        <div class="space-y-2 text-xs sm:text-sm">
            @foreach(($compliance['fields'] ?? []) as $fieldKey => $fieldData)
                    @php
                        $field = $formTemplate->formFields->where('field_key', $fieldKey)->first();

                        if (!$field) {
                            continue;
                        }

                        $weight = $field->compliance_weight ?? 0;
                        $score = $fieldData['score'] ?? 0;
                        $percentage = $weight > 0 ? ($score / $weight) * 100 : 100;
                        $fieldCompliant = $weight <= 0 || $percentage >= 100;
                        $statusColor = $fieldCompliant ? 'green' : 'red';
                    @endphp

                    <div @class([
                        'rounded-lg border p-2 sm:p-3 transition-colors',
                        'border-green-200 bg-green-100 dark:border-green-900 dark:bg-green-900' => $fieldCompliant,
                        'border-red-200 bg-red-100 dark:border-red-900 dark:bg-red-900' => !$fieldCompliant,
                    ])>
                        <div class="flex items-start justify-between gap-2">
                            <span
                                class="text-xs sm:text-sm font-medium leading-snug text-{{ $statusColor }}-700 dark:text-{{ $statusColor }}-300">
                                {!! $field->field_label !!}

                                {!! $field->is_critical_field ? '<span class="ml-1 text-yellow-500 dark:text-yellow-400">⚠</span>' : '' !!}
                            </span>

                            <span
                                class="whitespace-nowrap text-xs sm:text-sm font-semibold text-{{ $statusColor }}-700 dark:text-{{ $statusColor }}-300">
                                {{ number_format($percentage, 1) }}%
                            </span>
                        </div>

                        <div
                            class="mt-1 flex flex-wrap items-center gap-2 text-[11px] sm:text-xs text-slate-600 dark:text-slate-400">
                            <span class="text-{{ $statusColor }}-600 dark:text-{{ $statusColor }}-400">
                                {{ $fieldCompliant ? 'Sesuai' : 'Belum Sesuai' }}
                            </span>
                        </div>
                    </div>
            @endforeach
        </div>
    </div>

    @if(($compliance['warnings'] ?? []))
        <div class="rounded-xl border border-red-200 bg-red-50 p-3 sm:p-4 dark:border-red-900/40 dark:bg-red-950/20">
            <h4 class="mb-2 text-sm sm:text-base font-semibold text-red-800 dark:text-red-300">
                Catatan Perlu Ditinjau
            </h4>

            <ul class="space-y-1 text-xs sm:text-sm text-red-700 dark:text-red-300">
                @foreach($compliance['warnings'] as $warning)
                    <li class="flex gap-2">
                        <span class="flex-shrink-0">•</span>
                        <span>{{ $warning }}</span>
                    </li>
                @endforeach
            </ul>

            <p class="mt-2 text-[11px] sm:text-xs italic text-red-600 dark:text-red-400">
                Periksa kembali jawaban yang belum sesuai agar tingkat kepatuhan dapat tercapai secara optimal.
            </p>
        </div>
    @endif
</div>