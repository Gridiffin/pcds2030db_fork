<?php
/**
 * Edit Sector Metrics
 * 
 * Admin interface to edit sector-specific metrics
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

// Set page title
$pageTitle = 'Edit Sector Metrics';

// Initialize variables
$message = '';
$message_type = '';
$metric_id = isset($_GET['metric_id']) ? intval($_GET['metric_id']) : 0;
$sector_id = isset($_GET['sector_id']) ? intval($_GET['sector_id']) : 0;

// If we have a metric_id, get the sector_id from the metric
if ($metric_id > 0) {
    $metric_data = get_metric_data($metric_id);
    if ($metric_data) {
        $sector_id = $metric_data['sector_id'];
    }
}

// If we still don't have a sector_id and this is a new metric, we need to select a sector
$select_sector = false;
if ($metric_id === 0 && $sector_id === 0) {
    $select_sector = true;
    $sectors = get_all_sectors();
}

// Handle form submission for sector selection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['select_sector'])) {
    $sector_id = intval($_POST['sector_id']);
    if ($sector_id > 0) {
        // Get max metric_id from sector_metrics_data table
        $max_metric_id = 0;
        $max_query = "SELECT MAX(metric_id) AS max_id FROM sector_metrics_data";
        $max_stmt = $conn->prepare($max_query);
        if ($max_stmt) {
            $max_stmt->execute();
            $max_result = $max_stmt->get_result();
            if ($max_result && $max_result->num_rows > 0) {
                $row = $max_result->fetch_assoc();
                $max_metric_id = intval($row['max_id']);
            }
            $max_stmt->close();
        }
        $new_metric_id = $max_metric_id + 1;

        // Create empty data_json placeholder
        $metrics_data = [
            'columns' => [],
            'units' => [],
            'data' => [
                'January' => [],
                'February' => [],
                'March' => [],
                'April' => [],
                'May' => [],
                'June' => [],
                'July' => [],
                'August' => [],
                'September' => [],
                'October' => [],
                'November' => [],
                'December' => []
            ]
        ];
        $json_data = json_encode($metrics_data);

        // Insert new metric row with new_metric_id, sector_id, empty table_name, data_json, is_draft=0
        $insert_query = "INSERT INTO sector_metrics_data (metric_id, sector_id, table_name, data_json, is_draft) VALUES (?, ?, '', ?, 0)";
        $insert_stmt = $conn->prepare($insert_query);
        if ($insert_stmt) {
            $insert_stmt->bind_param("iis", $new_metric_id, $sector_id, $json_data);
            $insert_stmt->execute();
            $insert_stmt->close();
        }

        header("Location: edit_metric.php?sector_id=$sector_id&metric_id=$new_metric_id");
        exit;
    } else {
        $message = "Please select a valid sector.";
        $message_type = "danger";
    }
}

// Handle form submission for table name update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['table_name']) && trim($_POST['table_name']) !== '') {
    $table_name = trim($_POST['table_name']);
    
    if ($metric_id > 0) {
        // Update existing metric
        $metric_data = get_metric_data($metric_id);
        if ($metric_data) {
            $data_json = $metric_data['data_json'];
            
            // Update table name in database
            $update_query = "UPDATE sector_metrics_data 
                            SET table_name = ? 
                            WHERE metric_id = ? AND is_draft = 0";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $table_name, $metric_id);
            
            if ($update_stmt->execute()) {
                $message = "Table name updated successfully.";
                $message_type = "success";
            } else {
                $message = "Error updating table name: " . $conn->error;
                $message_type = "danger";
            }
        }
    } else {
        // Create new metric with empty data structure
        $metrics_data = [
            'columns' => [],
            'units' => [],
            'data' => []
        ];
        
        // Initialize months
        $months = ['January', 'February', 'March', 'April', 'May', 'June', 
                  'July', 'August', 'September', 'October', 'November', 'December'];
        
        foreach ($months as $m) {
            $metrics_data['data'][$m] = [];
        }
        
        // Save as JSON
        $json_data = json_encode($metrics_data);
        
        // Insert new metric
        $insert_query = "INSERT INTO sector_metrics_data 
                        (sector_id, table_name, data_json, is_draft) 
                        VALUES (?, ?, ?, 0)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("iss", $sector_id, $table_name, $json_data);
        
        if ($insert_stmt->execute()) {
            $metric_id = $conn->insert_id;
            $message = "New metric table created successfully.";
            $message_type = "success";
            
            // Redirect to the edit page for the new metric
            header("Location: edit_metric.php?metric_id=$metric_id");
            exit;
        } else {
            $message = "Error creating new metric: " . $conn->error;
            $message_type = "danger";
        }
    }
}

// Get metric data for display if we have a metric_id
$table_name = '';
$metric_names = [];
$month_names = ['January', 'February', 'March', 'April', 'May', 'June', 
                'July', 'August', 'September', 'October', 'November', 'December'];
$table_data = [];
$metrics_data = [];
$sector_name = '';

if ($metric_id > 0) {
    $metric_data = get_metric_data($metric_id);
    
    if ($metric_data) {
        $table_name = $metric_data['table_name'];
        $sector_id = $metric_data['sector_id'];
        $sector_name = $metric_data['sector_name'];
        $metrics_data = json_decode($metric_data['data_json'], true);
        
        // Get column names
        $metric_names = $metrics_data['columns'] ?? [];
        
        // Ensure units array exists
        if (!isset($metrics_data['units'])) {
            $metrics_data['units'] = [];
        }
        
        // Organize data for display
        foreach ($month_names as $month_name) {
            $month_data = ['month_name' => $month_name, 'metrics' => []];
            
            // Add data for each metric in this month
            if (isset($metrics_data['data'][$month_name])) {
                $month_data['metrics'] = $metrics_data['data'][$month_name];
            }
            
            $table_data[] = $month_data;
        }
    }
} else if ($sector_id > 0) {
    // Get sector name
    $sector_query = "SELECT sector_name FROM sectors WHERE sector_id = ?";
    $sector_stmt = $conn->prepare($sector_query);
    $sector_stmt->bind_param("i", $sector_id);
    $sector_stmt->execute();
    $sector_result = $sector_stmt->get_result();
    
    if ($sector_result->num_rows > 0) {
        $sector_row = $sector_result->fetch_assoc();
        $sector_name = $sector_row['sector_name'];
    }
    
    // Initialize empty table_data for a new metric
    foreach ($month_names as $month_name) {
        $table_data[] = ['month_name' => $month_name, 'metrics' => []];
    }
}

// Add CSS references
$additionalStyles = [
    APP_URL . '/assets/css/custom/metric-create.css'
];

// Add JS references
$additionalScripts = [
    APP_URL . '/assets/js/metric-editor.js'
];

// Include header
require_once '../layouts/header.php';

// Include admin navigation
require_once '../layouts/admin_nav.php';

// Set up the page header variables
$title = $metric_id > 0 ? "Edit Sector Metrics" : "Create New Metric";
$subtitle = $metric_id > 0 
    ? "Edit metrics for " . htmlspecialchars($sector_name) . " sector" 
    : "Create a new metric table" . ($sector_id > 0 ? " for " . htmlspecialchars($sector_name) . " sector" : "");
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
<?php echo $metric_id?>
<div class="container-fluid px-4 py-4">
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
            <?= $message ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($select_sector): ?>
        <!-- Sector Selection Form -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title m-0">Select Sector for New Metric</h5>
            </div>
            <div class="card-body">
                <form method="post" class="row g-3">
                    <div class="col-md-6">
                        <label for="sector_id" class="form-label">Sector</label>
                        <select name="sector_id" id="sector_id" class="form-select" required>
                            <option value="">-- Select Sector --</option>
                            <?php foreach ($sectors as $sector): ?>
                                <option value="<?= $sector['sector_id'] ?>">
                                    <?= htmlspecialchars($sector['sector_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" name="select_sector" class="btn btn-primary">Continue</button>
                    </div>
                </form>
            </div>
        </div>
    <?php elseif ($sector_id > 0): ?>
        <!-- Metric Editor -->
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">
                    <?= $metric_id > 0 ? 'Edit Metrics Table' : 'Create New Metrics Table' ?>
                </h5>
                <div>
                    <button class="btn btn-sm btn-success" id="doneBtn">
                        <i class="fas fa-check me-1"></i> Done
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">Table Name</span>
                            <input type="text" class="form-control" id="tableNameInput" value="<?= htmlspecialchars($table_name) ?>" />
                            <button class="btn btn-primary" id="saveTableNameBtn">Save</button>
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group">
                            <button class="btn btn-primary" id="addColumnBtn">
                                <i class="fas fa-plus me-1"></i> Add Column
                            </button>
                            <button class="btn btn-outline-secondary" id="setAllUnitsBtn">
                                <i class="fas fa-ruler me-1"></i> Set All Units
                            </button>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover metrics-table">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 150px;">Month</th>
                                <?php foreach ($metric_names as $name): ?>
                                    <th>
                                        <div class="metric-header">
                                            <div class="metric-title">
                                                <span class="metric-name" contenteditable="true" data-metric="<?= htmlspecialchars($name) ?>">
                                                    <?= htmlspecialchars($name) ?>
                                                </span>
                                                <?php if (isset($metrics_data['units'][$name])): ?>
                                                <span class="metric-unit-display">
                                                    (<?= htmlspecialchars($metrics_data['units'][$name]) ?>)
                                                </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="metric-actions">
                                                <button class="unit-btn" data-metric="<?= htmlspecialchars($name) ?>" 
                                                        data-current-unit="<?= htmlspecialchars($metrics_data['units'][$name] ?? '') ?>">
                                                    <i class="fas fa-ruler"></i>
                                                </button>
                                                <button class="save-btn" data-metric="<?= htmlspecialchars($name) ?>">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                                <button class="delete-column-btn" data-metric="<?= htmlspecialchars($name) ?>">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </div>
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
                            <?php foreach ($table_data as $month_data): ?>
                                <tr>
                                    <td>
                                        <span class="month-badge"><?= $month_data['month_name'] ?></span>
                                    </td>
                                    <?php foreach ($metric_names as $name): ?>
                                        <td>
                                            <div class="metric-cell">
                                                <span class="metric-value" 
                                                    contenteditable="true" 
                                                    data-metric="<?= htmlspecialchars($name) ?>" 
                                                    data-month="<?= $month_data['month_name'] ?>">
                                                    <?= isset($month_data['metrics'][$name]) ? number_format($month_data['metrics'][$name], 2) : ' ' ?>
                                                </span>
                                                <button class="save-btn" data-metric="<?= htmlspecialchars($name) ?>" data-month="<?= $month_data['month_name'] ?>">
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
                            
                            <!-- Total Row -->
                            <?php if (!empty($metric_names)): ?>
                            <tr class="table-light font-weight-bold">
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
            <div class="card-footer">
                <small class="text-muted">
                    <i class="fas fa-info-circle me-1"></i> Click on any cell to edit its value. Click the check button to save changes.
                </small>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    // Define variables needed by the metric-editor.js script
    const metricId = <?= json_encode($metric_id) ?>;
    const sectorId = <?= json_encode($sector_id) ?>;
    const isAdmin = true;
    
    document.addEventListener('DOMContentLoaded', function() {
        // Done button returns to metrics list
        document.getElementById('doneBtn')?.addEventListener('click', function() {
            window.location.href = 'manage_metrics.php';
        });
        
        // If not using metric-editor.js, implement inline JS here
        if (typeof initializeMetricEditor !== 'function') {
            console.warn('Metric editor JS not loaded, using inline fallback');
            
            // Save table name button handler
            document.getElementById('saveTableNameBtn')?.addEventListener('click', async () => {
                const tableNameInput = document.getElementById('tableNameInput');
                const newTableName = tableNameInput.value.trim();
                if (!newTableName) {
                    alert('Table name cannot be empty.');
                    return;
                }

                // Create form data
                const formData = new FormData();
                formData.append('table_name', newTableName);

                try {
                    const response = await fetch('', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (response.ok) {
                        alert('Table name saved successfully.');
                        location.reload();
                    } else {
                        alert('Error saving table name.');
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            });
            
            // Add Column button handler
            document.getElementById('addColumnBtn')?.addEventListener('click', async () => {
                const newName = prompt('Enter name for new metric column:');
                if (!newName) return;

                try {
                    const response = await fetch('update_metric.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            metric_id: metricId,
                            action: 'add_column',
                            new_name: newName
                        })
                    });

                    if (response.ok) {
                        alert('New column added successfully.');
                        location.reload();
                    } else {
                        alert('Error adding new column.');
                    }
                } catch (error) {
                    alert('Error: ' + error.message);
                }
            });
        }
    });
</script>
</html>