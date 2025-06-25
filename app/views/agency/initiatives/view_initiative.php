<?php
/**
 * Agency Initiative Details View
 * 
 * Full-page view for initiative details with programs and complete information.
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'config/config.php';
require_once PROJECT_ROOT_PATH . 'lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'lib/session.php';
require_once PROJECT_ROOT_PATH . 'lib/functions.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/index.php';
require_once PROJECT_ROOT_PATH . 'lib/agencies/initiatives.php';
require_once PROJECT_ROOT_PATH . 'lib/initiative_functions.php';
require_once PROJECT_ROOT_PATH . 'lib/rating_helpers.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get initiative ID
$initiative_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$initiative_id) {
    $_SESSION['message'] = 'Invalid initiative ID.';
    $_SESSION['message_type'] = 'error';
    header('Location: view_initiatives.php');
    exit;
}

// Get initiative details
$agency_id = $_SESSION['user_id'];
$initiative = get_agency_initiative_details($initiative_id, $agency_id);

if (!$initiative) {
    $_SESSION['message'] = 'Initiative not found or access denied.';
    $_SESSION['message_type'] = 'error';
    header('Location: view_initiatives.php');
    exit;
}

// Get programs under this initiative
$programs = get_initiative_programs_for_agency($initiative_id, $agency_id);

// Set page title
$pageTitle = 'Initiative Details - ' . $initiative['initiative_name'];

// Include header
require_once '../../layouts/header.php';

// Configure breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '../../agency/dashboard.php'],
    ['title' => 'Initiatives', 'url' => 'view_initiatives.php'],
    ['title' => 'Initiative Details']
];

// Configure the modern page header
$header_config = [
    'title' => $initiative['initiative_name'],
    'subtitle' => !empty($initiative['initiative_number']) ? 'Initiative #' . $initiative['initiative_number'] : 'Initiative Details',
    'variant' => 'blue',
    'breadcrumbs' => $breadcrumbs,
    'actions' => [
        [
            'text' => 'Back to Initiatives',
            'url' => 'view_initiatives.php',
            'class' => 'btn-outline-light-active',
            'icon' => 'fas fa-arrow-left'
        ]
    ]
];

// Include the modern page header
require_once '../../layouts/page_header.php';
?>

<main class="flex-fill">

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Initiative Information Card -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="card-title m-0">
                        <i class="fas fa-lightbulb me-2"></i>Initiative Information
                    </h5>
                    <div>
                        <?php if ($initiative['is_active']): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Initiative Header -->
                <div class="initiative-header mb-4">
                    <div class="d-flex align-items-center mb-3">
                        <?php if (!empty($initiative['initiative_number'])): ?>
                            <span class="badge bg-primary me-3" style="font-size: 1.1em; padding: 0.5rem 1rem;">
                                <?php echo htmlspecialchars($initiative['initiative_number']); ?>
                            </span>
                        <?php endif; ?>
                        <h4 class="mb-0 text-primary"><?php echo htmlspecialchars($initiative['initiative_name']); ?></h4>
                    </div>
                </div>

                <!-- Description -->
                <?php if (!empty($initiative['initiative_description'])): ?>
                <div class="initiative-description mb-4">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-align-left me-1"></i>Description
                    </h6>
                    <div class="p-3 bg-light rounded">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($initiative['initiative_description'])); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Timeline Information -->
                <div class="mb-4">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-calendar-alt me-1"></i>Timeline
                    </h6>
                    <div class="timeline-info p-3 bg-light rounded">
                        <?php if (!empty($initiative['start_date']) || !empty($initiative['end_date'])): ?>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-check me-2 text-success"></i>
                                <span>
                                    <?php 
                                    if (!empty($initiative['start_date']) && !empty($initiative['end_date'])) {
                                        echo date('M j, Y', strtotime($initiative['start_date'])) . ' - ' . date('M j, Y', strtotime($initiative['end_date']));
                                        
                                        // Calculate duration
                                        $start = new DateTime($initiative['start_date']);
                                        $end = new DateTime($initiative['end_date']);
                                        $interval = $start->diff($end);
                                        echo ' <span class="text-muted">(' . $interval->days . ' days)</span>';
                                    } elseif (!empty($initiative['start_date'])) {
                                        echo 'Started: ' . date('M j, Y', strtotime($initiative['start_date']));
                                    } elseif (!empty($initiative['end_date'])) {
                                        echo 'Due: ' . date('M j, Y', strtotime($initiative['end_date']));
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

                <!-- Program Statistics -->
                <div class="mb-0">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-chart-bar me-1"></i>Program Overview
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card bg-primary text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-1"><?php echo $initiative['agency_program_count']; ?></h3>
                                    <div class="small">Your Programs</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-1"><?php echo $initiative['total_program_count']; ?></h3>
                                    <div class="small">Total Programs</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Related Programs -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h5 class="card-title m-0">
                    <i class="fas fa-tasks me-2"></i>Related Programs
                    <span class="badge bg-secondary ms-2"><?php echo count($programs); ?></span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($programs)): ?>
                    <div class="programs-list" style="max-height: 500px; overflow-y: auto;">
                        <?php foreach ($programs as $program): ?>
                            <div class="program-item mb-3 <?php echo $program['is_owned_by_agency'] ? 'owned' : 'other-agency'; ?>">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="fw-medium mb-1">
                                            <?php if (!empty($program['program_number'])): ?>
                                                <span class="badge bg-info me-2" style="font-size: 0.7em;">
                                                    <?php echo htmlspecialchars($program['program_number']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php echo htmlspecialchars($program['program_name']); ?>
                                        </div>
                                        <div class="small text-muted mb-2">
                                            <i class="fas fa-building me-1"></i>
                                            <?php echo htmlspecialchars($program['agency_name']); ?>
                                        </div>
                                        <?php if ($program['is_owned_by_agency']): ?>
                                            <span class="badge bg-primary mb-2" style="font-size: 0.7em;">
                                                <i class="fas fa-star me-1"></i>Your Program
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ms-2">
                                        <?php
                                        $status = convert_legacy_rating($program['rating']);
                                        $status_colors = [
                                            'target-achieved' => 'success',
                                            'on-track-yearly' => 'warning',
                                            'severe-delay' => 'danger',
                                            'not-started' => 'secondary'
                                        ];
                                        $color_class = $status_colors[$status] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $color_class; ?>" style="font-size: 0.7em;">
                                            <?php echo $program['is_draft'] ? 'Draft' : 'Final'; ?>
                                        </span>
                                    </div>
                                </div>
                                <?php if ($program['is_owned_by_agency']): ?>
                                    <div class="mt-2">
                                        <a href="../programs/program_details.php?id=<?php echo $program['program_id']; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="mt-2">
                                        <span class="text-muted small">
                                            <i class="fas fa-lock me-1"></i>View-only (other agency)
                                        </span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-muted text-center py-4">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <div>No programs found under this initiative.</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card shadow-sm mt-4">
            <div class="card-header">
                <h5 class="card-title m-0">
                    <i class="fas fa-cogs me-2"></i>Quick Actions
                </h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="view_initiatives.php" class="btn btn-outline-primary">
                        <i class="fas fa-list me-2"></i>View All Initiatives
                    </a>
                    <a href="../programs/view_programs.php" class="btn btn-outline-secondary">
                        <i class="fas fa-tasks me-2"></i>View Your Programs
                    </a>
                    <a href="../dashboard.php" class="btn btn-outline-info">
                        <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

</main>

<style>
/* Initiative-specific styles */
.initiative-header {
    border-left: 4px solid #0d6efd;
    padding-left: 1rem;
}

.initiative-description,
.timeline-info {
    background: #f8f9fa;
    border-radius: 0.375rem;
}

.program-item {
    border-left: 3px solid #dee2e6;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}

.program-item:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.program-item.owned {
    border-left-color: #0d6efd;
    background: #e7f1ff;
}

.program-item.other-agency {
    border-left-color: #6c757d;
    background: #f8f9fa;
}

.programs-list {
    scrollbar-width: thin;
    scrollbar-color: #ccc transparent;
}

.programs-list::-webkit-scrollbar {
    width: 6px;
}

.programs-list::-webkit-scrollbar-track {
    background: transparent;
}

.programs-list::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}

.programs-list::-webkit-scrollbar-thumb:hover {
    background: #999;
}

@media (max-width: 768px) {
    .col-lg-4 {
        margin-top: 2rem;
    }
    
    .card-body {
        padding: 1rem;
    }
    
    .program-item {
        padding: 0.75rem;
        margin-bottom: 0.75rem;
    }
}
</style>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
