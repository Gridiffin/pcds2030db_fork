<?php
/**
 * Delete Reporting Period
 * 
 * AJAX endpoint to delete a reporting period by ID
 */

// Prevent any output before our JSON response
if (ob_get_level() > 0) {
    ob_end_clean(); // Clean any existing buffer
}
ob_start(); // Start a new output buffer

// Include necessary files
require_once '../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';

// Set JSON content type header IMMEDIATELY
header('Content-Type: application/json');

// Custom error handler to catch any PHP errors and prevent them from corrupting JSON output
function delete_period_json_error_handler($errno, $errstr, $errfile, $errline) {
    // Log the error
    error_log("PHP Error in delete_period.php: [$errno] $errstr in $errfile on line $errline");
    
    // Clean any output buffer if not already cleaned
    if (ob_get_level() > 0) {
        ob_clean();
    }
    
    // Ensure header is set again in case it was overwritten
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    
    // Return JSON error
    echo json_encode([
        'success' => false,
        'message' => 'Server error occurred during period deletion.'
    ]);
    exit;
}

// Set custom error handler
set_error_handler('delete_period_json_error_handler');

try {
    // Verify user is logged in and is admin
    if (!is_logged_in() || !is_admin()) {
        echo json_encode([
            'success' => false,
            'message' => 'Unauthorized access'
        ]);
        exit;
    }

    // Check if required parameter is provided
    if (!isset($_POST['period_id']) || !is_numeric($_POST['period_id'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid or missing period ID'
        ]);
        exit;
    }

    // Get parameter
    $periodId = intval($_POST['period_id']);

    // Connect to database
    global $pdo;
    
    // First check if the period exists
    $checkStmt = $pdo->prepare("SELECT period_id FROM reporting_periods WHERE period_id = :period_id");
    $checkStmt->execute([':period_id' => $periodId]);
    
    if ($checkStmt->rowCount() === 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Period not found'
        ]);
        exit;
    }
    
    // TODO: Consider adding a check for dependencies (e.g., submissions linked to this period)
    // before allowing deletion. If dependencies exist, you might want to prevent deletion
    // or provide a warning. For example:
    // $dependencyStmt = $pdo->prepare("SELECT COUNT(*) FROM submissions WHERE period_id = :period_id");
    // $dependencyStmt->execute([':period_id' => $periodId]);
    // if ($dependencyStmt->fetchColumn() > 0) {
    //     echo json_encode(['success' => false, 'message' => 'Cannot delete period. It has associated submissions.']);
    //     exit;
    // }
    
    // Delete the period
    $deleteStmt = $pdo->prepare("DELETE FROM reporting_periods WHERE period_id = :period_id");
    $deleteSuccess = $deleteStmt->execute([':period_id' => $periodId]);
    
    if ($deleteSuccess && $deleteStmt->rowCount() > 0) {
        // Successfully deleted
        echo json_encode([
            'success' => true,
            'message' => 'Period deleted successfully'
        ]);
    } else {
        // Failed to delete or period was already gone
        error_log("Failed to delete period ID: {$periodId}. Row count: " . $deleteStmt->rowCount());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete period. It might have been already deleted or an error occurred.'
        ]);
    }
    
} catch (PDOException $e) {
    // Log database error
    error_log('Database error in delete_period.php: ' . $e->getMessage());
    
    // Clean buffer before outputting error
    if (ob_get_level() > 0) {
        ob_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred during period deletion.'
    ]);
} catch (Exception $e) {
    // Log general error
    error_log('General error in delete_period.php: ' . $e->getMessage());

    if (ob_get_level() > 0) {
        ob_clean();
    }
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred during period deletion.'
    ]);
}

// Restore previous error handler
restore_error_handler();

// Clean output buffer and send response
ob_end_flush();
exit;
