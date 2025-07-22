/**
 * Edit Outcome Module
 * Handles edit functionality for outcome forms and dynamic tables
 */

export class EditOutcome {
    constructor(chartManager) {
        this.chartManager = chartManager;
        this.chart = null;
        this.structureManager = null;
        this.currentRowConfig = [];
        this.currentColumnConfig = [];
        this.unsavedChanges = false;
        this.autoSaveTimer = null;
    }

    /**
     * Initialize edit outcome functionality
     */
    init() {
        
        
        // Load configuration data
        this.loadConfiguration();
        
        // Initialize structure manager
        this.initStructureManager();
        
        // Set up the editable table
        this.initEditableTable();
        
        // Initialize chart if data is available
        if (this.hasData()) {
            this.initializeChart();
        }
        
        // Set up event listeners
        this.setupEventListeners();
        
        // Set up auto-save
        this.setupAutoSave();
        
        // Set up unsaved changes tracking
        this.setupUnsavedChangesTracking();
    }

    /**
     * Load configuration data from PHP hidden inputs
     */
    loadConfiguration() {
        const rowConfigElement = document.getElementById('row_config');
        const columnConfigElement = document.getElementById('column_config');
        
        if (rowConfigElement && columnConfigElement) {
            try {
                const rowConfig = JSON.parse(rowConfigElement.value || '{}');
                const columnConfig = JSON.parse(columnConfigElement.value || '{}');
                
                this.currentRowConfig = Array.isArray(rowConfig) ? rowConfig : (rowConfig.rows || []);
                this.currentColumnConfig = Array.isArray(columnConfig) ? columnConfig : (columnConfig.columns || []);
                
                
            } catch (error) {
                console.error('EditOutcome: Error parsing configuration:', error);
                this.currentRowConfig = [];
                this.currentColumnConfig = [];
            }
        }
    }

    /**
     * Initialize structure manager for dynamic table manipulation
     */
    initStructureManager() {
        this.structureManager = {
            rows: this.currentRowConfig,
            columns: this.currentColumnConfig,
            
            addRow: (label = '') => {
                const newRow = {
                    id: 'row_' + Date.now(),
                    label: label || `Row ${this.structureManager.rows.length + 1}`,
                    data: {}
                };
                this.structureManager.rows.push(newRow);
                this.updateTableStructure();
                this.markUnsavedChanges();
                return newRow;
            },
            
            removeRow: (index) => {
                if (index >= 0 && index < this.structureManager.rows.length) {
                    this.structureManager.rows.splice(index, 1);
                    this.updateTableStructure();
                    this.markUnsavedChanges();
                }
            },
            
            addColumn: (label = '') => {
                const newColumn = {
                    id: 'col_' + Date.now(),
                    label: label || `Column ${this.structureManager.columns.length + 1}`,
                    type: 'numeric'
                };
                this.structureManager.columns.push(newColumn);
                this.updateTableStructure();
                this.markUnsavedChanges();
                return newColumn;
            },
            
            removeColumn: (index) => {
                if (index >= 0 && index < this.structureManager.columns.length) {
                    const columnId = this.structureManager.columns[index].id;
                    this.structureManager.columns.splice(index, 1);
                    
                    // Remove data from all rows for this column
                    this.structureManager.rows.forEach(row => {
                        delete row.data[columnId];
                    });
                    
                    this.updateTableStructure();
                    this.markUnsavedChanges();
                }
            },
            
            getStructureData: () => {
                return {
                    rows: this.structureManager.rows,
                    columns: this.structureManager.columns
                };
            }
        };
    }

    /**
     * Initialize the editable table interface
     */
    initEditableTable() {
        this.updateTableStructure();
    }

