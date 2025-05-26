<?php
ob_start(); // Start output buffering to prevent headers already sent issues
/**
 * Create Sector Outcomes
 * 
 * Interface for agency users to create sector-specific outcomes
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';

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

// After the PHP initialization code, handle the AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'error' => ''];

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'save_metric_value':
            try {
                $metric_id = intval($_POST['metric_id'] ?? 0);
                $sector_id = $conn->real_escape_string($_POST['sector_id'] ?? '');
                $column_title = $conn->real_escape_string($_POST['column_title'] ?? '');
                $month = $conn->real_escape_string($_POST['month'] ?? '');
                $value = floatval($_POST['table_content'] ?? 0);

                // Get existing data_json
                $select_query = "SELECT data_json FROM sector_outcomes_data 
                               WHERE metric_id = ? AND sector_id = ? LIMIT 1";
                $stmt = $conn->prepare($select_query);
                $stmt->bind_param("ii", $metric_id, $sector_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                if ($row = $result->fetch_assoc()) {
                    $metrics_data = json_decode($row['data_json'], true) ?: [
                        'columns' => [],
                        'units' => [],
                        'data' => []
                    ];
                    
                    // Ensure column exists
                    if (!in_array($column_title, $metrics_data['columns'])) {
                        $metrics_data['columns'][] = $column_title;
                    }
                    
                    // Update value
                    if (!isset($metrics_data['data'][$month])) {
                        $metrics_data['data'][$month] = [];
                    }
                    $metrics_data['data'][$month][$column_title] = $value;
                    
                    // Save updated data
                    $update_query = "UPDATE sector_outcomes_data 
                                   SET data_json = ? 
                                   WHERE metric_id = ? AND sector_id = ?";
                    $stmt = $conn->prepare($update_query);
                    $json_data = json_encode($metrics_data);
                    $stmt->bind_param("sii", $json_data, $metric_id, $sector_id);
                    
                    if ($stmt->execute()) {
                        $response['success'] = true;
                    } else {
                        throw new Exception("Failed to update data");
                    }
                } else {
                    // No existing row, insert new with initial JSON structure
                    $metrics_data = [
                        'columns' => [$column_title],
                        'units' => [],
                        'data' => [
                            $month => [
                                $column_title => $value
                            ]
                        ]
                    ];
                    $json_data = json_encode($metrics_data);
                    $table_name_post = "Table_" . $metric_id;
                    $insert_query = "INSERT INTO sector_outcomes_data (metric_id, sector_id, table_name, data_json, is_draft) VALUES (?, ?, ?, ?, 1)";
                    $insert_stmt = $conn->prepare($insert_query);
                    $insert_stmt->bind_param("iiss", $metric_id, $sector_id, $table_name_post, $json_data);

                    if ($insert_stmt->execute()) {
                        $response['success'] = true;
                    } else {
                        throw new Exception("Failed to insert data: " . $conn->error);
                    }
                }
            } catch (Exception $e) {
                $response['error'] = $e->getMessage();
            }
            break;
                
            case 'update_unit':
                try {
                    $data = json_decode(file_get_contents('php://input'), true);
                    $metric_id = intval($data['metric_id'] ?? 0);
                    $column_title = $conn->real_escape_string($data['column_title'] ?? '');
                    $unit = $conn->real_escape_string($data['unit'] ?? '');
                    
                    if ($metric_id && $column_title) {
                        $select_query = "SELECT data_json FROM sector_outcomes_data 
                                       WHERE metric_id = ? AND sector_id = ? LIMIT 1";
                        $stmt = $conn->prepare($select_query);
                        $stmt->bind_param("ii", $metric_id, $sector_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        
                        if ($row = $result->fetch_assoc()) {
                            $metrics_data = json_decode($row['data_json'], true) ?: [
                                'columns' => [],
                                'units' => [],
                                'data' => []
                            ];
                            
                            // Update unit
                            $metrics_data['units'][$column_title] = $unit;
                            
                            // Save updated data
                            $update_query = "UPDATE sector_outcomes_data 
                                           SET data_json = ? 
                                           WHERE metric_id = ? AND sector_id = ?";
                            $stmt = $conn->prepare($update_query);
                            $json_data = json_encode($metrics_data);
                            $stmt->bind_param("sii", $json_data, $metric_id, $sector_id);
                            
                            if ($stmt->execute()) {
                                $response['success'] = true;
                            } else {
                                throw new Exception("Failed to update unit");
                            }
                        } else {
                            throw new Exception("Metric data not found");
                        }
                    } else {
                        throw new Exception("Invalid parameters");
                    }
                } catch (Exception $e) {
                    $response['error'] = $e->getMessage();
                }
                break;
        }
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
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
        }    if (isset($_POST['table_name']) && trim($_POST['table_name']) !== '') {
        $new_table_name = $conn->real_escape_string($_POST['table_name']);
        $metric_id = intval($_POST['metric_id'] ?? 0);
        $sector_id = intval($_POST['sector_id'] ?? 0);

        // For AJAX requests, we'll return JSON
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        // Initialize empty data structure for new metrics
        $metrics_data = [
            'columns' => [],
            'units' => [],
            'data' => []
        ];
        
        // Initialize all months
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 
                  'July', 'August', 'September', 'October', 'November', 'December'];
        foreach ($months as $month) {
            $metrics_data['data'][$month] = [];
        }
        
        // Convert to JSON
        $json_data = json_encode($metrics_data);
        
        // Check if a row exists for this metric_id and sector_id
        $check_query = "SELECT data_json FROM sector_outcomes_data WHERE metric_id = ? AND sector_id = ? LIMIT 1";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ii", $metric_id, $sector_id);
        $stmt->execute();
        $check_result = $stmt->get_result();
        
        if ($check_result && $check_result->num_rows > 0) {
            // Update existing row
            $update_query = "UPDATE sector_outcomes_data SET table_name = ? WHERE sector_id = ? AND metric_id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("sii", $new_table_name, $sector_id, $metric_id);
            
            if ($stmt->execute()) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Table name updated successfully']);
                    exit;
                }
                $message = "Table name updated successfully.";
                $message_type = "success";
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => 'Error updating table name: ' . $conn->error]);
                    exit;
                }
                $message = "Error updating table name: " . $conn->error;
                $message_type = "danger";
            }
        } else {            // Insert new row with table_name and empty data_json
            $empty_data_json = json_encode([
                'columns' => [],
                'units' => [],
                'data' => []
            ]);
            $insert_query = "INSERT INTO sector_outcomes_data (metric_id, sector_id, table_name, data_json, is_draft) 
                VALUES (?, ?, ?, ?, 1)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iiss", $metric_id, $sector_id, $new_table_name, $empty_data_json);
            
            if ($stmt->execute()) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Table name saved successfully']);
                    exit;
                }
                $message = "Table name saved successfully.";
                $message_type = "success";
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => 'Error saving table name: ' . $conn->error]);
                    exit;
                }
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
require_once PROJECT_ROOT_PATH . 'app/lib/dashboard_header.php';
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
        <div class="card-body">            <form id="tableNameForm" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label for="tableNameInput" class="form-label">Table Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-signature"></i></span>
                            <input type="text" class="form-control" id="tableNameInput" 
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

    .metric-cell {
        position: relative;
        display: flex;
        align-items: center;
    }

    .metric-value {
        flex: 1;
        padding: 0.375rem;
        text-align: right;
    }

    .save-btn {
        display: none;
        padding: 0.25rem;
        margin-left: 0.5rem;
        border: none;
        background: none;
        color: #28a745;
    }

    .save-btn:disabled {
        opacity: 0.5;
    }

    .month-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        background-color: #e9ecef;
        font-size: 0.875rem;
    }

    .total-badge {
        display: inline-block;
        padding: 0.25rem 0.5rem;
        border-radius: 0.25rem;
        background-color: #f8f9fa;
        font-weight: bold;
    }

    .metric-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 0.5rem;
    }

    .metric-title {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .metric-actions {
        display: flex;
        gap: 0.25rem;
    }

    .unit-btn {
        border: none;
        background: none;
        color: #6c757d;
        padding: 0.25rem;
    }

    .delete-column-btn {
        border: none;
        background: none;
        color: #dc3545;
        padding: 0.25rem;
    }
</style>

<script>
    const metricId = <?= json_encode($metric_id) ?>;
    const sectorId = <?= json_encode($sector_id) ?>;
    let tableName = <?= json_encode($table_name) ?>;
    let currentMetricForUnit = '';
    const metricUnits = <?= json_encode($metrics_data['units'] ?? []) ?>;

    document.addEventListener('DOMContentLoaded', function() {
        setupEventHandlers();
        makeMetricCellsClickable();

        // Save table name button handler
        document.getElementById('saveTableNameBtn').addEventListener('click', async function() {
            const tableNameInput = document.getElementById('tableNameInput');
            const newTableName = tableNameInput.value.trim();
            
            if (!newTableName) {
                showToast('Table name cannot be empty', 'warning');
                return;
            }

            // Prepare form data
            const formData = new FormData();
            formData.append('table_name', newTableName);
            formData.append('metric_id', '<?= $metric_id ?>');
            formData.append('sector_id', '<?= $sector_id ?>');

            try {
                const response = await fetch('<?= $_SERVER['PHP_SELF'] ?>?next_metric_id=<?= $metric_id ?>', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.text();
                if (response.ok) {
                    showToast('Table name saved successfully', 'success');
                } else {
                    throw new Error('Failed to save table name');
                }
            } catch (error) {
                showToast('Error saving table name: ' + error.message, 'danger');
            }
        });
    });

    function setupEventHandlers() {
        // Set up metric value handlers
        document.querySelectorAll('.metric-value').forEach(cell => {
            cell.addEventListener('input', function() {
                const btn = this.parentElement.querySelector('.save-btn');
                if (btn) btn.style.display = 'inline-block';
            });
            
            cell.addEventListener('blur', function() {
                if (!isNaN(parseFloat(this.textContent))) {
                    this.textContent = parseFloat(this.textContent).toFixed(2);
                }
            });
        });

        // Set up metric name handlers
        document.querySelectorAll('.metric-name').forEach(cell => {
            cell.addEventListener('input', function() {
                const btn = this.closest('.metric-header').querySelector('.save-btn');
                if (btn) btn.style.display = 'inline-block';
            });
        });

        // Set up unit buttons
        document.querySelectorAll('.unit-btn').forEach(button => {
            button.addEventListener('click', function() {
                currentMetricForUnit = this.dataset.metric;
                document.getElementById('unitInput').value = this.dataset.currentUnit || '';
                const unitsModal = new bootstrap.Modal(document.getElementById('unitsModal'));
                unitsModal.show();
            });
        });

        // Set up save value buttons
        document.querySelectorAll('.metric-cell .save-btn').forEach(button => {
            button.addEventListener('click', async function() {
                const valueSpan = this.parentElement.querySelector('.metric-value');
                const metric = valueSpan.dataset.metric;
                const month = valueSpan.dataset.month;
                let value = valueSpan.textContent.trim();
                
                if (value === '') value = '0';
                if (isNaN(parseFloat(value))) {
                    showToast('Invalid numeric value', 'warning');
                    return;
                }
                value = parseFloat(value).toFixed(2);
                
                this.disabled = true;
                
                try {
                    const formData = new FormData();
                    formData.append('action', 'save_metric_value');
                    formData.append('metric_id', metricId);
                    formData.append('sector_id', sectorId);
                    formData.append('column_title', metric);
                    formData.append('month', month);
                    formData.append('table_content', value);
                    
                    const response = await fetch('', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        showToast('Value saved successfully', 'success');
                        valueSpan.textContent = value;
                        updateTotal(metric);
                    } else {
                        throw new Error(data.error || 'Failed to save value');
                    }
                } catch (error) {
                    showToast('Error: ' + error.message, 'danger');
                } finally {
                    this.disabled = false;
                    this.style.display = 'none';
                }
            });
        });

        // Save unit button
        document.getElementById('saveUnitBtn').addEventListener('click', async function() {
            const unitInput = document.getElementById('unitInput');
            const newUnit = unitInput.value.trim();
            
            try {
                const response = await fetch('', {
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
                });
                
                const data = await response.json();
                if (data.success) {
                    updateUnitDisplay(currentMetricForUnit, newUnit);
                    metricUnits[currentMetricForUnit] = newUnit;
                    showToast('Unit updated successfully', 'success');
                    bootstrap.Modal.getInstance(document.getElementById('unitsModal')).hide();
                } else {
                    throw new Error(data.error || 'Failed to update unit');
                }
            } catch (error) {
                showToast('Error: ' + error.message, 'danger');
            }
        });

        // Add Column button
        document.getElementById('addColumnBtn').addEventListener('click', function() {
            const headers = document.querySelector('.metrics-table thead tr');
            const newColumnName = prompt('Enter name for the new column:');
            
            if (!newColumnName?.trim()) return;
            
            // Add header
            const th = document.createElement('th');
            th.innerHTML = `
                <div class="metric-header">
                    <div class="metric-title">
                        <span class="metric-name" contenteditable="true" data-metric="${newColumnName}">
                            ${newColumnName}
                        </span>
                        <span class="metric-unit-display"></span>
                    </div>
                    <div class="metric-actions">
                        <button class="unit-btn" data-metric="${newColumnName}" data-current-unit="">
                            <i class="fas fa-ruler"></i>
                        </button>
                        <button class="save-btn" data-metric="${newColumnName}">
                            <i class="fas fa-check"></i>
                        </button>
                        <button class="delete-column-btn" data-metric="${newColumnName}">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </div>
                </div>
            `;
            headers.appendChild(th);

            // Add cells for all months
            document.querySelectorAll('.metrics-table tbody tr:not(.table-light)').forEach(row => {
                const td = document.createElement('td');
                const month = row.querySelector('.month-badge').textContent;
                td.innerHTML = `
                    <div class="metric-cell">
                        <span class="metric-value" contenteditable="true" 
                              data-metric="${newColumnName}" 
                              data-month="${month}">0.00</span>
                        <button class="save-btn" data-metric="${newColumnName}" data-month="${month}">
                            <i class="fas fa-check"></i>
                        </button>
                    </div>
                `;
                row.appendChild(td);
            });

            // Add total cell
            const totalRow = document.querySelector('.metrics-table tbody tr.table-light');
            if (totalRow) {
                const td = document.createElement('td');
                td.className = 'fw-bold text-end';
                td.textContent = '0.00';
                totalRow.appendChild(td);
            }

            setupEventHandlers();
            makeMetricCellsClickable();
        });

        // Done button
        document.getElementById('doneBtn').addEventListener('click', () => {
            window.location.href = 'submit_metrics.php';
        });
    }

    function makeMetricCellsClickable() {
        document.querySelectorAll('.metric-value').forEach(cell => {
            cell.addEventListener('click', function() {
                this.focus();
            });
        });
    }

    function updateUnitDisplay(metric, unit) {
        const unitDisplay = document.querySelector(`.metric-unit-display[data-metric="${metric}"]`);
        if (unitDisplay) {
            unitDisplay.textContent = unit ? `(${unit})` : '';
        }
        const unitBtn = document.querySelector(`.unit-btn[data-metric="${metric}"]`);
        if (unitBtn) {
            unitBtn.dataset.currentUnit = unit;
        }
    }

    function updateTotal(metric) {
        const cells = document.querySelectorAll(`.metric-value[data-metric="${metric}"]`);
        let total = 0;
        cells.forEach(cell => {
            total += parseFloat(cell.textContent) || 0;
        });
        
        const totalRow = document.querySelector('.metrics-table tbody tr.table-light');
        if (totalRow) {
            const totalCells = totalRow.querySelectorAll('td');
            const metricIndex = Array.from(document.querySelectorAll('.metric-name')).findIndex(el => el.dataset.metric === metric);
            if (metricIndex !== -1 && totalCells[metricIndex + 1]) {
                totalCells[metricIndex + 1].textContent = total.toFixed(2);
            }
        }
    }

    function showToast(message, type = 'info') {
        const alertContainer = document.getElementById(`${type}Container`);
        if (alertContainer) {
            alertContainer.textContent = message;
            alertContainer.style.display = 'block';
            setTimeout(() => {
                alertContainer.style.display = 'none';
            }, 5000);
        }
    }
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>


