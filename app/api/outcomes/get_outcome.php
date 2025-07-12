<?php
/**
 * Get Outcome API Endpoint
 * 
 * Fetches outcome data by ID for chart visualization.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/outcomes.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';

// Validate user is authenticated
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Authentication required'
    ]);
    exit;
}

// Check if outcome_id is provided
if (!isset($_GET['outcome_id']) || !is_numeric($_GET['outcome_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid outcome ID'
    ]);
    exit;
}

$outcome_id = (int) $_GET['outcome_id'];

// Get outcome data from new outcomes table
$outcome = get_outcome_by_id($outcome_id);

if (!$outcome) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Outcome not found'
    ]);
    exit;
}

// Return outcome data
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'outcome' => [
        'id' => $outcome['id'],
        'code' => $outcome['code'],
        'type' => $outcome['type'],
        'title' => $outcome['title'],
        'description' => $outcome['description'],
        'data' => $outcome['data'],
        'updated_at' => $outcome['updated_at']
    ]
]);
exit;
