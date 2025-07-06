<?php
/**
 * Admin View Initiative
 * 
 * Detailed view of a specific initiative for administrators with status grid.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/initiative_functions.php';
require_once ROOT_PATH . 'app/lib/db_names_helper.php';
require_once ROOT_PATH . 'app/lib/asset_helpers.php';

// Verify user is admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get initiative ID from URL
$initiative_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Validate initiative_id
if (!$initiative_id) {
    $_SESSION['message'] = 'Invalid initiative ID.';
    $_SESSION['message_type'] = 'danger';
    header('Location: manage_initiatives.php');
    exit;
}

// Get initiative details
$initiative = get_initiative_by_id($initiative_id);

if (!$initiative) {
    $_SESSION['message'] = 'Initiative not found.';
    $_SESSION['message_type'] = 'danger';
    header('Location: manage_initiatives.php');
    exit;
}

// Get programs associated with this initiative
$initiative_programs = get_initiative_programs($initiative_id);

// Calculate initiative health/statistics
$total_programs = count($initiative_programs);
$active_programs = 0;
$agencies_involved = [];

foreach ($initiative_programs as $program) {
    if (!empty($program['agency_name'])) {
        $agencies_involved[$program['agency_name']] = true;
    }
}
$total_agencies = count($agencies_involved);

// Load config and extract column names
$config = include __DIR__ . '/../../../config/db_names.php';
$initiative_id_col = $config['columns']['initiatives']['id'];
$initiative_name_col = $config['columns']['initiatives']['name'];
$initiative_number_col = $config['columns']['initiatives']['number'];
$initiative_description_col = $config['columns']['initiatives']['description'];
$start_date_col = $config['columns']['initiatives']['start_date'];
$end_date_col = $config['columns']['initiatives']['end_date'];
$is_active_col = $config['columns']['initiatives']['is_active'];
$created_at_col = $config['columns']['initiatives']['created_at'];
$updated_at_col = $config['columns']['initiatives']['updated_at'];

// Set page title
$pageTitle = 'View Initiative - ' . ($initiative[$initiative_name_col] ?? '');

// Include additional CSS for status grid
$additionalCSS = [
    asset_url('css', 'main.css')
];

// Include header
require_once '../../layouts/header.php';

// Configure breadcrumbs
$breadcrumbs = [
    ['title' => 'Dashboard', 'url' => '../../admin/dashboard.php'],
    ['title' => 'Initiatives', 'url' => 'manage_initiatives.php'],
    ['title' => 'View Initiative']
];

// Configure the modern page header
$header_config = [
    'title' => 'Initiative Details',
    'subtitle' => 'Comprehensive view of initiative progress and programs',
    'variant' => 'green',
    'breadcrumbs' => $breadcrumbs,
    'actions' => [
        [
            'text' => 'Edit Initiative',
            'url' => 'edit.php?id=' . $initiative_id,
            'class' => 'btn-outline-light-active',
            'icon' => 'fas fa-edit'
        ],
        [
            'text' => 'Back to Initiatives',
            'url' => 'manage_initiatives.php',
            'class' => 'btn-outline-light-active',
            'icon' => 'fas fa-arrow-left'
        ]
    ]
];

// Include the modern page header
require_once '../../layouts/page_header.php';
?>

<main class="flex-fill">

<!-- Initiative Overview Section -->
<div class="container-fluid">
    <!-- Initiative Header -->
    <div class="initiative-overview mb-4">
        <div class="initiative-title">
            <i class="fas fa-lightbulb"></i>
            <?php echo htmlspecialchars($initiative[$initiative_name_col] ?? ''); ?>
            <?php if (!empty($initiative[$initiative_number_col])): ?>
                <span class="badge bg-primary ms-3" style="font-size: 0.6em; padding: 0.5rem 1rem; vertical-align: middle;">
                    #<?php echo htmlspecialchars($initiative[$initiative_number_col]); ?>
                </span>
            <?php endif; ?>
        </div>
        <div class="initiative-meta">
            <div class="meta-item">
                <i class="fas fa-calendar"></i>
                <span>
                    <?php 
                    if (!empty($initiative[$start_date_col]) && !empty($initiative[$end_date_col])) {
                        echo date('Y-m-d', strtotime($initiative[$start_date_col])) . ' to ' . date('Y-m-d', strtotime($initiative[$end_date_col]));
                    } elseif (!empty($initiative[$start_date_col])) {
                        echo 'Started: ' . date('Y-m-d', strtotime($initiative[$start_date_col]));
                    } elseif (!empty($initiative[$end_date_col])) {
                        echo 'Ends: ' . date('Y-m-d', strtotime($initiative[$end_date_col]));
                    } else {
                        echo 'No timeline specified';
                    }
                    ?>
                </span>
            </div>
            <div class="meta-item">
                <i class="fas fa-user"></i>
                <span>Created by: <?php echo htmlspecialchars($initiative['created_by_username'] ?? 'Unknown'); ?></span>
            </div>
            <div class="meta-item">
                <i class="fas fa-building"></i>
                <span><?php echo $total_agencies; ?> agencies involved</span>
            </div>
            <div class="status-programs">
                <i class="fas fa-tasks"></i>
                <?php echo $total_programs; ?> programs total
            </div>
        </div>
    </div>
</div>

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
                        <?php if (!empty($initiative[$is_active_col])): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Description -->
                <?php if (!empty($initiative[$initiative_description_col])): ?>
                <div class="initiative-description mb-4">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-align-left me-1"></i>Description
                    </h6>
                    <div class="p-3 bg-light rounded">
                        <p class="mb-0"><?php echo nl2br(htmlspecialchars($initiative[$initiative_description_col])); ?></p>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Timeline Information -->
                <div class="mb-4">
                    <h6 class="text-muted mb-2">
                        <i class="fas fa-calendar-alt me-1"></i>Timeline
                    </h6>
                    <div class="timeline-info p-3 bg-light rounded">
                        <?php if (!empty($initiative[$start_date_col]) || !empty($initiative[$end_date_col])): ?>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-check me-2 text-success"></i>
                                <span>
                                    <?php 
                                    if (!empty($initiative[$start_date_col]) && !empty($initiative[$end_date_col])) {
                                        $start = new DateTime($initiative[$start_date_col]);
                                        $end = new DateTime($initiative[$end_date_col]);
                                        $interval = $start->diff($end);
                                        $years = $interval->y;
                                        $months = $interval->m;
                                        
                                        echo $start->format('F j, Y') . ' to ' . $end->format('F j, Y');
                                        
                                        if ($years > 0 || $months > 0) {
                                            echo ' <span class="text-muted">(';
                                            if ($years > 0) echo $years . ' year' . ($years > 1 ? 's' : '');
                                            if ($years > 0 && $months > 0) echo ', ';
                                            if ($months > 0) echo $months . ' month' . ($months > 1 ? 's' : '');
                                            echo ')</span>';
                                        }
                                    } elseif (!empty($initiative[$start_date_col])) {
                                        echo 'Started: ' . date('F j, Y', strtotime($initiative[$start_date_col]));
                                    } elseif (!empty($initiative[$end_date_col])) {
                                        echo 'Ends: ' . date('F j, Y', strtotime($initiative[$end_date_col]));
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
                                    <h3 class="mb-1"><?php echo $total_programs; ?></h3>
                                    <div class="small">Total Programs</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card bg-secondary text-white">
                                <div class="card-body text-center">
                                    <h3 class="mb-1"><?php echo $total_agencies; ?></h3>
                                    <div class="small">Agencies Involved</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Programs List -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="card-title m-0">
                    <i class="fas fa-tasks me-2"></i>Associated Programs
                    <span class="badge bg-primary ms-2"><?php echo count($initiative_programs); ?></span>
                </h5>
            </div>
            <div class="card-body">
                <?php if (!empty($initiative_programs)): ?>
                    <div class="programs-list">
                        <?php foreach ($initiative_programs as $program): ?>
                            <div class="program-item p-3 border rounded mb-3">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="program-header d-flex align-items-center mb-2">
                                            <?php if (!empty($program['program_number'])): ?>
                                                <span class="badge bg-info me-2" style="font-size: 0.7em;">
                                                    <?php echo htmlspecialchars($program['program_number']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <h6 class="mb-0 fw-semibold">
                                                <?php echo htmlspecialchars($program['program_name']); ?>
                                            </h6>
                                        </div>
                                        
                                        <?php if (!empty($program['sector_name']) || !empty($program['agency_name'])): ?>
                                        <div class="program-meta small text-muted">
                                            <?php if (!empty($program['sector_name'])): ?>
                                                <span class="me-3">
                                                    <i class="fas fa-tag me-1"></i>
                                                    <?php echo htmlspecialchars($program['sector_name']); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($program['agency_name'])): ?>
                                                <span>
                                                    <i class="fas fa-building me-1"></i>
                                                    <?php echo htmlspecialchars($program['agency_name']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <a href="../programs/view_program.php?id=<?php echo $program['program_id']; ?>" 
                                           class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-eye me-1"></i>View Details
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-muted text-center py-4">
                        <i class="fas fa-info-circle fa-2x mb-3"></i>
                        <div>No programs found under this initiative.</div>
                        <div class="mt-2">
                            <a href="../programs/bulk_assign_initiatives.php" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>Assign Programs
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Quick Actions -->
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h6 class="card-title m-0">
                    <i class="fas fa-cogs me-2"></i>Quick Actions
                </h6>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="edit.php?id=<?php echo $initiative_id; ?>" class="btn btn-primary">
                        <i class="fas fa-edit me-2"></i>Edit Initiative
                    </a>
                    <a href="../programs/bulk_assign_initiatives.php" class="btn btn-outline-secondary">
                        <i class="fas fa-tasks me-2"></i>Manage Programs
                    </a>
                    <a href="manage_initiatives.php" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                </div>
            </div>
        </div>

        <!-- Initiative Details -->
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="card-title m-0">
                    <i class="fas fa-info-circle me-2"></i>Initiative Details
                </h6>
            </div>
            <div class="card-body">
                <div class="detail-item mb-3">
                    <div class="small text-muted">Created</div>
                    <div class="fw-medium">
                        <?php echo date('M j, Y', strtotime($initiative[$created_at_col])); ?>
                    </div>
                </div>
                
                <?php if (!empty($initiative[$updated_at_col]) && $initiative[$updated_at_col] !== $initiative[$created_at_col]): ?>
                <div class="detail-item mb-3">
                    <div class="small text-muted">Last Updated</div>
                    <div class="fw-medium">
                        <?php echo date('M j, Y', strtotime($initiative[$updated_at_col])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="detail-item mb-3">
                    <div class="small text-muted">Created By</div>
                    <div class="fw-medium">
                        <?php echo htmlspecialchars($initiative['created_by_username'] ?? 'Unknown'); ?>
                    </div>
                </div>
                
                <div class="detail-item mb-0">
                    <div class="small text-muted">Status</div>
                    <div>
                        <?php if (!empty($initiative[$is_active_col])): ?>
                            <span class="badge bg-success">Active</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Inactive</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</main>

<!-- Status Grid Chart Section -->
<div class="container-fluid mb-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-chart-line me-2"></i>Initiative Status Grid
            </h5>
        </div>
        <div class="card-body p-0">
            <div id="status_grid_here">
                <div class="status-grid-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Loading status grid...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Status Grid Component -->
<script src="<?php echo asset_url('js', 'components/status-grid.js'); ?>"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get initiative ID from URL parameter
    const initiativeId = <?php echo $initiative_id; ?>;
    
    if (initiativeId) {
        // Initialize StatusGrid component with status grid data API
        const apiUrl = "<?php echo rtrim(BASE_URL, '/'); ?>/app/api/simple_gantt_data.php?initiative_id=" + initiativeId;
        const statusGrid = new StatusGrid('status_grid_here', apiUrl);
        
        // Store reference globally for debugging
        window.statusGrid = statusGrid;
    } else {
        document.getElementById('status_grid_here').innerHTML = 
            '<div class="status-grid-error">No initiative ID provided.</div>';
    }
});
</script>

<style>
/* Initiative specific styles */
.initiative-overview {
    background: linear-gradient(135deg, var(--forest-gradient-primary, #537D5D) 0%, var(--forest-gradient-secondary, #73946B) 100%);
    color: white;
    padding: 2rem;
    border-radius: 12px;
    margin-bottom: 2rem;
    box-shadow: 0 8px 32px rgba(83, 125, 93, 0.2);
}

.initiative-title {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 1rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.initiative-title i {
    margin-right: 1rem;
    color: rgba(255,255,255,0.9);
}

.initiative-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 2rem;
    margin-bottom: 1rem;
}

.meta-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.95rem;
    opacity: 0.95;
}

.meta-item i {
    color: rgba(255,255,255,0.8);
}

.status-programs {
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.status-programs i {
    color: rgba(255,255,255,0.8);
}

.program-item {
    transition: all 0.2s ease;
    border: 1px solid #e9ecef !important;
}

.program-item:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border-color: var(--forest-medium, #73946B) !important;
}

.detail-item {
    padding: 0.5rem 0;
}

@media (max-width: 768px) {
    .initiative-meta {
        flex-direction: column;
        gap: 1rem;
    }
    
    .initiative-title {
        font-size: 1.5rem;
    }
}
</style>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
