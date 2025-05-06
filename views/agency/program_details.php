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
        $remarks = $content['remarks'] ?? '';
    } else {
        // Legacy data format - create a single target
        $targets = [
            [
                'text' => $content['target'] ?? $current_submission['target'] ?? '',
                'status_description' => $content['status_text'] ?? $current_submission['status_text'] ?? ''
            ]
        ];
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

// Back button URL depends on source
$backUrl = $source === 'all_sectors' ? 'view_all_sectors.php' : 'view_programs.php';

$actions = [
    [
        'url' => $backUrl,
        'text' => 'Back to ' . ($source === 'all_sectors' ? 'All Sectors' : 'My Programs'),
        'icon' => 'fas fa-arrow-left',
        'class' => 'btn-outline-secondary'
    ]
];

// Include the dashboard header component
require_once '../../includes/dashboard_header.php';

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
?>

<!-- Program Draft Banner (if applicable) -->
<?php if ($is_draft): ?>
<div class="alert alert-warning alert-dismissible fade show" role="alert">
    <div class="d-flex">
        <div class="me-3">
            <i class="fas fa-exclamation-triangle fa-2x"></i>
        </div>
        <div>
            <h5 class="alert-heading">Draft Submission</h5>
            <p class="mb-0">This program has a draft submission that needs to be finalized. 
            <a href="update_program.php?id=<?php echo $program_id; ?>" class="alert-link">Click here to edit and submit the final version</a>.</p>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Program Overview Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-clipboard-list me-2"></i>Program Overview
        </h5>
        <div>
            <span class="badge bg-<?php echo $status_map[$status]['class']; ?> py-2 px-3">
                <i class="<?php echo $status_map[$status]['icon']; ?> me-1"></i> 
                <?php echo $status_map[$status]['label']; ?>
            </span>
            <?php if ($is_owner && $is_draft): ?>
            <a href="update_program.php?id=<?php echo $program_id; ?>" class="btn btn-warning btn-sm ms-2">
                <i class="fas fa-edit me-1"></i> Edit Draft
            </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Left Column: Program Basic Info -->
            <div class="col-lg-6 mb-4">
                <h6 class="border-bottom pb-2 mb-3">Basic Information</h6>
                
                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-4 text-muted">Program Name:</div>
                        <div class="col-md-8 fw-medium"><?php echo htmlspecialchars($program['program_name']); ?></div>
                    </div>
                </div>
                
                <div class="mb-3">
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
                
                <div class="mb-3">
                    <div class="row">
                        <div class="col-md-4 text-muted">Sector:</div>
                        <div class="col-md-8"><?php echo htmlspecialchars($program['sector_name'] ?? 'Not specified'); ?></div>
                    </div>
                </div>
                
                <div class="mb-3">
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
                
                <div class="mb-3">
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
                
                <?php if (!empty($program['description'])): ?>
                <div class="mt-4">
                    <h6 class="border-bottom pb-2 mb-3">Description</h6>
                    <div class="p-3 bg-light rounded border">
                        <?php echo nl2br(htmlspecialchars($program['description'])); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Right Column: Targets and Status -->
            <div class="col-lg-6">
                <h6 class="border-bottom pb-2 mb-3">Current Period Performance</h6>
                
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
                        
                        <?php if (isset($current_submission['achievement']) && !empty($current_submission['achievement'])): ?>
                        <div class="mt-3 p-3 rounded border bg-light">
                            <label class="fw-medium mb-1">Overall Achievement:</label>
                            <div>
                                <?php echo nl2br(htmlspecialchars($current_submission['achievement'])); ?>
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
                        <?php if ($is_owner): ?>
                            <a href="update_program.php?id=<?php echo $program_id; ?>" class="alert-link">Add targets</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Submission History Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-history me-2"></i>Submission History
        </h5>
    </div>
    <div class="card-body">
        <?php if (isset($program['submissions']) && !empty($program['submissions'])): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Period</th>
                            <th>Targets</th>
                            <th>Status</th>
                            <th>Submission Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $shown_submissions = 0;
                        foreach ($program['submissions'] as $submission): 
                            // Skip draft submissions in history view
                            if (isset($submission['is_draft']) && $submission['is_draft']) {
                                continue;
                            }
                            $shown_submissions++;
                            
                            // Get period info if available
                            $period_info = '';
                            if (isset($submission['period_info'])) {
                                $period_info = 'Q' . $submission['period_info']['quarter'] . '-' . $submission['period_info']['year'];
                            }
                            
                            // Process content_json to get targets
                            $sub_targets = [];
                            $sub_status = $submission['status'] ?? 'not-started';
                            
                            if (isset($submission['content_json']) && !empty($submission['content_json'])) {
                                $sub_content = is_string($submission['content_json']) 
                                    ? json_decode($submission['content_json'], true) 
                                    : $submission['content_json'];
                                
                                if ($sub_content) {
                                    if (isset($sub_content['targets']) && is_array($sub_content['targets'])) {
                                        $sub_targets = $sub_content['targets'];
                                    } else if (isset($sub_content['target'])) {
                                        $sub_targets[] = [
                                            'text' => $sub_content['target']
                                        ];
                                    }
                                    
                                    $sub_status = $sub_content['rating'] ?? $submission['status'] ?? 'not-started';
                                }
                            } else if (isset($submission['target'])) {
                                $sub_targets[] = ['text' => $submission['target']];
                            }
                            
                            // Convert status for display
                            $sub_status = convert_legacy_status($sub_status);
                            if (!isset($status_map[$sub_status])) {
                                $sub_status = 'not-started';
                            }
                        ?>
                        <tr>
                            <td class="align-middle"><?php echo $period_info ?: 'Unknown Period'; ?></td>
                            <td>
                                <?php if (!empty($sub_targets)): ?>
                                    <ul class="mb-0 ps-3">
                                        <?php foreach ($sub_targets as $t): ?>
                                            <?php if (!empty($t['text'])): ?>
                                            <li><?php echo htmlspecialchars(substr($t['text'], 0, 80)) . (strlen($t['text']) > 80 ? '...' : ''); ?></li>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <span class="text-muted">No targets specified</span>
                                <?php endif; ?>
                            </td>
                            <td class="align-middle">
                                <span class="badge bg-<?php echo $status_map[$sub_status]['class']; ?>">
                                    <i class="<?php echo $status_map[$sub_status]['icon']; ?> me-1"></i> 
                                    <?php echo $status_map[$sub_status]['label']; ?>
                                </span>
                            </td>
                            <td class="align-middle">
                                <?php echo date('M j, Y', strtotime($submission['submission_date'])); ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        
                        <?php if ($shown_submissions === 0): ?>
                        <tr>
                            <td colspan="4" class="text-center py-3">No finalized submissions found</td>
                        </tr>
                        <?php endif; ?>
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

<?php if (!$is_owner): ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i>
    <strong>Note:</strong> You are viewing this program in read-only mode. Only the program's owning agency can submit updates.
</div>
<?php endif; ?>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
