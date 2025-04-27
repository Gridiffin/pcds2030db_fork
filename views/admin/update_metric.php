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

if (!$metric_id) {
    echo json_encode(['error' => 'Metric ID not found in request']);
    exit;
}

// For admin view, we need to determine which sector this metric belongs to
$sector_query = "SELECT sector_id FROM sector_metrics_data 
                WHERE metric_id = ? AND is_draft = 0 LIMIT 1";
$stmt = $conn->prepare($sector_query);
$stmt->bind_param("i", $metric_id);
$stmt->execute();
$sector_result = $stmt->get_result();

$sector_id = null;
if ($sector_result->num_rows > 0) {
    $sector_row = $sector_result->fetch_assoc();
    $sector_id = $sector_row['sector_id'];
} else {
    // If this is a new metric being created by admin, we'll need to identify the sector
    // This would need more logic in a real implementation
    echo json_encode(['error' => 'Cannot determine sector for this metric']);
    exit;
}

// Get existing metrics data or initialize new structure
$query = "SELECT data_json FROM sector_metrics_data 
          WHERE metric_id = ? AND is_draft = 0 LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $metric_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $metrics_data = json_decode($row['data_json'], true);
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
}

$old_name = $data['column_title'] ?? '';
$new_name = $data['new_name'] ?? '';
$month = $data['month'] ?? '';
$new_value = isset($data['new_value']) ? floatval($data['new_value']) : 0;

// Handle metric name change
if (!empty($new_name) && $new_name !== $old_name) {
    // Update column name in the data structure
    if (in_array($old_name, $metrics_data['columns'])) {
        $index = array_search($old_name, $metrics_data['columns']);
        $metrics_data['columns'][$index] = $new_name;
    } else {
        $metrics_data['columns'][] = $new_name;
    }
    
    // Update all values with this column name
    foreach ($metrics_data['data'] as $m => $values) {
        if (isset($values[$old_name])) {
            $metrics_data['data'][$m][$new_name] = $values[$old_name];
            unset($metrics_data['data'][$m][$old_name]);
        }
    }
} 
// Handle metric value update
elseif (!empty($month)) {
    // Ensure column exists
    if (!in_array($old_name, $metrics_data['columns'])) {
        $metrics_data['columns'][] = $old_name;
    }
    
    // Update value
    $metrics_data['data'][$month][$old_name] = $new_value;
} else {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Save data back to the database
$json_data = json_encode($metrics_data);

$upsert_query = "INSERT INTO sector_metrics_data 
                (metric_id, sector_id, table_name, data_json, is_draft) 
                VALUES (?, ?, ?, ?, 0) 
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
