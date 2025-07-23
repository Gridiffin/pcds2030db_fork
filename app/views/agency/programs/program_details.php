<?php
/**
 * Enhanced Program Details View
 * 
 * Displays comprehensive information about a specific program including
 * submissions, targets, attachments, and timeline.
 * Refactored to use base.php layout with modular partials and MVC architecture.
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
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_details_data.php';
require_once PROJECT_ROOT_PATH . 'app/lib/program_status_helpers.php';

// Verify user is an agency
if (!is_agency()) {
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
    header('Location: view_programs.php');
    exit;
}

// Get all program details data using the data helper
$program_data = get_program_details_view_data($program_id);

if (!$program_data) {
    $_SESSION['message'] = 'Program not found or access denied.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
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
$can_edit = $program_data['permissions']['can_edit'];
$can_view = $program_data['permissions']['can_view'];
$is_owner = $program_data['permissions']['is_owner'];

// Set page title and bundles
$pageTitle = 'Program Details';
$cssBundle = 'agency-program-details'; // CSS bundle for program details module
$jsBundle = 'agency-program-details';

// Back button URL depends on source
$allSectorsUrl = APP_URL . '/app/views/agency/sectors/view_all_sectors.php';
$myProgramsUrl = APP_URL . '/app/views/agency/programs/view_programs.php';
$backUrl = $source === 'all_sectors' ? $allSectorsUrl : $myProgramsUrl;

// Configure modern page header
$program_display_name = '';
if (!empty($program['program_number'])) {
    $program_display_name = '<span class="badge bg-info me-2" title="Program Number">' . htmlspecialchars($program['program_number']) . '</span>';
}
$program_display_name .= htmlspecialchars($program['program_name']);

$header_config = [
    'title' => 'Program Details',
    'subtitle' => $program_display_name,
    'variant' => 'white',
    'actions' => [
        [
            'url' => $backUrl,
            'text' => $source === 'all_sectors' ? 'Back to All Sectors' : 'Back to My Programs',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Add edit button if user can edit
if ($can_edit) {
    $header_config['actions'][] = [
        'url' => 'edit_program.php?id=' . $program_id,
        'text' => 'Edit Program',
        'icon' => 'fas fa-edit',
        'class' => 'btn-primary'
    ];
}

// Extract additional variables for easier access in partials
$is_draft = $program_data['is_draft'];
$alert_flags = $program_data['alert_flags'];
$related_programs = $program_data['related_programs'];

// Set content file for base layout
$contentFile = __DIR__ . '/partials/program_details_content.php';

// Include base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
?>