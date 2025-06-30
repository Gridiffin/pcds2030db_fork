/**
 * Edit Outcome JavaScript Module
 * Handles edit functionality for outcomes
 */

// Global variables
let currentRowConfig = [];
let currentColumnConfig = [];
let simpleStructureManager = null;

/**
 * Initialize the flexible outcome editor
 */
function initFlexibleOutcomeEditor() {
    console.log('Initializing flexible outcome editor');
    
    // Get configuration data from PHP
    const rowConfigElement = document.getElementById('row_config');
    const columnConfigElement = document.getElementById('column_config');
    
    if (!rowConfigElement || !columnConfigElement) {
        console.warn('Configuration elements not found');
        return;
    }

    try {
        const rowConfig = JSON.parse(rowConfigElement.value || '{}');
        const columnConfig = JSON.parse(columnConfigElement.value || '{}');
        
        console.log('Configurations loaded:', { rowConfig, columnConfig });
        
        // Create simple structure manager
        simpleStructureManager = {
            rows: Array.isArray(rowConfig) ? rowConfig : (rowConfig.rows || []),
            columns: Array.isArray(columnConfig) ? columnConfig : (columnConfig.columns || []),
            onStructureChange: function(newStructure) {
                updateTableStructure(newStructure);
            },
            getStructureData: function() {
                return {
                    rows: this.rows,
                    columns: this.columns
                };
            }
        };
        
        // Store current configs globally
        currentRowConfig = simpleStructureManager.rows;
        currentColumnConfig = simpleStructureManager.columns;
        
        // Initialize simple controls
        addSimpleColumnControls();
        
        console.log('Structure manager initialized:', simpleStructureManager.getStructureData());
        
    } catch (error) {
        console.error('Error parsing configuration:', error);
        createFallbackStructure();
    }
}

/**
 * Create fallback structure if configs are invalid
 */
function createFallbackStructure() {
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                        'July', 'August', 'September', 'October', 'November', 'December'];
    
    currentRowConfig = monthNames.map(month => ({
        id: month,
        label: month,
        type: 'data'
    }));
    
    currentColumnConfig = [
        { id: 'metric1', label: 'Metric 1', type: 'number', unit: '' }
    ];
    
    console.log('Fallback structure created');
}

/**
 * Add simple column and row controls
 */
function addSimpleColumnControls() {
    const designerContainer = document.getElementById('table-designer-container');
    if (!designerContainer) {
        createFallbackDesignerContainer();
        return;
    }
    
    designerContainer.innerHTML = `
        <div class="simple-controls mb-4">
            <div class="alert alert-info">
                <i class="fas fa-tools me-2"></i>
                <strong>Table Structure Manager</strong><br>
                Add or remove columns and rows to customize your outcome table structure.
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="card border-success">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-columns me-2 text-success"></i>Manage Columns
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <input type="text" id="newColumnName" class="form-control" placeholder="Enter column name" style="max-width: 180px;">
                                <select id="newColumnType" class="form-select" style="max-width: 110px;">
                                    <option value="number">Number</option>
                                    <option value="currency">Currency</option>
                                    <option value="percentage">Percentage</option>
                                    <option value="text">Text</option>
                                </select>
                                <button type="button" class="btn btn-success btn-sm" onclick="addNewColumn()">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                            <div id="columnsList"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card border-primary">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="fas fa-list me-2 text-primary"></i>Manage Rows
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <input type="text" id="newRowName" class="form-control" placeholder="Enter row name" style="max-width: 180px;">
                                <select id="newRowType" class="form-select" style="max-width: 110px;">
                                    <option value="data">Data</option>
                                    <option value="calculated">Calculated</option>
                                    <option value="separator">Separator</option>
                                </select>
                                <button type="button" class="btn btn-primary btn-sm" onclick="addNewRow()">
                                    <i class="fas fa-plus"></i> Add
                                </button>
                            </div>
                            <div id="rowsList"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Populate existing columns and rows
    populateSimpleControls();
}

/**
 * Create fallback designer container if none exists
 */
function createFallbackDesignerContainer() {
    const tableContainer = document.querySelector('.table-responsive');
    if (!tableContainer) return;
    
    const fallbackContainer = document.createElement('div');
    fallbackContainer.id = 'table-designer-container';
    fallbackContainer.className = 'mb-4';
    
    tableContainer.parentNode.insertBefore(fallbackContainer, tableContainer);
    addSimpleColumnControls();
}

/**
 * Populate existing columns and rows in the controls with edit functionality
 */
function populateSimpleControls() {
    const columnsList = document.getElementById('columnsList');
    const rowsList = document.getElementById('rowsList');
    
    if (columnsList && currentColumnConfig) {
        columnsList.innerHTML = currentColumnConfig.map((col, index) => `
            <div class="d-flex align-items-center justify-content-between mb-2 p-3 border rounded bg-light">
                <div class="flex-grow-1">
                    <span class="text-success fw-bold">${col.label}</span>
                    <small class="text-muted d-block">${col.type}</small>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="editColumn(${index})" title="Edit column">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeColumn(${index})" title="Remove column">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }
    
    if (rowsList && currentRowConfig) {
        rowsList.innerHTML = currentRowConfig.map((row, index) => `
            <div class="d-flex align-items-center justify-content-between mb-2 p-3 border rounded bg-light">
                <div class="flex-grow-1">
                    <span class="text-primary fw-bold">${row.label}</span>
                    <small class="text-muted d-block">${row.type}</small>
                </div>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="editRow(${index})" title="Edit row">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(${index})" title="Remove row">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `).join('');
    }
}

