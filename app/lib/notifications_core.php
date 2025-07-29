<?php
/**
 * Core Notification System
 * 
 * Centralized notification trigger functions for program and submission actions.
 * Integrates with the existing audit log system and notification infrastructure.
 */

// Define PROJECT_ROOT_PATH if not already defined
if (!defined('PROJECT_ROOT_PATH')) {
    // Use absolute path resolution that works regardless of working directory
    $current_file = __FILE__;
    $project_root = dirname(dirname(dirname($current_file))); // Go up 3 levels: lib -> app -> pcds2030_dashboard_fork
    define('PROJECT_ROOT_PATH', $project_root . DIRECTORY_SEPARATOR);
}

require_once PROJECT_ROOT_PATH . 'app' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.php';
require_once PROJECT_ROOT_PATH . 'app' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'db_connect.php';
require_once PROJECT_ROOT_PATH . 'app' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'functions.php';
require_once PROJECT_ROOT_PATH . 'app' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'agencies' . DIRECTORY_SEPARATOR . 'notifications.php';
require_once PROJECT_ROOT_PATH . 'app' . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'audit_log.php';

/**
 * Notify when a program is created
 * @param int $program_id Program ID that was created
 * @param int $creator_user_id User who created the program
 * @param array $program_data Program information
 * @return bool Success status
 */
function notify_program_created($program_id, $creator_user_id, $program_data) {
    global $conn;
    
    if (!$program_id || !$creator_user_id) {
        error_log("notify_program_created: Missing required parameters");
        return false;
    }
    
    try {
        // Get program details and agency information
        $program_query = "SELECT p.program_name, p.agency_id, a.agency_name 
                         FROM programs p 
                         JOIN agency a ON p.agency_id = a.agency_id 
                         WHERE p.program_id = ?";
        $stmt = $conn->prepare($program_query);
        $stmt->bind_param('i', $program_id);
        $stmt->execute();
        $program = $stmt->get_result()->fetch_assoc();
        
        if (!$program) {
            error_log("notify_program_created: Program not found: $program_id");
            return false;
        }
        
        // Get creator information
        $creator_query = "SELECT username, fullname FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($creator_query);
        $stmt->bind_param('i', $creator_user_id);
        $stmt->execute();
        $creator = $stmt->get_result()->fetch_assoc();
        
        $creator_name = $creator['fullname'] ?? $creator['username'] ?? 'Unknown User';
        
        // Notify agency users (excluding the creator)
        $agency_users_query = "SELECT user_id FROM users WHERE agency_id = ? AND user_id != ? AND is_active = 1";
        $stmt = $conn->prepare($agency_users_query);
        $stmt->bind_param('ii', $program['agency_id'], $creator_user_id);
        $stmt->execute();
        $agency_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $message = "New program '{$program['program_name']}' created by {$creator_name}";
        $action_url = "/index.php?page=agency_program_details&id={$program_id}";
        
        foreach ($agency_users as $user) {
            create_notification($user['user_id'], $message, 'program_created', $action_url);
        }
        
        // Notify all admin users
        $admin_users_query = "SELECT user_id FROM users WHERE role = 'admin' AND is_active = 1";
        $stmt = $conn->prepare($admin_users_query);
        $stmt->execute();
        $admin_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $admin_message = "New program '{$program['program_name']}' created by {$creator_name} ({$program['agency_name']})";
        $admin_action_url = "/index.php?page=admin_program_details&id={$program_id}";
        
        foreach ($admin_users as $user) {
            create_notification($user['user_id'], $admin_message, 'program_created', $admin_action_url);
        }
        
        // Log the notification action
        log_audit_action('notifications_sent', "Program creation notifications sent for program ID: $program_id", 'success', $creator_user_id);
        
        return true;
        
    } catch (Exception $e) {
        error_log("notify_program_created error: " . $e->getMessage());
        return false;
    }
}

/**
 * Notify when a program is edited
 * @param int $program_id Program ID that was edited
 * @param int $editor_user_id User who edited the program
 * @param array $changes Array of changes made
 * @return bool Success status
 */
