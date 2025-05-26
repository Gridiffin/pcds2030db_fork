/**
 * Outcome Editor JavaScript
 * Handles interactive features for the sector outcomes editor
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize outcome editor functionality
    initializeOutcomeEditor();
    
    // Event delegation for delete column buttons
    const editorContainer = document.getElementById('metricEditorContainer');
    if (editorContainer) {
        editorContainer.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('delete-column-btn')) {
                console.log('Delete button clicked for column index:', e.target.dataset.columnIndex);
                e.stopPropagation();
                handleDeleteColumn(e.target);
            }
        });
    }
});

/**
 * Initialize all outcome editor functionality
 */
function initializeOutcomeEditor() {
    // Check if we're in edit mode or creating a new outcome
    const isNewOutcome = !initialMetricData || !initialMetricData.outcome_id || initialMetricData.outcome_id <= 0;
    
    if (isNewOutcome) {
        console.log('Initializing editor for new outcome creation');
        // For new outcomes, set up an empty structure
        renderOutcomeTable({ columns: [], units: [] });
        // We'll handle saving through the form submission instead of a separate AJAX call
    } else if (typeof initialMetricData !== 'undefined' && initialMetricData.data_json) {
        console.log('Initializing editor for existing outcome:', initialMetricData.outcome_id);
        renderOutcomeTable(initialMetricData.data_json);
        // Initialize outcome data saving via AJAX for existing outcomes
        initDataSaving();
    } else {
        console.error('initialMetricData or initialMetricData.data_json is not available.');
        // Render an empty table as fallback
        renderOutcomeTable({ columns: [], units: [] });
    }
    
    // Set up column addition buttons - needed for both new and existing outcomes
    setupColumnButtons();
}

/**
 * Render the outcome structure table
 * @param {object} jsonData - The data containing columns and units { columns: [], units: [] }
 */
