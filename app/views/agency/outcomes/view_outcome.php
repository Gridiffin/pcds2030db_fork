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
require_once ROOT_PATH . 'app/lib/agencies/outcomes.php';

// Verify user is an agency user
if (!is_agency()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get outcome ID from URL
$outcome_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($outcome_id === 0) {
    $_SESSION['error_message'] = 'Invalid outcome ID.';
    header('Location: submit_outcomes.php');
    exit;
}

// Fetch outcome from new outcomes table
$outcome = get_outcome_by_id($outcome_id);
if (!$outcome) {
    $_SESSION['error_message'] = 'Outcome not found.';
    header('Location: submit_outcomes.php');
    exit;
}

// Extract fields
$title = $outcome['title'];
$code = $outcome['code'];
$type = $outcome['type'];
$description = $outcome['description'];
$data = $outcome['data'];
$updated_at = new DateTime($outcome['updated_at']);

// Success message handling
$success_message = '';
if (isset($_GET['saved']) && $_GET['saved'] == '1') {
    $success_message = 'Outcome updated successfully!';
}

// Parse the data structure (compatible with edit_outcome.php format)
$data_array = $data ?? ['columns' => [], 'data' => []];

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
    'subtitle' => htmlspecialchars($outcome['title']),
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'edit_outcome.php?id=' . $outcome_id,
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
                    <i class="fas fa-table me-2"></i><?= htmlspecialchars($outcome['title']) ?>
                </h5>
                <!-- Removed the green badge for Flexible Structure -->
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
        </ul>

        <!-- Tab Content -->
        <div class="tab-content" id="outcomeDetailTabsContent">
            <!-- Table View Tab -->
            <div class="tab-pane fade show active" id="table-view" role="tabpanel" aria-labelledby="table-tab">
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <strong>Description:</strong> <?= htmlspecialchars($description) ?>
                            </div>
                            <div class="mb-3">
                                <strong>Updated:</strong> <?= $updated_at->format('F j, Y g:i A') ?>
                            </div>
                        </div>
                    </div>

                    <?php if ($type === 'kpi'): ?>
                        <?php if (!empty($outcome['data']) && is_array($outcome['data'])): ?>
                            <div class="row">
                                <?php foreach ($outcome['data'] as $key => $value): ?>
                                    <div class="col-md-4 mb-3">
                                        <div class="card card-body shadow-sm h-100">
                                            <div class="fw-bold text-uppercase text-muted small mb-1"><?= htmlspecialchars($key) ?></div>
                                            <div class="fs-4 fw-semibold">
                                                <?= is_numeric($value) ? number_format($value, 2) : htmlspecialchars($value) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning text-center my-4">
                                <i class="fas fa-exclamation-circle me-2"></i> No KPI data available for this outcome.
                            </div>
                        <?php endif; ?>
                    <?php elseif ($type === 'graph'): ?>
                        <?php if ($has_data): ?>
                        <!-- View Mode: Read-only Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 150px;">Row</th>
                                        <?php foreach ($columns as $column): ?>
                                            <th class="text-center">
                                                <?php if (is_array($column)): ?>
                                                    <?= htmlspecialchars($column['label'] ?? $column['id']) ?>
                                                    <?php if (!empty($column['unit'])): ?>
                                                        <br><small class="text-muted">(<?= htmlspecialchars($column['unit']) ?>)</small>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <?= htmlspecialchars($column) ?>
                                                <?php endif; ?>
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
                                                    $col_id = $column['id'] ?? $column;
                                                    $value = $data[$row_label][$col_id] ?? 0;
                                                    // Handle empty strings and non-numeric values safely
                                                    if (is_numeric($value) && $value !== '') {
                                                        echo number_format((float)$value, 2);
                                                    } else {
                                                        echo htmlspecialchars($value);
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
                                                $col_id = $column['id'] ?? $column;
                                                $total = 0;
                                                foreach ($row_labels as $row_label) {
                                                    $cell_value = $data[$row_label][$col_id] ?? 0;
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
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
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
                                        <?php if (is_array($column)): ?>
                                            <option value="<?= htmlspecialchars($column['id']) ?>" selected>
                                                <?= htmlspecialchars($column['label'] ?? $column['id']) ?>
                                            </option>
                                        <?php else: ?>
                                            <option value="<?= htmlspecialchars($column) ?>" selected>
                                                <?= htmlspecialchars($column) ?>
                                            </option>
                                        <?php endif; ?>
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
                    
                    <!-- Chart Canvas - Simple Approach -->
                    <div style="width: 100%; height: 800px; margin: 20px 0;">
                        <canvas id="metricChart" style="width: 100%; height: 100%;"></canvas>
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
    title: <?= json_encode($outcome['title']) ?>,
    hasData: <?= json_encode($has_data) ?>
};

// Initialize table data and chart functionality

// Initialize chart functionality if Chart.js is available
function initializeChart() {
    if (typeof Chart !== 'undefined' && window.tableData && window.tableColumns && window.tableRows) {
        // Get the canvas element
        const canvas = document.getElementById('metricChart');
        
        if (canvas && Object.keys(window.tableData).length > 0) {
            // Clear any previous chart
            if (window.currentChart) {
                window.currentChart.destroy();
            }
            
            // Get chart settings from form inputs
            const chartType = document.getElementById('chartType') ? document.getElementById('chartType').value : 'bar';
            const cumulativeView = document.getElementById('cumulativeView') ? document.getElementById('cumulativeView').checked : false;
            
            // Create chart data - ensure all values are numeric
            const labels = window.tableRows;
            const datasets = window.tableColumns.map((column, index) => {
                // Handle both string columns and object columns
                const colId = typeof column === 'object' ? (column['id'] || '') : column;
                const colLabel = typeof column === 'object' ? (column['label'] || column['id'] || '') : column;
                
                let data = labels.map(row => {
                    const cellValue = window.tableData[row] ? window.tableData[row][colId] : null;
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
                    label: colLabel + (cumulativeView ? ' (Cumulative)' : ''),
                    data: data,
                    backgroundColor: colors[index % colors.length],
                    borderColor: colors[index % colors.length].replace('0.8', '1'),
                    borderWidth: 2,
                    fill: chartType === 'area'
                };
            });
            
            // Create chart with basic configuration
            window.currentChart = new Chart(canvas, {
                type: chartType === 'area' ? 'line' : chartType,
                data: {
                    labels: labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    devicePixelRatio: window.devicePixelRatio || 1,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                font: {
                                    size: 14,
                                    weight: '600'
                                },
                                // Format large numbers (e.g., financial data)
                                callback: function(value) {
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
                        },
                        x: {
                            ticks: {
                                font: {
                                    size: 14,
                                    weight: '600'
                                },
                                maxRotation: 45,
                                minRotation: 0
                            }
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Outcome Data Visualization' + (cumulativeView ? ' (Cumulative View)' : ''),
                            font: {
                                size: 20,
                                weight: 'bold',
                                family: "'Segoe UI', 'Helvetica', 'Arial', sans-serif"
                            }
                        },
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 16,
                                    weight: '600'
                                }
                            }
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
        }
    }
}

// Initialize everything when the page loads
document.addEventListener('DOMContentLoaded', function() {
    // Simple setup for chart initialization on tab switch
    const chartTab = document.getElementById('chart-tab');
    if (chartTab) {
        chartTab.addEventListener('shown.bs.tab', function() {
            console.log('Chart tab activated, initializing chart');
            setTimeout(initializeChart, 100);
        });
    }
    
    // Check if chart tab is initially active
    const chartView = document.getElementById('chart-view');
    if (chartView && chartView.classList.contains('active')) {
        console.log('Chart tab is initially active, initializing chart');
        setTimeout(initializeChart, 100);
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
            let csv = 'Row,' + window.tableColumns.map(col => typeof col === 'object' ? (col['id'] || '') : col).join(',') + '\n';
            window.tableRows.forEach(row => {
                let rowData = [row];
                window.tableColumns.forEach(col => {
                    const colId = typeof col === 'object' ? (col['id'] || '') : col;
                    rowData.push(window.tableData[row] ? (window.tableData[row][colId] || 0) : 0);
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

    // Simple handler for window resize
    window.addEventListener('resize', function() {
        if (window.currentChart) {
            setTimeout(() => window.currentChart.resize(), 100);
        }
    });
});
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>
