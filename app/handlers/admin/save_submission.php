<?php
/**
 * Admin Save Submission Handler
 * 
 * Handles saving and updating submissions from the admin interface.
 * Supports both creating new submissions and updating existing ones.
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
$period_id = isset($_POST['period_id']) ? intval($_POST['period_id']) : 0;
$submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : null;
$is_new_submission = isset($_POST['is_new_submission']) && $_POST['is_new_submission'] === '1';

// Validate required parameters
if (!$program_id || !$period_id) {
    $_SESSION['message'] = 'Program ID and Period ID are required.';
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

    // Verify period exists
    $period_query = "SELECT * FROM reporting_periods WHERE period_id = ?";
    $stmt = $conn->prepare($period_query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $period = $stmt->get_result()->fetch_assoc();

    if (!$period) {
        throw new Exception('Invalid reporting period.');
    }

    // Get form data
    $description = $_POST['description'] ?? '';
    $content_json = $_POST['content_json'] ?? '';
    
    // Prepare submission data
    $current_time = date('Y-m-d H:i:s');
    $user_id = $_SESSION['user_id'];

    if ($is_new_submission) {
        // Create new submission (admin creates finalized submissions)
        $insert_query = "INSERT INTO program_submissions 
                         (program_id, period_id, description, content_json, is_draft, is_submitted, 
                          submitted_by, submitted_at, created_at, updated_at) 
                         VALUES (?, ?, ?, ?, 0, 1, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iissssss", 
            $program_id, $period_id, $description, $content_json, 
            $user_id, $current_time, $current_time, $current_time
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create submission: ' . $stmt->error);
        }
        
        $new_submission_id = $conn->insert_id;
        
        // Log the creation
        log_audit_action(
            'admin_submission_created',
            "Admin created new submission for program '{$program['program_name']}' (ID: {$program_id}) for period {$period_id}",
            'success',
            $user_id,
            $new_submission_id
        );
        
        $action_message = 'New submission created successfully.';
        
    } else {
        // Update existing submission
        if (!$submission_id) {
            throw new Exception('Submission ID is required for updates.');
        }
        
        // Verify submission exists
        $check_query = "SELECT * FROM program_submissions 
                        WHERE submission_id = ? AND program_id = ? AND is_deleted = 0";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ii", $submission_id, $program_id);
        $stmt->execute();
        $existing = $stmt->get_result()->fetch_assoc();
        
        if (!$existing) {
            throw new Exception('Submission not found.');
        }
        
        // Update submission
        $update_query = "UPDATE program_submissions 
                         SET description = ?, content_json = ?, updated_at = ?
                         WHERE submission_id = ?";
        
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssi", $description, $content_json, $current_time, $submission_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to update submission: ' . $stmt->error);
        }
        
        // Log the update
        log_audit_action(
            'admin_submission_updated',
            "Admin updated submission for program '{$program['program_name']}' (ID: {$program_id}) for period {$period_id}",
            'success',
            $user_id,
            $submission_id
        );
        
        $action_message = 'Submission updated successfully.';
        $new_submission_id = $submission_id;
    }

    // Handle targets if they exist in the form
    if (isset($_POST['targets']) && is_array($_POST['targets'])) {
        // First, soft delete existing targets for this submission
        if (!$is_new_submission) {
            $delete_targets_query = "UPDATE program_targets SET is_deleted = 1, updated_at = ? 
                                     WHERE submission_id = ?";
            $stmt = $conn->prepare($delete_targets_query);
            $stmt->bind_param("si", $current_time, $new_submission_id);
            $stmt->execute();
        }
        
        // Insert/update targets
        foreach ($_POST['targets'] as $target_data) {
            if (empty($target_data['target_description'])) continue; // Skip empty targets
            
            $target_insert = "INSERT INTO program_targets 
                              (submission_id, target_number, target_description, status_indicator, 
                               status_description, remarks, start_date, end_date, created_at, updated_at) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($target_insert);
            $stmt->bind_param("isssssssss",
                $new_submission_id,
                $target_data['target_number'] ?? '',
                $target_data['target_description'] ?? '',
                $target_data['status_indicator'] ?? 'pending',
                $target_data['status_description'] ?? '',
                $target_data['remarks'] ?? '',
                $target_data['start_date'] ?? null,
                $target_data['end_date'] ?? null,
                $current_time,
                $current_time
            );
            
            if (!$stmt->execute()) {
                throw new Exception('Failed to save target: ' . $stmt->error);
            }
        }
    }

    $conn->commit();
    
    $_SESSION['message'] = $action_message;
    $_SESSION['message_type'] = 'success';
    
    // Redirect back to edit submission or to view submissions
    if (isset($_POST['save_and_continue'])) {
        // Stay on edit page
        header('Location: ' . APP_URL . '/app/views/admin/programs/edit_submission.php?program_id=' . $program_id . '&period_id=' . $period_id);
    } else {
        // Go to view submissions
        header('Location: ' . APP_URL . '/app/views/admin/programs/view_submissions.php?program_id=' . $program_id);
    }
    exit;

} catch (Exception $e) {
    $conn->rollback();
    
    // Log the error
    log_audit_action(
        'admin_submission_save_error',
        "Admin submission save failed for program {$program_id}, period {$period_id}: " . $e->getMessage(),
        'failure',
        $_SESSION['user_id'] ?? null
    );
    
    $_SESSION['message'] = 'Error saving submission: ' . $e->getMessage();
    $_SESSION['message_type'] = 'danger';
    
    // Redirect back to edit form with error
    header('Location: ' . APP_URL . '/app/views/admin/programs/edit_submission.php?program_id=' . $program_id . '&period_id=' . $period_id);
    exit;
}
?>
