/**
 * View Outcome Module
 * Handles view-only functionality for outcome details
 */

export class ViewOutcome {
    constructor(chartManager) {
        this.chartManager = chartManager;
        this.chart = null;
        this.tableData = null;
        this.columns = null;
        this.rows = null;
    }

    /**
     * Initialize view outcome functionality
     */
    init() {
        console.log('ViewOutcome: Initializing view outcome module');
        
        // Load data from global variables set by PHP
        this.loadData();
        
        // Initialize chart if data is available
        if (this.hasData()) {
            this.initializeChart();
        } else {
            this.showEmptyState();
        }
        
        // Set up event listeners
        this.setupEventListeners();
    }

    /**
     * Load data from global variables set by PHP
     */
    loadData() {
        if (typeof window.tableData !== 'undefined') {
            this.tableData = window.tableData;
            this.columns = window.tableColumns || [];
            this.rows = window.tableRows || [];
            
            console.log('ViewOutcome: Data loaded', {
                tableData: this.tableData,
                columns: this.columns,
                rows: this.rows
            });
        }
    }

    /**
     * Check if outcome has data to display
     */
    hasData() {
        return this.tableData && 
               this.columns && this.columns.length > 0 && 
               this.rows && this.rows.length > 0;
    }

    /**
     * Initialize chart visualization
     */
    initializeChart() {
        console.log('ViewOutcome: Initializing chart');
        
        const chartContainer = document.querySelector('.outcome-chart-container');
        const chartCanvas = document.getElementById('metricChart');
        
        if (!chartCanvas) {
            console.warn('ViewOutcome: Chart canvas not found');
            return;
        }

        // Show the chart container
        if (chartContainer) {
            chartContainer.style.display = 'block';
        }

        try {
            this.chart = this.chartManager.createChart(chartCanvas, {
                data: this.tableData,
                columns: this.columns,
                rows: this.rows,
                type: 'line' // Default chart type for view mode
            });
            
            console.log('ViewOutcome: Chart created successfully');
        } catch (error) {
            console.error('ViewOutcome: Error creating chart:', error);
            this.showChartError();
        }
    }

    /**
     * Show empty state when no data is available
     */
    showEmptyState() {
        console.log('ViewOutcome: Showing empty state');
        
        const chartContainer = document.querySelector('.outcome-chart-container');
        if (chartContainer) {
            chartContainer.innerHTML = `
                <div class="outcome-empty-state">
                    <i class="fas fa-chart-line"></i>
                    <h4>No Data Available</h4>
                    <p>This outcome doesn't have any data to display yet. Please contact your administrator to add data.</p>
                </div>
            `;
        }
    }

    /**
     * Show chart error state
     */
    showChartError() {
        const chartContainer = document.querySelector('.outcome-chart-container');
        if (chartContainer) {
            chartContainer.innerHTML = `
                <div class="chart-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="chart-error-text">
                        Unable to display chart. Please check the data format or refresh the page.
                    </div>
                </div>
            `;
        }
    }

    /**
     * Set up event listeners for view page
     */
    setupEventListeners() {
        // Export chart button
        const exportChartBtn = document.getElementById('exportChart');
        if (exportChartBtn) {
            exportChartBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.exportChart();
            });
        }

        // Export data button
        const exportDataBtn = document.getElementById('exportData');
        if (exportDataBtn) {
            exportDataBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.exportData();
            });
        }

        // Chart type selector (if available)
        const chartTypeButtons = document.querySelectorAll('.chart-type-btn');
        chartTypeButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                this.changeChartType(btn.dataset.type);
            });
        });

        // Back button confirmation if there are unsaved changes
        const backButton = document.querySelector('a[href*="submit_outcomes.php"]');
        if (backButton) {
            // Just navigate normally in view mode - no unsaved changes
        }
    }

    /**
     * Change chart visualization type
     */
    changeChartType(type) {
        if (!this.chart || !this.hasData()) {
            return;
        }

        console.log('ViewOutcome: Changing chart type to', type);
        
        try {
            this.chartManager.updateChartType(this.chart, type, {
                data: this.tableData,
                columns: this.columns,
                rows: this.rows
            });
            
            // Update active button
            document.querySelectorAll('.chart-type-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            const activeBtn = document.querySelector(`[data-type="${type}"]`);
            if (activeBtn) {
                activeBtn.classList.add('active');
            }
            
        } catch (error) {
            console.error('ViewOutcome: Error changing chart type:', error);
        }
    }

    /**
     * Export chart as PNG image
     */
    exportChart() {
        if (!this.chart) {
            console.warn('ViewOutcome: No chart available for export');
            return;
        }

        try {
            const link = document.createElement('a');
            link.download = `outcome-${this.getOutcomeId()}-chart.png`;
            link.href = this.chart.toBase64Image();
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            console.log('ViewOutcome: Chart exported successfully');
        } catch (error) {
            console.error('ViewOutcome: Error exporting chart:', error);
        }
    }

    /**
     * Export data as CSV
     */
    exportData() {
        if (!this.hasData()) {
            console.warn('ViewOutcome: No data available for export');
            return;
        }

        try {
            let csvContent = '';
            
            // Add headers
            const headers = ['Month/Period', ...this.columns];
            csvContent += headers.join(',') + '\\n';
            
            // Add data rows
            this.rows.forEach(row => {
                const rowData = [row.month || row.label || ''];
                this.columns.forEach(col => {
                    rowData.push(row.data[col] || '');
                });
                csvContent += rowData.join(',') + '\\n';
            });
            
            // Create and trigger download
            const blob = new Blob([csvContent], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `outcome-${this.getOutcomeId()}-data.csv`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);
            
            console.log('ViewOutcome: Data exported successfully');
        } catch (error) {
            console.error('ViewOutcome: Error exporting data:', error);
        }
    }

    /**
     * Get outcome ID from URL or page data
     */
    getOutcomeId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id') || 'unknown';
    }

    /**
     * Clean up resources
     */
    destroy() {
        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }
        
        // Remove event listeners (they'll be cleaned up with element removal)
        console.log('ViewOutcome: Module destroyed');
    }
}
