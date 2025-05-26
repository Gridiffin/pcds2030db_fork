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
$sector_id = $outcome_details['sector_id'];
$sector_name = $outcome_details['sector_name'] ?? 'Unknown Sector';
$period_id = $outcome_details['period_id'];
$year = $outcome_details['year'] ?? 'N/A';
$quarter = $outcome_details['quarter'] ?? 'N/A';
$reporting_period_name = "Q{$quarter} {$year}"; // Construct proper period name
$status = $outcome_details['status'] ?? 'submitted'; // Default to submitted if not present
$overall_rating = $outcome_details['overall_rating'] ?? null;

$created_at = new DateTime($outcome_details['created_at']);
$updated_at = new DateTime($outcome_details['updated_at']);

// Use parsed data if available, otherwise parse manually
$outcome_metrics_data = $outcome_details['parsed_data'] ?? [];
if (empty($outcome_metrics_data) && !empty($outcome_details['data_json'])) {
    $outcome_metrics_data = json_decode($outcome_details['data_json'], true) ?? [];
}

// Get column names (metric names within the outcome)
$metric_names = $outcome_metrics_data['columns'] ?? [];
$metric_units = $outcome_metrics_data['units'] ?? [];

// Organize data for display
$month_names = ['January', 'February', 'March', 'April', 'May', 'June', 
                'July', 'August', 'September', 'October', 'November', 'December'];
$table_data = [];

foreach ($month_names as $month_name) {
    $month_data_row = ['month_name' => $month_name, 'metrics' => []];
    if (isset($outcome_metrics_data['data'][$month_name])) {
        $month_data_row['metrics'] = $outcome_metrics_data['data'][$month_name];
    }
    $table_data[] = $month_data_row;
}

// Add CSS references (if any specific to outcome viewing)
$additionalStyles = [
    // APP_URL . '/assets/css/custom/outcome-view.css' // Example
];

// Include header
require_once ROOT_PATH . 'app/views/layouts/header.php';
// Include admin navigation
require_once ROOT_PATH . 'app/views/layouts/admin_nav.php';

// Set up the page header variables for dashboard_header.php
$title = "View Outcome Details";
$subtitle = "Review outcome data for " . htmlspecialchars($sector_name) . " sector for " . htmlspecialchars($reporting_period_name);
$headerStyle = 'light'; 
$actions = [
    [
        'url' => 'manage_outcomes.php',
        'text' => 'Back to Manage Outcomes',
        'icon' => 'fa-arrow-left',
        'class' => 'btn-outline-primary'
    ],    [
        'url' => 'edit_outcome.php?metric_id=' . $metric_id, // Use consistent parameter naming
        'text' => 'Edit Outcome',
        'icon' => 'fa-edit',
        'class' => 'btn-primary'
    ]
];

