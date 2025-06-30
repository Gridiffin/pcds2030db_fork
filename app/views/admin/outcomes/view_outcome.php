<?php
/**
 * View Submitted Outcome Details for Admin
 * 
 * Allows admin users to view the details of submitted outcomes from any sector.
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php'; // Contains legacy functions
require_once ROOT_PATH . 'app/lib/admins/outcomes.php'; // Contains updated outcome functions
require_once ROOT_PATH . 'app/lib/admins/index.php'; // Contains is_admin
require_once ROOT_PATH . 'app/lib/admins/users.php'; // Contains user information functions
require_once ROOT_PATH . 'app/lib/status_helpers.php'; // For display_submission_status_badge
require_once ROOT_PATH . 'app/lib/rating_helpers.php'; // For display_overall_rating_badge

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Check if metric_id is provided (the system uses metric_id as primary identifier)
if (!isset($_GET['metric_id']) || !is_numeric($_GET['metric_id'])) {
    $_SESSION['error_message'] = 'Invalid outcome ID.';
    header('Location: manage_outcomes.php');
    exit;
}

$metric_id = (int) $_GET['metric_id'];

// Get outcome data using the updated function with improved error handling
$outcome_details = get_outcome_data_for_display($metric_id);

if (!$outcome_details) {
    $_SESSION['error_message'] = 'Outcome not found.';
    header('Location: manage_outcomes.php');
    exit;
}

$table_name = $outcome_details['table_name'];
$period_id = $outcome_details['period_id'];
$year = $outcome_details['year'] ?? 'N/A';
$quarter = $outcome_details['quarter'] ?? 'N/A';
$status = $outcome_details['status'] ?? 'submitted'; // Default to submitted if not present
$overall_rating = $outcome_details['overall_rating'] ?? null;

$created_at = new DateTime($outcome_details['created_at']);
$updated_at = new DateTime($outcome_details['updated_at']);

// Get flexible structure configuration (same logic as agency side)
$table_structure_type = $outcome_details['table_structure_type'] ?? 'monthly';
$row_config = json_decode($outcome_details['row_config'] ?? '{}', true);
$column_config = json_decode($outcome_details['column_config'] ?? '{}', true);

// Parse the outcome data
$outcome_data = json_decode($outcome_details['data_json'] ?? '{}', true) ?? [];

// Determine if this is a flexible structure or legacy
$is_flexible = !empty($row_config) && !empty($column_config);

if ($is_flexible) {
    // New flexible structure
    $rows = $row_config['rows'] ?? [];
    $columns = $column_config['columns'] ?? [];
} else {
    // Legacy structure - convert to flexible format using parsed data
    $outcome_metrics_data = $outcome_details['parsed_data'] ?? [];
    if (empty($outcome_metrics_data) && !empty($outcome_details['data_json'])) {
        $outcome_metrics_data = json_decode($outcome_details['data_json'], true) ?? [];
    }
    
    $metric_names = $outcome_metrics_data['columns'] ?? [];
    
    // Create default monthly rows
    $month_names = ['January', 'February', 'March', 'April', 'May', 'June', 
                    'July', 'August', 'September', 'October', 'November', 'December'];
    $rows = array_map(function($month) {
        return ['id' => $month, 'label' => $month, 'type' => 'data'];
    }, $month_names);
    
    $columns = array_map(function($col) {
        return ['id' => $col, 'label' => $col, 'type' => 'number', 'unit' => ''];
    }, $metric_names);
}

// Organize data for display
$table_data = [];
foreach ($rows as $row_def) {
    $row_data = ['row' => $row_def, 'metrics' => []];
    
    // Add data for each metric in this row
    if (isset($outcome_data[$row_def['id']])) {
        $row_data['metrics'] = $outcome_data[$row_def['id']];
    }
    
    $table_data[] = $row_data;
}

// Add CSS references (if any specific to outcome viewing)
$additionalStyles = [
    // APP_URL . '/assets/css/custom/outcome-view.css' // Example
];

// Include header
require_once ROOT_PATH . 'app/views/layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'View Outcome Details',
    'subtitle' => 'Review outcome data and metrics',
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'manage_outcomes.php',
            'text' => 'Back to Manage Outcomes',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-primary'
        ],
        [
            'url' => 'outcome_history.php?metric_id=' . $metric_id,
            'text' => 'View History',
            'icon' => 'fas fa-history',
            'class' => 'btn-outline-info'
        ],
        [
            'url' => 'edit_outcome.php?metric_id=' . $metric_id,
            'text' => 'Edit Outcome',
            'icon' => 'fas fa-edit',
            'class' => 'btn-primary'
        ]
    ]
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="card mb-4 admin-card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-bullseye me-2"></i><?= htmlspecialchars($table_name) ?>
            </h5>
        </div>        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="outcomeDetailTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="table-tab" data-bs-toggle="tab" data-bs-target="#table-view" 
                    type="button" role="tab" aria-controls="table-view" aria-selected="true">
                    <i class="fas fa-table me-1"></i> Table View
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="structure-tab" data-bs-toggle="tab" data-bs-target="#structure-view" 
                    type="button" role="tab" aria-controls="structure-view" aria-selected="false">
                    <i class="fas fa-cogs me-1"></i> Structure Info
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="chart-tab" data-bs-toggle="tab" data-bs-target="#chart-view" 
                    type="button" role="tab" aria-controls="chart-view" aria-selected="false">
                    <i class="fas fa-chart-line me-1"></i> Chart View
                </button>
            </li>
        </ul>
        
        <!-- Tab Content -->
        <div class="tab-content" id="outcomeTabsContent">
            <!-- Table View Tab -->
            <div class="tab-pane fade show active" id="table-view" role="tabpanel" aria-labelledby="table-tab">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Outcome ID:</strong> <?= $metric_id ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Created:</strong> <?= $created_at->format('F j, Y, g:i A') ?></p>
                            <p><strong>Last Updated:</strong> <?= $updated_at->format('F j, Y, g:i A') ?></p>
                            <?php 
                            // Only show submitted by if available
                            $submitted_by = $outcome_details['submitted_by_username'] ?? null;
                            if (!empty($submitted_by)): 
                            ?>
                                <p><strong>Submitted By:</strong> <?= htmlspecialchars($submitted_by) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($columns)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover data-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 150px;">Month</th>
                                    <?php foreach ($columns as $column): ?>
                                        <th>
                                            <?= htmlspecialchars($column['label']) ?>
                                            <?php if (!empty($column['unit'])): ?>
                                                <span class="text-muted small">(<?= htmlspecialchars($column['unit']) ?>)</span>
                                            <?php endif; ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($table_data as $row_data): ?>
                                    <tr>
                                        <td>
                                            <span class="month-badge"><?= htmlspecialchars($row_data['row']['label']) ?></span>
                                        </td>
                                        <?php foreach ($columns as $col_idx => $column): ?>
                                            <td class="text-end">
                                                <?= isset($row_data['metrics'][$col_idx]) && $row_data['metrics'][$col_idx] !== null && $row_data['metrics'][$col_idx] !== '' ? number_format((float)$row_data['metrics'][$col_idx], 2) : '—' ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <!-- Optional: Total Row (calculate if needed) -->
                                <tr class="table-light fw-bold">
                                    <td><span class="total-badge">TOTAL</span></td>
                                    <?php foreach ($columns as $col_idx => $column): 
                                        $total = 0;
                                        foreach ($table_data as $row_data) {
                                            if (isset($row_data['metrics'][$col_idx]) && is_numeric($row_data['metrics'][$col_idx])) {
                                                $total += (float)$row_data['metrics'][$col_idx];
                                            }
                                        }
                                    ?>
                                        <td class="text-end"><?= number_format($total, 2) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php else: ?>
                        <div class="alert alert-info">No metrics defined for this outcome structure.</div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Structure Info Tab -->
            <div class="tab-pane fade" id="structure-view" role="tabpanel" aria-labelledby="structure-tab">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-primary">
                                <i class="fas fa-list me-2"></i>Row Configuration
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Label</th>
                                            <th>Type</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($rows as $row): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row['label']) ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?= ucfirst($row['type']) ?></span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success">
                                <i class="fas fa-columns me-2"></i>Column Configuration
                            </h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Label</th>
                                            <th>Type</th>
                                            <th>Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($columns as $column): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($column['label']) ?></td>
                                            <td>
                                                <span class="badge bg-info"><?= ucfirst($column['type']) ?></span>
                                            </td>
                                            <td><?= htmlspecialchars($column['unit'] ?? '') ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart View Tab -->
            <div class="tab-pane fade" id="chart-view" role="tabpanel" aria-labelledby="chart-tab">
                <div class="card-body">
                    <!-- Chart options -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="chartTypeSelect" class="form-label">Chart Type</label>
                                <select class="form-select" id="chartTypeSelect">
                                    <option value="line">Line Chart</option>
                                    <option value="bar">Bar Chart</option>
                                    <option value="area">Area Chart</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="chartColumnSelect" class="form-label">Select Data Series</label>
                                <select class="form-select" id="chartColumnSelect" multiple>
                                    <?php foreach ($columns as $column): ?>
                                        <option value="<?= htmlspecialchars($column['id']) ?>" selected>
                                            <?= htmlspecialchars($column['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="mt-4">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox" id="cumulativeView">
                                    <label class="form-check-label" for="cumulativeView">
                                        Cumulative View
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-center justify-content-end">
                            <div class="btn-group mt-4" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="downloadChartImage" title="Download Chart">
                                    <i class="fas fa-image"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="downloadDataCSV" title="Download CSV">
                                    <i class="fas fa-download"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chart Canvas -->
                    <div class="chart-container" style="position: relative; height:400px;">
                        <canvas id="metricChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Load enhanced charting script -->
<script src="<?= APP_URL ?>/assets/js/charts/enhanced-outcomes-chart.js"></script>

<!-- Initialize the enhanced chart with classic structure data -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Prepare structure data for chart (flexible format)
        const structure = {
            rows: <?= json_encode($rows) ?>,
            columns: <?= json_encode($columns) ?>
        };
        
        // Prepare data for chart (flexible format)
        const chartData = <?= json_encode($outcome_data) ?>;
        
        // Initialize enhanced chart with flexible data
        initEnhancedOutcomesChart(
            chartData, 
            structure,
            "<?= addslashes($table_name) ?>",
            "<?= $is_flexible ? 'flexible' : 'classic' ?>"
        );
    });
</script>

<?php 
// Include footer
require_once ROOT_PATH . 'app/views/layouts/footer.php';
?>
