<?php
/**
 * Check Period Overlap AJAX Endpoint
 * 
 * Checks if a reporting period's date range overlaps with existing periods
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

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    // Database connection is already available via db_connect.php as $conn (MySQLi)
    
    // Get and validate input
    $start_date = trim($_POST['start_date'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $exclude_period_id = isset($_POST['exclude_period_id']) && is_numeric($_POST['exclude_period_id']) 
                        ? intval($_POST['exclude_period_id']) 
                        : null;
    
    // Validate dates
    if (empty($start_date) || empty($end_date)) {
        throw new Exception('Both start and end dates are required');
    }
    
    // Validate date formats
    $start_date_obj = DateTime::createFromFormat('Y-m-d', $start_date);
    $end_date_obj = DateTime::createFromFormat('Y-m-d', $end_date);
    
    if (!$start_date_obj || !$end_date_obj) {
        throw new Exception('Invalid date format. Use YYYY-MM-DD');
    }
    
    // Check for overlapping periods in the database
    $query = "
        SELECT period_id, quarter, year, start_date, end_date 
        FROM reporting_periods 
        WHERE 
            (
                (start_date <= ? AND end_date >= ?) OR  -- New period starts during existing period
                (start_date <= ? AND end_date >= ?) OR  -- New period ends during existing period
                (start_date >= ? AND end_date <= ?)     -- New period is contained within existing period
            )";
    
    // If excluding a specific period (for updates), add that condition
    $params = [$end_date, $start_date, $end_date, $start_date, $start_date, $end_date];
    
    if ($exclude_period_id !== null) {
        $query .= " AND period_id != ?";
        $params[] = $exclude_period_id;
    }
    
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $overlapping_periods = [];
    while ($row = $result->fetch_assoc()) {
        $overlapping_periods[] = [
            'period_id' => $row['period_id'],
            'quarter' => $row['quarter'],
            'year' => $row['year'],
            'start_date' => $row['start_date'],
            'end_date' => $row['end_date']
        ];
    }
    
    $stmt->close();
    
    // Return result
    echo json_encode([
        'success' => true,
        'overlaps' => count($overlapping_periods) > 0,
        'periods' => $overlapping_periods
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Error $e) {
    error_log("Error in check_period_overlap.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'A system error occurred. Please try again.'
    ]);
}
?>
