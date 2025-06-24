<?php
/**
 * View Submitted Outcome Details
 * 
 * Allows agency users to view the details of submitted outcomes
 */

// Define project root path for consistent file references
if (!defined('PROJECT_ROOT_PATH')) {
    define('PROJECT_ROOT_PATH', rtrim(dirname(dirname(dirname(__DIR__))), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR);
}

// Fix require_once paths for config and libraries
require_once dirname(__DIR__, 3) . '/config/config.php';
require_once dirname(__DIR__, 3) . '/lib/db_connect.php';
require_once dirname(__DIR__, 3) . '/lib/session.php';
require_once dirname(__DIR__, 3) . '/lib/functions.php';
require_once dirname(__DIR__, 3) . '/lib/agencies/index.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

$sector_id = $_SESSION['sector_id'] ?? 0;

// Check if outcome_id is provided
if (!isset($_GET['outcome_id']) || !is_numeric($_GET['outcome_id'])) {
    $_SESSION['error_message'] = 'Invalid outcome ID.';
    header('Location: submit_outcomes.php');
    exit;
}

$outcome_id = (int) $_GET['outcome_id'];

// Get outcome data using JSON-based storage
$query = "SELECT data_json, table_name, created_at, updated_at, is_draft FROM sector_outcomes_data 
          WHERE metric_id = ? AND sector_id = ? LIMIT 1";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $outcome_id, $sector_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error_message'] = 'Outcome not found or you do not have permission to view it.';
    header('Location: submit_outcomes.php');
    exit;
}

$row = $result->fetch_assoc();
$table_name = $row['table_name'];
$created_at = new DateTime($row['created_at']);
$updated_at = new DateTime($row['updated_at']);
$outcome_data = json_decode($row['data_json'], true);
$is_draft = (bool)$row['is_draft'];

// Get column names
$metric_names = $outcome_data['columns'] ?? [];

// Organize data for display
$month_names = ['January', 'February', 'March', 'April', 'May', 'June', 
                'July', 'August', 'September', 'October', 'November', 'December'];
$table_data = [];

foreach ($month_names as $month_name) {
    $month_data = ['month_name' => $month_name, 'metrics' => []];
    
    // Add data for each metric in this month
    if (isset($outcome_data['data'][$month_name])) {
        $month_data['metrics'] = $outcome_data['data'][$month_name];
    }
    
    $table_data[] = $month_data;
}

// Add CSS references
$additionalStyles = [
    APP_URL . '/assets/css/custom/metric-create.css'
];

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'View Outcome Details',
    'subtitle' => 'Review your submitted outcomes data',
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'submit_outcomes.php',
            'text' => 'Back to Outcomes',
            'icon' => 'fa-arrow-left',
            'class' => 'btn-outline-primary'
        ]
    ]
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<div class="container-fluid px-4 py-4">
    <div class="card mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-chart-bar me-2"></i><?= htmlspecialchars($table_name) ?>
            </h5>
            <div>
                <?php if ($is_draft): ?>
                <span class="badge bg-warning">
                    <i class="fas fa-pencil-alt me-1"></i> Draft
                </span>
                <?php else: ?>
                <span class="badge bg-success">
                    <i class="fas fa-check-circle me-1"></i> Submitted
                </span>
                <?php endif; ?>
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
                                <strong>Outcomes ID:</strong> <?= $outcome_id ?>
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
                                        <th><?= htmlspecialchars($name) ?></th>
                                    <?php endforeach; ?>
                                    <?php if (empty($metric_names)): ?>
                                        <th class="text-center text-muted">No outcomes defined</th>
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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="chartType" class="form-label">Chart Type</label>
                                <select class="form-select" id="chartType">
                                    <option value="line">Line Chart</option>
                                    <option value="bar">Bar Chart</option>
                                    <option value="radar">Radar Chart</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="metricToChart" class="form-label">Outcomes to Display</label>
                                <select class="form-select" id="metricToChart" multiple>
                                    <?php foreach ($metric_names as $name): ?>
                                        <option value="<?= htmlspecialchars($name) ?>" selected>
                                            <?= htmlspecialchars($name) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted">Hold Ctrl/Cmd to select multiple outcomes</small>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="cumulativeToggle">
                                <label class="form-check-label" for="cumulativeToggle">
                                    Cumulative Display
                                </label>
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
                    <i class="fas fa-info-circle me-1"></i> This is a view-only page. To make changes, you need to create a new draft.
                </small>
                <a href="<?php echo APP_URL; ?>/app/views/agency/edit_outcomes.php?outcome_id=<?= $outcome_id ?>" class="btn btn-primary btn-sm">
                    <i class="fas fa-pencil-alt me-1"></i> Create New Draft
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
            <?= json_encode($outcome_data) ?>, 
            <?= json_encode($table_data) ?>, 
            <?= json_encode($month_names) ?>,
            "<?= addslashes($table_name) ?>"
        );
    });
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>


