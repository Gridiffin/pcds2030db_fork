<?php
/**
 * Enhanced Admin Edit Submission Page
 * 
 * Allows admin users to edit existing submissions for any program.
 * Refactored to use base_admin.php layout following agency side patterns.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/admin_edit_submission_data.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get parameters from URL
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : 0;

// Validate required parameters
if (!$program_id) {
    $_SESSION['message'] = 'Missing required parameter (program_id).';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// If no period_id provided, show period selection
if (!$period_id) {
    // Get available periods for this program to show selection
    require_once PROJECT_ROOT_PATH . 'app/lib/admins/admin_program_details_data.php';
    $program_data = get_admin_program_details_view_data($program_id);
    
    if (!$program_data) {
        $_SESSION['message'] = 'Program not found or access denied.';
        $_SESSION['message_type'] = 'danger';
        header('Location: programs.php');
        exit;
    }
    
    // Extract data for period selection
    $program = $program_data['program'] ?? null;
    $agency_info = $program_data['agency_info'] ?? null;
    $all_periods = $program_data['all_periods'] ?? [];
    $latest_by_period = $program_data['latest_by_period'] ?? [];
    
    // Set up base layout variables for period selection
    $pageTitle = 'Select Reporting Period';
    if ($program) {
        $pageTitle .= ' - ' . ($program['program_name'] ?? 'Unknown Program');
    }
    $cssBundle = 'admin-edit-submission';
    $jsBundle = 'admin-edit-submission';
    
    // Configure page header for period selection
    $header_config = [
        'title' => 'Select Reporting Period',
        'subtitle' => ($program['program_name'] ?? 'Unknown Program') . ' - ' . ($agency_info['agency_name'] ?? 'Unknown Agency'),
        'variant' => 'white',
        'actions' => [
            [
                'url' => 'program_details.php?id=' . $program_id,
                'text' => 'Back to Program Details',
                'icon' => 'fas fa-arrow-left',
                'class' => 'btn-outline-secondary'
            ]
        ]
    ];
    
    // Set content file for period selection
    $contentFile = __DIR__ . '/partials/admin_select_submission_period_content.php';
    
    // Include base layout for period selection
    require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';
    exit;
}

// Get all edit submission data using the admin data helper
$edit_data = get_admin_edit_submission_data($program_id, $period_id);

if (!$edit_data) {
    $_SESSION['message'] = 'Program or submission not found or access denied.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Extract data for easier access in views
$program = $edit_data['program'];
$agency_info = $edit_data['agency_info'];
$submission = $edit_data['submission'];
$period = $edit_data['period'];
$targets = $edit_data['targets'];
$attachments = $edit_data['attachments'];
$is_new_submission = $edit_data['is_new_submission'];

// Admin can edit all submissions
$can_edit = true;
$can_view = true;

// Set up base layout variables
$pageTitle = 'Edit Submission - ' . $program['program_name'] . ' (' . $period['period_display'] . ')';
$cssBundle = 'admin-edit-submission'; // Vite bundle for admin edit submission page
$jsBundle = 'admin-edit-submission';

// Configure modern page header
$header_config = [
    'title' => $is_new_submission ? 'Add New Submission' : 'Edit Submission',
    'subtitle' => $program['program_name'] . ' - ' . $agency_info['agency_name'] . ' (' . $period['period_display'] . ')',
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'view_submissions.php?program_id=' . $program_id,
            'text' => 'Back to Submissions',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Add additional actions
$header_config['actions'][] = [
    'url' => 'program_details.php?id=' . $program_id,
    'text' => 'Program Details',
    'icon' => 'fas fa-info-circle',
    'class' => 'btn-info'
];

// Set content file for base layout
$contentFile = __DIR__ . '/partials/admin_edit_submission_content.php';

// Include base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';