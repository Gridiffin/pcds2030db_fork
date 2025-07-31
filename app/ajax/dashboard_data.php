<?php
// Start session FIRST before any output
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * AJAX endpoint for dashboard data
 * 
 * Fetches dashboard data with filtering options
 */

// Include necessary files
require_once '../config/config.php';
require_once '../lib/db_connect.php';
require_once '../lib/session.php';
require_once '../lib/functions.php';
require_once '../lib/agency_functions.php';
require_once '../lib/DashboardController.php';

// Verify user is an agency
if (!is_agency()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get filter parameters
$agency_id = $_SESSION['user_id'] ?? 0;
$period_id_param = isset($_POST['period_id']) ? $_POST['period_id'] : 
                   (isset($_GET['period_id']) ? $_GET['period_id'] : 
                   getCurrentPeriodId($conn));
$period_ids = array_map('intval', explode(',', $period_id_param));

$include_assigned = isset($_POST['include_assigned']) ? 
                    filter_var($_POST['include_assigned'], FILTER_VALIDATE_BOOLEAN) : 
                    (isset($_GET['include_assigned']) ? 
                     filter_var($_GET['include_assigned'], FILTER_VALIDATE_BOOLEAN) : 
                     false);  // Default to exclude assigned programs when parameter not provided

$initiative_id = isset($_POST['initiative_id']) ? intval($_POST['initiative_id']) : 
                 (isset($_GET['initiative_id']) ? intval($_GET['initiative_id']) : null);

// Initialize controller
$dashboardController = new DashboardController($conn);

// Get filtered data
$data = $dashboardController->getDashboardData($agency_id, $period_ids, $include_assigned, $initiative_id);

// Return JSON response
header('Content-Type: application/json');
echo json_encode($data);
exit;
?>