<?php
/**
 * Edit Outcome Details - Admin Version
 * 
 * Admin interface to edit outcome details with support for flexible table structures
 * Rewritten to align with agency side implementation
 */

// Include necessary files
require_once '../../../config/config.php';
require_once ROOT_PATH . 'app/lib/db_connect.php';
require_once ROOT_PATH . 'app/lib/session.php';
require_once ROOT_PATH . 'app/lib/functions.php';
require_once ROOT_PATH . 'app/lib/admins/outcomes.php';
require_once ROOT_PATH . 'app/lib/admins/index.php';
require_once ROOT_PATH . 'app/lib/audit_log.php';

// Verify user is an admin
if (!is_admin()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

// Get outcome ID from URL
$metric_id = isset($_GET['metric_id']) ? intval($_GET['metric_id']) : 0;

if ($metric_id === 0) {
    $_SESSION['error_message'] = 'Invalid outcome ID.';
    header('Location: manage_outcomes.php');
    exit;
}

// Get outcome data using the updated function
$outcome_details = get_outcome_data_for_display($metric_id);

if (!$outcome_details) {
    $_SESSION['error_message'] = 'Outcome not found.';
    header('Location: manage_outcomes.php');
    exit;
}

// Extract data from outcome_details
$table_name = $outcome_details['table_name'];
$sector_id = $outcome_details['sector_id'];
$period_id = $outcome_details['period_id'];
$created_at = new DateTime($outcome_details['created_at']);
$updated_at = new DateTime($outcome_details['updated_at']);
$outcome_data = json_decode($outcome_details['data_json'], true) ?? [];

// Initialize message variables
$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_table_name = trim($_POST['table_name'] ?? '');
    $post_data_json = $_POST['data_json'] ?? '';
    $post_row_config = $_POST['row_config'] ?? '';
    $post_column_config = $_POST['column_config'] ?? '';
    $post_structure_type = $_POST['structure_type'] ?? 'flexible';

    if (empty($post_table_name) || empty($post_data_json)) {
        $message = 'Table name and data are required.';
        $message_type = 'danger';
    } else {
        try {
            // Validate JSON data
            $data_check = json_decode($post_data_json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Invalid JSON data format.');
            }

            // Update the outcome
            $update_query = "UPDATE sector_outcomes_data 
                           SET table_name = ?, data_json = ?, row_config = ?, column_config = ?, table_structure_type = ?, updated_at = NOW() 
                           WHERE metric_id = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sssssi", 
                $post_table_name, 
                $post_data_json, 
                $post_row_config, 
                $post_column_config, 
                $post_structure_type,
                $metric_id
            );

            if ($update_stmt->execute()) {
                // Log the update
                log_audit_action(
                    'outcome_updated',
                    "Admin updated outcome '{$post_table_name}' (ID: {$metric_id})",
                    'success',
                    $_SESSION['user_id']
                );

                // Redirect with success message
                header('Location: view_outcome.php?metric_id=' . $metric_id . '&saved=1');
                exit;
            } else {
                throw new Exception('Failed to update outcome: ' . $conn->error);
            }
        } catch (Exception $e) {
            $message = 'Error updating outcome: ' . $e->getMessage();
            $message_type = 'danger';
            error_log("Admin outcome update error: " . $e->getMessage());
        }
    }
}

// Get flexible structure configuration
$table_structure_type = $outcome_details['table_structure_type'] ?? 'monthly';
$row_config = json_decode($outcome_details['row_config'] ?? '{}', true);
$column_config = json_decode($outcome_details['column_config'] ?? '{}', true);

// Determine if this is a flexible structure or legacy
$is_flexible = !empty($row_config) && !empty($column_config);

