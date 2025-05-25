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
    if (typeof initialMetricData !== 'undefined' && initialMetricData.data_json) {
        renderOutcomeTable(initialMetricData.data_json);
    } else {
        console.error('initialMetricData or initialMetricData.data_json is not available.');
        // Optionally render an empty table or show an error
        renderOutcomeTable({ columns: [], units: [] });
    }

    // Set up event listeners for outcome value cells (if used for data entry)
    // setupOutcomeValueListeners(); // Placeholder for now
    
    // Set up event listeners for outcome name cells (for editing column names/units)
    // setupOutcomeNameListeners(); // Placeholder for now
    
    // Initialize outcome data saving
    initDataSaving();
    
    // Set up column addition buttons
    setupColumnButtons(); // Assumes addColumnBtn is static or managed elsewhere
}

/**
 * Render the outcome structure table
 * @param {object} jsonData - The data containing columns and units { columns: [], units: [] }
 */
function renderOutcomeTable(jsonData) {
    const container = document.getElementById('metricEditorContainer');
    if (!container) {
        console.error('metricEditorContainer not found');
        return;
    }
    container.innerHTML = ''; // Clear previous content

    const table = document.createElement('table');
    table.id = 'outcomeStructureTable';
    table.className = 'table table-bordered table-sm'; // Added table-sm for compactness

    const thead = table.createTHead();
    const headerRow = thead.insertRow();

    if (jsonData && jsonData.columns && jsonData.units) {
        jsonData.columns.forEach((colName, index) => {
            const th = document.createElement('th');
            // For now, just text. Later, openNameEditor could make these editable.
            th.innerHTML = `
                <span class="column-name">${colName}</span><br>
                <small class="column-unit text-muted">(${jsonData.units[index] || 'N/A'})</small>
                <button type="button" class="btn btn-danger btn-sm delete-column-btn float-end" data-column-index="${index}" title="Delete column">
                    <i class="fas fa-times"></i>
                </button>
            `;
            headerRow.appendChild(th);
        });
    }
    
    // Add a header for the "Add Column" button if placing it in the header
    // Or ensure addColumnBtn is handled by setupColumnButtons and is visible

    container.appendChild(table);
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
    const collected = { columns: [], units: [] };
    const table = document.getElementById('outcomeStructureTable');
    if (!table || !table.tHead || !table.tHead.rows[0]) {
        console.error('Cannot find table or table header to collect data.');
        return collected;
    }

    const headerCells = table.tHead.rows[0].cells;
    for (let i = 0; i < headerCells.length; i++) {
        const cell = headerCells[i];
        const nameEl = cell.querySelector('.column-name');
        const unitEl = cell.querySelector('.column-unit');
        
        collected.columns.push(nameEl ? nameEl.textContent.trim() : '');
        // Extract unit text, removing parentheses and 'N/A' default
        let unitText = unitEl ? unitEl.textContent.trim() : '';
        if (unitText.startsWith('(') && unitText.endsWith(')')) {
            unitText = unitText.substring(1, unitText.length - 1);
        }
        if (unitText === 'N/A') {
            unitText = '';
        }
        collected.units.push(unitText);
    }
    console.log('Collected table data:', collected);
    return collected;
}

/**
 * Add a new column to the outcome table
 */
function addNewColumn() {
    const columnName = prompt("Enter the name for the new outcome column (indicator):", "New Indicator");
    if (!columnName) return; // User cancelled

    const columnUnit = prompt("Enter the unit for this column (e.g., %, count, USD):", "N/A");
    // No validation for unit, can be empty

    const table = document.getElementById('outcomeStructureTable');
    if (!table || !table.tHead || !table.tHead.rows[0]) {
        console.error('Cannot find table or table header to add column.');
        showErrorMessage('Could not add column: Table not found.');
        return;
    }
    const headerRow = table.tHead.rows[0];
    const newIndex = headerRow.cells.length;

    const th = document.createElement('th');
    th.innerHTML = `
        <span class="column-name">${columnName}</span><br>
        <small class="column-unit text-muted">(${columnUnit || 'N/A'})</small>
        <button type="button" class="btn btn-danger btn-sm delete-column-btn float-end" data-column-index="${newIndex}" title="Delete column">
            <i class="fas fa-times"></i>
        </button>
    `;
    headerRow.appendChild(th);
    showSuccessMessage(`Column "${columnName}" added.`);
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

