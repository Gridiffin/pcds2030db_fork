<?php
/**
 * Admin Save Program Handler
 * 
 * Handles saving and updating programs from the admin interface.
 * Supports both creating new programs and updating existing ones.
 */

// Start session FIRST before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Define project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is an admin
if (!is_admin()) {
    $_SESSION['message'] = 'Access denied. Admin login required.';
    $_SESSION['message_type'] = 'danger';
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['message'] = 'Invalid request method.';
    $_SESSION['message_type'] = 'danger';
    header('Location: ' . APP_URL . '/app/views/admin/programs/programs.php');
    exit;
}

// Get and validate input parameters
$program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : 0;

// Validate required parameters
if (!$program_id) {
    $_SESSION['message'] = 'Program ID is required.';
    $_SESSION['message_type'] = 'danger';
    header('Location: ' . APP_URL . '/app/views/admin/programs/programs.php');
    exit;
}

try {
    $conn->begin_transaction();
    
    // Verify program exists
    $program_query = "SELECT p.*, a.agency_name 
                      FROM programs p 
                      LEFT JOIN agency a ON p.agency_id = a.agency_id
                      WHERE p.program_id = ? AND p.is_deleted = 0";
    $stmt = $conn->prepare($program_query);
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $program = $stmt->get_result()->fetch_assoc();

    if (!$program) {
        throw new Exception('Program not found.');
    }

    // Prepare program data for update
    $program_data = [
        'program_id' => $program_id,
        'program_name' => $_POST['program_name'] ?? '',
        'program_number' => $_POST['program_number'] ?? '',
        'brief_description' => $_POST['description'] ?? '',
        'start_date' => $_POST['start_date'] ?? '',
        'end_date' => $_POST['end_date'] ?? '',
        'initiative_id' => $_POST['initiative_id'] ?? '',
        'rating' => $_POST['rating'] ?? 'not_started'
    ];

    // Update agency assignment if provided
    if (isset($_POST['agency_id']) && !empty($_POST['agency_id'])) {
        $agency_id = intval($_POST['agency_id']);
        
        // Verify agency exists
        $agency_query = "SELECT agency_id FROM agency WHERE agency_id = ?";
        $stmt = $conn->prepare($agency_query);
        $stmt->bind_param("i", $agency_id);
        $stmt->execute();
        $agency = $stmt->get_result()->fetch_assoc();
        
        if (!$agency) {
            throw new Exception('Selected agency not found.');
        }
        
        // Update program's agency assignment
        $update_agency_query = "UPDATE programs SET agency_id = ? WHERE program_id = ?";
        $stmt = $conn->prepare($update_agency_query);
        $stmt->bind_param("ii", $agency_id, $program_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update agency assignment: ' . $conn->error);
        }
    }

    // Update program using the existing function
    $result = update_admin_program($program_data);
    
    if (!$result['success']) {
        throw new Exception($result['error']);
    }

    // Handle file uploads if any
    if (isset($_FILES['attachments']) && !empty($_FILES['attachments']['name'][0])) {
        // Process file uploads here if needed
        // This would involve saving files and updating program_attachments table
    }

    $conn->commit();
    
    // Set success message
    $_SESSION['message'] = 'Program updated successfully.';
    $_SESSION['message_type'] = 'success';
    
    // Redirect to the programs list page
    header('Location: ' . APP_URL . '/app/views/admin/programs/programs.php');
    exit;

} catch (Exception $e) {
    $conn->rollback();
    
    // Log the error
    error_log("Admin program save failed for program {$program_id}: " . $e->getMessage());
    
    // Set error message
    $_SESSION['message'] = 'Failed to update program: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    
    // Redirect to the programs list page
    header('Location: ' . APP_URL . '/app/views/admin/programs/programs.php');
    exit;
}
?>
