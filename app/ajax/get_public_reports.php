<?php
/**
 * AJAX endpoint to get public reports available for download
 */
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/agencies/core.php';
require_once '../lib/agencies/reports.php';
require_once '../lib/admins/core.php';

header('Content-Type: application/json');

// Verify user is logged in
if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'User not logged in.']);
    exit;
}

// Verify user is an agency
if (!is_agency()) {
    echo json_encode(['success' => false, 'error' => 'Access denied. User role: ' . ($_SESSION['role'] ?? 'not set')]);
    exit;
}

try {
    // Get public reports available for this agency
    // Use the working function from core.php instead of reports.php
    $public_reports = get_public_reports();
    
    echo json_encode([
        'success' => true,
        'reports' => $public_reports,
        'total' => count($public_reports)
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_public_reports.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'An error occurred while loading public reports.'
    ]);
}
?>
