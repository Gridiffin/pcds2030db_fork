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
    
    // Check if this is cumulative data
    const isCumulative = data.datasets && data.datasets.some(dataset => 
        dataset.label && dataset.label.includes('(Cumulative)')
    );
    
    // Default chart configuration
    const defaultConfig = {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: isCumulative,
                        text: isCumulative ? 'Cumulative Values' : 'Values'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Categories'
                    }
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: isCumulative ? 'Outcome Data Chart (Cumulative View)' : 'Outcome Data Chart'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.dataset.label || '';
                            const value = context.parsed.y;
                            
                            // Format based on data type - this could be enhanced to detect column type
                            const formattedValue = typeof value === 'number' ? 
                                value.toLocaleString(undefined, {
                                    minimumFractionDigits: 0,
                                    maximumFractionDigits: 2
                                }) : value;
                            
                            return label + ': ' + formattedValue;
                        }
                    }
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
function prepareChartData(tableData, columns, rows, options = {}) {
    const datasets = [];
    const labels = rows.filter(row => row.type === 'data').map(row => row.label);
    const isCumulative = options.cumulative || false;

    columns.forEach((column, index) => {
        let data = rows.filter(row => row.type === 'data').map(row => {
            return tableData[row.id] ? (tableData[row.id][column.id] || 0) : 0;
        });

        // Apply cumulative transformation if requested
        if (isCumulative) {
            data = calculateCumulativeData(data);
        }

        datasets.push({
            label: column.label + (isCumulative ? ' (Cumulative)' : ''),
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

/**
 * Calculate cumulative data from regular data array
 */
function calculateCumulativeData(data) {
    const cumulative = [];
    let runningTotal = 0;
    
    data.forEach(value => {
        runningTotal += parseFloat(value) || 0;
        cumulative.push(runningTotal);
    });
    
    return cumulative;
}

// Export functions for global access
window.initializeOutcomeChart = initializeOutcomeChart;
window.updateChart = updateChart;
window.changeChartType = changeChartType;
window.downloadChart = downloadChart;
window.prepareChartData = prepareChartData;
window.calculateCumulativeData = calculateCumulativeData;
