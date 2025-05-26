/**
 * Outcomes Chart
 * 
 * JavaScript for visualizing outcomes data in chart format.
 * Used by both admin and agency views to provide consistent chart displays.
 */

let outcomesChart = null;
let chartData = null;
let chartMonths = null;
let chartOptions = {
    type: 'line',
    outcomes: []
};

/**
 * Initialize outcomes chart with data
 * 
 * @param {Object} outcomesData Full outcomes data structure
 * @param {Array} tableData Array of month data objects
 * @param {Array} monthNames Array of month names
 * @param {String} tableName Name of the outcome table
 */
function initOutcomesChart(outcomesData, tableData, monthNames, tableName) {
    // Save data globally
    chartData = outcomesData;
    chartMonths = monthNames;
    
    // Set default outcomes to display (all of them)
    chartOptions.outcomes = outcomesData.columns || [];
    
    // Get select elements
    const typeSelect = document.getElementById('chartTypeSelect');
    const outcomeSelect = document.getElementById('outcomeSelect');
    
    // Clear any existing options
    if (outcomeSelect) {
        outcomeSelect.innerHTML = '';
        
        // Add options for each outcome
        if (outcomesData.columns && outcomesData.columns.length > 0) {
            // Add "All outcomes" option
            const allOption = document.createElement('option');
            allOption.value = 'all';
            allOption.textContent = 'All Outcomes';
            outcomeSelect.appendChild(allOption);
            
            // Add individual outcome options
            outcomesData.columns.forEach(column => {
                const option = document.createElement('option');
                option.value = column;
                option.textContent = column;
                outcomeSelect.appendChild(option);
            });
        }
    }
    
    // Set up event listeners
    if (typeSelect) {
        typeSelect.addEventListener('change', updateChartType);
    }
    
    if (outcomeSelect) {
        outcomeSelect.addEventListener('change', updateSelectedOutcomes);
    }
    
    // Initialize chart
    renderOutcomesChart();
}

/**
 * Update the chart type based on selection
 */
function updateChartType() {
    const typeSelect = document.getElementById('chartTypeSelect');
    if (typeSelect) {
        chartOptions.type = typeSelect.value;
        renderOutcomesChart();
    }
}

/**
 * Update which outcomes are displayed in the chart
 */
function updateSelectedOutcomes() {
    const outcomeSelect = document.getElementById('outcomeSelect');
    if (outcomeSelect) {
        const selectedValue = outcomeSelect.value;
        
        if (selectedValue === 'all') {
            // Show all outcomes
            chartOptions.outcomes = chartData.columns || [];
        } else {
            // Show only the selected outcome
            chartOptions.outcomes = [selectedValue];
        }
        
        renderOutcomesChart();
    }
}

/**
 * Render the outcomes chart with current options
 */
function renderOutcomesChart() {
    const chartCanvas = document.getElementById('outcomesChart');
    if (!chartCanvas) return;
    
    // Destroy previous chart instance if exists
    if (outcomesChart) {
        outcomesChart.destroy();
    }
    
    // Prepare data structure for Chart.js
    const datasets = prepareChartDatasets();
    
    // Create the chart
    outcomesChart = new Chart(chartCanvas, {
        type: chartOptions.type,
        data: {
            labels: chartMonths,
            datasets: datasets
        },
        options: getChartOptions()
    });
}

/**
 * Prepare datasets for the chart based on selected outcomes
 * @return {Array} Array of dataset objects for Chart.js
 */
function prepareChartDatasets() {
    // Get dynamic color function
    const getColor = function(index) {
        const colors = [
            'rgba(54, 162, 235, 0.8)', // blue
            'rgba(255, 99, 132, 0.8)', // red
            'rgba(75, 192, 192, 0.8)', // green
            'rgba(255, 206, 86, 0.8)', // yellow
            'rgba(153, 102, 255, 0.8)', // purple
            'rgba(255, 159, 64, 0.8)', // orange
            'rgba(199, 199, 199, 0.8)' // gray
        ];
        return colors[index % colors.length];
    };
    
    // Array to hold datasets
    const datasets = [];
    
    // Check if data exists
    if (!chartData || !chartData.data) {
        return datasets;
    }
    
    // For each selected outcome, create a dataset
    chartOptions.outcomes.forEach((outcome, index) => {
        const dataset = {
            label: outcome,
            backgroundColor: chartOptions.type === 'line' ? 'transparent' : getColor(index),
            borderColor: getColor(index),
            pointBackgroundColor: getColor(index),
            pointBorderColor: '#fff',
            pointHoverBackgroundColor: '#fff',
            pointHoverBorderColor: getColor(index),
            data: []
        };
        
        // Add data for each month
        chartMonths.forEach(month => {
            let value = 0;
            if (chartData.data[month] && chartData.data[month][outcome] !== undefined) {
                value = parseFloat(chartData.data[month][outcome]) || 0;
            }
            dataset.data.push(value);
        });
        
        // Add dataset to array
        datasets.push(dataset);
    });
    
    return datasets;
}

/**
 * Get chart configuration options based on chart type
 * @return {Object} Chart.js options object
 */
function getChartOptions() {
    // Base options common to all chart types
    const options = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
            },
            tooltip: {
                mode: 'index',
                intersect: false,
                callbacks: {
                    label: function(context) {
                        let label = context.dataset.label || '';
                        if (label) {
                            label += ': ';
                        }
                        if (context.parsed.y !== null) {
                            label += new Intl.NumberFormat('en-US', { 
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2 
                            }).format(context.parsed.y);
                        }
                        
                        // Add unit if available
                        if (chartData.units && chartData.units[context.dataset.label]) {
                            label += ' ' + chartData.units[context.dataset.label];
                        }
                        
                        return label;
                    }
                }
            },
            title: {
                display: true,
                text: 'Outcome Metrics by Month'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    // Use a callback to format the tick values
                    callback: function(value) {
                        return value.toFixed(2);
                    }
                }
            }
        }
    };
    
    // Custom options for specific chart types
    if (chartOptions.type === 'radar') {
        // Radar chart specific options
        options.scales = {}; // Remove normal scales
        options.elements = {
            line: {
                tension: 0.1 // Smoother lines
            }
        };
    } else if (chartOptions.type === 'bar') {
        // Bar chart specific options
        options.scales.x = {
            grid: {
                display: false
            }
        };
    } else if (chartOptions.type === 'line') {
        // Line chart specific options
        options.elements = {
            line: {
                tension: 0.4 // Smoother lines
            },
            point: {
                radius: 4,
                hitRadius: 10,
                hoverRadius: 6
            }
        };
    }
    
    return options;
}

// Export functions for use in other modules if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initOutcomesChart,
        updateChartType,
        updateSelectedOutcomes,
        renderOutcomesChart
    };
}