/**
 * Add new column with enhanced UX
 */
function addNewColumn() {
    const nameInput = document.getElementById('newColumnName');
    const typeSelect = document.getElementById('newColumnType');
    
    if (!nameInput || !typeSelect || !nameInput.value.trim()) {
        showToast('Please enter a column name', 'warning');
        nameInput?.focus();
        return;
    }
    
    const columnName = nameInput.value.trim();
    
    // Check for duplicate names
    if (currentColumnConfig.some(col => col.label.toLowerCase() === columnName.toLowerCase())) {
        showToast('Column name already exists', 'error');
        nameInput.focus();
        nameInput.select();
        return;
    }
    
    const newColumn = {
        id: columnName.toLowerCase().replace(/\s+/g, '_'),
        label: columnName,
        type: typeSelect.value,
        unit: ''
    };
    
    // Collect current data before structure change
    collectCurrentTableData();
    
    currentColumnConfig.push(newColumn);
    
    // Clear inputs
    nameInput.value = '';
    typeSelect.value = 'number';
    
    // Refresh controls and table
    populateSimpleControls();
    updateTableStructure();
    
    showToast(`Column "${columnName}" added successfully`, 'success');
}

/**
 * Add new row with enhanced UX
 */
function addNewRow() {
    const nameInput = document.getElementById('newRowName');
    const typeSelect = document.getElementById('newRowType');
    
    if (!nameInput || !typeSelect || !nameInput.value.trim()) {
        showToast('Please enter a row name', 'warning');
        nameInput?.focus();
        return;
    }
    
    const rowName = nameInput.value.trim();
    
    // Check for duplicate names
    if (currentRowConfig.some(row => row.label.toLowerCase() === rowName.toLowerCase())) {
        showToast('Row name already exists', 'error');
        nameInput.focus();
        nameInput.select();
        return;
    }
    
    const newRow = {
        id: rowName.toLowerCase().replace(/\s+/g, '_'),
        label: rowName,
        type: typeSelect.value
    };
    
    // Collect current data before structure change
    collectCurrentTableData();
    
    currentRowConfig.push(newRow);
    
    // Clear inputs
    nameInput.value = '';
    typeSelect.value = 'data';
    
    // Refresh controls and table
    populateSimpleControls();
    updateTableStructure();
    
    showToast(`Row "${rowName}" added successfully`, 'success');
}

/**
 * Remove column by index with enhanced UX
 */
