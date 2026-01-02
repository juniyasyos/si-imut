<x-filament-panels::page>
    <style>
        /* Page-scoped styling for preview form labels */
        .fi-fo-field-wrp-label,
        .fi-fo-field-wrp-label span {
            font-size: 2vh !important;
            /* slightly larger */
            font-weight: 600 !important;
            /* semibold */
            color: rgb(15, 23, 42) !important;
            /* slate-900 */
            line-height: 1.2 !important;
        }

        .fi-form-wrapper .filament-forms-field-wrapper .filament-forms-field-description,
        .fi-form-wrapper .filament-forms-component-description {
            color: rgb(71, 85, 105);
            /* slate-600 for helper text */
        }
    </style>

    <div class="fi-form-wrapper">
        {{ $this->form }}
    </div>
</x-filament-panels::page>