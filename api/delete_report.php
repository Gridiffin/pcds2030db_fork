<?php
/**
 * Delete Report API
 * 
 * This endpoint handles the deletion of generated reports.
 * Only admins can delete reports.
 */

// Include necessary files
require_once '../config/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is logged in and is an admin
if (!is_admin()) {
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized access'
    ]);
    exit();
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
    exit();
}

// Get report ID from POST data
$reportId = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;

if ($reportId <= 0) {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid report ID'
    ]);
    exit();
}

// Start transaction
$conn->begin_transaction();

try {
    // Get the file path before deletion
    $query = "SELECT pptx_path FROM reports WHERE report_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $reportId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Report not found');
    }
    
    $report = $result->fetch_assoc();
    $filePath = '../reports/' . $report['pptx_path'];
    
    // Delete from database
    $deleteQuery = "DELETE FROM reports WHERE report_id = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param('i', $reportId);
    $deleteStmt->execute();
    
    if ($deleteStmt->affected_rows === 0) {
        throw new Exception('Failed to delete report from database');
    }
    
    // Delete the physical file
    if (file_exists($filePath) && !unlink($filePath)) {
        // If file deletion fails, log but don't throw exception
        error_log("Warning: Failed to delete file: $filePath");
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Report deleted successfully'
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    // Close statements
    if (isset($stmt)) $stmt->close();
    if (isset($deleteStmt)) $deleteStmt->close();
}