    /**
     * Update the table structure and rebuild the interface
     */
    updateTableStructure() {
        const tableContainer = document.getElementById('dynamic-table-container');
        if (!tableContainer) {
            console.warn('EditOutcome: Table container not found');
            return;
        }

        // Build table HTML
        let tableHTML = '<div class=\"editable-table\">';
        tableHTML += '<table class=\"table table-bordered\">';
        
        // Header row
        tableHTML += '<thead><tr>';
        tableHTML += '<th class=\"row-control\">Row</th>';
        
        this.structureManager.columns.forEach((column, colIndex) => {
            tableHTML += `
                <th>
                    <div class=\"metric-header\">
                        <input type=\"text\" 
                               class=\"metric-title\" 
                               value=\"${this.escapeHtml(column.label)}\"
                               data-column-index=\"${colIndex}\"
                               placeholder=\"Column title\">
                        <div class=\"metric-actions\">
                            <button type=\"button\" 
                                    class=\"metric-action-btn btn-danger\"
                                    onclick=\"outcomesModule.editModule.removeColumn(${colIndex})\"
                                    title=\"Remove column\">
                                <i class=\"fas fa-times\"></i>
                            </button>
                        </div>
                    </div>
                </th>
            `;
        });
        
        tableHTML += '<th><button type=\"button\" class=\"btn-control btn-primary\" onclick=\"outcomesModule.editModule.addColumn()\"><i class=\"fas fa-plus\"></i></button></th>';
        tableHTML += '</tr></thead>';
        
        // Body rows
        tableHTML += '<tbody>';
        this.structureManager.rows.forEach((row, rowIndex) => {
            tableHTML += '<tr>';
            tableHTML += `
                <td class=\"row-control\">
                    <input type=\"text\" 
                           class=\"cell-input\"
                           value=\"${this.escapeHtml(row.label)}\"
                           data-row-index=\"${rowIndex}\"
                           placeholder=\"Row label\">
                    <div class=\"row-actions\">
                        <button type=\"button\" 
                                class=\"row-action-btn btn-danger\"
                                onclick=\"outcomesModule.editModule.removeRow(${rowIndex})\"
                                title=\"Remove row\">
                            <i class=\"fas fa-times\"></i>
                        </button>
                    </div>
                </td>
            `;
            
            this.structureManager.columns.forEach((column, colIndex) => {
                const cellValue = row.data[column.id] || '';
                tableHTML += `
                    <td class=\"editable-cell\">
                        <input type=\"text\" 
                               class=\"cell-input\"
                               value=\"${this.escapeHtml(cellValue)}\"
                               data-row-index=\"${rowIndex}\"
                               data-column-index=\"${colIndex}\"
                               placeholder=\"Enter value\">
                    </td>
                `;
            });
            
            tableHTML += '<td></td>';
            tableHTML += '</tr>';
        });
        
        tableHTML += '</tbody></table>';
        tableHTML += '</div>';
        
        // Add table controls
        const controlsHTML = `
            <div class=\"table-controls\">
                <div class=\"table-controls-left\">
                    <button type=\"button\" class=\"btn-control btn-primary\" onclick=\"outcomesModule.editModule.addRow()\">
                        <i class=\"fas fa-plus\"></i> Add Row
                    </button>
                </div>
                <div class=\"table-controls-right\">
                    <button type=\"button\" class=\"btn-control\" onclick=\"outcomesModule.editModule.previewChart()\">
                        <i class=\"fas fa-chart-line\"></i> Preview Chart
                    </button>
                </div>
            </div>
        `;
        
        tableContainer.innerHTML = controlsHTML + tableHTML;
        
        // Set up event listeners for the new elements
        this.setupTableEventListeners();
        
        
    }

