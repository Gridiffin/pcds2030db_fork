<?php
/**
 * Add Submission to Program
 *
 * This file handles the business logic for adding a program submission.
 * It integrates with the base layout system for rendering.
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
require_once PROJECT_ROOT_PATH . 'app/lib/notifications_core.php';

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

// Check if user can edit this program (add submissions)
if (!can_edit_program($program_id)) {
    $_SESSION['message'] = 'You do not have permission to add submissions to this program.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get reporting periods for dropdown (excluding half-yearly and yearly periods)
$reporting_periods = get_reporting_periods_for_submissions(true);

// Get existing submissions for this program
$stmt = $conn->prepare("
    SELECT ps.period_id, ps.is_draft, ps.is_submitted, ps.submission_id,
           rp.year, rp.period_type, rp.period_number
    FROM program_submissions ps
    JOIN reporting_periods rp ON ps.period_id = rp.period_id
    WHERE ps.program_id = ? AND ps.is_deleted = 0
    ORDER BY rp.year DESC, rp.period_number ASC
");
$stmt->bind_param("i", $program_id);
$stmt->execute();
$existing_submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Process form submission
$message = '';
$messageType = '';

// Get message from session if available (for error messages from redirects)
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    $messageType = $_SESSION['message_type'] ?? 'info';
    
    // Clear message from session
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Additional cleanup: Clear any notification-related session messages that might have been set elsewhere
if (isset($_SESSION['notification_message'])) {
    unset($_SESSION['notification_message']);
}
if (isset($_SESSION['notification_type'])) {
    unset($_SESSION['notification_type']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submission_data = [
        'program_id' => $program_id,
        'period_id' => !empty($_POST['period_id']) ? intval($_POST['period_id']) : null,
        'description' => $_POST['description'] ?? '',
        'targets' => []
    ];

    if (isset($_POST['target_text']) && is_array($_POST['target_text'])) {
        foreach ($_POST['target_text'] as $i => $target_text) {
            if (!empty(trim($target_text))) {
                $submission_data['targets'][] = [
                    'target_number' => trim($_POST['target_number'][$i] ?? ''),
                    'target_text' => trim($target_text),
                    'target_status' => trim($_POST['target_status'][$i] ?? 'not_started'),
                    'status_description' => trim($_POST['target_status_description'][$i] ?? ''),
                ];
            }
        }
    }

    $result = create_program_submission($submission_data);

    if (isset($result['success']) && $result['success']) {
        // Send submission creation notification
        if (isset($result['submission_id']) && function_exists('notify_submission_created')) {
            $notification_result = notify_submission_created($result['submission_id'], $program_id, $_SESSION['user_id'], $submission_data['period_id']);
            error_log("Notification result for submission {$result['submission_id']}: " . ($notification_result ? 'SUCCESS' : 'FAILED'));
        }
        
        // Set success message and show redirecting modal
        $message = $result['message'];
        $messageType = 'success';
        
        // Add JavaScript to show redirecting modal
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                // Show success message first
                showToast('Success', '" . addslashes($result['message']) . "', 'success');
                
                // Show redirecting modal after a short delay
                setTimeout(function() {
                    const redirectModal = document.createElement('div');
                    redirectModal.innerHTML = `
                        <div class='modal fade' tabindex='-1'>
                            <div class='modal-dialog'>
                                <div class='modal-content'>
                                    <div class='modal-body text-center py-4'>
                                        <i class='fas fa-check-circle text-success' style='font-size: 3rem;'></i>
                                        <h5 class='mt-3'>Submission Created Successfully!</h5>
                                        <p class='text-muted'>Redirecting to programs page...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    document.body.appendChild(redirectModal);
                    const tempModal = new bootstrap.Modal(redirectModal.querySelector('.modal'));
                    tempModal.show();
                    
                    // Redirect after 2 seconds
                    setTimeout(function() {
                        window.location.href = 'view_programs.php';
                    }, 2000);
                }, 1000);
            });
        </script>";
        
        // Don't exit - let the page render with the message
    } else {
        $message = $result['error'] ?? 'An error occurred while creating the submission.';
        $messageType = 'danger';
    }
}

// Page configuration
$pageTitle = 'Add Submission - ' . htmlspecialchars($program['program_name']);
$bodyClass = 'add-submission-page';
$cssBundle = 'agency-add-submission'; // Vite bundle for add submission page
$jsBundle = 'agency-add-submission';

// Configure modern page header
$header_config = [
    'title' => 'Add Submission',
    'subtitle' => 'Add a submission for ' . htmlspecialchars($program['program_name']),
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'view_programs.php',
            'text' => 'Back to Programs',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-primary'
        ]
    ]
];

// Set content file
$contentFile = 'partials/add_submission_content.php';

// Include base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base.php'; 