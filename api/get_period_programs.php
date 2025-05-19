<?php
/**
 * Get Programs By Period API
 * 
 * Returns program data for a specific reporting period
 */

// Prevent any output before headers
ob_start();

// Include necessary files
require_once '../config/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/admins/index.php';

// Verify user is admin
if (!is_admin()) {
    ob_end_clean(); // Clear any buffered output
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Permission denied. Only admin users can access this API.']);
    exit;
}

// Get period_id parameter
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;
$sector_id = isset($_GET['sector_id']) ? intval($_GET['sector_id']) : null;

// Validate period_id
if (!$period_id) {
    ob_end_clean(); // Clear any buffered output
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Missing period_id parameter']);
    exit;
}

try {
    // Get programs that have submissions for this period or all programs if no submissions exist
    $programs_query = "SELECT DISTINCT p.program_id, p.program_name, s.sector_id, s.sector_name
                      FROM programs p
                      LEFT JOIN program_submissions ps ON p.program_id = ps.program_id AND ps.period_id = ?
                      LEFT JOIN sectors s ON p.sector_id = s.sector_id
                      WHERE 
                            (ps.period_id IS NOT NULL OR NOT EXISTS (
                                SELECT 1 FROM program_submissions WHERE period_id = ?
                            ))
                            " . ($sector_id ? "AND p.sector_id = ?" : "") . "
                      ORDER BY s.sector_name, p.program_name";

    $stmt = $conn->prepare($programs_query);

    if ($sector_id) {
        $stmt->bind_param("iii", $period_id, $period_id, $sector_id);
    } else {
        $stmt->bind_param("ii", $period_id, $period_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $programs = [];
    while ($program = $result->fetch_assoc()) {
        if (!isset($programs[$program['sector_id']])) {
            $programs[$program['sector_id']] = [
                'sector_name' => $program['sector_name'],
                'programs' => []
            ];
        }
        $programs[$program['sector_id']]['programs'][] = [
            'program_id' => $program['program_id'],
            'program_name' => $program['program_name']
        ];
    }

    // Return the programs
    ob_end_clean(); // Clear any buffered output
    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'programs' => $programs]);

} catch (Exception $e) {
    // Handle any errors
    ob_end_clean(); // Clear any buffered output
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit;
}
