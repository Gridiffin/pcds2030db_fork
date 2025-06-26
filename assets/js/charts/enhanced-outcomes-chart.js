/**
 * Enhanced Outcomes Chart
 * 
 * JavaScript for visualizing outcomes data in chart format with support for flexible table structures.
 * Supports both classic monthly data and flexible row/column configurations.
 */

let enhancedOutcomesChart = null;
let chartData = null;
let chartStructure = null;
let chartOptions = {
    type: 'line',
    columns: [],
    showCumulative: false
};

/**
 * Initialize enhanced outcomes chart with flexible structure support
 * 
 * @param {Object} outcomesData Full outcomes data structure
 * @param {Object} structure Table structure configuration (rows and columns)
 * @param {String} tableName Name of the outcome table
 * @param {String} structureType Type of structure (classic, flexible)
 */
function initEnhancedOutcomesChart(outcomesData, structure, tableName, structureType = 'classic') {
    // Save data globally
    chartData = outcomesData;
    chartStructure = structure;
    
    // Determine chart configuration based on structure type
    if (structureType === 'flexible') {
        setupFlexibleChart();
    } else {
        setupClassicChart();
    }
    
    // Set up event listeners
    setupChartControls();
    
    // Initialize chart
    renderEnhancedChart();
}

/**
 * Setup chart for classic monthly structure
 */
function setupClassicChart() {
    // For classic structure, use months as X-axis and columns as series
    chartOptions.xAxisData = ['January', 'February', 'March', 'April', 'May', 'June',
                              'July', 'August', 'September', 'October', 'November', 'December'];
    chartOptions.columns = chartData.columns || [];
    chartOptions.dataStructure = 'classic';
    
    // Setup column selector
    populateColumnSelector(chartOptions.columns);
}

/**
 * Setup chart for flexible structure
 */
function setupFlexibleChart() {
    // For flexible structure, use row labels as X-axis and columns as series
    chartOptions.xAxisData = chartStructure.rows.map(row => row.label);
    chartOptions.columns = chartStructure.columns.map(col => col.name);
    chartOptions.dataStructure = 'flexible';
    
    // Setup column selector
    populateColumnSelector(chartOptions.columns);
}

/**
 * Populate the column selector dropdown
 */
function populateColumnSelector(columns) {
    const columnSelect = document.getElementById('chartColumnSelect');
    if (!columnSelect || !columns.length) return;
    
    columnSelect.innerHTML = '';
    
    // Add "All columns" option
    const allOption = document.createElement('option');
    allOption.value = 'all';
    allOption.textContent = 'All Columns';
    allOption.selected = true;
    columnSelect.appendChild(allOption);
    
    // Add individual column options
    columns.forEach(column => {
        const option = document.createElement('option');
        option.value = column;
        option.textContent = column;
        columnSelect.appendChild(option);
    });
    
    // Set default to show all columns
    chartOptions.columns = columns;
}

/**
 * Setup chart control event listeners
 */
function setupChartControls() {
    // Chart type selector
    const typeSelect = document.getElementById('chartType');
    if (typeSelect) {
        typeSelect.addEventListener('change', function() {
            chartOptions.type = this.value;
            renderEnhancedChart();
        });
    }
    
    // Column selector
    const columnSelect = document.getElementById('chartColumnSelect');
    if (columnSelect) {
        columnSelect.addEventListener('change', function() {
            if (this.value === 'all') {
                chartOptions.columns = chartStructure?.columns?.map(col => col.name) || chartData.columns || [];
            } else {
                chartOptions.columns = [this.value];
            }
            renderEnhancedChart();
        });
    }
    
    // Cumulative toggle
    const cumulativeToggle = document.getElementById('cumulativeToggle');
    if (cumulativeToggle) {
        cumulativeToggle.addEventListener('change', function() {
            chartOptions.showCumulative = this.checked;
            renderEnhancedChart();
        });
    }
    
    // Download buttons
    const downloadChartBtn = document.getElementById('downloadChartImage');
    if (downloadChartBtn) {
        downloadChartBtn.addEventListener('click', downloadChartAsImage);
    }
    
    const downloadCSVBtn = document.getElementById('downloadDataCSV');
    if (downloadCSVBtn) {
        downloadCSVBtn.addEventListener('click', downloadDataAsCSV);
    }
}

/**
 * Render the chart with current options
 */