function removeColumn(index) {
    if (index < 0 || index >= currentColumnConfig.length) {
        showToast('Invalid column index', 'error');
        return;
    }
    
    const columnName = currentColumnConfig[index].label;
    
    if (confirm(`Are you sure you want to remove column "${columnName}"? This action cannot be undone and all data in this column will be lost.`)) {
        // Collect current data before structure change
        collectCurrentTableData();
        
        currentColumnConfig.splice(index, 1);
        populateSimpleControls();
        updateTableStructure();
        
        showToast(`Column "${columnName}" removed successfully`, 'success');
    }
}

/**
 * Remove row by index with enhanced UX
 */
function removeRow(index) {
    if (index < 0 || index >= currentRowConfig.length) {
        showToast('Invalid row index', 'error');
        return;
    }
    
    const rowName = currentRowConfig[index].label;
    
    if (confirm(`Are you sure you want to remove row "${rowName}"? This action cannot be undone and all data in this row will be lost.`)) {
        // Collect current data before structure change
        collectCurrentTableData();
        
        currentRowConfig.splice(index, 1);
        populateSimpleControls();
        updateTableStructure();
        
        showToast(`Row "${rowName}" removed successfully`, 'success');
    }
}

/**
 * Edit column inline
 */
function editColumn(index) {
    if (index < 0 || index >= currentColumnConfig.length) return;
    
    const column = currentColumnConfig[index];
    const newLabel = prompt('Enter new column name:', column.label);
    
    if (newLabel && newLabel.trim() && newLabel !== column.label) {
        const trimmedLabel = newLabel.trim();
        
        // Check for duplicates
        if (currentColumnConfig.some((col, i) => i !== index && col.label.toLowerCase() === trimmedLabel.toLowerCase())) {
            showToast('Column name already exists', 'error');
            return;
        }
        
        // Collect current data before change
        collectCurrentTableData();
        
        // Update column
        currentColumnConfig[index].label = trimmedLabel;
        currentColumnConfig[index].id = trimmedLabel.toLowerCase().replace(/\s+/g, '_');
        
        // Refresh controls and table
        populateSimpleControls();
        updateTableStructure();
        
        showToast(`Column renamed to "${trimmedLabel}"`, 'success');
    }
}

/**
 * Edit row inline
 */
function editRow(index) {
    if (index < 0 || index >= currentRowConfig.length) return;
    
    const row = currentRowConfig[index];
    const newLabel = prompt('Enter new row name:', row.label);
    
    if (newLabel && newLabel.trim() && newLabel !== row.label) {
        const trimmedLabel = newLabel.trim();
        
        // Check for duplicates
        if (currentRowConfig.some((r, i) => i !== index && r.label.toLowerCase() === trimmedLabel.toLowerCase())) {
            showToast('Row name already exists', 'error');
            return;
        }
        
        // Collect current data before change
        collectCurrentTableData();
        
        // Update row
        currentRowConfig[index].label = trimmedLabel;
        currentRowConfig[index].id = trimmedLabel.toLowerCase().replace(/\s+/g, '_');
        
        // Refresh controls and table
        populateSimpleControls();
        updateTableStructure();
        
        showToast(`Row renamed to "${trimmedLabel}"`, 'success');
    }
}

/**
 * Collect current table data before structure changes
 */
function collectCurrentTableData() {
    const data = {};
    
    // Collect data from input fields
    document.querySelectorAll('.data-input').forEach(input => {
        const rowId = input.dataset.row;
        const columnId = input.dataset.column;
        
        if (rowId && columnId) {
            if (!data[rowId]) {
                data[rowId] = {};
            }
            data[rowId][columnId] = parseFloat(input.value) || 0;
        }
    });
    
    // Store data globally for preservation
    window.currentTableData = data;
    
    return data;
}

/**
 * Update table structure based on current config
 */
