<?php
// AJAX handler for admin user tables
require_once '../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is admin
if (!is_admin()) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// Pagination parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = isset($_GET['per_page']) ? max(1, intval($_GET['per_page'])) : 20;

// Get all users and separate them by role
$all_users = get_all_users();
$admin_users = array_filter($all_users, function($user) {
    return $user['role'] === 'admin';
});
$agency_users = array_filter($all_users, function($user) {
    return $user['role'] === 'agency' || $user['role'] === 'focal';
});

// Helper to paginate arrays
function paginate_array($array, $page, $per_page) {
    $total = count($array);
    $total_pages = max(1, ceil($total / $per_page));
    $page = min($page, $total_pages);
    $offset = ($page - 1) * $per_page;
    $items = array_slice($array, $offset, $per_page);
    return [
        'items' => $items,
        'page' => $page,
        'per_page' => $per_page,
        'total' => $total,
        'total_pages' => $total_pages
    ];
}

// Paginate admin and agency users
$admin_pagination = paginate_array(array_values($admin_users), $page, $per_page);
$agency_pagination = paginate_array(array_values($agency_users), $page, $per_page);

// Render tables using the partial
$tableTitle = 'Admin Users';
$roleType = 'admin';
$users = $admin_pagination['items'];
$pagination = $admin_pagination;
include ROOT_PATH . 'app/views/admin/users/_user_table.php';

$tableTitle = 'Agency Users';
$roleType = 'agency';
$users = $agency_pagination['items'];
$pagination = $agency_pagination;
include ROOT_PATH . 'app/views/admin/users/_user_table.php';

exit; 