function notify_program_edited($program_id, $editor_user_id, $changes = []) {
    global $conn;
    
    if (!$program_id || !$editor_user_id) {
        error_log("notify_program_edited: Missing required parameters");
        return false;
    }
    
    try {
        // Get program details
        $program_query = "SELECT p.program_name, p.agency_id, a.agency_name 
                         FROM programs p 
                         JOIN agency a ON p.agency_id = a.agency_id 
                         WHERE p.program_id = ?";
        $stmt = $conn->prepare($program_query);
        $stmt->bind_param('i', $program_id);
        $stmt->execute();
        $program = $stmt->get_result()->fetch_assoc();
        
        if (!$program) {
            error_log("notify_program_edited: Program not found: $program_id");
            return false;
        }
        
        // Get editor information
        $editor_query = "SELECT username, fullname FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($editor_query);
        $stmt->bind_param('i', $editor_user_id);
        $stmt->execute();
        $editor = $stmt->get_result()->fetch_assoc();
        
        $editor_name = $editor['fullname'] ?? $editor['username'] ?? 'Unknown User';
        
        // Get program editors and viewers (excluding the editor)
        $assigned_users_query = "SELECT DISTINCT user_id FROM program_user_assignments 
                                WHERE program_id = ? AND user_id != ? AND assignment_type IN ('editor', 'viewer')";
        $stmt = $conn->prepare($assigned_users_query);
        $stmt->bind_param('ii', $program_id, $editor_user_id);
        $stmt->execute();
        $assigned_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $changes_summary = empty($changes) ? '' : ' (Changes: ' . implode(', ', array_keys($changes)) . ')';
        $message = "Program '{$program['program_name']}' updated by {$editor_name}{$changes_summary}";
        $action_url = "/index.php?page=agency_program_details&id={$program_id}";
        
        foreach ($assigned_users as $user) {
            create_notification($user['user_id'], $message, 'program_edited', $action_url);
        }
        
        // Notify admin users
        $admin_users_query = "SELECT user_id FROM users WHERE role = 'admin' AND status = 'active'";
        $stmt = $conn->prepare($admin_users_query);
        $stmt->execute();
        $admin_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $admin_message = "Program '{$program['program_name']}' updated by {$editor_name} ({$program['agency_name']}){$changes_summary}";
        $admin_action_url = "/index.php?page=admin_program_details&id={$program_id}";
        
        foreach ($admin_users as $user) {
            create_notification($user['user_id'], $admin_message, 'program_edited', $admin_action_url);
        }
        
        // Log the notification action
        log_audit_action('notifications_sent', "Program edit notifications sent for program ID: $program_id", 'success', $editor_user_id);
        
        return true;
        
    } catch (Exception $e) {
        error_log("notify_program_edited error: " . $e->getMessage());
        return false;
    }
}

/**
 * Notify when a program is deleted
 * @param int $program_id Program ID that was deleted
 * @param int $deleter_user_id User who deleted the program
 * @param array $program_data Program information before deletion
 * @return bool Success status
 */
function notify_program_deleted($program_id, $deleter_user_id, $program_data) {
    global $conn;
    
    if (!$program_id || !$deleter_user_id || !$program_data) {
        error_log("notify_program_deleted: Missing required parameters");
        return false;
    }
    
    try {
        // Get deleter information
        $deleter_query = "SELECT username, fullname FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($deleter_query);
        $stmt->bind_param('i', $deleter_user_id);
        $stmt->execute();
        $deleter = $stmt->get_result()->fetch_assoc();
        
        $deleter_name = $deleter['fullname'] ?? $deleter['username'] ?? 'Unknown User';
        
        // Get agency users (excluding the deleter)
        $agency_users_query = "SELECT user_id FROM users WHERE agency_id = ? AND user_id != ? AND status = 'active'";
        $stmt = $conn->prepare($agency_users_query);
        $stmt->bind_param('ii', $program_data['agency_id'], $deleter_user_id);
        $stmt->execute();
        $agency_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $message = "Program '{$program_data['program_name']}' has been deleted by {$deleter_name}";
        
        foreach ($agency_users as $user) {
            create_notification($user['user_id'], $message, 'program_deleted', null);
        }
        
        // Notify admin users
        $admin_users_query = "SELECT user_id FROM users WHERE role = 'admin' AND status = 'active'";
        $stmt = $conn->prepare($admin_users_query);
        $stmt->execute();
        $admin_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $admin_message = "Program '{$program_data['program_name']}' deleted by {$deleter_name} ({$program_data['agency_name']})";
        
        foreach ($admin_users as $user) {
            create_notification($user['user_id'], $admin_message, 'program_deleted', null);
        }
        
        // Log the notification action
        log_audit_action('notifications_sent', "Program deletion notifications sent for program ID: $program_id", 'success', $deleter_user_id);
        
        return true;
        
    } catch (Exception $e) {
        error_log("notify_program_deleted error: " . $e->getMessage());
        return false;
    }
}

