<x-filament-panels::page>
    @if($formHeader)
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Header Form Style Google Form -->
        <div class="bg-white dark:bg-gray-800 rounded-t-lg border-t-8 border-primary-600 shadow-sm p-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                {{ $formHeader->title }}
            </h1>
            @if($formHeader->description)
            <p class="text-gray-600 dark:text-gray-400 text-base leading-relaxed">
                {{ $formHeader->description }}
            </p>
            @endif
        </div>

        <!-- Form Fields -->
        <div class="space-y-4">
            @foreach($formHeader->formFields as $field)
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-md transition-shadow">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-900 dark:text-white mb-2">
                        {{ $field->label }}
                        @if($field->is_required)
                        <span class="text-red-500 ml-1">*</span>
                        @endif
                    </label>
                </div>

                @if($field->type === 'text')
                <input
                    type="text"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    placeholder="Jawaban Anda"
                    {{ $field->is_required ? 'required' : '' }} />

                @elseif($field->type === 'textarea')
                <textarea
                    rows="4"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    placeholder="Jawaban Anda"
                    {{ $field->is_required ? 'required' : '' }}></textarea>

                @elseif($field->type === 'number')
                <input
                    type="number"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    placeholder="Masukkan angka"
                    {{ $field->is_required ? 'required' : '' }} />

                @elseif($field->type === 'date')
                <input
                    type="date"
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    {{ $field->is_required ? 'required' : '' }} />

                @elseif($field->type === 'bool')
                <div class="flex items-center gap-3 p-4 bg-gray-50 dark:bg-gray-900/50 rounded-lg">
                    <input
                        type="checkbox"
                        class="rounded border-gray-300 dark:border-gray-600 text-primary-600 shadow-sm focus:ring-primary-500 w-5 h-5"
                        {{ $field->is_required ? 'required' : '' }} />
                    <span class="text-sm text-gray-700 dark:text-gray-300">Ya</span>
                </div>

                @elseif($field->type === 'select')
                <select
                    class="w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-900 dark:text-white shadow-sm focus:border-primary-500 focus:ring-primary-500"
                    {{ $field->is_required ? 'required' : '' }}>
                    <option value="">-- Pilih {{ $field->label }} --</option>
                    @if($field->options)
                    @foreach($field->options as $option)
                    <option value="{{ $option['value'] ?? $option }}">
                        {{ $option['label'] ?? $option }}
                    </option>
                    @endforeach
                    @endif
                </select>

                @elseif($field->type === 'radio')
                <div class="space-y-3">
                    @if($field->options)
                    @foreach($field->options as $option)
                    <div class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-900/50 rounded-lg transition-colors">
                        <input
                            type="radio"
                            name="field_{{ $field->id }}"
                            value="{{ $option['value'] ?? $option }}"
                            class="border-gray-300 dark:border-gray-600 text-primary-600 shadow-sm focus:ring-primary-500 w-5 h-5"
                            {{ $field->is_required ? 'required' : '' }} />
                        <label class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $option['label'] ?? $option }}
                        </label>
                    </div>
                    @endforeach
                    @endif
                </div>

                @elseif($field->type === 'checkbox')
                <div class="space-y-3">
                    @if($field->options)
                    @foreach($field->options as $option)
                    <div class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-900/50 rounded-lg transition-colors">
                        <input
                            type="checkbox"
                            name="field_{{ $field->id }}[]"
                            value="{{ $option['value'] ?? $option }}"
                            class="rounded border-gray-300 dark:border-gray-600 text-primary-600 shadow-sm focus:ring-primary-500 w-5 h-5" />
                        <label class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $option['label'] ?? $option }}
                        </label>
                    </div>
                    @endforeach
                    @endif
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Submit Button Preview -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <button
                type="button"
                class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-sm transition-colors">
                Kirim
            </button>
        </div>

        <!-- Info Banner -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
            <div class="flex gap-3">
                <x-heroicon-o-information-circle class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0" />
                <p class="text-sm text-blue-800 dark:text-blue-300">
                    Ini adalah preview form Anda. Form belum bisa diisi pada mode preview ini.
                </p>
            </div>
        </div>
    </div>
    @else
    <div class="text-center py-12">
        <x-heroicon-o-document-text class="w-16 h-16 text-gray-400 dark:text-gray-600 mx-auto mb-4" />
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Belum Ada Form</h3>
        <p class="text-gray-600 dark:text-gray-400 mb-6">
            Anda belum membuat form untuk data IMUT ini.
        </p>
        <a
            href="{{ \App\Filament\Resources\ImutDataResource::getUrl('manage-form-builder', ['record' => $record->slug]) }}"
            class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg shadow-sm transition-colors">
            <x-heroicon-o-plus class="w-5 h-5" />
            Buat Form
        </a>
    </div>
    @endif
</x-filament-panels::page>