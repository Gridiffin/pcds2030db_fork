<?php
// Start session FIRST before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Export Audit Logs Handler
 * 
 * Handles exporting audit logs to CSV format.
 */

// Include necessary files
require_once '../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is admin
if (!is_admin()) {
    // Log unauthorized export attempt
    log_audit_action(
        'audit_log_export_denied',
        'Unauthorized attempt to export audit logs',
        'failure'
    );
    http_response_code(403);
    echo 'Access denied';
    exit;
}

try {
    // Get filters from POST data
    $filters = [];
    
    if (!empty($_POST['date_from'])) {
        $filters['date_from'] = sanitize_input($_POST['date_from']);
    }
    
    if (!empty($_POST['date_to'])) {
        $filters['date_to'] = sanitize_input($_POST['date_to']);
    }
    
    if (!empty($_POST['action_type'])) {
        $filters['action_type'] = sanitize_input($_POST['action_type']);
    }
    
    if (!empty($_POST['user'])) {
        $filters['user'] = sanitize_input($_POST['user']);
    }
    
    if (!empty($_POST['status'])) {
        $filters['status'] = sanitize_input($_POST['status']);
    }
    
    if (!empty($_POST['user_id'])) {
        $filters['user_id'] = intval($_POST['user_id']);
    }
    
    // Get all audit logs matching the filters (no pagination for export)
    $result = get_audit_logs($filters, 10000, 0); // Limit to 10k records for performance
    
    if (isset($result['error'])) {
        throw new Exception($result['error']);
    }
      // Log the export operation using audit log
    $filter_details = [];
    if (!empty($filters['date_from'])) $filter_details[] = "Date from: {$filters['date_from']}";
    if (!empty($filters['date_to'])) $filter_details[] = "Date to: {$filters['date_to']}";
    if (!empty($filters['action_type'])) $filter_details[] = "Action type: {$filters['action_type']}";
    if (!empty($filters['user'])) $filter_details[] = "User: {$filters['user']}";
    if (!empty($filters['status'])) $filter_details[] = "Status: {$filters['status']}";
    if (!empty($filters['user_id'])) $filter_details[] = "User ID: {$filters['user_id']}";
    
    $filter_summary = !empty($filter_details) ? implode(', ', $filter_details) : 'No filters applied';
    
    log_audit_action(
        'audit_log_export',
        "Exported audit logs to CSV ({$result['total']} records). Filters: {$filter_summary}",
        'success'
    );
    
    // Set headers for CSV download
    $filename = 'audit_logs_' . date('Y-m-d_H-i-s') . '.csv';
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
    
    // Open output stream
    $output = fopen('php://output', 'w');
    
    // Write CSV header
    fputcsv($output, [
        'ID',
        'Date/Time',
        'User',
        'Action',
        'Details',
        'IP Address',
        'Status'
    ]);
    
    // Write data rows
    foreach ($result['logs'] as $log) {
        fputcsv($output, [
            $log['id'],
            $log['created_at'],
            $log['user_name'] ?? 'System',
            $log['action'],
            $log['details'],
            $log['ip_address'],
            $log['status']
        ]);
    }
    
    fclose($output);
    
} catch (Exception $e) {
    error_log("Export audit logs error: " . $e->getMessage());
    http_response_code(500);
    echo 'Export failed: ' . $e->getMessage();
}
?>