// Include the dashboard header component
require_once ROOT_PATH . 'app/lib/dashboard_header.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="card mb-4 admin-card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-bullseye me-2"></i><?= htmlspecialchars($table_name) ?>
            </h5>
            <div class="d-flex align-items-center">
                <span class="badge bg-info me-2">
                    <i class="fas fa-calendar-alt me-1"></i><?= htmlspecialchars($reporting_period_name) ?>
                </span>
                <span class="badge bg-secondary me-2">
                    <i class="fas fa-sitemap me-1"></i><?= htmlspecialchars($sector_name) ?>
                </span>
                <?php echo get_status_display_name($status); // Replaced display_submission_status_badge ?>
                <?php if ($overall_rating !== null) { echo get_rating_badge($overall_rating); } // Replaced display_overall_rating_badge ?>
            </div>
        </div>

        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="outcomeDetailTabs" role="tablist">
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
             <li class="nav-item" role="presentation">
                <button class="nav-link" id="raw-data-tab" data-bs-toggle="tab" data-bs-target="#raw-data-view"
                    type="button" role="tab" aria-controls="raw-data-view" aria-selected="false">
                    <i class="fas fa-code me-1"></i> Raw JSON Data
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
                            <p><strong>Sector:</strong> <?= htmlspecialchars($sector_name) ?></p>
                            <p><strong>Reporting Period:</strong> <?= htmlspecialchars($reporting_period_name) ?></p>
                        </div>
                        <div class="col-md-6">                            <p><strong>Created:</strong> <?= $created_at->format('F j, Y, g:i A') ?></p>
                            <p><strong>Last Updated:</strong> <?= $updated_at->format('F j, Y, g:i A') ?></p>                            <?php 
                            // Only show submitted by if available
                            $submitted_by = $outcome_details['submitted_by_username'] ?? null;
                            if (!empty($submitted_by)): 
                            ?>
                                <p><strong>Submitted By:</strong> <?= htmlspecialchars($submitted_by) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!empty($metric_names)): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover data-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 150px;">Month</th>
                                    <?php foreach ($metric_names as $name): ?>
                                        <th>
                                            <?= htmlspecialchars($name) ?>
                                            <?php if (isset($metric_units[$name]) && !empty($metric_units[$name])): ?>
                                                <span class="text-muted small">(<?= htmlspecialchars($metric_units[$name]) ?>)</span>
                                            <?php endif; ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($table_data as $month_data_row): ?>
                                    <tr>
                                        <td>
                                            <span class="month-badge"><?= $month_data_row['month_name'] ?></span>
                                        </td>
                                        <?php foreach ($metric_names as $name): ?>
                                            <td class="text-end">
                                                <?= isset($month_data_row['metrics'][$name]) && $month_data_row['metrics'][$name] !== '' ? number_format((float)$month_data_row['metrics'][$name], 2) : '—' ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <!-- Optional: Total Row (calculate if needed) -->
                                <tr class="table-light fw-bold">
                                    <td><span class="total-badge">TOTAL</span></td>
                                    <?php foreach ($metric_names as $name): 
                                        $total = 0;
                                        foreach ($table_data as $month_data_row) {
                                            if (isset($month_data_row['metrics'][$name]) && is_numeric($month_data_row['metrics'][$name])) {
                                                $total += (float)$month_data_row['metrics'][$name];
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

            <!-- Chart View Tab -->
            <div class="tab-pane fade" id="chart-view" role="tabpanel" aria-labelledby="chart-tab">
                <div class="card-body">
                    <!-- Chart options -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="chartTypeSelect" class="form-label">Chart Type</label>
                                <select class="form-select" id="chartTypeSelect">
                                    <option value="line">Line Chart</option>
                                    <option value="bar">Bar Chart</option>
                                    <option value="radar">Radar Chart</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="outcomeSelect" class="form-label">Outcomes to Display</label>
                                <select class="form-select" id="outcomeSelect">
                                    <option value="all">All Outcomes</option>
                                    <?php foreach ($metric_names as $name): ?>
                                        <option value="<?= htmlspecialchars($name) ?>">
                                            <?= htmlspecialchars($name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chart Canvas -->
                    <div class="chart-container" style="position: relative; height:400px;">
                        <canvas id="outcomesChart"></canvas>
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
            
            <!-- Raw Data View Tab -->
            <div class="tab-pane fade" id="raw-data-view" role="tabpanel" aria-labelledby="raw-data-tab">
                <div class="card-body">
                    <h5>Raw JSON Data:</h5>
                    <pre class="bg-light p-3 rounded"><code><?= htmlspecialchars(json_encode($outcome_metrics_data, JSON_PRETTY_PRINT)) ?></code></pre>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// Include JS specific to this page (e.g., for charts if implemented)
$additionalScripts = [
    // Chart.js is already included in footer.php via CDN
    APP_URL . '/assets/js/charts/outcomes-chart.js'
];

// Add inline JavaScript to initialize chart with the data from PHP
$inlineScripts = "
// Parse PHP data for use in chart
const outcomeData = " . json_encode($outcome_metrics_data) . ";
const tableData = " . json_encode($table_data) . ";
const monthNames = " . json_encode(array_column($table_data, 'month_name')) . ";
const tableName = " . json_encode($table_name) . ";

// Wait for document to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize chart when the chart tab is shown
    document.getElementById('chart-tab').addEventListener('shown.bs.tab', function (e) {
        if (typeof initOutcomesChart === 'function') {
            initOutcomesChart(outcomeData, tableData, monthNames, tableName);
        }
    });
    
    // Set up download buttons
    if (document.getElementById('downloadChartImage')) {
        document.getElementById('downloadChartImage').addEventListener('click', function() {
            if (window.outcomesChart) {
                const link = document.createElement('a');
                link.download = 'outcome-chart-" . $metric_id . ".png';
                link.href = document.getElementById('outcomesChart').toDataURL('image/png');
                link.click();
            }
        });
    }
    
    if (document.getElementById('downloadDataCSV')) {
        document.getElementById('downloadDataCSV').addEventListener('click', function() {
            // Create CSV content from the data
            let csvContent = 'data:text/csv;charset=utf-8,Month';
            const metrics = " . json_encode($metric_names) . ";
            
            // Add headers
            metrics.forEach(metric => {
                csvContent += ',' + metric;
            });
            csvContent += '\\n';
            
            // Add data rows
            tableData.forEach(row => {
                csvContent += row.month_name;
                metrics.forEach(metric => {
                    const value = row.metrics && row.metrics[metric] !== undefined ? row.metrics[metric] : '';
                    csvContent += ',' + value;
                });
                csvContent += '\\n';
            });
            
            // Create download link
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', 'outcome-data-" . $metric_id . ".csv');
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    }
});
";

// Include footer
require_once ROOT_PATH . 'app/views/layouts/footer.php';
?>
