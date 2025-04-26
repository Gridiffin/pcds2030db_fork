/**
 * Metric Editor JavaScript
 * Handles interactive features for the sector metrics editor
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize metric editor functionality
    initializeMetricEditor();
});

/**
 * Initialize all metric editor functionality
 */
function initializeMetricEditor() {
    // Set up event listeners for metric value cells
    setupMetricValueListeners();
    
    // Set up event listeners for metric name cells
    setupMetricNameListeners();
    
    // Set up button handlers
    setupButtonHandlers();
    
    // Make cells clickable for better UX
    makeMetricCellsClickable();
}

/**
 * Set up event listeners for metric value cells
 */
function setupMetricValueListeners() {
    document.querySelectorAll('.metric-value').forEach(cell => {
        cell.addEventListener('input', function() {
            const btn = this.parentElement.querySelector('.save-btn');
            if (btn) btn.style.display = 'inline-block';
        });
        
        cell.addEventListener('blur', function() {
            // Format numeric values on blur
            if (!isNaN(parseFloat(this.textContent))) {
                this.textContent = parseFloat(this.textContent).toFixed(2);
            }
        });
    });
}

/**
 * Set up event listeners for metric name cells
 */
function setupMetricNameListeners() {
    document.querySelectorAll('.metric-name').forEach(cell => {
        cell.addEventListener('input', function() {
            const btn = this.parentElement.querySelector('.save-btn');
            if (btn) btn.style.display = 'inline-block';
        });
    });
}

/**
 * Set up handlers for various buttons
 */
function setupButtonHandlers() {
    // Value save buttons
    document.querySelectorAll('.save-btn[data-month]').forEach(btn => {
        btn.addEventListener('click', handleMetricValueSave);
    });
    
    // Name save buttons
    document.querySelectorAll('.save-btn:not([data-month])').forEach(btn => {
        btn.addEventListener('click', handleMetricNameSave);
    });
    
    // Add column button
    const addColumnBtn = document.getElementById('addColumnBtn');
    if (addColumnBtn) {
        addColumnBtn.addEventListener('click', handleAddColumn);
    }
    
    // Save table name button
    const saveTableNameBtn = document.getElementById('saveTableNameBtn');
    if (saveTableNameBtn) {
        saveTableNameBtn.addEventListener('click', handleSaveTableName);
    }
    
    // Done button
    const doneBtn = document.getElementById('doneBtn');
    if (doneBtn) {
        doneBtn.addEventListener('click', () => {
            window.location.href = 'submit_metrics.php';
        });
    }
}

/**
 * Make entire metric-cell div clickable
 */
function makeMetricCellsClickable() {
    document.querySelectorAll('.metric-cell').forEach(cell => {
        cell.addEventListener('click', function(event) {
            // Avoid focusing if clicking on the save button or the span itself
            if (event.target.classList.contains('save-btn') || 
                event.target.classList.contains('metric-value')) {
                return;
            }
            
            const editableSpan = this.querySelector('.metric-value');
            if (editableSpan) {
                editableSpan.focus();
                // Place cursor at end
                placeCursorAtEnd(editableSpan);
            }
        });
    });
}

/**
 * Handle saving metric values
 */
async function handleMetricValueSave() {
    const cell = this.parentElement.querySelector('.metric-value');
    const metric = cell.dataset.metric;
    const month = cell.dataset.month;
    const newValue = parseFloat(cell.textContent) || 0;
    
    try {
        const response = await fetch('update_metric.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                column_title: metric,
                month: month,
                new_value: newValue,
                metric_id: metricId,
                table_name: tableName
            })
        });
        
        if (!response.ok) throw new Error('Update failed');
        cell.textContent = newValue.toFixed(2);
        this.style.display = 'none';
        
        // Show a toast notification
        showToast('Value updated successfully', 'success');
    } catch (error) {
        showToast('Error updating value: ' + error.message, 'danger');
        // Try to reload the original value
        try {
            const response = await fetch(`get_metric_value.php?metric=${metric}&month=${month}`);
            const data = await response.json();
            cell.textContent = data.value.toFixed(2);
        } catch (reloadError) {
            // Silently fail if we can't reload
        }
    }
}

/**
 * Handle saving metric names
 */
async function handleMetricNameSave() {
    const cell = this.parentElement.querySelector('.metric-name');
    const oldName = cell.dataset.metric;
    const newName = cell.textContent.trim();
    
    if (!newName) {
        showToast('Metric name cannot be empty', 'warning');
        cell.textContent = oldName || 'Unnamed Metric';
        return;
    }
    
    if (newName === oldName) {
        this.style.display = 'none';
        return;
    }
    
    try {
        const response = await fetch('update_metric.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                column_title: oldName,
                new_name: newName,
                metric_id: metricId,
                table_name: tableName
            })
        });
        
        if (!response.ok) throw new Error('Update failed');
        cell.dataset.metric = newName;
        this.style.display = 'none';
        
        // Update all corresponding value cells
        document.querySelectorAll(`.metric-value[data-metric="${oldName}"]`)
            .forEach(cell => cell.dataset.metric = newName);
        
        // Show a toast notification
        showToast('Metric name updated successfully', 'success');
    } catch (error) {
        showToast('Error updating metric name: ' + error.message, 'danger');
        cell.textContent = oldName;
    }
}

/**
 * Handle adding a new column
 */
async function handleAddColumn() {
    const newMetricName = prompt('Enter new metric name:');
    if (!newMetricName || newMetricName.trim() === '') return;

    // Prepare data for POST request
    const data = new URLSearchParams();
    data.append('column_title', newMetricName);
    data.append('table_content', '0');
    data.append('month', 'January');
    data.append('table_name', document.getElementById('tableNameInput').value);

    try {
        const response = await fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: data.toString()
        });
        
        if (!response.ok) throw new Error('Failed to add new metric column');
        showToast('New metric column added successfully', 'success');
        setTimeout(() => location.reload(), 1000);
    } catch (error) {
        showToast('Error adding new metric column: ' + error.message, 'danger');
    }
}

/**
 * Handle saving the table name
 */
async function handleSaveTableName() {
    const tableNameInput = document.getElementById('tableNameInput');
    const newTableName = tableNameInput.value.trim();
    
    if (!newTableName) {
        showToast('Table name cannot be empty', 'warning');
        return;
    }

    // Prepare form data
    const formData = new FormData();
    formData.append('table_name', newTableName);

    try {
        const response = await fetch('', {
            method: 'POST',
            body: formData
        });
        
        if (!response.ok) throw new Error('Failed to update table name');
        showToast('Table name updated successfully', 'success');
        // Don't reload the page to avoid losing changes
    } catch (error) {
        showToast('Error updating table name: ' + error.message, 'danger');
    }
}

/**
 * Show toast notification
 */
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    
    // Set toast content
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    // Add toast to container
    toastContainer.appendChild(toastEl);
    
    // Initialize and show toast
    const toast = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
    toast.show();
    
    // Remove toast after it's hidden
    toastEl.addEventListener('hidden.bs.toast', () => {
        toastEl.remove();
    });
}

/**
 * Place cursor at the end of content
 */
function placeCursorAtEnd(element) {
    const range = document.createRange();
    const sel = window.getSelection();
    range.selectNodeContents(element);
    range.collapse(false);
    sel.removeAllRanges();
    sel.addRange(range);
}