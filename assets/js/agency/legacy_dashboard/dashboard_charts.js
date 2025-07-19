/**
 * Dashboard Charts
 * 
 * Initialize charts for the agency dashboard.
 */

let programRatingChart = null; // Updated from programStatusChart to programRatingChart

// Initialize charts when document is ready
document.addEventListener('DOMContentLoaded', function() {
    // DISABLED: Chart initialization is now handled by dashboard_chart.js to avoid conflicts
    // initCharts();
    
    // Re-initialize charts when content updated (period changes)
    document.addEventListener('contentUpdated', function() {
        // DISABLED: Chart initialization is now handled by dashboard_chart.js to avoid conflicts
        // initCharts();
    });
});

/**
 * Initialize or update all dashboard charts
 */
function initCharts() {
    // Program Rating Doughnut Chart (updated from Program Status)
    initProgramRatingChart();
}

/**
 * Initialize or update the program rating chart
 * 
 * @param {Object} newData Optional new data to update the chart
 */
function initProgramRatingChart(newData) {
    const chartCanvas = document.getElementById('programRatingChart') || document.getElementById('programStatusChart'); // Support both for backward compatibility
    
    if (!chartCanvas) return;
    
    // Use the provided data or fall back to the data from PHP
    const data = newData || window.programRatingData || window.programStatusData; // Support both data sources
    
    if (!data) return;
    
    // If chart already exists, destroy it before recreating
    if (programRatingChart) {
        programRatingChart.destroy();
    }
    
    // Create the chart
    programRatingChart = new Chart(chartCanvas, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.data,
                backgroundColor: data.colors,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });
}

/**
 * Update chart data when period changes
 * 
 * @param {Object} chartData New chart data from AJAX
 */
function updateChartData(chartData) {
    if (!chartData) return;
    
    // Update the global program rating data (support both variable names)
    window.programRatingData = chartData;
    window.programStatusData = chartData; // Maintain backward compatibility
    
    // Update the chart
    initProgramRatingChart(chartData);
}

// Backward compatibility functions
function initProgramStatusChart(newData) {
    return initProgramRatingChart(newData);
}

// Export for global access
window.initProgramRatingChart = initProgramRatingChart;
window.initProgramStatusChart = initProgramStatusChart; // Backward compatibility
window.updateChartData = updateChartData;