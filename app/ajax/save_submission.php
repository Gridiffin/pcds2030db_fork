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

    // Verify period exists and is valid
    $period_query = "SELECT * FROM reporting_periods WHERE period_id = ? AND status != 'closed'";
    $stmt = $conn->prepare($period_query);
    $stmt->bind_param("i", $period_id);
    $stmt->execute();
    $period = $stmt->get_result()->fetch_assoc();

    if (!$period) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid or closed reporting period.']);
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
    
    // Handle targets array data
    if (isset($_POST['target_text']) && is_array($_POST['target_text'])) {
        $target_texts = $_POST['target_text'];
        $target_numbers = $_POST['target_number'] ?? [];
        $target_statuses = $_POST['target_status'] ?? [];
        $target_status_descriptions = $_POST['target_status_description'] ?? [];
        $target_remarks = $_POST['target_remarks'] ?? [];
        $target_start_dates = $_POST['target_start_date'] ?? [];
        $target_end_dates = $_POST['target_end_date'] ?? [];
        
        for ($i = 0; $i < count($target_texts); $i++) {
            $target_text = trim($target_texts[$i] ?? '');
            if (!empty($target_text)) {
                $targets[] = [
                    'target_number' => trim($target_numbers[$i] ?? ''),
                    'target_text' => $target_text,
                    'target_status' => trim($target_statuses[$i] ?? 'not_started'),
                    'status_description' => trim($target_status_descriptions[$i] ?? ''),
                    'remarks' => trim($target_remarks[$i] ?? ''),
                    'start_date' => !empty($target_start_dates[$i]) ? $target_start_dates[$i] : null,
                    'end_date' => !empty($target_end_dates[$i]) ? $target_end_dates[$i] : null,
                ];
            }
        }
    }

    // Determine submission mode
    $is_draft = isset($_POST['save_as_draft']) && $_POST['save_as_draft'] == '1';
    $is_submitted = !$is_draft;

    // Start transaction
    $conn->begin_transaction();

    try {
        if ($is_update) {
            // Update existing submission
            $update_query = "UPDATE program_submissions 
                           SET description = ?, is_draft = ?, is_submitted = ?, 
                               updated_at = NOW(), submitted_at = ?
                           WHERE submission_id = ? AND program_id = ?";
            
            $submitted_at = $is_submitted ? date('Y-m-d H:i:s') : null;
            
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("siissi", $description, $is_draft, $is_submitted, 
                            $submitted_at, $submission_id, $program_id);
            $stmt->execute();

            if ($stmt->affected_rows === 0) {
                throw new Exception('Failed to update submission.');
            }

            // Delete existing targets for this submission
            $delete_targets_query = "UPDATE program_targets SET is_deleted = 1 WHERE submission_id = ?";
            $stmt = $conn->prepare($delete_targets_query);
            $stmt->bind_param("i", $submission_id);
            $stmt->execute();

            $message = $is_draft ? 'Submission updated as draft.' : 'Submission finalized successfully.';
        } else {
            // Check if submission already exists for this program and period
            $check_query = "SELECT submission_id FROM program_submissions 
                           WHERE program_id = ? AND period_id = ? AND is_deleted = 0";
            $stmt = $conn->prepare($check_query);
            $stmt->bind_param("ii", $program_id, $period_id);
            $stmt->execute();
            $existing = $stmt->get_result()->fetch_assoc();

            if ($existing) {
                throw new Exception('A submission already exists for this period. Please edit the existing submission.');
            }

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
            $message = $is_draft ? 'Submission created as draft.' : 'Submission created and finalized.';
        }

        // Save targets to program_targets table
        if (!empty($targets)) {
            $target_insert_query = "INSERT INTO program_targets 
                                   (target_number, submission_id, target_description, status_indicator, 
                                    status_description, remarks, start_date, end_date) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($target_insert_query);
            
            foreach ($targets as $target) {
                $start_date = !empty($target['start_date']) ? $target['start_date'] : null;
                $end_date = !empty($target['end_date']) ? $target['end_date'] : null;
                
                $stmt->bind_param("sissssss", 
                    $target['target_number'],
                    $submission_id,
                    $target['target_text'],
                    $target['target_status'],
                    $target['status_description'],
                    $target['remarks'] ?? '',
                    $start_date,
                    $end_date
                );
                $stmt->execute();
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
?> 