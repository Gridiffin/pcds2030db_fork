<?php
/**
 * Toggle Period Status AJAX Endpoint
 * 
 * Allows admins to quickly toggle a reporting period's status between open/closed.
 */

// Include necessary files
require_once '../config/config.php';
require_once '../includes/db_connect.php';
require_once '../includes/session.php';
require_once '../includes/functions.php';
require_once '../includes/admin_functions.php';

// Ensure user is admin
if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Permission denied']);
    exit;
}

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get period ID and requested status from POST data
$period_id = isset($_POST['period_id']) ? intval($_POST['period_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : '';

if (!$period_id) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing period ID']);
    exit;
}

// First check if updated_at column exists, if not add it
$check_column = "SHOW COLUMNS FROM reporting_periods LIKE 'updated_at'";
$column_result = $conn->query($check_column);

if ($column_result->num_rows === 0) {
    // Column doesn't exist, add it
    $alter_query = "ALTER TABLE reporting_periods 
                    ADD COLUMN updated_at TIMESTAMP NOT NULL 
                    DEFAULT CURRENT_TIMESTAMP 
                    ON UPDATE CURRENT_TIMESTAMP";
    $conn->query($alter_query);
}

// Call function to update period status
$result = update_reporting_period_status($period_id, $status);

// Return result as JSON
header('Content-Type: application/json');
echo json_encode($result);
exit;
