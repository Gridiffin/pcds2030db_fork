<?php
// Start session FIRST before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Load Audit Logs AJAX Handler
 * 
 * Handles AJAX requests for loading audit logs with filtering and pagination.
 */

// Start output buffering to prevent accidental output before JSON
ob_start();

// Set JSON content type
header('Content-Type: application/json');

// Include necessary files
require_once '../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is admin
if (!is_admin()) {
    // Log unauthorized audit log access attempt
    log_audit_action(
        'audit_log_access_denied',
        'Unauthorized attempt to access audit logs',
        'failure'
    );
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Access denied']);
    ob_end_flush();
    exit;
}

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    ob_end_flush();
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
    
    // Get pagination parameters
    $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
    $limit = isset($_POST['limit']) ? min(100, max(10, intval($_POST['limit']))) : 25;
    $offset = ($page - 1) * $limit;
    
    // Log that we're about to get audit logs
    error_log("Fetching audit logs with filters: " . json_encode($filters));
    
    // Get audit logs    // Log detailed request information
    error_log("AUDIT LOG REQUEST: Page {$page}, Limit {$limit}, Offset {$offset}");
    error_log("AUDIT LOG FILTERS: " . json_encode($filters));
    
    // Get audit logs with profiling
    $start_time = microtime(true);
    $result = get_audit_logs($filters, $limit, $offset);
    $execution_time = number_format((microtime(true) - $start_time) * 1000, 2);
    error_log("AUDIT LOG DB QUERY EXECUTION TIME: {$execution_time}ms");
    
    // Check for errors in the result
    if (isset($result['error'])) {
        throw new Exception($result['error']);
    }
    
    // Validate result structure
    if (!isset($result['logs']) || !isset($result['total'])) {
        throw new Exception("Invalid result structure returned from get_audit_logs()");
    }
    
    // Format the logs for display
    $formatted_logs = [];
    foreach ($result['logs'] as $log) {
        // Apply default values to avoid undefined index errors
        $formatted_logs[] = [
            'id' => $log['id'] ?? 0,
            'user_name' => $log['user_name'] ?? 'System',
            'action' => $log['action'] ?? 'Unknown',
            'details' => $log['details'] ?? 'No details available',
            'ip_address' => $log['ip_address'] ?? 'Unknown',
            'status' => $log['status'] ?? 'unknown',
            'created_at' => $log['created_at'] ?? date('Y-m-d H:i:s'),
            'formatted_date' => isset($log['created_at']) ? date('M j, Y g:i A', strtotime($log['created_at'])) : 'Unknown',
            'status_badge' => ($log['status'] ?? '') === 'success' ? 'success' : 'danger',
            'action_badge' => get_action_badge_class($log['action'] ?? 'unknown')
        ];
    }
    
    // Calculate pagination info
    $total_pages = ceil($result['total'] / $limit);
    
    // Log successful audit log access
    $filter_details = [];
    if (!empty($filters['date_from'])) $filter_details[] = "Date from: {$filters['date_from']}";
    if (!empty($filters['date_to'])) $filter_details[] = "Date to: {$filters['date_to']}";
    if (!empty($filters['action_type'])) $filter_details[] = "Action type: {$filters['action_type']}";
    if (!empty($filters['user'])) $filter_details[] = "User: {$filters['user']}";
    if (!empty($filters['status'])) $filter_details[] = "Status: {$filters['status']}";
    if (!empty($filters['user_id'])) $filter_details[] = "User ID: {$filters['user_id']}";
    
    $filter_summary = !empty($filter_details) ? implode(', ', $filter_details) : 'No filters applied';
    
    log_audit_action(
        'audit_log_access',
        "Successfully accessed audit logs (Page {$page}, {$limit} per page, {$result['total']} total records). Filters: {$filter_summary}",
        'success'
    );
    
    // Return JSON response
    $response = [
        'success' => true,
        'logs' => $formatted_logs,
        'pagination' => [
            'current_page' => $page,
            'total_pages' => $total_pages,
            'total_records' => $result['total'],
            'records_per_page' => $limit,
            'showing_from' => min($result['total'], 1 + $offset),
            'showing_to' => min($offset + $limit, $result['total'])
        ]
    ];
      // Debug log the response structure
    error_log("Returning audit logs response with " . count($formatted_logs) . " logs");
    
    // Clear any potential output before sending JSON response
    while (ob_get_level()) ob_end_clean();
    
    // Encode response as JSON
    $json_response = json_encode($response);
    
    // Check for JSON encoding errors
    if ($json_response === false) {
        throw new Exception("JSON encoding failed: " . json_last_error_msg());
    }
    
    echo $json_response;
    exit; // Ensure no further output
    
} catch (Exception $e) {
    // Log the full error details
    error_log("Load audit logs error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    http_response_code(500);
    
    // Clean output buffer completely before sending error JSON
    while (ob_get_level()) ob_end_clean();
    
    echo json_encode([
        'success' => false, 
        'error' => 'Failed to load audit logs', 
        'debug_message' => $e->getMessage()
    ]);
    
    exit; // Ensure no further output
}

/**
 * Get CSS class for action badge based on action type
 * 
 * @param string $action The action type
 * @return string CSS class for badge
 */
function get_action_badge_class($action) {
    $action_lower = strtolower($action);
    
    if (strpos($action_lower, 'login') !== false) {
        return 'primary';
    } elseif (strpos($action_lower, 'logout') !== false) {
        return 'secondary';
    } elseif (strpos($action_lower, 'create') !== false || strpos($action_lower, 'assign') !== false) {
        return 'success';
    } elseif (strpos($action_lower, 'update') !== false || strpos($action_lower, 'edit') !== false) {
        return 'info';
    } elseif (strpos($action_lower, 'delete') !== false) {
        return 'danger';
    } elseif (strpos($action_lower, 'export') !== false) {
        return 'warning';
    } elseif (strpos($action_lower, 'failed') !== false) {
        return 'danger';
    } else {
        return 'dark';
    }
}
?>