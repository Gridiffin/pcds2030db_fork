<?php
/**
 * Save Submission
 * 
 * AJAX endpoint to handle both creating new submissions and updating existing ones.
 * Supports both draft and final submission modes.
 */

// Define project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(__DIR__)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/agencies/programs.php';

// Set JSON header
header('Content-Type: application/json');

// Verify user is an agency
if (!is_agency()) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Agency login required.']);
    exit;
}

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed. Use POST.']);
    exit;
}

// Get and validate input parameters
$program_id = isset($_POST['program_id']) ? intval($_POST['program_id']) : 0;
$period_id = isset($_POST['period_id']) ? intval($_POST['period_id']) : 0;
$submission_id = isset($_POST['submission_id']) ? intval($_POST['submission_id']) : 0;
$is_update = !empty($submission_id);

if (!$program_id || !$period_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Program ID and Period ID are required.']);
    exit;
}

try {
    // Verify program exists and user has access
    $program = get_program_details($program_id);
    if (!$program) {
        http_response_code(404);
        echo json_encode(['error' => 'Program not found or access denied.']);
        exit;
    }

    // Verify period exists
    $period_query = "SELECT * FROM reporting_periods WHERE period_id = ?";
    $stmt = $conn->prepare($period_query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $period = $stmt->get_result()->fetch_assoc();

    if (!$period) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid reporting period.']);
        exit;
    }

    // If updating, verify submission exists and user has access
    if ($is_update) {
        $submission_query = "SELECT * FROM program_submissions 
                           WHERE submission_id = ? AND program_id = ? AND is_deleted = 0";
        $stmt = $conn->prepare($submission_query);
        $stmt->bind_param("ii", $submission_id, $program_id);
        $stmt->execute();
        $existing_submission = $stmt->get_result()->fetch_assoc();

        if (!$existing_submission) {
            http_response_code(404);
            echo json_encode(['error' => 'Submission not found or access denied.']);
            exit;
        }
    }

    // Prepare submission data
    $description = $_POST['description'] ?? '';
    $targets = [];
    
    // Handle targets from JSON
    if (isset($_POST['targets_json'])) {
        $decoded_targets = json_decode($_POST['targets_json'], true);
        if (is_array($decoded_targets)) {
            foreach ($decoded_targets as $target) {
                $targets[] = [
                    'target_id' => isset($target['target_id']) && $target['target_id'] !== '' ? intval($target['target_id']) : null,
                    'target_number' => trim($target['target_number'] ?? ''),
                    'target_text' => trim($target['target_text'] ?? ''),
                    'target_status' => trim($target['target_status'] ?? 'not_started'),
                    'status_description' => trim($target['status_description'] ?? ''),
                    'remarks' => trim($target['remarks'] ?? ''),
                    'start_date' => !empty($target['start_date']) ? $target['start_date'] : null,
                    'end_date' => !empty($target['end_date']) ? $target['end_date'] : null,
                ];
            }
        }
    }

    // Determine submission mode - always save as draft from edit page
    $is_draft = true;
    $is_submitted = false;

    // Start transaction
    $conn->begin_transaction();

    try {
        // After updating/inserting the submission
        // Create audit log entry
        $audit_log_id = null;
        if ($is_update) {
            $audit_log_query = "INSERT INTO audit_logs (user_id, action, details, ip_address, status) 
                               VALUES (?, 'update_submission', ?, ?, 'success')";
            $stmt = $conn->prepare($audit_log_query);
            $details = "Updated submission ID: {$submission_id} for program ID: {$program_id}";
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $stmt->bind_param("iss", $_SESSION['user_id'], $details, $ip_address);
            $stmt->execute();
            $audit_log_id = $conn->insert_id;
        } else {
            $audit_log_query = "INSERT INTO audit_logs (user_id, action, details, ip_address, status) 
                               VALUES (?, 'create_submission', ?, ?, 'success')";
            $stmt = $conn->prepare($audit_log_query);
            $details = "Created new submission ID: {$submission_id} for program ID: {$program_id}";
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $stmt->bind_param("iss", $_SESSION['user_id'], $details, $ip_address);
            $stmt->execute();
            $audit_log_id = $conn->insert_id;
        }

        if (!$audit_log_id) {
            throw new Exception('Failed to create audit log entry.');
        }

        if ($is_update) {
            // Get existing targets for comparison
            $existing_targets_query = "SELECT * FROM program_targets WHERE submission_id = ? AND is_deleted = 0 ORDER BY target_id ASC";
            $stmt = $conn->prepare($existing_targets_query);
            $stmt->bind_param("i", $submission_id);
            $stmt->execute();
            $existing_targets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            // Build a map of existing targets by target_id
            $existing_targets_map = [];
            foreach ($existing_targets as $et) {
                $existing_targets_map[$et['target_id']] = $et;
            }
            $matched_existing_ids = [];
            // Update or insert targets
            foreach ($targets as $target) {
                if (!empty($target['target_id']) && isset($existing_targets_map[$target['target_id']])) {
                    $existing_target = $existing_targets_map[$target['target_id']];
                    // Compare all fields
                    $changed = false;
                    foreach ([
                        'target_number', 'target_description', 'status_indicator',
                        'status_description', 'remarks', 'start_date', 'end_date'
                    ] as $field) {
                        $old_val = $existing_target[$field] ?? null;
                        $new_val = $field === 'target_description' ? $target['target_text'] : $target[$field] ?? null;
                        if ($old_val != $new_val) {
                            $changed = true;
                            break;
                        }
                    }
                    if ($changed) {
                        // Update existing target
                        $update_target_query = "UPDATE program_targets 
                            SET target_number = ?, target_description = ?, status_indicator = ?,
                                status_description = ?, remarks = ?, start_date = ?, end_date = ?
                            WHERE target_id = ?";
                        $stmt = $conn->prepare($update_target_query);
                        $start_date = !empty($target['start_date']) ? $target['start_date'] : null;
                        $end_date = !empty($target['end_date']) ? $target['end_date'] : null;
                        $stmt->bind_param("sssssssi", 
                            $target['target_number'],
                            $target['target_text'],
                            $target['target_status'],
                            $target['status_description'],
                            $target['remarks'],
                            $start_date,
                            $end_date,
                            $existing_target['target_id']
                        );
                        $stmt->execute();
                        if ($audit_log_id) {
                            logTargetFieldChanges($conn, $audit_log_id, $existing_target, $target);
                        } else {
                            error_log('No audit_log_id available for logTargetFieldChanges');
                        }
                    }
                    $matched_existing_ids[] = $target['target_id'];
                } else {
                    // Insert new target
                    $insert_target_query = "INSERT INTO program_targets 
                        (target_number, submission_id, target_description, status_indicator, 
                         status_description, remarks, start_date, end_date) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($insert_target_query);
                    $start_date = !empty($target['start_date']) ? $target['start_date'] : null;
                    $end_date = !empty($target['end_date']) ? $target['end_date'] : null;
                    $stmt->bind_param("sissssss", 
                        $target['target_number'],
                        $submission_id,
                        $target['target_text'],
                        $target['target_status'],
                        $target['status_description'],
                        $target['remarks'],
                        $start_date,
                        $end_date
                    );
                    $stmt->execute();
                    if ($audit_log_id) {
                        logTargetAddition($conn, $audit_log_id, $target);
                    } else {
                        error_log('No audit_log_id available for logTargetAddition');
                    }
                }
            }
            // Remove targets that are no longer in the list
            foreach ($existing_targets as $target_to_remove) {
                if (!in_array($target_to_remove['target_id'], $matched_existing_ids)) {
                    // Double-check the target still exists before logging removal
                    $check_query = "SELECT COUNT(*) as cnt FROM program_targets WHERE target_id = ? AND is_deleted = 0";
                    $check_stmt = $conn->prepare($check_query);
                    $check_stmt->bind_param("i", $target_to_remove['target_id']);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result()->fetch_assoc();
                    if ($check_result && $check_result['cnt'] > 0) {
                        if ($audit_log_id) {
                            logTargetRemoval($conn, $audit_log_id, $target_to_remove);
                        } else {
                            error_log('No audit_log_id available for logTargetRemoval');
                        }
                    }
                    $delete_target_query = "DELETE FROM program_targets WHERE target_id = ?";
                    $stmt = $conn->prepare($delete_target_query);
                    $stmt->bind_param("i", $target_to_remove['target_id']);
                    $stmt->execute();
                }
            }
            $message = 'Submission updated as draft.';
        } else {
            // Create new submission
            $insert_query = "INSERT INTO program_submissions 
                           (program_id, period_id, description, is_draft, is_submitted, 
                            submitted_by, submitted_at, updated_at) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            
            $submitted_at = $is_submitted ? date('Y-m-d H:i:s') : null;
            
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iissiis", $program_id, $period_id, $description, $is_draft, $is_submitted, 
                            $_SESSION['user_id'], $submitted_at);
            $stmt->execute();

            $submission_id = $conn->insert_id;
            
            if (!$submission_id) {
                throw new Exception('Failed to create submission.');
            }

            // Log submission creation
            $audit_log_query = "INSERT INTO audit_logs (user_id, action, details, ip_address, status) 
                               VALUES (?, 'create_submission', ?, ?, 'success')";
            $stmt = $conn->prepare($audit_log_query);
            $details = "Created new submission ID: {$submission_id} for program ID: {$program_id}";
            $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
            $stmt->bind_param("iss", $_SESSION['user_id'], $details, $ip_address);
            $stmt->execute();
            $audit_log_id = $conn->insert_id;
            
            $message = 'Submission created as draft.';
            // Save targets to program_targets table (ONLY for new submissions)
            if (!empty($targets)) {
                $target_insert_query = "INSERT INTO program_targets 
                                       (target_number, submission_id, target_description, status_indicator, 
                                        status_description, remarks, start_date, end_date) 
                                       VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($target_insert_query);
                foreach ($targets as $target) {
                    $start_date = !empty($target['start_date']) ? $target['start_date'] : null;
                    $end_date = !empty($target['end_date']) ? $target['end_date'] : null;
                    $remarks = $target['remarks'] ?? '';
                    $stmt->bind_param("sissssss", 
                        $target['target_number'],
                        $submission_id,
                        $target['target_text'],
                        $target['target_status'],
                        $target['status_description'],
                        $remarks,
                        $start_date,
                        $end_date
                    );
                    $stmt->execute();
                    // Log target addition for new submissions
                    if ($audit_log_id) {
                        logTargetAddition($conn, $audit_log_id, $target);
                    } else {
                        error_log('No audit_log_id available for logTargetAddition (new submission)');
                    }
                }
            }
        }

        // Handle file uploads if any
        if (!empty($_FILES['attachments']['name'][0])) {
            $upload_dir = '../../uploads/programs/attachments/' . $submission_id . '/';
            
            // Create directory if it doesn't exist
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            foreach ($_FILES['attachments']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['attachments']['error'][$key] === UPLOAD_ERR_OK) {
                    $original_name = $_FILES['attachments']['name'][$key];
                    $file_size = $_FILES['attachments']['size'][$key];
                    $file_type = $_FILES['attachments']['type'][$key];
                    
                    // Generate unique filename
                    $file_extension = pathinfo($original_name, PATHINFO_EXTENSION);
                    $unique_filename = time() . '_' . uniqid() . '.' . $file_extension;
                    $file_path = $upload_dir . $unique_filename;
                    
                    if (move_uploaded_file($tmp_name, $file_path)) {
                        // Save attachment record
                        $attachment_query = "INSERT INTO program_attachments 
                                           (submission_id, original_filename, stored_filename, file_path, 
                                            file_size, mime_type, uploaded_by, upload_date) 
                                           VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
                        
                        $stmt = $conn->prepare($attachment_query);
                        $stmt->bind_param("isssssi", $submission_id, $original_name, $unique_filename, 
                                        $file_path, $file_size, $file_type, $_SESSION['user_id']);
                        $stmt->execute();
                    }
                }
            }
        }

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => $message,
            'submission_id' => $submission_id,
            'is_draft' => $is_draft,
            'is_submitted' => $is_submitted
        ]);

    } catch (Exception $e) {
        // Rollback transaction
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    error_log("Error in save_submission.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}

/**
 * Log field changes for target updates
 */
function logTargetFieldChanges($conn, $audit_log_id, $existing_target, $new_target) {
    $fields_to_check = [
        'target_number' => 'text',
        'target_description' => 'text', 
        'status_indicator' => 'text',
        'status_description' => 'text',
        'remarks' => 'text',
        'start_date' => 'date',
        'end_date' => 'date'
    ];
    
    foreach ($fields_to_check as $field => $type) {
        $old_value = $existing_target[$field] ?? null;
        $new_value = $new_target[$field] ?? null;
        
        // Handle field name mapping
        if ($field === 'target_description') {
            $new_value = $new_target['target_text'] ?? null;
        }
        
        if ($old_value !== $new_value) {
            $insert_query = "INSERT INTO audit_field_changes 
                           (audit_log_id, field_name, field_type, old_value, new_value, change_type) 
                           VALUES (?, ?, ?, ?, ?, 'modified')";
            
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("issss", $audit_log_id, $field, $type, $old_value, $new_value);
            $stmt->execute();
        }
    }
}

/**
 * Log target addition
 */
function logTargetAddition($conn, $audit_log_id, $target) {
    $fields_to_log = [
        'target_number' => 'text',
        'target_description' => 'text',
        'status_indicator' => 'text', 
        'status_description' => 'text',
        'remarks' => 'text',
        'start_date' => 'date',
        'end_date' => 'date'
    ];
    
    foreach ($fields_to_log as $field => $type) {
        $value = $target[$field] ?? null;
        
        // Handle field name mapping
        if ($field === 'target_description') {
            $value = $target['target_text'] ?? null;
        }
        
        if ($value !== null) {
            $insert_query = "INSERT INTO audit_field_changes 
                           (audit_log_id, field_name, field_type, old_value, new_value, change_type) 
                           VALUES (?, ?, ?, NULL, ?, 'added')";
            
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("isss", $audit_log_id, $field, $type, $value);
            $stmt->execute();
        }
    }
}

/**
 * Log target removal
 */
function logTargetRemoval($conn, $audit_log_id, $target) {
    $fields_to_log = [
        'target_number' => 'text',
        'target_description' => 'text',
        'status_indicator' => 'text',
        'status_description' => 'text', 
        'remarks' => 'text',
        'start_date' => 'date',
        'end_date' => 'date'
    ];
    
    foreach ($fields_to_log as $field => $type) {
        $value = $target[$field] ?? null;
        if ($value !== null) {
            $insert_query = "INSERT INTO audit_field_changes 
                           (audit_log_id, field_name, field_type, old_value, new_value, change_type) 
                           VALUES (?, ?, ?, ?, NULL, 'removed')";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("isss", $audit_log_id, $field, $type, $value);
            $stmt->execute();
        }
    }
}
?> 