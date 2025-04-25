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
$allowed_tables = [];

if ($table_name) {
    $allowed_tables[] = $table_name;
}

if (!in_array($table_name, $allowed_tables)) {
    echo json_encode(['error' => 'Invalid table name']);
    exit;
}

$old_name = $conn->real_escape_string($data['column_title'] ?? '');
$new_name = $conn->real_escape_string($data['new_name'] ?? '');
$month = $conn->real_escape_string($data['month'] ?? '');
$new_value = floatval($data['new_value'] ?? 0);

// Admin does not use sector_id filtering
// $sector_id = $_SESSION['sector_id'] ?? '';

if (!$metric_id) {
    echo json_encode(['error' => 'Metric ID not found in request']);
    exit;
}

// Handle metric name change
if (!empty($new_name) && $new_name !== $old_name) {
    $update_name_query = "UPDATE sector_metrics_submitted 
                          SET column_title = '$new_name', table_name = '$table_name'  
                          WHERE column_title = '$old_name' AND metric_id = $metric_id";
    if ($conn->query($update_name_query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => $conn->error]);
    }
    exit;
}

// Handle metric value update/addition
if (!empty($month)) {
    $check_exists_query = "SELECT 1 FROM sector_metrics_submitted 
                           WHERE column_title = '$old_name' 
                           AND month = '$month' 
                           AND metric_id = $metric_id LIMIT 1";
    $exists_result = $conn->query($check_exists_query);

    if ($exists_result && $exists_result->num_rows > 0) {
        // Update existing record
        $update_value_query = "UPDATE sector_metrics_submitted 
                               SET table_content = $new_value, table_name = '$table_name'
                               WHERE column_title = '$old_name' 
                               AND month = '$month' 
                               AND metric_id = $metric_id";
        if ($conn->query($update_value_query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $conn->error]);
        }
    } else {
        // Insert new record
        $insert_query = "INSERT INTO sector_metrics_submitted 
                         (table_name, column_title, table_content, month, metric_id) 
                         VALUES ('$table_name', '$old_name', $new_value, '$month', $metric_id)";
        if ($conn->query($insert_query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $conn->error]);
        }
    }
    exit;
}

echo json_encode(['error' => 'Invalid request']);
?>
