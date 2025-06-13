<?php
/**
 * Edit Outcome for Agency
 * 
 * Agency page to edit an existing outcome with a table name, dynamic columns, and monthly data.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/agency_functions.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is an agency user
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Initialize variables
$message = '';
$message_type = '';

$sector_id = $_SESSION['sector_id'] ?? 0; // Use agency user's sector_id
// Use outcome_id instead of metric_id
$outcome_id = isset($_GET['outcome_id']) ? intval($_GET['outcome_id']) : 0;

if ($outcome_id === 0) {
    $_SESSION['error_message'] = 'Invalid outcome ID.';
    header('Location: submit_outcomes.php');
    exit;
}

// Load existing outcome data
$query = "SELECT table_name, data_json FROM sector_outcomes_data WHERE metric_id = ? AND sector_id = ? AND is_draft = 1 LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $outcome_id, $sector_id);
$stmt->execute();
$result = $stmt->get_result();

$table_name = '';
$data_array = [
    'columns' => [],
    'data' => []
];

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $table_name = $row['table_name'];
    $data_array = json_decode($row['data_json'], true);
    if (!is_array($data_array)) {
        $data_array = ['columns' => [], 'data' => []];
    }
} else {
    // No existing data found, initialize empty structure
    $data_array = ['columns' => [], 'data' => []];
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
        } else {            // Update existing record in sector_outcomes_data
            $update_query = "UPDATE sector_outcomes_data SET table_name = ?, data_json = ?, is_draft = ? WHERE metric_id = ? AND sector_id = ?";
            $stmt_update = $conn->prepare($update_query);
            $data_json_str = json_encode($post_data_array);
            $stmt_update->bind_param("ssiii", $post_table_name, $data_json_str, $is_draft, $outcome_id, $sector_id);            if ($stmt_update->execute()) {
                // Log successful outcome edit
                log_audit_action(
                    'outcome_updated',
                    "Updated outcome '{$post_table_name}' (Metric ID: {$outcome_id}) for sector {$sector_id}" . ($is_draft ? ' as draft' : ''),
                    'success',
                    $_SESSION['user_id']
                );
                
                // Redirect to submit_outcomes.php after successful save or save draft
                header('Location: submit_outcomes.php');
                exit;
            } else {
                $message = 'Error updating outcome: ' . $conn->error;
                $message_type = 'danger';
                
                // Log outcome update failure
                log_audit_action(
                    'outcome_update_failed',
                    "Failed to update outcome '{$post_table_name}' (Metric ID: {$outcome_id}) for sector {$sector_id}: " . $conn->error,
                    'failure',
                    $_SESSION['user_id']
                );
            }
        }
    }
}

// Add CSS and JS references
$additionalStyles = [
    APP_URL . '/assets/css/custom/metric-create.css'
];
$additionalScripts = [
    APP_URL . '/assets/js/metric-editor.js'
];

// Include header and agency navigation
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Edit Outcome',
    'subtitle' => 'Edit an existing outcome with monthly data',
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'submit_outcomes.php',
            'text' => 'Back to Submit Outcomes',
            'icon' => 'fa-arrow-left',
            'class' => 'btn-outline-primary'
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
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover metrics-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 150px;">Month</th>
                                <!-- Dynamic columns will be added here -->
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $month_names = ['January', 'February', 'March', 'April', 'May', 'June',
                                'July', 'August', 'September', 'October', 'November', 'December'];
                            foreach ($month_names as $month_name): ?>
                                <tr>
                                    <td><span class="month-badge"><?= htmlspecialchars($month_name) ?></span></td>
                                    <!-- Dynamic cells will be added here -->
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <input type="hidden" name="data_json" id="dataJsonInput" />

                <div class="mt-3">
                    <input type="hidden" name="is_draft" id="isDraftInput" value="0" />
                    <button type="submit" class="btn btn-success" id="saveBtn" onclick="document.getElementById('isDraftInput').value='0';">Save Outcome</button>
                    <button type="submit" class="btn btn-warning ms-2" id="saveDraftBtn" onclick="document.getElementById('isDraftInput').value='1';">Save as Draft</button>
                    <a href="submit_outcomes.php" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // JavaScript to handle dynamic columns and data collection

    const monthNames = <?= json_encode($month_names) ?>;
    let columns = <?= json_encode($data_array['columns'] ?? []) ?>;
    let data = <?= json_encode($data_array['data'] ?? []) ?>;    function addColumn() {
        const columnName = prompt('Enter column title:');
        if (!columnName || columnName.trim() === '') return;
        if (columns.includes(columnName)) {
            alert('Column title already exists.');
            return;
        }
        
        // Collect current data from DOM before adding column
        collectCurrentData();
        
        columns.push(columnName);
        renderTable();
    }

    function removeColumn(columnName) {
        // Collect current data from DOM before removing column
        collectCurrentData();
        
        columns = columns.filter(c => c !== columnName);
        
        // Remove data for the deleted column
        monthNames.forEach(month => {
            if (data[month]) {
                delete data[month][columnName];
            }
        });
        
        renderTable();
    }

    function collectCurrentData() {
        // Initialize data structure if needed
        if (!data || typeof data !== 'object') {
            data = {};
        }
        
        // Collect all current values from DOM
        monthNames.forEach(month => {
            if (!data[month]) {
                data[month] = {};
            }
            
            columns.forEach(col => {
                const cell = document.querySelector(`.metric-cell[data-month="${month}"][data-column="${col}"]`);
                if (cell) {
                    let val = parseFloat(cell.textContent.trim());
                    if (isNaN(val)) val = 0;
                    data[month][col] = val;
                }
            });
        });
    }

    function renderTable() {
        const theadRow = document.querySelector('.metrics-table thead tr');
        // Remove all columns except the first (Month)
        while (theadRow.children.length > 1) {
            theadRow.removeChild(theadRow.lastChild);
        }
        columns.forEach(col => {
            const th = document.createElement('th');
            th.innerHTML = `
                <div class="metric-header">
                    <div class="metric-title" contenteditable="true" data-column="${col}">${col}</div>
                    <div class="metric-actions">
                        <button type="button" class="btn btn-sm btn-danger delete-column-btn" data-column="${col}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>`;
            theadRow.appendChild(th);
        });

        const tbody = document.querySelector('.metrics-table tbody');
        tbody.querySelectorAll('tr').forEach(row => {
            // Remove all cells except the first (Month)
            while (row.children.length > 1) {
                row.removeChild(row.lastChild);
            }
            columns.forEach(col => {
                const month = row.querySelector('.month-badge').textContent;
                const cellValue = (data[month] && data[month][col] !== undefined) ? data[month][col] : '';
                const td = document.createElement('td');
                td.innerHTML = `<div class="metric-cell" contenteditable="true" data-column="${col}" data-month="${month}">${cellValue}</div>`;
                row.appendChild(td);
            });
        });

        // Attach delete handlers
        document.querySelectorAll('.delete-column-btn').forEach(btn => {
            btn.onclick = () => {
                const col = btn.getAttribute('data-column');
                if (confirm('Delete column "' + col + '"?')) {
                    removeColumn(col);
                }
            };
        });        // Attach contenteditable change handlers for column titles
        document.querySelectorAll('.metric-title').forEach(el => {
            el.addEventListener('input', () => {
                const oldCol = el.getAttribute('data-column');
                const newCol = el.textContent.trim();
                if (newCol && newCol !== oldCol) {
                    if (columns.includes(newCol)) {
                        alert('Column title already exists.');
                        el.textContent = oldCol;
                        return;
                    }
                    
                    // Collect current data before renaming column
                    collectCurrentData();
                    
                    const index = columns.indexOf(oldCol);
                    if (index !== -1) {
                        columns[index] = newCol;
                        el.setAttribute('data-column', newCol);
                        
                        // Update data object with new column name
                        monthNames.forEach(month => {
                            if (data[month] && data[month][oldCol] !== undefined) {
                                data[month][newCol] = data[month][oldCol];
                                delete data[month][oldCol];
                            }
                        });
                        
                        // Update all cells data-column attribute
                        document.querySelectorAll(`[data-column="${oldCol}"]`).forEach(cell => {
                            cell.setAttribute('data-column', newCol);
                        });
                    }
                }
            });
        });

        // Make entire header th clickable to focus the contenteditable div inside
        document.querySelectorAll('.metrics-table thead th').forEach(th => {
            th.style.cursor = 'text';
            th.addEventListener('click', (e) => {
                // Prevent focusing if clicking on delete button
                if (e.target.closest('.delete-column-btn')) return;
                const editableDiv = th.querySelector('.metric-title');
                if (editableDiv) {
                    editableDiv.focus();
                    // Place cursor at end
                    const range = document.createRange();
                    range.selectNodeContents(editableDiv);
                    range.collapse(false);
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            });
        });        // Make entire body td clickable to focus the contenteditable div inside
        document.querySelectorAll('.metrics-table tbody td').forEach(td => {
            td.style.cursor = 'text';
            td.addEventListener('click', (e) => {
                // Prevent focusing if clicking inside the div itself to avoid double focus
                if (e.target.classList.contains('metric-cell')) return;
                const editableDiv = td.querySelector('.metric-cell');
                if (editableDiv) {
                    editableDiv.focus();
                    // Place cursor at end
                    const range = document.createRange();
                    range.selectNodeContents(editableDiv);
                    range.collapse(false);
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                }
            });
        });

        // Add real-time data updating for cell edits
        document.querySelectorAll('.metric-cell').forEach(cell => {
            cell.addEventListener('input', () => {
                const month = cell.getAttribute('data-month');
                const column = cell.getAttribute('data-column');
                if (month && column) {
                    if (!data[month]) {
                        data[month] = {};
                    }
                    let val = parseFloat(cell.textContent.trim());
                    if (isNaN(val)) val = 0;
                    data[month][column] = val;
                }
            });
            
            // Also update on blur (when user leaves the cell)
            cell.addEventListener('blur', () => {
                const month = cell.getAttribute('data-month');
                const column = cell.getAttribute('data-column');
                if (month && column) {
                    if (!data[month]) {
                        data[month] = {};
                    }
                    let val = parseFloat(cell.textContent.trim());
                    if (isNaN(val)) val = 0;
                    data[month][column] = val;
                }
            });
        });
    }

    document.getElementById('addColumnBtn').addEventListener('click', addColumn);    document.getElementById('editOutcomeForm').addEventListener('submit', function(e) {
        // Collect any final changes from DOM before submission
        collectCurrentData();
        
        // Use the maintained data object
        const collectedData = {
            columns: columns,
            data: data
        };
        
        document.getElementById('dataJsonInput').value = JSON.stringify(collectedData);
    });

    // Initial render
    document.addEventListener('DOMContentLoaded', () => {
        renderTable();
    });
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
