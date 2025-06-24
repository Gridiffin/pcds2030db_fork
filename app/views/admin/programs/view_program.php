<?php
/**
 * Admin View Program
 * 
 * Detailed view of a specific program for administrators.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/rating_helpers.php';
require_once ROOT_PATH . 'app/lib/agencies/program_attachments.php';
require_once ROOT_PATH . 'app/lib/initiative_functions.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get program ID from URL
$program_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validate program_id
if (!$program_id) {
    $_SESSION['message'] = 'Invalid program ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Get program details with submissions history
$program = get_admin_program_details($program_id); // Using the admin function from statistics.php

if (!$program) {
    $_SESSION['message'] = 'Program not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Get program attachments
$program_attachments = get_program_attachments($program_id);

// Process content_json for better data access
$content = [];
$targets = [];
$rating = 'not-started';
$remarks = '';

// Extract data from content_json if available
if (isset($program['current_submission']['content_json']) && !empty($program['current_submission']['content_json'])) {
    if (is_string($program['current_submission']['content_json'])) {
        $content = json_decode($program['current_submission']['content_json'], true) ?: [];
    } elseif (is_array($program['current_submission']['content_json'])) {
        $content = $program['current_submission']['content_json'];
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
        $rating = $content['rating'] ?? 'not-started'; // Default since status column doesn't exist
        $remarks = $content['remarks'] ?? '';    } else {
        // Legacy data format - handle semicolon-separated targets
        $target_text = $content['target'] ?? $program['current_submission']['target'] ?? '';
        $status_description = $content['status_text'] ?? '';
        
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
        
        $rating = 'not-started'; // Default since status column doesn't exist
        $remarks = $program['current_submission']['remarks'] ?? '';
    }
} else {
    // Fallback to direct properties if no content_json
    if (!empty($program['current_submission']['target'])) {
        $targets[] = [
            'text' => $program['current_submission']['target'],
            'status_description' => ''
        ];
    }
    $rating = 'not-started'; // Default since status column doesn't exist
    $remarks = $program['current_submission']['remarks'] ?? '';
}

// Set page title
$pageTitle = 'Program Details: ' . $program['program_name'];

// Remove inline CSS - now handled by external CSS file
// Responsive table styling moved to admin-performance-table.css

// Define rating mapping for display
$rating_map = [
    'on-track' => ['label' => 'On Track', 'class' => 'warning', 'icon' => 'fas fa-chart-line'],
    'on-track-yearly' => ['label' => 'On Track for Year', 'class' => 'warning', 'icon' => 'fas fa-calendar-check'],
    'target-achieved' => ['label' => 'Monthly Target Achieved', 'class' => 'success', 'icon' => 'fas fa-check-circle'],
    'delayed' => ['label' => 'Delayed', 'class' => 'danger', 'icon' => 'fas fa-exclamation-circle'],
    'severe-delay' => ['label' => 'Severe Delays', 'class' => 'danger', 'icon' => 'fas fa-exclamation-triangle'],
    'completed' => ['label' => 'Completed', 'class' => 'primary', 'icon' => 'fas fa-flag-checkered'],
    'not-started' => ['label' => 'Not Started', 'class' => 'secondary', 'icon' => 'fas fa-hourglass-start']
];

// Convert rating for display
$rating_value = isset($rating) ? convert_legacy_rating($rating) : 'not-started';
if (!isset($rating_map[$rating_value])) {
    $rating_value = 'not-started';
}

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/utilities/rating_utils.js',
    APP_URL . '/assets/js/utilities/program_details_table.js',
    APP_URL . '/assets/js/utilities/program_details_responsive.js'
];

// Additional CSS
$additionalCSS = [
    APP_URL . '/assets/css/admin/programs.css',
    APP_URL . '/assets/css/components/period-performance.css'
];

// Include header
require_once '../../layouts/header.php';

// Configure the modern page header
$header_config = [
    'title' => 'Program Details',
    'subtitle' => (!empty($program['program_number']) ? '#' . htmlspecialchars($program['program_number']) . ' - ' : '') . htmlspecialchars($program['program_name']),
    'variant' => 'white',
    'actions' => [
        [
            'text' => 'Back to Programs',
            'url' => APP_URL . '/app/views/admin/programs/programs.php',
            'class' => 'btn-outline-primary',
            'icon' => 'fas fa-arrow-left'
        ]
    ]
];

// Include the modern page header
require_once '../../layouts/page_header.php';
?>

<main class="flex-fill">
<?php if (isset($program['current_submission']['is_draft']) && $program['current_submission']['is_draft']): ?>
<div class="alert alert-warning alert-dismissible fade show custom-alert" role="alert">
    <div class="d-flex">
        <div class="alert-icon me-3">
            <i class="fas fa-exclamation-triangle"></i>
        </div>
        <div>
            <h5 class="alert-heading">Draft Submission</h5>
            <p class="mb-0">This program has a draft submission that has not been finalized by the agency.</p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row">    <!-- Program Information Card -->
    <div class="col-lg-12 mb-4">
        <div class="card shadow-sm program-info-card">
            <div class="card-header d-flex justify-content-between align-items-center">                <h5 class="card-title m-0">
                    <i class="fas fa-clipboard-list me-2"></i>Program Information
                </h5><div>
                    <?php if (isset($program['status'])): ?>
                    <span class="rating-badge badge bg-<?php echo $rating_map[$rating_value]['class']; ?> py-2 px-3">
                        <i class="<?php echo $rating_map[$rating_value]['icon']; ?> me-1"></i> 
                        <?php echo $rating_map[$rating_value]['label']; ?>
                    </span>
                    <?php endif; ?>
                    <a href="edit_program.php?id=<?php echo $program_id; ?>" class="btn btn-sm btn-primary ms-2">
                        <i class="fas fa-edit me-1"></i> Edit Program
                    </a>
                </div>
            </div>
            <div class="card-body">                <div class="row">
                    <!-- Program Basic Info -->
                    <div class="col-lg-12 mb-4">
                        <h6 class="info-section-title border-bottom pb-2 mb-3">
                            <i class="fas fa-info-circle me-2"></i>Basic Information
                        </h6>
                        
                        <div class="row">                            <div class="col-md-6 mb-3">
                                <div class="row">
                                    <div class="col-md-4 text-muted">Program Name:</div>                                    <div class="col-md-8 fw-medium">
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
                                    <div class="col-md-8">                                        <?php if ($program['is_assigned']): ?>
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
                                    <div class="col-md-4 text-muted">Agency:</div>
                                    <div class="col-md-8"><?php echo htmlspecialchars($program['agency_name']); ?></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="row">
                                    <div class="col-md-4 text-muted">Sector:</div>
                                    <div class="col-md-8"><?php echo htmlspecialchars($program['sector_name']); ?></div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
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
                            
                            <div class="col-md-6 mb-3">
                                <div class="row">
                                    <div class="col-md-4 text-muted">Created On:</div>
                                    <div class="col-md-8"><?php echo date('M j, Y', strtotime($program['created_at'])); ?></div>
                                </div>
                            </div>
                        </div>
                                                  
                        <!-- Program Rating Pills -->
                        <div class="mt-4">
                            <h6 class="info-section-title border-bottom pb-2 mb-3">
                                <i class="fas fa-star me-2"></i>Program Rating
                            </h6>
                            <div class="rating-pills read-only">
                                <?php if (isset($rating_map[$rating_value])): ?>
                                <div class="rating-pill <?php echo $rating_value; ?> active">
                                    <i class="<?php echo $rating_map[$rating_value]['icon']; ?> me-2"></i> 
                                    <?php echo $rating_map[$rating_value]['label']; ?>
                                </div>
                                <?php else: ?>
                                <div class="rating-pill not-started active">
                                    <i class="fas fa-hourglass-start me-2"></i> Not Started
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>        </div>
    </div>

    <!-- Initiative Details Card -->
    <?php if (!empty($program['initiative_id'])): ?>
    <div class="col-lg-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">
                    <i class="fas fa-lightbulb me-2"></i>Initiative Information
                </h5>
            </div>
            <div class="card-body">
                <!-- Initiative Basic Info -->
                <div class="row">
                    <div class="col-12">
                        <div class="initiative-header mb-3">
                            <?php if (!empty($program['initiative_number'])): ?>
                                <span class="badge bg-primary initiative-number me-2" 
                                      title="Initiative Number">
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
                                <i class="fas fa-align-left me-1"></i>Description
                            </h6>
                            <p class="mb-0">
                                <?php echo nl2br(htmlspecialchars($program['initiative_description'])); ?>
                            </p>
                        </div>
                        <?php endif; ?>

                        <div class="initiative-timeline">
                            <h6 class="text-muted mb-2">
                                <i class="fas fa-calendar-alt me-1"></i>Initiative Timeline
                            </h6>
                            <div class="timeline-info">
                                <?php if (!empty($program['initiative_start_date']) || !empty($program['initiative_end_date'])): ?>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar-check me-2 text-success"></i>
                                        <span>
                                            <?php 
                                            if (!empty($program['initiative_start_date']) && !empty($program['initiative_end_date'])) {
                                                echo date('M j, Y', strtotime($program['initiative_start_date'])) . ' - ' . date('M j, Y', strtotime($program['initiative_end_date']));
                                            } elseif (!empty($program['initiative_start_date'])) {
                                                echo 'Started: ' . date('M j, Y', strtotime($program['initiative_start_date']));
                                            } elseif (!empty($program['initiative_end_date'])) {
                                                echo 'Due: ' . date('M j, Y', strtotime($program['initiative_end_date']));
                                            }
                                            ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="text-muted">
                                        <i class="fas fa-info-circle me-2"></i>
                                        No timeline information available
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
      <!-- Current Submission Details -->
    <?php if (isset($program['current_submission']) && !empty($program['current_submission'])): ?>
    <div class="col-lg-12 mb-4">
        <div class="card shadow-sm performance-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">
                    <i class="fas fa-tasks me-2 text-primary"></i>Current Period Performance
                </h5>
                <?php if (isset($program['current_submission']['is_draft']) && $program['current_submission']['is_draft'] == 0): ?>
                <div>
                    <a href="reopen_program.php?program_id=<?php echo $program_id; ?>&submission_id=<?php echo $program['current_submission']['submission_id']; ?>" 
                       class="btn btn-sm btn-warning" title="Allow agency to edit this submission again">
                        <i class="fas fa-lock-open me-1"></i> Reopen Submission
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body">                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="info-item d-flex align-items-center">
                            <div class="info-icon me-3">
                                <i class="fas fa-calendar-alt text-primary"></i>
                            </div>                            <div>
                                <div class="info-label text-muted">Reporting Period</div>
                                <div class="info-value d-flex align-items-center">
                                    <strong class="me-2"><?php echo get_period_display_name($program['current_submission']); ?></strong>
                                    <?php if (isset($program['current_submission']['is_draft']) && $program['current_submission']['is_draft'] == 1): ?>
                                        <span class="badge bg-secondary">Draft</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Final</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="info-item d-flex align-items-center">
                            <div class="info-icon me-3">
                                <i class="fas fa-clock text-primary"></i>
                            </div>
                            <div>
                                <div class="info-label text-muted">Submission Date</div>
                                <div class="info-value">
                                    <strong><?php echo date('M j, Y', strtotime($program['current_submission']['submission_date'])); ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>                <?php if (!empty($targets)): ?>
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
                    
                    <?php if (isset($program['current_submission']['achievement']) && !empty($program['current_submission']['achievement'])): ?>
                        <div class="overall-achievement p-4">
                            <div class="overall-achievement-label">
                                <i class="fas fa-award me-2"></i>Overall Achievement
                            </div>
                            <div class="achievement-content">
                                <?php echo nl2br(htmlspecialchars($program['current_submission']['achievement'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($remarks)): ?>
                    <div class="remarks-section">
                        <h6 class="border-bottom pb-2 mb-3">
                            <i class="fas fa-comment-alt me-2"></i>Additional Remarks
                        </h6>
                        <div class="remarks-container">
                            <?php echo nl2br(htmlspecialchars($remarks)); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                      <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No targets have been specified for this program.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Program Attachments Section -->
    <div class="col-lg-12 mb-4">
        <div class="card shadow-sm">
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
                        <p class="text-muted mb-0">This program doesn't have any supporting documents uploaded.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</main>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>



