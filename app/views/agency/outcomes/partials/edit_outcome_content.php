<?php
/**
 * Edit Outcome Content Partial - Agency Version
 * 
 * Content partial for editing outcome details in agency area
 */

// Ensure variables are available from parent view
if (!isset($outcome_id) || !isset($outcome)) {
    return;
}
?>

<div class="container-fluid px-4 py-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">Edit Outcome</h5>
            <p class="text-muted mb-0 small">Outcome ID: <?= $outcome_id ?> | Code: <?= htmlspecialchars($outcome['code']) ?></p>
        </div>
        <div class="card-body">
            <form id="editOutcomeForm" method="post" action="">
                <div class="mb-3">
                    <label for="outcomeCodeInput" class="form-label">Outcome Code</label>
                    <input type="text" class="form-control" id="outcomeCodeInput" name="code" required value="<?= htmlspecialchars($outcome['code']) ?>" />
                </div>
                <div class="mb-3">
                    <label for="outcomeTypeInput" class="form-label">Outcome Type</label>
                    <input type="text" class="form-control" id="outcomeTypeInput" name="type" required value="<?= htmlspecialchars($outcome['type']) ?>" />
                </div>
                <div class="mb-3">
                    <label for="outcomeTitleInput" class="form-label">Outcome Title</label>
                    <input type="text" class="form-control" id="outcomeTitleInput" name="title" required value="<?= htmlspecialchars($outcome['title']) ?>" />
                </div>
                <div class="mb-3">
                    <label for="outcomeDescriptionInput" class="form-label">Outcome Description</label>
                    <textarea class="form-control" id="outcomeDescriptionInput" name="description" rows="3"><?= htmlspecialchars($outcome['description']) ?></textarea>
                </div>

                <div class="mb-3">
                    <button type="button" class="btn btn-primary" id="addColumnBtn">
                        <i class="fas fa-plus me-1"></i> Add Column
                    </button>
                    <button type="button" class="btn btn-primary ms-2" id="addRowBtn">
                        <i class="fas fa-plus me-1"></i> Add Row
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover metrics-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 150px;">Row</th>
                                <!-- Dynamic columns will be added here -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // Extract columns and rows from outcome data
                            $columns = isset($outcome['data']['columns']) && is_array($outcome['data']['columns']) ? $outcome['data']['columns'] : [];
                            $rows = isset($outcome['data']['rows']) && is_array($outcome['data']['rows']) ? $outcome['data']['rows'] : [];
                            
                            // If no existing data, provide a default structure that can be modified
                            if (empty($rows)) {
                                $rows = [
                                    ['label' => 'Row 1', 'month' => ''],
                                    ['label' => 'Row 2', 'month' => ''],
                                    ['label' => 'Row 3', 'month' => '']
                                ]; // Default starting rows
                            }
                            
                            if (empty($columns)) {
                                $columns = ['Column A', 'Column B', 'Column C']; // Default starting columns
                            }
                            
                            ?>
                            <?php if (!empty($rows)): ?>
                                <?php foreach ($rows as $row): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="row-badge editable-hint" contenteditable="true" data-row="<?= htmlspecialchars($row['label'] ?? $row['month'] ?? '') ?>"><?= htmlspecialchars($row['label'] ?? $row['month'] ?? '') ?></span>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-row-btn ms-2" data-row="<?= htmlspecialchars($row['label'] ?? $row['month'] ?? '') ?>" title="Delete row">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <?php foreach ($columns as $col): ?>
                                            <td>
                                                <div class="metric-cell editable-hint" contenteditable="true" data-row="<?= htmlspecialchars($row['label'] ?? $row['month'] ?? '') ?>" data-column="<?= htmlspecialchars($col) ?>">
                                                    <?= isset($row[$col]) ? htmlspecialchars($row[$col]) : '' ?>
                                                </div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Default starting rows if no data -->
                                <?php for ($i = 1; $i <= 3; $i++): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <span class="row-badge editable-hint" contenteditable="true" data-row="Row <?= $i ?>">Row <?= $i ?></span>
                                                <button type="button" class="btn btn-sm btn-outline-danger delete-row-btn ms-2" data-row="Row <?= $i ?>" title="Delete row">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <?php foreach ($columns as $col): ?>
                                            <td>
                                                <div class="metric-cell editable-hint" contenteditable="true" data-row="Row <?= $i ?>" data-column="<?= htmlspecialchars($col) ?>"></div>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <input type="hidden" name="data" id="dataJsonInput" />
                <input type="hidden" name="is_draft" id="isDraftInput" value="<?= $is_draft ?>" />

                <div class="d-flex justify-content-end gap-2">
                    <a href="view_outcome.php?id=<?= $outcome_id ?>" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-success" id="submitBtn">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Disable any conflicting external JS
    window.editOutcomeJsDisabled = true;

    // Initialize data from PHP
    let columns = <?= json_encode($columns) ?>;
    let rows = <?= json_encode($rows) ?>;
    let data = {};
    rows.forEach(function(row) {
        let rowLabel = row['label'] || row['month'] || '';
        data[rowLabel] = {};
        columns.forEach(function(col) {
            data[rowLabel][col] = row[col] !== undefined ? row[col] : 0;
        });
    });

    // Set up save button to work with the main form
    const saveBtn = document.getElementById('submitBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function(e) {
            document.getElementById('isDraftInput').value = '0';
        });
    }

    function addRow() {
        const rowName = prompt('Enter row name:');
        if (rowName && rowName.trim() !== '') {
            const trimmedName = rowName.trim();
            if (data[trimmedName] !== undefined) {
                alert('Row already exists!');
                return;
            }
            
            // Initialize data for this row with all existing columns
            data[trimmedName] = {};
            columns.forEach(col => {
                data[trimmedName][col] = 0;
            });
            
            renderTable();
        }
    }

    function removeRow(rowName) {
        if (Object.keys(data).length <= 1) {
            alert('Cannot delete the last row. At least one row is required.');
            return;
        }
        
        if (data[rowName] !== undefined) {
            delete data[rowName];
            renderTable();
        }
    }

    function addColumn() {
        const columnName = prompt('Enter column name:');
        if (columnName && columnName.trim() !== '') {
            const trimmedName = columnName.trim();
            if (columns.includes(trimmedName)) {
                alert('Column already exists!');
                return;
            }
            columns.push(trimmedName);
            
            // Initialize data for this column in all existing rows
            Object.keys(data).forEach(rowLabel => {
                if (!data[rowLabel]) data[rowLabel] = {};
                data[rowLabel][trimmedName] = 0;
            });
            
            renderTable();
        }
    }

    function removeColumn(columnName) {
        const columnIndex = columns.indexOf(columnName);
        if (columnIndex > -1) {
            columns.splice(columnIndex, 1);
            
            // Remove this column's data from all rows
            Object.keys(data).forEach(rowLabel => {
                if (data[rowLabel] && data[rowLabel][columnName] !== undefined) {
                    delete data[rowLabel][columnName];
                }
            });
            
            renderTable();
        }
    }

    // Event handler functions
    function handleColumnTitleEdit() {
        const oldColumn = this.getAttribute('data-column');
        const newColumn = this.textContent.trim();
        
        if (newColumn !== oldColumn && newColumn !== '') {
            if (columns.includes(newColumn)) {
                alert('Column name already exists!');
                this.textContent = oldColumn;
                return;
            }
            
            // Update columns array
            const index = columns.indexOf(oldColumn);
            if (index > -1) {
                columns[index] = newColumn;
                
                // Update data object keys
                Object.keys(data).forEach(rowLabel => {
                    if (data[rowLabel] && data[rowLabel][oldColumn] !== undefined) {
                        data[rowLabel][newColumn] = data[rowLabel][oldColumn];
                        delete data[rowLabel][oldColumn];
                    }
                });
                
                // Update the data attribute
                this.setAttribute('data-column', newColumn);
            }
        }
    }

    function handleColumnTitleBlur() {
        // Validate column name
        const columnName = this.textContent.trim();
        if (columnName === '') {
            const oldColumn = this.getAttribute('data-column');
            this.textContent = oldColumn;
        }
    }

    function handleColumnTitleKeydown(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            this.blur();
        }
    }

    function handleRowTitleEdit() {
        const oldRow = this.getAttribute('data-row');
        const newRow = this.textContent.trim();
        
        if (newRow !== oldRow && newRow !== '') {
            if (data[newRow] !== undefined) {
                alert('Row name already exists!');
                this.textContent = oldRow;
                return;
            }
            
            // Update data object keys
            if (data[oldRow] !== undefined) {
                data[newRow] = data[oldRow];
                delete data[oldRow];
                
                // Update the data attribute
                this.setAttribute('data-row', newRow);
            }
        }
    }

    function handleRowTitleBlur() {
        // Validate row name
        const rowName = this.textContent.trim();
        if (rowName === '') {
            const oldRow = this.getAttribute('data-row');
            this.textContent = oldRow;
        }
    }

    function handleRowTitleKeydown(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            this.blur();
        }
    }

    function handleDataCellEdit() {
        const row = this.getAttribute('data-row');
        const column = this.getAttribute('data-column');
        const value = parseFloat(this.textContent.trim()) || 0;
        
        if (!data[row]) data[row] = {};
        data[row][column] = value;
    }

    function handleDataCellBlur() {
        // Format the number for display
        const value = parseFloat(this.textContent.trim());
        if (!isNaN(value)) {
            this.textContent = value.toString();
        } else {
            this.textContent = '0';
        }
        
        // Trigger data update
        handleDataCellEdit.call(this);
    }

    function collectCurrentData() {
        const currentData = {};
        
        // Collect data from all metric cells
        document.querySelectorAll('.metric-cell').forEach(cell => {
            const row = cell.getAttribute('data-row');
            const column = cell.getAttribute('data-column');
            const value = parseFloat(cell.textContent.trim()) || 0;
            
            if (!currentData[row]) currentData[row] = {};
            currentData[row][column] = value;
        });
        
        // Update the global data object
        data = currentData;
    }

    function renderTable(skipDataCollection = false) {
        // Only collect current data if this is not the initial render
        if (!skipDataCollection) {
            collectCurrentData();
        }
        
        const theadRow = document.querySelector('.metrics-table thead tr');
        // Remove all columns except the first (Row)
        while (theadRow.children.length > 1) {
            theadRow.removeChild(theadRow.lastChild);
        }
        
        // Add column headers with enhanced styling and edit functionality
        columns.forEach(col => {
            const th = document.createElement('th');
            th.classList.add('position-relative');
            th.innerHTML = `
                <div class="metric-header">
                    <div class="metric-title editable-hint" contenteditable="true" data-column="${col}">${col}</div>
                    <div class="metric-actions">
                        <button type="button" class="btn btn-sm btn-danger delete-column-btn" data-column="${col}" title="Delete column">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>`;
            theadRow.appendChild(th);
        });

        // Rebuild table body with preserved data
        const tbody = document.querySelector('.metrics-table tbody');
        tbody.innerHTML = ''; // Clear all rows
        
        // Create rows dynamically from data object
        Object.keys(data).forEach(rowLabel => {
            const tr = document.createElement('tr');
            
            // Create row header cell with editable name and delete button
            const rowHeaderTd = document.createElement('td');
            rowHeaderTd.innerHTML = `
                <div class="d-flex align-items-center justify-content-between">
                    <span class="row-badge editable-hint" contenteditable="true" data-row="${rowLabel}">${rowLabel}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger delete-row-btn ms-2" data-row="${rowLabel}" title="Delete row">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>`;
            tr.appendChild(rowHeaderTd);
            
            // Create data cells for each column
            columns.forEach(col => {
                const cellValue = (data[rowLabel] && data[rowLabel][col] !== undefined) ? data[rowLabel][col] : '';
                const td = document.createElement('td');
                td.innerHTML = `<div class="metric-cell editable-hint" contenteditable="true" data-column="${col}" data-row="${rowLabel}">${cellValue}</div>`;
                tr.appendChild(td);
            });
            
            tbody.appendChild(tr);
        });

        // Reattach all event handlers
        attachEventHandlers();
    }

    function attachEventHandlers() {
        // Delete row button handlers
        document.querySelectorAll('.delete-row-btn').forEach(btn => {
            btn.onclick = (e) => {
                e.stopPropagation();
                const row = btn.getAttribute('data-row');
                if (confirm(`Delete row "${row}"? This action cannot be undone.`)) {
                    removeRow(row);
                }
            };
        });

        // Row title edit handlers
        document.querySelectorAll('.row-badge').forEach(el => {
            // Remove existing listeners first
            el.removeEventListener('input', handleRowTitleEdit);
            el.removeEventListener('blur', handleRowTitleBlur);
            el.removeEventListener('keydown', handleRowTitleKeydown);
            
            // Add new listeners
            el.addEventListener('input', handleRowTitleEdit);
            el.addEventListener('blur', handleRowTitleBlur);
            el.addEventListener('keydown', handleRowTitleKeydown);
        });

        // Delete column button handlers
        document.querySelectorAll('.delete-column-btn').forEach(btn => {
            btn.onclick = (e) => {
                e.stopPropagation();
                const col = btn.getAttribute('data-column');
                if (confirm(`Delete column "${col}"? This action cannot be undone.`)) {
                    removeColumn(col);
                }
            };
        });

        // Column title edit handlers
        document.querySelectorAll('.metric-title').forEach(el => {
            // Remove existing listeners first
            el.removeEventListener('input', handleColumnTitleEdit);
            el.removeEventListener('blur', handleColumnTitleBlur);
            el.removeEventListener('keydown', handleColumnTitleKeydown);
            
            // Add new listeners
            el.addEventListener('input', handleColumnTitleEdit);
            el.addEventListener('blur', handleColumnTitleBlur);
            el.addEventListener('keydown', handleColumnTitleKeydown);
        });

        // Data cell edit handlers
        document.querySelectorAll('.metric-cell').forEach(cell => {
            // Remove existing listeners first
            cell.removeEventListener('input', handleDataCellEdit);
            cell.removeEventListener('blur', handleDataCellBlur);
            
            // Add new listeners
            cell.addEventListener('input', handleDataCellEdit);
            cell.addEventListener('blur', handleDataCellBlur);
        });
    }

    // Initialize event handlers for add column and add row buttons
    document.getElementById('addColumnBtn').addEventListener('click', addColumn);
    document.getElementById('addRowBtn').addEventListener('click', addRow);

    // Handle button clicks to set draft status
    document.getElementById('submitBtn').addEventListener('click', function(e) {
        document.getElementById('isDraftInput').value = '0';
        // Save as final outcome clicked
    });

    // Handle form submission
    document.getElementById('editOutcomeForm').addEventListener('submit', function(e) {
        // Form submission started
        
        // Blur all contenteditable elements to ensure latest edits are committed
        document.querySelectorAll('.editable-hint').forEach(function(el) {
            el.blur();
        });
        
        // Collect any final changes from DOM before submission
        collectCurrentData();
        
        // Convert data object to rows array for DB
        const rowsArray = Object.keys(data).map(rowLabel => {
            const rowObj = { month: rowLabel };
            columns.forEach(col => {
                rowObj[col] = data[rowLabel][col];
            });
            return rowObj;
        });
        
        // Use the maintained data object
        const collectedData = {
            columns: columns,
            rows: rowsArray
        };
        
        // Data collected for submission
        document.getElementById('dataJsonInput').value = JSON.stringify(collectedData);
        
        // Debug: log the data being submitted
        console.log('Submitting outcome data:', collectedData);
        console.log('Hidden input value:', document.getElementById('dataJsonInput').value);
        
        // Basic validation
        const outcomeCode = document.getElementById('outcomeCodeInput').value.trim();
        const outcomeType = document.getElementById('outcomeTypeInput').value.trim();
        const outcomeTitle = document.getElementById('outcomeTitleInput').value.trim();
        const outcomeDescription = document.getElementById('outcomeDescriptionInput').value.trim();
        if (!outcomeCode) {
            e.preventDefault();
            alert('Please enter an outcome code.');
            return false;
        }
        if (!outcomeType) {
            e.preventDefault();
            alert('Please enter an outcome type.');
            return false;
        }
        if (!outcomeTitle) {
            e.preventDefault();
            alert('Please enter an outcome title.');
            return false;
        }
        if (!outcomeDescription) {
            e.preventDefault();
            alert('Please enter an outcome description.');
            return false;
        }
        if (columns.length === 0) {
            e.preventDefault();
            alert('Please add at least one column.');
            return false;
        }
        if (Object.keys(data).length === 0) {
            e.preventDefault();
            alert('Please add at least one row.');
            return false;
        }
        // Form validation passed, submitting
    });

    // Initial render when page loads
    renderTable(true);
});
</script> 