if ($is_flexible) {
    // New flexible structure
    $rows = $row_config['rows'] ?? [];
    $columns = $column_config['columns'] ?? [];
} else {
    // Legacy structure - convert to flexible format
    $metric_names = $outcome_data['columns'] ?? [];
    
    // Create default monthly rows
    $month_names = ['January', 'February', 'March', 'April', 'May', 'June', 
                    'July', 'August', 'September', 'October', 'November', 'December'];
    $rows = array_map(function($month) {
        return ['id' => $month, 'label' => $month, 'type' => 'data'];
    }, $month_names);
    
    $columns = array_map(function($col) {
        return ['id' => $col, 'label' => $col, 'type' => 'number', 'unit' => ''];
    }, $metric_names);
}

// Organize data for display
$table_data = [];
foreach ($rows as $row_def) {
    $row_data = ['row' => $row_def, 'metrics' => []];
    
    // Add data for each metric in this row
    if (isset($outcome_data[$row_def['id']])) {
        $row_data['metrics'] = $outcome_data[$row_def['id']];
    }
    
    $table_data[] = $row_data;
}

// Include header
require_once '../../layouts/header.php';

// Configure modern page header
$header_config = [
    'title' => 'Edit Outcome Details',
    'subtitle' => htmlspecialchars($table_name),
    'variant' => 'white',
    'actions' => [
        [
            'url' => 'view_outcome.php?metric_id=' . $metric_id,
            'text' => 'Back to View',
            'icon' => 'fas fa-arrow-left',
            'class' => 'btn-outline-secondary'
        ]
    ]
];

// Include modern page header
require_once '../../layouts/page_header.php';
?>

<div class="container-fluid px-4">
    <!-- Error/Message display -->
    <?php if (!empty($message)): ?>
        <div class="alert alert-<?= htmlspecialchars($message_type) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Outcome Information -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title m-0">
                    <i class="fas fa-edit me-2"></i>Editing: <?= htmlspecialchars($table_name) ?>
                </h5>
                <div>
                    <?php if ($is_flexible): ?>
                        <span class="badge bg-light text-dark ms-2">
                            <i class="fas fa-cogs me-1"></i> Flexible Structure
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="mb-3">
                        <strong>Outcome ID:</strong> <?= $metric_id ?>
                    </div>
                    <div class="mb-3">
                        <strong>Structure Type:</strong> 
                        <span class="badge bg-secondary"><?= ucfirst($table_structure_type) ?></span>
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
            </div>

            <!-- Edit Mode: Editable Form -->
            <form id="editFlexibleOutcomeForm" method="post" action="">
                <!-- Table Name Editor -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <label for="table_name" class="form-label">Outcome Name</label>
                        <input type="text" class="form-control" id="table_name" name="table_name" 
                               value="<?= htmlspecialchars($table_name) ?>" required>
                    </div>
                </div>

                <!-- Editable Data Table -->
                <div class="table-responsive">
                    <table class="table table-bordered" id="editableDataTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 150px;">Row</th>
                                <?php foreach ($columns as $column): ?>
                                    <th class="text-center" data-column-id="<?= htmlspecialchars($column['id']) ?>">
                                        <div><?= htmlspecialchars($column['label']) ?></div>
                                        <?php if (!empty($column['unit'])): ?>
                                            <small class="text-muted">(<?= htmlspecialchars($column['unit']) ?>)</small>
                                        <?php endif; ?>
                                    </th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($table_data as $row_index => $row_data): ?>
                                <tr data-row-id="<?= htmlspecialchars($row_data['row']['id']) ?>" 
                                    class="<?= $row_data['row']['type'] === 'separator' ? 'table-secondary' : '' ?>">
                                    <td>
                                        <span class="<?= $row_data['row']['type'] === 'separator' ? 'separator-badge' : 'month-badge' ?>">
                                            <?= htmlspecialchars($row_data['row']['label']) ?>
                                        </span>
                                    </td>
                                    <?php foreach ($columns as $col_idx => $column): ?>
                                        <td class="text-center">
                                            <?php if ($row_data['row']['type'] === 'separator'): ?>
                                                <span class="text-muted">—</span>
                                            <?php else: ?>
                                                <input type="number" 
                                                       class="form-control form-control-sm text-center data-input" 
                                                       step="0.01" 
                                                       value="<?= isset($row_data['metrics'][$col_idx]) && $row_data['metrics'][$col_idx] !== null ? number_format((float)$row_data['metrics'][$col_idx], 2, '.', '') : '' ?>"
                                                       data-row-id="<?= htmlspecialchars($row_data['row']['id']) ?>"
                                                       data-column-id="<?= htmlspecialchars($column['id']) ?>"
                                                       data-column-index="<?= $col_idx ?>"
                                                       style="min-width: 80px;">
                                            <?php endif; ?>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                            
                            <!-- Total Row (if needed) -->
                            <tr class="table-light fw-bold">
                                <td><span class="total-badge">TOTAL</span></td>
                                <?php foreach ($columns as $col_idx => $column): 
                                    $total = 0;
                                    foreach ($table_data as $row_data) {
                                        if ($row_data['row']['type'] !== 'separator' && isset($row_data['metrics'][$col_idx]) && is_numeric($row_data['metrics'][$col_idx])) {
                                            $total += (float)$row_data['metrics'][$col_idx];
                                        }
                                    }
                                ?>
                                    <td class="text-center total-cell" data-column-index="<?= $col_idx ?>">
                                        <?= number_format($total, 2) ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Hidden form fields for structure data -->
                <input type="hidden" id="data_json" name="data_json" value="">
                <input type="hidden" id="row_config" name="row_config" value="<?= htmlspecialchars(json_encode($row_config)) ?>">
                <input type="hidden" id="column_config" name="column_config" value="<?= htmlspecialchars(json_encode($column_config)) ?>">
                <input type="hidden" id="structure_type" name="structure_type" value="<?= htmlspecialchars($table_structure_type) ?>">

                <!-- Action Buttons -->
                <div class="mt-4 d-flex justify-content-between">
                    <div>
                        <button type="button" class="btn btn-outline-info" id="previewChangesBtn">
                            <i class="fas fa-eye me-1"></i> Preview Changes
                        </button>
                    </div>
                    <div>
                        <a href="view_outcome.php?metric_id=<?= $metric_id ?>" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-times me-1"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-success" id="submitBtn">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Include necessary scripts -->
