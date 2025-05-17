/**
 * Outcome Editor JavaScript
 * Handles interactive features for the sector outcomes editor
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize metric editor functionality
    initializeMetricEditor();
    
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
    
    // Set up delete column buttons
    setupDeleteColumnButtons();
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
    
    // Unit buttons
    document.querySelectorAll('.unit-btn').forEach(btn => {
        btn.addEventListener('click', handleUnitEdit);
    });
    
    // Save table name button
    const saveTableNameBtn = document.getElementById('saveTableNameBtn');
    if (saveTableNameBtn) {
        saveTableNameBtn.addEventListener('click', handleSaveTableName);
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
                event.target.classList.contains('metric-value') ||
                event.target.tagName === 'I') {
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
 * Set up delete column buttons
 */
function setupDeleteColumnButtons() {
    document.querySelectorAll('.delete-column-btn').forEach(btn => {
        btn.addEventListener('click', handleDeleteColumn);
    });
}

/**
 * Handle column deletion
 */
async function handleDeleteColumn() {
    const metric = this.dataset.metric;
    
    if (!metric) return;
    
    // Confirm deletion
    if (!confirm(`Are you sure you want to delete the "${metric}" column? This action cannot be undone.`)) {
        return;
    }
    
    try {
        // Send delete request
        const response = await fetch('update_metric.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                action: 'delete_column',
                column_title: metric,
                metric_id: metricId,
                table_name: tableName
            })
        });
        
        if (!response.ok) throw new Error('Failed to delete column');
        
        // Remove column from DOM
        const columnIndex = findColumnIndex(metric);
        if (columnIndex !== -1) {
            removeColumnFromTable(columnIndex);
            showToast(`Column "${metric}" deleted successfully`, 'success');
        }
    } catch (error) {
        showToast('Error deleting column: ' + error.message, 'danger');
    }
}

/**
 * Find index of column by metric name
 */
function findColumnIndex(metricName) {
    const headers = document.querySelectorAll('.metrics-table thead th');
    
    for (let i = 0; i < headers.length; i++) {
        const nameEl = headers[i].querySelector('.metric-name[data-metric="' + metricName + '"]');
        if (nameEl) {
            return i;
        }
    }
    
    return -1;
}

/**
 * Remove column from table by index
 */
function removeColumnFromTable(columnIndex) {
    // Account for month column
    const actualIndex = columnIndex;
    
    // Remove header
    const headerRow = document.querySelector('.metrics-table thead tr');
    if (headerRow && headerRow.children[actualIndex]) {
        headerRow.children[actualIndex].remove();
    }
    
    // Remove cells from all rows
    const rows = document.querySelectorAll('.metrics-table tbody tr');
    rows.forEach(row => {
        if (row.children[actualIndex]) {
            row.children[actualIndex].remove();
        }
    });
      // Add "No outcomes" placeholder if we removed the last column
    const remainingColumns = headerRow.querySelectorAll('th');
    if (remainingColumns.length === 1) { // Only month column remains
        const placeholderTh = document.createElement('th');        placeholderTh.className = 'text-center text-muted';
        placeholderTh.innerHTML = '<em>No outcomes defined. Click "Add Column" to start.</em>';
        headerRow.appendChild(placeholderTh);
        
        // Add empty cells to data rows
        rows.forEach(row => {
            const placeholderTd = document.createElement('td');
            row.appendChild(placeholderTd);
        });
    }
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
        showToast('Outcome name cannot be empty', 'warning');
        cell.textContent = oldName || 'Unnamed Outcome';
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
        showToast('Outcome name updated successfully', 'success');
    } catch (error) {
        showToast('Error updating outcome name: ' + error.message, 'danger');
        cell.textContent = oldName;
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
        
        // Only show toast if we're not showing PHP messages
        if (typeof showPhpMessages === 'undefined' || !showPhpMessages) {
            showToast('Table name updated successfully', 'success');
        }
        
        // The global tableName variable needs to be updated
        if (typeof tableName !== 'undefined') {
            tableName = newTableName;
        }
    } catch (error) {
        showToast('Error updating table name: ' + error.message, 'danger');
    }
}

/**
 * Handle editing units for outcomes
 */
