<?php
/**
 * Admin View Program
 * 
 * Detailed view of a specific program for administrators.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/admins/index.php';
require_once '../../includes/status_helpers.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ../../login.php');
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

// Set page title
$pageTitle = 'Program Details: ' . $program['program_name'];

// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/utilities/status_utils.js'
];

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';

// Set up the page header variables
$title = "Program Details";
$subtitle = $program['program_name'];
$headerStyle = 'light';
$actions = [
    [
        'url' => 'programs.php',
        'text' => 'Back to Programs',
        'icon' => 'fas fa-arrow-left',
        'class' => 'btn-outline-secondary'
    ]
];

// Include the dashboard header component
require_once '../../includes/dashboard_header.php';
?>

<div class="row">
    <!-- Program Information Card -->
    <div class="col-lg-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Program Information</h5>
                <div>
                    <a href="edit_program.php?id=<?php echo $program_id; ?>" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit me-1"></i> Edit Program
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Program Name</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($program['program_name']); ?></dd>
                            
                            <dt class="col-sm-4">Description</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($program['description'] ?? 'No description provided'); ?></dd>
                            
                            <dt class="col-sm-4">Agency</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($program['agency_name']); ?></dd>
                            
                            <dt class="col-sm-4">Sector</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($program['sector_name']); ?></dd>
                        </dl>
                    </div>
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Timeline</dt>
                            <dd class="col-sm-8">
                                <?php if (isset($program['start_date']) && $program['start_date']): ?>
                                    <?php echo date('F j, Y', strtotime($program['start_date'])); ?>
                                    <?php if (isset($program['end_date']) && $program['end_date']): ?>
                                        to <?php echo date('F j, Y', strtotime($program['end_date'])); ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    Not specified
                                <?php endif; ?>
                            </dd>
                            
                            <dt class="col-sm-4">Current Status</dt>
                            <dd class="col-sm-8">
                                <?php if (isset($program['status'])): ?>
                                    <?php echo get_status_badge($program['status']); ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Not Reported</span>
                                <?php endif; ?>
                            </dd>
                            
                            <dt class="col-sm-4">Program Type</dt>
                            <dd class="col-sm-8">
                                <?php if ($program['is_assigned']): ?>
                                    <span class="badge bg-info">Assigned Program</span>
                                <?php else: ?>
                                    <span class="badge bg-success">Agency Created</span>
                                <?php endif; ?>
                            </dd>
                            
                            <dt class="col-sm-4">Created On</dt>
                            <dd class="col-sm-8"><?php echo date('F j, Y', strtotime($program['created_at'])); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Current Submission Details -->
    <?php if (isset($program['current_submission']) && !empty($program['current_submission'])): ?>
    <div class="col-lg-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">Current Period Submission</h5>
                <?php if (isset($program['current_submission']['is_draft']) && $program['current_submission']['is_draft'] == 0): ?>
                <div>
                    <a href="reopen_program.php?program_id=<?php echo $program_id; ?>&submission_id=<?php echo $program['current_submission']['submission_id']; ?>" 
                       class="btn btn-sm btn-warning" title="Allow agency to edit this submission again">
                        <i class="fas fa-lock-open me-1"></i> Reopen Submission
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <?php
                        // Process content_json if it exists
                        $targets = [];
                        $content = null;
                        if (isset($program['current_submission']['content_json']) && is_string($program['current_submission']['content_json'])) {
                            $content = json_decode($program['current_submission']['content_json'], true);
                            if ($content && isset($content['targets'])) {
                                $targets = $content['targets'];
                            }
                        }

                        // If we have targets from content_json, show them with status descriptions
                        if (!empty($targets)): 
                        ?>
                        <h6 class="fw-bold mb-3">Program Targets</h6>
                        <div class="targets-list">
                            <?php foreach($targets as $index => $target): ?>
                            <div class="target-item mb-4">
                                <div class="mb-2">
                                    <strong>Target <?php echo $index + 1; ?>:</strong>
                                    <p><?php echo htmlspecialchars($target['text'] ?? ''); ?></p>
                                </div>
                                <?php if (!empty($target['status_description'])): ?>
                                <div class="mb-2">
                                    <strong>Status Description:</strong>
                                    <p class="text-muted"><?php echo htmlspecialchars($target['status_description']); ?></p>
                                </div>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Target</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($program['current_submission']['target'] ?? 'Not specified'); ?></dd>
                            
                            <dt class="col-sm-4">Achievement</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($program['current_submission']['achievement'] ?? 'Not specified'); ?></dd>
                            
                            <dt class="col-sm-4">Status</dt>
                            <dd class="col-sm-8">
                                <?php echo get_status_badge($program['current_submission']['status'] ?? 'not-started'); ?>
                                <?php if (isset($program['current_submission']['status_date'])): ?>
                                    <small class="text-muted d-block">as of <?php echo date('F j, Y', strtotime($program['current_submission']['status_date'])); ?></small>
                                <?php endif; ?>
                            </dd>
                        </dl>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <dl class="row mb-0">
                            <dt class="col-sm-4">Reporting Period</dt>
                            <dd class="col-sm-8">
                                Q<?php echo $program['current_submission']['quarter']; ?>-<?php echo $program['current_submission']['year']; ?>
                                <?php if (isset($program['current_submission']['is_draft']) && $program['current_submission']['is_draft'] == 1): ?>
                                    <span class="badge bg-secondary ms-1">Draft</span>
                                <?php else: ?>
                                    <span class="badge bg-success ms-1">Final</span>
                                <?php endif; ?>
                            </dd>
                            
                            <dt class="col-sm-4">Submission Date</dt>
                            <dd class="col-sm-8"><?php echo date('F j, Y', strtotime($program['current_submission']['submission_date'])); ?></dd>
                            
                            <dt class="col-sm-4">Overall Status</dt>
                            <dd class="col-sm-8">
                                <?php 
                                $rating = $content['rating'] ?? $program['current_submission']['status'] ?? 'not-started';
                                echo get_status_badge($rating);
                                ?>
                            </dd>
                            
                            <dt class="col-sm-4">Remarks</dt>
                            <dd class="col-sm-8"><?php echo htmlspecialchars($content['remarks'] ?? $program['current_submission']['remarks'] ?? 'No remarks provided'); ?></dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Submission History -->
    <div class="col-lg-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">Submission History</h5>
            </div>
            <div class="card-body">
                <?php if (isset($program['submissions']) && !empty($program['submissions'])): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Period</th>
                                <th>Target</th>
                                <th>Achievement</th>
                                <th>Status</th>
                                <th>Submission Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($program['submissions'] as $submission): ?>
                            <tr>
                                <td>Q<?php echo $submission['quarter']; ?>-<?php echo $submission['year']; ?></td>
                                <td><?php echo htmlspecialchars($submission['target'] ?? 'Not specified'); ?></td>
                                <td><?php echo htmlspecialchars($submission['achievement'] ?? 'Not specified'); ?></td>
                                <td><?php echo get_status_badge($submission['status'] ?? 'not-started'); ?></td>
                                <td><?php echo date('M j, Y', strtotime($submission['submission_date'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="alert alert-info mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    No submission history available for this program.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
