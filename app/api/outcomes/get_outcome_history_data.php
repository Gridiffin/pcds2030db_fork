<?php
/**
 * API Endpoint: Get Outcome History Data
 * 
 * Returns the data_json for a specific history record.
 */

// Include necessary files
require_once '../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/outcomes.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

// Get history ID from request
$history_id = isset($_GET['history_id']) ? intval($_GET['history_id']) : 0;

if ($history_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid history ID']);
    exit;
}

try {
    // Query the database for the history record
    $query = "SELECT data_json FROM outcome_history WHERE history_id = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("i", $history_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true, 
            'data' => $row['data_json']
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'History record not found']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
