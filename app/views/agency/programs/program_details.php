<?php
/**
 * Program Details View
 * 
 * Displays detailed information about a specific program.
 */

// Define the root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/agencies/index.php';
require_once ROOT_PATH . 'app/lib/agencies/programs.php';
require_once ROOT_PATH . 'app/lib/rating_helpers.php';
require_once ROOT_PATH . 'app/lib/agencies/program_attachments.php';
require_once ROOT_PATH . 'app/lib/agencies/program-details/data-processor.php';
require_once ROOT_PATH . 'app/lib/agencies/program-details/error-handler.php';

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

// Get program details
$program = get_program_details($program_id, true); // Always allow cross-agency viewing

// Allow all users to view any program
$allow_view = true;
$is_owner = isset($program['owner_agency_id']) && $program['owner_agency_id'] == $_SESSION['user_id'];

if (!$program) {
    $_SESSION['message'] = 'Program not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get program attachments (permission check is handled by the function)
$program_attachments = get_program_attachments($program_id);

// Get related programs if this program is linked to an initiative
$related_programs = [];
if (!empty($program['initiative_id'])) {
    $related_programs = get_related_programs_by_initiative(
        $program['initiative_id'], 
        $program_id, 
        $source === 'all_sectors'
    );
}

// Get latest submission if available
$latest_submission = $program['current_submission'] ?? null;
$has_submissions = !empty($latest_submission);
$is_draft = $has_submissions && isset($latest_submission['is_draft']) && $latest_submission['is_draft'];

// Process submission data if available
$content = [];
$targets = [];
$rating = 'not-started';
$remarks = '';

if ($has_submissions && isset($latest_submission['content_json']) && !empty($latest_submission['content_json'])) {
    if (is_string($latest_submission['content_json'])) {
        $content = json_decode($latest_submission['content_json'], true) ?: [];
    } elseif (is_array($latest_submission['content_json'])) {
        $content = $latest_submission['content_json'];
    }
    
    // Extract targets from content
    if (isset($content['targets']) && is_array($content['targets'])) {
        $targets = [];
        foreach ($content['targets'] as $target) {
            if (isset($target['target_text'])) {
                $targets[] = [
                    'target_number' => $target['target_number'] ?? '',
                    'text' => $target['target_text'],
                    'status_description' => $target['status_description'] ?? '',
                    'start_date' => $target['start_date'] ?? '',
                    'end_date' => $target['end_date'] ?? ''
                ];
            } else {
                $targets[] = $target;
            }
        }
        $rating = $content['rating'] ?? $latest_submission['status'] ?? 'not-started';
        $remarks = $content['remarks'] ?? '';
    } else {
        // Legacy data format
        $target_text = $content['target'] ?? $latest_submission['target'] ?? '';
        $status_description = $content['status_text'] ?? $latest_submission['status_text'] ?? '';
        
        if (strpos($target_text, ';') !== false) {
            $target_parts = array_map('trim', explode(';', $target_text));
            $status_parts = array_map('trim', explode(';', $status_description));
            
            $targets = [];
            foreach ($target_parts as $index => $target_part) {
                if (!empty($target_part)) {
                    $targets[] = [
                        'text' => $target_part,
                        'status_description' => isset($status_parts[$index]) ? $status_parts[$index] : ''
                    ];
                }
            }
        } else {
            $targets = [
                [
                    'text' => $target_text,
                    'status_description' => $status_description
                ]
            ];
        }
        
        $rating = $latest_submission['status'] ?? 'not-started';
        $remarks = $latest_submission['remarks'] ?? '';
    }
} elseif ($has_submissions && !empty($latest_submission['target'])) {
    // Fallback to direct properties
    $targets[] = [
        'text' => $latest_submission['target'],
        'status_description' => $latest_submission['status_text'] ?? ''
    ];
    $rating = $latest_submission['status'] ?? 'not-started';
    $remarks = $latest_submission['remarks'] ?? '';
}

// Get all reporting periods for display
$all_periods = get_all_reporting_periods();
$latest_by_period = $program['latest_submissions_by_period'] ?? [];

// Set page title
$pageTitle = 'Program Details';

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/utilities/rating_utils.js',
    APP_URL . '/assets/js/agency/program_details.js'
];

// Additional CSS for attachments
$additionalCSS = [
    APP_URL . '/assets/css/admin/programs.css', // Reuse admin attachment styling
    APP_URL . '/assets/css/components/period-performance.css' // Performance section styling
];

