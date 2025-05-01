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
        <!-- Units modal -->
        <div class="modal fade" id="unitsModal" tabindex="-1" aria-labelledby="unitsModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="unitsModalLabel">Set Unit of Measurement</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="unitInput" class="form-label">Unit</label>
                            <input type="text" class="form-control" id="unitInput" placeholder="e.g., kg, ha, $, %, etc.">
                        </div>
                        <p class="text-muted small">Enter the unit of measurement for this metric.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="saveUnitBtn">Save</button>
                    </div>
                </div>
            </div>
        </div>
        
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
    let tableName = <?= json_encode($table_name) ?>;
    let currentMetricForUnit = ''; // Track which metric we're setting units for units modal
    
    document.addEventListener('DOMContentLoaded', function() {
        // If metric-editor.js successfully loaded, it will call initializeMetricEditor()
        if (typeof initializeMetricEditor === 'function') {
            initializeMetricEditor();
        } else {
            // Fallback to inline implementation if metric-editor.js failed to load
            console.warn('Metric editor JS not loaded, using inline fallback');
            setupInlineMetricEditor();
        }
        
        // Done button returns to metrics list - always needed
        document.getElementById('doneBtn')?.addEventListener('click', function() {
            window.location.href = 'manage_metrics.php';
        });
    });
    
    // Setup inline metric editor if the external JS file fails to load
    function setupInlineMetricEditor() {
        // Setup metric value cells
        setupMetricValueCells();
        
        // Setup metric name cells
        setupMetricNameCells();
        
        // Setup button handlers
        setupButtonHandlers();
        
        // Make cells more clickable
        makeMetricCellsClickable();
    }
    
    // Setup handlers for metric value cells
    function setupMetricValueCells() {
        document.querySelectorAll('.metric-value').forEach(cell => {
            cell.addEventListener('input', function() {
                const btn = this.parentElement.querySelector('.save-btn');
                if (btn) btn.style.display = 'inline-block';
            });
            
            cell.addEventListener('blur', function() {
                // Format numeric values on blur
                if (!isNaN(parseFloat(this.textContent))) {
                    this.textContent = parseFloat(this.textContent).toFixed(2);
                }
            });
        });
    }
    
    // Setup handlers for metric name cells
    function setupMetricNameCells() {
        document.querySelectorAll('.metric-name').forEach(cell => {
            cell.addEventListener('input', function() {
                const btn = this.closest('.metric-header').querySelector('.save-btn');
                if (btn) btn.style.display = 'inline-block';
            });
        });
    }
    
    // Setup all button handlers
    function setupButtonHandlers() {
        // Save table name button handler
        document.getElementById('saveTableNameBtn')?.addEventListener('click', handleSaveTableName);
        
        // Add Column button handler
        document.getElementById('addColumnBtn')?.addEventListener('click', handleAddColumn);
        
        // Set All Units button handler
        document.getElementById('setAllUnitsBtn')?.addEventListener('click', handleSetAllUnits);
        
        // Setup Unit buttons
        document.querySelectorAll('.unit-btn').forEach(btn => {
            btn.addEventListener('click', handleUnitButtonClick);
        });
        
        // Set up save buttons for metric values
        document.querySelectorAll('.save-btn[data-month]').forEach(btn => {
            btn.addEventListener('click', handleSaveMetricValue);
        });
        
        // Set up save buttons for metric names
        document.querySelectorAll('.save-btn:not([data-month])').forEach(btn => {
            btn.addEventListener('click', handleSaveMetricName);
        });
        
        // Set up delete column buttons
        document.querySelectorAll('.delete-column-btn').forEach(btn => {
            btn.addEventListener('click', handleDeleteColumn);
        });
        
        // Set up save unit button in modal
        document.getElementById('saveUnitBtn')?.addEventListener('click', handleSaveUnit);
    }
    
    // Make entire metric cell clickable
    function makeMetricCellsClickable() {
        document.querySelectorAll('.metric-cell').forEach(cell => {
            cell.addEventListener('click', function(event) {
                // Skip if clicking on a button or the editable value itself
                if (event.target.classList.contains('save-btn') || 
                    event.target.classList.contains('metric-value') ||
                    event.target.tagName === 'I') {
                    return;
                }
                
                const editableSpan = this.querySelector('.metric-value');
                if (editableSpan) {
                    editableSpan.focus();
                    placeCursorAtEnd(editableSpan);
                }
            });
        });
    }
    
    // Place cursor at the end of content
    function placeCursorAtEnd(element) {
        const range = document.createRange();
        const sel = window.getSelection();
        range.selectNodeContents(element);
        range.collapse(false);
        sel.removeAllRanges();
        sel.addRange(range);
    }
    
    // Handle saving table name
    async function handleSaveTableName() {
        const tableNameInput = document.getElementById('tableNameInput');
        const newTableName = tableNameInput.value.trim();
        
        if (!newTableName) {
            showToast('Table name cannot be empty', 'warning');
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
                showToast('Table name saved successfully', 'success');
                tableName = newTableName; // Update global variable
            } else {
                throw new Error('Failed to save table name');
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'danger');
        }
    }
    
    // Handle adding a new column
    async function handleAddColumn() {
        const newName = prompt('Enter name for new metric column:');
        if (!newName || newName.trim() === '') return;

        try {
            // Create new column in UI first
            addColumnToUI(newName);
            
            // Then save to database
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

            if (!response.ok) {
                throw new Error('Failed to add column');
            }
            
            showToast(`Column "${newName}" added successfully`, 'success');
        } catch (error) {
            showToast('Error adding column: ' + error.message, 'danger');
        }
    }
    
    // Add column to UI
    function addColumnToUI(columnName) {
        // Add to table header
        const tableHead = document.querySelector('.metrics-table thead tr');
        const tableRows = document.querySelectorAll('.metrics-table tbody tr:not(.table-light)'); // Skip total row
        
        // Create new header cell
        const newTh = document.createElement('th');
        newTh.innerHTML = `
            <div class="metric-header">
                <div class="metric-title">
                    <span class="metric-name" contenteditable="true" data-metric="${columnName}">
                        ${columnName}
                    </span>
                    <span class="metric-unit-display"></span>
                </div>
                <div class="metric-actions">
                    <button class="unit-btn" data-metric="${columnName}" data-current-unit="">
                        <i class="fas fa-ruler"></i>
                    </button>
                    <button class="save-btn" data-metric="${columnName}">
                        <i class="fas fa-check"></i>
                    </button>
                    <button class="delete-column-btn" data-metric="${columnName}">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>`;
        
        // Remove placeholder if present
        const placeholder = tableHead.querySelector('th.text-muted');
        if (placeholder) {
            placeholder.remove();
        }
        
        tableHead.appendChild(newTh);
        
        // Add cells to all data rows
        tableRows.forEach(row => {
            const monthName = row.querySelector('.month-badge').textContent;
            const newTd = document.createElement('td');
            
            // Remove placeholder empty cell if present
            if (row.cells.length === 2 && !row.cells[1].querySelector('.metric-cell')) {
                row.cells[1].remove();
            }
            
            newTd.innerHTML = `
                <div class="metric-cell">
                    <span class="metric-value" 
                        contenteditable="true" 
                        data-metric="${columnName}" 
                        data-month="${monthName}">
                        0.00
                    </span>
                    <button class="save-btn" data-metric="${columnName}" data-month="${monthName}">
                        <i class="fas fa-check"></i>
                    </button>
                </div>`;
            
            row.appendChild(newTd);
        });
        
        // Add to total row if it exists
        const totalRow = document.querySelector('.metrics-table tbody tr.table-light');
        if (totalRow) {
            const totalTd = document.createElement('td');
            totalTd.className = 'fw-bold text-end';
            totalTd.textContent = '0.00';
            totalRow.appendChild(totalTd);
        } else {
            // Create total row if it doesn't exist
            createTotalRow();
        }
        
        // Setup event handlers for the new elements
        setupInlineMetricEditor();
    }
    
    // Create total row
    function createTotalRow() {
        const tableBody = document.querySelector('.metrics-table tbody');
        const columnCount = document.querySelectorAll('.metrics-table thead th').length;
        
        if (columnCount <= 1) return; // No metrics columns yet
        
        const totalRow = document.createElement('tr');
        totalRow.className = 'table-light font-weight-bold';
        
        // Add "TOTAL" cell
        const totalLabelCell = document.createElement('td');
        totalLabelCell.className = 'fw-bold';
        totalLabelCell.innerHTML = '<span class="total-badge">TOTAL</span>';
        totalRow.appendChild(totalLabelCell);
        
        // Add value cells for each metric
        const metricNames = [];
        document.querySelectorAll('.metric-name').forEach(el => {
            metricNames.push(el.dataset.metric);
        });
        
        metricNames.forEach(name => {
            const totalCell = document.createElement('td');
            totalCell.className = 'fw-bold text-end';
            totalCell.textContent = '0.00';
            totalRow.appendChild(totalCell);
        });
        
        tableBody.appendChild(totalRow);
    }
    
    // Handle unit button click
    function handleUnitButtonClick() {
        // Store the metric name for the modal
        currentMetricForUnit = this.dataset.metric;
        
        // Get current unit if any
        const currentUnit = this.dataset.currentUnit || '';
        
        // Set the input value in the modal
        document.getElementById('unitInput').value = currentUnit;
        
        // Show the modal - using getElementById to ensure we have the element
        const unitsModalEl = document.getElementById('unitsModal');
        if (unitsModalEl) {
            // Check if Bootstrap is available
            if (typeof bootstrap !== 'undefined') {
                const unitsModal = new bootstrap.Modal(unitsModalEl);
                unitsModal.show();
            } else {
                // Fallback if bootstrap isn't available
                console.log('Bootstrap not loaded, showing modal with direct DOM manipulation');
                unitsModalEl.classList.add('show');
                unitsModalEl.style.display = 'block';
                document.body.classList.add('modal-open');
                
                // Create backdrop
                const backdrop = document.createElement('div');
                backdrop.className = 'modal-backdrop fade show';
                document.body.appendChild(backdrop);
            }
        } else {
            console.error('Units modal element not found');
        }
    }
    
    // Handle saving unit button in modal
    async function handleSaveUnit() {
        const unitInput = document.getElementById('unitInput');
        const newUnit = unitInput.value.trim();
        
        if (!currentMetricForUnit) return;
        
        try {
            // Send unit update request
            const response = await fetch('update_metric.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    column_title: currentMetricForUnit,
                    unit: newUnit,
                    metric_id: metricId
                })
            });
            
            if (!response.ok) throw new Error('Failed to update unit');
            
            // Update unit display in UI
            updateUnitDisplay(currentMetricForUnit, newUnit);
            
            // Update data attribute on the button
            const unitBtn = document.querySelector(`.unit-btn[data-metric="${currentMetricForUnit}"]`);
            if (unitBtn) {
                unitBtn.dataset.currentUnit = newUnit;
            }
            
            // Close the modal safely
            try {
                const unitsModalEl = document.getElementById('unitsModal');
                if (typeof bootstrap !== 'undefined') {
                    const modalInstance = bootstrap.Modal.getInstance(unitsModalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                } else {
                    // Fallback to direct DOM manipulation if bootstrap is not available
                    unitsModalEl.classList.remove('show');
                    unitsModalEl.style.display = 'none';
                    document.body.classList.remove('modal-open');
                    
                    // Remove backdrop if it exists
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) backdrop.remove();
                }
            } catch (modalError) {
                console.warn('Could not properly close modal:', modalError);
            }
            
            showToast(`Unit for "${currentMetricForUnit}" updated successfully`, 'success');
        } catch (error) {
            showToast('Error updating unit: ' + error.message, 'danger');
        }
    }
    
    // Update unit display
    function updateUnitDisplay(metricName, unit) {
        // Find the unit display element next to the metric name with matching data-metric
        const metricNameEl = document.querySelector(`.metric-name[data-metric="${metricName}"]`);
        if (metricNameEl) {
            // Get the parent metric-title div
            const metricTitleEl = metricNameEl.closest('.metric-title');
            if (metricTitleEl) {
                // Look for existing unit display
                let unitDisplay = metricTitleEl.querySelector('.metric-unit-display');
                
                if (unit) {
                    // If unit exists, update or create unit display
                    if (!unitDisplay) {
                        // Create new unit display element if it doesn't exist
                        unitDisplay = document.createElement('span');
                        unitDisplay.className = 'metric-unit-display';
                        metricTitleEl.appendChild(unitDisplay);
                    }
                    unitDisplay.textContent = `(${unit})`;
                } else if (unitDisplay) {
                    // If no unit and display exists, remove it
                    unitDisplay.textContent = '';
                }
            }
        }
    }
    
    // Handle setting units for all columns
    async function handleSetAllUnits() {
        // Prompt for unit value
        const newUnit = prompt('Enter unit of measurement for all columns:');
        
        // User canceled
        if (newUnit === null) return;
        
        try {
            // Get all column names
            const metricNames = [];
            document.querySelectorAll('.metric-name').forEach(el => {
                const metric = el.dataset.metric;
                if (metric && !metricNames.includes(metric)) {
                    metricNames.push(metric);
                }
            });
            
            if (metricNames.length === 0) {
                showToast('No metrics found to update', 'warning');
                return;
            }
            
            // Update units for all columns
            let successCount = 0;
            
            for (const metric of metricNames) {
                // Send unit update request for each column
                const response = await fetch('update_metric.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        column_title: metric,
                        unit: newUnit,
                        metric_id: metricId
                    })
                });
                
                if (response.ok) {
                    successCount++;
                    
                    // Update unit display in UI
                    updateUnitDisplay(metric, newUnit);
                    
                    // Update data attribute on the button
                    const unitBtn = document.querySelector(`.unit-btn[data-metric="${metric}"]`);
                    if (unitBtn) {
                        unitBtn.dataset.currentUnit = newUnit;
                    }
                }
            }
            
            if (successCount === metricNames.length) {
                showToast(`Unit updated for all ${successCount} columns`, 'success');
            } else {
                showToast(`Updated ${successCount} of ${metricNames.length} columns`, 'warning');
            }
        } catch (error) {
            showToast('Error updating units: ' + error.message, 'danger');
        }
    }
    
    // Handle saving metric values
    async function handleSaveMetricValue() {
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
                    metric_id: metricId
                })
            });
            
            if (!response.ok) throw new Error('Update failed');
            
            // Format the value properly
            cell.textContent = newValue.toFixed(2);
            
            // Hide the save button
            this.style.display = 'none';
            
            // Update the total row
            updateTotalRow(metric);
            
            showToast('Value updated successfully', 'success');
        } catch (error) {
            showToast('Error updating value: ' + error.message, 'danger');
        }
    }
    
    // Update total row for a specific metric
    function updateTotalRow(metricName) {
        // Find index of this metric column
        const metricIndex = findColumnIndex(metricName);
        if (metricIndex === -1) return;
        
        // Calculate new total
        let total = 0;
        document.querySelectorAll(`.metric-value[data-metric="${metricName}"]`).forEach(cell => {
            total += parseFloat(cell.textContent) || 0;
        });
        
        // Update total cell
        const totalRow = document.querySelector('.metrics-table tbody tr.table-light');
        if (totalRow && totalRow.cells[metricIndex]) {
            totalRow.cells[metricIndex].textContent = total.toFixed(2);
        }
    }
    
    // Handle saving metric names
    async function handleSaveMetricName() {
        const cell = this.closest('.metric-header').querySelector('.metric-name');
        const oldName = cell.dataset.metric;
        const newName = cell.textContent.trim();
        
        if (!newName) {
            showToast('Metric name cannot be empty', 'warning');
            cell.textContent = oldName;
            return;
        }
        
        if (newName === oldName) {
            this.style.display = 'none';
            return;
        }
        
        try {
            const response = await fetch('update_metric.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    column_title: oldName,
                    new_name: newName,
                    metric_id: metricId
                })
            });
            
            if (!response.ok) throw new Error('Failed to update metric name');
            
            // Update all corresponding data attributes
            cell.dataset.metric = newName;
            
            // Update all value cells
            document.querySelectorAll(`.metric-value[data-metric="${oldName}"]`).forEach(valueCell => {
                valueCell.dataset.metric = newName;
            });
            
            // Update all save buttons
            document.querySelectorAll(`.save-btn[data-metric="${oldName}"]`).forEach(btn => {
                btn.dataset.metric = newName;
            });
            
            // Update all unit buttons
            document.querySelectorAll(`.unit-btn[data-metric="${oldName}"]`).forEach(btn => {
                btn.dataset.metric = newName;
            });
            
            // Update all delete buttons
            document.querySelectorAll(`.delete-column-btn[data-metric="${oldName}"]`).forEach(btn => {
                btn.dataset.metric = newName;
            });
            
            // Update all unit displays
            document.querySelectorAll(`.metric-unit-display[data-metric="${oldName}"]`).forEach(span => {
                span.dataset.metric = newName;
            });
            
            // Hide the save button
            this.style.display = 'none';
            
            showToast('Metric name updated successfully', 'success');
        } catch (error) {
            showToast('Error updating metric name: ' + error.message, 'danger');
            cell.textContent = oldName; // Revert to original name
        }
    }
    
    // Handle deleting a column
    async function handleDeleteColumn() {
        const metric = this.dataset.metric;
        
        if (!metric) return;
        
        // Confirm deletion
        if (!confirm(`Are you sure you want to delete the "${metric}" column? This action cannot be undone.`)) {
            return;
        }
        
        try {
            const response = await fetch('update_metric.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'delete_column',
                    column_title: metric,
                    metric_id: metricId
                })
            });
            
            if (!response.ok) throw new Error('Failed to delete column');
            
            // Remove column from DOM
            const columnIndex = findColumnIndex(metric);
            if (columnIndex !== -1) {
                removeColumnFromTable(columnIndex);
                showToast(`Column "${metric}" deleted successfully`, 'success');
            }
        } catch (error) {
            showToast('Error deleting column: ' + error.message, 'danger');
        }
    }
    
    // Find index of column by metric name
    function findColumnIndex(metricName) {
        const headers = document.querySelectorAll('.metrics-table thead th');
        
        for (let i = 0; i < headers.length; i++) {
            const nameEl = headers[i].querySelector(`.metric-name[data-metric="${metricName}"]`);
            if (nameEl) {
                return i;
            }
        }
        
        return -1;
    }
    
    // Remove column from table by index
    function removeColumnFromTable(columnIndex) {
        // Remove header
        const headerRow = document.querySelector('.metrics-table thead tr');
        if (headerRow && headerRow.children[columnIndex]) {
            headerRow.children[columnIndex].remove();
        }
        
        // Remove cells from all rows
        const rows = document.querySelectorAll('.metrics-table tbody tr');
        rows.forEach(row => {
            if (row.children[columnIndex]) {
                row.children[columnIndex].remove();
            }
        });
        
        // Add placeholder if no columns left
        const remainingColumns = headerRow.querySelectorAll('th');
        if (remainingColumns.length === 1) { // Only month column left
            const placeholderTh = document.createElement('th');
            placeholderTh.className = 'text-center text-muted';
            placeholderTh.innerHTML = '<em>No metrics defined. Click "Add Column" to start.</em>';
            headerRow.appendChild(placeholderTh);
            
            // Add empty cells to data rows
            const dataRows = document.querySelectorAll('.metrics-table tbody tr:not(.table-light)');
            dataRows.forEach(row => {
                const placeholderTd = document.createElement('td');
                row.appendChild(placeholderTd);
            });
            
            // Remove total row
            const totalRow = document.querySelector('.metrics-table tbody tr.table-light');
            if (totalRow) totalRow.remove();
        }
    }
    
    // Show toast notification
    function showToast(message, type = 'info') {
        // Create toast container if it doesn't exist
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }
        
        // Create toast element
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');
        
        // Set toast content
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'danger' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        // Add toast to container
        toastContainer.appendChild(toastEl);
        
        // Initialize and show toast using Bootstrap
        const bsToast = new bootstrap.Toast(toastEl, { autohide: true, delay: 3000 });
        bsToast.show();
        
        // Remove toast after it's hidden
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }
</script>
</html>