<?php
/**
 * Toggle Period Status AJAX Endpoint
 * 
 * Allows admins to quickly toggle a reporting period's status between open/closed.
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

// Ensure user is admin
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
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Get period ID and requested status from POST data
    $period_id = isset($_POST['period_id']) ? intval($_POST['period_id']) : 0;
    $status = isset($_POST['status']) ? trim($_POST['status']) : '';
    
    // Validate inputs
    if ($period_id <= 0) {
        throw new Exception('Invalid period ID');
    }
    
    if (!in_array($status, ['open', 'closed'])) {
        throw new Exception('Invalid status. Must be "open" or "closed"');
    }
      // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Check if period exists
        $check_query = "SELECT period_id, year, quarter, status FROM reporting_periods WHERE period_id = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("i", $period_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $period = $result->fetch_assoc();
        
        if (!$period) {
            throw new Exception('Period not found');
        }
        
        // If setting to open, close all other periods first
        if ($status === 'open') {
            $close_query = "UPDATE reporting_periods SET status = 'closed', updated_at = NOW()";
            $conn->query($close_query);
        }
        
        // Update the specific period
        $update_query = "UPDATE reporting_periods SET status = ?, updated_at = NOW() WHERE period_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $status, $period_id);
        $update_stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Log the action
        if (function_exists('log_activity') && isset($_SESSION['user_id'])) {
            $period_name = "Q" . $period['quarter'] . " " . $period['year'];
            log_activity($_SESSION['user_id'], "Changed status of period {$period_name} to {$status}");
        }
        
        echo json_encode([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => [
                'period_id' => $period_id,
                'status' => $status,
                'period_name' => "Q" . $period['quarter'] . " " . $period['year']
            ]
        ]);
          } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error in toggle_period_status.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
