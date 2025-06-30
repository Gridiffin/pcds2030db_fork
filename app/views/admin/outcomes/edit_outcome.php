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

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Initialize variables
$message = '';
$message_type = '';

// Get outcome ID from URL (using metric_id for admin consistency)
$metric_id = isset($_GET['metric_id']) ? intval($_GET['metric_id']) : 0;

if ($metric_id === 0) {
    $_SESSION['error_message'] = 'Invalid outcome ID.';
    header('Location: manage_outcomes.php');
    exit;
}

// Load existing outcome data - admin can edit any outcome regardless of sector
$query = "SELECT table_name, data_json, is_draft, sector_id FROM sector_outcomes_data WHERE metric_id = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $metric_id);
$stmt->execute();
$result = $stmt->get_result();

$table_name = '';
$is_outcome_draft = 1; // Default to draft
$sector_id = 0;
$data_array = [
    'columns' => [],
    'data' => []
];

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $table_name = $row['table_name'];
    $is_outcome_draft = $row['is_draft']; // Store the current draft status
    $sector_id = $row['sector_id'];
    $data_array = json_decode($row['data_json'], true);
    if (!is_array($data_array)) {
        $data_array = ['columns' => [], 'data' => []];
    }
} else {
    $_SESSION['error_message'] = 'Outcome not found.';
    header('Location: manage_outcomes.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_table_name = trim($_POST['table_name'] ?? '');
    $post_data_json = $_POST['data_json'] ?? '';
    $is_draft = isset($_POST['is_draft']) ? intval($_POST['is_draft']) : 0;

    if ($post_table_name === '' || $post_data_json === '') {
        $message = 'Table name and data are required.';
        $message_type = 'danger';
    } else {
        $post_data_array = json_decode($post_data_json, true);
        if ($post_data_array === null) {
            $message = 'Invalid JSON data.';
            $message_type = 'danger';
        } else {
            // Update existing record in sector_outcomes_data
            $update_query = "UPDATE sector_outcomes_data SET table_name = ?, data_json = ?, is_draft = ?, updated_at = NOW() WHERE metric_id = ?";
            $stmt_update = $conn->prepare($update_query);
            $data_json_str = json_encode($post_data_array);
            $stmt_update->bind_param("ssii", $post_table_name, $data_json_str, $is_draft, $metric_id);
            
            if ($stmt_update->execute()) {
                // Log successful outcome edit
                log_audit_action(
                    'outcome_updated',
                    "Admin updated outcome '{$post_table_name}' (Metric ID: {$metric_id}) for sector {$sector_id}" . ($is_draft ? ' as draft' : ''),
                    'success',
                    $_SESSION['user_id']
                );
                
                // Redirect to view outcome details after successful save
                header('Location: view_outcome.php?metric_id=' . $metric_id . '&saved=1');
                exit;
            } else {
                $message = 'Error updating outcome: ' . $conn->error;
                $message_type = 'danger';
                
                // Log outcome update failure
                log_audit_action(
                    'outcome_update_failed',
                    "Admin failed to update outcome '{$post_table_name}' (Metric ID: {$metric_id}) for sector {$sector_id}: " . $conn->error,
                    'failure',
                    $_SESSION['user_id']
                );
            }
        }
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
$header_config = [
    'title' => 'Edit Outcome',
    'subtitle' => 'Edit existing outcome with dynamic table structure' . ($is_outcome_draft ? ' (Draft)' : ' (Submitted)'),
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'manage_outcomes.php',
            'text' => 'Back to Manage Outcomes',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-primary'
        ],
        [
            'url' => 'view_outcome.php?metric_id=' . $metric_id,
            'text' => 'View Outcome',
            'icon' => 'fas fa-eye',
            'class' => 'btn-outline-info'
        ],
        [
            'html' => '<span class="badge ' . ($is_outcome_draft ? 'bg-warning text-dark' : 'bg-success') . '"><i class="fas ' . ($is_outcome_draft ? 'fa-edit' : 'fa-check') . ' me-1"></i>' . ($is_outcome_draft ? 'Draft' : 'Submitted') . '</span>'
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
            <p class="text-muted mb-0 small">Outcome ID: <?= $metric_id ?> | Sector ID: <?= $sector_id ?></p>
        </div>
        <div class="card-body">
            <form id="editOutcomeForm" method="post" action="">
                <div class="mb-3">
                    <label for="tableNameInput" class="form-label">Table Name</label>
                    <input type="text" class="form-control" id="tableNameInput" name="table_name" required value="<?= htmlspecialchars($table_name) ?>" />
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
                            // Get row labels from existing data or use default if no data exists
                            $row_labels = [];
                            if (!empty($data_array['data']) && is_array($data_array['data'])) {
                                $row_labels = array_keys($data_array['data']);
                            }
                            
                            // If no existing data, provide a default structure that can be modified
                            if (empty($row_labels)) {
                                $row_labels = ['Row 1', 'Row 2', 'Row 3']; // Default starting rows
                            }
                            
                            foreach ($row_labels as $row_label): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-between">
                                            <span class="row-badge editable-hint" contenteditable="true" data-row="<?= htmlspecialchars($row_label) ?>"><?= htmlspecialchars($row_label) ?></span>
                                            <button type="button" class="btn btn-sm btn-outline-danger delete-row-btn ms-2" data-row="<?= htmlspecialchars($row_label) ?>" title="Delete row">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                    <!-- Dynamic cells will be added here -->
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <input type="hidden" name="data_json" id="dataJsonInput" />
                <input type="hidden" name="is_draft" id="isDraftInput" value="<?= $is_outcome_draft ?>" />

                <div class="d-flex justify-content-end gap-2">
                    <a href="view_outcome.php?metric_id=<?= $metric_id ?>" class="btn btn-outline-secondary">
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
    // Set up save button to work with the main form
    const saveBtn = document.getElementById('submitBtn');
    if (saveBtn) {
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            this.blur();
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

    function handleDataCellEdit() {
        const rowLabel = this.getAttribute('data-row');
        const columnName = this.getAttribute('data-column');
        const value = this.textContent.trim();
        
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
    document.getElementById('saveBtn').addEventListener('click', function(e) {
        document.getElementById('isDraftInput').value = '0';
        // Save as final outcome clicked
    });

    // Handle form submission
    document.getElementById('editOutcomeForm').addEventListener('submit', function(e) {
        // Form submission started
        
        // Collect any final changes from DOM before submission
        collectCurrentData();
        
        // Use the maintained data object
        const collectedData = {
            columns: columns,
            data: data
        };
        
        // Data collected for submission
        document.getElementById('dataJsonInput').value = JSON.stringify(collectedData);
        
        // Basic validation
        const tableName = document.getElementById('tableNameInput').value.trim();
        if (!tableName) {
            e.preventDefault();
            alert('Please enter a table name.');
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
