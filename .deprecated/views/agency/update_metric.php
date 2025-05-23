<?php
header('Content-Type: application/json');

session_start();

// Database connection
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';

// Get input data
$data = json_decode(file_get_contents('php://input'), true);
$table_name = $data['table_name'] ?? '';
$metric_id = isset($data['metric_id']) ? intval($data['metric_id']) : null;
$action = $data['action'] ?? '';
$data_json = $data['data_json'] ?? null; // Add support for direct data_json updates

// Get sector_id from session
$sector_id = $_SESSION['sector_id'] ?? '';
// Allow passing sector_id in the request for API consistency
if (isset($data['sector_id'])) {
    $sector_id = intval($data['sector_id']);
}

if (!$sector_id) {
    echo json_encode(['error' => 'Sector ID not found in session']);
    exit;
}
if (!$metric_id) {
    echo json_encode(['error' => 'Metric ID not found in request']);
    exit;
}

// Special case: If complete data_json is provided, use it directly
if ($data_json !== null) {
    // Save data directly to the database
    $json_data = json_encode($data_json);
    
    $upsert_query = "INSERT INTO sector_metrics_data 
                    (metric_id, sector_id, table_name, data_json, is_draft) 
                    VALUES (?, ?, ?, ?, 1) 
                    ON DUPLICATE KEY UPDATE 
                    table_name = VALUES(table_name), 
                    data_json = VALUES(data_json),
                    updated_at = CURRENT_TIMESTAMP";

    $stmt = $conn->prepare($upsert_query);
    $stmt->bind_param("iiss", $metric_id, $sector_id, $table_name, $json_data);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => $conn->error]);
    }
    exit;
}

// Get existing metrics data or initialize new structure
$query = "SELECT data_json FROM sector_metrics_data 
          WHERE metric_id = ? AND sector_id = ? AND is_draft = 1 LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $metric_id, $sector_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $metrics_data = json_decode($row['data_json'], true);
    
    // Ensure units array exists
    if (!isset($metrics_data['units'])) {
        $metrics_data['units'] = [];
    }
} else {
    // Initialize new data structure
    $metrics_data = [
        'columns' => [],
        'units' => [], // Add units array to store measurement units
        'data' => []
    ];
    
    // Initialize months
    $months = ['January', 'February', 'March', 'April', 'May', 'June', 
               'July', 'August', 'September', 'October', 'November', 'December'];
    
    foreach ($months as $m) {
        $metrics_data['data'][$m] = [];
    }
}

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

// Use prepared statement for security
$upsert_query = "INSERT INTO sector_metrics_data 
                (metric_id, sector_id, table_name, data_json, is_draft) 
                VALUES (?, ?, ?, ?, 1) 
                ON DUPLICATE KEY UPDATE 
                table_name = VALUES(table_name), 
                data_json = VALUES(data_json),
                updated_at = CURRENT_TIMESTAMP";

$stmt = $conn->prepare($upsert_query);
$stmt->bind_param("iiss", $metric_id, $sector_id, $table_name, $json_data);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => $conn->error]);
}
?>