/**
 * Notify when a submission is created
 * @param int $submission_id Submission ID that was created
 * @param int $program_id Program ID the submission belongs to
 * @param int $creator_user_id User who created the submission
 * @param int $period_id Reporting period ID
 * @return bool Success status
 */
function notify_submission_created($submission_id, $program_id, $creator_user_id, $period_id) {
    global $conn;
    
    if (!$submission_id || !$program_id || !$creator_user_id || !$period_id) {
        error_log("notify_submission_created: Missing required parameters");
        return false;
    }
    
    try {
        // Get submission and program details
        $details_query = "SELECT p.program_name, p.agency_id, a.agency_name, rp.period_type, rp.year
                         FROM programs p 
                         JOIN agency a ON p.agency_id = a.agency_id
                         JOIN program_submissions ps ON p.program_id = ps.program_id
                         JOIN reporting_periods rp ON ps.period_id = rp.period_id
                         WHERE p.program_id = ? AND ps.submission_id = ?";
        $stmt = $conn->prepare($details_query);
        $stmt->bind_param('ii', $program_id, $submission_id);
        $stmt->execute();
        $details = $stmt->get_result()->fetch_assoc();
        
        if (!$details) {
            error_log("notify_submission_created: Submission or program not found");
            return false;
        }
        
        // Get creator information
        $creator_query = "SELECT username, fullname FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($creator_query);
        $stmt->bind_param('i', $creator_user_id);
        $stmt->execute();
        $creator = $stmt->get_result()->fetch_assoc();
        
        $creator_name = $creator['fullname'] ?? $creator['username'] ?? 'Unknown User';
        
        // Get program editors and viewers (excluding creator)
        $assigned_users_query = "SELECT DISTINCT user_id FROM program_user_assignments 
                                WHERE program_id = ? AND user_id != ? AND assignment_type IN ('editor', 'viewer')";
        $stmt = $conn->prepare($assigned_users_query);
        $stmt->bind_param('ii', $program_id, $creator_user_id);
        $stmt->execute();
        $assigned_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $period_text = $details['period_type'] . ' ' . $details['year'];
        $message = "New submission created for '{$details['program_name']}' ({$period_text}) by {$creator_name}";
        $action_url = "/index.php?page=agency_edit_submission&program_id={$program_id}&submission_id={$submission_id}";
        
        foreach ($assigned_users as $user) {
            create_notification($user['user_id'], $message, 'submission_created', $action_url);
        }
        
        // Notify focal users of the agency
        $focal_users_query = "SELECT user_id FROM users WHERE agency_id = ? AND role = 'focal' AND user_id != ? AND status = 'active'";
        $stmt = $conn->prepare($focal_users_query);
        $stmt->bind_param('ii', $details['agency_id'], $creator_user_id);
        $stmt->execute();
        $focal_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        foreach ($focal_users as $user) {
            create_notification($user['user_id'], $message, 'submission_created', $action_url);
        }
        
        // Notify admin users
        $admin_users_query = "SELECT user_id FROM users WHERE role = 'admin' AND status = 'active'";
        $stmt = $conn->prepare($admin_users_query);
        $stmt->execute();
        $admin_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $admin_message = "New submission created for '{$details['program_name']}' ({$period_text}) by {$creator_name} ({$details['agency_name']})";
        $admin_action_url = "/index.php?page=admin_edit_submission&program_id={$program_id}&submission_id={$submission_id}";
        
        foreach ($admin_users as $user) {
            create_notification($user['user_id'], $admin_message, 'submission_created', $admin_action_url);
        }
        
        // Log the notification action
        log_audit_action('notifications_sent', "Submission creation notifications sent for submission ID: $submission_id", 'success', $creator_user_id);
        
        return true;
        
    } catch (Exception $e) {
        error_log("notify_submission_created error: " . $e->getMessage());
        return false;
    }
}

