@php
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Filament\Support\Facades\FilamentView;
@endphp

@props([
'alignment' => Alignment::Start,
'ariaLabelledby' => null,
'autofocus' => \Filament\Support\View\Components\Modal::$isAutofocused,
'closeButton' => \Filament\Support\View\Components\Modal::$hasCloseButton,
'closeByClickingAway' => \Filament\Support\View\Components\Modal::$isClosedByClickingAway,
'closeByEscaping' => \Filament\Support\View\Components\Modal::$isClosedByEscaping,
'closeEventName' => 'close-modal',
'description' => null,
'displayClasses' => 'inline-block',
'extraModalWindowAttributeBag' => null,
'footer' => null,
'footerActions' => [],
'footerActionsAlignment' => Alignment::Start,
'header' => null,
'heading' => null,
'icon' => null,
'iconAlias' => null,
'iconColor' => 'primary',
'id' => null,
'openEventName' => 'open-modal',
'slideOver' => false,
'stickyFooter' => false,
'stickyHeader' => false,
'trigger' => null,
'visible' => true,
'width' => 'sm',
])

@php
$hasDescription = filled($description);
$hasFooter = (! \Filament\Support\is_slot_empty($footer)) || (is_array($footerActions) && count($footerActions)) || (! is_array($footerActions) && (! \Filament\Support\is_slot_empty($footerActions)));
$hasHeading = filled($heading);
$hasIcon = filled($icon);
$hasSlot = ! \Filament\Support\is_slot_empty($slot);

if (! $alignment instanceof Alignment) {
$alignment = filled($alignment) ? (Alignment::tryFrom($alignment) ?? $alignment) : null;
}

if (! $footerActionsAlignment instanceof Alignment) {
$footerActionsAlignment = filled($footerActionsAlignment) ? (Alignment::tryFrom($footerActionsAlignment) ?? $footerActionsAlignment) : null;
}

if (! $width instanceof MaxWidth) {
$width = filled($width) ? (MaxWidth::tryFrom($width) ?? $width) : null;
}

$closeEventHandler = filled($id) ? '$dispatch(' . \Illuminate\Support\Js::from($closeEventName) . ', { id: ' . \Illuminate\Support\Js::from($id) . ' })' : 'close()';
@endphp

