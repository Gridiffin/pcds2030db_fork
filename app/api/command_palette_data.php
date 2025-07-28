<?php
/**
 * Command Palette Data API
 * 
 * Provides programs and submissions data specifically formatted for the command palette
 */

// Prevent any output before headers
ob_start();

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/agencies/core.php';
require_once '../lib/admins/core.php';

// Verify user is logged in
if (!is_logged_in()) {
    ob_end_clean();
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Clear any buffered output and set JSON header
ob_end_clean();
header('Content-Type: application/json');

try {
    $type = $_GET['type'] ?? 'programs';
    
    if ($type === 'programs') {
        // Get programs for current agency user
        if (is_agency()) {
            $agency_id = $_SESSION['agency_id'] ?? $_SESSION['user_id'];
            $sql = "SELECT p.program_id as id, p.program_name as name, 
                           CASE 
                               WHEN p.is_deleted = 1 THEN 'Deleted'
                               WHEN p.program_id IS NOT NULL THEN 'Active'
                               ELSE 'Draft'
                           END as status
                    FROM programs p
                    WHERE p.agency_id = ? AND p.is_deleted = 0
                    ORDER BY p.program_name";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $agency_id);
        } else {
            // For admin users, show all programs
            $sql = "SELECT p.program_id as id, p.program_name as name,
                           CASE 
                               WHEN p.is_deleted = 1 THEN 'Deleted'
                               WHEN p.program_id IS NOT NULL THEN 'Active'
                               ELSE 'Draft'
                           END as status,
                           u.agency_name
                    FROM programs p
                    LEFT JOIN users u ON p.agency_id = u.user_id
                    WHERE p.is_deleted = 0
                    ORDER BY p.program_name";
            
            $stmt = $conn->prepare($sql);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $programs = [];
        
        while ($row = $result->fetch_assoc()) {
            $programs[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $programs]);
        
    } elseif ($type === 'submissions') {
        // Get submissions for current agency user
        if (is_agency()) {
            $agency_id = $_SESSION['agency_id'] ?? $_SESSION['user_id'];
            $sql = "SELECT ps.submission_id as id, 
                           ps.program_id,
                           ps.period_id,
                           CONCAT(p.program_name, ' - ', 
                               CASE 
                                   WHEN rp.period_type = 'quarter' THEN CONCAT('Q', rp.period_number, '-', rp.year)
                                   WHEN rp.period_type = 'half' THEN CONCAT('H', rp.period_number, '-', rp.year)
                                   WHEN rp.period_type = 'yearly' THEN CONCAT('Yearly-', rp.year)
                                   ELSE CONCAT('Unknown-', rp.year)
                               END
                           ) as name,
                           CASE 
                               WHEN ps.is_submitted = 1 THEN 'Submitted'
                               WHEN ps.is_draft = 1 THEN 'Draft'
                               ELSE 'Pending'
                           END as status,
                           CASE 
                               WHEN rp.period_type = 'quarter' THEN CONCAT('Q', rp.period_number, '-', rp.year)
                               WHEN rp.period_type = 'half' THEN CONCAT('H', rp.period_number, '-', rp.year)
                               WHEN rp.period_type = 'yearly' THEN CONCAT('Yearly-', rp.year)
                               ELSE CONCAT('Unknown-', rp.year)
                           END as period
                    FROM program_submissions ps
                    JOIN programs p ON ps.program_id = p.program_id
                    JOIN reporting_periods rp ON ps.period_id = rp.period_id
                    WHERE p.agency_id = ? AND ps.is_deleted = 0
                    ORDER BY ps.created_at DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('i', $agency_id);
        } else {
            // For admin users, show all submissions
            $sql = "SELECT ps.submission_id as id, 
                           ps.program_id,
                           ps.period_id,
                           CONCAT(p.program_name, ' - ', 
                               CASE 
                                   WHEN rp.period_type = 'quarter' THEN CONCAT('Q', rp.period_number, '-', rp.year)
                                   WHEN rp.period_type = 'half' THEN CONCAT('H', rp.period_number, '-', rp.year)
                                   WHEN rp.period_type = 'yearly' THEN CONCAT('Yearly-', rp.year)
                                   ELSE CONCAT('Unknown-', rp.year)
                               END,
                               ' (', u.agency_name, ')'
                           ) as name,
                           CASE 
                               WHEN ps.is_submitted = 1 THEN 'Submitted'
                               WHEN ps.is_draft = 1 THEN 'Draft'
                               ELSE 'Pending'
                           END as status,
                           CASE 
                               WHEN rp.period_type = 'quarter' THEN CONCAT('Q', rp.period_number, '-', rp.year)
                               WHEN rp.period_type = 'half' THEN CONCAT('H', rp.period_number, '-', rp.year)
                               WHEN rp.period_type = 'yearly' THEN CONCAT('Yearly-', rp.year)
                               ELSE CONCAT('Unknown-', rp.year)
                           END as period
                    FROM program_submissions ps
                    JOIN programs p ON ps.program_id = p.program_id
                    JOIN reporting_periods rp ON ps.period_id = rp.period_id
                    LEFT JOIN users u ON p.agency_id = u.user_id
                    WHERE ps.is_deleted = 0
                    ORDER BY ps.created_at DESC";
            
            $stmt = $conn->prepare($sql);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $submissions = [];
        
        while ($row = $result->fetch_assoc()) {
            $submissions[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $submissions]);
        
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid type parameter']);
    }

} catch (Exception $e) {
    error_log("Error in command palette data API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>