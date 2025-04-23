<?php
/**
 * Edit Sector Metric
 * 
 * Display all data in the selected metric and make them editable
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

$metric_id = isset($_GET['metric_id']) ? intval($_GET['metric_id']) : 0;
if ($metric_id === 0) {
    die('Invalid metric ID.');
}

$sector_id = $_SESSION['sector_id'];

// Set page title
$pageTitle = 'Edit Sector Metric';

// Retrieve all metrics for the selected metric_id
$select_query = "SELECT * FROM sector_metrics_draft WHERE metric_id = $metric_id AND sector_id = '$sector_id'";
$metrics = $conn->query($select_query);
if (!$metrics) die("Error getting metrics: " . $conn->error);

// Organize data for display
$month_names = ['January', 'February', 'March', 'April', 'May', 'June', 
                'July', 'August', 'September', 'October', 'November', 'December'];
$table_data = [];
while ($row = $metrics->fetch_assoc()) {
    $month_index = date('n', strtotime($row['month'])) - 1; // Get month index from month
    if ($month_index < 0 || $month_index > 11) {
        continue; // Skip invalid month index
    }
    $table_data[$month_index]['month_name'] = $month_names[$month_index];
    $table_data[$month_index]['metrics'][$row['column_title']] = $row['table_content'];
}

// Get the table_name from the first metric row for the sector
$table_name = '';
if (!empty($table_data)) {
    foreach ($table_data as $month_data) {
        if (!empty($month_data['metrics'])) {
            $result = $conn->query("SELECT table_name FROM sector_metrics_draft WHERE metric_id = $metric_id AND sector_id = '$sector_id' LIMIT 1");
            if ($result && $row = $result->fetch_assoc()) {
                $table_name = $row['table_name'];
            }
            break;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <link rel="stylesheet" type="text/css" href="../../assets/css/custom/metric-create.css">
</head>
<body>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-0"><?= htmlspecialchars($pageTitle) ?></h1> 
            <p class="text-muted">Edit your sector-specific metric data</p>
            <p><strong>Metric ID:</strong> <?= htmlspecialchars($metric_id) ?></p>
        </div>
    </div>

    <div class="container">
        <div class="d-flex align-items-center mb-3">
            <label for="tableNameInput" class="me-2 h4 mb-0">Table Name:</label>
            <input type="text" id="tableNameInput" value="<?= htmlspecialchars($table_name) ?>" />
            <button class="btn btn-primary ms-2" id="saveTableNameBtn">Save</button>
        </div>

        <!-- Add Column Button -->
        <button class="btn" id="addColumnBtn">Add Column</button>

        <!-- Sector Metrics Table -->
        <table>
            <thead>
                <tr>
                    <th>Month</th>
                    <?php
                        // Get unique metric names for column headers
                        $metric_names = [];
                        foreach ($table_data as $month_data) {  
                            if (isset($month_data['metrics'])) {
                                $metric_names = array_merge($metric_names, array_keys($month_data['metrics']));
                            }
                        }
                        $metric_names = array_unique($metric_names);
                        sort($metric_names);

                        foreach ($metric_names as $name): ?>
                        <th>
                            <div class="metric-header">
                                <span class="metric-name" contenteditable="true" data-metric="<?= htmlspecialchars($name) ?>">
                                    <?= $name === '' ? 'click here to edit name' : htmlspecialchars($name) ?>
                                </span>
                                <button class="save-btn" 
                                        data-metric="<?= htmlspecialchars($name) ?>"> ✓ </button>
                            </div>
                        </th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php
                    // Ensure all months are shown, when there is data or no data
                    foreach ($month_names as $month_name):
                        $month_index = array_search($month_name, $month_names);
                        $month_data = $table_data[$month_index] ?? ['month_name' => $month_name, 'metrics' => []];
                ?>
                <tr>
                    <td><?= $month_name ?></td>
                    <?php foreach ($metric_names as $name): ?>
                        <td>
                            <div class="metric-cell">
                                <span class="metric-value" 
                                    contenteditable="true" 
                                    data-metric="<?= htmlspecialchars($name) ?>" 
                                    data-month="<?= $month_name ?>">
                                    <?= isset($month_data['metrics'][$name]) ? number_format($month_data['metrics'][$name], 2) : ' ' ?>
                                </span>
                                <button class="save-btn" data-metric="<?= htmlspecialchars($name) ?>" data-month="<?= $month_name ?>"> ✓ </button>
                            </div>
                        </td>
                    <?php endforeach; ?>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
<script>
    // Show save button when metric value is edited
    document.querySelectorAll('.metric-value').forEach(cell => {
        cell.addEventListener('input', function() {
            const btn = this.parentElement.querySelector('.save-btn');
            if (btn) btn.style.display = 'inline-block';
        });
    });

    // Show save button when metric name is edited
    document.querySelectorAll('.metric-name').forEach(cell => {
        cell.addEventListener('input', function() {
            const btn = this.parentElement.querySelector('.save-btn');
            if (btn) btn.style.display = 'inline-block';
        });
    });

    // Handle metric value saves
    document.querySelectorAll('.save-btn[data-month]').forEach(btn => {
        btn.addEventListener('click', async function() {
            const cell = this.parentElement.querySelector('.metric-value');
            const metric = cell.dataset.metric;
            const month = cell.dataset.month;
            const newValue = parseFloat(cell.textContent) || 0;
            
            try {
                const response = await fetch('update_metric.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        column_title: metric,
                        month: month,
                        new_value: newValue,
                        metric_id: <?= json_encode($metric_id) ?>,
                        table_name: <?= json_encode($table_name) ?>
                    })
                });
                
                if (!response.ok) throw new Error('Update failed');
                cell.textContent = newValue.toFixed(2);
                this.textContent = '✓';
                this.style.display = 'none';
            } catch (error) {
                alert('Error updating value: ' + error.message);
                const response = await fetch(`get_metric_value.php?metric=${metric}&month=${month}`);
                const data = await response.json();
                cell.textContent = data.value.toFixed(2);
            }
        });
    });

    // Handle metric name saves
    document.querySelectorAll('.save-btn:not([data-month])').forEach(btn => {
        btn.addEventListener('click', async function() {
            const cell = this.parentElement.querySelector('.metric-name');
            const oldName = cell.dataset.metric;
            const newName = cell.textContent.trim();
            
            if (newName === oldName) return;
            
            try {
                const response = await fetch('update_metric.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        column_title: oldName,
                        new_name: newName,
                        metric_id: <?= json_encode($metric_id) ?>,
                        table_name: <?= json_encode($table_name) ?>
                    })
                });
                
                if (!response.ok) throw new Error('Update failed');
                cell.dataset.metric = newName;
                this.textContent = '✓';
                this.style.display = 'none';
                // Update all corresponding value cells
                document.querySelectorAll(`.metric-value[data-metric="${oldName}"]`)
                    .forEach(cell => cell.dataset.metric = newName);
            } catch (error) {
                alert('Error updating metric name: ' + error.message);
                cell.textContent = oldName;
            }
        });
    });

    // Add Column button handler
    document.getElementById('addColumnBtn').addEventListener('click', async () => {
        const newMetricName = prompt('Enter new metric name:');
        if (!newMetricName) return;

        // Prepare data for POST request
        const data = new URLSearchParams();
        data.append('column_title', newMetricName);
        data.append('table_content', '');
        // Use current date as month
        const currentDate = new Date();
        const month = 'January'; // placeholder for month name
        data.append('month', month);

        try {
            const response = await fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: data.toString()
            });
            if (!response.ok) throw new Error('Failed to add new metric column');
            alert('New metric column added successfully.');
            location.reload();
        } catch (error) {
            alert('Error adding new metric column: ' + error.message);
        }
    });

    // Make entire metric-cell div clickable to focus the editable span.metric-value inside
    document.querySelectorAll('.metric-cell').forEach(cell => {
        cell.addEventListener('click', function(event) {
            if (event.target.classList.contains('save-btn') || event.target.classList.contains('metric-value')) {
                return;
            }
            const editableSpan = this.querySelector('.metric-value');
            if (editableSpan) {
                editableSpan.focus();
                const range = document.createRange();
                const sel = window.getSelection();
                range.selectNodeContents(editableSpan);
                range.collapse(false);
                sel.removeAllRanges();
                sel.addRange(range);
            }
        });
    });

    // Save table name button handler
    document.getElementById('saveTableNameBtn').addEventListener('click', async () => {
        const tableNameInput = document.getElementById('tableNameInput');
        const newTableName = tableNameInput.value.trim();
        if (!newTableName) {
            alert('Table name cannot be empty.');
            return;
        }

        // Prepare form data
        const formData = new FormData();
        formData.append('table_name', newTableName);

        try {
            const response = await fetch('update_metric.php', {
                method: 'POST',
                body: formData
            });
            if (!response.ok) throw new Error('Failed to update table name');
            alert('Table name updated successfully.');
            location.reload();
        } catch (error) {
            alert('Error updating table name: ' + error.message);
        }
    });
</script>
</html>
