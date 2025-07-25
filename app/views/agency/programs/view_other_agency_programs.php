<?php
/**
 * View Other Agencies' Programs - Placeholder
 * 
 * This feature will allow viewing programs from other agencies.
 * Converted to base.php layout system with bundle loading.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/index.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set up base layout variables
$pageTitle = 'Other Agencies\' Programs';
$cssBundle = 'agency-view-other-programs'; // Vite bundle for view other agency programs page
$jsBundle = null;

// Configure modern page header
$header_config = [
    'title' => 'Other Agencies\' Programs',
    'subtitle' => 'Browse programs from other agencies',
    'variant' => 'blue'
];

// Content will be rendered inline
$contentFile = null;
?>

<main>
    <div class="container-fluid">
        <div class="card shadow-sm">
            <div class="card-body text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-university fa-4x text-primary"></i>
                </div>
                <h2 class="mb-3">Other Agencies' Programs</h2>
                <p class="lead text-muted">This feature will allow you to browse and view programs from other agencies.</p>
                <p class="text-muted">Coming soon!</p>
            </div>
        </div>
    </div>
</main>

<?php
// Include base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
?> 