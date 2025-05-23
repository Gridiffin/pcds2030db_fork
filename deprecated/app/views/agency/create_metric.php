<?php
ob_start(); // Start output buffering to prevent headers already sent issues
/**
 * Create Sector Outcomes
 * 
 * Interface for agency users to create sector-specific outcomes
 */

// Include necessary files
require_once ROOT_PATH . 'app/config/config.php';
require_once ROOT_PATH . 'app/lib/$((includes/db_connect.php -replace 'includes/', ''))';
require_once ROOT_PATH . 'app/lib/$((includes/session.php -replace 'includes/', ''))';
require_once ROOT_PATH . 'app/lib/$((includes/functions.php -replace 'includes/', ''))';
require_once ROOT_PATH . 'app/lib/$((includes/agencies/index.php -replace 'includes/', ''))';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
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
$pageTitle = 'Create Sector Outcomes';

// Handle form submission for new metrics
$message = '';
$message_type = '';

// Handle AJAX requests for column deletion or updating units
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    // Clear any output buffer before sending JSON response
    ob_clean();
    header('Content-Type: application/json');
    
    $data = json_decode(file_get_contents('php://input'), true);
    $metric_id = isset($data['metric_id']) ? intval($data['metric_id']) : 0;
    $column_title = $data['column_title'] ?? '';
    $action = $data['action'] ?? '';
    $unit = $data['unit'] ?? '';

    if ($action === 'delete_column' && !empty($column_title) && $metric_id > 0) {
        try {
            // Get existing data from data_json column
            $select_query = "SELECT data_json FROM sector_outcomes_data WHERE metric_id = ? AND sector_id = ? LIMIT 1";
            $stmt = $conn->prepare($select_query);
            $stmt->bind_param("ii", $metric_id, $sector_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $metrics_data = json_decode($row['data_json'], true);
                
                // If no valid JSON data exists, initialize the structure
                if (empty($metrics_data) || !is_array($metrics_data)) {
                    echo json_encode(['success' => false, 'error' => 'No valid outcome data found']);
                    exit;
                }
                
                // Remove the column from columns array
                if (isset($metrics_data['columns']) && is_array($metrics_data['columns'])) {
                    $index = array_search($column_title, $metrics_data['columns']);
                    if ($index !== false) {
                        array_splice($metrics_data['columns'], $index, 1);
                    }
                }
                
                // Remove the column from units if it exists
                if (isset($metrics_data['units'][$column_title])) {
                    unset($metrics_data['units'][$column_title]);
                }
                
                // Remove this column from all months' data
                foreach ($metrics_data['data'] as $month => &$values) {
                    if (isset($values[$column_title])) {
                        unset($values[$column_title]);
                    }
                }
                
                // Save updated data back to database
                $json_data = json_encode($metrics_data);
                $update_query = "UPDATE sector_outcomes_data SET data_json = ? WHERE metric_id = ? AND sector_id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("sii", $json_data, $metric_id, $sector_id);
                
                if ($update_stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Column deleted successfully']);
                    exit;
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to update data: ' . $conn->error]);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Metric data not found']);
                exit;
            }
        } catch (Exception $e) {
            error_log('Error deleting column: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error processing request: ' . $e->getMessage()]);
            exit;
        }
    } else if ($action === 'update_unit' && !empty($column_title)) {
        try {
            // Get existing data from data_json column
            $select_query = "SELECT data_json FROM sector_outcomes_data WHERE metric_id = ? AND sector_id = ? LIMIT 1";
            $stmt = $conn->prepare($select_query);
            $stmt->bind_param("ii", $metric_id, $sector_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $metrics_data = json_decode($row['data_json'], true);
                
                // Initialize units array if it doesn't exist
                if (!isset($metrics_data['units'])) {
                    $metrics_data['units'] = [];
                }
                
                // Set the unit for the specified column
                $metrics_data['units'][$column_title] = $unit;
                
                // Save updated data back to database
                $json_data = json_encode($metrics_data);
                $update_query = "UPDATE sector_outcomes_data SET data_json = ? WHERE metric_id = ? AND sector_id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("sii", $json_data, $metric_id, $sector_id);
                
                if ($update_stmt->execute()) {
                    echo json_encode(['success' => true, 'message' => 'Unit updated successfully']);
                    exit;
                } else {
                    echo json_encode(['success' => false, 'error' => 'Failed to update unit: ' . $conn->error]);
                    exit;
                }
            } else {
                echo json_encode(['success' => false, 'error' => 'Metric data not found']);
                exit;
            }
        } catch (Exception $e) {
            error_log('Error updating unit: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Error processing request: ' . $e->getMessage()]);
            exit;
        }
    } else {
        // Invalid or missing parameters
        echo json_encode(['success' => false, 'error' => 'Invalid request parameters']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    $metric_id = isset($_GET['next_metric_id']) ? intval($_GET['next_metric_id']) : 0;
    $_SESSION['metric_id'] = $metric_id;

    // Handle AJAX request to save individual metric value
        if (isset($_POST['action']) && $_POST['action'] === 'save_metric_value') {
            $metric_id = intval($_POST['metric_id'] ?? 0);
            $sector_id = $conn->real_escape_string($_POST['sector_id'] ?? '');
            $column_title = $conn->real_escape_string($_POST['column_title'] ?? '');
            $month = $conn->real_escape_string($_POST['month'] ?? '');
            $table_content = floatval($_POST['table_content'] ?? 0);

            if ($metric_id > 0 && $sector_id !== '' && $column_title !== '' && $month !== '') {
                // Get existing data_json for this metric and sector
                $select_query = "SELECT data_json FROM sector_outcomes_data WHERE metric_id = ? AND sector_id = ? LIMIT 1";
                $stmt = $conn->prepare($select_query);
                $stmt->bind_param("ii", $metric_id, $sector_id);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($row = $result->fetch_assoc()) {
                    $metrics_data = json_decode($row['data_json'], true);

                    if (!is_array($metrics_data) || !isset($metrics_data['data'])) {
                        $metrics_data = [
                            'columns' => [],
                            'units' => [],
                            'data' => []
                        ];
                    }

                    // Initialize month data if not set
                    if (!isset($metrics_data['data'][$month])) {
                        $metrics_data['data'][$month] = [];
                    }

                    // Update the value for the column and month
                    $metrics_data['data'][$month][$column_title] = $table_content;

                    // Ensure column is in columns list
                    if (!in_array($column_title, $metrics_data['columns'])) {
                        $metrics_data['columns'][] = $column_title;
                    }

                    // Encode JSON and update database
                    $json_data = json_encode($metrics_data);
                    $update_query = "UPDATE sector_outcomes_data SET data_json = ? WHERE metric_id = ? AND sector_id = ?";
                    $update_stmt = $conn->prepare($update_query);
                    $update_stmt->bind_param("sii", $json_data, $metric_id, $sector_id);

                    if ($update_stmt->execute()) {
                        ob_clean();
                        echo json_encode(['success' => true, 'message' => 'Metric value saved successfully']);
                    } else {
                        ob_clean();
                        echo json_encode(['success' => false, 'error' => 'Error updating metric value: ' . $conn->error]);
                    }
                } else {
                    // No existing row, insert new with initial JSON structure
                    $metrics_data = [
                        'columns' => [$column_title],
                        'units' => [],
                        'data' => [
                            $month => [
                                $column_title => $table_content
                            ]
                        ]
                    ];
                    $json_data = json_encode($metrics_data);
                    $table_name_post = $conn->real_escape_string($_POST['table_name'] ?? '');
                    if (empty($table_name_post)) {
                        $table_name_post = "Table_" . $metric_id;
                    }
                    $insert_query = "INSERT INTO sector_outcomes_data (metric_id, sector_id, table_name, data_json, is_draft) VALUES (?, ?, ?, ?, 1)";
                    $insert_stmt = $conn->prepare($insert_query);
                    $insert_stmt->bind_param("iiss", $metric_id, $sector_id, $table_name_post, $json_data);

                    if ($insert_stmt->execute()) {
                        ob_clean();
                        echo json_encode(['success' => true, 'message' => 'Metric value inserted successfully']);
                    } else {
                        ob_clean();
                        echo json_encode(['success' => false, 'error' => 'Error inserting metric value: ' . $conn->error]);
                    }
                }
            } else {
                ob_clean();
                echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
            }
            exit;
        }

    if (isset($_POST['table_name']) && trim($_POST['table_name']) !== '') {
        $new_table_name = $conn->real_escape_string($_POST['table_name']);
        // Check if a row exists for this metric_id and sector_id
        $check_query = "SELECT 1 FROM sector_outcomes_data WHERE metric_id = $metric_id AND sector_id = '$sector_id' LIMIT 1";
        $check_result = $conn->query($check_query);
        if ($check_result && $check_result->num_rows > 0) {
            // Update existing row
            $update_query = "UPDATE sector_outcomes_data SET table_name = '$new_table_name' WHERE sector_id = '$sector_id' AND metric_id = $metric_id";
            if ($conn->query($update_query) === TRUE) {
                $message = "Table name updated successfully.";
                $message_type = "success";
            } else {
                $message = "Error updating table name: " . $conn->error;
                $message_type = "danger";
            }
        } else {
            // Insert new row with table_name
            $insert_table_name_query = "INSERT INTO sector_outcomes_data (metric_id, table_name, column_title, table_content, month, sector_id) 
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
        $query = "INSERT INTO sector_outcomes_data (metric_id, table_name, column_title, table_content, month, sector_id) 
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
    $result = $conn->query("SELECT MAX(metric_id) AS max_id FROM sector_outcomes_data");
    if ($result && $row = $result->fetch_assoc()) {
        $metric_id = $row['max_id'] + 1;
    }
}
$select_query = "SELECT * FROM sector_outcomes_data WHERE metric_id = $metric_id";
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
$result = $conn->query("SELECT table_name FROM sector_outcomes_data WHERE metric_id = $metric_id AND sector_id = $sector_id LIMIT 1");
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
$title = "Create Sector Outcomes";
$subtitle = "Define and manage your sector-specific outcomes";
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
require_once ROOT_PATH . 'app/lib/$((includes/dashboard_header.php -replace 'includes/', ''))';
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

    <!-- Units modal -->
    <div class="modal fade" id="unitsModal" tabindex="-1" aria-labelledby="unitsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="unitsModalLabel">Set Unit of Measurement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="unitInput" class="form-label">Unit</label>
                        <input type="text" class="form-control" id="unitInput" placeholder="e.g., kg, ha, $, %, etc.">
                    </div>
                    <p class="text-muted small">Enter the unit of measurement for this outcome.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="saveUnitBtn">Save</button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">        <div class="card-header bg-primary text-white">
            <h5 class="card-title m-0">
                <i class="fas fa-table me-2"></i>Outcome Table Definition
            </h5>
        </div>
        <div class="card-body">
            <form id="tableNameForm" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label for="tableNameInput" class="form-label">Table Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-signature"></i></span>                            <input type="text" class="form-control" id="tableNameInput" 
                                   placeholder="Enter a descriptive name for this outcome table" 
                                   value="<?= htmlspecialchars($table_name) ?>" required />
                            <button type="button" class="btn btn-primary" id="saveTableNameBtn">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                        <div class="form-text">Provide a clear, descriptive name for your outcome table</div>
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
                                            <span class="metric-unit-display" data-metric="<?= htmlspecialchars($name) ?>"></span>
                                        </div>
                                        <div class="metric-actions">
                                            <button class="unit-btn" data-metric="<?= htmlspecialchars($name) ?>" 
                                                    data-current-unit="">
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
    // Remove metric-editor.js to prevent conflicts
    // APP_URL . '/assets/js/metric-editor.js'
];
?>

<!-- Add custom CSS for unit display -->
<style>
    .metric-unit-display {
        display: inline-block;
        margin-left: 4px;
        color: #6c757d;
        font-size: 0.85em;
        font-style: italic;
    }
    
    /* Hide initially until populated */
    .metric-unit-display:empty {
        display: none;
    }
</style>

<script>
    // Define variables needed by the metric-editor.js script
    const metricId = <?= json_encode($metric_id) ?>;
    const sectorId = <?= json_encode($sector_id) ?>;
    let tableName = <?= json_encode($table_name) ?>;
    let showPhpMessages = false; // Flag to prevent duplicate messages
    let currentMetricForUnit = ''; // Track which metric we're setting units for
    
    // Store all metrics and their units
    const metricUnits = {};
    
    // Document ready handler
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize all event handlers
        setupEventHandlers();
    });
    
        // Set up all event handlers
        function setupEventHandlers() {
            // Set up handlers for metric value cells
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
            
            // Set up handlers for metric name cells
            document.querySelectorAll('.metric-name').forEach(cell => {
                cell.addEventListener('input', function() {
                    const btn = this.closest('.metric-header').querySelector('.save-btn');
                    if (btn) btn.style.display = 'inline-block';
                });
            });
            
            // Set up unit buttons
            document.querySelectorAll('.unit-btn').forEach(button => {
                button.addEventListener('click', function() {
                    // Store the metric name for the modal
                    currentMetricForUnit = this.dataset.metric;
                    
                    // Get current unit if any
                    const currentUnit = this.dataset.currentUnit || '';
                    
                    // Set the input value in the modal
                    document.getElementById('unitInput').value = currentUnit;
                    
                    // Show the modal
                    const unitsModal = new bootstrap.Modal(document.getElementById('unitsModal'));
                    unitsModal.show();
                });
            });
            
            // Save unit button in modal
            document.getElementById('saveUnitBtn').addEventListener('click', function() {
                const unitInput = document.getElementById('unitInput');
                const newUnit = unitInput.value.trim();
                
                // Save the unit value
                if (currentMetricForUnit) {
                    // Save unit to database via AJAX
                    fetch('', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            action: 'update_unit',
                            column_title: currentMetricForUnit,
                            metric_id: metricId,
                            unit: newUnit
                        })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.text().then(text => {
                                throw new Error(`Server responded with status ${response.status}: ${text}`);
                            });
                        }
                        return response.text().then(text => {
                            if (!text) return {};
                            try {
                                return JSON.parse(text);
                            } catch (e) {
                                console.error('Failed to parse JSON:', text);
                                throw new Error('Invalid JSON response from server');
                            }
                        });
                    })
                    .then(data => {
                        // Store in our object
                        metricUnits[currentMetricForUnit] = newUnit;
                        
                        // Update the display
                        updateUnitDisplay(currentMetricForUnit, newUnit);
                        
                        // Update the data attribute on the button
                        const unitBtn = document.querySelector(`.unit-btn[data-metric="${currentMetricForUnit}"]`);
                        if (unitBtn) {
                            unitBtn.dataset.currentUnit = newUnit;
                        }
                        
                        // Show confirmation
                        showToast(`Unit for "${currentMetricForUnit}" set to "${newUnit}"`, 'success');
                        
                        // Close the modal
                        bootstrap.Modal.getInstance(document.getElementById('unitsModal')).hide();
                    })
                    .catch(error => {
                        console.error('Unit update error:', error);
                        showToast('Error updating unit: ' + error.message, 'danger');
                    });
                }
            });
            
            // Add click event handler for metric value save buttons
            document.querySelectorAll('.metric-cell .save-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const metric = this.dataset.metric;
                    const month = this.dataset.month;
                    const valueSpan = this.parentElement.querySelector('.metric-value');
                    if (!metric || !month || !valueSpan) return;
                    
                    let value = valueSpan.textContent.trim();
                    if (value === '') value = '0';
                    if (isNaN(parseFloat(value))) {
                        showToast('Invalid numeric value', 'warning');
                        return;
                    }
                    value = parseFloat(value).toFixed(2);
                    
                    // Disable button during save
                    this.disabled = true;
                    
                    // Prepare data to send
                    const formData = new FormData();
                    formData.append('metric_id', metricId);
                    formData.append('sector_id', sectorId);
                    formData.append('column_title', metric);
                    formData.append('month', month);
                    formData.append('table_content', value);
                    formData.append('action', 'save_metric_value');
                    
                    fetch('', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Failed to save metric value');
                        return response.json();
                    })
                    .then(data => {
                        if (data.success) {
                            showToast('Metric value saved', 'success');
                            valueSpan.textContent = value;
                        } else {
                            showToast(data.error || 'Failed to save metric value', 'danger');
                        }
                    })
                    .catch(error => {
                        showToast('Error saving metric value: ' + error.message, 'danger');
                    })
                    .finally(() => {
                        this.disabled = false;
                        this.style.display = 'none';
                    });
                });
            });
            
            // Function to handle adding a new column
            document.getElementById('addColumnBtn').addEventListener('click', handleAddColumn);
            document.getElementById('saveTableNameBtn').addEventListener('click', handleSaveTableName);
            document.getElementById('doneBtn').addEventListener('click', () => {
                window.location.href = 'submit_metrics.php';
            });
            
            // Set up handler for the "Set All Units" button
            document.getElementById('setAllUnitsBtn').addEventListener('click', handleSetAllUnits);
            
            // Set up delete column buttons - this will override any conflicts
            document.querySelectorAll('.delete-column-btn').forEach(button => {
                // Remove any existing listeners first to avoid duplicates
                button.replaceWith(button.cloneNode(true));
            });
            
            // Re-add event listeners to the fresh elements
            document.querySelectorAll('.delete-column-btn').forEach(button => {
                button.addEventListener('click', handleDeleteColumn);
            });
        }
    
    // Function to update the unit display for a metric
    function updateUnitDisplay(metricName, unit) {
        const unitDisplay = document.querySelector(`.metric-unit-display[data-metric="${metricName}"]`);
        if (unitDisplay) {
            unitDisplay.textContent = unit ? `(${unit})` : '';
        }
    }
    
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
                <div class="metric-title">
                    <span class="metric-name" contenteditable="true" data-metric="${newMetricName}">
                        ${newMetricName}
                    </span>
                    <span class="metric-unit-display" data-metric="${newMetricName}"></span>
                </div>
                <div class="metric-actions">
                    <button class="unit-btn" data-metric="${newMetricName}" data-current-unit="">
                        <i class="fas fa-ruler"></i>
                    </button>
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
        setupEventHandlers();

        // Get all existing columns from the UI
        const allMetricNames = [];
        document.querySelectorAll('.metric-name').forEach(el => {
            const metric = el.dataset.metric;
            if (metric && !allMetricNames.includes(metric)) {
                allMetricNames.push(metric);
            }
        });

        // Check if we need to create or update the data_json structure
        fetch(`../../api/check_metric.php?metric_id=${metricId}&sector_id=${sectorId}`, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data.exists) {
                // Get the existing data first, then update it
                fetch(`../../api/get_metric_data.php?metric_id=${metricId}&sector_id=${sectorId}`, {
                    method: 'GET'
                })
                .then(response => response.json())
                .then(metricData => {
                    if (metricData.success) {
                        const existingData = metricData.data;
                        
                        // Add the new column to existingData.columns if it's not already there
                        if (!existingData.columns.includes(newMetricName)) {
                            existingData.columns.push(newMetricName);
                        }
                        
                        // Ensure units object exists
                        if (!existingData.units) {
                            existingData.units = {};
                        }
                        
                        // Add empty unit for new column
                        existingData.units[newMetricName] = "";
                        
                        // Add the column to all months
                        Object.keys(existingData.data).forEach(month => {
                            if (!existingData.data[month][newMetricName]) {
                                existingData.data[month][newMetricName] = 0;
                            }
                        });
                        
                        // Save the updated data
                        return fetch('../../api/save_metric_json.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                metric_id: metricId,
                                sector_id: sectorId,
                                table_name: tableName || `Table_${metricId}`,
                                data_json: existingData
                            })
                        });
                    } else {
                        throw new Error(metricData.error || 'Failed to get existing metric data');
                    }
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showToast('Column added successfully', 'success');
                    } else {
                        throw new Error(result.error || 'Failed to save column data');
                    }
                });
            } else {
                // Create initial JSON structure with proper format
                const initialData = {
                    columns: allMetricNames, // Include ALL column names, not just the new one
                    data: {},
                    units: {}
                };
                
                // Initialize units for all columns
                allMetricNames.forEach(metric => {
                    initialData.units[metric] = "";
                });
                
                // Add empty data for all months and columns
                ['January', 'February', 'March', 'April', 'May', 'June', 
                'July', 'August', 'September', 'October', 'November', 'December']
                .forEach(month => {
                    initialData.data[month] = {};
                    allMetricNames.forEach(metric => {
                        initialData.data[month][metric] = 0;
                    });
                });
                
                return fetch('../../api/save_metric_json.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        metric_id: metricId,
                        sector_id: sectorId,
                        table_name: tableName || `Table_${metricId}`,
                        data_json: initialData
                    })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        showToast('New column added successfully', 'success');
                    } else {
                        throw new Error(result.error || 'Failed to save column data');
                    }
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
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
        
        console.log('Deleting outcome column:', metric); // Debug info
        
        // Delete directly from the current page instead of from update_metric.php
        fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json' // Explicitly accept JSON response
            },
            body: JSON.stringify({
                action: 'delete_column',
                column_title: metric,
                metric_id: metricId,
                table_name: tableName
            })
        })
        .then(response => {
            // Check if the response is ok (status 200-299)
            if (!response.ok) {
                // If not ok, try to get text response for debugging
                return response.text().then(text => {
                    throw new Error(`Server responded with status ${response.status}: ${text}`);
                });
            }
            // Try to parse the response as JSON
            return response.text().then(text => {
                if (!text) return {};
                try {
                    return JSON.parse(text);
                } catch (e) {
                    console.error('Failed to parse JSON:', text);
                    throw new Error('Invalid JSON response from server');
                }
            });
        })
        .then(data => {
            console.log('Delete response:', data); // Debug info
            
            if (data.success) {
                // Remove the column from the UI
                const columnIndex = findColumnIndex(metric);
                if (columnIndex !== -1) {
                    removeColumnFromTable(columnIndex);
                    showToast(data.message || `Column "${metric}" deleted successfully`, 'success');
                    
                    // Also remove from units object
                    delete metricUnits[metric];
                } else {
                    showToast(data.message || 'Column deleted, but UI update failed.', 'warning');
                }
            } else {
                // Show specific error from server if available
                showToast(data.error || 'Failed to delete column.', 'danger');
            }
        })
        .catch(error => {
            console.error('Delete error:', error); // Log the full error
            // Display a user-friendly message, potentially including details from the error
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
    
    // Function to handle setting units for all columns
    function handleSetAllUnits() {
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
                showToast('No metrics found to update', 'warning');
                return;
            }
            
            // Update all unit displays and store in metricUnits object
            // Also send AJAX requests to save units to backend
            const saveUnitPromises = metricNames.map(metric => {
                updateUnitDisplay(metric, newUnit);
                metricUnits[metric] = newUnit;
                
                // Also update data attributes on buttons
                const unitBtn = document.querySelector(`.unit-btn[data-metric="${metric}"]`);
                if (unitBtn) {
                    unitBtn.dataset.currentUnit = newUnit;
                }
                
                // Send AJAX request to save unit
                return fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: 'update_unit',
                        column_title: metric,
                        metric_id: metricId,
                        unit: newUnit
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`Server responded with status ${response.status}: ${text}`);
                        });
                    }
                    return response.text().then(text => {
                        if (!text) return {};
                        try {
                            return JSON.parse(text);
                        } catch (e) {
                            console.error('Failed to parse JSON:', text);
                            throw new Error('Invalid JSON response from server');
                        }
                    });
                });
            });
            
            Promise.all(saveUnitPromises)
                .then(results => {
                    showToast(`Units updated and saved for all ${metricNames.length} columns`, 'success');
                })
                .catch(error => {
                    console.error('Error saving units:', error);
                    showToast('Error saving units: ' + error.message, 'danger');
                });
        } catch (error) {
            showToast('Error updating units: ' + error.message, 'danger');
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
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>

