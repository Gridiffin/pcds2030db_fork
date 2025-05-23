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
    // Implementation to extract and format data for chart.js
    return [];
}

/**
 * Get chart configuration options based on chart type
 * @return {Object} Chart.js options object
 */
function getChartOptions() {
    // Implementation to configure chart options
    return {};
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
