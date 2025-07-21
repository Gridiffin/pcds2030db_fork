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
require_once ROOT_PATH . 'app/lib/program_status_helpers.php'; // For display_submission_status_badge
require_once ROOT_PATH . 'app/lib/rating_helpers.php'; // For display_overall_rating_badge

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get outcome ID from URL
$outcome_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($outcome_id === 0) {
    $_SESSION['error_message'] = 'Invalid outcome ID.';
    header('Location: manage_outcomes.php');
    exit;
}

// Fetch outcome from new outcomes table
$outcome = get_outcome_by_id($outcome_id);
if (!$outcome) {
    $_SESSION['error_message'] = 'Outcome not found.';
    header('Location: manage_outcomes.php');
    exit;
}
// Set $title to fallback only if truly empty
$title = (!empty($outcome['title'])) ? $outcome['title'] : 'Untitled Outcome';
$code = $outcome['code'] ?? '';
$type = $outcome['type'] ?? '';
$description = $outcome['description'] ?? '';
$data_array = $outcome['data'] ?? ['columns' => [], 'rows' => []];
$updated_at = new DateTime($outcome['updated_at'] ?? 'now');

// Ensure we have the correct structure
if (!isset($data_array['columns']) || !isset($data_array['rows'])) {
    $data_array = ['columns' => [], 'rows' => []];
}

$columns = $data_array['columns'] ?? [];
$rows = $data_array['rows'] ?? [];

// Ensure $columns and $data_array['rows'] are always arrays
if (!isset($columns) || !is_array($columns)) {
    $columns = [];
}
if (!isset($data_array['rows']) || !is_array($data_array['rows'])) {
    $data_array['rows'] = [];
}

// Get row labels from the rows array
$row_labels = [];
foreach ($rows as $row) {
    $row_labels[] = $row['month'] ?? $row['label'] ?? '';
}

// If no data exists, show empty state
$has_data = !empty($columns) && !empty($rows);

// Add CSS references (if any specific to outcome viewing)
$additionalStyles = [
    // APP_URL . '/assets/css/custom/outcome-view.css' // Example
];

// Include header
require_once ROOT_PATH . 'app/views/layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => $title,
    'subtitle' => 'Review outcome data and metrics',
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'manage_outcomes.php',
            'text' => 'Back to Manage Outcomes',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-primary'
        ],
        // Replace Edit Outcome button with Edit KPI if type is kpi
        (
            $type === 'kpi'
            ? [
                'url' => 'edit_kpi.php?id=' . $outcome_id,
                'text' => 'Edit KPI',
                'icon' => 'fas fa-edit',
                'class' => 'btn-primary'
            ]
            : [
                'url' => 'edit_outcome.php?id=' . $outcome_id,
                'text' => 'Edit Outcome',
                'icon' => 'fas fa-edit',
                'class' => 'btn-primary'
            ]
        )
    ]
];

// Include modern page header
require_once '../../layouts/page_header.php';
// Re-assign $title after page_header.php unsets it
$title = (!empty($outcome['title'])) ? $outcome['title'] : 'Untitled Outcome';
?>

