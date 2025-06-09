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
require_once PROJECT_ROOT_PATH . 'config/config.php';
require_once PROJECT_ROOT_PATH . 'lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'lib/session.php';
require_once PROJECT_ROOT_PATH . 'lib/functions.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'lib/rating_helpers.php';

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

// Include header
require_once '../../layouts/header.php';

// Include agency navigation
require_once '../../layouts/agency_nav.php';

// Set up the page header variables
$title = "Program Details";
$subtitle = $program['program_name'];
$headerStyle = 'light'; // Use light (white) style for inner pages

// Back button URL depends on source
// Back button URL depends on source
$allSectorsUrl = APP_URL . '/app/views/agency/sectors/view_all_sectors.php';
$myProgramsUrl = APP_URL . '/app/views/agency/programs/view_programs.php';

$backUrl = $source === 'all_sectors' ? $allSectorsUrl : $myProgramsUrl;

$actions = [
    [
        'url' => $backUrl,
        'text' => 'Back to ' . ($source === 'all_sectors' ? 'All Sectors' : 'My Programs'),
        'icon' => 'fas fa-arrow-left',
        'class' => 'btn-outline-secondary'
    ]
];

// Include the dashboard header component
require_once ROOT_PATH . 'app/lib/dashboard_header.php';

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
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-clipboard-list me-2"></i>Program Overview
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
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Program Targets and Status Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-tasks me-2"></i>Current Period Performance
        </h5>
    </div>
    <div class="card-body">
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
                                </td>                                <td>
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
                No targets have been specified for this program.                <?php if ($is_owner): ?>
                    <a href="<?php echo APP_URL; ?>/app/views/agency/programs/update_program.php?id=<?php echo $program_id; ?>" class="alert-link">Add targets</a>
                <?php endif; ?>
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