function renderEnhancedChart() {
    const canvas = document.getElementById('metricChart');
    if (!canvas) {
        console.error('Chart canvas not found');
        return;
    }
    
    // Destroy existing chart
    if (enhancedOutcomesChart) {
        enhancedOutcomesChart.destroy();
    }
    
    // Prepare chart data
    const datasets = prepareChartDatasets();
    
    // Chart configuration
    const config = {
        type: chartOptions.type,
        data: {
            labels: chartOptions.xAxisData,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Outcomes Data Visualization'
                },
                legend: {
                    display: datasets.length > 1,
                    position: 'top'
                }
            },
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
                        text: chartOptions.dataStructure === 'classic' ? 'Months' : 'Categories'
                    }
                }
            }
        }
    };
    
    // Create new chart
    const ctx = canvas.getContext('2d');
    enhancedOutcomesChart = new Chart(ctx, config);
}

/**
 * Prepare datasets for the chart
 */
function prepareChartDatasets() {
    const datasets = [];
    const colors = [
        'rgb(54, 162, 235)',   // Blue
        'rgb(255, 99, 132)',   // Red
        'rgb(255, 205, 86)',   // Yellow
        'rgb(75, 192, 192)',   // Green
        'rgb(153, 102, 255)',  // Purple
        'rgb(255, 159, 64)',   // Orange
        'rgb(199, 199, 199)',  // Grey
        'rgb(83, 102, 147)',   // Dark Blue
        'rgb(247, 70, 74)',    // Dark Red
        'rgb(70, 191, 189)'    // Teal
    ];
    
    chartOptions.columns.forEach((column, index) => {
        const data = extractColumnData(column);
        const color = colors[index % colors.length];
        
        datasets.push({
            label: column,
            data: data,
            borderColor: color,
            backgroundColor: color + '20', // Add transparency
            borderWidth: 2,
            fill: chartOptions.type === 'line' ? false : true
        });
    });
    
    return datasets;
}

/**
 * Extract data for a specific column across all rows
 */
function extractColumnData(columnName) {
    const data = [];
    
    if (chartOptions.dataStructure === 'classic') {
        // Classic monthly structure
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
                           'July', 'August', 'September', 'October', 'November', 'December'];
        
        let cumulativeValue = 0;
        monthNames.forEach(month => {
            let value = 0;
            if (chartData.data && chartData.data[month] && chartData.data[month][columnName]) {
                value = parseFloat(chartData.data[month][columnName]) || 0;
            }
            
            if (chartOptions.showCumulative) {
                cumulativeValue += value;
                data.push(cumulativeValue);
            } else {
                data.push(value);
            }
        });
    } else {
        // Flexible structure
        let cumulativeValue = 0;
        chartStructure.rows.forEach(row => {
            let value = 0;
            if (chartData.data && chartData.data[row.label] && chartData.data[row.label][columnName]) {
                value = parseFloat(chartData.data[row.label][columnName]) || 0;
            }
            
            if (chartOptions.showCumulative) {
                cumulativeValue += value;
                data.push(cumulativeValue);
            } else {
                data.push(value);
            }
        });
    }
    
    return data;
}

/**
 * Download chart as image
 */
function downloadChartAsImage() {
    if (!enhancedOutcomesChart) return;
    
    const link = document.createElement('a');
    link.download = 'outcomes-chart.png';
    link.href = enhancedOutcomesChart.toBase64Image();
    link.click();
}

/**
 * Download data as CSV
 */
function downloadDataAsCSV() {
    let csvContent = "data:text/csv;charset=utf-8,";
    
    // Add header row
    const headers = ['Category', ...chartOptions.columns];
    csvContent += headers.join(',') + '\n';
    
    // Add data rows
    chartOptions.xAxisData.forEach(category => {
        const row = [category];
        
        chartOptions.columns.forEach(column => {
            let value = 0;
            if (chartOptions.dataStructure === 'classic') {
                if (chartData.data && chartData.data[category] && chartData.data[category][column]) {
                    value = chartData.data[category][column];
                }
            } else {
                if (chartData.data && chartData.data[category] && chartData.data[category][column]) {
                    value = chartData.data[category][column];
                }
            }
            row.push(value);
        });
        
        csvContent += row.join(',') + '\n';
    });
    
    // Download the CSV
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement('a');
    link.setAttribute('href', encodedUri);
    link.setAttribute('download', 'outcomes-data.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Legacy support - redirect old function calls to new implementation
function initMetricsChart(metricsData, tableData, monthNames, tableName) {
    console.log('Redirecting legacy initMetricsChart to enhanced version');
    
    // Convert legacy data format to new structure
    const structure = {
        rows: monthNames.map(month => ({ label: month, type: 'text' })),
        columns: (metricsData.columns || []).map(col => ({ name: col, type: 'number' }))
    };
    
    initEnhancedOutcomesChart(metricsData, structure, tableName, 'classic');
}
