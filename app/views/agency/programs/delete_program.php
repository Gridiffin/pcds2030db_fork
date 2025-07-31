<?php
/**
 * Delete Program
 * 
 * Handles deletion of agency-created programs.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_permissions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/audit_log.php';
require_once PROJECT_ROOT_PATH . 'app/lib/notifications_core.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Handle program deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['program_id'])) {
    $program_id = intval($_POST['program_id']);
    $user_id = $_SESSION['user_id'];
    
    // Check if user can delete program (creators and focal users)
    $can_delete = is_focal_user() || is_program_creator($program_id);
    if (!$can_delete) {
        // Log failed deletion attempt - unauthorized
        log_audit_action('delete_program_failed', "Program ID: $program_id | Error: Unauthorized access - not program creator or focal user", 'failure', $user_id);
        
        // Return JSON response for AJAX requests
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'You do not have permission to delete this program. Only program creators and focal users can delete programs.'
        ]);
        exit;
    }
    
    // Get program details for logging and notifications
    $query = "SELECT p.program_name, p.program_number, p.agency_id, a.agency_name 
              FROM programs p 
              JOIN agency a ON p.agency_id = a.agency_id 
              WHERE p.program_id = ? AND p.is_deleted = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Log failed deletion attempt - program not found
        log_audit_action('delete_program_failed', "Program ID: $program_id | Error: Program not found", 'failure', $user_id);
        
        // Return JSON response for AJAX requests
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Program not found.'
        ]);
        exit;
    }
    
    $program = $result->fetch_assoc();
    $program_name = $program['program_name'];
    $program_data = [
        'program_name' => $program['program_name'],
        'program_number' => $program['program_number'],
        'agency_id' => $program['agency_id'],
        'agency_name' => $program['agency_name']
    ];
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Delete related records in the correct order to avoid foreign key constraint violations
        
        // 1. Delete program hold points
        $delete_hold_points = "DELETE FROM program_hold_points WHERE program_id = ?";
        $stmt = $conn->prepare($delete_hold_points);
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        
        // 2. Delete program status history
        $delete_status_history = "DELETE FROM program_status_history WHERE program_id = ?";
        $stmt = $conn->prepare($delete_status_history);
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        
        // 3. Delete program submissions (these are already handled but keeping for completeness)
        $delete_submissions = "DELETE FROM program_submissions WHERE program_id = ?";
        $stmt = $conn->prepare($delete_submissions);
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        
        // Note: program_user_assignments will be automatically deleted due to ON DELETE CASCADE
        
        // 4. Finally delete the program itself
        $delete_program = "DELETE FROM programs WHERE program_id = ?";
        $stmt = $conn->prepare($delete_program);
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        // Log the successful deletion
        log_audit_action('delete_program', "Program Name: $program_name | Program ID: $program_id", 'success', $user_id);
        
        // Send notification for program deletion
        notify_program_deleted($program_id, $user_id, $program_data);
        
        // Return JSON response for AJAX requests
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'message' => 'Program deleted successfully.'
        ]);
        exit;
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        
        // Log the failed deletion attempt
        log_audit_action('delete_program_failed', "Program Name: $program_name | Program ID: $program_id | Error: " . $e->getMessage(), 'failure', $user_id);
        
        // Return JSON response for AJAX requests
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to delete program: ' . $e->getMessage()
        ]);
        exit;
    }
} else {
    // Invalid request - return JSON response for AJAX requests
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request.'
    ]);
    exit;
}


