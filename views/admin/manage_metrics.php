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
require_once '../../includes/admin_functions.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ../../login.php');
    exit;
}

// Set page title
$pageTitle = 'Manage Metrics';

require_once '../../includes/admin_functions.php';

// Get all metrics
$metrics = get_all_metrics();

// Get all sectors and build sector_id to sector_name map
$sectors = get_all_sectors();
$sector_map = [];
foreach ($sectors as $sector) {
    $sector_map[$sector['sector_id']] = $sector['sector_name'];
}

// Sort metrics descending by metric_id
usort($metrics, function($a, $b) {
    return $b['metric_id'] <=> $a['metric_id'];
});

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
        <button class="btn btn-sm btn-outline-primary" id="refreshPage">
            <i class="fas fa-sync-alt me-1"></i> Refresh
        </button>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $unique_metrics=[];
                    foreach ($metrics as $metric){
                        if (!in_array($metric['metric_id'], $unique_metrics)) {
                            $unique_metrics[] = $metric['metric_id'];
                    ?>
                        <tr data-metric-id="<?php echo $metric['metric_id']; ?>">
                            <td><?php echo $metric['metric_id']; ?></td>
                            <td><?php echo htmlspecialchars($sector_map[$metric['sector_id']] ?? 'Unknown'); ?></td>
                            <td><?php echo htmlspecialchars($metric['table_name']); ?></td>
                            <td>
                                <a href="edit_metric.php?metric_id=<?php echo $metric['metric_id']; ?>" class="btn btn-sm btn-primary edit-metric" role="button">Edit</a>
                                <a href="delete_metric.php?metric_id=<?php echo $metric['metric_id']; ?>" class="btn btn-sm btn-danger delete-metric" role="button" onclick="return confirm('Are you sure you want to delete this metric?');">Delete</a>
                            </td>
                        </tr>
                    <?php 
                        } 
                    } 
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
