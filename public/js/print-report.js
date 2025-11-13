/**
 * Print Report Functions
 * Handle print and back functionality for report preview
 */

document.addEventListener('DOMContentLoaded', function() {
    console.log('Print Report JS loaded');

    // Print button handler
    const printBtn = document.getElementById('printBtn');
    if (printBtn) {
        printBtn.addEventListener('click', function() {
            window.print();
        });
    }

    // Back button handler
    const backBtn = document.getElementById('backBtn');
    if (backBtn) {
        backBtn.addEventListener('click', function() {
            window.history.back();
        });
    }

    // Optional: Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + P for print
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            window.print();
        }

        // ESC to go back
        if (e.key === 'Escape') {
            window.history.back();
        }
    });

    // Handle print events untuk ApexCharts
    window.addEventListener('beforeprint', function() {
        console.log('Before print triggered');

        // Pastikan chart sudah fully rendered
        if (window.ApexCharts && window.chartInstance) {
            try {
                // Refresh chart untuk memastikan render sempurna
                window.chartInstance.render();
            } catch (e) {
                console.error('Error rendering chart before print:', e);
            }
        }
    });

    window.addEventListener('afterprint', function() {
        console.log('After print triggered');
    });

    // Optional: Show print dialog automatically after page load
    // Uncomment if needed
    // setTimeout(() => {
    //     window.print();
    // }, 500);
});
