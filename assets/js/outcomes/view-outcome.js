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
    console.log('initializeChart called');
    
    // Check if Chart.js is available
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js not available, retrying in 100ms');
        setTimeout(initializeChart, 100);
        return;
    }
    
    const chartCanvas = document.getElementById('metricChart');
    if (!chartCanvas) {
        console.warn('Chart canvas not found');
        return;
    }

    // Get table data from PHP (this will be populated by the PHP page)
    if (typeof window.tableData !== 'undefined') {
        currentTableData = window.tableData;
        currentColumns = window.tableColumns;
        currentRows = window.tableRows;
        
        console.log('Chart data loaded:', {
            tableData: currentTableData,
            columns: currentColumns,
            rows: currentRows
        });
        
        try {
            console.log('About to call updateChart()');
            updateChart();
            console.log('updateChart() completed');
        } catch (error) {
            console.error('Error in updateChart():', error);
        }
    } else {
        console.warn('Window table data not defined');
    }
    
    console.log('Chart initialization for view mode completed');
}

/**
 * Update chart based on current settings
 */
function updateChart() {
    try {
        console.log('updateChart called');
        if (!currentTableData || !currentColumns || !currentRows) {
            console.warn('Missing chart data:', {
                tableData: !!currentTableData,
                columns: !!currentColumns,
                rows: !!currentRows
            });
            return;
        }
        
        const chartType = document.getElementById('chartType')?.value || 'line';
        const selectedColumns = getSelectedColumns();
        const cumulativeView = document.getElementById('cumulativeView')?.checked || false;
        
        console.log('Chart settings:', { chartType, selectedColumns, cumulativeView });
        console.log('Available columns with IDs:', currentColumns.map(col => ({ id: col.id, label: col.label })));
        console.log('Selected column values (type):', selectedColumns.map(val => ({ value: val, type: typeof val })));
        console.log('Current columns IDs (type):', currentColumns.map(col => ({ id: col.id, type: typeof col.id })));
        
        // Filter columns based on selection
        const filteredColumns = currentColumns.filter(col => 
            selectedColumns.length === 0 || selectedColumns.includes(col.id) || selectedColumns.includes(String(col.id))
        );
        
        console.log('Filtered columns:', filteredColumns);
        console.log('Selected column values:', selectedColumns);
        console.log('Column ID matching check:', currentColumns.map(col => ({ 
            id: col.id, 
            isSelected: selectedColumns.includes(col.id),
            isSelectedAsString: selectedColumns.includes(String(col.id)),
            selectedValues: selectedColumns
        })));
        
        // Prepare chart data with cumulative option
        console.log('About to call prepareChartData with:', {
            tableData: currentTableData,
            filteredColumns: filteredColumns,
            rows: currentRows,
            options: { cumulativeView: cumulativeView }
        });
        
        let chartData;
        if (typeof prepareChartData === 'function') {
            console.log('Using direct prepareChartData function');
            chartData = prepareChartData(currentTableData, filteredColumns, currentRows, {
                cumulativeView: cumulativeView
            });
        } else if (typeof window.prepareChartData === 'function') {
            console.log('Using window.prepareChartData function');
            chartData = window.prepareChartData(currentTableData, filteredColumns, currentRows, {
                cumulativeView: cumulativeView
            });
        } else {
            console.error('prepareChartData function not found!');
            return;
        }
        
        console.log('Prepared chart data:', chartData);
        
        // Check if chart functions are available
        console.log('Available functions:', {
            prepareChartData: typeof prepareChartData,
            initializeOutcomeChart: typeof initializeOutcomeChart,
            window_prepareChartData: typeof window.prepareChartData,
            window_initializeOutcomeChart: typeof window.initializeOutcomeChart
        });
        
        // Initialize chart with proper type
        const chartConfig = {
            type: chartType === 'area' ? 'line' : chartType
        };
        
        // Add area fill for area charts
        if (chartType === 'area') {
            chartData.datasets.forEach(dataset => {
                dataset.fill = true;
                dataset.backgroundColor = dataset.backgroundColor || 
                    dataset.borderColor.replace('50%)', '20%)');
            });
        }
        
        console.log('About to call initializeOutcomeChart with data:', chartData, 'and config:', chartConfig);
        
        // Try multiple ways to call the function
        if (typeof initializeOutcomeChart === 'function') {
            console.log('Using direct function call');
            initializeOutcomeChart(chartData, chartConfig);
        } else if (typeof window.initializeOutcomeChart === 'function') {
            console.log('Using window.initializeOutcomeChart');
            window.initializeOutcomeChart(chartData, chartConfig);
        } else {
            console.error('initializeOutcomeChart function not found!');
            console.log('Available window functions:', Object.keys(window).filter(key => key.includes('Chart') || key.includes('chart')));
        }
        
        console.log('initializeOutcomeChart call completed');
    } catch (error) {
        console.error('Error in updateChart:', error);
    }
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
    console.log('initializeViewOutcome called');
    
    // Check if required functions are available
    console.log('Chart functions availability:', {
        Chart: typeof Chart,
        prepareChartData: typeof prepareChartData,
        initializeOutcomeChart: typeof initializeOutcomeChart,
        window_prepareChartData: typeof window.prepareChartData,
        window_initializeOutcomeChart: typeof window.initializeOutcomeChart
    });
    
    // Initialize chart if tab is active
    const chartTab = document.getElementById('chart-tab');
    if (chartTab) {
        chartTab.addEventListener('click', function() {
            console.log('Chart tab clicked, initializing chart');
            setTimeout(initializeChart, 100);
        });
        
        // Also initialize immediately if chart tab is already active
        if (chartTab.classList.contains('active')) {
            console.log('Chart tab is already active, initializing chart');
            setTimeout(initializeChart, 100);
        }
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
    
    // Cumulative view checkbox
    const cumulativeView = document.getElementById('cumulativeView');
    if (cumulativeView) {
        cumulativeView.addEventListener('change', updateChart);
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
        const filename = 'outcome-chart.png';
        
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
    
    const selectedColumns = getSelectedColumns();
    const filteredColumns = currentColumns.filter(col => 
        selectedColumns.length === 0 || selectedColumns.includes(col.id)
    );
    
    // Prepare CSV content
    let csvContent = 'Row';
    filteredColumns.forEach(column => {
        csvContent += ',' + column.label;
    });
    csvContent += '\n';
    
    // Process data rows
    const dataRows = currentRows.filter(row => row.type === 'data');
    dataRows.forEach((row, rowIndex) => {
        csvContent += '"' + row.label + '"';
        
        filteredColumns.forEach(column => {
            let value = currentTableData[row.id] ? (currentTableData[row.id][column.id] || 0) : 0;
            csvContent += ',' + value;
        });
        csvContent += '\n';
    });
    
    // Download the CSV
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const filename = 'outcome-data.csv';
    
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
