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

// Include header
require_once '../../layouts/header.php';

// Configure the modern page header
$header_config = [
    'title' => 'Program Details',
    'subtitle' => htmlspecialchars($program['program_name']),
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">
                    <i class="fas fa-clipboard-list me-2 text-primary"></i>Program Information
                </h5>                <div>
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
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="row">
                                    <div class="col-md-4 text-muted">Program Name:</div>
                                    <div class="col-md-8 fw-medium"><?php echo htmlspecialchars($program['program_name']); ?></div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="row">
                                    <div class="col-md-4 text-muted">Program Type:</div>
                                    <div class="col-md-8">
                                        <?php if ($program['is_assigned']): ?>
                                            <span class="badge bg-info">Assigned Program</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Agency Created</span>
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
            </div>
        </div>
    </div>
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
                    <div class="targets-container">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover table-bordered" style="min-width: 500px;">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50%">Program Target</th>
                                        <th width="50%">Status & Achievements</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($targets as $index => $target): ?>
                                    <tr>
                                        <td class="text-truncate">
                                            <?php if (!empty($target['text'])): ?>
                                                <?php echo nl2br(htmlspecialchars($target['text'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">No target specified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-truncate">
                                            <?php if (!empty($target['status_description'])): ?>
                                                <?php echo nl2br(htmlspecialchars($target['status_description'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">No status update provided</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
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
        </div>    </div>
    <?php endif; ?>
</div>
</main>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>



