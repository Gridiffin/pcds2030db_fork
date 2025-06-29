<?php
/**
* Manage Outcomes - Debug Version (No JS)
* Testing if JavaScript is causing the blank page issue
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
$pageTitle = 'Manage Outcomes - Debug';

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

// Configure modern page header
$header_config = [
    'title' => 'Manage Outcomes (Debug - No JS)',
    'subtitle' => 'Testing if JavaScript is causing the blank page issue',
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'edit_metric.php',
            'text' => 'Create New Outcome',
            'icon' => 'fas fa-plus-circle',
            'class' => 'btn-primary'
        ],
        [
            'url' => '?refresh=1',
            'text' => 'Refresh',
            'icon' => 'fa-sync-alt',
            'class' => 'btn-secondary'
        ]
    ]
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<div class="container-fluid px-4 py-4">

    <!-- Debug Information -->
    <div class="card mb-4" style="border-left: 4px solid #17a2b8;">
        <div class="card-header bg-info text-white">
            <h5 class="card-title m-0">Debug Information</h5>
        </div>
        <div class="card-body">
            <p><strong>Total Outcomes Found:</strong> <?= count($outcomes) ?></p>
            <p><strong>Selected Sector:</strong> <?= $selected_sector ?></p>
            <p><strong>Selected Period:</strong> <?= $selected_period ?></p>
            <p><strong>Current Period:</strong> <?= $current_period ? "Q{$current_period['quarter']}-{$current_period['year']}" : 'None' ?></p>
        </div>
    </div>

    <!-- Sector Filter -->
    <div class="card mb-4">
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
                    <button type="submit" class="btn btn-primary me-2">Apply Filter</button>
                    <?php if ($selected_sector > 0 || $selected_period > 0): ?>
                        <a href="?" class="btn btn-secondary">Clear Filters</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

    <!-- Outcomes Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="card-title m-0">
                <i class="fas fa-chart-line me-2"></i>Outcomes Data
            </h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
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
                        // Apply array_values to reindex after filtering and ensure we have valid data
                        if (!is_array($outcomes)) {
                            $outcomes = [];
                        }
                        $display_outcomes = !empty($outcomes) ? array_values($outcomes) : [];
                        if (empty($display_outcomes)): 
                        ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        <?php
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
                            <?php foreach ($display_outcomes as $outcome): ?>
                                <tr data-metric-id="<?php echo $outcome['metric_id']; ?>">
                                    <td><?php echo $outcome['metric_id']; ?></td>
                                    <td><?php echo htmlspecialchars($outcome['sector_name'] ?? 'No Sector'); ?></td>
                                    <td><?php echo htmlspecialchars($outcome['table_name']); ?></td>
                                    <td>
                                        <?php if (isset($outcome['quarter']) && isset($outcome['year'])): ?>
                                            <span class="badge <?= ($current_period && $outcome['period_id'] == $current_period['period_id']) ? 'bg-success' : 'bg-info' ?>">
                                                Q<?= $outcome['quarter'] ?>-<?= $outcome['year'] ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning">Not Specified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($outcome['created_at'])); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($outcome['updated_at'])); ?></td>
                                    <td>
                                        <a href="<?php echo APP_URL; ?>/app/views/admin/metrics/unsubmit.php?metric_id=<?php echo $outcome['metric_id']; ?>" class="btn btn-sm btn-warning me-1">
                                            <i class="fas fa-undo me-1"></i> Unsubmit
                                        </a>
                                        <a href="metrics/view_metric.php?metric_id=<?php echo $outcome['metric_id']; ?>" class="btn btn-sm btn-info me-1">
                                            <i class="fas fa-eye me-1"></i> View
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/app/views/admin/metrics/edit_metric.php?metric_id=<?php echo $outcome['metric_id']; ?>" class="btn btn-sm btn-primary me-1">
                                            <i class="fas fa-edit me-1"></i> Edit
                                        </a>
                                        <a href="<?php echo APP_URL; ?>/app/views/admin/metrics/delete_metric.php?metric_id=<?php echo $outcome['metric_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this outcome?');">
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
</div>

<?php require_once '../../layouts/footer.php'; ?>
