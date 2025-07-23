<?php
/**
 * Enhanced Edit Submission Page
 * 
 * Allows users to edit existing submissions or add new ones based on period selection.
 * Features a period selector at the top and dynamic content based on selected period.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', dirname(dirname(dirname(dirname(__DIR__)))) . DIRECTORY_SEPARATOR);
}


// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/programs.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/program_permissions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/initiative_functions.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get program ID from URL
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;

if (!$program_id) {
    $_SESSION['message'] = 'No program specified.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get program details
$program = get_program_details($program_id);
if (!$program) {
    $_SESSION['message'] = 'Program not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Check if user can edit this program
if (!can_edit_program($program_id)) {
    $_SESSION['message'] = 'You do not have permission to edit submissions for this program.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get reporting periods for dropdown
$reporting_periods = get_reporting_periods_for_dropdown(true);

// Get existing submissions for this program to show which periods are already covered
$existing_submissions_query = "SELECT ps.period_id, ps.is_draft, ps.is_submitted, ps.submission_id,
                                     rp.year, rp.period_type, rp.period_number, rp.status
                              FROM program_submissions ps
                              JOIN reporting_periods rp ON ps.period_id = rp.period_id
                              WHERE ps.program_id = ? AND ps.is_deleted = 0
                              ORDER BY rp.year DESC, rp.period_number ASC";
$stmt = $conn->prepare($existing_submissions_query);
$stmt->bind_param("i", $program_id);
$stmt->execute();
$existing_submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Create a map of period_id to submission for quick lookup
$submissions_by_period = [];
foreach ($existing_submissions as $submission) {
    $submissions_by_period[$submission['period_id']] = $submission;
}

// Set page title
$pageTitle = 'Edit Submission - ' . $program['program_name'];
$cssBundle = 'agency-edit-submission'; // Vite bundle for edit submission page
$jsBundle = 'agency-edit-submission';

// Additional CSS for edit submission page - NO LONGER NEEDED, HANDLED BY BUNDLE
// $additionalCSS = [
//     APP_URL . '/assets/css/agency/edit_submission.css',
// ];

// Include header - NO LONGER NEEDED, HANDLED BY base.php
// require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Edit Submission',
    'subtitle' => 'Edit submissions for ' . htmlspecialchars($program['program_name']),
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

// Content will be rendered via partial
$contentFile = __DIR__ . '/partials/edit_submission_content.php';
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php';
?> 