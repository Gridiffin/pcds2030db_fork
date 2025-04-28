<?php
header('Content-Type: application/json');

session_start();

// Database connection
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/functions.php';
require_once '../../includes/admins/index.php';

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

// Get metric data
$metric_data = get_metric_data($metric_id);
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
$unit = $data['unit'] ?? '';  // Add unit parameter

// Handle delete column action
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
// Handle metric name change
else if (!empty($new_name) && $new_name !== $old_name) {
    // Update column name in the data structure
    if (in_array($old_name, $metrics_data['columns'])) {
        $index = array_search($old_name, $metrics_data['columns']);
        $metrics_data['columns'][$index] = $new_name;
        
        // Update unit if the name changes
        if (isset($metrics_data['units'][$old_name])) {
            $metrics_data['units'][$new_name] = $metrics_data['units'][$old_name];
            unset($metrics_data['units'][$old_name]);
        }
        
        // Set unit if provided
        if (!empty($unit)) {
            $metrics_data['units'][$new_name] = $unit;
        }
    } else {
        $metrics_data['columns'][] = $new_name;
        
        // Set unit if provided
        if (!empty($unit)) {
            $metrics_data['units'][$new_name] = $unit;
        }
    }
    
    // Update all values with this column name
    foreach ($metrics_data['data'] as $m => $values) {
        if (isset($values[$old_name])) {
            $metrics_data['data'][$m][$new_name] = $values[$old_name];
            unset($metrics_data['data'][$m][$old_name]);
        }
    }
} 
// Handle unit update only
else if (!empty($unit) && !empty($old_name)) {
    // Update unit for existing column
    $metrics_data['units'][$old_name] = $unit;
}
// Handle metric value update
else if (!empty($month)) {
    // Ensure column exists
    if (!in_array($old_name, $metrics_data['columns'])) {
        $metrics_data['columns'][] = $old_name;
        
        // Set unit if provided with new column
        if (!empty($unit)) {
            $metrics_data['units'][$old_name] = $unit;
        }
    }
    
    // Update value
    $metrics_data['data'][$month][$old_name] = $new_value;
} else {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Save data back to the database
$json_data = json_encode($metrics_data);

$upsert_query = "UPDATE sector_metrics_data 
                SET table_name = ?, 
                    data_json = ?
                WHERE metric_id = ? AND is_draft = 0";

$stmt = $conn->prepare($upsert_query);
$stmt->bind_param("ssi", $table_name, $json_data, $metric_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => $conn->error]);
}
?>
