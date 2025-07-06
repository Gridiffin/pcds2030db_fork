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

// Load database configuration
$config = include __DIR__ . '/../config/db_names.php';
if (!$config || !isset($config['tables']['reporting_periods'])) {
    die('Config not loaded or missing reporting_periods table definition.');
}
$periodsTable = $config['tables']['reporting_periods'];
$periodIdCol = $config['columns']['reporting_periods']['id'];
$periodYearCol = $config['columns']['reporting_periods']['year'];
$periodTypeCol = $config['columns']['reporting_periods']['period_type'];
$periodNumberCol = $config['columns']['reporting_periods']['period_number'];
$periodStartDateCol = $config['columns']['reporting_periods']['start_date'];
$periodEndDateCol = $config['columns']['reporting_periods']['end_date'];
$periodStatusCol = $config['columns']['reporting_periods']['status'];
$periodUpdatedAtCol = $config['columns']['reporting_periods']['updated_at'];

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
    $period_type = trim($_POST['period_type'] ?? 'quarter');
    $period_number = trim($_POST['period_number'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $status = trim($_POST['status'] ?? 'closed'); // Default to closed
    
    // Validation
    if (empty($periodId) || $periodId <= 0) {
        throw new Exception('Invalid period ID');
    }
    
    if (empty($period_number)) {
        throw new Exception('Period number is required');
    }
    
    if (!in_array($period_type, ['quarter', 'half', 'yearly'])) {
        throw new Exception('Invalid period type. Must be quarter, half, or yearly.');
    }
    
    if (!is_numeric($period_number) || $period_number < 1) {
        throw new Exception('Period number must be a positive number');
    }
    
    // Validate period number based on type
    if ($period_type == 'quarter' && ($period_number < 1 || $period_number > 4)) {
        throw new Exception('Quarter period number must be between 1 and 4');
    }
    
    if ($period_type == 'half' && ($period_number < 1 || $period_number > 2)) {
        throw new Exception('Half yearly period number must be between 1 and 2');
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
    $checkStmt = $conn->prepare("SELECT $periodIdCol, $periodTypeCol, $periodNumberCol, $periodYearCol, 
                                        $periodStartDateCol, $periodEndDateCol, $periodStatusCol 
                                 FROM $periodsTable 
                                 WHERE $periodIdCol = ?");
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
    
    // Check for conflicting periods (same period_type/period_number/year but different ID)
    $conflictStmt = $conn->prepare(
        "SELECT $periodIdCol FROM $periodsTable 
         WHERE $periodTypeCol = ? AND $periodNumberCol = ? AND $periodYearCol = ? AND $periodIdCol != ?"
    );
    $conflictStmt->bind_param("siii", $period_type, $period_number, $year, $periodId);
    $conflictStmt->execute();
    $conflictResult = $conflictStmt->get_result();
    
    if ($conflictResult->num_rows > 0) {
        throw new Exception("A period for this period type, number and year already exists");
    }
    $conflictStmt->close();
    
    // Format dates to MySQL format
    $start_date_db = $start_date;
    $end_date_db = $end_date;
    
    // Update the period
    $updateStmt = $conn->prepare(
        "UPDATE $periodsTable 
         SET $periodTypeCol = ?, 
             $periodNumberCol = ?, 
             $periodYearCol = ?, 
             $periodStartDateCol = ?, 
             $periodEndDateCol = ?, 
             $periodStatusCol = ?,
             $periodUpdatedAtCol = NOW()
         WHERE $periodIdCol = ?"
    );
    $updateStmt->bind_param("siisssi", $period_type, $period_number, $year, $start_date_db, $end_date_db, $status, $periodId);
    $updateSuccess = $updateStmt->execute();
    
    if (!$updateSuccess) {
        throw new Exception("Database error: " . $conn->error);
    }
      if ($updateStmt->affected_rows === 0) {
        // No rows were updated (maybe data hasn't changed)
        $updateStmt->close();
        
        // Log no changes made
        $period_name = "{$period_type} {$period_number} {$year}";
        log_audit_action('update_period', "No changes made to period: {$period_name} (ID: {$periodId})", 'success');
        
        echo json_encode([
            'success' => true,
            'message' => 'No changes were made to the period'
        ]);
        exit;
    }
    
    $updateStmt->close();
    
    // Log successful period update with before/after data
    $period_name = "{$period_type} {$period_number} {$year}";
    $changes = [];
    if ($current_period[$periodTypeCol] != $period_type) {
        $changes[] = "Period Type: {$current_period[$periodTypeCol]} → {$period_type}";
    }
    if ($current_period[$periodNumberCol] != $period_number) {
        $changes[] = "Period Number: {$current_period[$periodNumberCol]} → {$period_number}";
    }
    if ($current_period[$periodYearCol] != $year) {
        $changes[] = "Year: {$current_period[$periodYearCol]} → {$year}";
    }
    if ($current_period[$periodStartDateCol] != $start_date_db) {
        $changes[] = "Start Date: {$current_period[$periodStartDateCol]} → {$start_date_db}";
    }
    if ($current_period[$periodEndDateCol] != $end_date_db) {
        $changes[] = "End Date: {$current_period[$periodEndDateCol]} → {$end_date_db}";
    }
    if ($current_period[$periodStatusCol] != $status) {
        $changes[] = "Status: {$current_period[$periodStatusCol]} → {$status}";
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
    $period_name = !empty($period_type) && !empty($period_number) && !empty($year) ? "{$period_type} {$period_number} {$year}" : "Unknown Period";
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
