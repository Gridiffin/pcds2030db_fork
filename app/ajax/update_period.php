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
require_once ROOT_PATH . 'app/lib/audit_log.php';

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
    $use_custom_dates = isset($_POST['use_custom_dates']) ? (bool)$_POST['use_custom_dates'] : false;
    
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
      // Check if the period exists and get current data
    $checkStmt = $conn->prepare("SELECT period_id, quarter, year, start_date, end_date, status, is_standard_dates FROM reporting_periods WHERE period_id = ?");
    $checkStmt->bind_param("i", $periodId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        $checkStmt->close();
        throw new Exception('Period not found');
    }
    
    // Store current data for audit logging
    $current_period = $checkResult->fetch_assoc();
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
      // Set is_standard_dates based on the use_custom_dates flag (inverse)
    $is_standard_dates = $use_custom_dates ? 0 : 1;
    
    // Update the period
    $updateStmt = $conn->prepare(
        "UPDATE reporting_periods 
         SET quarter = ?, 
             year = ?, 
             start_date = ?, 
             end_date = ?, 
             status = ?,
             is_standard_dates = ?,
             updated_at = NOW()
         WHERE period_id = ?"
    );
    $updateStmt->bind_param("sisssii", $quarter, $year, $start_date_db, $end_date_db, $status, $is_standard_dates, $periodId);
    $updateSuccess = $updateStmt->execute();
    
    if (!$updateSuccess) {
        throw new Exception("Database error: " . $conn->error);
    }
      if ($updateStmt->affected_rows === 0) {
        // No rows were updated (maybe data hasn't changed)
        $updateStmt->close();
        
        // Log no changes made
        $period_name = "Q{$quarter} {$year}";
        log_audit_action('update_period', "No changes made to period: {$period_name} (ID: {$periodId})", 'success');
        
        echo json_encode([
            'success' => true,
            'message' => 'No changes were made to the period'
        ]);
        exit;
    }
    
    $updateStmt->close();
    
    // Log successful period update with before/after data
    $period_name = "Q{$quarter} {$year}";
    $changes = [];
    if ($current_period['quarter'] != $quarter) {
        $changes[] = "Quarter: {$current_period['quarter']} → {$quarter}";
    }
    if ($current_period['year'] != $year) {
        $changes[] = "Year: {$current_period['year']} → {$year}";
    }
    if ($current_period['start_date'] != $start_date_db) {
        $changes[] = "Start Date: {$current_period['start_date']} → {$start_date_db}";
    }
    if ($current_period['end_date'] != $end_date_db) {
        $changes[] = "End Date: {$current_period['end_date']} → {$end_date_db}";
    }
    if ($current_period['status'] != $status) {
        $changes[] = "Status: {$current_period['status']} → {$status}";
    }
    if ($current_period['is_standard_dates'] != $is_standard_dates) {
        $changes[] = "Custom Dates: " . ($current_period['is_standard_dates'] ? 'No' : 'Yes') . " → " . ($is_standard_dates ? 'No' : 'Yes');
    }
    
    $change_details = !empty($changes) ? implode(', ', $changes) : 'Minor updates';
    log_audit_action('update_period', "Updated period: {$period_name} (ID: {$periodId}). Changes: {$change_details}", 'success');
    
    // Success
    echo json_encode([
        'success' => true,
        'message' => 'Period updated successfully',
        'period_id' => $periodId
    ]);
    
} catch (Exception $e) {
    // Log failed period update attempt
    $period_name = !empty($quarter) && !empty($year) ? "Q{$quarter} {$year}" : "Unknown Period";
    log_audit_action('update_period', "Failed to update period: {$period_name} (ID: {$periodId}). Error: " . $e->getMessage(), 'failure');
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    error_log("Error in update_period.php: " . $e->getMessage());
    
    // Log system error
    log_audit_action('update_period', "System error while updating period (ID: {$periodId}). Error: " . $e->getMessage(), 'failure');
    
    echo json_encode([
        'success' => false,
        'message' => 'A system error occurred. Please try again.'
    ]);
}
?>
