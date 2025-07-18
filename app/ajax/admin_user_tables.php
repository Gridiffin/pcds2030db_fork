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

// Get all users and separate them by role
$all_users = get_all_users();
$admin_users = array_filter($all_users, function($user) {
    return $user['role'] === 'admin';
});
$agency_users = array_filter($all_users, function($user) {
    return $user['role'] === 'agency' || $user['role'] === 'focal';
});

// Render tables using the partial
$tableTitle = 'Admin Users';
$roleType = 'admin';
$users = $admin_users;
include ROOT_PATH . 'app/views/admin/users/_user_table.php';

$tableTitle = 'Agency Users';
$roleType = 'agency';
$users = $agency_users;
include ROOT_PATH . 'app/views/admin/users/_user_table.php';

exit; 