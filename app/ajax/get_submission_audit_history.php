<?php
require_once '../config/config.php';
require_once '../lib/session.php';
require_once '../lib/db_connect.php';
require_once '../lib/functions.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized access']);
    exit;
}

// Check if submission_id is provided
if (!isset($_GET['submission_id']) || !is_numeric($_GET['submission_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid submission ID']);
    exit;
}

$submission_id = intval($_GET['submission_id']);
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'];

try {
    global $conn;
    
    // Verify user has access to this submission
    $access_query = "SELECT ps.submission_id, ps.program_id, p.program_name, 
                            rp.period_type, rp.period_number, rp.year,
                            a.agency_name
                     FROM program_submissions ps
                     JOIN programs p ON ps.program_id = p.program_id
                     JOIN reporting_periods rp ON ps.period_id = rp.period_id
                     JOIN agency a ON p.agency_id = a.agency_id
                     WHERE ps.submission_id = ?";
    
    // Add role-based access control
    if ($user_role === 'admin') {
        // Admin can access all submissions
        $stmt = $conn->prepare($access_query);
        $stmt->bind_param("i", $submission_id);
    } else {
        // Agency users can only access their own submissions
        $access_query .= " AND p.agency_id = (SELECT agency_id FROM users WHERE user_id = ?)";
        $stmt = $conn->prepare($access_query);
        $stmt->bind_param("ii", $submission_id, $user_id);
    }
    
    $stmt->execute();
    $submission_info = $stmt->get_result()->fetch_assoc();
    
    if (!$submission_info) {
        http_response_code(404);
        echo json_encode(['error' => 'Submission not found or access denied']);
        exit;
    }
    
    // Get audit history for this submission
    $audit_query = "SELECT 
                        al.id as audit_id,
                        al.action,
                        al.details,
                        al.created_at as timestamp,
                        al.ip_address,
                        u.username,
                        u.fullname,
                        a.agency_name as user_agency
                    FROM audit_logs al
                    JOIN users u ON al.user_id = u.user_id
                    LEFT JOIN agency a ON u.agency_id = a.agency_id
                    WHERE al.action IN ('create_submission', 'update_submission')
                    AND al.details LIKE ?
                    ORDER BY al.created_at DESC
                    LIMIT 50";
    
    $stmt = $conn->prepare($audit_query);
    $search_pattern = "%submission ID: {$submission_id}%";
    $stmt->bind_param("s", $search_pattern);
    $stmt->execute();
    $audit_logs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // Get field changes for each audit log
    $audit_history = [];
    foreach ($audit_logs as $log) {
        $field_changes_query = "SELECT 
                                   afc.field_name,
                                   afc.field_type,
                                   afc.old_value,
                                   afc.new_value,
                                   afc.change_type
                               FROM audit_field_changes afc
                               WHERE afc.audit_log_id = ?
                               ORDER BY afc.change_id";
        
        $stmt = $conn->prepare($field_changes_query);
        $stmt->bind_param("i", $log['audit_id']);
        $stmt->execute();
        $field_changes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Format the audit entry
        $audit_entry = [
            'audit_id' => $log['audit_id'],
            'user_name' => $log['fullname'] ?: $log['username'],
            'user_agency' => $log['user_agency'],
            'action' => $log['action'],
            'timestamp' => $log['timestamp'],
            'ip_address' => $log['ip_address'],
            'summary' => $log['details'],
            'field_changes' => [],
            'change_summary' => [
                'added' => 0,
                'modified' => 0,
                'removed' => 0
            ]
        ];
        
        // Process field changes
        foreach ($field_changes as $change) {
            $field_change = [
                'field_name' => $change['field_name'],
                'field_type' => $change['field_type'],
                'old_value' => $change['old_value'],
                'new_value' => $change['new_value'],
                'change_type' => $change['change_type'],
                'field_label' => getFieldLabel($change['field_name'])
            ];
            
            $audit_entry['field_changes'][] = $field_change;
            $audit_entry['change_summary'][$change['change_type']]++;
        }
        
        // Generate human-readable summary
        $audit_entry['summary'] = generateChangeSummary($audit_entry);
        
        $audit_history[] = $audit_entry;
    }
    
    // Prepare response
    $response = [
        'success' => true,
        'data' => [
            'submission_info' => [
                'submission_id' => $submission_info['submission_id'],
                'program_id' => $submission_info['program_id'],
                'program_name' => $submission_info['program_name'],
                'period_name' => get_period_display_name($submission_info),
                'period_type' => $submission_info['period_type'],
                'year' => $submission_info['year'],
                'agency_name' => $submission_info['agency_name']
            ],
            'audit_history' => $audit_history,
            'total_changes' => count($audit_history)
        ]
    ];
    
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in get_submission_audit_history.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Failed to load audit history']);
    exit;
}

/**
 * Get human-readable field labels
 */
function getFieldLabel($field_name) {
    $labels = [
        'target_number' => 'Target Number',
        'target_description' => 'Target Description',
        'status_indicator' => 'Status',
        'status_description' => 'Status Description',
        'remarks' => 'Remarks',
        'start_date' => 'Start Date',
        'end_date' => 'End Date',
        'description' => 'Submission Description'
    ];
    
    return $labels[$field_name] ?? ucfirst(str_replace('_', ' ', $field_name));
}

/**
 * Generate human-readable change summary
 */
function generateChangeSummary($audit_entry) {
    $summary = $audit_entry['summary'];
    $changes = $audit_entry['change_summary'];
    
    $change_parts = [];
    if ($changes['added'] > 0) {
        $change_parts[] = "{$changes['added']} field(s) added";
    }
    if ($changes['modified'] > 0) {
        $change_parts[] = "{$changes['modified']} field(s) modified";
    }
    if ($changes['removed'] > 0) {
        $change_parts[] = "{$changes['removed']} field(s) removed";
    }
    
    if (!empty($change_parts)) {
        $summary .= " (" . implode(', ', $change_parts) . ")";
    }
    
    return $summary;
}
?> 