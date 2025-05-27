<?php
/**
 * Update Period AJAX Endpoint
 * 
 * Updates an existing reporting period
 */

// Start session
session_start();

header('Content-Type: application/json');

// Include necessary files
require_once '../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admin_functions.php';

// Check if user is admin
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Database connection is already available via db_connect.php as $conn (MySQLi)
    
    // Get and validate input data
    $periodId = isset($_POST['period_id']) ? intval($_POST['period_id']) : 0;
    $quarter = trim($_POST['quarter'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $status = trim($_POST['status'] ?? 'closed'); // Default to closed
    
    // Validation
    if (empty($periodId) || $periodId <= 0) {
        throw new Exception('Invalid period ID');
    }
    
    if (empty($quarter)) {
        throw new Exception('Period type is required');
    }
    
    if (!in_array($quarter, ['1', '2', '3', '4', '5', '6'])) {
        throw new Exception('Invalid period type. Must be Q1-Q4 or Half Yearly.');
    }
    
    if (empty($year)) {
        throw new Exception('Year is required');
    }
    
    if (!is_numeric($year) || $year < 2000 || $year > 2099) {
        throw new Exception('Year must be between 2000 and 2099');
    }
    
    if (empty($start_date)) {
        throw new Exception('Start date is required');
    }
    
    if (empty($end_date)) {
        throw new Exception('End date is required');
    }
    
    // Validate dates
    $start = new DateTime($start_date);
    $end = new DateTime($end_date);
    
    if ($start >= $end) {
        throw new Exception('End date must be after start date');
    }
    
    if (!in_array($status, ['open', 'closed'])) {
        throw new Exception('Invalid status value');
    }
    
    // Check if the period exists
    $checkStmt = $conn->prepare("SELECT period_id FROM reporting_periods WHERE period_id = ?");
    $checkStmt->bind_param("i", $periodId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        throw new Exception('Period not found');
    }
    $checkStmt->close();
    
    // Check for conflicting periods (same quarter/year but different ID)
    $conflictStmt = $conn->prepare(
        "SELECT period_id FROM reporting_periods 
         WHERE quarter = ? AND year = ? AND period_id != ?"
    );
    $conflictStmt->bind_param("sii", $quarter, $year, $periodId);
    $conflictStmt->execute();
    $conflictResult = $conflictStmt->get_result();
    
    if ($conflictResult->num_rows > 0) {
        throw new Exception("A period for this quarter and year already exists");
    }
    $conflictStmt->close();
    
    // Format dates to MySQL format
    $start_date_db = $start_date;
    $end_date_db = $end_date;
    
    // Update the period
    $updateStmt = $conn->prepare(
        "UPDATE reporting_periods 
         SET quarter = ?, 
             year = ?, 
             start_date = ?, 
             end_date = ?, 
             status = ?, 
             updated_at = NOW()
         WHERE period_id = ?"
    );
    $updateStmt->bind_param("sisssi", $quarter, $year, $start_date_db, $end_date_db, $status, $periodId);
    $updateSuccess = $updateStmt->execute();
    
    if (!$updateSuccess) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    if ($updateStmt->affected_rows === 0) {
        // No rows were updated (maybe data hasn't changed)
        $updateStmt->close();
        echo json_encode([
            'success' => true,
            'message' => 'No changes were made to the period'
        ]);
        exit;
    }
    
    $updateStmt->close();
    
    // Success
    echo json_encode([
        'success' => true,
        'message' => 'Period updated successfully',
        'period_id' => $periodId
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    error_log("Error in update_period.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'A system error occurred. Please try again.'
    ]);
}
?>
