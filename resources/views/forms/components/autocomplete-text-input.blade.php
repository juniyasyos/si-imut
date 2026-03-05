@php
$suggestions = $field->getSuggestions();
$statePath = $getStatePath();
$id = $getId();
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field">
    <div
        x-data="{
            value: $wire.entangle('{{ $statePath }}').live,
            query: '',
            open: false,
            activeIndex: -1,
            suggestions: {{ Js::from($suggestions) }},

            get filtered() {
                if (this.query === '') return this.suggestions;
                const q = this.query.toLowerCase();
                return this.suggestions.filter(s => s.toLowerCase().includes(q));
            },

            init() {
                // Sinkronkan query dengan value yang sudah ada (edit mode)
                this.$watch('value', (val) => {
                    if (val && val !== this.query) {
                        this.query = val;
                    }
                });
                if (this.value) {
                    this.query = this.value;
                }
            },

            onInput() {
                this.open = true;
                this.activeIndex = -1;
                // TIDAK sync ke Livewire saat ketik — hanya filter dropdown lokal
            },

            onKeydown(e) {
                if (!this.open && (e.key === 'ArrowDown' || e.key === 'ArrowUp')) {
                    this.open = true;
                    return;
                }

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    this.activeIndex = Math.min(this.activeIndex + 1, this.filtered.length - 1);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    this.activeIndex = Math.max(this.activeIndex - 1, -1);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (this.open && this.activeIndex >= 0 && this.filtered[this.activeIndex] !== undefined) {
                        // Pilih dari dropdown
                        this.select(this.filtered[this.activeIndex]);
                    } else {
                        // Pakai teks yang sedang diketik langsung
                        this.confirm();
                    }
                } else if (e.key === 'Escape') {
                    this.open = false;
                    this.activeIndex = -1;
                }
            },

            select(suggestion) {
                this.query = suggestion;
                this.value = suggestion;
                this.open  = false;
                this.activeIndex = -1;
            },

            confirm() {
                if (this.query.trim() === '') return;
                this.value = this.query.trim();
                this.open  = false;
                this.activeIndex = -1;
            },

            onBlur() {
                // Delay agar klik pada dropdown sempat tercatat
                setTimeout(() => {
                    if (this.query.trim() !== '') {
                        this.confirm();
                    }
                    this.open = false;
                }, 150);
            },
        }"
        class="relative w-full">
        {{-- Input utama --}}
        <input
            x-model="query"
            @input="onInput()"
            @keydown="onKeydown($event)"
            @focus="open = filtered.length > 0"
            @blur="onBlur()"
            id="{{ $id }}"
            type="text"
            placeholder="Ketik atau pilih dari history..."
            @if($field->isRequired()) required @endif
        @if($field->isDisabled()) disabled @endif
        class="
        block w-full rounded-lg border-0 py-1.5 px-3 text-gray-900
        shadow-sm ring-1 ring-inset ring-gray-300
        placeholder:text-gray-400
        focus:ring-2 focus:ring-inset focus:ring-primary-600
        disabled:cursor-not-allowed disabled:bg-gray-50 disabled:text-gray-500
        sm:text-sm sm:leading-6
        dark:bg-slate-900 dark:text-white dark:ring-gray-700
        dark:placeholder:text-gray-500 dark:focus:ring-primary-500
        " />

        {{-- Dropdown suggestions --}}
        <ul
            x-show="open && filtered.length > 0"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            x-transition:leave="transition ease-in duration-75"
            x-transition:leave-start="opacity-100 translate-y-0"
            x-transition:leave-end="opacity-0 -translate-y-1"
            @mousedown.prevent
            class="
                absolute z-50 mt-1 w-full rounded-lg border border-gray-200
                bg-white shadow-lg
                dark:border-slate-700 dark:bg-slate-800
                max-h-56 overflow-y-auto
            "
            style="display: none;">
            <template x-for="(suggestion, index) in filtered" :key="index">
                <li
                    @click="select(suggestion)"
                    @mouseenter="activeIndex = index"
                    :class="activeIndex === index
                        ? 'bg-primary-50 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400'
                        : 'text-gray-700 dark:text-gray-300'"
                    class="
                        cursor-pointer select-none px-3 py-2 text-sm
                        flex items-center gap-2
                        hover:bg-primary-50 dark:hover:bg-primary-900/30
                    ">
                    {{-- Icon history --}}
                    <svg class="h-3.5 w-3.5 shrink-0 opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span x-text="suggestion"></span>
                </li>
            </template>
        </ul>
    </div>
</x-dynamic-component>