<div class="container-fluid px-4 py-4">
    <div class="card mb-4 admin-card">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="card-title m-0">
                <i class="fas fa-bullseye me-2"></i><?= htmlspecialchars($title ?? 'Untitled Outcome') ?>
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
                            <p><strong>Outcome ID:</strong> <?= $outcome_id ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Updated:</strong> <?= $updated_at->format('F j, Y, g:i A') ?></p>
                        </div>
                    </div>

                    <?php if ($type === 'kpi'): ?>
                        <?php if (!empty($outcome['data']) && is_array($outcome['data'])): ?>
                            <div class="row">
                                <?php foreach ($outcome['data'] as $item): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="card card-body shadow-sm h-100">
                                            <div class="fw-bold mb-1"><?= htmlspecialchars($item['description'] ?? '') ?></div>
                                            <div class="fs-2 fw-semibold text-primary">
                                                <?= isset($item['value']) ? htmlspecialchars($item['value']) : '' ?>
                                                <?php if (!empty($item['unit'])): ?>
                                                    <span class="fs-5 text-muted ms-1"><?= htmlspecialchars($item['unit']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (!empty($item['extra'])): ?>
                                                <div class="text-muted small mt-2">
                                                    <?= htmlspecialchars($item['extra']) ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning text-center my-4">
                                <i class="fas fa-exclamation-circle me-2"></i> No KPI data available for this outcome.
                            </div>
                        <?php endif; ?>
                    <?php elseif (!empty($columns) && $has_data): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover data-table">
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
                                <?php foreach ($data_array['rows'] as $row): ?>
                                    <tr>
                                        <td>
                                            <span class="month-badge"><?= htmlspecialchars($row['month'] ?? '') ?></span>
                                        </td>
                                        <?php foreach ($columns as $col_id): 
                                            $value = $row[$col_id] ?? 0;
                                        ?>
                                            <td class="text-end">
                                                <?php
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
                                <tr class="table-light fw-bold">
                                    <td><span class="total-badge">TOTAL</span></td>
                                    <?php foreach ($columns as $col_id): 
                                        $total = 0;
                                        foreach ($data_array['rows'] as $row) {
                                            $cell_value = $row[$col_id] ?? 0;
                                            if (is_numeric($cell_value) && $cell_value !== '') {
                                                $total += (float)$cell_value;
                                            }
                                        }
                                    ?>
                                        <td class="text-end"><?= number_format($total, 2) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <?php elseif (!empty($columns)): ?>
                    <!-- Legacy format display (fallback) -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover data-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 150px;">Month</th>
                                    <?php foreach ($columns as $column): ?>
                                        <th>
                                            <?= htmlspecialchars($column['label'] ?? '') ?>
                                            <?php if (!empty($column['unit'])): ?>
                                                <span class="text-muted small">(<?= htmlspecialchars($column['unit']) ?>)</span>
                                            <?php endif; ?>
                                        </th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rows as $row_data): ?>
                                    <tr>
                                        <td>
                                            <span class="month-badge"><?= htmlspecialchars($row_data['label'] ?? $row_data['month'] ?? '') ?></span>
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
                                        foreach ($rows as $row_data) {
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
                                            <td><?= htmlspecialchars($row['label'] ?? $row['month'] ?? '') ?></td>
                                            <td>
                                                <span class="badge bg-secondary"><?= ucfirst($row['type'] ?? 'data') ?></span>
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
                                            <td><?= htmlspecialchars($column['label'] ?? '') ?></td>
                                            <td>
                                                <span class="badge bg-info"><?= ucfirst($column['type'] ?? 'number') ?></span>
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
                                    <?php foreach ($columns as $col): ?>
                                        <option value="<?= htmlspecialchars($col) ?>" selected>
                                            <?= htmlspecialchars($col) ?>
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
                    
                    <!-- Chart Canvas - Simple Approach -->
                    <div style="width: 100%; height: 800px; margin: 20px 0;">
                        <canvas id="metricChart" style="width: 100%; height: 100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pass data to JavaScript (same format as agency side) -->
<script>
// Prepare data for chart in a simple, compatible format
window.tableData = {};
window.tableColumns = <?= json_encode($columns) ?>;
window.tableRows = <?= json_encode(array_map(function($row) { return $row['month'] ?? ''; }, $data_array['rows'])) ?>;
<?php if ($has_data && !empty($columns) && !empty($data_array['rows'])): ?>
<?php foreach ($data_array['rows'] as $row): ?>
window.tableData[<?= json_encode($row['month'] ?? '') ?>] = {};
<?php foreach ($columns as $col_id): ?>
window.tableData[<?= json_encode($row['month'] ?? '') ?>][<?= json_encode($col_id) ?>] = <?= json_encode($row[$col_id] ?? 0) ?>;
<?php endforeach; ?>
<?php endforeach; ?>
// Debug output to verify data structure
console.log('Chart Debug: tableData', window.tableData);
console.log('Chart Debug: tableColumns', window.tableColumns);
console.log('Chart Debug: tableRows', window.tableRows);
<?php else: ?>
// Chart Debug: No data available for chart
console.log('Chart Debug: No data available for chart');
<?php endif; ?>

// Additional data for context
const outcomeInfo = {
    id: <?= $outcome_id ?>,
    title: <?= isset($title) ? json_encode($title) : json_encode('Untitled Outcome') ?>,
    hasData: <?= json_encode($has_data) ?>
};

// Initialize chart functionality if Chart.js is available (same as agency side)
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
            const chartType = document.getElementById('chartTypeSelect') ? document.getElementById('chartTypeSelect').value : 'bar';
            const cumulativeView = document.getElementById('cumulativeView') ? document.getElementById('cumulativeView').checked : false;
            // Get selected columns from dropdown
            const chartColumnSelect = document.getElementById('chartColumnSelect');
            let selectedColumns = window.tableColumns;
            if (chartColumnSelect) {
                selectedColumns = Array.from(chartColumnSelect.selectedOptions).map(opt => opt.value);
            }
            // Create chart data - ensure all values are numeric
            const labels = window.tableRows;
            const datasets = selectedColumns.map((column, index) => {
                let data = labels.map(row => {
                    const cellValue = window.tableData[row] ? window.tableData[row][column] : null;
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
    
    // Chart type change handler
    const chartTypeSelect = document.getElementById('chartTypeSelect');
    if (chartTypeSelect) {
        chartTypeSelect.addEventListener('change', function() {
            initializeChart();
        });
    }
    
    // Cumulative view toggle handler
    const cumulativeToggle = document.getElementById('cumulativeView');
    if (cumulativeToggle) {
        cumulativeToggle.addEventListener('change', function() {
            initializeChart();
        });
    }
    
    // Download chart image handler
    const downloadImageBtn = document.getElementById('downloadChartImage');
    if (downloadImageBtn) {
        downloadImageBtn.addEventListener('click', function() {
            if (window.currentChart) {
                const url = window.currentChart.toBase64Image();
                const link = document.createElement('a');
                link.download = 'outcome-chart.png';
                link.href = url;
                link.click();
            }
        });
    }

    // Download CSV handler
    const downloadBtn = document.getElementById('downloadDataCSV');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            if (window.tableData && window.tableColumns && window.tableRows) {
                let csv = 'Row,' + window.tableColumns.join(',') + '\n';
                
                window.tableRows.forEach(row => {
                    const rowData = [row];
                    window.tableColumns.forEach(col => {
                        const value = window.tableData[row] && window.tableData[row][col] ? window.tableData[row][col] : 0;
                        rowData.push(value);
                    });
                    csv += rowData.join(',') + '\n';
                });
                
                const blob = new Blob([csv], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const link = document.createElement('a');
                link.download = 'outcome-data.csv';
                link.href = url;
                link.click();
                window.URL.revokeObjectURL(url);
            }
        });
    }

    // Add event listener for chartColumnSelect to make it toggleable
    const chartColumnSelect = document.getElementById('chartColumnSelect');
    if (chartColumnSelect) {
        // Toggle selection on click (mousedown)
        Array.from(chartColumnSelect.options).forEach(option => {
            option.addEventListener('mousedown', function(e) {
                e.preventDefault();
                option.selected = !option.selected;
                // Trigger change event to update chart
                chartColumnSelect.dispatchEvent(new Event('change'));
            });
        });
        // Update chart on change
        chartColumnSelect.addEventListener('change', function() {
            initializeChart();
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

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.js"></script>

<?php 
// Include footer
require_once ROOT_PATH . 'app/views/layouts/footer.php';
?>
