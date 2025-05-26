<?php
/**
* Manage Outcomes
* 
* Admin page to manage outcomes.
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
$pageTitle = 'Manage Outcomes';

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

// Include admin navigation
require_once '../../layouts/admin_nav.php';
?>

<!-- Chart.js for visualization -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="container-fluid px-4 py-4"><div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0">Manage Outcomes</h1>
            <p class="text-muted">Admin interface to manage outcomes</p>
        </div>
        <div>            <a href="javascript:void(0)" class="btn btn-forest me-2" id="createMetricBtn">
                <i class="fas fa-plus-circle me-1"></i> Create New Outcome
            </a>
            <button class="btn btn-forest-light" id="refreshPage">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>    <!-- Sector Filter -->
    <div class="card admin-card mb-4">
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
                </div>                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-forest me-2">Apply Filter</button>                    <?php if ($selected_sector > 0 || $selected_period > 0): ?>
                        <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/manage_outcomes.php" class="btn btn-forest-light">Clear Filters</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>    <div class="card admin-card mb-4">
        <div class="card-header">
            <h5 class="card-title m-0">Outcomes</h5>
        </div>
        
        <!-- Tab Navigation -->
        <ul class="nav nav-tabs" id="outcomesTabs" role="tablist">
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
        <div class="tab-content" id="outcomesTabsContent">
            <!-- Table View Tab -->
            <div class="tab-pane fade show active" id="table-view" role="tabpanel" aria-labelledby="table-tab">
                <div class="card-body p-0">
                    <table id="metricsTable" class="table table-forest">
                        <thead>
                            <tr>
                                <th>Outcome ID</th>
                                <th>Sector</th>
                                <th>Table Name</th>
                                <th>Reporting Period</th>
                                <th>Created</th>
                                <th>Last Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>                            <?php 
                            // Apply array_values to reindex after filtering and ensure we have valid data
                            // Extra safety: ensure $outcomes is array before processing
                            if (!is_array($outcomes)) {
                                $outcomes = [];
                            }
                            $display_outcomes = !empty($outcomes) ? array_values($outcomes) : [];
                            if (empty($display_outcomes)): 
                            ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">                                <div class="alert alert-forest alert-info mb-0">
                                            <i class="fas fa-info-circle alert-icon"></i><?php
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
                                </tr>                            <?php else: ?>
                                <?php foreach ($display_outcomes as $outcome): ?>
                                    <tr data-metric-id="<?php echo $outcome['metric_id']; ?>">
                                        <td><?php echo $outcome['metric_id']; ?></td>
                                        <td><?php echo htmlspecialchars($outcome['sector_name'] ?? 'No Sector'); ?></td>                                        <td><?php echo htmlspecialchars($outcome['table_name']); ?></td>                                <td>
                                            <?php if (isset($outcome['quarter']) && isset($outcome['year'])): ?>
                                                <span class="status-indicator <?= ($current_period && $outcome['period_id'] == $current_period['period_id']) ? 'status-success' : 'status-info' ?>">
                                                    Q<?= $outcome['quarter'] ?>-<?= $outcome['year'] ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="status-indicator status-warning">Not Specified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($outcome['created_at'])); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($outcome['updated_at'])); ?></td>                                <td>                                            <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/unsubmit_outcome.php?metric_id=<?php echo $outcome['metric_id']; ?>" class="btn btn-forest-light me-1" role="button" onclick="return confirm('Are you sure you want to unsubmit?');">
                                                <i class="fas fa-undo me-1"></i> Unsubmit
                                            </a>                 
                                            <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/view_outcome.php?metric_id=<?php echo $outcome['metric_id']; ?>" class="btn btn-forest-light me-1" role="button">
                                                <i class="fas fa-eye me-1"></i> View
                                            </a>
                                            <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/edit_outcome.php?metric_id=<?php echo $outcome['metric_id']; ?>" class="btn btn-forest me-1" role="button">
                                                <i class="fas fa-edit me-1"></i> Edit
                                            </a>                                    
                                            <a href="<?php echo APP_URL; ?>/app/views/admin/outcomes/delete_outcome.php?metric_id=<?php echo $outcome['metric_id']; ?>" class="btn btn-forest-light text-danger" role="button" onclick="return confirm('Are you sure you want to delete this outcome?');">
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
            
            <!-- Chart View Tab -->
            <div class="tab-pane fade" id="chart-view" role="tabpanel" aria-labelledby="chart-tab">
                <div class="card-body">
                    <!-- Chart options -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="chartTypeSelect" class="form-label">Chart Type</label>
                                <select class="form-select" id="chartTypeSelect">
                                    <option value="line">Line Chart</option>
                                    <option value="bar">Bar Chart</option>
                                    <option value="radar">Radar Chart</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="outcomeSelect" class="form-label">Outcome to Display</label>
                                <select class="form-select" id="outcomeSelect">                                    <option value="0">Select an outcome...</option>
                                    <?php if (!empty($display_outcomes)): ?>
                                        <?php foreach ($display_outcomes as $outcome): ?>
                                            <option value="<?= $outcome['metric_id'] ?>">
                                                <?= htmlspecialchars($outcome['table_name']) ?>
                                                (<?= htmlspecialchars($outcome['sector_name'] ?? 'No Sector') ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="metricSelect" class="form-label">Metric to Display</label>
                                <select class="form-select" id="metricSelect" disabled>
                                    <option value="all">All Metrics</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Chart Canvas -->
                    <div class="chart-container" style="position: relative; height:400px;">
                        <canvas id="outcomesChart"></canvas>
                        <div id="chartPlaceholder" class="text-center py-5">
                            <i class="fas fa-chart-line fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">Select an outcome to display chart</h4>
                        </div>
                    </div>
                    
                    <!-- Download Options -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-end">
                            <button id="downloadChartImage" class="btn btn-outline-primary me-2" disabled>
                                <i class="fas fa-image me-1"></i> Download Chart
                            </button>
                            <button id="downloadDataCSV" class="btn btn-outline-success" disabled>
                                <i class="fas fa-file-csv me-1"></i> Download Data as CSV
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Refresh page button
        document.getElementById('refreshPage').addEventListener('click', function() {
            window.location.reload();
        });
          // Create Metric button - redirect to create page
        document.getElementById('createMetricBtn').addEventListener('click', function() {
            // Get selected sector and period from filters, if any
            const sectorId = document.getElementById('sector_id').value;
            const periodId = document.getElementById('period_id').value;
            
            let url = 'edit_outcome.php';
            let params = [];
            
            if (sectorId > 0) {
                params.push('sector_id=' + sectorId);
            }
            
            if (periodId > 0) {
                params.push('period_id=' + periodId);
            }
            
            if (params.length > 0) {
                url += '?' + params.join('&');
            }
            
            window.location.href = url;
        });
        
        // Auto-submit filter when sector changes
        document.getElementById('sector_id').addEventListener('change', function() {
            this.form.submit();
        });
        
        // Auto-submit filter when period changes
        document.getElementById('period_id').addEventListener('change', function() {
            this.form.submit();
        });
        
        // Fix dropdown menu functionality
        document.querySelectorAll('.dropdown-toggle').forEach(function(dropdownToggle) {
            dropdownToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Find the closest dropdown parent
                const dropdown = this.closest('.dropdown');
                
                // Toggle 'show' class on dropdown and menu
                dropdown.classList.toggle('show');
                
                // Find and toggle dropdown menu
                const dropdownMenu = dropdown.querySelector('.dropdown-menu');
                if (dropdownMenu) {
                    dropdownMenu.classList.toggle('show');
                }
                
                // Update aria-expanded attribute
                this.setAttribute('aria-expanded', 
                    this.getAttribute('aria-expanded') === 'true' ? 'false' : 'true');
            });
        });
        
        // Chart-related functionality
        const outcomeSelect = document.getElementById('outcomeSelect');
        const metricSelect = document.getElementById('metricSelect');
        const chartTypeSelect = document.getElementById('chartTypeSelect');
        const chartPlaceholder = document.getElementById('chartPlaceholder');
        const chartCanvas = document.getElementById('outcomesChart');
        const downloadChartBtn = document.getElementById('downloadChartImage');
        const downloadDataBtn = document.getElementById('downloadDataCSV');
        
        let outcomesChart = null;
        
        if (outcomeSelect) {
            outcomeSelect.addEventListener('change', function() {
                const selectedOutcomeId = this.value;
                
                if (selectedOutcomeId > 0) {
                    // Hide placeholder and enable metric selector
                    if (chartPlaceholder) chartPlaceholder.style.display = 'none';
                    if (chartCanvas) chartCanvas.style.display = 'block';
                    if (metricSelect) metricSelect.disabled = false;
                    if (downloadChartBtn) downloadChartBtn.disabled = false;
                    if (downloadDataBtn) downloadDataBtn.disabled = false;
                    
                    // Fetch outcome data and update chart
                    fetchOutcomeData(selectedOutcomeId);
                } else {
                    // Show placeholder and disable metric selector
                    if (chartPlaceholder) chartPlaceholder.style.display = 'block';
                    if (chartCanvas) chartCanvas.style.display = 'none';
                    if (metricSelect) {
                        metricSelect.innerHTML = '<option value="all">All Metrics</option>';
                        metricSelect.disabled = true;
                    }
                    if (downloadChartBtn) downloadChartBtn.disabled = true;
                    if (downloadDataBtn) downloadDataBtn.disabled = true;
                }
            });
        }
        
        if (chartTypeSelect) {
            chartTypeSelect.addEventListener('change', function() {
                updateChart();
            });
        }
        
        if (metricSelect) {
            metricSelect.addEventListener('change', function() {
                updateChart();
            });
        }
        
        // Initial setup
        if (chartCanvas) {
            chartCanvas.style.display = 'none';
        }
          // Functions for chart handling
        function fetchOutcomeData(outcomeId) {
            // Fetch outcome data via AJAX
            fetch(`<?php echo APP_URL; ?>/app/api/outcomes/get_outcome.php?outcome_id=${outcomeId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Populate metric select with available metrics
                        populateMetricSelect(data.outcome);
                        
                        // Initialize chart with data
                        initializeChart(data.outcome);
                    } else {
                        console.error('Error fetching outcome data:', data.message);
                    }
                })
                .catch(error => {
                    console.error('Error fetching outcome data:', error);
                });
        }
        
        function populateMetricSelect(outcomeData) {
            if (!metricSelect || !outcomeData || !outcomeData.data_json) return;
            
            const parsedData = typeof outcomeData.data_json === 'string' ? 
                JSON.parse(outcomeData.data_json) : outcomeData.data_json;
            
            const metrics = parsedData.columns || [];
            
            // Clear previous options
            metricSelect.innerHTML = '';
            
            // Add "All Metrics" option
            const allOption = document.createElement('option');
            allOption.value = 'all';
            allOption.textContent = 'All Metrics';
            metricSelect.appendChild(allOption);
            
            // Add individual metrics
            metrics.forEach(metric => {
                const option = document.createElement('option');
                option.value = metric;
                option.textContent = metric;
                metricSelect.appendChild(option);
            });
        }
        
        function initializeChart(outcomeData) {
            if (!chartCanvas || !outcomeData || !outcomeData.data_json) return;
            
            // Parse data if needed
            const parsedData = typeof outcomeData.data_json === 'string' ? 
                JSON.parse(outcomeData.data_json) : outcomeData.data_json;
            
            // Store the data for later use
            chartCanvas.dataset.outcomeData = JSON.stringify(parsedData);
            
            // Update the chart
            updateChart();
        }
        
        function updateChart() {
            if (!chartCanvas) return;
            
            const outcomeDataStr = chartCanvas.dataset.outcomeData;
            if (!outcomeDataStr) return;
            
            const outcomeData = JSON.parse(outcomeDataStr);
            const chartType = chartTypeSelect ? chartTypeSelect.value : 'line';
            const selectedMetric = metricSelect ? metricSelect.value : 'all';
            
            // Destroy previous chart instance if exists
            if (outcomesChart) {
                outcomesChart.destroy();
            }
            
            // Prepare data for chart
            const months = Object.keys(outcomeData.data || {});
            const metrics = selectedMetric === 'all' ? 
                outcomeData.columns : [selectedMetric];
            
            const datasets = [];
            const colorPalette = [
                'rgba(54, 162, 235, 0.7)', 'rgba(255, 99, 132, 0.7)', 
                'rgba(75, 192, 192, 0.7)', 'rgba(255, 206, 86, 0.7)',
                'rgba(153, 102, 255, 0.7)', 'rgba(255, 159, 64, 0.7)'
            ];
            
            metrics.forEach((metric, index) => {
                const data = months.map(month => {
                    return outcomeData.data[month] && outcomeData.data[month][metric] ? 
                        parseFloat(outcomeData.data[month][metric]) : 0;
                });
                
                datasets.push({
                    label: metric,
                    data: data,
                    backgroundColor: colorPalette[index % colorPalette.length],
                    borderColor: colorPalette[index % colorPalette.length].replace('0.7', '1'),
                    borderWidth: 1,
                    fill: chartType === 'radar'
                });            });
            
            // Create chart with error handling
            try {
                if (typeof Chart === 'undefined') {
                    console.error('Chart.js is not loaded');
                    return;
                }
                
                outcomesChart = new Chart(chartCanvas, {
                    type: chartType,
                    data: {
                    labels: months,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        title: {
                            display: true,
                            text: 'Outcome Metrics by Month'
                        }
                    }                }
            });
            
            } catch (error) {
                console.error('Error creating chart:', error);
                // Optionally show a user-friendly message
                if (chartCanvas) {
                    chartCanvas.style.display = 'none';
                }
                if (chartPlaceholder) {
                    chartPlaceholder.innerHTML = '<div class="text-center py-5"><i class="fas fa-exclamation-triangle fa-4x text-warning mb-3"></i><h4 class="text-muted">Chart visualization unavailable</h4><p class="text-muted">Unable to load chart library</p></div>';
                    chartPlaceholder.style.display = 'block';
                }
                return;
            }
            
            // Enable download buttons
            if (downloadChartBtn) downloadChartBtn.disabled = false;
            if (downloadDataBtn) downloadDataBtn.disabled = false;
            
            // Setup download chart button
            if (downloadChartBtn) {
                downloadChartBtn.onclick = function() {
                    const dataURL = chartCanvas.toDataURL('image/png');
                    const link = document.createElement('a');
                    link.href = dataURL;
                    link.download = 'outcome_chart.png';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                };
            }
            
            // Setup download CSV button
            if (downloadDataBtn) {
                downloadDataBtn.onclick = function() {
                    // Create CSV content
                    let csv = 'Month';
                    metrics.forEach(metric => {
                        csv += ',' + metric;
                    });
                    csv += '\n';
                    
                    months.forEach(month => {
                        csv += month;
                        metrics.forEach(metric => {
                            const value = outcomeData.data[month] && outcomeData.data[month][metric] ? 
                                outcomeData.data[month][metric] : 0;
                            csv += ',' + value;
                        });
                        csv += '\n';
                    });
                    
                    // Create download link
                    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
                    const link = document.createElement('a');
                    link.href = URL.createObjectURL(blob);
                    link.download = 'outcome_data.csv';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                };
            }
        }
    });
</script>