/**
 * Notify when a submission is edited
 * @param int $submission_id Submission ID that was edited
 * @param int $program_id Program ID the submission belongs to
 * @param int $editor_user_id User who edited the submission
 * @param array $changes Array of changes made
 * @return bool Success status
 */
function notify_submission_edited($submission_id, $program_id, $editor_user_id, $changes = []) {
    global $conn;
    
    if (!$submission_id || !$program_id || !$editor_user_id) {
        error_log("notify_submission_edited: Missing required parameters");
        return false;
    }
    
    try {
        // Get submission and program details
        $details_query = "SELECT p.program_name, p.agency_id, a.agency_name, rp.period_type, rp.year, ps.is_draft
                         FROM programs p 
                         JOIN agency a ON p.agency_id = a.agency_id
                         JOIN program_submissions ps ON p.program_id = ps.program_id
                         JOIN reporting_periods rp ON ps.period_id = rp.period_id
                         WHERE p.program_id = ? AND ps.submission_id = ?";
        $stmt = $conn->prepare($details_query);
        $stmt->bind_param('ii', $program_id, $submission_id);
        $stmt->execute();
        $details = $stmt->get_result()->fetch_assoc();
        
        if (!$details) {
            error_log("notify_submission_edited: Submission or program not found");
            return false;
        }
        
        // Get editor information
        $editor_query = "SELECT username, fullname FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($editor_query);
        $stmt->bind_param('i', $editor_user_id);
        $stmt->execute();
        $editor = $stmt->get_result()->fetch_assoc();
        
        $editor_name = $editor['fullname'] ?? $editor['username'] ?? 'Unknown User';
        
        // Get program editors and viewers (excluding editor)
        $assigned_users_query = "SELECT DISTINCT user_id FROM program_user_assignments 
                                WHERE program_id = ? AND user_id != ? AND assignment_type IN ('editor', 'viewer')";
        $stmt = $conn->prepare($assigned_users_query);
        $stmt->bind_param('ii', $program_id, $editor_user_id);
        $stmt->execute();
        $assigned_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $period_text = $details['period_type'] . ' ' . $details['year'];
        $status_text = $details['is_draft'] ? '(Draft)' : '(Finalized)';
        $changes_summary = empty($changes) ? '' : ' - Changes: ' . implode(', ', array_keys($changes));
        
        $message = "Submission updated for '{$details['program_name']}' ({$period_text}) {$status_text} by {$editor_name}{$changes_summary}";
        $action_url = "/index.php?page=agency_edit_submission&program_id={$program_id}&submission_id={$submission_id}";
        
        foreach ($assigned_users as $user) {
            create_notification($user['user_id'], $message, 'submission_edited', $action_url);
        }
        
        // Notify focal users if submission is finalized
        if (!$details['is_draft']) {
            $focal_users_query = "SELECT user_id FROM users WHERE agency_id = ? AND role = 'focal' AND user_id != ? AND status = 'active'";
            $stmt = $conn->prepare($focal_users_query);
            $stmt->bind_param('ii', $details['agency_id'], $editor_user_id);
            $stmt->execute();
            $focal_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            foreach ($focal_users as $user) {
                create_notification($user['user_id'], $message, 'submission_edited', $action_url);
            }
        }
        
        // Notify admin users
        $admin_users_query = "SELECT user_id FROM users WHERE role = 'admin' AND status = 'active'";
        $stmt = $conn->prepare($admin_users_query);
        $stmt->execute();
        $admin_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $admin_message = "Submission updated for '{$details['program_name']}' ({$period_text}) {$status_text} by {$editor_name} ({$details['agency_name']}){$changes_summary}";
        $admin_action_url = "/index.php?page=admin_edit_submission&program_id={$program_id}&submission_id={$submission_id}";
        
        foreach ($admin_users as $user) {
            create_notification($user['user_id'], $admin_message, 'submission_edited', $admin_action_url);
        }
        
        // Log the notification action
        log_audit_action('notifications_sent', "Submission edit notifications sent for submission ID: $submission_id", 'success', $editor_user_id);
        
        return true;
        
    } catch (Exception $e) {
        error_log("notify_submission_edited error: " . $e->getMessage());
        return false;
    }
}

