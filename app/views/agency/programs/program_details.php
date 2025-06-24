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
// Pass true as the second parameter when source is 'all_sectors' to allow cross-agency viewing
$program = get_program_details($program_id, $source === 'all_sectors');

// If coming from all_sectors view, we allow viewing of any program
// Otherwise, check if this agency owns the program
$allow_view = ($source === 'all_sectors');

// Check if current user is the owner of this program
$is_owner = false;
if (isset($program['owner_agency_id']) && $program['owner_agency_id'] == $_SESSION['user_id']) {
    $allow_view = true;
    $is_owner = true;
}

if (!$program || (!$allow_view)) {
    $_SESSION['message'] = 'Program not found or you do not have permission to view it.';
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

// Get current submission if available
$current_submission = $program['current_submission'] ?? null;
$is_draft = isset($current_submission['is_draft']) && $current_submission['is_draft'];

// Process content_json for better data access
$content = [];
$targets = [];
$rating = 'not-started';
$remarks = '';

// Extract data from content_json if available
if (isset($current_submission['content_json']) && !empty($current_submission['content_json'])) {
    if (is_string($current_submission['content_json'])) {
        $content = json_decode($current_submission['content_json'], true) ?: [];
    } elseif (is_array($current_submission['content_json'])) {
        $content = $current_submission['content_json'];
    }
    
    // If we have the new structure with targets array, use it
    if (isset($content['targets']) && is_array($content['targets'])) {
        $targets = [];
        foreach ($content['targets'] as $target) {
            if (isset($target['target_text'])) {
                // New format that uses target_text
                $targets[] = [
                    'text' => $target['target_text'],
                    'status_description' => $target['status_description'] ?? ''
                ];
            } else {
                // Format that uses text directly
                $targets[] = $target;
            }
        }
        $rating = $content['rating'] ?? $current_submission['status'] ?? 'not-started';
        $remarks = $content['remarks'] ?? '';    } else {
        // Legacy data format - handle semicolon-separated targets
        $target_text = $content['target'] ?? $current_submission['target'] ?? '';
        $status_description = $content['status_text'] ?? $current_submission['status_text'] ?? '';
        
        // Check if targets are semicolon-separated
        if (strpos($target_text, ';') !== false) {
            // Split semicolon-separated targets and status descriptions
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
            // Single target
            $targets = [
                [
                    'text' => $target_text,
                    'status_description' => $status_description
                ]
            ];
        }
        
        $rating = $current_submission['status'] ?? 'not-started';
        $remarks = $current_submission['remarks'] ?? '';
    }
} else {
    // Fallback to direct properties if no content_json
    if (!empty($current_submission['target'])) {
        $targets[] = [
            'text' => $current_submission['target'],
            'status_description' => $current_submission['status_text'] ?? ''
        ];
    }
    $rating = $current_submission['status'] ?? 'not-started';
    $remarks = $current_submission['remarks'] ?? '';
}

// Set page title
$pageTitle = 'Program Details';

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/utilities/rating_utils.js'
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
$status = convert_legacy_status($rating);
if (!isset($status_map[$status])) {
    $status = 'not-started';
}

// Initialize alert flags
$showDraftAlert = $is_draft && $is_owner; // Only show draft alert if user owns the program
$showNoTargetsAlert = empty($targets) && $is_owner; // Only show no targets alert if user owns the program
?>

<!-- Toast Notifications -->
<?php if ($showDraftAlert): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast('Draft Submission', 'This program is in draft mode. <a href="<?= APP_URL ?>/app/views/agency/programs/update_program.php?id=<?= $program_id ?>" class="alert-link">Click here to edit and submit the final version</a>.', 'warning', 10000);
    });
</script>
<?php endif; ?>

<?php if ($showNoTargetsAlert): ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast('No Targets', 'No targets have been added for this program. <a href="<?= APP_URL ?>/app/views/agency/programs/update_program.php?id=<?= $program_id ?>" class="alert-link">Add targets</a>.', 'info', 10000);
    });
</script>
<?php endif; ?>

