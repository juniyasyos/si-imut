<!-- Share Button Example -->
<template x-if="getActionButton(indicator.id, selectedDate).state !== 'disabled'">
    <button
        @click="copySlideOverUrl(indicator.id, selectedDate)"
        class="inline-flex items-center gap-2 px-3 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-lg transition-colors"
        title="Copy Link">
        @svg("heroicon-m-share", "w-4 h-4")
    </button>
</template>

<script>
    function copySlideOverUrl(indicatorId, date) {
        const baseUrl = window.location.origin + window.location.pathname;
        const shareUrl = baseUrl + '?indicator_id=' + indicatorId + '&date=' + date;

        navigator.clipboard.writeText(shareUrl).then(() => {
            // Show success notification
            console.log('URL copied to clipboard:', shareUrl);
            // You can add Filament notification here
        }).catch(() => {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = shareUrl;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
        });
    }
</script>