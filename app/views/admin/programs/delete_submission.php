<?php
/**
 * Admin Delete Submission
 * 
 * Allows administrators to soft-delete a specific submission.
 * Useful for cleaning up duplicate drafts or erroneous submissions.
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
require_once PROJECT_ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get parameters
$submission_id = isset($_REQUEST['submission_id']) ? intval($_REQUEST['submission_id']) : 0;

// Validate submission ID
if (!$submission_id) {
    $_SESSION['message'] = 'Invalid submission ID.';
    $_SESSION['message_type'] = 'danger';
    
    // Try to redirect back effectively
    if (isset($_GET['program_id'])) {
        header('Location: view_submissions.php?program_id=' . intval($_GET['program_id']));
    } else {
        header('Location: programs.php');
    }
    exit;
}

// Fetch submission details to confirm existence and get context for redirection/logging
$query = "SELECT ps.*, p.program_name, rp.period_type, rp.period_number, rp.year
          FROM program_submissions ps
          JOIN programs p ON ps.program_id = p.program_id
          LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id
          WHERE ps.submission_id = ? AND ps.is_deleted = 0";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $submission_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['message'] = 'Submission not found or already deleted.';
    $_SESSION['message_type'] = 'danger';
    if (isset($_GET['program_id'])) {
        header('Location: view_submissions.php?program_id=' . intval($_GET['program_id']));
    } else {
        header('Location: programs.php');
    }
    exit;
}

$submission = $result->fetch_assoc();
$program_id = $submission['program_id'];
$period_display = ($submission['year'] ?? '') . ' ' . ucfirst($submission['period_type'] ?? '') . ' ' . ($submission['period_number'] ?? '');

// Process deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete'])) {
    
    // Soft delete the submission
    $delete_query = "UPDATE program_submissions SET is_deleted = 1 WHERE submission_id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $submission_id);
    
    if ($stmt->execute()) {
        // Log the action
        log_audit_action(
            'delete_submission', 
            "Deleted submission #$submission_id for program {$submission['program_name']} ($period_display)", 
            'success', 
            $_SESSION['user_id']
        );
        
        $_SESSION['message'] = 'Submission successfully deleted.';
        $_SESSION['message_type'] = 'success';
        
        // Redirect back to view submissions page
        header("Location: view_submissions.php?program_id=$program_id");
        exit;
    } else {
        $_SESSION['message'] = 'Error deleting submission: ' . $conn->error;
        $_SESSION['message_type'] = 'danger';
    }
}

// Set page title and bundles
$pageTitle = 'Delete Submission - ' . htmlspecialchars($submission['program_name']);
$cssBundle = 'admin-common';
$jsBundle = 'admin-common';

// Configure page header
$header_config = [
    'title' => 'Delete Submission',
    'subtitle' => 'Confirmation Required',
    'variant' => 'danger',
    'actions' => [
        [
            'url' => 'view_submissions.php?program_id=' . $program_id,
            'text' => 'Cancel',
            'icon' => 'fas fa-times',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Create inline content partial
$contentFile = __DIR__ . '/partials/delete_submission_content.php';

// Write temporary content file
$content = <<<'PHP'
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-danger shadow-sm">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title m-0">
                        <i class="fas fa-exclamation-triangle me-2"></i> Confirm Deletion
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-trash-alt text-danger fa-3x mb-3"></i>
                        <h4>Are you sure?</h4>
                        <p class="text-muted">
                            You are about to delete the submission for <strong><?php echo htmlspecialchars($submission['program_name']); ?></strong> 
                            for the period <strong><?php echo htmlspecialchars($period_display); ?></strong>.
                        </p>
                        <div class="alert alert-warning text-start">
                            <i class="fas fa-info-circle me-2"></i>
                            This will mark the submission as deleted. It will be removed from views but remains in the database for audit purposes.
                        </div>
                    </div>
                    
                    <form method="POST" action="delete_submission.php">
                        <input type="hidden" name="submission_id" value="<?php echo $submission_id; ?>">
                        <input type="hidden" name="confirm_delete" value="1">
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="fas fa-trash me-2"></i> Yes, Delete Submission
                            </button>
                            <a href="view_submissions.php?program_id=<?php echo $program_id; ?>" class="btn btn-light btn-lg">
                                <i class="fas fa-times me-2"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
PHP;

file_put_contents($contentFile, $content);

// Include base layout
require_once PROJECT_ROOT_PATH . 'app/views/layouts/base_admin.php';

// Clean up temporary file
if (file_exists($contentFile)) {
    unlink($contentFile);
}
