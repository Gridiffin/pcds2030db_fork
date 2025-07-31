<?php
// Start session FIRST before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * AJAX endpoint to get reports for a specific period
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

$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;

if (!$period_id) {
    echo json_encode(['success' => false, 'error' => 'Missing period_id parameter.']);
    exit;
}

try {
    // Note: The reports table doesn't appear to have an agency_id column
    // For now, return empty array until database schema is clarified
    $reports = [];
    
    // Alternative: Could get all reports for the period without agency filtering
    // $reports = get_reports_for_period($period_id);
    
    echo json_encode([
        'success' => true,
        'reports' => $reports,
        'period_id' => $period_id,
        'total' => count($reports),
        'message' => 'Agency-specific reports feature needs database schema update'
    ]);
    
} catch (Exception $e) {
    error_log("Error in get_reports.php: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'error' => 'An error occurred while loading reports.'
    ]);
}
?>
