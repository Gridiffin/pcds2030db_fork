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
            $programs = getReportedAgencies($conn, $period_id);
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
    $whereClause = '';
    $params = [];
    
    if ($period_id) {
        $whereClause = 'AND ps.period_id = ?';
        $params[] = $period_id;
    }

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
        LEFT JOIN program_submissions ps ON p.program_id = ps.program_id
        WHERE p.rating IN ('severe_delay') OR p.status = 'delayed'
        $whereClause
        ORDER BY p.program_name ASC
    ";

    $stmt = $conn->prepare($sql);
    
    if ($params) {
        $stmt->bind_param(str_repeat('i', count($params)), ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get programs with on-track rating
 */
function getOnTrackPrograms($conn, $period_id) {
    $whereClause = '';
    $params = [];
    
    if ($period_id) {
        $whereClause = 'AND ps.period_id = ?';
        $params[] = $period_id;
    }

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
        LEFT JOIN program_submissions ps ON p.program_id = ps.program_id
        WHERE p.rating IN ('monthly_target_achieved', 'on_track_for_year') OR p.status = 'active'
        $whereClause
        ORDER BY p.program_name ASC
    ";

    $stmt = $conn->prepare($sql);
    
    if ($params) {
        $stmt->bind_param(str_repeat('i', count($params)), ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}

/**
 * Get agencies that have reported (with their program counts)
 */
function getReportedAgencies($conn, $period_id) {
    $whereClause = '';
    $params = [];
    
    if ($period_id) {
        $whereClause = 'AND ps.period_id = ?';
        $params[] = $period_id;
    }

    $sql = "
        SELECT 
            a.agency_id,
            a.agency_name,
            COUNT(DISTINCT p.program_id) as total_programs,
            COUNT(DISTINCT ps.submission_id) as submitted_programs,
            MAX(ps.updated_at) as last_updated
        FROM agency a
        INNER JOIN programs p ON a.agency_id = p.agency_id
        INNER JOIN program_submissions ps ON p.program_id = ps.program_id
        WHERE (ps.is_submitted = 1 OR ps.is_draft = 0)
        $whereClause
        GROUP BY a.agency_id, a.agency_name
        HAVING submitted_programs > 0
        ORDER BY a.agency_name ASC
    ";

    $stmt = $conn->prepare($sql);
    
    if ($params) {
        $stmt->bind_param(str_repeat('i', count($params)), ...$params);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_all(MYSQLI_ASSOC);
}
?>