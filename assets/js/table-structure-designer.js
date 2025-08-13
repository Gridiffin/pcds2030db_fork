/**
 * Table Structure Designer
 * 
 * JavaScript library for creating flexible data table structures
 * beyond the traditional monthly format.
 */

// Prevent redeclaration
if (typeof TableStructureDesigner === 'undefined') {
    class TableStructureDesigner {
        constructor(options = {}) {
            this.container = options.container || '#table-designer-container';
            this.previewContainer = options.previewContainer || '#table-preview-container';
        // Set default structure type to custom (only option now)
        this.structureType = 'custom';
            this.rows = options.rows || [];
            this.columns = options.columns || [];
            this.onStructureChange = options.onStructureChange || function() {};
            
            // Initialize calculation engine
            this.calculationEngine = new TableCalculationEngine();
            
            this.init();
        }
    
    init() {
        this.renderCustomGuide();
        this.renderRowDesigner();
        this.renderColumnDesigner();
        this.renderCalculationDesigner();
        this.populatePresetRows(); // Initialize with one sample row for custom structure
        this.renderPreview();
        this.bindEvents();
    }
    
    renderCustomGuide() {
        const container = document.querySelector(this.container);
        if (!container) return;
        
        // Always use custom structure - show helpful guide with permanent alert
        const customGuideHTML = `
            <div class="custom-structure-guide mb-4">
                <div class="alert alert-info border-0 shadow-sm" role="alert">
                    <div class="d-flex align-items-center mb-3">
                        <i class="fas fa-cogs me-3 fs-4 text-primary"></i>
                        <h5 class="mb-0 text-primary">Custom Table Designer</h5>
                    </div>
                    <p class="mb-3">
                        <strong>Design your outcome table with complete flexibility:</strong>
                    </p>
                    <ul class="mb-0 ps-4">
                        <li class="mb-2">Create custom rows to organize your data (e.g., metrics, categories, time periods)</li>
                        <li class="mb-2">Add columns for different measurements, targets, or data types</li>
                        <li class="mb-2">Choose data types: Numbers, Currency, Percentages, or Text</li>
                        <li class="mb-0">Build calculated rows that automatically sum or compute values</li>
                    </ul>
                </div>
            </div>
        `;
        
        container.innerHTML = customGuideHTML;
    }
    
    renderRowDesigner() {
        const container = document.querySelector(this.container);
        if (!container) return;
        
        const rowDesignerHTML = `
            <div class="row-designer mb-4">
                <h5 class="mb-3">
                    <i class="fas fa-list me-2"></i>Row Configuration
                </h5>
                <div class="row">
                    <div class="col-md-8">
                        <div id="rows-list" class="border rounded p-3">
                            ${this.renderRowsList()}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="add-row-form">
                            <div class="mb-3">
                                <label class="form-label">Row Label</label>
                                <input type="text" class="form-control" id="new-row-label" placeholder="Enter row name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Row Type</label>
                                <select class="form-select" id="new-row-type">
                                    <option value="data">Data Entry</option>
                                    <option value="calculated">Calculated</option>
                                    <option value="separator">Separator</option>
                                </select>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm w-100" id="add-row-btn">
                                <i class="fas fa-plus me-1"></i>Add Row
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', rowDesignerHTML);
    }
    
    renderColumnDesigner() {
        const container = document.querySelector(this.container);
        if (!container) return;
        
        const columnDesignerHTML = `
            <div class="column-designer mb-4">
                <h5 class="mb-3">
                    <i class="fas fa-columns me-2"></i>Column Configuration
                </h5>
                <div class="row">
                    <div class="col-md-8">
                        <div id="columns-list" class="border rounded p-3">
                            ${this.renderColumnsList()}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="add-column-form">
                            <div class="mb-3">
                                <label class="form-label">Column Label</label>
                                <input type="text" class="form-control" id="new-column-label" placeholder="Enter column name">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Data Type</label>
                                <select class="form-select" id="new-column-type">
                                    <option value="number">Number</option>
                                    <option value="currency">Currency</option>
                                    <option value="percentage">Percentage</option>
                                    <option value="text">Text</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Unit</label>
                                <input type="text" class="form-control" id="new-column-unit" placeholder="e.g., RM, %, m³">
                            </div>
                            <button type="button" class="btn btn-primary btn-sm w-100" id="add-column-btn">
                                <i class="fas fa-plus me-1"></i>Add Column
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', columnDesignerHTML);
    }
    
    renderCalculationDesigner() {
        const container = document.querySelector(this.container);
        if (!container) return;
        
        const calculationTypes = TableCalculationEngine.getCalculationTypes();
        
        const calculationDesignerHTML = `
            <div class="calculation-designer mb-4">
                <h5 class="mb-3">
                    <i class="fas fa-calculator me-2"></i>Auto-Calculation Rows
                    <small class="text-muted">(Optional)</small>
                </h5>
                <div class="row">
                    <div class="col-md-4">
                        <div class="calculation-types">
                            <h6 class="fw-bold">Calculation Type</h6>
                            ${calculationTypes.map(type => `
                                <div class="form-check mb-2">
                                    <input class="form-check-input calculation-type-radio" type="radio" name="calculationType" value="${type.id}" id="calc-${type.id}">
                                    <label class="form-check-label" for="calc-${type.id}">
                                        <i class="fas ${type.icon} me-2"></i>${type.name}
                                        <br><small class="text-muted">${type.description}</small>
                                    </label>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="calculation-config">
                            <h6 class="fw-bold">Configuration</h6>
                            <div id="calculation-config-area">
                                <p class="text-muted">Select a calculation type to configure.</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="calculation-preview">
                            <h6 class="fw-bold">Added Calculations</h6>
                            <div id="calculation-list">
                                <p class="text-muted">No calculations added yet.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        container.insertAdjacentHTML('beforeend', calculationDesignerHTML);
        
        // Bind calculation events
        this.bindCalculationEvents();
    }
    
    renderRowsList() {
        if (this.rows.length === 0) {
            return '<p class="text-muted text-center py-3">No rows defined. Add rows to customize your table structure.</p>';
        }
        
        return this.rows.map((row, index) => `
            <div class="row-item d-flex justify-content-between align-items-center p-2 border-bottom" data-row-id="${row.id}" data-row-index="${index}">
                <div class="row-info">
                    <strong>${row.label}</strong>
                    <span class="badge bg-secondary ms-2">${row.type}</span>
                </div>
                <div class="row-actions">
                    <button type="button" class="btn btn-sm btn-outline-primary me-1 edit-row-btn" title="Edit row">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger me-1 remove-row-btn" title="Remove row">
                        <i class="fas fa-trash"></i>
                    </button>
                    <div class="btn-group">
                        <button type="button" class="btn btn-sm btn-outline-secondary move-row-up-btn" ${index === 0 ? 'disabled' : ''} title="Move up">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary move-row-down-btn" ${index === this.rows.length - 1 ? 'disabled' : ''} title="Move down">
                            <i class="fas fa-arrow-down"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    renderColumnsList() {
        if (this.columns.length === 0) {
            return '<p class="text-muted text-center py-3">No columns defined. Add columns to store your data.</p>';
        }
        
        return this.columns.map((column, index) => `
            <div class="column-item d-flex justify-content-between align-items-center p-2 border-bottom" data-column-id="${column.id}">
                <div class="column-info">
                    <strong>${column.label}</strong>
                    <span class="badge bg-info ms-2">${column.type}</span>
                    ${column.unit ? `<small class="text-muted ms-2">(${column.unit})</small>` : ''}
                </div>
                <div class="column-actions">
                    <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="window.handleTableDesignerAction('editColumn', ${index})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="window.handleTableDesignerAction('removeColumn', ${index})">
                        <i class="fas fa-trash"></i>
                    </button>
                    <div class="btn-group ms-1">
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.handleTableDesignerAction('moveColumnLeft', ${index})" ${index === 0 ? 'disabled' : ''}>
                            <i class="fas fa-arrow-left"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.handleTableDesignerAction('moveColumnRight', ${index})" ${index === this.columns.length - 1 ? 'disabled' : ''}>
                            <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    renderPreview() {
        const previewContainer = document.querySelector(this.previewContainer);
        if (!previewContainer) return;
        
        let rows = this.getEffectiveRows();
        
        if (rows.length === 0 || this.columns.length === 0) {
            previewContainer.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    Configure table structure and add columns to see preview
                </div>
            `;
            return;
        }
        
        const tableHTML = `
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 150px;">Row</th>
                            ${this.columns.map(col => `
                                <th class="text-center">
                                    <div>${col.label}</div>
                                    ${col.unit ? `<small class="text-muted">(${col.unit})</small>` : ''}
                                </th>
                            `).join('')}
                        </tr>
                    </thead>
                    <tbody>
                        ${rows.map(row => `
                            <tr class="${row.type === 'separator' ? 'table-secondary' : ''}">
                                <td>
                                    <span class="row-badge ${row.type === 'calculated' ? 'calculated' : ''}">${row.label}</span>
                                </td>
                                ${this.columns.map(col => `
                                    <td class="text-center ${row.type === 'separator' ? '' : 'preview-cell'}">
                                        ${row.type === 'separator' ? '—' : this.getPreviewCellContent(col.type)}
                                    </td>
                                `).join('')}
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;
        
        previewContainer.innerHTML = tableHTML;
    }
    
    getEffectiveRows() {
        let currentYear;
        
        switch (this.structureType) {
            case 'monthly':
                return [
                    {id: 'January', label: 'January', type: 'data'},
                    {id: 'February', label: 'February', type: 'data'},
                    {id: 'March', label: 'March', type: 'data'},
                    {id: 'April', label: 'April', type: 'data'},
                    {id: 'May', label: 'May', type: 'data'},
                    {id: 'June', label: 'June', type: 'data'},
                    {id: 'July', label: 'July', type: 'data'},
                    {id: 'August', label: 'August', type: 'data'},
                    {id: 'September', label: 'September', type: 'data'},
                    {id: 'October', label: 'October', type: 'data'},
                    {id: 'November', label: 'November', type: 'data'},
                    {id: 'December', label: 'December', type: 'data'}
                ];
            case 'quarterly':
                return [
                    {id: 'Q1', label: 'Q1', type: 'data'},
                    {id: 'Q2', label: 'Q2', type: 'data'},
                    {id: 'Q3', label: 'Q3', type: 'data'},
                    {id: 'Q4', label: 'Q4', type: 'data'}
                ];
            case 'yearly':
                currentYear = new Date().getFullYear();
                return [
                    {id: (currentYear-2).toString(), label: (currentYear-2).toString(), type: 'data'},
                    {id: (currentYear-1).toString(), label: (currentYear-1).toString(), type: 'data'},
                    {id: currentYear.toString(), label: currentYear.toString(), type: 'data'},
                    {id: (currentYear+1).toString(), label: (currentYear+1).toString(), type: 'data'},
                    {id: (currentYear+2).toString(), label: (currentYear+2).toString(), type: 'data'}
                ];
            case 'custom':
                return this.rows;
            default:
                return [];
        }
    }
    
    getPreviewCellContent(type) {
        switch (type) {
            case 'currency':
                return '1,000.00';
            case 'percentage':
                return '75.5%';
            case 'number':
                return '100.0';
            case 'text':
                return 'Sample';
            default:
                return '—';
        }
    }
    
    bindEvents() {
        // Structure type is now fixed to custom - remove structure type change handlers
        // Custom structure is always active, no need for switching logic
        
        // Add row button
        document.addEventListener('click', (e) => {
            if (e.target.id === 'add-row-btn' || e.target.closest('#add-row-btn')) {
                this.addRow();
            }
        });
        
        // Add column button
        document.addEventListener('click', (e) => {
            if (e.target.id === 'add-column-btn' || e.target.closest('#add-column-btn')) {
                this.addColumn();
            }
        });
        
        // Apply calculation button
        document.addEventListener('click', (e) => {
            if (e.target.id === 'apply-calculation-btn' || e.target.closest('#apply-calculation-btn')) {
                this.applyCalculation();
            }
        });
        
        // Row action buttons with event delegation
        document.addEventListener('click', (e) => {
            const rowItem = e.target.closest('.row-item');
            if (!rowItem) return;
            
            const rowIndex = parseInt(rowItem.dataset.rowIndex);
            if (isNaN(rowIndex)) return;
            
            if (e.target.closest('.edit-row-btn')) {
                this.editRow(rowIndex);
            } else if (e.target.closest('.remove-row-btn')) {
                this.removeRow(rowIndex);
            } else if (e.target.closest('.move-row-up-btn')) {
                this.moveRowUp(rowIndex);
            } else if (e.target.closest('.move-row-down-btn')) {
                this.moveRowDown(rowIndex);
            }
        });
    }
    
    
    // Custom structure only - no preset row population needed
    populatePresetRows() {
        // Custom structure starts with empty rows - user will add their own
        // No preset rows needed since we only support custom structure
        if (this.rows.length === 0) {
            // Add one sample row to get started
            this.rows = [
                { id: 'sample_row', label: 'Sample Row', type: 'data' }
            ];
        }
        
        // Update rows list display if it exists
        const rowsList = document.getElementById('rows-list');
        if (rowsList) {
            const newHTML = this.renderRowsList();
            rowsList.innerHTML = newHTML;
        } else {
            console.warn('rows-list element not found when trying to update');
        }
    }
    
    addRow() {
        const labelInput = document.getElementById('new-row-label');
        const typeSelect = document.getElementById('new-row-type');
        
        const label = labelInput.value.trim();
        const type = typeSelect.value;
        
        if (!label) {
            alert('Please enter a row label');
            return;
        }
        
        const id = label.toLowerCase().replace(/\s+/g, '_');
        
        // Check for duplicate IDs
        if (this.rows.find(row => row.id === id)) {
            alert('A row with this name already exists');
            return;
        }
        
        this.rows.push({ id, label, type });
        
        // Clear inputs
        labelInput.value = '';
        typeSelect.value = 'data';
        
        // Re-render
        document.getElementById('rows-list').innerHTML = this.renderRowsList();
        this.renderPreview();
        this.onStructureChange(this.getStructureData());
    }
    
    addColumn() {
        const labelInput = document.getElementById('new-column-label');
        const typeSelect = document.getElementById('new-column-type');
        const unitInput = document.getElementById('new-column-unit');
        
        const label = labelInput.value.trim();
        const type = typeSelect.value;
        const unit = unitInput.value.trim();
        
        if (!label) {
            alert('Please enter a column label');
            return;
        }
        
        const id = label.toLowerCase().replace(/\s+/g, '_');
        
        // Check for duplicate IDs
        if (this.columns.find(col => col.id === id)) {
            alert('A column with this name already exists');
            return;
        }
        
        this.columns.push({ id, label, type, unit });
        
        // Clear inputs
        labelInput.value = '';
        typeSelect.value = 'number';
        unitInput.value = '';
        
        // Re-render
        document.getElementById('columns-list').innerHTML = this.renderColumnsList();
        this.renderPreview();
        this.onStructureChange(this.getStructureData());
    }
    
    applyCalculation() {
        const formulaInput = document.getElementById('calculation-formula');
        const formula = formulaInput.value.trim();
        
        if (!formula) {
            alert('Please enter a calculation formula');
            return;
        }
        
        // TODO: Implement calculation parsing and application logic
        alert('Calculation applied: ' + formula);
    }
    
    removeRow(index) {
        if (confirm('Are you sure you want to remove this row?')) {
            this.rows.splice(index, 1);
            document.getElementById('rows-list').innerHTML = this.renderRowsList();
            this.renderPreview();
            this.onStructureChange(this.getStructureData());
        }
    }
    
    removeColumn(index) {
        if (confirm('Are you sure you want to remove this column?')) {
            this.columns.splice(index, 1);
            document.getElementById('columns-list').innerHTML = this.renderColumnsList();
            this.renderPreview();
            this.onStructureChange(this.getStructureData());
        }
    }
    
    moveRowUp(index) {
        if (index > 0) {
            [this.rows[index], this.rows[index - 1]] = [this.rows[index - 1], this.rows[index]];
            document.getElementById('rows-list').innerHTML = this.renderRowsList();
            this.renderPreview();
            this.onStructureChange(this.getStructureData());
        }
    }
    
    moveRowDown(index) {
        if (index < this.rows.length - 1) {
            [this.rows[index], this.rows[index + 1]] = [this.rows[index + 1], this.rows[index]];
            document.getElementById('rows-list').innerHTML = this.renderRowsList();
            this.renderPreview();
            this.onStructureChange(this.getStructureData());
        }
    }
    
    moveColumnLeft(index) {
        if (index > 0) {
            [this.columns[index], this.columns[index - 1]] = [this.columns[index - 1], this.columns[index]];
            document.getElementById('columns-list').innerHTML = this.renderColumnsList();
            this.renderPreview();
            this.onStructureChange(this.getStructureData());
        }
    }
    
    moveColumnRight(index) {
        if (index < this.columns.length - 1) {
            [this.columns[index], this.columns[index + 1]] = [this.columns[index + 1], this.columns[index]];
            document.getElementById('columns-list').innerHTML = this.renderColumnsList();
            this.renderPreview();
            this.onStructureChange(this.getStructureData());
        }
    }
    
    editRow(index) {
        const row = this.rows[index];
        const newLabel = prompt('Enter new row label:', row.label);
        if (newLabel && newLabel.trim()) {
            this.rows[index].label = newLabel.trim();
            this.rows[index].id = newLabel.toLowerCase().replace(/\s+/g, '_');
            document.getElementById('rows-list').innerHTML = this.renderRowsList();
            this.renderPreview();
            this.onStructureChange(this.getStructureData());
        }
    }
    
    editColumn(index) {
        const column = this.columns[index];
        const newLabel = prompt('Enter new column label:', column.label);
        if (newLabel && newLabel.trim()) {
            this.columns[index].label = newLabel.trim();
            this.columns[index].id = newLabel.toLowerCase().replace(/\s+/g, '_');
            document.getElementById('columns-list').innerHTML = this.renderColumnsList();
            this.renderPreview();
            this.onStructureChange(this.getStructureData());
        }
    }
    
    getStructureData() {
        return {
            structureType: this.structureType,
            rows: this.getEffectiveRows(),
            columns: this.columns,
            calculations: Array.from(this.calculationEngine.calculationRows.entries()),
            rowConfig: {
                type: this.structureType,
                rows: this.getEffectiveRows(),
                calculations: Array.from(this.calculationEngine.calculationRows.entries())
            },
            columnConfig: {
                columns: this.columns
            }
        };
    }
    
    setStructureData(data) {
        this.structureType = 'custom'; // Always use custom structure
        this.rows = data.rows || [];
        this.columns = data.columns || [];
        
        // Re-render everything
        this.renderCustomGuide();
        this.renderRowDesigner();
        this.renderColumnDesigner();
        this.renderCalculationDesigner();
        this.renderPreview();
    }
    
    bindCalculationEvents() {
        // Calculation type selection
        document.addEventListener('change', (e) => {
            if (e.target.classList.contains('calculation-type-radio')) {
                this.showCalculationConfig(e.target.value);
            }
        });
        
        // Add calculation button
        document.addEventListener('click', (e) => {
            if (e.target.id === 'add-calculation-btn') {
                this.addCalculationRow();
            }
        });
        
        // Remove calculation
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('remove-calculation')) {
                const calcId = e.target.getAttribute('data-calc-id');
                this.removeCalculationRow(calcId);
            }
        });
    }
    
    showCalculationConfig(calculationType) {
        const configArea = document.getElementById('calculation-config-area');
        let configHTML = '';
        
        switch (calculationType) {
            case 'sum':
            case 'average':
                configHTML = `
                    <div class="mb-3">
                        <label class="form-label">Row Label</label>
                        <input type="text" class="form-control" id="calc-row-label" placeholder="e.g., Total">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Source Rows</label>
                        <select class="form-select" id="calc-source-rows" multiple>
                            ${this.getEffectiveRows().map(row => 
                                `<option value="${row.id}">${row.label}</option>`
                            ).join('')}
                        </select>
                        <small class="text-muted">Hold Ctrl/Cmd to select multiple rows</small>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="add-calculation-btn">
                        <i class="fas fa-plus me-1"></i>Add ${calculationType === 'sum' ? 'Sum' : 'Average'} Row
                    </button>
                `;
                break;
            case 'percentage':
                configHTML = `
                    <div class="mb-3">
                        <label class="form-label">Row Label</label>
                        <input type="text" class="form-control" id="calc-row-label" placeholder="e.g., Completion Rate">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Numerator Row</label>
                        <select class="form-select" id="calc-numerator-row">
                            ${this.getEffectiveRows().map(row => 
                                `<option value="${row.id}">${row.label}</option>`
                            ).join('')}
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Denominator Row</label>
                        <select class="form-select" id="calc-denominator-row">
                            ${this.getEffectiveRows().map(row => 
                                `<option value="${row.id}">${row.label}</option>`
                            ).join('')}
                        </select>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="add-calculation-btn">
                        <i class="fas fa-plus me-1"></i>Add Percentage Row
                    </button>
                `;
                break;
            case 'formula':
                configHTML = `
                    <div class="mb-3">
                        <label class="form-label">Row Label</label>
                        <input type="text" class="form-control" id="calc-row-label" placeholder="e.g., Custom Calculation">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Formula</label>
                        <input type="text" class="form-control" id="calc-formula" placeholder="e.g., [row1] + [row2] * 2">
                        <small class="text-muted">Use [rowId] to reference row values. Supports +, -, *, /, ()</small>
                    </div>
                    <div class="mb-3">
                        <h6>Available Rows:</h6>
                        <div class="row-reference-list">
                            ${this.getEffectiveRows().map(row => 
                                `<span class="badge bg-secondary me-1 mb-1">[${row.id}] ${row.label}</span>`
                            ).join('')}
                        </div>
                    </div>
                    <button type="button" class="btn btn-success btn-sm" id="add-calculation-btn">
                        <i class="fas fa-plus me-1"></i>Add Formula Row
                    </button>
                `;
                break;
        }
        
        configArea.innerHTML = configHTML;
    }
    
    addCalculationRow() {
        const calculationType = document.querySelector('input[name="calculationType"]:checked')?.value;
        const rowLabel = document.getElementById('calc-row-label')?.value;
        
        if (!calculationType || !rowLabel) {
            alert('Please select a calculation type and provide a row label.');
            return;
        }
        
        let sourceRows, formula, validation;
        
        switch (calculationType) {
            case 'sum':
            case 'average':
                sourceRows = Array.from(document.getElementById('calc-source-rows').selectedOptions)
                    .map(option => option.value);
                if (sourceRows.length === 0) {
                    alert('Please select at least one source row.');
                    return;
                }
                break;
            case 'percentage':
                break;
            case 'formula':
                formula = document.getElementById('calc-formula').value;
                validation = TableCalculationEngine.validateFormula(formula);
                if (!validation.valid) {
                    alert('Invalid formula: ' + validation.error);
                    return;
                }
                break;
        }
        
        const calcId = 'calc_' + Date.now();
        let calculation = {
            id: calcId,
            type: calculationType,
            label: rowLabel
        };
        
        switch (calculationType) {
            case 'sum':
            case 'average':
                calculation.sourceRows = sourceRows;
                break;
            case 'percentage':
                calculation.numeratorRow = document.getElementById('calc-numerator-row').value;
                calculation.denominatorRow = document.getElementById('calc-denominator-row').value;
                break;
            case 'formula':
                calculation.formula = formula;
                break;
        }
        
        // Add to calculation engine
        this.calculationEngine.addCalculationRow(calcId, calculation);
        
        // Update display
        this.updateCalculationList();
        this.renderPreview();
        this.onStructureChange(this.getStructureData());
        
        // Clear form
        document.getElementById('calc-row-label').value = '';
        document.querySelector('input[name="calculationType"]:checked').checked = false;
        document.getElementById('calculation-config-area').innerHTML = '<p class="text-muted">Select a calculation type to configure.</p>';
    }
    
    removeCalculationRow(calcId) {
        this.calculationEngine.removeCalculationRow(calcId);
        this.updateCalculationList();
        this.renderPreview();
        this.onStructureChange(this.getStructureData());
    }
    
    updateCalculationList() {
        const listContainer = document.getElementById('calculation-list');
        const calculations = Array.from(this.calculationEngine.calculationRows.entries());
        
        if (calculations.length === 0) {
            listContainer.innerHTML = '<p class="text-muted">No calculations added yet.</p>';
            return;
        }
        
        const listHTML = calculations.map(([id, calc]) => `
            <div class="calculation-item mb-2 p-2 border rounded">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>${calc.label}</strong>
                        <br><small class="text-muted">${calc.type.toUpperCase()}</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger remove-calculation" data-calc-id="${id}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        `).join('');
        
        listContainer.innerHTML = listHTML;
    }
}

} // End of TableStructureDesigner class check

// Global reference for easier access
let tableDesigner = null;

// Initialize function
function initTableStructureDesigner(options = {}) {
    tableDesigner = new TableStructureDesigner(options);
    return tableDesigner;
}