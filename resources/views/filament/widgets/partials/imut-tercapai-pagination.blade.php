@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-wrap items-center justify-between gap-3">
        {{-- Tombol Pagination di Sebelah Kiri --}}
        <div class="flex flex-wrap items-center gap-1">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span aria-disabled="true"
                      aria-label="{{ __('pagination.previous') }}"
                      class="inline-flex cursor-not-allowed items-center rounded-lg border border-gray-200 bg-gray-100 text-xs font-semibold text-gray-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500"
                      style="padding: 0.375rem 0.625rem;">
                    @svg('heroicon-m-chevron-left', 'h-4 w-4')
                </span>
            @else
                <button type="button"
                        wire:click="previousPage('{{ $paginator->getPageName() }}')"
                        wire:loading.attr="disabled"
                        dusk="previousPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}"
                        aria-label="{{ __('pagination.previous') }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700"
                        style="padding: 0.375rem 0.625rem;">
                    @svg('heroicon-m-chevron-left', 'h-4 w-4')
                </button>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span aria-disabled="true"
                          class="inline-flex cursor-default items-center text-xs font-semibold text-gray-500 dark:text-gray-400"
                          style="padding: 0.375rem 0.5rem;">
                        {{ $element }}
                    </span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        <span wire:key="paginator-{{ $paginator->getPageName() }}-page{{ $page }}">
                            @if ($page == $paginator->currentPage())
                                <span aria-current="page"
                                      class="inline-flex cursor-default items-center rounded-lg border border-blue-600 bg-blue-600 font-mono text-xs font-bold text-white shadow-sm"
                                      style="background-color: #2563eb; border-color: #2563eb; color: #ffffff; padding: 0.375rem 0.75rem;">
                                    {{ $page }}
                                </span>
                            @else
                                <button type="button"
                                        wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')"
                                        wire:loading.attr="disabled"
                                        aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white font-mono text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700"
                                        style="padding: 0.375rem 0.75rem;">
                                    {{ $page }}
                                </button>
                            @endif
                        </span>
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button type="button"
                        wire:click="nextPage('{{ $paginator->getPageName() }}')"
                        wire:loading.attr="disabled"
                        dusk="nextPage{{ $paginator->getPageName() == 'page' ? '' : '.' . $paginator->getPageName() }}"
                        aria-label="{{ __('pagination.next') }}"
                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white text-xs font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 hover:text-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-200 dark:hover:bg-slate-700"
                        style="padding: 0.375rem 0.625rem;">
                    @svg('heroicon-m-chevron-right', 'h-4 w-4')
                </button>
            @else
                <span aria-disabled="true"
                      aria-label="{{ __('pagination.next') }}"
                      class="inline-flex cursor-not-allowed items-center rounded-lg border border-gray-200 bg-gray-100 text-xs font-semibold text-gray-400 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-500"
                      style="padding: 0.375rem 0.625rem;">
                    @svg('heroicon-m-chevron-right', 'h-4 w-4')
                </span>
            @endif
        </div>

        {{-- Info Ringkasan Halaman --}}
        <div class="text-xs text-gray-600 dark:text-gray-400">
            Menampilkan
            <span class="font-bold text-gray-900 dark:text-white">{{ $paginator->firstItem() }}</span>
            -
            <span class="font-bold text-gray-900 dark:text-white">{{ $paginator->lastItem() }}</span>
            dari
            <span class="font-bold text-gray-900 dark:text-white">{{ $paginator->total() }}</span>
            unit kerja
        </div>
    </nav>
@endif
