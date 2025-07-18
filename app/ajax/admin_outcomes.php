<?php
// app/ajax/admin_outcomes.php
// AJAX endpoint for admin outcome detail editing

require_once '../../config/config.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/admins/outcomes.php';

header('Content-Type: application/json');

if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    // Fetch outcome details by ID
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid outcome ID']);
        exit;
    }
    $outcome = get_outcome_by_id($id); // Assumes this function exists
    if ($outcome) {
        echo json_encode(['success' => true, 'data' => $outcome]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Outcome not found']);
    }
    exit;
}

if ($method === 'POST') {
    // Update outcome details
    $input = json_decode(file_get_contents('php://input'), true);
    $id = isset($input['id']) ? intval($input['id']) : 0;
    $title = isset($input['title']) ? trim($input['title']) : '';
    $description = isset($input['description']) ? trim($input['description']) : '';
    if ($id <= 0 || $title === '' || $description === '') {
        echo json_encode(['success' => false, 'error' => 'Missing or invalid data']);
        exit;
    }
    $result = update_outcome($id, $title, $description); // Assumes this function exists
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update outcome']);
    }
    exit;
}

// Method not allowed
http_response_code(405);
echo json_encode(['success' => false, 'error' => 'Method not allowed']); 