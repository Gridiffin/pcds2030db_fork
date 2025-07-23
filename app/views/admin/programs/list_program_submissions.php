<?php
/**
 * List Program Submissions Page
 * 
 * Displays all submissions for a specific program across all reporting periods.
 * This provides an overview of all submissions and allows navigation to specific submissions.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(dirname(__DIR__)))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php';
require_once PROJECT_ROOT_PATH . 'app/lib/agencies/programs.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/core.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/program_management.php';
require_once PROJECT_ROOT_PATH . 'app/lib/admins/statistics.php';
require_once PROJECT_ROOT_PATH . 'app/lib/rating_helpers.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get parameters from URL
$program_id = isset($_GET['program_id']) ? intval($_GET['program_id']) : 0;

// Validate required parameters
if (!$program_id) {
    $_SESSION['message'] = 'Missing required parameter (program_id).';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Get program details
$program = get_admin_program_details($program_id);
if (!$program) {
    $_SESSION['message'] = 'Program not found or access denied.';
    $_SESSION['message_type'] = 'danger';
    header('Location: programs.php');
    exit;
}

// Get all submissions for this program
$submissions_query = "SELECT ps.*, 
                            rp.year, rp.period_type, rp.period_number, rp.status as period_status,
                            CONCAT(rp.year, ' ', 
                                   CASE 
                                       WHEN rp.period_type = 'quarter' THEN CONCAT('Q', rp.period_number)
                                       WHEN rp.period_type = 'half' THEN CONCAT('H', rp.period_number)
                                       WHEN rp.period_type = 'yearly' THEN 'Yearly'
                                       ELSE CONCAT(UPPER(LEFT(rp.period_type, 1)), SUBSTRING(rp.period_type, 2), ' ', rp.period_number)
                                   END
                            ) as period_display,
                            u.username as submitted_by_name, 
                            u.fullname as submitted_by_fullname,
                            a.agency_name as submitted_by_agency
                     FROM program_submissions ps
                     LEFT JOIN reporting_periods rp ON ps.period_id = rp.period_id
                     LEFT JOIN users u ON ps.submitted_by = u.user_id
                     LEFT JOIN agency a ON u.agency_id = a.agency_id
                     WHERE ps.program_id = ? AND ps.is_deleted = 0
                     ORDER BY rp.year DESC, rp.period_number DESC";

$stmt = $conn->prepare($submissions_query);
$stmt->bind_param("i", $program_id);
$stmt->execute();
$submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Set page title
$pageTitle = 'Program Submissions - ' . htmlspecialchars($program['program_name']);

// Rating mapping for display
$rating_map = [
    'not_started' => [
        'label' => 'Not Started', 
        'class' => 'secondary',
        'icon' => 'fas fa-hourglass-start'
    ],
    'on_track_for_year' => [
        'label' => 'On Track for Year', 
        'class' => 'warning',
        'icon' => 'fas fa-calendar-check'
    ],
    'monthly_target_achieved' => [
        'label' => 'Monthly Target Achieved', 
        'class' => 'success',
        'icon' => 'fas fa-check-circle'
    ],
    'severe_delay' => [
        'label' => 'Severe Delays', 
        'class' => 'danger',
        'icon' => 'fas fa-exclamation-triangle'
    ]
];

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Program Submissions',
    'subtitle' => 'All submissions for: ' . htmlspecialchars($program['program_name']),
    'variant' => 'info'
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<!-- Breadcrumb Navigation -->
<nav aria-label="breadcrumb">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="programs.php">Programs</a></li>
        <li class="breadcrumb-item active" aria-current="page">Submissions for <?php echo htmlspecialchars($program['program_name']); ?></li>
    </ol>
</nav>

<!-- Program Information Card -->
<div class="card shadow-sm mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title m-0 d-flex align-items-center">
            <i class="fas fa-project-diagram text-primary me-2"></i>
            Program Information
        </h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Program Name:</strong> <?php echo htmlspecialchars($program['program_name']); ?></p>
                <?php if (!empty($program['program_number'])): ?>
                    <p><strong>Program Number:</strong> <span class="badge bg-info"><?php echo htmlspecialchars($program['program_number']); ?></span></p>
                <?php endif; ?>
                <p><strong>Agency:</strong> <?php echo htmlspecialchars($program['agency_name'] ?? 'Unknown'); ?></p>
            </div>
            <div class="col-md-6">
                <?php if (!empty($program['initiative_name'])): ?>
                    <p><strong>Initiative:</strong> <?php echo htmlspecialchars($program['initiative_name']); ?></p>
                <?php endif; ?>
                <p><strong>Created:</strong> <?php echo date('M j, Y', strtotime($program['created_at'])); ?></p>
                <?php if (!empty($program['description'])): ?>
                    <p><strong>Description:</strong> <?php echo htmlspecialchars($program['description']); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Submissions Table -->
<div class="card shadow-sm">
    <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0 d-flex align-items-center">
            <i class="fas fa-list-alt text-info me-2"></i>
            Program Submissions
            <span class="badge bg-secondary ms-2"><?php echo count($submissions); ?> Total</span>
        </h5>
        <div>
            <a href="add_submission.php?program_id=<?php echo $program_id; ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i> Add New Submission
            </a>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($submissions)): ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox text-muted" style="font-size: 3rem;"></i>
                <h5 class="text-muted mt-3">No Submissions Yet</h5>
                <p class="text-muted">This program has no submissions across any reporting periods.</p>
                <a href="add_submission.php?program_id=<?php echo $program_id; ?>" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Create First Submission
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><i class="fas fa-calendar-alt me-1"></i>Reporting Period</th>
                            <th><i class="fas fa-chart-line me-1"></i>Rating</th>
                            <th><i class="fas fa-info-circle me-1"></i>Status</th>
                            <th><i class="fas fa-user me-1"></i>Submitted By</th>
                            <th><i class="fas fa-clock me-1"></i>Last Updated</th>
                            <th class="text-end"><i class="fas fa-cog me-1"></i>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $submission): 
                            $current_rating = isset($submission['rating']) ? $submission['rating'] : 'not_started';
                            if (!isset($rating_map[$current_rating])) {
                                $current_rating = 'not_started';
                            }
                        ?>
                            <tr>
                                <!-- Reporting Period -->
                                <td>
                                    <span class="fw-medium"><?php echo htmlspecialchars($submission['period_display'] ?? 'Unknown Period'); ?></span>
                                    <?php if ($submission['period_status'] === 'closed'): ?>
                                        <span class="badge bg-secondary ms-2" title="Reporting period is closed">
                                            <i class="fas fa-lock me-1"></i>Closed
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success ms-2" title="Reporting period is open">
                                            <i class="fas fa-unlock me-1"></i>Open
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Rating -->
                                <td>
                                    <span class="badge bg-<?php echo $rating_map[$current_rating]['class']; ?> rating-badge" 
                                          title="<?php echo $rating_map[$current_rating]['label']; ?>">
                                        <i class="<?php echo $rating_map[$current_rating]['icon']; ?> me-1"></i>
                                        <?php echo $rating_map[$current_rating]['label']; ?>
                                    </span>
                                </td>
                                
                                <!-- Status -->
                                <td>
                                    <?php if ($submission['is_draft']): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-edit me-1"></i>Draft
                                        </span>
                                    <?php elseif ($submission['is_submitted']): ?>
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Submitted
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-question me-1"></i>Unknown
                                        </span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Submitted By -->
                                <td>
                                    <?php if (!empty($submission['submitted_by_fullname'])): ?>
                                        <span title="<?php echo htmlspecialchars($submission['submitted_by_fullname']); ?>">
                                            <?php echo htmlspecialchars($submission['submitted_by_fullname']); ?>
                                        </span>
                                        <?php if (!empty($submission['submitted_by_agency'])): ?>
                                            <div class="small text-muted">
                                                <?php echo htmlspecialchars($submission['submitted_by_agency']); ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Unknown</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Last Updated -->
                                <td>
                                    <?php if (!empty($submission['updated_at'])): ?>
                                        <span><?php echo date('M j, Y g:i A', strtotime($submission['updated_at'])); ?></span>
                                    <?php elseif (!empty($submission['submission_date'])): ?>
                                        <span><?php echo date('M j, Y g:i A', strtotime($submission['submission_date'])); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">Not set</span>
                                    <?php endif; ?>
                                </td>
                                
                                <!-- Actions -->
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="view_submissions.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $submission['period_id']; ?>" 
                                           class="btn btn-outline-primary" 
                                           title="View detailed submission information"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit_submission.php?program_id=<?php echo $program_id; ?>&period_id=<?php echo $submission['period_id']; ?>" 
                                           class="btn btn-outline-success" 
                                           title="Edit this submission"
                                           data-bs-toggle="tooltip">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Back Navigation -->
<div class="mt-4">
    <a href="programs.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-1"></i> Back to Programs
    </a>
    <a href="view_program.php?id=<?php echo $program_id; ?>" class="btn btn-outline-info ms-2">
        <i class="fas fa-project-diagram me-1"></i> View Program Details
    </a>
</div>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