// Include header
require_once '../../layouts/header.php';

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
    'subtitle_html' => true, // Allow HTML in subtitle
    'variant' => 'white',
    'actions' => [
        [
            'url' => $backUrl,
            'text' => 'Back to ' . ($source === 'all_sectors' ? 'All Sectors' : 'My Programs'),
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Include modern page header
require_once '../../layouts/page_header.php';

// Define status mapping for display
$status_map = [
    'on-track' => ['label' => 'On Track', 'class' => 'warning', 'icon' => 'fas fa-chart-line'],
    'on-track-yearly' => ['label' => 'On Track for Year', 'class' => 'warning', 'icon' => 'fas fa-calendar-check'],
    'target-achieved' => ['label' => 'Monthly Target Achieved', 'class' => 'success', 'icon' => 'fas fa-check-circle'],
    'delayed' => ['label' => 'Delayed', 'class' => 'danger', 'icon' => 'fas fa-exclamation-circle'],
    'severe-delay' => ['label' => 'Severe Delays', 'class' => 'danger', 'icon' => 'fas fa-exclamation-triangle'],
    'completed' => ['label' => 'Completed', 'class' => 'primary', 'icon' => 'fas fa-flag-checkered'],
    'not-started' => ['label' => 'Not Started', 'class' => 'secondary', 'icon' => 'fas fa-hourglass-start']
];

// Convert status for display
if ($has_submissions) {
    $status = convert_legacy_status($rating);
    if (!isset($status_map[$status])) {
        $status = 'not-started';
    }
} else {
    // Program has no submissions - show as "not-started"
    $status = 'not-started';
}

// Initialize alert flags
$showDraftAlert = $is_draft && $is_owner; // Only show draft alert if user owns the program and has draft submission
$showNoTargetsAlert = $has_submissions && empty($targets) && $is_owner; // Only show no targets alert if user owns the program and has submissions but no targets
$showNoSubmissionsAlert = !$has_submissions && $is_owner; // Show alert if program has no submissions yet
?>

<!-- Toast Notifications -->
<?php if ($showDraftAlert): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast('Draft Submission', 'This program is in draft mode. <a href="<?= APP_URL ?>/app/views/agency/programs/edit_program.php?id=<?= $program_id ?>" class="alert-link">Click here to edit and submit the final version</a>.', 'warning', 10000);
    });
</script>
<?php endif; ?>

<?php if ($showNoTargetsAlert): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast('No Targets', 'No targets have been added for this program. <a href="<?= APP_URL ?>/app/views/agency/programs/edit_program.php?id=<?= $program_id ?>" class="alert-link">Add targets</a>.', 'info', 10000);
    });
</script>
<?php endif; ?>

<?php if ($showNoSubmissionsAlert): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast('Program Template', 'This program is a template. <a href="<?= APP_URL ?>/app/views/agency/programs/add_submission.php?program_id=<?= $program_id ?>" class="alert-link">Add your first progress report</a>.', 'info', 10000);
    });
</script>
<?php endif; ?>

