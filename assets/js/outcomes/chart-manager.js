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
    console.log('initializeOutcomeChart called with:', { data, config });
    
    // Check if Chart.js is available
    if (typeof Chart === 'undefined') {
        console.error('Chart.js library is not loaded');
        return;
    }
    
    const canvas = document.getElementById('metricChart');
    if (!canvas) {
        console.warn('Chart canvas not found');
        return;
    }

    console.log('Chart canvas found:', canvas);

    // Destroy existing chart
    if (outcomeChart) {
        console.log('Destroying existing chart');
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
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Values'
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
                    text: 'Outcome Data Chart'
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
    
    console.log('Final chart config:', chartConfig);

    try {
        console.log('Creating Chart.js chart with Chart version:', Chart.version || 'unknown');
        outcomeChart = new Chart(ctx, chartConfig);
        console.log('Chart created successfully:', outcomeChart);
        
        // Force update to ensure the chart renders
        outcomeChart.update('active');
        
    } catch (error) {
        console.error('Error creating chart:', error);
        console.error('Error stack:', error.stack);
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
    console.log('prepareChartData called with:', { tableData, columns, rows, options });
    
    const datasets = [];
    const labels = rows.filter(row => row.type === 'data').map(row => row.label);

    console.log('Chart labels:', labels);

    columns.forEach((column, index) => {
        let data = rows.filter(row => row.type === 'data').map(row => {
            return tableData[row.id] ? (tableData[row.id][column.id] || 0) : 0;
        });

        // Apply cumulative transformation if enabled
        if (options.cumulativeView) {
            data = data.reduce((acc, value, index) => {
                if (index === 0) {
                    acc.push(value);
                } else {
                    acc.push(acc[index - 1] + value);
                }
                return acc;
            }, []);
        }

        console.log(`Data for column ${column.label} (cumulative: ${options.cumulativeView}):`, data);

        datasets.push({
            label: column.label + (options.cumulativeView ? ' (Cumulative)' : ''),
            data: data,
            borderColor: `hsl(${index * 60}, 70%, 50%)`,
            backgroundColor: `hsla(${index * 60}, 70%, 50%, 0.1)`,
            fill: false
        });
    });

    const result = {
        labels: labels,
        datasets: datasets
    };
    
    console.log('prepareChartData result:', result);
    return result;
}

// Export functions for global access
window.initializeOutcomeChart = initializeOutcomeChart;
window.updateChart = updateChart;
window.changeChartType = changeChartType;
window.downloadChart = downloadChart;
window.prepareChartData = prepareChartData;
