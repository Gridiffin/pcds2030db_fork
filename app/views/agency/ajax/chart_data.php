<?php
/**
 * AJAX endpoint for agency chart data with toggle support
 * 
 * Returns chart data for the program rating distribution chart
 */

// Define project root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'app/controllers/DashboardController.php';

// Set content type
header('Content-Type: application/json');

try {
    // Verify user is an agency
    if (!is_agency()) {
        throw new Exception('Unauthorized access', 401);
    }

    // Get parameters
    $period_id = isset($_POST['period_id']) ? intval($_POST['period_id']) : null;
    $include_assigned = isset($_POST['include_assigned']) ? filter_var($_POST['include_assigned'], FILTER_VALIDATE_BOOLEAN) : false;

    // Validate period_id
    if (!$period_id) {
        $current_period = get_current_reporting_period();
        $period_id = $current_period['period_id'] ?? null;
    }

    if (!$period_id) {
        throw new Exception('No valid reporting period found', 400);
    }

    // Initialize dashboard controller
    $dashboardController = new DashboardController($conn);
    $dashboardData = $dashboardController->getDashboardData(
        $_SESSION['user_id'], 
        $period_id,
        $include_assigned
    );

    // Extract stats and chart data
    $stats = $dashboardData['stats'];
    $chartData = $dashboardData['chart_data'];

    // Prepare response
    $response = [
        'success' => true,
        'stats' => $stats,
        'chart_data' => $chartData,
        'include_assigned' => $include_assigned,
        'period_id' => $period_id
    ];

    echo json_encode($response);

} catch (Exception $e) {
    $status_code = $e->getCode();
    if (!in_array($status_code, [400, 401, 403, 404, 500])) {
        $status_code = 500;
    }
    
    http_response_code($status_code);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
