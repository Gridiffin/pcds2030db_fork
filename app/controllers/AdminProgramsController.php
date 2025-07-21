<?php
/**
 * Admin Programs Controller
 * 
 * This controller handles the business logic for the admin programs page.
 * It retrieves data from the database and prepares it for the view.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(__DIR__)) . '/');
}

require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';
require_once PROJECT_ROOT_PATH . 'app/lib/initiative_functions.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get message from session if available
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? 'info';

// Clear message from session
if (isset($_SESSION['message'])) {
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Set page title
$pageTitle = 'Admin Programs';

// Get active initiatives for filtering
$active_initiatives = get_initiatives_for_select(true);

// Get all agencies for filtering
$agencies = [];
$agency_query = "SELECT u.agency_id, a.agency_name 
                FROM users u 
                JOIN agency a ON u.agency_id = a.agency_id 
                WHERE u.role IN ('agency', 'focal') AND u.is_active = 1 
                GROUP BY u.agency_id, a.agency_name
                ORDER BY a.agency_name";
$agency_result = $conn->query($agency_query);
if ($agency_result) {
    while ($row = $agency_result->fetch_assoc()) {
        $agencies[] = $row;
    }
}

// Initialize program arrays
$programs = [];
$programs_with_drafts = [];
$programs_with_submissions = [];
$programs_without_submissions = [];

// Get current period for programs context
$current_period = get_current_reporting_period();
$period_id = $current_period['period_id'] ?? null;

// Get all programs for admin (no agency filtering)
$all_programs = get_admin_programs_list($period_id, []);
$programs = $all_programs;

// Process programs and separate into appropriate arrays
foreach ($programs as $program) {
    // Check if program has any submissions
    if (isset($program['submission_id']) && $program['submission_id']) {
        // Program has submissions
        if (isset($program['is_draft']) && $program['is_draft']) {
            // Latest submission is a draft
            $programs_with_drafts[] = $program;
        } else {
            // Latest submission is finalized
            $programs_with_submissions[] = $program;
        }
    } else {
        // Program has no submissions (program template)
        $programs_without_submissions[] = $program;
    }
}

// The controller's job is done. It has prepared all the necessary variables.
// The view file will be included by the entry point (e.g., index.php). 