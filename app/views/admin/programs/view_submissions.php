<?php
/**
 * Admin View Submission Details Page
 * 
 * Displays detailed information for a specific program submission
 * in a specific reporting period. Refactored to use base_admin.php layout
 * following agency side patterns.
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
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/admin_submission_data.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get parameters from URL
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : null;

// Validate required parameters
if (!$program_id) {
    $_SESSION['message'] = 'Missing required parameter (program_id).';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Get all submission data using the admin data helper
$submission_data = get_admin_submission_view_data($program_id, $period_id);

if (!$submission_data) {
    $_SESSION['message'] = 'Program not found or no submissions available.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Extract data for easier access in views
$program = $submission_data['program'];
$agency_info = $submission_data['agency_info'];
$submission = $submission_data['submission'];
$targets = $submission_data['targets'];
$attachments = $submission_data['attachments'];
$rating_info = $submission_data['rating_info'];
$all_submissions = $submission_data['all_submissions'];

// If no specific period_id and multiple submissions exist, show period selection
if (!$period_id && count($all_submissions) > 1) {
    // Set up base layout variables for period selection
    $pageTitle = 'Select Submission Period - ' . $program['program_name'];
    $cssBundle = 'admin-view-submissions';
    $jsBundle = 'admin-view-submissions';
    
    // Configure page header for period selection
    $header_config = [
        'title' => 'Select Submission to View',
        'subtitle' => $program['program_name'] . ' - ' . $agency_info['agency_name'],
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
    $contentFile = __DIR__ . '/partials/admin_select_view_submission_period_content.php';
    
    // Include base layout for period selection
    require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';
    exit;
}

// Admin can view and edit all submissions
$can_edit = true;
$can_view = true;

// Set up base layout variables
$pageTitle = 'View Submission - ' . $program['program_name'];
if ($submission) {
    $pageTitle .= ' (' . $submission['period_display'] . ')';
}
$cssBundle = 'admin-view-submissions'; // Vite bundle for admin view submissions page
$jsBundle = 'admin-view-submissions';

// Configure modern page header
$header_config = [
    'title' => 'View Submission Details',
    'subtitle' => $program['program_name'] . ' - ' . $agency_info['agency_name'],
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'programs.php',
            'text' => 'Back to Programs',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Add period display to subtitle if specific submission
if ($submission) {
    $header_config['subtitle'] .= ' (' . $submission['period_display'] . ')';
}

// Add action buttons
$header_config['actions'][] = [
    'url' => 'program_details.php?id=' . $program_id,
    'text' => 'Program Details',
    'icon' => 'fas fa-info-circle',
    'class' => 'btn-info'
];

if ($submission && $period_id) {
    $header_config['actions'][] = [
        'url' => 'edit_submission.php?program_id=' . $program_id . '&period_id=' . $period_id,
        'text' => 'Edit Submission',
        'icon' => 'fas fa-edit',
        'class' => 'btn-primary'
    ];
}

// Set content file for base layout
$contentFile = __DIR__ . '/partials/admin_view_submissions_content.php';

// Include base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';