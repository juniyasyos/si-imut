@php
$user = filament()->auth()->user();
$items = filament()->getUserMenuItems();

$profileItem = $items['profile'] ?? $items['account'] ?? null;
$profileItemUrl = $profileItem?->getUrl();
$profilePage = filament()->getProfilePage();
$hasProfileItem = filament()->hasProfile() || filled($profileItemUrl);

$logoutItem = $items['logout'] ?? null;

$items = \Illuminate\Support\Arr::except($items, ['account', 'logout', 'profile']);
@endphp

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_BEFORE) }}

<x-filament::dropdown
    placement="bottom-end"
    teleport
    :attributes="
        \Filament\Support\prepare_inherited_attributes($attributes)
            ->class(['fi-user-menu'])
    ">
    <x-slot name="trigger">
        <button
            aria-label="{{ __('filament-panels::layout.actions.open_user_menu.label') }}"
            type="button"
            class="shrink-0">
            <x-filament-panels::avatar.user :user="$user" />
        </button>
    </x-slot>

    @if ($profileItem?->isVisible() ?? true)
    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_BEFORE) }}

    @if ($hasProfileItem)
    <x-filament::dropdown.list>
        <x-filament::dropdown.list.item
            :color="$profileItem?->getColor()"
            :icon="$profileItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.profile-item') ?? 'heroicon-m-user-circle'"
            :href="$profileItemUrl ?? filament()->getProfileUrl()"
            :target="($profileItem?->shouldOpenUrlInNewTab() ?? false) ? '_blank' : null"
            tag="a">
            {{ $profileItem?->getLabel() ?? ($profilePage ? $profilePage::getLabel() : null) ?? filament()->getUserName($user) }}
        </x-filament::dropdown.list.item>
    </x-filament::dropdown.list>
    @else
    <x-filament::dropdown.header
        :color="$profileItem?->getColor()"
        :icon="$profileItem?->getIcon() ?? \Filament\Support\Facades\FilamentIcon::resolve('panels::user-menu.profile-item') ?? 'heroicon-m-user-circle'">
        {{ $profileItem?->getLabel() ?? filament()->getUserName($user) }}
    </x-filament::dropdown.header>
    @endif

    {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_PROFILE_AFTER) }}
    @endif

    @if (filament()->hasDarkMode() && (! filament()->hasDarkModeForced()))
    <x-filament::dropdown.list>
        <x-filament-panels::theme-switcher />
    </x-filament::dropdown.list>
    @endif

    <x-filament::dropdown.list>
        @foreach ($items as $key => $item)
        @php
        $itemPostAction = $item->getPostAction();
        @endphp

        <x-filament::dropdown.list.item
            :action="$itemPostAction"
            :color="$item->getColor()"
            :href="$item->getUrl()"
            :icon="$item->getIcon()"
            :method="filled($itemPostAction) ? 'post' : null"
            :tag="filled($itemPostAction) ? 'form' : 'a'"
            :target="$item->shouldOpenUrlInNewTab() ? '_blank' : null">
            {{ $item->getLabel() }}
        </x-filament::dropdown.list.item>
        @endforeach

        {{-- Tambah blok logout custom --}}
        <x-filament::dropdown.list>
            <form method="POST" action="{{ route('logout') }}" x-data>
                @csrf

                <x-filament::dropdown.list.item
                    icon="heroicon-m-arrow-left-on-rectangle"
                    x-on:click.prevent="$root.submit()">
                    {{ __('Logout') }}
                </x-filament::dropdown.list.item>
            </form>
        </x-filament::dropdown.list>

    </x-filament::dropdown.list>
</x-filament::dropdown>

{{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::USER_MENU_AFTER) }}