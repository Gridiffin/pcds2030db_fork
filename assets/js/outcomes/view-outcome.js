/**
 * View Outcome JavaScript Module
 * Handles view-only functionality for outcomes
 */

// Global variables for chart functionality
let metricChart = null;
let currentTableData = null;
let currentColumns = null;
let currentRows = null;

/**
 * Initialize chart functionality for view mode
 */
function initializeChart() {
    const chartCanvas = document.getElementById('metricChart');
    if (!chartCanvas) return;

    // Get table data from PHP (this will be populated by the PHP page)
    if (typeof window.tableData !== 'undefined') {
        currentTableData = window.tableData;
        currentColumns = window.tableColumns;
        currentRows = window.tableRows;
        
        updateChart();
    }
    
    console.log('Chart initialization for view mode');
}

/**
 * Update chart based on current settings
 */
function updateChart() {
    if (!currentTableData || !currentColumns || !currentRows) return;
    
    const chartType = document.getElementById('chartType')?.value || 'line';
    const selectedColumns = getSelectedColumns();
    const showTotals = document.getElementById('showTotals')?.checked || true;
    const isCumulative = document.getElementById('cumulativeView')?.checked || false;
    
    // Filter columns based on selection
    const filteredColumns = currentColumns.filter(col => 
        selectedColumns.length === 0 || selectedColumns.includes(col.id)
    );
    
    // Prepare chart data with cumulative option
    const chartData = prepareChartData(currentTableData, filteredColumns, currentRows, {
        cumulative: isCumulative,
        showTotals: showTotals
    });
    
    // Initialize chart with proper type
    const chartConfig = {
        type: chartType === 'area' ? 'line' : chartType,
        data: chartData
    };
    
    // Add area fill for area charts
    if (chartType === 'area') {
        chartData.datasets.forEach(dataset => {
            dataset.fill = true;
            dataset.backgroundColor = dataset.backgroundColor || 
                dataset.borderColor.replace('50%)', '20%)');
        });
    }
    
    initializeOutcomeChart(chartData, chartConfig);
}

/**
 * Get selected columns from the multi-select
 */
function getSelectedColumns() {
    const columnSelect = document.getElementById('chartColumns');
    if (!columnSelect) return [];
    
    return Array.from(columnSelect.selectedOptions).map(option => option.value);
}

/**
 * Initialize view outcome page
 */
function initializeViewOutcome() {
    // Initialize chart if tab is active
    const chartTab = document.getElementById('chart-tab');
    if (chartTab) {
        chartTab.addEventListener('click', function() {
            setTimeout(initializeChart, 100);
        });
    }
    
    // Setup event handlers for chart controls
    setupChartEventHandlers();

    // Initialize any other view-specific functionality
    console.log('View outcome page initialized');
}

/**
 * Setup event handlers for chart controls
 */
function setupChartEventHandlers() {
    // Chart type selector
    const chartType = document.getElementById('chartType');
    if (chartType) {
        chartType.addEventListener('change', updateChart);
    }
    
    // Column selector
    const chartColumns = document.getElementById('chartColumns');
    if (chartColumns) {
        chartColumns.addEventListener('change', updateChart);
    }
    
    // Show totals checkbox
    const showTotals = document.getElementById('showTotals');
    if (showTotals) {
        showTotals.addEventListener('change', updateChart);
    }
    
    // Cumulative view checkbox
    const cumulativeView = document.getElementById('cumulativeView');
    if (cumulativeView) {
        cumulativeView.addEventListener('change', function() {
            // Add visual feedback for cumulative mode
            const chartContainer = document.querySelector('.chart-container');
            if (chartContainer) {
                if (this.checked) {
                    chartContainer.classList.add('cumulative-mode');
                } else {
                    chartContainer.classList.remove('cumulative-mode');
                }
            }
            updateChart();
        });
    }
    
    // Download buttons
    const downloadChartBtn = document.getElementById('downloadChartImage');
    if (downloadChartBtn) {
        downloadChartBtn.addEventListener('click', downloadChartImage);
    }
    
    const downloadCSVBtn = document.getElementById('downloadDataCSV');
    if (downloadCSVBtn) {
        downloadCSVBtn.addEventListener('click', downloadDataCSV);
    }
}

/**
 * Download chart as image
 */
function downloadChartImage() {
    if (outcomeChart) {
        const isCumulative = document.getElementById('cumulativeView')?.checked;
        const filename = isCumulative ? 'outcome-chart-cumulative.png' : 'outcome-chart.png';
        
        const link = document.createElement('a');
        link.download = filename;
        link.href = outcomeChart.toBase64Image();
        link.click();
    } else {
        console.warn('No chart available for download');
    }
}

/**
 * Download data as CSV
 */
function downloadDataCSV() {
    if (!currentTableData || !currentColumns || !currentRows) {
        console.warn('No data available for download');
        return;
    }
    
    const isCumulative = document.getElementById('cumulativeView')?.checked;
    const selectedColumns = getSelectedColumns();
    const filteredColumns = currentColumns.filter(col => 
        selectedColumns.length === 0 || selectedColumns.includes(col.id)
    );
    
    // Prepare CSV content
    let csvContent = 'Row';
    filteredColumns.forEach(column => {
        csvContent += ',' + column.label + (isCumulative ? ' (Cumulative)' : '');
    });
    csvContent += '\n';
    
    // Process data rows
    const dataRows = currentRows.filter(row => row.type === 'data');
    dataRows.forEach((row, rowIndex) => {
        csvContent += '"' + row.label + '"';
        
        filteredColumns.forEach(column => {
            let value = currentTableData[row.id] ? (currentTableData[row.id][column.id] || 0) : 0;
            
            // Apply cumulative calculation if needed
            if (isCumulative) {
                // Calculate cumulative sum up to this row
                let cumulativeValue = 0;
                for (let i = 0; i <= rowIndex; i++) {
                    const currentRowData = currentTableData[dataRows[i].id];
                    if (currentRowData) {
                        cumulativeValue += parseFloat(currentRowData[column.id] || 0);
                    }
                }
                value = cumulativeValue;
            }
            
            csvContent += ',' + value;
        });
        csvContent += '\n';
    });
    
    // Download the CSV
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const filename = isCumulative ? 'outcome-data-cumulative.csv' : 'outcome-data.csv';
    
    if (navigator.msSaveBlob) { // IE 10+
        navigator.msSaveBlob(blob, filename);
    } else {
        link.href = URL.createObjectURL(blob);
        link.download = filename;
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeViewOutcome();
});
