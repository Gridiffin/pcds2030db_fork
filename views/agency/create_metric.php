<?php
/**
 * Create Sector Metrics
 * 
 * Interface for agency users to create sector-specific metrics
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agency_functions.php';

// Verify user is an agency
if (!is_agency()) {
    header('Location: ../../login.php');
    exit;
}

$sector_id = $_GET['sector_id'] ?? $_SESSION['sector_id'];

// Set page title
$pageTitle = 'Create Sector Metrics';

// Handle form submission for new metrics
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    $metric_id = isset($_GET['next_metric_id']) ? intval($_GET['next_metric_id']) : 0;
    $_SESSION['metric_id'] = $metric_id;
    if (isset($_POST['table_name']) && trim($_POST['table_name']) !== '') {
        $new_table_name = $conn->real_escape_string($_POST['table_name']);
        // Check if a row exists for this metric_id and sector_id
        $check_query = "SELECT 1 FROM sector_metrics_draft WHERE metric_id = $metric_id AND sector_id = '$sector_id' LIMIT 1";
        $check_result = $conn->query($check_query);
        if ($check_result && $check_result->num_rows > 0) {
            // Update existing row
            $update_query = "UPDATE sector_metrics_draft SET table_name = '$new_table_name' WHERE sector_id = '$sector_id' AND metric_id = $metric_id";
            if ($conn->query($update_query) === TRUE) {
                $message = "Table name updated successfully.";
                $message_type = "success";
            } else {
                $message = "Error updating table name: " . $conn->error;
                $message_type = "danger";
            }
        } else {
            // Insert new row with table_name
            $insert_table_name_query = "INSERT INTO sector_metrics_draft (metric_id, table_name, column_title, table_content, month, sector_id) 
                VALUES ($metric_id, '$new_table_name', '', 0, 'January', '$sector_id')";
            if ($conn->query($insert_table_name_query) === TRUE) {
                $message = "Table name saved successfully.";
                $message_type = "success";
            } else {
                $message = "Error saving table name: " . $conn->error;
                $message_type = "danger";
            }
        }
    } else {
        // Use provided values or defaults for metric insert
        $name = $conn->real_escape_string($_POST['column_title'] ?? '');
        $value = floatval($_POST['table_content'] ?? 0);
        $month = $conn->real_escape_string($_POST['month'] ?? '');
        $table_name_post = $conn->real_escape_string($_POST['table_name'] ?? '');

        // If table_name is empty, generate a new table_name
        if (empty($table_name_post)) {
            $table_name_post = "Table_" . $metric_id;
        }

        // Insert new metric with table_name and metric_id
        $query = "INSERT INTO sector_metrics_draft (metric_id, table_name, column_title, table_content, month, sector_id) 
                VALUES ($metric_id, '$table_name_post', '$name', '$value', '$month', '$sector_id')";

        if ($conn->query($query) === TRUE) {
            $message = "Metric created successfully.";
            $message_type = "success";
        } else {
            $message = "Error: " . $conn->error;
            $message_type = "danger";
        }
    }
}

// Retrieve all metrics for display
$metric_id = isset($_GET['next_metric_id']) ? intval($_GET['next_metric_id']) : 0;
if ($metric_id === 0) {
    $result = $conn->query("SELECT MAX(metric_id) AS max_id FROM sector_metrics_draft");
    if ($result && $row = $result->fetch_assoc()) {
        $metric_id = $row['max_id'] + 1;
    }
}
$select_query = "SELECT * FROM sector_metrics_draft WHERE metric_id = $metric_id";
$metrics = $conn->query($select_query);
if (!$metrics) die("Error getting metrics: " . $conn->error);

// Organize data for display
$month_names = ['January', 'February', 'March', 'April', 'May', 'June', 
                'July', 'August', 'September', 'October', 'November', 'December'];
$table_data = [];
while ($row = $metrics->fetch_assoc()) {
    $month_index = array_search($row['month'], $month_names);
    if ($month_index === false) {
        continue; // Skip invalid month
    }
    $table_data[$month_index]['month_name'] = $month_names[$month_index];
    $table_data[$month_index]['metrics'][$row['column_title']] = $row['table_content'];
}

// Get the table_name from the first metric row for the sector
$table_name = '';
$result = $conn->query("SELECT table_name FROM sector_metrics_draft WHERE metric_id = $metric_id AND sector_id = $sector_id LIMIT 1");
if ($result && $row = $result->fetch_assoc()) {
    $table_name = $row['table_name'];
}

// Get unique metric names for column headers
$metric_names = [];
foreach ($table_data as $month_data) {  
    if (isset($month_data['metrics'])) {
        $metric_names = array_merge($metric_names, array_keys($month_data['metrics']));
    }
}
$metric_names = array_unique($metric_names);
sort($metric_names);

// Add CSS references
$additionalStyles = [
    APP_URL . '/assets/css/custom/metric-create.css'
];

// Include header
require_once '../layouts/header.php';

// Include agency navigation
require_once '../layouts/agency_nav.php';

// Set up the page header variables for dashboard_header.php
$title = "Create Sector Metrics";
$subtitle = "Define and manage your sector-specific metrics";
$headerStyle = 'light'; // Use light (white) style for inner pages
$actions = [
    [
        'url' => 'submit_metrics.php',
        'text' => 'Back to Metrics',
        'icon' => 'fa-arrow-left',
        'class' => 'btn-outline-primary'
    ]
];

// Include the dashboard header component
require_once '../../includes/dashboard_header.php';
?>

<div class="container-fluid px-4">
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-<?php echo $message_type === 'success' ? 'check-circle' : 'exclamation-circle'; ?> me-2"></i>
                <div><?php echo $message; ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title m-0">
                <i class="fas fa-table me-2"></i>Metric Table Definition
            </h5>
        </div>
        <div class="card-body">
            <form id="tableNameForm" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label for="tableNameInput" class="form-label">Table Name</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-signature"></i></span>
                            <input type="text" class="form-control" id="tableNameInput" 
                                   placeholder="Enter a descriptive name for this metric table" 
                                   value="<?= htmlspecialchars($table_name) ?>" required />
                            <button type="button" class="btn btn-primary" id="saveTableNameBtn">
                                <i class="fas fa-save me-1"></i> Save
                            </button>
                        </div>
                        <div class="form-text">Provide a clear, descriptive name for your metric table</div>
                    </div>
                    <div class="col-md-6 text-md-end mt-3 mt-md-0">
                        <button type="button" class="btn btn-info" id="addColumnBtn">
                            <i class="fas fa-plus-circle me-1"></i> Add Column
                        </button>
                        <button type="button" class="btn btn-success ms-2" id="doneBtn">
                            <i class="fas fa-check me-1"></i> Save & Finish
                        </button>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-hover metrics-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 150px;">Month</th>
                            <?php foreach ($metric_names as $name): ?>
                                <th>
                                    <div class="metric-header">
                                        <span class="metric-name" contenteditable="true" data-metric="<?= htmlspecialchars($name) ?>">
                                            <?= $name === '' ? '<span class="empty-value">Click to edit</span>' : htmlspecialchars($name) ?>
                                        </span>
                                        <button class="save-btn" data-metric="<?= htmlspecialchars($name) ?>">
                                            <i class="fas fa-check"></i>
                                        </button>
                                    </div>
                                </th>
                            <?php endforeach; ?>
                            <?php if (empty($metric_names)): ?>
                                <th class="text-center text-muted">
                                    <em>No metrics defined. Click "Add Column" to start.</em>
                                </th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($month_names as $month_name): ?>
                            <?php 
                                $month_index = array_search($month_name, $month_names);
                                $month_data = $table_data[$month_index] ?? ['month_name' => $month_name, 'metrics' => []];
                            ?>
                            <tr>
                                <td>
                                    <span class="month-badge"><?= $month_name ?></span>
                                </td>
                                <?php foreach ($metric_names as $name): ?>
                                    <td>
                                        <div class="metric-cell">
                                            <span class="metric-value" 
                                                contenteditable="true" 
                                                data-metric="<?= htmlspecialchars($name) ?>" 
                                                data-month="<?= $month_name ?>">
                                                <?= isset($month_data['metrics'][$name]) ? number_format($month_data['metrics'][$name], 2) : ' ' ?>
                                            </span>
                                            <button class="save-btn" data-metric="<?= htmlspecialchars($name) ?>" data-month="<?= $month_name ?>">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                                <?php if (empty($metric_names)): ?>
                                    <td></td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i> Click on any cell to edit its value. Click the check button to save changes.
            </small>
        </div>
    </div>
</div>

<?php
// Additional scripts for the page
$additionalScripts = [
    APP_URL . '/assets/js/metric-editor.js'
];
?>

<script>
    // Define variables needed by the metric-editor.js script
    const metricId = <?= json_encode($metric_id) ?>;
    const tableName = <?= json_encode($table_name) ?>;
    
    // Any remaining inline scripts that haven't been moved to metric-editor.js
    document.getElementById('addColumnBtn').addEventListener('click', handleAddColumn);
    document.getElementById('saveTableNameBtn').addEventListener('click', handleSaveTableName);
    document.getElementById('doneBtn').addEventListener('click', () => {
        window.location.href = 'submit_metrics.php';
    });
</script>

<?php
// Include footer
require_once '../layouts/footer.php';
?>
