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
 * Populate existing columns and rows in the controls
 */
function populateSimpleControls() {
    const columnsList = document.getElementById('columnsList');
    const rowsList = document.getElementById('rowsList');
    
    if (columnsList && currentColumnConfig) {
        columnsList.innerHTML = currentColumnConfig.map((col, index) => `
            <div class="d-flex align-items-center justify-content-between mb-2 p-2 border rounded">
                <span class="text-success">${col.label} (${col.type})</span>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeColumn(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');
    }
    
    if (rowsList && currentRowConfig) {
        rowsList.innerHTML = currentRowConfig.map((row, index) => `
            <div class="d-flex align-items-center justify-content-between mb-2 p-2 border rounded">
                <span class="text-primary">${row.label} (${row.type})</span>
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="removeRow(${index})">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `).join('');
    }
}

/**
 * Add new column
 */
function addNewColumn() {
    const nameInput = document.getElementById('newColumnName');
    const typeSelect = document.getElementById('newColumnType');
    
    if (!nameInput || !typeSelect || !nameInput.value.trim()) {
        alert('Please enter a column name');
        return;
    }
    
    const newColumn = {
        id: nameInput.value.toLowerCase().replace(/\s+/g, '_'),
        label: nameInput.value.trim(),
        type: typeSelect.value,
        unit: ''
    };
    
    currentColumnConfig.push(newColumn);
    
    // Clear inputs
    nameInput.value = '';
    typeSelect.value = 'number';
    
    // Refresh controls and table
    populateSimpleControls();
    updateTableStructure();
}

/**
 * Add new row
 */
function addNewRow() {
    const nameInput = document.getElementById('newRowName');
    const typeSelect = document.getElementById('newRowType');
    
    if (!nameInput || !typeSelect || !nameInput.value.trim()) {
        alert('Please enter a row name');
        return;
    }
    
    const newRow = {
        id: nameInput.value.toLowerCase().replace(/\s+/g, '_'),
        label: nameInput.value.trim(),
        type: typeSelect.value
    };
    
    currentRowConfig.push(newRow);
    
    // Clear inputs
    nameInput.value = '';
    typeSelect.value = 'data';
    
    // Refresh controls and table
    populateSimpleControls();
    updateTableStructure();
}

/**
 * Remove column by index
 */
function removeColumn(index) {
    if (confirm('Are you sure you want to remove this column?')) {
        currentColumnConfig.splice(index, 1);
        populateSimpleControls();
        updateTableStructure();
    }
}

/**
 * Remove row by index
 */
function removeRow(index) {
    if (confirm('Are you sure you want to remove this row?')) {
        currentRowConfig.splice(index, 1);
        populateSimpleControls();
        updateTableStructure();
    }
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
 * Rebuild the editable table with current structure
 */
function rebuildEditableTable() {
    const table = document.getElementById('editableDataTable');
    if (!table) return;
    
    // Rebuild header
    const thead = table.querySelector('thead');
    if (thead) {
        thead.innerHTML = `
            <tr>
                <th style="width: 150px;">Row</th>
                ${currentColumnConfig.map(col => `
                    <th class="text-center" data-column-id="${col.id}">
                        <div>${col.label}</div>
                        ${col.unit ? `<small class="text-muted">(${col.unit})</small>` : ''}
                    </th>
                `).join('')}
            </tr>
        `;
    }
    
    // Rebuild body
    const tbody = table.querySelector('tbody');
    if (tbody) {
        tbody.innerHTML = currentRowConfig.map(row => `
            <tr data-row-id="${row.id}" class="${row.type === 'separator' ? 'table-secondary' : ''}">
                <td>
                    <span class="row-badge ${row.type === 'calculated' ? 'calculated' : ''}">${row.label}</span>
                </td>
                ${currentColumnConfig.map(col => `
                    <td class="text-center">
                        ${row.type === 'separator' ? '—' : 
                          row.type === 'calculated' ? '<span class="calculated-value">0</span>' :
                          `<input type="number" class="form-control form-control-sm data-input text-end" 
                                  data-row="${row.id}" data-column="${col.id}" value="0" step="0.01">`}
                    </td>
                `).join('')}
            </tr>
        `).join('');
        
        // Add total row if there are numeric columns
        const numericColumns = currentColumnConfig.filter(col => ['number', 'currency'].includes(col.type));
        if (numericColumns.length > 0) {
            tbody.innerHTML += `
                <tr class="table-light total-row">
                    <td class="fw-bold">
                        <span class="total-badge">TOTAL</span>
                    </td>
                    ${currentColumnConfig.map(col => `
                        <td class="fw-bold text-end" data-column="${col.id}">
                            ${['number', 'currency'].includes(col.type) ? '0' : '—'}
                        </td>
                    `).join('')}
                </tr>
            `;
        }
    }
    
    // Reattach event listeners
    attachInputListeners();
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
        // Collect data from inputs
        const data = {};
        
        currentRowConfig.forEach(row => {
            if (row.type === 'data') {
                data[row.id] = {};
                document.querySelectorAll(`[data-row="${row.id}"].data-input`).forEach(input => {
                    const columnId = input.dataset.column;
                    data[row.id][columnId] = parseFloat(input.value) || 0;
                });
            }
        });
        
        // Update the outcome data structure
        const outcomeData = {
            data: data,
            columns: currentColumnConfig.map(col => col.id),
            structure_type: 'flexible'
        };
        
        // Set the JSON data in the hidden field
        const dataJsonField = document.getElementById('data_json');
        if (dataJsonField) {
            dataJsonField.value = JSON.stringify(outcomeData);
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