<!-- Program Overview Card -->
<div class="card program-details-card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-clipboard-list me-2"></i>Program Information
        </h5>
        <div class="action-buttons">
            <span class="badge status-badge bg-<?php echo $status_map[$status]['class']; ?> py-2 px-3">
                <i class="<?php echo $status_map[$status]['icon']; ?> me-1"></i> 
                <?php echo $status_map[$status]['label']; ?>
            </span>
            <?php if ($is_owner): ?>
                <a href="<?php echo APP_URL; ?>/app/views/agency/programs/add_submission.php?program_id=<?php echo $program_id; ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus me-1"></i> Add Submission
                </a>
                <a href="<?php echo APP_URL; ?>/app/views/agency/programs/edit_program.php?id=<?php echo $program_id; ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-edit me-1"></i> Edit Program
                </a>
                <?php if ($is_draft): ?>
                <span class="badge bg-warning text-dark" title="Latest submission is in draft status">
                    <i class="fas fa-pencil-alt me-1"></i> Draft Submission
                </span>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Program Basic Info -->
            <div class="col-lg-12 mb-4">
                <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-4 text-muted">Program Name:</div>
                            <div class="col-md-8 fw-medium">
                                <?php echo htmlspecialchars($program['program_name']); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-4 text-muted">Program Number:</div>
                            <div class="col-md-8">
                                <?php if (!empty($program['program_number'])): ?>
                                    <span class="badge bg-info" title="Program Number"><?php echo htmlspecialchars($program['program_number']); ?></span>
                                <?php else: ?>
                                    <span class="text-muted">Not specified</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-4 text-muted">Program Type:</div>
                            <div class="col-md-8">
                                <?php if (isset($program['is_assigned']) && $program['is_assigned']): ?>
                                    <span class="badge bg-info">Assigned Program</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Agency-Created</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-4 text-muted">Initiative:</div>
                            <div class="col-md-8">
                                <?php if (!empty($program['initiative_name'])): ?>
                                    <span class="fw-medium"><?php echo htmlspecialchars($program['initiative_name']); ?></span>
                                    <?php if (!empty($program['initiative_number'])): ?>
                                        <span class="badge bg-secondary ms-2" title="Initiative Number"><?php echo htmlspecialchars($program['initiative_number']); ?></span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Not specified</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-4 text-muted">Timeline:</div>
                            <div class="col-md-8">
                                <?php if (!empty($program['start_date'])): ?>
                                    <i class="far fa-calendar-alt me-1"></i>
                                    <?php echo date('M j, Y', strtotime($program['start_date'])); ?>
                                    <?php if (!empty($program['end_date'])): ?>
                                        <i class="fas fa-long-arrow-alt-right mx-1"></i>
                                        <?php echo date('M j, Y', strtotime($program['end_date'])); ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-muted">Not specified</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-4 text-muted">Last Updated:</div>
                            <div class="col-md-8">
                                <?php if ($has_submissions && isset($latest_submission['submission_date']) && $latest_submission['submission_date']): ?>
                                    <i class="far fa-clock me-1"></i>
                                    <?php echo date('M j, Y', strtotime($latest_submission['submission_date'])); ?>
                                    <span class="text-muted small ms-2">(Latest submission)</span>
                                <?php elseif (isset($program['created_at']) && $program['created_at']): ?>
                                    <i class="far fa-clock me-1"></i>
                                    <?php echo date('M j, Y', strtotime($program['created_at'])); ?>
                                    <span class="text-muted small ms-2">(Program created)</span>
                                <?php else: ?>
                                    <span class="text-muted">Not available</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($program['description'])): ?>
                <div class="mt-4">
                    <h6 class="border-bottom pb-2 mb-3">Description</h6>
                    <div class="p-3 bg-light rounded border">
                        <?php echo nl2br(htmlspecialchars($program['description'])); ?>
                    </div>
                </div>
                <?php endif; ?>            </div>
        </div>
    </div>
</div>

