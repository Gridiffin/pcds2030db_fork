<?php
/**
* Manage Outcomes
* 
* Admin page to manage outcomes.
*/

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Include necessary files
require_once PROJECT_ROOT_PATH . 'app/config/config.php';
require_once PROJECT_ROOT_PATH . 'app/lib/db_connect.php';
require_once PROJECT_ROOT_PATH . 'app/lib/session.php';
require_once PROJECT_ROOT_PATH . 'app/lib/functions.php'; // Contains get_all_outcomes_data, get_current_reporting_period, get_all_reporting_periods, get_all_sectors
require_once PROJECT_ROOT_PATH . 'app/lib/admins/index.php'; // Contains is_admin

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'Manage Outcomes';

// Get all outcomes using the JSON-based storage function
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : 0;
// Ensure get_all_outcomes_data is used and the variable is $outcomes
$outcomes = get_all_outcomes_data($period_id); 

// Get current and all reporting periods for filtering
$current_period = get_current_reporting_period();
$reporting_periods = get_all_reporting_periods();

// Get all sectors for filtering
$sectors = get_all_sectors();

// Initialize filter variables
$selected_sector = isset($_GET['sector_id']) ? intval($_GET['sector_id']) : 0;
$selected_period = $period_id ?: ($current_period ? $current_period['period_id'] : 0);

