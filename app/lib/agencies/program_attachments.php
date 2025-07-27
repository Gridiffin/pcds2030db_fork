<?php
/**
 * Program Attachments Management Library
 * 
 * Provides functions for handling program file attachments
 */

if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

require_once dirname(__DIR__) . '/audit_log.php';

// Include core agency functions
if (file_exists(dirname(__FILE__) . '/core.php')) {
    require_once 'core.php';
} else {
    require_once dirname(__DIR__) . '/session.php';
    require_once dirname(__DIR__) . '/functions.php';
}

// Include admin core functions for is_admin() function
require_once dirname(__DIR__) . '/admins/core.php';

/**
 * Upload a file attachment to a program
 *
 * @param int $program_id Program ID
 * @param array $file $_FILES array element
 * @param string $description Optional file description
 * @param int $submission_id Optional submission ID for version control
 * @return array Success/error response
 */
function upload_program_attachment($program_id, $file, $description = '', $submission_id = null) {
    global $conn;
    
    // Validate input
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    $program_id = intval($program_id);
    if ($program_id <= 0) {
        return ['error' => 'Invalid program ID'];
    }
    
    // Validate submission_id is provided
    if ($submission_id === null || $submission_id <= 0) {
        return ['error' => 'Submission ID is required for attachment upload'];
    }
    
    // Verify user owns the program or has access
    if (!verify_program_access($program_id)) {
        log_audit_action(
            'attachment_upload_denied',
            "Unauthorized attachment upload attempt for program ID: {$program_id}",
            'failure',
            $_SESSION['user_id']
        );
        return ['error' => 'Access denied to this program'];
    }
    
    // Verify submission exists and belongs to the program
    $stmt = $conn->prepare("SELECT submission_id FROM program_submissions WHERE submission_id = ? AND program_id = ? AND is_deleted = 0");
    $stmt->bind_param("ii", $submission_id, $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return ['error' => 'Invalid submission ID or submission does not belong to this program'];
    }
    
    // Validate file upload
    $validation = validate_file_upload($file);
    if (!$validation['valid']) {
        return ['error' => $validation['error']];
    }
    
    // Check program attachment limits
    $current_count = get_program_attachment_count($program_id);
    if ($current_count >= 10) { // Max 10 attachments per program
        return ['error' => 'Maximum number of attachments (10) reached for this program'];
    }
    
    try {
        $conn->begin_transaction();
        
        // Create secure filename and directory
        $upload_result = process_file_upload($program_id, $file);
        if (!$upload_result['success']) {
            throw new Exception($upload_result['error']);
        }
        
        // Insert attachment record
        $stmt = $conn->prepare("
            INSERT INTO program_attachments 
            (submission_id, file_name, file_path, file_size, file_type, uploaded_by, uploaded_at) 
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->bind_param(
            "issiis",
            $submission_id,
            $upload_result['original_filename'],
            $upload_result['file_path'],
            $upload_result['file_size'],
            $upload_result['file_type'],
            $_SESSION['user_id']
        );
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to save attachment record: ' . $stmt->error);
        }
        
        $attachment_id = $conn->insert_id;
        $conn->commit();
        
        // Log successful upload
        log_audit_action(
            'attachment_uploaded',
            "File '{$upload_result['original_filename']}' uploaded to program ID: {$program_id}",
            'success',
            $_SESSION['user_id']
        );
        
        return [
            'success' => true,
            'attachment_id' => $attachment_id,
            'filename' => $upload_result['original_filename'],
            'file_size' => $upload_result['file_size'],
            'mime_type' => $upload_result['mime_type'],
            'file_type' => $upload_result['file_type'],
            'upload_date' => date('Y-m-d H:i:s'),
            'message' => 'File uploaded successfully'
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        
        // Clean up uploaded file if exists
        if (isset($upload_result['file_path']) && file_exists($upload_result['file_path'])) {
            unlink($upload_result['file_path']);
        }
        
        log_audit_action(
            'attachment_upload_failed',
            "Failed to upload attachment to program ID {$program_id}: " . $e->getMessage(),
            'failure',
            $_SESSION['user_id']
        );
        
        return ['error' => 'Upload failed: ' . $e->getMessage()];
    }
}

/**
 * Delete a program attachment
 *
 * @param int $attachment_id Attachment ID
 * @return array Success/error response
 */
function delete_program_attachment($attachment_id) {
    global $conn;
    
    if (!is_agency()) {
        return ['error' => 'Permission denied'];
    }
    
    $attachment_id = intval($attachment_id);
    if ($attachment_id <= 0) {
        return ['error' => 'Invalid attachment ID'];
    }
    
    try {
        // Get attachment details
        $stmt = $conn->prepare("
            SELECT pa.*, p.agency_id 
            FROM program_attachments pa 
            LEFT JOIN program_submissions ps ON pa.submission_id = ps.submission_id
            LEFT JOIN programs p ON ps.program_id = p.program_id 
            WHERE pa.attachment_id = ? AND pa.is_deleted = 0
        ");
        $stmt->bind_param("i", $attachment_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return ['error' => 'Attachment not found'];
        }
        
        $attachment = $result->fetch_assoc();
        
        // Verify user has access
        if ($attachment['agency_id'] != $_SESSION['agency_id'] && !is_admin()) {
            log_audit_action(
                'attachment_delete_denied',
                "Unauthorized attachment deletion attempt for attachment ID: {$attachment_id}",
                'failure',
                $_SESSION['user_id']
            );
            return ['error' => 'Access denied'];
        }
        
        $conn->begin_transaction();
        
        // Soft delete the attachment
        $stmt = $conn->prepare("UPDATE program_attachments SET is_deleted = 1 WHERE attachment_id = ?");
        $stmt->bind_param("i", $attachment_id);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to delete attachment record');
        }
        
        $conn->commit();
        
        // Delete physical file
        if (file_exists($attachment['file_path'])) {
            unlink($attachment['file_path']);
        }
        
        // Log successful deletion
        log_audit_action(
            'attachment_deleted',
            "Attachment '{$attachment['file_name']}' deleted from program ID: {$attachment['program_id']}",
            'success',
            $_SESSION['user_id']
        );
        
        return [
            'success' => true,
            'message' => 'Attachment deleted successfully'
        ];
        
    } catch (Exception $e) {
        $conn->rollback();
        
        log_audit_action(
            'attachment_delete_failed',
            "Failed to delete attachment ID {$attachment_id}: " . $e->getMessage(),
            'failure',
            $_SESSION['user_id']
        );
        
        return ['error' => 'Delete failed: ' . $e->getMessage()];
    }
}

/**
 * Get attachments for a program
 *
 * @param int $program_id Program ID
 * @return array List of attachments
 */
function get_program_attachments($program_id) {
    global $conn;
    
    $program_id = intval($program_id);
    if ($program_id <= 0) {
        return [];
    }
    
    // Verify user has access to the program
    if (!verify_program_access($program_id)) {
        return [];
    }
    
    // Get all attachments for this program across all submissions
    $stmt = $conn->prepare("
        SELECT pa.*, u.username as uploaded_by_name, ps.period_id
        FROM program_attachments pa
        LEFT JOIN users u ON pa.uploaded_by = u.user_id
        LEFT JOIN program_submissions ps ON pa.submission_id = ps.submission_id
        WHERE ps.program_id = ? AND pa.is_deleted = 0
        ORDER BY pa.uploaded_at DESC
    ");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $attachments = [];
    while ($row = $result->fetch_assoc()) {
        $attachments[] = [
            'attachment_id' => $row['attachment_id'],
            'filename' => $row['file_name'],
            'original_filename' => $row['file_name'],
            'file_path' => $row['file_path'],
            'file_size' => $row['file_size'],
            'file_type' => $row['file_type'],
            'uploaded_at' => $row['uploaded_at'],
            'upload_date' => $row['uploaded_at'],
            'uploaded_by' => $row['uploaded_by_name'],
            'file_size_formatted' => format_file_size($row['file_size'] ?? 0),
            'submission_id' => $row['submission_id'],
            'period_id' => $row['period_id']
        ];
    }
    
    return $attachments;
}

/**
 * Get attachments for a specific submission with user details
 *
 * @param int $submission_id Submission ID
 * @return array Array of attachments with user information
 */
function get_submission_attachments_with_details($submission_id) {
    global $conn;
    
    $submission_id = intval($submission_id);
    if ($submission_id <= 0) {
        return [];
    }
    
    // Get attachments for this specific submission
    $stmt = $conn->prepare("
        SELECT pa.*, u.username as uploaded_by_name
        FROM program_attachments pa
        LEFT JOIN users u ON pa.uploaded_by = u.user_id
        WHERE pa.submission_id = ? AND pa.is_deleted = 0
        ORDER BY pa.uploaded_at DESC
    ");
    $stmt->bind_param("i", $submission_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $attachments = [];
    while ($row = $result->fetch_assoc()) {
        $attachments[] = [
            'attachment_id' => $row['attachment_id'],
            'original_filename' => $row['file_name'],
            'file_size' => $row['file_size'],
            'mime_type' => $row['file_type'],
            'file_type' => $row['file_type'],
            'description' => $row['description'] ?? '',
            'upload_date' => $row['uploaded_at'],
            'uploaded_by' => $row['uploaded_by_name'],
            'file_size_formatted' => format_file_size($row['file_size']),
            'submission_id' => $row['submission_id']
        ];
    }
    
    return $attachments;
}

/**
 * Validate file upload
 *
 * @param array $file $_FILES array element
 * @return array Validation result
 */
function validate_file_upload($file) {
    // Check for upload errors
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['valid' => false, 'error' => 'Invalid file upload'];
    }
    
    switch ($file['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            return ['valid' => false, 'error' => 'No file was uploaded'];
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            return ['valid' => false, 'error' => 'File is too large'];
        default:
            return ['valid' => false, 'error' => 'Upload error occurred'];
    }
    
    // File size validation (10MB max)
    if ($file['size'] > 10 * 1024 * 1024) {
        return ['valid' => false, 'error' => 'File size cannot exceed 10MB'];
    }
    
    // File type validation
    $allowed_types = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'txt' => 'text/plain',
        'csv' => 'text/csv'
    ];
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!array_key_exists($file_extension, $allowed_types)) {
        return ['valid' => false, 'error' => 'File type not allowed. Allowed types: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG, TXT, CSV'];
    }
    
    // MIME type validation
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if ($mime_type !== $allowed_types[$file_extension]) {
        return ['valid' => false, 'error' => 'File content does not match extension'];
    }
    
    return [
        'valid' => true,
        'file_extension' => $file_extension,
        'mime_type' => $mime_type
    ];
}

/**
 * Process file upload and move to secure location
 *
 * @param int $program_id Program ID
 * @param array $file $_FILES array element
 * @return array Upload result
 */
function process_file_upload($program_id, $file) {
    $uploads_dir = PROJECT_ROOT_PATH . 'uploads/programs/attachments';
    $program_dir = $uploads_dir . '/' . $program_id;
    
    // Create directory if it doesn't exist
    if (!is_dir($program_dir)) {
        if (!mkdir($program_dir, 0755, true)) {
            return ['success' => false, 'error' => 'Failed to create upload directory'];
        }
    }
    
    // Generate secure filename
    $original_filename = basename($file['name']);
    $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    $timestamp = time();
    $hash = substr(md5($original_filename . $timestamp . rand()), 0, 8);
    $safe_filename = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($original_filename, PATHINFO_FILENAME));
    $stored_filename = $timestamp . '_' . $hash . '_' . $safe_filename . '.' . $file_extension;
    
    $file_path = $program_dir . '/' . $stored_filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $file_path)) {
        return ['success' => false, 'error' => 'Failed to move uploaded file'];
    }
    
    // Set secure permissions
    chmod($file_path, 0644);
    
    return [
        'success' => true,
        'original_filename' => $original_filename,
        'stored_filename' => $stored_filename,
        'file_path' => $file_path,
        'file_size' => $file['size'],
        'file_type' => $file_extension,
        'mime_type' => mime_content_type($file_path)
    ];
}