/**
 * Notify when a submission is deleted
 * @param int $submission_id Submission ID that was deleted
 * @param int $program_id Program ID the submission belonged to
 * @param int $deleter_user_id User who deleted the submission
 * @param array $submission_data Submission information before deletion
 * @return bool Success status
 */
function notify_submission_deleted($submission_id, $program_id, $deleter_user_id, $submission_data) {
    global $conn;
    
    if (!$submission_id || !$program_id || !$deleter_user_id || !$submission_data) {
        error_log("notify_submission_deleted: Missing required parameters");
        return false;
    }
    
    try {
        // Get deleter information
        $deleter_query = "SELECT username, fullname FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($deleter_query);
        $stmt->bind_param('i', $deleter_user_id);
        $stmt->execute();
        $deleter = $stmt->get_result()->fetch_assoc();
        
        $deleter_name = $deleter['fullname'] ?? $deleter['username'] ?? 'Unknown User';
        
        // Get program editors and viewers (excluding deleter)
        $assigned_users_query = "SELECT DISTINCT user_id FROM program_user_assignments 
                                WHERE program_id = ? AND user_id != ? AND assignment_type IN ('editor', 'viewer')";
        $stmt = $conn->prepare($assigned_users_query);
        $stmt->bind_param('ii', $program_id, $deleter_user_id);
        $stmt->execute();
        $assigned_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $message = "Submission for '{$submission_data['program_name']}' ({$submission_data['period_text']}) has been deleted by {$deleter_name}";
        
        foreach ($assigned_users as $user) {
            create_notification($user['user_id'], $message, 'submission_deleted', null);
        }
        
        // Notify focal users
        $focal_users_query = "SELECT user_id FROM users WHERE agency_id = ? AND role = 'focal' AND user_id != ? AND status = 'active'";
        $stmt = $conn->prepare($focal_users_query);
        $stmt->bind_param('ii', $submission_data['agency_id'], $deleter_user_id);
        $stmt->execute();
        $focal_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        foreach ($focal_users as $user) {
            create_notification($user['user_id'], $message, 'submission_deleted', null);
        }
        
        // Notify admin users
        $admin_users_query = "SELECT user_id FROM users WHERE role = 'admin' AND status = 'active'";
        $stmt = $conn->prepare($admin_users_query);
        $stmt->execute();
        $admin_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $admin_message = "Submission for '{$submission_data['program_name']}' ({$submission_data['period_text']}) deleted by {$deleter_name} ({$submission_data['agency_name']})";
        
        foreach ($admin_users as $user) {
            create_notification($user['user_id'], $admin_message, 'submission_deleted', null);
        }
        
        // Log the notification action
        log_audit_action('notifications_sent', "Submission deletion notifications sent for submission ID: $submission_id", 'success', $deleter_user_id);
        
        return true;
        
    } catch (Exception $e) {
        error_log("notify_submission_deleted error: " . $e->getMessage());
        return false;
    }
}

/**
 * Notify when a submission is finalized
 * @param int $submission_id Submission ID that was finalized
 * @param int $program_id Program ID the submission belongs to
 * @param int $finalizer_user_id User who finalized the submission
 * @return bool Success status
 */
