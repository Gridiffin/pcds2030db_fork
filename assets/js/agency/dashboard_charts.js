/**
 * Agency Dashboard Charts
 * Creates charts for agency dashboard
 */
document.addEventListener('DOMContentLoaded', function() {
    // Create program status chart if data and element exist
    if (typeof programStatusData !== 'undefined' && document.getElementById('programStatusChart')) {
        const ctx = document.getElementById('programStatusChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: programStatusData.labels,
                datasets: [{
                    data: programStatusData.data,
                    backgroundColor: programStatusData.colors,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            boxWidth: 12,
                            usePointStyle: true,
                            pointStyle: 'circle'
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.raw || 0;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${label}: ${value} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
});
