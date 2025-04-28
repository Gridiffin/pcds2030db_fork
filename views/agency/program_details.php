<?php
/**
 * Program Details View
 * 
 * Displays detailed information about a specific program.
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agencies/index.php';
require_once '../../includes/status_helpers.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ../../login.php');
    exit;
}

// Get program ID from URL
$program_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$program_id) {
    $_SESSION['message'] = 'Invalid program ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get program details
$program = get_program_details($program_id);

if (!$program) {
    $_SESSION['message'] = 'Program not found or you do not have permission to view it.';
    $_SESSION['message_type'] = 'danger';
    header('Location: view_programs.php');
    exit;
}

// Get current submission if available
$current_submission = $program['current_submission'] ?? null;
$is_draft = isset($current_submission['is_draft']) && $current_submission['is_draft'];

// Set page title
$pageTitle = 'Program Details';


// Additional scripts
$additionalScripts = [
    APP_URL . '/assets/js/utilities/status_utils.js'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';

// Set up the page header variables
$title = "Program Details";
$subtitle = $program['program_name'];
$headerStyle = 'light'; // Use light (white) style for inner pages
$actions = [
    [
        'url' => 'view_programs.php',
        'text' => 'Back to Programs',
        'icon' => 'fas fa-arrow-left',
        'class' => 'btn-outline-secondary'
    ]
];

// Include the dashboard header component
require_once '../../includes/dashboard_header.php';
?>

<div class="row">
    <!-- Program Overview - Now with combined information -->
    <div class="col-lg-12 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-white">Program Overview</h6>
                <div class="d-flex align-items-center">
                    <?php if ($is_draft): ?>
                        <a href="update_program.php?id=<?php echo $program_id; ?>" class="btn btn-warning btn-sm me-2">
                            <i class="fas fa-edit me-1"></i> Edit Draft
                        </a>
                    <?php endif; ?>
                    <?php 
                    // Get status for display
                    $status = $current_submission['status'] ?? 'not-started';
                    $status = convert_legacy_status($status);
                    $status_map = [
                        'on-track' => ['label' => 'On Track', 'class' => 'warning'],
                        'on-track-yearly' => ['label' => 'On Track for Year', 'class' => 'warning'],
                        'target-achieved' => ['label' => 'Monthly Target Achieved', 'class' => 'success'],
                        'delayed' => ['label' => 'Delayed', 'class' => 'danger'],
                        'severe-delay' => ['label' => 'Severe Delays', 'class' => 'danger'],
                        'completed' => ['label' => 'Completed', 'class' => 'primary'],
                        'not-started' => ['label' => 'Not Started', 'class' => 'secondary']
                    ];
                    
                    // Set default if status is not in our map
                    if (!isset($status_map[$status])) {
                        $status = 'not-started';
                    }
                    ?>
                    <span class="badge bg-<?php echo $status_map[$status]['class']; ?> px-3 py-2">
                        <?php echo $status_map[$status]['label']; ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <?php if ($is_draft): ?>
                    <div class="draft-banner mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Draft Status:</strong> This program has a draft submission that needs to be finalized.
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Basic Program Information -->
                    <div class="col-md-6">
                        <div class="program-info">
                            <div class="info-group mb-3">
                                <label class="text-muted">Program Name</label>
                                <div class="fw-medium h5"><?php echo htmlspecialchars($program['program_name']); ?></div>
                            </div>
                            
                            <div class="info-group mb-3">
                                <label class="text-muted">Program Type</label>
                                <div class="fw-medium">
                                    <?php if (isset($program['is_assigned']) && $program['is_assigned']): ?>
                                        <span class="badge bg-info">Assigned Program</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Agency-Created</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="info-group mb-3">
                                <label class="text-muted">Sector</label>
                                <div class="fw-medium"><?php echo htmlspecialchars($program['sector_name'] ?? 'Not specified'); ?></div>
                            </div>
                            
                            <div class="info-group mb-3">
                                <label class="text-muted">Timeline</label>
                                <div class="fw-medium">
                                    <?php if (isset($program['start_date']) && $program['start_date']): ?>
                                        <?php echo date('M j, Y', strtotime($program['start_date'])); ?>
                                        <?php if (isset($program['end_date']) && $program['end_date']): ?>
                                            - <?php echo date('M j, Y', strtotime($program['end_date'])); ?>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        Not specified
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Current Period Reporting Data -->
                    <div class="col-md-6 border-start">
                        <div class="program-info ps-md-4">
                            <div class="info-group mb-3">
                                <label class="text-muted">Current Target</label>
                                <div class="fw-medium">
                                    <?php 
                                    // Properly extract target from either content_json or direct field
                                    if (isset($current_submission['content_json']) && is_string($current_submission['content_json'])) {
                                        $content = json_decode($current_submission['content_json'], true);
                                        echo htmlspecialchars($content['target'] ?? 'Not set');
                                    } else {
                                        echo htmlspecialchars($current_submission['target'] ?? 'Not set');
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="info-group mb-3">
                                <label class="text-muted">Status Date</label>
                                <div class="fw-medium">
                                    <?php 
                                    if (isset($current_submission['content_json']) && is_string($current_submission['content_json'])) {
                                        $content = json_decode($current_submission['content_json'], true);
                                        if (isset($content['status_date']) && $content['status_date']) {
                                            echo date('M j, Y', strtotime($content['status_date']));
                                        } else {
                                            echo 'Not set';
                                        }
                                    } else if (isset($current_submission['status_date']) && $current_submission['status_date']) {
                                        echo date('M j, Y', strtotime($current_submission['status_date']));
                                    } else {
                                        echo 'Not set';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="info-group mb-3">
                                <label class="text-muted">Last Updated</label>
                                <div class="fw-medium">
                                    <?php echo isset($program['updated_at']) ? date('M j, Y', strtotime($program['updated_at'])) : 'Not updated'; ?>
                                </div>
                            </div>
                            
                            <div class="info-group mb-3">
                                <label class="text-muted">Created</label>
                                <div class="fw-medium">
                                    <?php echo date('M j, Y', strtotime($program['created_at'])); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Description (full width) -->
                    <div class="col-12 mt-3">
                        <?php if (isset($program['description']) && $program['description']): ?>
                        <div class="info-group">
                            <label class="text-muted">Program Description</label>
                            <div class="description-box p-3 rounded bg-light">
                                <?php echo nl2br(htmlspecialchars($program['description'])); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Achievement (full width) -->
                    <?php if (isset($current_submission['status_text']) && $current_submission['status_text']): ?>
                    <div class="col-12 mt-3">
                        <div class="info-group">
                            <label class="text-muted">Achievement</label>
                            <div class="description-box p-3 rounded bg-light">
                                <?php echo nl2br(htmlspecialchars($current_submission['status_text'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Remarks (full width) -->
                    <?php if (isset($current_submission['remarks']) && $current_submission['remarks']): ?>
                    <div class="col-12 mt-3">
                        <div class="info-group">
                            <label class="text-muted">Remarks</label>
                            <div class="description-box p-3 rounded bg-light">
                                <?php echo nl2br(htmlspecialchars($current_submission['remarks'])); ?>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if (!$current_submission): ?>
                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle me-2"></i>
                    No data submitted for the current reporting period.
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Submission History -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="m-0 font-weight-bold text-white">Submission History</h6>
    </div>
    <div class="card-body">
        <?php if (isset($program['submissions']) && !empty($program['submissions'])): ?>
            <div class="table-responsive">
                <table class="table table-hover table-custom">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Target</th>
                            <th>Achievement</th>
                            <th>Status</th>
                            <th>Submitted</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($program['submissions'] as $submission): ?>
                            <?php
                            // Process content_json data if it exists and hasn't been processed already
                            if (isset($submission['content_json']) && is_string($submission['content_json'])) {
                                $content = json_decode($submission['content_json'], true);
                                if ($content) {
                                    // Extract content fields into submission array
                                    $submission['current_target'] = $content['target'] ?? null;
                                    $submission['status_text'] = $content['status_text'] ?? null;
                                    $submission['remarks'] = $content['remarks'] ?? null;
                                }
                            }
                            ?>
                            <tr class="<?php echo isset($submission['is_draft']) && $submission['is_draft'] ? 'draft-program' : ''; ?>">
                                <td>
                                    <?php 
                                    // Get period information from period_id
                                    $period_info = get_reporting_period($submission['period_id'] ?? 0);
                                    if ($period_info) {
                                        echo 'Q' . $period_info['quarter'] . '-' . $period_info['year'];
                                    } else {
                                        echo 'Unknown Period';
                                    }
                                    ?>
                                    <?php if (isset($submission['is_draft']) && $submission['is_draft']): ?>
                                        <span class="draft-indicator">Draft</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($submission['current_target'] ?? $submission['target'] ?? 'Not set'); ?>
                                </td>
                                <td>
                                    <?php 
                                    // Extract achievement/status_text from either content_json or direct field
                                    if (isset($submission['content_json']) && is_string($submission['content_json'])) {
                                        $content = json_decode($submission['content_json'], true);
                                        echo htmlspecialchars($content['status_text'] ?? $content['achievement'] ?? 'Not set');
                                    } else {
                                        echo htmlspecialchars($submission['status_text'] ?? $submission['achievement'] ?? 'Not set');
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $sub_status = convert_legacy_status($submission['status'] ?? 'not-started');
                                    if (!isset($status_map[$sub_status])) {
                                        $sub_status = 'not-started';
                                    }
                                    ?>
                                    <span class="badge bg-<?php echo $status_map[$sub_status]['class']; ?>">
                                        <?php echo $status_map[$sub_status]['label']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    if (isset($submission['created_at']) && $submission['created_at']) {
                                        echo date('M j, Y', strtotime($submission['created_at']));
                                    } else if (isset($submission['submission_date']) && $submission['submission_date']) {
                                        echo date('M j, Y', strtotime($submission['submission_date']));
                                    } else {
                                        echo 'Not recorded';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                No submission history found for this program.
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Add some enhanced styles for this page -->
<style>
.info-group label {
    font-size: 0.85rem;
    margin-bottom: 0.25rem;
    display: block;
    color: #6c757d;
}

.info-group .fw-medium {
    font-weight: 500;
}

.description-box {
    max-height: 150px;
    overflow-y: auto;
    font-size: 0.9rem;
    border: 1px solid rgba(0,0,0,.1);
}

.border-start {
    border-left: 1px solid #dee2e6;
}

@media (max-width: 767px) {
    .border-start {
        border-left: none;
        border-top: 1px solid #dee2e6;
        margin-top: 1rem;
        padding-top: 1rem;
    }
    
    .ps-md-4 {
        padding-left: 0 !important;
    }
}

.card-header .badge {
    font-size: 0.9rem;
}
</style>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
