<?php
/**
 * Edit Sector Metrics
 * 
 * Interface for agency users to edit sector-specific metrics
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

$sector_id = $_GET['sector_id'] ?? $_SESSION['sector_id'];

// Set page title
$pageTitle = 'Edit Sector Metrics';

// Handle form submission for new metrics
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $metric_id = isset($_GET['metric_id']) ? intval($_GET['metric_id']) : 0;
    $_SESSION['metric_id'] = $metric_id;
    
    if (isset($_POST['table_name']) && trim($_POST['table_name']) !== '') {
        $new_table_name = $conn->real_escape_string($_POST['table_name']);
        
        // For the new JSON-based storage
        $query = "SELECT data_json FROM sector_metrics_data 
                WHERE metric_id = ? AND sector_id = ? AND is_draft = 1 LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $metric_id, $sector_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            // Update existing metric data with new table name
            $row = $result->fetch_assoc();
            $metrics_data = json_decode($row['data_json'], true);
            
            // Update table name in database
            $update_query = "UPDATE sector_metrics_data 
                            SET table_name = ? 
                            WHERE metric_id = ? AND sector_id = ? AND is_draft = 1";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sii", $new_table_name, $metric_id, $sector_id);
            
            if ($update_stmt->execute()) {
                $message = "Table name updated successfully.";
                $message_type = "success";
            } else {
                $message = "Error updating table name: " . $conn->error;
                $message_type = "danger";
            }
        } else {
            // Initialize new data structure
            $metrics_data = [
                'columns' => [],
                'data' => []
            ];
            
            // Initialize months
            $months = ['January', 'February', 'March', 'April', 'May', 'June', 
                    'July', 'August', 'September', 'October', 'November', 'December'];
            
            foreach ($months as $m) {
                $metrics_data['data'][$m] = [];
            }
            
            // Save the new entry
            $json_data = json_encode($metrics_data);
            
            $insert_query = "INSERT INTO sector_metrics_data 
                            (metric_id, sector_id, table_name, data_json, is_draft) 
                            VALUES (?, ?, ?, ?, 1)";
            $insert_stmt = $conn->prepare($insert_query);
            $insert_stmt->bind_param("iiss", $metric_id, $sector_id, $new_table_name, $json_data);
            
            if ($insert_stmt->execute()) {
                $message = "Table name saved successfully.";
                $message_type = "success";
            } else {
                $message = "Error saving table name: " . $conn->error;
                $message_type = "danger";
            }
        }
    } else if (isset($_POST['column_title'])) {
        // Handle AJAX request for adding a new column
        $name = $conn->real_escape_string($_POST['column_title'] ?? '');
        $value = floatval($_POST['table_content'] ?? 0);
        $month = $conn->real_escape_string($_POST['month'] ?? 'January');
        $table_name = $conn->real_escape_string($_POST['table_name'] ?? '');

        // Get existing data
        $query = "SELECT data_json FROM sector_metrics_data 
                WHERE metric_id = ? AND sector_id = ? AND is_draft = 1 LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $metric_id, $sector_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $metrics_data = json_decode($row['data_json'], true);
            
            // Add new column to columns list if it doesn't exist
            if (!in_array($name, $metrics_data['columns'])) {
                $metrics_data['columns'][] = $name;
            }
            
            // Add value for January (or selected month)
            $metrics_data['data'][$month][$name] = $value;
            
            // Save updated data
            $json_data = json_encode($metrics_data);
            
            $update_query = "UPDATE sector_metrics_data 
                            SET data_json = ? 
                            WHERE metric_id = ? AND sector_id = ? AND is_draft = 1";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sii", $json_data, $metric_id, $sector_id);
            
            if ($update_stmt->execute()) {
                $message = "New column added successfully.";
                $message_type = "success";
                
                // For AJAX response
                if (isset($_POST['ajax']) && $_POST['ajax'] == 1) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Column added successfully',
                        'column_name' => $name,
                        'metrics_data' => $metrics_data
                    ]);
                    exit;
                }
            } else {
                $message = "Error adding column: " . $conn->error;
                $message_type = "danger";
                
                // For AJAX response
                if (isset($_POST['ajax']) && $_POST['ajax'] == 1) {
                    header('Content-Type: application/json');
                    echo json_encode(['error' => $conn->error]);
                    exit;
                }
            }
        } else {
            $message = "Error: Metric data not found.";
            $message_type = "danger";
            
            // For AJAX response
            if (isset($_POST['ajax']) && $_POST['ajax'] == 1) {
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Metric data not found']);
                exit;
            }
        }
    }
}

// Retrieve all metrics for display
$metric_id = isset($_GET['metric_id']) ? intval($_GET['metric_id']) : 0;
if ($metric_id === 0) {
    // Handle invalid metric ID
    $_SESSION['error_message'] = 'Invalid metric ID.';
    header('Location: submit_metrics.php');
    exit;
}

// Get metric data using JSON-based storage
$query = "SELECT data_json, table_name FROM sector_metrics_data 
          WHERE metric_id = ? AND sector_id = ? AND is_draft = 1 LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $metric_id, $sector_id);
$stmt->execute();
$result = $stmt->get_result();

$table_name = '';
$metric_names = [];
$month_names = ['January', 'February', 'March', 'April', 'May', 'June', 
                'July', 'August', 'September', 'October', 'November', 'December'];
$table_data = [];

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $table_name = $row['table_name'];
    $metrics_data = json_decode($row['data_json'], true);
    
    // Get column names
    $metric_names = $metrics_data['columns'] ?? [];
    
    // Organize data for display
    foreach ($month_names as $month_name) {
        $month_data = ['month_name' => $month_name, 'metrics' => []];
        
        // Add data for each metric in this month
        if (isset($metrics_data['data'][$month_name])) {
            $month_data['metrics'] = $metrics_data['data'][$month_name];
        }
        
        $table_data[] = $month_data;
    }
} else {
    // No data found - create empty structure
    foreach ($month_names as $month_name) {
        $table_data[] = ['month_name' => $month_name, 'metrics' => []];
    }
}

// Add CSS references
$additionalStyles = [
    APP_URL . '/assets/css/custom/metric-create.css'
];

// Add JS references
$additionalScripts = [
    APP_URL . '/assets/js/metric-editor.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';

// Set up the page header variables for dashboard_header.php
$title = "Edit Sector Metrics";
$subtitle = "Update your sector-specific metrics data";
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

<div class="container-fluid px-4 py-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">Edit Metrics Table</h5>
            <div>
                <button class="btn btn-sm btn-success" id="doneBtn">
                    <i class="fas fa-check me-1"></i> Done
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text">Table Name</span>
                        <input type="text" class="form-control" id="tableNameInput" value="<?= htmlspecialchars($table_name) ?>" />
                        <button class="btn btn-primary" id="saveTableNameBtn">Save</button>
                    </div>
                </div>
                <div class="col-md-6 text-end">
                    <div class="btn-group">
                        <button class="btn btn-primary" id="addColumnBtn">
                            <i class="fas fa-plus me-1"></i> Add Column
                        </button>
                        <button class="btn btn-outline-secondary" id="setAllUnitsBtn">
                            <i class="fas fa-ruler me-1"></i> Set All Units
                        </button>
                    </div>
                </div>
            </div>

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
                                                <?= htmlspecialchars($name) ?>
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
                        <?php foreach ($table_data as $month_data): ?>
                            <tr>
                                <td>
                                    <span class="month-badge"><?= $month_data['month_name'] ?></span>
                                </td>
                                <?php foreach ($metric_names as $name): ?>
                                    <td>
                                        <div class="metric-cell">
                                            <span class="metric-value" 
                                                contenteditable="true" 
                                                data-metric="<?= htmlspecialchars($name) ?>" 
                                                data-month="<?= $month_data['month_name'] ?>">
                                                <?= isset($month_data['metrics'][$name]) ? number_format($month_data['metrics'][$name], 2) : ' ' ?>
                                            </span>
                                            <button class="save-btn" data-metric="<?= htmlspecialchars($name) ?>" data-month="<?= $month_data['month_name'] ?>">
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

<script>
    // Define variables needed by the metric-editor.js script
    const metricId = <?= json_encode($metric_id) ?>;
    let tableName = <?= json_encode($table_name) ?>;
    let showPhpMessages = false; // Flag to prevent duplicate messages
    
    // Override the addColumn function to handle dynamic updates
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
        setupMetricValueListeners();
        setupMetricNameListeners();
        setupButtonHandlers();
        makeMetricCellsClickable();

        // Save the new column to the database in the background
        fetch('update_metric.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                column_title: newMetricName,
                new_name: newMetricName,
                metric_id: metricId,
                table_name: tableName
            })
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed to save new column');
            showToast('New column added successfully', 'success');
        })
        .catch(error => {
            showToast('Error saving new column: ' + error.message, 'danger');
        });
    }

    // Override the done button handler
    document.getElementById('doneBtn').addEventListener('click', function() {
        window.location.href = 'submit_metrics.php';
    });

    // Override the add column button handler
    document.getElementById('addColumnBtn').addEventListener('click', handleAddColumn);

    // Set up event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Set up setAllUnits button handler
        const setAllUnitsBtn = document.getElementById('setAllUnitsBtn');
        if (setAllUnitsBtn) {
            setAllUnitsBtn.addEventListener('click', handleSetAllUnits);
        }
        
        // Other event listeners...
    });
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
