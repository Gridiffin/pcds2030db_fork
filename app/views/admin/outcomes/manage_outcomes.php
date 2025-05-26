<?php
/**
* Manage Outcomes
* 
* Admin page to manage outcomes.
*/

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Set page title
$pageTitle = 'Manage Outcomes';

// Get all outcomes using the JSON-based storage function
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : 0;
$outcomes = get_all_outcomes_data($period_id);

// Ensure $outcomes is always an array to prevent null reference errors
if (!is_array($outcomes)) {
    $outcomes = [];
}

// Get current and all reporting periods for filtering
$current_period = get_current_reporting_period();
$reporting_periods = get_all_reporting_periods();

// Get all sectors for filtering
$sectors = get_all_sectors();

// Initialize filter variables
$selected_sector = isset($_GET['sector_id']) ? intval($_GET['sector_id']) : 0;
$selected_period = $period_id ?: ($current_period ? $current_period['period_id'] : 0);

// Filter outcomes by sector if a sector filter is applied
if ($selected_sector > 0 && !empty($outcomes)) {
    $outcomes = array_filter($outcomes, function($outcome) use ($selected_sector) {
        return isset($outcome['sector_id']) && $outcome['sector_id'] == $selected_sector;
    });
}

// Include header
require_once '../../layouts/header.php';

// Include admin navigation
require_once '../../layouts/admin_nav.php';

// Get outcome creation setting
require_once ROOT_PATH . 'app/lib/admins/settings.php';
$allow_outcome_creation = get_outcome_creation_setting();
?>

<?php /* <!-- Chart.js for visualization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script> */ ?>

<div class="container-fluid px-4 py-4"><div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">Manage Outcomes</h1>
            <p class="text-muted">Admin interface to manage outcomes</p>
        </div>
        <div>            <?php if ($allow_outcome_creation): ?>
            <a href="javascript:void(0)" class="btn btn-forest me-2" id="createMetricBtn">
                <i class="fas fa-plus-circle me-1"></i> Create New Outcome
            </a>
            <?php endif; ?>
            <button class="btn btn-forest-light" id="refreshPage">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>    <!-- Sector Filter -->
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
                </div>                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-forest me-2">Apply Filter</button>                    <?php if ($selected_sector > 0 || $selected_period > 0): ?>
                        <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/manage_outcomes.php" class="btn btn-forest-light">Clear Filters</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>    <div class="card admin-card mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">Outcomes</h5>        </div>
        
        <?php if (!$allow_outcome_creation): ?>
        <div class="card-body border-bottom">
            <div class="alert alert-info mb-0">
                <i class="fas fa-info-circle me-2"></i>
                <strong>Important:</strong> 
                Creation of new outcomes has been disabled by the administrator. This ensures outcomes remain consistent across reporting periods.
                Outcome history is now tracked, and existing outcomes cannot be deleted to maintain data integrity.
                <a href="<?php echo APP_URL; ?>/app/views/admin/settings/system_settings.php" class="alert-link">
                    <i class="fas fa-cog ms-1"></i> Manage settings
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="outcomesTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="table-tab" data-bs-toggle="tab" data-bs-target="#table-view" 
                    type="button" role="tab" aria-controls="table-view" aria-selected="true">
                    <i class="fas fa-table me-1"></i> Table View
                </button>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content" id="outcomesTabsContent">
            <!-- Table View Tab -->
            <div class="tab-pane fade show active" id="table-view" role="tabpanel" aria-labelledby="table-tab">                <div class="card-body p-0">
                    <table id="metricsTable" class="table table-hover table-custom mb-0"><thead>
                            <tr>
                                <th>Outcome ID</th>
                                <th>Sector</th>
                                <th>Table Name</th>
                                <th>Reporting Period</th>
                                <th>Created</th>
                                <th>Last Updated</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>                            <?php 
                            // Apply array_values to reindex after filtering and ensure we have valid data
                            // Extra safety: ensure $outcomes is array before processing
                            if (!is_array($outcomes)) {
                                $outcomes = [];
                            }
                            $display_outcomes = !empty($outcomes) ? array_values($outcomes) : [];
                            if (empty($display_outcomes)): 
                            ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">                                <div class="alert alert-forest alert-info mb-0">
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
                                </tr>                            <?php else: ?>
                                <?php foreach ($display_outcomes as $outcome): ?>
                                    <tr data-metric-id="<?php echo $outcome['metric_id']; ?>">
                                        <td><?php echo $outcome['metric_id']; ?></td>
                                        <td><?php echo htmlspecialchars($outcome['sector_name'] ?? 'No Sector'); ?></td>                                        <td><?php echo htmlspecialchars($outcome['table_name']); ?></td>                                <td>
                                            <?php if (isset($outcome['quarter']) && isset($outcome['year'])): ?>
                                                <span class="status-indicator <?= ($current_period && $outcome['period_id'] == $current_period['period_id']) ? 'status-success' : 'status-info' ?>">
                                                    Q<?= $outcome['quarter'] ?>-<?= $outcome['year'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="status-indicator status-warning">Not Specified</span>
                                            <?php endif; ?>
                                        </td>                                        <td><?php echo date('M j, Y', strtotime($outcome['created_at'])); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($outcome['updated_at'])); ?></td>                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group" aria-label="Primary outcome actions">
                                                <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/view_outcome.php?metric_id=<?php echo $outcome['metric_id']; ?>" 
                                                   class="btn btn-outline-primary" 
                                                   title="View Outcome Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>                                                <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/edit_outcome.php?metric_id=<?php echo $outcome['metric_id']; ?>" 
                                                   class="btn btn-outline-secondary" 
                                                   title="Edit Outcome">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/outcome_history.php?metric_id=<?php echo $outcome['metric_id']; ?>" 
                                                   class="btn btn-outline-info" 
                                                   title="View Change History">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                            </div>
                                            <div class="mt-1 d-grid">
                                                <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/unsubmit_outcome.php?metric_id=<?php echo $outcome['metric_id']; ?>" 
                                                   class="btn btn-outline-warning btn-sm w-100" 
                                                   title="Unsubmit Outcome"
                                                   onclick="return confirm('Are you sure you want to unsubmit this outcome?');">
                                                    <i class="fas fa-undo"></i> Unsubmit
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
    </div>
