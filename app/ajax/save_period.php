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
    $period_name = trim($_POST['period_name'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $status = trim($_POST['status'] ?? 'closed');
    
    // Validation
    if (empty($period_name)) {
        throw new Exception('Period name is required');
    }
    
    if (empty($start_date) || empty($end_date)) {
        throw new Exception('Start date and end date are required');
    }
    
    // Validate date format and logic
    $start_date_obj = DateTime::createFromFormat('Y-m-d', $start_date);
    $end_date_obj = DateTime::createFromFormat('Y-m-d', $end_date);
    
    if (!$start_date_obj || !$end_date_obj) {
        throw new Exception('Invalid date format');
    }
    
    if ($end_date_obj <= $start_date_obj) {
        throw new Exception('End date must be after start date');
    }
    
    // Extract year and quarter from period name (e.g., "Q1 2025")
    if (preg_match('/Q(\d+)\s+(\d{4})/', $period_name, $matches)) {
        $quarter = (int)$matches[1];
        $year = (int)$matches[2];
        
        if ($quarter < 1 || $quarter > 4) {
            throw new Exception('Invalid quarter. Must be between 1 and 4.');
        }
    } else {
        throw new Exception('Period name must follow format "Q1 2025"');
    }
    
    // Validate status
    if (!in_array($status, ['open', 'closed'])) {
        $status = 'closed'; // Default to closed
    }
      // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Check if period already exists
        $check_query = "SELECT period_id FROM reporting_periods WHERE year = ? AND quarter = ?";
        $check_stmt = $conn->prepare($check_query);
        if (!$check_stmt) {
            throw new Exception('Failed to prepare check statement: ' . $conn->error);
        }
        
        $check_stmt->bind_param("ii", $year, $quarter);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("Period Q{$quarter} {$year} already exists");
        }
        
        // If setting to open, close all other periods first
        if ($status === 'open') {
            $close_query = "UPDATE reporting_periods SET status = 'closed'";
            if (!$conn->query($close_query)) {
                throw new Exception('Failed to close existing periods: ' . $conn->error);
            }
        }
        
        // Insert new period
        $insert_query = "INSERT INTO reporting_periods (year, quarter, start_date, end_date, status, is_standard_dates, created_at, updated_at) 
                        VALUES (?, ?, ?, ?, ?, 1, NOW(), NOW())";
        
        $insert_stmt = $conn->prepare($insert_query);
        if (!$insert_stmt) {
            throw new Exception('Failed to prepare insert statement: ' . $conn->error);
        }
        
        $insert_stmt->bind_param("iisss", $year, $quarter, $start_date, $end_date, $status);
        
        if (!$insert_stmt->execute()) {
            throw new Exception('Failed to save period: ' . $insert_stmt->error);
        }
        
        $period_id = $conn->insert_id;
        
        // Commit transaction
        $conn->commit();
        
        // Log the action
        if (function_exists('log_activity') && isset($_SESSION['user_id'])) {
            log_activity($_SESSION['user_id'], "Created new reporting period: Q{$quarter} {$year}");
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Period created successfully',
            'data' => [
                'period_id' => $period_id,
                'period_name' => "Q{$quarter} {$year}",
                'year' => $year,
                'quarter' => $quarter,
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
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
