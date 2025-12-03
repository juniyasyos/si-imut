<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <div class="space-y-4">
        @foreach($getFormFields() as $formField)
        @if($formField->type === 'text')
        <x-filament::input.wrapper>
            <x-filament::input
                type="text"
                wire:model="data.{{ $formField->key }}"
                id="{{ $formField->key }}"
                placeholder="{{ $formField->label }}"
                :required="$formField->is_required" />
            <x-slot name="label">
                {{ $formField->label }}
                @if($formField->is_required)
                <span class="text-danger-600 dark:text-danger-400">*</span>
                @endif
            </x-slot>
        </x-filament::input.wrapper>
        @elseif($formField->type === 'textarea')
        <x-filament::input.wrapper>
            <textarea
                wire:model="data.{{ $formField->key }}"
                id="{{ $formField->key }}"
                rows="3"
                class="fi-input block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                placeholder="{{ $formField->label }}"
                {{ $formField->is_required ? 'required' : '' }}></textarea>
            <x-slot name="label">
                {{ $formField->label }}
                @if($formField->is_required)
                <span class="text-danger-600 dark:text-danger-400">*</span>
                @endif
            </x-slot>
        </x-filament::input.wrapper>
        @elseif($formField->type === 'number')
        <x-filament::input.wrapper>
            <x-filament::input
                type="number"
                wire:model="data.{{ $formField->key }}"
                id="{{ $formField->key }}"
                placeholder="{{ $formField->label }}"
                :required="$formField->is_required" />
            <x-slot name="label">
                {{ $formField->label }}
                @if($formField->is_required)
                <span class="text-danger-600 dark:text-danger-400">*</span>
                @endif
            </x-slot>
        </x-filament::input.wrapper>
        @elseif($formField->type === 'date')
        <x-filament::input.wrapper>
            <x-filament::input
                type="date"
                wire:model="data.{{ $formField->key }}"
                id="{{ $formField->key }}"
                :required="$formField->is_required" />
            <x-slot name="label">
                {{ $formField->label }}
                @if($formField->is_required)
                <span class="text-danger-600 dark:text-danger-400">*</span>
                @endif
            </x-slot>
        </x-filament::input.wrapper>
        @elseif($formField->type === 'bool')
        <div class="flex items-center gap-3">
            <input
                type="checkbox"
                wire:model="data.{{ $formField->key }}"
                id="{{ $formField->key }}"
                class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900"
                {{ $formField->is_required ? 'required' : '' }} />
            <label for="{{ $formField->key }}" class="text-sm font-medium text-gray-700 dark:text-gray-200">
                {{ $formField->label }}
                @if($formField->is_required)
                <span class="text-danger-600 dark:text-danger-400">*</span>
                @endif
            </label>
        </div>
        @elseif($formField->type === 'select')
        <x-filament::input.wrapper>
            <select
                wire:model="data.{{ $formField->key }}"
                id="{{ $formField->key }}"
                class="fi-input block w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                {{ $formField->is_required ? 'required' : '' }}>
                <option value="">-- Pilih {{ $formField->label }} --</option>
                @if($formField->options)
                @foreach($formField->options as $option)
                <option value="{{ $option['value'] ?? $option }}">{{ $option['label'] ?? $option }}</option>
                @endforeach
                @endif
            </select>
            <x-slot name="label">
                {{ $formField->label }}
                @if($formField->is_required)
                <span class="text-danger-600 dark:text-danger-400">*</span>
                @endif
            </x-slot>
        </x-filament::input.wrapper>
        @elseif($formField->type === 'radio')
        <x-filament::input.wrapper>
            <x-slot name="label">
                {{ $formField->label }}
                @if($formField->is_required)
                <span class="text-danger-600 dark:text-danger-400">*</span>
                @endif
            </x-slot>
            <div class="space-y-2">
                @if($formField->options)
                @foreach($formField->options as $option)
                <div class="flex items-center gap-3">
                    <input
                        type="radio"
                        wire:model="data.{{ $formField->key }}"
                        id="{{ $formField->key }}_{{ $loop->index }}"
                        value="{{ $option['value'] ?? $option }}"
                        class="fi-radio-input border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900"
                        {{ $formField->is_required ? 'required' : '' }} />
                    <label for="{{ $formField->key }}_{{ $loop->index }}" class="text-sm text-gray-700 dark:text-gray-200">
                        {{ $option['label'] ?? $option }}
                    </label>
                </div>
                @endforeach
                @endif
            </div>
        </x-filament::input.wrapper>
        @elseif($formField->type === 'checkbox')
        <x-filament::input.wrapper>
            <x-slot name="label">
                {{ $formField->label }}
                @if($formField->is_required)
                <span class="text-danger-600 dark:text-danger-400">*</span>
                @endif
            </x-slot>
            <div class="space-y-2">
                @if($formField->options)
                @foreach($formField->options as $option)
                <div class="flex items-center gap-3">
                    <input
                        type="checkbox"
                        wire:model="data.{{ $formField->key }}"
                        id="{{ $formField->key }}_{{ $loop->index }}"
                        value="{{ $option['value'] ?? $option }}"
                        class="fi-checkbox-input rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900" />
                    <label for="{{ $formField->key }}_{{ $loop->index }}" class="text-sm text-gray-700 dark:text-gray-200">
                        {{ $option['label'] ?? $option }}
                    </label>
                </div>
                @endforeach
                @endif
            </div>
        </x-filament::input.wrapper>
        @endif
        @endforeach
    </div>
</x-dynamic-component>