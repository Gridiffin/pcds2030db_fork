<?php
/**
 * Create Outcome
 * 
 * Admin page to create a new outcome with a table name, dynamic columns, and monthly data.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Initialize variables
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $table_name = trim($_POST['table_name'] ?? '');
    $data_json = $_POST['data_json'] ?? '';
    $is_draft = isset($_POST['is_draft']) ? intval($_POST['is_draft']) : 0;

    if ($table_name === '' || $data_json === '') {
        $message = 'Table name and data are required.';
        $message_type = 'danger';
    } else {
        $metric_id = 0; // New metric id will be generated
        $sector_id = $_POST['sector_id'] ?? 0;

        // Decode JSON data
        $data_array = json_decode($data_json, true);
        if ($data_array === null) {
            $message = 'Invalid JSON data.';
            $message_type = 'danger';
        } else {
            // Insert new record into sector_outcomes_data
            $insert_query = "INSERT INTO sector_outcomes_data (metric_id, sector_id, table_name, data_json, is_draft) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            // metric_id is auto-increment or generated, so set to NULL or 0
            $metric_id = 0;
            $sector_id = intval($sector_id);
            $data_json_str = json_encode($data_array);
            $stmt->bind_param("iissi", $metric_id, $sector_id, $table_name, $data_json_str, $is_draft);
            if ($stmt->execute()) {
                $message = 'Outcome created successfully.';
                $message_type = 'success';
                // Redirect to manage outcomes or edit page
                header('Location: manage_outcomes.php');
                exit;
            } else {
                $message = 'Error saving outcome: ' . $conn->error;
                $message_type = 'danger';
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

// Include header and admin navigation
require_once '../../layouts/header.php';
require_once '../../layouts/admin_nav.php';

// Set page header variables
$title = "Create Outcome";
$subtitle = "Create a new outcome with monthly data";
$headerStyle = 'light';
$actions = [
    [
        'url' => 'manage_outcomes.php',
        'text' => 'Back to Manage Outcomes',
        'icon' => 'fa-arrow-left',
        'class' => 'btn-outline-primary'
    ]
];

// Include dashboard header
require_once ROOT_PATH . 'app/lib/dashboard_header.php';
?>

<div class="container-fluid px-4 py-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">Create New Outcome</h5>
        </div>
        <div class="card-body">
            <form id="createOutcomeForm" method="post" action="">
                <div class="mb-3">
                    <label for="tableNameInput" class="form-label">Table Name</label>
                    <input type="text" class="form-control" id="tableNameInput" name="table_name" required />
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
                    <a href="manage_outcomes.php" class="btn btn-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // JavaScript to handle dynamic columns and data collection

    const monthNames = <?= json_encode($month_names) ?>;
    let columns = [];

    function addColumn() {
        const columnName = prompt('Enter column title:');
        if (!columnName || columnName.trim() === '') return;
        if (columns.includes(columnName)) {
            alert('Column title already exists.');
            return;
        }
        columns.push(columnName);
        renderTable();
    }

    function removeColumn(columnName) {
        columns = columns.filter(c => c !== columnName);
        renderTable();
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
                const td = document.createElement('td');
                td.innerHTML = `<div class="metric-cell" contenteditable="true" data-column="${col}" data-month="${row.querySelector('.month-badge').textContent}"></div>`;
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
        });

        // Attach contenteditable change handlers for column titles
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
                    const index = columns.indexOf(oldCol);
                    if (index !== -1) {
                        columns[index] = newCol;
                        el.setAttribute('data-column', newCol);
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
        });

        // Make entire body td clickable to focus the contenteditable div inside
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
    }

    document.getElementById('addColumnBtn').addEventListener('click', addColumn);

    document.getElementById('createOutcomeForm').addEventListener('submit', function(e) {
        // Collect data into JSON
        const data = {
            columns: columns,
            data: {}
        };
        monthNames.forEach(month => {
            data.data[month] = {};
            columns.forEach(col => {
                const cell = document.querySelector(`.metric-cell[data-month="${month}"][data-column="${col}"]`);
                let val = 0;
                if (cell) {
                    val = parseFloat(cell.textContent.trim());
                    if (isNaN(val)) val = 0;
                }
                data.data[month][col] = val;
            });
        });
        document.getElementById('dataJsonInput').value = JSON.stringify(data);
    });
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
