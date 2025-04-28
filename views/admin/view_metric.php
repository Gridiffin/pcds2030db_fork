<?php
/**
 * View Submitted Metric Details for Admin
 * 
 * Allows admin users to view the details of submitted metrics from any sector
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

// Check if metric_id is provided
if (!isset($_GET['metric_id']) || !is_numeric($_GET['metric_id'])) {
    $_SESSION['error_message'] = 'Invalid metric ID.';
    header('Location: manage_metrics.php');
    exit;
}

$metric_id = (int) $_GET['metric_id'];

// Get metric data using JSON-based storage
$query = "SELECT smd.*, s.sector_name 
          FROM sector_metrics_data smd
          LEFT JOIN sectors s ON smd.sector_id = s.sector_id
          WHERE smd.metric_id = ? AND smd.is_draft = 0 LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $metric_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = 'Metric not found.';
    header('Location: manage_metrics.php');
    exit;
}

$row = $result->fetch_assoc();
$table_name = $row['table_name'];
$sector_id = $row['sector_id'];
$sector_name = $row['sector_name'] ?? 'Unknown Sector';
$created_at = new DateTime($row['created_at']);
$updated_at = new DateTime($row['updated_at']);
$metrics_data = json_decode($row['data_json'], true);

// Get column names
$metric_names = $metrics_data['columns'] ?? [];

// Organize data for display
$month_names = ['January', 'February', 'March', 'April', 'May', 'June', 
                'July', 'August', 'September', 'October', 'November', 'December'];
$table_data = [];

foreach ($month_names as $month_name) {
    $month_data = ['month_name' => $month_name, 'metrics' => []];
    
    // Add data for each metric in this month
    if (isset($metrics_data['data'][$month_name])) {
        $month_data['metrics'] = $metrics_data['data'][$month_name];
    }
    
    $table_data[] = $month_data;
}

// Add CSS references
$additionalStyles = [
    APP_URL . '/assets/css/custom/metric-create.css'
];

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';

// Set up the page header variables for dashboard_header.php
$title = "View Metric Details";
$subtitle = "Review metric data for " . htmlspecialchars($sector_name) . " sector";
$headerStyle = 'light'; // Use light (white) style for inner pages
$actions = [
    [
        'url' => 'manage_metrics.php',
        'text' => 'Back to Metrics',
        'icon' => 'fa-arrow-left',
        'class' => 'btn-outline-primary'
    ]
];

// Include the dashboard header component
require_once '../../includes/dashboard_header.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-chart-bar me-2"></i><?= htmlspecialchars($table_name) ?>
            </h5>
            <div class="d-flex align-items-center">
                <span class="badge bg-secondary me-2">
                    <i class="fas fa-sitemap me-1"></i><?= htmlspecialchars($sector_name) ?>
                </span>
                <span class="badge bg-success">
                    <i class="fas fa-check-circle me-1"></i>Submitted
                </span>
            </div>
        </div>
        
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="metricTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="table-tab" data-bs-toggle="tab" data-bs-target="#table-view" 
                    type="button" role="tab" aria-controls="table-view" aria-selected="true">
                    <i class="fas fa-table me-1"></i> Table View
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
        <div class="tab-content" id="metricTabsContent">
            <!-- Table View Tab -->
            <div class="tab-pane fade show active" id="table-view" role="tabpanel" aria-labelledby="table-tab">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Metric ID:</strong> <?= $metric_id ?>
                            </div>
                            <div class="mb-3">
                                <strong>Sector:</strong> <?= htmlspecialchars($sector_name) ?>
                            </div>
                            <div class="mb-3">
                                <strong>Submitted:</strong> <?= $created_at->format('F j, Y g:i A') ?>
                            </div>
                            <?php if ($created_at->format('Y-m-d H:i:s') !== $updated_at->format('Y-m-d H:i:s')): ?>
                            <div class="mb-3">
                                <strong>Last Updated:</strong> <?= $updated_at->format('F j, Y g:i A') ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 150px;">Month</th>
                                    <?php foreach ($metric_names as $name): ?>
                                        <th>
                                            <?= htmlspecialchars($name) ?>
                                            <?php if (isset($metrics_data['units'][$name])): ?>
                                                <span class="text-muted small">
                                                    (<?= htmlspecialchars($metrics_data['units'][$name]) ?>)
                                                </span>
                                            <?php endif; ?>
                                        </th>
                                    <?php endforeach; ?>
                                    <?php if (empty($metric_names)): ?>
                                        <th class="text-center text-muted">No metrics defined</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($table_data as $month_data): ?>
                                    <tr>
                                        <td>
                                            <span class="month-badge"><?= $month_data['month_name'] ?></span>
                                        </td>
                                        <?php foreach ($metric_names as $name): ?>
                                            <td class="text-end">
                                                <?= isset($month_data['metrics'][$name]) ? number_format($month_data['metrics'][$name], 2) : '—' ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <?php if (empty($metric_names)): ?>
                                            <td></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <!-- Total Row -->
                                <?php if (!empty($metric_names)): ?>
                                <tr class="table-light">
                                    <td class="fw-bold">
                                        <span class="total-badge">TOTAL</span>
                                    </td>
                                    <?php foreach ($metric_names as $name): ?>
                                        <td class="fw-bold text-end">
                                            <?php
                                                $total = 0;
                                                foreach ($table_data as $month_data) {
                                                    if (isset($month_data['metrics'][$name])) {
                                                        $total += floatval($month_data['metrics'][$name]);
                                                    }
                                                }
                                                echo number_format($total, 2);
                                            ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Chart View Tab -->
            <div class="tab-pane fade" id="chart-view" role="tabpanel" aria-labelledby="chart-tab">
                <div class="card-body">
                    <!-- Chart options -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="chartType" class="form-label">Chart Type</label>
                                <select class="form-select" id="chartType">
                                    <option value="line">Line Chart</option>
                                    <option value="bar">Bar Chart</option>
                                    <option value="radar">Radar Chart</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="metricToChart" class="form-label">Metrics to Display</label>
                                <select class="form-select" id="metricToChart" multiple>
                                    <?php foreach ($metric_names as $name): ?>
                                        <option value="<?= htmlspecialchars($name) ?>" selected>
                                            <?= htmlspecialchars($name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple metrics</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chart Canvas -->
                    <div class="chart-container" style="position: relative; height:400px;">
                        <canvas id="metricChart"></canvas>
                    </div>
                    
                    <!-- Download Options -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-end">
                            <button id="downloadChartImage" class="btn btn-outline-primary me-2">
                                <i class="fas fa-image me-1"></i> Download Chart
                            </button>
                            <button id="downloadDataCSV" class="btn btn-outline-success">
                                <i class="fas fa-file-csv me-1"></i> Download Data as CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="card-footer text-muted">
            <div class="d-flex justify-content-between align-items-center">
                <small>
                    <i class="fas fa-info-circle me-1"></i> You are viewing this metric as an administrator
                </small>
                <a href="edit_metric.php?metric_id=<?= $metric_id ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-edit me-1"></i> Edit Metric
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Load required scripts -->
<script src="<?= APP_URL ?>/assets/js/charts/metrics-chart.js"></script>

<!-- Initialize the metrics chart with PHP data -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize chart with data from PHP
        initMetricsChart(
            <?= json_encode($metrics_data) ?>, 
            <?= json_encode($table_data) ?>, 
            <?= json_encode($month_names) ?>,
            "<?= addslashes($table_name) ?>"
        );
    });
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>