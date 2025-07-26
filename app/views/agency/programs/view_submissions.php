<?php
/**
 * View Submission Details Page
 * 
 * Displays detailed information for a specific program submission
 * in a specific reporting period. Refactored to use base.php layout
 * with modular partials.
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
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/submission_data.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get parameters from URL
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : 0;
$is_finalize_mode = isset($_GET['finalize']) && $_GET['finalize'] == '1';

// Validate required parameters
if (!$program_id || !$period_id) {
    $_SESSION['message'] = 'Missing required parameters (program_id and period_id).';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get all submission data using the data helper
$submission_data = get_submission_view_data($program_id, $period_id);

if (!$submission_data) {
    $_SESSION['message'] = 'Submission not found or access denied.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Extract data for easier access in views
$program = $submission_data['program'];
$period = $submission_data['period'];
$submission = $submission_data['submission'];
$targets = $submission_data['targets'];
$attachments = $submission_data['attachments'];
$rating_info = $submission_data['rating_info'];
$can_edit = $submission_data['permissions']['can_edit'];
$can_view = $submission_data['permissions']['can_view'];

// Set up base layout variables
$pageTitle = 'View Submission - ' . $program['program_name'] . ' (' . $submission['period_display'] . ')';
$cssBundle = 'agency-view-submissions'; // Vite bundle for view submissions page
$jsBundle = 'agency-view-submissions';

// Configure modern page header
$header_config = [
    'title' => 'View Submission Details',
    'subtitle' => $program['program_name'] . ' - ' . $submission['period_display'],
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'view_programs.php',
            'text' => 'Back to Programs',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Add appropriate action buttons based on mode and permissions
if ($is_finalize_mode && is_focal_user() && isset($submission['is_draft']) && $submission['is_draft']) {
    // Finalization mode for focal users with draft submissions
    $header_config['actions'][] = [
        'url' => 'edit_submission.php?program_id=' . $program_id . '&period_id=' . $period_id,
        'text' => 'Edit Submission',
        'icon' => 'fas fa-edit',
        'class' => 'btn-outline-primary me-2'
    ];
    $header_config['actions'][] = [
        'url' => '#',
        'text' => 'Finalize Submission',
        'icon' => 'fas fa-check-circle',
        'class' => 'btn-success',
        'onclick' => 'confirmFinalization(' . $program_id . ', ' . $period_id . ', \'' . addslashes($program['program_name']) . '\'); return false;'
    ];
} elseif ($can_edit && !$is_finalize_mode) {
    // Normal edit mode
    $header_config['actions'][] = [
        'url' => 'edit_submission.php?program_id=' . $program_id . '&period_id=' . $period_id,
        'text' => 'Edit Submission',
        'icon' => 'fas fa-edit',
        'class' => 'btn-primary'
    ];
}

// Set content file for base layout
$contentFile = __DIR__ . '/partials/view_submissions_content.php';

// Include base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
?>