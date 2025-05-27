<?php
/**
 * Check if a reporting period already exists
 * 
 * AJAX endpoint to check if a period with the specified quarter and year already exists
 */

// Include necessary files
require_once '../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';

// Verify user is logged in and is admin
if (!is_logged_in() || !is_admin()) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Check if required parameters are provided
if (!isset($_POST['quarter']) || !isset($_POST['year'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Missing required parameters'
    ]);
    exit;
}

// Get parameters
$quarter = intval($_POST['quarter']);
$year = intval($_POST['year']);

// Validate parameters
if ($quarter < 1 || $quarter > 6 || $year < 2000 || $year > 2099) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid parameters'
    ]);
    exit;
}

// Check if period exists
try {
    global $pdo;
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM reporting_periods 
        WHERE quarter = :quarter AND year = :year
    ");
    
    $stmt->execute([
        ':quarter' => $quarter,
        ':year' => $year
    ]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $exists = $result['count'] > 0;
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'exists' => $exists
    ]);
    
} catch (PDOException $e) {
    // Log error
    error_log('Database error in check_period_exists.php: ' . $e->getMessage());
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred'
    ]);
}
