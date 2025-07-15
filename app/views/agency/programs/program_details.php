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
require_once ROOT_PATH . 'app/lib/agencies/program_permissions.php';
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

if ($has_submissions) {
    // Get targets from the current submission (now properly fetched from program_targets table)
    if (isset($latest_submission['targets']) && is_array($latest_submission['targets'])) {
        $targets = $latest_submission['targets'];
    }
    
    // Get rating and remarks from submission
    $rating = $latest_submission['rating'] ?? $latest_submission['status_indicator'] ?? 'not-started';
    $remarks = $latest_submission['remarks'] ?? $latest_submission['description'] ?? '';
    
    // Fallback to legacy content_json if targets are not found in program_targets table
    if (empty($targets) && isset($latest_submission['content_json']) && !empty($latest_submission['content_json'])) {
        if (is_string($latest_submission['content_json'])) {
            $content = json_decode($latest_submission['content_json'], true) ?: [];
        } elseif (is_array($latest_submission['content_json'])) {
            $content = $latest_submission['content_json'];
        }
        
        // Extract targets from legacy content
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
        } elseif (isset($content['target']) && !empty($content['target'])) {
            // Legacy single target format
            $targets[] = [
                'text' => $content['target'],
                'status_description' => $content['status_text'] ?? ''
            ];
        }
        
        // Override rating and remarks from legacy content if available
        if (isset($content['rating'])) {
            $rating = $content['rating'];
        }
        if (isset($content['remarks'])) {
            $remarks = $content['remarks'];
        }
    }
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

// Enhanced target detection - check multiple sources for targets
$has_targets = false;
if (!empty($targets)) {
    $has_targets = true;
} elseif ($has_submissions) {
    // Check if there are any targets in the submission data
    if (isset($latest_submission['targets']) && is_array($latest_submission['targets']) && !empty($latest_submission['targets'])) {
        $has_targets = true;
    } elseif (isset($latest_submission['content_json']) && !empty($latest_submission['content_json'])) {
        $content_check = is_string($latest_submission['content_json']) ? 
            json_decode($latest_submission['content_json'], true) : 
            $latest_submission['content_json'];
        
        if (isset($content_check['targets']) && is_array($content_check['targets']) && !empty($content_check['targets'])) {
            $has_targets = true;
        } elseif (isset($content_check['target']) && !empty($content_check['target'])) {
            $has_targets = true;
        }
    } elseif (!empty($latest_submission['target'])) {
        $has_targets = true;
    }
}

$showNoTargetsAlert = $has_submissions && !$has_targets && $is_owner;
$showNoSubmissionsAlert = !$has_submissions; // Show for all users, but action link only for editors
?>

<!-- Toast Notifications -->
<?php if ($showDraftAlert): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($can_edit): ?>
        showToastWithAction('Draft Submission', 'This program is in draft mode.', 'warning', 10000, {
            text: 'Edit & Submit',
            url: '<?= APP_URL ?>/app/views/agency/programs/edit_program.php?id=<?= $program_id ?>'
        });
        <?php else: ?>
        showToast('Draft Submission', 'This program is in draft mode and pending final submission.', 'warning', 8000);
        <?php endif; ?>
    });
</script>
<?php endif; ?>

<?php if ($showNoTargetsAlert): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($can_edit): ?>
        showToastWithAction('No Targets', 'No targets have been added for this program.', 'info', 10000, {
            text: 'Add Targets',
            url: '<?= APP_URL ?>/app/views/agency/programs/edit_program.php?id=<?= $program_id ?>'
        });
        <?php else: ?>
        showToast('No Targets', 'This program does not have any targets defined yet.', 'info', 8000);
        <?php endif; ?>
    });
</script>
<?php endif; ?>

