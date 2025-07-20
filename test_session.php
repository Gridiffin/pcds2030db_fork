<?php
/**
 * Simple test to check session authentication for AJAX
 */
require_once 'app/config/config.php';
require_once 'app/lib/db_connect.php';
require_once 'app/lib/session.php';
require_once 'app/lib/agencies/core.php';

echo "Session ID: " . session_id() . "\n";
echo "Session data:\n";
print_r($_SESSION);
echo "is_logged_in(): " . (function_exists('is_logged_in') ? (is_logged_in() ? 'true' : 'false') : 'function not found') . "\n";
echo "is_agency(): " . (function_exists('is_agency') ? (is_agency() ? 'true' : 'false') : 'function not found') . "\n";

if (isset($_SESSION['user_id'])) {
    echo "User ID: " . $_SESSION['user_id'] . "\n";
}
if (isset($_SESSION['role'])) {
    echo "Role: " . $_SESSION['role'] . "\n";
}
if (isset($_SESSION['agency_id'])) {
    echo "Agency ID: " . $_SESSION['agency_id'] . "\n";
}
?>
