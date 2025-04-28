<?php
/**
 * Create Sector Metrics
 * 
 * Interface for agency users to create sector-specific metrics
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agency_functions.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ../../login.php');
    exit;
}

// Get current period ID
$current_period = get_current_reporting_period();
$period_id = $current_period['period_id'] ?? null;

if (!$period_id) {
    $_SESSION['error_message'] = 'No active reporting period found. Please contact an administrator.';
    header('Location: submit_metrics.php');
    exit;
}

$sector_id = $_GET['sector_id'] ?? $_SESSION['sector_id'];

// Set page title
$pageTitle = 'Create Sector Metrics';

// Handle form submission for new metrics
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    $metric_id = isset($_GET['next_metric_id']) ? intval($_GET['next_metric_id']) : 0;
    $_SESSION['metric_id'] = $metric_id;
    if (isset($_POST['table_name']) && trim($_POST['table_name']) !== '') {
        $new_table_name = $conn->real_escape_string($_POST['table_name']);
        // Check if a row exists for this metric_id and sector_id
        $check_query = "SELECT 1 FROM sector_metrics_data WHERE metric_id = $metric_id AND sector_id = '$sector_id' LIMIT 1";
        $check_result = $conn->query($check_query);
        if ($check_result && $check_result->num_rows > 0) {
            // Update existing row
            $update_query = "UPDATE sector_metrics_data SET table_name = '$new_table_name' WHERE sector_id = '$sector_id' AND metric_id = $metric_id";
            if ($conn->query($update_query) === TRUE) {
                $message = "Table name updated successfully.";
                $message_type = "success";
            } else {
                $message = "Error updating table name: " . $conn->error;
                $message_type = "danger";
            }
        } else {
            // Insert new row with table_name
            $insert_table_name_query = "INSERT INTO sector_metrics_data (metric_id, table_name, column_title, table_content, month, sector_id) 
                VALUES ($metric_id, '$new_table_name', '', 0, 'January', '$sector_id')";
            if ($conn->query($insert_table_name_query) === TRUE) {
                $message = "Table name saved successfully.";
                $message_type = "success";
            } else {
                $message = "Error saving table name: " . $conn->error;
                $message_type = "danger";
            }
        }
    } else {
        // Use provided values or defaults for metric insert
        $name = $conn->real_escape_string($_POST['column_title'] ?? '');
        $value = floatval($_POST['table_content'] ?? 0);
        $month = $conn->real_escape_string($_POST['month'] ?? '');
        $table_name_post = $conn->real_escape_string($_POST['table_name'] ?? '');

        // If table_name is empty, generate a new table_name
        if (empty($table_name_post)) {
            $table_name_post = "Table_" . $metric_id;
        }

        // Insert new metric with table_name and metric_id
        $query = "INSERT INTO sector_metrics_data (metric_id, table_name, column_title, table_content, month, sector_id) 
                VALUES ($metric_id, '$table_name_post', '$name', '$value', '$month', '$sector_id')";

        if ($conn->query($query) === TRUE) {
            $message = "Metric created successfully.";
            $message_type = "success";
        } else {
            $message = "Error: " . $conn->error;
            $message_type = "danger";
        }
    }
}

// Retrieve all metrics for display
$metric_id = isset($_GET['next_metric_id']) ? intval($_GET['next_metric_id']) : 0;
if ($metric_id === 0) {
    $result = $conn->query("SELECT MAX(metric_id) AS max_id FROM sector_metrics_data");
    if ($result && $row = $result->fetch_assoc()) {
        $metric_id = $row['max_id'] + 1;
    }
}
$select_query = "SELECT * FROM sector_metrics_data WHERE metric_id = $metric_id";
$metrics = $conn->query($select_query);
if (!$metrics) die("Error getting metrics: " . $conn->error);

// Organize data for display
$month_names = ['January', 'February', 'March', 'April', 'May', 'June', 
                'July', 'August', 'September', 'October', 'November', 'December'];
$table_data = [];
while ($row = $metrics->fetch_assoc()) {
    $month_index = array_search($row['month'], $month_names);
    if ($month_index === false) {
        continue; // Skip invalid month
    }
    $table_data[$month_index]['month_name'] = $month_names[$month_index];
    $table_data[$month_index]['metrics'][$row['column_title']] = $row['table_content'];
}

// Get the table_name from the first metric row for the sector
$table_name = '';
$result = $conn->query("SELECT table_name FROM sector_metrics_data WHERE metric_id = $metric_id AND sector_id = $sector_id LIMIT 1");
if ($result && $row = $result->fetch_assoc()) {
    $table_name = $row['table_name'];
}

// Get unique metric names for column headers
$metric_names = [];
foreach ($table_data as $month_data) {  
    if (isset($month_data['metrics'])) {
        $metric_names = array_merge($metric_names, array_keys($month_data['metrics']));
    }
}
$metric_names = array_unique($metric_names);
sort($metric_names);

// Add CSS references
$additionalStyles = [
    APP_URL . '/assets/css/custom/metric-create.css'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';

// Set up the page header variables for dashboard_header.php
$title = "Create Sector Metrics";
$subtitle = "Define and manage your sector-specific metrics";
$headerStyle = 'light'; // Use light (white) style for inner pages
$actions = [
    [
        'url' => 'submit_metrics.php',
        'text' => 'Back to Metrics',
        'icon' => 'fa-arrow-left',
        'class' => 'btn-outline-primary'
    ]
];

// Include the dashboard header component
require_once '../../includes/dashboard_header.php';
?>

<div class="container-fluid px-4">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                <div><?php echo $message; ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title m-0">
                <i class="fas fa-table me-2"></i>Metric Table Definition
            </h5>
        </div>
        <div class="card-body">
            <form id="tableNameForm" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label for="tableNameInput" class="form-label">Table Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-signature"></i></span>
                            <input type="text" class="form-control" id="tableNameInput" 
                                   placeholder="Enter a descriptive name for this metric table" 
                                   value="<?= htmlspecialchars($table_name) ?>" required />
                            <button type="button" class="btn btn-primary" id="saveTableNameBtn">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                        <div class="form-text">Provide a clear, descriptive name for your metric table</div>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <div class="btn-group">
                            <button type="button" class="btn btn-info" id="addColumnBtn">
                                <i class="fas fa-plus-circle me-1"></i> Add Column
                            </button>
                            <button type="button" class="btn btn-outline-info" id="setAllUnitsBtn">
                                <i class="fas fa-ruler me-1"></i> Set All Units
                            </button>
                            <button type="button" class="btn btn-success ms-2" id="doneBtn">
                                <i class="fas fa-check me-1"></i> Save & Finish
                            </button>
                        </div>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover metrics-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 150px;">Month</th>
                            <?php foreach ($metric_names as $name): ?>
                                <th>
                                    <div class="metric-header">
                                        <div class="metric-title">
                                            <span class="metric-name" contenteditable="true" data-metric="<?= htmlspecialchars($name) ?>">
                                                <?= $name === '' ? '<span class="empty-value">Click to edit</span>' : htmlspecialchars($name) ?>
                                            </span>
                                            <?php if (isset($metrics_data['units'][$name])): ?>
                                            <span class="metric-unit-display">
                                                (<?= htmlspecialchars($metrics_data['units'][$name]) ?>)
                                            </span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="metric-actions">
                                            <button class="unit-btn" data-metric="<?= htmlspecialchars($name) ?>" 
                                                    data-current-unit="<?= htmlspecialchars($metrics_data['units'][$name] ?? '') ?>">
                                                <i class="fas fa-ruler"></i>
                                            </button>
                                            <button class="save-btn" data-metric="<?= htmlspecialchars($name) ?>">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button class="delete-column-btn" data-metric="<?= htmlspecialchars($name) ?>">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                            <?php if (empty($metric_names)): ?>
                                <th class="text-center text-muted">
                                    <em>No metrics defined. Click "Add Column" to start.</em>
                                </th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($month_names as $month_name): ?>
                            <?php 
                                $month_index = array_search($month_name, $month_names);
                                $month_data = $table_data[$month_index] ?? ['month_name' => $month_name, 'metrics' => []];
                            ?>
                            <tr>
                                <td>
                                    <span class="month-badge"><?= $month_name ?></span>
                                </td>
                                <?php foreach ($metric_names as $name): ?>
                                    <td>
                                        <div class="metric-cell">
                                            <span class="metric-value" 
                                                contenteditable="true" 
                                                data-metric="<?= htmlspecialchars($name) ?>" 
                                                data-month="<?= $month_name ?>">
                                                <?= isset($month_data['metrics'][$name]) ? number_format($month_data['metrics'][$name], 2) : ' ' ?>
                                            </span>
                                            <button class="save-btn" data-metric="<?= htmlspecialchars($name) ?>" data-month="<?= $month_name ?>">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                                <?php if (empty($metric_names)): ?>
                                    <td></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- Total Row -->
                        <?php if (!empty($metric_names)): ?>
                        <tr class="table-light font-weight-bold">
                            <td class="fw-bold">
                                <span class="total-badge">TOTAL</span>
                            </td>
                            <?php foreach ($metric_names as $name): ?>
                                <td class="fw-bold text-end">
                                    <?php
                                        $total = 0;
                                        foreach ($table_data as $month_data) {
                                            if (isset($month_data['metrics'][$name])) {
                                                $total += floatval($month_data['metrics'][$name]);
                                            }
                                        }
                                        echo number_format($total, 2);
                                    ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i> Click on any cell to edit its value. Click the check button to save changes.
            </small>
        </div>
    </div>
</div>

<?php
// Additional scripts for the page
$additionalScripts = [
    APP_URL . '/assets/js/metric-editor.js'
];
?>

<script>
    // Define variables needed by the metric-editor.js script
    const metricId = <?= json_encode($metric_id) ?>;
    let tableName = <?= json_encode($table_name) ?>;
    let showPhpMessages = false; // Flag to prevent duplicate messages
    
    // Function to handle adding a new column
    function handleAddColumn() {
        const newMetricName = prompt('Enter new metric name:');
        if (!newMetricName || newMetricName.trim() === '') return;

        // Create a new column in the UI immediately
        const tableHead = document.querySelector('.metrics-table thead tr');
        const tableRows = document.querySelectorAll('.metrics-table tbody tr');
        
        // Add the column header
        const newTh = document.createElement('th');
        newTh.innerHTML = `
            <div class="metric-header">
                <span class="metric-name" contenteditable="true" data-metric="${newMetricName}">
                    ${newMetricName}
                </span>
                <div class="metric-actions">
                    <button class="save-btn" data-metric="${newMetricName}">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="delete-column-btn" data-metric="${newMetricName}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>`;
        
        // If there was a "No metrics" placeholder column, remove it
        const noMetricsColumn = tableHead.querySelector('th.text-muted');
        if (noMetricsColumn) {
            noMetricsColumn.remove();
        }
        
        tableHead.appendChild(newTh);
        
        // Add the column cells for each row
        tableRows.forEach(row => {
            const monthName = row.querySelector('.month-badge').textContent;
            const newTd = document.createElement('td');
            
            // If there was a placeholder empty column, remove it
            if (row.cells.length === 2 && !row.cells[1].querySelector('.metric-cell')) {
                row.cells[1].remove();
            }
            
            newTd.innerHTML = `
                <div class="metric-cell">
                    <span class="metric-value" 
                        contenteditable="true" 
                        data-metric="${newMetricName}" 
                        data-month="${monthName}">
                        0.00
                    </span>
                    <button class="save-btn" data-metric="${newMetricName}" data-month="${monthName}">
                        <i class="fas fa-check"></i>
                    </button>
                </div>`;
            
            row.appendChild(newTd);
        });

        // Reinitialize event listeners
        if (typeof setupMetricValueListeners === 'function') {
            setupMetricValueListeners();
            setupMetricNameListeners();
            setupButtonHandlers();
            makeMetricCellsClickable();
        }

        // Save the new column to the database using standard form submission
        const formData = new FormData();
        formData.append('column_title', newMetricName);
        formData.append('table_content', '0');
        formData.append('month', 'January');
        formData.append('table_name', tableName);
        
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to save new column');
            showToast('New column added successfully', 'success');
        })
        .catch(error => {
            showToast('Error saving new column: ' + error.message, 'danger');
        });
    }
    
    // Function to handle saving the table name
    function handleSaveTableName() {
        const tableNameInput = document.getElementById('tableNameInput');
        const newTableName = tableNameInput.value.trim();
        
        if (!newTableName) {
            showToast('Table name cannot be empty', 'warning');
            return;
        }

        // Prepare form data
        const formData = new FormData();
        formData.append('table_name', newTableName);

        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to update table name');
            showToast('Table name updated successfully', 'success');
            // Update the global tableName variable
            tableName = newTableName;
        })
        .catch(error => {
            showToast('Error updating table name: ' + error.message, 'danger');
        });
    }
    
    // Function to handle column deletion
    function handleDeleteColumn() {
        const metric = this.dataset.metric;
        
        if (!metric) return;
        
        // Confirm deletion
        if (!confirm(`Are you sure you want to delete the "${metric}" column? This action cannot be undone.`)) {
            return;
        }
        
        console.log('Deleting column:', metric); // Debug info
        
        // Delete from the database using JSON format to match update_metric.php expectations
        fetch('update_metric.php', {
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
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to delete column');
            return response.json();
        })
        .then(data => {
            console.log('Delete response:', data); // Debug info
            
            // Remove the column from the UI
            const columnIndex = findColumnIndex(metric);
            if (columnIndex !== -1) {
                removeColumnFromTable(columnIndex);
                showToast(`Column "${metric}" deleted successfully`, 'success');
            }
        })
        .catch(error => {
            console.error('Delete error:', error); // Debug info
            showToast('Error deleting column: ' + error.message, 'danger');
        });
    }
    
    // Find column index by metric name
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
    
    // Remove column from table by index
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
        
        // Add "No metrics" placeholder if we removed the last column
        const remainingColumns = headerRow.querySelectorAll('th');
        if (remainingColumns.length === 1) { // Only month column remains
            const placeholderTh = document.createElement('th');
            placeholderTh.className = 'text-center text-muted';
            placeholderTh.innerHTML = '<em>No metrics defined. Click "Add Column" to start.</em>';
            headerRow.appendChild(placeholderTh);
            
            // Add empty cells to data rows
            rows.forEach(row => {
                const placeholderTd = document.createElement('td');
                row.appendChild(placeholderTd);
            });
        }
    }
    
    // Function to show toast notifications
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
        
        // Initialize and show toast using Bootstrap
        const bsToast = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
        bsToast.show();
        
        // Remove toast after it's hidden
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }
    
    // Any remaining inline scripts that haven't been moved to metric-editor.js
    document.getElementById('addColumnBtn').addEventListener('click', handleAddColumn);
    document.getElementById('saveTableNameBtn').addEventListener('click', handleSaveTableName);
    document.getElementById('doneBtn').addEventListener('click', () => {
        window.location.href = 'submit_metrics.php';
    });
    
    // Set up event handlers for delete column buttons
    document.querySelectorAll('.delete-column-btn').forEach(button => {
        button.addEventListener('click', handleDeleteColumn);
    });
    
    // Set up handler for the "Set All Units" button
    document.getElementById('setAllUnitsBtn').addEventListener('click', handleSetAllUnits);
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
