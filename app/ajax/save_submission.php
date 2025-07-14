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

    // Handle deleted attachments
    if (!empty($_POST['deleted_attachments'])) {
        $deleted_attachments = json_decode($_POST['deleted_attachments'], true);
        if (is_array($deleted_attachments) && $is_update) {
            foreach ($deleted_attachments as $attachment_id) {
                // Get file path
                $stmt = $conn->prepare("SELECT file_path FROM program_attachments WHERE attachment_id = ? AND submission_id = ?");
                $stmt->bind_param("ii", $attachment_id, $submission_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                if ($row && !empty($row['file_path']) && file_exists($row['file_path'])) {
                    @unlink($row['file_path']);
                }
                // Delete DB record
                $stmt = $conn->prepare("DELETE FROM program_attachments WHERE attachment_id = ? AND submission_id = ?");
                $stmt->bind_param("ii", $attachment_id, $submission_id);
                $stmt->execute();
            }
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
                // Validate and normalize target status
                $target_status = trim($target['target_status'] ?? 'not_started');
                $valid_statuses = ['not_started', 'in_progress', 'completed', 'delayed'];
                if (!in_array($target_status, $valid_statuses)) {
                    $target_status = 'not_started'; // Default to not_started if invalid
                }
                
                $targets[] = [
                    'target_id' => isset($target['target_id']) && $target['target_id'] !== '' ? intval($target['target_id']) : null,
                    'target_number' => trim($target['target_number'] ?? ''),
                    'target_text' => trim($target['target_text'] ?? ''),
                    'target_status' => $target_status,
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
    if (isset($_POST['finalize_submission']) && $_POST['finalize_submission'] == '1') {
        $is_draft = false;
        $is_submitted = true;
    }

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
            // Update submission status (is_draft, is_submitted) when editing
            $update_status_query = "UPDATE program_submissions SET is_draft = ?, is_submitted = ?, updated_at = NOW() WHERE submission_id = ?";
            $stmt = $conn->prepare($update_status_query);
            $stmt->bind_param("iii", $is_draft, $is_submitted, $submission_id);
            $stmt->execute();
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
                // Debug logging
                error_log("Processing target: target_id=" . ($target['target_id'] ?? 'null') . ", target_number=" . ($target['target_number'] ?? 'null'));
                
                if (!empty($target['target_id']) && isset($existing_targets_map[intval($target['target_id'])])) {
                    $existing_target = $existing_targets_map[intval($target['target_id'])];
                    error_log("Found existing target: target_id=" . $existing_target['target_id']);
                    
                    // Compare all fields
                    $changed = false;
                    $field_changes = [];
                    foreach ([
                        'target_number', 'target_description', 'status_indicator',
                        'status_description', 'remarks', 'start_date', 'end_date'
                    ] as $field) {
                        $old_val = $existing_target[$field] ?? null;
                        $new_val = null;
                        
                        // Map form field names to database field names
                        if ($field === 'target_description') {
                            $new_val = $target['target_text'] ?? null;
                        } elseif ($field === 'status_indicator') {
                            $new_val = $target['target_status'] ?? null;
                        } else {
                            $new_val = $target[$field] ?? null;
                        }
                        
                        // Normalize values for comparison
                        $old_val = $old_val !== null ? trim($old_val) : null;
                        $new_val = $new_val !== null ? trim($new_val) : null;
                        
                        if ($old_val !== $new_val) {
                            $changed = true;
                            $field_changes[] = "$field: '$old_val' -> '$new_val'";
                        }
                    }
                    
                    error_log("Target comparison result: changed=" . ($changed ? 'true' : 'false') . ", changes: " . implode(', ', $field_changes));
                    
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
                    $matched_existing_ids[] = intval($target['target_id']);
                } else {
                    error_log("Target not found in existing targets, treating as new: target_id=" . ($target['target_id'] ?? 'null'));
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
            // Set message based on status
            if (!$is_draft) {
                $message = 'Submission finalized.';
            } else {
                $message = 'Submission saved as draft.';
            }
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
            
            // Set message based on status
            if (!$is_draft) {
                $message = 'Submission finalized.';
            } else {
                $message = 'Submission saved as draft.';
            }
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
                        // Prevent duplicate uploads: check if file already exists for this submission
                        $dup_check_query = "SELECT COUNT(*) as cnt FROM program_attachments WHERE submission_id = ? AND file_name = ? AND is_deleted = 0";
                        $dup_stmt = $conn->prepare($dup_check_query);
                        $dup_stmt->bind_param("is", $submission_id, $original_name);
                        $dup_stmt->execute();
                        $dup_result = $dup_stmt->get_result();
                        $dup_row = $dup_result->fetch_assoc();
                        if ($dup_row['cnt'] == 0) {
                            // Save attachment record
                            $attachment_query = "INSERT INTO program_attachments 
                                               (submission_id, file_name, file_path, file_size, file_type, uploaded_by, uploaded_at) 
                                               VALUES (?, ?, ?, ?, ?, ?, NOW())";
                            $stmt = $conn->prepare($attachment_query);
                            $stmt->bind_param("issssi", $submission_id, $original_name, $file_path, $file_size, $file_type, $_SESSION['user_id']);
                            $stmt->execute();
                        }
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
    
    $target_id = $existing_target['target_id'];
    $has_changes = false;
    
    // Debug logging
    error_log("logTargetFieldChanges: target_id = {$target_id}, audit_log_id = {$audit_log_id}");
    
    foreach ($fields_to_check as $field => $type) {
        $old_value = $existing_target[$field] ?? null;
        $new_value = $new_target[$field] ?? null;
        
        // Handle field name mapping
        if ($field === 'target_description') {
            $new_value = $new_target['target_text'] ?? null;
        } elseif ($field === 'status_indicator') {
            $new_value = $new_target['target_status'] ?? null;
        }
        
        // Normalize values for comparison (trim whitespace, handle nulls)
        $old_value = $old_value !== null ? trim($old_value) : null;
        $new_value = $new_value !== null ? trim($new_value) : null;
        
        // Debug logging for field comparison
        error_log("Field {$field}: old='{$old_value}' vs new='{$new_value}'");
        
        if ($old_value !== $new_value) {
            $has_changes = true;
            error_log("Change detected for field {$field}");
            
            $insert_query = "INSERT INTO audit_field_changes 
                           (audit_log_id, target_id, field_name, field_type, old_value, new_value, change_type) 
                           VALUES (?, ?, ?, ?, ?, ?, 'modified')";
            
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iissss", $audit_log_id, $target_id, $field, $type, $old_value, $new_value);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                error_log("Successfully logged change for field {$field}");
            } else {
                error_log("Failed to log change for field {$field}");
            }
        }
    }
    
    // If no individual field changes were detected, log a general modification
    if (!$has_changes) {
        error_log("No specific field changes detected, logging general modification");
        $insert_query = "INSERT INTO audit_field_changes 
                       (audit_log_id, target_id, field_name, field_type, old_value, new_value, change_type) 
                       VALUES (?, ?, 'target_modified', 'text', NULL, NULL, 'modified')";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $audit_log_id, $target_id);
        $stmt->execute();
    }
}

/**
 * Log target addition
 */
function logTargetAddition($conn, $audit_log_id, $target) {
    // Get the target_id that was just inserted
    $target_id = $conn->insert_id;
    
    error_log("logTargetAddition: target_id = {$target_id}, audit_log_id = {$audit_log_id}");
    
    $fields_to_log = [
        'target_number' => 'text',
        'target_description' => 'text',
        'status_indicator' => 'text', 
        'status_description' => 'text',
        'remarks' => 'text',
        'start_date' => 'date',
        'end_date' => 'date'
    ];
    
    $has_fields = false;
    
    foreach ($fields_to_log as $field => $type) {
        $value = $target[$field] ?? null;
        
        // Handle field name mapping
        if ($field === 'target_description') {
            $value = $target['target_text'] ?? null;
        } elseif ($field === 'status_indicator') {
            $value = $target['target_status'] ?? null;
        }
        
        if ($value !== null && trim($value) !== '') {
            $has_fields = true;
            error_log("Logging addition for field {$field} with value '{$value}'");
            
            $insert_query = "INSERT INTO audit_field_changes 
                           (audit_log_id, target_id, field_name, field_type, old_value, new_value, change_type) 
                           VALUES (?, ?, ?, ?, NULL, ?, 'added')";
            
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iisss", $audit_log_id, $target_id, $field, $type, $value);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                error_log("Successfully logged addition for field {$field}");
            } else {
                error_log("Failed to log addition for field {$field}");
            }
        }
    }
    
    // If no specific fields were logged, log a general addition
    if (!$has_fields) {
        error_log("No specific fields to log, logging general addition");
        $insert_query = "INSERT INTO audit_field_changes 
                       (audit_log_id, target_id, field_name, field_type, old_value, new_value, change_type) 
                       VALUES (?, ?, 'target_added', 'text', NULL, NULL, 'added')";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $audit_log_id, $target_id);
        $stmt->execute();
    }
}

/**
 * Log target removal
 */
function logTargetRemoval($conn, $audit_log_id, $target) {
    $target_id = $target['target_id'];
    
    $fields_to_log = [
        'target_number' => 'text',
        'target_description' => 'text',
        'status_indicator' => 'text',
        'status_description' => 'text', 
        'remarks' => 'text',
        'start_date' => 'date',
        'end_date' => 'date'
    ];
    
    $has_fields = false;
    
    foreach ($fields_to_log as $field => $type) {
        $value = $target[$field] ?? null;
        if ($value !== null && trim($value) !== '') {
            $has_fields = true;
            $insert_query = "INSERT INTO audit_field_changes 
                           (audit_log_id, target_id, field_name, field_type, old_value, new_value, change_type) 
                           VALUES (?, ?, ?, ?, ?, NULL, 'removed')";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iisss", $audit_log_id, $target_id, $field, $type, $value);
            $stmt->execute();
        }
    }
    
    // If no specific fields were logged, log a general removal
    if (!$has_fields) {
        $insert_query = "INSERT INTO audit_field_changes 
                       (audit_log_id, target_id, field_name, field_type, old_value, new_value, change_type) 
                       VALUES (?, ?, 'target_removed', 'text', NULL, NULL, 'removed')";
        
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("ii", $audit_log_id, $target_id);
        $stmt->execute();
    }
}
?> 