<?php
/**
 * AJAX endpoint for dashboard data
 * 
 * Fetches dashboard data with filtering options
 */

// Include necessary files
require_once '../config/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/agency_functions.php';
require_once '../includes/DashboardController.php';

// Verify user is an agency
if (!is_agency()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get filter parameters
$agency_id = $_SESSION['user_id'] ?? 0;
$period_id = isset($_POST['period_id']) ? intval($_POST['period_id']) : 
             (isset($_GET['period_id']) ? intval($_GET['period_id']) : 
             getCurrentPeriodId($conn));

$include_assigned = isset($_POST['include_assigned']) ? 
                    filter_var($_POST['include_assigned'], FILTER_VALIDATE_BOOLEAN) : 
                    (isset($_GET['include_assigned']) ? 
                     filter_var($_GET['include_assigned'], FILTER_VALIDATE_BOOLEAN) : 
                     false);  // Default to exclude assigned programs when parameter not provided

// Initialize controller
$dashboardController = new DashboardController($conn);

// Get filtered data
$data = $dashboardController->getDashboardData($agency_id, $period_id, $include_assigned);

// Return JSON response
header('Content-Type: application/json');
echo json_encode($data);
exit;
?>