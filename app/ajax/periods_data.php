<?php
/**
 * Periods Data AJAX Endpoint
 * 
 * Retrieves reporting periods data for the admin interface
 */

// Start session
session_start();

header('Content-Type: application/json');

// Include necessary files
require_once '../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admin_functions.php';

// Check if user is admin
if (!is_admin()) {
    echo json_encode(['success' => false, 'message' => 'Access denied']);
    exit;
}

try {
    // Database connection is already available via db_connect.php as $conn (MySQLi)
    
    // Query to get all reporting periods
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
    $stmt->execute();
    $result = $stmt->get_result();
    $periods = $result->fetch_all(MYSQLI_ASSOC);
    
    // Format the data
    $formattedPeriods = [];
    foreach ($periods as $period) {
        // Create period name from year and quarter
        $periodName = "Q" . $period['quarter'] . " " . $period['year'];
        
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
    
    echo json_encode([
        'success' => true,
        'data' => $formattedPeriods,
        'count' => count($formattedPeriods)
    ]);

} catch (Exception $e) {
    error_log("Error in periods_data.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to load periods data: ' . $e->getMessage()
    ]);
}
?>
