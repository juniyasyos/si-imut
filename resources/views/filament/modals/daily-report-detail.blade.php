<div class="space-y-6">
    <!-- Header Information -->
    <div class="bg-gray-50 rounded-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal Laporan</label>
                <p class="mt-1 text-sm text-gray-900">{{ $record->report_date->format('d F Y') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Unit Kerja</label>
                <p class="mt-1 text-sm text-gray-900">{{ $record->unitKerja->unit_name ?? '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Dibuat Oleh</label>
                <p class="mt-1 text-sm text-gray-900">{{ $record->user->name ?? '-' }}</p>
            </div>
        </div>
    </div>

    <!-- Compliance Summary -->
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Kepatuhan</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center space-x-3">
                <div class="w-4 h-4 rounded-full {{ $record->compliance_status ? 'bg-green-500' : 'bg-red-500' }}"></div>
                <span class="text-sm font-medium">
                    Status: {{ $record->compliance_status ? 'Patuh' : 'Tidak Patuh' }}
                </span>
            </div>
            <div class="flex items-center space-x-3">
                <div class="w-4 h-4 rounded-full bg-blue-500"></div>
                <span class="text-sm font-medium">
                    Skor Total: {{ number_format($record->total_score, 2) }}%
                </span>
            </div>
        </div>
    </div>

    <!-- Field Responses -->
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Respon</h3>
        <div class="space-y-4">
            @foreach($record->fieldResponses as $fieldResponse)
            <div class="border border-gray-100 rounded-md p-3">
                <div class="flex justify-between items-start mb-2">
                    <h4 class="text-sm font-medium text-gray-900">
                        {{ $fieldResponse->enhancedFormField->field_label ?? 'Field tidak ditemukan' }}
                    </h4>
                    @if($fieldResponse->enhancedFormField->is_critical_field)
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">
                        Kritis
                    </span>
                    @endif
                </div>

                <div class="text-sm text-gray-600 mb-2">
                    {{ $fieldResponse->enhancedFormField->field_description ?? '' }}
                </div>

                <div class="flex items-center space-x-4">
                    <span class="text-sm">
                        <strong>Jawaban:</strong> {{ $fieldResponse->response_value ?? 'Tidak ada jawaban' }}
                    </span>

                    @if($fieldResponse->compliance_score !== null)
                    <span class="text-sm">
                        <strong>Skor:</strong>
                        <span class="font-mono {{ $fieldResponse->compliance_score >= 0.8 ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($fieldResponse->compliance_score * 100, 1) }}%
                        </span>
                    </span>
                    @endif

                    @if($fieldResponse->enhancedFormField->compliance_weight)
                    <span class="text-sm text-gray-500">
                        Bobot: {{ $fieldResponse->enhancedFormField->compliance_weight }}
                    </span>
                    @endif
                </div>

                @if($fieldResponse->notes)
                <div class="mt-2 text-sm text-gray-600 italic">
                    <strong>Catatan:</strong> {{ $fieldResponse->notes }}
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    <!-- Calculation Details -->
    @if($record->calculation_details)
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Detail Perhitungan</h3>
        <div class="text-sm text-gray-700">
            <pre class="whitespace-pre-wrap">{{ json_encode($record->calculation_details, JSON_PRETTY_PRINT) }}</pre>
        </div>
    </div>
    @endif

    <!-- Notes -->
    @if($record->notes)
    <div class="bg-white border border-gray-200 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Catatan</h3>
        <p class="text-sm text-gray-700">{{ $record->notes }}</p>
    </div>
    @endif

    <!-- Metadata -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Tambahan</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
            <div>
                <label class="block font-medium text-gray-700">Waktu Dibuat</label>
                <p class="text-gray-900">{{ $record->created_at->format('d F Y, H:i:s') }}</p>
            </div>
            <div>
                <label class="block font-medium text-gray-700">Terakhir Diperbarui</label>
                <p class="text-gray-900">{{ $record->updated_at->format('d F Y, H:i:s') }}</p>
            </div>
            <div>
                <label class="block font-medium text-gray-700">Auto Calculated</label>
                <p class="text-gray-900">{{ $record->auto_calculated ? 'Ya' : 'Tidak' }}</p>
            </div>
            <div>
                <label class="block font-medium text-gray-700">Form Template</label>
                <p class="text-gray-900">{{ $record->formTemplate->title ?? 'Tidak ditemukan' }}</p>
            </div>
        </div>
    </div>
</div>