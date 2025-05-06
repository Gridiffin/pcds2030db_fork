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
?>

<div class="row">
    <!-- Program Overview - Now with combined information -->
    <div class="col-lg-12 mb-4">
        <div class="card shadow-sm h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-white">Program Overview</h6>
                <div class="d-flex align-items-center">
                    <?php if ($is_owner && $is_draft): ?>
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
                    
                    if (!isset($status_map[$status])) {
                        $status = 'not-started';
                    }
                    ?>
                    <span class="badge bg-<?php echo $status_map[$status]['class']; ?> ms-2">
                        <?php echo $status_map[$status]['label']; ?>
                    </span>
                </div>
            </div>
            <div class="card-body">
                <?php if ($is_draft): ?>
                    <div class="alert alert-warning">
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
                if (isset($current_submission['target']) && $current_submission['target']): 
                    echo htmlspecialchars($current_submission['target']);
                else: 
                    echo '<span class="text-muted">Not set</span>';
                endif; 
                ?>
            </div>
        </div>
        
        <div class="info-group mb-3">
            <label class="text-muted">Current Achievement</label>
            <div class="fw-medium">
                <?php 
                if (isset($current_submission['achievement']) && $current_submission['achievement']): 
                    echo htmlspecialchars($current_submission['achievement']);
                else: 
                    echo '<span class="text-muted">Not reported</span>';
                endif; 
                ?>
            </div>
        </div>
        
        <div class="info-group mb-3">
            <label class="text-muted">Status Text</label>
            <div class="fw-medium">
                <?php 
                if (isset($current_submission['status_text']) && $current_submission['status_text']): 
                    echo htmlspecialchars($current_submission['status_text']);
                else: 
                    echo '<span class="text-muted">No status provided</span>';
                endif; 
                ?>
            </div>
        </div>
        
        <div class="info-group mb-3">
            <label class="text-muted">Last Updated</label>
            <div class="fw-medium">
                <?php 
                if (isset($current_submission['submission_date']) && $current_submission['submission_date']): 
                    echo date('M j, Y', strtotime($current_submission['submission_date']));
                else: 
                    echo '<span class="text-muted">Not submitted</span>';
                endif; 
                ?>
            </div>
        </div>
    </div>
</div>

                    <!-- Description -->
                    <div class="col-md-12 mt-4">
                        <div class="info-group">
                            <label class="text-muted">Program Description</label>
                            <div class="mt-2 p-3 bg-light-subtle border rounded">
                                <?php if (!empty($program['description'])): ?>
                                    <?php echo nl2br(htmlspecialchars($program['description'])); ?>
                                <?php else: ?>
                                    <span class="text-muted">No description available</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if ($is_owner && empty($current_submission)): ?>
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
                            // Skip draft submissions in history view
                            if (isset($submission['is_draft']) && $submission['is_draft']) {
                                continue;
                            }
                            
                            // Get period info if available
                            $period_info = '';
                            if (isset($submission['period_info'])) {
                                $period_info = 'Q' . $submission['period_info']['quarter'] . '-' . $submission['period_info']['year'];
                            }
                            
                            // Convert status for display
                            $sub_status = isset($submission['status']) ? convert_legacy_status($submission['status']) : 'not-started';
                            ?>
                            <tr>
                                <td><?php echo $period_info; ?></td>
                                <td><?php echo isset($submission['target']) ? htmlspecialchars($submission['target']) : 'Not set'; ?></td>
                                <td><?php echo isset($submission['achievement']) ? htmlspecialchars($submission['achievement']) : 'Not reported'; ?></td>
                                <td>
                                    <?php 
                                    if (isset($submission['status'])):
                                        $status_class = 'secondary';
                                        switch ($sub_status) {
                                            case 'on-track':
                                            case 'on-track-yearly':
                                                $status_class = 'warning';
                                                break;
                                            case 'target-achieved':
                                                $status_class = 'success';
                                                break;
                                            case 'delayed':
                                            case 'severe-delay':
                                                $status_class = 'danger';
                                                break;
                                            case 'completed':
                                                $status_class = 'primary';
                                                break;
                                        }
                                    ?>
                                        <span class="badge bg-<?php echo $status_class; ?>">
                                            <?php echo ucfirst(str_replace('-', ' ', $sub_status)); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Not reported</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($submission['submission_date'])); ?></td>
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
