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

// Get sector_id from session
$sector_id = $_SESSION['sector_id'] ?? '';

if (!$sector_id) {
    echo json_encode(['error' => 'Sector ID not found in session']);
    exit;
}
if (!$metric_id) {
    echo json_encode(['error' => 'Metric ID not found in request']);
    exit;
}

// Handle metric name change
if (!empty($new_name) && $new_name !== $old_name) {
    $update_name_query = "UPDATE sector_metrics_draft 
                          SET column_title = '$new_name' 
                          WHERE column_title = '$old_name' AND sector_id = '$sector_id' AND metric_id = $metric_id";
    if ($conn->query($update_name_query)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => $conn->error]);
    }
    exit;
}

// Handle metric value update/addition
if (!empty($month)) {
    $check_exists_query = "SELECT 1 FROM sector_metrics_draft 
                           WHERE column_title = '$old_name' 
                           AND month = '$month' 
                           AND sector_id = '$sector_id'
                           AND metric_id = $metric_id LIMIT 1";
    $exists_result = $conn->query($check_exists_query);

    if ($exists_result && $exists_result->num_rows > 0) {
        // Update existing record
        $update_value_query = "UPDATE sector_metrics_draft 
                               SET table_content = $new_value 
                               WHERE column_title = '$old_name' 
                               AND month = '$month' 
                               AND sector_id = '$sector_id'
                               AND metric_id = $metric_id";
        if ($conn->query($update_value_query)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => $conn->error]);
        }
    } else {
        // Insert new record
        $insert_query = "INSERT INTO sector_metrics_draft 
                         (column_title, table_content, month, sector_id, metric_id) 
                         VALUES ('$old_name', $new_value, '$month', '$sector_id', $metric_id)";
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