<div
    @if ($ariaLabelledby)
    aria-labelledby="{{ $ariaLabelledby }}"
    @elseif ($heading)
    aria-labelledby="{{ "{$id}.heading" }}"
    @endif
    aria-modal="true"
    role="dialog"
    x-data="{
        isOpen: false,

        livewire: null,

        close: function () {
            this.isOpen = false

            this.$refs.modalContainer.dispatchEvent(
                new CustomEvent('modal-closed', { detail: { id: '{{ $id }}' } }),
            )
        },

        open: function () {
            this.$nextTick(() => {
                this.isOpen = true

                @if (FilamentView::hasSpaMode())
                    this.$dispatch('ax-modal-opened')
                @endif

                this.$refs.modalContainer.dispatchEvent(
                    new CustomEvent('modal-opened', { detail: { id: '{{ $id }}' } }),
                )
            })
        },
    }"
    @if ($id)
    x-on:{{ $closeEventName }}.window="if ($event.detail.id === '{{ $id }}') close()"
    x-on:{{ $openEventName }}.window="if ($event.detail.id === '{{ $id }}') open()"
    data-fi-modal-id="{{ $id }}"
    @endif
    x-trap.noscroll{{ $autofocus ? '' : '.noautofocus' }}="isOpen"
    x-bind:class="{
        'fi-modal-open': isOpen,
    }"
    @class([ 'fi-modal' , 'fi-width-screen'=> $width === MaxWidth::Screen,
    $displayClasses,
    ])
    >
    @if ($trigger)
    <div
        @if (! $trigger->attributes->get('disabled'))
        x-on:click="open"
        @endif
        {{ $trigger->attributes->class(['fi-modal-trigger flex cursor-pointer']) }}
        >
        {{ $trigger }}
    </div>
    @endif

    <div x-cloak x-show="isOpen">
        <div
            aria-hidden="true"
            x-show="isOpen"
            x-transition:enter="transition-opacity ease-out duration-400"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-in duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @class([ 'fi-modal-close-overlay fixed inset-0 z-40' , 'backdrop-blur-md backdrop-saturate-150'=> $slideOver,
            'bg-gray-950/50 dark:bg-gray-950/75' => ! $slideOver,
            ])
            @if ($slideOver)
            style="background: linear-gradient(to bottom right, rgba(17, 24, 39, 0.7), rgba(17, 24, 39, 0.8), rgba(0, 0, 0, 0.9));"
            @endif
            ></div>

        <div
            @class([ 'fixed inset-0 z-40' , 'overflow-y-auto'=> ! ($slideOver || ($width === MaxWidth::Screen)),
            'cursor-pointer' => $closeByClickingAway,
            'flex max-w-full pl-10 sm:pl-16 pointer-events-none' => $slideOver,
            ])
            @if ($slideOver)
            style="position: fixed !important; top: 0 !important; right: 0 !important; bottom: 0 !important; left: auto !important;"
            @endif
            >
            <div
                x-ref="modalContainer"
                @if ($closeByClickingAway)
                {{-- Ensure that the click element is not triggered from a user selecting text inside an input. --}}
                x-on:click.self="
                        document.activeElement.selectionStart === undefined &&
                            document.activeElement.selectionEnd === undefined &&
                            {{ $closeEventHandler }}
                    "
                @endif
                {{
                    $attributes->class([
                        'relative grid min-h-full grid-rows-[1fr_auto_1fr] justify-items-center sm:grid-rows-[1fr_auto_3fr]',
                        'p-4' => ! ($slideOver || ($width === MaxWidth::Screen)),
                    ])
                }}>
                <div
                    x-data="{ isShown: false }"
                    x-init="
                        $nextTick(() => {
                            isShown = isOpen
                            $watch('isOpen', () => (isShown = isOpen))
                        })
                    "
                    @if ($closeByEscaping)
                    x-on:keydown.window.escape="{{ $closeEventHandler }}"
                    @endif
                    x-show="isShown"
                    @if ($width===MaxWidth::Screen)
                    x-transition:enter="duration-300"
                    x-transition:leave="duration-300"
                    @elseif ($slideOver)
                    x-transition:enter="transform transition ease-out duration-500"
                    x-transition:enter-start="translate-x-full opacity-0"
                    x-transition:enter-end="translate-x-0 opacity-100"
                    x-transition:leave="transform transition ease-in duration-400"
                    x-transition:leave-start="translate-x-0 opacity-100"
                    x-transition:leave-end="translate-x-full opacity-0"
                    @else
                    x-transition:enter="duration-300"
                    x-transition:leave="duration-300"
                    x-transition:enter-start="scale-95 opacity-0"
                    x-transition:enter-end="scale-100 opacity-100"
                    x-transition:leave-start="scale-100 opacity-100"
                    x-transition:leave-end="scale-95 opacity-0"
                    @endif
                    @if (filled($id))
                    wire:key="{{ isset($this) ? "{$this->getId()}." : '' }}modal.{{ $id }}.window"
                    @endif
                    {{
                        ($extraModalWindowAttributeBag ?? new \Illuminate\View\ComponentAttributeBag)->class([
                            'fi-modal-window pointer-events-auto relative row-start-2 flex w-full cursor-default flex-col shadow-xl ring-1',
                            'bg-white ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10' => ! $slideOver,
                            'fi-modal-slide-over-window ms-auto overflow-y-auto backdrop-blur-xl bg-gray-50 dark:bg-slate-800/80 ring-gray-200/50 dark:ring-gray-700/50' => $slideOver,
                            // Using an arbitrary value instead of the h-dvh class that was added in Tailwind CSS v3.4.0
                            // to ensure compatibility with custom themes that may use an older version of Tailwind CSS.
                            'h-[100dvh]' => $slideOver || ($width === MaxWidth::Screen),
                            'mx-auto rounded-xl' => ! ($slideOver || ($width === MaxWidth::Screen)),
                            'hidden' => ! $visible,
                            match ($width) {
                                MaxWidth::ExtraSmall => 'max-w-xs',
                                MaxWidth::Small => 'max-w-sm',
                                MaxWidth::Medium => 'max-w-md',
                                MaxWidth::Large => 'max-w-lg',
                                MaxWidth::ExtraLarge => 'max-w-xl',
                                MaxWidth::TwoExtraLarge => 'max-w-2xl',
                                MaxWidth::ThreeExtraLarge => 'max-w-3xl',
                                MaxWidth::FourExtraLarge => 'max-w-4xl',
                                MaxWidth::FiveExtraLarge => 'max-w-5xl',
                                MaxWidth::SixExtraLarge => 'max-w-6xl',
                                MaxWidth::SevenExtraLarge => 'max-w-7xl',
                                MaxWidth::Full => 'max-w-full',
                                MaxWidth::MinContent => 'max-w-min',
                                MaxWidth::MaxContent => 'max-w-max',
                                MaxWidth::FitContent => 'max-w-fit',
                                MaxWidth::Prose => 'max-w-prose',
                                MaxWidth::ScreenSmall => 'max-w-screen-sm',
                                MaxWidth::ScreenMedium => 'max-w-screen-md',
                                MaxWidth::ScreenLarge => 'max-w-screen-lg',
                                MaxWidth::ScreenExtraLarge => 'max-w-screen-xl',
                                MaxWidth::ScreenTwoExtraLarge => 'max-w-screen-2xl',
                                MaxWidth::Screen => 'fixed inset-0',
                                default => $width,
                            },
                        ])
                    }}
                    @if ($slideOver)
                    style="box-shadow: 0 0 80px rgba(0,0,0,0.5);"
                    @endif>
                    @if ($heading || $header)
                    <div
                        @if (filled($id))
                        wire:key="{{ isset($this) ? "{$this->getId()}." : '' }}modal.{{ $id }}.header"
                        @endif
                        @class([ 'fi-modal-header flex px-6 pt-6' , 'pb-6'=> (! $hasSlot) && (! $hasFooter),
                        'fi-sticky sticky top-0 z-10 border-b border-gray-200 bg-white pb-6 dark:border-white/10 dark:bg-gray-900' => $stickyHeader && ! $slideOver,
                        'fi-sticky sticky top-0 z-10 border-b border-blue-400/30 pb-6' => $stickyHeader && $slideOver,
                        'rounded-t-xl' => $stickyHeader && ! ($slideOver || ($width === MaxWidth::Screen)),
                        'relative py-7 shadow-2xl bg-blue-600 dark:bg-blue-700' => $slideOver,
                        match ($alignment) {
                        Alignment::Start, Alignment::Left => 'gap-x-5',
                        Alignment::Center => 'flex-col',
                        default => null,
                        },
                        'items-center' => $hasIcon && $hasHeading && (! $hasDescription) && in_array($alignment, [Alignment::Start, Alignment::Left]),
                        ])

                        >
                        @if ($slideOver)
                        <!-- Animated decorative background pattern -->
                        <div class="absolute inset-0 opacity-[0.08]">
                            <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg">
                                <defs>
                                    <pattern id="grid" width="32" height="32" patternUnits="userSpaceOnUse">
                                        <circle cx="16" cy="16" r="1.5" fill="currentColor" />
                                    </pattern>
                                </defs>
                                <rect width="100%" height="100%" fill="url(#grid)" />
                            </svg>
                        </div>
                        <!-- Gradient overlay for depth -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/10 via-transparent to-white/5 dark:from-black/20 dark:to-black/5"></div>
                        @endif

                        @if ($closeButton)
                        <div
                            @class([ 'absolute z-50' , 'end-4 top-4'=> ! $slideOver,
                            'end-6 top-6' => $slideOver,
                            ])
                            >
                            @if ($slideOver)
                            <button
                                type="button"
                                x-on:click="{{ $closeEventHandler }}"
                                class="relative flex h-10 w-10 items-center justify-center rounded-full bg-gray-900/10 dark:bg-white/10 text-gray-900 dark:text-white backdrop-blur-sm transition-all duration-200 hover:bg-gray-900/20 dark:hover:bg-white/20 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-900/50 dark:focus:ring-white/50">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            @else
                            <x-filament::icon-button
                                color="gray"
                                icon="heroicon-o-x-mark"
                                icon-alias="modal.close-button"
                                icon-size="lg"
                                :label="__('filament::components/modal.actions.close.label')"
                                tabindex="-1"
                                x-on:click="{{ $closeEventHandler }}"
                                class="fi-modal-close-btn" />
                            @endif
                        </div>
                        @endif

                        @if ($header)
                        {{ $header }}
                        @else
                        @if ($hasIcon)
                        <div
                            @class([ 'mb-5 flex items-center justify-center'=> $alignment === Alignment::Center,
                            ])
                            >
                            <div
                                @class([ 'rounded-full' ,
                                match ($iconColor) { 'gray'=> 'bg-gray-100 dark:bg-gray-500/20',
                                default => 'fi-color-custom bg-custom-100 dark:bg-custom-500/20',
                                },
                                is_string($iconColor) ? "fi-color-{$iconColor}" : null,
                                match ($alignment) {
                                Alignment::Start, Alignment::Left => 'p-2',
                                Alignment::Center => 'p-3',
                                default => null,
                                },
                                ])
                                @style([
                                \Filament\Support\get_color_css_variables(
                                $iconColor,
                                shades: [100, 400, 500, 600],
                                alias: 'modal.icon',
                                ) => $iconColor !== 'gray',
                                ])
                                >
                                <x-filament::icon
                                    :alias="$iconAlias"
                                    :icon="$icon"
                                    @class([ 'fi-modal-icon h-6 w-6' ,
                                    match ($iconColor) { 'gray'=> 'text-gray-500 dark:text-gray-400',
                                    default => 'text-custom-600 dark:text-custom-400',
                                    },
                                    ])
                                    />
                            </div>
                        </div>
                        @endif

                        <div
                            @class([ 'text-center'=> $alignment === Alignment::Center,
                            'relative flex-1 min-w-0' => $slideOver,
                            ])
                            >
                            <x-filament::modal.heading
                                @class([ 'me-6'=> $closeButton && ((! $hasIcon) || in_array($alignment, [Alignment::Start, Alignment::Left])) && ! $slideOver,
                                'ms-6' => $closeButton && (! $hasIcon) && ($alignment === Alignment::Center) && ! $slideOver,
                                'text-xl font-bold truncate drop-shadow-lg text-gray-900 dark:text-white' => $slideOver,
                                ])
                                >
                                {{ $heading }}
                            </x-filament::modal.heading>

                            @if ($hasDescription)
                            <x-filament::modal.description
                                @class([ 'mt-2' , 'text-sm font-medium drop-shadow-md text-gray-700 dark:text-gray-200'=> $slideOver,
                                ])
                                >
                                {{ $description }}
                            </x-filament::modal.description>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif

                    @if ($hasSlot)
                    <div
                        @if (filled($id))
                        wire:key="{{ isset($this) ? "{$this->getId()}." : '' }}modal.{{ $id }}.content"
                        @endif
                        @class([ 'fi-modal-content flex flex-col gap-y-4 py-6' , 'flex-1'=> ($width === MaxWidth::Screen) || $slideOver,
                        'pe-6 ps-[5.25rem]' => $hasIcon && ($alignment === Alignment::Start) && (! $stickyHeader) && ! $slideOver,
                        'px-6' => ! ($hasIcon && ($alignment === Alignment::Start) && (! $stickyHeader)) || $slideOver,
                        'overflow-y-auto backdrop-blur-sm' => $slideOver,
                        ])

                        >
                        {{ $slot }}
                    </div>
                    @endif

                    @if ($hasFooter)
                    <div
                        @if (filled($id))
                        wire:key="{{ isset($this) ? "{$this->getId()}." : '' }}modal.{{ $id }}.footer"
                        @endif
                        @class([ 'fi-modal-footer w-full' , 'pe-6 ps-[5.25rem]'=> $hasIcon && ($alignment === Alignment::Start) && ($footerActionsAlignment !== Alignment::Center) && (! $stickyFooter),
                        'px-6' => ! ($hasIcon && ($alignment === Alignment::Start) && ($footerActionsAlignment !== Alignment::Center) && (! $stickyFooter)),
                        'fi-sticky sticky bottom-0 border-t border-gray-200 bg-white py-5 dark:border-white/10 dark:bg-gray-900' => $stickyFooter && ! $slideOver,
                        'fi-sticky sticky bottom-0 border-t border-blue-400/30 py-5' => $stickyFooter && $slideOver,
                        'rounded-b-xl' => $stickyFooter && ! ($slideOver || ($width === MaxWidth::Screen)),
                        'pb-6' => ! $stickyFooter,
                        'mt-6' => (! $stickyFooter) && (! $hasSlot),
                        'mt-auto' => $slideOver,
                        ])
                        >
                        @if (! \Filament\Support\is_slot_empty($footer))
                        {{ $footer }}
                        @else
                        <div
                            @class([ 'fi-modal-footer-actions gap-3' ,
                            match ($footerActionsAlignment) {
                            Alignment::Start, Alignment::Left=> 'flex flex-wrap items-center',
                            Alignment::Center => 'flex flex-col-reverse sm:grid sm:grid-cols-[repeat(auto-fit,minmax(0,1fr))]',
                            Alignment::End, Alignment::Right => 'flex flex-row-reverse flex-wrap items-center',
                            default => null,
                            },
                            ])
                            >
                            @if (is_array($footerActions))
                            @foreach ($footerActions as $action)
                            {{ $action }}
                            @endforeach
                            @else
                            {{ $footerActions }}
                            @endif
                        </div>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>