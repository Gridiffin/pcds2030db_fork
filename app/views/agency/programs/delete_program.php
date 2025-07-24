<?php
/**
 * Delete Program
 * 
 * Handles deletion of agency-created programs.
 */

// Define the root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_permissions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/audit_log.php';

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
        
        $_SESSION['message'] = 'You do not have permission to delete this program. Only program creators and focal users can delete programs.';
        $_SESSION['message_type'] = 'danger';
        header('Location: ' . APP_URL . '/app/views/agency/programs/view_programs.php');
        exit;
    }
    
    // Get program details for logging
    $query = "SELECT program_name FROM programs WHERE program_id = ? AND is_deleted = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Log failed deletion attempt - program not found
        log_audit_action('delete_program_failed', "Program ID: $program_id | Error: Program not found", 'failure', $user_id);
        
        $_SESSION['message'] = 'Program not found.';
        $_SESSION['message_type'] = 'danger';
        header('Location: ' . APP_URL . '/app/views/agency/programs/view_programs.php');
        exit;
    }
    
    $program = $result->fetch_assoc();
    $program_name = $program['program_name'];
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Delete program submissions first
        $delete_submissions = "DELETE FROM program_submissions WHERE program_id = ?";
        $stmt = $conn->prepare($delete_submissions);
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        
        // Then delete the program
        $delete_program = "DELETE FROM programs WHERE program_id = ?";
        $stmt = $conn->prepare($delete_program);
        $stmt->bind_param("i", $program_id);
        $stmt->execute();
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['message'] = 'Program deleted successfully.';
        $_SESSION['message_type'] = 'success';
        
        // Log the successful deletion
        log_audit_action('delete_program', "Program Name: $program_name | Program ID: $program_id", 'success', $user_id);
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        
        $_SESSION['message'] = 'Failed to delete program: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
        
        // Log the failed deletion attempt
        log_audit_action('delete_program_failed', "Program Name: $program_name | Program ID: $program_id | Error: " . $e->getMessage(), 'failure', $user_id);
    }
    
    // Redirect back to program list
    header('Location: ' . APP_URL . '/app/views/agency/programs/view_programs.php');
    exit;
} else {
    // Invalid request
    $_SESSION['message'] = 'Invalid request.';
    $_SESSION['message_type'] = 'danger';
    header('Location: ' . APP_URL . '/app/views/agency/programs/view_programs.php');
    exit;
}