function notify_submission_finalized($submission_id, $program_id, $finalizer_user_id) {
    global $conn;
    
    if (!$submission_id || !$program_id || !$finalizer_user_id) {
        error_log("notify_submission_finalized: Missing required parameters");
        return false;
    }
    
    try {
        // Get submission and program details
        $details_query = "SELECT p.program_name, p.agency_id, a.agency_name, rp.period_type, rp.year
                         FROM programs p 
                         JOIN agency a ON p.agency_id = a.agency_id
                         JOIN program_submissions ps ON p.program_id = ps.program_id
                         JOIN reporting_periods rp ON ps.period_id = rp.period_id
                         WHERE p.program_id = ? AND ps.submission_id = ?";
        $stmt = $conn->prepare($details_query);
        $stmt->bind_param('ii', $program_id, $submission_id);
        $stmt->execute();
        $details = $stmt->get_result()->fetch_assoc();
        
        if (!$details) {
            error_log("notify_submission_finalized: Submission or program not found");
            return false;
        }
        
        // Get finalizer information
        $finalizer_query = "SELECT username, fullname FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($finalizer_query);
        $stmt->bind_param('i', $finalizer_user_id);
        $stmt->execute();
        $finalizer = $stmt->get_result()->fetch_assoc();
        
        $finalizer_name = $finalizer['fullname'] ?? $finalizer['username'] ?? 'Unknown User';
        
        // Get all agency users (excluding finalizer)
        $agency_users_query = "SELECT user_id FROM users WHERE agency_id = ? AND user_id != ? AND status = 'active'";
        $stmt = $conn->prepare($agency_users_query);
        $stmt->bind_param('ii', $details['agency_id'], $finalizer_user_id);
        $stmt->execute();
        $agency_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $period_text = $details['period_type'] . ' ' . $details['year'];
        $message = "Submission for '{$details['program_name']}' ({$period_text}) has been finalized by {$finalizer_name}";
        $action_url = "/index.php?page=agency_view_submissions&program_id={$program_id}";
        
        foreach ($agency_users as $user) {
            create_notification($user['user_id'], $message, 'submission_finalized', $action_url);
        }
        
        // Notify admin users - this is important for report generation
        $admin_users_query = "SELECT user_id FROM users WHERE role = 'admin' AND status = 'active'";
        $stmt = $conn->prepare($admin_users_query);
        $stmt->execute();
        $admin_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $admin_message = "Submission for '{$details['program_name']}' ({$period_text}) finalized by {$finalizer_name} ({$details['agency_name']}) - Ready for reporting";
        $admin_action_url = "/index.php?page=admin_view_submissions&program_id={$program_id}";
        
        foreach ($admin_users as $user) {
            create_notification($user['user_id'], $admin_message, 'submission_finalized', $admin_action_url);
        }
        
        // Log the notification action
        log_audit_action('notifications_sent', "Submission finalization notifications sent for submission ID: $submission_id", 'success', $finalizer_user_id);
        
        return true;
        
    } catch (Exception $e) {
        error_log("notify_submission_finalized error: " . $e->getMessage());
        return false;
    }
}

/**
 * Notify when a user is assigned as editor to a program
 * @param int $program_id Program ID
 * @param int $assigned_user_id User who was assigned
 * @param int $assigner_user_id User who made the assignment
 * @param string $assignment_type Type of assignment (editor, viewer)
 * @return bool Success status
 */
