<div>
    @if (config('iam.enabled'))
    <div class="fi-iam-app-switcher relative">
        <!-- Toggle Button -->
        <button
            wire:click="toggleOpen"
            type="button"
            class="inline-flex items-center gap-2 px-3 py-2.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors text-gray-700 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white font-medium"
            title="Aplikasi IAM">
            <!-- Grid Icon -->
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2h-4a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h4a2 2 0 012 2v4a2 2 0 01-2 2h-4a2 2 0 01-2-2v-4z" />
            </svg>
            <span class="text-sm font-medium hidden sm:inline">Aplikasi</span>
        </button>

        <!-- Dropdown Menu -->
        @if($open)
        <div class="absolute right-0 z-50 mt-2 w-screen max-w-2xl sm:max-w-xl md:max-w-2xl origin-top-right rounded-xl bg-white shadow-2xl ring-1 ring-gray-950/5 dark:bg-slate-900 dark:ring-white/10">

            <div class="border-b border-gray-200 px-6 py-5 dark:border-slate-700">
                <p class="text-base font-semibold text-gray-900 dark:text-white">
                    Accessible Applications
                </p>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                    You have access to {{ count($applications) }} application{{ count($applications) !== 1 ? 's' : '' }}
                </p>
            </div>

            <div class="max-h-[500px] overflow-y-auto">
                <!-- Loading State -->
                @if($loading)
                <div class="px-6 py-8 flex items-center justify-center gap-3">
                    <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-primary-500"></div>
                    <span class="text-base text-gray-600 dark:text-gray-400">Loading applications...</span>
                </div>
                @endif

                @if($error)
                <div class="px-6 py-4 border-b border-red-100 bg-red-50 dark:border-red-900/50 dark:bg-red-950/30">
                    <p class="text-sm font-medium text-red-700 dark:text-red-300">{{ $error }}</p>
                </div>
                @endif

                <!-- Applications List -->
                @forelse ($applications as $app)
                <button
                    wire:click="navigateTo('{{ $app['app_url'] }}')"
                    type="button"
                    class="w-full px-5 py-5 border-b border-gray-100 dark:border-slate-800 last:border-b-0 hover:bg-gray-50 dark:hover:bg-gray-800/70 transition-all duration-150 group text-left">

                    <!-- MAIN LAYOUT -->
                    <div class="flex items-start gap-4">

                        <!-- LOGO -->
                        <div class="flex-shrink-0 w-12 h-12 rounded-lg bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-sm overflow-hidden">
                            @if($app['logo_url'])
                            <img src="{{ $app['logo_url'] }}" alt="{{ $app['name'] }}" class="w-full h-full object-cover">
                            @else
                            <!-- Default SVG Icon -->
                            <svg class="w-6 h-6 text-white" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" />
                            </svg>
                            @endif
                        </div>

                        <!-- CONTENT -->
                        <div class="flex-1 min-w-0">

                            <!-- STATUS BADGE -->
                            @if($app['status'])
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-xs px-2.5 py-1 rounded-full @if($app['status'] === 'active') bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 @else bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 @endif font-semibold">
                                    {{ ucfirst($app['status']) }}
                                </span>
                            </div>
                            @endif

                            <!-- TITLE (Full Length) -->
                            <p class="text-sm font-bold text-gray-900 dark:text-white mb-1.5 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition line-clamp-2 leading-snug">
                                {{ $app['name'] }}
                            </p>

                            <!-- DESCRIPTION -->
                            @if($app['description'])
                            <p class="text-xs text-gray-600 dark:text-gray-400 line-clamp-2 mb-3">
                                {{ $app['description'] }}
                            </p>
                            @endif

                            <!-- ROLES BADGES -->
                            @if(!empty($app['roles']))
                            <div class="flex items-center flex-wrap gap-2 mb-3">
                                @foreach($app['roles'] as $role)
                                <span class="inline-flex items-center text-xs px-2.5 py-1 rounded-md bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 font-semibold">
                                    {{ $role['name'] }}
                                    @if($role['description'])
                                    <span class="ml-1 text-gray-600 dark:text-gray-400">— {{ $role['description'] }}</span>
                                    @endif
                                </span>
                                @endforeach
                            </div>
                            @endif
                        </div>

                        <!-- ARROW -->
                        <div class="flex-shrink-0 flex items-center pt-1">
                            <svg class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition"
                                xmlns="http://www.w3.org/2000/svg"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </div>

                    </div>
                </button>
                @empty
                @if(!$loading)
                <div class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-base font-medium text-gray-900 dark:text-white">No applications available</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">You don't have access to any applications yet</p>
                </div>
                @endif
                @endforelse
            </div>
        </div>

        <!-- Overlay -->
        @if($open)
        <div class="fixed inset-0 z-40" wire:click="toggleOpen"></div>
        @endif
        @endif
    </div>
    @endif
</div>