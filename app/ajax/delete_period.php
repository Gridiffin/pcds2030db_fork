<?php
/**
 * Delete Reporting Period
 * 
 * AJAX endpoint to delete a reporting period by ID
 */

// Start output buffering immediately to catch any stray output
if (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

// Set content type to JSON as the very first header
header('Content-Type: application/json');

// Function to send JSON response and exit
function send_json_response($success, $message, $data = null) {
    ob_clean(); // Clean the buffer before sending the response
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    ob_end_flush(); // Flush the output buffer
    exit;
}

// Global error handler to catch any uncaught errors/warnings and send JSON response
function global_json_error_handler($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error in delete_period.php: [$errno] $errstr in $errfile on line $errline");
    // Do not send header here again as it's already sent
    // Ensure buffer is clean before sending error
    if (ob_get_level() > 0) {
        ob_clean();
    }
    echo json_encode([
        'success' => false,
        'message' => 'A server error occurred. Please check logs.' // Generic message for client
    ]);
    if (ob_get_level() > 0) {
        ob_end_flush();
    }
    exit;
}
set_error_handler('global_json_error_handler');

// Load configuration first to ensure ROOT_PATH is defined
if (!file_exists('../config/config.php')) {
    send_json_response(false, 'Configuration file missing.');
}
require_once '../config/config.php';

// Include essential libraries
$required_libs = [
    ROOT_PATH . 'app/lib/session.php',       // For session management (is_logged_in)
    ROOT_PATH . 'app/lib/functions.php',     // For general functions (is_logged_in, potentially is_admin if not in admin_functions)
    ROOT_PATH . 'app/lib/admin_functions.php', // For admin-specific functions (is_admin)
    ROOT_PATH . 'app/lib/db_connect.php',    // For database connection ($conn)
    ROOT_PATH . 'app/lib/audit_log.php'      // For audit logging
];

foreach ($required_libs as $lib_path) {
    if (!file_exists($lib_path)) {
        error_log("Missing required library: " . $lib_path);
        send_json_response(false, 'A critical server file is missing.');
    }
    require_once $lib_path;
}

// db_connect.php creates $conn. Make it available.
global $conn;

if (!$conn) {
    error_log("MySQLi connection object (\$conn) not available in delete_period.php after includes.");
    send_json_response(false, 'Database connection failed. Check server logs.');
}

try {
    // Verify user is logged in and is admin (functions from session.php and admin_functions.php)
    if (!is_logged_in()) {
        send_json_response(false, 'Unauthorized access: Not logged in.');
    }
    if (!is_admin()) {
        send_json_response(false, 'Unauthorized access: Admin privileges required.');
    }

    // Check if required parameter is provided
    if (!isset($_POST['period_id']) || !is_numeric($_POST['period_id'])) {
        send_json_response(false, 'Invalid or missing period ID.');
    }

    $periodId = intval($_POST['period_id']);    // Check if the period exists using mysqli and get period details for audit logging
    $stmt = $conn->prepare("SELECT period_id, quarter, year, status FROM reporting_periods WHERE period_id = ?");
    if (!$stmt) {
        error_log("MySQLi prepare error (check period): " . $conn->error);
        send_json_response(false, 'Database query error (check period).');
    }
    $stmt->bind_param("i", $periodId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        
        // Log failed deletion attempt - period not found
        log_audit_action('delete_period', "Attempted to delete non-existent period (ID: {$periodId})", 'failure');
        
        send_json_response(false, 'Period not found.');
    }
    
    // Store period details for audit logging
    $period_data = $result->fetch_assoc();
    $period_name = "Q{$period_data['quarter']} {$period_data['year']}";
    $stmt->close();

    // TODO: Add dependency check here if necessary (e.g., check for submissions linked to this period)
    // Example for mysqli:
    // $depStmt = $conn->prepare("SELECT COUNT(*) as count FROM submissions WHERE period_id = ?");
    // $depStmt->bind_param("i", $periodId);
    // $depStmt->execute();
    // $depResult = $depStmt->get_result()->fetch_assoc();
    // $depStmt->close();
    // if ($depResult['count'] > 0) {
    //     send_json_response(false, 'Cannot delete period. It has associated submissions.');
    // }

    // Delete the period using mysqli
    $deleteStmt = $conn->prepare("DELETE FROM reporting_periods WHERE period_id = ?");
    if (!$deleteStmt) {
        error_log("MySQLi prepare error (delete period): " . $conn->error);
        send_json_response(false, 'Database query error (delete period).');
    }
    $deleteStmt->bind_param("i", $periodId);
    $deleteSuccess = $deleteStmt->execute();
    $affected_rows = $deleteStmt->affected_rows;
    $deleteStmt->close();    if ($deleteSuccess && $affected_rows > 0) {
        // Log successful period deletion
        log_audit_action(
            'delete_period',
            "Deleted reporting period: {$period_name} (ID: {$periodId}, Status: {$period_data['status']})",
            'success',
            $_SESSION['user_id'] ?? null
        ); // Added user_id for better traceability
        
        send_json_response(true, 'Period deleted successfully.');
    } else if ($deleteSuccess && $affected_rows === 0) {
        // This means the query ran but didn't delete anything (e.g., already deleted)
        log_audit_action('delete_period', "Attempted to delete already deleted period: {$period_name} (ID: {$periodId})", 'failure');
        
        send_json_response(false, 'Period not found or already deleted.');
    } else {
        error_log("Failed to delete period ID: {$periodId}. MySQLi execute status: " . ($deleteSuccess ? 'true' : 'false') . ". Affected rows: " . $affected_rows . " Error: " . $conn->error);
        
        // Log failed deletion attempt
        log_audit_action(
            'delete_period',
            "Failed to delete period: {$period_name} (ID: {$periodId}). Database error: " . $conn->error,
            'failure',
            $_SESSION['user_id'] ?? null
        ); // Added user_id for better traceability
        
        send_json_response(false, 'Failed to delete period. An error occurred.');
    }

} catch (Exception $e) { // Catch any other exceptions
    error_log("General Error in delete_period.php: " . $e->getMessage() . "\nStack trace:\n" . $e->getTraceAsString());
    
    // Log general error
    log_audit_action('delete_period', "Exception during period deletion (ID: {$periodId}). Error: " . $e->getMessage(), 'failure');
    
    send_json_response(false, 'An unexpected error occurred: ' . $e->getMessage());
}

// Restore default error handler and flush buffer if anything remains (should be empty due to send_json_response)
restore_error_handler();
if (ob_get_level() > 0) {
    ob_end_flush();
}
?>