function notify_program_assignment($program_id, $assigned_user_id, $assigner_user_id, $assignment_type = 'editor') {
    global $conn;
    
    if (!$program_id || !$assigned_user_id || !$assigner_user_id) {
        error_log("notify_program_assignment: Missing required parameters");
        return false;
    }
    
    try {
        // Get program details
        $program_query = "SELECT p.program_name, p.agency_id, a.agency_name 
                         FROM programs p 
                         JOIN agency a ON p.agency_id = a.agency_id 
                         WHERE p.program_id = ?";
        $stmt = $conn->prepare($program_query);
        $stmt->bind_param('i', $program_id);
        $stmt->execute();
        $program = $stmt->get_result()->fetch_assoc();
        
        if (!$program) {
            error_log("notify_program_assignment: Program not found: $program_id");
            return false;
        }
        
        // Get assigner information
        $assigner_query = "SELECT username, fullname FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($assigner_query);
        $stmt->bind_param('i', $assigner_user_id);
        $stmt->execute();
        $assigner = $stmt->get_result()->fetch_assoc();
        
        $assigner_name = $assigner['fullname'] ?? $assigner['username'] ?? 'Unknown User';
        
        // Notify the assigned user
        $message = "You have been assigned as {$assignment_type} for program '{$program['program_name']}' by {$assigner_name}";
        $action_url = "/index.php?page=agency_program_details&id={$program_id}";
        
        create_notification($assigned_user_id, $message, 'program_assignment', $action_url);
        
        // Notify other editors and viewers of the program (excluding assigned user and assigner)
        $other_users_query = "SELECT DISTINCT user_id FROM program_user_assignments 
                             WHERE program_id = ? AND user_id NOT IN (?, ?) AND assignment_type IN ('editor', 'viewer')";
        $stmt = $conn->prepare($other_users_query);
        $stmt->bind_param('iii', $program_id, $assigned_user_id, $assigner_user_id);
        $stmt->execute();
        $other_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Get assigned user's name for the notification to others
        $assigned_user_query = "SELECT username, fullname FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($assigned_user_query);
        $stmt->bind_param('i', $assigned_user_id);
        $stmt->execute();
        $assigned_user = $stmt->get_result()->fetch_assoc();
        
        $assigned_user_name = $assigned_user['fullname'] ?? $assigned_user['username'] ?? 'Unknown User';
        
        $other_message = "{$assigned_user_name} has been assigned as {$assignment_type} for program '{$program['program_name']}' by {$assigner_name}";
        
        foreach ($other_users as $user) {
            create_notification($user['user_id'], $other_message, 'program_assignment', $action_url);
        }
        
        // Notify admin users
        $admin_users_query = "SELECT user_id FROM users WHERE role = 'admin' AND status = 'active'";
        $stmt = $conn->prepare($admin_users_query);
        $stmt->execute();
        $admin_users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $admin_message = "{$assigned_user_name} assigned as {$assignment_type} for program '{$program['program_name']}' by {$assigner_name} ({$program['agency_name']})";
        $admin_action_url = "/index.php?page=admin_program_details&id={$program_id}";
        
        foreach ($admin_users as $user) {
            create_notification($user['user_id'], $admin_message, 'program_assignment', $admin_action_url);
        }
        
        // Log the notification action
        log_audit_action('notifications_sent', "Program assignment notifications sent for program ID: $program_id", 'success', $assigner_user_id);
        
        return true;
        
    } catch (Exception $e) {
        error_log("notify_program_assignment error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send system-wide notifications to all users
 * @param string $message Notification message
 * @param string $type Notification type
 * @param string|null $action_url Optional action URL
 * @param int|null $sender_user_id User sending the notification (admin only)
 * @return bool Success status
 */
function notify_system_wide($message, $type = 'system', $action_url = null, $sender_user_id = null) {
    global $conn;
    
    if (!$message) {
        error_log("notify_system_wide: Message is required");
        return false;
    }
    
    // Verify sender is admin if specified
    if ($sender_user_id) {
        $sender_query = "SELECT role FROM users WHERE user_id = ?";
        $stmt = $conn->prepare($sender_query);
        $stmt->bind_param('i', $sender_user_id);
        $stmt->execute();
        $sender = $stmt->get_result()->fetch_assoc();
        
        if (!$sender || $sender['role'] !== 'admin') {
            error_log("notify_system_wide: Unauthorized user attempting system-wide notification");
            return false;
        }
    }
    
    try {
        // Get all active users
        $users_query = "SELECT user_id FROM users WHERE status = 'active'";
        $stmt = $conn->prepare($users_query);
        $stmt->execute();
        $users = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $success_count = 0;
        foreach ($users as $user) {
            if (create_notification($user['user_id'], $message, $type, $action_url)) {
                $success_count++;
            }
        }
        
        // Log the notification action
        log_audit_action('system_notifications_sent', "System-wide notification sent to $success_count users", 'success', $sender_user_id);
        
        return $success_count > 0;
        
    } catch (Exception $e) {
        error_log("notify_system_wide error: " . $e->getMessage());
        return false;
    }
}

/**
 * Clean up old notifications (maintenance function)
 * @param int $days_to_keep Number of days to keep notifications
 * @return int Number of notifications deleted
 */
function cleanup_old_notifications($days_to_keep = 30) {
    global $conn;
    
    try {
        $delete_query = "DELETE FROM notifications WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)";
        $stmt = $conn->prepare($delete_query);
        $stmt->bind_param('i', $days_to_keep);
        $stmt->execute();
        
        $deleted_count = $stmt->affected_rows;
        
        // Log the cleanup action
        log_audit_action('notifications_cleanup', "Deleted $deleted_count old notifications (older than $days_to_keep days)", 'success');
        
        return $deleted_count;
        
    } catch (Exception $e) {
        error_log("cleanup_old_notifications error: " . $e->getMessage());
        return 0;
    }
}
?>