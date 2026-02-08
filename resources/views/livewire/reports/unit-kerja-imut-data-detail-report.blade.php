<div>
    {{ $this->table }}
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('openUrlInNewTab', (url) => {
            window.open(url, '_blank');
        });
    });
</script>