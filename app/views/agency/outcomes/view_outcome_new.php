<?php
/**
 * View Outcome Details 
 * 
 * Agency page to view outcome details (view-only mode)
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/agency_functions.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is an agency user
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get outcome ID from URL
$outcome_id = isset($_GET['outcome_id']) ? intval($_GET['outcome_id']) : 0;

if ($outcome_id === 0) {
    $_SESSION['error_message'] = 'Invalid outcome ID.';
    header('Location: submit_outcomes.php');
    exit;
}

$sector_id = $_SESSION['sector_id'] ?? 0;

// Get outcome data with flexible structure support
$query = "SELECT sod.*, u.username as submitted_by_username 
          FROM sector_outcomes_data sod 
          LEFT JOIN users u ON sod.submitted_by = u.user_id 
          WHERE sod.metric_id = ? AND sod.sector_id = ?";
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

// Success message handling
$success_message = '';
if (isset($_GET['saved']) && $_GET['saved'] == '1') {
    $success_message = 'Outcome updated successfully!';
}

// Get flexible structure configuration
$table_structure_type = $row['table_structure_type'] ?? 'monthly';
$row_config = json_decode($row['row_config'] ?? '{}', true);
$column_config = json_decode($row['column_config'] ?? '{}', true);

// Determine if this is a flexible structure or legacy
$is_flexible = !empty($row_config) && !empty($column_config);

if ($is_flexible) {
    // New flexible structure
    $rows = $row_config['rows'] ?? [];
    $columns = $column_config['columns'] ?? [];
} else {
    // Legacy structure - convert to flexible format
    $metric_names = $outcome_data['columns'] ?? [];
    
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
    if (isset($outcome_data['data'][$row_def['id']])) {
        $row_data['metrics'] = $outcome_data['data'][$row_def['id']];
    }
    
    $table_data[] = $row_data;
}

// Add CSS references
$additionalStyles = [
    APP_URL . '/assets/css/table-structure-designer.css',
    APP_URL . '/assets/css/custom/metric-create.css'
];

// Add JS references for view mode
$additionalScripts = [
    APP_URL . '/assets/js/outcomes/view-outcome.js',
    APP_URL . '/assets/js/outcomes/chart-manager.js'
];

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'View Outcome Details',
    'subtitle' => htmlspecialchars($table_name),
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'edit_outcome.php?outcome_id=' . $outcome_id,
            'text' => 'Edit Outcome',
            'icon' => 'fa-edit',
            'class' => 'btn-outline-primary'
        ],
        [
            'url' => 'submit_outcomes.php',
            'text' => 'Back to Outcomes',
            'icon' => 'fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<div class="container-fluid px-4">
    <!-- Success message -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Outcome Information -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">
                    <i class="fas fa-info-circle me-2"></i><?= htmlspecialchars($table_name) ?>
                </h5>
                <div>
                    <?php if ($is_draft): ?>
                        <span class="badge bg-warning">
                            <i class="fas fa-file-alt me-1"></i> Draft
                        </span>
                    <?php else: ?>
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i> Submitted
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($is_flexible): ?>
                        <span class="badge bg-primary ms-2">
                            <i class="fas fa-cogs me-1"></i> Flexible Structure
                        </span>
                    <?php endif; ?>
                </div>
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
        <div class="tab-content" id="outcomeDetailTabsContent">
            <!-- Table View Tab -->
            <div class="tab-pane fade show active" id="table-view" role="tabpanel" aria-labelledby="table-tab">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Outcome ID:</strong> <?= $outcome_id ?>
                            </div>
                            <div class="mb-3">
                                <strong>Structure Type:</strong> 
                                <span class="badge bg-secondary"><?= ucfirst($table_structure_type) ?></span>
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

                    <!-- View Mode: Read-only Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 150px;">Row</th>
                                    <?php foreach ($columns as $column): ?>
                                        <th class="text-center">
                                            <div><?= htmlspecialchars($column['label']) ?></div>
                                            <?php if (!empty($column['unit'])): ?>
                                                <small class="text-muted">(<?= htmlspecialchars($column['unit']) ?>)</small>
                                            <?php endif; ?>
                                        </th>
                                    <?php endforeach; ?>
                                    <?php if (empty($columns)): ?>
                                        <th class="text-center text-muted">No columns defined</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($table_data as $row_data): ?>
                                    <tr class="<?= $row_data['row']['type'] === 'separator' ? 'table-secondary' : '' ?>">
                                        <td>
                                            <span class="row-badge <?= $row_data['row']['type'] === 'calculated' ? 'calculated' : '' ?>">
                                                <?= htmlspecialchars($row_data['row']['label']) ?>
                                            </span>
                                        </td>
                                        <?php foreach ($columns as $column): ?>
                                            <td class="text-end">
                                                <?php if ($row_data['row']['type'] === 'separator'): ?>
                                                    —
                                                <?php else: ?>
                                                    <?php 
                                                    $value = $row_data['metrics'][$column['id']] ?? 0;
                                                    if ($column['type'] === 'currency') {
                                                        echo 'RM ' . number_format($value, 2);
                                                    } elseif ($column['type'] === 'percentage') {
                                                        echo number_format($value, 1) . '%';
                                                    } else {
                                                        echo number_format($value, 2);
                                                    }
                                                    ?>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                        <?php if (empty($columns)): ?>
                                            <td class="text-center text-muted">No data available</td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <!-- Total Row for numeric columns -->
                                <?php if (!empty($columns) && array_filter($columns, function($col) { return in_array($col['type'], ['number', 'currency']); })): ?>
                                <tr class="table-light">
                                    <td class="fw-bold">TOTAL</td>
                                    <?php foreach ($columns as $column): ?>
                                        <td class="fw-bold text-end">
                                            <?php if (in_array($column['type'], ['number', 'currency'])): ?>
                                                <?php
                                                $total = 0;
                                                foreach ($table_data as $row_data) {
                                                    if ($row_data['row']['type'] === 'data') {
                                                        $total += $row_data['metrics'][$column['id']] ?? 0;
                                                    }
                                                }
                                                if ($column['type'] === 'currency') {
                                                    echo 'RM ' . number_format($total, 2);
                                                } else {
                                                    echo number_format($total, 2);
                                                }
                                                ?>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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
                                <label for="chartType" class="form-label">Chart Type</label>
                                <select class="form-select" id="chartType">
                                    <option value="line">Line Chart</option>
                                    <option value="bar">Bar Chart</option>
                                    <option value="area">Area Chart</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="chartColumns" class="form-label">Select Data Series</label>
                                <select class="form-select" id="chartColumns" multiple>
                                    <?php foreach ($columns as $column): ?>
                                        <option value="<?= htmlspecialchars($column['id']) ?>" selected>
                                            <?= htmlspecialchars($column['label']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 d-flex align-items-center">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="showTotals" checked>
                                <label class="form-check-label" for="showTotals">
                                    Include Totals
                                </label>
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-center justify-content-end">
                            <div class="btn-group mt-4" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="downloadChartImage" title="Download Chart">
                                    <i class="fas fa-image"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary btn-sm" id="downloadDataCSV" title="Download CSV">
                                    <i class="fas fa-file-csv"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chart Canvas -->
                    <div class="chart-container" style="position: relative; height:400px;">
                        <canvas id="metricChart"></canvas>
                    </div>
                    
                    <!-- Chart Info -->
                    <div class="mt-3">
                        <div class="alert alert-light">
                            <h6><i class="fas fa-info-circle me-2"></i>Chart Information</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <small><strong>Structure Type:</strong> <?= ucfirst($table_structure_type) ?></small><br>
                                    <small><strong>Rows:</strong> <?= count($rows) ?> categories</small>
                                </div>
                                <div class="col-md-6">
                                    <small><strong>Columns:</strong> <?= count($columns) ?> data series</small><br>
                                    <small><strong>Total Data Points:</strong> <?= count($rows) * count($columns) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer with Actions -->
        <div class="card-footer text-muted">
            <div class="d-flex justify-content-between align-items-center">
                <small>
                    <i class="fas fa-info-circle me-1"></i> This outcome supports flexible table structures beyond monthly data
                </small>
                <div>
                    <?php if ($is_flexible): ?>
                        <a href="create_outcome_flexible.php" class="btn btn-primary btn-sm me-2">
                            <i class="fas fa-plus me-1"></i> Create New Flexible
                        </a>
                    <?php endif; ?>
                    <a href="create_outcome.php" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-1"></i> Create New Classic
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pass data to JavaScript -->
<script>
// Prepare data for chart
const outcomeData = <?= json_encode($outcome_data) ?>;
const rowsConfig = <?= json_encode($rows) ?>;
const columnsConfig = <?= json_encode($columns) ?>;
const tableData = <?= json_encode($table_data) ?>;

// Initialize chart when chart tab is activated
document.addEventListener('DOMContentLoaded', function() {
    const chartTab = document.getElementById('chart-tab');
    if (chartTab) {
        chartTab.addEventListener('shown.bs.tab', function() {
            if (typeof prepareChartData === 'function' && typeof initializeOutcomeChart === 'function') {
                const chartData = prepareChartData(outcomeData.data || {}, columnsConfig, rowsConfig);
                initializeOutcomeChart(chartData);
            }
        });
    }
    
    // Setup download buttons
    const downloadChartBtn = document.getElementById('downloadChartImage');
    if (downloadChartBtn) {
        downloadChartBtn.addEventListener('click', function() {
            if (typeof downloadChart === 'function') {
                downloadChart();
            }
        });
    }
    
    const downloadCSVBtn = document.getElementById('downloadDataCSV');
    if (downloadCSVBtn) {
        downloadCSVBtn.addEventListener('click', function() {
            // CSV download functionality
            downloadDataAsCSV();
        });
    }
});

// CSV download function
function downloadDataAsCSV() {
    const csv = [];
    const headers = ['Row', ...columnsConfig.map(col => col.label)];
    csv.push(headers.join(','));
    
    tableData.forEach(rowData => {
        const row = [rowData.row.label];
        columnsConfig.forEach(col => {
            const value = rowData.metrics[col.id] || 0;
            row.push(value);
        });
        csv.push(row.join(','));
    });
    
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'outcome-data.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