<!-- Program Overview Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">        <h5 class="card-title mb-0">
            <i class="fas fa-clipboard-list me-2"></i>Program Information
        </h5>
        <div>
            <span class="badge bg-<?php echo $status_map[$status]['class']; ?> py-2 px-3">
                <i class="<?php echo $status_map[$status]['icon']; ?> me-1"></i> 
                <?php echo $status_map[$status]['label']; ?>
            </span>            <?php if ($is_owner && $is_draft): ?>
            <a href="<?php echo APP_URL; ?>/app/views/agency/programs/update_program.php?id=<?php echo $program_id; ?>" class="btn btn-warning btn-sm ms-2">
                <i class="fas fa-edit me-1"></i> Edit Draft
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Program Basic Info -->
            <div class="col-lg-12 mb-4">
                <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                
                <div class="row">
                    <div class="col-md-6 mb-3">                        <div class="row">
                            <div class="col-md-4 text-muted">Program Name:</div>
                            <div class="col-md-8 fw-medium">
                                <?php if (!empty($program['program_number'])): ?>
                                    <span class="badge bg-info me-2" title="Program Number"><?php echo htmlspecialchars($program['program_number']); ?></span>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($program['program_name']); ?>
                            </div>
                        </div>
                    </div>
                    
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
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-4 text-muted">Sector:</div>
                            <div class="col-md-8"><?php echo htmlspecialchars($program['sector_name'] ?? 'Not specified'); ?></div>
                        </div>
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <div class="row">
                            <div class="col-md-4 text-muted">Timeline:</div>
                            <div class="col-md-8">
                                <?php if (isset($program['start_date']) && $program['start_date']): ?>
                                    <i class="far fa-calendar-alt me-1"></i>
                                    <?php echo date('M j, Y', strtotime($program['start_date'])); ?>
                                    <?php if (isset($program['end_date']) && $program['end_date']): ?>
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
                                <?php if (isset($current_submission['submission_date']) && $current_submission['submission_date']): ?>
                                    <i class="far fa-clock me-1"></i>
                                    <?php echo date('M j, Y', strtotime($current_submission['submission_date'])); ?>
                                <?php else: ?>
                                    <span class="text-muted">Not submitted</span>
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