    /**
     * Set up event listeners for table elements
     */
    setupTableEventListeners() {
        // Column title changes
        document.querySelectorAll('.metric-title').forEach(input => {
            input.addEventListener('blur', (e) => {
                const colIndex = parseInt(e.target.dataset.columnIndex);
                if (this.structureManager.columns[colIndex]) {
                    this.structureManager.columns[colIndex].label = e.target.value;
                    this.markUnsavedChanges();
                }
            });
        });

        // Row label changes
        document.querySelectorAll('input[data-row-index]:not([data-column-index])').forEach(input => {
            input.addEventListener('blur', (e) => {
                const rowIndex = parseInt(e.target.dataset.rowIndex);
                if (this.structureManager.rows[rowIndex]) {
                    this.structureManager.rows[rowIndex].label = e.target.value;
                    this.markUnsavedChanges();
                }
            });
        });

        // Cell data changes
        document.querySelectorAll('input[data-row-index][data-column-index]').forEach(input => {
            input.addEventListener('blur', (e) => {
                const rowIndex = parseInt(e.target.dataset.rowIndex);
                const colIndex = parseInt(e.target.dataset.columnIndex);
                
                if (this.structureManager.rows[rowIndex] && this.structureManager.columns[colIndex]) {
                    const columnId = this.structureManager.columns[colIndex].id;
                    this.structureManager.rows[rowIndex].data[columnId] = e.target.value;
                    this.markUnsavedChanges();
                }
            });
        });
    }

    /**
     * Check if outcome has data for visualization
     */
    hasData() {
        return this.structureManager && 
               this.structureManager.columns.length > 0 && 
               this.structureManager.rows.length > 0;
    }

    /**
     * Initialize chart for live preview
     */
    initializeChart() {
        const chartCanvas = document.getElementById('metricChart');
        if (!chartCanvas) {
            return;
        }

        this.updateChartData();
    }

    /**
     * Update chart with current table data
     */
    updateChartData() {
        if (!this.hasData()) {
            return;
        }

        try {
            const chartData = this.prepareChartData();
            
            if (this.chart) {
                this.chartManager.updateChart(this.chart, chartData);
            } else {
                const chartCanvas = document.getElementById('metricChart');
                if (chartCanvas) {
                    this.chart = this.chartManager.createChart(chartCanvas, {
                        ...chartData,
                        type: 'line'
                    });
                }
            }
        } catch (error) {
            console.error('EditOutcome: Error updating chart:', error);
        }
    }

    /**
     * Prepare data for chart visualization
     */
    prepareChartData() {
        const data = {};
        const columns = this.structureManager.columns.map(col => col.label);
        const rows = this.structureManager.rows.map(row => ({
            label: row.label,
            data: {}
        }));

        // Convert structure manager data to chart format
        this.structureManager.rows.forEach((row, rowIndex) => {
            rows[rowIndex].data = {};
            this.structureManager.columns.forEach(column => {
                rows[rowIndex].data[column.label] = row.data[column.id] || '';
            });
        });

        return { data, columns, rows };
    }

