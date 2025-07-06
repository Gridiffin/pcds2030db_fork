<?php
/**
 * Periods Data AJAX Endpoint
 * 
 * Retrieves reporting periods data for the admin interface
 */

// Start output buffering immediately to catch any stray output
if (ob_get_level() > 0) {
    ob_end_clean();
}
ob_start();

// Start session
session_start();

// Include necessary files
require_once '../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admin_functions.php';

// Load database configuration
$config = include __DIR__ . '/../config/db_names.php';
if (!$config || !isset($config['tables']['reporting_periods'])) {
    die('Config not loaded or missing reporting_periods table definition.');
}
$periodsTable = $config['tables']['reporting_periods'];
$periodIdCol = $config['columns']['reporting_periods']['id'];
$periodYearCol = $config['columns']['reporting_periods']['year'];
$periodTypeCol = $config['columns']['reporting_periods']['period_type'];
$periodNumberCol = $config['columns']['reporting_periods']['period_number'];
$periodStartDateCol = $config['columns']['reporting_periods']['start_date'];
$periodEndDateCol = $config['columns']['reporting_periods']['end_date'];
$periodStatusCol = $config['columns']['reporting_periods']['status'];
$periodCreatedAtCol = $config['columns']['reporting_periods']['created_at'];
$periodUpdatedAtCol = $config['columns']['reporting_periods']['updated_at'];

// Clear any output from includes and set JSON header
ob_end_clean();
header('Content-Type: application/json');

// Function to send JSON response and exit cleanly
function send_json_response($success, $message, $data = null) {
    $response = ['success' => $success, 'message' => $message];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit;
}

// Set up global error handler to catch any PHP errors and return JSON
function json_error_handler($errno, $errstr, $errfile, $errline) {
    error_log("PHP Error in periods_data.php: [$errno] $errstr in $errfile on line $errline");
    send_json_response(false, 'Server error occurred');
}
set_error_handler('json_error_handler');

// Check if user is admin
if (!is_admin()) {
    send_json_response(false, 'Access denied');
}

try {
    // Database connection is already available via db_connect.php as $conn (MySQLi)
    
    // Check database connection
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Database connection failed');
    }
    
    // Check if a specific period ID is requested
    if (isset($_GET['period_id']) && is_numeric($_GET['period_id'])) {
        $periodId = intval($_GET['period_id']);
        
        // Query to get a specific period
        $query = "SELECT $periodIdCol, $periodYearCol, $periodTypeCol, $periodNumberCol, 
                         $periodStartDateCol, $periodEndDateCol, $periodStatusCol, 
                         $periodCreatedAtCol, $periodUpdatedAtCol 
                  FROM $periodsTable 
                  WHERE $periodIdCol = ?";
        
        $stmt = $conn->prepare($query);
        if (!$stmt) {
            throw new Exception('Failed to prepare statement: ' . $conn->error);
        }
        
        $stmt->bind_param("i", $periodId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            send_json_response(false, 'Period not found');
        }
        
        $period = $result->fetch_assoc();
        send_json_response(true, 'Period found', $period);
    }
      // If no specific period requested, return all periods
    $query = "SELECT $periodIdCol, $periodYearCol, $periodTypeCol, $periodNumberCol, 
                     $periodStartDateCol, $periodEndDateCol, $periodStatusCol, 
                     $periodCreatedAtCol, $periodUpdatedAtCol 
              FROM $periodsTable 
              ORDER BY $periodYearCol DESC, $periodNumberCol DESC";
    

    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        throw new Exception('Failed to prepare statement: ' . $conn->error);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    $periods = $result->fetch_all(MYSQLI_ASSOC);
    
    // Format the data
    $formattedPeriods = [];    
    foreach ($periods as $period) {
        // Create period name from year, period_type and period_number with proper terminology
        $period_type = $period[$periodTypeCol];
        $period_number = $period[$periodNumberCol];
        $year = $period[$periodYearCol];
        
        if ($period_type == 'quarter') {
            $periodName = "Q" . $period_number . " " . $year;
        } elseif ($period_type == 'half') {
            $periodName = "Half Yearly " . $period_number . " " . $year;
        } elseif ($period_type == 'yearly') {
            $periodName = "Yearly " . $period_number . " " . $year;
        } else {
            $periodName = "Period " . $period_number . " " . $year;
        }
        
        $formattedPeriods[] = [
            'period_id' => (int)$period[$periodIdCol],
            'period_name' => $periodName,
            'year' => (int)$period[$periodYearCol],
            'period_type' => $period[$periodTypeCol],
            'period_number' => (int)$period[$periodNumberCol],
            'start_date' => $period[$periodStartDateCol],
            'end_date' => $period[$periodEndDateCol],
            'status' => $period[$periodStatusCol],
            'created_at' => $period[$periodCreatedAtCol],
            'updated_at' => $period[$periodUpdatedAtCol]
        ];
    }
    
    $response = [
        'success' => true,
        'data' => $formattedPeriods,
        'count' => count($formattedPeriods)
    ];
    
    echo json_encode($response);

} catch (Exception $e) {
    error_log("Error in periods_data.php: " . $e->getMessage());
    send_json_response(false, 'Failed to load periods data: ' . $e->getMessage());
}
?>
