<?php
/**
 * View and Edit Outcome Details
 * 
 * Combined interface for agency users to view and edit outcome details
 * Supports both view mode (default) and edit mode (via mode=edit parameter)
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
require_once dirname(__DIR__, 3) . '/lib/audit_log.php';

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

// Check if we're in edit mode
$edit_mode = isset($_GET['mode']) && $_GET['mode'] === 'edit';

// Initialize message variables
$message = '';
$message_type = '';

// Handle form submission for edit mode
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $edit_mode) {
    $post_table_name = trim($_POST['table_name'] ?? '');
    $post_data_json = $_POST['data_json'] ?? '';

    if ($post_table_name === '' || $post_data_json === '') {
        $message = 'Table name and data are required.';
        $message_type = 'danger';
    } else {
        $post_data_array = json_decode($post_data_json, true);
        if ($post_data_array === null) {
            $message = 'Invalid JSON data.';
            $message_type = 'danger';
        } else {
            // Update existing record in sector_outcomes_data
            $update_query = "UPDATE sector_outcomes_data SET table_name = ?, data_json = ?, updated_at = NOW() WHERE metric_id = ? AND sector_id = ?";
            $stmt_update = $conn->prepare($update_query);
            $data_json_str = json_encode($post_data_array);
            $stmt_update->bind_param("ssii", $post_table_name, $data_json_str, $outcome_id, $sector_id);
            
            if ($stmt_update->execute()) {
                // Log successful outcome edit
                log_audit_action(
                    'outcome_updated',
                    "Updated classic outcome '{$post_table_name}' (Metric ID: {$outcome_id}) for sector {$sector_id}",
                    'success',
                    $_SESSION['user_id']
                );
                
                // Redirect back to view mode after successful save
                $redirect_url = 'view_outcome.php?outcome_id=' . $outcome_id . '&saved=1';
                header('Location: ' . $redirect_url);
                exit;
            } else {
                $message = 'Error updating outcome: ' . $conn->error;
                $message_type = 'danger';
                
                // Log outcome update failure
                log_audit_action(
                    'outcome_update_failed',
                    "Failed to update classic outcome '{$post_table_name}' (Metric ID: {$outcome_id}) for sector {$sector_id}: " . $conn->error,
                    'failure',
                    $_SESSION['user_id']
                );
            }
        }
    }
}

// Get outcome data using JSON-based storage with flexible structure detection
$query = "SELECT data_json, table_name, created_at, updated_at, is_draft, table_structure_type, row_config, column_config 
          FROM sector_outcomes_data 
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

// Check if this is a flexible table structure and redirect if needed
$table_structure_type = $row['table_structure_type'] ?? 'classic';
if ($table_structure_type === 'flexible') {
    // Redirect to the flexible outcome viewer (preserve edit mode if applicable)
    $redirect_url = 'view_outcome_flexible.php?outcome_id=' . $outcome_id;
    if ($edit_mode) {
        $redirect_url .= '&mode=edit';
    }
    header('Location: ' . $redirect_url);
    exit;
}

// Success message handling
$success_message = '';
if (isset($_GET['saved']) && $_GET['saved'] == '1') {
    $success_message = 'Outcome updated successfully!';
}

// Get column names and organize data
$metric_names = $outcome_data['columns'] ?? [];
$data_array = [
    'columns' => $metric_names,
    'data' => $outcome_data['data'] ?? []
];

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

// Add JS references for edit mode
if ($edit_mode) {
    $additionalScripts = [
        APP_URL . '/assets/js/metric-editor.js'
    ];
}

// Include header
require_once '../../layouts/header.php';

// Configure modern page header based on mode
$page_title = $edit_mode ? 'Edit Outcome' : 'View Outcome Details';
$page_subtitle = $edit_mode ? 'Edit outcome data and monthly values' : 'Review your outcome data';

$header_config = [
    'title' => $page_title,
    'subtitle' => $page_subtitle,
    'variant' => 'white',
    'actions' => []
];

// Add mode-specific back button
if ($edit_mode) {
    // In edit mode: Back to view outcome details
    $header_config['actions'][] = [
        'url' => 'view_outcome.php?outcome_id=' . $outcome_id,
        'text' => 'Back to View Outcome Details',
        'icon' => 'fas fa-arrow-left',
        'class' => 'btn-outline-primary'
    ];
} else {
    // In view mode: Back to outcomes listing
    $header_config['actions'][] = [
        'url' => 'submit_outcomes.php',
        'text' => 'Back to Outcomes',
        'icon' => 'fas fa-arrow-left',
        'class' => 'btn-outline-primary'
    ];
}

// Add mode-specific actions
if (!$edit_mode) {
    // Add edit button for view mode
    $header_config['actions'][] = [
        'url' => 'view_outcome.php?outcome_id=' . $outcome_id . '&mode=edit',
        'text' => 'Edit Outcome',
        'icon' => 'fas fa-edit',
        'class' => 'btn-primary'
    ];
}

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<div class="container-fluid px-4 py-4">
    <!-- Success message -->
    <?php if (!empty($success_message)): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($success_message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Error/Message display for edit mode -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($edit_mode): ?>
        <!-- Edit Mode Form -->
        <form id="editOutcomeForm" method="post" action="">
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                    <h5 class="card-title m-0">
                        <i class="fas fa-edit me-2"></i>Edit: <?= htmlspecialchars($table_name) ?>
                    </h5>
                    <div>
                        <span class="badge <?= $is_draft ? 'bg-secondary' : 'bg-primary' ?>">
                            <i class="fas <?= $is_draft ? 'fa-pencil-alt' : 'fa-check-circle' ?> me-1"></i> 
                            <?= $is_draft ? 'Draft' : 'Submitted' ?>
                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Table Name Input -->
                    <div class="mb-3">
                        <label for="tableNameInput" class="form-label">Table Name</label>
                        <input type="text" class="form-control" id="tableNameInput" name="table_name" required value="<?= htmlspecialchars($table_name) ?>" />
                    </div>

                    <!-- Add Column Button -->
                    <div class="mb-3">
                        <button type="button" class="btn btn-primary" id="addColumnBtn">
                            <i class="fas fa-plus me-1"></i> Add Column
                        </button>
                    </div>

                    <!-- Editable Table -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover metrics-table">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 150px;">Month</th>
                                    <!-- Dynamic columns will be added here by JavaScript -->
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $month_names = ['January', 'February', 'March', 'April', 'May', 'June',
                                    'July', 'August', 'September', 'October', 'November', 'December'];
                                foreach ($month_names as $month_name): ?>
                                    <tr>
                                        <td><span class="month-badge"><?= htmlspecialchars($month_name) ?></span></td>
                                        <!-- Dynamic cells will be added here by JavaScript -->
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Hidden input for JSON data -->
                    <input type="hidden" name="data_json" id="dataJsonInput" />

                    <!-- Form Actions -->
                    <div class="mt-3">
                        <button type="submit" class="btn btn-success" id="saveBtn">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                        <a href="view_outcome.php?outcome_id=<?= $outcome_id ?>" class="btn btn-secondary ms-2">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    <?php else: ?>
        <!-- View Mode Display -->
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
                                <label for="chartColumnSelect" class="form-label">Outcomes to Display</label>
                                <select class="form-select" id="chartColumnSelect">
                                    <!-- Options will be populated by JavaScript -->
                                </select>
                                <small class="text-muted">Select which outcomes to display in the chart</small>
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
    </div>
    <?php endif; ?> <!-- End view mode -->
</div> <!-- End container -->

<!-- Load enhanced charting script for view mode -->
<?php if (!$edit_mode): ?>
<script src="<?= APP_URL ?>/assets/js/charts/enhanced-outcomes-chart.js"></script>

<!-- Initialize the enhanced chart with classic structure data -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Prepare structure data for chart (classic monthly format)
        const structure = {
            rows: <?= json_encode(array_map(function($month) { return ['label' => $month, 'type' => 'text']; }, $month_names)) ?>,
            columns: <?= json_encode(array_map(function($col) { return ['name' => $col, 'type' => 'number']; }, $metric_names)) ?>
        };
        
        // Initialize enhanced chart with classic data
        initEnhancedOutcomesChart(
            <?= json_encode($outcome_data) ?>, 
            structure,
            "<?= addslashes($table_name) ?>",
            "classic"
        );
    });
<?php endif; ?>

<?php if ($edit_mode): ?>
<script>
    // JavaScript for edit mode functionality
    const monthNames = <?= json_encode($month_names) ?>;
    let columns = <?= json_encode($data_array['columns'] ?? []) ?>;
    let data = <?= json_encode($data_array['data'] ?? []) ?>;

    function addColumn() {
        const columnName = prompt('Enter column title:');
        if (!columnName || columnName.trim() === '') return;
        if (columns.includes(columnName)) {
            alert('Column title already exists.');
            return;
        }
        
        // Collect current data from DOM before adding column
        collectCurrentData();
        
        columns.push(columnName);
        renderTable();
    }

    function removeColumn(columnName) {
        // Collect current data from DOM before removing column
        collectCurrentData();
        
        columns = columns.filter(c => c !== columnName);
        
        // Remove data for the deleted column
        monthNames.forEach(month => {
            if (data[month]) {
                delete data[month][columnName];
            }
        });
        
        renderTable();
    }

    function collectCurrentData() {
        // Initialize data structure if needed
        if (!data || typeof data !== 'object') {
            data = {};
        }
        
        // Collect all current values from DOM
        monthNames.forEach(month => {
            if (!data[month]) {
                data[month] = {};
            }
            
            columns.forEach(col => {
                const cell = document.querySelector(`.metric-cell[data-month="${month}"][data-column="${col}"]`);
                if (cell) {
                    const value = parseFloat(cell.value) || 0;
                    data[month][col] = value;
                }
            });
        });
    }

    function renderTable() {
        const thead = document.querySelector('.metrics-table thead tr');
        const tbody = document.querySelector('.metrics-table tbody');
        
        // Clear and rebuild header
        thead.innerHTML = '<th style="width: 150px;">Month</th>';
        columns.forEach(col => {
            thead.innerHTML += `
                <th>
                    ${col}
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="removeColumn('${col}')" title="Remove Column">
                        <i class="fas fa-times"></i>
                    </button>
                </th>
            `;
        });
        
        // Clear and rebuild body
        tbody.innerHTML = '';
        monthNames.forEach(month => {
            let row = `<tr><td><span class="month-badge">${month}</span></td>`;
            columns.forEach(col => {
                const value = (data[month] && data[month][col]) ? data[month][col] : '';
                row += `<td><input type="number" step="0.01" class="form-control metric-cell" data-month="${month}" data-column="${col}" value="${value}" /></td>`;
            });
            row += '</tr>';
            tbody.innerHTML += row;
        });
    }

    // Form submission handler
    function prepareFormSubmission() {
        collectCurrentData();
        document.getElementById('dataJsonInput').value = JSON.stringify({
            columns: columns,
            data: data
        });
        return true;
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        renderTable();
        
        // Add event listener to add column button
        document.getElementById('addColumnBtn').addEventListener('click', addColumn);
        
        // Add form submission handlers
        document.getElementById('saveBtn').addEventListener('click', prepareFormSubmission);
        document.getElementById('saveDraftBtn').addEventListener('click', prepareFormSubmission);
    });
</script>
<?php endif; ?>
</script>

<?php
// Include footer
require_once '../../layouts/footer.php';
?>