<?php if ($has_submissions): ?>
<!-- Latest Submission Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-file-alt me-2"></i>Latest Submission
        </h5>
    </div>
    <div class="card-body">
        <?php if (!empty($targets)): ?>
            <div class="mb-4">
                <h6 class="border-bottom pb-2 mb-3">Targets</h6>
                <div class="row">
                    <?php foreach ($targets as $index => $target): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card border">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        Target <?php echo $index + 1; ?>
                                        <?php if (!empty($target['target_number'])): ?>
                                            <span class="badge bg-info ms-2"><?php echo htmlspecialchars($target['target_number']); ?></span>
                                        <?php endif; ?>
                                    </h6>
                                    <p class="card-text"><?php echo htmlspecialchars($target['text']); ?></p>
                                    <?php if (!empty($target['status_description'])): ?>
                                        <div class="mt-2">
                                            <strong>Status:</strong> <?php echo htmlspecialchars($target['status_description']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($target['start_date']) || !empty($target['end_date'])): ?>
                                        <div class="mt-2 small text-muted">
                                            <?php if (!empty($target['start_date'])): ?>
                                                <i class="far fa-calendar-alt me-1"></i>Start: <?php echo date('M j, Y', strtotime($target['start_date'])); ?>
                                            <?php endif; ?>
                                            <?php if (!empty($target['end_date'])): ?>
                                                <br><i class="far fa-calendar-alt me-1"></i>End: <?php echo date('M j, Y', strtotime($target['end_date'])); ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($remarks)): ?>
            <div class="mb-4">
                <h6 class="border-bottom pb-2 mb-3">Remarks</h6>
                <div class="p-3 bg-light rounded border">
                    <?php echo nl2br(htmlspecialchars($remarks)); ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="row">
            <div class="col-md-6">
                <strong>Submission Date:</strong>
                <?php if (isset($latest_submission['submission_date']) && $latest_submission['submission_date']): ?>
                    <?php echo date('M j, Y g:i A', strtotime($latest_submission['submission_date'])); ?>
                <?php else: ?>
                    <span class="text-muted">Not submitted</span>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <strong>Status:</strong>
                <span class="badge bg-<?php echo $status_map[$status]['class']; ?>">
                    <i class="<?php echo $status_map[$status]['icon']; ?> me-1"></i> 
                    <?php echo $status_map[$status]['label']; ?>
                </span>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<!-- No Submissions Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-folder-open me-2"></i>Submissions
        </h5>
    </div>
    <div class="card-body text-center py-4">
        <div class="mb-3">
            <i class="fas fa-folder-open fa-3x text-muted"></i>
        </div>
        <h6 class="text-muted">No Submissions Yet</h6>
        <p class="text-muted mb-3">This program is ready for submissions. Add your first submission to start tracking progress.</p>
        <?php if ($is_owner): ?>
            <a href="<?php echo APP_URL; ?>/app/views/agency/programs/add_submission.php?program_id=<?php echo $program_id; ?>" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add First Submission
            </a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<!-- Program Attachments Section -->
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">
            <i class="fas fa-paperclip me-2 text-primary"></i>Program Attachments
        </h5>
        <span class="badge bg-secondary">
            <?php echo count($program_attachments); ?> 
            <?php echo count($program_attachments) === 1 ? 'file' : 'files'; ?>
        </span>
    </div>
    <div class="card-body">
        <?php if (!empty($program_attachments)): ?>
            <div class="attachments-list">
                <?php foreach ($program_attachments as $attachment): ?>
                    <div class="attachment-item d-flex justify-content-between align-items-center border rounded p-3 mb-3">
                        <div class="attachment-info d-flex align-items-center">
                            <div class="attachment-icon me-3">
                                <i class="fas <?php echo get_file_icon($attachment['mime_type']); ?> fa-2x text-primary"></i>
                            </div>
                            <div class="attachment-details">
                                <h6 class="mb-1 fw-bold"><?php echo htmlspecialchars($attachment['original_filename']); ?></h6>
                                <div class="attachment-meta text-muted small">
                                    <span class="me-3">
                                        <i class="fas fa-hdd me-1"></i>
                                        <?php echo $attachment['file_size_formatted']; ?>
                                    </span>
                                    <span class="me-3">
                                        <i class="fas fa-calendar me-1"></i>
                                        <?php echo date('M j, Y \a\t g:i A', strtotime($attachment['upload_date'])); ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-user me-1"></i>
                                        <?php echo htmlspecialchars($attachment['uploaded_by'] ?? 'Unknown'); ?>
                                    </span>
                                </div>
                                <?php if (!empty($attachment['description'])): ?>
                                    <div class="attachment-description mt-2">
                                        <small class="text-muted">
                                            <i class="fas fa-comment me-1"></i>
                                            <?php echo htmlspecialchars($attachment['description']); ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="attachment-actions">
                            <a href="<?php echo APP_URL; ?>/app/ajax/download_program_attachment.php?id=<?php echo $attachment['attachment_id']; ?>" 
                               class="btn btn-sm btn-outline-primary" 
                               target="_blank"
                               title="Download <?php echo htmlspecialchars($attachment['original_filename']); ?>">
                                <i class="fas fa-download me-1"></i> Download
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <div class="mb-3">
                    <i class="fas fa-folder-open fa-3x text-muted"></i>
                </div>
                <h6 class="text-muted">No Attachments</h6>
                <p class="text-muted mb-0">
                    This program doesn't have any supporting documents uploaded.
                    <?php if ($is_owner): ?>
                        <br><a href="<?php echo APP_URL; ?>/app/views/agency/programs/edit_program.php?id=<?php echo $program_id; ?>" class="text-decoration-none">Upload attachments</a> in the program editor.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>



<?php if (!$is_owner): ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Note:</strong> You are viewing this program in read-only mode. Only the program's owning agency can submit updates.
</div>
<?php endif; ?>

<!-- JavaScript Configuration -->
<script>
// Pass PHP variables to JavaScript
window.currentUser = {
    id: <?php echo $_SESSION['user_id'] ?? 'null'; ?>,
    agency_id: <?php echo $_SESSION['agency_id'] ?? 'null'; ?>,
    role: '<?php echo $_SESSION['role'] ?? ''; ?>'
};
window.isOwner = <?php echo $is_owner ? 'true' : 'false'; ?>;
window.programId = <?php echo $program_id; ?>;
window.APP_URL = '<?php echo APP_URL; ?>';
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>



