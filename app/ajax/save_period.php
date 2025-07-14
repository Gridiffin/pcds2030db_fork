<?php
/**
 * Save Period AJAX Endpoint
 * 
 * Creates a new reporting period
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
require_once ROOT_PATH . 'app/lib/db_names_helper.php';

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
    $period_type = trim($_POST['period_type'] ?? 'quarter');
    $period_number = trim($_POST['period_number'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $status = trim($_POST['status'] ?? 'closed'); // Default to closed to match ENUM and new modal
    
    // Validation
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
    
    if ($period_type == 'yearly' && $period_number != 1) {
        throw new Exception('Yearly period number must be 1 (there is only one yearly period per year)');
    }
    
    if (empty($year)) {
        throw new Exception('Year is required');
    }
    if (!preg_match('/^\d{4}$/', $year) || (int)$year < 2000 || (int)$year > 2099) {
        throw new Exception('Invalid year format. Must be YYYY between 2000 and 2099.');
    }

    // Construct period_name for internal use or logging if necessary
    $period_name_constructed = "";
    $period_number_int = (int)$period_number;
    $year_int = (int)$year;

    if ($period_type == 'quarter') {
        $period_name_constructed = "Q{$period_number_int} {$year_int}";
    } elseif ($period_type == 'half') {
        $period_name_constructed = "Half Yearly {$period_number_int} {$year_int}";
    } elseif ($period_type == 'yearly') {
        $period_name_constructed = "Yearly {$period_number_int} {$year_int}";
    }
    
    if (empty($start_date) || empty($end_date)) {
        throw new Exception('Start date and end date are required');
    }
    
    // Validate date format and logic
    $start_date_obj = DateTime::createFromFormat('Y-m-d', $start_date);
    $end_date_obj = DateTime::createFromFormat('Y-m-d', $end_date);
    
    if (!$start_date_obj || !$end_date_obj) {
        throw new Exception('Invalid date format. Expected YYYY-MM-DD.');
    }
    
    if ($end_date_obj <= $start_date_obj) {
        throw new Exception('End date must be after start date');
    }
    
    // Remove old extraction logic
    /*
    if (preg_match('/Q(\d+)\s+(\d{4})/', $period_name, $matches)) {
        $quarter_extracted = (int)$matches[1]; // Use new $quarter and $year directly
        $year_extracted = (int)$matches[2];
        
        if ($quarter_extracted < 1 || $quarter_extracted > 4) {
            throw new Exception('Invalid quarter. Must be between 1 and 4.');
        }
    } else {
        // Could add regex for HY1/HY2 if period_name was still primary input
        throw new Exception('Period name must follow format \"Q1 2025\" or \"HY1 2025\"'); 
    }
    */
    
    // Convert to integer after validation for database (already done for $quarter_int, $year_int)

    // Validate status
    if (!in_array($status, ['open', 'closed'])) { // Use valid ENUM values
        $status = 'closed'; // Default to closed
    }
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Check if period already exists
        $check_query = "SELECT period_id FROM reporting_periods 
                       WHERE year = ? AND period_type = ? AND period_number = ?";
        $check_stmt = $conn->prepare($check_query);
        if (!$check_stmt) {
            throw new Exception('Failed to prepare check statement: ' . $conn->error);
        }
        $check_stmt->bind_param("isi", $year_int, $period_type, $period_number_int);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        if ($result->num_rows > 0) {
            throw new Exception("Period {$period_name_constructed} already exists");
        }
        // If setting to open, close all other periods (if that's the desired logic)
        if ($status === 'open') {
            $close_query = "UPDATE reporting_periods SET status = 'closed' WHERE status = 'open'";
            if (!$conn->query($close_query)) {
                throw new Exception('Failed to close existing periods: ' . $conn->error);
            }
        }        // Insert new period
        $insert_query = "INSERT INTO reporting_periods (year, period_type, period_number, start_date, end_date, status, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())";
        $insert_stmt = $conn->prepare($insert_query);
        if (!$insert_stmt) {
            throw new Exception('Failed to prepare insert statement: ' . $conn->error);
        }
        
        // Bind parameters: year, period_type, period_number, start_date, end_date, status
        $insert_stmt->bind_param("isisss", $year_int, $period_type, $period_number_int, $start_date, $end_date, $status);
        if (!$insert_stmt->execute()) {
            throw new Exception('Failed to save period: ' . $insert_stmt->error);
        }
        $period_id = $conn->insert_id;        // Commit transaction
        $conn->commit();
        
        // Log successful period creation
        log_audit_action('create_period', "Created reporting period: {$period_name_constructed} (ID: {$period_id}, Status: {$status})", 'success');
        
        // Log the action (legacy logging)
        if (function_exists('log_activity') && isset($_SESSION['user_id'])) {
            log_activity($_SESSION['user_id'], "Created new reporting period: {$period_name_constructed}");
        }
        echo json_encode([
            'success' => true,
            'message' => 'Period created successfully',
            'data' => [
                'period_id' => $period_id,
                'period_name' => $period_name_constructed, // Use constructed name for response only
                'year' => $year_int,
                'period_type' => $period_type,
                'period_number' => $period_number_int,
                'start_date' => $start_date,
                'end_date' => $end_date,
                'status' => $status
            ]
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error in save_period.php: " . $e->getMessage());
    
    // Log failed period creation attempt
    $error_details = "Failed to create reporting period. Error: " . $e->getMessage();
    if (!empty($period_name_constructed)) {
        $error_details = "Failed to create period {$period_name_constructed}. Error: " . $e->getMessage();
    }
    log_audit_action('create_period', $error_details, 'failure');
    
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
