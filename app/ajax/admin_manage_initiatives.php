<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/lib/db_connect.php';
require_once dirname(__DIR__) . '/lib/session.php';
require_once dirname(__DIR__) . '/lib/initiative_functions.php';
require_once dirname(__DIR__) . '/lib/db_names_helper.php';

if (!is_admin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$filters = [];
if (!empty($search)) {
    $filters['search'] = $search;
}
if ($status_filter !== '') {
    $filters['is_active'] = $status_filter === 'active' ? 1 : 0;
}

try {
    $initiatives = get_all_initiatives($filters);
    echo json_encode(['success' => true, 'initiatives' => $initiatives]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error', 'details' => $e->getMessage()]);
} 