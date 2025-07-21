<?php
/**
 * View Submission Details Page - Refactored with Best Practices
 * 
 * Displays detailed information for a specific program submission
 * in a specific reporting period. This is the detailed view page
 * that users reach after selecting a period from the modal connector.
 * 
 * Modular structure with base.php layout and Vite bundling.
 */

// Define project root path for consistent file references - Anti-pattern fix (Bug #15)
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}

// Include necessary files with proper app/ prefix - Anti-pattern fix (Bug #16)
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/programs.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_attachments.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_permissions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get parameters from URL
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : 0;

// Validate required parameters
if (!$program_id || !$period_id) {
    $_SESSION['message'] = 'Missing required parameters (program_id and period_id).';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get program details
$program = get_program_details($program_id, true);
if (!$program) {
    $_SESSION['message'] = 'Program not found or access denied.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Check if user has access to this program
if (!can_view_program($program_id)) {
    $_SESSION['message'] = 'You do not have access to this program.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Define permission levels
$can_view = can_view_program($program_id);
$can_edit = can_edit_program($program_id);
$is_owner = is_program_owner($program_id);

// Get the specific submission for this program and period
$submission_query = "SELECT ps.*, 
                            rp.year, rp.period_type, rp.period_number, rp.status as period_status,
                            CONCAT(rp.year, ' ', 
                                   CASE 
                                       WHEN rp.period_type = 'quarterly' THEN CONCAT('Q', rp.period_number)
                                       ELSE CONCAT(UPPER(LEFT(rp.period_type, 1)), SUBSTRING(rp.period_type, 2), ' ', rp.period_number)
                                   END
                            ) as period_display,
                            u.username as submitted_by_name, 
                            u.fullname as submitted_by_fullname,
                            a.agency_name as submitted_by_agency
                     FROM program_submissions ps
                     LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id
                     LEFT JOIN users u ON ps.submitted_by = u.user_id
                     LEFT JOIN agency a ON u.agency_id = a.agency_id
                     WHERE ps.program_id = ? AND ps.period_id = ? AND ps.is_deleted = 0
                     ORDER BY ps.updated_at DESC
                     LIMIT 1";

$stmt = $conn->prepare($submission_query);
$stmt->bind_param("ii", $program_id, $period_id);
$stmt->execute();
$submission = $stmt->get_result()->fetch_assoc();

// Check if submission exists
if (!$submission) {
    $_SESSION['message'] = 'No submission found for this program and reporting period.';
    $_SESSION['message_type'] = 'warning';
    header('Location: program_details.php?id=' . $program_id);
    exit;
}

// Get targets for this submission
$targets_query = "SELECT * FROM program_targets 
                  WHERE submission_id = ? AND is_deleted = 0 
                  ORDER BY target_number ASC, target_id ASC";

$stmt = $conn->prepare($targets_query);
$stmt->bind_param("i", $submission['submission_id']);
$stmt->execute();
$targets = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get program rating information
$program_rating = $program['rating'] ?? 'not_started';
$rating_info = get_rating_info($program_rating);

// Configure modern page header
$header_config = [
    'title' => 'View Submission Details',
    'subtitle' => htmlspecialchars($program['program_name']) . ' - ' . htmlspecialchars($submission['period_display']),
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

// Add edit button if user can edit
if ($can_edit) {
    $header_config['actions'][] = [
        'url' => 'edit_submission.php?program_id=' . $program_id . '&period_id=' . $period_id,
        'text' => 'Edit Submission',
        'icon' => 'fas fa-edit',
        'class' => 'btn-primary'
    ];
}

// Set up base layout variables - Anti-pattern fix (Bug #12)
$pageTitle = 'View Submission - ' . $program['program_name'] . ' (' . $submission['period_display'] . ')';
$cssBundle = 'view-submissions'; // Vite bundling - Anti-pattern fix (Bug #1)
$jsBundle = 'view-submissions';

// Set content file for base layout - Anti-pattern fix (Bug #12)
$contentFile = __DIR__ . '/partials/view_submissions/view_submissions_content.php';

// Include base layout - it will render header, nav, content, and footer
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
