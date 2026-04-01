@if(config('iam.enabled'))
<div x-data="appSwitcher()" class="relative">

    <!-- Button -->
    <button
        @click="open = !open; if (open && !loaded) loadApplications()"
        class="p-2.5 rounded-xl transition-all duration-200
               text-gray-600 dark:text-gray-300
               hover:bg-gray-100 dark:hover:bg-gray-800
               hover:text-gray-900 dark:hover:text-white
               focus:outline-none focus:ring-2 focus:ring-primary-500"
        title="Switch Application">
        <!-- Grid Icon (lebih jelas untuk multi-app) -->
        <svg xmlns="http://www.w3.org/2000/svg"
            class="w-5 h-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 4h4v4H4V4zm6 0h4v4h-4V4zm6 0h4v4h-4V4z
                     M4 10h4v4H4v-4zm6 0h4v4h-4v-4zm6 0h4v4h-4v-4z
                     M4 16h4v4H4v-4zm6 0h4v4h-4v-4zm6 0h4v4h-4v-4z" />
        </svg>
    </button>

    <!-- Dropdown -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="open = false"
        class="absolute right-0 mt-2 w-screen max-w-sm sm:max-w-md md:max-w-lg rounded-2xl shadow-2xl z-50
               bg-white dark:bg-gray-900
               border border-gray-200 dark:border-gray-800
               mx-4 sm:mx-0">

        <!-- Header -->
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 dark:border-gray-800">
            <p class="text-sm sm:text-base font-semibold text-gray-700 dark:text-gray-200">
                Aplikasi yang Tersedia
            </p>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">
                Beralih antar aplikasi Anda
            </p>
        </div>

        <!-- List -->
        <div class="max-h-96 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
            <!-- Loading State -->
            <div x-show="loading" class="px-4 sm:px-6 py-8 flex items-center justify-center gap-3">
                <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-primary-500"></div>
                <span class="text-sm text-gray-600 dark:text-gray-400">Memuat aplikasi...</span>
            </div>

            <!-- Error State -->
            <div x-show="error && !loading" class="px-4 sm:px-6 py-4 bg-red-50 dark:bg-red-900/20">
                <p class="text-xs sm:text-sm text-red-700 dark:text-red-300">
                    <strong>Error:</strong> <span x-text="error"></span>
                </p>
            </div>

            <!-- Applications List -->
            <template x-for="app in applications" :key="app.id">
                <a
                    :href="app.app_url"
                    class="flex items-start sm:items-center gap-3 sm:gap-4 px-4 sm:px-6 py-4 sm:py-5 group
                           hover:bg-gradient-to-r hover:from-blue-50 hover:to-transparent
                           dark:hover:from-blue-900/20 dark:hover:to-transparent
                           transition-all duration-150 border-l-4 border-transparent hover:border-blue-500">

                    <!-- Icon -->
                    <div class="flex-shrink-0 w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center rounded-xl
                               bg-gradient-to-br from-blue-100 to-blue-50 dark:from-blue-900 dark:to-blue-800
                               group-hover:scale-110 transition-transform">
                        <svg xmlns="http://www.w3.org/2000/svg"
                            class="w-5 h-5 sm:w-6 sm:h-6 text-blue-600 dark:text-blue-300"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 4H5a2 2 0 00-2 2v14a2 2 0 002 2h4m0-18v18m0-18h10a2 2 0 012 2v14a2 2 0 01-2 2h-10" />
                        </svg>
                    </div>

                    <!-- Text Content -->
                    <div class="flex-1 min-w-0">
                        <p class="text-xs sm:text-sm font-semibold text-gray-900 dark:text-white truncate">
                            <span x-text="app.name"></span>
                        </p>
                        <div class="flex flex-wrap gap-2 mt-2 sm:mt-2.5">
                            <span class="inline-block text-xs px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-mono">
                                <span x-text="app.app_key"></span>
                            </span>
                            <span class="inline-block text-xs px-2.5 py-1 rounded-full bg-blue-100 dark:bg-blue-900 text-blue-700 dark:text-blue-300 font-medium">
                                <span x-text="app.role"></span>
                            </span>
                        </div>
                    </div>

                    <!-- Arrow Icon -->
                    <svg xmlns="http://www.w3.org/2000/svg"
                        class="flex-shrink-0 w-4 h-4 sm:w-5 sm:h-5 text-gray-400 dark:text-gray-600 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors group-hover:translate-x-0.5"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </a>
            </template>

            <!-- Empty State -->
            <div x-show="!loading && !error && applications.length === 0" class="px-4 sm:px-6 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                Tidak ada aplikasi yang tersedia
            </div>
        </div>
    </div>
</div>

<script>
    function appSwitcher() {
        return {
            open: false,
            loading: false,
            error: null,
            loaded: false,
            applications: [],

            async loadApplications() {
                this.loading = true;
                this.error = null;

                try {
                    const response = await fetch('/api/user-applications', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }

                    const data = await response.json();

                    if (data.success === false || data.error) {
                        throw new Error(data.error || 'Gagal memuat aplikasi');
                    }

                    this.applications = data.applications || [];
                    this.loaded = true;
                } catch (err) {
                    this.error = err.message;
                    console.error('Error loading applications:', err);
                } finally {
                    this.loading = false;
                }
            }
        }
    }
</script>
@endif