</div>

<script>
    /*
    document.addEventListener('DOMContentLoaded', function() {
        // Refresh page button
        const refreshBtn = document.getElementById('refreshPage');
        if (refreshBtn) {
            refreshBtn.addEventListener('click', function() {
                window.location.reload();
            });
        }

        // Create Metric button - redirect to create page
        const createBtn = document.getElementById('createMetricBtn');
        if (createBtn) {
            createBtn.addEventListener('click', function() {
                // Please verify this is the correct URL for creating an outcome
                window.location.href = '<?php echo APP_URL; ?>/app/views/admin/outcomes/create_outcome.php';
            });
        }
        
        // Auto-submit filter when sector changes
        const sectorFilter = document.getElementById('sector_id');
        if (sectorFilter) {
            sectorFilter.addEventListener('change', function() {
                if (this.form) {
                    this.form.submit();
                }
            });
        }
        
        // Auto-submit filter when period changes
        const periodFilter = document.getElementById('period_id');
        if (periodFilter) {
            periodFilter.addEventListener('change', function() {
                if (this.form) {
                    this.form.submit();
                }
            });
        }
        
        // Fix dropdown menu functionality - This line was incomplete.
        // If you are using Bootstrap 5, it might look like this:
        // document.querySelectorAll('.dropdown-toggle').forEach(function(dropdownToggle) {
        //     new bootstrap.Dropdown(dropdownToggle);
        // });
        // For now, I will comment out the problematic line if it's literally {…}
        // document.querySelectorAll('.dropdown-toggle').forEach(function(dropdownToggle) {…});
        // If the '...' was a placeholder for actual code, please ensure that code is correct.
        // Assuming the error was from getElementById, this part might not be the immediate cause of *that* error.
    });
    */
</script>



