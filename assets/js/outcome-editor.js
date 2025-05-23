/**
 * Outcome Editor JavaScript
 * Handles interactive features for the sector outcomes editor
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize outcome editor functionality
    initializeOutcomeEditor();
    
    // Add debugging for delete buttons
    console.log('Adding event listeners to delete buttons');
    document.querySelectorAll('.delete-column-btn').forEach(btn => {
        console.log('Delete button found:', btn.dataset.metric);
        btn.addEventListener('click', function(e) {
            console.log('Delete button clicked for:', this.dataset.metric);
            e.stopPropagation(); // Prevent event bubbling
            handleDeleteColumn.call(this);
        });
    });
});

/**
 * Initialize all outcome editor functionality
 */
function initializeOutcomeEditor() {
    // Set up event listeners for outcome value cells
    setupOutcomeValueListeners();
    
    // Set up event listeners for outcome name cells
    setupOutcomeNameListeners();
    
    // Initialize outcome data saving
    initDataSaving();
    
    // Set up column addition buttons
    setupColumnButtons();
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
    // Check if we're in edit mode
    if (document.getElementById('saveOutcomeBtn')) {
        document.getElementById('saveOutcomeBtn').addEventListener('click', function() {
            saveOutcomeData();
        });
    }
    
    // Check if we're in create mode
    if (document.getElementById('createOutcomeBtn')) {
        document.getElementById('createOutcomeBtn').addEventListener('click', function() {
            createNewOutcome();
        });
    }
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
    const metricId = document.getElementById('metricId').value;
    const sectorId = document.getElementById('sectorId').value;
    
    // Collect all outcome data from the table
    const tableData = collectTableData();
    
    // Create the data structure for saving
    const saveData = {
        metric_id: metricId,
        sector_id: sectorId,
        table_name: document.getElementById('tableName').value,
        data_json: tableData,
        is_draft: document.getElementById('isDraft') ? 
                  parseInt(document.getElementById('isDraft').value) : 1
    };
    
    // Send the data to the server
    fetch(apiUrl("save_outcome_json.php"), {
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
    // Implementation similar to saveOutcomeData but for new outcomes...
}

/**
 * Collect all table data into a structured format
 * @return {Object} The table data as a structured object
 */
function collectTableData() {
    // Implementation to collect data from the table...
    return {};
}

/**
 * Add a new column to the outcome table
 */
function addNewColumn() {
    // Implementation for adding a new column...
}

/**
 * Handle deleting a column
 */
function handleDeleteColumn() {
    const metricId = this.dataset.metric;
    if (confirm('Are you sure you want to delete this outcome column? This cannot be undone.')) {
        // Implement deletion logic...
    }
}

/**
 * Show a success message
 * @param {string} message - The message to display
 */
function showSuccessMessage(message) {
    // Implementation for showing a success message...
}

/**
 * Show an error message
 * @param {string} message - The message to display
 */
function showErrorMessage(message) {
    // Implementation for showing an error message...
}