<script>
// Initialize table data and structure for JavaScript
window.tableData = <?= json_encode($outcome_data) ?>;
window.tableStructure = {
    rows: <?= json_encode($rows) ?>,
    columns: <?= json_encode($columns) ?>
};
window.isFlexible = <?= $is_flexible ? 'true' : 'false' ?>;

document.addEventListener('DOMContentLoaded', function() {
    // Initialize data collection from inputs
    function collectTableData() {
        const data = {};
        const inputs = document.querySelectorAll('.data-input');
        
        inputs.forEach(input => {
            const rowId = input.dataset.rowId;
            const columnIndex = input.dataset.columnIndex;
            const value = input.value.trim();
            
            if (!data[rowId]) {
                data[rowId] = {};
            }
            
            data[rowId][columnIndex] = value !== '' ? parseFloat(value) || 0 : null;
        });
        
        return data;
    }
    
    // Update totals when data changes
    function updateTotals() {
        const data = collectTableData();
        const totalCells = document.querySelectorAll('.total-cell');
        
        totalCells.forEach(cell => {
            const columnIndex = cell.dataset.columnIndex;
            let total = 0;
            
            Object.keys(data).forEach(rowId => {
                if (data[rowId][columnIndex] !== null && !isNaN(data[rowId][columnIndex])) {
                    total += data[rowId][columnIndex];
                }
            });
            
            cell.textContent = total.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        });
    }
    
    // Add event listeners to data inputs
    document.querySelectorAll('.data-input').forEach(input => {
        input.addEventListener('input', updateTotals);
        input.addEventListener('change', updateTotals);
    });
    
    // Handle form submission
    document.getElementById('editFlexibleOutcomeForm').addEventListener('submit', function(e) {
        // Collect current data and update hidden field
        const currentData = collectTableData();
        document.getElementById('data_json').value = JSON.stringify(currentData);
    });
});
</script>

<?php 
// Include footer
require_once ROOT_PATH . 'app/views/layouts/footer.php';
?>
