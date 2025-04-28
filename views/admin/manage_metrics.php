<?php
/**
* Manage Metrics
* 
* Admin page to manage metrics.
*/

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/admins/index.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ../../login.php');
    exit;
}

// Set page title
$pageTitle = 'Manage Metrics';

require_once '../../includes/admins/index.php';

// Get all metrics using the JSON-based storage function
$period_id = isset($_GET['period_id']) ? intval($_GET['period_id']) : 0;
$metrics = get_all_metrics_data($period_id);

// Get current and all reporting periods for filtering
$current_period = get_current_reporting_period();
$reporting_periods = get_all_reporting_periods();

// Get all sectors for filtering
$sectors = get_all_sectors();

// Initialize filter variables
$selected_sector = isset($_GET['sector_id']) ? intval($_GET['sector_id']) : 0;
$selected_period = $period_id ?: ($current_period ? $current_period['period_id'] : 0);

// Filter metrics by sector if a sector filter is applied
if ($selected_sector > 0) {
    $metrics = array_filter($metrics, function($metric) use ($selected_sector) {
        return $metric['sector_id'] == $selected_sector;
    });
}

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">Manage Metrics</h1>
            <p class="text-muted">Admin interface to manage metrics</p>
        </div>
        <div>
            <a href="javascript:void(0)" class="btn btn-primary me-2" id="createMetricBtn">
                <i class="fas fa-plus-circle me-1"></i> Create New Metric
            </a>
            <button class="btn btn-sm btn-outline-primary" id="refreshPage">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Sector Filter -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">Filter Metrics</h5>
        </div>
        <div class="card-body">
            <form method="get" class="row g-3">
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
                    <button type="submit" class="btn btn-primary me-2">Apply Filter</button>
                    <?php if ($selected_sector > 0 || $selected_period > 0): ?>
                        <a href="manage_metrics.php" class="btn btn-outline-secondary">Clear Filters</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">Metrics</h5>
        </div>
        <div class="card-body">
            <table id="metricsTable" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>Metric ID</th>
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
                    // Apply array_values to reindex after filtering
                    $display_metrics = array_values($metrics);
                    if (empty($display_metrics)): 
                    ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-info-circle me-2"></i>
                                    <?php
                                    if ($selected_sector > 0 && $selected_period > 0) {
                                        echo 'No metrics found for the selected sector and reporting period.';
                                    } elseif ($selected_sector > 0) {
                                        echo 'No metrics found for the selected sector.';
                                    } elseif ($selected_period > 0) {
                                        echo 'No metrics found for the selected reporting period.';
                                    } else {
                                        echo 'No metrics found in the system.';
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($display_metrics as $metric): ?>
                            <tr data-metric-id="<?php echo $metric['metric_id']; ?>">
                                <td><?php echo $metric['metric_id']; ?></td>
                                <td><?php echo htmlspecialchars($metric['sector_name'] ?? 'No Sector'); ?></td>
                                <td><?php echo htmlspecialchars($metric['table_name']); ?></td>
                                <td>
                                    <?php if (isset($metric['quarter']) && isset($metric['year'])): ?>
                                        <span class="badge bg-<?= ($current_period && $metric['period_id'] == $current_period['period_id']) ? 'success' : 'secondary' ?>">
                                            Q<?= $metric['quarter'] ?>-<?= $metric['year'] ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Not Specified</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($metric['created_at'])); ?></td>
                                <td><?php echo date('M j, Y', strtotime($metric['updated_at'])); ?></td>
                                <td>
                                    <a href="view_metric.php?metric_id=<?php echo $metric['metric_id']; ?>" class="btn btn-sm btn-info me-1" role="button">
                                        <i class="fas fa-eye me-1"></i> View
                                    </a>
                                    <a href="edit_metric.php?metric_id=<?php echo $metric['metric_id']; ?>" class="btn btn-sm btn-primary me-1" role="button">
                                        <i class="fas fa-edit me-1"></i> Edit
                                    </a>
                                    <a href="delete_metric.php?metric_id=<?php echo $metric['metric_id']; ?>" class="btn btn-sm btn-danger" role="button" onclick="return confirm('Are you sure you want to delete this metric?');">
                                        <i class="fas fa-trash-alt me-1"></i> Delete
                                    </a>
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
        
        // Create Metric button - redirect to create page
        document.getElementById('createMetricBtn').addEventListener('click', function() {
            // Get selected sector and period from filters, if any
            const sectorId = document.getElementById('sector_id').value;
            const periodId = document.getElementById('period_id').value;
            
            let url = 'edit_metric.php';
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
    });
</script>
