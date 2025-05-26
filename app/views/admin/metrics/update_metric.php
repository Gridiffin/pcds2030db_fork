<?php
header('Content-Type: application/json');

session_start();

// Database connection
require_once ROOT_PATH . 'app/config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is an admin
if (!is_admin()) {
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Get input data
$data = json_decode(file_get_contents('php://input'), true);
$table_name = $data['table_name'] ?? '';
$metric_id = isset($data['metric_id']) ? intval($data['metric_id']) : null;
$action = $data['action'] ?? '';

if (!$metric_id) {
    echo json_encode(['error' => 'Metric ID not found in request']);
    exit;
}

// Get metric data - try outcome data first (new system), fallback to metric data (legacy)
$metric_data = get_outcome_data($metric_id);
if (!$metric_data) {
    $metric_data = get_metric_data($metric_id);
}
if (!$metric_data) {
    echo json_encode(['error' => 'Metric not found']);
    exit;
}

$sector_id = $metric_data['sector_id'];
$table_name = $table_name ?: $metric_data['table_name'];
$metrics_data = json_decode($metric_data['data_json'], true);

// Ensure metrics data has the expected structure
if (!isset($metrics_data['columns'])) {
    $metrics_data['columns'] = [];
}
if (!isset($metrics_data['units'])) {
    $metrics_data['units'] = [];
}
if (!isset($metrics_data['data'])) {
    $metrics_data['data'] = [];
    
    // Initialize months
    $months = ['January', 'February', 'March', 'April', 'May', 'June', 
               'July', 'August', 'September', 'October', 'November', 'December'];
    
    foreach ($months as $m) {
        $metrics_data['data'][$m] = [];
    }
}

// Process different types of updates
$old_name = $data['column_title'] ?? '';
$new_name = $data['new_name'] ?? '';
$month = $data['month'] ?? '';
$new_value = isset($data['new_value']) ? floatval($data['new_value']) : 0;
$unit = $data['unit'] ?? '';  // For unit updates

// Handle specific actions
if ($action === 'delete_column' && !empty($old_name)) {
    // Remove column from columns array
    if (in_array($old_name, $metrics_data['columns'])) {
        $index = array_search($old_name, $metrics_data['columns']);
        array_splice($metrics_data['columns'], $index, 1);
        
        // Remove unit for this column
        if (isset($metrics_data['units'][$old_name])) {
            unset($metrics_data['units'][$old_name]);
        }
        
        // Remove column data from all months
        foreach ($metrics_data['data'] as $m => $values) {
            if (isset($values[$old_name])) {
                unset($metrics_data['data'][$m][$old_name]);
            }
        }
    }
} 
// Handle add column action
else if ($action === 'add_column' && !empty($new_name)) {
    // Add new column if it doesn't exist
    if (!in_array($new_name, $metrics_data['columns'])) {
        $metrics_data['columns'][] = $new_name;
        
        // Ensure all months are properly initialized
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 
                  'July', 'August', 'September', 'October', 'November', 'December'];
        
        // Make sure the data array exists
        if (!isset($metrics_data['data']) || !is_array($metrics_data['data'])) {
            $metrics_data['data'] = [];
        }
        
        // Initialize any missing months and add new column to all months
        foreach ($months as $month) {
            if (!isset($metrics_data['data'][$month]) || !is_array($metrics_data['data'][$month])) {
                $metrics_data['data'][$month] = [];
            }
            $metrics_data['data'][$month][$new_name] = 0;  // Default value
        }
        
        // Set unit if provided
        if (!empty($unit)) {
            $metrics_data['units'][$new_name] = $unit;
        }
    }
}
// Handle metric name change
else if (!empty($new_name) && !empty($old_name) && $new_name !== $old_name) {
    // Update column name in the data structure
    if (in_array($old_name, $metrics_data['columns'])) {
        $index = array_search($old_name, $metrics_data['columns']);
        $metrics_data['columns'][$index] = $new_name;
        
        // Update unit if the name changes
        if (isset($metrics_data['units'][$old_name])) {
            $metrics_data['units'][$new_name] = $metrics_data['units'][$old_name];
            unset($metrics_data['units'][$old_name]);
        }
        
        // Update all values with this column name
        foreach ($metrics_data['data'] as $m => &$values) {
            if (isset($values[$old_name])) {
                $values[$new_name] = $values[$old_name];
                unset($values[$old_name]);
            }
        }
    }
} 
// Handle unit update only
else if (!empty($unit) && !empty($old_name)) {
    // Update unit for existing column
    $metrics_data['units'][$old_name] = $unit;
}
// Handle metric value update
else if (!empty($month) && !empty($old_name)) {
    // Ensure column exists
    if (!in_array($old_name, $metrics_data['columns'])) {
        $metrics_data['columns'][] = $old_name;
    }
    
    // Update value
    $metrics_data['data'][$month][$old_name] = $new_value;
} else {
    echo json_encode(['error' => 'Invalid request or missing parameters']);
    exit;
}

// Save data back to the database
$json_data = json_encode($metrics_data);

// Update only the sector_outcomes_data table (system no longer uses sector_metrics_data)
$table = "sector_outcomes_data";
$success = false;

$upsert_query = "UPDATE $table 
                SET table_name = ?, 
                    data_json = ?,
                    updated_at = NOW()
                WHERE metric_id = ? AND is_draft = 0";

$stmt = $conn->prepare($upsert_query);
if ($stmt) {
    $stmt->bind_param("ssi", $table_name, $json_data, $metric_id);
    if ($stmt->execute()) {
        $success = true;
    }
    $stmt->close();
}

// Return success or failure based on update result
if ($success) {
    echo json_encode([
        'success' => true,
        'message' => 'Metric updated successfully'
    ]);
} else {
    echo json_encode([
        'error' => 'Database error: ' . $conn->error
    ]);
}
?>


