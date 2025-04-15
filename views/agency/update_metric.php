<?php
header('Content-Type: application/json');

// Database connection
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';

// Get input data
$data = json_decode(file_get_contents('php://input'), true);
$metric_id = $conn->real_escape_string($data['metric_id'] ?? '');
$metric_name = $conn->real_escape_string($data['metric_name'] ?? '');
$month = intval($data['month'] ?? 0);
$new_value = floatval($data['new_value'] ?? 0);
$new_name = $conn->real_escape_string($data['new_name'] ?? '');

// Handle metric name change
if (!empty($new_name) && $new_name !== $metric_name) {
    // Update all records with old name to new name
    $result = $conn->query("UPDATE sector_metrics_draft 
                         SET metric_name = '$new_name' 
                         WHERE metric_name = '$metric_name'");

    if (!$result) die(json_encode(['error' => $conn->error]));
    $metric_name = $new_name;
}

// Handle metric value update/addition
if ($month > 0) {
    $exists = $conn->query("SELECT 1 FROM sector_metrics_draft 
                         WHERE metric_name = '$metric_name' 
                         AND metric_month = $month");

    if ($exists && $exists->num_rows) {
        // Update existing
        $result = $conn->query("UPDATE sector_metrics_draft 
                            SET metric_value = $new_value 
                            WHERE metric_name = '$metric_name' 
                            AND metric_month = $month");
    } else {
        // Insert new
        $result = $conn->query("INSERT INTO sector_metrics_draft 
                            (metric_id, metric_name, metric_value, metric_month) 
                            VALUES ('$metric_id', '$metric_name', $new_value, $month)");
    }

    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['error' => $conn->error]);
    }
} else {
    echo json_encode(['success' => true]); // For name-only changes
}
?>
