<?php
/**
 * Agency Initiatives View
 * 
 * Read-only view of initiatives that have programs assigned to the current agency.
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
require_once PROJECT_ROOT_PATH . 'lib/db_names_helper.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get message from session if available
$message = $_SESSION['message'] ?? '';
$messageType = $_SESSION['message_type'] ?? 'info';

// Clear message from session
if (isset($_SESSION['message'])) {
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}

// Set page title
$pageTitle = 'Initiatives';

// Get filter parameters
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build filters array
$filters = [];
if (!empty($search)) {
    $filters['search'] = $search;
}
if ($status_filter !== '') {
    $filters['is_active'] = $status_filter === 'active' ? 1 : 0;
}

// Get column names using db_names helper
$initiative_id_col = get_column_name('initiatives', 'id');
$initiative_name_col = get_column_name('initiatives', 'name');
$initiative_number_col = get_column_name('initiatives', 'number');
$initiative_description_col = get_column_name('initiatives', 'description');
$start_date_col = get_column_name('initiatives', 'start_date');
$end_date_col = get_column_name('initiatives', 'end_date');
$is_active_col = get_column_name('initiatives', 'is_active');

// Get initiatives for admin (all initiatives)
$initiatives = get_all_initiatives($filters);

// Additional scripts
$additionalScripts = [];

// Additional styles  
$additionalStyles = [];

// Include header
require_once '../../layouts/header.php';

// Configure the modern page header
$header_config = [
    'title' => 'Initiatives',
    'subtitle' => 'View initiatives where your agency has assigned programs',
    'variant' => 'blue',
    'actions' => []
];

// Include the modern page header
require_once '../../layouts/page_header.php';
?>

<main class="flex-fill">

<?php if ($message): ?>
<div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
    <?php echo htmlspecialchars($message); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<!-- Search and Filter Section -->
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-6 col-sm-12">
                <label for="search" class="form-label">Search Initiatives</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Search by name, number, or description" 
                           value="<?php echo htmlspecialchars($search); ?>">
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <label for="status" class="form-label">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">All Status</option>
                    <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Active</option>
                    <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                </select>
            </div>
            <div class="col-md-3 col-sm-6 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search me-1"></i> Search
                </button>
                <a href="initiatives.php" class="btn btn-outline-secondary">
                    <i class="fas fa-undo me-1"></i> Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Initiatives List -->
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="card-title m-0">
            <i class="fas fa-lightbulb me-2"></i>Your Initiatives
            <span class="badge bg-primary ms-2"><?php echo count($initiatives); ?></span>
        </h5>
        <div class="d-flex align-items-center">
            <div class="text-muted small">
                <i class="fas fa-info-circle me-1"></i>
                Showing initiatives where your agency has programs
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <?php if (empty($initiatives)): ?>
            <div class="text-center py-5">
                <i class="fas fa-lightbulb fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No initiatives found</h5>
                <p class="text-muted">
                    <?php if (!empty($search) || $status_filter !== ''): ?>
                        No initiatives match your search criteria.
                    <?php else: ?>
                        Your agency doesn't have any programs assigned to initiatives yet.
                    <?php endif; ?>
                </p>
                <?php if (!empty($search) || $status_filter !== ''): ?>
                    <a href="initiatives.php" class="btn btn-outline-primary">
                        <i class="fas fa-undo me-1"></i>Clear Filters
                    </a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Table View Only -->
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Initiative</th>
                            <th class="text-center">Total Programs</th>
                            <th>Timeline</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($initiatives as $initiative): ?>
                            <tr data-initiative-id="<?php echo isset($initiative[$initiative_id_col]) ? htmlspecialchars($initiative[$initiative_id_col]) : ''; ?>">
                                <td>
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold mb-1">
                                                <?php if (!empty($initiative[$initiative_number_col])): ?>
                                                    <span class="badge bg-primary me-2">
                                                        <?php echo htmlspecialchars($initiative[$initiative_number_col] ?? ''); ?>
                                                    </span>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($initiative[$initiative_name_col] ?? ''); ?>
                                            </div>
                                            <?php if (!empty($initiative[$initiative_description_col])): ?>
                                                <div class="text-muted small" style="line-height: 1.4;">
                                                    <?php 
                                                    $description = htmlspecialchars($initiative[$initiative_description_col] ?? '');
                                                    echo strlen($description) > 120 ? substr($description, 0, 120) . '...' : $description;
                                                    ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">
                                        <?php echo isset($initiative['program_count']) ? $initiative['program_count'] : 0; ?> total
                                    </span>
                                </td>
                                <td>
                                    <?php if (!empty($initiative[$start_date_col]) || !empty($initiative[$end_date_col])): ?>
                                        <div class="small">
                                            <?php if (!empty($initiative[$start_date_col]) && !empty($initiative[$end_date_col])): ?>
                                                <i class="fas fa-calendar-alt me-1 text-muted"></i>
                                                <?php echo date('M j, Y', strtotime($initiative[$start_date_col] ?? '')); ?> - 
                                                <?php echo date('M j, Y', strtotime($initiative[$end_date_col] ?? '')); ?>
                                            <?php elseif (!empty($initiative[$start_date_col])): ?>
                                                <i class="fas fa-play me-1 text-success"></i>
                                                Started: <?php echo date('M j, Y', strtotime($initiative[$start_date_col] ?? '')); ?>
                                            <?php elseif (!empty($initiative[$end_date_col])): ?>
                                                <i class="fas fa-flag-checkered me-1 text-warning"></i>
                                                Due: <?php echo date('M j, Y', strtotime($initiative[$end_date_col] ?? '')); ?>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted small">
                                            <i class="fas fa-calendar-times me-1"></i>
                                            No timeline
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($initiative[$is_active_col])): ?>
                                        <span class="badge bg-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Inactive</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <a href="view_initiative.php?id=<?php echo isset($initiative[$initiative_id_col]) ? htmlspecialchars($initiative[$initiative_id_col]) : ''; ?>" 
                                       class="btn btn-outline-primary btn-sm"
                                       title="View Initiative Details">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

</main>

<style>
@media (max-width: 768px) {
    .table-responsive table {
        font-size: 0.9em;
    }
    
    .badge {
        font-size: 0.7em;
    }
}

.view-container {
    transition: opacity 0.3s ease;
}
</style>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
