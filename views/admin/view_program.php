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
        $rating = $content['rating'] ?? $program['current_submission']['status'] ?? 'not-started';
        $remarks = $content['remarks'] ?? '';
    } else {
        // Legacy data format - create a single target
        $targets = [
            [
                'text' => $content['target'] ?? $program['current_submission']['target'] ?? '',
                'status_description' => $content['status_text'] ?? $program['current_submission']['status_text'] ?? ''
            ]
        ];
        $rating = $program['current_submission']['status'] ?? 'not-started';
        $remarks = $program['current_submission']['remarks'] ?? '';
    }
} else {
    // Fallback to direct properties if no content_json
    if (!empty($program['current_submission']['target'])) {
        $targets[] = [
            'text' => $program['current_submission']['target'],
            'status_description' => $program['current_submission']['status_text'] ?? ''
        ];
    }
    $rating = $program['current_submission']['status'] ?? 'not-started';
    $remarks = $program['current_submission']['remarks'] ?? '';
}

// Set page title
$pageTitle = 'Program Details: ' . $program['program_name'];

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
$status = isset($rating) ? convert_legacy_status($rating) : 'not-started';
if (!isset($status_map[$status])) {
    $status = 'not-started';
}

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

<?php if (isset($program['current_submission']['is_draft']) && $program['current_submission']['is_draft']): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <div class="d-flex">
        <div class="me-3">
            <i class="fas fa-exclamation-triangle fa-2x"></i>
        </div>
        <div>
            <h5 class="alert-heading">Draft Submission</h5>
            <p class="mb-0">This program has a draft submission that has not been finalized by the agency.</p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row">
    <!-- Program Information Card -->
    <div class="col-lg-12 mb-4">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">
                    <i class="fas fa-clipboard-list me-2"></i>Program Information
                </h5>
                <div>
                    <?php if (isset($program['status'])): ?>
                    <span class="badge bg-<?php echo $status_map[$status]['class']; ?> py-2 px-3">
                        <i class="<?php echo $status_map[$status]['icon']; ?> me-1"></i> 
                        <?php echo $status_map[$status]['label']; ?>
                    </span>
                    <?php endif; ?>
                    <a href="edit_program.php?id=<?php echo $program_id; ?>" class="btn btn-sm btn-primary ms-2">
                        <i class="fas fa-edit me-1"></i> Edit Program
                    </a>
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
                        
                        <?php if (!empty($program['description'])): ?>
                        <div class="mt-4">
                            <h6 class="border-bottom pb-2 mb-3">Description</h6>
                            <div class="p-3 bg-light rounded border">
                                <?php echo nl2br(htmlspecialchars($program['description'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
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
                <h5 class="card-title m-0">
                    <i class="fas fa-tasks me-2"></i>Current Period Performance
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
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-4 text-muted">Reporting Period:</div>
                            <div class="col-md-8">
                                Q<?php echo $program['current_submission']['quarter']; ?>-<?php echo $program['current_submission']['year']; ?>
                                <?php if (isset($program['current_submission']['is_draft']) && $program['current_submission']['is_draft'] == 1): ?>
                                    <span class="badge bg-secondary ms-1">Draft</span>
                                <?php else: ?>
                                    <span class="badge bg-success ms-1">Final</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-4 text-muted">Submission Date:</div>
                            <div class="col-md-8">
                                <i class="far fa-clock me-1"></i>
                                <?php echo date('M j, Y', strtotime($program['current_submission']['submission_date'])); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($targets)): ?>
                    <div class="targets-container">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th width="50%">Target</th>
                                        <th width="50%">Status / Achievements</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($targets as $index => $target): ?>
                                    <tr class="<?php echo ($index % 2 == 0) ? 'bg-light' : ''; ?>">
                                        <td>
                                            <?php if (!empty($target['text'])): ?>
                                                <?php echo nl2br(htmlspecialchars($target['text'])); ?>
                                            <?php else: ?>
                                                <span class="text-muted fst-italic">No target specified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
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
                        
                        <?php if (isset($program['current_submission']['achievement']) && !empty($program['current_submission']['achievement'])): ?>
                        <div class="mt-3 p-3 rounded border bg-light">
                            <label class="fw-medium mb-1">Overall Achievement:</label>
                            <div>
                                <?php echo nl2br(htmlspecialchars($program['current_submission']['achievement'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <?php if (!empty($remarks)): ?>
                    <div class="remarks-section mt-4">
                        <h6 class="border-bottom pb-2 mb-3">Additional Remarks</h6>
                        <div class="p-3 bg-light rounded border">
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
</div>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
