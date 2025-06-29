/**
 * Chart Manager for Outcomes
 * Handles chart functionality for both view and edit modes
 */

// Global chart instance
let outcomeChart = null;

/**
 * Initialize chart with data
 */
function initializeOutcomeChart(data, config = {}) {
    const canvas = document.getElementById('metricChart');
    if (!canvas) {
        console.warn('Chart canvas not found');
        return;
    }

    // Destroy existing chart
    if (outcomeChart) {
        outcomeChart.destroy();
    }

    const ctx = canvas.getContext('2d');
    
    // Default chart configuration
    const defaultConfig = {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'Outcome Data Chart'
                }
            }
        }
    };

    // Merge with provided config
    const chartConfig = { ...defaultConfig, ...config };

    try {
        if (typeof Chart !== 'undefined') {
            outcomeChart = new Chart(ctx, chartConfig);
        } else {
            console.warn('Chart.js library not loaded');
        }
    } catch (error) {
        console.error('Error creating chart:', error);
    }
}

/**
 * Update chart with new data
 */
function updateChart(newData) {
    if (outcomeChart) {
        outcomeChart.data = newData;
        outcomeChart.update();
    }
}

/**
 * Change chart type
 */
function changeChartType(type) {
    if (outcomeChart) {
        outcomeChart.config.type = type;
        outcomeChart.update();
    }
}

/**
 * Download chart as image
 */
function downloadChart() {
    if (outcomeChart) {
        const link = document.createElement('a');
        link.download = 'outcome-chart.png';
        link.href = outcomeChart.toBase64Image();
        link.click();
    }
}

/**
 * Prepare chart data from table data
 */
function prepareChartData(tableData, columns, rows) {
    const datasets = [];
    const labels = rows.filter(row => row.type === 'data').map(row => row.label);

    columns.forEach((column, index) => {
        const data = rows.filter(row => row.type === 'data').map(row => {
            return tableData[row.id] ? (tableData[row.id][column.id] || 0) : 0;
        });

        datasets.push({
            label: column.label,
            data: data,
            borderColor: `hsl(${index * 60}, 70%, 50%)`,
            backgroundColor: `hsla(${index * 60}, 70%, 50%, 0.1)`,
            fill: false
        });
    });

    return {
        labels: labels,
        datasets: datasets
    };
}

// Export functions for global access
window.initializeOutcomeChart = initializeOutcomeChart;
window.updateChart = updateChart;
window.changeChartType = changeChartType;
window.downloadChart = downloadChart;
window.prepareChartData = prepareChartData;
