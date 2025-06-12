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
        $query = "SELECT 
                    period_id,
                    year,
                    quarter,
                    start_date,
                    end_date,
                    status,
                    is_standard_dates,
                    created_at,
                    updated_at
                  FROM reporting_periods 
                  WHERE period_id = ?";
        
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
    $query = "SELECT 
                period_id,
                year,
                quarter,
                start_date,
                end_date,
                status,
                is_standard_dates,
                created_at,
                updated_at
              FROM reporting_periods 
              ORDER BY year DESC, quarter DESC";
    
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
        // Create period name from year and quarter with proper terminology
        if ($period['quarter'] >= 1 && $period['quarter'] <= 4) {
            $periodName = "Q" . $period['quarter'] . " " . $period['year'];
        } elseif ($period['quarter'] == 5) {
            $periodName = "Half Yearly 1 " . $period['year'];
        } elseif ($period['quarter'] == 6) {
            $periodName = "Half Yearly 2 " . $period['year'];
        } else {
            $periodName = "Period " . $period['quarter'] . " " . $period['year'];
        }
        
        $formattedPeriods[] = [
            'period_id' => (int)$period['period_id'],
            'period_name' => $periodName,
            'year' => (int)$period['year'],
            'quarter' => (int)$period['quarter'],
            'start_date' => $period['start_date'],
            'end_date' => $period['end_date'],
            'status' => $period['status'],
            'is_standard_dates' => (bool)$period['is_standard_dates'],
            'created_at' => $period['created_at'],
            'updated_at' => $period['updated_at']
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