<?php if ($showNoSubmissionsAlert): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($can_edit): ?>
        showToastWithAction('Program Template', 'This program is a template.', 'info', 10000, {
            text: 'Add Progress Report',
            url: '<?= APP_URL ?>/app/views/agency/programs/add_submission.php?program_id=<?= $program_id ?>'
        });
        <?php else: ?>
        showToast('Program Template', 'This program is a template. No progress reports have been added yet.', 'info', 8000);
        <?php endif; ?>
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
                    <div class="status-indicators">
                        <span class="badge status-badge bg-<?php echo $status_map[$status]['class']; ?> py-2 px-3">
                            <i class="<?php echo $status_map[$status]['icon']; ?> me-1"></i> 
                            <?php echo $status_map[$status]['label']; ?>
                        </span>
                        <?php if ($is_draft): ?>
                        <span class="badge bg-warning text-dark ms-2" title="Latest submission is in draft status">
                            <i class="fas fa-pencil-alt me-1"></i> Draft Submission
                        </span>
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

            <!-- Quick Actions Section -->
            <?php if ($can_edit): ?>
            <div class="card quick-actions-card shadow-sm mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <a href="<?php echo APP_URL; ?>/app/views/agency/programs/add_submission.php?program_id=<?php echo $program_id; ?>" class="btn btn-outline-success w-100">
                                <i class="fas fa-plus me-2"></i>Add New Submission
                            </a>
                            <small class="text-muted d-block mt-1">Create a new progress report for this program</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <a href="<?php echo APP_URL; ?>/app/views/agency/programs/edit_program.php?id=<?php echo $program_id; ?>" class="btn btn-outline-secondary w-100">
                                <i class="fas fa-edit me-2"></i>Edit Program Details
                            </a>
                            <small class="text-muted d-block mt-1">Modify program information and settings</small>
                        </div>
                    </div>
                    <?php if ($has_submissions): ?>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <?php if ($allow_view): ?>
                                <?php if ($has_submissions && isset($latest_submission['period_id'])): ?>
                                    <button type="button" class="btn btn-outline-success w-100" data-bs-toggle="modal" data-bs-target="#viewSubmissionModal">
                                        <i class="fas fa-eye me-2"></i>View Submission
                                    </button>
                                    <small class="text-muted d-block mt-1">View the latest progress report for this program</small>
                                <?php else: ?>
                                    <button class="btn btn-outline-success w-100" type="button" disabled title="No submissions available yet">
                                        <i class="fas fa-eye me-2"></i>View Submission
                                    </button>
                                    <small class="text-muted d-block mt-1">No submissions available yet</small>
                                <?php endif; ?>
                            <?php endif; ?>
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
                                            <i class="fas <?php echo get_file_icon(isset($attachment['mime_type']) ? $attachment['mime_type'] : ''); ?> text-primary"></i>
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

<!-- View Submission Modal -->
<?php if ($has_submissions && !empty($submission_history['submissions'])): ?>
<div class="modal fade" id="viewSubmissionModal" tabindex="-1" aria-labelledby="viewSubmissionModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewSubmissionModalLabel"><i class="fas fa-eye me-2"></i>Select Submission to View</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div class="list-group">
          <?php foreach ($submission_history['submissions'] as $submission): ?>
            <div class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <strong><?php echo htmlspecialchars($submission['period_display']); ?></strong>
                <span class="badge bg-<?php echo ($submission['is_draft'] ? 'warning' : 'success'); ?> ms-2">
                  <?php echo $submission['is_draft_label']; ?>
                </span>
                <br>
                <small class="text-muted">
                  Submitted by: <?php echo htmlspecialchars($submission['submitted_by_name'] ?? 'Unknown'); ?>
                  &nbsp;|&nbsp;
                  <?php echo htmlspecialchars($submission['formatted_date']); ?>
                </small>
              </div>
              <a href="<?php echo APP_URL; ?>/app/views/agency/programs/view_submissions.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $submission['period_id']; ?>" class="btn btn-outline-success">
                <i class="fas fa-eye"></i> View
              </a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
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
window.canEdit = <?php echo $can_edit ? 'true' : 'false'; ?>;
window.programId = <?php echo $program_id; ?>;
window.APP_URL = '<?php echo APP_URL; ?>';
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?> 