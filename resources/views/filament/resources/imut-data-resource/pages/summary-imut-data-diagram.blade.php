<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Tabs Navigation -->
        <div x-data="summaryDiagram()" class="space-y-4">
            <!-- Tab Headers -->
            <div class="border-b border-gray-200 dark:border-gray-700">
                <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                    <button
                        @click="activeTab = 'summary'; loadSummary()"
                        :class="activeTab === 'summary' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <span class="flex items-center gap-2">
                            @svg("heroicon-o-chart-bar", "w-5 h-5")
                            Summary Data
                        </span>
                    </button>
                    <button
                        @click="activeTab = 'notes'; loadNotes()"
                        :class="activeTab === 'notes' ? 'border-primary-500 text-primary-600 dark:text-primary-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                        <span class="flex items-center gap-2">
                            @svg("heroicon-o-document-text", "w-5 h-5")
                            Catatan
                        </span>
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div>
                <!-- Summary Tab -->
                <div x-show="activeTab === 'summary'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div x-show="loading" class="text-center py-8">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Loading summary data...</p>
                            </div>
                            <div x-show="!loading">
                                <!-- Summary content will be populated by Alpine.js -->
                                <div x-html="summaryHtml"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notes Tab -->
                <div x-show="activeTab === 'notes'" x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 transform scale-95"
                    x-transition:enter-end="opacity-100 transform scale-100">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <div x-show="loadingNotes" class="text-center py-8">
                                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-primary-600"></div>
                                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Loading notes...</p>
                            </div>
                            <div x-show="!loadingNotes">
                                <!-- Notes content will be populated by Alpine.js -->
                                <div x-html="notesHtml"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function summaryDiagram() {
            return {
                activeTab: 'summary',
                loading: true,
                loadingNotes: false,
                summaryHtml: '',
                notesHtml: '',
                imutDataId: {
                    {
                        $data['imutDataId']
                    }
                },

                async init() {
                    await this.loadSummary();
                },

                async loadSummary() {
                    this.activeTab = 'summary';
                    this.loading = true;
                    this.loadingNotes = false;
                    try {
                        const response = await fetch(`/api/imut-data/${this.imutDataId}/summary`);
                        if (!response.ok) throw new Error('Failed to fetch summary');
                        const data = await response.json();
                        this.summaryHtml = data.html;
                    } catch (error) {
                        console.error('Error loading summary:', error);
                        this.summaryHtml = '<p class="text-red-500">Error loading summary data.</p>';
                    } finally {
                        this.loading = false;
                    }
                },

                async loadNotes() {
                    this.activeTab = 'notes';
                    this.loading = false;
                    this.loadingNotes = true;
                    try {
                        const response = await fetch(`/api/imut-data/${this.imutDataId}/notes`);
                        if (!response.ok) throw new Error('Failed to fetch notes');
                        const data = await response.json();
                        this.notesHtml = data.html;
                    } catch (error) {
                        console.error('Error loading notes:', error);
                        this.notesHtml = '<p class="text-red-500">Error loading notes.</p>';
                    } finally {
                        this.loadingNotes = false;
                    }
                }
            }
        }
    </script>
</x-filament-panels::page>