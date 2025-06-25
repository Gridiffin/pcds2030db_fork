<?php
/**
 * Programs API
 * 
 * Simple API endpoint for fetching programs data
 */

// Prevent any output before headers
ob_start();

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/admins/index.php';

// Verify user is admin or agency user
if (!is_admin() && !is_agency()) {
    ob_end_clean();
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Clear any buffered output and set JSON header
ob_end_clean();
header('Content-Type: application/json');

try {
    // Get programs with basic info
    $sql = "SELECT p.program_id, p.program_name, p.program_number, p.sector_id,
                   u.agency_name
            FROM programs p
            LEFT JOIN users u ON p.owner_agency_id = u.user_id
            ORDER BY p.program_name";
    
    $result = $conn->query($sql);
    $programs = [];
    
    while ($row = $result->fetch_assoc()) {
        $programs[] = $row;
    }
    
    echo json_encode($programs);

} catch (Exception $e) {
    error_log("Error in programs API: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