    /**
     * Set up main event listeners
     */
    setupEventListeners() {
        // Form submission
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', (e) => {
                this.prepareFormSubmission();
            });
        }

        // Save button
        const saveBtn = document.getElementById('save-outcome');
        if (saveBtn) {
            saveBtn.addEventListener('click', () => {
                this.saveOutcome();
            });
        }

        // Preview chart button
        const previewBtn = document.getElementById('preview-chart');
        if (previewBtn) {
            previewBtn.addEventListener('click', () => {
                this.previewChart();
            });
        }

        // Page unload warning
        window.addEventListener('beforeunload', (e) => {
            if (this.unsavedChanges) {
                e.preventDefault();
                e.returnValue = '';
                return '';
            }
        });
    }

    /**
     * Add row to table
     */
    addRow(label = '') {
        this.structureManager.addRow(label);
    }

    /**
     * Remove row from table
     */
    removeRow(index) {
        if (confirm('Are you sure you want to remove this row?')) {
            this.structureManager.removeRow(index);
        }
    }

    /**
     * Add column to table
     */
    addColumn(label = '') {
        this.structureManager.addColumn(label);
    }

    /**
     * Remove column from table
     */
    removeColumn(index) {
        if (confirm('Are you sure you want to remove this column? All data in this column will be lost.')) {
            this.structureManager.removeColumn(index);
        }
    }

    /**
     * Preview chart with current data
     */
    previewChart() {
        this.updateChartData();
        
        // Show chart container if hidden
        const chartContainer = document.querySelector('.outcome-chart-container');
        if (chartContainer) {
            chartContainer.style.display = 'block';
            chartContainer.scrollIntoView({ behavior: 'smooth' });
        }
    }

    /**
     * Prepare form data for submission
     */
    prepareFormSubmission() {
        const structureData = this.structureManager.getStructureData();
        
        // Update hidden fields
        const rowConfigInput = document.getElementById('row_config');
        const columnConfigInput = document.getElementById('column_config');
        
        if (rowConfigInput) {
            rowConfigInput.value = JSON.stringify(structureData.rows);
        }
        
        if (columnConfigInput) {
            columnConfigInput.value = JSON.stringify(structureData.columns);
        }
        
        
    }

    /**
     * Save outcome data via AJAX
     */
    async saveOutcome() {
        try {
            const formData = new FormData();
            const structureData = this.structureManager.getStructureData();
            
            formData.append('data', JSON.stringify({
                columns: structureData.columns,
                rows: structureData.rows
            }));
            
            const outcomeId = this.getOutcomeId();
            const response = await fetch(`save_outcome.php?id=${outcomeId}`, {
                method: 'POST',
                body: formData
            });
            
            if (response.ok) {
                this.clearUnsavedChanges();
                this.showSaveSuccess();
            } else {
                throw new Error('Save failed');
            }
        } catch (error) {
            console.error('EditOutcome: Error saving outcome:', error);
            this.showSaveError();
        }
    }

    /**
     * Set up auto-save functionality
     */
    setupAutoSave() {
        // Auto-save every 30 seconds if there are unsaved changes
        this.autoSaveTimer = setInterval(() => {
            if (this.unsavedChanges) {
                this.saveOutcome();
            }
        }, 30000);
    }

    /**
     * Set up unsaved changes tracking
     */
    setupUnsavedChangesTracking() {
        // Track changes to form inputs
        document.addEventListener('input', (e) => {
            if (e.target.matches('.cell-input, .metric-title')) {
                this.markUnsavedChanges();
            }
        });
    }

    /**
     * Mark that there are unsaved changes
     */
    markUnsavedChanges() {
        this.unsavedChanges = true;
        
        // Update UI to show unsaved state
        const saveBtn = document.getElementById('save-outcome');
        if (saveBtn) {
            saveBtn.innerHTML = '<i class=\"fas fa-save\"></i> Save Changes*';
            saveBtn.classList.add('btn-warning');
        }
    }

    /**
     * Clear unsaved changes state
     */
    clearUnsavedChanges() {
        this.unsavedChanges = false;
        
        // Update UI to show saved state
        const saveBtn = document.getElementById('save-outcome');
        if (saveBtn) {
            saveBtn.innerHTML = '<i class=\"fas fa-check\"></i> Saved';
            saveBtn.classList.remove('btn-warning');
            saveBtn.classList.add('btn-success');
            
            setTimeout(() => {
                saveBtn.innerHTML = '<i class=\"fas fa-save\"></i> Save Changes';
                saveBtn.classList.remove('btn-success');
            }, 2000);
        }
    }

    /**
     * Show save success message
     */
    showSaveSuccess() {
        // Implementation depends on your alert system
        
    }

    /**
     * Show save error message
     */
    showSaveError() {
        // Implementation depends on your alert system
        console.error('EditOutcome: Save failed');
    }

    /**
     * Get outcome ID from URL
     */
    getOutcomeId() {
        const urlParams = new URLSearchParams(window.location.search);
        return urlParams.get('id') || '';
    }

    /**
     * Escape HTML for safe display
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Clean up resources
     */
    destroy() {
        if (this.autoSaveTimer) {
            clearInterval(this.autoSaveTimer);
        }
        
        if (this.chart) {
            this.chart.destroy();
            this.chart = null;
        }
        
        
    }
}
