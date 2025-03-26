/**
 * Program Details Functionality
 * Handles visualization and interaction on the program details page
 */
document.addEventListener('DOMContentLoaded', function() {
    // Initialize charts if submission data is available
    if (typeof submissionData !== 'undefined' && submissionData.length > 0) {
        initProgressChart();
    }
});

/**
 * Initialize the progress chart visualization
 */
function initProgressChart() {
    const ctx = document.getElementById('progressChart');
    if (!ctx) return;
    
    // Prepare labels and datasets
    const periods = submissionData.map(item => item.period).reverse();
    const targets = submissionData.map(item => item.target).reverse();
    const achievements = submissionData.map(item => item.achievement || 0).reverse();
    
    // Create the chart
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: periods,
            datasets: [
                {
                    label: 'Target',
                    data: targets,
                    borderColor: '#4e73df',
                    backgroundColor: 'rgba(78, 115, 223, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#4e73df',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    fill: false,
                    tension: 0.1
                },
                {
                    label: 'Achievement',
                    data: achievements,
                    borderColor: '#1cc88a',
                    backgroundColor: 'rgba(28, 200, 138, 0.1)',
                    borderWidth: 2,
                    pointBackgroundColor: '#1cc88a',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    fill: false,
                    tension: 0.1
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                title: {
                    display: true,
                    text: 'Program Progress Over Time',
                    padding: {
                        top: 10,
                        bottom: 20
                    },
                    font: {
                        size: 16
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                },
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        padding: 15
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        drawOnChartArea: false
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            // Determine if we're dealing with a numeric value
                            if (!isNaN(parseFloat(value)) && isFinite(value)) {
                                return value;
                            }
                            // For non-numeric values, add a placeholder
                            return '';
                        }
                    }
                }
            }
        }
    });
}