/**
 * Verify user has access to program
 *
 * @param int $program_id Program ID
 * @return bool True if user has access
 */
function verify_program_access($program_id) {
    global $conn;
    
    if (is_admin()) {
        return true; // Admins have access to all programs
    }
    
    if (!is_agency()) {
        return false;
    }
    
    $stmt = $conn->prepare("SELECT agency_id FROM programs WHERE program_id = ?");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $program = $result->fetch_assoc();
    return $program['agency_id'] == $_SESSION['agency_id'];
}

/**
 * Get count of active attachments for a program
 *
 * @param int $program_id Program ID
 * @return int Attachment count
 */
function get_program_attachment_count($program_id) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count 
        FROM program_attachments pa
        LEFT JOIN program_submissions ps ON pa.submission_id = ps.submission_id
        WHERE ps.program_id = ? AND pa.is_deleted = 0
    ");
    $stmt->bind_param("i", $program_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return intval($row['count']);
}

// Note: format_file_size() function is available from functions.php

/**
 * Get attachment for secure download
 *
 * @param int $attachment_id Attachment ID
 * @return array|false Attachment data or false
 */
function get_attachment_for_download($attachment_id) {
    global $conn;
    
    $attachment_id = intval($attachment_id);
    
    $stmt = $conn->prepare("
        SELECT pa.*, p.agency_id 
        FROM program_attachments pa 
        JOIN programs p ON pa.program_id = p.program_id 
        WHERE pa.attachment_id = ? AND pa.is_active = 1
    ");
    $stmt->bind_param("i", $attachment_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $attachment = $result->fetch_assoc();
    
    // Verify user has access
    if (!is_admin() && $attachment['agency_id'] != $_SESSION['agency_id']) {
        return false;
    }
    
    return $attachment;
}

/**
 * Validate attachment file
 *
 * @param array $file $_FILES array element
 * @return array Validation result with success/error info
 */
function validate_attachment_file($file) {
    // Check if file was uploaded without errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error_messages = [
            UPLOAD_ERR_INI_SIZE => 'File is too large (exceeds server limit)',
            UPLOAD_ERR_FORM_SIZE => 'File is too large (exceeds form limit)',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by extension'
        ];
        
        return [
            'valid' => false,
            'error' => $error_messages[$file['error']] ?? 'Unknown upload error'
        ];
    }
    
    // Check file size (10MB max)
    $max_size = 10 * 1024 * 1024; // 10MB
    if ($file['size'] > $max_size) {
        return [
            'valid' => false,
            'error' => 'File is too large. Maximum size is 10MB.'
        ];
    }
    
    // Check MIME type
    $allowed_types = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'text/plain',
        'text/csv',
        'image/jpeg',
        'image/jpg',
        'image/png',
        'image/gif'
    ];
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime_type, $allowed_types)) {
        return [
            'valid' => false,
            'error' => 'File type not allowed. Supported formats: PDF, Word, Excel, PowerPoint, images, text files.'
        ];
    }
    
    // Check file extension
    $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'csv'];
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_extension, $allowed_extensions)) {
        return [
            'valid' => false,
            'error' => 'File extension not allowed.'
        ];
    }
    
    return [
        'valid' => true,
        'mime_type' => $mime_type,
        'extension' => $file_extension
    ];
}

/**
 * Get file icon class based on MIME type
 *
 * @param string $mime_type File MIME type
 * @return string FontAwesome icon class
 */
function get_file_icon($mime_type) {
    $icons = [
        'application/pdf' => 'fa-file-pdf',
        'application/msword' => 'fa-file-word',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fa-file-word',
        'application/vnd.ms-excel' => 'fa-file-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fa-file-excel',
        'application/vnd.ms-powerpoint' => 'fa-file-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'fa-file-powerpoint',
        'text/plain' => 'fa-file-alt',
        'text/csv' => 'fa-file-csv',
        'image/jpeg' => 'fa-file-image',
        'image/jpg' => 'fa-file-image',
        'image/png' => 'fa-file-image',
        'image/gif' => 'fa-file-image'
    ];
    
    return $icons[$mime_type] ?? 'fa-file';
}
?>