<!-- Initiative Details Card -->
<?php if (!empty($program['initiative_id'])): ?>
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-lightbulb me-2"></i>Initiative Information
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Initiative Basic Info -->
            <div class="col-lg-8 mb-4">
                <div class="initiative-header mb-3">
                    <?php if (!empty($program['initiative_number'])): ?>
                        <span class="badge bg-primary initiative-number me-2" 
                              style="font-family: 'Courier New', monospace; font-size: 1.1em; font-weight: 700;">
                            <i class="fas fa-hashtag me-1"></i>
                            <?php echo htmlspecialchars($program['initiative_number']); ?>
                        </span>
                    <?php endif; ?>
                    <span class="initiative-name fw-bold text-primary" style="font-size: 1.2em;">
                        <?php echo htmlspecialchars($program['initiative_name']); ?>
                    </span>
                </div>
                
                <?php if (!empty($program['initiative_description'])): ?>
                <div class="initiative-description mb-3">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-info-circle me-1"></i>Description
                    </h6>
                    <div class="p-3 bg-light rounded border" style="max-height: 200px; overflow-y: auto;">
                        <?php echo nl2br(htmlspecialchars($program['initiative_description'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="initiative-timeline">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-calendar-alt me-1"></i>Initiative Timeline
                    </h6>
                    <div class="timeline-info">
                        <?php if (!empty($program['initiative_start_date']) || !empty($program['initiative_end_date'])): ?>
                            <span class="text-muted">
                                <?php if (!empty($program['initiative_start_date'])): ?>
                                    <i class="far fa-calendar-alt me-1"></i>
                                    Started: <?php echo date('M j, Y', strtotime($program['initiative_start_date'])); ?>
                                <?php endif; ?>
                                <?php if (!empty($program['initiative_end_date'])): ?>
                                    <?php if (!empty($program['initiative_start_date'])): ?>
                                        <span class="mx-2">•</span>
                                    <?php endif; ?>
                                    <i class="far fa-calendar-check me-1"></i>
                                    Target End: <?php echo date('M j, Y', strtotime($program['initiative_end_date'])); ?>
                                <?php endif; ?>
                            </span>
                        <?php else: ?>
                            <span class="text-muted fst-italic">Timeline not specified</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Related Programs -->
            <div class="col-lg-4">
                <h6 class="text-muted mb-3">
                    <i class="fas fa-project-diagram me-1"></i>Related Programs
                    <span class="badge bg-secondary ms-1"><?php echo count($related_programs); ?></span>
                </h6>
                
                <?php if (!empty($related_programs)): ?>
                    <div class="related-programs-list" style="max-height: 300px; overflow-y: auto;">
                        <?php foreach ($related_programs as $related): ?>
                            <div class="related-program-item p-2 mb-2 border rounded bg-white" style="border-left: 3px solid #0d6efd !important;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="fw-medium" style="font-size: 0.9em;">
                                            <?php if (!empty($related['program_number'])): ?>
                                                <span class="badge bg-info me-1" style="font-size: 0.7em;">
                                                    <?php echo htmlspecialchars($related['program_number']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($related['program_name']); ?>
                                        </div>
                                        <div class="small text-muted">
                                            <?php echo htmlspecialchars($related['agency_name']); ?>
                                        </div>
                                    </div>
                                    <div class="ms-2">
                                        <?php
                                        $status = convert_legacy_rating($related['rating']);
                                        $status_colors = [
                                            'target-achieved' => 'success',
                                            'on-track-yearly' => 'warning',
                                            'severe-delay' => 'danger',
                                            'not-started' => 'secondary'
                                        ];
                                        $color_class = $status_colors[$status] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $color_class; ?>" style="font-size: 0.6em;">
                                            <?php echo $related['is_draft'] ? 'Draft' : 'Final'; ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if ($allow_view): ?>
                                    <div class="mt-1">
                                        <a href="program_details.php?id=<?php echo $related['program_id']; ?>&source=<?php echo htmlspecialchars($source); ?>" 
                                           class="btn btn-outline-primary btn-sm" style="font-size: 0.7em;">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-muted fst-italic small">
                        <i class="fas fa-info-circle me-1"></i>
                        No other programs found under this initiative.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Program Targets and Status Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-tasks me-2"></i>Current Period Performance
        </h5>
    </div>    <div class="card-body">
        <?php if (!empty($targets)): ?>
            <div class="performance-grid">
                <?php foreach ($targets as $index => $target): ?>
                    <div class="performance-item card mb-3 shadow-sm">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="target-section">
                                        <h6 class="target-header d-flex align-items-center mb-3">
                                            <span class="target-number me-2"><?php echo $index + 1; ?></span>
                                            <span class="text-primary fw-bold">Program Target</span>
                                        </h6>
                                        <div class="target-content">
                                            <?php if (!empty($target['text'])): ?>
                                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($target['text'])); ?></p>
                                            <?php else: ?>
                                                <p class="text-muted fst-italic mb-0">No target specified</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <div class="status-section">
                                        <h6 class="status-header d-flex align-items-center mb-3">
                                            <i class="fas fa-chart-line me-2 text-success"></i>
                                            <span class="text-success fw-bold">Status & Achievements</span>
                                        </h6>
                                        <div class="status-content">
                                            <?php if (!empty($target['status_description'])): ?>
                                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($target['status_description'])); ?></p>
                                            <?php else: ?>
                                                <p class="text-muted fst-italic mb-0">No status update provided</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if (isset($current_submission['achievement']) && !empty($current_submission['achievement'])): ?>
            <div class="overall-achievement p-4">
                <div class="overall-achievement-label">
                    <i class="fas fa-award me-2"></i>Overall Achievement
                </div>
                <div class="achievement-content">
                    <?php echo nl2br(htmlspecialchars($current_submission['achievement'])); ?>
                </div>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No targets have been specified for this program.                <?php if ($is_owner): ?>
                    <a href="<?php echo APP_URL; ?>/app/views/agency/programs/update_program.php?id=<?php echo $program_id; ?>" class="alert-link">Add targets</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>    </div>
</div>

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
                        <br><a href="<?php echo APP_URL; ?>/app/views/agency/programs/update_program.php?id=<?php echo $program_id; ?>" class="text-decoration-none">Upload attachments</a> in the program editor.
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

<?php
// Include footer
require_once '../../layouts/footer.php';
?>



