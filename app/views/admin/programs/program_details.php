<?php
/**
 * Enhanced Admin Program Details View
 * 
 * Displays comprehensive information about a specific program including
 * submissions, targets, attachments, timeline, and agency information.
 * Refactored to use base_admin.php layout with modular partials following agency side patterns.
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
require_once PROJECT_ROOT_PATH . 'app/lib/admins/admin_program_details_data.php';
require_once PROJECT_ROOT_PATH . 'app/lib/program_status_helpers.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get program ID from URL
$program_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get source parameter to determine where the user came from
$source = isset($_GET['source']) ? $_GET['source'] : '';

if (!$program_id) {
    $_SESSION['message'] = 'Invalid program ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Get all program details data using the admin data helper
$program_data = get_admin_program_details_view_data($program_id);

if (!$program_data) {
    $_SESSION['message'] = 'Program not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Extract data for easier access in views
$program = $program_data['program'];
$has_submissions = $program_data['has_submissions'];
$latest_submission = $program_data['latest_submission'];
$targets = $program_data['targets'];
$rating = $program_data['rating'];
$remarks = $program_data['remarks'];
$all_periods = $program_data['all_periods'];
$latest_by_period = $program_data['latest_by_period'];
$submission_history = $program_data['submission_history'];
$hold_points = $program_data['hold_points'];
$attachments = $program_data['attachments'];
$agency_info = $program_data['agency_info'];
$submission_info = $program_data['submission_info'];
$program_assignees = $program_data['program_assignees'];

// Admin can view and edit all programs
$can_edit = true;
$can_view = true;
$is_owner = false; // Admin is not the owner but has edit rights

// Set page title and bundles
$pageTitle = 'Program Details';
$cssBundle = 'admin-program-details'; // CSS bundle for admin program details module
$jsBundle = 'admin-program-details'; // JS bundle for admin program details module

// Back button URL
$backUrl = APP_URL . '/app/views/admin/programs/programs.php';

// Configure modern page header
$program_display_name = '';
if (!empty($program['program_number'])) {
    $program_display_name = '<span class="badge bg-info me-2" title="Program Number">' . htmlspecialchars($program['program_number']) . '</span>';
}
$program_display_name .= htmlspecialchars($program['program_name']);

// Add agency information to subtitle
$agency_subtitle = '';
if (!empty($agency_info['agency_name'])) {
    $agency_subtitle = ' <small class="text-muted">(' . htmlspecialchars($agency_info['agency_name']);
    if (!empty($agency_info['agency_acronym'])) {
        $agency_subtitle .= ' - ' . htmlspecialchars($agency_info['agency_acronym']);
    }
    $agency_subtitle .= ')</small>';
}

$header_config = [
    'title' => 'Program Details',
    'subtitle' => $program_display_name . $agency_subtitle,
    'subtitle_html' => true,
    'variant' => 'white',
    'actions' => [
        [
            'url' => $backUrl,
            'text' => 'Back to Programs',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Add admin action buttons
$header_config['actions'][] = [
    'url' => 'edit_program.php?id=' . $program_id,
    'text' => 'Edit Program',
    'icon' => 'fas fa-edit',
    'class' => 'btn-primary'
];

$header_config['actions'][] = [
    'url' => 'view_submissions.php?program_id=' . $program_id,
    'text' => 'View Submissions',
    'icon' => 'fas fa-file-alt',
    'class' => 'btn-success'
];

// Add Edit Submission action - links to period selection
$header_config['actions'][] = [
    'url' => 'edit_submission.php?program_id=' . $program_id,
    'text' => 'Edit Submission',
    'icon' => 'fas fa-edit',
    'class' => 'btn-warning'
];

// Extract additional variables for easier access in partials
$is_draft = false; // Admin only sees finalized submissions
$alert_flags = $program_data['alert_flags'] ?? [];
$related_programs = $program_data['related_programs'] ?? [];

// Set content file for base layout
$contentFile = __DIR__ . '/partials/admin_program_details_content.php';

// Include base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';