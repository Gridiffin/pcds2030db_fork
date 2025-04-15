<?php
/**
 * Create Sector Metrics
 * 
 * Interface for agency users to create sectir-specific metrics
 */

// Include necessary files
require_once '../../config/config.php';
require_once '../../includes/db_connect.php';
require_once '../../includes/session.php';
require_once '../../includes/functions.php';
require_once '../../includes/agency_functions.php';

// Verify user is an agency
if (!is_agency_user()) {
    header('Location: ../../login.php');
    exit;
}

// Set page title
$pageTitle = 'Create Sector Metrics';

// Get metrics for the agency's sector
$metrics = get_agency_sector_metrics($_SESSION['sector_id']);
if (!is_array($metrics)) {
    $metrics = [];
}

// Handle form submission
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $conn->real_escape_string($_POST['metric_id']);
    $name = $conn->real_escape_string($_POST['metric_name']);
    $value = floatval($_POST['metric_value']);
    $month = $conn->real_escape_string($_POST['metric_month']);

    $query = "INSERT INTO sector_metrics_draft (metric_id, metric_name, metric_value, metric_month) 
            VALUES ('$id', '$name', '$value', '$month')";

    if ($conn->query($query) === TRUE) {
        $message = "Metric created successfully.";
        $message_type = "success";
    } else {
        $message = "Error: " . $conn->error;
        $message_type = "danger";
    }
}

$select_query = "SELECT metric_name, metric_value, metric_month AS month FROM sector_metrics_draft WHERE sector_id = '" . $conn->real_escape_string($_SESSION['sector_id']) . "'";
$metrics = $conn->query($select_query);
if (!$metrics) die("Error getting metrics: " . $conn->error);

// Organize data for display
$month_names = ['January', 'February', 'March', 'April', 'May', 'June', 
                'July', 'August', 'September', 'October', 'November', 'December'];
$table_data = [];
while ($row = $metrics->fetch_assoc()) {
    $month_index = $row['month'] - 1;
    if ($month_index < 0 || $month_index > 11) {
        // Skip invalid month index to avoid undefined array key warning
        continue;
    }
    $table_data[$month_index]['month_name'] = $month_names[$month_index];
    $table_data[$month_index]['metrics'][$row['metric_name']] = $row['metric_value'];
}
?>
<!DOCTYPE html>
<html>
    <head>
        <style>
            .metric-header, .metric-cell {
                    display: flex;
                    align-items: center;
                    gap: 5px;
                }
        </style>
    </head>
    <body>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h2 mb-0">Create New Sector Metrics</h1>
                <p class="text-muted">Create your sector-specific metrics</p>
            </div>
        </div>

        <div class="container">
            <h1>lorem ipsum</h1>

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
                                            <?= htmlspecialchars($name) ?>
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
                            metric_name: metric,
                            month: month,
                            new_value: newValue
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
                            metric_name: oldName,
                            new_name: newName
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
            data.append('metric_name', newMetricName);
            data.append('metric_value', '0');
            // Use current date as metric_month
            const currentDate = new Date();
            const year = currentDate.getFullYear();
            const month = currentDate.getMonth() + 1; // 0-based
            const day = 1;
            const metricDate = `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}`;
            data.append('metric_month', metricDate);

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
    </script>
</html>
