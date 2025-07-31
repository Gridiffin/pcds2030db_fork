<?php
// Start session FIRST before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a reporting period already exists
 * 
 * AJAX endpoint to check if a period with the specified quarter and year already exists
 */

// Prevent any output before our JSON response
if (ob_get_level() > 0) {
    ob_end_clean(); // Clean any existing buffer
}
ob_start(); // Start a new output buffer

// Include necessary files
require_once '../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admin_functions.php';
require_once ROOT_PATH . 'app/lib/db_names_helper.php';

// Set JSON content type header IMMEDIATELY
header('Content-Type: application/json');

// Custom error handler
function check_period_json_error_handler($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error in check_period_exists.php: [$errno] $errstr in $errfile on line $errline");
    if (ob_get_level() > 0) {
        ob_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred while checking period existence.'
    ]);
    exit;
}
set_error_handler('check_period_json_error_handler');

try {
    // Verify user is logged in and is admin
    if (!is_logged_in() || !is_admin()) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access'
        ]);
        exit;
    }

    // Check if required parameters are provided
    if (!isset($_POST['period_type']) || !isset($_POST['period_number']) || !isset($_POST['year'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Missing required parameters (period_type, period_number, or year)'
        ]);
        exit;
    }

    // Get parameters
    $period_type = trim($_POST['period_type'] ?? '');
    $period_number = filter_input(INPUT_POST, 'period_number', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $year = filter_input(INPUT_POST, 'year', FILTER_VALIDATE_INT, ['options' => ['min_range' => 2000, 'max_range' => 2099]]);

    // Validate parameters
    if (!in_array($period_type, ['quarter', 'half', 'yearly']) || $period_number === false || $year === false) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid parameters for period_type, period_number, or year.'
        ]);
        exit;
    }
    
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM reporting_periods 
        WHERE period_type = ? AND period_number = ? AND year = ?
    ");
    
    $stmt->bind_param("sii", $period_type, $period_number, $year);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $exists = $row && $row['count'] > 0;
    
    echo json_encode([
        'success' => true,
        'exists' => $exists
    ]);
    
} catch (Exception $e) {
    error_log('Database error in check_period_exists.php: ' . $e->getMessage());
    if (ob_get_level() > 0) {
        ob_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred while checking period existence.'
    ]);
} catch (Exception $e) {
    error_log('General error in check_period_exists.php: ' . $e->getMessage());
    if (ob_get_level() > 0) {
        ob_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred while checking period existence.'
    ]);
}

restore_error_handler();
ob_end_flush();
exit;
