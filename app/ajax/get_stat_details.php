<?php
/**
 * Get Stat Details AJAX Endpoint
 * 
 * Returns detailed program information for dashboard stat cards
 */

// Define project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(__DIR__)), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';

// Set JSON content type
header('Content-Type: application/json');

// Check if user is admin
if (!is_admin()) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => 'Access denied. Admin privileges required.'
    ]);
    exit;
}

// Get POST data
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['stat_type'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters.'
    ]);
    exit;
}

$stat_type = $input['stat_type'];
$period_id = $input['period_id'] ?? null;

try {
    $programs = [];

    switch ($stat_type) {
        case 'delayed_programs':
            $programs = getDelayedPrograms($conn, $period_id);
            break;
            
        case 'on_track_programs':
            $programs = getOnTrackPrograms($conn, $period_id);
            break;
            
        case 'agencies_reported':
            $programs = getActiveUsersWithSubmissionActions($conn, $period_id);
            break;
            
        case 'monthly_target_achieved':
            $programs = getMonthlyTargetAchievedPrograms($conn, $period_id);
            break;
            
        default:
            throw new Exception('Invalid stat type requested.');
    }

    echo json_encode([
        'success' => true,
        'programs' => $programs,
        'count' => count($programs)
    ]);

} catch (Exception $e) {
    error_log("Stat details error: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch program details.'
    ]);
}

/**
 * Get programs with delayed rating
 */
function getDelayedPrograms($conn, $period_id) {
    // Since rating is at program level, we don't need to filter by period
    // The rating represents the current status of the program regardless of period
    
    $sql = "
        SELECT DISTINCT
            p.program_id,
            p.program_name,
            p.agency_id,
            a.agency_name,
            p.rating,
            p.updated_at as last_updated
        FROM programs p
        LEFT JOIN agency a ON p.agency_id = a.agency_id
        WHERE p.rating = 'severe_delay'
        ORDER BY p.program_name ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get programs with on-track rating
 */
function getOnTrackPrograms($conn, $period_id) {
    // Since rating is at program level, we don't need to filter by period
    // The rating represents the current status of the program regardless of period
    
    $sql = "
        SELECT DISTINCT
            p.program_id,
            p.program_name,
            p.agency_id,
            a.agency_name,
            p.rating,
            p.updated_at as last_updated
        FROM programs p
        LEFT JOIN agency a ON p.agency_id = a.agency_id
        WHERE p.rating = 'on_track_for_year'
        ORDER BY p.program_name ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get agencies that have reported (with their program counts)
 */
function getActiveUsersWithSubmissionActions($conn, $period_id) {
    // Resolve period window
    $period_start = null; $period_end = null;
    if ($period_id) {
        $pstmt = $conn->prepare("SELECT start_date, end_date FROM reporting_periods WHERE period_id = ?");
        if ($pstmt) {
            $pstmt->bind_param('i', $period_id);
            $pstmt->execute();
            $pres = $pstmt->get_result();
            if ($pres && $prow = $pres->fetch_assoc()) {
                $period_start = $prow['start_date'] . ' 00:00:00';
                $period_end = $prow['end_date'] . ' 23:59:59';
            }
            $pstmt->close();
        }
    }

    if (!$period_start || !$period_end) {
        return [];
    }

    // Detect available audit tables
    $has_audit_logs = false; // plural
    $has_audit_log = false;  // singular
    try { $r1 = $conn->query("SHOW TABLES LIKE 'audit_logs'"); $has_audit_logs = $r1 && $r1->num_rows > 0; } catch (Exception $e) {}
    try { $r2 = $conn->query("SHOW TABLES LIKE 'audit_log'"); $has_audit_log = $r2 && $r2->num_rows > 0; } catch (Exception $e) {}

    $inner_parts = [];
    $param_values = [];
    $param_types = '';

    if ($has_audit_logs) {
        $inner_parts[] = "SELECT 
                al.user_id,
                SUM(al.action = 'create_submission') AS create_count,
                SUM(al.action = 'update_submission') AS update_count,
                0 AS finalize_count,
                MAX(al.created_at) AS last_activity
            FROM audit_logs al
            JOIN users uu ON uu.user_id = al.user_id
            WHERE al.action IN ('create_submission','update_submission')
              AND al.created_at BETWEEN ? AND ?
              AND uu.role IN ('agency','focal') AND uu.is_active = 1
            GROUP BY al.user_id";
        $param_values[] = $period_start; $param_values[] = $period_end;
        $param_types .= 'ss';
    }
    if ($has_audit_log) {
        $inner_parts[] = "SELECT 
                al2.user_id,
                0 AS create_count,
                0 AS update_count,
                COUNT(*) AS finalize_count,
                MAX(al2.created_at) AS last_activity
            FROM audit_log al2
            JOIN users uu2 ON uu2.user_id = al2.user_id
            WHERE al2.action = 'finalize_submission'
              AND al2.created_at BETWEEN ? AND ?
              AND uu2.role IN ('agency','focal') AND uu2.is_active = 1
            GROUP BY al2.user_id";
        $param_values[] = $period_start; $param_values[] = $period_end;
        $param_types .= 'ss';
    }

    if (empty($inner_parts)) {
        return [];
    }

    $sql = "SELECT 
            u.user_id,
            COALESCE(NULLIF(TRIM(u.fullname), ''), u.username) AS display_name,
            u.username,
            u.fullname,
            u.role,
            a.agency_id,
            a.agency_name,
            SUM(x.create_count) AS create_count,
            SUM(x.update_count) AS update_count,
            SUM(x.finalize_count) AS finalize_count,
            MAX(x.last_activity) AS last_activity
        FROM (" . implode("\nUNION ALL\n", $inner_parts) . ") x
        JOIN users u ON u.user_id = x.user_id
        LEFT JOIN agency a ON a.agency_id = u.agency_id
        GROUP BY u.user_id, u.username, u.fullname, u.role, a.agency_id, a.agency_name
        ORDER BY last_activity DESC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) { return []; }
    if (!empty($param_values)) {
        $stmt->bind_param($param_types, ...$param_values);
    }
    $stmt->execute();
    $res = $stmt->get_result();
    $rows = $res->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
}

/**
 * Get programs with monthly target achieved rating
 */
function getMonthlyTargetAchievedPrograms($conn, $period_id) {
    // Since rating is at program level, we don't need to filter by period
    // The rating represents the current status of the program regardless of period
    
    $sql = "
        SELECT DISTINCT
            p.program_id,
            p.program_name,
            p.agency_id,
            a.agency_name,
            p.rating,
            p.updated_at as last_updated
        FROM programs p
        LEFT JOIN agency a ON p.agency_id = a.agency_id
        WHERE p.rating = 'monthly_target_achieved'
        ORDER BY p.program_name ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>