function updateTableStructure() {
    const table = document.getElementById('editableDataTable');
    if (!table) return;
    
    // Update hidden fields
    const rowConfigField = document.getElementById('row_config');
    const columnConfigField = document.getElementById('column_config');
    
    if (rowConfigField) {
        rowConfigField.value = JSON.stringify({ rows: currentRowConfig });
    }
    if (columnConfigField) {
        columnConfigField.value = JSON.stringify({ columns: currentColumnConfig });
    }
    
    // Rebuild table structure
    rebuildEditableTable();
}

/**
 * Rebuild the editable table with current structure and preserve data
 */
function rebuildEditableTable() {
    const table = document.getElementById('editableDataTable');
    if (!table) return;
    
    // Get preserved data
    const preservedData = window.currentTableData || {};
    
    // Rebuild header with enhanced styling
    const thead = table.querySelector('thead');
    if (thead) {
        thead.innerHTML = `
            <tr>
                <th style="width: 180px; background: #f8f9fa;">
                    <div class="fw-bold text-primary">
                        <i class="fas fa-list me-2"></i>Row
                    </div>
                </th>
                ${currentColumnConfig.map(col => `
                    <th class="text-center" data-column-id="${col.id}" style="min-width: 120px; background: #f8f9fa;">
                        <div class="fw-bold text-success">${col.label}</div>
                        ${col.unit ? `<small class="text-muted">(${col.unit})</small>` : ''}
                        <small class="text-muted d-block">${col.type}</small>
                    </th>
                `).join('')}
            </tr>
        `;
    }
    
    // Rebuild body with preserved data
    const tbody = table.querySelector('tbody');
    if (tbody) {
        tbody.innerHTML = currentRowConfig.map(row => `
            <tr data-row-id="${row.id}" class="${row.type === 'separator' ? 'table-secondary' : ''}">
                <td class="fw-bold position-sticky start-0 bg-light">
                    <span class="row-badge ${row.type === 'calculated' ? 'calculated' : ''}">${row.label}</span>
                    <small class="text-muted d-block">${row.type}</small>
                </td>
                ${currentColumnConfig.map(col => `
                    <td class="text-center">
                        ${row.type === 'separator' ? '—' : 
                          row.type === 'calculated' ? '<span class="calculated-value fw-bold text-info">0</span>' :
                          `<input type="number" 
                                  class="form-control form-control-sm data-input text-end" 
                                  data-row="${row.id}" 
                                  data-column="${col.id}" 
                                  value="${preservedData[row.id] && preservedData[row.id][col.id] !== undefined ? preservedData[row.id][col.id] : 0}" 
                                  step="0.01"
                                  style="min-width: 100px;">`}
                    </td>
                `).join('')}
            </tr>
        `).join('');
        
        // Add total row if there are numeric columns
        const numericColumns = currentColumnConfig.filter(col => ['number', 'currency'].includes(col.type));
        if (numericColumns.length > 0) {
            tbody.innerHTML += `
                <tr class="table-light total-row border-top border-2">
                    <td class="fw-bold position-sticky start-0 bg-light">
                        <span class="total-badge">TOTAL</span>
                    </td>
                    ${currentColumnConfig.map(col => `
                        <td class="fw-bold text-end text-primary" data-column="${col.id}">
                            ${['number', 'currency'].includes(col.type) ? '0.00' : '—'}
                        </td>
                    `).join('')}
                </tr>
            `;
        }
    }
    
    // Reattach event listeners
    attachInputListeners();
    
    // Update calculations and totals
    updateCalculations();
    updateTotals();
    
    console.log('Table rebuilt with preserved data:', preservedData);
}

/**
 * Attach event listeners to data inputs
 */
function attachInputListeners() {
    document.querySelectorAll('.data-input').forEach(input => {
        input.addEventListener('input', function() {
            updateCalculations();
            updateTotals();
        });
    });
}

/**
 * Update calculations for calculated rows
 */
function updateCalculations() {
    // Implementation for updating calculated values
    console.log('Updating calculations');
}

/**
 * Update totals row
 */
