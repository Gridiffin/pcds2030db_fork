<?php
/**
 * Enhanced Program Details View
 * 
 * Displays comprehensive information about a specific program including
 * submissions, targets, attachments, and timeline.
 */

// Define the root path
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/agencies/index.php';
require_once ROOT_PATH . 'app/lib/agencies/programs.php';
require_once ROOT_PATH . 'app/lib/rating_helpers.php';
require_once ROOT_PATH . 'app/lib/agencies/program_attachments.php';
require_once ROOT_PATH . 'app/lib/agencies/program_agency_assignments.php';
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

// Get comprehensive program details
$program = get_program_details($program_id, true);

// Check permissions using new system
$allow_view = can_view_program($program_id);
$can_edit = can_edit_program($program_id);
$is_owner = is_program_owner($program_id);

if (!$allow_view) {
    $_SESSION['message'] = 'You do not have permission to view this program.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

if (!$program) {
    $_SESSION['message'] = 'Program not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get program attachments
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

// Determine rating for badge - use same logic as view_programs.php for consistency
$rating = isset($program['rating']) ? $program['rating'] : 'not_started';

// Get all reporting periods for display
$all_periods = get_all_reporting_periods();
$latest_by_period = $program['latest_submissions_by_period'] ?? [];

// Get submission history for timeline
$submission_history = get_program_edit_history($program_id);

// Set page title
$pageTitle = 'Enhanced Program Details';

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/utilities/rating_utils.js',
    APP_URL . '/assets/js/agency/enhanced_program_details.js'
];

// Additional CSS
$additionalCSS = [
    APP_URL . '/assets/css/components/program-details.css',
    APP_URL . '/assets/css/components/period-performance.css'
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
    'subtitle_html' => true,
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

// Define status mapping for display using new rating system
$status_map = [
    'not_started' => ['label' => 'Not Started', 'class' => 'secondary', 'icon' => 'fas fa-hourglass-start'],
    'on_track_for_year' => ['label' => 'On Track for Year', 'class' => 'warning', 'icon' => 'fas fa-calendar-check'],
    'monthly_target_achieved' => ['label' => 'Monthly Target Achieved', 'class' => 'success', 'icon' => 'fas fa-check-circle'],
    'severe_delay' => ['label' => 'Severe Delays', 'class' => 'danger', 'icon' => 'fas fa-exclamation-triangle']
];

// Convert status for display using new rating system
if ($has_submissions) {
    $status = $rating;
    if (!isset($status_map[$status])) {
        $status = 'not_started';
    }
} else {
    $status = 'not_started';
}

// Initialize alert flags
$showDraftAlert = $is_draft && $is_owner;
$showNoTargetsAlert = $has_submissions && empty($targets) && $is_owner;
$showNoSubmissionsAlert = !$has_submissions && $is_owner;
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

<!-- Enhanced Program Overview -->
<div class="container-fluid">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Program Information Card -->
            <div class="card program-info-card shadow-sm mb-4">
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
                        <div class="col-md-6 mb-3">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-tag text-primary"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Program Name</div>
                                    <div class="info-value fw-medium"><?php echo htmlspecialchars($program['program_name']); ?></div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-hashtag text-info"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Program Number</div>
                                    <div class="info-value">
                                        <?php if (!empty($program['program_number'])): ?>
                                            <span class="badge bg-info"><?php echo htmlspecialchars($program['program_number']); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">Not specified</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-layer-group text-success"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Program Type</div>
                                    <div class="info-value">
                                        <?php if (isset($program['is_assigned']) && $program['is_assigned']): ?>
                                            <span class="badge bg-info">Assigned Program</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Agency-Created</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-project-diagram text-warning"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Initiative</div>
                                    <div class="info-value">
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
                        <div class="col-md-6 mb-3">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-calendar-alt text-danger"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Timeline</div>
                                    <div class="info-value">
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
                        <div class="col-md-6 mb-3">
                            <div class="info-item">
                                <div class="info-icon">
                                    <i class="fas fa-clock text-secondary"></i>
                                </div>
                                <div class="info-content">
                                    <div class="info-label">Last Updated</div>
                                    <div class="info-value">
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
                    </div>
                    
                    <?php if (!empty($program['description'])): ?>
                    <div class="mt-4">
                        <h6 class="info-section-title">
                            <i class="fas fa-align-left me-2"></i>Description
                        </h6>
                        <div class="description-box">
                            <?php echo nl2br(htmlspecialchars($program['description'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Targets and Progress Section -->
            <?php if ($has_submissions && !empty($targets)): ?>
            <div class="card performance-card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bullseye me-2"></i>Targets & Progress
                    </h5>
                </div>
                <div class="card-body">
                    <div class="targets-container">
                        <?php foreach ($targets as $index => $target): ?>
                            <div class="target-item mb-4">
                                <div class="target-header d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="target-title mb-0">
                                        <i class="fas fa-target me-2 text-primary"></i>
                                        Target <?php echo $index + 1; ?>
                                        <?php if (!empty($target['target_number'])): ?>
                                            <span class="badge bg-info ms-2"><?php echo htmlspecialchars($target['target_number']); ?></span>
                                        <?php endif; ?>
                                    </h6>
                                    <div class="target-progress">
                                        <div class="progress" style="width: 100px; height: 8px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 75%"></div>
                                        </div>
                                        <small class="text-muted">75% Complete</small>
                                    </div>
                                </div>
                                <div class="target-content">
                                    <div class="target-description mb-3">
                                        <strong>Target:</strong>
                                        <p class="mb-2"><?php echo htmlspecialchars($target['text']); ?></p>
                                    </div>
                                    <?php if (!empty($target['status_description'])): ?>
                                        <div class="target-status mb-3">
                                            <strong>Status:</strong>
                                            <div class="status-pill rating-target-achieved">
                                                <i class="fas fa-info-circle"></i>
                                                <?php echo htmlspecialchars($target['status_description']); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($target['start_date']) || !empty($target['end_date'])): ?>
                                        <div class="target-timeline">
                                            <strong>Timeline:</strong>
                                            <div class="timeline-info mt-2">
                                                <?php if (!empty($target['start_date'])): ?>
                                                    <span class="badge bg-light text-dark me-2">
                                                        <i class="far fa-calendar-alt me-1"></i>Start: <?php echo date('M j, Y', strtotime($target['start_date'])); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php if (!empty($target['end_date'])): ?>
                                                    <span class="badge bg-light text-dark">
                                                        <i class="far fa-calendar-alt me-1"></i>End: <?php echo date('M j, Y', strtotime($target['end_date'])); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if (!empty($remarks)): ?>
                        <div class="remarks-section mt-4">
                            <h6 class="info-section-title">
                                <i class="fas fa-comment me-2"></i>Remarks
                            </h6>
                            <div class="remarks-container">
                                <?php echo nl2br(htmlspecialchars($remarks)); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Submission Timeline -->
            <?php if (!empty($submission_history['submissions'])): ?>
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>Submission Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline-container">
                        <?php foreach ($submission_history['submissions'] as $submission): ?>
                            <div class="timeline-item">
                                <div class="timeline-marker">
                                    <i class="fas fa-circle"></i>
                                </div>
                                <div class="timeline-content">
                                    <div class="timeline-header">
                                        <h6 class="timeline-title"><?php echo htmlspecialchars($submission['period_display']); ?></h6>
                                        <span class="badge bg-<?php echo ($submission['is_draft'] ? 'warning' : 'success'); ?>">
                                            <?php echo $submission['is_draft_label']; ?>
                                        </span>
                                    </div>
                                    <div class="timeline-meta">
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            <?php echo htmlspecialchars($submission['submitted_by_name'] ?? 'Unknown'); ?>
                                            <i class="fas fa-clock ms-2 me-1"></i>
                                            <?php echo htmlspecialchars($submission['formatted_date']); ?>
                                        </small>
                                    </div>
                                    <?php if ($is_owner): ?>
                                        <div class="timeline-actions mt-2">
                                            <a href="<?php echo APP_URL; ?>/app/views/agency/programs/view_submissions.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $submission['period_id']; ?>" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                        </div>
                                    <?php endif; ?>
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
            <!-- Quick Actions -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <?php if ($is_owner): ?>
                            <a href="<?php echo APP_URL; ?>/app/views/agency/programs/add_submission.php?program_id=<?php echo $program_id; ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Add New Submission
                            </a>
                            <a href="<?php echo APP_URL; ?>/app/views/agency/programs/edit_program.php?id=<?php echo $program_id; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-2"></i>Edit Program
                            </a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewSubmissionsModal">
                            <i class="fas fa-list-alt me-2"></i>View Submissions
                        </button>
                    </div>
                </div>
            </div>

            <!-- Program Statistics -->
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-chart-pie me-2"></i>Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="stat-item d-flex justify-content-between align-items-center mb-3">
                        <span>Total Submissions</span>
                        <span class="badge bg-primary"><?php echo count($submission_history['submissions']); ?></span>
                    </div>
                    <div class="stat-item d-flex justify-content-between align-items-center mb-3">
                        <span>Targets</span>
                        <span class="badge bg-info"><?php echo count($targets); ?></span>
                    </div>
                    <div class="stat-item d-flex justify-content-between align-items-center mb-3">
                        <span>Attachments</span>
                        <span class="badge bg-secondary"><?php echo count($program_attachments); ?></span>
                    </div>
                    <div class="stat-item d-flex justify-content-between align-items-center">
                        <span>Last Activity</span>
                        <small class="text-muted" id="last-activity-value">
                            <?php if ($has_submissions && isset($latest_submission['submission_date'])): ?>
                                <?php echo date('M j', strtotime($latest_submission['submission_date'])); ?>
                            <?php else: ?>
                                Never
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Program Attachments -->
            <div class="card shadow-sm mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-paperclip me-2"></i>Attachments
                    </h6>
                    <span class="badge bg-secondary">
                        <?php echo count($program_attachments); ?>
                    </span>
                </div>
                <div class="card-body">
                    <?php if (!empty($program_attachments)): ?>
                        <div class="attachments-list">
                            <?php foreach ($program_attachments as $attachment): ?>
                                <div class="attachment-item mb-3">
                                    <div class="attachment-info d-flex align-items-center">
                                        <div class="attachment-icon me-2">
                                            <i class="fas <?php echo get_file_icon($attachment['mime_type']); ?> text-primary"></i>
                                        </div>
                                        <div class="attachment-details flex-grow-1">
                                            <div class="attachment-name small fw-medium"><?php echo htmlspecialchars($attachment['original_filename']); ?></div>
                                            <div class="attachment-meta text-muted small">
                                                <?php echo $attachment['file_size_formatted']; ?> • 
                                                <?php echo date('M j, Y', strtotime($attachment['upload_date'])); ?>
                                            </div>
                                        </div>
                                        <div class="attachment-actions">
                                            <a href="<?php echo APP_URL; ?>/app/ajax/download_program_attachment.php?id=<?php echo $attachment['attachment_id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" 
                                               target="_blank"
                                               title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-3">
                            <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
                            <p class="text-muted small mb-0">No attachments</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Related Programs -->
            <?php if (!empty($related_programs)): ?>
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-link me-2"></i>Related Programs
                    </h6>
                </div>
                <div class="card-body">
                    <?php foreach ($related_programs as $related): ?>
                        <div class="related-program-item mb-2 d-flex align-items-center justify-content-between">
                            <div>
                                <div class="related-program-name small fw-medium"><?php echo htmlspecialchars($related['program_name']); ?></div>
                                <div class="related-program-meta text-muted small">
                                    <?php if (!empty($related['program_number'])): ?>
                                        <?php echo htmlspecialchars($related['program_number']); ?> •
                                    <?php endif; ?>
                                    <?php echo htmlspecialchars($related['agency_name']); ?>
                                </div>
                            </div>
                            <a href="<?php echo APP_URL; ?>/app/views/agency/programs/program_details.php?id=<?php echo $related['program_id']; ?>" class="btn btn-sm btn-outline-primary ms-2" title="View Details">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (!$is_owner): ?>
<div class="alert alert-info mt-4">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Note:</strong> You are viewing this program in read-only mode. Only the program's owning agency can submit updates.
</div>
<?php endif; ?>

<!-- View Submissions Modal -->
<div class="modal fade" id="viewSubmissionsModal" tabindex="-1" aria-labelledby="viewSubmissionsModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewSubmissionsModalLabel"><i class="fas fa-list-alt me-2"></i>Submissions by Reporting Period</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <?php if (!empty($latest_by_period)): ?>
          <div class="list-group">
            <?php foreach ($latest_by_period as $period_id => $submission): ?>
              <div class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                <div>
                  <div class="fw-medium">
                    <i class="fas fa-calendar-alt me-1 text-primary"></i>
                    <?php echo htmlspecialchars($submission['period_display'] ?? 'Unknown Period'); ?>
                  </div>
                  <div class="small text-muted">
                    Submitted: <?php echo !empty($submission['submission_date']) ? date('M j, Y', strtotime($submission['submission_date'])) : 'N/A'; ?>
                    <?php if (!empty($submission['submitted_by_name'])): ?>
                      &bull; By <?php echo htmlspecialchars($submission['submitted_by_name']); ?>
                    <?php endif; ?>
                  </div>
                </div>
                <a href="<?php echo APP_URL; ?>/app/views/agency/programs/view_submissions.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $period_id; ?>" class="btn btn-sm btn-outline-primary ms-2" title="View Submission">
                  <i class="fas fa-eye"></i> View
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center text-muted py-4">
            <i class="fas fa-folder-open fa-2x mb-2"></i>
            <div>No submissions found for this program.</div>
          </div>
        <?php endif; ?>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>

<!-- JavaScript Configuration -->
<script>
// Pass PHP variables to JavaScript
window.currentUser = {
    id: <?php echo $_SESSION['user_id'] ?? 'null'; ?>,
    agency_id: <?php echo $_SESSION['agency_id'] ?? 'null'; ?>,
    role: '<?php echo $_SESSION['role'] ?? ''; ?>'
};
window.isOwner = <?php echo $is_owner ? 'true' : 'false'; ?>;
window.canEdit = <?php echo $can_edit ? 'true' : 'false'; ?>;
window.programId = <?php echo $program_id; ?>;
window.APP_URL = '<?php echo APP_URL; ?>';
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?> 