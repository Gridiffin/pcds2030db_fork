<?php
/**
 * Delete Program
 * 
 * Handles deletion of agency-created programs.
 */

// Include necessary files
require_once ROOT_PATH . 'app/config/config.php';
require_once ROOT_PATH . 'app/lib/$((includes/db_connect.php -replace 'includes/', ''))';
require_once ROOT_PATH . 'app/lib/$((includes/session.php -replace 'includes/', ''))';
require_once ROOT_PATH . 'app/lib/$((includes/functions.php -replace 'includes/', ''))';
require_once ROOT_PATH . 'app/lib/$((includes/agencies/index.php -replace 'includes/', ''))';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Handle program deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['program_id'])) {
    $program_id = intval($_POST['program_id']);
    $user_id = $_SESSION['user_id'];
    
    // Verify program exists and belongs to this agency
    $query = "SELECT * FROM programs WHERE program_id = ? AND owner_agency_id = ? AND is_assigned = 0";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $program_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['message'] = 'You do not have permission to delete this program.';
        $_SESSION['message_type'] = 'danger';
        header('Location: view_programs.php');
        exit;
    }
    
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
    } catch (Exception $e) {
        // Rollback on error
        $conn->rollback();
        
        $_SESSION['message'] = 'Failed to delete program: ' . $e->getMessage();
        $_SESSION['message_type'] = 'danger';
    }
    
    // Redirect back to program list
    header('Location: view_programs.php');
    exit;
} else {
    // Invalid request
    $_SESSION['message'] = 'Invalid request.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

