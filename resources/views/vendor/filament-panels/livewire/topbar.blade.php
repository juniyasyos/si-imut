<div class="fi-topbar-ctn">
    @php
        $isRtl = __('filament-panels::layout.direction') === 'rtl';
        $isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
        $isSidebarFullyCollapsibleOnDesktop = filament()->isSidebarFullyCollapsibleOnDesktop();
        $hasTopNavigation = filament()->hasTopNavigation();
        $hasNavigation = filament()->hasNavigation();
        $hasTenancy = filament()->hasTenancy();
        $navigation = $hasNavigation ? filament()->getNavigation() : [];
    @endphp

    <nav class="fi-topbar">
        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_START) }}

        @if ($hasNavigation)
            <x-filament::icon-button color="gray" icon="heroicon-o-bars-3-bottom-left"
                icon-alias="panels::topbar.open-sidebar-button" icon-size="lg"
                :label="__('filament-panels::layout.actions.sidebar.expand.label')" x-cloak x-data="{}"
                x-on:click="$store.sidebar.open()" x-show="! $store.sidebar.isOpen" @class([
                    'fi-topbar-open-sidebar-btn',
                    'lg:hidden' => (!filament()->isSidebarFullyCollapsibleOnDesktop()) || filament()->isSidebarCollapsibleOnDesktop(),
                ]) />

            <x-filament::icon-button color="gray" icon="heroicon-o-x-mark" icon-alias="panels::topbar.close-sidebar-button"
                icon-size="lg" :label="__('filament-panels::layout.actions.sidebar.collapse.label')" x-cloak x-data="{}"
                x-on:click="$store.sidebar.close()" x-show="$store.sidebar.isOpen"
                class="fi-topbar-close-sidebar-btn lg:hidden" />
        @endif

        <div class="fi-topbar-start">
            <div class="lg:hidden">
                @if ($homeUrl = filament()->getHomeUrl())
                    <a {{ \Filament\Support\generate_href_html($homeUrl) }}>
                        <x-logo />
                    </a>
                @else
                    <x-logo />
                @endif
            </div>

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_LOGO_BEFORE) }}

            <div class="hidden items-center lg:flex">
                @if ($isSidebarCollapsibleOnDesktop || $isSidebarFullyCollapsibleOnDesktop)
                    <div x-show="$store.sidebar.isOpen || @js($isSidebarCollapsibleOnDesktop)"
                        class="fi-topbar-collapse-sidebar-btn-ctn flex items-center">
                        @if ($isSidebarCollapsibleOnDesktop)
                            <x-filament::icon-button color="gray" :icon="$isRtl ? 'heroicon-c-bars-3-bottom-right' : 'heroicon-c-bars-3-bottom-left'"
                                :icon-alias="$isRtl ? ['panels::topbar.open-sidebar-button-rtl', 'panels::topbar.open-sidebar-button'] : 'panels::topbar.open-sidebar-button'"
                                icon-size="md" :label="__('filament-panels::layout.actions.sidebar.expand.label')" x-cloak
                                x-data="{}" x-on:click="$store.sidebar.open()" x-show="! $store.sidebar.isOpen"
                                class="fi-topbar-open-collapse-sidebar-btn" />
                        @endif

                        @if ($isSidebarCollapsibleOnDesktop || $isSidebarFullyCollapsibleOnDesktop)
                            <x-filament::icon-button color="gray" :icon="$isRtl ? 'heroicon-c-bars-3-bottom-left' : 'heroicon-c-bars-3-bottom-right'"
                                :icon-alias="$isRtl ? ['panels::topbar.close-sidebar-button-rtl', 'panels::topbar.close-sidebar-button'] : 'panels::topbar.close-sidebar-button'"
                                icon-size="md" :label="__('filament-panels::layout.actions.sidebar.collapse.label')" x-cloak
                                x-data="{}" x-on:click="$store.sidebar.close()" x-show="$store.sidebar.isOpen"
                                class="fi-topbar-close-collapse-sidebar-btn" />
                        @endif
                    </div>
                @endif

                @if ($homeUrl = filament()->getHomeUrl())
                    <a {{ \Filament\Support\generate_href_html($homeUrl) }} class="me-3">
                        <x-logo />
                    </a>
                @else
                    <div class="me-3">
                        <x-logo />
                    </div>
                @endif
            </div>

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_LOGO_AFTER) }}
        </div>

        @if ($hasTopNavigation || (!$hasNavigation))
            @if ($hasTenancy && filament()->hasTenantMenu())
                <x-filament-panels::tenant-menu class="hidden lg:block" />
            @endif

            @if ($hasNavigation)
                <ul class="me-4 hidden items-center gap-x-4 lg:flex">
                    @foreach ($navigation as $group)
                        @php
                            $groupLabel = $group->getLabel();
                            $isGroupActive = $group->isActive();
                            $groupIcon = $group->getIcon();
                        @endphp

                        @if ($groupLabel)
                            <x-filament::dropdown placement="bottom-start" teleport
                                :attributes="\Filament\Support\prepare_inherited_attributes($group->getExtraTopbarAttributeBag())">
                                <x-slot name="trigger">
                                    <x-filament-panels::topbar.item :active="$isGroupActive" :icon="$groupIcon">
                                        {{ $groupLabel }}
                                    </x-filament-panels::topbar.item>
                                </x-slot>

                                @php
                                    $lists = [];

                                    foreach ($group->getItems() as $item) {
                                        if ($childItems = $item->getChildItems()) {
                                            $lists[] = [
                                                $item,
                                                ...$childItems,
                                            ];

                                            $lists[] = [];

                                            continue;
                                        }

                                        if (empty($lists)) {
                                            $lists[] = [$item];

                                            continue;
                                        }

                                        $lists[count($lists) - 1][] = $item;
                                    }

                                    if (empty($lists[count($lists) - 1])) {
                                        array_pop($lists);
                                    }
                                @endphp

                                @foreach ($lists as $list)
                                    <x-filament::dropdown.list>
                                        @foreach ($list as $item)
                                            @php
                                                $isItemActive = $item->isActive();
                                                $itemBadge = $item->getBadge();
                                                $itemBadgeColor = $item->getBadgeColor($itemBadge);
                                                $itemBadgeTooltip = $item->getBadgeTooltip($itemBadge);
                                                $itemUrl = $item->getUrl();
                                                $itemIcon = $isItemActive
                                                    ? ($item->getActiveIcon() ?? $item->getIcon())
                                                    : $item->getIcon();
                                                $shouldItemOpenUrlInNewTab = $item->shouldOpenUrlInNewTab();
                                                $itemExtraAttributes = $item->getExtraAttributeBag();
                                            @endphp

                                            <x-filament::dropdown.list.item :badge="$itemBadge" :badge-color="$itemBadgeColor"
                                                :badge-tooltip="$itemBadgeTooltip" :color="$isItemActive ? 'primary' : 'gray'" :href="$itemUrl"
                                                :icon="$itemIcon" tag="a" :target="$shouldItemOpenUrlInNewTab ? '_blank' : null"
                                                :attributes="\Filament\Support\prepare_inherited_attributes($itemExtraAttributes)">
                                                {{ $item->getLabel() }}
                                            </x-filament::dropdown.list.item>
                                        @endforeach
                                    </x-filament::dropdown.list>
                                @endforeach
                            </x-filament::dropdown>
                        @else
                            @foreach ($group->getItems() as $item)
                                @php
                                    $isItemActive = $item->isActive();
                                @endphp

                                <x-filament-panels::topbar.item :active="$isItemActive" :active-icon="$item->getActiveIcon()"
                                    :badge="$item->getBadge()" :badge-color="$item->getBadgeColor()"
                                    :badge-tooltip="$item->getBadgeTooltip()" :icon="$item->getIcon()"
                                    :should-open-url-in-new-tab="$item->shouldOpenUrlInNewTab()" :url="$item->getUrl()"
                                    :attributes="\Filament\Support\prepare_inherited_attributes($item->getExtraAttributeBag())">
                                    {{ $item->getLabel() }}
                                </x-filament-panels::topbar.item>
                            @endforeach
                        @endif
                    @endforeach
                </ul>
            @endif
        @endif

        <div @if ($hasTenancy) x-persist="topbar.end.tenant-{{ filament()->getTenant()?->getKey() }}" @else
        x-persist="topbar.end" @endif class="ms-auto flex items-center gap-x-4">
            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::GLOBAL_SEARCH_BEFORE) }}

            @if (filament()->isGlobalSearchEnabled())
                @livewire(Filament\Livewire\GlobalSearch::class)
            @endif

            {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::GLOBAL_SEARCH_AFTER) }}

            @if (filament()->auth()->check())
                @if (filament()->hasDatabaseNotifications())
                    @livewire(filament()->getDatabaseNotificationsLivewireComponent(), [
                        'lazy' => filament()->hasLazyLoadedDatabaseNotifications(),
                    ])
                   @endif
                @if (config('iam.enabled', false) || env('USE_SSO', false))
                    @livewire('iam-app-switcher', ['lazy' => true])
                   @endif
                @if (filament()->hasUserMenu() && filament()->getUserMenuPosition() === \Filament\Enums\UserMenuPosition::Topbar)
                    <x-filament-panels::user-menu />
                @endif
            @endif
        </div>

        {{ \Filament\Support\Facades\FilamentView::renderHook(\Filament\View\PanelsRenderHook::TOPBAR_END) }}
    </nav>

    <x-filament-actions::modals />
</div>