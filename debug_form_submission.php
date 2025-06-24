<?php
/**
 * Debug script to check form submission data
 */

require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';

// Only allow admin access
if (!is_admin()) {
    http_response_code(403);
    exit('Access denied');
}

// Log all POST data to help debug the issue
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("=== EDIT PROGRAM FORM DEBUG ===");
    error_log("POST data: " . print_r($_POST, true));
    
    // Specifically check date fields
    if (isset($_POST['end_date'])) {
        error_log("end_date value: '" . $_POST['end_date'] . "'");
        error_log("end_date length: " . strlen($_POST['end_date']));
        error_log("end_date type: " . gettype($_POST['end_date']));
    }
    
    if (isset($_POST['start_date'])) {
        error_log("start_date value: '" . $_POST['start_date'] . "'");
        error_log("start_date length: " . strlen($_POST['start_date']));
        error_log("start_date type: " . gettype($_POST['start_date']));
    }
    
    // Check if there are any other fields that might be conflicting
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'date') !== false || strpos($key, 'year') !== false) {
            error_log("Date-related field - $key: '$value'");
        }
    }
    
    echo json_encode([
        'status' => 'debug',
        'post_data' => $_POST,
        'end_date' => $_POST['end_date'] ?? 'not set',
        'start_date' => $_POST['start_date'] ?? 'not set'
    ]);
    exit;
}

echo "Debug script ready. Submit the form to see the data.";
?>
