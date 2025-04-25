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

// Get all metrics
$metrics = get_all_metrics();

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
                        <th>Metric Name</th>
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
                        } 
                    ?>
                        <tr data-metric-id="<?php echo $metric['metric_id']; ?>">
                            <td><?php echo $metric['metric_id']; ?></td>
                            <td><?php echo htmlspecialchars($metric['column_title']); ?></td>
                            <td><?php echo htmlspecialchars($metric['table_name']); ?></td>
                            <td>
                                <button class="btn btn-sm btn-primary edit-metric" data-metric-id="<?php echo $metric['metric_id']; ?>">Edit</button>
                                <button class="btn btn-sm btn-danger delete-metric" data-metric-id="<?php echo $metric['metric_id']; ?>">Delete</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
