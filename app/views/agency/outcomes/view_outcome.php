<?php
/**
 * View Outcome Details 
 * 
 * Agency page to view outcome details (view-only mode)
 * Supports flexible table structures (dynamic rows and columns)
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

// Get outcome data
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

// Parse the data structure (compatible with edit_outcome.php format)
$data_array = $outcome_data ?? ['columns' => [], 'data' => []];

// Ensure we have the correct structure
if (!isset($data_array['columns']) || !isset($data_array['data'])) {
    $data_array = ['columns' => [], 'data' => []];
}

$columns = $data_array['columns'] ?? [];
$data = $data_array['data'] ?? [];

// Get row labels from the data
$row_labels = [];
if (!empty($data) && is_array($data)) {
    $row_labels = array_keys($data);
}

// If no data exists, show empty state
$has_data = !empty($columns) && !empty($row_labels);

// Add CSS references
$additionalStyles = [
    APP_URL . '/assets/css/custom/metric-create.css'
];

// Add JS references for view mode
$additionalScripts = [
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js'
];

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'View Outcome Details',
    'subtitle' => htmlspecialchars($table_name) . ($is_draft ? ' (Draft)' : ' (Submitted)'),
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'edit_outcome.php?outcome_id=' . $outcome_id,
            'text' => 'Edit Outcome',
            'icon' => 'fas fa-edit',
            'class' => 'btn-outline-primary'
        ],
        [
            'url' => 'submit_outcomes.php',
            'text' => 'Back to Outcomes',
            'icon' => 'fas fa-arrow-left',
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
                    <i class="fas fa-table me-2"></i><?= htmlspecialchars($table_name) ?>
                </h5>
                <div>
                    <span class="badge bg-success">
                        <i class="fas fa-check-circle me-1"></i> Flexible Structure
                    </span>
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
                                <span class="badge bg-primary">Flexible Table</span>
                            </div>
                            <div class="mb-3">
                                <strong>Created:</strong> <?= $created_at->format('F j, Y g:i A') ?>
                            </div>
                            <?php if ($created_at->format('Y-m-d H:i:s') !== $updated_at->format('Y-m-d H:i:s')): ?>
                            <div class="mb-3">
                                <strong>Last Updated:</strong> <?= $updated_at->format('F j, Y g:i A') ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Columns:</strong> <?= count($columns) ?>
                            </div>
                            <div class="mb-3">
                                <strong>Rows:</strong> <?= count($row_labels) ?>
                            </div>
                            <div class="mb-3">
                                <strong>Data Points:</strong> <?= count($columns) * count($row_labels) ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($has_data): ?>
                    <!-- View Mode: Read-only Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 150px;">Row</th>
                                    <?php foreach ($columns as $column): ?>
                                        <th class="text-center">
                                            <?= htmlspecialchars($column) ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($row_labels as $row_label): ?>
                                    <tr>
                                        <td>
                                            <span class="row-badge">
                                                <?= htmlspecialchars($row_label) ?>
                                            </span>
                                        </td>
                                        <?php foreach ($columns as $column): ?>
                                            <td class="text-end">
                                                <?php 
                                                $value = $data[$row_label][$column] ?? 0;
                                                // Handle empty strings and non-numeric values safely
                                                if (is_numeric($value) && $value !== '') {
                                                    echo number_format((float)$value, 2);
                                                } else {
                                                    echo '0.00';
                                                }
                                                ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                                
                                <!-- Total Row -->
                                <?php if (!empty($columns)): ?>
                                <tr class="table-light">
                                    <td class="fw-bold">TOTAL</td>
                                    <?php foreach ($columns as $column): ?>
                                        <td class="fw-bold text-end">
                                            <?php
                                            $total = 0;
                                            foreach ($row_labels as $row_label) {
                                                $cell_value = $data[$row_label][$column] ?? 0;
                                                // Only add numeric values to total
                                                if (is_numeric($cell_value) && $cell_value !== '') {
                                                    $total += (float)$cell_value;
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
                    <?php else: ?>
                    <!-- Empty State -->
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-table fa-3x text-muted"></i>
                        </div>
                        <h5 class="text-muted">No Data Available</h5>
                        <p class="text-muted">This outcome doesn't have any data yet.</p>
                        <a href="edit_outcome.php?outcome_id=<?= $outcome_id ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Add Data
                        </a>
                    </div>
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
                            <?php if (!empty($row_labels)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Label</th>
                                            <th>Index</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($row_labels as $index => $row_label): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($row_label) ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?= $index + 1 ?></span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-muted">No rows defined yet.</p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-success">
                                <i class="fas fa-columns me-2"></i>Column Configuration
                            </h6>
                            <?php if (!empty($columns)): ?>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Label</th>
                                            <th>Index</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($columns as $index => $column): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($column) ?></td>
                                            <td>
                                                <span class="badge bg-info"><?= $index + 1 ?></span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php else: ?>
                            <p class="text-muted">No columns defined yet.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Raw Data Preview -->
                    <hr class="my-4">
                    <h6 class="text-warning">
                        <i class="fas fa-code me-2"></i>Raw Data Structure
                    </h6>
                    <pre class="bg-light p-3 rounded" style="max-height: 200px; overflow-y: auto;">
                        <code><?= htmlspecialchars(json_encode($data_array, JSON_PRETTY_PRINT)) ?></code>
                    </pre>
                </div>
            </div>
            
            <!-- Chart View Tab -->
            <div class="tab-pane fade" id="chart-view" role="tabpanel" aria-labelledby="chart-tab">
                <div class="card-body">
                    <?php if ($has_data): ?>
                    <!-- Chart options -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="chartType" class="form-label">Chart Type</label>
                                <select class="form-select" id="chartType">
                                    <option value="bar" selected>Bar Chart</option>
                                    <option value="line">Line Chart</option>
                                    <option value="area">Area Chart</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="chartColumns" class="form-label">Select Data Series</label>
                                <select class="form-select" id="chartColumns" multiple>
                                    <?php foreach ($columns as $column): ?>
                                        <option value="<?= htmlspecialchars($column) ?>" selected>
                                            <?= htmlspecialchars($column) ?>
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
                                    <small><strong>Structure Type:</strong> Flexible Table</small><br>
                                    <small><strong>Rows:</strong> <?= count($row_labels) ?> categories</small>
                                </div>
                                <div class="col-md-6">
                                    <small><strong>Columns:</strong> <?= count($columns) ?> data series</small><br>
                                    <small><strong>Total Data Points:</strong> <?= count($row_labels) * count($columns) ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Empty Chart State -->
                    <div class="text-center py-5">
                        <div class="mb-3">
                            <i class="fas fa-chart-line fa-3x text-muted"></i>
                        </div>
                        <h5 class="text-muted">No Data to Chart</h5>
                        <p class="text-muted">Add some data to this outcome to see charts.</p>
                        <a href="edit_outcome.php?outcome_id=<?= $outcome_id ?>" class="btn btn-primary">
                            <i class="fas fa-edit me-1"></i> Add Data
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Footer with Actions -->
        <div class="card-footer text-muted">
            <div class="d-flex justify-content-between align-items-center">
                <small>
                    <i class="fas fa-info-circle me-1"></i> This outcome supports flexible table structures with custom rows and columns
                </small>
                <div>
                    <a href="create_outcome_flexible.php" class="btn btn-success btn-sm">
                        <i class="fas fa-plus me-1"></i> Create New Outcome
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pass data to JavaScript -->
<script>
// Prepare data for chart in a simple, compatible format
window.tableData = <?= json_encode($data) ?>;
window.tableColumns = <?= json_encode($columns) ?>;
window.tableRows = <?= json_encode($row_labels) ?>;

// Additional data for context
const outcomeInfo = {
    id: <?= $outcome_id ?>,
    tableName: <?= json_encode($table_name) ?>,
    isDraft: <?= json_encode($is_draft) ?>,
    hasData: <?= json_encode($has_data) ?>
};

// Initialize table data and chart functionality

// Initialize chart functionality if Chart.js is available
function initializeChart() {
    if (typeof Chart !== 'undefined' && window.tableData && window.tableColumns && window.tableRows) {
        // Simple chart initialization
        const ctx = document.getElementById('metricChart');
        
        if (ctx && Object.keys(window.tableData).length > 0) {
            // Destroy existing chart if present
            const existingChart = Chart.getChart(ctx);
            if (existingChart) {
                existingChart.destroy();
            }
            
            const chartType = document.getElementById('chartType')?.value || 'bar';
            const cumulativeView = document.getElementById('cumulativeView')?.checked || false;
            
            // Create chart data - ensure all values are numeric
            const labels = window.tableRows;
            const datasets = window.tableColumns.map((column, index) => {
                let data = labels.map(row => {
                    const cellValue = window.tableData[row] ? window.tableData[row][column] : null;
                    // Convert to number, handle empty strings and null values
                    let numericValue = 0;
                    if (cellValue !== null && cellValue !== '' && cellValue !== undefined) {
                        numericValue = parseFloat(cellValue) || 0;
                    }
                    return numericValue;
                });
                
                // Apply cumulative calculation if enabled
                if (cumulativeView) {
                    const cumulativeData = [];
                    let runningTotal = 0;
                    for (let i = 0; i < data.length; i++) {
                        runningTotal += data[i];
                        cumulativeData.push(runningTotal);
                    }
                    data = cumulativeData;
                }
                
                const colors = [
                    'rgba(54, 162, 235, 0.8)',
                    'rgba(255, 99, 132, 0.8)',
                    'rgba(75, 192, 192, 0.8)',
                    'rgba(255, 205, 86, 0.8)',
                    'rgba(153, 102, 255, 0.8)',
                    'rgba(255, 159, 64, 0.8)'
                ];
                
                return {
                    label: column + (cumulativeView ? ' (Cumulative)' : ''),
                    data: data,
                    backgroundColor: colors[index % colors.length],
                    borderColor: colors[index % colors.length].replace('0.8', '1'),
                    borderWidth: 2,
                    fill: chartType === 'area'
                };
            });
            
            // Create chart with improved configuration for financial data
            const chartInstance = new Chart(ctx, {
                type: chartType === 'area' ? 'line' : chartType,
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                // Format large numbers (e.g., financial data)
                                callback: function(value, index, values) {
                                    if (value >= 1000000000) {
                                        return 'RM ' + (value / 1000000000).toFixed(1) + 'B';
                                    } else if (value >= 1000000) {
                                        return 'RM ' + (value / 1000000).toFixed(1) + 'M';
                                    } else if (value >= 1000) {
                                        return 'RM ' + (value / 1000).toFixed(1) + 'K';
                                    } else {
                                        return 'RM ' + value.toFixed(2);
                                    }
                                }
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Outcome Data Visualization' + (cumulativeView ? ' (Cumulative View)' : '')
                        },
                        legend: {
                            display: true,
                            position: 'top'
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.parsed.y;
                                    const formattedValue = new Intl.NumberFormat('en-MY', {
                                        style: 'currency',
                                        currency: 'MYR',
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    }).format(value);
                                    return context.dataset.label + ': ' + formattedValue;
                                }
                            }
                        }
                    }
                }
            });
            
            // Store chart instance globally for download functionality
            window.currentChart = chartInstance;
        }
    }
}

// Wait for Chart.js to load and initialize
function waitForChart() {
    if (typeof Chart !== 'undefined') {
        // Chart.js loaded, initialize chart
        initializeChart();
    } else {
        // Waiting for Chart.js to load
        setTimeout(waitForChart, 100);
    }
}

// Initialize everything when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Start chart initialization
    waitForChart();
    
    // Handle chart tab activation
    const chartTab = document.getElementById('chart-tab');
    if (chartTab) {
        chartTab.addEventListener('shown.bs.tab', function () {
            // Ensure chart is initialized when tab is shown
            setTimeout(() => {
                const canvas = document.getElementById('metricChart');
                if (canvas && !Chart.getChart(canvas)) {
                    initializeChart();
                }
            }, 100);
        });
    }
    
    // Chart type change handler (if implemented)
    const chartTypeSelect = document.getElementById('chartType');
    if (chartTypeSelect) {
        chartTypeSelect.addEventListener('change', function() {
            // Reinitialize chart with new type
            initializeChart();
        });
    }
    
    // Cumulative view toggle handler
    const cumulativeToggle = document.getElementById('cumulativeView');
    if (cumulativeToggle) {
        cumulativeToggle.addEventListener('change', function() {
            // Reinitialize chart with cumulative view setting
            initializeChart();
        });
    }
    
    // Download chart image handler
    const downloadImageBtn = document.getElementById('downloadChartImage');
    if (downloadImageBtn) {
        downloadImageBtn.addEventListener('click', function() {
            if (window.currentChart) {
                const link = document.createElement('a');
                link.download = 'outcome-chart.png';
                link.href = window.currentChart.toBase64Image();
                link.click();
            } else {
                alert('Chart not available for download');
            }
        });
    }

    // Download handlers
    const downloadBtn = document.getElementById('downloadDataCSV');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            // Create CSV content
            let csv = 'Row,' + window.tableColumns.join(',') + '\n';
            window.tableRows.forEach(row => {
                let rowData = [row];
                window.tableColumns.forEach(col => {
                    rowData.push(window.tableData[row] ? (window.tableData[row][col] || 0) : 0);
                });
                csv += rowData.join(',') + '\n';
            });
            
            // Download CSV
            const blob = new Blob([csv], { type: 'text/csv' });
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'outcome_data.csv';
            a.click();
            window.URL.revokeObjectURL(url);
        });
    }
});
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
