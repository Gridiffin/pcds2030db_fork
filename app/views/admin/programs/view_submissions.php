<?php
/**
 * View Submission Details Page
 * 
 * Displays detailed information for a specific program submission
 * in a specific reporting period. This is the detailed view page
 * that users reach after selecting a period from the modal connector.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'config/config.php';
require_once PROJECT_ROOT_PATH . 'lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'lib/session.php';
require_once PROJECT_ROOT_PATH . 'lib/functions.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/programs.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/program_attachments.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/program_permissions.php';
require_once PROJECT_ROOT_PATH . 'lib/admins/core.php';
require_once PROJECT_ROOT_PATH . 'lib/admins/program_management.php';
require_once PROJECT_ROOT_PATH . 'lib/admins/statistics.php';
require_once PROJECT_ROOT_PATH . 'lib/rating_helpers.php';

// Verify user is an admin
if (!is_admin()) {
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
    header('Location: programs.php');
    exit;
}

// Get program details
$program = get_admin_program_details($program_id);
if (!$program) {
    $_SESSION['message'] = 'Program not found or access denied.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Admin users have full access to all programs - no permission check needed

// Define permission levels for admin users
$can_view = true;  // Admin can view all programs
$can_edit = true;  // Admin can edit all programs
$is_owner = true;  // Admin has ownership rights over all programs

// Get the specific submission for this program and period
$submission_query = "SELECT ps.*, 
                            rp.year, rp.period_type, rp.period_number, rp.status as period_status,
                            CONCAT(rp.year, ' ', 
                                   CASE 
                                       WHEN rp.period_type = 'quarter' THEN CONCAT('Q', rp.period_number)
                                       WHEN rp.period_type = 'half' THEN CONCAT('H', rp.period_number)
                                       WHEN rp.period_type = 'yearly' THEN 'Yearly'
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

// Set page title
$pageTitle = 'View Submission (Admin) - ' . $program['program_name'] . ' (' . $submission['period_display'] . ')';

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'View Submission Details (Admin)',
    'subtitle' => $program['program_name'] . ' - ' . $submission['period_display'] . ' | Agency: ' . ($program['agency_name'] ?? 'Unknown'),
    'variant' => 'admin',
    'actions' => [
        [
            'url' => 'programs.php',
            'text' => 'Back to Programs',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Add edit button (admin can always edit)
$header_config['actions'][] = [
    'url' => 'edit_submission.php?program_id=' . $program_id . '&period_id=' . $period_id,
    'text' => 'Edit Submission',
    'icon' => 'fas fa-edit',
    'class' => 'btn-primary'
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<div class="container-fluid">
    <!-- Error/Success Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('<?= ucfirst($_SESSION['message_type']) ?>', <?= json_encode($_SESSION['message']) ?>, '<?= $_SESSION['message_type'] ?>');
            });
        </script>
        <?php 
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        ?>
    <?php endif; ?>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Submission Overview Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 me-3">
                            <i class="fas fa-file-alt me-2 text-white"></i>
                            Submission Details
                        </h5>
                        <div class="d-flex gap-3">
                            <!-- Submission Status Badge -->
                            <?php if ($submission['is_submitted']): ?>
                                <span class="badge bg-success px-3 py-2">
                                    <i class="fas fa-check-circle me-1"></i>Submitted
                                </span>
                            <?php elseif ($submission['is_draft']): ?>
                                <span class="badge bg-warning px-3 py-2">
                                    <i class="fas fa-edit me-1"></i>Draft
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary px-3 py-2">
                                    <i class="fas fa-clock me-1"></i>Not Started
                                </span>
                            <?php endif; ?>
                            
                            <!-- Period Status Badge -->
                            <span class="badge bg-<?php echo $submission['period_status'] === 'open' ? 'info' : 'secondary'; ?> px-3 py-2">
                                Period: <?php echo ucfirst($submission['period_status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Submission Description -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-2">
                            <i class="fas fa-align-left me-1"></i>Description
                        </h6>
                        <div class="bg-light p-3 rounded">
                            <?php if (!empty($submission['description'])): ?>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($submission['description'])); ?></p>
                            <?php else: ?>
                                <p class="text-muted mb-0 fst-italic">No description provided for this submission.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Submission Timeline -->
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-clock me-1"></i>Timeline
                            </h6>
                            <div class="d-flex flex-column gap-2">
                                <?php if (!empty($submission['submitted_at'])): ?>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-check text-success me-2"></i>
                                        <div>
                                            <div class="fw-medium">Submitted</div>
                                            <small class="text-muted">
                                                <?php echo date('F j, Y \a\t g:i A', strtotime($submission['submitted_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if (!empty($submission['updated_at'])): ?>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-edit text-info me-2"></i>
                                        <div>
                                            <div class="fw-medium">Last Updated</div>
                                            <small class="text-muted">
                                                <?php echo date('F j, Y \a\t g:i A', strtotime($submission['updated_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-user me-1"></i>Submitted By
                            </h6>
                            <?php if (!empty($submission['submitted_by_name'])): ?>
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-2" 
                                         style="width: 40px; height: 40px;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <div class="fw-medium">
                                            <?php echo htmlspecialchars($submission['submitted_by_fullname'] ?: $submission['submitted_by_name']); ?>
                                        </div>
                                        <?php if (!empty($submission['submitted_by_agency'])): ?>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($submission['submitted_by_agency']); ?>
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0">Information not available</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Program Targets Section -->
            <?php if (!empty($targets)): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bullseye me-2 text-white"></i>
                        Program Targets (<?php echo count($targets); ?>)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($targets as $index => $target): ?>
                            <div class="col-lg-6 mb-4">
                                <div class="card border-start border-4 border-primary">
                                    <div class="card-header bg-light">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="card-title mb-0 me-3">
                                                <i class="fas fa-target me-1"></i>
                                                Target <?php echo htmlspecialchars($target['target_number'] ?: ($index + 1)); ?>
                                            </h6>
                                            <span class="badge bg-<?php 
                                                echo match($target['status_indicator']) {
                                                    'not_started' => 'secondary',
                                                    'in_progress' => 'warning',
                                                    'completed' => 'success',
                                                    'delayed' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?> px-3 py-2">
                                                <i class="fas <?php 
                                                    echo match($target['status_indicator']) {
                                                        'not_started' => 'fa-clock',
                                                        'in_progress' => 'fa-spinner',
                                                        'completed' => 'fa-check-circle',
                                                        'delayed' => 'fa-exclamation-triangle',
                                                        default => 'fa-question'
                                                    };
                                                ?> me-1"></i>
                                                <?php echo ucwords(str_replace('_', ' ', $target['status_indicator'])); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        <!-- Target Description -->
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-2">
                                                <i class="fas fa-align-left me-1"></i>Target Name/Description
                                            </h6>
                                            <?php if (!empty($target['target_description'])): ?>
                                                <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($target['target_description'])); ?></p>
                                            <?php else: ?>
                                                <p class="text-muted mb-0 small">-</p>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Status Description -->
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-2">
                                                <i class="fas fa-info-circle me-1"></i>Achievements/Status Description
                                            </h6>
                                            <?php if (!empty($target['status_description'])): ?>
                                                <div class="bg-light p-2 rounded">
                                                    <p class="mb-0 small"><?php echo nl2br(htmlspecialchars($target['status_description'])); ?></p>
                                                </div>
                                            <?php else: ?>
                                                <p class="text-muted mb-0 small">-</p>
                                            <?php endif; ?>
                                        </div>

                                        <!-- Timeline -->
                                        <div class="mb-3">
                                            <h6 class="text-muted mb-2">
                                                <i class="fas fa-calendar-alt me-1"></i>Timeline
                                            </h6>
                                            <div class="row small">
                                                <div class="col-6">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-play text-success me-2"></i>
                                                        <div>
                                                            <div class="fw-medium">Start Date</div>
                                                            <small class="text-muted">
                                                                <?php echo !empty($target['start_date']) ? date('M j, Y', strtotime($target['start_date'])) : '-'; ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-6">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-flag text-danger me-2"></i>
                                                        <div>
                                                            <div class="fw-medium">End Date</div>
                                                            <small class="text-muted">
                                                                <?php echo !empty($target['end_date']) ? date('M j, Y', strtotime($target['end_date'])) : '-'; ?>
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Remarks -->
                                        <div class="mb-0">
                                            <h6 class="text-muted mb-2">
                                                <i class="fas fa-comment me-1"></i>Remarks
                                            </h6>
                                            <?php if (!empty($target['remarks'])): ?>
                                                <div class="alert alert-light mb-0">
                                                    <small><?php echo nl2br(htmlspecialchars($target['remarks'])); ?></small>
                                                </div>
                                            <?php else: ?>
                                                <p class="text-muted mb-0 small">-</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Targets Summary -->
                    <div class="mt-3 pt-3 border-top">
                        <div class="row text-center">
                            <?php
                            $target_stats = array_count_values(array_column($targets, 'status_indicator'));
                            ?>
                            <div class="col-3">
                                <div class="text-muted small">Not Started</div>
                                <div class="fw-bold text-secondary"><?php echo $target_stats['not_started'] ?? 0; ?></div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small">In Progress</div>
                                <div class="fw-bold text-warning"><?php echo $target_stats['in_progress'] ?? 0; ?></div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small">Completed</div>
                                <div class="fw-bold text-success"><?php echo $target_stats['completed'] ?? 0; ?></div>
                            </div>
                            <div class="col-3">
                                <div class="text-muted small">Delayed</div>
                                <div class="fw-bold text-danger"><?php echo $target_stats['delayed'] ?? 0; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <!-- No Targets State -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bullseye me-2 text-white"></i>
                        Program Targets
                    </h5>
                </div>
                <div class="card-body text-center py-4">
                    <div class="mb-3">
                        <i class="fas fa-target fa-3x text-muted"></i>
                    </div>
                    <h6 class="text-muted">No Targets Defined</h6>
                    <p class="text-muted mb-0">This submission doesn't have any targets defined yet.</p>
                    <?php if ($can_edit): ?>
                        <p class="text-muted small">You can add targets when editing this submission.</p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Program Attachments (if any) -->
            <?php
            // Get program attachments
            $attachments = get_program_attachments($program_id);
            if (!empty($attachments)):
            ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-paperclip me-2 text-success"></i>
                            Program Attachments
                        </h5>
                        <?php if ($can_edit): ?>
                            <a href="edit_submission.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $period_id; ?>" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-edit me-1"></i>Edit Submission
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($attachments as $attachment): ?>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center p-2 border rounded">
                                    <i class="fas fa-file text-primary me-2"></i>
                                    <div class="flex-grow-1">
                                        <div class="fw-medium"><?php echo htmlspecialchars($attachment['filename'] ?? 'Unknown file'); ?></div>
                                        <small class="text-muted">
                                            Uploaded: <?php echo !empty($attachment['uploaded_at']) ? date('M j, Y', strtotime($attachment['uploaded_at'])) : 'Unknown date'; ?>
                                            <?php if (!empty($attachment['uploaded_by'])): ?>
                                                <br>By: <span class="fw-medium"><?php echo htmlspecialchars($attachment['uploaded_by']); ?></span>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                    <a href="<?php echo htmlspecialchars($attachment['file_path'] ?? '#'); ?>" 
                                       class="btn btn-sm btn-outline-primary ms-2" target="_blank">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Program Summary Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Program Summary
                    </h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-5">Program:</dt>
                        <dd class="col-7"><?php echo htmlspecialchars($program['program_name']); ?></dd>
                        
                        <dt class="col-5">Number:</dt>
                        <dd class="col-7">
                            <?php if (!empty($program['program_number'])): ?>
                                <span class="badge bg-info"><?php echo htmlspecialchars($program['program_number']); ?></span>
                            <?php else: ?>
                                <span class="text-muted">Not assigned</span>
                            <?php endif; ?>
                        </dd>
                        
                        <dt class="col-5">Agency:</dt>
                        <dd class="col-7"><?php echo htmlspecialchars($program['agency_name'] ?? 'Unknown'); ?></dd>
                        
                        <dt class="col-5">Initiative:</dt>
                        <dd class="col-7">
                            <?php if (!empty($program['initiative_name'])): ?>
                                <?php echo htmlspecialchars($program['initiative_name']); ?>
                                <?php if (!empty($program['initiative_number'])): ?>
                                    <br><span class="badge bg-secondary mt-1"><?php echo htmlspecialchars($program['initiative_number']); ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted">Not linked</span>
                            <?php endif; ?>
                        </dd>
                        
                        <dt class="col-5">Rating:</dt>
                        <dd class="col-7">
                            <span class="badge" style="background-color: <?php echo $rating_info['color']; ?>; color: white;">
                                <?php echo $rating_info['label']; ?>
                            </span>
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Reporting Period Card -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-calendar-alt me-2"></i>Reporting Period
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center">
                        <div class="display-6 text-primary mb-2">
                            <?php echo htmlspecialchars($submission['period_display']); ?>
                        </div>
                        <div class="small text-muted">
                            <?php echo ucfirst($submission['period_type']); ?> Period 
                            <?php echo $submission['period_number']; ?> of <?php echo $submission['year']; ?>
                        </div>
                        <div class="mt-2">
                            <span class="badge bg-<?php echo $submission['period_status'] === 'open' ? 'success' : 'secondary'; ?>">
                                <?php echo ucfirst($submission['period_status']); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Card -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($can_edit): ?>
                            <a href="edit_submission.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $period_id; ?>" 
                               class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Edit This Submission
                            </a>
                            
                            <?php if ($submission['is_draft']): ?>
                                <button type="button" class="btn btn-success" 
                                        onclick="submitSubmission(<?php echo $submission['submission_id']; ?>)">
                                    <i class="fas fa-paper-plane me-2"></i>Submit for Review
                                </button>
                            <?php endif; ?>
                            
                            <a href="add_submission.php?program_id=<?php echo $program_id; ?>" 
                               class="btn btn-outline-primary">
                                <i class="fas fa-plus me-2"></i>Add New Submission
                            </a>
                        <?php else: ?>
                            <!-- No edit access - show basic navigation only -->
                        <?php endif; ?>
                        
                        <a href="program_details.php?id=<?php echo $program_id; ?>" 
                           class="btn btn-outline-secondary">
                            <i class="fas fa-chart-line me-2"></i>View Program Details
                        </a>
                        
                        <a href="programs.php" class="btn btn-outline-info">
                            <i class="fas fa-list me-2"></i>All Programs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Submit submission functionality
function submitSubmission(submissionId) {
    if (confirm('Are you sure you want to submit this submission for review? Once submitted, you may not be able to edit it.')) {
        // Add loading state and redirect to submission handler
        window.location.href = `submit_submission.php?submission_id=${submissionId}&program_id=<?php echo $program_id; ?>`;
    }
}

// Pass PHP variables to JavaScript
window.programId = <?php echo $program_id; ?>;
window.periodId = <?php echo $period_id; ?>;
window.submissionId = <?php echo $submission['submission_id']; ?>;
window.APP_URL = '<?php echo APP_URL; ?>';
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
