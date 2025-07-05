<?php
/**
 * AJAX endpoint to get detailed field changes for a specific audit log entry
 */

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/admins/index.php';
require_once '../lib/audit_log.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check if user is admin
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Check if audit_log_id is provided
if (!isset($_GET['audit_log_id']) || !is_numeric($_GET['audit_log_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid audit log ID']);
    exit;
}

$audit_log_id = intval($_GET['audit_log_id']);

try {
    // Get field changes
    $field_changes = get_audit_field_changes($audit_log_id);
    
    // Format the changes for display
    $formatted_changes = [];
    foreach ($field_changes as $change) {
        $formatted_changes[] = [
            'field_name' => $change['field_name'],
            'field_type' => $change['field_type'],
            'old_value' => $change['old_value'],
            'new_value' => $change['new_value'],
            'change_type' => $change['change_type'],
            'formatted_description' => format_field_change($change),
            'created_at' => $change['created_at']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'field_changes' => $formatted_changes,
        'total_changes' => count($formatted_changes)
    ]);
    
} catch (Exception $e) {
    error_log("Error getting field changes: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to retrieve field changes']);
}
?> 