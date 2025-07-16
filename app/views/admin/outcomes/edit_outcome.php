<?php
/**
 * Edit Outcome Details - Admin Version
 * 
 * Admin interface to edit outcome details with support for flexible table structures
 * Based on working agency implementation
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admin_functions.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';
require_once ROOT_PATH . 'app/lib/admins/outcomes.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Initialize variables
$message = '';
$message_type = '';

// Get outcome ID from URL
$outcome_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($outcome_id === 0) {
    $_SESSION['error_message'] = 'Outcome not found or already deleted.';
    header('Location: manage_outcomes.php');
    exit;
}

// Fetch outcome from new outcomes table
$outcome = get_outcome_by_id($outcome_id);
if (!$outcome) {
    $_SESSION['error_message'] = 'Outcome not found or already deleted.';
    header('Location: manage_outcomes.php');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_data = [];
    if (isset($_POST['data'])) {
        // Decode the JSON string from the hidden input
        $decoded = json_decode($_POST['data'], true);
        if (is_array($decoded)) {
            $post_data = $decoded;
        }
    }
    if (update_outcome_data_by_code($outcome['code'], $post_data)) {
        header('Location: view_outcome.php?id=' . $outcome_id . '&saved=1');
        exit;
    } else {
        $message = 'Error updating outcome.';
        $message_type = 'danger';
    }
}

// Add CSS references
$additionalStyles = [
    APP_URL . '/assets/css/custom/metric-create.css'
];
$additionalScripts = [
    // Using embedded JavaScript instead of external files
];

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$is_draft = isset($outcome['is_draft']) ? $outcome['is_draft'] : 0;
$header_config = [
    'title' => 'Edit Outcome',
    'subtitle' => 'Edit existing outcome with dynamic table structure' . ($is_draft ? ' (Draft)' : ' (Submitted)'),
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'manage_outcomes.php',
            'text' => 'Back to Manage Outcomes',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-primary'
        ],
        [
            'url' => 'view_outcome.php?id=' . $outcome_id,
            'text' => 'View Outcome',
            'icon' => 'fas fa-eye',
            'class' => 'btn-outline-info'
        ],
        [
            'html' => '<span class="badge ' . ($is_draft ? 'bg-warning text-dark' : 'bg-success') . '"><i class="fas ' . ($is_draft ? 'fa-edit' : 'fa-check') . ' me-1"></i>' . ($is_draft ? 'Draft' : 'Submitted') . '</span>'
        ]
    ]
];

// Include modern page header
require_once '../../layouts/page_header.php';
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
                
                // Update delete button data attribute
                const deleteBtn = this.parentElement.querySelector('.delete-row-btn');
                if (deleteBtn) {
                    deleteBtn.setAttribute('data-row', newRow);
                }
            }
        }
    }

    function handleRowTitleBlur() {
        // Validate row name
        const rowName = this.textContent.trim();
        if (rowName === '') {
            this.textContent = this.getAttribute('data-row');
        }
    }

    function handleRowTitleKeydown(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            this.blur();
        }
    }

    function handleDataCellEdit() {
        const rowLabel = this.getAttribute('data-row');
        const columnName = this.getAttribute('data-column');
        const value = this.textContent.trim();
        
        // Initialize nested objects if they don't exist
        if (!data[rowLabel]) {
            data[rowLabel] = {};
        }
        
        // Store numeric value or 0 if invalid
        const numValue = parseFloat(value);
        data[rowLabel][columnName] = isNaN(numValue) ? 0 : numValue;
    }

    function collectCurrentData() {
        // Collect data from table DOM elements
        const rowElements = document.querySelectorAll('.metrics-table tbody tr');
        const currentData = {};
        
        rowElements.forEach(row => {
            const rowBadge = row.querySelector('.row-badge');
            if (rowBadge) {
                const rowLabel = rowBadge.textContent.trim();
                currentData[rowLabel] = {};
                
                columns.forEach(col => {
                    const cell = row.querySelector(`.metric-cell[data-row="${rowLabel}"][data-column="${col}"]`);
                    if (cell) {
                        let val = parseFloat(cell.textContent.trim());
                        if (isNaN(val)) val = 0;
                        currentData[rowLabel][col] = val;
                    }
                });
            }
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
        
        // Basic validation
        const outcomeCode = document.getElementById('outcomeCodeInput').value.trim();
        if (!outcomeCode) {
            e.preventDefault();
            alert('Please enter an outcome code.');
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

<?php
// Include footer
require_once ROOT_PATH . 'app/views/layouts/footer.php';
?>
