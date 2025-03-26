/**
 * Dashboard Charts
 * 
 * Initialize charts for the agency dashboard.
 */

let programStatusChart = null;

// Initialize charts when document is ready
document.addEventListener('DOMContentLoaded', function() {
    initCharts();
    
    // Re-initialize charts when content updated (period changes)
    document.addEventListener('contentUpdated', function() {
        initCharts();
    });
});

/**
 * Initialize or update all dashboard charts
 */
function initCharts() {
    // Program Status Doughnut Chart
    initProgramStatusChart();
}

/**
 * Initialize or update the program status chart
 * 
 * @param {Object} newData Optional new data to update the chart
 */
function initProgramStatusChart(newData) {
    const chartCanvas = document.getElementById('programStatusChart');
    
    if (!chartCanvas) return;
    
    // Use the provided data or fall back to the data from PHP
    const data = newData || window.programStatusData;
    
    if (!data) return;
    
    // If chart already exists, destroy it before recreating
    if (programStatusChart) {
        programStatusChart.destroy();
    }
    
    // Create the chart
    programStatusChart = new Chart(chartCanvas, {
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
                    display: false
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
    
    // Update the global program status data
    window.programStatusData = chartData;
    
    // Update the chart
    initProgramStatusChart(chartData);
}
