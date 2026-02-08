document.addEventListener('DOMContentLoaded', function () {
    // Wait a bit for chartDataMap to be ready
    setTimeout(function () {
        if (typeof window.chartDataMap === 'undefined') {
            console.error('chartDataMap not found');
            return;
        }

        const charts = document.querySelectorAll('[data-chart]');

        charts.forEach(canvas => {
            const chartId = canvas.id;
            const chartData = window.chartDataMap && window.chartDataMap[chartId];

            if (chartData) {
                try {
                    new Chart(canvas, {
                        type: 'line',
                        data: chartData,
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'top',
                                },
                            },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    max: 100,
                                    ticks: {
                                        callback: function (value) {
                                            return value + '%';
                                        }
                                    }
                                }
                            }
                        }
                    });
                } catch (e) {
                    console.error('Error initializing chart for ' + chartId, e);
                }
            }
        });
    }, 100);
});