async function handleUnitEdit() {
    const metric = this.dataset.metric;
    const currentUnit = this.dataset.currentUnit || '';
    
    if (!metric) return;
    
    // Prompt for new unit
    const newUnit = prompt(`Enter unit of measurement for "${metric}":`, currentUnit);
    
    // User canceled or entered the same unit
    if (newUnit === null || newUnit === currentUnit) return;
    
    try {
        // Send unit update request
        const response = await fetch('update_metric.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                column_title: metric,
                unit: newUnit,
                metric_id: metricId,
                table_name: tableName
            })
        });
        
        if (!response.ok) throw new Error('Failed to update unit');
        
        // Update unit display in UI
        this.dataset.currentUnit = newUnit;
        
        // Find and update the unit display span
        const headerCell = this.closest('.metric-header');
        let unitDisplay = headerCell.querySelector('.metric-unit-display');
        
        if (newUnit) {
            if (unitDisplay) {
                unitDisplay.textContent = `(${newUnit})`;
            } else {
                // Create unit display if it doesn't exist
                unitDisplay = document.createElement('span');
                unitDisplay.className = 'metric-unit-display';
                unitDisplay.textContent = `(${newUnit})`;
                headerCell.querySelector('.metric-title').appendChild(unitDisplay);
            }
        } else if (unitDisplay) {
            // Remove unit display if unit is empty
            unitDisplay.remove();
        }
        
        showToast(`Unit for "${metric}" updated successfully`, 'success');
    } catch (error) {
        showToast('Error updating unit: ' + error.message, 'danger');
    }
}

/**
 * Handle setting the same unit for all columns
 */
async function handleSetAllUnits() {
    // Prompt for unit value
    const newUnit = prompt('Enter unit of measurement for all columns:');
    
    // User canceled
    if (newUnit === null) return;
    
    try {
        // Get all column names
        const metricNames = [];
        document.querySelectorAll('.metric-name').forEach(el => {
            const metric = el.dataset.metric;
            if (metric && !metricNames.includes(metric)) {
                metricNames.push(metric);
            }
        });
          if (metricNames.length === 0) {
            showToast('No outcomes found to update', 'warning');
            return;
        }
        
        // Update units for all columns
        let successCount = 0;
        
        for (const metric of metricNames) {
            // Send unit update request for each column
            const response = await fetch('update_metric.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    column_title: metric,
                    unit: newUnit,
                    metric_id: metricId,
                    table_name: tableName
                })
            });
            
            if (response.ok) {
                successCount++;
                
                // Update unit display in UI
                const unitBtn = document.querySelector(`.unit-btn[data-metric="${metric}"]`);
                if (unitBtn) unitBtn.dataset.currentUnit = newUnit;
                
                // Find and update unit display span
                const headerCell = document.querySelector(`.metric-name[data-metric="${metric}"]`).closest('.metric-header');
                let unitDisplay = headerCell.querySelector('.metric-unit-display');
                
                if (newUnit) {
                    if (unitDisplay) {
                        unitDisplay.textContent = `(${newUnit})`;
                    } else {
                        // Create unit display if it doesn't exist
                        unitDisplay = document.createElement('span');
                        unitDisplay.className = 'metric-unit-display';
                        unitDisplay.textContent = `(${newUnit})`;
                        headerCell.querySelector('.metric-title').appendChild(unitDisplay);
                    }
                } else if (unitDisplay) {
                    // Remove unit display if unit is empty
                    unitDisplay.remove();
                }
            }
        }
        
        if (successCount === metricNames.length) {
            showToast(`Unit updated for all ${successCount} columns`, 'success');
        } else {
            showToast(`Updated ${successCount} of ${metricNames.length} columns`, 'warning');
        }
    } catch (error) {
        showToast('Error updating units: ' + error.message, 'danger');
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
    
    // Check active toast count to prevent too many notifications
    const activeToasts = toastContainer.querySelectorAll('.toast');
    const MAX_TOASTS = 3; // Maximum number of visible toasts at once
    
    if (activeToasts.length >= MAX_TOASTS) {
        // If we already have similar successful updates, just update the count
        if (type === 'success') {
            const successToast = Array.from(activeToasts).find(t => t.classList.contains('bg-success') && 
                                                             t.querySelector('.toast-count'));
            if (successToast) {
                // Get the current count and increment it
                const countEl = successToast.querySelector('.toast-count');
                const currentCount = parseInt(countEl.dataset.count || '1');
                countEl.dataset.count = currentCount + 1;
                countEl.textContent = currentCount + 1;
                
                // Reset the auto-hide timer for this toast
                const bsToast = bootstrap.Toast.getInstance(successToast);
                if (bsToast) {
                    bsToast.hide();
                    setTimeout(() => {
                        bsToast.show();
                    }, 100);
                }
                
                return; // Don't create a new toast
            } else {
                // Remove the oldest toast
                const oldestToast = activeToasts[0];
                const bsToast = bootstrap.Toast.getInstance(oldestToast);
                if (bsToast) bsToast.hide();
            }
        } else {
            // For non-success toasts, remove the oldest
            const oldestToast = activeToasts[0];
            const bsToast = bootstrap.Toast.getInstance(oldestToast);
            if (bsToast) bsToast.hide();
        }
    }
    
    // Create toast element
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    
    // Set toast content
    let countHtml = '';
    if (type === 'success') {
        countHtml = '<span class="toast-count ms-1" data-count="1"></span>';
    }
    
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                ${message} ${countHtml}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    
    // Add toast to container
    toastContainer.appendChild(toastEl);
    
    // Initialize and show toast using Bootstrap
    const bsToast = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
    bsToast.show();
    
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