function updateTotals() {
    const totalRow = document.querySelector('.total-row');
    if (!totalRow) return;
    
    currentColumnConfig.forEach(col => {
        if (['number', 'currency'].includes(col.type)) {
            let total = 0;
            document.querySelectorAll(`[data-row][data-column="${col.id}"]`).forEach(input => {
                const value = parseFloat(input.value) || 0;
                total += value;
            });
            
            const totalCell = totalRow.querySelector(`[data-column="${col.id}"]`);
            if (totalCell) {
                if (col.type === 'currency') {
                    totalCell.textContent = 'RM ' + total.toFixed(2);
                } else {
                    totalCell.textContent = total.toFixed(2);
                }
            }
        }
    });
}

/**
 * Save flexible outcome
 */
function saveFlexibleOutcome() {
    const saveBtn = document.getElementById('saveOutcomeBtn');
    if (!saveBtn) return;
    const originalText = saveBtn.innerHTML;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';
    saveBtn.disabled = true;
    try {
        // Collect data from inputs and serialize as PHP expects
        const data = {};
        currentRowConfig.forEach(row => {
            if (row.type === 'data') {
                // Build array of values in column order
                const rowValues = [];
                currentColumnConfig.forEach(col => {
                    const input = document.querySelector(
                        `[data-row-id="${row.id}"][data-column-id="${col.id}"]`
                    );
                    let value = '';
                    if (input) {
                        value = input.value !== '' ? parseFloat(input.value) : '';
                    }
                    rowValues.push(value);
                });
                data[row.id] = rowValues;
            }
        });
        // Add columns array for reference
        data.columns = currentColumnConfig.map(col => col.id);
        // Set the JSON data in the hidden field
        const dataJsonField = document.getElementById('data_json');
        if (dataJsonField) {
            dataJsonField.value = JSON.stringify(data);
        }
        // Submit the form
        const form = document.getElementById('editFlexibleOutcomeForm');
        if (form) {
            form.submit();
        }
    } catch (error) {
        console.error('Error saving outcome:', error);
        alert('Error saving outcome. Please try again.');
        // Restore button state
        saveBtn.innerHTML = originalText;
        saveBtn.disabled = false;
    }
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existingToasts = document.querySelectorAll('.outcome-toast');
    existingToasts.forEach(toast => toast.remove());
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `outcome-toast alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : type === 'warning' ? 'warning' : 'info'} alert-dismissible fade show`;
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 9999;
        min-width: 300px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
    `;
    
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 5000);
}

/**
 * Global error handler for table designer actions
 */
function handleTableDesignerAction(action, ...args) {
    try {
        switch (action) {
            case 'addColumn':
                addNewColumn();
                break;
            case 'addRow':
                addNewRow();
                break;
            case 'removeColumn':
                removeColumn(args[0]);
                break;
            case 'removeRow':
                removeRow(args[0]);
                break;
            default:
                console.warn(`Unknown table designer action: ${action}`);
                return false;
        }
        return true;
    } catch (error) {
        console.error(`Error executing table designer action "${action}":`, error);
        return false;
    }
}

// Make functions globally available
window.handleTableDesignerAction = handleTableDesignerAction;
window.addNewColumn = addNewColumn;
window.addNewRow = addNewRow;
window.removeColumn = removeColumn;
window.removeRow = removeRow;
window.saveFlexibleOutcome = saveFlexibleOutcome;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Check if this script should be disabled (for compatibility with other edit pages)
    if (window.editOutcomeJsDisabled) {
        console.log('edit-outcome.js disabled by page request');
        return;
    }
    
    // Add small delay to ensure all scripts are loaded
    setTimeout(function() {
        initFlexibleOutcomeEditor();
        
        // Set up save button functionality
        const saveBtn = document.getElementById('saveOutcomeBtn');
        if (saveBtn) {
            saveBtn.addEventListener('click', function() {
                saveFlexibleOutcome();
            });
        }
        
        // Set up real-time calculation updates
        attachInputListeners();
        
        // Initial calculation update
        updateCalculations();
        updateTotals();
        
    }, 100);
});
