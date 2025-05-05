<?php
/**
 * Audit Log
 * 
 * Admin page to view audit logs.
 */

require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/admin_functions.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ../../login.php');
    exit;
}

// Set page title
$pageTitle = 'Audit Log';

// Include header and admin navigation
require_once '../layouts/header.php';
require_once '../layouts/admin_nav.php';
?>

<div class="container-fluid px-4 py-4">
    <h1 class="h2 mb-4">Audit Log</h1>
    <p class="text-muted">This page will display audit log entries.</p>
    <!-- TODO: Implement audit log table and functionality -->
</div>
<?php
require_once '../layouts/footer.php';
?>