function renderOutcomeTable(jsonData) {
    // Check if we're working with an existing table or need to create one
    let table = document.getElementById('outcomeStructureTable');
    const isNewTable = !table;
    const isPreviewTable = table && table.querySelector('thead tr th.text-center.bg-light'); // Check if it's our preview table
    
    // If we're working with a new outcome in preview mode
    if (isPreviewTable) {
        // Find the header row (second row in the preview table)
        const headerRow = table.querySelector('thead tr:nth-child(2)');
        if (!headerRow) {
            console.error('Header row not found in preview table');
            return;
        }
        
        // Clear existing columns (except the Month column)
        while (headerRow.cells.length > 1) {
            headerRow.deleteCell(1);
        }
        
        // Clear sample data rows (except the Month column)
        const dataRows = table.querySelectorAll('tbody tr:not(.bg-light)');
        dataRows.forEach(row => {
            while (row.cells.length > 1) {
                row.deleteCell(1);
            }
        });
        
        // Add columns from json data
        if (jsonData && jsonData.columns) {
            jsonData.columns.forEach((colName, index) => {
                // Add to header
                const th = document.createElement('th');
                th.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="column-name">${colName}</span><br>
                            <small class="column-unit text-muted">(${jsonData.units[colName] || jsonData.units[index] || 'N/A'})</small>
                        </div>
                        <button type="button" class="btn btn-outline-danger btn-sm delete-column-btn" data-column-index="${index}" title="Delete column">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                `;
                headerRow.appendChild(th);
                
                // Add sample data cells
                dataRows.forEach(row => {
                    const cell = row.insertCell();
                    cell.className = 'text-center text-muted';
                    cell.textContent = '0.00';
                });
            });
        }
    } 
    // If we're working with an existing outcome or need to create a new table
    else {
        const container = document.getElementById('metricEditorContainer');
        if (!container) {
            console.error('metricEditorContainer not found');
            return;
        }
        
        // Clear previous content if creating a new table
        if (isNewTable) {
            container.innerHTML = '';
            table = document.createElement('table');
            table.id = 'outcomeStructureTable';
            table.className = 'table table-bordered table-hover'; // Match the styling
            
            const thead = table.createTHead();
            const headerRow = thead.insertRow();
            
            // For existing outcomes editor, add column headers
            if (jsonData && jsonData.columns) {
                jsonData.columns.forEach((colName, index) => {
                    const th = document.createElement('th');
                    th.innerHTML = `
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="column-name">${colName}</span><br>
                                <small class="column-unit text-muted">(${jsonData.units[colName] || jsonData.units[index] || 'N/A'})</small>
                            </div>
                            <button type="button" class="btn btn-outline-danger btn-sm delete-column-btn" data-column-index="${index}" title="Delete column">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    `;
                    headerRow.appendChild(th);
                });
            }
            
            container.appendChild(table);
        }
    }
    
    // Add hidden input fields to store the structure data if in new outcome creation mode
    if (isPreviewTable && jsonData && jsonData.columns) {
        updateHiddenFields(jsonData);
    }
}


/**
 * Set up event listeners for outcome value cells
 */
function setupOutcomeValueListeners() {
    document.querySelectorAll('.outcome-value-cell').forEach(cell => {
        cell.addEventListener('click', function() {
            openValueEditor(this);
        });
    });
}

/**
 * Set up event listeners for outcome name cells
 */
function setupOutcomeNameListeners() {
    document.querySelectorAll('.outcome-name-cell').forEach(cell => {
        cell.addEventListener('click', function() {
            openNameEditor(this);
        });
    });
}

/**
 * Initialize data saving functions
 */
function initDataSaving() {
    // Check if we're in edit mode (button to save structure)
    const saveBtn = document.getElementById('saveMetricStructureBtn'); // Corrected ID
    if (saveBtn) {
        saveBtn.addEventListener('click', function() {
            saveOutcomeData();
        });
    }
    
    // createOutcomeBtn logic was commented out, assuming creation is via PHP form
}

/**
 * Set up column addition buttons
 */
function setupColumnButtons() {
    if (document.getElementById('addColumnBtn')) {
        document.getElementById('addColumnBtn').addEventListener('click', function() {
            addNewColumn();
        });
    }
}

/**
 * Open the value editor for a cell
 * @param {HTMLElement} cell - The cell element to edit
 */
function openValueEditor(cell) {
    // Implementation details...
}

/**
 * Open the name editor for a cell
 * @param {HTMLElement} cell - The cell element to edit
 */
function openNameEditor(cell) {
    // Implementation details...
}

/**
 * Save the outcome data to the server
 */
function saveOutcomeData() {
    // const metricId = document.getElementById('metricId').value; // Should be outcome_id from initialOutcomeData
    // const sectorId = document.getElementById('sectorId').value; // sector_id is part of outcome metadata, not structure
    
    if (!initialMetricData || typeof initialMetricData.outcome_id === 'undefined') {
        showErrorMessage('Outcome ID is missing. Cannot save data.');
        console.error('initialMetricData or outcome_id is undefined', initialMetricData);
        return;
    }
    if (!initialMetricData.save_url) {
        showErrorMessage('Save URL is not configured.');
        console.error('Save URL is missing in initialMetricData');
        return;
    }

    const outcomeId = initialMetricData.outcome_id;
    
    // Collect all outcome data from the table
    const tableData = collectTableData(); // This function needs to be implemented
    
    // Create the data structure for saving
    const saveData = {
        outcome_id: outcomeId, // Use outcome_id
        // sector_id: sectorId, // Sector ID is managed by the PHP form for metadata
        table_name: initialMetricData.table_name || document.getElementById('tableName')?.value, // tableName might be from initial data or a form field
        data_json: tableData,
        // is_draft is part of outcome metadata, typically handled by the PHP form.
        // If the structure save implies changing draft status, it needs careful consideration.
        // For now, assuming structure save doesn't alter draft status directly via this JSON save.
    };
    
    console.log('Saving outcome data:', saveData);

    // Send the data to the server
    fetch(initialMetricData.save_url, { // Use save_url from initialMetricData
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(saveData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccessMessage('Outcome saved successfully');
            // Additional success handling...
        } else {
            showErrorMessage('Error saving outcome: ' + data.error);
        }
    })
    .catch(error => {
        showErrorMessage('Network error: ' + error.message);
    });
}

/**
 * Create a new outcome entry
 */
function createNewOutcome() {
    // This function might be redundant if creation is handled by PHP form submission and redirect.
    // If it's meant to be an AJAX creation of the initial outcome record,
    // it would need to submit metadata (name, sector, period) and then potentially load the editor.
    // For now, commenting out as edit_outcome.php handles creation.
    /*
    console.log('createNewOutcome called. Functionality to be reviewed based on workflow.');
    showErrorMessage('New outcome creation via JS is not fully implemented. Please use the form fields.');
    */
}

/**
 * Collect all table data into a structured format
 * @return {Object} The table data as a structured object { columns: [], units: [] }
 */
function collectTableData() {
    const collected = { columns: [], units: [], data: {} };
    const table = document.getElementById('outcomeStructureTable');
    if (!table) {
        console.error('Cannot find table to collect data.');
        return collected;
    }
    
    const isPreviewTable = table.querySelector('thead tr th.text-center.bg-light');
    
    if (isPreviewTable) {
        // For preview table, we need the second row in the header which contains our columns
        const headerRow = table.querySelector('thead tr:nth-child(2)');
        if (!headerRow) {
            console.error('Cannot find header row in preview table');
            return collected;
        }
        
        // Skip the first cell which is "Month"
        for (let i = 1; i < headerRow.cells.length; i++) {
            const cell = headerRow.cells[i];
            const nameEl = cell.querySelector('.column-name');
            const unitEl = cell.querySelector('.column-unit');
            
            if (nameEl) {
                const columnName = nameEl.textContent.trim();
                collected.columns.push(columnName);
                
                // Extract unit text
                let unitText = unitEl ? unitEl.textContent.trim() : '';
                if (unitText.startsWith('(') && unitText.endsWith(')')) {
                    unitText = unitText.substring(1, unitText.length - 1);
                }
                if (unitText === 'N/A') {
                    unitText = '';
                }
                collected.units.push(unitText);
            }
        }
    } else {
        // For regular outcome editor table
        if (!table.tHead || !table.tHead.rows[0]) {
            console.error('Cannot find table header to collect data.');
            return collected;
        }
        
        const headerCells = table.tHead.rows[0].cells;
        for (let i = 0; i < headerCells.length; i++) {
            const cell = headerCells[i];
            const nameEl = cell.querySelector('.column-name');
            const unitEl = cell.querySelector('.column-unit');
            
            collected.columns.push(nameEl ? nameEl.textContent.trim() : '');
            // Extract unit text
            let unitText = unitEl ? unitEl.textContent.trim() : '';
            if (unitText.startsWith('(') && unitText.endsWith(')')) {
                unitText = unitText.substring(1, unitText.length - 1);
            }
            if (unitText === 'N/A') {
                unitText = '';
            }
            collected.units.push(unitText);
        }
    }
    
    // Initialize empty data structure for months
    const months = ['January', 'February', 'March', 'April', 'May', 'June', 
                   'July', 'August', 'September', 'October', 'November', 'December'];
    
    months.forEach(month => {
        collected.data[month] = {};
        collected.columns.forEach(column => {
            collected.data[month][column] = '0.00';
        });
    });
    
    console.log('Collected table data:', collected);
    return collected;
}

/**
 * Add a new column to the outcome table
 */
function addNewColumn() {
    const columnName = prompt("Enter the name for the new outcome column (indicator):", "New Indicator");
    if (!columnName) return; // User cancelled

    const columnUnit = prompt("Enter the unit for this column (e.g., %, count, USD):", "");
    
    const table = document.getElementById('outcomeStructureTable');
    if (!table) {
        console.error('Cannot find table to add column.');
        showErrorMessage('Could not add column: Table not found.');
        return;
    }
    
    // Check if we're working with the preview table or the existing outcome editor
    const isPreviewTable = table.querySelector('thead tr th.text-center.bg-light');
    
    if (isPreviewTable) {
        // For preview table, find the second row in thead which contains our column headers
        const headerRow = table.querySelector('thead tr:nth-child(2)');
        if (!headerRow) {
            console.error('Header row not found in preview table');
            showErrorMessage('Could not add column: Header row not found.');
            return;
        }
        
        const newIndex = headerRow.cells.length;
        
        // Add to header
        const th = document.createElement('th');
        th.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="column-name">${columnName}</span><br>
                    <small class="column-unit text-muted">(${columnUnit || 'N/A'})</small>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm delete-column-btn" data-column-index="${newIndex - 1}" title="Delete column">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        headerRow.appendChild(th);
        
        // Add sample data cells to sample rows
        const dataRows = table.querySelectorAll('tbody tr:not(.bg-light)');
        dataRows.forEach(row => {
            const cell = row.insertCell();
            cell.className = 'text-center text-muted';
            cell.textContent = '0.00';
        });
    } else {
        // For regular table in existing outcome editor
        if (!table.tHead || !table.tHead.rows[0]) {
            console.error('Cannot find table header to add column.');
            showErrorMessage('Could not add column: Table header not found.');
            return;
        }
        
        const headerRow = table.tHead.rows[0];
        const newIndex = headerRow.cells.length;
        
        const th = document.createElement('th');
        th.innerHTML = `
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="column-name">${columnName}</span><br>
                    <small class="column-unit text-muted">(${columnUnit || 'N/A'})</small>
                </div>
                <button type="button" class="btn btn-outline-danger btn-sm delete-column-btn" data-column-index="${newIndex}" title="Delete column">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        headerRow.appendChild(th);
    }
    
    // Update the structure data in memory
    const tableData = collectTableData();
    
    // If we're in preview mode, update hidden fields for form submission
    if (isPreviewTable) {
        updateHiddenFields(tableData);
    }
    
    showSuccessMessage(`Column "${columnName}" added.`);
}

/**
 * Update hidden fields in the form to store structure data for submission
 * @param {Object} structureData - The collected table structure data
 */
function updateHiddenFields(structureData) {
    // Find the form that creates the outcome
    const form = document.querySelector('form[action*="edit_outcome.php"]');
    if (!form) {
        console.error('Could not find the outcome creation form');
        return;
    }
    
    // First, remove any existing hidden structure fields
    const existingFields = form.querySelectorAll('input[name="structure_data"]');
    existingFields.forEach(field => field.remove());
    
    // Create a new hidden field with the structure data
    const hiddenField = document.createElement('input');
    hiddenField.type = 'hidden';
    hiddenField.name = 'structure_data';
    hiddenField.value = JSON.stringify(structureData);
    form.appendChild(hiddenField);
}

/**
 * Handle deleting a column
 * @param {HTMLElement} buttonElement - The delete button that was clicked
 */
function handleDeleteColumn(buttonElement) {
    const columnIndex = buttonElement.dataset.columnIndex; 
    
    if (typeof columnIndex === 'undefined') {
        showErrorMessage('Could not identify column to delete. Index missing.');
        console.error('data-column-index attribute missing on delete button', buttonElement);
        return;
    }
    
    // Find the closest table cell (th) and its parent row
    const headerCell = buttonElement.closest('th');
    if (!headerCell) {
        showErrorMessage('Could not find the column to delete.');
        return;
    }
      const table = document.getElementById('outcomeStructureTable');
    const isPreviewTable = table && table.querySelector('thead tr th.text-center.bg-light');
    
    if (isPreviewTable) {
        // For preview table, get the index in the row (accounting for Month column)
        const headerRow = headerCell.parentElement;
        const columnPosition = Array.from(headerRow.cells).indexOf(headerCell);
        
        // Confirm deletion
        if (!confirm(`Are you sure you want to delete the column "${headerCell.querySelector('.column-name').textContent}"?`)) {
            return;
        }
        
        // Remove column from header
        headerRow.removeChild(headerCell);
        
        // Remove corresponding data cells from each data row
        const dataRows = table.querySelectorAll('tbody tr:not(.bg-light)');
        dataRows.forEach(row => {
            if (row.cells.length > columnPosition) {
                row.removeChild(row.cells[columnPosition]);
            }
        });
        
        // Update data-column-index attributes for remaining columns
        const remainingButtons = headerRow.querySelectorAll('.delete-column-btn');
        remainingButtons.forEach((btn, idx) => {
            btn.dataset.columnIndex = idx;
        });
        
        // Update hidden fields for form submission
        updateHiddenFields(collectTableData());
    } else {
        // For existing outcome editor
        if (!confirm(`Are you sure you want to delete this column?`)) {
            return;
        }
        
        const table = document.getElementById('outcomeStructureTable');
        if (!table || !table.tHead || !table.tHead.rows[0]) {
            showErrorMessage('Could not find the table structure.');
            return;
        }
        
        const headerRow = table.tHead.rows[0];
        if (columnIndex >= 0 && columnIndex < headerRow.cells.length) {
            headerRow.deleteCell(columnIndex);
            
            // Reindex remaining columns
            const remainingButtons = headerRow.querySelectorAll('.delete-column-btn');
            remainingButtons.forEach((btn, idx) => {
                btn.dataset.columnIndex = idx;
            });
            
            showSuccessMessage('Column deleted successfully.');
        } else {
            showErrorMessage('Invalid column index.');
        }
    }

    const th = buttonElement.closest('th');
    if (th) {
        const columnName = th.querySelector('.column-name')?.textContent.trim() || `Column ${parseInt(columnIndex) + 1}`;
        if (confirm(`Are you sure you want to delete the column "${columnName}"? This cannot be undone.`)) {
            th.remove();
            showSuccessMessage(`Column "${columnName}" deleted.`);
            // Optional: Re-index remaining delete buttons if columnIndex is strictly sequential
            // For now, direct deletion is simpler. Saving will collect current state.
        }
    } else {
        showErrorMessage('Could not find column header to delete.');
    }
}

/**
 * Show a success message
 * @param {string} message - The message to display
 */
function showSuccessMessage(message) {
    // alert('Success: ' + message); // Simple alert for now
    // TODO: Implement a more user-friendly notification (e.g., a toast or a message div)
    // Example:
    const msgDiv = document.getElementById('outcome-editor-messages');
    if (msgDiv) {
        msgDiv.className = 'alert alert-success alert-dismissible fade show';
        msgDiv.innerHTML = `${message} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
        msgDiv.style.display = 'block';
        setTimeout(() => {
            // msgDiv.style.display = 'none'; 
            // Instead of hiding, let Bootstrap's dismissible handle it or fade it out
            const alertInstance = bootstrap.Alert.getInstance(msgDiv);
            if (alertInstance) {
                alertInstance.close();
            }
        }, 3000);
    }
}

/**
 * Show an error message
 * @param {string} message - The message to display
 */
function showErrorMessage(message) {
    // alert('Error: ' + message); // Simple alert for now
    // TODO: Implement a more user-friendly notification
    // Example:
    const msgDiv = document.getElementById('outcome-editor-messages');
    if (msgDiv) {
        msgDiv.className = 'alert alert-danger alert-dismissible fade show';
        msgDiv.innerHTML = `${message} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>`;
        msgDiv.style.display = 'block';
        // Error messages might not auto-close, or have a longer timeout
        // setTimeout(() => { 
        //     const alertInstance = bootstrap.Alert.getInstance(msgDiv);
        //     if (alertInstance) {
        //         alertInstance.close();
        //     }
        // }, 5000);
    }
}