// Filter outcomes by sector if a sector filter is applied
if ($selected_sector > 0) {
    // Ensure $outcomes is an array before filtering
    if (is_array($outcomes)) {
        $outcomes = array_filter($outcomes, function($outcome) use ($selected_sector) {
            return isset($outcome['sector_id']) && $outcome['sector_id'] == $selected_sector;
        });
    } else {
        $outcomes = []; // Initialize as empty array if not an array
    }
}

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">Manage Outcomes</h1>
            <p class="text-muted">Admin interface to manage outcomes</p>
        </div>
        <div>
            <a href="javascript:void(0)" class="btn btn-forest me-2" id="createOutcomeBtn">
                <i class="fas fa-plus-circle me-1"></i> Create New Outcome
            </a>
            <button class="btn btn-forest-light" id="refreshPage">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Sector Filter -->
    <div class="card admin-card mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">Filter Outcomes</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3 filter-controls">
                <div class="col-md-4">
                    <label for="period_id" class="form-label">Filter by Reporting Period</label>
                    <select name="period_id" id="period_id" class="form-select">
                        <option value="0">All Reporting Periods</option>
                        <?php foreach ($reporting_periods as $period): ?>
                            <option value="<?= $period['period_id'] ?>" <?= $selected_period == $period['period_id'] ? 'selected' : '' ?>>
                                Q<?= $period['quarter'] ?>-<?= $period['year'] ?> 
                                (<?= $period['status'] == 'open' ? 'Current' : 'Closed' ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="sector_id" class="form-label">Filter by Sector</label>
                    <select name="sector_id" id="sector_id" class="form-select">
                        <option value="0">All Sectors</option>
                        <?php foreach ($sectors as $sector): ?>
                            <option value="<?= $sector['sector_id'] ?>" <?= $selected_sector == $sector['sector_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sector['sector_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-forest me-2">Apply Filter</button>
                    <?php if ($selected_sector > 0 || $selected_period > 0): ?>
                        <a href="<?php echo APP_URL; ?>/app/views/admin/manage_outcomes.php" class="btn btn-forest-light">Clear Filters</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card admin-card mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">Outcomes</h5>
        </div>
        <div class="card-body p-0">
            <table id="outcomesTable" class="table table-forest">
                <thead>
                    <tr>
                        <th>Outcome ID</th>
                        <th>Sector</th>
                        <th>Table Name</th>
                        <th>Reporting Period</th>
                        <th>Created</th>
                        <th>Last Updated</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    // Ensure $outcomes is an array before using array_values
                    $display_outcomes = array_values(is_array($outcomes) ? $outcomes : []);
                    if (empty($display_outcomes)): 
                    ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="alert alert-forest alert-info mb-0">
                                    <i class="fas fa-info-circle alert-icon"></i><?php
                                    if ($selected_sector > 0 && $selected_period > 0) {
                                        echo 'No outcomes found for the selected sector and reporting period.';
                                    } elseif ($selected_sector > 0) {
                                        echo 'No outcomes found for the selected sector.';
                                    } elseif ($selected_period > 0) {
                                        echo 'No outcomes found for the selected reporting period.';
                                    } else {
                                        echo 'No outcomes found in the system.';
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($display_outcomes as $outcome): ?>                            <tr data-outcome-id="<?php echo $outcome['metric_id']; ?>">
                                <td><?php echo $outcome['metric_id']; ?></td>
                                <td><?php echo htmlspecialchars($outcome['sector_name'] ?? 'No Sector'); ?></td>
                                <td><?php echo htmlspecialchars($outcome['table_name']); ?></td>
                                <td>
                                    <?php if (isset($outcome['quarter']) && isset($outcome['year'])): ?>
                                        <span class="status-indicator <?= ($current_period && $outcome['period_id'] == $current_period['period_id']) ? 'status-success' : 'status-info' ?>">
                                            Q<?= $outcome['quarter'] ?>-<?= $outcome['year'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="status-indicator status-warning">Not Specified</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($outcome['created_at'])); ?></td>
                                <td><?php echo date('M j, Y', strtotime($outcome['updated_at'])); ?></td>
                                <td>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="<?php echo APP_URL; ?>/app/views/admin/unsubmit_outcome.php?outcome_id=<?php echo $outcome['metric_id']; ?>" class="btn btn-forest-light btn-sm me-1" role="button" onclick="return confirm('Are you sure you want to unsubmit this outcome?');">
                                            <i class="fas fa-undo me-1"></i> Unsubmit
                                        </a>
                                        <a href="view_outcome.php?outcome_id=<?php echo $outcome['metric_id']; ?>" class="btn btn-forest-light btn-sm me-1" role="button">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/app/views/admin/edit_outcome.php?outcome_id=<?php echo $outcome['metric_id']; ?>" class="btn btn-forest btn-sm me-1" role="button">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/app/views/admin/delete_outcome.php?outcome_id=<?php echo $outcome['metric_id']; ?>" class="btn btn-forest-light btn-sm text-danger" role="button" onclick="return confirm('Are you sure you want to delete this outcome?');">
                                            <i class="fas fa-trash-alt me-1"></i> Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Refresh page button
        document.getElementById('refreshPage').addEventListener('click', function() {
            window.location.reload();
        });
        
        // Create Outcome button - redirect to create page
        document.getElementById('createOutcomeBtn').addEventListener('click', function() {
            // Get selected sector and period from filters, if any
            const sectorId = document.getElementById('sector_id').value;
            const periodId = document.getElementById('period_id').value;
            
            let url = 'edit_outcome.php'; // Changed from edit_metric.php
            let params = [];
            
            if (sectorId > 0) {
                params.push('sector_id=' + sectorId);
            }
            
            if (periodId > 0) {
                params.push('period_id=' + periodId);
            }
            
            if (params.length > 0) {
                url += '?' + params.join('&');
            }
            
            window.location.href = url;
        });
        
        // Auto-submit filter when sector changes
        document.getElementById('sector_id').addEventListener('change', function() {
            this.form.submit();
        });
        
        // Auto-submit filter when period changes
        document.getElementById('period_id').addEventListener('change', function() {
            this.form.submit();
        });
        
        // Fix dropdown menu functionality (if any dropdowns are used, this is a generic fix)
        document.querySelectorAll('.dropdown-toggle').forEach(function(dropdownToggle) {
            dropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                const dropdown = this.closest('.dropdown');
                if (dropdown) {
                    dropdown.classList.toggle('show');
                    const dropdownMenu = dropdown.querySelector('.dropdown-menu');
                    if (dropdownMenu) {
                        dropdownMenu.classList.toggle('show');
                    }
                    this.setAttribute('aria-expanded', 
                        this.getAttribute('aria-expanded') === 'true' ? 'false' : 'true');
                }
            });
        });
    });
</script>
<?php 
// Include footer
require_once '../layouts/footer.php'; 
?>



